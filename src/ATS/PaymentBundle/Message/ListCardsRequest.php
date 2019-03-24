<?php

/**
 * Stripe List Invoices Request.
 */
namespace ATS\PaymentBundle\Message;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe List Invoices Request.
 *
 * @see Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#list_invoices
 */
class ListCardsRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('customerReference');
        return;
    }

    public function getEndpoint()
    {
        $endpoint = $this->endpoint . '/customers/' . $this->getCustomerReference() . '/sources?object=card';

        return $endpoint;
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
