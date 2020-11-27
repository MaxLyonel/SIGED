<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaestroCuentabancariaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('cuentabancaria', 'text', array('label' => 'Nro. Cta. Bancaria', 'attr' => array('autocomplete' => 'off', 'maxlength' => '14')))
            ->add('persona', null, array('label' => 'Persona', 'read_only' => true))
            ->add('maestroInscripcion', null, array('read_only' => true))
            ->add('institucioneducativa', null, array('read_only' => true))
            ->add('cargoTipo', null, array('read_only' => true))
            ->add('entidadfinancieraTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\EntidadfinancieraTipo',
                'mapped' => false,
                'required' => true,
                'label' => 'Entidad Financiera'
            ))
            ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => '14')))
            ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('autocomplete' => 'off')))
            ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('autocomplete' => 'off')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off')))
            ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('autocomplete' => 'off', 'maxlength' => '2')))
            ->add('apellidoEsposo', 'text', array('label' => 'Apellido de Esposo', 'required' => false, 'attr' => array('autocomplete' => 'off')))
            ->add('fechaNacimiento', 'date', array('widget' => 'single_text','format' => 'dd-mm-yyyy', 'required' => true, 'attr' => array('autocomplete' => 'off')))
            ->add('expedido', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo',
                'mapped' => false,
                'required' => true,
                'label' => 'Expedido del C.I.'
            ))
            ->add('nacionalidad', 'choice', array('label' => 'Nacional/Extranjero', 'required' => true, 'choices' => [0 => 'Nacional', 1 => 'Extranjero']))
            ->add('enlazador', 'choice', array('label' => 'Enlazador Apellido', 'required' => true, 'choices' => [0 => 'NINGUNO', 1 => 'DE', 2 => 'VDA. DE']))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_maestrocuentabancaria';
    }

}
