<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonaDatosType extends AbstractType {
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder  
        ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
        ->add('departamentoTipo', 'entity', array('label' => 'Expedido', 'class' => 'SieAppWebBundle:DepartamentoTipo', 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase')))
        ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell','data-toggle'=>'tooltip','data-placement'=>'bottom','data-original-title'=>'El número de celular debe tener 8 digitos')))
        ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail','pattern' => '^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$','maxlength'=>'40')))
        ->add('direccion', 'text', array('label' => 'Domicilio', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','maxlength'=>'49')))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_persona_datos';
    }

}
