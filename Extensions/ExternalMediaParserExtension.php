<?php

namespace Unifik\MediaBundle\Extensions;

use Unifik\MediaBundle\Lib\ExternalMediaParser;

/**
 * Class ExternalMediaParserExtension
 */
class ExternalMediaParserExtension extends \Twig_Extension {

    /**
     * @var ExternalMediaParser $externalMediaParser The ExternalMediaParser service
     */
    protected $externalMediaParser;

    /**
     * Construct
     */
    public function __construct(ExternalMediaParser $externalMediaParser)
    {
        $this->externalMediaParser = $externalMediaParser;
    }

    /**
     * Get Name
     *
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'external_media_parser_extension';
    }

    /**
     * Get Functions
     *
     * List of available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'media_thumbnail_url' => new \Twig_Function_Method($this, 'getMediaThumbnailUrl'),
            'media_thumbnail_image_tag' => new \Twig_Function_Method($this, 'getMediaThumbnailImageTag'),
            'media_embed_url' => new \Twig_Function_Method($this, 'getMediaEmbedUrl')
        );
    }

    /**
     * Get Media Thumbnail Url
     *
     * @param string $mediaUrl
     *
     * return string|null
     */
    public function getMediaThumbnailUrl($mediaUrl)
    {
        $parser = $this->externalMediaParser->getParser($mediaUrl);

        if ($parser) {
            return $parser->getThumbnailUrl();
        }

        return null;
    }

    /**
     * Get Media Thumbnail Image Tag
     *
     * @param string      $mediaUrl
     * @param int|null    $width
     * @param int|null    $height
     * @param string      $alt
     * @param string|null $title
     *
     * @return null|string
     */
    public function getMediaThumbnailImageTag($mediaUrl, $width = null, $height = null, $alt = '', $title = null)
    {
        $thumbnailUrl = $this->getMediaThumbnailUrl($mediaUrl);

        if ($thumbnailUrl) {
            $tag = '<img src="' . $thumbnailUrl . '" ';

            if ($width) {
                $tag .= 'width="' . $width . '" ';
            }

            if ($height) {
                $tag .= 'height="' . $height . '" ';
            }

            $tag .= 'alt="' . $alt . '" ';

            if ($title) {
                $tag .= 'title="' . $title . '" ';
            }

            $tag .= '/>';

            return $tag;
        }

        return null;
    }

    /**
     * Get Media Embed Url
     *
     * @param string $mediaUrl
     *
     * return string|null
     */
    public function getMediaEmbedUrl($mediaUrl)
    {
        $parser = $this->externalMediaParser->getParser($mediaUrl);

        if ($parser) {
            return $parser->getEmbedUrl();
        }

        return null;
    }

}