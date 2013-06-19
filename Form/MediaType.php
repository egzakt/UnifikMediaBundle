<?php

namespace Egzakt\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('mediaFile', 'advanced_file', array('file_path_method' => 'mediaPath'))
            ->add('description', 'ckeditor')
            ->add('title')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Egzakt\MediaBundle\Entity\Media'
        ));
    }

    public function getName()
    {
        return 'egzakt_mediabundle_mediatype';
    }
}
