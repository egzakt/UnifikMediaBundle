<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Egzakt\SystemBundle\Lib\BaseEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Media
 */
class Media extends BaseEntity
{
    /**
     * @var integer
     */
    protected  $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

	/**
	 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	protected $media;

	protected $mediaPath;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var boolean
     */
    protected  $hidden;


	public function __construct()
	{
		//The default type is media
		$this->type = "media";
        $this->hidden = false;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		if(false == $this->id)
			return "New media";
		if($this->name)
			return $this->name;
		return '';
	}

	public function getRouteBackend($action = 'edit')
	{
        if ('list' === $action)
            return 'egzakt_media_backend_media';
		return 'egzakt_media_backend_media_' . $action;
	}

	/**
	 * Get Backend route params
	 *
	 * @param array $params Array of params to get
	 *
	 * @return array
	 */
	public function getRouteBackendParams($params = array())
	{
		$defaults = array(
			'id' => $this->id ? $this->id : 0
		);

		$params = array_merge($defaults, $params);

		return $params;
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Media
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return 'media';
    }


    /**
     * Get meidaPath
     *
     * @param bool $absolute
     * @return string
     */
    public function getMediaPath($absolute = false)
    {
        if ($absolute) {
            return $this->container->get('kernel')->getRootDir().'/../web/'.$this->mediaPath;
        }

        return '/'.$this->mediaPath;
    }

	public function setMediaPath($path)
	{
		$this->mediaPath = $path;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function getMediaFile()
	{
		return $this->media;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function setMediaFile($file)
	{
		$this->media = $file;
	}

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Media
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Media
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Media
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return Media
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    
        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     * @return Media
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    
        return $this;
    }

    /**
     * Get displayName
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     * @return Media
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    
        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean 
     */
    public function getHidden()
    {
        return $this->hidden;
    }
}