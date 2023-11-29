<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Command;

use Appflix\DewaShop\Core\ScheduledTask\ProductWeekdayTaskHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProductWeekdayCommand extends Command
{
    protected static $defaultName = 'appflix:dewa-shop:product-weekday';

    /**
     * @var ProductWeekdayTaskHandler
     */
    private $taskHandler;

    public function __construct(ProductWeekdayTaskHandler $taskHandler)
    {
        parent::__construct('appflix-dewa-shop-product-weekday');

        $this->taskHandler = $taskHandler;
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start ProductWeekdayCommand');

        $this->taskHandler->run();

        $output->writeln('Finish ProductWeekdayCommand');

        return 1;
    }
}
