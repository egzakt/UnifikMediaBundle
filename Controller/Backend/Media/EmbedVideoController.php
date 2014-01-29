<?php

namespace Unifik\MediaBundle\Controller\Backend\Media;

use Unifik\MediaBundle\Entity\Media;
use Unifik\MediaBundle\Form\EmbedVideoType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unifik\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Unifik\MediaBundle\Lib\MediaParserInterface;
use Unifik\MediaBundle\Lib\MediaFile;

/**
 * Class EmbedVideoController
 * @package Unifik\MediaBundle\Controller\Backend\Media
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
        $t = $this->get('translator');

        if ("POST" !== $request->getMethod()) {
            throw new Exception('The request method must be post.');
        }

        $mediaParser = $this->get('unifik_media.parser');
        if (!$mediaParser = $mediaParser->getParser($request->get('video_url'))) {
            return new JsonResponse(array(
                'error' => true
            ));
        }

        $video = new Media();
        $video->setType('embedvideo');
        $video->setUrl($request->get('video_url'));
        $video->setName($request->get('video_url'));
        $video->setMimeType('EmbedVideo');
        $video->setSize(0);
        $video->setMediaPath($mediaParser->getEmbedUrl());

        $this->updateThumbnail($video, $mediaParser);

        $this->getEm()->persist($video);
        $this->getEm()->flush();

        return new JsonResponse(array(
            "message" => $t->trans('File uploaded')
        ));
    }

    /**
     * Edit embed video detail
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $id = ($request->query->has('mediaId')) ? $request->query->get('mediaId') : $request->request->get('mediaId');

            $media = $this->getEm()->getRepository('UnifikMediaBundle:Media')->find($id);

            if (!$media) {
                throw $this->createNotFoundException('Unanble to find the media');
            }

            $form = $this->createForm(new EmbedVideoType(), $media);

            if ("POST" == $request->getMethod()) {

                $oldUrl = $media->getUrl();
                $form->submit($request);

                $mediaParser = $this->get('unifik_media.parser');

                if ($oldUrl != $media->getUrl() && !$mediaParser = $mediaParser->getParser($form->get('url')->getData())) {

                    $t = $this->get('translator');

                    $form->get('url')->addError(new FormError($t->trans('This embed video url is not valid. Try the one in the iframe code if it\'s not already done.')));

                }

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    if ($oldUrl != $media->getUrl()) {

                        $media->setMediaPath($mediaParser->getEmbedUrl());

                        $this->updateThumbnail($media, $mediaParser);

                        $media->setNeedUpdate(true);

                    }

                    $this->getEm()->flush();

                    $this->get('unifik_system.router_invalidator')->invalidate();
                }
            }

            return new JsonResponse(array(
                'html' => $this->renderView('UnifikMediaBundle:Backend/Media/EmbedVideo:edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'video_url' => $media->getMediaPath()
                ))
            ));
        }

        return new JsonResponse();
    }

    public function updateThumbnail(Media $video, MediaParserInterface $mediaParser)
    {
        //The file needs to be download from a remote server and stored temporary on the server to allow doctrine extension to handle it properly
        $tempFile = '/tmp/' . uniqid('EmbedVideoThumbnail-') . '.jpg';

        $thumbnailUrl = $mediaParser->getThumbnailUrl();

        if (null == $thumbnailUrl) {
            $thumbnailUrl = $this->container->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/video-icon.png';
        }

        file_put_contents($tempFile, file_get_contents($thumbnailUrl));

        $thumbnailFile = new MediaFile($tempFile);
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        if ($video->getThumbnail()) {
            $this->getEm()->remove($video->getThumbnail());
        }

        //Generate the thumbnail
        $image = new Media();

        $image->setType('image');
        $image->setHidden(true);
        $image->setName("Preview - " . $video->getName());
        $image->setMedia($thumbnailFile);

        $this->getEm()->persist($image);

        $video->setThumbnail($image);
    }
}
