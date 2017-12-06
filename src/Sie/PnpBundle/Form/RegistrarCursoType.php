<?php

namespace Sie\PnpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrarCursoType extends AbstractType
{
    protected $values;

    public function __construct(array $values) {
        $this->values['lugar_tipo_id'] = $values['lugar_tipo_id'];
        $this->values['nombre'] = $values['nombre'];
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
                
            ->add('departamento', 'choice', array('required' => true,'placeholder'=>'Departamento',
               'choices' => array(
                        $this->values['lugar_tipo_id'] => $this->values['nombre']
                    ) ,))    
                
            ->add('provincia', 'choice', array('required' => true,'placeholder'=>'Provincia',
               'choices' => array(            
                    ) ,))
            ->add('municipio', 'choice', array('required' => true,'placeholder'=>'Municipio',
               'choices' => array(
                    ) ,))

            ->add('localidad', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off','placeholder' => 'Localidad','maxlength'=>'30','style'=>'text-transform:uppercase','onkeyup'=>'javascript:this.value=this.value.toUpperCase();')))            
            ->add('bloque', 'choice', array('required' => true,'placeholder'=>'Bloque',
               'choices' => array(
                        '1' => '1',
                        '2' => '2'
                    ) ,))
            ->add('parte', 'choice', array('required' => true,'placeholder'=>'Parte',
               'choices' => array(
                        '1' => '1',
                        '2' => '2'
                    ) ,))
            ->add('fecha_inicio','text', array('required'=> true,'attr'=>array('placeholder'=>'dd/mm/YYYY')))
            ->add('fecha_fin','text', array('required'=> true,'attr'=>array('placeholder'=>'dd/mm/YYYY')))
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success"), 'label' => ' Guardar Curso'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_pnp_registrar_curso_nuevo';
    }
}
