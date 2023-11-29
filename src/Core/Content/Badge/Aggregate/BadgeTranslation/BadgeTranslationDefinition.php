<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeTranslation;

use Appflix\DewaShop\Core\Content\Badge\BadgeDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BadgeTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'dewa_badge_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return BadgeTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return BadgeTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return BadgeDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required())
        ]);
    }
}
