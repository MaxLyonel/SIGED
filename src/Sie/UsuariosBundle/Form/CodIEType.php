<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CodIEType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->add('ue', 'text', array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase"), "label_attr" => array("class" => "help-block"), 'label' => 'Cod. Institución Educativa:', 'max_length' => '8', 'required' => true))
            ->add('buscar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Buscar'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_ie_buscar';
    }
}
