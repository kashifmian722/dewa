<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Deliverer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(DelivererEntity $entity)
 * @method void                set(string $key, DelivererEntity $entity)
 * @method DelivererEntity[]    getIterator()
 * @method DelivererEntity[]    getElements()
 * @method DelivererEntity|null get(string $key)
 * @method DelivererEntity|null first()
 * @method DelivererEntity|null last()
 */
class DelivererCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_deliverer_collection';
    }

    protected function getExpectedClass(): string
    {
        return DelivererEntity::class;
    }
}
