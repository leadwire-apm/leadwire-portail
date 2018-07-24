<?php declare (strict_types = 1);

namespace ATS\GeneratorBundle\Command;

use ATS\CoreBundle\Command\Base\BaseCommand;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Input\InputOption;

class GenerateFakeDataCommand extends BaseCommand
{
    const DEFAULT_COUNT = 10;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var AnnotationReader
     */

    private $annotationReader;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param AnnotationReader $annotationReader
     */
    public function __construct(ManagerRegistry $managerRegistry, AnnotationReader $annotationReader)
    {
        $this->managerRegistry = $managerRegistry;
        $this->annotationReader = $annotationReader;
        $this->faker = Factory::create();

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ats:generator:generate:fake')
            ->setDescription('Generates fake data for selected entity')
            ->addArgument('entity')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'c', self::DEFAULT_COUNT)
            ->addOption('ref-bypass', null, InputOption::VALUE_NONE, 'r')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'p');
    }

    protected function doExecute()
    {
        $documentClass = $this->input->getArgument('entity');
        $documentClass = str_replace(":", "\\Document\\", $documentClass);

        $manager = $this->managerRegistry->getManager();

        $count = $this->input->getOption('count');
        $shouldPurge = $this->input->getOption('purge');

        if ($shouldPurge) {
            $this->output->writeln("Wiping all instances of <comment>$documentClass</comment> before generation");
            $manager->getRepository($documentClass)->deleteAll();
        }

        for ($i = 0; $i < $count; $i++) {
            $instance = $this->populateObject($documentClass);
            $this->managerRegistry->getManager()->persist($instance);
        }

        $this->managerRegistry->getManager()->flush();
        $this->output->writeln("Generated <info>$count</info> elements of <info>$documentClass</info>");
    }

    /**
     * @param $documentClass
     * @return mixed
     */
    private function populateObject($documentClass)
    {
        $manager = $this->managerRegistry->getManager();

        $instance = new $documentClass;
        $properties = $this->fetchProperties($documentClass);
        $shouldRefBypass = $this->input->getOption('ref-bypass');

        foreach ($properties as $property) {
            $value = null;

            $annotations = $this->annotationReader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                switch (get_class($annotation)) {
                    case ODM\Id::class:
                        break;
                    case ODM\Field::class:
                        switch ($annotation->type) {
                            case 'string':
                                $value = $this->faker->realText(50);
                                break;
                            case 'integer':
                                $value = $this->faker->randomNumber();
                                break;
                            case 'boolean':
                                $value = $this->faker->boolean();
                                break;
                            case 'float':
                                $value = $this->faker->randomFloat();
                                break;
                            case 'hash':
                                $value = $this->faker->shuffleArray();
                                break;
                            case 'date':
                                $value = $this->faker->dateTimeBetween('-1 month', '+1 month');
                                break;
                            default:
                                break;
                        }
                        break;
                    case ODM\ReferenceOne::class:
                        $targetClass = $annotation->targetDocument;
                        if ($shouldRefBypass) {
                            $value = $this->fetchRandom($targetClass);
                        } else {
                            $value = $this->populateObject($targetClass);
                            $manager->persist($value);
                        }
                        break;
                    case ODM\ReferenceMany::class:
                        $targetClass = $annotation->targetDocument;
                        if ($shouldRefBypass) {
                            $ref = $this->fetchRandom($targetClass);
                        } else {
                            $ref = $this->populateObject($annotation->targetDocument);
                            $manager->persist($ref);
                        }
                        $value = [$ref];
                        break;
                    case ODM\EmbedOne::class:
                        $value = $this->populateObject($annotation->targetDocument);
                        break;
                    case ODM\EmbedMany::class:
                        $ref = $this->populateObject($annotation->targetDocument);
                        $value = [$ref];
                        break;
                    default:
                        break;
                }
            }

            if (!is_null($value)) {
                $setterMethod = new \ReflectionMethod($documentClass, 'set' . ucfirst($property->name));
                $setterMethod->invoke($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * @param $targetClass
     * @return array
     */
    public function fetchProperties($targetClass)
    {
        $result = [];

        $reflectionClass = new \ReflectionClass($targetClass);

        while ($reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                $result[] = $property;
            }

            $parentClass = $reflectionClass->getParentClass();

            if ($parentClass && $this->annotationReader->getClassAnnotation($parentClass, ODM\Document::class)) {
                $reflectionClass = $parentClass;
            } else {
                $reflectionClass = null;
            }
        }

        return $result;
    }

    /**
     * @param $targetClass
     * @return mixed
     */
    public function fetchRandom($targetClass)
    {
        $manager = $this->managerRegistry->getManager();
        $result = $manager->getRepository($targetClass)->findOneBy([]);

        if (!$result) {
            throw new \Exception(
                sprintf("Cannot bypass %s refs : not enough data", $targetClass)
            );
        }

        return $result;
    }
}
