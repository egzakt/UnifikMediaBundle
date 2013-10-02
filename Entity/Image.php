<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * Image
 */
class Image extends Media
{
    use EgzaktORMBehaviors\Uploadable\Uploadable;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'image';
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
     * getRouteBackend
     *
     * @param string $action
     * @return string
     */
    public function getRouteBackend($action = 'edit')
    {
        if ('list' === $action) {
            return 'egzakt_media_backend_media';
        }

        return 'egzakt_media_backend_image_' . $action;
    }

    /**
     * Override the parent method to return the file
     *
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->getMediaPath();
    }

    /**
     * Override the parent method to return the file
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this;
    }

    /**
     * getReplaceRegex
     *
     * @return string
     */
    public function getReplaceRegex()
    {
        return sprintf('/(<img [^>]*data-mediaid="%d"[^>]*src=")[^>]+("[^>]*>)/', $this->getId());
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
     * getReplaceUrl
     *
     * @return string
     */
    public function getReplaceUrl()
    {
        return $this->getMediaPath();
    }

    /**
     * Serialize the media to an array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'width' => $this->width,
                'height' => $this->height,
                'attr' => $this->attr
            )
        );
    }
}