<?php declare(strict_types=1);

namespace Appflix\DewaShop\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1662144866 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1662144866;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_print_turnover` (
    `id` BINARY(16) NOT NULL,
    `dewa_printer_id` BINARY(16) NULL,
    `dewa_shop_id` BINARY(16) NULL,
    `interval` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `key` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `data` JSON NULL,
    `is_printing` tinyint(4) NOT NULL DEFAULT '0',
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_print_turnover.dewa_printer_id` 
        FOREIGN KEY (`dewa_printer_id`)
        REFERENCES `dewa_printer` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_print_turnover.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
ALTER TABLE `dewa_print_job`
ADD `is_printing` tinyint(4) NOT NULL DEFAULT '0';
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
