<?php

namespace Unifik\MediaBundle\Controller\Backend\Media;

use Unifik\ComposerManagerBundle\Lib\Json\JsonFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Unifik\MediaBundle\Entity\Media;
use Unifik\MediaBundle\Entity\Folder;
use Unifik\SystemBundle\Lib\Backend\BackendController;
use Unifik\MediaBundle\Entity\MediaRepository;
use Unifik\MediaBundle\Entity\FolderRepository;
use Unifik\MediaBundle\Lib\MediaPager;

/**
 * Media Controller
 */
class MediaController extends BackendController
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->createAndPushNavigationElement('Medias', 'unifik_media_backend_media', array());

        $this->mediaRepository = $this->getEm()->getRepository('UnifikMediaBundle:Media');
        $this->folderRepository = $this->getEm()->getRepository('UnifikMediaBundle:Folder');
    }

    /**
     * Media library
     *
     * @param Request $request
     * @return Response
     */
    public function mediaAction(Request $request)
    {
        return $this->render('UnifikMediaBundle:Backend/Media/Media:media.html.twig');
    }

    /**
     * Javascripts
     *
     * @param Request $request
     * @return Response
     */
    public function jsAction(Request $request)
    {
        $content = $this->renderView('UnifikMediaBundle:Backend/Media/Javascripts:main.js.twig');
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/javascript');
        return $response;
    }

    /**
     * Load Media
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('page')
            && $request->query->has('type')
            && $request->query->has('text')
            && $request->query->has('date')
            && $request->query->has('folderId')) {

            $this->mediaRepository->setReturnQueryBuilder(true);

            $mediaQb = $this->mediaRepository->findByFolderType(
                $request->query->get('folderId', 'base'),
                $request->query->get('type', 'any'),
                $request->query->get('date', 'newer'),
                $request->query->get('text', '')
            );

            $tree = array();

            if ($request->query->get('init', false)) {

                $baseFolders = $this->folderRepository->findBy(
                    array('parent' => null),
                    array('name' => 'ASC')
                );

                /* @var $folder Folder */
                foreach ($baseFolders as $folder) {
                    $tree[] = $folder->toArray();
                }
            }

            $mediaPager = new MediaPager(
                $mediaQb,
                $request->query->get('page'),
                $this->container->getParameter('unifik_media.media_select.resultPerPage', 30)
            );

            $template = ('true' == $request->query->get('init', 'false')) ? 'UnifikMediaBundle:Backend/Media/MediaSelect:media_select.html.twig'
                : 'UnifikMediaBundle:Backend/Media/MediaSelect/content:media_select_content.html.twig';

            return new JsonResponse(array(
                'html' => $this->renderView($template, array(
                        'medias' => $mediaPager->getResult(),
                        'folderId' => $request->query->get('folderId', 'base'),
                        'type' => $request->query->get('type', 'image'),
                        'text' => $request->query->get('text', ''),
                        'date' => $request->query->get('date', 'newer'),
                        'view' => $request->query->get('view', 'ckeditor'),
                        'pagesTotal' => $mediaPager->getPageTotal(),
                        'page' => $request->get('page', 1)
                 )),
                'tree' => $tree
            ));
        }

        return new JsonResponse(array());
    }

    /**
     * Move folder or media
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function moveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('type')
            && $request->query->has('sourceIds')
            && $request->query->has('targetId')) {

            if ('folder' == $request->query->get('type')) {
                $folderSource = $this->folderRepository->find($request->query->get('sourceIds')[0]);

                if ('base' == $request->query->get('targetId')) {
                    $folderSource->setParent();
                } else {
                    $folderTarget = $this->folderRepository->find($request->query->get('targetId'));
                    $folderSource->setParent($folderTarget);
                }

            } else {

                $folderTarget = $this->folderRepository->find($request->query->get('targetId'));

                foreach ($request->query->get('sourceIds') as $sourceId) {
                    $media = $this->mediaRepository->find($sourceId);
                    $media->setFolder($folderTarget);
                }
            }

            $this->getEm()->flush();
        }

        return new JsonResponse();
    }

    /**
     * Create New Folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFolderAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('parentFolderId')) {

            $newFolder = new Folder();
            $newFolder->setName('New Folder');

            if ('base' != $request->query->get('parentFolderId')) {

                $parentFolder = $this->folderRepository->find($request->query->get('parentFolderId'));

                if ($parentFolder) {
                    $newFolder->setParent($parentFolder);
                }

            }

            $this->getEm()->persist($newFolder);
            $this->getEm()->flush();

            return new JsonResponse(array('key' => $newFolder->getId()));
        }

        return new JsonResponse();
    }

    /**
     * Delete a folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFolderAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('folderId')) {

            $t = $this->get('translator');

            if ('base' != $request->query->get('folderId')) {

                $folder = $this->folderRepository->find($request->query->get('folderId'));

                if ($folder) {

                    $deletable = $this->checkDeletable($folder);

                    if ($deletable->isSuccess()) {
                        $this->getEm()->remove($folder);
                        $this->getEm()->flush();
                    }

                    return new JsonResponse(array(
                        'removed' => $deletable->isSuccess(),
                        'message' => $deletable->getErrors()[0]
                    ));
                }

            } else {
                return new JsonResponse(array(
                    'removed' => false,
                    'message' => $t->trans('This folder cannot be removed.')
                ));
            }
        }

        return new JsonResponse();
    }

    /**
     * Rename a folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function renameFolderAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('folderId')
            && $request->query->has('folderTitle')) {

            $t = $this->get('translator');

            if ('base' != $request->query->get('folderId')) {

                if ('' == $request->query->get('folderTitle')) {

                    return new JsonResponse(array(
                        'renamed' => false,
                        'message' => $t->trans('You must enter a name.')
                    ));

                } else {
                    $folder = $this->folderRepository->find($request->query->get('folderId'));

                    if ($folder) {

                        $folder->setName($request->query->get('folderTitle'));

                        $this->getEm()->flush();

                        return new JsonResponse(array(
                            'renamed' => true
                        ));

                    }

                }

            } else {
                return new JsonResponse(array(
                    'removed' => false,
                    'message' => $t->trans('This folder cannot be renamed.')
                ));
            }
        }

        return new JsonResponse();
    }

    /**
     * Return all associated contents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function associationsAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('mediaId')) {

            $media = $this->mediaRepository->find($request->query->get('mediaId'));

            if ($media) {
                $associatedContents = $this::getAssociatedContents($media, $this->container);

                $associatedContents = array_merge($associatedContents['text'], $associatedContents['field']);

                $tree = array();

                foreach ($associatedContents as $entity => $fields) {

                    $entityNodes = array(
                        'title' => $entity,
                        'expand' => true,
                        'addClass' => 'entity',
                        'noLink' => true,
                        'unselectable' => true,
                        'hideCheckbox' => true,
                        'children' => array()
                    );

                    foreach ($fields as $field => $contents) {

                        $fieldNodes = array(
                            'title' => '« ' . $field . ' » field',
                            'expand' => true,
                            'addClass' => 'field',
                            'noLink' => true,
                            'children' => array()
                        );

                        foreach ($contents as $content) {

                            $targetEntity = (method_exists($content, 'getTranslatable'))
                                ? $content->getTranslatable() : $content;

                            $targetEntity2str = (method_exists($targetEntity, '__toString'));
                            $targetEntityRoute = (method_exists($targetEntity, 'getRouteBackend'))
                                ? $this->generateUrl($targetEntity->getRouteBackend(), $targetEntity->getRouteBackendParams(
                                    array('sectionId' => $this->guessSection($targetEntity)))
                                )
                                : false;

                            $contentNode = array(
                                'title' => ($targetEntity2str) ?  substr(strip_tags($targetEntity->__toString()), 0, 100) . '...' : ' ( '. $entity . ' ) ',
                                'href' => ($targetEntityRoute) ?: null,
                                'class' => get_class($content),
                                'field' => $field,
                                'id' => $content->getId()
                            );

                            $fieldNodes['children'][] = $contentNode;

                        }

                        $entityNodes['children'][] = $fieldNodes;
                    }

                    $tree[] = $entityNodes;
                }

                return new JsonResponse(array(
                    'html' => $this->renderView('UnifikMediaBundle:Backend/Media/MediaSelect/content:media_select_associations.html.twig', array(
                        'media' => $media,
                        'associatedContents' => $associatedContents
                    )),
                    'tree' => $tree
                ));
            }
        }

        return new JsonResponse();
    }

    /**
     * Replace media associations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function associationsReplaceAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('entities')
            && $request->query->has('mediaId')) {

            $metadataFactory = $this->getEm()->getMetadataFactory();

            $media = $this->mediaRepository->find($request->query->get('mediaId'));
            $replacement = $this->mediaRepository->find($request->query->get('mediaReplacementId', 0));

            if ($media) {

                foreach ($request->query->get('entities') as $entityString) {

                    $explode = explode(':', $entityString);

                    $repository = $this->getEm()->getRepository($explode[0]);

                    if ($repository && $entity = $repository->find($explode[2])) {

                        $metadata = $metadataFactory->getMetadataFor($explode[0]);

                        $fieldType = $metadata->getTypeOfField($explode[1]);

                        if ($fieldType == 'text') {
                            $this->replaceMediaFromTexts($media, array(array($explode[1] => array($entity))), $replacement);
                        } else {
                            $this->replaceMediaRelation(array(array($explode[1] => array($entity))), $replacement);
                        }
                    }
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * Delete medias
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMediaAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('mediaIds')) {

            $medias = array();

            foreach ($request->query->get('mediaIds') as $id) {

                /** @var Media $media */
                $media = $this->mediaRepository->find($id);

                if ($media) {
                    $associatedContents = $this::getAssociatedContents($media, $this->container);

                    if ($request->query->has('delete')) {

                        // Unlink content in case 'onDelete set null' hasn't been set
                        $this->replaceMediaRelation($associatedContents['field']);

                        // Remove the file from all texts where it is used
                        $this->replaceMediaFromTexts($media, $associatedContents['text']);

                        if ('image' != $media->getType()) {
                            $thumbnail = $media->getThumbnail();

                            if ($this->checkDeletable($thumbnail)->isSuccess()) {
                                $this->deleteMediaCache($thumbnail);
                                $this->getEm()->remove($thumbnail);
                                $this->getEm()->flush();
                            }
                        }

                        if ($this->checkDeletable($media)->isSuccess()) {
                            $this->deleteMediaCache($media);
                            $this->getEm()->remove($media);
                            $this->getEm()->flush();
                        }

                    } else {

                        $medias[$media->getId()] = array(
                            'name' => $media->getName(),
                            'associatedContents' => array_merge($associatedContents['field'], $associatedContents['text'])
                        );
                    }
                }

            }

            if ($request->query->has('delete')) {

                $this->get('unifik_system.router_invalidator')->invalidate();

            } else {

                return new JsonResponse(array(
                    'message' => $this->renderView('UnifikMediaBundle:Backend/Media/Core:delete_message.html.twig', array(
                        'medias' => $medias
                    ))
                ));
            }

        }

        return new JsonResponse();
    }

    /**
     * Delete Media Cache
     *
     * Delete all Media cache files
     *
     * @param $media
     */
    protected function deleteMediaCache($media)
    {
        if ($cacheManager = $this->container->get('liip_imagine.cache.manager', ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            $cacheManager->remove($media->getMediaPath());
        }
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
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function duplicateAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('mediaIds')) {

            /** @var Media $media */
            $media = $this->mediaRepository->find($request->query->get('mediaIds')[0]);

            if ($media) {
                $newMedia = clone($media);
                $newMedia->setName($media->getName() . ' - copy');

                $date = new \DateTime();

                $newMedia->setCreatedAt($date);
                $newMedia->setUpdatedAt($date);

                $thumbnailFile = $media->getThumbnail()->getMediaPath(true);

                $explodePath = explode('/', $thumbnailFile);
                $filename = $explodePath[count($explodePath) - 1];

                array_pop($explodePath);

                $path = implode('/', $explodePath) . '/';

                $increment = 0;

                do {
                    $increment++;
                    $newThumbnailFileName = 'Copy' . $increment . '-' . $filename;
                    $newThumbnailFile = $path . $newThumbnailFileName;
                } while (file_exists($newThumbnailFile));

                copy($thumbnailFile, $newThumbnailFile);

                if ('image' == $media->getType()) {

                    $newMedia->setMediaPath($newThumbnailFileName);

                } else {

                    $newThumbnail = clone $media->getThumbnail();
                    $newThumbnail->setMediaPath($newThumbnailFileName);

                    $this->getEm()->persist($newThumbnail);
                    $newMedia->setThumbnail($newThumbnail);

                }

                $this->getEm()->persist($newMedia);
                $this->getEm()->flush();
            }
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
        $em = $container->get('doctrine')->getManager();

        $metadataFactory = $em->getMetadataFactory();

        $metadata = $metadataFactory->getAllMetadata();

        $entitiesAssociated = array();
        $entitiesAssociated['field'] = array();
        $entitiesAssociated['text'] = array();

        /* @var $classMetadata ClassMetadata */
        foreach ($metadata as $classMetadata) {
            if ('Unifik\MediaBundle\Entity\Media' != $classMetadata->getName()) {
                foreach ($classMetadata->getAssociationMappings() as $association) {

                    if ('Unifik\MediaBundle\Entity\Media' == $association['targetEntity'] && $association['isOwningSide']) {
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
     * Replace related relations with $replacement
     *
     * @param array $associatedField
     * @param $replacement Media
     */
    private function replaceMediaRelation(array $associatedField, Media $replacement = null) {
        if (count($associatedField)) {
            foreach ($associatedField as $methodGroup) {
                foreach ($methodGroup as $methodName => $entities) {
                    foreach ($entities as $entity) {
                        $method = 'set' . ucfirst($methodName);
                        $entity->$method($replacement);
                    }
                }
            }
        }

        $this->getEm()->flush();
    }

    /**
     * Replace $media from any text containing it with $replacement
     *
     * @param Media $media
     * @param Media $replacement
     * @param array $associatedText
     */
    private function replaceMediaFromTexts(Media $media, array $associatedText, Media $replacement = null) {

        foreach ($associatedText as $entityGroup) {
            foreach ($entityGroup as $fieldName => $entities) {

                $getMethod = 'get' . ucfirst($fieldName);
                $setMethod = 'set' . ucfirst($fieldName);

                foreach ($entities as $entity) {

                    $entity->$setMethod(preg_replace($media->getReplaceRegex(), (($replacement) ? $replacement->getHtmlTag() : ''), $entity->$getMethod()));

                }
            }
        }

        $this->getEm()->flush();
    }

    /**
     * modalSearch
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function modalSearchAction(Request $request)
    {
        $response = new JsonResponse();

        if ($request->isXmlHttpRequest()
            && $request->query->has('search')
            && $request->query->has('mediaId')) {

            $media = $this->getRepository('UnifikMediaBundle:Media')->find($request->query->get('mediaId'));

            $medias = $this->getRepository('UnifikMediaBundle:Media')
                ->createQueryBuilder('m')
                ->where('m.name LIKE :search')
                ->andWhere('m.type = :type')
                ->setParameter('search', '%' . $request->query->get('search') . '%')
                ->setParameter('type', $media->getType())
                ->getQuery()->getResult()
            ;

            $mediaGroup = array();

            /** @var $media Media */
            foreach ($medias as $media) {
                $mediaGroup[$media->getType()][] = array(
                    'id' => $media->getId(),
                    'text' => $media->getName()
                );
            }

            $formattedResult = array();

            foreach ($mediaGroup as $groupName => $medias) {
                $formattedResult[] = array(
                    'text' => ucfirst($groupName) . ((count($medias) > 1) ? 's' : ''),
                    'children' => $medias
                );
            }

            $response->setContent(json_encode($formattedResult));
        }

        return $response;
    }

    /**
     * Guess Section
     *
     * @param $entity
     * @return int
     */
    private function guessSection($entity)
    {
        if (method_exists($entity, 'getSection')) {
            if ($entity->getSection()) {
                return $entity->getSection()->getId();
            }
        }

        return 0;
    }
}