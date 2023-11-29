<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(ShopCategoryEntity $entity)
 * @method void                set(string $key, ShopCategoryEntity $entity)
 * @method ShopCategoryEntity[]    getIterator()
 * @method ShopCategoryEntity[]    getElements()
 * @method ShopCategoryEntity|null get(string $key)
 * @method ShopCategoryEntity|null first()
 * @method ShopCategoryEntity|null last()
 */
class ShopCategoryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_shop_category_collection';
    }

    protected function getExpectedClass(): string
    {
        return ShopCategoryEntity::class;
    }
}
