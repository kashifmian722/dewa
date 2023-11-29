<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\ScheduledTask;

use Appflix\DewaShop\Core\Service\StockService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class RestockTaskHandler extends ScheduledTaskHandler
{
    private StockService $stockService;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        StockService $stockService
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->stockService = $stockService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ RestockTask::class ];
    }

    public function run(): void
    {
        try {
            $this->stockService->executeRestock();
        } catch (\Exception $exception) {}
    }
}
