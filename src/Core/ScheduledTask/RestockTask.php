<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class RestockTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'dewa_shop.restock';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }
}