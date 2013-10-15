<?php

namespace Flexy\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Flexy\SystemBundle\Lib\BaseEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * Media
 */
class Media extends BaseEntity
{

    use FlexyORMBehaviors\Uploadable\Uploadable;
    use FlexyORMBehaviors\Timestampable\Timestampable;

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
     * @var string
     */
    protected $url;

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
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var string
     */
    private $attr;

    /**
     * @var Media
     */
    private $thumbnail;

    /**
     * @var \Flexy\MediaBundle\Entity\Folder
     */
    private $folder;

    /**
     * Non mapped field
     *
     * @var boolean
     */
    protected $needUpdate = false;

    /**
     * @var bool
     */
    private $hidden = false;

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->name) ?: 'New media' ;
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
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
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
     * Get mediaPath
     *
     * @param bool $absolute
     * @return string
     */
    public function getMediaPath($absolute = false)
    {
        if ($absolute) {
            return $this->container->get('kernel')->getRootDir().'/../web/uploads/' . $this->getWebPath('media');
        }

//        $testweb = $this->getWebPath('media');
//        $testabsolute = $this->getAbsolutePath('media');
//        $testupload = $this->getUploadPath('media');
//        $testroot = $this->getUploadRootDir('media');

        switch ($this->type) {
            case 'embedvideo':
                return $this->mediaPath;
            default:
                return $this->getWebPath('media');
        }
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl( $url )
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set media
     *
     * @param $media
     */
    public function setMedia($media)
    {
        $this->setUploadedFile($media, 'media');
    }

    /**
     * Get media
     *
     * @return UploadedFile
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * Set caption
     *
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
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
     * Set width
     *
     * @param $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set attr
     *
     * @param $attr
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    }

    /**
     * Get attr
     *
     * @return string
     */
    public function getAttr()
    {
        return $this->attr;
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
     * Set hidden
     *
     * @param $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Get hidden
     *
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set folder
     *
     * @param \Flexy\MediaBundle\Entity\Folder $folder
     * @return Media
     */
    public function setFolder(\Flexy\MediaBundle\Entity\Folder $folder = null)
    {
        $this->folder = $folder;
    }

    /**
     * Get folder
     *
     * @return \Flexy\MediaBundle\Entity\Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set thumbnail
     *
     * @param Media $thumbnail
     */
    public function setThumbnail(Media $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get thumbnail
     *
     * @return \Flexy\MediaBundle\Entity\Media
     */
    public function getThumbnail()
    {
        if ('image' == $this->type) {
            return $this;
        }

        return $this->thumbnail;
    }

    /**
     * Get the thumbnail url
     * @return string
     */
    public function getThumbnailUrl()
    {
        if ('image' == $this->type) {
            return $this->getMediaPath();
        }

        return $this->thumbnail->getMediaPath();
    }

    /**
     * Get Backend Route
     *
     * @param string $action
     * @return string
     */
    public function getRouteBackend($action = 'edit')
    {
        if ('list' === $action) {
            return 'flexy_media_backend_media';
        }

        switch ($action) {
            case 'list':
                return 'flexy_media_backend_media';
                break;
            case 'duplicate':
                return 'flexy_media_backend_' . $action;
        }

        switch ($this->type) {
            case 'image':
                return 'flexy_media_backend_image_' . $action;
            case 'video':
                return 'flexy_media_backend_video_' . $action;
            case 'embedvideo':
                return 'flexy_media_backend_embed_video_' . $action;
            default:
                return 'flexy_media_backend_document_' . $action;
        }
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
     * getReplaceUrl
     *
     * @return string
     */
    public function getReplaceUrl()
    {
        return $this->getMediaPath();
    }

    /**
     * Get Replacement Regex
     *
     * @return string
     */
    public function getReplaceRegex()
    {
        switch ($this->type) {
            case 'image':
                return sprintf('/(<img [^>]*data-mediaid="%d"[^>]*src=")[^>]+("[^>]*>)/', $this->getId());
            case 'video':
                return sprintf('/(<iframe [^>]*data-mediaid="%d"[^>]*src=")[^>]+("[^>]*><\/iframe>)/', $this->getId());
            case 'embedVideo':
                return sprintf('/(<iframe [^>]*data-mediaid="%d"[^>]*src=")[^>]+("[^>]*><\/iframe>)/', $this->getId());
            default:
                return sprintf('/(<a [^>]*data-mediaid="%d"[^>]*href=")[^>]+("[^>]*>)[^<]+(<\/a>)/', $this->getId());
        }
    }

    /**
     * Get the list of uploabable fields and their respective upload directory in a key => value array format.
     *
     * @return array
     */
    public function getUploadableFields()
    {
        return [
            'media' => 'medias'
        ];
    }
}