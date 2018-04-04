<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OlimRegistroOlimpiadaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombreOlimpiada')
            ->add('fechaInicio', null, array('widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('fechaFin', null, array('widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('gestionTipoId')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\OlimRegistroOlimpiada'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_olimregistroolimpiada';
    }
}
