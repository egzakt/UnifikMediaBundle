<?php

namespace Egzakt\MediaBundle\Lib;

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
            return 'http://img.youtube.com/vi/' . $this->getId() . '/hqdefault.jpg';
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
        parse_str(parse_url($this->getMediaUrl(), PHP_URL_QUERY), $vars);

        if (array_key_exists('v', $vars)) {
            return $vars['v'];
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
        return preg_match('/youtube.com/i', $mediaUrl);
    }

}