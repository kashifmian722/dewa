<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class Textarea extends Flag
{
    public function parse(): \Generator
    {
        yield 'dewa_textarea' => true;
    }
}
