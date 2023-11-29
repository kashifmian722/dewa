<?php declare(strict_types=1);

namespace Appflix\DewaShop\Demo\Base;

use MoorlFoundation\Core\System\DataInterface;
use Appflix\DewaShop\Core\System\DewaShopDataExtension;

class DemoBase extends DewaShopDataExtension implements DataInterface
{
    public function getName(): string
    {
        return 'base';
    }

    public function getType(): string
    {
        return 'demo';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function getInstallConfig(): array {
        return [
            "AppflixDewaShop.config.registrationFormDewa" => false,
            "AppflixDewaShop.config.checkoutTimepicker" => 'dropdownMinutes',
            "AppflixDewaShop.config.checkoutDropdownSteps" => '30,45,60,90,120,180,240,300,450,600,750,900'
        ];
    }
}
