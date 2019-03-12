<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Document\Plan;
use ATS\PaymentBundle\Document\PricingPlan;
use ATS\PaymentBundle\Manager\PlanManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

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
     * @var CustomStripeGateway
     */
    private $gateway;

    /**
     * Constructor
     *
     * @param PlanManager $planManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param CustomStripeGateway $gateway
     */
    public function __construct(
        PlanManager $planManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        CustomStripeGateway $gateway
    ) {
        $this->planManager = $planManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->gateway = $gateway;
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
     * @return Plan|null
     */
    public function getPlan($id): ?Plan
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
        /** @var ?Plan $plan */
        $plan = $this->planManager->getOneBy(['id' => $id]);

        if ($plan instanceof Plan) {
            $this->gateway->deletePlan(["id" => $plan->getName() . "-month"])->send();
            $this->gateway->deletePlan(["id" => $plan->getName() . "-year"])->send();
            $this->planManager->deleteById($id);
        }
    }

    public function createDefaulPlans()
    {

        $createdPlans = $this->gateway->listPlans()->send()->getData()['data'];

        $first = $this->planManager->getOneBy(['name' => "BASIC"]);
        if (!$first) {
            $first = new Plan();
            $first->setName("BASIC")
                ->setIsCreditCard(false)
                ->setDiscount(0)
                ->setPrice(0)
                ->setMaxTransactionPerDay(10000)
                ->setRetention(1);
            $this->planManager->update($first);
        }

        $second = $this->planManager->getOneBy(['name' => "STANDARD"]);
        if (!$second) {
            $second = new Plan();
            $second->setName("STANDARD")
                ->setIsCreditCard(true)
                ->setDiscount(15)
                ->setPrice(71)
                ->setMaxTransactionPerDay(100000)
                ->setRetention(7);

            /**
             * Monthly Plan
             */

            $stripePlan = array_filter($createdPlans, function ($elem) use ($second) {
                return $elem['id'] === $second->getName() . "-month";
            });

            if (empty($stripePlan) === true) {
                $token = $this->gateway->createPlan([
                    "interval" => 'month',
                    "name" => $second->getName(),
                    "currency" => Plan::CURRENCY_EURO,
                    "amount" => $second->getPrice(),
                    "id" => $second->getName() . "-month",
                ])->send()->getData()['id'];

            } else {
                $token = reset($stripePlan)['id'];
            }

            $pricing = new PricingPlan();
            $pricing->setName("monthly");
            $pricing->setToken($token);
            $second->addPrice($pricing);

            /**
             * Yearly Plan
             */
            $stripePlan = array_filter($createdPlans, function ($elem) use ($second) {
                return $elem['id'] === $second->getName() . "-year";
            });

            if (empty($stripePlan) === true) {
                $token = $this->gateway->createPlan([
                    "interval" => 'year',
                    "name" => $second->getName(),
                    "currency" => Plan::CURRENCY_EURO,
                    "amount" => $second->getYearlyPrice(),
                    "id" => $second->getName() . "-year",
                ])->send()->getData()['id'];
            } else {
                $token = reset($stripePlan)['id'];
            }

            $pricing = new PricingPlan();
            $pricing->setName("yearly");
            $pricing->setToken($token);
            $second->addPrice($pricing);

            $this->planManager->update($second);
        }

        $third = $this->planManager->getOneBy(['name' => "PREMIUM"]);
        if (!$third) {
            $third = new Plan();
            $third->setName("PREMIUM")
                ->setIsCreditCard(true)
                ->setDiscount(15)
                ->setPrice(640)
                ->setMaxTransactionPerDay(1000000)
                ->setRetention(15);

            /**
             * monthly plan
             */

            $stripePlan = array_filter($createdPlans, function ($elem) use ($third) {
                return $elem['id'] === $third->getName() . "-month";
            });

            if (empty($stripePlan) === true) {
                $token = $this->gateway->createPlan([
                    "interval" => 'month',
                    "name" => $third->getName(),
                    "currency" => Plan::CURRENCY_EURO,
                    "amount" => $third->getPrice(),
                    "id" => $third->getName() . "-month",
                ])->send()->getData()['id'];
            } else {
                $token = reset($stripePlan)['id'];
            }

            $pricing = new PricingPlan();
            $pricing->setName("monthly");
            $pricing->setToken($token);
            $third->addPrice($pricing);

            /**
             * Yearly plan
             */

            $stripePlan = array_filter($createdPlans, function ($elem) use ($third) {
                return $elem['id'] === $third->getName() . "-year";
            });
            if (empty($stripePlan) === true) {
                $token = $this->gateway->createPlan([
                    "interval" => 'year',
                    "name" => $third->getName(),
                    "currency" => Plan::CURRENCY_EURO,
                    "amount" => $third->getYearlyPrice(),
                    "id" => $third->getName() . "-year",
                ])->send()->getData()['id'];
            } else {
                $token = reset($stripePlan)['id'];
            }

            $pricing = new PricingPlan();
            $pricing->setName("yearly");
            $pricing->setToken($token);
            $third->addPrice($pricing);

            $this->planManager->update($third);
        }
    }

    /**
     * @see https://stripe.com/docs/api/plans/update
     * ! By design, you cannot change a plan’s ID, amount, currency, or billing cycle.
     *
     * @param string $json
     *
     * @return void
     */
    public function modifyPlan(string $json)
    {
        /** @var Plan $plan */
        $plan = $this->serializer->deserialize($json, Plan::class, 'json');

        $data = [
            "interval" => 'month',
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            "amount" => $plan->getPrice(),
            "id" => $plan->getName() . "-month",
        ];
        // Monthly plan
        $this->gateway->deletePlan($data)->send();
        $this->gateway->createPlan($data)->send();

        $data = [
            "interval" => 'year',
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            "amount" => $plan->getYearlyPrice(),
            "id" => $plan->getName() . "-year",
        ];

        $this->gateway->deletePlan($data)->send();
        $this->gateway->createPlan($data)->send();

        $this->planManager->update($plan);
    }

    public function createNewPlan(string $json)
    {
        /** @var Plan $plan */
        $plan = $this->serializer->deserialize($json, Plan::class, 'json');

        $data = [
            "interval" => 'month',
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            "amount" => $plan->getPrice(),
            "id" => $plan->getName() . "-month",
        ];

        $this->gateway->createPlan($data)->send();

        $data = [
            "interval" => 'year',
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            "amount" => $plan->getYearlyPrice(),
            "id" => $plan->getName() . "-year",
        ];

        $this->gateway->createPlan($data)->send();

        $this->planManager->update($plan);
    }
}
