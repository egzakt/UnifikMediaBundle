<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Video
 */
class Video extends Media
{
    /**
     * @var Image
     */
    private $thumbnail;

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
     * Set thumbnail
     *
     * @param \Egzakt\MediaBundle\Entity\Image $thumbnail
     * @return Document
     */
    public function setThumbnail(Image $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get thumbnail
     *
     * @return \Egzakt\MediaBundle\Entity\Image
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Get the thumbnail url
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnail->getMediaPath();
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'video';
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

        return 'egzakt_media_backend_video_' . $action;
    }

    /**
     * getReplaceRegex
     *
     * @return string
     */
    public function getReplaceRegex()
    {
        return sprintf('/(<iframe [^>]*data-mediaid="%d"[^>]*src=")[^>]*("[^>]*><\/iframe>)/', $this->getId());
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
}