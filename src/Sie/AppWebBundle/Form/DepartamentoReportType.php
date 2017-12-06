<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DepartamentoReportType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])    
            ->add('depto', 'choice', array(
                 'choices' => array(
                                    '1:Chuquisaca' => 'Chuquisaca',
                                    '2:La Paz' => 'La Paz',
                                    '3:Cochabamba' => 'Cochabamba',
                                    '4:Oruro' => 'Oruro',
                                    '5:Potosi' => 'Potosí',
                                    '6:Tarija' => 'Tarija',
                                    '7:Santa Cruz' => 'Santa Cruz',
                                    '8:Beni' => 'Beni',
                                    '9:Pando' => 'Pando',
                 ),
                'label' => 'Departamento :', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Carnet de Identidad',)
            ))
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
        return 'sie_formdepto';
    }
}
