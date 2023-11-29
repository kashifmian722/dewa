<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Command;

use Appflix\DewaShop\Core\ScheduledTask\RestockTaskHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RestockCommand extends Command
{
    protected static $defaultName = 'appflix:dewa-shop:restock';

    /**
     * @var RestockTaskHandler
     */
    private $taskHandler;

    public function __construct(RestockTaskHandler $taskHandler)
    {
        parent::__construct('appflix-dewa-shop-restock');

        $this->taskHandler = $taskHandler;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start RestockCommand');

        $this->taskHandler->run();

        $output->writeln('Finish RestockCommand');

        return 1;
    }
}
