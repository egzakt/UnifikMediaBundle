<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Egzakt\SystemBundle\Lib\BaseEntity;
use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * Media
 */
class Media extends BaseEntity
{

    use EgzaktORMBehaviors\Uploadable\Uploadable;
    use EgzaktORMBehaviors\Timestampable\Timestampable;

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
    private $parentMedia;

    /**
     * @var Media
     */
    private $thumbnail;

    /**
     * Non mapped field
     *
     * @var boolean
     */
    protected $needUpdate = false;

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
            return $this->container->get('kernel')->getRootDir().'/../web/uploads/' . $this->mediaPath;
        }

        $testweb = $this->getWebPath('media');
        $testabsolute = $this->getAbsolutePath('media');
        $testupload = $this->getUploadPath('media');
        $testroot = $this->getUploadRootDir('media');

        switch ($this->type) {
            case 'embedvideo':
                return $this->mediaPath;
            default:
                return 'uploads/' . $this->mediaPath;
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
     * setParentMedia
     *
     * @param Media $parentMedia
     */
    public function setParentMedia(Media $parentMedia)
    {
        $this->parentMedia = $parentMedia;
    }

    /**
     * getParentMedia
     *
     * @return Media
     */
    public function getParentMedia()
    {
        return $this->parentMedia;
    }

    /**
     * Set thumbnail
     *
     * @param \Egzakt\MediaBundle\Entity\Media $thumbnail
     */
    public function setThumbnail(Media $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get thumbnail
     *
     * @return \Egzakt\MediaBundle\Entity\Media
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
            return 'egzakt_media_backend_media';
        }

        switch ($this->type) {
            case 'image':
                return 'egzakt_media_backend_image_' . $action;
            case 'video':
                return 'egzakt_media_backend_video_' . $action;
            case 'embedvideo':
                return 'egzakt_media_backend_embed_video_' . $action;
            default:
                return 'egzakt_media_backend_document_' . $action;
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
     * Serialize the media to an array
     *
     * @return array
     */
    public function toArray()
    {
        $base = array(
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

        switch ($this->type) {
            case 'image':
                return array_merge(
                    $base,
                    array(
                        'width' => $this->width,
                        'height' => $this->height,
                        'attr' => $this->attr
                    )
                );
            case 'embedvideo':
                return array_merge(
                    $base,
                    array(
                        'embedUrl' => $this->getMediaPath()
                    )
                );
            default:
                return $base;
        }
    }

    /**
     * Get the list of uploabable fields and their respective upload directory in a key => value array format.
     *
     * @return array
     */
    public function getUploadableFields()
    {
        $date = new \DateTime();

        return [
            'media' => 'medias' . '/' . $date->format('Y') . '/' . $date->format('F')
        ];
    }

    /**
     * Set Upload Path OVERLOAD
     *
     * @param $field
     * @param $uploadPath
     */
    public function setUploadPath($field, $uploadPath)
    {
        $this->uploadableFieldExists($field);

        $this->{$field . 'Path'} = $this->getUploadableFields()[$field] . '/' . $uploadPath;
    }

    /**
     * Get Absolute Path OVERLOAD
     *
     * @param string $field
     *
     * @return null|string
     */
    public function getAbsolutePath($field)
    {
        $this->uploadableFieldExists($field);

        return null === $this->getUploadPath($field)
            ? null
            : $this->uploadRootDir . '/' . $this->getUploadPath($field);
    }

    /**
     * Get Previous Upload Absolute Path OVERLOAD
     *
     * @param string $field
     *
     * @return null|string
     */
    private function getPreviousUploadAbsolutePath($field)
    {
        $this->uploadableFieldExists($field);

        return null === $this->previousUploadPaths[$field]
            ? null
            : $this->uploadRootDir . '/' . $this->previousUploadPaths[$field];
    }

    /**
     * Get Web Path
     *
     * @param string $field
     *
     * @return null|string
     */
    public function getWebPath($field)
    {
        $this->uploadableFieldExists($field);

        return null === $this->getUploadPath($field)
            ? null
            : '/uploads/' . $this->getUploadPath($field);
    }

    /**
     * Get Upload Root Dir
     *
     * @param string $field
     *
     * @return string
     */
    public function getUploadRootDir($field)
    {
        $this->uploadableFieldExists($field);

        // the absolute directory path where uploaded
        // documents should be saved
        return $this->uploadRootDir . '/' . $this->getUploadableFields()[$field];
    }
}