<?php

namespace Sie\PnpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class XlsType extends AbstractType
{
        
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->add('xls', 'textarea', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Archivo xls Procesado :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('typexls', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Cod. Bloque y Parte del Archivo :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('gestion', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Año Inicio :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('mes', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Mes Inicio :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('dia', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Dia Inicio :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('gestionfin', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Año Final :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('mesfin', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Mes Final :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('diafin', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Dia Final :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('facilitador', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Facilitador :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.' ))
            ->add('facilitadorci', 'text', array("attr" => array("class" => "form-control jnumbers"), "label_attr" => array(), 'required' => true, 'label' => 'Facilitador Carnet :', 'disabled' => false, 'trim'=> true, 'invalid_message' => 'Valor no permitido.' ))             
            ->add('departamento', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Departamento :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('provincia', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Provincia :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('municipio', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Municipio :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
            ->add('localidad', 'text', array("attr" => array('readonly' => true, "class" => "form-control input-sm"), "label_attr" => array(), 'required' => true, 'label' => 'Localidad :', 'disabled' => false, 'invalid_message' => 'Valor no permitido.'))
                
            ->add('save', 'submit', array("attr" => array("id" => "save", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Enviar'))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_pnp_xls_form';
    }
}
