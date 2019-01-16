<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Command\Tools\Doctrine;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckReferenceCommand extends ContainerAwareCommand
{
    const CMD_NAME = 'ats:core:tools:doctrine:check-reference';

    const THRESHOLD = 10000;
    const PAGE_COUNT = 50000;

    const LEVEL_INFO = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_ERROR = 2;

    /**
     * @var array
     */
    private $documentsMetadata;

    /**
     * @var boolean
     */
    private $countOnly;

    /**
     * @var boolean
     */
    private $force;

    /**
     * @var boolean
     */
    private $autoRemove;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var array
     */
    private static $levelToTag = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARNING => 'comment',
        self::LEVEL_ERROR => 'error',
    ];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->documentManager = $managerRegistry->getManager();
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName(self::CMD_NAME)
            ->addOption('collection', 'col', InputOption::VALUE_REQUIRED, 'Specifiy a collection')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Display bad refs count only')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignore threshold (10000)')
            ->addOption('auto-remove', 'a', InputOption::VALUE_NONE, 'Automatically removes broken references')
            ->setDescription("Finds all broken DBRefs in the MongoDB database and optionnaly removes them");
    }

    /**
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $selectedCollection = $input->getOption('collection');
        $this->countOnly = (bool) $input->getOption('count');
        $this->force = (bool) $input->getOption('force');
        $this->autoRemove = (bool) $input->getOption('auto-remove');

        $metas = $this->documentManager->getMetadataFactory()->getAllMetadata();

        $this->documentsMetadata = [];

        foreach ($metas as $meta) {
            $metaIdentifier = (new \ReflectionClass($meta->getName()))->getShortName();
            $this->documentsMetadata[$metaIdentifier] = $meta;
        }

        if ($selectedCollection !== null) {
            $scope =
                array_filter(
                    $this->documentsMetadata,
                    function (ClassMetadata $meta) use ($selectedCollection) {
                        return (new \ReflectionClass($meta->getName()))->getShortName() == $selectedCollection;
                    }
                );
        } else {
            $scope = $this->documentsMetadata;
        }

        foreach ($scope as $className => $classMetadata) {
            $referenceFields = [];
            if ($this->hasReferences($classMetadata, $referenceFields) === true) {
                $this->checkReferences($classMetadata, $referenceFields);
            } else {
                $this->display(
                    sprintf("Collection %s has no attached references, skipping ..", $className),
                    self::LEVEL_WARNING
                );
            }
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array $referenceFields
     *
     * @return mixed
     */
    private function hasReferences(ClassMetadata $classMetadata, &$referenceFields)
    {
        $hasReferences = false;

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if ($classMetadata->hasReference($fieldName) === true) {
                if (array_key_exists('mappedBy', $classMetadata->fieldMappings[$fieldName]) === true) {
                    if ($classMetadata->fieldMappings[$fieldName]['mappedBy'] === null) {
                        $hasReferences = true;
                        $referenceFields[] = $fieldName;
                    }
                }
            }
        }

        return $hasReferences;
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array $referenceFields
     *
     * @return void
     */
    private function checkReferences(ClassMetadata $classMetadata, array $referenceFields)
    {
        $qb = $this->documentManager
            ->getRepository($classMetadata->name)
            ->createQueryBuilder()
            ->hydrate(false);

        $qbCount = clone ($qb);

        $documentsCount = $qbCount->count()->getQuery()->execute();

        if ($this->force === false && $documentsCount > self::THRESHOLD) {
            $this->display(
                sprintf(
                    "[%s] => Ignored! Contains %d documents",
                    $classMetadata->collection,
                    $documentsCount
                ),
                self::LEVEL_WARNING
            );

            return;
        }

        foreach ($referenceFields as $ref) {
            $qb = $qb->select($ref);
        }

        $current = 0;
        $count = 0;
        while ($current < $documentsCount) {
            $documents = $qb
                ->skip($current / self::PAGE_COUNT)
                ->limit(self::PAGE_COUNT)
                ->getQuery()
                ->execute()
                ->toArray();

            foreach ($documents as $document) {
                foreach ($document as $attr => $dbRef) {
                    if ($attr === '_id') {
                        continue;
                    }

                    // if DBRef is null --> No reference for this document
                    if ($dbRef === null) {
                        continue;
                    }

                    if (array_key_exists('$ref', $dbRef) === true) {
                        // ReferenceOne
                        $found = $this
                            ->assertValidReference(
                                $dbRef,
                                $classMetadata->collection,
                                $document['_id'],
                                $attr
                            );
                        if ($found === false) {
                            $count++;
                        }
                    } else {
                        // ReferenceMany
                        $cnt = 0;
                        foreach ($dbRef as $ref) {
                            $found = $this
                                ->assertValidReference(
                                    $ref,
                                    $classMetadata->collection,
                                    $document['_id'],
                                    $attr . '.' . $cnt
                                );
                            if ($found === false) {
                                $count++;
                            }
                            $cnt++;
                        }
                    }
                }
            }
            $current += self::PAGE_COUNT;
        }

        if ($count > 0) {
            $this->display(
                sprintf(
                    "[%s] has [%d] bad REFs",
                    $classMetadata->collection,
                    $count
                ),
                self::LEVEL_ERROR
            );
        } else {
            $this->display(sprintf("[%s] All good!", $classMetadata->collection));
        }
    }

    /**
     * @param array $dbRef
     * @param string $collection
     * @param string $documentId
     * @param string $fieldName
     *
     * @return mixed
     */
    private function assertValidReference(
        array $dbRef,
        $collection,
        $documentId,
        $fieldName
    ) {
        if (array_key_exists('$ref', $dbRef) === false
            || array_key_exists('$id', $dbRef) === false
        ) {
            $this->display(
                sprintf(
                    'Ill-formed DBRef in document [%s] [%s]. Either \'$id\' or \'$ref\' is missing in [%s]!',
                    $collection,
                    $documentId,
                    $fieldName
                ),
                self::LEVEL_ERROR
            );

            return null;
        }

        if (array_key_exists($dbRef['$ref'], $this->documentsMetadata) === false) {
            $this->display(
                sprintf(
                    'Ill-formed DBRef in document [%s] [%s]. [%s] does not reference any known Entity in [%s]',
                    $collection,
                    $documentId,
                    $dbRef['$ref'],
                    $fieldName
                ),
                self::LEVEL_ERROR
            );

            return null;
        }

        $referenceClass = $dbRef['$ref'];
        $classMeta = $this->documentsMetadata[$referenceClass];
        $found = $this->documentManager
            ->getRepository($classMeta->name)
            ->createQueryBuilder()
            ->hydrate(false)
            ->field('id')->equals($dbRef['$id'])
            ->select('id')
            ->getQuery()
            ->getSingleResult();

        if ($found === false && $this->countOnly === false) {
            $this->display(
                sprintf(
                    "[%s] [%s] referenced by [%s] [%s] not found",
                    $dbRef['$ref'],
                    $dbRef['$id'],
                    $collection,
                    $documentId
                ),
                self::LEVEL_ERROR
            );
        }
        if ($found === false && $this->autoRemove === true) {
            $fixerQb = $this->documentManager
                ->getRepository($this->documentsMetadata[$collection]->name)
                ->createQueryBuilder()
                ->findAndUpdate()
                ->field('id')
                ->equals($documentId);

            if (strpos($fieldName, '.') !== false) {
                $realFieldName = explode('.', $fieldName)[0];
                $fixerQb = $fixerQb->field($realFieldName)->pull(array('$id' => $dbRef['$id']));
            } else {
                $fixerQb = $fixerQb->field($fieldName)->unsetField();
            }

            $fixerQb
                ->update()
                ->getQuery()
                ->execute();

            $this->display(
                sprintf(
                    "Removed bad DBRef [%s] [%s] referenced by [%s] [%s]",
                    $dbRef['$ref'],
                    $dbRef['$id'],
                    $collection,
                    $documentId
                ),
                self::LEVEL_WARNING
            );
        }

        return $found;
    }

    /**
     * @param string $message
     * @param int    $level
     *
     * @return void
     */
    private function display($message, $level = self::LEVEL_INFO)
    {
        $this->output->writeln(
            sprintf(
                "<%s>%s</%s>",
                self::$levelToTag[$level],
                $message,
                self::$levelToTag[$level]
            )
        );
    }
}
