<?php declare(strict_types=1);

namespace ATS\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadedEvent extends Event
{
    const NAME = "core.file.uploaded";

    protected $filePath;

    public function __construct($filePath = '')
    {
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }
}
