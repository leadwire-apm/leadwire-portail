<?php declare (strict_types = 1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\MappedSuperclass(repositoryClass="AppBundle\Repository\TaskRepository")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({
 *      "delete"="AppBundle\Document\DeleteTask"
 * })
 */
abstract class Task
{
    const DELETE_TASKL = 'DELETE';

    /**
     * @ODM\Id(strategy="auto")
     * @var \MongoId
     */
    protected $id;

    protected $name;
}
