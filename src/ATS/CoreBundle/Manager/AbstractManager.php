<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Manager;

use ATS\CoreBundle\Repository\BaseDocumentRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * AbstractManager
 *
 * @author Ali Turki <aturki@ats-digital.com>
 */
abstract class AbstractManager
{
    /**
     *
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string
     */
    private $managerName;

    /**
     *
     * @param ManagerRegistry $managerRegistry
     * @param string $documentClass
     * @param string $managerName
     *
     */
    public function __construct(ManagerRegistry $managerRegistry, $documentClass = '', $managerName = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->documentClass = $documentClass;
        $this->managerName = $managerName;
    }

    /**
     * Paginate through a collection of documents
     *
     * @param array $criteria
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param string $sortOrder
     * @param string $sortableField
     *
     *
     * @return array
     */
    public function paginate(
        array $criteria = [],
        $pageNumber = 1,
        $itemsPerPage = 20,
        $sortOrder = 'DESC',
        $sortableField = 'id'
    ) {
        $qb = $this->getDocumentRepository()
            ->createQueryBuilder();

        if (count($criteria) > 0) {
            foreach ($criteria as $field => $value) {
                $qb = $qb->field($field)->equals($value);
            }
        }

        return $qb
            ->sort($sortableField, $sortOrder)
            ->skip(($pageNumber - 1) * $itemsPerPage)
            ->limit($itemsPerPage)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }

    /**
     * @param mixed $document
     */
    public function update($document)
    {
        return $this
            ->getDocumentRepository()
            ->save($document);
    }

    /**
     *
     * @param array $documents
     *
     * @return void
     */
    public function batchUpdate(array $documents)
    {
        foreach ($documents as $document) {
            $this->managerRegistry->getManager($this->managerName)->persist($document);
        }

        $this->managerRegistry->getManager($this->managerName)->flush();
    }

    /**
     * @param mixed $document
     */
    public function delete($document)
    {
        return $this
            ->getDocumentRepository()
            ->delete($document);
    }

    /**
     * @return void
     */

    public function deleteById($id)
    {
        $document = $this->getOneBy(['id' => $id]);

        if ($document) {
            $this->delete($document);
        }
    }

    /**
     * @return void
     */

    public function deleteAll()
    {
        $this->getDocumentRepository()->deleteAll();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->getDocumentRepository()->findAll();
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param integer    $limit
     * @param integer    $offset
     *
     * @return array
     */
    public function getBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getDocumentRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Performs a text search on Text-indexed fields
     *
     * @param string $text
     * @param string $language
     *
     * @return mixed
     */
    public function textSearch($text, $language = 'en')
    {
        return $this->getDocumentRepository()->like($text, $language);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function getOneBy(array $criteria)
    {
        $result = $this->getBy($criteria, null, 1);
        return array_pop($result);
    }

    /**
     * @param array $records
     * @param array $groupFields
     * @param array $valueProcessors
     * @return mixed
     */
    public function groupBy(array $records, array $groupFields = [], array $valueProcessors = [])
    {
        $result = [];
        $getters = [];

        foreach ($groupFields as $groupField) {
            $getters[] = new \ReflectionMethod($this->documentClass, 'get' . ucfirst($groupField));
        }

        foreach ($records as $record) {
            $current = &$result;

            foreach ($getters as $index => $getter) {
                $value = $getter->invoke($record, $getter);

                if (array_key_exists($index, $valueProcessors) && is_callable($valueProcessors[$index])) {
                    $value = $valueProcessors[$index]($value);
                }

                if (!is_int($value) && !is_string($value)) {
                    throw new \Exception(
                        "Unprocessed or improperly processed group value %s",
                        serialize($value)
                    );
                }

                if (!isset($current[$value])) {
                    $current[$value] = null;
                }
                $current = &$current[$value];
            }

            $current[] = $record;
        }

        return $result;
    }

    /**
     * @param array $criteria
     * @param string $groupField
     * @param array $valueProcessors
     * @return array
     */
    public function getAndGroupBy(array $criteria, $groupFields = [], array $valueProcessors = [])
    {
        $records = $this->getBy($criteria);
        return $this->groupBy($records, $groupFields, $valueProcessors);
    }

    /**
     * @return BaseDocumentRepository
     */
    protected function getDocumentRepository()
    {
        return $this->managerRegistry
            ->getManager($this->managerName)
            ->getRepository($this->documentClass);
    }
}
