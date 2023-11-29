<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionProduct;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(OptionProductEntity $entity)
 * @method void                set(string $key, OptionProductEntity $entity)
 * @method OptionProductEntity[]    getIterator()
 * @method OptionProductEntity[]    getElements()
 * @method OptionProductEntity|null get(string $key)
 * @method OptionProductEntity|null first()
 * @method OptionProductEntity|null last()
 */
class OptionProductCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_product_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionProductEntity::class;
    }
}
