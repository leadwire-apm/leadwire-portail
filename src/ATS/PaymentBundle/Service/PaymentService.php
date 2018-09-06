<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Exception\OmnipayException;
use ATS\PaymentBundle\Service\GateWay;
use Omnipay\Common\CreditCard;
use ATS\PaymentBundle\Document\Customer;
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
}
