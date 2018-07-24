<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Command\Tools\Doctrine;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class RunQueryCommand extends ContainerAwareCommand
{
    const DEFAULT_CURSOR_LIMIT = 50;

    const SORT_SHORTHAND_ASC = '-';
    const SORT_SHORTHAND_DESC = '+';

    const DATETIME_SHORTHAND_FORMAT = 'Y-m-d';
    const RANGE_SEPERATOR = '...';

    const MISSING_REFERENCE_LABEL = '__NOT_FOUND__';
    const INTERNAL_CACHE_HIT_COUNTER = '__CHIT__';
    const INTERNAL_CACHE_MISS_COUNTER = '__CMISS__';
    const INTERNAL_CACHE_REQUEST_COUNTER = '__RQ__';

    const ROOT_BENCHMARK_EVENT = '__ROOT_BENCHMARK_EVENT__';
    const ROUTINE_BENCHMARK_CATEGORY = '__ROUTINE__';
    const SUBROUTINE_BENCHMARK_CATEGORY = '__SUBROUTINE__';

    const OPTION_SERIALIZE_JSON = 'json';
    const OPTION_SERIALIZE_CSV = 'csv';
    const OPTION_SERIALIZE_TABLE = 'table';

    const CSV_SEPERATOR = ';';

    const AGGREGATION_SUM_SHORTHAND = '+';
    const AGGREGATION_AVG_SHORTHAND = '~';
    const AGGREGATION_MUL_SHORTHAND = '*';
    const AGGREGATION_MIN_SHORTHAND = '>';
    const AGGREGATION_MAX_SHORTHAND = '<';
    const AGGREGATION_CNT_SHORTHAND = '=';
    const AGGREGATION_FST_SHORTHAND = '^';
    const AGGREGATION_LST_SHORTHAND = '$';

    /**
     * @var mixed
     */
    private $aggregateHandlers;
    /**
     * @var mixed
     */
    private $groupFields;
    /**
     * @var mixed
     */
    private $selectFields;
    /**
     * @var mixed
     */
    private $input;
    /**
     * @var mixed
     */
    private $output;
    /**
     * @var mixed
     */
    private $documentManager;
    /**
     * @var mixed
     */
    private $terminalFieldsCache;
    /**
     * @var mixed
     */
    private $virtualFieldsCache;
    /**
     * @var mixed
     */
    private $queryBenchmarker;
    /**
     * @var mixed
     */
    private $stopwatch;
    /**
     * @var mixed
     */
    private $virtualSelectFields;
    /**
     * @var mixed
     */
    private $allDocumentMetaData;
    /**
     * @var mixed
     */
    private $annotationReader;
    /**
     * @var mixed
     */
    private $qb;
    /**
     * @var mixed
     */
    private $serializeFormat;
    /**
     * @var mixed
     */
    private $collection;
    /**
     * @var mixed
     */
    private $targetDocumentMetaData;
    /**
     * @var mixed
     */
    private $targetDocumentMetaDataFields;
    /**
     * @var mixed
     */
    private $isStrictMode;
    /**
     * @var mixed
     */
    private $criteria;
    /**
     * @var mixed
     */
    private $repositoryQualifier;
    /**
     * @var mixed
     */
    private $targetRepository;
    /**
     * @var mixed
     */
    private $globalQueryIdentityMap;
    /**
     * @var mixed
     */
    private $result;
    /**
     * @var mixed
     */
    private $realSelectFields;
    /**
     * @var mixed
     */
    private $extraSelectFields;
    /**
     * @var mixed
     */
    private $currentBenchmark;

    /**
     * @param CachedReader $annotationReader
     */
    public function __construct(CachedReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ats:core:tools:doctrine:query')
            ->setDescription('Runs standard doctrine query')
            ->addArgument('collection', InputArgument::REQUIRED, 'provides traget collection')
            ->addOption('where', 'c', InputOption::VALUE_REQUIRED, 'provides query criteria')
            ->addOption('select', null, InputOption::VALUE_REQUIRED, 'provides select() fields')
            ->addOption('aggregate', null, InputOption::VALUE_REQUIRED, 'provides aggregate fields')
            ->addOption('group', null, InputOption::VALUE_REQUIRED, 'provides group fields')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'provides limit to query')
            ->addOption('sort', null, InputOption::VALUE_REQUIRED, 'provides sort priority to query')
            ->addOption('serialize', null, InputOption::VALUE_REQUIRED, 'specifies inline output format')
            ->addOption('strict', null, InputOption::VALUE_NONE, 'bypasses query transformation');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);

        $executionSteps = [
            'processInput',
            'prepareGroup',
            'prepareSelect',
            'prepareAggregate',
            'prepareSerialize',
            'ensureValidSemantics',
            'generateQueryIdentityMap',
            'seedQuery',
            'sortAndLimit',
            'queryAndMaybeAggregate',
            'render',
            'displayPerformanceStats',
        ];

        foreach ($executionSteps as $executionStep) {
            $eventName = '__GLOB__' . $executionStep;
            $this->initBenchmark($eventName);
            $this->{$executionStep}();
            $this->stopBenchmark($eventName);
        }
    }

    protected function getAggregationShorthands()
    {
        return [
            'SUM' => self::AGGREGATION_SUM_SHORTHAND,
            'AVG' => self::AGGREGATION_AVG_SHORTHAND,
            'MUL' => self::AGGREGATION_MUL_SHORTHAND,
            'MIN' => self::AGGREGATION_MIN_SHORTHAND,
            'MAX' => self::AGGREGATION_MAX_SHORTHAND,
            'CNT' => self::AGGREGATION_CNT_SHORTHAND,
            'FST' => self::AGGREGATION_FST_SHORTHAND,
            'LST' => self::AGGREGATION_LST_SHORTHAND,
        ];
    }

    /**
     * @param array $mongoResult
     * @return mixed
     */
    protected function performAggregation(array $mongoResult)
    {
        $aggregateResult = [];

        foreach ($this->aggregateHandlers as $handler) {
            $aggregateField = $handler['field'];
            $operator = $handler['operator'];

            $this->initBenchmark(
                __FUNCTION__,
                $aggregateField,
                $operator
            );

            if (empty($this->groupFields)) {
                $aggregateSet = array_map(
                    function ($entry) use ($aggregateField) {
                        return $entry[$aggregateField];
                    },
                    $mongoResult
                );

                $aggregateResult[][$aggregateField] = $this->aggregate(
                    $aggregateSet,
                    $operator
                );
            } else {
                foreach ($this->groupFields as $groupField) {
                    $this->debug('Grouping by ' . $groupField);

                    $aggregateSet = array_map(
                        function ($entry) use ($aggregateField, $groupField) {
                            return [
                                $aggregateField => $entry[$aggregateField],
                                $groupField => $entry[$groupField],
                            ];
                        },
                        $mongoResult
                    );

                    $groupIdentifiers = array_map(
                        function ($entry) use ($groupField) {
                            return $entry[$groupField];
                        },
                        $aggregateSet
                    );

                    $groupIdentifiers = array_values(
                        array_unique($groupIdentifiers)
                    );

                    foreach ($groupIdentifiers as $groupIdentifier) {
                        $groupSet = array_filter(
                            $aggregateSet,
                            function ($entry) use ($groupField, $groupIdentifier) {
                                return $entry[$groupField] == $groupIdentifier;
                            }
                        );

                        $aggregateGroupSet = array_map(
                            function ($entry) use ($aggregateField) {
                                return $entry[$aggregateField];
                            },
                            $groupSet
                        );

                        $aggregateResult[] = [
                            $groupField => $groupIdentifier,
                            $aggregateField => $this->aggregate($aggregateGroupSet, $operator),
                        ];
                    }
                }
            }

            $this->stopBenchmark();
        }

        return $aggregateResult;
    }

    /**
     * @param array $aggregateSet
     * @param $operator
     * @return mixed
     */
    protected function aggregate(array $aggregateSet, $operator)
    {
        if (empty($aggregateSet)) {
            return [];
        }

        switch ($operator) {
            case self::AGGREGATION_SUM_SHORTHAND:
                return array_reduce(
                    $aggregateSet,
                    function ($carry, $item) {
                        $carry += $item;
                        return $carry;
                    }
                );
            case self::AGGREGATION_AVG_SHORTHAND:
                return array_reduce(
                    $aggregateSet,
                    function ($carry, $item) {
                        $carry += $item;
                        return $carry;
                    }
                ) / count($aggregateSet);
            case self::AGGREGATION_MUL_SHORTHAND:
                return array_reduce(
                    $aggregateSet,
                    function ($carry, $item) {
                        $carry *= $item;
                        return $carry;
                    }
                );
            case self::AGGREGATION_MIN_SHORTHAND:
                return min($aggregateSet);
            case self::AGGREGATION_MAX_SHORTHAND:
                return max($aggregateSet);
            case self::AGGREGATION_CNT_SHORTHAND:
                return count($aggregateSet);
            case self::AGGREGATION_FST_SHORTHAND:
                return reset($aggregateSet);
            case self::AGGREGATION_LST_SHORTHAND:
                return end($aggregateSet);
            default:
                break;
        }
    }

    protected function ensureValidSemantics()
    {
        if (count($this->selectFields) && count($this->aggregateHandlers)) {
            throw new \Exception("Cannot perform both aggregation and projection operations");
        }

        if (count($this->selectFields) && count($this->groupFields)) {
            throw new \Exception("Cannot perform grouping operations without aggregation");
        }
    }

    protected function isAggregateQuery()
    {
        return count($this->aggregateHandlers);
    }

    protected function isSelectQuery()
    {
        return count($this->selectFields);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->debug("Initializing ... ");

        /** @var DocumentManager $documentManager */
        $this->documentManager = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $this->terminalFieldsCache = $this->initCache();
        $this->virtualFieldsCache = $this->initCache();

        $this->queryBenchmarker = [
            self::SUBROUTINE_BENCHMARK_CATEGORY => [],
            self::ROUTINE_BENCHMARK_CATEGORY => [],
        ];

        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start(self::ROOT_BENCHMARK_EVENT);

        // Actual fields specified by user

        $this->selectFields = [];
        $this->aggregateHandlers = [];
        $this->groupFields = [];

        // Virtual fields, further expanding association mappings
        // and directly output results

        $this->virtualSelectFields = [];

        // Metaclass for each document referenced in DM
        // These contain repository classes, database collection names
        // and other meta stuff.
        // Output is wrapped in key-value store, key is collection name for fast lookup

        $this->allDocumentMetaData = $this->getMetaData();

        // @Note: Replaced this deprecated call with service autowiring in Ctor
        // This is kept as a reference to the initial intent
        // $this->annotationReader = $this->getContainer()->get('annotation_reader');
    }

    protected function prepareGroup()
    {
        if ($groupFields = $this->input->getOption('group')) {
            // All group fields
            $this->groupFields = $this->inferGroupFields($groupFields);
            $this->debug("Performing grouping on : " . json_encode($this->groupFields));
            $this->virtualSelectFields = $this
                ->inferVirtualSelectFields($this->groupFields);
        }
    }

    protected function prepareSelect()
    {
        if ($selectFields = $this->input->getOption('select')) {
            // All fields in UI layer
            $this->selectFields = explode(',', $selectFields);

            $this->debug("Selecting only these fields : " . json_encode($this->selectFields));

            // All fields outside document schema layer

            $this->virtualSelectFields = $this
                ->inferVirtualSelectFields(
                    $this->selectFields
                );

            $this->debug(
                "Following fields identified as virtual : " .
                json_encode(array_keys($this->virtualSelectFields))
            );
        }
    }

    protected function prepareAggregate()
    {
        if ($aggregateFields = $this->input->getOption('aggregate')) {
            // All aggregate fields
            $this->aggregateHandlers = $this->inferAggregateFieds($aggregateFields);
            $this->debug("Performing aggregate on : " . json_encode($this->aggregateHandlers));
        }
    }

    protected function sortAndLimit()
    {
        $limit = $this->input->getOption('limit');
        $sort = $this->input->getOption('sort');

        if ($limit !== null) {
            $this->qb->limit($limit);
            $this->debug("Limiting query size to " . $limit);
        } else {
            $this->debug("Limiting query size to default value : " . self::DEFAULT_CURSOR_LIMIT);
            $this->qb->limit(self::DEFAULT_CURSOR_LIMIT);
        }

        if ($sort) {
            $sortCriteria = explode(',', $sort);

            foreach ($sortCriteria as $criterion) {
                $sortDirection = substr($criterion, -1);

                if (in_array($sortDirection, $this->getSortShorthands())) {
                    $this->qb->sort(substr($criterion, 0, -1), $this->getSortShorthandDefinition()[$sortDirection]);
                } else {
                    $this->qb->sort($criterion);
                }
            }
        }
    }

    protected function prepareSerialize()
    {
        $this->serializeFormat = self::OPTION_SERIALIZE_TABLE;

        if ($serializeFormat = $this->input->getOption('serialize')) {
            $this->serializeFormat = $serializeFormat;
        }
    }

    protected function processInput()
    {
        // Get collection name, mandatory

        $this->collection = $this->input->getArgument('collection');

        $this->debug("Collection : " . $this->collection);

        // Queries metadata for target document

        $this->targetDocumentMetaData = $this->allDocumentMetaData[$this->collection];

        // All field names for target collection

        $this->targetDocumentMetaDataFields = $this->targetDocumentMetaData->getFieldNames();

        // Experimental, bypasses all mongo-agnostic specific syntax (mostly shorthands)

        $this->isStrictMode = $this->input->getOption('strict');

        $this->debug("Strict Mode : " . ($this->isStrictMode ? 'true' : 'false'));

        $where = $this->input->getOption('where');

        if (!$where) {
            $where = "[]";
        }

        // Query in JSON format
        // Todo : protect against invalid input

        $this->criteria = json_decode($where, true);

        // Repository class, looked up from target DM

        $this->repositoryQualifier = $this->targetDocumentMetaData->getName();

        // Target repository, self-explanatory

        $this->targetRepository = $this->documentManager->getRepository($this->repositoryQualifier);

        // Post-processed query fields,
        // transforms references onto direct queries
    }

    protected function generateQueryIdentityMap()
    {
        $this->globalQueryIdentityMap = $this->doGenerateQueryIdentityMap($this->criteria);

        $this->debug("Generated Query has " . count($this->globalQueryIdentityMap) . " nested \$id fields");
    }

    protected function queryAndMaybeAggregate()
    {
        $result = $this
            ->qb
            ->hydrate(false) // Hydrated results are out of scope for now
            ->getQuery()
            ->execute()
            ->toArray();

        $result = $this->appendVirtualFields($result);

        if ($this->isAggregateQuery()) {
            $result = $this->performAggregation($result);
        }

        $this->result = $result;
    }

    protected function seedQuery()
    {
        // Fields to be passed to QueryBuilder

        $this->realSelectFields = $this->inferRealSelectFields();

        // Fields that are selected *under the hood* to compensate for virtual fields

        $this->extraSelectFields = $this->inferExtraSelectFields();

        $this->qb = $this->targetRepository->createQueryBuilder();

        if (count($this->globalQueryIdentityMap)) {
            $this->qb->field('id')->in($this->globalQueryIdentityMap);
        }

        // Select required fields for Virtual fields + actual fields
        // support by direct association mappings

        foreach ($this->realSelectFields as $field) {
            $this->qb->select($field);
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    public function initBenchmark($params)
    {
        $this->currentBenchmark = implode('.', func_get_args());
        $this->debug('Now starting benchmarking event ' . $this->currentBenchmark);
        $this->stopwatch->start($this->currentBenchmark);

        return $this;
    }

    /**
     * @param $eventName
     */
    public function stopBenchmark($eventName = null)
    {
        $benchmark = $eventName ? $eventName : $this->currentBenchmark;
        $benchmarkCategory = $eventName ?
        self::ROUTINE_BENCHMARK_CATEGORY :
        self::SUBROUTINE_BENCHMARK_CATEGORY;

        $this->debug('Now stopping benchmarking event ' . $benchmark);
        $duration = $this->stopwatch->stop($benchmark)->getDuration();

        $this->queryBenchmarker[$benchmarkCategory][$benchmark] = $duration;
    }

    public function displayPerformanceStats()
    {
        $globalBenchmark = $this->stopwatch->stop(self::ROOT_BENCHMARK_EVENT);

        $this->debug('Performance statistics : ');
        $this->debug(
            sprintf(
                'Terminal field CHit : %s',
                json_encode($this->getCacheEfficiencyInfo($this->terminalFieldsCache))
            )
        );
        $this->debug(
            sprintf(
                'Virtual field CHit : %s',
                json_encode($this->getCacheEfficiencyInfo($this->virtualFieldsCache))
            )
        );

        foreach ($this->queryBenchmarker[self::SUBROUTINE_BENCHMARK_CATEGORY] as $key => $value) {
            $this->debug(
                sprintf(
                    'Query performance for %s : %s ms',
                    $key,
                    $value
                )
            );
        }

        foreach ($this->queryBenchmarker[self::ROUTINE_BENCHMARK_CATEGORY] as $key => $value) {
            $this->debug(
                sprintf(
                    'Query performance for %s : %s ms',
                    $key,
                    $value
                )
            );
        }

        $this->debug(
            sprintf(
                'Total logged per-subroutine performance time : %s ms',
                array_sum($this->queryBenchmarker[self::SUBROUTINE_BENCHMARK_CATEGORY])
            )
        );

        $this->debug(
            sprintf(
                'Total logged per-routine performance time : %s ms',
                array_sum($this->queryBenchmarker[self::ROUTINE_BENCHMARK_CATEGORY])
            )
        );

        $this->debug(
            sprintf(
                'Total performance time : %s ms',
                $globalBenchmark->getDuration()
            )
        );
    }

    public function inferExtraSelectFields()
    {
        return array_diff($this->realSelectFields, $this->selectFields);
    }

    public function inferRealSelectFields()
    {
        $result = [];

        foreach ($this->selectFields as $field) {
            $explodedSanitizedField = explode('.', $field);
            $result[] = reset($explodedSanitizedField);
        }

        return array_unique($result);
    }

    /**
     * @param array $entry
     * @param $virtualFieldChain
     * @return mixed
     */
    public function doAppendVirtualFields(array $entry, $virtualFieldChain)
    {
        $identityMapBuffer = [$entry['_id']];

        $associationChain = $virtualFieldChain['associationChain'];

        foreach ($associationChain as $association) {
            $this->debug(
                sprintf(
                    "Virtual field chain : Intermediary lookup of %s within %s with %s ids",
                    $association['fieldName'],
                    $association['owningEntity']->getName(),
                    count($identityMapBuffer)
                )
            );

            $identityMapBuffer = $this->getAssociation(
                $association['owningEntity'],
                $association['fieldName'],
                $identityMapBuffer
            );
        }

        $finalizer = $virtualFieldChain['finalizer'];

        $fieldValue = $this->getField(
            $finalizer['owningEntity'],
            $finalizer['fieldName'],
            $identityMapBuffer
        );

        return $fieldValue;
    }

    /**
     * @param array $mongoResult
     * @return mixed
     */
    public function appendVirtualFields(array $mongoResult)
    {
        $self = $this;

        $mongoResult = array_map(
            function ($entry) use ($self) {
                foreach ($self->virtualSelectFields as $key => $virtualFieldChain) {
                    $this->debug(
                        sprintf(
                            'Appending Virtual Field %s for %s',
                            $key,
                            $entry['_id']->__toString()
                        )
                    );

                    $entry[$key] = $this
                        ->doAppendVirtualFields(
                            $entry,
                            $virtualFieldChain
                        );
                }

                return $entry;
            },
            $mongoResult
        );

        return $mongoResult;
    }

    /**
     * @param array $mongoEntry
     * @return mixed
     */
    public function humanizeMongoEntry(array $mongoEntry)
    {
        $normalizedEntry = [];
        unset($mongoEntry['_id']);

        foreach ($mongoEntry as $fieldName => $elem) {
            switch (true) {
                case $elem instanceof \MongoDate:
                    $normalizedEntry[$fieldName] = $elem->toDateTime()->format('Y-m-d H:i:s');
                    break;
                case $this->targetDocumentMetaData->hasAssociation($fieldName):
                    $normalizedEntry[$fieldName] = $this->stringifyAssociation(
                        $elem,
                        $fieldName
                    );
                    break;
                case is_array($elem):
                    if (count($elem) == 1) {
                        $normalizedEntry[$fieldName] = implode(',', $elem);
                    } else {
                        $normalizedEntry[$fieldName] = json_encode($elem);
                    }
                    break;
                default:
                    $normalizedEntry[$fieldName] = $elem;
                    break;
            }
        }

        return $normalizedEntry;
    }

    /**
     * @param array $mongoResult
     * @return mixed
     */
    public function humanizeGlobalMongoResult(array $mongoResult)
    {
        $this->debug(
            'Preparing output for table display ..'
        );

        $result = [];

        $allMissingFields = [];

        foreach ($mongoResult as $entry) {
            $this->debug('Normalizing entry ' . $entry['_id']);

            $normalizedEntry = $this->humanizeMongoEntry($entry);

            $missingFields = array_diff($this->targetDocumentMetaDataFields, array_keys($entry));
            $allMissingFields[] = $missingFields;

            foreach ($missingFields as $field) {
                $normalizedEntry[$field] = null;
            }

            foreach ($this->extraSelectFields as $field) {
                $normalizedEntry[$field] = null;
            }

            $result[] = $normalizedEntry;
        }

        $this->debug('Resolving missing fields ..');

        if (count($allMissingFields) > 1) {
            $missingFieldsPostNormalization = call_user_func_array('array_intersect', $allMissingFields);
        } else {
            $missingFieldsPostNormalization = reset($allMissingFields);
        }

        $this->debug('Missing fields are : ' . implode(', ', $missingFieldsPostNormalization));

        $finalResult = [];

        foreach ($result as $entry) {
            $finalResult[] = array_diff_key(
                $entry,
                array_flip(
                    array_merge(
                        $this->extraSelectFields,
                        $missingFieldsPostNormalization
                    )
                )
            );
            ksort($finalResult);
        }

        return $finalResult;
    }

    public function render()
    {
        $mongoResult = $this->result;

        $headers = [];
        $rows = [];

        if (count($mongoResult)) {
            if ($this->isSelectQuery()) {
                $rows = $this->humanizeGlobalMongoResult($mongoResult);
                $headers = array_keys(reset($rows));
            }
            if ($this->isAggregateQuery()) {
                $rows = array_values($mongoResult);
                $headers = array_keys(reset($mongoResult));
            }
        }

        switch ($this->serializeFormat) {
            case self::OPTION_SERIALIZE_TABLE:
                $table = new Table($this->output);
                $table
                    ->setHeaders(
                        array_unique(
                            array_merge(
                                $headers,
                                array_keys($this->virtualSelectFields)
                            )
                        )
                    )
                    ->setRows($rows);
                $table->render();
                break;

            case self::OPTION_SERIALIZE_JSON:
                $this->output->writeln(
                    json_encode($rows)
                );
                break;
            case self::OPTION_SERIALIZE_CSV:
                $this->output->writeln(implode(self::CSV_SEPERATOR, $headers));
                $this->output->writeln(
                    array_map(
                        function ($row) {
                            return implode(self::CSV_SEPERATOR, $row);
                        },
                        $rows
                    )
                );
                break;
            default:
                break;
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function normalizeValue($value)
    {
        if ($this->isStrictMode) {
            return $value;
        }

        switch (true) {
            case is_numeric($value):
                return intval($value);
            case $this->isDateTime($value):
                return new \MongoDate(strtotime($value));
            case is_array($value):
                return json_encode($value);
            default:
                return $value;
        }
    }

    /**
     * @return mixed
     */
    public function getMetaData()
    {
        $result = [];
        $metas = $this->documentManager->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $metaIdentifier = (new \ReflectionClass($meta->getName()))->getShortName();
            $result[$metaIdentifier] = $meta;
        }

        return $result;
    }

    /**
     * @param $targetClass
     */
    public function lookupMetaDataByTargetClass($targetClass)
    {
        $filteredMetaData = array_filter(
            $this->allDocumentMetaData,
            function (ClassMetadataInfo $metadata) use ($targetClass) {
                return in_array(
                    $targetClass,
                    [$metadata->getName(), $metadata->rootDocumentName]
                );
            }
        );

        if (empty($filteredMetaData)) {
            throw new \Exception("Unknown target class " . $targetClass);
        }

        return reset($filteredMetaData);
    }

    /**
     * @param ClassMetadataInfo $meta
     * @param $lookAheadFieldName
     * @return mixed
     */
    public function lookupDiscriminatedTargetClass(ClassMetadataInfo $meta, $lookAheadFieldName)
    {
        foreach ($meta->discriminatorMap as $dField => $dClass) {
            $subMeta = $this->lookupMetaDataByTargetClass($dClass);

            if ($subMeta->hasField($lookAheadFieldName)) {
                return $subMeta;
            }
        }

        return $meta;
    }

    /**
     * @param $selectField
     * @return mixed
     */
    public function processVirtualField($selectField)
    {
        $virtualFieldChain = explode('.', $selectField);

        $owningEntity = $this->targetDocumentMetaData;

        $result = [
            'associationChain' => [],
            'finalizer' => null,
        ];

        foreach ($virtualFieldChain as $index => $field) {
            $this->debug(
                sprintf(
                    'Validating field %s against entity %s',
                    $field,
                    $owningEntity->getName()
                )
            );

            if ($owningEntity->hasField($field)) {
                if ($owningEntity->hasAssociation($field)) {
                    $result['associationChain'][] = [
                        'owningEntity' => $owningEntity,
                        'fieldName' => $field,
                    ];

                    $owningEntity = $this->getMetaDataFromField(
                        $owningEntity,
                        $field,
                        $virtualFieldChain[$index + 1]
                    );
                } else {
                    $result['finalizer'] = [
                        'owningEntity' => $owningEntity,
                        'fieldName' => $field,
                    ];
                }
            } else {
                throw new \Exception(
                    "Invalid select field chain " . json_encode($virtualFieldChain)
                );
            }
        }

        if (!$result['associationChain']) {
            return null;
        }

        return $result;
    }

    /**
     * @param $groupFields
     * @return mixed
     */
    public function inferGroupFields($groupFields)
    {
        $groupFields = explode(',', $groupFields);

        // Todo, ensure group fields are aggregate fields

        return $groupFields;
    }

    /**
     * @param $aggregateFields
     * @return mixed
     */
    public function inferAggregateFieds($aggregateFields)
    {
        $aggregateFields = explode(',', $aggregateFields);

        $result = [];

        foreach ($aggregateFields as $aggregateField) {
            $operator = substr($aggregateField, 0, 1);
            $field = substr($aggregateField, 1);

            if (!in_array($operator, $this->getAggregationShorthands())) {
                throw new \Exception(
                    sprintf(
                        "Invalid or inexistant aggregation operator in field %s",
                        $aggregateField
                    )
                );
            }

            $result[] = [
                'operator' => $operator,
                'field' => $field,
            ];
        }

        return $result;
    }

    /**
     * @param $fields
     */
    public function inferVirtualSelectFields($fields)
    {
        $result = [];

        $this->debug('Now processing virtual fields...');

        foreach ($fields as $selectField) {
            $result[$selectField] = $this->processVirtualField($selectField);
        }

        return array_filter($result);
    }

    /**
     * @param Builder $qb
     * @param $field
     * @param $value
     */
    public function processQuerySubExpression(Builder $qb, $field, $value)
    {
        $processedValue = $value;

        switch (true) {
            case (is_null($value)):
                $qb->field($field)->exists(false);
                break;
            case ($value instanceof \MongoId):
                $qb->field($field)->equals($value->__toString());
                break;
            case is_array($value):
                $qb->field($field)->in($processedValue);
                break;
            case is_numeric($value):
                $processedValue = intval($value);
                $qb->field($field)->equals($processedValue);
                break;
            case $this->isDateTime($value):
                $processedValue = (new \DateTime($value));

                $qb->field($field)->gte(
                    $processedValue->setTime(0, 0, 0)
                );
                $qb->field($field)->lte(
                    $processedValue->setTime(23, 59, 59)
                );
                break;

            case is_string($value):
                if (($rangePos = strpos($value, self::RANGE_SEPERATOR)) !== false) {
                    $processedValue = array_filter(
                        explode(self::RANGE_SEPERATOR, $value)
                    );

                    if (count($processedValue) < 2) {
                        $edge = $this->normalizeValue(reset($processedValue));
                        $operator = $rangePos ? 'gte' : 'lte';
                        $qb->field($field)->{$operator}($edge);
                    } else {
                        $edgeHi = $this->normalizeValue(end($processedValue));
                        $edgeLo = $this->normalizeValue(reset($processedValue));

                        $qb->field($field)->gte($edgeLo);
                        $qb->field($field)->lte($edgeHi);
                    }
                } else {
                    $qb->field($field)->equals(new \MongoRegex('/.*' . $processedValue . '.*/i'));
                }
                break;
            case is_bool($value):
                $qb->field($field)->equals($processedValue);
                break;
            default:
                throw new \Exception(
                    "Unable to handle subExpression : " . json_encode($value)
                );
                break;
        }
    }

    /**
     * @param ClassMetadataInfo $owningEntity
     * @param $fieldName
     * @param $finalizer
     * @return mixed
     */
    public function getField(ClassMetadataInfo $owningEntity, $fieldName, $finalizer)
    {
        $result = null;

        $cacheKey = sprintf(
            '%s.%s.%s',
            $owningEntity->rootDocumentName,
            $fieldName,
            serialize($finalizer)
        );

        $this->initBenchmark(
            __FUNCTION__,
            $owningEntity->rootDocumentName,
            $fieldName
        );

        if ($this->isCacheHit($this->virtualFieldsCache, $cacheKey)) {
            $this->debug('Virtual Field Cache Hit : ' . $cacheKey);
            $this->stopBenchmark();
            return $this->getCache($this->virtualFieldsCache, $cacheKey);
        }

        $this->debug(
            "Initiating Field lookup of " .
            $fieldName .
            " from entity " .
            $owningEntity->getName(),
            "question"
        );

        $qb = $this
            ->documentManager
            ->getRepository($owningEntity->getName())
            ->createQueryBuilder()
            ->field('id')->in($finalizer)
            ->field($fieldName)->exists(true)
            ->select($fieldName)
            ->hydrate(false)
            ->getQuery()
            ->execute();

        if (count($finalizer) > 1) {
            $entities = $qb->toArray();
            if (count($entities)) {
                $result = array_map(
                    function ($entity) use ($fieldName) {
                        return $entity[$fieldName];
                    },
                    array_values($entities)
                );
            }
        } else {
            $entity = $qb->getSingleResult();

            if ($entity) {
                $result = $entity[$fieldName];
            }
        }

        if (!$result) {
            $result = self::MISSING_REFERENCE_LABEL;
        }

        $this->setCache($this->virtualFieldsCache, $cacheKey, $result);
        $this->stopBenchmark();

        return $result;
    }

    /**
     * @param ClassMetadataInfo $owningEntity
     * @param $fieldName
     * @param array $associationMap
     * @return mixed
     */
    public function getAssociation(ClassMetadataInfo $owningEntity, $fieldName, $associationMap = [])
    {
        $this->initBenchmark(
            __FUNCTION__,
            $owningEntity->rootDocumentName,
            $fieldName
        );

        $cacheKey = sprintf(
            '%s.%s.%s',
            $owningEntity->rootDocumentName,
            $fieldName,
            serialize($associationMap)
        );

        if ($this->isCacheHit($this->virtualFieldsCache, $cacheKey)) {
            $this->debug('Virtual Field Cache Hit : ' . $cacheKey);
            $this->stopBenchmark();
            return $this->getCache($this->virtualFieldsCache, $cacheKey);
        }

        if ($owningEntity->hasAssociation($fieldName)) {
            $this->debug(
                "Initiating Association Query of " .
                $fieldName .
                " from entity " .
                $owningEntity->getName(),
                "question"
            );

            if (empty($associationMap)) {
                $this->stopBenchmark();
                return [];
            }

            $qb = $this
                ->documentManager
                ->getRepository($owningEntity->getName())
                ->createQueryBuilder()
                ->hydrate(false)
                ->field($fieldName)->exists(true)
                ->field('id')->in($associationMap)
                ->select($fieldName);

            $rawResult = array_values(
                $qb
                    ->getQuery()
                    ->execute()
                    ->toArray()
            );

            switch (true) {
                case $owningEntity->isSingleValuedAssociation($fieldName):
                    $result =
                        array_values(
                            array_map(
                                function ($elem) use ($fieldName) {
                                    return $elem[$fieldName]['$id'];
                                },
                                $rawResult
                            )
                        )
                    ;
                    break;
                case $owningEntity->isCollectionValuedAssociation($fieldName):
                    $result =
                    $this->flatten(
                        array_map(
                            function ($elem) use ($fieldName) {
                                return array_map(
                                    function ($subElem) {
                                        return $subElem['$id'];
                                    },
                                    $elem[$fieldName]
                                );
                            },
                            $rawResult
                        )
                    );
                    break;
                default:
                    throw new \Exception(
                        "Unhandled Doctrine association between " .
                        $fieldName . " and " . $owningEntity->getName()
                    );
                    break;
            }

            $this->debug("Intermediary Association Query generated " . count($result) . " results", "info");

            $result = $this->flatten($result);
            $this->setCache($this->virtualFieldsCache, $cacheKey, $result);

            $this->stopBenchmark();

            return $result;
        } else {
            throw new \Exception(
                sprintf(
                    "Unknown association name %s of %s",
                    $fieldName,
                    $owningEntity->getName()
                )
            );
        }
    }

    /**
     * @param ClassMetadataInfo $owningEntity
     * @param $fieldName
     * @param $value
     * @return mixed
     */
    public function queryField(ClassMetadataInfo $owningEntity, $fieldName, $value)
    {
        if ($owningEntity->hasField($fieldName)) {
            $this->initBenchmark(
                __FUNCTION__,
                $owningEntity->rootDocumentName,
                $fieldName
            );

            $this->debug(
                "Initiating Field Query of " .
                $fieldName .
                " from entity " .
                $owningEntity->getName(),
                "question"
            );

            $qb = $this
                ->documentManager
                ->getRepository($owningEntity->getName())
                ->createQueryBuilder()
                ->hydrate(false)
                ->select('id');

            $this->processQuerySubExpression($qb, $fieldName, $value);

            $result = $this->flatten(
                array_values(
                    $qb
                        ->getQuery()
                        ->execute()
                        ->toArray()
                )
            );

            $this->debug(
                "Intermediary Field Query generated " . count($result) . " results",
                "question"
            );

            $this->stopBenchmark();

            return $result;
        }

        throw new \Exception(
            sprintf(
                "Document %s has no field named %s",
                $owningEntity->getName(),
                $fieldName
            )
        );
    }

    /**
     * @param ClassMetadataInfo $owningEntity
     * @param $fieldName
     * @param array $identityMapBuffer
     * @return mixed
     */
    public function queryAssociation(ClassMetadataInfo $owningEntity, $fieldName, $identityMapBuffer = [])
    {
        if ($owningEntity->hasAssociation($fieldName)) {
            $this->debug(
                "Initiating Association Query of " .
                $fieldName .
                " from entity " .
                $owningEntity->getName(),
                "question"
            );

            $this->initBenchmark(
                __FUNCTION__,
                $owningEntity->rootDocumentName,
                $fieldName
            );

            $qb = $this
                ->documentManager
                ->getRepository($owningEntity->getName())
                ->createQueryBuilder()
                ->hydrate(false)
                ->select('id');

            if (is_array($identityMapBuffer) && !empty($identityMapBuffer)) {
                $qb->field($fieldName . '.id')->in($identityMapBuffer);
            }

            $result = $this->flatten(
                array_values(
                    $qb
                        ->getQuery()
                        ->execute()
                        ->toArray()
                )
            );

            $this->debug("Intermediary Association Query generated " . count($result) . " results", "info");

            $this->stopBenchmark();

            return $result;
        } else {
            throw new \Exception(
                sprintf(
                    "Unknown association name %s of %s",
                    $fieldName,
                    $owningEntity->getName()
                )
            );
        }
    }

    /**
     * @param $criterion
     * @return mixed
     */
    public function preProcessQueryString($criterion)
    {
        $subQueryMetaDataHolder = [];

        $fields = explode('.', $criterion);

        $owningEntity = $this->targetDocumentMetaData;

        $subQueryMetaDataHolder['associationChain'] = [];

        foreach ($fields as $childField) {
            if ($owningEntity->hasField($childField)) {
                if ($owningEntity->hasAssociation($childField)) {
                    $subQueryMetaDataHolder['associationChain'][] = [
                        'owningEntity' => $owningEntity,
                        'fieldName' => $childField,
                    ];

                    $owningEntity = $this->getMetaDataFromField(
                        $owningEntity,
                        $childField
                    );

                    continue;
                }

                $subQueryMetaDataHolder['initializers'] = [
                    'owningEntity' => $owningEntity,
                    'fieldName' => $childField,
                ];
            } else {
                throw new \Exception(
                    sprintf(
                        "Invalid field chain %s for collection %s",
                        $criterion,
                        $this->collection
                    )
                );
            }
        }

        return $subQueryMetaDataHolder;
    }

    /**
     * @param ClassMetadataInfo $owningEntity
     * @param $fieldName
     * @param $lookAheadFieldName
     * @return mixed
     */
    public function getMetaDataFromField(ClassMetadataInfo $owningEntity, $fieldName, $lookAheadFieldName = null)
    {
        $targetClass = $owningEntity->getAssociationTargetClass($fieldName);

        $result = $this->lookupMetaDataByTargetClass($targetClass);

        if ($lookAheadFieldName) {
            $result = $this->lookupDiscriminatedTargetClass($result, $lookAheadFieldName);
        }

        return $result;
    }

    /**
     * @param $criterion
     * @param $criterionMetaData
     * @param $value
     * @param array $queryLimiter
     * @return mixed
     */
    public function processQueryString($criterion, $criterionMetaData, $value, $queryLimiter = [])
    {
        $queryFieldChain = explode('.', $criterion);

        $initializers = $criterionMetaData['initializers'];

        $this->debug(
            "Query Initializer : " .
            json_encode(
                [
                    'owningEntity' => $initializers['owningEntity']->getName(),
                    'fieldName' => $initializers['fieldName'],
                ]
            )
        );

        $identityMapBuffer = $this->queryField(
            $initializers['owningEntity'],
            $initializers['fieldName'],
            $value
        );

        if (empty($identityMapBuffer)) {
            $this->debug(
                "Empty initializer map, bypassing query..."
            );

            return [];
        }

        $associationChain = array_reverse($criterionMetaData['associationChain']);

        $ctr = 0;

        foreach ($associationChain as $index => $association) {
            $ctr++;

            $this->debug(
                sprintf(
                    'Association #%s : %s',
                    $index + 1,
                    json_encode(
                        [
                            'owningEntity' => $association['owningEntity']->getName(),
                            'fieldName' => $association['fieldName'],
                        ]
                    )
                )
            );

            $identityMapBuffer = $this->queryAssociation(
                $association['owningEntity'],
                $association['fieldName'],
                $identityMapBuffer
            );
        }

        return $identityMapBuffer;
    }

    /**
     * @param $criteria
     * @return mixed
     */
    public function doGenerateQueryIdentityMap($criteria)
    {
        $result = [];

        $ctr = 0;

        if (is_array($criteria) && count($criteria)) {
            foreach ($criteria as $criterion => $value) {
                $criterionMetaData = $this->preProcessQueryString($criterion);

                $criterionResults = $this->processQueryString(
                    $criterion,
                    $criterionMetaData,
                    $value,
                    $result
                );

                if (!$ctr) {
                    $result = $criterionResults;
                } else {
                    $this->debug(
                        sprintf(
                            'Attempting to shrink Identity map (%s ids)',
                            count($result)
                        ),
                        'question'
                    );

                    $result = array_intersect(
                        $result,
                        $criterionResults
                    );

                    $this->debug(
                        sprintf(
                            'Finished shrinking Identity Map (%s ids)',
                            count($result)
                        ),
                        'question'
                    );
                }

                $ctr++;
            }
        }

        return $this->flatten(array_values($result));
    }

    /**
     * @param $ref
     * @param $fieldName
     * @return mixed
     */
    public function stringifyAssociation($ref, $fieldName)
    {
        // Else clauses implicitly assume it's embed association.

        if ($this->targetDocumentMetaData->isSingleValuedAssociation($fieldName)) {
            if ($this->targetDocumentMetaData->isSingleValuedReference($fieldName)) {
                return $this->stringifySingleValuedReference($ref);
            } else {
                return "__SINGLE_EMBED__";
            }
        } else {
            if ($this->targetDocumentMetaData->isCollectionValuedReference($fieldName)) {
                return $this->stringifyCollectionValuedReference($ref);
            } else {
                return "__COLLECTION_EMBED__";
            }
        }
    }

    /**
     * @param $ref
     * @return mixed
     */
    public function stringifySingleValuedReference($ref)
    {
        if (is_null($ref)) {
            return self::MISSING_REFERENCE_LABEL;
        }

        $cacheKey = $ref['$id']->__toString();

        if ($this->isCacheHit($this->terminalFieldsCache, $cacheKey)) {
            $elem = $this->getCache($this->terminalFieldsCache, $cacheKey);
            $this->debug('Terminal Field Cache Hit for single reference ' . $elem);
        } else {
            $this->targetRepository = $this->allDocumentMetaData[$ref['$ref']]->getName();

            $elem = $this
                ->documentManager
                ->getRepository(
                    $this->targetRepository
                )
                ->find($cacheKey);

            if ($elem) {
                $elem = $elem->__toString();
            } else {
                $elem = self::MISSING_REFERENCE_LABEL;
            }

            $this->setCache($this->terminalFieldsCache, $cacheKey, $elem);
        }

        return $elem;
    }

    /**
     * @param $refs
     */
    public function stringifyCollectionValuedReference($refs)
    {
        $result = [];

        if (is_array($refs)) {
            foreach ($refs as $ref) {
                $result[] = $this->stringifySingleValuedReference($ref);
            }
        }

        return json_encode($result);
    }

    public function getSortShorthandDefinition()
    {
        return [self::SORT_SHORTHAND_DESC => 'DESC', self::SORT_SHORTHAND_ASC => 'ASC'];
    }

    public function getSortShorthands()
    {
        return array_keys($this->getSortShorthandDefinition());
    }

    /**
     * @param $kvCache
     * @param $key
     */
    public function isCacheHit(&$kvCache, $key)
    {
        $kvCache[self::INTERNAL_CACHE_REQUEST_COUNTER]++;

        if (array_key_exists($key, $kvCache)) {
            $kvCache[self::INTERNAL_CACHE_HIT_COUNTER]++;
            return true;
        }

        $kvCache[self::INTERNAL_CACHE_MISS_COUNTER]++;

        return false;
    }

    /**
     * @param $cache
     * @return mixed
     */
    public function getCacheEfficiencyInfo($cache)
    {
        $result = [
            'hits' => $cache[self::INTERNAL_CACHE_HIT_COUNTER],
            'misses' => $cache[self::INTERNAL_CACHE_MISS_COUNTER],
            'ratio' => 'N/A',
        ];

        if ($cache[self::INTERNAL_CACHE_HIT_COUNTER]) {
            $result['ratio'] = round(
                100 * $cache[self::INTERNAL_CACHE_HIT_COUNTER] /
                $cache[self::INTERNAL_CACHE_REQUEST_COUNTER]
            ) . '%';
        }

        return $result;
    }

    /**
     * @param $kvCache
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setCache(&$kvCache, $key, $value)
    {
        $kvCache[$key] = $value;

        return $this;
    }

    /**
     * @param $kvCache
     * @param $key
     * @return mixed
     */
    public function getCache($kvCache, $key)
    {
        return $kvCache[$key];
    }

    /**
     * @return mixed
     */
    protected function initCache()
    {
        $cache = [];

        $cache[self::INTERNAL_CACHE_REQUEST_COUNTER] = 0;
        $cache[self::INTERNAL_CACHE_HIT_COUNTER] = 0;
        $cache[self::INTERNAL_CACHE_MISS_COUNTER] = 0;

        return $cache;
    }

    /**
     * @param array $array
     * @return mixed
     */
    protected function flatten(array $array)
    {
        $return = array();
        array_walk_recursive(
            $array,
            function ($a) use (&$return) {
                $return[] = $a;
            }
        );

        return $return;
    }

    /**
     * @param $dateString
     * @param $format
     * @return mixed
     */
    protected function isDateTime($dateString, $format = self::DATETIME_SHORTHAND_FORMAT)
    {
        $date = \DateTime::createFromFormat($format, $dateString);

        return $date && $date->format($format) == $dateString;
    }

    /**
     * @param $message
     * @param $style
     */
    protected function debug($message, $style = 'comment')
    {
        $this->output->writeln(
            sprintf(
                '<%s>%s</%s>',
                $style,
                $message,
                $style
            ),
            OutputInterface::VERBOSITY_VERBOSE
        );
    }
}
