<?php declare(strict_types=1);

namespace ATS\GeneratorBundle\Command;

use ATS\GeneratorBundle\Generator\RestControllerGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateRestControllerCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this->setName('ats:generator:generate:rest')
            ->setDescription('Generates a Rest controller')
            ->setDefinition(
                array(
                    new InputOption(
                        'controller',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The name of the controller to create'
                    ),
                )
            )
        ;
    }

    protected function createGenerator()
    {
        return new RestControllerGenerator($this->getContainer()->get('filesystem'), $this->getContainer());
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if (null === $input->getOption('controller')) {
            throw new \RuntimeException('The controller option must be provided.');
        }

        list($bundle, $controller) = $this->parseShortcutNotation($input->getOption('controller'));
        if (is_string($bundle)) {
            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }

        $questionHelper->writeSection($output, 'Controller generation');

        /** @var RestControllerGenerator $generator */
        $generator = $this->getGenerator($bundle);
        $generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $generator->generate(
            $bundle,
            $controller
        );

        $output->writeln('Generating the controller code: <info>OK</info>');

        $questionHelper->writeGeneratorSummary($output, array());
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        $controller = $input->getOption('controller');

        if (!$controller) {
            $question = new Question(
                $questionHelper->getQuestion('Controller name', $input->getOption('controller')),
                $input->getOption('controller')
            );

            $question->setAutocompleterValues($bundleNames);
            $controller = $questionHelper->ask($input, $output, $question);
        }

        list($bundle, $controller) = $this->parseShortcutNotation($controller);
        $input->setOption('controller', $bundle . ':' . $controller);
    }

    public function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The controller name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)',
                    $entity
                )
            );
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
}
