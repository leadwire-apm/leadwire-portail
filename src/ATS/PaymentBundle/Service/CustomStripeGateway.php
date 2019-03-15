<?php


namespace ATS\PaymentBundle\Service;

use Omnipay\Stripe\Gateway;
use ATS\PaymentBundle\Message\CreatePlanRequest;
use ATS\PaymentBundle\Message\ListInvoicesRequest;
use ATS\PaymentBundle\Message\UpdateSubscriptionRequest;
use ATS\PaymentBundle\Message\UpdatePlanRequest;
use Omnipay\Stripe\Message\DeletePlanRequest;
use ATS\PaymentBundle\Message\CreateProductRequest;



class CustomStripeGateway extends Gateway
{

    public function __construct(string $apiKey)
    {
        parent::__construct();

        $this->setApiKey($apiKey);
    }
    /**
     * Create Plan
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\CreatePlanRequest
     */
    public function createPlan(array $parameters = array())
    {
        return $this->createRequest(CreatePlanRequest::class, $parameters);
    }

    /**
     * List Invoices
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\ListInvoicesRequest
     */
    public function listInvoices(array $parameters = array())
    {
        return $this->createRequest(ListInvoicesRequest::class, $parameters);
    }

    /**
     * Update Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\UpdateSubscriptionRequest
     */
    public function updateSubscription(array $parameters = array())
    {
        return $this->createRequest(UpdateSubscriptionRequest::class, $parameters);
    }

    public function updatePlan(array $parameters = [])
    {
        $this->createRequest(DeletePlanRequest::class, $parameters);
        // return $this->createRequest(UpdatePlanRequest::class, $parameters);
    }

    public function createProduct(array $parameters = [])
    {
        return $this->createRequest(CreateProductRequest::class, $parameters);
    }
}