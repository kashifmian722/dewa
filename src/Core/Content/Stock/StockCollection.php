<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Stock;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(StockEntity $entity)
 * @method void                set(string $key, StockEntity $entity)
 * @method StockEntity[]    getIterator()
 * @method StockEntity[]    getElements()
 * @method StockEntity|null get(string $key)
 * @method StockEntity|null first()
 * @method StockEntity|null last()
 */
class StockCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_stock_collection';
    }

    protected function getExpectedClass(): string
    {
        return StockEntity::class;
    }

    public function hasShopId(string $id): bool
    {
        foreach ($this->getElements() as $entity) {
            if ($entity->getShopId() === $id) {
                return true;
            }
        }
        return false;
    }

    public function getByProductId(string $id): ?StockEntity
    {
        foreach ($this->getElements() as $entity) {
            if ($entity->getProductId() === $id) {
                return $entity;
            }
        }
        return null;
    }

    public function filterModified(): self
    {
        return $this->filter(
            static function (StockEntity $struct) {
                return $struct->isModified();
            }
        );
    }
}
