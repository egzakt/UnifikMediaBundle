<?php

namespace Unifik\MediaBundle\Controller\Backend\Media;

use Unifik\MediaBundle\Form\VideoType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unifik\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class VideoController
 *
 * @package Unifik\MediaBundle\Controller\Backend\Media
 */
class VideoController extends BaseController
{
    /**
     * Displays a form to edit an existing document entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = ($request->query->has('mediaId')) ? $request->query->get('mediaId') : $request->request->get('mediaId');

            $media = $this->getEm()->getRepository('UnifikMediaBundle:Media')->find($id);

            if (!$media) {
                throw $this->createNotFoundException('Unable to find the media');
            }

            $form = $this->createForm(new VideoType(), $media);

            if ("POST" == $request->getMethod()) {

                $form->submit($request);

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    $this->getEm()->flush();

                    $this->get('unifik_system.router_invalidator')->invalidate();
                }
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            $associatedContents = MediaController::getAssociatedContents($media, $this->container);

            return new JsonResponse(array(
                'html' => $this->renderView('UnifikMediaBundle:Backend/Media/Video:edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                    'realName' => $realName
                ))
            ));
        }

        return new JsonResponse();
    }
}
