<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Listener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

class MapDiscriminatorListener
{
    protected $map;

    public function __construct($map = [])
    {
        $this->map = $map;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $classMetadata = $event->getClassMetadata();
        if ($classMetadata->getName() == 'AnalyticsBundle/Document/AbstractStatistic') {
            $classMetadata->setDiscriminatorMap($this->map);
            $classMetadata->setCollection('Statistic');
        }
    }
}
