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
            ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => '10')))
            ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('maxlength' => '2', 'autocomplete' => 'off')))
            ->add('fecha_nacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => false, 'attr' => array('autocomplete' => 'off', 'maxlength' => '10')))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona_segip';
    }

}
