<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(PrinterEntity $entity)
 * @method void                set(string $key, PrinterEntity $entity)
 * @method PrinterEntity[]    getIterator()
 * @method PrinterEntity[]    getElements()
 * @method PrinterEntity|null get(string $key)
 * @method PrinterEntity|null first()
 * @method PrinterEntity|null last()
 */
class PrinterCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_printer_collection';
    }

    protected function getExpectedClass(): string
    {
        return PrinterEntity::class;
    }
}
