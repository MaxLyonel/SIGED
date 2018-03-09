<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH6AmbienteadministrativoVivMaestrosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n21NumeroAmbientes')
            ->add('n21NumeroHabitantes')
            ->add('n21NumeroBanios')
            ->add('n21NumeroDuchas')
            ->add('n21NumeroCocinas')
            ->add('n21MetrosArea')
            ->add('n21EsAmbienteTecho','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbienteCieloFal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbientePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbienteVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbienteMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbienteRevestimiento','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n21EsAmbientePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            // ->add('obs')
            // ->add('fecharegistro')
            ->add('estadoTipo')
            ->add('n212AbreTipo')
            // ->add('infraestructuraH6Ambienteadministrativo')
            ->add('n21AmbientePisoMatTipo')
            ->add('n21AmbienteRevestMatTipo')
            ->add('n21AmbienteMuroCaracTipo')
            ->add('n21AmbienteMuroMatTipo')
            ->add('n21AmbienteVentanaTipo')
            ->add('n211SeguroTipo')
            ->add('n21AmbientePisoCaracTipo')
            ->add('n21AmbienteRevestCaracTipo')
            ->add('n21AmbienteCieloFalTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoVivMaestros'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah6ambienteadministrativovivmaestros';
    }
}
