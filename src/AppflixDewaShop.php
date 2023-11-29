<?php declare(strict_types=1);

namespace Appflix\DewaShop;

use Appflix\DewaShop\Core\Defaults;
use MoorlFoundation\Core\Service\DataService;
use Doctrine\DBAL\Connection;
use MoorlFoundation\MoorlFoundation;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Storefront\Framework\ThemeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppflixDewaShop extends Plugin implements ThemeInterface
{
    public const PLUGIN_TABLES = [
        'dewa_product_index',
        'dewa_category_icon',
        'dewa_location',
        'dewa_shop',
        'dewa_deliverer',
        'dewa_shop_order',
        'dewa_printer',
        'dewa_print_job',
        'dewa_print_turnover',
        'dewa_shop_category',
        'dewa_shop_area',
        'dewa_shop_product',
        'dewa_option',
        'dewa_option_category',
        'dewa_option_category_translation',
        'dewa_option_product',
        'dewa_option_product_translation',
        'dewa_option_item',
        'dewa_option_item_translation',
        'dewa_ingredient',
        'dewa_ingredient_translation',
        'dewa_ingredient_product',
        'dewa_ingredient_option_item',
        'dewa_svg_icon',
        'dewa_badge',
        'dewa_badge_translation',
        'dewa_badge_product',
        'dewa_stock',
        'dewa_bundle',
    ];

    public const SHOPWARE_TABLES = [
        'tag',
        'unit',
        'shipping_method',
        'shipping_method_translation',
        'cms_page',
        'cms_page_translation',
        'cms_section',
        'cms_block',
        'category',
        'category_translation',
        'product',
        'product_translation',
        'product_category',
        'product_visibility',
        'promotion',
        'mail_template_type',
        'mail_template_type_translation',
        'mail_template',
        'mail_template_translation',
        'event_action',
        'theme',
        'theme_sales_channel',
        'sales_channel',
        'acl_role'
    ];

    public function build(ContainerBuilder $container): void
    {
        if (class_exists(MoorlFoundation::class)) {
            parent::build($container);
        }
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        /* @var $dataService DataService */
        $dataService = $this->container->get(DataService::class);
        $dataService->install(Defaults::NAME);

        /* Migration to MoorlFoundation */
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-a' WHERE `slot` = 'one' AND `type` = 'appflix-search-hero';");
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-a' WHERE `slot` = 'one' AND `type` = 'appflix-usp';");
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-b' WHERE `slot` = 'two' AND `type` = 'appflix-usp';");
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-c' WHERE `slot` = 'three' AND `type` = 'appflix-usp';");
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-a' WHERE `slot` = 'one' AND `type` = 'appflix-newsletter';");
        $connection->executeStatement("UPDATE `cms_slot` SET `slot` = 'slot-b' WHERE `slot` = 'two' AND `type` = 'appflix-newsletter';");
        $connection->executeStatement("UPDATE `cms_slot` SET `type` = REPLACE(`type`, 'appflix', 'moorl');");
        $connection->executeStatement("UPDATE `cms_block` SET `custom_fields` = REPLACE(`custom_fields`, 'appflix', 'moorl');");
        $connection->executeStatement("UPDATE `cms_block` SET `type` = REPLACE(`type`, 'appflix-usp', 'appflix-column-layout-1-1-1');");
        $connection->executeStatement("UPDATE `cms_block` SET `type` = REPLACE(`type`, 'appflix-newsletter', 'appflix-column-layout-1-1');");
        $connection->executeStatement("UPDATE `cms_block` SET `type` = REPLACE(`type`, 'appflix-search-hero', 'appflix-column-layout-1');");
        $connection->executeStatement("UPDATE `cms_block` SET `type` = REPLACE(`type`, 'appflix', 'moorl');");
        $connection->executeStatement("UPDATE `cms_section` SET `custom_fields` = REPLACE(`custom_fields`, 'appflix_section', 'moorl_section_config');");
        $connection->executeStatement("UPDATE `cms_section` SET `custom_fields` = REPLACE(`custom_fields`, 'appflix', 'moorl');");
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->uninstallTrait();
    }

    private function uninstallTrait(): void
    {
        $connection = $this->container->get(Connection::class);

        foreach (self::PLUGIN_TABLES as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }

        foreach (array_reverse(self::SHOPWARE_TABLES) as $table) {
            $sql = sprintf("SET FOREIGN_KEY_CHECKS=0; DELETE FROM `%s` WHERE `created_at` = '%s';", $table, Defaults::DATA_CREATED_AT);

            try {
                $connection->executeStatement($sql);
            } catch (\Exception $exception) {
                continue;
            }
        }
    }
}
