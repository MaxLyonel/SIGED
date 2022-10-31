<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstitucioneducativaHumanisticoTecnicoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('gestionTipoId', 'choice', array('label' => 'Gestión', 'attr' => array('class' => 'form-control'), 'choices' => array('2015' => '2015','2016' => '2016')))
                ->add('institucioneducativaId', 'text', array('label' => 'Sie', 'attr' => array('class' => 'form-control')))
                ->add('institucioneducativa', 'text', array('label' => 'Unidad Educativa', 'attr' => array('class' => 'form-control')))
                ->add('esimpreso', 'hidden')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_institucioneducativahumanisticotecnico';
    }

}
