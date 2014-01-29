<?php

namespace Unifik\MediaBundle\Lib;

/**
 * Class VimeoVideoParser
 */
class VimeoVideoParser extends MediaParser implements MediaParserInterface {

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
            $data = file_get_contents('http://vimeo.com/api/v2/video/' . $this->getId() . '.json');
            $data = json_decode($data);
            return $data[0]->thumbnail_medium;
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
            return 'http://player.vimeo.com/video/' . $this->getId();
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
        if (preg_match('/player.vimeo.com/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://)?(www\.|//)?player.vimeo.com/video/([\d]+)#i', '$3', $this->getMediaUrl());
        } elseif (preg_match('/vimeo.com/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://)?(www\.)?vimeo.com/([\d]+)#i', '$3', $this->getMediaUrl());
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
        return (preg_match('#vimeo.com/[\d]+$#i', $mediaUrl) || preg_match('#player.vimeo.com/video/[\d]+$#i', $mediaUrl));
    }

}