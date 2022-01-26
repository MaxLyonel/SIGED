<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonaApropiacionType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('idpersona', 'hidden')
            ->add('carnet', 'hidden')
            ->add('complemento', 'hidden')
            ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('class' => 'form-control jletters','pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'13','style'=>'text-transform:uppercase')))
            ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'13','style'=>'text-transform:uppercase')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'26','style'=>'text-transform:uppercase')))            
            ->add('fechaNacimiento', 'date', array('years' => range(date('Y') -100, date('Y')), 'label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
            ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))            
            ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('class' => 'form-control jemail','pattern' => '^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$','maxlength'=>'40')))            
            ->add('save', 'submit', array("attr" => array("type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Guardar'))                         
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_persona_apropiacion';
    }
}
