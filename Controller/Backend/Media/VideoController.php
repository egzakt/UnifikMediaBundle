<?php

namespace Flexy\MediaBundle\Controller\Backend\Media;

use Flexy\MediaBundle\Form\VideoType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Flexy\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class VideoController
 *
 * @package Flexy\MediaBundle\Controller\Backend\Media
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
    public function editAction($id, Request $request)
    {

        $media = $this->getEm()->getRepository('FlexyMediaBundle:Media')->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

        $form = $this->createForm(new VideoType(), $media);

        if ("POST" == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {
                $this->getEm()->persist($media);

                //Update the file only if a new one has been uploaded
                if ($media->getMedia()) {
                    $media->setNeedUpdate(true);
                }

                $this->getEm()->flush();

                $this->get('flexy_system.router_invalidator')->invalidate();

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('flexy_media_backend_media'));
                }

                return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
            }
        }

        $explode = explode('/', $media->getMediaPath());
        $realName = array_pop($explode);

        $associatedContents = MediaController::getAssociatedContents($media, $this->container);

        return $this->render('FlexyMediaBundle:Backend/Media/Video:edit.html.twig', array(
            'form' => $form->createView(),
            'media' => $media,
            'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
            'realName' => $realName,
            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
        ));
    }
}
