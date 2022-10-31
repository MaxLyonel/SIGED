<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BuscarPersonaExtranjeroType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->setAction($options['action'])
            ->add('ci', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Número de carnet :', 'max_length' => '12', 'required' => true))            
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
    }
    
    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona_extranjero';
    }

}
