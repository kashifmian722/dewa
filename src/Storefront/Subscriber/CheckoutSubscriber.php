<?php

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Service\DewaShopService;
use MoorlFoundation\Core\Service\LocationService;
use Appflix\DewaShop\Core\Service\StockService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    private DewaShopService $dewaShopService;
    private StockService $stockService;
    private LocationService $locationService;
    private SystemConfigService $systemConfigService;

    public function __construct(
        DewaShopService $dewaShopService,
        LocationService $locationService,
        StockService $stockService,
        SystemConfigService $systemConfigService
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->locationService = $locationService;
        $this->stockService = $stockService;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onPageLoaded',
            CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced',
            CheckoutFinishPageLoadedEvent::class => 'onPageLoaded'
        ];
    }

    public function onPageLoaded(ShopwareSalesChannelEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();
        if (!$salesChannelContext) {
            return;
        }

        /* Shop selection is just necessary if one of the following shipping method selected */
        $shippingMethod = $salesChannelContext->getShippingMethod();
        $shippingMethodId = $shippingMethod->getId();
        if (!in_array($shippingMethodId, [
            Defaults::SHIPPING_METHOD_DELIVERY_ID,
            Defaults::SHIPPING_METHOD_COLLECT_ID
        ])) {
            return;
        }

        $customer = $salesChannelContext->getCustomer();
        if (!$customer) {
            return;
        }

        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        $geoPoint = $this->locationService->getCustomerLocation($customer);
        $shops = $this->dewaShopService->getShops(null, $geoPoint);

        $data = $this->dewaShopService->getSession();
        $data = array_merge($data, [
            'shops' => $shops,
            'calculatedTime' => $this->dewaShopService->getCalculatedTime(
                $salesChannelContext->getShippingMethod()->getId(),
                null,
                true
            )
        ]);

        if ($event instanceof CheckoutFinishPageLoadedEvent) {
            $data['shopOrder'] = $this->dewaShopService->getShopOrder($event->getPage()->getOrder()->getId());
        } elseif ($event instanceof CheckoutConfirmPageLoadedEvent) {
            $checkoutTimepicker = $this->systemConfigService->get('AppflixDewaShop.config.checkoutTimepicker');

            $checkoutDropdownSteps = $this->systemConfigService->get('AppflixDewaShop.config.checkoutDropdownSteps');
            if (!$checkoutDropdownSteps) {
                $checkoutDropdownSteps = '30,45,60,90,120';
            }
            $checkoutDropdownAsap = $this->systemConfigService->get('AppflixDewaShop.config.checkoutDropdownAsap');
            if ($checkoutDropdownAsap) {
                $checkoutDropdownSteps = sprintf("0,%s", $checkoutDropdownSteps);
            }
            $checkoutDropdownSteps = explode(',', $checkoutDropdownSteps);

            $checkoutDateMin = $this->systemConfigService->get('AppflixDewaShop.config.checkoutDateMin') ?: 0;
            $minDate = new \DateTimeImmutable(sprintf("+%d day", $checkoutDateMin));

            $data['checkout'] = [
                'timepicker' => $checkoutTimepicker,
                'dropdownSteps' => $checkoutDropdownSteps,
                'minDate' => $minDate->format("Y-m-d"),
            ];
        }

        $salesChannelContext->assign(['dewa' => $data]);
    }

    public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $this->dewaShopService->setContext($event->getContext());
        $this->dewaShopService->insertShopOrder($event->getOrder());
        $this->dewaShopService->enrichOrder($event->getOrder());

        $this->stockService->updateStocksByOrder($event->getOrder());
    }
}
