<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Command;

use ATS\CoreBundle\Command\Base\BaseCommand;
use ATS\PaymentBundle\Service\CustomerService;
use ATS\PaymentBundle\Service\PaymentService;

class PaymentCommand extends BaseCommand
{
    const DEFAULT_INTERVAL = 1;
    const CMD_NAME = 'ats:payment:test';

    private $paymentService;
    private $customerService;

    public function __construct(PaymentService $paymentService, CustomerService $customerService)
    {
        $this->paymentService = $paymentService;
        $this->customerService = $customerService;
        parent::__construct();
    }

    protected function configure()
    {

        $this->setName(self::CMD_NAME)
            ->setDescription('Creates files and data required by the app.')
            ->setHelp("Courage");
    }

    protected function doExecute()
    {
        //$this->paymentService->useProvider('Stripe');
        $json = json_encode(["name" => "test", "email" => "test123@example.com"]);

        $data = [
            'number' => '4242424242424242',
            'expiryMonth' => '12',
            'expiryYear' => '2020',
            'cvv' => '123',
        ];
        $customer = $this->customerService->newCustomer($json, $data);
// @todo clean  test cmd
//        if ($this->paymentService->purchase($data, 'test123@example.com', '100', 'EUR')) {
//            $this->info("hell yeah");
//        } else {
//            $this->error("could not be executed");
//        }

        if ($response = $this->paymentService->createSubscription(
            "plan_DXfkvylic5gOHx",
            $customer,
            $data
        )
        ) {
            dump($response);
            $this->info("hell yeah");
        } else {
            $this->error("could not be executed");
        }
    }
}
