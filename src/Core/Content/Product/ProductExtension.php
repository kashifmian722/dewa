<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Product;

use Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeProduct\BadgeProductDefinition;
use Appflix\DewaShop\Core\Content\Badge\BadgeDefinition;
use Appflix\DewaShop\Core\Content\Bundle\BundleDefinition;
use Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientProduct\IngredientProductDefinition;
use Appflix\DewaShop\Core\Content\Ingredient\IngredientDefinition;
use Appflix\DewaShop\Core\Content\OptionCategory\OptionCategoryDefinition;
use Appflix\DewaShop\Core\Content\OptionProduct\OptionProductDefinition;
use Appflix\DewaShop\Core\Content\Product\Aggregate\ProductIndex\ProductIndexDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopProduct\ShopProductDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Content\Stock\StockDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'bundles',
                BundleDefinition::class,
                'product_id'
            )
        );

        $collection->add(
            (new OneToManyAssociationField(
                'dewaOptions',
                OptionProductDefinition::class,
                'product_id'
            ))->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'shops',
                ShopDefinition::class,
                ShopProductDefinition::class,
                'product_id',
                'dewa_shop_id'
            ))->addFlags(new Inherited())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'ingredients',
                IngredientDefinition::class,
                IngredientProductDefinition::class,
                'product_id',
                'dewa_ingredient_id'
            ))->addFlags(new Inherited())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'badges',
                BadgeDefinition::class,
                BadgeProductDefinition::class,
                'product_id',
                'dewa_badge_id'
            ))->addFlags(new Inherited())
        );

        $collection->add(
            new OneToManyAssociationField(
                'stocks',
                StockDefinition::class,
                'product_id'
            )
        );

        $collection->add(
            (new OneToOneAssociationField(
                'dewaProductIndex',
                'id',
                'product_id',
                ProductIndexDefinition::class,
                true
            ))->addFlags(new CascadeDelete())
        );
    }
}
