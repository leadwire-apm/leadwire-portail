<?php declare(strict_types=1);

namespace ATS\PaymentBundle\Service;

use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use ATS\PaymentBundle\Manager\PlanManager;
use ATS\PaymentBundle\Document\Plan;

/**
 * Service class for Plan entities
 *
 */
class PlanService
{
    /**
     * @var PlanManager
     */
    private $planManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param PlanManager $planManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(PlanManager $planManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->planManager = $planManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all plans
     *
     * @return array
     */
    public function listPlans()
    {
        return $this->planManager->getAll();
    }

    /**
     * Paginates through Plans
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->planManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific plan
     *
     * @param string $id
     *
     * @return Plan
     */
    public function getPlan($id)
    {
         return $this->planManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific plans
     *
     * @param string $criteria
     *
     * @return array
     */
    public function getPlans(array $criteria = [])
    {
         return $this->planManager->getBy($criteria);
    }

    /**
     * Creates a new plan from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function newPlan($json)
    {
        $plan = $this
                ->serializer
                ->deserialize($json, Plan::class, 'json');

        return $this->updatePlan($json);
    }

    /**
     * Updates a specific plan from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updatePlan($json)
    {
        $isSuccessful = false;

        try {
            $plan = $this->serializer->deserialize($json, Plan::class, 'json');
            $this->planManager->update($plan);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific plan from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deletePlan($id)
    {
         $this->planManager->deleteById($id);
    }

     /**
      * Performs a full text search on  Plan
      *
      * @param string $term
      * @param string $lang
      *
      * @return array
      */
    public function textSearch($term, $lang)
    {
        return $this->planManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on Plan
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->planManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
    }
}
