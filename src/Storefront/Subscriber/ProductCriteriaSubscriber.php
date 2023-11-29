<?php

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductCriteriaSubscriber implements EventSubscriberInterface
{
    private DewaShopService $dewaShopService;

    public function __construct(
        DewaShopService $dewaShopService
    )
    {
        $this->dewaShopService = $dewaShopService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'enrich',
            ProductPageCriteriaEvent::class => 'enrich',
            ProductSearchCriteriaEvent::class => 'enrich',
            ProductSuggestCriteriaEvent::class => 'enrich'
        ];
    }

    public function enrich($event): void
    {
        /* @var $criteria Criteria */
        $criteria = $event->getCriteria();
        if (!$criteria) {
            return;
        }

        $this->dewaShopService->enrichIsNotDewaShopProductCriteria($criteria);
    }
}
