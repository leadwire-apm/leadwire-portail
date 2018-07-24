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
            ->execute()
        ;
    }

    /**
     * Performs a text search on Text-indexed fields
     *
     * @param string $text
     * @param string $language
     *
     * @return mixed
     */
    public function like($text, $language = 'en')
    {
        return $this
            ->createQueryBuilder()
            ->text($text)
            ->language($language)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }

    /**
     * Returs search result within given dates for selected criteria
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $criteria
     * @param string $dateField
     * @param string $sortDirection
     *
     * @return array
     */
    public function withinDates(
        \DateTime $from,
        \DateTime $to,
        $criteria = [],
        $dateField = 'date',
        $sortDirection = 'DESC'
    ) {
        $_from = clone $from;
        $_to = clone $to;

        $_from->setTime(0, 0, 0);
        $_to->setTime(23, 59, 59);

        $qb = $this
            ->createQueryBuilder()
            ->field($dateField)->gte($_from)
            ->field($dateField)->lte($_to);

        foreach ($criteria as $field => $value) {
            $qb->field($field)->equals($value);
        }

        $qb->sort($dateField, $sortDirection);

        return $qb
            ->getQuery()
            ->execute()
            ->toArray(false);
    }

    /**
     * @param array $criteria
     * @param array $selectFields
     * @return array
     */
    public function noHydrate($criteria = [], $selectFields = [])
    {
        $qb = $this->createQueryBuilder()->hydrate(false);

        if (count($criteria)) {
            foreach ($criteria as $field => $value) {
                $qb->field($field)->equals($value);
            }
        }

        if (count($selectFields)) {
            $qb->select($selectFields);
        }

        return $qb
            ->getQuery()
            ->execute()
            ->toArray(false)
        ;
    }
}
