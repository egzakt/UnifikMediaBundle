<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Video;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Upload Controller
 */
class UploadController extends BaseController
{
    /**
     * FancyBox version of the create action
     * @return Response
     */
    public function fancyboxUploadAction()
    {
        return $this->render('EgzaktMediaBundle:Backend/Media/Upload:upload_fancybox.html.twig');
    }

    /**
     * Upload action
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function uploadAction(Request $request)
    {
        if ("POST" == $request->getMethod()) {
            $file = $request->files->get('file');

            if (!$file instanceof UploadedFile || !$file->isValid()) {
                return new JsonResponse(array(
                    "error" => array(
                        "message" => "Unable to upload the file",
                    ),
                ));
            }

            switch ($file->getMimeType()) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $uploadFunction = 'imageUpload';
                    break;
                case 'video/mpeg':
                case 'video/mp4':
                case 'application/x-shockwave-flash':
                case 'video/x-flv':
                case 'video/quicktime':

                case 'video/x-ms-wmv':
                case 'video/x-msvideo':

                    $uploadFunction = 'videoUpload';
                    break;
                default:
                    $uploadFunction = 'documentUpload';
            }

            return $this->$uploadFunction($file);

        }

        throw new NotFoundHttpException();
    }

    /**
     * imageUpload
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    private function imageUpload(UploadedFile $file)
    {
        $media = new Image();
        $media->setMediaFile($file);
        $media->setName($file->getClientOriginalName());

        list($width, $height, $type, $attr) = getimagesize($file->getRealPath());

        $media->setWidth($width);
        $media->setHeight($height);
        $media->setAttr($attr);

        $this->getEm()->persist($media);

        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $uploadableManager->markEntityToUpload($media, $media->getMediaFile());

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'id' => $media->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
        ));
    }

    /**
     * videoUpload
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    private function videoUpload(UploadedFile $file)
    {
        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');

        $media = new Video();
        $media->setContainer($this->container);
        $media->setMediaFile($file);
        $media->setName($file->getClientOriginalName());

        $this->getEm()->persist($media);
        $uploadableManager->markEntityToUpload($media, $media->getMediaFile());

        //Generate the thumbnail
        $image = new Image();
        $image->setName("Preview - ".$file->getClientOriginalName());
        $image->setHidden(true);
        $image->setParentMedia($media);

        $this->getEm()->persist($image);

        $media->setThumbnail($image);

        $uploadableManager->markEntityToUpload($image, new MediaFileInfo($this->getVideoThumbnailPath($file)));

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'id' => $media->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
        ));
    }

    /**
     * documentUpload
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    private function documentUpload(UploadedFile $file)
    {
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
        $image->setParentMedia($media);

        $this->getEm()->persist($image);

        $media->setThumbnail($image);

        $uploadableManager->markEntityToUpload($image, new MediaFileInfo($this->getThumbnailPath($file)));

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array(
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'id' => $media->getId(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
            "message" => "File uploaded",
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
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/word-icon.png';
            case 'application/vnd.oasis.opendocument.text':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/writer-icon.jpg';
            default:
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/file-icon.png';
        }
    }

    /**
     * Get the path of the thumbnail icon depending of the content
     *
     * @param UploadedFile $file
     * @return string
     */
    private function getVideoThumbnailPath(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            default:
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/egzaktmedia/backend/images/video-icon.png';
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