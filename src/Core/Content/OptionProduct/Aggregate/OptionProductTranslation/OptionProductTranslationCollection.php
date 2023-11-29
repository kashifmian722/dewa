<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionProduct\Aggregate\OptionProductTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(OptionProductTranslationEntity $entity)
 * @method void                           set(string $key, OptionProductTranslationEntity $entity)
 * @method OptionProductTranslationEntity[]    getIterator()
 * @method OptionProductTranslationEntity[]    getElements()
 * @method OptionProductTranslationEntity|null get(string $key)
 * @method OptionProductTranslationEntity|null first()
 * @method OptionProductTranslationEntity|null last()
 */
class OptionProductTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_product_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionProductTranslationEntity::class;
    }
}
