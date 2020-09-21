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
     * @param string $applicationId
     * @param string $dashboardId
     *
     * @return Dashboard
     */
    public function getOrCreateDashboard($applicationId, $dashboardId, $visible = true)
    {
        /** @var Dashboard $dashboard */
        $dashboard = $this->getDocumentRepository()->findOneBy(['applicationId' => $applicationId, 'dashboardId' => $dashboardId]);

        if ($dashboard === null) {
            $dashboard = $this->create($applicationId, $dashboardId, $visible);
        }

        return $dashboard;
    }

    /**
     * Get Dashboard 
     *
     * @param string $applicationId
     * @param string $dashboardId
     *
     * @return Dashboard
     */
    public function getDashboard($applicationId, $dashboardId)
    {
        return $this->getDocumentRepository()->findOneBy(['applicationId' => $applicationId, 'dashboardId' => $dashboardId]);
    }

    /**
     *
     * @param string $applicationId
     * @param string $dashboardId
     * @param boolean $visible
     *
     * @return Dashboard
     */
    public function create($applicationId, $dashboardId, $visible = true): Dashboard
    {
        $dashboard = new Dashboard();
        $dashboard->setApplicationId($applicationId)
            ->setDashboardId($dashboardId)
            ->setVisible($visible);

        $this->update($dashboard);

        return $dashboard;
    }
}
