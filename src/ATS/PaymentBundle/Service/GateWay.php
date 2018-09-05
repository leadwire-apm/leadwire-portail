<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use Omnipay\Omnipay;

class GateWay
{
    private $gateway;
    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * OmniPay constructor.
     * @param string $apiKey
     * @param string $secretKey
     * @param string $provider
     */
    public function __construct(
        string $apiKey,
        string $secretKey,
        string $provider
    ) {
        $this->secretKey = $secretKey;
        $this->apiKey = $apiKey;
        $this->useProvider($provider);
    }

    private function useProvider($providerName)
    {
        $this->gateway = Omnipay::create($providerName);
        $this->gateway->setApiKey($this->secretKey);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->gateway,$name), $arguments);
    }
}
