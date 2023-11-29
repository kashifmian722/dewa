<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\ScheduledTask;

use Appflix\DewaShop\Core\Service\ProductWeekdayService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductWeekdayTaskHandler extends ScheduledTaskHandler
{
    private ProductWeekdayService $productWeekdayService;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        ProductWeekdayService $productWeekdayService
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->productWeekdayService = $productWeekdayService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ ProductWeekdayTask::class ];
    }

    public function run(): void
    {
        try {
            $this->productWeekdayService->execute();
        } catch (\Exception $exception) {}
    }
}
