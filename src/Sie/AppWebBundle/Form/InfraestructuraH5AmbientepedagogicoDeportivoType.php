<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH5AmbientepedagogicoDeportivoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n53EsRecreativo','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsDeportivo','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsCultural','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsUsoUniversal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteAreaMts')
            ->add('n53AmbienteTipo')
            ->add('n53AmbienteCapacidad')
            ->add('n53EsGraderia','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsIluminacionElectrica','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsIluminacionNatural','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsTechado','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53EsAmbienteCieloFal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteCieloFalTipo')
            ->add('n53EsAmbienteMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteMuroCaracTipo')
            ->add('n53AmbienteMuroMatTipo')
            ->add('n53EsAmbientePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n511SeguroTipo')
            ->add('n512AbreTipo')
            ->add('n53EsAmbienteRevestimiento','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteRevestMatTipo')
            ->add('n53AmbienteRevestCaracTipo')
            ->add('n53EsAmbienteVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteVentanaTipo')
            ->add('n53AmbientePisoMatTipo')
            ->add('n53AmbientePisoCaracTipo')
            ->add('n53EsAmbientePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n53AmbienteGraderiaTipo')
            ->add('estadoTipo')
          // ->add('infraestructuraJuridiccionGeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah5ambientepedagogicodeportivo';
    }
}
