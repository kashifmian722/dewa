<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder;

use Appflix\DewaShop\Core\Content\Deliverer\DelivererDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShopOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_shop_order';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ShopOrderCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShopOrderEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),

            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class))->addFlags(new Required()),
            (new FkField('dewa_deliverer_id', 'delivererId', DelivererDefinition::class)),

            (new FloatField('location_lat', 'locationLat'))->addFlags(new EditField('number')),
            (new FloatField('location_lon', 'locationLon'))->addFlags(new EditField('number')),
            (new FloatField('distance', 'distance'))->addFlags(new EditField('number')),

            (new LongTextField('comment', 'comment'))->addFlags(new EditField('textarea')),
            (new DateTimeField('desired_time', 'desiredTime'))->addFlags(new EditField('datetime-local')),

            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id'))->addFlags(new ApiAware(), new EditField(), new LabelProperty('orderNumber')),
            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('deliverer', 'dewa_deliverer_id', DelivererDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
