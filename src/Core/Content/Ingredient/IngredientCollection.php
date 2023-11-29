<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IngredientEntity $entity)
 * @method void                set(string $key, IngredientEntity $entity)
 * @method IngredientEntity[]    getIterator()
 * @method IngredientEntity[]    getElements()
 * @method IngredientEntity|null get(string $key)
 * @method IngredientEntity|null first()
 * @method IngredientEntity|null last()
 */
class IngredientCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_ingredient_collection';
    }

    protected function getExpectedClass(): string
    {
        return IngredientEntity::class;
    }
}
