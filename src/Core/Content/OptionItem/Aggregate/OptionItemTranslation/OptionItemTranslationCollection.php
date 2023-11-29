<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem\Aggregate\OptionItemTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(OptionItemTranslationEntity $entity)
 * @method void                           set(string $key, OptionItemTranslationEntity $entity)
 * @method OptionItemTranslationEntity[]    getIterator()
 * @method OptionItemTranslationEntity[]    getElements()
 * @method OptionItemTranslationEntity|null get(string $key)
 * @method OptionItemTranslationEntity|null first()
 * @method OptionItemTranslationEntity|null last()
 */
class OptionItemTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_option_item_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return OptionItemTranslationEntity::class;
    }
}
