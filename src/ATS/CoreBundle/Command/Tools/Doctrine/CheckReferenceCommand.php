<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Command\Tools\Doctrine;

use ATS\CoreBundle\Command\Base\BaseCommand;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Symfony\Component\Console\Input\InputOption;

class CheckReferenceCommand extends BaseCommand
{
    const CMD_NAME = 'ats:core:tools:doctrine:check-reference';

    /**
     * @var mixed
     */
    private $documentsMetadata;
    /**
     * @var mixed
     */
    private $countOnly;
    /**
     * @var mixed
     */
    private $force;
    /**
     * @var mixed
     */
    private $autoFix;

    const THRESHOLD = 10000;
    const PAGE_COUNT = 50000;

    const LEVEL_INFO = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_ERROR = 2;

    /**
     * @var array
     */
    private static $levelToTag = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARNING => 'comment',
        self::LEVEL_ERROR => 'error',
    ];

    protected function configure()
    {
        $this
            ->setName(self::CMD_NAME)
            ->addOption('collection', 'col', InputOption::VALUE_REQUIRED, 'Specifiy a collection')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Display bad refs count only')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignore threshold (10000)')
            ->addOption('auto-fix', 'a', InputOption::VALUE_NONE, 'Automatically removes bad references');
    }

    protected function doExecute()
    {
        $selectedCollection = $this->input->getOption('collection');
        $this->countOnly = $this->input->getOption('count');
        $this->force = $this->input->getOption('force');
        $this->autoFix = $this->input->getOption('auto-fix');

        /** @var DocumentManager $documentManager */
        $documentManager = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $metas = $documentManager->getMetadataFactory()->getAllMetadata();

        $this->documentsMetadata = [];

        foreach ($metas as $meta) {
            $metaIdentifier = (new \ReflectionClass($meta->getName()))->getShortName();
            $this->documentsMetadata[$metaIdentifier] = $meta;
        }

        if ($selectedCollection != null) {
            $scope =
                array_filter(
                    $this->documentsMetadata,
                    function ($meta) use ($selectedCollection) {
                        return (new \ReflectionClass($meta->getName()))->getShortName() == $selectedCollection;
                    }
                );
        } else {
            $scope = $this->documentsMetadata;
        }

        foreach ($scope as $className => $classMetadata) {
            $referenceFields = [];
            if ($this->hasReferences($classMetadata, $referenceFields)) {
                $this->checkReferences($documentManager, $classMetadata, $referenceFields);
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
     * @param $referenceFields
     * @return mixed
     */
    private function hasReferences(ClassMetadata $classMetadata, &$referenceFields)
    {
        $hasReferences = false;

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if ($classMetadata->hasReference($fieldName)) {
                if (array_key_exists('mappedBy', $classMetadata->fieldMappings[$fieldName])) {
                    if ($classMetadata->fieldMappings[$fieldName]['mappedBy'] == null) {
                        $hasReferences = true;
                        $referenceFields[] = $fieldName;
                    }
                }
            }
        }

        return $hasReferences;
    }

    /**
     * @param $documentManager
     * @param $classMetadata
     * @param $referenceFields
     * @return null
     */
    private function checkReferences($documentManager, $classMetadata, $referenceFields)
    {
        $qb = $documentManager
            ->getRepository($classMetadata->name)
            ->createQueryBuilder()
            ->hydrate(false);

        $qbCount = clone ($qb);

        $documentsCount = $qbCount->count()->getQuery()->execute();

        if (!$this->force && $documentsCount > self::THRESHOLD) {
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
                    if ($attr == '_id') {
                        continue;
                    }

                    // if DBRef is null --> No reference for this document
                    if ($dbRef == null) {
                        continue;
                    }

                    if (array_key_exists('$ref', $dbRef)) {
                        // ReferenceOne
                        $found = $this
                            ->assertValidReference(
                                $documentManager,
                                $dbRef,
                                $classMetadata->collection,
                                $document['_id'],
                                $attr
                            );
                        if (!$found) {
                            $count++;
                        }
                    } else {
                        // ReferenceMany
                        $cnt = 0;
                        foreach ($dbRef as $ref) {
                            $found = $this
                                ->assertValidReference(
                                    $documentManager,
                                    $ref,
                                    $classMetadata->collection,
                                    $document['_id'],
                                    $attr . '.' . $cnt
                                );
                            if (!$found) {
                                $count++;
                            }
                            $cnt++;
                        }
                    }
                }
            }
            $current += self::PAGE_COUNT;
        }

        if ($count) {
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
     * @param $documentManager
     * @param $dbRef
     * @param $collection
     * @param $documentId
     * @param $fieldName
     * @return mixed
     */
    private function assertValidReference($documentManager, $dbRef, $collection, $documentId, $fieldName)
    {
        if (!array_key_exists('$ref', $dbRef)
            || !array_key_exists('$id', $dbRef)
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

        if (!array_key_exists($dbRef['$ref'], $this->documentsMetadata)) {
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
        $found = $documentManager
            ->getRepository($classMeta->name)
            ->createQueryBuilder()
            ->hydrate(false)
            ->field('id')->equals($dbRef['$id'])
            ->select('id')
            ->getQuery()
            ->getSingleResult();

        if (!$found && !$this->countOnly) {
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
        if (!$found && $this->autoFix) {
            $fixerQb = $documentManager
                ->getRepository($this->documentsMetadata[$collection]->name)
                ->createQueryBuilder()
                ->findAndUpdate()
                ->field('id')->equals($documentId);

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
     * @param $message
     * @param $level
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
