<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UsuarioResetType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            //USUARIO
            ->add('usuario', 'text', array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase", "disabled" => "true"), "label_attr" => array("class" => "help-block"), 'label' => 'USUARIO:', 'max_length' => '4', 'required' => true))
            //->add('password', 'text', array("attr" => array("class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'CONTRASEÑA:', 'max_length' => '6', 'required' => true))
            ->add('idpersona', 'hidden')
            ->add('idusuario', 'hidden')
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Iniciar reseteo de contraseña.'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_reset_form';
    }
}
