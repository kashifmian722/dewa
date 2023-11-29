<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(IngredientTranslationEntity $entity)
 * @method void                           set(string $key, IngredientTranslationEntity $entity)
 * @method IngredientTranslationEntity[]    getIterator()
 * @method IngredientTranslationEntity[]    getElements()
 * @method IngredientTranslationEntity|null get(string $key)
 * @method IngredientTranslationEntity|null first()
 * @method IngredientTranslationEntity|null last()
 */
class IngredientTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_ingredient_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return IngredientTranslationEntity::class;
    }
}
