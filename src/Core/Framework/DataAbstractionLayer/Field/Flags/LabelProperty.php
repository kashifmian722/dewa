<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class LabelProperty extends Flag
{
    private ?string $labelProperty;

    public function __construct(?string $labelProperty)
    {
        $this->labelProperty = $labelProperty;
    }

    public function parse(): \Generator
    {
        yield 'dewa_label_property' => $this->labelProperty;
    }
}
