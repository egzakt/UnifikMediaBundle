<?php

namespace Egzakt\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * EmbedVideo
 */
class EmbedVideo extends Media
{
    /**
     * @var integer
     */
    protected $width;

    /**
     * @var integer
     */
    protected $height;

	/**
	 * @var string
	 */
	protected $url;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;
    
        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;
    
        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

	/**
	 * @param string $url
	 */
	public function setUrl( $url ) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'embedvideo';
    }

    public function getRouteBackend($action = 'edit')
    {
        if ('list' === $action) {
            return 'egzakt_media_backend_media';
        }

        return 'egzakt_media_backend_video_' . $action;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $array = parent::toArray();
        $parser = $this->container->get('egzakt_media.parser')->getParser($this->getUrl());
        $array['embedUrl'] = $parser->getEmbedUrl();
        return $array;
    }

    public function getReplaceRegex()
    {
        return sprintf('/(<iframe [^>]*data-mediaid="%d"[^>]*src=").*("[^>]*>)/', $this->getId());
    }

    public function getReplaceUrl()
    {
        return $this->container->get('egzakt_media.parser')->getParser($this->getUrl())->getEmbedUrl();
    }
}