<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Media;
use Gedmo\Uploadable\FileInfo\FileInfoArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

	public function createAction()
	{
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

		$form = $this->createForm(new MediaType(), $media);

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
		));
    }

}
