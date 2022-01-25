<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NotificacionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('titulo', null, array('label' => 'Título'))    
            ->add('mensaje', null, array('label' => 'Notificación'))
            ->add('fechaCrea', 'datetime', array('data' => new \DateTime('now'), 'label' => 'Fecha inicio'))
            ->add('fechaPub', 'datetime', array('data' => new \DateTime('now'), 'label' => 'Fecha fin'))
            ->add('adjunto', 'file', array('label' => 'Adjunto (.pdf)', 'required' => false, 'data_class' => null))
            ->add('estado', null, array('label' => 'Estado'))
            ->add('envioTipo', 'choice', array('label' => 'Tipo envío', 'choices' => array('f' => 'Por Rol')))
            ->add('noticiaTipo', 'choice', array('label' => 'Tipo notificación', 'choices' => array('0' => 'Normal', '1' => 'Urgente', '2' => 'Muy urgente')))
            ->add('rolTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\RolTipo',
                'property' => 'rol',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'Grupo receptor'
            ))
            ->add('sistemaTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\SistemaTipo',
                'property' => 'sistema',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'Sub Sistema'
            ))
            ->add('usuarioId', 'hidden', array('label' => 'Creado por', 'read_only' => 'true'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Notificacion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_notificacion';
    }
}
