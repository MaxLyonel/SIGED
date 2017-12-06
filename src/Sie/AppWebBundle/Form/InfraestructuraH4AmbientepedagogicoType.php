<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4AmbientepedagogicoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n12AmbienteAnchoMts','text',array('required'=>true))
            ->add('n12AmbienteLargoMts','text',array('required'=>true))
            ->add('n12AmbienteAltoMts','text',array('required'=>true))
            ->add('n14CapacidadAmbiente','text',array('required'=>true))
            ->add('n15UsoOrgcurricularTipo',null,array('required'=>true))
            ->add('n13CielorasoEstadogeneralTipo',null,array('required'=>true))
            ->add('n13PinturaEstadogeneralTipo',null,array('required'=>true))
            ->add('n13PuertasEstadogeneralTipo',null,array('required'=>true))
            ->add('n13VentanasEstadogeneralTipo',null,array('required'=>true))
            ->add('n13TechoEstadogeneralTipo',null,array('required'=>true))
            ->add('n13ParedEstadogeneralTipo',null,array('required'=>true))
            ->add('n13PisoEstadogeneralTipo',null,array('required'=>true))
            ->add('n13SeguridadEstadogeneralTipo',null,array('required'=>true))
            ->add('n13IluminacionelectricaEstadogeneralTipo',null,array('required'=>true))
            ->add('n13IluminacionnaturalEstadogeneralTipo',null,array('required'=>true))
            ->add('n13AmbienteEstadogeneralTipo',null,array('required'=>true))
            ->add('n11AmbienteTipo',null,array('required'=>true))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4Ambientepedagogico'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4ambientepedagogico';
    }
}
