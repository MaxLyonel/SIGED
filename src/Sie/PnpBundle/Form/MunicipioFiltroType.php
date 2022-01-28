<?php

namespace Sie\PnpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MunicipioFiltroType extends AbstractType
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
                        '80480300' => 'Chuquisaca',
                        '80730794' => 'La Paz',
                        '80980569' => 'Cochabamba',
                        '81230297' => 'Oruro',
                        '81480201' => 'Potosí',
                        '81730264' => 'Tarija',
                        '81981501' => 'Santa Cruz',
                        '82230130' => 'Beni',
                        '82480050' => 'Pando',
                    ) ,))    
                
            ->add('provincia', 'choice', array(
               'choices' => array(
                        'null' => 'Provincias.',
                    ) ,))
            ->add('municipio', 'choice', array(
               'choices' => array(
                        'null' => 'Municipio.',
                    ) ,))            
                        
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Generar'))             
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_pnp_municipio_filtro';
    }
}
