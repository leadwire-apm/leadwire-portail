<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Http;

use GuzzleHttp\Client;

/**
 * Class GuzzleClient
 * Helper class to expose Guzzle Client as a Symfony Service.
 * This service is autowired
 */
class GuzzleClient extends Client
{

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }
}
