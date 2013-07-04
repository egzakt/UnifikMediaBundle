<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Form\DocumentType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Document controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class DocumentController extends BaseController
{
    /**
     * @var DocumentRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
		$this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Document');
    }

    /**
     * Display document list
     *
     * @return Response
     */
    public function indexAction()
    {
        $medias = $this->mediaRepository->findByHidden(false);
        return $this->render('EgzaktMediaBundle:Backend/Media/Document:list.html.twig', array(
            'medias' => $medias,
        ));
    }

    /**
     * Create a document media
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    public function createAction(UploadedFile $file)
    {
        /** @var Image $image */
        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');

        $media = new Document();
        $media->setContainer($this->container);
        $media->setMediaFile($file);
        $media->setName($file->getClientOriginalName());

        $this->getEm()->persist($media);
        $uploadableManager->markEntityToUpload($media, $media->getMediaFile());

        //Generate the thumbnail
        $image = new Image();
        $image->setName("Preview - ".$file->getClientOriginalName());
        $image->setHidden(true);

        $this->getEm()->persist($image);
        $uploadableManager->markEntityToUpload($image, new MediaFileInfo($this->getThumbnailPath($file)));

        $media->setThumbnail($image);

        $this->getEm()->flush();

        return new JsonResponse(json_encode(array(
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            "message" => "File uploaded",
        )));
    }

    /**
     * Displays a form to edit an existing document entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
	{
        /** @var Document $media */
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

		$form = $this->createForm(new DocumentType(), $media);

		if ("POST" == $request->getMethod()) {

			$form->submit($request);

			if ($form->isValid()) {
				$this->getEm()->persist($media);

                $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
				//Update the file only if a new one has been uploaded
				if ($media->getMediaFile()) {
					$uploadableManager->markEntityToUpload($media, $media->getMediaFile());
				}

				$this->getEm()->flush();

				$this->get('egzakt_system.router_invalidator')->invalidate();

				if ($request->request->has('save')) {
					return $this->redirect($this->generateUrl('egzakt_media_backend_document'));
				}

				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
			}
		}

		return $this->render('EgzaktMediaBundle:Backend/Media/Document:edit.html.twig', array(
			'form' => $form->createView(),
			'media' => $media,
		));
	}

    /**
     * Get the path of the thumbnail icon depending of the content
     *
     * @param UploadedFile $file
     * @return string
     */
    private function getThumbnailPath(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            case 'application/pdf':
                return $this->createPdfPreview($file->getPathname());
            case 'application/msword':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/word-icon.png';
            case 'application/vnd.oasis.opendocument.text':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/writer-icon.jpg';
            default:
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/file-icon.png';
        }
    }

    /**
     * Generate a pdf preview if "convert" is present on the host system
     *
     * @param $path
     * @return string
     */
    private function createPdfPreview($path)
    {
        if (shell_exec("which convert")) {
            $target = $path.'.jpg';
            $command = sprintf("convert %s[0] %s", $path, $target);
            if (!shell_exec($command)) {
                return $target;
            }
        }

        return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/pdf-icon.png';
    }

}
