<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Bundle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(BundleEntity $entity)
 * @method void                set(string $key, BundleEntity $entity)
 * @method BundleEntity[]    getIterator()
 * @method BundleEntity[]    getElements()
 * @method BundleEntity|null get(string $key)
 * @method BundleEntity|null first()
 * @method BundleEntity|null last()
 */
class BundleCollection extends EntityCollection
{
    public function getAccessoryProductIds(): array
    {
        return array_values($this->fmap(static function (BundleEntity $entity) {
            return $entity->getAccessoryProductId();
        }));
    }

    public function getAccessoryStreamIds(): array
    {
        return array_values($this->fmap(static function (BundleEntity $entity) {
            return $entity->getAccessoryStreamId();
        }));
    }

    public function getApiAlias(): string
    {
        return 'dewa_bundle_collection';
    }

    protected function getExpectedClass(): string
    {
        return BundleEntity::class;
    }
}
