<?php

namespace App\Command;

use App\Helper\ConsoleHelper;
use Isolate\ConsoleServiceProvider\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RebuildDatabaseCommand.
 */
class RebuildDatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:rebuild-database')
            ->setDescription('Rebuild application database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();

        $output->writeln(sprintf('Rebuild for environment <info>%s</info>', $this->getContainer()['env']));

        ConsoleHelper::runCommand($app, 'app:clear-all-caches');
        ConsoleHelper::runCommand($app, 'orm:schema-tool:drop', ['--force' => 'true']);
        ConsoleHelper::runCommand($app, 'orm:schema-tool:create');
        ConsoleHelper::runCommand($app, 'orm:generate-proxies');
        ConsoleHelper::runCommand($app, 'orm:info');

        $output->writeln('Rebuild application <info>success</info>');
    }
}
