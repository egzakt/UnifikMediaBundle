<?php

namespace Egzakt\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Advanced Choice Type
 */
class MediaSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (null == $options['media_method']) {
            throw new MissingOptionsException('The "media_method" option must be set.');
        }

        $media = null;
        $parentData = $form->getParent()->getData();

        if (null !== $parentData) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $media = $accessor->getValue($parentData, $options['media_method']);
        }

        $view->vars['media'] = $media;
        $view->vars['type'] = $options['type'];
    }

    /**
     * Set default options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'class' => 'Egzakt\MediaBundle\Entity\Media',
           'media_method' => null,
           'type' => array('image', 'video', 'document'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media_select';
    }
}
