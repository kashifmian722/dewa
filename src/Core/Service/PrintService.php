<?php

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover\PrintTurnoverDefinition;
use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover\PrintTurnoverEntity;
use Appflix\DewaShop\Core\Content\Printer\PrinterDefinition;
use Appflix\DewaShop\Core\Content\Printer\PrinterEntity;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderCollection;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use GuzzleHttp\ClientInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class PrintService
 * @package Appflix\DewaShop\Core\Service
 *
 * TODO: Add Cloud Print Service
 * https://star-emea.com/de/simple-on-line-receipt-and-order-printing-with-star-web-and-cloud-printing-utilities/
 */
class PrintService
{
    private ?Context $context;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    protected ClientInterface $client;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->context = Context::createDefaultContext();
    }

    public function addPrintTurnover(string $shopId, string $interval, string $key): void
    {
        $criteria = new Criteria([$shopId]);
        $criteria->setLimit(1);
        $criteria->addAssociation('printers');
        $shopRepository = $this->definitionInstanceRegistry->getRepository(ShopDefinition::ENTITY_NAME);
        $shop = $shopRepository->search($criteria, $this->context)->get($shopId);
        $printers = $shop->getPrinters();
        if (!$printers) {
            return;
        }

        $printTurnoverRepository = $this->definitionInstanceRegistry->getRepository(PrintTurnoverDefinition::ENTITY_NAME);
        foreach ($printers as $printer) {
            $printTurnoverRepository->upsert([
                [
                    'id' => Uuid::randomHex(),
                    'printerId' => $printer->getId(),
                    'shopId' => $shopId,
                    'key' => $key,
                    'interval' => $interval
                ]
            ], $this->context);
        }
    }

    public function getPrintTurnoverShopOrders(PrintTurnoverEntity $printTurnover): ?ShopOrderCollection
    {
        $minDate = new \DateTimeImmutable($printTurnover->getKey(), new \DateTimeZone($printTurnover->getShop()->getTimeZone()));
        $maxDate = $minDate->modify(sprintf("+1 %s", $printTurnover->getInterval()));

        $criteria = new Criteria();
        $criteria->addAssociation('order.currency');
        $criteria->addAssociation('order.transactions.paymentMethod');
        $criteria->addAssociation('order.deliveries.shippingMethod');
        $criteria->addAssociation('order.stateMachineState');
        $criteria->addFilter(new EqualsFilter('order.stateMachineState.technicalName', 'completed'));
        $criteria->addFilter(new EqualsFilter('shopId', $printTurnover->getShopId()));
        $criteria->addFilter(new RangeFilter('order.orderDateTime', [
            'gte' => $minDate->format(DATE_ATOM),
            'lt' => $maxDate->format(DATE_ATOM)
        ]));
        $criteria->addSorting(new FieldSorting('order.orderDateTime', FieldSorting::ASCENDING));

        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository(ShopOrderDefinition::ENTITY_NAME);

        return $shopOrderRepository->search($criteria, $this->context)->getEntities();
    }

    public function addPrintJobByShopOrderId(string $shopOrderId): void
    {
        $criteria = new Criteria([$shopOrderId]);
        $criteria->setLimit(1);
        $criteria->addAssociation('shop.printers');
        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository(ShopOrderDefinition::ENTITY_NAME);
        /** @var ShopOrderEntity $shopOrder */
        $shopOrder = $shopOrderRepository->search($criteria, $this->context)->get($shopOrderId);

        $shop = $shopOrder->getShop();
        if (!$shop) {
            return;
        }

        $printers = $shop->getPrinters();
        if (!$printers) {
            return;
        }

        foreach ($printers as $printer) {
            $this->addPrintJob($printer->getId(), $shopOrderId);
        }
    }

    /**
     * Set queue of a order for selected printer
     */
    public function addPrintJob(string $printerId, string $shopOrderId): void
    {
        // $shopOrder = $this->getShopOrder($shopOrderId);
        // $printer = $this->getPrinter($printerId);

        $printJobRepository = $this->definitionInstanceRegistry->getRepository('dewa_print_job');

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('shopOrderId', $printerId));
        $criteria->addFilter(new EqualsFilter('printerId', $shopOrderId));
        $printJobs = $printJobRepository->search($criteria, $this->context)->getEntities();

        // Add print job only once
        if (count($printJobs) === 0) {
            $printJobRepository->upsert([[
                'shopOrderId' => $shopOrderId,
                'printerId' => $printerId,
            ]], $this->context);
        }
    }

    public function getShopOrder(string $shopOrderId): ?ShopOrderEntity
    {
        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop_order');

        $criteria = new Criteria([$shopOrderId]);
        $criteria->setLimit(1);
        $criteria->addAssociation('deliverer');
        $criteria->addAssociation('shop.media');
        $criteria->addAssociation('order.deliveries.shippingOrderAddress');
        $criteria->addAssociation('order.deliveries.shippingMethod');
        $criteria->addAssociation('order.deliveries.stateMachineState');
        $criteria->addAssociation('order.transactions.stateMachineState');
        $criteria->addAssociation('order.stateMachineState');
        $criteria->addAssociation('order.lineItems');

        return $shopOrderRepository->search($criteria, $this->context)->get($shopOrderId);
    }

    public function getPrinter(string $printerId): ?PrinterEntity
    {
        $printerRepository = $this->definitionInstanceRegistry->getRepository('dewa_printer');

        $criteria = new Criteria([$printerId]);
        $criteria->setLimit(1);

        return $printerRepository->search($criteria, $this->context)->get($printerId);
    }

    public function getPrinterFromMAC(string $macAddress, bool $isPrinting = false): ?PrinterEntity
    {
        $printerRepository = $this->definitionInstanceRegistry->getRepository(PrinterDefinition::ENTITY_NAME);

        $criteria = new Criteria();

        $criteria->setLimit(1)->addFilter(new EqualsFilter('mac', (string) $macAddress));

        $criteria->addAssociation('printJobs');
        $criteria->addAssociation('printJobs.shopOrder');
        $criteria->addAssociation('printJobs.shopOrder.deliverer');
        $criteria->addAssociation('printJobs.shopOrder.shop.media');
        $criteria->addAssociation('printJobs.shopOrder.order.deliveries.shippingOrderAddress');
        $criteria->addAssociation('printJobs.shopOrder.order.deliveries.shippingMethod');
        $criteria->addAssociation('printJobs.shopOrder.order.deliveries.stateMachineState');
        $criteria->addAssociation('printJobs.shopOrder.order.transactions.stateMachineState');
        $criteria->addAssociation('printJobs.shopOrder.order.stateMachineState');
        $criteria->addAssociation('printJobs.shopOrder.order.lineItems');
        $criteria->getAssociation('printJobs')->addFilter(new EqualsFilter('isPrinting', $isPrinting));

        $criteria->addAssociation('printTurnovers.shop');
        $criteria->getAssociation('printTurnovers')->addFilter(new EqualsFilter('isPrinting', $isPrinting));

        return $printerRepository->search($criteria, $this->context)->first();
    }

    /**
     * @return Context|null
     */
    public function getContext(): ?Context
    {
        return $this->context;
    }

    /**
     * @param Context|null $context
     */
    public function setContext(?Context $context): void
    {
        $this->context = $context;
    }
}
