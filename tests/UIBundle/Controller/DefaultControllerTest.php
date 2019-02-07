<?php

namespace UIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @uses ATS\CoreBundle\Manager\AbstractManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Twig\TranslationTwigExtension::__construct
     * @uses ATS\TranslationBundle\Twig\TranslationTwigExtension::getFunctions
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
