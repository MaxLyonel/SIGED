<?php

namespace Sie\PnpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonaType extends AbstractType
{
    protected $values;

    public function __construct(array $values) {
        $this->values['ci'] = $values['ci'];
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->add('carnetIdentidad', 'text', array('read_only' => 'true', 'label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => 'Carnet de Identidad', 'pattern' => '[0-9]{5,10}','value'=>$this->values['ci'],'maxlength'=>'15')))
            ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jletters','pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'45','style'=>'text-transform:uppercase')))
            ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'45','style'=>'text-transform:uppercase')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jletters', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}','maxlength'=>'45','style'=>'text-transform:uppercase')))
            ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
            ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
            ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','data-toggle'=>'tooltip','data-placement'=>'bottom','data-original-title'=>'El número de celular debe tener 8 digitos')))
            ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail','maxlength'=>'40')))
            ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters','pattern'=>'[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ]{4,50}','maxlength'=>'50','style'=>'text-transform:uppercase')))
            ->add('rda', 'text', array('label' => 'Rda', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{1,10}','maxlength'=>'10','data-toggle'=>'tooltip','data-placement'=>'bottom','data-original-title'=>'En caso de no contar con RDA colocar: 0')))
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Guardar'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_pnp_persona';
    }
}
