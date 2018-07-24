<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Repository;

use ATS\CoreBundle\Repository\BaseDocumentRepository;

/**
 * Repository class for Option entities
 *
 * @see \ATS\CoreBundle\Repository\BaseDocumentRepository
 */
class TranslationEntryRepository extends BaseDocumentRepository
{
    public function getAllKeys()
    {
        return $this->createQueryBuilder()
            ->find()
            ->select('key')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }
}
