<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Exception\OmnipayException;
use Monolog\Logger;
use ATS\PaymentBundle\Service\GateWay;
use Omnipay\Common\CreditCard;
use ATS\PaymentBundle\Document\Customer;
use ATS\PaymentBundle\Service\CustomerService;
use ATS\PaymentBundle\Exception\CustomerNotRecognizedException;
use Psr\Log\LoggerInterface;

class PaymentService
{
    /**
     * @var \ATS\PaymentBundle\Service\CustomerService
     */
    private $customerService;

    /**
     * @var \ATS\PaymentBundle\Service\GateWay
     */
    private $gateway;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * PaymentService constructor.
     * @param \ATS\PaymentBundle\Service\CustomerService $customerService
     * @param LoggerInterface $logger
     * @param GateWay $gateWay
     */
    public function __construct(
        CustomerService $customerService,
        LoggerInterface $logger,
        GateWay $gateWay
    ) {
        $this->customerService = $customerService;
        $this->logger = $logger;
        $this->gateway = $gateWay;
    }


    public function purchase($creditCardData, $customerEmail, $amount, $currency)
    {
        /** @var Customer $customer */
        $customer = $this->customerService->getCustomerByEmail($customerEmail);

        if ($customer) {
            $card = new CreditCard($creditCardData);

            $response = $this
                ->gateway
                ->createToken(
                    [
                        'card' => $card,
                    ]
                )
                ->send();

            if ($response->isSuccessful()) {
                $cardToken = $response->getToken();
                $response = $this->gateway->purchase(
                    [
                        'amount' => $amount,
                        'currency' => $currency,
                        'token' => $cardToken,
                    ]
                )->send();
                return true;
            } else {
                $this->logger->error($response->getMessage());
                throw new OmnipayException($response->getMessage());
            }
        } else {
            return false;
        }
    }

    /**
     * @param $subscriptionName
     * @param Customer $customer
     * @return string | bool
     *
     * @throws OmnipayException
     */
    public function createSubscription($subscriptionName, Customer $customer)
    {
        $response = $this->gateway->createSubscription([
            'plan' => $subscriptionName,
            "customerReference" => $customer->getGatewayToken(),
        ])->send();

        if (!$response->isSuccessful()) {
            $this->logger->critical($response->getMessage());
            throw new OmnipayException($response->getMessage());
        } else {
            return $response->getData()['id'];
        }
    }

    /**
     * @param string $sub
     * @param Customer $customer
     * @return array | bool
     * @throws OmnipayException
     */
    public function fetchSubscription(string $sub, Customer $customer)
    {
        $response = $this->gateway->fetchSubscription(
            ['subscriptionReference' => $sub, 'customerReference' =>  $customer->getGatewayToken()]
        )->send();

        if ($response->isSuccessful()) {
            return $response->getData();
        } else {
            $this->logger->error($response->getMessage());
            throw new OmnipayException($response->getMessage());
        }
    }

    /**
     * @param string $customerReference
     * @param string $subscriptionReference
     * @param string $planReference
     * @return mixed
     * @throws OmnipayException
     */
    public function updateSubscription(string $customerReference, string $subscriptionReference, string $planReference)
    {
        $response = $this->gateway->updateSubscription([
            'customerReference' => $customerReference,
            'subscriptionReference' => $subscriptionReference,
            'plan' => $planReference
        ])->send();

        if ($response->isSuccessful()) {
            return $response->getData();
        } else {
            $this->logger->error($response->getMessage());
            throw new OmnipayException($response->getMessage());
        }
    }
}
