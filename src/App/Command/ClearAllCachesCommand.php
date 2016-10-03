<?php

namespace App\Command;

use App\Helper\ConsoleHelper;
use Isolate\ConsoleServiceProvider\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearAllCachesCommand
 */
class ClearAllCachesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:clear-all-caches')
            ->setDescription('Clear all caches');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();

        ConsoleHelper::runCommand($app, 'orm:clear-cache:metadata');
        ConsoleHelper::runCommand($app, 'orm:clear-cache:query');
        ConsoleHelper::runCommand($app, 'orm:clear-cache:result');

        $output->writeln('Clear all caches <info>success</info>');
    }
}
