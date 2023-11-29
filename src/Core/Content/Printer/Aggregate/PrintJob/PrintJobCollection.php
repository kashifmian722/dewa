<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintJob;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(PrintJobEntity $entity)
 * @method void                set(string $key, PrintJobEntity $entity)
 * @method PrintJobEntity[]    getIterator()
 * @method PrintJobEntity[]    getElements()
 * @method PrintJobEntity|null get(string $key)
 * @method PrintJobEntity|null first()
 * @method PrintJobEntity|null last()
 */
class PrintJobCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dewa_print_job_collection';
    }

    protected function getExpectedClass(): string
    {
        return PrintJobEntity::class;
    }
}
