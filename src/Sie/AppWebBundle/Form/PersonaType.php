<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('idiomaMaterno')
                ->add('carnet')
                ->add('libretaMilitar')
                ->add('pasaporte')
                ->add('paterno')
                ->add('materno')
                ->add('nombre')
                ->add('fechaNacimiento', 'date', array('widget' => 'single_text'))
                ->add('complemento')
                ->add('generoTipo')
                ->add('estadocivilTipo')
                ->add('sangreTipo')
                //->add('foto', 'file', array('data_class' => null, 'required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Persona'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_persona';
    }

}
