<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Exporter;

use ATS\CoreBundle\Service\Exporter\CsvFormatter;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\GetAttrNode;

class Exporter
{
    const FORMAT_CSV = 'csv';
    const FORMAT_XLS = 'xls';

    private $expressionLanguage;

    private $formatter;

    private $documentManager;

    private $schema;

    private $entity;

    private $filter;

    /**
     * @var array
     */
    private $lines;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->documentManager = $managerRegistry->getManager();
        $this->expressionLanguage = new ExpressionLanguage();
        $this->formatter = null;
        $this->lines = [];
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function setFormat($format)
    {
        switch ($format) {
            case self::FORMAT_CSV:
                $this->formatter = new CsvFormatter();
                break;
            default:
                throw new \Exception("Unknown format ($format)");
                break;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function export()
    {
        // Reset previous result
        $this->lines = [];

        $preCompiled = $this->precompileSchema();
        $data = $this->prefetch($preCompiled['exportable']);

        foreach ($data as $item) {
            if ($item == null) {
                continue;
            }

            $line = [];

            foreach ($preCompiled['schema'] as $propertyChain) {
                $line[] = $this->expressionLanguage->evaluate(
                    $propertyChain,
                    array(
                        $preCompiled['exportable'] => $item,
                    )
                );
            }

            $this->lines[] = $line;
        }

        return $this;
    }

    public function getFile($filename)
    {
        throw new \Exception('Not yet supported');
        // return $this->formatter->format($lines);
    }

    public function getRawData()
    {
        return $this->lines;
    }

    private function precompileSchema()
    {
        $newSchema = [];
        $self = $this;
        $exportable = explode('.', $this->schema[0])[0];

        foreach ($this->schema as $propertyChain) {
            $attributes = explode('.', $propertyChain);
            array_shift($attributes);
            $attributes = array_map(function ($item) use ($self) {
                return $self->getter($item);
            }, $attributes);

            $newSchema[] = $exportable . '.' . implode('.', $attributes);
        }

        return [
            'exportable' => $exportable,
            'schema' => $newSchema,
        ];
    }

    private function getter($item)
    {
        return 'get' . ucfirst($item) . '()';
    }

    private function prefetch($exportable)
    {
        $qb = $this
            ->documentManager
            ->getRepository($this->entity)
            ->createQueryBuilder()
            ->find()
        ;

        if ($this->filter) {
            $ast = (new ExpressionLanguage())
                ->parse($this->filter, array($exportable))
                ->getNodes()
                ->toArray();

            $qb = $this->astToQueryBuilder($qb, $ast, $exportable);
        }

        $data = $qb
            ->getQuery()
            ->execute()
            ->toArray()
        ;

        return $data;
    }

    private function astToQueryBuilder($qb, $ast, $exportable)
    {
        $info = [];
        foreach ($ast as $node) {
            $field = null;
            if ($node instanceof BinaryNode) {
                $qb = $this->astToQueryBuilder($qb, $node->toArray(), $exportable);
            }
            if ($node instanceof GetAttrNode) {
                if (isset($node->nodes["node"]->attributes['name'])) {
                    $info['field'] = $node->nodes['attribute']->attributes['value'];
                } else {
                    $info['field'] =
                    $node->nodes["node"]->nodes["attribute"]->attributes["value"] .
                    '.' .
                    $node->nodes['attribute']->attributes['value'];
                }
            }
            if (is_string($node) && in_array(trim($node), ['==', '!='])) {
                $info['op'] = trim($node);
            }
            if ($node instanceof ConstantNode) {
                $info['value'] = $node->attributes['value'];
            }

            if (count($info) == 3) {
                $qb = $qb->field($info['field']);

                if ($info['op'] == '==') {
                    $qb = $qb->equals($info['value']);
                }

                if ($info['op'] == '!=') {
                    $qb = $qb->notEquals($info['value']);
                }

                $info = [];
            }
        }

        return $qb;
    }

    public static function getSupportedFormats()
    {
        return [
            self::FORMAT_CSV,
        ];
    }
}
