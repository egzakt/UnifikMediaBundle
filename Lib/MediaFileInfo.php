<?php
namespace Egzakt\MediaBundle\Lib;

use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Symfony\Component\HttpFoundation\File\File;

class MediaFileInfo implements FileInfoInterface
{
	protected $file;

	public function __construct($path)
	{
		$this->file = new File($path);
	}

	public function getTmpName()
	{
		return $this->file->getPathname();
	}

	public function getName()
	{
		return $this->file->getFilename();
	}

	public function getSize()
	{
		return $this->file->getSize();
	}

	public function getType()
	{
		return $this->file->getMimeType();
	}

	public function getError()
	{
		return 0;
	}

	public function isUploadedFile()
	{
		return false;
	}
}