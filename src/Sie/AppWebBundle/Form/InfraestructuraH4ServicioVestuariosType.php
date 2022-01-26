<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4ServicioVestuariosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n6ServicioAmbienteTipo')
            ->add('n6Areatotalm2')
            ->add('n6EsFuncionaAmbiente','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62EsTieneTecho','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62EsTieneCieloFalso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62TieneCieloFalsoCaracTipo')
            ->add('n62EsTieneMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62TieneMurosCaracTipo')
            ->add('n62TieneMurosMaterTipo')
            ->add('n62EsTienePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62EsTieneRevest','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62TieneRevestMaterTipo')
            ->add('n62TieneRevestCaracTipo')
            ->add('n62EsTieneVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62TieneVentanaCaracTipo')
            ->add('n62EsTienePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n62TienePisoMaterTipo')
            ->add('n62TienePisoCaracTipo')
            
            // ->add('fecharegistro')
            // ->add('estadoTipo')
            // ->add('n622AbreTipo')
            // ->add('n621SeguroTipo')
             // ->add('infraestructuraH4Servicio')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4ServicioVestuarios'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4serviciovestuarios';
    }
}
