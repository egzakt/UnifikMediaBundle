<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\MediaBundle\Form\MediaType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
/**
 * MEdia Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class MediaController extends BaseController
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
		$this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Media');
    }

    /**
     * Lists all media entities (not child entities).
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function indexAction()
    {
		$medias = $this->mediaRepository->findByType('media');
        return $this->render('EgzaktMediaBundle:Backend/Media/Media:list.html.twig', array(
			'medias' => $medias,
        ));
    }

    /**
     * Ajax version of the list action. Used to select a media to insert in another entity
     *
     * @param $type
     * @return Response
     */
    public function listAjaxAction($type)
    {
        if ("all" == $type)
            $medias = $this->mediaRepository->findByHidden(false);
        else
            $medias = $this->mediaRepository->findByType($type);

        $mediasOutput = array();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        /* @var $media Media */
        foreach ($medias as $media) {
            $current = array(
                'name' => $media->getName(),
                'id' => $media->getId(),
                'type' => $media->getType(),
                'path' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
                'editLink' =>  $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            );
            $mediasOutput[] = $current;
        }

        return new JsonResponse(array(
            'medias' => $mediasOutput,
        ));
    }

    /**
     * Create a media and guess the type with the mime type
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function createAction(Request $request)
	{
		if ("POST" == $request->getMethod()) {
			$file = $request->files->get('file');

			if (!$file instanceof UploadedFile || !$file->isValid()) {
				return new JsonResponse(array(
					"error" => array(
						"message" => "Unable to upload the file",
					),
				));
			}

            switch ($file->getMimeType()) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $controller = "EgzaktMediaBundle:Backend/Media/Image:create";
                    break;
                default:
                    $controller = "EgzaktMediaBundle:Backend/Media/Document:create";
            }

            return $this->forward($controller, array(
                'file' => $file,
            ));

		}
		return $this->render('EgzaktMediaBundle:Backend/Media/Media:create.html.twig');
	}

    /**
     * Ajax version of the create action,
     * @return Response
     */
    public function createAjaxAction()
    {
        return $this->render('EgzaktMediaBundle:Backend/Media/Media:create_ajax.html.twig');
    }

    /**
     * Displays a form to edit an existing ad entity
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

		$form = $this->createForm(new MediaType(), $media);

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
					return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
				}

				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
			}
		}

		return $this->render('EgzaktMediaBundle:Backend/Media/Media:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
		));
	}

    /**
     * Duplicate a media
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function duplicateAction($id)
	{
		/** @var Media $media */
		$media = $this->mediaRepository->find($id);
		if (!$media) {
			throw $this->createNotFoundException('Unable to find Media entity.');
		}

		$newMedia = clone($media);
		$newMedia->setName($media->getName() . ' - copy');

		$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
		$uploadableManager->markEntityToUpload($newMedia, new MediaFileInfo($this->get('kernel')->getRootDir().'/../web'.$media->getMediaPath()));
		$this->getEm()->persist($newMedia);
		$this->getEm()->flush();

		return $this->redirect($this->generateUrl($newMedia->getRouteBackend(), $newMedia->getRouteBackendParams()));
	}


    /**
     * Delete a media (including child entities)
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction($id)
	{
        /** @var Media $media */
		$media = $this->mediaRepository->find($id);
		if (!$media) {
			throw $this->createNotFoundException('Unable to find Media entity.');
		}

		if ($this->get('request')->get('message')) {
			$template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
				'entity' => $media,
			));

			return new JsonResponse(array(
				'template' => $template,
				'isDeletable' => $media->isDeletable()
			));
		}

		$this->getEm()->remove($media);
		$this->getEm()->flush();

		$this->get('egzakt_system.router_invalidator')->invalidate();

		return $this->redirect($this->generateUrl($media->getRouteBackend('list')));
	}

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateImageAction($id, Request $request)
    {
        /** @var Media $image */
        $image = $this->mediaRepository->find($id);
        if (!$image) {
            throw $this->createNotFoundException('Unable to find the Media Entity');
        }

        file_put_contents($image->getMediaPath(true), file_get_contents($request->get('image')));

        $this->getEm()->persist($image);
        $this->getEm()->flush();

        //The imagine cache needs to be cleared because the image keep the same filename
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        foreach ($this->container->getParameter('liip_imagine.filter_sets') as $filter => $value ) {
            $cacheManager->remove($image->getMediaPath(), $filter);
        }

        return new JsonResponse(array());
    }

}
