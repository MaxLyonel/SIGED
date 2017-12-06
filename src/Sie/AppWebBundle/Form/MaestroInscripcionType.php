<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaestroInscripcionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rdaPlanillasId')
            ->add('ref')
            ->add('idioma')
            ->add('lee')
            ->add('habla')
            ->add('escribe')
            ->add('estudia')
            ->add('cargoTipo')
            ->add('especialidadTipo')
            ->add('financiamientoTipo')
            ->add('formacionTipo')
            ->add('persona')
            ->add('gestionTipo')
            ->add('institucioneducativa')
            ->add('institucioneducativaSucursalI')
            ->add('periodoTipo')
            ->add('estadomaestro')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\MaestroInscripcion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_maestroinscripcion';
    }
}
