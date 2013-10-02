<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * Video
 */
class Video extends Media
{
    use EgzaktORMBehaviors\Uploadable\Uploadable;

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
        return sprintf('/(<iframe [^>]*data-mediaid="%d"[^>]*src=")[^>]+("[^>]*><\/iframe>)/', $this->getId());
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