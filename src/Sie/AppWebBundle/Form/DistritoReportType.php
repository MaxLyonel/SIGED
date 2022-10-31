<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DistritoReportType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])    
            ->add('coddis', 'text', array('label' => 'Código Distrito', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Código distrito', 'maxlength'=>'4')))
            ->add('gestion', 'choice', array(
                 'choices' => array(
                                    '2013' => '2013',
                                    '2014' => '2014',
                                    '2015' => '2015',                                    
                 ),
                 'label' => 'Gestión :', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Carnet de Identidad',)
            ))
            ->add('generar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Generar'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_formdistrito';
    }
}
