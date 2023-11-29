<?php

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Content\Product\Aggregate\ProductIndex\ProductIndexEntity;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Symfony\Component\Console\Output\OutputInterface;

class ProductWeekdayService
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

    public function execute(): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('dewaProductIndex');
        $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
            new EqualsFilter('dewaProductIndex.productId', null)
        ]));

        $productRepository = $this->definitionInstanceRegistry->getRepository('product');

        /** @var ProductCollection $products */
        $products = $productRepository->search($criteria, $this->context)->getEntities();
        $payload = [];

        foreach ($products as $product) {
            /** @var ProductIndexEntity $dewaProductIndex */
            $dewaProductIndex = $product->getExtension('dewaProductIndex');

            if (!$dewaProductIndex->getTodayConfig()) {
                continue;
            }

            $todayConfig = $dewaProductIndex->getTodayConfig();

            $payloadPrice = [];

            foreach ($product->getPrice() as $price) {
                if (!$price->getListPrice()) {
                    $payloadPrice[] = [
                        'currencyId' => $price->getCurrencyId(),
                        'net' => $price->getNet(),
                        'gross' => $price->getGross(),
                        'linked' => $price->getLinked()
                    ];

                    continue;
                }

                $listPrice = $price->getListPrice();

                $payloadPrice[] = [
                    'currencyId' => $price->getCurrencyId(),
                    'net' => $listPrice->getNet() * (100 - $todayConfig['discountValue']) / 100,
                    'gross' => $listPrice->getGross() * (100 - $todayConfig['discountValue']) / 100,
                    'linked' => $price->getLinked(),
                    'listPrice' => [
                        'currencyId' => $listPrice->getCurrencyId(),
                        'net' => $listPrice->getNet(),
                        'gross' => $listPrice->getGross(),
                        'linked' => $listPrice->getLinked(),
                    ]
                ];
            }

            $payload[] = [
                'id' => $product->getId(),
                'active' => $todayConfig['active'],
                'price' => $payloadPrice
            ];
        }

        $productRepository->upsert($payload, $this->context);
    }
}
