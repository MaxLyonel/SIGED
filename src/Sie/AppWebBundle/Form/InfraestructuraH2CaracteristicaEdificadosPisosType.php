<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH2CaracteristicaEdificadosPisosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n11NroPisoTipo')
            ->add('n12AreaM2')
            ->add('n13NroAmbPedagogicos')
            ->add('n14NroAmbNoPedagogicos')
            ->add('n15TotalBanios')
            ->add('n16TotalAmbientes')

            ->add('n21SiCieloFalso','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n211CaracteristicasTipo')

            ->add('n22SiPuertas','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n221SeguroTipo')
            ->add('n222AbreTipo')

            ->add('n23SiVentanas','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n231VidriosTipo')

            ->add('n24SiTecho','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))

            ->add('n25SiMuros','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n251MurosMaterialTipo')
            ->add('n252MurosCaracteristicasTipo')

            ->add('n26SiRevestimiento','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n261RevestMaterialTipo')
            ->add('n262RevestCaracteristicasTipo')

            ->add('n27SiPiso','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n271PisoMaterialTipo')
            ->add('n272PisoCaracteristicasTipo')

            ->add('n31SiGradas','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n32GradasTipo',null, array(
                'multiple'=>true,
                'expanded'=>true
            ))

            ->add('n33SiRampas','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))
            ->add('n31SenalesTipo',null, array(
                'multiple'=>true,
                'expanded'=>true
            ))

            ->add('n35SiSenaletica','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true,
                'data'=>0
            ))

            
            ->add('estadoTipo')
            ->add('n35SenalesiomaTipo2')
            ->add('n35SenalesiomaTipo1')
            
            //->add('infraestructuraH2CaracteristicaEdificadosa','entity',array('class'=> $options[0] ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah2caracteristicaedificadospisos';
    }
}
