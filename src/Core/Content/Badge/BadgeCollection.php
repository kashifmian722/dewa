<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Badge;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(BadgeEntity $entity)
 * @method void                set(string $key, BadgeEntity $entity)
 * @method BadgeEntity[]    getIterator()
 * @method BadgeEntity[]    getElements()
 * @method BadgeEntity|null get(string $key)
 * @method BadgeEntity|null first()
 * @method BadgeEntity|null last()
 */
class BadgeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_badge_collection';
    }

    protected function getExpectedClass(): string
    {
        return BadgeEntity::class;
    }
}
