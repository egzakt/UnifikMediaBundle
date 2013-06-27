<?php

namespace Egzakt\MediaBundle\Lib;

/**
 * Class MediaParser
 */
abstract class MediaParser {

    /**
     * @var string $mediaUrl
     */
    protected $mediaUrl;

    /**
     * Set Media Url
     *
     * @param string $mediaUrl
     */
    public function setMediaUrl($mediaUrl)
    {
        $this->mediaUrl = $mediaUrl;
    }

    /**
     * Get Media Url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->mediaUrl;
    }

}