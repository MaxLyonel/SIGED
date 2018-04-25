<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OlimMateriaTipoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('materia')
            // ->add('fechaRegistro')
            ->add('fechaInsIni', null, array('widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('fechaInsFin', null, array('widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('descripcion', 'textarea')
            // ->add('olimRegistroOlimpiada')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\OlimMateriaTipo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_olimmateriatipo';
    }
}
