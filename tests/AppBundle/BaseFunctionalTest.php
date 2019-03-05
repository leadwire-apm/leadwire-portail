<?php declare(strict_types=1);

namespace Tests\AppBundle;

use Firebase\JWT\JWT;
use JMS\serializer\Serializer;
use AppBundle\Manager\UserManager;
use AppBundle\Service\UserService;
use JMS\Serializer\SerializerInterface;
use AppBundle\Service\ApplicationService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

/**
 * KernelTestCase
 *
 * @author Wajih WERIEMI <wweriemi@ats-digital.com>
 */
abstract class BaseFunctionalTest extends BaseKernelTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var ApplicationService
     */
    protected $applicationService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->documentManager = $this->container->get('doctrine_mongodb.odm.document_manager');
        $this->serializer = $this->container->get('jms_serializer');
        $this->userManager = $this->container->get(UserManager::class);
        $this->userService = $this->container->get(UserService::class);

        $this->applicationService = $this->container->get(ApplicationService::class);

        $this->documentManager->getSchemaManager()->dropDatabases();
        $this->documentManager->getSchemaManager()->updateIndexes();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Invoke method to test protected methods by reflection
     *
     * @param mixed  $object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function generateToken($username, $userIndex)
    {
        $token = [
            'host' => 'http://leadwire.local',
            'user' => $userIndex,
            'name' => $username,
            'iat' => time(),
            'exp' => time() + 1800 + 1800 * 2,
            'nbf' => time(),
        ];

        return JWT::encode($token);
    }
}
