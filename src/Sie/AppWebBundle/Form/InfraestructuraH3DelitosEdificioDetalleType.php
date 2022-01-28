<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH3DelitosEdificioDetalleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n22CantVeces')
            ->add('n22ObsAcciones','textarea')
            ->add('n22EsRoboEdificio')
            ->add('n22EquipamientoTipo',null, array('empty_value'=>'Seleccionar...'))
            ->add('n22MobiliarioTipo',null, array('empty_value'=>'Seleccionar...'))
            ->add('n22AmbientesTipo',null, array('empty_value'=>'Seleccionar...'))
            ->add('n22HorarioTipo',null, array('empty_value'=>'Seleccionar...'))
            ->add('n22GestionTipo',null, array('empty_value'=>'Seleccionar...'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH3DelitosEdificioDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah3delitosedificiodetalle';
    }
}
