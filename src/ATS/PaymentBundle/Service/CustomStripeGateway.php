<?php


namespace ATS\PaymentBundle\Service;

use Omnipay\Stripe\Gateway;
use Omnipay\Stripe\Message\DeletePlanRequest;
use ATS\PaymentBundle\Message\CreatePlanRequest;
use ATS\PaymentBundle\Message\UpdatePlanRequest;
use ATS\PaymentBundle\Message\ListInvoicesRequest;
use ATS\PaymentBundle\Message\ListProductsRequest;
use ATS\PaymentBundle\Message\CreateProductRequest;
use ATS\PaymentBundle\Message\DeleteProductRequest;
use ATS\PaymentBundle\Message\UpdateSubscriptionRequest;



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

    public function listProducts(array $parameters = [])
    {
        return $this->createRequest(ListProductsRequest::class, $parameters);
    }

    public function deleteProduct(array $parameters = [])
    {
        return $this->createRequest(DeleteProductRequest::class, $parameters);
    }
}