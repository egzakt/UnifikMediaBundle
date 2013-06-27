<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Entity\Video;
use Egzakt\MediaBundle\Form\ImageType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\Tests\TestEventSubscriberWithMultipleListeners;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\MediaBundle\Form\MediaType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
/**
 * ad Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class MediaController extends BaseController
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
		$this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Media');
       // $this->getCore()->addNavigationElement($this->getSectionBundle());
    }

    /**
     * Lists all ad entities.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function indexAction($type)
    {
		$medias = $this->mediaRepository->findByType($type);
        return $this->render('EgzaktMediaBundle:Backend/Media/Media:list.html.twig', array(
			'medias' => $medias,
        ));
    }

	public function createAction(Request $request)
	{
		if("POST" == $request->getMethod()){
			$file = $request->files->get('file');

			if(!$file instanceof UploadedFile || !$file->isValid()){
				return new Response(json_encode(array(
					"error" => array(
						"message" => "Error",
					),
				)));
			}
			$media = $this->createMediaFromFile($file);
			$this->getEm()->persist($media);

			$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
			$uploadableManager->markEntityToUpload($media, $media->getMediaFile());

			$this->getEm()->flush();

			return new Response(json_encode(array(
				'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
				"message" => "File uploaded",
			)));
		}
		return $this->render('EgzaktMediaBundle:Backend/Media/Media:create.html.twig');
	}

	public function editAction($id, Request $request)
	{
		$media = $this->mediaRepository->find($id);
		if(!$media){
			throw new Exception('Unanble to find the media');
		}

		if($media instanceof Image)
			return $this->forward('EgzaktMediaBundle:Backend/Media/Image:edit', array(
				'media' => $media,
				'request' => $request,
			));

		if($media instanceof Video)
			return $this->forward('EgzaktMediaBundle:Backend/Media/Video:edit', array(
				'media' => $media,
				'request' => $request,
			));

		return $this->forward('EgzaktMediaBundle:Backend/Media/Media:editGeneric', array(
			'media' => $media,
			'request' => $request,
		));
	}

	/**
	 * Displays a form to edit an existing ad entity.
	 *
	 * @param integer $id The ad ID
	 *
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\RedirectResponse|\Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function editGenericAction(Media $media, Request $request)
	{
		$form = $this->createForm(new MediaType(), $media);

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

		return $this->render('EgzaktMediaBundle:Backend/Media/Media:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
			'isImage' => $media instanceof Image,
		));
	}

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

	public function deleteAction($id)
	{
		$media = $this->mediaRepository->find($id);

		if (!$media) {
			throw $this->createNotFoundException('Unable to find Media entity.');
		}

		if ($this->get('request')->get('message')) {
			$template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
				'entity' => $media,
			));

			return new Response(json_encode(array(
				'template' => $template,
				'isDeletable' => $media->isDeletable()
			)));
		}

		$this->getEm()->remove($media);
		$this->getEm()->flush();

		$this->get('egzakt_system.router_invalidator')->invalidate();

		return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
	}

    public function updateImageAction($id, Request $request)
    {
        /** @var Media $image */
        $image = $this->mediaRepository->find($id);
        if(!$image){
            return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
        }

        $path = $this->get('kernel')->getRootDir().'/../web'.$image->getMediaPath();
        file_put_contents($path, file_get_contents($request->get('image')));

        $this->getEm()->persist($image);
        $this->getEm()->flush();

        return new JsonResponse(json_encode(array()));
    }

	private function createMediaFromFile(UploadedFile $file)
	{
		$media = null;
		switch($file->getMimeType()){
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$media = new Image();
				break;
			default:
				$media = new Media();
		}
		$media->setMediaFile($file);
		$media->setName($file->getClientOriginalName());

		return $media;
	}

}
