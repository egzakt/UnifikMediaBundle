<?php

namespace Flexy\MediaBundle\Lib;

/**
 * Class YoutubeVideoParser
 */
class YoutubeVideoParser extends MediaParser implements MediaParserInterface {

    /**
     * Get Thumbnail Url
     *
     * Returns a video thumbnail URL
     *
     * @return string $url
     */
    public function getThumbnailUrl()
    {
        if ($this->getId()) {
            return 'http://img.youtube.com/vi/' . $this->getId() . '/0.jpg';
        }

        return null;
    }

    /**
     * Get Embed Url
     *
     * Returns a video embed URL
     *
     * @return string $url
     */
    public function getEmbedUrl()
    {
        if ($this->getId()) {
            return 'http://www.youtube.com/embed/' . $this->getId();
        }
    }

    /**
     * Get Id
     *
     * Returns the ID of a video from a video URL
     *
     * @return mixed $id
     */
    public function getId()
    {
        if (preg_match('/youtube.com/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://)?(www\.)?youtube.com/embed/([^/]+)#i', '$3', $this->getMediaUrl());
        } elseif (preg_match('/youtu.be/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://)?(www\.)?youtu.be/([^/]+)#i', '$3', $this->getMediaUrl());
        }

        return null;
    }

    /**
     * Supports
     *
     * Check if $mediaUrl is supported by this parser
     *
     * @param $mediaUrl
     *
     * @return bool|int
     */
    public function supports($mediaUrl)
    {
        return (preg_match('#youtube.com/embed/[^/]+$#i', $mediaUrl) || preg_match('#youtu.be/[^/]+$#i', $mediaUrl));
    }

}