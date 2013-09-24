<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 */
class Document extends Media
{
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
        if ('list' === $action) {
            return 'egzakt_media_backend_media';
        }

        return 'egzakt_media_backend_document_' . $action;
    }

    public function getReplaceRegex()
    {
        return sprintf('/(<a [^>]*data-mediaid="%d"[^>]*href=")[^>]+("[^>]*>)[^<]+(<\/a>)/', $this->getId());
    }

    public function getReplaceUrl()
    {
        return $this->getMediaPath();
    }
}