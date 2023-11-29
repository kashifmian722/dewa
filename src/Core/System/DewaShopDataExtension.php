<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\System;

use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\AppflixDewaShop;
use Appflix\DewaShop\Core\Event\ShopOrderMailEvent;
use Appflix\DewaShop\Core\Event\TableRegistrationMailEvent;
use MoorlFoundation\Core\System\DataExtension;
use Shopware\Core\Content\MailTemplate\MailTemplateActions;

class DewaShopDataExtension extends DataExtension
{
    private ?array $globalReplacers = null;

    public function getTables(): ?array
    {
        return array_merge(
            $this->getShopwareTables(),
            $this->getPluginTables()
        );
    }

    public function getShopwareTables(): ?array
    {
        return AppflixDewaShop::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return AppflixDewaShop::PLUGIN_TABLES;
    }

    /**
     * @return array|null
     */
    public function getGlobalReplacers(): ?array
    {
        return $this->globalReplacers;
    }

    /**
     * @param array|null $globalReplacers
     */
    public function setGlobalReplacers(?array $globalReplacers): void
    {
        $this->globalReplacers = $globalReplacers;
    }

    public function getPluginName(): string
    {
        return Defaults::NAME;
    }

    public function getCreatedAt(): string
    {
        return Defaults::DATA_CREATED_AT;
    }

    public function getLocalReplacers(): array
    {
        return [
            '{CMS_PAGE_ID}' => Defaults::CMS_PAGE_ID,
            '{SHIPPING_METHOD_COLLECT_ID}' => Defaults::SHIPPING_METHOD_COLLECT_ID,
            '{SHIPPING_METHOD_DELIVERY_ID}' => Defaults::SHIPPING_METHOD_DELIVERY_ID,
            '{MAIL_TEMPLATE_MAIL_SEND_ACTION}' => MailTemplateActions::MAIL_TEMPLATE_MAIL_SEND_ACTION,
            '{SHOP_ORDER_MAIL_EVENT}' => ShopOrderMailEvent::EVENT_NAME,
            '{TABLE_REGISTRATION_MAIL_EVENT}' => TableRegistrationMailEvent::EVENT_NAME,
        ];
    }

    public function process(): void {}

    public function getRemoveQueries(): array
    {
        return [
            "UPDATE `category` SET `cms_page_id` = NULL WHERE `cms_page_id` = UNHEX('{ID:CMS_PAGE_HOME}');",
            "UPDATE `category` SET `cms_page_id` = NULL WHERE `cms_page_id` = UNHEX('{ID:CMS_PAGE_ONLINESHOP}');",
            "DELETE FROM `cms_page` WHERE `id` = UNHEX('{ID:CMS_PAGE_HOME}');",
            "DELETE FROM `cms_page` WHERE `id` = UNHEX('{ID:CMS_PAGE_ONLINESHOP}');",
            "UPDATE `sales_channel` SET `footer_category_id` = NULL, `footer_category_version_id` = NULL, `service_category_id` = NULL, `service_category_version_id` = NULL WHERE `id` = UNHEX('{SALES_CHANNEL_ID}');",
        ];
    }

    public function getInstallQueries(): array
    {
        return [];
    }

    public function getInstallConfig(): array {
        return [];
    }

    public function getStylesheets(): array
    {
        return [];
    }
}
