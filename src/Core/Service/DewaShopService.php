<?php

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Content\OptionCategory\OptionCategoryCollection;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Appflix\DewaShop\Core\Content\Shop\ShopCollection;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Event\ShopOrderMailEvent;
use MoorlFoundation\Core\Framework\GeoLocation\BoundingBox;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class DewaShopService
{
    private ?Context $context;
    private EventDispatcherInterface $eventDispatcher;
    private ?SalesChannelContext $salesChannelContext;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    private RequestStack $requestStack;
    private Session $session;
    private PrintService $printService;
    private TranslatorInterface $translator;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        Session $session,
        PrintService $printService,
        TranslatorInterface $translator
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->printService = $printService;
        $this->translator = $translator;

        $this->context = Context::createDefaultContext();
    }

    public function posAutoClose(): void
    {
        return;

        /* Open shop if logged in admin or POS device is online */
        if (!$this->systemConfigService->get('AppflixDewaShop.config.posAutoOpen')) {
            return;
        }
        $updatedAt = (new \DateTimeImmutable('now'))->modify("-3 minutes");
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isOpen', 1));
        $criteria->addFilter(new RangeFilter('updatedAt', [
            'lte' => $updatedAt->format(DATE_ATOM)
        ]));
        $shopRepository = $this->definitionInstanceRegistry->getRepository(ShopDefinition::ENTITY_NAME);
        $shopIds = $shopRepository->searchIds($criteria, $this->context)->getIds();
        $payload = [];
        foreach ($shopIds as $shopId) {
            $payload[] = [
                'id' => $shopId,
                'isOpen' => false
            ];
        }
        $shopRepository->upsert($payload, $this->context);
    }

    public function enrichIsNotDewaShopProductCriteria(?Criteria $criteria = null): Criteria
    {
        if (!$criteria) {
            $criteria = new Criteria();
        }

        /* Old with Associations */
        $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
            new EqualsFilter('categories.parent.cmsPageId', Defaults::CMS_PAGE_ID)
        ]));

        return $criteria;

        /* New with one OneToOneAssociation - Indexed by Product EntityWriteResult */
        $criteria->addAssociation('dewaProductIndex');
        $criteria->addFilter(new EqualsFilter('dewaProductIndex.productId', null));
    }

    public function enrichDewaShopProductCriteria(Criteria $productCriteria): void
    {
        $productCriteria->addFilter(new NotFilter(NotFilter::CONNECTION_OR, [
            new EqualsFilter('id', null),
            new EqualsFilter('active', false)
        ]));

        $productCriteria->addSorting(new FieldSorting('productNumber', FieldSorting::ASCENDING));

        if ($this->salesChannelContext) {
            $productCriteria->addFilter(new EqualsFilter(
                    'visibilities.salesChannelId',
                    $this->salesChannelContext->getSalesChannelId())
            );
        }

        $shop = $this->getShop();
        if ($shop) {
            $productCriteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('shops.id', $shop->getId()),
                new EqualsFilter('shops.id', null),
            ]));

            $productCriteria->addAssociation('stocks');
            $productCriteria->addAssociation('bundles');
            $productStocksCriteria = $productCriteria->getAssociation('stocks');
            $productStocksCriteria->addFilter(new EqualsFilter('shopId', $shop->getId()));
        }
    }

    public function enrichIsDewaShopProductCriteria(?Criteria $criteria = null): Criteria
    {
        if (!$criteria) {
            $criteria = new Criteria();
        }
        $this->enrichDewaShopProductCriteria($criteria);

        /* Old with Associations */
        $criteria->addAssociation('categories.parent');
        $criteria->addAssociation('categories.options');
        $criteria->addFilter(new EqualsFilter('categories.parent.cmsPageId', Defaults::CMS_PAGE_ID));
        $categoryCriteria = $criteria->getAssociation('categories');
        $categoryCriteria->addFilter(new EqualsFilter('parent.cmsPageId', Defaults::CMS_PAGE_ID));

        return $criteria;

        /* New with one OneToOneAssociation - Indexed by Product EntityWriteResult */
        $criteria->addAssociation('dewaProductIndex');
        $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
            new EqualsFilter('dewaProductIndex.productId', null)
        ]));
    }

    public function getShopOrder(string $orderId): ?ShopOrderEntity
    {
        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop_order');

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addAssociation('deliverer');
        $criteria->addAssociation('shop.media');
        $criteria->addAssociation('order.deliveries.shippingOrderAddress');
        $criteria->addAssociation('order.deliveries.stateMachineState');
        $criteria->addAssociation('order.transactions.stateMachineState');
        $criteria->addAssociation('order.stateMachineState');
        $criteria->addFilter(new EqualsFilter('orderId', $orderId));

        return $shopOrderRepository->search($criteria, $this->context)->first();
    }

    public function updateShopOrder($data): void
    {
        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop_order');
        $shopOrderRepository->upsert([$data], $this->context);
    }

    public function insertShopOrder(OrderEntity $order, ?string $shopId = null): void
    {
        try {
            $shippingMethodId = $order->getDeliveries()->first()->getShippingMethodId();
        } catch (\Exception $exception) {
            return;
        }
        if (!in_array($shippingMethodId, [
            Defaults::SHIPPING_METHOD_DELIVERY_ID,
            Defaults::SHIPPING_METHOD_COLLECT_ID
        ])) {
            return;
        }

        $data = $this->getSession();
        if (empty($data['shopId'])) {
            return;
        }

        $criteria = new Criteria([$data['shopId']]);

        $shopRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop');
        $shop = $shopRepository->search($criteria, $this->context)->get($data['shopId']);
        if (!$shop) {
            return;
        }

        $shopOrderId = Uuid::randomHex();

        $data = array_merge($data, [
            'id' => $shopOrderId,
            'orderId' => $order->getId()
        ]);

        $data['desiredTime'] = $this->getCalculatedTime($shippingMethodId, $shopId)->format(DATE_ATOM);

        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop_order');
        $shopOrderRepository->upsert([$data], $this->context);

        if ($this->systemConfigService->get('AppflixDewaShop.config.orderMail')) {
            /** @var ShopOrderEntity $shopOrder */
            $shopOrder = $shopOrderRepository->search(new Criteria([$shopOrderId]), $this->context)->get($shopOrderId);
            $shopOrder->setOrder($order);
            $shopOrder->setShop($shop);

            $event = new ShopOrderMailEvent(
                $this->context,
                $shopOrder
            );
            $this->eventDispatcher->dispatch($event);
        }

        if ($this->systemConfigService->get('AppflixDewaShop.config.orderPrintJob')) {
            $this->printService->addPrintJobByShopOrderId($shopOrderId);
        }
    }

    public function getCalculatedTime(string $shippingMethodId, ?string $shopId = null, bool $localTimezone = false): \DateTimeImmutable
    {
        $data = $this->getSession();
        $shop = $this->getShop($shopId);
        $calculatedTime = new \DateTimeImmutable();

        if (!$data || !$shop) {
            return $calculatedTime;
        }

        // TODO: Add preperation time if no desired date set
        $calculatedTime = $calculatedTime->modify(sprintf('+%d minute', $shop->getPreparationTime()));

        // TODO: Use sum of time for the countdown
        if ($shippingMethodId === Defaults::SHIPPING_METHOD_DELIVERY_ID) {
            $calculatedTime = $calculatedTime->modify(sprintf('+%d minute', $shop->getDeliveryTime()));
        }

        if ($localTimezone) {
            return $calculatedTime->setTimezone(new \DateTimeZone($shop->getTimeZone()));
        }

        // TODO: Test Timezone
        if (!empty($data['desiredTime'])) {
            preg_match('/(\+\d+.\w+)/', $data['desiredTime'], $matches, PREG_UNMATCHED_AS_NULL);

            if (!empty($matches[1])) {
                $desiredTime = (new \DateTimeImmutable('now', new \DateTimeZone($shop->getTimeZone())))->modify($data['desiredTime']);
            } else {
                $desiredTime = new \DateTimeImmutable($data['desiredTime'], new \DateTimeZone($shop->getTimeZone()));
            }

            if ($desiredTime > $calculatedTime) {
                $calculatedTime = $desiredTime;
            }
        }

        return $calculatedTime;
    }

    public function enrichOrder(OrderEntity $order): void
    {
        $shopOrderRepository = $this->definitionInstanceRegistry->getRepository('dewa_shop_order');

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addAssociation('shop.media');
        $criteria->addAssociation('deliverer.media');
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));

        /** @var ShopOrderEntity $shopOrder */
        $shopOrder = $shopOrderRepository->search($criteria, $this->context)->first();

        if (!$shopOrder) {
            return;
        }

        $order->assign(['dewa' => $shopOrder]);
    }

    public function getShop(?string $id = null, ?GeoPoint $geoPoint = null): ?ShopEntity
    {
        if (!$id) {
            $data = $this->getSession();

            if (empty($data['shopId'])) {
                $shops = $this->getShops();

                if ($shops->count() === 1) {
                    $this->setSession([
                        'shopId' => $shops->first()->getId()
                    ]);

                    return $shops->first();
                }

                return null;
            } else {
                $id = $data['shopId'];
            }
        }

        $repo = $this->definitionInstanceRegistry->getRepository('dewa_shop');

        $criteria = new Criteria([$id]);
        $criteria->setLimit(1);
        $criteria->addAssociation('media');
        $criteria->addAssociation('country');
        $criteria->addAssociation('shopAreas');

        /** @var ShopEntity $shop */
        $shop = $repo->search($criteria, $this->context)->first();
        if (!$shop) {
            return null;
        }

        $shop->setDistance($geoPoint);

        return $shop;
    }

    public function getShops(?Criteria $criteria = null, ?GeoPoint $geoPoint = null): ShopCollection
    {
        $repo = $this->definitionInstanceRegistry->getRepository('dewa_shop');

        if (!$criteria) {
            $criteria = new Criteria();
            $criteria->addAssociation('media');
            $criteria->addAssociation('country');
            $criteria->addAssociation('shopAreas');
        }

        if ($this->salesChannelContext) {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('salesChannels.id', $this->salesChannelContext->getSalesChannelId()),
                new EqualsFilter('salesChannels.id', null)
            ]));
        }

        if ($geoPoint) {
            /** @var BoundingBox $boundingBox */
            $boundingBox = $geoPoint->boundingBox(Defaults::SHOP_RADIUS, 'km');

            $criteria->addFilter(
                new RangeFilter('locationLat', [
                    'gte' => $boundingBox->getMinLatitude(),
                    'lte' => $boundingBox->getMaxLatitude()
                ])
            );
            $criteria->addFilter(
                new RangeFilter('locationLon', [
                    'lte' => $boundingBox->getMaxLongitude(),
                    'gte' => $boundingBox->getMinLongitude()
                ])
            );
        }

        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var ShopCollection $shops  */
        $shops = $repo->search($criteria, $this->context)->getEntities();

        if ($geoPoint) {
            $shops->addDistances($geoPoint);

            return $shops->sortByDistance();
        }

        return $shops;
    }

    public function getChildCategories(string $categoryId): CategoryCollection
    {
        $categoryRepository = $this->definitionInstanceRegistry->getRepository('sales_channel.category');

        $criteria = new Criteria();
        $criteria->addAssociation('products');
        $criteria->addFilter(new EqualsFilter('parentId', $categoryId));

        return $categoryRepository->search($criteria, $this->context)->getEntities();
    }

    public function calculateProductConfigurator(SalesChannelProductEntity $product, ?RequestDataBag $configurator = null): LineItem
    {
        $this->enrichProductConfigurator($product);

        $id = md5(serialize([
            'productId' => $product->getId(),
            'configurator' => $configurator
        ]));

        $lineItem = new LineItem($id, Defaults::LINE_ITEM, $product->getId(), 1);
        $lineItem->setStackable(true);
        $lineItem->setRemovable(true);

        if (!$configurator) {
            $lineItem->setPrice($product->getCalculatedPrice());
        } else {
            /* Prepare calculation */
            $payload = [];
            $calculatedPrice = $product->getCalculatedPrice()->getUnitPrice();
            $pricePrecision = $this->systemConfigService->get('AppflixDewaShop.config.pricePrecision') ?: 0.25;
            $priceFactor = 1;
            $round = function(float $number) use ($pricePrecision) {
                return round($number / $pricePrecision) * $pricePrecision;
            };

            $purchaseUnit = $product->getPurchaseUnit() ?: 1;
            $referenceUnit = $product->getReferenceUnit() ?: 1;

            /** @var OptionCategoryCollection $options */
            $options = $product->getExtension('options');

            foreach ($options as $option) {
                if ($option->getOption()->getType() === 'single') {
                    if (!$configurator->get($option->getId())) {
                        continue;
                    }
                    $optionItemId = $configurator->get($option->getId());

                    $optionItem = $option->getOption()->getItems()->get($optionItemId);
                    if (!$optionItem) {
                        continue;
                    }

                    $priceFactor = $priceFactor * $optionItem->getPriceFactor();
                    $calculatedPrice = $round($calculatedPrice * $priceFactor);

                    $purchaseUnit = $optionItem->getPurchaseUnit() ?: $purchaseUnit;
                    $referenceUnit = $option->getOption()->getReferenceUnit() ?: $referenceUnit;

                    $payload[] = [
                        'name' => $option->getTranslated()['name'],
                        'value' => $optionItem->getTranslated()['name']
                    ];
                }
            }

            foreach ($options as $option) {
                if (in_array($option->getOption()->getType(), ['checkbox', 'radio'])) {
                    if (empty($configurator->get($option->getId()))) {
                        continue;
                    }
                    $optionItemIds = $configurator->get($option->getId())->all();

                    $valuePayload = [];
                    foreach ($optionItemIds as $optionItemId) {
                        $optionItem = $option->getOption()->getItems()->get($optionItemId);
                        if (!$optionItem) {
                            continue;
                        }

                        $calculatedPrice = $calculatedPrice + $round($optionItem->getPrice() * $priceFactor);
                        $valuePayload[] = $optionItem->getTranslated()['name'];
                    }

                    if (count($valuePayload) > 0) {
                        $payload[] = [
                            'name' => $option->getTranslated()['name'],
                            'value' => $valuePayload
                        ];
                    }
                }

                if ($option->getOption()->getType() === 'textarea') {
                    if (!$configurator->get($option->getId())) {
                        continue;
                    }

                    $payload[] = [
                        'name' => $option->getTranslated()['name'],
                        'value' => $configurator->get($option->getId())
                    ];
                }
            }

            foreach ($options as $option) {
                if ($option->getOption()->getType() === 'deposit') {
                    $calculatedPrice = $calculatedPrice + (float) $option->getOption()->getReferenceUnit();

                    $payload[] = [
                        'name' => $this->translator->trans('dewa-shop.menu.depositShort'),
                        'value' => $configurator->get($option->getId())
                    ];
                }
            }

            $lineItem->setPrice(new CalculatedPrice(
                $calculatedPrice,
                $calculatedPrice,
                $product->getCalculatedPrice()->getCalculatedTaxes(),
                $product->getCalculatedPrice()->getTaxRules()
            ));

            /**
             * @deprecated Use dewa instead dewa_shop
             */
            $lineItem->setPayloadValue('dewa_shop', $payload);

            $lineItem->setPayloadValue('dewa', $payload);
            $lineItem->setPayloadValue('stockUnit', $referenceUnit / $purchaseUnit);
        }

        $lineItem->setPayloadValue('productNumber', $product->getProductNumber());

        return $lineItem;
    }

    public function enrichProductConfigurator(?SalesChannelProductEntity $product = null): void
    {
        if (!$product) {
            return;
        }

        $options = new EntityCollection();

        /* Step 1: Add linked Options to Product */
        if ($product->getExtension('dewaOptions')) {
            $options->merge($product->getExtension('dewaOptions'));
        }

        /* Step 1: Add linked Options from Categories to Product */
        $categories = $product->getCategories();

        if ($categories) {
            foreach ($categories as $category) {
                if ($category->getExtension('options')) {
                    $options->merge($category->getExtension('options'));
                }
            }
        }

        $product->addExtension('options', $options);
    }

    public function getEntityById(string $entityName, string $id, array $associations = []): ?Entity
    {
        $repo = $this->definitionInstanceRegistry->getRepository($entityName);

        $criteria = new Criteria([$id]);
        $criteria->setLimit(1);
        $criteria->addAssociations($associations);

        return $repo->search($criteria, $this->getContext())->get($id);
    }

    public function setSession(array $payload): void
    {
        $data = $this->session->get('dewa') ?: [];
        $data = array_merge($data, $payload);
        $this->session->set('dewa', $data);

        $this->setCurrentCustomerSession($data);
    }

    public function getSession(): array
    {
        return $this->getCurrentCustomerSession() ?: $this->session->get('dewa') ?: [];
    }

    public function setCurrentCustomerSession(array $data): void
    {
        $customerId = $this->salesChannelContext->getCustomerId();
        if (!$customerId) {
            return;
        }

        $customerRepository = $this->definitionInstanceRegistry->getRepository(CustomerDefinition::ENTITY_NAME);

        $customerRepository->upsert([[
            'id' => $customerId,
            'customFields' => [
                'dewa' => $data
            ]
        ]], $this->context);
    }

    public function getCurrentCustomerSession(): ?array
    {
        $customerId = $this->salesChannelContext->getCustomerId();

        $customerRepository = $this->definitionInstanceRegistry->getRepository(CustomerDefinition::ENTITY_NAME);

        /** @var CustomerEntity $customer */
        $customer = $customerRepository->search(new Criteria([$customerId]), $this->context)->get($customerId);
        if (!$customer) {
            return null;
        }

        $customFields = $customer->getCustomFields();
        if (empty($customFields['dewa'])) {
            return null;
        }

        return $customFields['dewa'];
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

    /**
     * @return SalesChannelContext|null
     */
    public function getSalesChannelContext(): ?SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext|null $salesChannelContext
     */
    public function setSalesChannelContext(?SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->context = $salesChannelContext->getContext();
    }

    /**
     * @return SystemConfigService
     */
    public function getSystemConfigService(): SystemConfigService
    {
        return $this->systemConfigService;
    }

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function setSystemConfigService(SystemConfigService $systemConfigService): void
    {
        $this->systemConfigService = $systemConfigService;
    }
}
