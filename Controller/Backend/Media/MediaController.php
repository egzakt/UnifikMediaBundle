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

    public function mediaAction(Request $request)
    {
        $images = $this->mediaRepository->findByType('image');
        $videos = $this->mediaRepository->findByType('video');
        $embedVideos = $this->mediaRepository->findByType('embedvideo');
        $documents = $this->mediaRepository->findByType('document');

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
        /** @var Media $media */
        $media = $this->mediaRepository->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Unable to find Media entity.');
        }

        $associatedContents = $this->getAssociatedContents($media);

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
        if (count($associatedContents['field'])) {
            foreach ($associatedContents['field'] as $methodGroup) {
                foreach ($methodGroup as $methodName => $entities) {
                    foreach ($entities as $entity) {
                        $method = 'set' . ucfirst($methodName);
                        $entity->$method(null);
                    }
                }
            }
        }

        // Remove the file from all texts where it is used
        $this->removeMediaFromTexts($media, $associatedContents['text']);

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
     * @return array
     */
    public function getAssociatedContents(Media $media)
    {
        $metadataFactory = $this->getEm()->getMetadataFactory();

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

                    $entities = $this->getEm()->getRepository($sourceEntity)->findBy(array(
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
                    $entities = $this->getEm()->getRepository($classMetadata->getName())->createQueryBuilder('t')
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


/**
 *
 *       OLD CODE BELOW
 * -----------------------------
 *
 */


//    /**
//     * Lists all media entities (not child entities).
//     *
//     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
//     */
//    public function indexAction()
//    {
//        $medias = $this->mediaRepository->findByType('media');
//
//        return $this->render('EgzaktMediaBundle:Backend/Media/Media:list.html.twig', array(
//            'medias' => $medias,
//        ));
//    }

//    /**
//     * Ajax version of the list action. Used to select a media to insert in another entity
//     *
//     * @param $type
//     * @return Response
//     */
//    public function listAjaxAction($type)
//    {
//        if ("all" == $type)
//            $medias = $this->mediaRepository->findByHidden(false);
//        else
//            $medias = $this->mediaRepository->findByType($type);
//
//        $mediasOutput = array();
//
//        /* @var $media Media */
//        foreach ($medias as $media) {
//            $mediasOutput[] = $media->toArray();
//        }
//
//        return new JsonResponse(array(
//            'medias' => $mediasOutput,
//        ));
//    }

//    /**
//     * Create a media and guess the type with the mime type
//     * @param Request $request
//     * @return JsonResponse|Response
//     */
//    public function createAction(Request $request)
//	{
//		if ("POST" == $request->getMethod()) {
//			$file = $request->files->get('file');
//
//			if (!$file instanceof UploadedFile || !$file->isValid()) {
//				return new JsonResponse(array(
//					"error" => array(
//						"message" => "Unable to upload the file",
//					),
//				));
//			}
//
//            switch ($file->getMimeType()) {
//                case 'image/jpeg':
//                case 'image/png':
//                case 'image/gif':
//                    $controller = "EgzaktMediaBundle:Backend/Media/Image:create";
//                    break;
//                case 'video/mpeg':
//                    $controller = "EgzaktMediaBundle:Backend/Media/Video:create";
//                    break;
//                default:
//                    $controller = "EgzaktMediaBundle:Backend/Media/Document:create";
//            }
//
//            return $this->forward($controller, array(
//                'file' => $file,
//            ));
//
//		}
//		return $this->render('EgzaktMediaBundle:Backend/Media/Media:create.html.twig');
//	}

//    /**
//     * Ajax version of the create action,
//     * @return Response
//     */
//    public function createAjaxAction()
//    {
//        return $this->render('EgzaktMediaBundle:Backend/Media/Media:create_ajax.html.twig');
//    }

//    /**
//     * Displays a form to edit an existing ad entity
//     *
//     * @param $id
//     * @param Request $request
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
//     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
//     */
//    public function editAction($id, Request $request)
//	{
//        $media = $this->mediaRepository->find($id);
//        if (!$media) {
//             throw $this->createNotFoundException('Unable to find the media');
//        }
//
//		$form = $this->createForm(new MediaType(), $media);
//
//		if ("POST" == $request->getMethod()) {
//
//			$form->submit($request);
//
//			if ($form->isValid()) {
//				$this->getEm()->persist($media);
//
//				//Update the file only if a new one has been uploaded
//				if ($media->getMediaFile()) {
//					$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
//					$uploadableManager->markEntityToUpload($media, $media->getMediaFile());
//				}
//
//				$this->getEm()->flush();
//
//				$this->get('egzakt_system.router_invalidator')->invalidate();
//
//				if ($request->request->has('save')) {
//					return $this->redirect($this->generateUrl('egzakt_media_backend_media'));
//				}
//
//				return $this->redirect($this->generateUrl($media->getRoute(), $media->getRouteParams()));
//			}
//		}
//
//		return $this->render('EgzaktMediaBundle:Backend/Media/Media:edit.html.twig', array(
//			'form' => $form->createView(),
//			'media' => $media,
//		));
//	}
}
