<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Bundle;

use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BundleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_bundle';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return BundleCollection::class;
    }

    public function getEntityClass(): string
    {
        return BundleEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'priority' => 0
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new FkField('accessory_product_id', 'accessoryProductId', ProductDefinition::class))->addFlags(),
            (new FkField('accessory_stream_id', 'accessoryStreamId', ProductStreamDefinition::class))->addFlags(),
            (new IntField('priority', 'priority'))->addFlags(new EditField('number')),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('accessoryProduct', 'accessory_product_id', ProductDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('accessoryStream', 'accessory_stream_id', ProductStreamDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
