<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Doctrine\Tests\ORM\Functional\CompositePrimaryKeyTest;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Form\ImageType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\MediaBundle\Form\MediaType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Image controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class ImageController extends BaseController
{
    /**
     * @var ImageRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
		$this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Image');
    }

    /**
     * Display image list
     *
     * @return Response
     */
    public function indexAction()
    {
        $medias = $this->mediaRepository->findByHidden(false);
        return $this->render('EgzaktMediaBundle:Backend/Media/Image:list.html.twig', array(
            'medias' => $medias,
        ));
    }

    public function createAction(UploadedFile $file)
    {
        $media = new Image();
        $media->setMediaFile($file);
		$media->setName($file->getClientOriginalName());

        $this->getEm()->persist($media);

        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $uploadableManager->markEntityToUpload($media, $media->getMediaFile());

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'id' => $media->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
        ));
    }

    /**
     * Displays a form to edit an existing image entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
	{
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

		$form = $this->createForm(new ImageType(), $media);

		if ("POST" == $request->getMethod()) {

			$form->submit($request);

			if ($form->isValid()) {
				$this->getEm()->persist($media);

				//Update the file only if a new one has been uploaded
				if ($media->getMediaFile()) {
					$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
					$uploadableManager->markEntityToUpload($media, $media->getMediaFile());
				}

				$this->getEm()->flush();

				$this->get('egzakt_system.router_invalidator')->invalidate();

				if ($request->request->has('save')) {
					return $this->redirect($this->generateUrl('egzakt_media_backend_image'));
				}

				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
			}
		}

		return $this->render('EgzaktMediaBundle:Backend/Media/Image:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
		));
	}

}
