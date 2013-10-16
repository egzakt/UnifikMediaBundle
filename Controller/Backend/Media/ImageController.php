<?php

namespace Flexy\MediaBundle\Controller\Backend\Media;

use Flexy\MediaBundle\Entity\Media;
use Flexy\MediaBundle\Form\ImageType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Flexy\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Flexy\MediaBundle\Controller\Backend\Media\MediaController;

/**
 * Image controller
 */
class ImageController extends BaseController
{
    /**
     * Edit image detail
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

            $form = $this->createForm(new ImageType(), $media);

            if ("POST" == $request->getMethod()) {

                $form->submit($request);

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    $this->getEm()->flush();

                    $this->get('flexy_system.router_invalidator')->invalidate();
                }
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            return new JsonResponse(array(
                'html' => $this->renderView('FlexyMediaBundle:Backend/Media/Image:edit.html.twig', array(
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
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateImageAction($id, Request $request)
    {
        /** @var Media $image */
        $image = $this->getEm()->getRepository('FlexyMediaBundle:Media')->find($id);

        if (!$image) {
            throw $this->createNotFoundException('Unable to find the Media Entity');
        }

        file_put_contents($image->getMediaPath(true), file_get_contents($request->get('image')));

        // Update image format
        list($width, $height, $type, $attr) = getimagesize($image->getMediaPath(true));

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setAttr($attr);

        $this->getEm()->persist($image);
        $this->getEm()->flush();

        //The imagine cache needs to be cleared because the image keep the same filename
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        foreach ($this->container->getParameter('liip_imagine.filter_sets') as $filter => $value ) {
            $cacheManager->remove($image->getMediaPath(), $filter);
        }

        return new JsonResponse(array());
    }
}
