<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem;

use Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientOptionItem\IngredientOptionItemDefinition;
use Appflix\DewaShop\Core\Content\Ingredient\IngredientDefinition;
use Appflix\DewaShop\Core\Content\Option\OptionDefinition;
use Appflix\DewaShop\Core\Content\OptionItem\Aggregate\OptionItemTranslation\OptionItemTranslationDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OptionItemDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_option_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OptionItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return OptionItemEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'purchaseUnit' => 1
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('dewa_option_id', 'optionId', OptionDefinition::class))->addFlags(new Required()),

            (new FloatField('price', 'price'))->addFlags(new EditField('number', ['tooltip' => 'optionItemPrice'])),
            (new FloatField('price_factor', 'priceFactor'))->addFlags(new EditField('number', ['tooltip' => 'optionItemPriceFactor'])),
            (new IntField('priority', 'priority'))->addFlags(new EditField('number', ['tooltip' => 'priority'])),
            (new FloatField('purchase_unit', 'purchaseUnit'))->addFlags(new EditField('number', ['tooltip' => 'optionItemPurchaseUnit'])),

            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),

            new TranslationsAssociationField(OptionItemTranslationDefinition::class, 'dewa_option_item_id'),
            (new ManyToOneAssociationField('option', 'dewa_option_id', OptionDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToManyAssociationField('ingredients', IngredientDefinition::class,IngredientOptionItemDefinition::class, 'dewa_option_item_id', 'dewa_ingredient_id'))->addFlags(new EditField(), new LabelProperty('name'))
        ]);
    }
}
