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
        if ('list' === $action)
            return 'egzakt_media_backend_image';
        return 'egzakt_media_backend_image_' . $action;
    }

    /**
     * getReplaceRegex
     *
     * @return string
     */
    public function getReplaceRegex()
    {
        return sprintf('/(<img [^>]*data-mediaid="%d"[^>]*src=").*("[^>]*>)/', $this->getId());
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