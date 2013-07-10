<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Video;
use Egzakt\MediaBundle\Form\VideoType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Video Controller
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


    /**
     * Display video list
     *
     * @return Response
     */
    public function indexAction()
    {
        $medias = $this->mediaRepository->findByHidden(false);
        return $this->render('EgzaktMediaBundle:Backend/Media/Video:list.html.twig', array(
            'medias' => $medias,
        ));
    }

    /**
     * Create a video from a url
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function createAction(Request $request)
	{
		if ("POST" !== $request->getMethod()) {
			throw new Exception('The request method must be post.');
		}

        $mediaParser = $this->get('egzakt_media.parser');
        if (!$mediaParser->getParser($request->get('video_url'))) {
            return new JsonResponse(array(
                'error' => array(
                  'message' => 'Unable to parse the video url',
                ),
            ));
        }

		$video = new Video();

		$video->setUrl($request->get('video_url'));
		$video->setName($request->get('video_url'));

		$this->updateThumbnail($video);

		$this->getEm()->persist($video);
		$this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($video->getRouteBackend(), $video->getRouteBackendParams()),
            'id' => $video->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($video->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
        ));
	}

    /**
     * Displays a form to edit an existing ad entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
	{
        /** @var Video $media */
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unanble to find the media');
        }

		$form = $this->createForm(new VideoType(), $media);

		if ("POST" == $request->getMethod()) {

            $oldUrl = $media->getUrl();
			$form->submit($request);

			if ($form->isValid()) {
				$this->getEm()->persist($media);

				//Update the file only if a new one has been uploaded
				if ($media->getMediaFile()) {
					$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
					$uploadableManager->markEntityToUpload($media, $media->getMediaFile());
				}elseif ($oldUrl !== $media->getUrl()) {
                    $this->updateThumbnail($media);
                }

				$this->getEm()->flush();

				$this->get('egzakt_system.router_invalidator')->invalidate();

				if ($request->request->has('save')) {
					return $this->redirect($this->generateUrl('egzakt_media_backend_video'));
				}

				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
			}
		}

		$mediaParser = $this->get('egzakt_media.parser');
		$parser = $mediaParser->getParser($media->getUrl());

		return $this->render('EgzaktMediaBundle:Backend/Media/Video:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
            'video_url' => $parser->getEmbedUrl(),
		));
	}

    /**
     * Update (or create) the thumbnail for a video
     * @param Video $video
     */
    public function updateThumbnail(Video $video)
    {
        $mediaParser = $this->get('egzakt_media.parser');
        $parser = $mediaParser->getParser($video->getUrl());

        //The file needs to be download from a remote server and stored temporary on the server to allow doctrine extension to handle it properly
        $tempFile = '/tmp/'.$parser->getId().'.jpg';
        file_put_contents($tempFile, file_get_contents($parser->getThumbnailUrl()));

        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $uploadableManager->markEntityToUpload($video, new MediaFileInfo($tempFile));
    }

}
