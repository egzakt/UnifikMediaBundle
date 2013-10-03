<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Egzakt\MediaBundle\Lib\MediaFile;
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
     * Upload
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {


        if ($request->isXmlHttpRequest() && ( $request->files->has('files') )) {

            if ("POST" == $request->getMethod()) {
                $file = $request->files->get('files')[0];

                if ($file instanceof UploadedFile && $file->isValid()) {

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

            }

        }

        return new JsonResponse();
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
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());

        list($width, $height, $type, $attr) = getimagesize($file->getRealPath());

        $media->setWidth($width);
        $media->setHeight($height);
        $media->setMimeType($file->getClientMimeType());
        $media->setAttr($attr);
        $media->setSize($file->getClientSize());

        $this->getEm()->persist($media);

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $media->getSize(),
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
        ))));
    }

    /**
     * videoUpload
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    private function videoUpload(UploadedFile $file)
    {
        $media = new Video();
        $media->setContainer($this->container);
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $this->getEm()->persist($media);

        //Generate the thumbnail

        $thumbnailFile = new MediaFile($this->getVideoThumbnailPath($file));
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        $image = new Image();
        $image->setName("Preview - ".$file->getClientOriginalName());
        $image->setHidden(true);
        $image->setParentMedia($media);

        list($width, $height, $type, $attr) = getimagesize($thumbnailFile->getRealPath());

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setMimeType($thumbnailFile->getClientMimeType());
        $image->setAttr($attr);
        $image->setSize($thumbnailFile->getClientSize());

        $this->getEm()->persist($image);

        $media->setThumbnail($image);

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $file->getClientSize(),
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb')
        ))));
    }

    /**
     * documentUpload
     *
     * @param UploadedFile $file
     * @return JsonResponse
     */
    private function documentUpload(UploadedFile $file)
    {
        $media = new Document();
        $media->setContainer($this->container);
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $this->getEm()->persist($media);

        //Generate the thumbnail

        $thumbnailFile = new MediaFile($this->getThumbnailPath($file));
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        $image = new Image();
        $image->setMedia($thumbnailFile);
        $image->setName("Preview - " . $file->getClientOriginalName());
        $image->setHidden(true);
        $image->setParentMedia($media);

        list($width, $height, $type, $attr) = getimagesize($thumbnailFile->getRealPath());

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setMimeType($thumbnailFile->getClientMimeType());
        $image->setAttr($attr);
        $image->setSize($thumbnailFile->getClientSize());

        $this->getEm()->persist($image);

        $media->setThumbnail($image);

        $this->getEm()->flush();

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $file->getClientSize(),
            'url' => $this->generateUrl($media->getRouteBackend(), $media->getRouteBackendParams()),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb')
        ))));
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