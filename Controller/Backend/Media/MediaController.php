<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Form\ImageType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @var adRepository
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
    public function indexAction()
    {
		$medias = $this->mediaRepository->findAll();
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

    /**
     * Displays a form to edit an existing ad entity.
     *
     * @param integer $id The ad ID
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\RedirectResponse|\Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function editAction($id, Request $request)
    {
		$media = $this->mediaRepository->find($id);

		if(!$media){
			$media = new Media();
			$media->setContainer($this->container);
		}

		$formType = null;

		if($media instanceof Image)
			$formType = new ImageType();
		else
			$formType = new MediaType();

		$form = $this->createForm($formType, $media);

		if("POST" == $request->getMethod()){

			$form->submit($request);

			if($form->isValid()){
				$this->getEm()->persist($media);

				$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
				$uploadableManager->markEntityToUpload($media, $media->getMediaFile());

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
