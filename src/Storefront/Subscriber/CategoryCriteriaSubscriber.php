<?php

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategoryCriteriaSubscriber implements EventSubscriberInterface
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
        return [];
    }

    public function enrich($event): void
    {
    }
}
