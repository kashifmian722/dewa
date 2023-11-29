<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopArea;

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
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShopAreaDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_shop_area';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ShopAreaCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShopAreaEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'deliveryPrice' => 2,
            'minOrderValue' => 20,
            'deliveryTime' => 30,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class))->addFlags(new Required()),

            (new StringField('zipcode', 'zipcode'))->addFlags(new EditField('text'), new Required()),
            (new FloatField('delivery_price', 'deliveryPrice'))->addFlags(new EditField('number'), new Required()),
            (new FloatField('min_order_value', 'minOrderValue'))->addFlags(new EditField('number'), new Required()),
            (new IntField('delivery_time', 'deliveryTime'))->addFlags(new EditField('number'), new Required()),

            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
