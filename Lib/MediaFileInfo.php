<?php
namespace Egzakt\MediaBundle\Lib;

use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class MediaFileInfo
 */
class MediaFileInfo implements FileInfoInterface
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
}