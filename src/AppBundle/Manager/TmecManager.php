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
    public function getUserByVersion($version)
    {
        /** @var Tmec $tmec */
        $tmec = $this->getDocumentRepository()->findOneBy(['version' => $version]);

        return $tmec;
    }

    /**
     *
     * @param string $version
     * @param string $description
     * @param string $startdate
     * @param string $enddate
     * @param stirng $application
     *
     * @return Tmec
     */
    public function create($version, $description, $startdate, $enddate, $application): Tmec
    {
        $tmec = new Tmec();
        $tmec
            ->setVersion($version)
            ->setDescription($description)
            ->setStartDate($startdate)
            ->setEndDate($enddate)
            ->setApplication($application);

        $this->update($tmec);

        return $tmec;
    }
}