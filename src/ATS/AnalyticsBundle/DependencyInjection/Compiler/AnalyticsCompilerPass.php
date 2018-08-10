<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\DependencyInjection\Compiler;

use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;
use ATS\AnalyticsBundle\Listener\AnalyticsEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AnalyticsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has(AnalyticsEventListener::class)) {
            return;
        }

        $definition = $container->findDefinition(AnalyticsEventListener::class);

        $taggedServices = $container->findTaggedServiceIds('ats.analytics.handler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', array(new Reference($id)));
        }

        $taggedServices = $container->findTaggedServiceIds('ats.analytics.event');

        foreach ($taggedServices as $id => $tags) {
            if ($id == BaseAnalyticsEvent::class) {
                continue;
            }

            $class = new \ReflectionClass($id);

            if (!array_key_exists('NAME', $class->getConstants())) {
                throw new \Exception('Constant "NAME" must be defined for class ' . $class->getName());
            } else {
                $definition->addTag(
                    'kernel.event_listener',
                    array(
                        'event' => $class->getConstants()['NAME'],
                        'method' => 'onTrackingEventTriggered',
                    )
                );
            }
        }
    }
}
