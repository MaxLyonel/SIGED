<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;


class ComisionJuegosFotoType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => false, 'data_class' => null))             
            ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default')))
        ;        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\ComisionJuegosDatos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_juegos_comision_nacional_lista_index';
    }
}
