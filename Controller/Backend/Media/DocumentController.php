<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Form\DocumentType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * Displays a form to edit an existing document entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
    {

        $media = $this->getEm()->getRepository('EgzaktMediaBundle:Document')->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

        $form = $this->createForm(new DocumentType(), $media);

        if ("POST" == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {
                $this->getEm()->persist($media);

                // Update link in text field
                $media->setNeedUpdate(true);

                //Update the file only if a new one has been uploaded or if the name have change
                if ($media->getMediaFile()) {
                    $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
                    $uploadableManager->markEntityToUpload($media, $media->getMediaFile());

                    $this->getEm()->remove($media->getThumbnail());

                    //Generate the thumbnail
                    $image = new Image();
                    $image->setName("Preview - ".$media->getMediaFile()->getClientOriginalName());
                    $image->setHidden(true);
                    $image->setParentMedia($media);

                    $this->getEm()->persist($image);
                    $media->setThumbnail($image);

                    $uploadableManager->markEntityToUpload($image, new MediaFileInfo($this->getThumbnailPath($media->getMediaFile())));
                }

                $this->getEm()->flush();



                $this->get('egzakt_system.router_invalidator')->invalidate();

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
                }

                return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
            }
        }

        $explode = explode('/', $media->getMediaPath());
        $realName = array_pop($explode);

        $associatedContents = MediaController::getAssociatedContents($media, $this->container);

        return $this->render('EgzaktMediaBundle:Backend/Media/Document:edit.html.twig', array(
            'form' => $form->createView(),
            'media' => $media,
            'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
            'realName' => $realName,
            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
        ));
    }

    /**
     * Get Thumbnail path
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
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
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
