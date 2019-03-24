<?php

/**
 * Stripe Delete Plan Request.
 */
namespace ATS\PaymentBundle\Message;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe Delete Plan Request.
 *
 * @link https://stripe.com/docs/api/service_products/delete
 */
class DeleteProductRequest extends AbstractRequest
{
    /**
     * Get the plan id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getParameter('id');
    }

    /**
     * Set the plan id.
     *
     * @return DeletePlanRequest provides a fluent interface.
     */
    public function setId($planId)
    {
        return $this->setParameter('id', $planId);
    }

    public function getData()
    {
        $this->validate('id');

        return;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/products/'.$this->getId();
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }
}
