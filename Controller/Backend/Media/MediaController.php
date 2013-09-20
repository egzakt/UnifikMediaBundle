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

        $images = $this->mediaRepository->findByType('image');
        $videos = $this->mediaRepository->findByType('video');
        $embedVideos = $this->mediaRepository->findByType('embedvideo');
        $documents = $this->mediaRepository->findByType('document');

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
            'images' => $images,
            'videos' => $videos,
            'embedVideos' => $embedVideos,
            'documents' => $documents
        ));
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

        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $uploadableManager->markEntityToUpload($newMedia, new MediaFileInfo($this->get('kernel')->getRootDir().'/../web'.$media->getMediaPath()));
        $this->getEm()->persist($newMedia);
        $this->getEm()->flush();

        return $this->redirect($this->generateUrl($newMedia->getRouteBackend(), $newMedia->getRouteBackendParams()));
    }

    /**
     * Ajax version of the list action. Used to select a media to insert in another entity
     *
     * @param $type
     * @return Response
     */
    public function listAjaxAction($type)
    {
        if ("all" == $type) {
            $medias = $this->mediaRepository->findByHidden(false);
        } else {
            $medias = $this->mediaRepository->findByType($type);
        }

        $mediasOutput = array();

        /* @var $media Media */
        foreach ($medias as $media) {
            $mediasOutput[] = $media->toArray();
        }

        return new JsonResponse(array(
            'medias' => $mediasOutput,
        ));
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
        $em = $container->get('doctrine')->getManager();

        $metadataFactory = $em->getMetadataFactory();

        $metadata = $metadataFactory->getAllMetadata();

        $entitiesAssociated = array();
        $entitiesAssociated['field'] = array();
        $entitiesAssociated['text'] = array();

        /* @var $classMetadata ClassMetadata */
        foreach ($metadata as $classMetadata) {
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
