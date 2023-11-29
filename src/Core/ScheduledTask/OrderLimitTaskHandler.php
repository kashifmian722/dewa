<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\ScheduledTask;

use Appflix\DewaShop\Core\Service\OrderLimitService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class OrderLimitTaskHandler extends ScheduledTaskHandler
{
    private OrderLimitService $orderLimitService;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        OrderLimitService $orderLimitService
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->orderLimitService = $orderLimitService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ OrderLimitTask::class ];
    }

    public function run(): void
    {
        $this->orderLimitService->executeTask();
    }
}