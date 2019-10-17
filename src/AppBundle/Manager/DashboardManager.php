<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Dashboard;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Dashboar entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class DashboardManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Dashboard::class, $managerName);
    }

    /**
     * Get Dashboard 
     *
     * @param string $userId
     * @param string $applicationId
     * @param string $tenant
     * @param string $applicationName
     * @param string $name
     * @param boolean $visible
     *
     * @return Dashboard
     */
    public function getDashboard($userId, $applicationId, $tenant, $applicationName, $name, $visible = true)
    {
        /** @var Dashboard $dashboard */
        $dashboard = $this->getDocumentRepository()->findOneBy(['applicationId' => $applicationId, 'userId' => $userId, 'tenant' => $tenant, 'name' => $name, 'applicationName' => $applicationName]);

        if ($dashboard === null) {
            $dashboard = $this->create($userId, $applicationId, $tenant, $applicationName, $name, $visible);
        }

        return $dashboard;
    }

    /**
     *
     * @param string $userId
     * @param string $applicationId
     * @param string $tenant
     * @param string $applicationName
     * @param string $name
     * @param boolean $visible
     *
     * @return Dashboard
     */
    public function create($userId, $applicationId, $tenant, $applicationName, $name, $visible = true): Dashboard
    {
        $dashboard = new Dashboard();
        $dashboard
            ->setUserId($userId)
            ->setApplicationId($applicationId)
            ->setTenant($tenant)
            ->setApplicationName($applicationName)
            ->setName($name)
            ->setVisible($visible);

        $this->update($dashboard);

        return $dashboard;
    }
}
