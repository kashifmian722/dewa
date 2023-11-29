<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop;

use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(ShopEntity $entity)
 * @method void                set(string $key, ShopEntity $entity)
 * @method ShopEntity[]    getIterator()
 * @method ShopEntity[]    getElements()
 * @method ShopEntity|null get(string $key)
 * @method ShopEntity|null first()
 * @method ShopEntity|null last()
 */
class ShopCollection extends EntityCollection
{
    public function addDistances(?GeoPoint $geoPoint): void
    {
        if (!$geoPoint) {
            return;
        }

        foreach ($this as $entity) {
            $entity->setDistance($geoPoint);
        }
    }

    public function getApiAlias(): string
    {
        return 'dewa_shop_collection';
    }

    protected function getExpectedClass(): string
    {
        return ShopEntity::class;
    }

    public function sortByDistance(): self
    {
        $this->sort(function (ShopEntity $a, ShopEntity $b) {
            return $a->getDistance() > $b->getDistance();
        });

        return $this;
    }
}
