<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Event;

use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ObjectType;
use Shopware\Core\Framework\Event\MailActionInterface;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class TableRegistrationMailEvent extends Event implements MailActionInterface, SalesChannelAware
{
    public const EVENT_NAME = 'moorl.foundation.table-registration';

    private ShopEntity $shop;
    private ?MailRecipientStruct $mailRecipientStruct = null;
    private Context $context;
    private string $salesChannelId;
    private array $contactFormData;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        DataBag $data,
        ShopEntity $shop
    )
    {
        $this->context = $salesChannelContext->getContext();
        $this->contactFormData = $data->all();
        $this->shop = $shop;
        $this->salesChannelId = $salesChannelContext->getSalesChannelId();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('shop', new EntityType(ShopDefinition::class))
            ->add('contactFormData', (new ObjectType()));
    }

    /**
     * @return array
     */
    public function getContactFormData(): array
    {
        return $this->contactFormData;
    }

    /**
     * @return ShopEntity
     */
    public function getShop(): ShopEntity
    {
        return $this->shop;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
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
