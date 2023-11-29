<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class GridColumn extends Flag
{
    public function parse(): \Generator
    {
        yield 'dewa_grid_column' => true;
    }
}
