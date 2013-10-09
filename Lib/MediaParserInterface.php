<?php

namespace Flexy\MediaBundle\Lib;


interface MediaParserInterface {

    /**
     * Get Thumbnail Url
     *
     * Returns a media thumbnail URL
     *
     * @return string
     */
    public function getThumbnailUrl();

    /**
     * Get Embed Url
     *
     * Returns a media embed URL
     *
     * @return string
     */
    public function getEmbedUrl();

    /**
     * Get Id
     *
     * Returns the ID of a media from a media URL
     *
     * @return mixed
     */
    public function getId();

    /**
     * Supports
     *
     * Check if $mediaUrl is supported by this parser
     *
     * @param $mediaUrl
     *
     * @return bool|int
     */
    public function supports($mediaUrl);

}