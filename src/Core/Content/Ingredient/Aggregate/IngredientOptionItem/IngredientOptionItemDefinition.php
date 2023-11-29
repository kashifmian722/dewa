<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientOptionItem;

use Appflix\DewaShop\Core\Content\Ingredient\IngredientDefinition;
use Appflix\DewaShop\Core\Content\OptionItem\OptionItemDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class IngredientOptionItemDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'dewa_ingredient_option_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('dewa_ingredient_id', 'ingredientId', IngredientDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('dewa_option_item_id', 'optionItemId', OptionItemDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ManyToOneAssociationField('optionItem', 'dewa_option_item_id', OptionItemDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('ingredient', 'dewa_ingredient_id', IngredientDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
