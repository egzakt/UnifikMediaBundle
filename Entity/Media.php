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

    /**
     * @var string
     */
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
    protected $caption;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var float
     */
    protected $size;

    /**
     * Non mapped field
     *
     * @var boolean
     */
    protected $needUpdate = false;

    /**
     * Internal field used to hide the media from the list in certain case
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

    /**
     * Get the backend route
     * @param string $action
     * @return string
     */
    public function getRouteBackend($action = null)
    {
        if ('list' === $action)
            return 'egzakt_media_backend_media';
        return 'egzakt_media_backend_media';
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
     * Get the url used to serve the thumbnail
     * Some child class may have to overwrite it
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->getMediaPath();
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
     * Get the media type
     * It needs to be hardcoded becauseDoctrine does not allow to get the discriminator field
     *
     * @return string 
     */
    public function getType()
    {
        return 'media';
    }


    /**
     * Get mediaPath
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

    /**
     * Set the media path
     * @param $path
     */
    public function setMediaPath($path)
    {
        $this->mediaPath = $path;
    }

    /**
     * Get the media file
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getMediaFile()
    {
        return $this->media;
    }

    /**
     * Set the media file
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
    public function setCaption($caption)
    {
        $this->caption = $caption;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getCaption()
    {
        return $this->caption;
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
     * setSize
     *
     * @param $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * getSize
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
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

    public function getReplaceRegex()
    {
        return '';
    }

    public function getReplaceUrl()
    {
        return '';
    }

    /**
     * Set needUpdate
     *
     * @param $needUpdate
     */
    public function setNeedUpdate($needUpdate)
    {
        $this->needUpdate = $needUpdate;
    }

    /**
     * needUpdate
     *
     * @return bool
     */
    public function needUpdate()
    {
        return $this->needUpdate;
    }

    /**
     * Serialize the media to an array
     */
    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'id' => $this->getId(),
            'type' => $this->getType(),
            'path' => $this->container->get('liip_imagine.cache.manager')->getBrowserPath($this->getThumbnailUrl(), 'media_thumb'),
            'pathLarge' => $this->container->get('liip_imagine.cache.manager')->getBrowserPath($this->getThumbnailUrl(), 'media_thumb_large'),
            'mediaUrl' => $this->getMediaPath(),
            'editLink' =>  $this->container->get('router')->generate($this->getRouteBackend(), $this->getRouteBackendParams()),
            'size' => $this->size,
            'caption' => $this->caption
        );
    }
}