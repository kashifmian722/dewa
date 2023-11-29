<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient;

use Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientOptionItem\IngredientOptionItemDefinition;
use Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientProduct\IngredientProductDefinition;
use Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientTranslation\IngredientTranslationDefinition;
use Appflix\DewaShop\Core\Content\OptionItem\OptionItemDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IngredientDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_ingredient';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return IngredientCollection::class;
    }

    public function getEntityClass(): string
    {
        return IngredientEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new IntField('order', 'order'))->addFlags(new EditField('number')),
            (new IntField('priority', 'priority'))->addFlags(new EditField('number')),

            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new TranslatedField('description'))->addFlags(new Required(), new EditField('textarea')),

            new TranslationsAssociationField(IngredientTranslationDefinition::class, 'dewa_ingredient_id'),
            new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                IngredientProductDefinition::class,
                'dewa_ingredient_id',
                'product_id'
            ),
            (new ManyToManyAssociationField(
                'optionItems',
                OptionItemDefinition::class,
                IngredientOptionItemDefinition::class,
                'dewa_ingredient_id',
                'dewa_option_item_id')
            )->addFlags(new CascadeDelete()),
        ]);
    }
}
