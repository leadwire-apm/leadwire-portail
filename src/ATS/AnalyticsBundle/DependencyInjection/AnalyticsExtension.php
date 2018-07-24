<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ATS\AnalyticsBundle\Service\Handler\EventHandlerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AnalyticsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(EventHandlerInterface::class)
            ->addTag('ats.analytics.handler')
        ;

        $container->registerForAutoconfiguration(BaseAnalyticsEvent::class)
            ->addTag('ats.analytics.event')
        ;

        $container->setParameter('analytics.discriminator_map', $config['discriminator_map']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
