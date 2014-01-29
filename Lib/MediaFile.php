<?php
namespace Unifik\MediaBundle\Lib;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MediaFileInfo
 */
class MediaFile
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\File
     */
    protected $file;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->file = new File($path);
    }

    /**
     * @return string
     */
    public function getTmpName()
    {
        return $this->file->getPathname();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->file->getFilename();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->file->getMimeType();
    }

    /**
     * @return int
     */
    public function getError()
    {
        return 0;
    }

    /**
     * @return bool
     */
    public function isUploadedFile()
    {
        return false;
    }

    /**
     * Get Uploaded File
     *
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return new UploadedFile(
            $this->file->getPathname(),
            $this->getTmpName(),
            $this->getType(),
            $this->getSize(),
            $this->getError(),
            true
        );
    }
}