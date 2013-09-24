<?php

namespace Egzakt\MediaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Entity\Video;
use Egzakt\SystemBundle\Entity\TextTranslation;
use Doctrine\ORM\Mapping\ClassMetadata;

class MediaChangeListener implements EventSubscriber
{
    private $markedToUpdate;

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'postUpdate'
        );
    }

    /**
     * Check if an update of text associated with the updated media is needed.
     * The preUpdate event is the only one to allow to compare the old and the new value, but it is not possible to persist data
     * (http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#preupdate)
     * For that reason, if the media path change, the entity is markedToUpdated and the update is done in the postUpdate event
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->markedToUpdate = false;

        if ((($entity instanceof Document || $entity instanceof Image) && $args->hasChangedField('mediaPath')) ||
                $entity instanceof Video && $args->hasChangedField('url')) {
            $this->markedToUpdate = true;
        }
    }

    /**
     * Update the text related of the current media if it has been marked to update
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Media) {

            $em = $args->getEntityManager();

            if ($this->markedToUpdate || $entity->needUpdate()) {

                $metadataFactory = $em->getMetadataFactory();

                $metadata = $metadataFactory->getAllMetadata();

                /* @var $classMetadata ClassMetadata */
                foreach ($metadata as $classMetadata) {

                    $textFields = array();

                    foreach ($classMetadata->getFieldNames() as $fieldName) {

                        $fieldMapping = $classMetadata->getFieldMapping($fieldName);

                        if ('text' == $fieldMapping['type']) {
                            $textFields[] = $fieldName;
                        }
                    }

                    if (!empty($textFields)) {
                        $qb = $em->getRepository($classMetadata->getName())->createQueryBuilder('t');

                        foreach ($textFields as $textFieldName) {
                            $qb->orWhere('t.' . $textFieldName . ' LIKE :expression')
                                ->setParameter('expression', '%data-mediaid="' . $entity->getId() . '"%')
                            ;
                        }

                        $results = $qb->getQuery()->getResult();

                        foreach ($results as $result) {
                            foreach ($textFields as $textFieldName) {
                                $getMethod = 'get' . ucfirst($textFieldName);
                                $setMethod = 'set' . ucfirst($textFieldName);

                                if ('document' == $entity->getType()) {

                                    $replacement = sprintf('$1%s$2%s$3', $entity->getReplaceUrl(), $entity->getName());
                                    $result->$setMethod(preg_replace($entity->getReplaceRegex(), $replacement, $result->$getMethod()));

                                } elseif ('video' == $entity->getType() || 'embedvideo' == $entity->getType()) {

                                    // Replace the whole expression cause ckEditor don't recognize his own transformation of <iframe...

                                    $result->$setMethod(preg_replace(
                                        $entity->getReplaceRegex(),
                                        '<iframe data-mediaid="' . $entity->getId() . '" width="560" height="315" frameborder="0"  allowfullscreen src="' . $entity->getReplaceUrl() . '"></iframe>',
                                        $result->$getMethod()
                                    ));

                                } else {

                                    $replacement = sprintf('$1%s$2', $entity->getReplaceUrl());
                                    $result->$setMethod(preg_replace($entity->getReplaceRegex(), $replacement, $result->$getMethod()));

                                }
                            }
                        }

                        $em->flush();
                    }
                }
            }
        }
    }
}