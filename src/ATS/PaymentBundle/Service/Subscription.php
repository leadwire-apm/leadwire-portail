<?php declare (strict_types = 1);
namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Document\Customer;
use ATS\PaymentBundle\Exception\OmnipayException;
use ATS\PaymentBundle\Service\CustomStripeGateway;
use Psr\Log\LoggerInterface;

class Subscription
{
    /**
     * @var CustomStripeGateway
     */
    private $gateway;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PaymentService constructor.
     * @param LoggerInterface $logger
     * @param CustomStripeGateway $gateWay
     */
    public function __construct(
        LoggerInterface $logger,
        CustomStripeGateway $gateWay
    ) {
        $this->logger = $logger;
        $this->gateway = $gateWay;
    }

    /**
     * @param $subscriptionName
     * @param Customer $customer
     *
     * @return mixed
     * @throws OmnipayException
     */
    public function create($subscriptionName, Customer $customer)
    {
        return $this->request(
            'createSubscription',
            [
                'plan' => $subscriptionName,
                "customerReference" => $customer->getGatewayToken(),
            ]
        )['id'];
    }

    /**
     * @param string $sub
     * @param Customer $customer
     * @return array | bool
     * @throws OmnipayException
     */
    public function get(string $sub, Customer $customer)
    {
        return $this->request(
            'fetchSubscription',
            ['subscriptionReference' => $sub, 'customerReference' => $customer->getGatewayToken()]
        );
    }

    /**
     * @param string $customerReference
     * @param string $subscriptionReference
     * @param string $planReference
     * @param string $anchor
     * @return array
     * @throws OmnipayException
     */
    public function update(
        string $customerReference,
        string $subscriptionReference,
        string $planReference,
        $anchor
    ) {
        return $this->request(
            'updateSubscription',
            [
                'customerReference' => $customerReference,
                'subscriptionReference' => $subscriptionReference,
                'plan' => $planReference,
                "anchor" => $anchor,
            ]
        );
    }

    /**
     * @param string $subscriptionReference
     * @param string $customerReference
     * @param bool $atPeriodEnd
     * @return array
     * @throws OmnipayException
     */
    public function delete(string $subscriptionReference, string $customerReference, bool $atPeriodEnd = true)
    {
        return $this->request(
            'cancelSubscription',
            [
                'subscriptionReference' => $subscriptionReference,
                '//atPeriodEnd' => $atPeriodEnd,
                'customerReference' => $customerReference,
            ]
        );
    }

    /**
     * @param $functionName
     * @param $parameters
     * @return array
     * @throws OmnipayException
     */
    private function request($functionName, $parameters)
    {
        $response = $this->gateway->{$functionName}($parameters)->send();

        if ($response->isSuccessful() === true) {
            return $response->getData();
        } else {
            $this->logger->error($response->getMessage());
            throw new OmnipayException($response->getMessage());
        }
    }
}
