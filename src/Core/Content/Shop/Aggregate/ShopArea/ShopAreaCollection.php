<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopArea;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(ShopAreaEntity $entity)
 * @method void                set(string $key, ShopAreaEntity $entity)
 * @method ShopAreaEntity[]    getIterator()
 * @method ShopAreaEntity[]    getElements()
 * @method ShopAreaEntity|null get(string $key)
 * @method ShopAreaEntity|null first()
 * @method ShopAreaEntity|null last()
 */
class ShopAreaCollection extends EntityCollection
{
    public function getByZipcode(string $zipcode): ?ShopAreaEntity
    {
        foreach ($this->getElements() as $entity) {
            if ($entity->getZipcode() === $zipcode) {
                return $entity;
            }
        }
        return null;
    }

    public function getZipcodes(): array
    {
        $zipcodes = [];
        foreach ($this->getElements() as $entity) {
            $zipcodes[] = $entity->getZipcode();
        }
        return $zipcodes;
    }

    public function getApiAlias(): string
    {
        return 'dewa_shop_area_collection';
    }

    protected function getExpectedClass(): string
    {
        return ShopAreaEntity::class;
    }
}
