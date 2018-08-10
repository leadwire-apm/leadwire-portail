<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ODM\MappedSuperclass
 * @ODM\Document(collection="Statistic")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
abstract class AbstractStatistic
{
    /**
     * @var
     * @ODM\Id(strategy="auto")
     * @Expose
     */
    protected $id;


    public function getId()
    {
        return $this->id;
    }
}
