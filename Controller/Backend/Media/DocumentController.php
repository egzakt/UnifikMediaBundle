<?php

namespace Flexy\MediaBundle\Controller\Backend\Media;

use Flexy\MediaBundle\Entity\Media;
use Flexy\MediaBundle\Form\DocumentType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Flexy\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class DocumentController
 * @package Flexy\MediaBundle\Controller\Backend\Media
 */
class DocumentController extends BaseController
{
    /**
     * Edit document detail
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $id = ($request->query->has('mediaId')) ? $request->query->get('mediaId') : $request->request->get('mediaId');

            $media = $this->getEm()->getRepository('FlexyMediaBundle:Media')->find($id);

            if (!$media) {
                throw $this->createNotFoundException('Unable to find the media');
            }

            $form = $this->createForm(new DocumentType(), $media);

            if ("POST" == $request->getMethod()) {

                $form->submit($request);

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    // Update link in text field
                    //$media->setNeedUpdate(true);

                    //Update the file only if a new one has been uploaded or if the name have change
//                    if ($media->getMedia()) {
//
//                        $this->getEm()->remove($media->getThumbnail());
//
//                        //Generate the thumbnail
//                        $image = new Media();
//                        $image->setName("Preview - ".$media->getMedia()->getClientOriginalName());
//                        $image->setParentMedia($media);
//
//                        $this->getEm()->persist($image);
//                        $media->setThumbnail($image);
//                    }

                    $this->getEm()->flush();



                    $this->get('flexy_system.router_invalidator')->invalidate();

                }
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            return new JsonResponse(array(
                'html' => $this->renderView('FlexyMediaBundle:Backend/Media/Document:edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                    'realName' => $realName
                ))
            ));
        }

        return new JsonResponse();
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
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/flexymedia/backend/images/word-icon.png';
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/flexymedia/backend/images/word-icon.png';
            case 'application/vnd.oasis.opendocument.text':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/flexymedia/backend/images/writer-icon.jpg';
            default:
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/flexymedia/backend/images/file-icon.png';
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

        return $this->container->get('kernel')->getRootDir().'/../web/bundles/flexymedia/backend/images/pdf-icon.png';
    }
}
