<?php declare(strict_types=1);

namespace Appflix\DewaShop\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1604593154 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1604593154;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_product_index` (
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`product_id`, `product_version_id`),

    CONSTRAINT `fk.dewa_product_index.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_category_icon` (
    `category_id` BINARY(16) NOT NULL,
    `category_version_id` BINARY(16) NOT NULL,
    `icon` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,

    PRIMARY KEY (`category_id`, `category_version_id`),

    CONSTRAINT `fk.dewa_category_icon.category_id` 
        FOREIGN KEY (`category_id`, `category_version_id`)
        REFERENCES `category` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_location` (
    `id` BINARY(16) NOT NULL,
    `payload` JSON NOT NULL,
    `location_lat` FLOAT NULL,
    `location_lon` FLOAT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop` (
    `id` BINARY(16) NOT NULL,
    `category_id` BINARY(16) NULL,
    `media_id` BINARY(16) NULL,
    `dewa_location_id` BINARY(16) NULL,
    `country_id` BINARY(16) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `delivery_active` TINYINT(1) NOT NULL DEFAULT 1,
    `collect_active` TINYINT(1) NOT NULL DEFAULT 1,
    `max_radius` FLOAT NOT NULL DEFAULT 2.5,
    `pickup_active` TINYINT(1) NOT NULL DEFAULT 1,
    `opening_hours` JSON,
    `delivery_hours` JSON,
    `preparation_time` INT(11),
    `delivery_time` INT(11),
    `name` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NULL,
    `email` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NULL,
    `last_name` VARCHAR(255) NULL,
    `street` VARCHAR(255) NULL,
    `street_number` VARCHAR(255) NULL,
    `zipcode` VARCHAR(255) NULL,
    `city` VARCHAR(255) NULL,
    `phone_number` VARCHAR(255) NULL,
    `fax_number` VARCHAR(255) NULL,
    `vat_id` VARCHAR(255) NULL,
    `location_lat` FLOAT NULL,
    `location_lon` FLOAT NULL,
    
    `is_limit` TINYINT NOT NULL DEFAULT 0,
    `in_progress` INT(11),
    `workload` INT(11),
    `limit_rule` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `limit_interval` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `limit_amount` INT(11),
    `limited_at` DATETIME(3),
    
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_shop.category_id` 
        FOREIGN KEY (`category_id`)
        REFERENCES `category` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop.dewa_location_id` 
        FOREIGN KEY (`dewa_location_id`)
        REFERENCES `dewa_location` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop.country_id` 
        FOREIGN KEY (`country_id`)
        REFERENCES `country` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_deliverer` (
    `id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NULL,
    `media_id` BINARY(16) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NULL,
    `tracking_code` VARCHAR(255) NULL,
    `location_lat` FLOAT NULL,
    `location_lon` FLOAT NULL,
    `phone_number` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_deliverer.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_deliverer.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop_order` (
    `id` BINARY(16) NOT NULL,
    `order_id` BINARY(16) NOT NULL,
    `order_version_id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,
    `dewa_deliverer_id` BINARY(16) NULL,
    `dewa_location_id` BINARY(16) NULL,
    `location_lat` FLOAT NULL,
    `location_lon` FLOAT NULL,
    `distance` FLOAT NULL,
    `desired_time` DATETIME(3) NULL,
    `comment` LONGTEXT COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq.dewa_shop_order.id` (`order_id`, `order_version_id`, `dewa_shop_id`),

    KEY `idx.dewa_shop_order.order_id` (`order_id`, `order_version_id`),
    KEY `idx.dewa_shop_order.dewa_shop_id` (`dewa_shop_id`),

    CONSTRAINT `fk.dewa_shop_order.order_id` 
        FOREIGN KEY (`order_id`, `order_version_id`)
        REFERENCES `order` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_order.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_order.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_order.dewa_deliverer_id` 
        FOREIGN KEY (`dewa_deliverer_id`)
        REFERENCES `dewa_deliverer` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_order.dewa_location_id` 
        FOREIGN KEY (`dewa_location_id`)
        REFERENCES `dewa_location` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_printer` (
    `id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NULL,
    `template` TEXT NULL,
    `mac` VARCHAR(255) NOT NULL,
    `last_polled` DATETIME(3) NULL,
    `status` VARCHAR(255) NULL,
    `dot_width` INT(11) NOT NULL DEFAULT 0,
    `printer_type` VARCHAR(255) NULL,
    `printer_version` VARCHAR(255) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_printer.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_printer.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_print_job` (
    `id` BINARY(16) NOT NULL,
    `dewa_printer_id` BINARY(16) NULL,
    `dewa_shop_order_id` BINARY(16) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_print_job.dewa_printer_id` 
        FOREIGN KEY (`dewa_printer_id`)
        REFERENCES `dewa_printer` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_print_job.dewa_shop_order_id` 
        FOREIGN KEY (`dewa_shop_order_id`)
        REFERENCES `dewa_shop_order` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_shop_category` (
    `category_id` BINARY(16) NOT NULL,
    `category_version_id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`category_id`, `category_version_id`, `dewa_shop_id`),

    CONSTRAINT `fk.dewa_shop_category.category_id` 
        FOREIGN KEY (`category_id`, `category_version_id`)
        REFERENCES `category` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_shop_category.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option` (
    `id` BINARY(16) NOT NULL,
    `unit_id` BINARY(16),
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT 'single',
    `ignore_price_factor` TINYINT(1) NULL,
    `min_selection` INT(11) NOT NULL DEFAULT 0,
    `max_selection` INT(11) NOT NULL DEFAULT 5,
    `reference_unit` DECIMAL(10,3) unsigned NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_category` (
    `id` BINARY(16) NOT NULL,
    `category_id` BINARY(16) NOT NULL,
    `category_version_id` BINARY(16) NOT NULL,
    `dewa_option_id` BINARY(16) NOT NULL,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `is_collapsible` TINYINT(1) NOT NULL DEFAULT 0,
    `info` varchar(255),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.dewa_option_category.category_id` 
        FOREIGN KEY (`category_id`, `category_version_id`)
        REFERENCES `category` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_option_category.dewa_option_id` 
        FOREIGN KEY (`dewa_option_id`)
        REFERENCES `dewa_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_category_translation` (
    `dewa_option_category_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`dewa_option_category_id`, `language_id`),

    CONSTRAINT `fk.dewa_option_category_translation.language_id` 
        FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_option_category_translation.dewa_option_category_id` 
        FOREIGN KEY (`dewa_option_category_id`)
        REFERENCES `dewa_option_category` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_product` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `dewa_option_id` BINARY(16) NOT NULL,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `is_collapsible` TINYINT(1) NOT NULL DEFAULT 0,
    `info` varchar(255),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.dewa_option_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_option_product.dewa_option_id` 
        FOREIGN KEY (`dewa_option_id`)
        REFERENCES `dewa_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_product_translation` (
    `dewa_option_product_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`dewa_option_product_id`, `language_id`),

    CONSTRAINT `fk.dewa_option_product_translation.language_id` 
        FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_option_product_translation.dewa_option_product_id` 
        FOREIGN KEY (`dewa_option_product_id`)
        REFERENCES `dewa_option_product` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_item` (
    `id` BINARY(16) NOT NULL,
    `dewa_option_id` BINARY(16) NOT NULL,
    `price` FLOAT NOT NULL DEFAULT 0,
    `price_factor` FLOAT NOT NULL DEFAULT 0,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `quantity` INT(11) NOT NULL DEFAULT 0,
    `purchase_unit` DECIMAL(11,4) unsigned NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_option_item.dewa_option_id` 
        FOREIGN KEY (`dewa_option_id`)
        REFERENCES `dewa_option` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_option_item_translation` (
    `dewa_option_item_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`dewa_option_item_id`, `language_id`),

    CONSTRAINT `fk.dewa_option_item_translation.language_id` 
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_option_item_translation.dewa_option_item_id` 
        FOREIGN KEY (`dewa_option_item_id`) 
        REFERENCES `dewa_option_item` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_ingredient` (
    `id` BINARY(16) NOT NULL,
    `order` INT(11) NOT NULL DEFAULT 0,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_ingredient_translation` (
    `dewa_ingredient_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `description` LONGTEXT COLLATE utf8mb4_unicode_ci NULL,
    `tooltip` LONGTEXT COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`dewa_ingredient_id`, `language_id`),

    CONSTRAINT `fk.dewa_ingredient_translation.language_id` 
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_ingredient_translation.dewa_ingredient_id` 
        FOREIGN KEY (`dewa_ingredient_id`) 
        REFERENCES `dewa_ingredient` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_ingredient_product` (
    `dewa_ingredient_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,

    PRIMARY KEY (`dewa_ingredient_id`, `product_id`, `product_version_id`),

    CONSTRAINT `fk.dewa_ingredient_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_ingredient_product.dewa_ingredient_id` 
        FOREIGN KEY (`dewa_ingredient_id`)
        REFERENCES `dewa_ingredient` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_ingredient_option_item` (
    `dewa_ingredient_id` BINARY(16) NOT NULL,
    `dewa_option_item_id` BINARY(16) NOT NULL,

    PRIMARY KEY (`dewa_ingredient_id`, `dewa_option_item_id`),

    CONSTRAINT `fk.dewa_ingredient_option_item.dewa_ingredient_id` 
        FOREIGN KEY (`dewa_ingredient_id`)
        REFERENCES `dewa_ingredient` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_ingredient_option_item.dewa_option_item_id`
        FOREIGN KEY (`dewa_option_item_id`)
        REFERENCES `dewa_option_item` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_svg_icon` (
    `id` BINARY(16) NOT NULL,
    `content` TEXT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_badge` (
    `id` BINARY(16) NOT NULL,
    `dewa_svg_icon_id` BINARY(16) NULL,
    `icon` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `background_color` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `icon_color` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `filterable` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.dewa_badge.dewa_svg_icon_id` 
        FOREIGN KEY (`dewa_svg_icon_id`)
        REFERENCES `dewa_svg_icon` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_badge_translation` (
    `dewa_badge_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`dewa_badge_id`, `language_id`),

    CONSTRAINT `fk.dewa_badge_translation.language_id` 
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_badge_translation.dewa_badge_id` 
        FOREIGN KEY (`dewa_badge_id`) 
        REFERENCES `dewa_badge` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_badge_product` (
    `dewa_badge_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,

    PRIMARY KEY (`dewa_badge_id`, `product_id`, `product_version_id`),

    CONSTRAINT `fk.dewa_badge_product.dewa_badge_id` 
        FOREIGN KEY (`dewa_badge_id`)
        REFERENCES `dewa_badge` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_badge_product.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `dewa_stock` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `dewa_shop_id` BINARY(16) NOT NULL,
    `is_stock` TINYINT NOT NULL DEFAULT 0,
    `stock` INT(11),
    `restock_rule` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `restock_interval` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `restock_amount` INT(11),
    `restocked_at` DATETIME(3),
    `info` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),
    
    UNIQUE KEY `uniq.dewa_stock.id` (`product_id`, `product_version_id`, `dewa_shop_id`),

    CONSTRAINT `fk.dewa_stock.product_id` 
        FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.dewa_stock.dewa_shop_id` 
        FOREIGN KEY (`dewa_shop_id`)
        REFERENCES `dewa_shop` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        try {
            $this->updateInheritance($connection, 'product', 'badges');
        } catch (\Exception $exception) {}

        try {
            $this->updateInheritance($connection, 'product', 'ingredients');
        } catch (\Exception $exception) {}

        try {
            $this->updateInheritance($connection, 'product', 'stocks');
        } catch (\Exception $exception) {}

        try {
            $this->updateInheritance($connection, 'category', 'options');
        } catch (\Exception $exception) {}

        try {
            $this->updateInheritance($connection, 'order', 'dewa_shop_order_version_id');
        } catch (\Exception $exception) {}
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
