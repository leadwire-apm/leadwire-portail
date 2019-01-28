<?php declare(strict_types=1);

namespace Tests\AppBundle;

use Firebase\JWT\JWT;
use JMS\serializer\Serializer;
use AppBundle\Service\UserService;
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
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->documentManager = $this->container->get('doctrine_mongodb.odm.document_manager');

        $this->userService = $this->container->get(UserService::class);

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

    public function generateToken($username, $userIndex, $tokenSecret="52e431f6ed5a80ed700c04986b6ddf")
    {
        $token = [
            'host' => 'http://leadwire.local',
            'user' => $userIndex,
            'name' => $username,
            'iat' => time(),
            'exp' => time() + 1800 + 1800 * 2,
            'nbf' => time(),
        ];

        return JWT::encode($token, $tokenSecret);
    }
}
