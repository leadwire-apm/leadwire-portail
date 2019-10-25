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
     * @param string $dashboardId
     *
     * @return Dashboard
     */
    public function getOrCreateDashboard($userId, $applicationId, $dashboardId, $visible = true)
    {
        /** @var Dashboard $dashboard */
        $dashboard = $this->getDocumentRepository()->findOneBy(['applicationId' => $applicationId, 'userId' => $userId, 'dashboardId' => $dashboardId]);

        if ($dashboard === null) {
            $dashboard = $this->create($userId, $applicationId, $dashboardId, $visible);
        }

        return $dashboard;
    }

    /**
     * Get Dashboard 
     *
     * @param string $userId
     * @param string $applicationId
     * @param string $dashboardId
     *
     * @return Dashboard
     */
    public function getDashboard($userId, $applicationId, $dashboardId)
    {
        return $this->getDocumentRepository()->findOneBy(['applicationId' => $applicationId, 'userId' => $userId, 'dashboardId' => $dashboardId]);
    }

    /**
     *
     * @param string $userId
     * @param string $applicationId
     * @param string $dashboardId
     * @param boolean $visible
     *
     * @return Dashboard
     */
    public function create($userId, $applicationId, $dashboardId, $visible = true): Dashboard
    {
        $dashboard = new Dashboard();
        $dashboard
            ->setUserId($userId)
            ->setApplicationId($applicationId)
            ->setDashboardId($dashboardId)
            ->setVisible($visible);

        $this->update($dashboard);

        return $dashboard;
    }
}
