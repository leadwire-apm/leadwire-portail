<?php declare (strict_types = 1);

namespace ATS\GeneratorBundle\Command;

use ATS\CoreBundle\Service\Util\StringWrapper;
use ATS\GeneratorBundle\Generator\DocumentGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateDocumentCommand extends GeneratorCommand
{

    protected function configure()
    {
        $this->setName('ats:generator:generate:document')
            ->setDescription('Generates a new Doctrine document inside a bundle')
            ->addOption(
                'document',
                null,
                InputOption::VALUE_REQUIRED,
                'The document class name to initialize (shortcut notation)'
            )
            ->addOption(
                'fields',
                null,
                InputOption::VALUE_REQUIRED,
                'The fields to create with the new document'
            )
            ->addOption(
                'with-controller',
                null,
                InputOption::VALUE_NONE,
                'Whether to generate the associated Rest Controller or not'
            )
            ->addOption(
                'no-strict',
                null,
                InputOption::VALUE_NONE,
                'Strict Check'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $confirmationQuestion = new ConfirmationQuestion(
                $questionHelper->getQuestion('Do you confirm generation', 'yes', '?'),
                true
            );

            if (!$questionHelper->ask($input, $output, $confirmationQuestion)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        list($bundle, $document) = $this->parseShortcutNotation($input->getOption('document'));
        if (is_string($bundle)) {
            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }

        $fields = $this->parseFields($input->getOption('fields'));

        $questionHelper->writeSection($output, 'Document generation');

        /** @var DocumentGenerator $generator */
        $generator = $this->getGenerator($bundle);
        $generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $generator->generate(
            $bundle,
            $document,
            $fields
        );

        $output->writeln('Generating the Document code: <info>OK</info>');

        $withController = $input->getOption('with-controller');

        if ($withController) {
            $command = $this->getApplication()->find('ats:generator:generate:rest');

            $arguments = array(
                '--controller' => $bundle->getName() . ':' . $document,
            );

            $returnCode = $command->run(new ArrayInput($arguments), $output);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $noStrict = $input->getOption('no-strict');

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        $question = new Question(
            $questionHelper->getQuestion(
                'The Document shortcut name',
                $input->getOption('document')
            ),
            $input->getOption('document')
        );

        $question->setAutocompleterValues($bundleNames);

        $document = $questionHelper->ask($input, $output, $question);
        list($bundle, $document) = $this->parseShortcutNotation($document);
        $document = ucfirst($document);
        $input->setOption('document', $bundle . ':' . $document);

        // Document fields

        $entities = [];

        foreach ($bundleNames as $bundleName) {
            /** @var BundleInterface $bundle */
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
            $finder = new Finder();
            if (is_dir(__DIR__ . '/../../' . $bundle->getNamespace() . '/Document')) {
                $finder->files()->in(__DIR__ . '/../../' . $bundle->getNamespace() . '/Document');
                foreach ($finder as $file) {
                    $className = explode('.', $file->getFilename())[0];
                    $entities[] = "$bundleName:$className";
                }
            }
        }

        $fields = [];

        while (true) {
            $output->writeln('');

            if (!$name = $this->askForFieldName($input, $output, $questionHelper, $fields)) {
                break;
            }

            $default = (new StringWrapper($name))->inferPhpType();

            $question = new Question(
                $questionHelper->getQuestion('Field type', $default),
                $default
            );

            $question->setAutocompleterValues(array_merge($entities, $this->getBasicTypes()));

            $type = $questionHelper->ask($input, $output, $question);

            if ($type == "datetime") {
                $type = "\\DateTime";
            }

            if (!in_array($type, $this->getBasicTypes())) {
                list($bundle, $document) = $this->parseShortcutNotation($type);

                if (is_string($bundle)) {
                    try {
                        /** @var BundleInterface $bundle */
                        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
                    } catch (\Exception $e) {
                        $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
                        continue;
                    }
                }

                $type = $bundle->getNamespace() . '\\Document\\' . $document;

                if (!$noStrict) {
                    try {
                        $class = new \ReflectionClass($type);
                    } catch (\ReflectionException $e) {
                        $output->writeln(sprintf('<bg=red>Class "%s" does not exist.</>', $type));
                        continue;
                    }
                }

                $default = (new StringWrapper($name))->endsWith('s') ? 'ReferenceMany' : 'ReferenceOne';

                $question = new Question(
                    $questionHelper->getQuestion('Association type', $default),
                    $default
                );
                $question->setAutocompleterValues(['ReferenceOne', 'ReferenceMany', 'EmbedOne', 'EmbedMany']);
                $associationType = $questionHelper->ask($input, $output, $question);
                $associationMany = in_array($associationType, ['ReferenceMany', 'EmbedMany']);

                if (!in_array($associationType, ['ReferenceOne', 'ReferenceMany', 'EmbedOne', 'EmbedMany'])) {
                    $output->writeln(sprintf('<bg=red>Bad association "%s".</>', $associationType));
                    continue;
                }

                $isBasicType = false;
            } else {
                $isBasicType = true;
                $associationType = '';
                $associationMany = false;
            }

            $fields[$name] = array(
                'fieldName' => $name,
                'type' => $type,
                'mongoType' => $this->resolveMongoType($type),
                'jmsType' => $this->resolveSerializerType($type),
                'basicType' => $isBasicType,
                'associationType' => $associationType,
                'associationMany' => $associationMany,
            );
        }

        $input->setOption('fields', $fields);

        $output->writeln('');

        $controllerQuestion = new ConfirmationQuestion(
            $questionHelper->getQuestion(
                'Do you want to generate a Rest Controller',
                'yes',
                '?'
            )
        );

        $withController = $questionHelper->ask($input, $output, $controllerQuestion);

        $input->setOption('with-controller', $withController == 'yes' ? true : false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param $fields
     * @return mixed
     */
    private function askForFieldName(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        $fields
    ) {

        $question = new Question(
            $questionHelper->getQuestion('New field name (press <return> to stop adding fields)', null)
        );

        $question->setValidator(function ($name) use ($fields) {
            if (isset($fields[$name]) || 'id' == $name) {
                throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
            }
            return $name;
        });

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param $input
     * @return mixed
     */
    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();

        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $fields[$name] = array('fieldName' => $name, 'type' => $type);
            }
        }
        return $fields;
    }

    protected function createGenerator()
    {
        return new DocumentGenerator($this->getContainer());
    }

    /**
     * @param $shortcut
     */
    public function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)',
                    $entity
                )
            );
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    private function getBasicTypes()
    {
        return [
            'string',
            'integer',
            'bool',
            'float',
            'datetime',
            '\\DateTime',
            'ArrayCollection',
            'array',
        ];
    }

    /**
     * @param $phpType
     * @return mixed
     */
    private function resolveMongoType($phpType)
    {
        $mapping = [
            'string' => 'string',
            'integer' => 'integer',
            'int' => 'integer',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'float' => 'float',
            'datetime' => 'date',
            'DateTime' => 'date',
            "\\DateTime" => 'date',
            'array' => 'hash',
        ];

        if (array_key_exists($phpType, $mapping)) {
            return $mapping[$phpType];
        }

        return $phpType;
    }

    /**
     * @param $phpType
     * @return mixed
     */
    private function resolveSerializerType($phpType)
    {
        $mapping = [
            'string' => 'string',
            'integer' => 'integer',
            'int' => 'integer',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'float' => 'float',
            'datetime' => 'DateTime',
            'DateTime' => 'DateTime',
            "\\DateTime" => 'DateTime',
        ];

        if (array_key_exists($phpType, $mapping)) {
            return $mapping[$phpType];
        }

        return $phpType;
    }
}
