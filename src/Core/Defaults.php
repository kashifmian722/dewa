<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core;

final class Defaults
{
    public const NAME = 'AppflixDewaShop';
    public const ACTIVE_STORE_COOKIE_NAME = 'AppflixDewaShopActiveStore';

    /**
     * This value is used as an identifier to remove Plugin Data from Shopware Database
     */
    public const DATA_CREATED_AT = '2001-02-03 01:02:03.000';

    public const CMS_PAGE = 'dewa_menu';
    public const CMS_PAGE_ID = '86f00acf43987ec2b568a7a757dbf19e';

    public const LINE_ITEM = 'dewa_product';

    public const SHIPPING_METHOD_COLLECT = 'dewa_shipping_collect';
    public const SHIPPING_METHOD_COLLECT_ID = '78d2aa20af38597657a4b48cf02f7dcb';

    public const SHIPPING_METHOD_DELIVERY = 'dewa_shipping_delivery';
    public const SHIPPING_METHOD_DELIVERY_ID = '38aeeba4f2a1432c6b45fd0b45ca0c61';

    public const SHOP_RADIUS = '1000';

    public const OSM_URL_TEMPLATE = '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    public const OSM_ATTRIBUTION = 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>';

    public const STAR_CLOUDPRNT_LINUX_X64 = 'https://www.star-m.jp/products/s_print/CloudPRNTSDK/cputil/cputil-linux-x64_v111.tar.gz';

    public static function getOpeningHours(array $excludeDays = []): array
    {
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $data = [];

        foreach ($days as $day) {
            $data[] = [
                'day' => $day,
                'info' => null,
                'times' => in_array($day, $excludeDays) ? [] : self::getTimes()
            ];
        }

        return $data;
    }

    public static function getTimes(): array
    {
        return [
            ['from' => '09:00', 'until' => '12:00'],
            ['from' => '13:30', 'until' => '22:00']
        ];
    }

    public static function getRestockIntervals(): array
    {
        return [
            'oneMinute' => ['modify' => '-1 minute'],
            'halfHour' => ['modify' => '-30 minute'],
            'oneHour' => ['modify' => '-1 hour'],
            'halfDay' => ['modify' => '-12 hour'],
            'oneDay' => ['modify' => '-1 day'],
            'oneWeek' => ['modify' => '-1 week'],
            'oneMonth' => ['modify' => '-1 month'],
            'oneYear' => ['modify' => '-1 year'],
        ];
    }
}
