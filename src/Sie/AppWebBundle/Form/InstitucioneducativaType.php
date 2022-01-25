<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstitucioneducativaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('institucioneducativa')
            ->add('rueUe')
            ->add('fechaResolucion')
            ->add('nroResolucion')
            ->add('obsRue')
            ->add('desUeAntes')
            ->add('fechaCreacion')
            ->add('fechaCierre')
            ->add('convenioTipo')
            ->add('dependenciaTipo')
            ->add('estadoinstitucionTipo')
            ->add('orgcurricularTipo')
            ->add('institucioneducativaTipo')
            ->add('leJuridicciongeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Institucioneducativa'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_institucioneducativa';
    }
}
