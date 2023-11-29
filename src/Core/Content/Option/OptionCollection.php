<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Option;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(OptionEntity $entity)
 * @method void                set(string $key, OptionEntity $entity)
 * @method OptionEntity[]    getIterator()
 * @method OptionEntity[]    getElements()
 * @method OptionEntity|null get(string $key)
 * @method OptionEntity|null first()
 * @method OptionEntity|null last()
 */
class OptionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionEntity::class;
    }
}
