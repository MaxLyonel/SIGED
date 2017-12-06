<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SelectIeType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('institucioneducativa', 'text', array('label' => 'Código SIE', 'required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'text', array('label' => 'Gestión', 'required' => true, 'read_only' => true))
        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'select_ie';
    }

}