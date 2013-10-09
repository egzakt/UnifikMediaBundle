<?php

namespace Flexy\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('media', 'advanced_file', array('file_path_method' => 'mediaPath'))
            ->add('description', 'ckeditor')
            ->add('caption')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Flexy\MediaBundle\Entity\Media'
        ));
    }

    public function getName()
    {
        return 'flexy_mediabundle_mediatype';
    }
}
