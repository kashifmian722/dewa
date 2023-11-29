<?php declare(strict_types=1);

namespace Appflix\DewaShop\Demo\Usergroups;

use MoorlFoundation\Core\System\DataInterface;
use Appflix\DewaShop\Core\System\DewaShopDataExtension;

class DemoUsergroups extends DewaShopDataExtension implements DataInterface
{
    public function getName(): string
    {
        return 'usergroups';
    }

    public function getType(): string
    {
        return 'demo';
    }

    public function getPath(): string
    {
        return __DIR__;
    }
    public function getRemoveQueries(): array
    {
        return [];
    }

    public function isCleanUp(): bool
    {
        return false;
    }
}
