<?php

namespace Sie\PnpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CursoType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->add('id', 'text', array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase"), "label_attr" => array("class" => "help-block"), 'label' => 'ID del Archivo :', 'max_length' => '10', 'required' => true))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pnp_curso_id_form';
    }
}
