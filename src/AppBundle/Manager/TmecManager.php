<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Application;
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
     * @param string $application
     *
     * @return Tmec|null
     */
    public function getTmecByVersion($version, $application)
    {
        /** @var ?Tmec $tmec */
        $tmec = $this->getDocumentRepository()->findOneBy(['version' => $version, 'application' => $application]);

        return $tmec;
    }

    /**
     * Get tmec by its id
     *
     * @param string $id
     *
     * @return Tmec|null
     */
    public function getTmecById($id)
    {
        /** @var ?Tmec $tmec */
        $tmec = $this->getDocumentRepository()->findOneBy(['_id' => $id]);

        return $tmec;
    }

    /**
     * Get tmec by its application
     *
     * @param boolean $completed
     * @param array $ids
     *
     * @return array
     */
    public function getTmecByApplication($completed, $ids)
    {
        if ($completed === true) {
            $tmecList = $this->getDocumentRepository()->createQueryBuilder()->find()->field('completed')->field('application')->in($ids)->getQuery()->execute()->toArray(false);
        } else {
            $tmecList = $this->getDocumentRepository()->createQueryBuilder()->find()->field('completed')->equals(false)->field('application')->in($ids)->getQuery()->execute()->toArray(false);
        }
        return $tmecList;
    }

    /**
     *
     * @param string $version
     * @param string $description
     * @param string $startDate
     * @param string $endDate
     * @param string $application
     * @param string $applicationName
     * @param string $user
     * @param string $cp
     * 
     * @param string $testEnvr
     * @param string $nTir
     * @param string $userName
     * @param string $cpName
     *
     * @return Tmec
     */
    public function create($version, $description, $startDate, $endDate, $application, $applicationName, $user, $cp, $testEnvr, $nTir, $userName, $cpName): Tmec
    {
        $tmec = new Tmec();
        $tmec
            ->setVersion($version)
            ->setDescription($description)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setApplication($application)
            ->setApplicationName($applicationName)
            ->setUser($user)
            ->setCp($cp)

            ->setTestEnvr($testEnvr)
            ->setnTir($nTir)
            ->setUserName($userName)
            ->setCpName($cpName)

            ->setCompleted(false);

        $this->update($tmec);

        return $tmec;
    }
}
