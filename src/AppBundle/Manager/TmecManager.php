<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Tmec;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Tmec entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class TmecManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Tmec::class, $managerName);
    }

    /**
     * Get tmec by its version
     *
     * @param string $version
     *
     * @return User
     */
    public function getTmecByVersion($version)
    {
        /** @var Tmec $tmec */
        $tmec = $this->getDocumentRepository()->findOneBy(['version' => $version]);

        return $tmec;
    }

    /**
     * Get tmec by its id
     *
     * @param string $id
     *
     * @return User
     */
    public function getTmecById($id)
    {
        /** @var Tmec $tmec */
        $tmec = $this->getDocumentRepository()->findOneBy(['id' => $id]);

        return $tmec;
    }

    /**
     * Get tmec by its application
     *
     * @param string $application
     *
     * @return Tmec
     */
    public function getTmecByApplication($application)
    {
        /** @var Tmec $tmec */
        $tmecList = $this->getDocumentRepository()->findBy(['application' => $application]);
        return $tmecList;
    }

    /**
     *
     * @param string $version
     * @param \DateTime $description
     * @param \DteTime $startDate
     * @param string $endDate
     * @param stirng $application
     *
     * @return Tmec
     */
    public function create($version, $description, $startDate, $endDate, $application): Tmec
    {
        $tmec = new Tmec();
        $tmec
            ->setVersion($version)
            ->setDescription($description)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setApplication($application);

        $this->update($tmec);

        return $tmec;
    }
}
