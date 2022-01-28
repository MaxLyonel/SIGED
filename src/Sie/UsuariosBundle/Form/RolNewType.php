<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RolNewType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            //ROLES
            ->add('rolTipoadmin', 'entity', array(
                'attr' => array('class' => 'form-control input-sm'),
                'class' => 'SieAppWebBundle:RolTipo',
                'property' => 'rol',
                'mapped' => true,
                'multiple' => false,
                'required' => true,
                'label' => 'ROL',                
                'label_attr' => array('class' => 'help-block'),
            ))
                
            ->add('rolTipoasign', 'entity', array(
                'attr' => array('class' => 'form-control input-sm'),
                'class' => 'SieAppWebBundle:RolTipo',
                'property' => 'rol',
                'mapped' => true,
                'multiple' => false,
                'required' => true,                
                'label' => 'ROL A ASIGNAR',                
                'label_attr' => array('class' => 'help-block'),
            ))
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Guardar'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_rol_nuevo_form';
    }
}
