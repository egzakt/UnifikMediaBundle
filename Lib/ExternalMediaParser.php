<?php

namespace Flexy\MediaBundle\Lib;

/**
 * Class ExternalMediaParser
 */
class ExternalMediaParser {

    /**
     * @var array $mediaParsers The list of MediaParserInterface parsers
     */
    protected $mediaParsers;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->mediaParsers = array();
    }

    /**
     * Set Media Parsers
     *
     * @param array $mediaParsers
     */
    public function setMediaParsers($mediaParsers)
    {
        $this->mediaParsers = $mediaParsers;
    }

    /**
     * Add Media Parser
     *
     * @param MediaParserInterface $mediaParser
     */
    public function addMediaParser(MediaParserInterface $mediaParser)
    {
        $this->mediaParsers[] = $mediaParser;
    }

    /**
     * Get Media Parsers
     *
     * @return array
     */
    public function getMediaParsers()
    {
        return $this->mediaParsers;
    }

    /**
     * Get Parser
     *
     * Loop through the video parsers to find the good parser for this URL
     *
     * @param $mediaUrl The URL of the media
     *
     * @return MediaParserInterface|null
     */
    public function getParser($mediaUrl)
    {
        foreach($this->mediaParsers as $mediaParser) {
            if ($mediaParser->supports($mediaUrl)) {

                $mediaParser->setMediaUrl($mediaUrl);
                return $mediaParser;
            }
        }

        return null;
    }

}