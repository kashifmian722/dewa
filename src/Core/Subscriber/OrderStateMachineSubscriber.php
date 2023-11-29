<?php

namespace Appflix\DewaShop\Core\Subscriber;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Appflix\DewaShop\Core\Service\InvoiceService;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriber;
use Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderStateMachineSubscriber implements EventSubscriberInterface
{
    private DewaShopService $dewaShopService;
    private InvoiceService $invoiceService;

    public function __construct(
        DewaShopService $dewaShopService,
        InvoiceService $invoiceService
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->invoiceService = $invoiceService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'state_enter.order_transaction.state.paid' => 'stateChanged',
            'state_enter.order_transaction.state.chargeback' => 'stateChanged',
            'state_enter.order_transaction.state.refunded' => 'stateChanged',
            'state_enter.order_transaction.state.paid_partially' => 'stateChanged',
            'state_enter.order_transaction.state.refunded_partially' => 'stateChanged',
            'state_enter.order_transaction.state.reminded' => 'stateChanged',
            'state_enter.order_transaction.state.cancelled' => 'stateChanged',
            'state_enter.order_transaction.state.authorized' => 'stateChanged',
            'state_enter.order_transaction.state.in_progress' => 'stateChanged',
            'state_enter.order_transaction.state.open' => 'stateChanged',
            'state_enter.order_delivery.state.shipped' => [
                ['stateChanged', 10],
                ['createInvoice']
            ],
            'state_enter.order_delivery.state.shipped_partially' => 'stateChanged',
            'state_enter.order_delivery.state.returned_partially' => 'stateChanged',
            'state_enter.order_delivery.state.returned' => 'stateChanged',
            'state_enter.order_delivery.state.open' => 'stateChanged',
            'state_enter.order_delivery.state.cancelled' => 'stateChanged'
        ];
    }

    public function stateChanged(OrderStateMachineStateChangeEvent $event): void
    {
        $this->dewaShopService->enrichOrder($event->getOrder());
    }

    public function createInvoice(OrderStateMachineStateChangeEvent $event): void
    {
        $documentId = $this->invoiceService->create($event->getOrder(), $event->getContext());
        if (!$documentId) {
            return;
        }

        if (!$event->getContext()->hasExtension(MailSendSubscriber::MAIL_CONFIG_EXTENSION)) {
            return;
        }

        /** @var MailSendSubscriberConfig $mailSendSubscriberConfig */
        $mailSendSubscriberConfig = $event->getContext()->getExtension(MailSendSubscriber::MAIL_CONFIG_EXTENSION);
        $mailSendSubscriberConfig->setDocumentIds([$documentId->getId()]);
    }
}
