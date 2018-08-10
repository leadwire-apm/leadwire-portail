<?php declare (strict_types = 1);

namespace ATS\GeneratorBundle\Command;

use Symfony\Component\Finder\Finder;
use ATS\CoreBundle\Service\Util\StringWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use ATS\GeneratorBundle\Generator\DocumentGenerator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use ATS\GeneratorBundle\Generator\EntityViewGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GenerateViewEntityCommand extends GeneratorCommand
{
    const VIEW_PARAM_NAME = 'view';
    const FIELDS_PARAM_NAME = 'fields';

    protected function configure()
    {
        $this->setName('ats:generator:generate:view-entity')
            ->setDescription('Generates a new ViewEntity inside a bundle')
            ->addOption(
                self::VIEW_PARAM_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'The document class name to initialize (shortcut notation)'
            )
            ->addOption(
                self::FIELDS_PARAM_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'The fields to create with the new document'
            )
        ;
    }

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

        list($bundle, $viewEntity) = $this->parseShortcutNotation($input->getOption(self::VIEW_PARAM_NAME));
        if (is_string($bundle)) {
            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }

        if ((new StringWrapper($viewEntity))->endsWith("View")) {
            $viewEntity = substr($viewEntity, 0, -4);
        }

        $fields = $this->parseFields($input->getOption('fields'));

        $questionHelper->writeSection($output, 'Document generation');

        /** @var DocumentGenerator $generator */
        $generator = $this->getGenerator($bundle);
        $generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $generator->generate(
            $bundle,
            $viewEntity,
            $fields
        );

        $output->writeln('Generating the Document code: <info>OK</info>');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        $question = new Question(
            $questionHelper->getQuestion(
                'The View shortcut name',
                $input->getOption(self::VIEW_PARAM_NAME)
            ),
            $input->getOption(self::VIEW_PARAM_NAME)
        );

        $question->setAutocompleterValues($bundleNames);

        $viewEntity = $questionHelper->ask($input, $output, $question);
        list($bundle, $viewEntity) = $this->parseShortcutNotation($viewEntity);
        $viewEntity = ucfirst($viewEntity);
        $input->setOption(self::VIEW_PARAM_NAME, $bundle . ':' . $viewEntity);

        // fields

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

            $question = new Question($questionHelper->getQuestion('Field Class', null), null);

            $question->setAutocompleterValues($entities);

            $type = $questionHelper->ask($input, $output, $question);

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

            $fields[$name] = array(
                'fieldName' => $name,
                'namespacedType' => $type,
                'shortType' => $document
            );
        }

        $input->setOption('fields', $fields);
    }

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
        return new EntityViewGenerator($this->getContainer());
    }

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
}
