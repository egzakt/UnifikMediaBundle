<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 */
class Document extends Media
{
    /**
     * @var \Egzakt\MediaBundle\Entity\Image
     */
    private $thumbnail;


    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return 'document';
    }

     /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getDocumentFile()
    {
        return $this->document;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function setDocumentFile($file)
    {
        $this->document = $file;
    }

    public function getRouteBackend($action = 'edit')
    {
        if ('list' === $action)
            return 'egzakt_media_backend_document';
        return 'egzakt_media_backend_document_' . $action;
    }

    /**
     * Set thumbnail
     *
     * @param \Egzakt\MediaBundle\Entity\Image $thumbnail
     * @return Document
     */
    public function setThumbnail(\Egzakt\MediaBundle\Entity\Image $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    
        return $this;
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

    public function getReplaceRegex()
    {
        return sprintf('/(<a [^>]*data-mediaid="%d"[^>]*href=").*("[^>]*>)/', $this->getId());
    }

    public function getReplaceUrl()
    {
        return $this->getMediaPath();
    }
}