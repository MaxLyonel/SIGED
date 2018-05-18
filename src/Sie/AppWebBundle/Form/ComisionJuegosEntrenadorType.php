<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;


class ComisionJuegosEntrenadorType extends AbstractType
{   
    protected $values;

    public function __construct(array $values) {
        $this->values['nivelId'] = $values['nivelId'];
        $this->values['disciplinaId'] = $values['disciplinaId'];
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->values['nivelId'] == 12 or $this->values['disciplinaId'] == 2) {
            $lugar = array( '1' => 'Primer Lugar');
        } else {
            $lugar = array( '1' => 'Primer Lugar', '2' => 'Segundo Lugar');
        }
        if ($this->values['nivelId'] == 12) {
            $comision = array('139' => 'Acompañante Entrenador', '12' => 'Acompañante Maestro', '13' => 'Acompañante Padre de Familia', '102' => 'Acompañante Delegado');
        } else {
            $comision = array('140' => 'Acompañante Entrenador', '143' => 'Acompañante Maestro', '141' => 'Acompañante Padre de Familia', '142' => 'Acompañante Delegado');
        }
        $builder
        ->add('carnetIdentidad','text', array('label' => 'Carnet de Identidad','required' => true))
        ->add('nombre','text', array('label' => 'Nombre','required' => true))
        ->add('paterno','text', array('label' => 'Apellido Paterno','required' => true))
        ->add('materno','text', array('label' => 'Apellido Materno','required' => true))            
        ->add('celular','number', array('label' => 'Celular','required' => true))     
        ->add('correo','email', array('label' => 'Correo','required' => false))   
        ->add('avc','text', array('label' => 'Número de Asegurado (AVC)','required' => false))
        ->add('obs','text', array('label' => 'Observación','required' => false))
        ->add('comisionTipoId',
                    'choice',  
                    array('label' => 'Comisión Tipo',
                        'choices' => $comision,
                        )
            )
        ->add('posicion',
                    'choice',  
                    array('label' => 'Posición',
                        'choices' => $lugar,
                        )
            )
        ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => false, 'data_class' => null)) 
        ->add('generoTipo',
                    'choice',  
                    array('label' => 'Género',
                        'choices' => array( '1' => 'Masculino'
                                            ,'2' => 'Femenino'
                                            ),
                        )
            )
        ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default')))
        ; 
          
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\ComisionJuegosDatos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_juegos_comision_entrenador_lista_registro';
    }
}
