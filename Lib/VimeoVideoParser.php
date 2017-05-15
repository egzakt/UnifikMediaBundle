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
            return $data[0]->thumbnail_large;
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
            return 'https://player.vimeo.com/video/' . $this->getId();
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
        $re = '#.*\/([\d]{6,}).*#i';
        return preg_replace($re, '$1', $this->getMediaUrl());
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
        $re = '#.*\/([\d]{6,}).*#i';
        $id = preg_replace($re, '$1', $mediaUrl);
        if(strpos(@get_headers('http://player.vimeo.com/video/' . $id)[0], '200')){
            return false;
        };
        return true;
    }

}
