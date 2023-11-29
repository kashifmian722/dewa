<?php declare(strict_types=1);

namespace Appflix\DewaShop\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1665287389 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1665287389;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `dewa_shop`
ADD `is_open` TINYINT(1) NOT NULL DEFAULT 0;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
