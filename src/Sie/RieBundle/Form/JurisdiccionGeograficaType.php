<?php

namespace Sie\RieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JurisdiccionGeograficaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('lugar_tipo_id_localidad')
                ->add('lugar_tipo_id_distrito')
                ->add('obs')
                ->add('cordx')
                ->add('cordy')
                ->add('distrito_tipo_id')
                ->add('lugar_tipo_id_localidad2012')
                ->add('circunscripcion_tipo_id')
                ->add('cod_nuc')
                ->add('des_nuc')
                ->add('direccion')
                ->add('zona')
                ->add('jurisdiccion_acreditacion_tipo_id')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\RieBundle\Entity\JurisdiccionGeografica'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_riebundle_jurisdiccion_geografica';
    }

}
