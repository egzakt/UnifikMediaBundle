<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 */
class Image extends Media
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
        return 'image';
    }

    public function getRouteBackend($action = 'edit')
    {
        if ('list' === $action)
            return 'egzakt_media_backend_image';
        return 'egzakt_media_backend_image_' . $action;
    }

    public function getReplaceRegex()
    {
        return sprintf('/(<img [^>]*data-mediaid="%d"[^>]*src=").*("[^>]*>)/', $this->getId());
    }

    public function getReplaceUrl()
    {
        return $this->getMediaPath();
    }
}