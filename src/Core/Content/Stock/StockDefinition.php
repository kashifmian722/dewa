<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Stock;

use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class StockDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_stock';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return StockCollection::class;
    }

    public function getEntityClass(): string
    {
        return StockEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'restockedAt' => (new \DateTimeImmutable())->format(DATE_ATOM)
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),

            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),

            (new BoolField('is_stock', 'isStock'))->addFlags(new EditField('switch', ['tooltip'=>'isStock'])),
            (new StringField('info', 'info'))->addFlags(new EditField('text', ['tooltip'=>'internalUse'])),
            (new IntField('stock', 'stock'))->addFlags(new EditField('number')),
            (new StringField('restock_rule', 'restockRule'))->addFlags(new EditField('select', ['options' => [
                'disabled',
                'add',
                'reset'
            ]])),
            (new StringField('restock_interval', 'restockInterval'))->addFlags(new EditField('select', ['options' => array_keys(Defaults::getRestockIntervals())])),
            (new DateTimeField('restocked_at', 'restockedAt'))->addFlags(new EditField('datetime-local')),
            (new IntField('restock_amount', 'restockAmount'))->addFlags(new EditField('number'))
        ]);
    }
}
