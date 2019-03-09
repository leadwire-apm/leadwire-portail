<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Service;

use ATS\PaymentBundle\Document\Customer;
use ATS\PaymentBundle\Exception\OmnipayException;
use ATS\PaymentBundle\Manager\CustomerManager;
use JMS\Serializer\SerializerInterface;
use Omnipay\Common\CreditCard;
use Psr\Log\LoggerInterface;

/**
 * Service class for Customer entities
 *
 */
class CustomerService
{
    private $customerManager;

    private $serializer;

    private $logger;

    private $gateway;

    /**
     * Constructor
     *
     * @param CustomerManager $customerManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param CustomStripeGateway $gateWay
     */
    public function __construct(
        CustomerManager $customerManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        CustomStripeGateway $gateWay
    ) {
        $this->customerManager = $customerManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->gateway = $gateWay;
    }

    /**
     * List all customers
     *
     * @return array
     */
    public function listCustomers()
    {
        return $this->customerManager->getAll();
    }

    /**
     * Paginates through Customers
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->customerManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific customer
     *
     * @param string $id
     *
     * @return Customer
     */
    public function getCustomer($id)
    {
        return $this->customerManager->getOneBy(['id' => $id]);
    }

    /**
     * Get a specific customer by email
     *
     * @param string $email
     *
     * @return Customer
     */
    public function getCustomerByEmail($email)
    {
        return $this->customerManager->getOneBy(['email' => $email]);
    }

    /**
     * Creates a new customer from JSON data
     *
     * @param string $json
     * @param array $card
     * @return Customer
     * @throws OmnipayException
     */
    public function newCustomer($json, $card)
    {
        $card = new CreditCard($card);
        $id = $this->request('createToken', ['card' => $card])['id'];

        $customer = $this
            ->serializer
            ->deserialize($json, Customer::class, 'json');
        $stripeCustomer  = $this->request(
            'createCustomer',
            ['description' => $customer->getName(), 'email' => $customer->getEmail(), 'source' => $id ]
        );

        $customer->setGatewayToken($stripeCustomer['id']);
        $this->customerManager->update($customer);
        return $customer;
    }

    public function updateCard(Customer $customer, $data)
    {
        $card = new CreditCard($data);
        $id = $this->request('createToken', ['card' => $card])['id'];

        return $this->request(
            'updateCustomer',
            [ 'customerReference' => $customer->getGatewayToken(), 'source' => $id]
        );
    }

    /**
     * Updates a specific customer from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateCustomer($json)
    {
        $isSuccessful = false;

        try {
            $customer = $this->serializer->deserialize($json, Customer::class, 'json');
            $this->customerManager->update($customer);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific customer from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteCustomer($id)
    {
        $this->customerManager->deleteById($id);
    }


    public function getInvoices($customerRef)
    {
        return $this->gateway->listInvoices(
            array(
                'customerReference' => $customerRef,
            )
        )->send()->getList();
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

        if ($response->isSuccessful()) {
            return $response->getData();
        } else {
            $this->logger->error($response->getMessage());
            throw new OmnipayException($response->getMessage());
        }
    }
}
