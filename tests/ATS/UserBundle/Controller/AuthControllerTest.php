<?php

namespace Tests\ATS\AuthBundle\Controller;

use ATS\AuthBundle\Document\AccessToken;
use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use FOS\OAuthServerBundle\Command\CreateClientCommand;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * AuthControllerTest
 *
 * @author Hounaida ZANNOUN <hzannoun@ats-digital.com>
 */
class AuthControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $kernel = self::bootKernel();
        /** @var $managerRegistry ManagerRegistry */
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $encoder = $kernel->getContainer()->get('security.password_encoder');
        $userManager = new UserManager($managerRegistry, $encoder);

        $user = new User();
        $user->setUsername('testadmin')
            ->setRoles(['ROLE_ADMIN'])
            ->setActive(true)
            ->setPassword($encoder->encodePassword($user, 'testadmin'));

        $userManager->update($user);
        $this->assertEquals($user->getRoles(), $userManager->getUserByUsername("testadmin")->getRoles());
    }

    public function testCreateClient()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        /** @var $clientManager  ClientManagerInterface */
        $clientManager = $kernel->getContainer()->get('test_alias.FOS\OAuthServerBundle\Model\ClientManagerInterface');
        $appDomain = $kernel->getContainer()->getParameter('app_domain');
        $application->add(new CreateClientCommand($clientManager));

        $command = $application->find('fos:oauth-server:create-client');
        $commandTester = new CommandTester($command);

        $returnCode = $commandTester->execute(
            [
                'command' => $command->getName(),
                '--redirect-uri' => [$appDomain],
                '--grant-type' => [
                    'authorization_code',
                    'password',
                    'refresh_token',
                    'token',
                    'client_credentials',
                ],
            ]
        );

        $this->assertEquals(0, $returnCode);

        $output = explode(' -', $commandTester->getDisplay());
        $output = explode('  ', $output[4]);

        return ['client_id' => $output[1], 'client_secret' => explode(' ', $output[2])];
    }

    public function testCheckLogin()
    {
        $output = $this->testCreateClient();
        $data = [
            'client_id' => $output['client_id'],
            'client_secret' => $output['client_secret'][1],
            'grant_type' => 'password',
            'username' => 'testadmin',
            'password' => 'testadmin',
        ];

        $kernel = self::bootKernel();
        $client = static::createClient([], ['HTTP_HOST' => $kernel->getContainer()->getParameter('app_domain')]);

        $client->getContainer()->get('doctrine_mongodb');
        $client->request(
            'POST',
            '/oauth/v2/token',
            $data
        );
        $this->assertTrue($client->getResponse()->isSuccessful());

        return json_decode($client->getResponse()->getContent(), true);
    }

    public function testLogout()
    {
        $accessTokenData = $this->testCheckLogin();
        $kernel = self::bootKernel();
        $tokenManager = $kernel->getContainer()->get(
            'test_alias.FOS\OAuthServerBundle\Model\AccessTokenManagerInterface'
        );
        $accessToken = $tokenManager->findTokenByToken($accessTokenData['access_token']);
        if ($accessToken) {
            $tokenManager->deleteToken($accessToken);
        }
        $this->assertEquals(null, $tokenManager->findTokenByToken($accessTokenData['access_token']));

    }

    public function testUpdateAccessToken()
    {
        $accessTokenData = $this->testCheckLogin();
        $kernel = self::bootKernel();
        $tokenManager = $kernel->getContainer()->get(
            'test_alias.FOS\OAuthServerBundle\Model\AccessTokenManagerInterface'
        );
        /** @var $accessToken  AccessToken */
        $accessToken = $tokenManager->findTokenByToken($accessTokenData['access_token']);
        $expiresAt = 86400 + time(); # 1 Day in seconds
        if ($accessToken) {
            $accessToken->setExpiresAt($expiresAt);
            $tokenManager->updateToken($accessToken);
        }
        $accessToken = $tokenManager->findTokenByToken($accessTokenData['access_token']);
        $this->assertEquals($expiresAt, $accessToken->getExpiresAt());
    }
}
