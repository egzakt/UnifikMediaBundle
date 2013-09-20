<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Form\ImageType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Egzakt\MediaBundle\Controller\Backend\Media\MediaController;

/**
 * Image controller
 */
class ImageController extends BaseController
{
    /**
     * Displays a form to edit an existing image entity.
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
    {
        $media = $this->getEm()->getRepository('EgzaktMediaBundle:Image')->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Unable to find the media');
        }

        $form = $this->createForm(new ImageType(), $media);

        if ("POST" == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {
                $this->getEm()->persist($media);

                //Update the file only if a new one has been uploaded
                if ($media->getMediaFile()) {
                    $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
                    $uploadableManager->markEntityToUpload($media, $media->getMediaFile());
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

        return $this->render('EgzaktMediaBundle:Backend/Media/Image:edit.html.twig', array(
            'form' => $form->createView(),
            'media' => $media,
            'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
            'realName' => $realName,
            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
        ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateImageAction($id, Request $request)
    {
        /** @var Image $image */
        $image = $this->getEm()->getRepository('EgzaktMediaBundle:Image')->find($id);

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
