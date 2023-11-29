<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionCategory\Aggregate\OptionCategoryTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(OptionCategoryTranslationEntity $entity)
 * @method void                           set(string $key, OptionCategoryTranslationEntity $entity)
 * @method OptionCategoryTranslationEntity[]    getIterator()
 * @method OptionCategoryTranslationEntity[]    getElements()
 * @method OptionCategoryTranslationEntity|null get(string $key)
 * @method OptionCategoryTranslationEntity|null first()
 * @method OptionCategoryTranslationEntity|null last()
 */
class OptionCategoryTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_category_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionCategoryTranslationEntity::class;
    }
}
