<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FlujoTipoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('flujo')
            ->add('obs')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\FlujoTipo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_flujotipo';
    }
}
