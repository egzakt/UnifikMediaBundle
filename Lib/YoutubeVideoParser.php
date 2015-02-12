<?php

namespace Unifik\MediaBundle\Lib;

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
        $ret = '';
        if(strlen($this->getMediaUrl()) == 11){
            return $this->getMediaUrl();
        }
        $re = "/^.*youtu(?:be\\.com|\\.be)\\/(?:watch\\?v=(.{11})|embed\\/(.{11})|(.{11})$)/";
        $ret = preg_replace($re, '$1$2$3', $this->getMediaUrl());
        if (strlen($ret) == 11){
            return $ret;
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
        $re = '/^.*youtu(?:be\\.com|\\.be)\\/(?:watch\\?v|embed|.{11})/';
        return (strlen($mediaUrl) == 11 || preg_match($re, $mediaUrl));
    }

}