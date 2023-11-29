<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\SvgIcon;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(SvgIconEntity $entity)
 * @method void                set(string $key, SvgIconEntity $entity)
 * @method SvgIconEntity[]    getIterator()
 * @method SvgIconEntity[]    getElements()
 * @method SvgIconEntity|null get(string $key)
 * @method SvgIconEntity|null first()
 * @method SvgIconEntity|null last()
 */
class SvgIconCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_svg_icon_collection';
    }

    protected function getExpectedClass(): string
    {
        return SvgIconEntity::class;
    }
}
