<?php

namespace Flexy\MediaBundle\Listener;

use Flexy\SystemBundle\Lib\BaseDeletableListener;
use Symfony\Component\Translation\TranslatorInterface;
use Flexy\MediaBundle\Entity\Folder;

class FolderDeletableListener extends BaseDeletableListener
{
    /**
     * @var Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * isDeletable
     *
     * @param Folder $entity
     * @return bool
     */
    public function isDeletable($entity)
    {
        if (count($entity->getMedias()) || count($entity->getChildren())) {
            $this->addError($this->translator->trans('This folder is not empty.'));
        }

        return $this->validate();
    }
}