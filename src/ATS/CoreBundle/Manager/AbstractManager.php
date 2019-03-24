<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Manager;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\CoreBundle\Repository\BaseDocumentRepository;

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
     * @var string|null
     */
    private $managerName;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     *
     * @param ManagerRegistry $managerRegistry
     * @param string $documentClass
     * @param string|null $managerName
     *
     */
    public function __construct(ManagerRegistry $managerRegistry, $documentClass = '', $managerName = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->documentClass = $documentClass;
        $this->managerName = $managerName;
        $this->documentManager = $managerRegistry->getManager($managerName);
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
     * @return array
     */
    public function paginate(
        array $criteria = [],
        $pageNumber = 1,
        $itemsPerPage = 20,
        $sortOrder = 'DESC',
        $sortableField = 'id'
    ) {
        return $this
            ->getDocumentRepository()
            ->findBy($criteria, [$sortableField => $sortOrder], $itemsPerPage, ($pageNumber - 1) * $itemsPerPage);
    }

    /**
     * @param mixed $document
     *
     * @return \MongoId
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
        $documentManager = $this->managerRegistry->getManager($this->managerName);
        foreach ($documents as $document) {
            $documentManager->persist($document);
        }

        $documentManager->flush();
    }

    /**
     * @param mixed $document
     *
     * @return \MongoId
     */
    public function delete($document)
    {
        return $this
            ->getDocumentRepository()
            ->delete($document);
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function deleteById($id)
    {
        $document = $this->getOneBy(['id' => $id]);

        if ($document !== null) {
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
     * @param array      $orderBy
     * @param integer    $limit
     * @param integer    $offset
     *
     * @return array
     */
    public function getBy(array $criteria, array $orderBy = null, $limit = 0, $offset = 0)
    {
        return $this
            ->getDocumentRepository()
            ->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Performs a text search on Text-indexed fields
     *
     * @param string $text
     * @param string $language
     *
     * @return mixed
     */
    public function textSearch($text, $language = 'english')
    {
        return $this->getDocumentRepository()->textSearch($text, $language);
    }

    /**
     * @param array $criteria
     *
     * @return mixed
     */
    public function getOneBy(array $criteria)
    {
        $results = $this->getBy($criteria);
        return count($results) === 0 ? null : reset($results);
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

    /**
     *
     * @return Builder
     */
    protected function qb()
    {
        return $this->managerRegistry
            ->getManager($this->managerName)
            ->getRepository($this->documentClass)
            ->createQueryBuilder();
    }
}
