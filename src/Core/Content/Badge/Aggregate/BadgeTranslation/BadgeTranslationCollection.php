<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(BadgeTranslationEntity $entity)
 * @method void                           set(string $key, BadgeTranslationEntity $entity)
 * @method BadgeTranslationEntity[]    getIterator()
 * @method BadgeTranslationEntity[]    getElements()
 * @method BadgeTranslationEntity|null get(string $key)
 * @method BadgeTranslationEntity|null first()
 * @method BadgeTranslationEntity|null last()
 */
class BadgeTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_badge_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return BadgeTranslationEntity::class;
    }
}
