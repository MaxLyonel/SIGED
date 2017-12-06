<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonaType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('idpersona', 'hidden')            
            ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('class' => 'form-control jletters','pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'13','style'=>'text-transform:uppercase')))
            ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'13','style'=>'text-transform:uppercase')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'26','style'=>'text-transform:uppercase')))
            //->add('libretaMilitar', 'text', array('label' => 'Libreta Militar', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'45','style'=>'text-transform:uppercase')))
            //->add('pasaporte', 'text', array('label' => 'Pasaporte', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'45','style'=>'text-transform:uppercase')))
            ->add('carnet', 'text', array('label' => 'C.I.', 'required' => true, 'attr' => array('class' => 'form-control jnumbers', 'placeholder' => 'Carnet de Identidad', 'pattern' => '[0-9]{5,10}','maxlength'=>'10')))
            ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jletters', 'pattern' => '[0-9]{0,2}','maxlength'=>'45','style'=>'text-transform:uppercase')))                
            ->add('fechaNacimiento', 'date', array('years' => range(date('Y') -100, date('Y')), 'label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
            ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
            //->add('sangreTipo', 'entity', array('label' => 'Sangre tipo', 'class' => 'SieAppWebBundle:SangreTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
            //->add('idiomaMaterno', 'entity', array('label' => 'Idioma Materno', 'class' => 'SieAppWebBundle:IdiomaMaterno', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
            //->add('estadocivilTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:EstadoCivilTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
            //->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell','data-toggle'=>'tooltip','data-placement'=>'bottom','data-original-title'=>'El número de celular debe tener 8 digitos')))
            ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('class' => 'form-control jemail','pattern' => '^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$','maxlength'=>'40')))
            //->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters','pattern'=>'[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ]{4,50}','maxlength'=>'50','style'=>'text-transform:uppercase')))
            //->add('rda', 'text', array('label' => 'Rda', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{1,10}','maxlength'=>'6','data-toggle'=>'tooltip','data-placement'=>'bottom','data-original-title'=>'En caso de no contar con RDA colocar: 0')))
            ->add('save', 'submit', array("attr" => array("type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Guardar'))             
            //->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Guardar'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_persona_edit';
    }
}
