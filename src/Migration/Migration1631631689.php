<?php declare(strict_types=1);

namespace Appflix\DewaShop\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1631631689 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1631631689;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `dewa_svg_icon`
ADD `file_name` varchar(255) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `content`,
ADD `locked` tinyint(1) NULL AFTER `file_name`;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
ALTER TABLE `dewa_shop`
ADD `time_zone` varchar(255) NOT NULL DEFAULT 'Europe/Berlin' AFTER `delivery_time`,
ADD `delivery_type` varchar(255) NOT NULL DEFAULT 'radius' AFTER `delivery_time`,
ADD `delivery_price` FLOAT NOT NULL DEFAULT 2 AFTER `delivery_time`,
ADD `min_order_value` FLOAT NOT NULL DEFAULT 20 AFTER `delivery_time`,
ADD `tax_number` varchar(255) AFTER `delivery_time`,
ADD `tax_office` varchar(255) AFTER `delivery_time`,
ADD `bank_name` varchar(255) AFTER `delivery_time`,
ADD `bank_iban` varchar(255) AFTER `delivery_time`,
ADD `bank_bic` varchar(255) AFTER `delivery_time`,
ADD `place_of_jurisdiction` varchar(255) AFTER `delivery_time`,
ADD `place_of_fulfillment` varchar(255) AFTER `delivery_time`,
ADD `executive_director` varchar(255) AFTER `delivery_time`,
ADD `sales_channel_id` binary(16) NULL AFTER `id`;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop_product` (
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`product_id`, `product_version_id`, `dewa_shop_id`),

    CONSTRAINT `fk.dewa_shop_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_product.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop_sales_channel` (
    `sales_channel_id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`sales_channel_id`, `dewa_shop_id`),

    CONSTRAINT `fk.dewa_shop_sales_channel.sales_channel_id` 
        FOREIGN KEY (`sales_channel_id`)
        REFERENCES `sales_channel` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_sales_channel.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop_area` (
    `id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,
    `zipcode` VARCHAR(255) NULL,
    `delivery_price` FLOAT NOT NULL DEFAULT 2,
    `min_order_value` FLOAT NOT NULL DEFAULT 20,
    `delivery_time` INT(11),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.dewa_shop_area.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
DROP TABLE `dewa_product_index`;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_product_index` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `weekday_config` JSON NULL,
    `product_configurator` TINYINT(1) NOT NULL DEFAULT 0,
    `product_ingredient` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` datetime(3) NOT NULL,
    `updated_at` datetime(3) NULL,

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.dewa_product_index.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        try {
            $this->updateInheritance($connection, 'product', 'dewaProductIndex');
        } catch (\Exception $exception) {}

        try {
            $this->updateInheritance($connection, 'product', 'shops');
        } catch (\Exception $exception) {}
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
