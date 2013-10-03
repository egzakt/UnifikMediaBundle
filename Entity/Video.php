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

    /**
     * Set Upload Path
     *
     * @param $field
     * @param $uploadPath
     */
    public function setUploadPath($field, $uploadPath)
    {
        $this->uploadableFieldExists($field);

        $pathArray = $this->getUploadableFields();

        $this->{$field . 'Path'} = $pathArray[$field] . '/' . $uploadPath;
    }

    /**
     * Get Absolute Path
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
     * Get Previous Upload Absolute Path
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
}