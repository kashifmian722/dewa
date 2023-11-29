<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopProduct;

use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class ShopProductDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'dewa_shop_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class),
            new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class)
        ]);
    }
}
