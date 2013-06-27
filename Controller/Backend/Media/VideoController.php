<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Doctrine\Tests\ORM\Functional\CompositePrimaryKeyTest;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Entity\Video;
use Egzakt\MediaBundle\Form\ImageType;
use Egzakt\MediaBundle\Form\VideoType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\MediaBundle\Form\MediaType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * ad Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class VideoController extends BaseController
{
    /**
     * @var mediaRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
		$this->mediaRepository = $this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Video');
    }

	public function createAction(Request $request)
	{
		if("POST" !== $request->getMethod()){
			throw new Exception('The request method must be post.');
		}

		$video = new Video();

		$video->setUrl($request->get('video_url'));
		$video->setName($request->get('video_url'));
		$video->setMimeType('video/x-flv');

		$mediaParser = $this->get('egzakt_media.parser');
		$parser = $mediaParser->getParser($video->getUrl());
		$tempFile = '/tmp/'.$parser->getId().'.jpg';
		file_put_contents($tempFile, file_get_contents($parser->getThumbnailUrl()));

		$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
		$uploadableManager->markEntityToUpload($video, new MediaFileInfo($tempFile));

		$this->getEm()->persist($video);
		$this->getEm()->flush();

		return new JsonResponse(json_encode(array(
			"path" => $this->generateUrl($video->getRouteBackend(), $video->getRouteBackendParams()),
			"name" => $video->getName(),
		)));
	}

	public function editAction(Video $media, Request $request)
	{
		$form = $this->createForm(new VideoType(), $media);

		if("POST" == $request->getMethod()){

			$form->submit($request);

			if($form->isValid()){
				$this->getEm()->persist($media);

				//Update the file only if a new one has been uploaded
				if($media->getMediaFile()){
					$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
					$uploadableManager->markEntityToUpload($media, $media->getMediaFile());
				}

				$this->getEm()->flush();

				$this->get('egzakt_system.router_invalidator')->invalidate();

				if ($request->request->has('save')) {
					return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
				}

				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
			}
		}

		$mediaParser = $this->get('egzakt_media.parser');
		$parser = $mediaParser->getParser($media->getUrl());

		return $this->render('EgzaktMediaBundle:Backend/Media/Video:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
			'video_id' => $parser->getId(),
			'image_path' => $media->getMediaPath(),
		));
	}

}
