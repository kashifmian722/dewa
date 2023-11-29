<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem\Aggregate\OptionItemTranslation;

use Appflix\DewaShop\Core\Content\OptionItem\OptionItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OptionItemTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'dewa_option_item_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return OptionItemTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OptionItemTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return OptionItemDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required())
        ]);
    }
}
