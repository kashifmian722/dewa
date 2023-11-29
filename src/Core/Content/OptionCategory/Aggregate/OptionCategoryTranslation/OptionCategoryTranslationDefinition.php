<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionCategory\Aggregate\OptionCategoryTranslation;

use Appflix\DewaShop\Core\Content\OptionCategory\OptionCategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OptionCategoryTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'dewa_option_category_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return OptionCategoryTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OptionCategoryTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return OptionCategoryDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required())
        ]);
    }
}
