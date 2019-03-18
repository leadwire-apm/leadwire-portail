<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Document\Plan;
use ATS\PaymentBundle\Document\PricingPlan;
use ATS\PaymentBundle\Manager\PlanManager;
use ATS\PaymentBundle\Service\CustomStripeGateway;
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
            /** @var PricingPlan $pricingPlan */
            foreach ($plan->getPrices() as $pricingPlan) {
                $response = $this->gateway->deletePlan(['id' => $pricingPlan->getToken()])->send()->getData();
            }

            $response = $this->gateway->deleteProduct(['id' => $plan->getStripeId()])->send()->getData();
            $this->planManager->deleteById($id);
        }
    }

    public function createDefaultPlans()
    {
        $stripeProducts = $this->gateway->listProducts()->send()->getData()['data'];

        $basic = $this->planManager->getOneBy(['name' => "BASIC"]);
        if ($basic === null) {
            $basic = new Plan();
            $basic->setName("BASIC")
                ->setIsCreditCard(false)
                ->setDiscount(0)
                ->setPrice(0)
                ->setMaxTransactionPerDay(10000)
                ->setRetention(1);

            $data = [
                "name" => $basic->getName(),
                "type" => 'service',
            ];
            $product = $this->gateway->createProduct($data)->send()->getData();
            $basic->setStripeId($product['id']);
            $this->planManager->update($basic);
        }

        $standard = $this->planManager->getOneBy(['name' => "STANDARD"]);
        if ($standard === null) {
            $standard = new Plan();
            $standard->setName("STANDARD")
                ->setIsCreditCard(true)
                ->setDiscount(15)
                ->setPrice(71)
                ->setMaxTransactionPerDay(100000)
                ->setRetention(7);

            $data = [
                "name" => $standard->getName(),
                "type" => 'service',
            ];

            $product = $this->gateway->createProduct($data)->send()->getData();
            $standard->setStripeId($product['id']);

            /**
             * Monthly STANDARD Plan
             */
            $data = [
                "amount" => $standard->getPrice(),
                "interval" => Plan::FREQUENCY_MONTHLY,
                "name" => $standard->getName(),
                "currency" => Plan::CURRENCY_EURO,
                'product_id' => $product['id'],
            ];

            $plan = $this->gateway->createPlan($data)->send()->getData();

            $pricing = new PricingPlan();
            $pricing->setName('monthly');
            $pricing->setToken($plan['id']);
            $standard->addPrice($pricing);

            /**
             * Yearly STANDARD Plan
             */
            $data = [
                "amount" => $standard->getYearlyPrice(),
                "interval" => Plan::FREQUENCY_YEARLY,
                "name" => $standard->getName(),
                "currency" => Plan::CURRENCY_EURO,
                'product_id' => $product['id'],
            ];

            $plan = $this->gateway->createPlan($data)->send()->getData();

            $pricing = new PricingPlan();
            $pricing->setName('yearly');
            $pricing->setToken($plan['id']);
            $standard->addPrice($pricing);
            $this->planManager->update($standard);
        }

        $premium = $this->planManager->getOneBy(['name' => "PREMIUM"]);

        if ($premium === null) {
            $premium = new Plan();
            $premium->setName("PREMIUM")
                ->setIsCreditCard(true)
                ->setDiscount(15)
                ->setPrice(640)
                ->setMaxTransactionPerDay(1000000)
                ->setRetention(15);

            $data = [
                "name" => $premium->getName(),
                "type" => 'service',
            ];

            $product = $this->gateway->createProduct($data)->send()->getData();
            $premium->setStripeId($product['id']);
            /**
             * Monthly PREMIUM plan
             */
            $data = [
                "amount" => $premium->getPrice(),
                "interval" => Plan::FREQUENCY_MONTHLY,
                "name" => $premium->getName(),
                "currency" => Plan::CURRENCY_EURO,
                'product_id' => $product['id'],
            ];

            $plan = $this->gateway->createPlan($data)->send()->getData();

            $pricing = new PricingPlan();
            $pricing->setName('monthly');
            $pricing->setToken($plan['id']);
            $premium->addPrice($pricing);

            /**
             * Yearly PREMIUM Plan
             */
            $data = [
                "amount" => $premium->getYearlyPrice(),
                "interval" => Plan::FREQUENCY_YEARLY,
                "name" => $premium->getName(),
                "currency" => Plan::CURRENCY_EURO,
                'product_id' => $product['id'],
            ];

            $plan = $this->gateway->createPlan($data)->send()->getData();

            $pricing = new PricingPlan();
            $pricing->setName('yearly');
            $pricing->setToken($plan['id']);
            $premium->addPrice($pricing);
            $this->planManager->update($premium);
        }
    }

    /**
     * @link https://stripe.com/docs/api/plans/update
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

        // Delete previous Plans
        foreach ($plan->getPrices() as $pricingPlan) {
            $data = $this->gateway->deletePlan(['id' => $pricingPlan->getToken()])->send()->getData();
        }
        $plan->setPrices([]);
        // Monthly
        $data = [
            "amount" => $plan->getPrice(),
            "interval" => Plan::FREQUENCY_MONTHLY,
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            'product_id' => $plan->getStripeId(),
        ];

        $pricingPlan = $this->gateway->createPlan($data)->send()->getData();

        $pricing = new PricingPlan();
        $pricing->setName('monthly');
        $pricing->setToken($pricingPlan['id']);
        $plan->addPrice($pricing);

        // Yearly
        $data = [
            "amount" => $plan->getYearlyPrice(),
            "interval" => Plan::FREQUENCY_YEARLY,
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            'product_id' => $plan->getStripeId(),
        ];

        $pricingPlan = $this->gateway->createPlan($data)->send()->getData();

        $pricing = new PricingPlan();
        $pricing->setName('yearly');
        $pricing->setToken($pricingPlan['id']);
        $plan->addPrice($pricing);

        $this->planManager->update($plan);
    }

    public function createPlan(string $json)
    {
        $stripeProducts = $this->gateway->listProducts()->send()->getData();
        /** @var Plan $plan */
        $plan = $this->serializer->deserialize($json, Plan::class, 'json');

        $product = array_filter($stripeProducts['data'], function ($element) use ($plan) {return $element['name'] === $plan->getName();});
        $product = reset($product);

        if (empty($product) === true) {
            $data = [
                "name" => $plan->getName(),
                "type" => 'service',
            ];

            $product = $this->gateway->createProduct($data)->send()->getData();
            $plan->setStripeId($data['id']);
        }

        $data = [
            "amount" => $plan->getPrice(),
            "interval" => Plan::FREQUENCY_MONTHLY,
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            'product_id' => $product['id'],
        ];

        $data = $this->gateway->createPlan($data)->send()->getData();

        $pricing = new PricingPlan();
        $pricing->setName('monthly');
        $pricing->setToken($data['id']);
        $plan->addPrice($pricing);

        $data = [
            "amount" => $plan->getYearlyPrice(),
            "interval" => Plan::FREQUENCY_YEARLY,
            "name" => $plan->getName(),
            "currency" => Plan::CURRENCY_EURO,
            'product_id' => $product['id'],
        ];

        $data = $this->gateway->createPlan($data)->send()->getData();

        $pricing = new PricingPlan();
        $pricing->setName('yearly');
        $pricing->setToken($data['id']);
        $plan->addPrice($pricing);

        $this->planManager->update($plan);
    }

    public function deleteAllPlans()
    {
        $plans = $this->planManager->getAll();

        foreach ($plans as $plan) {
            foreach ($plan->getPrices() as $pricingPlan) {
                $code = $this->gateway->deletePlan(['id' => $pricingPlan->getToken()])->send();
            }

            if ($plan->getStripeId() !== null) {
                $code = $this->gateway->deleteProduct(['id' => $plan->getStripeId()])->send();
            }
        }
    }
}
