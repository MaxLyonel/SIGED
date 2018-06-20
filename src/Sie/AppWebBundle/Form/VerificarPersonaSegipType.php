<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VerificarPersonaSegipType extends AbstractType {
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder  
            ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => '10')))
            ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('maxlength' => '2', 'autocomplete' => 'off')))
            ->add('fechaNac', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('autocomplete' => 'off')))
            ->add('paterno', 'text', array('label' => 'Paterno', 'required' => true, 'attr' => array('autocomplete' => 'off')))
            ->add('materno', 'text', array('label' => 'Materno', 'required' => true, 'attr' => array('autocomplete' => 'off')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off')));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_verificar_persona_segip';
    }

}
