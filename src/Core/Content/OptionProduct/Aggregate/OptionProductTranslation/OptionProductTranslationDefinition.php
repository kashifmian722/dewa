<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionProduct\Aggregate\OptionProductTranslation;

use Appflix\DewaShop\Core\Content\OptionProduct\OptionProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OptionProductTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'dewa_option_product_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return OptionProductTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OptionProductTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return OptionProductDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required())
        ]);
    }
}
