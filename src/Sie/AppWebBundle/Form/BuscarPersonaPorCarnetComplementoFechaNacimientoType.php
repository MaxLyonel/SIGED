<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
      
class BuscarPersonaPorCarnetComplementoFechaNacimientoType extends AbstractType {
    
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
            ->add('fechaNacimiento', 'date', array('years' => range(date('Y') -100, date('Y')), 'label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
        else
        $builder
            ->setAction($options['action'])
            ->add('ci', 'text', array("attr" => array("class" => "form-control input-sm numericOnly",'maxlength' => '10','minlenght'=>'4','pattern'=>'[0-9]'), "label_attr" => array("class" => "help-block","id"=>"l_ci"), 'label' => 'Número de carnet :', 'max_length' => '12', 'required' => true))
            ->add('complemento', 'text', array("attr" => array("class" => "form-control input-sm", 'maxlength' => '2','pattern'=>'[0-9]{1}[A-Z]{1}','style'=>'text-transform:uppercase','onkeyup'=>'javascript:this.value=this.value.toUpperCase();'), "label_attr" => array("class" => "help-block","id"=>"l_complemento"), 'label' => 'Complemento :', 'required' => false))
            ->add('fechaNacimiento', 'date', array('years' => range(date('Y') -100, date('Y')), 'label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))            
            ->add('buscar', 'button', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign",'onclick'=>'BuscarPersonaPorCarnetComplementoFechaNacimiento()'), 'label' => ' Buscar',))
        ;    
    }


    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona_carnet_complemento_fechaNacimiento';
    }

}
