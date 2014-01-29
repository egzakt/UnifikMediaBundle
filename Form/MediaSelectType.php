<?php

namespace Unifik\MediaBundle\Form;

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
        $media = null;
        $parentData = $form->getParent()->getData();

        if (null != $options['media_method']) {
            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $media = $accessor->getValue($parentData, $options['media_method']);
            }
        } else {
            $media = $form->getData();
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
           'class' => 'Unifik\MediaBundle\Entity\Media',
           'media_method' => null,
           'type' => 'image',
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
