<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstitucioneducativaSucursalType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombreSubcea')
            ->add('telefono1')
            ->add('telefono2')
            ->add('referenciaTelefono2')
            ->add('fax')
            ->add('email')
            ->add('casilla')
            ->add('codCerradaId')
            ->add('periodoTipoId')
            ->add('direccion')
            ->add('zona')
            ->add('esabierta')
            ->add('gestionTipo')
            ->add('institucioneducativa')
            ->add('leJuridicciongeografica')
            ->add('sucursalTipo')
            ->add('turnoTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InstitucioneducativaSucursal'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_institucioneducativasucursal';
    }
}
