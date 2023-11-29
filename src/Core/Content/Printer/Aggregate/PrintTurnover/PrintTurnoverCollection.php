<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(PrintTurnoverEntity $entity)
 * @method void                set(string $key, PrintTurnoverEntity $entity)
 * @method PrintTurnoverEntity[]    getIterator()
 * @method PrintTurnoverEntity[]    getElements()
 * @method PrintTurnoverEntity|null get(string $key)
 * @method PrintTurnoverEntity|null first()
 * @method PrintTurnoverEntity|null last()
 */
class PrintTurnoverCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_print_turnover_collection';
    }

    protected function getExpectedClass(): string
    {
        return PrintTurnoverEntity::class;
    }
}
