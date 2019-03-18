<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Message;

use Omnipay\Stripe\Message\AbstractRequest;

class ListProductsRequest extends AbstractRequest
{
    public function getData()
    {
        return;
    }

    public function getEndpoint()
    {
        $endpoint = $this->endpoint . '/products';

        return $endpoint;
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
