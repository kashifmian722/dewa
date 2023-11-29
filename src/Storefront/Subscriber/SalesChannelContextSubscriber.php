<?php declare(strict_types=1);

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelContextSubscriber implements EventSubscriberInterface
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
            SalesChannelContextResolvedEvent::class => 'onSalesChannelContextResolved'
        ];
    }

    public function onSalesChannelContextResolved(SalesChannelContextResolvedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();
        if (!$salesChannelContext) {
            return;
        }

        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        $this->dewaShopService->posAutoClose();

        $salesChannelContext->assign(['activeShop' => $this->dewaShopService->getShop()]);
        $salesChannelContext->addExtension('shops', $this->dewaShopService->getShops());
    }
}
