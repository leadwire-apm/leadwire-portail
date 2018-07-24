<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ATS\AnalyticsBundle\DependencyInjection\Compiler\AnalyticsCompilerPass;

class AnalyticsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AnalyticsCompilerPass());
    }
}
