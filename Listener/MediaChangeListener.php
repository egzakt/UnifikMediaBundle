<?php

namespace Egzakt\MediaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\PostUpdate;
use Egzakt\MediaBundle\Entity\Document;
use Egzakt\MediaBundle\Entity\Image;
use Egzakt\MediaBundle\Entity\Media;
use Egzakt\MediaBundle\Entity\Video;
use Egzakt\SystemBundle\Entity\TextTranslation;

class MediaChangeListener implements EventSubscriber{
    private $markedToUpdate;

    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'postUpdate',
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
        if ($this->markedToUpdate) {
            $em = $args->getEntityManager();
            $entity = $args->getEntity();

            if ($entity instanceof Media) {
                $texts = $em->getRepository('EgzaktSystemBundle:TextTranslation')->findAll();
                /** @var $text TextTranslation */
                foreach ( $texts as $text ) {
                    $replacement = sprintf('$1%s$2', $entity->getReplaceUrl());
                    $text->setText(preg_replace($entity->getReplaceRegex(), $replacement, $text->getText()));
                    $em->persist($text);
                    $em->flush($text);
                }
            }
        }
    }
}