<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BuscarPersonaSinCarnetType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->setAction($options['action'])
                ->add('nombre', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Nombre :', 'max_length' => '12', 'required' => true))
                ->add('paterno', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Materno :', 'max_length' => '12', 'required' => true))
                ->add('materno', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Paterno :', 'max_length' => '12', 'required' => true))
                ->add('fechaNacimiento', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Fecha de Nacimiento :', 'max_length' => '12', 'required' => true))
                ->add('genero', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'Genero :', 'max_length' => '12', 'required' => true))
                ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_buscar_persona_sin_carnet';
    }

}

