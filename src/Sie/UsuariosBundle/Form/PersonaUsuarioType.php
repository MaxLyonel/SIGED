<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonaUsuarioType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->add('username', 'text', array("attr" => array("class" => "form-control input-sm"), "label_attr" => array("class" => "help-block"), 'label' => 'Nombre de usuario :', 'max_length' => '20', 'required' => true))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuario_form';
    }
}
