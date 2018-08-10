<?php declare(strict_types=1);

namespace ATS\AdminBundle\Manager;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * AdminManager
 *
 * @author Ali Turki <aturki@ats-digital.com>
 */
class AdminManager
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
     * @throws
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
        $qb = $this
            ->getDocumentRepository()
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

    public function getPageCount($itemsPerPage)
    {
        return ceil($this->count() / $itemsPerPage);
    }

    public function count()
    {
        return $this
            ->getDocumentRepository()
            ->createQueryBuilder()
            ->getQuery()
            ->execute()
            ->count();
    }

    /**
     * @param $document
     */
    public function update($document)
    {
        $this->managerRegistry
            ->getManager($this->managerName)
            ->persist($document);

        $this->managerRegistry
            ->getManager($this->managerName)
            ->flush();
    }

    /**
     * @param $document
     */
    public function delete($document)
    {
        $this->getDocumentRepository()->delete($document);
    }

    public function deleteById($id)
    {
        $document = $this->getOneBy(['id' => $id]);

        if ($document) {
            $this->delete($document);
        }
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
     * @return ObjectRepository
     */
    protected function getDocumentRepository()
    {
        return $this->managerRegistry
                    ->getManager($this->managerName)
                    ->getRepository($this->documentClass);
    }

    public function getDocumentClass()
    {
        return $this->documentClass;
    }
}
