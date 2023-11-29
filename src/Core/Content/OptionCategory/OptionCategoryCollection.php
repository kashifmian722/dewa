<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(OptionCategoryEntity $entity)
 * @method void                set(string $key, OptionCategoryEntity $entity)
 * @method OptionCategoryEntity[]    getIterator()
 * @method OptionCategoryEntity[]    getElements()
 * @method OptionCategoryEntity|null get(string $key)
 * @method OptionCategoryEntity|null first()
 * @method OptionCategoryEntity|null last()
 */
class OptionCategoryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_category_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionCategoryEntity::class;
    }
}
