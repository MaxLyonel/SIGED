<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;


class ComisionDepartamentalJuegosDatosType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('carnetIdentidad','text', array('label' => 'Carnet de Identidad','required' => true))
            ->add('nombre','text', array('label' => 'Nombre','required' => true))
            ->add('paterno','text', array('label' => 'Apellido Paterno','required' => true))
            ->add('materno','text', array('label' => 'Apellido Materno','required' => true))            
            ->add('celular','number', array('label' => 'Celular','required' => true))     
            ->add('correo','email', array('label' => 'Correo','required' => false))   
            ->add('avc','text', array('label' => 'Número de Asegurado (AVC)','required' => false))
            ->add('comisionTipoId',
                      'choice',  
                      array('label' => 'Comisión Tipo',
                            'choices' => array( '146' => 'Jefe de Misión (Director Departamental)'
                                                ,'150' => 'Técnico de Acreditación'
                                                ,'151' => 'Chofer'
                                                ,'149' => 'Responsable Varones'
                                                ,'148' => 'Responsable Damas'
                                                ,'147' => 'Director Servicio Departamental Deporte'
                                                ,'152' => 'Equipo Médico'
                                                ,'153' => 'Equipo de Seguridad Patrulla'
                                                ,'122' => 'Representación Cultural - Responsable Danza'
                                            ),
                            )
                )
            ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true, 'data_class' => null)) 
            ->add('generoTipo',
                      'choice',  
                      array('label' => 'Género',
                            'choices' => array( '1' => 'Masculino'
                                                ,'2' => 'Femenino'
                                                ),
                            )
                )
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
        return 'sie_juegos_comision_acompanante_lista_registro';
    }
}
