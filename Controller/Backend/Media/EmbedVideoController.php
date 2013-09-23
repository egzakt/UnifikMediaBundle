<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\EmbedVideo;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Form\EmbedVideoType;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * EmbedVideo Controller
 *
 * @throws \Symfony\Bundle\FrameworkBundle\Controller\NotFoundHttpException
 *
 */
class EmbedVideoController extends BaseController
{
    /**
     * Create a video from a url
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function createAction(Request $request)
    {
        if ("POST" !== $request->getMethod()) {
            throw new Exception('The request method must be post.');
        }

        $mediaParser = $this->get('egzakt_media.parser');
        if (!$mediaParser = $mediaParser->getParser($request->get('video_url'))) {
            return new JsonResponse(array(
                'error' => array(
                    'message' => 'Unable to parse the video url',
                )
            ));
        }

        $video = new EmbedVideo();

        $video->setUrl($request->get('video_url'));
        $video->setName($request->get('video_url'));
        $video->setMimeType('EmbedVideo');
        $video->setSize(0);
        $video->setMediaPath($mediaParser->getEmbedUrl());


        $this->updateThumbnail($video);

        $this->getEm()->persist($video);
        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($video->getRouteBackend(), $video->getRouteBackendParams()),
            'id' => $video->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($video->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
            'name' => $video->getName(),
        ));
    }

    /**
     * Displays a form to edit an existing ad entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
    {
        $media = $this->getEm()->getRepository('EgzaktMediaBundle:EmbedVideo')->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unanble to find the media');
        }

        $form = $this->createForm(new EmbedVideoType(), $media);

        if ("POST" == $request->getMethod()) {

            $oldUrl = $media->getUrl();
            $form->submit($request);

            if ($form->isValid()) {
                $this->getEm()->persist($media);

                if ($oldUrl !== $media->getUrl()) {

                    $mediaParser = $this->get('egzakt_media.parser');
                    if (!$mediaParser = $mediaParser->getParser($request->get('video_url'))) {

                        $form->addError(new FormError('New embed video is not valid'));

                        return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
                    }

                    $this->updateThumbnail($media);
                }

                $this->getEm()->flush();

                $this->get('egzakt_system.router_invalidator')->invalidate();

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
                }

                return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
            }
        }

        $mediaParser = $this->get('egzakt_media.parser');
        $parser = $mediaParser->getParser($media->getUrl());

        $associatedContents = MediaController::getAssociatedContents($media, $this->container);

        return $this->render('EgzaktMediaBundle:Backend/Media/EmbedVideo:edit.html.twig', array(
            'form' => $form->createView(),
            'media' => $media,
            'video_url' => $parser->getEmbedUrl(),
            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
        ));
    }

    /**
     * Update (or create) the thumbnail for a video
     * @param EmbedVideo $video
     */
    public function updateThumbnail(EmbedVideo $video)
    {
        $mediaParser = $this->get('egzakt_media.parser');
        $parser = $mediaParser->getParser($video->getUrl());

        //The file needs to be download from a remote server and stored temporary on the server to allow doctrine extension to handle it properly
        $tempFile = '/tmp/' . uniqid('EmbedVideoThumbnail-') . '.jpg';

        $thumbnailUrl = $parser->getThumbnailUrl();

        if (null == $thumbnailUrl) {
            $thumbnailUrl = $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/video-icon.png';
        }

        file_put_contents($tempFile, file_get_contents($thumbnailUrl));

        if ($video->getThumbnail()) {
            $this->getEm()->remove($video->getThumbnail());
        }

        //Generate the thumbnail
        $image = new Image();
        $image->setName("Preview - " . $video->getName());
        $image->setHidden(true);
        $image->setParentMedia($video);

        $this->getEm()->persist($image);

        $video->setThumbnail($image);

        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $uploadableManager->markEntityToUpload($image, new MediaFileInfo($tempFile));
    }

}
