<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH6AmbienteadministrativoServAlimentacionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n41NumeroAmbientes')
            ->add('n41MetrosArea')
            
            ->add('n41EsAmbienteTecho','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n41EsAmbienteCieloFal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            
           ->add('n41EsAmbientePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
           
           ->add('n41EsAmbienteVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

           ->add('n41EsAmbienteMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
           ->add('n41EsAmbienteRevestimiento','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))            

            ->add('n41EsAmbientePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))            


            // ->add('obs')
            // ->add('fecharegistro')
            ->add('estadoTipo')
            ->add('n412AbreTipo')
            ->add('n41AmbientePisoMatTipo')
            ->add('n41AmbienteRevestMatTipo')
            ->add('n41AmbienteMuroCaracTipo')
            ->add('n41AmbienteMuroMatTipo')
            ->add('n41AmbienteVentanaTipo')
            ->add('n411SeguroTipo')
            ->add('n41AmbientePisoCaracTipo')
            ->add('n41AmbienteRevestCaracTipo')
            ->add('n41AmbienteCieloFalTipo')
            ->add('n41AmbienteAlimentacionTipo')
            // ->add('infraestructuraH6Ambienteadministrativo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoServAlimentacion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah6ambienteadministrativoservalimentacion';
    }
}
