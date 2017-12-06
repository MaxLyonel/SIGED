<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class UploadFotoPersonaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder   
            ->setAction($options['action'])    
            ->add('foto', 'file', array('label' => '... ', 'required' => true, 'data_class' => null))            
            ->add('personaid', 'hidden', array('read_only' => 'true'))
            ->add('subir', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success glyphicon glyphicon-ok-sign"), 'label' => ' Subir.'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuariofoto_form';
    }
}
