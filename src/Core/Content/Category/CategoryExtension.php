<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Category;

use Appflix\DewaShop\Core\Content\Option\OptionDefinition;
use Appflix\DewaShop\Core\Content\OptionCategory\OptionCategoryDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CategoryExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CategoryDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'options',
                OptionCategoryDefinition::class,
                'category_id'
            ))->addFlags(new CascadeDelete())
        );
    }
}
