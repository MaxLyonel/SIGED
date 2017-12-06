<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BuscarPersonaType extends AbstractType {
    
    protected $values;

    public function __construct(array $values) {
        $this->values['opcion'] = $values['opcion'];
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($this->values['opcion']===0)
        $builder
            ->setAction($options['action'])
            ->add('ci', 'text', array("attr" => array("class" => "form-control input-sm numericOnly",'maxlength' => '10','minlenght'=>'4'), "label_attr" => array("class" => "help-block","id"=>"l_ci"), 'label' => 'Número de carnet :', 'max_length' => '12', 'required' => true))
            ->add('complemento', 'text', array("attr" => array("class" => "form-control input-sm",'maxlength' => '2','pattern'=>'[0-9]{1}[A-Z]{1}','style'=>'text-transform:uppercase','onkeyup'=>'javascript:this.value=this.value.toUpperCase();'), "label_attr" => array("class" => "help-block",'id'=>'l_complemento'), 'label' => 'Complemento :', 'required' => false))
            ->add('extranjero', 'hidden', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'extranjero :', 'max_length' => '1'))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
        else
        $builder
            ->setAction($options['action'])
            ->add('ci', 'text', array("attr" => array("class" => "form-control input-sm numericOnly",'maxlength' => '10','minlenght'=>'4','pattern'=>'[0-9]'), "label_attr" => array("class" => "help-block","id"=>"l_ci"), 'label' => 'Número de carnet :', 'max_length' => '12', 'required' => true))
            ->add('complemento', 'text', array("attr" => array("class" => "form-control input-sm", 'maxlength' => '2','pattern'=>'[0-9]{1}[A-Z]{1}','style'=>'text-transform:uppercase','onkeyup'=>'javascript:this.value=this.value.toUpperCase();'), "label_attr" => array("class" => "help-block","id"=>"l_complemento"), 'label' => 'Complemento :', 'required' => false))
            ->add('extranjero', 'hidden', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'extranjero :', 'max_length' => '1'))
            ->add('buscar', 'button', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign",'onclick'=>'BuscarPersona()'), 'label' => ' Buscar',))
        ;    
    }


    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona';
    }

}
