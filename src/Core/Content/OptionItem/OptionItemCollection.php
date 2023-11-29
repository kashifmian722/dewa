<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(OptionItemEntity $entity)
 * @method void                set(string $key, OptionItemEntity $entity)
 * @method OptionItemEntity[]    getIterator()
 * @method OptionItemEntity[]    getElements()
 * @method OptionItemEntity|null get(string $key)
 * @method OptionItemEntity|null first()
 * @method OptionItemEntity|null last()
 */
class OptionItemCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_item_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionItemEntity::class;
    }
}
