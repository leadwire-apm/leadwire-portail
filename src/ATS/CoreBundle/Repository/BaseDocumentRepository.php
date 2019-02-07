<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
 * BaseDocumentRepository
 *
 * Note: This class should be used as base class for all document repositories
 *
 * @author Ali Turki <aturki@ats-digital.com>
 */
class BaseDocumentRepository extends DocumentRepository
{
    /**
     * Save a document in the database
     *
     * @param mixed $document
     *
     * @return \MongoId
     */
    public function save($document)
    {
        $this->dm->persist($document);
        $this->dm->flush();

        return $document->getId();
    }

    /**
     * Removes a document from the database
     *
     * @param mixed $document
     *
     * @return \MongoId
     */
    public function delete($document)
    {
        $id = $document->getId();
        $this->dm->remove($document);
        $this->dm->flush();

        return $id;
    }

    /**
     * Removes all repository documents from the database
     *
     * @return void
     */
    public function deleteAll()
    {
        $this->createQueryBuilder()
            ->remove()
            ->getQuery()
            ->execute();
    }
}
