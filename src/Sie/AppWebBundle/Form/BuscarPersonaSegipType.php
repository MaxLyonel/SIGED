<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BuscarPersonaSegipType extends AbstractType {
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder  
            ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'maxlength' => '10')))
            ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '2', 'autocomplete' => 'off')))
            ->add('fechaNac', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success"), 'label' => ' Buscar'))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona_segip';
    }

}
