<?php declare(strict_types=1);

namespace Appflix\DewaShop\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1664243682 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1664243682;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_bundle` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `accessory_product_id` BINARY(16) NULL,
    `accessory_stream_id` BINARY(16) NULL,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_bundle.product_id` 
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_bundle.accessory_product_id` 
        FOREIGN KEY (`accessory_product_id`)
        REFERENCES `product` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_bundle.accessory_stream_id` 
        FOREIGN KEY (`accessory_stream_id`)
        REFERENCES `product_stream` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
