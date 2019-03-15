<?php

namespace ATS\PaymentBundle\Message;

use Omnipay\Stripe\Message\AbstractRequest;

class CreateProductRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('name', 'type');

        $data = [
            'name' => $this->getName(),
            'type' => $this->getType(),
        ];

        return $data;
    }

    public function getName()
    {
        return $this->getParameter('name');
    }

    public function setName($name)
    {
        $this->setParameter('name', $name);
    }

    public function getType()
    {
        return $this->getParameter('type');
    }

    public function setType($type)
    {
        $this->setParameter('type', $type);
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/products';
    }
}
