<?php

namespace Egzakt\MediaBundle\Controller\Backend\Media;

use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Lib\MediaFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\MediaBundle\Entity\MediaRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Egzakt\MediaBundle\Lib\MediaPager;

/**
 * Media Controller
 */
class MediaController extends BaseController
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();
        $this->mediaRepository = $this->getEm()->getRepository('EgzaktMediaBundle:Media');
    }

    /**
     * Media library
     *
     * @param Request $request
     * @return Response
     */
    public function mediaAction(Request $request)
    {
        $t = $this->get('translator');

        $this->mediaRepository->setReturnQueryBuilder(true);

        $imageQb = $this->mediaRepository->findByType('image');
        $documentQb = $this->mediaRepository->findByType('document');
        $videoQb = $this->mediaRepository->findByType('video');
        $embedVideoQb = $this->mediaRepository->findByType('embedvideo');

        // Pagers
        $resultPerPage = $this->container->getParameter('egzakt_media.resultPerPage');
        $imagePager = new MediaPager($imageQb, 1, $resultPerPage);
        $documentPager = new MediaPager($documentQb, 1, $resultPerPage);
        $videoPager = new MediaPager($videoQb, 1, $resultPerPage);
        $embedVideoPager = new MediaPager($embedVideoQb, 1, $resultPerPage);

        // Bulk actions
        if ('POST' == $request->getMethod()) {
            if ('delete' == $request->request->get('action')) {

                $nbMediaRemoved = 0;

                foreach ($request->request->all() as $parameterName => $value) {
                    if ( false !== strpos($parameterName, 'massdelete' )) {
                        $media = $this->mediaRepository->find($value);


                        if ($media) {

                            $associatedContents = $this::getAssociatedContents($media, $this->container);

                            // Unlink content in case 'onDelete set null' hasn't been set
                            $this->removeMediaRelation($associatedContents['field']);

                            // Remove the file from all texts where it is used
                            $this->removeMediaFromTexts($media, $associatedContents['text']);

                            $this->getEm()->remove($media);

                            $this->getEm()->flush();

                            $nbMediaRemoved++;
                        }
                    }
                }

                $this->get('session')->getFlashBag()->set('success',
                    $nbMediaRemoved . ' ' . $t->trans('media(s) were removed in the process') . '.'
                );

                return $this->redirect($this->generateUrl('egzakt_media_backend_media'));

            }
        }

        return $this->render('EgzaktMediaBundle:Backend/Media/Media:media.html.twig', array(
            'imageView' => $this->renderView('EgzaktMediaBundle:Backend/Media/Media/tabs/content:images_content.html.twig', array('medias' => $imagePager->getResult())),
            'imagePageTotal' => $imagePager->getPageTotal(),

            'documentView' => $this->renderView('EgzaktMediaBundle:Backend/Media/Media/tabs/content:documents_content.html.twig', array('medias' => $documentPager->getResult())),
            'documentPageTotal' => $documentPager->getPageTotal(),

            'videoView' => $this->renderView('EgzaktMediaBundle:Backend/Media/Media/tabs/content:videos_content.html.twig', array('medias' => $videoPager->getResult())),
            'videoPageTotal' => $videoPager->getPageTotal(),

            'embedVideoView' => $this->renderView('EgzaktMediaBundle:Backend/Media/Media/tabs/content:embed_videos_content.html.twig', array('medias' => $embedVideoPager->getResult())),
            'embedVideoPageTotal' => $embedVideoPager->getPageTotal()
        ));
    }

    /**
     * mediaPager
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function mediaPagerAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('page') && $request->query->has('type')) {

            $this->mediaRepository->setReturnQueryBuilder(true);

            switch ($request->query->get('type')) {
                case 'image':
                    $mediaQb = $this->mediaRepository->findByType('image');
                    $template = 'EgzaktMediaBundle:Backend/Media/Media/tabs/content:images_content.html.twig';
                    break;
                case 'document':
                    $mediaQb = $this->mediaRepository->findByType('document');
                    $template = 'EgzaktMediaBundle:Backend/Media/Media/tabs/content:documents_content.html.twig';
                    break;
                case 'video':
                    $mediaQb = $this->mediaRepository->findByType('video');
                    $template = 'EgzaktMediaBundle:Backend/Media/Media/tabs/content:videos_content.html.twig';
                    break;
                case 'embedvideo':
                    $mediaQb = $this->mediaRepository->findByType('embedvideo');
                    $template = 'EgzaktMediaBundle:Backend/Media/Media/tabs/content:embed_videos_content.html.twig';
                    break;
                default:
                    throw new \Exception('Error');
            }

            $mediaPager = new MediaPager(
                $mediaQb,
                $request->query->get('page'),
                $this->container->getParameter('egzakt_media.resultPerPage', 20)
            );

            return new JsonResponse(array(
                'html' => $this->renderView($template, array('medias' => $mediaPager->getResult()))
            ));
        }

        return new JsonResponse(array());
    }

    /**
     * Delete a media (including child entities)
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction($id)
    {
        $t = $this->get('translator');

        /** @var Media $media */
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unable to find Media entity.');
        }

        $associatedContents = $this::getAssociatedContents($media, $this->container);

        if ($this->get('request')->get('message')) {
            $template = $this->renderView('EgzaktMediaBundle:Backend/Media/Core:delete_message.html.twig', array(
                'entity' => $media,
                'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
            ));

            return new JsonResponse(array(
                'template' => $template,
                'isDeletable' => $media->isDeletable()
            ));
        }


        // Unlink content in case 'onDelete set null' hasn't been set
        $this->removeMediaRelation($associatedContents['field']);

        // Remove the file from all texts where it is used
        $this->removeMediaFromTexts($media, $associatedContents['text']);

        $this->get('session')->getFlashBag()->set('success',
            $media->getName() . ' ' . $t->trans(' has been removed') . '.'
        );

        $this->getEm()->remove($media);
        $this->getEm()->flush();

        $this->get('egzakt_system.router_invalidator')->invalidate();


        return $this->redirect($this->generateUrl($media->getRouteBackend('list')));
    }

    /**
     * Guess extension of a file via his file path
     *
     * @param $filePath
     * @return mixed
     */
    public static function guessExtension($filePath)
    {
        $explode = explode('.', $filePath);
        return array_pop($explode);
    }

    /**
     * Duplicate a media
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function duplicateAction($id)
    {
        /** @var Media $media */
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unable to find Media entity.');
        }

        $newMedia = clone($media);
        $newMedia->setName($media->getName() . ' - copy');

        $this->getEm()->persist($newMedia);
        $this->getEm()->flush();

        return $this->redirect($this->generateUrl($newMedia->getRouteBackend(), $newMedia->getRouteBackendParams()));
    }

    /**
     * listAjax
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAjaxAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $type = $request->query->get('type');

            if ("all" == $type) {
                $medias = $this->mediaRepository->findByHidden(false);
                $mediaType = array('image', 'video', 'document', 'embedvideo');
            } else {
                $medias = $this->mediaRepository->findByType($type);
                $mediaType = array($type);
            }

            $mediasOutput = array();

            /* @var $media Media */
            foreach ($medias as $media) {
                $mediasOutput[] = $media->toArray();
            }

            return new JsonResponse(array(
                'html' => $this->renderView('EgzaktMediaBundle:Backend/Media/Media:media_select.html.twig', array(
                    'medias' => $medias,
                    'mediaType' => $mediaType
                )),
                'medias' => $mediasOutput
            ));
        }

        return new JsonResponse();
    }

    /**
     * Return all entities associated to the given media
     *
     * @param Media $media
     * @param ContainerInterface $container
     * @return array
     */
    public static function getAssociatedContents(Media $media, ContainerInterface $container)
    {
        $ignoreClass = array(
            'Egzakt\MediaBundle\Entity\Media',
            'Egzakt\MediaBundle\Entity\Image',
            'Egzakt\MediaBundle\Entity\Document',
            'Egzakt\MediaBundle\Entity\Video',
            'Egzakt\MediaBundle\Entity\EmbedVideo'
        );

        $em = $container->get('doctrine')->getManager();

        $metadataFactory = $em->getMetadataFactory();

        $metadata = $metadataFactory->getAllMetadata();

        $entitiesAssociated = array();
        $entitiesAssociated['field'] = array();
        $entitiesAssociated['text'] = array();

        /* @var $classMetadata ClassMetadata */
        foreach ($metadata as $classMetadata) {
            if (false == in_array($classMetadata->getName(), $ignoreClass)) {
                foreach ($classMetadata->getAssociationMappings() as $association) {

                    if ('Egzakt\MediaBundle\Entity\Media' == $association['targetEntity']) {
                        $fieldName = $association['fieldName'];
                        $sourceEntity = $association['sourceEntity'];

                        $explode = explode('\\', $sourceEntity);
                        $entityName = array_pop($explode);

                        $entities = $em->getRepository($sourceEntity)->findBy(array(
                            $fieldName => $media->getId()
                        ));

                        if ($entities) {
                            $entitiesAssociated['field'][$entityName][$fieldName] = $entities;
                        }
                    }
                }

                foreach ($classMetadata->getFieldNames() as $fieldName) {

                    $explode = explode('\\', $classMetadata->getName());
                    $entityName = array_pop($explode);

                    $fieldMapping = $classMetadata->getFieldMapping($fieldName);

                    if ('text' == $fieldMapping['type']) {
                        $entities = $em->getRepository($classMetadata->getName())->createQueryBuilder('t')
                            ->where('t.' . $fieldName . ' LIKE :expression')
                            ->setParameter('expression', '%data-mediaid="'.$media->getId().'"%')
                            ->getQuery()->getResult();

                        if ($entities) {
                            $entitiesAssociated['text'][$entityName][$fieldName] = $entities;
                        }
                    }
                }
            }
        }

        return $entitiesAssociated;
    }

    /**
     * Replace related relations with NULL value
     *
     * @param array $associatedField
     */
    private function removeMediaRelation(array $associatedField) {
        if (count($associatedField)) {
            foreach ($associatedField as $methodGroup) {
                foreach ($methodGroup as $methodName => $entities) {
                    foreach ($entities as $entity) {
                        $method = 'set' . ucfirst($methodName);
                        $entity->$method(null);
                    }
                }
            }
        }
    }

    /**
     * Remove $media from any text containing it
     *
     * @param Media $media
     * @param array $associatedText
     */
    private function removeMediaFromTexts(Media $media, array $associatedText) {

        foreach ($associatedText as $entityGroup) {
            foreach ($entityGroup as $fieldName => $entities) {

                $getMethod = 'get' . ucfirst($fieldName);
                $setMethod = 'set' . ucfirst($fieldName);

                foreach ($entities as $entity) {
                    $entity->$setMethod(preg_replace($media->getReplaceRegex(), '', $entity->$getMethod()));

                }
            }
        }

        $this->getEm()->flush();
    }
}
