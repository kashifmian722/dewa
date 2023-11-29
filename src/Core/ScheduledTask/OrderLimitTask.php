<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class OrderLimitTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'dewa_shop.order_limit';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }
}