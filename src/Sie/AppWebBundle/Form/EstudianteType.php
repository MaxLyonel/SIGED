<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstudianteType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('codigoRude')
                ->add('carnetIdentidad')
                ->add('pasaporte')
                ->add('paterno')
                ->add('materno')
                ->add('nombre')
                ->add('oficialia')
                ->add('libro')
                ->add('partida')
                ->add('folio')
                ->add('idiomaMaternoId')
                ->add('segipId')
                ->add('complemento')
                ->add('bolean')
                ->add('fechaNacimiento', 'date', array('widget' => 'single_text'))
                ->add('generoTipo')
                ->add('lugarNacTipo')
                ->add('sangreTipoId')
                ->add('estadoCivil')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Estudiante'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_estudiante';
    }

}
