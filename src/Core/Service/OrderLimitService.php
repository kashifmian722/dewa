<?php

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Appflix\DewaShop\Core\Content\Stock\StockCollection;
use Appflix\DewaShop\Core\Defaults;
use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Symfony\Component\Console\Output\OutputInterface;

class OrderLimitService
{
    private Connection $connection;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private Context $context;
    private ?OutputInterface $output = null;

    public function __construct(
        Connection $connection,
        DefinitionInstanceRegistry $definitionInstanceRegistry
    )
    {
        $this->connection = $connection;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;

        $this->context = Context::createDefaultContext();
    }

    /**
     * @return OutputInterface|null
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface|null $output
     */
    public function setOutput(?OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function writeln($msg): void
    {
        if ($this->output) {
            $this->output->writeln($msg);
        }
    }

    /**
     * @param OrderEntity $order
     * @param string $state
     *
     * TODO: Reset Stock if Order get state `cancelled` (Core Subscriber)
     */
    public function updateStocksByOrder(OrderEntity $order, string $state = 'open'): void
    {
        if (!$order->has('dewa')) {
            return;
        }

        /** @var ShopOrderEntity $shopOrder */
        $shopOrder = $order->get('dewa');
        if (!$shopOrder) {
            return;
        }

        $shopId = $shopOrder->getShopId();
        $stocks = $this->getShopStockItems($shopId);

        foreach ($order->getLineItems() as $lineItem) {
            $stock = $stocks->getByProductId($lineItem->getReferencedId());

            if (!$stock) {
                continue;
            }

            if ($state === 'open') {
                $stock->setStock($stock->getStock() - $lineItem->getQuantity());
            } elseif ($state === 'cancelled') {
                $stock->setStock($stock->getStock() + $lineItem->getQuantity());
            }
        }

        $payload = [];

        foreach ($stocks->filterModified() as $stock) {
            $payload[] = [
                'id' => $stock->getId(),
                'stock' => $stock->getStock()
            ];
        }

        $stockRepository = $this->definitionInstanceRegistry->getRepository('dewa_stock');
        $stockRepository->upsert($payload, $this->context);
    }

    public function executeTask(): void
    {
        $stocks = $this->getRestockItems();
        $date = new \DateTimeImmutable();
        $payload = [];

        foreach ($stocks as $stock) {
            if ($stock->getRestockRule() === 'add') {
                $setStock = $stock->getStock() + $stock->getRestockAmount();
            } elseif ($stock->getRestockRule() === 'reset') {
                $setStock = $stock->getRestockAmount();
            } else {
                continue;
            }

            $payload[] = [
                'id' => $stock->getId(),
                'stock' => $setStock,
                'restockedAt' => $date->format(DATE_ATOM)
            ];
        }

        $stockRepository = $this->definitionInstanceRegistry->getRepository('dewa_stock');
        $stockRepository->upsert($payload, $this->context);
    }

    public function getShopStockItems(string $shopId): StockCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('shopId', $shopId));

        $stockRepository = $this->definitionInstanceRegistry->getRepository('dewa_stock');

        return $stockRepository->search($criteria, $this->context)->getEntities();
    }

    public function getRestockItems(): StockCollection
    {
        $criteria = new Criteria();
        $filters = [];

        foreach (Defaults::getRestockIntervals() as $restockIntervalKey => $restockInterval) {
            $date = new \DateTimeImmutable($restockInterval['modify']);

            $filters[] = new MultiFilter(MultiFilter::CONNECTION_AND, [
                new RangeFilter('restockedAt', [
                    'lte' => $date->format(DATE_ATOM)
                ]),
                new EqualsFilter('restockInterval', $restockIntervalKey)
            ]);
        }

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filters));
        $criteria->addFilter(new EqualsAnyFilter('restockRule', ['add','reset']));

        $stockRepository = $this->definitionInstanceRegistry->getRepository('dewa_stock');

        return $stockRepository->search($criteria, $this->context)->getEntities();
    }
}
