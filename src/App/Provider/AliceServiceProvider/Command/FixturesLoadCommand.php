<?php

namespace App\Provider\AliceServiceProvider\Command;

use Isolate\ConsoleServiceProvider\Console\Command\Command;
use Nelmio\Alice\Fixtures;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FixturesLoadCommand.
 */
class FixturesLoadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('alice:fixtures-load')
            ->setDescription('Loads data fixtures to your database.')
            ->setDefinition(array(
                new InputArgument(
                    'config-path', InputArgument::OPTIONAL,
                    'Override the path of fixtures root file to config fixtures.'
                ),
            ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $configPath = $input->getArgument('config-path');

        $configPath = $configPath ?? $container['alice.config_path'];

        $output->writeln(sprintf('Starting fixtures load form <info>%s</info>', $configPath));

        if ($configPath !== null && !file_exists($configPath)) {
            throw new \InvalidArgumentException(
                sprintf('Fixtures source root file "<info>%s</info>" does not exist.', $configPath)
            );
        }

        /** @var Fixtures $aliceFixtures */
        $aliceFixtures = $container['alice.fixtures'];

        $aliceFixtures->loadFiles($configPath);

        $output->writeln('Fixtures load <info>success </info>');
    }
}
