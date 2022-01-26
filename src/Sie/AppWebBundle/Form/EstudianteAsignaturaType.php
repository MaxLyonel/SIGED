<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstudianteAsignaturaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ferchaLastUpdate')
            ->add('version')
            ->add('revisionId')
            ->add('asignaturaTipo')
            ->add('personaMaestro')
            ->add('cicloTipo')
            ->add('gestionTipo')
            ->add('gradoTipo')
            ->add('institucioneducativa')
            ->add('nivelTipo')
            ->add('paraleloTipo')
            ->add('periodoTipo')
            ->add('turnoTipo')
            ->add('estudiante')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\EstudianteAsignatura'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_estudianteasignatura';
    }
}
