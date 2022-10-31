<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EstudianteDestacadoAlternativaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('estudianteInscripcion', null, array('label' => 'Inscripción', 'read_only' => true))
            ->add('generoTipo', null, array('label' => 'Género', 'read_only' => true))
            ->add('promedioFinal', 'number', array('label' => 'Promedio Anual', 'attr' => array('step' => '0.01', 'maxlength' => '5', 'autocomplete' =>'off')))
            ->add('promedioSem1', 'number', array('label' => 'Promedio Semestre 1', 'attr' => array('step' => '0.01', 'maxlength' => '5', 'autocomplete' =>'off')))
            ->add('promedioSem2', 'number', array('label' => 'Promedio Semestre 2', 'attr' => array('step' => '0.01', 'maxlength' => '5', 'autocomplete' =>'off')))
            ->add('institucioneducativa', null, array('label' => 'SIE', 'read_only' => true))
            ->add('estudiante', null, array('label' => 'Estudiante', 'read_only' => true))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_estudiantedestacado';
    }
}
