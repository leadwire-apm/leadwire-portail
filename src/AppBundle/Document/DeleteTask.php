<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class DeleteTask extends Task
{
    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", storeAs="dbRef")
     *
     * @var Application
     */
    private $application;


    /**
     * Get the value of application
     *
     * @return  Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @param  Application  $application
     *
     * @return  self
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

        return $this;
    }
}
