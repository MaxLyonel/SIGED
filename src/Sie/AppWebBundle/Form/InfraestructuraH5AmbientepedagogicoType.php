<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH5AmbientepedagogicoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n51AmbienteTipo')
            ->add('n51NroBloque')
            ->add('n51NroPiso')
            ->add('n51AmbienteAnchoMts')
            ->add('n51AmbienteLargoMts')
            ->add('n51AmbienteAltoMts')
            ->add('n51CapacidadAmbiente')
            
            ->add('n51EsUsoAmbiente','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51EsUsoUniversal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51EsUsoBth','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n51EsAmbienteTecho','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51EsIluminacionElectrica','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51EsIluminacionNatural','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))

            ->add('n51EsAmbienteCieloFal','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51AmbienteCieloFalTipo')
            ->add('n51EsAmbienteMuros','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51AmbienteMuroMatTipo')
            ->add('n51AmbienteMuroCaracTipo')

            ->add('n51EsAmbientePuerta','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n511SeguroTipo')
            ->add('n512AbreTipo')

            ->add('n51EsAmbienteRevestimiento','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51AmbienteRevestCaracTipo')
            ->add('n51AmbienteRevestMatTipo')

            ->add('n51EsAmbienteVentana','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51AmbienteVentanaTipo')
            ->add('n51EsAmbientePiso','choice', array(
               'choices'=>array('1'=>'Si','0'=>'No'),
               'multiple'=>false,
               'expanded'=>true
           ))
            ->add('n51AmbientePisoCaracTipo')
            ->add('n51AmbientePisoMatTipo')

            // need to review this fields
            ->add('n13AmbienteEstadogeneralTipo')
            ->add('n13IluminacionnaturalEstadogeneralTipo')

            ->add('n13PuertasEstadogeneralTipo')
            ->add('n13CielorasoEstadogeneralTipo')
            ->add('n13PinturaEstadogeneralTipo')
            ->add('n13VentanasEstadogeneralTipo')
            ->add('n13TechoEstadogeneralTipo')
            ->add('n13ParedEstadogeneralTipo')
            ->add('n13PisoEstadogeneralTipo')
            ->add('n13SeguridadEstadogeneralTipo')
            ->add('n13IluminacionelectricaEstadogeneralTipo')
            ->add('n15UsoOrgcurricularTipo')
            // 
            
            // ->add('n51TalleresEspOtro')
            // ->add('n51TalleresEspTipo')
            // ->add('fecharegistro')
            // ->add('n51EspecialidadTipoId')
            ->add('estadoTipo')            
            // ->add('n51AreaTipo')
            
            

            
            
            // ->add('infraestructuraJuridiccionGeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah5ambientepedagogico';
    }
}
