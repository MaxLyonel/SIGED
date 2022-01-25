<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4ServicioBateriaBanosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n5Areatotalm2')
            // ->add('n5BanioConagua')
            // ->add('n5BanioSinagua')

            ->add('n5LetrinaFunciona')
            ->add('n5LetrinaNofunciona')
            ->add('n5InodoroFunciona')
            ->add('n5InodoroNofunciona')
            ->add('n5UrinarioFunciona')
            ->add('n5UrinarioNofunciona')
            ->add('n5LavamanoFunciona')
            ->add('n5LavamanoNofunciona')
            ->add('n5DuchaFunciona')
            ->add('n5DuchaNofunciona')

            ->add('n52EsTieneCieloFalso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n52TieneCieloFalsoCaracTipo')
            ->add('n52EsTieneMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n52TieneMurosMaterTipo')
            ->add('n52TieneMurosCaracTipo')
            ->add('n52EsTienePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n521SeguroTipo')
            ->add('n522AbreTipo')
            ->add('n52EsTieneRevest','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n52TieneRevestCaracTipo')
            
            ->add('n52EsTieneVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n52TieneVentanaCaracTipo')
            ->add('n52EsTienePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n52TienePisoMaterTipo')
            ->add('n52TienePisoCaracTipo')
            
            ->add('n52EsTieneTecho','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            
            // ->add('n5TechoEstadogeneralTipo')
            // ->add('obs')
            // ->add('fecharegistro')
            // ->add('n5EsFuncionaAmbiente')
            // ->add('estadoTipo')
            // ->add('n52TieneRevestMaterTipo')
            // ->add('n5CieloEstadogeneralTipo')
            // ->add('n5PisoEstadogeneralTipo')
            
            // ->add('n5ParedEstadogeneralTipo')
            ->add('n5AmbienteBanoTipo')
            // ->add('infraestructuraH4Servicio')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4ServicioBateriaBanos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4serviciobateriabanos';
    }
}
