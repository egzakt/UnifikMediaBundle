<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Video;
use Egzakt\MediaBundle\Form\VideoType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Video Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
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

        $media = $this->getEm()->getRepository('EgzaktMediaBundle:Video')->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

        $form = $this->createForm(new VideoType(), $media);

        if ("POST" == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {
                $this->getEm()->persist($media);

                //Update the file only if a new one has been uploaded
                if ($media->getMediaFile()) {
                    $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
                    $uploadableManager->markEntityToUpload($media, $media->getMediaFile());
                    $media->setNeedUpdate(true);
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

        return $this->render('EgzaktMediaBundle:Backend/Media/Video:edit.html.twig', array(
            'form' => $form->createView(),
            'media' => $media,
            'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
            'realName' => $realName,
            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
        ));
    }
}
