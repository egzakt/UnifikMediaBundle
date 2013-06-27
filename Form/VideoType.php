<?php

namespace Egzakt\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VideoType extends MediaType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		parent::buildForm($builder, $options);

		$builder->add('url');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
		parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'Egzakt\MediaBundle\Entity\Video'
        ));
    }

    public function getName()
    {
        return 'egzakt_mediabundle_videotype';
    }
}
