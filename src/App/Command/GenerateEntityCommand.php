<?php

namespace App\Command;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Types\Type;
use Isolate\ConsoleServiceProvider\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class GenerateEntityCommand.
 */
class GenerateEntityCommand extends Command
{
    const BASE_PATH = '/src';
    const ENTITY_NAMESPACE = 'App\\Entity';
    const REST_RESOURCE_NAMESPACE = 'App\\RestResourceDto';
    const REPOSITORY_NAMESPACE = 'App\\Repository';
    const REPOSITORY_INTERFACE_NAMESPACE = 'App\\Repository\\Interfaces';
    const PAD_LENGTH = 60;

    private static $dbTypes = [
        'string' => [
            Type::STRING,
            Type::TEXT,
            Type::BINARY,
            Type::BLOB,
            Type::GUID,
        ],
        'int' => [
            Type::BIGINT,
            Type::SMALLINT,
            Type::INTEGER,
        ],
        'array' => [
            Type::TARRAY,
            Type::SIMPLE_ARRAY,
            Type::JSON_ARRAY,
        ],
        'double' => [
            Type::DECIMAL,
            Type::FLOAT,
        ],
        '\DateTime' => [
            Type::DATETIME,
            Type::DATETIMETZ,
            Type::DATE,
            Type::TIME,
        ],
        'boolean' => [
            Type::BOOLEAN,
        ],
        'object' => [
            Type::OBJECT,
        ],
    ];

    private static $generatedTypes = [
        'SEQUENCE',
        'TABLE',
        'IDENTITY',
        'NONE',
        'UUID',
        'CUSTOM',
    ];

    private static $phpTypes = [
        'string',
        'int',
        'boolean',
        'double',
        'object',
        'array',
        '\DateTime',
    ];

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $entityPath;

    /**
     * @var string
     */
    private $restResourceSchemaPath;

    /**
     * @var string
     */
    private $restResourcePath;

    /**
     * @var string
     */
    private $repositoryPath;

    /**
     * @var string
     */
    private $repositoryInterfacePath;

    /**
     * @var string
     */
    private $restResourceTestsPath;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    protected function configure()
    {
        $this
            ->setName('generate:entity')
            ->setDescription('Generate entity and repository stubs')
            ->addArgument('className', InputArgument::REQUIRED, 'Entity class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $this->twig = $container['twig'];

        $this->basePath = $container['paths.root'] . self::BASE_PATH;

        $this->entityPath = $this->basePath . '/' . $this->namespaceToPath(self::ENTITY_NAMESPACE);
        $this->restResourceTestsPath = $this->basePath . '/../tests/api/v1';
        $this->restResourcePath = $this->basePath . '/' . $this->namespaceToPath(self::REST_RESOURCE_NAMESPACE);
        $this->restResourceSchemaPath = $this->basePath . '/../config/schemas/resource';
        $this->repositoryPath = $this->basePath . '/' . $this->namespaceToPath(self::REPOSITORY_NAMESPACE);
        $this->repositoryInterfacePath = $this->basePath . '/' . $this->namespaceToPath(self::REPOSITORY_INTERFACE_NAMESPACE);

        $this->checkDirs();
        $this->makeDirs();

        $entityClassName = ucfirst($input->getArgument('className'));
        $restResourceClassName = $entityClassName . 'RestResource';
        $repositoryClassName = $entityClassName . 'Repository';
        $repositoryInterface = $repositoryClassName . 'Interface';
        $resourceId = Inflector::tableize(Inflector::pluralize($entityClassName));
        $resourceTitle = str_replace('_', ' ', $resourceId);
        $tableName = $resourceId;

        $entityFile = $this->entityPath . '/' . $entityClassName . '.php';
        $restResourceFile = $this->restResourcePath . '/' . $restResourceClassName . '.php';
        $repositoryFile = $this->repositoryPath . '/' . $repositoryClassName . '.php';
        $repositoryInterfaceFile = $this->repositoryInterfacePath . '/' . $repositoryInterface . '.php';

        if (file_exists($entityFile)) {
            throw new \RuntimeException(sprintf('Entity class "%s" already exists', $entityClassName));
        }

        if (file_exists($repositoryFile)) {
            throw new \RuntimeException(sprintf('Repository class "%s" already exists', $repositoryClassName));
        }

        if (file_exists($repositoryInterfaceFile)) {
            throw new \RuntimeException(sprintf('Repository interface "%s" already exists', $repositoryInterfaceFile));
        }

        $isAddProperties = $this->askConfirmationQuestion('Do you have add properties?', $input, $output, false);

        $properties = [];
        $restResourceSchema = [];

        if ($isAddProperties) {
            while (true) {
                $propertyName = $this->askQuestion('Please enter the name of the property', $input, $output);

                if ($propertyName === null) {
                    break;
                }

                $phpType = $this->askChoiceOptions('Please select your PHP type', $input, $output, self::$phpTypes, 0);

                $dbTypes = self::$dbTypes[$phpType];

                if (count($dbTypes) > 1) {
                    $dbType = $this->askChoiceOptions('Please select your DB type', $input, $output, $dbTypes, 0);
                } else {
                    $dbType = $dbTypes[0];
                }

                $isGeneratedValue = false;
                $isIdentifier = false;
                $generatedValueType = null;

                if ($phpType === 'int') {
                    $isGeneratedValue = $this->askConfirmationQuestion('This is property generated value?', $input,
                        $output,
                        false);

                    if ($isGeneratedValue) {
                        $generatedValueType = $this->askChoiceOptions(
                            'Please select your generated value type', $input, $output, self::$generatedTypes, 0
                        );

                        $isIdentifier = $this->askConfirmationQuestion('This is property identifier/primary key specified?',
                            $input,
                            $output);
                    }
                }

                $isNullable = $this->askConfirmationQuestion('This is property nullable?', $input, $output, false);

                $properties[] = [
                    'name' => $propertyName,
                    'phpType' => $phpType,
                    'dbType' => $dbType,
                    'isGeneratedValue' => $isGeneratedValue,
                    'generatedValueType' => $generatedValueType,
                    'isNullable' => $isNullable,
                    'isIdentifier' => $isIdentifier,
                ];
            }

            $isCreateRestResource = $this->askConfirmationQuestion('Do you have create rest resource?', $input,
                $output);

            if ($isCreateRestResource) {
                $restResourceSchemaProperties = [];

                foreach ($properties as $property) {
                    $isAddPropertyToRestResource = $this->askConfirmationQuestion(
                        sprintf('Do you have add "%s" to rest resource? ', $property['name']), $input, $output
                    );

                    if ($isAddPropertyToRestResource) {
                        $restResourceSchemaProperties[$property['name']] = [
                            'type' => $property['phpType'],
                        ];
                    }
                }

                $restResourceSchema = [
                    'title' => $entityClassName . ' schema',
                    'type' => 'object',
                    'properties' => $restResourceSchemaProperties,
                ];
            }
        }

        $this->generateEntity($entityFile, [
            'className' => $entityClassName,
            'tableName' => $tableName,
            'properties' => $properties,
            'repositoryClass' => self::REPOSITORY_NAMESPACE . '\\' . $repositoryClassName,
        ], $output);

        $repositoryOptions = [
            'namespaceClass' => self::REPOSITORY_NAMESPACE,
            'namespaceInterface' => self::REPOSITORY_INTERFACE_NAMESPACE,
            'className' => $repositoryClassName,
            'interface' => $repositoryInterface,
        ];

        $this->generateRepository($repositoryFile, $repositoryOptions, $output);
        $this->generateRepositoryInterface($repositoryInterfaceFile, $repositoryOptions, $output);

        if (!$restResourceSchema && !$restResourceSchema['properties']) {
            $this->generateRestResource($restResourceFile, [
                'id' => $resourceId,
                'className' => $restResourceClassName,
                'schema' => $restResourceSchema,
            ], $output);

            $isGenerateRestResourceTests = $this->askConfirmationQuestion(
                'Do you have generate rest resource tests?', $input, $output
            );

            if ($isGenerateRestResourceTests) {
                $this->generateRestResourceTests(
                    $this->restResourceTestsPath, $resourceId, $resourceTitle, $restResourceSchema, $output
                );
            }
        }
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    private function namespaceToPath(string $namespace) : string
    {
        return str_replace('\\', '/', $namespace);
    }

    /**
     * @throws \RuntimeException
     */
    private function checkDirs()
    {
        $this->checkPathWritable($this->entityPath);
        $this->checkPathWritable($this->repositoryPath);
        $this->checkPathWritable($this->repositoryInterfacePath);
    }

    /**
     * @param $path
     *
     * @throws \RuntimeException
     */
    private function checkPathWritable($path)
    {
        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf('Path "%s" is not writable', $path));
        }
    }

    /**
     */
    private function makeDirs()
    {
        if (!is_dir($this->entityPath)) {
            mkdir($this->entityPath, 0777, true);
        }

        if (!is_dir($this->repositoryPath)) {
            mkdir($this->repositoryPath, 0777, true);
        }

        if (!is_dir($this->repositoryInterfacePath)) {
            mkdir($this->repositoryInterfacePath, 0777, true);
        }
    }

    private function askConfirmationQuestion(
        string $question,
        InputInterface $input,
        OutputInterface $output,
        $default = true
    ) {
        $question = new ConfirmationQuestion($question . ' (default to ' . ($default ? 'yes' : 'no') . '): ', $default);

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askQuestion($question, InputInterface $input, OutputInterface $output, $default = null)
    {
        $defaultTip = $default !== null ? ' (default to ' . $default . ')' : '';
        $question = new Question($question . $defaultTip . ': ');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askChoiceOptions(
        string $question,
        $input,
        $output,
        array $options,
        int $default = 0
    ) {
        $question = new ChoiceQuestion(sprintf($question . ' (defaults to %s)', self::$phpTypes[0]), $options,
            $default);
        $question->setErrorMessage('Invalid option %s.');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function generateEntity($file, $options, OutputInterface $output)
    {
        $content = $this->twig->render('generator/entity.twig', [
            'entity' => $options,
        ]);

        file_put_contents($file, $content);

        $output->writeln(str_pad('Generate entity', self::PAD_LENGTH, '.') . 'OK');
    }

    private function generateRepository($file, $options, OutputInterface $output)
    {
        $content = $this->twig->render('generator/repository.twig', [
            'repository' => $options,
        ]);

        file_put_contents($file, $content);

        $output->writeln(str_pad('Generate repository', self::PAD_LENGTH, '.') . 'OK');
    }

    private function generateRepositoryInterface($file, $options, OutputInterface $output)
    {
        $content = $this->twig->render('generator/repository_interface.twig', [
            'repository' => $options,
        ]);

        file_put_contents($file, $content);

        $output->writeln(str_pad('Generate repository interface', self::PAD_LENGTH, '.') . 'OK');
    }

    private function generateRestResource(string $file, array $options, OutputInterface $output)
    {
        $restResourceContent = $this->twig->render('generator/rest_resource.twig', [
            'resource' => $options,
        ]);

        file_put_contents($file, $restResourceContent);

        $output->writeln(str_pad('Generate rest resource', self::PAD_LENGTH, '.') . 'OK');
    }

    private function generateRestResourceTests(
        string $path,
        string $id,
        string $title,
        array $schema,
        OutputInterface $output
    ) {
        $path = $path . '/' . $id;

        if (!is_dir($path)) {
            mkdir($path);
        }

        $findTestFile = sprintf('/%sFindCept.php', ucfirst($id));
        $findAllTestFile = sprintf('/%sAllCept.php', ucfirst($id));
        $createTestFile = sprintf('/%sCreateCept.php', ucfirst($id));
        $updateTestFile = sprintf('/%sUpdateCept.php', ucfirst($id));

        $options = [
            'id' => $id,
            'title' => $title,
            'schema' => $schema,
        ];

        $findTestContent = $this->twig->render('generator/tests/rest_resource_find.twig', $options);
        $findAllTestContent = $this->twig->render('generator/tests/rest_resource_find_all.twig', $options);
        $createTestContent = $this->twig->render('generator/tests/rest_resource_create.twig', $options);
        $updateTestContent = $this->twig->render('generator/tests/rest_resource_update.twig', $options);

        file_put_contents($path . $findTestFile, $findTestContent);
        file_put_contents($path . $findAllTestFile, $findAllTestContent);
        file_put_contents($path . $createTestFile, $createTestContent);
        file_put_contents($path . $updateTestFile, $updateTestContent);

        $output->writeln(str_pad('Generate rest resource tests', self::PAD_LENGTH, '.') . 'OK');
    }
}
