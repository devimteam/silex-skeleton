<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\Export\Driver\AnnotationExporter;
use Isolate\ConsoleServiceProvider\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportMappingDoctrineCommand
 */
class ImportMappingDoctrineCommand extends Command
{
    protected function configure()
    {
        $filterDescription = 'A string pattern used to match entities that should be mapped.';
        $this
            ->setName('doctrine:mapping:import')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, $filterDescription)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force to overwrite existing mapping files.')
            ->setDescription('Imports mapping information from an existing database')
            ->setHelp(<<<EOT
The <info>doctrine:mapping:import</info> command imports mapping information
from an existing database:
<info>php app/console doctrine:mapping:import</info>
If you don't want to map every entity that can be found in the database, use the
<info>--filter</info> option. It will try to match the targeted mapped entity with the
provided pattern string.
<info>php app/console doctrine:mapping:import --filter=MyMatchedEntity</info>
Use the <info>--force</info> option, if you want to override existing mapping files:
<info>php app/console doctrine:mapping:import --force</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container['orm.em'];

        $srcPath = $container['paths.root'] . '/src';

        $destPath = $srcPath . DIRECTORY_SEPARATOR . preg_replace("#\\\#", DIRECTORY_SEPARATOR,
                $this->getEntityNamespace());

        $type = 'annotation';

        $cme = new ClassMetadataExporter();

        /** @var AnnotationExporter $exporter */
        $exporter = $cme->getExporter($type);
        $exporter->setOverwriteExistingFiles($input->getOption('force'));
        $exporter->setEntityGenerator($this->getEntityGenerator());

        $databaseDriver = new DatabaseDriver($em->getConnection()->getSchemaManager());

        $em->getConfiguration()->setMetadataDriverImpl($databaseDriver);

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);

        $metadata = $cmf->getAllMetadata();

        $metadata = MetadataFilter::filter($metadata, $input->getOption('filter'));

        if ($metadata) {
            $output->writeln(sprintf('Importing mapping information from "<info>%s</info>" entity manager', 'default'));

            foreach ($metadata as $class) {
                $className = $class->name;
                $class->name = sprintf('%s\\%s', $this->getEntityNamespace(), $className);
                $path = $destPath . DIRECTORY_SEPARATOR . str_replace('\\', '.', $className) . '.php';
                $output->writeln(sprintf('  > writing <comment>%s</comment>', $path));
                $code = $exporter->exportClassMetadata($class);
                if (!is_dir($dir = dirname($path))) {
                    mkdir($dir, 0775, true);
                }
                file_put_contents($path, $code);
                chmod($path, 0664);
            }

            return 0;
        } else {
            $output->writeln('Database does not have any mapping information.', 'ERROR');
            $output->writeln('', 'ERROR');

            return 1;
        }
    }

    private function getEntityNamespace()
    {
        return 'App\\Entity';
    }

    private function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setFieldVisibility(EntityGenerator::FIELD_VISIBLE_PROTECTED);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');

        return $entityGenerator;
    }
}
