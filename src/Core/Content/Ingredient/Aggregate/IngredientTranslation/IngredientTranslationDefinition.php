<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientTranslation;

use Appflix\DewaShop\Core\Content\Ingredient\IngredientDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IngredientTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'dewa_ingredient_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return IngredientTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return IngredientTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return IngredientDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new LongTextField('description', 'description'))->addFlags(new Required(), new AllowHtml())
        ]);
    }
}
