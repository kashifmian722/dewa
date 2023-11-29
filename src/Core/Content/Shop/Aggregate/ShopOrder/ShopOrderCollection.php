<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(ShopOrderEntity $entity)
 * @method void                set(string $key, ShopOrderEntity $entity)
 * @method ShopOrderEntity[]    getIterator()
 * @method ShopOrderEntity[]    getElements()
 * @method ShopOrderEntity|null get(string $key)
 * @method ShopOrderEntity|null first()
 * @method ShopOrderEntity|null last()
 */
class ShopOrderCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_shop_order_collection';
    }

    protected function getExpectedClass(): string
    {
        return ShopOrderEntity::class;
    }
}
