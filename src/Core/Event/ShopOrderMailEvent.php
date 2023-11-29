<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Event;

use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\MailActionInterface;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Symfony\Contracts\EventDispatcher\Event;

class ShopOrderMailEvent extends Event implements MailActionInterface, SalesChannelAware
{
    public const EVENT_NAME = 'appflix.dewa-shop.shop-order-mail';

    /**
     * @var OrderEntity
     */
    private $order;

    /**
     * @var ShopOrderEntity
     */
    private $shopOrder;

    /**
     * @var ShopEntity
     */
    private $shop;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var MailRecipientStruct|null
     */
    private $mailRecipientStruct;

    /**
     * @var string
     */
    private $salesChannelId;

    public function __construct(Context $context, ShopOrderEntity $shopOrder)
    {
        $this->shopOrder = $shopOrder;
        $this->order = $shopOrder->getOrder();
        $this->shop = $shopOrder->getShop();
        $this->context = $context;
        $this->salesChannelId = $shopOrder->getOrder()->getSalesChannelId();
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('order', new EntityType(OrderDefinition::class));
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $this->mailRecipientStruct = new MailRecipientStruct([$this->shop->getEmail() => $this->shop->getEmail()]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }
}
