<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PreinsInstitucioneducativaCursoCupoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('gestionTipoId')
            // ->add('fechaRegistro')
            // ->add('fechaModificacion')
            ->add('observacion','textarea',array('attr' => array('class' => 'form-control') ))
            ->add('cantidadCupos', 'text', array('attr' => array('class' => 'form-control') ))
            ->add('gradoTipo', null, array('attr' => array('class' => 'form-control') ) )
            ->add('nivelTipo', null, array('attr' => array('class' => 'form-control') ) )
            ->add('institucioneducativa')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_preinsinstitucioneducativacursocupo';
    }
}
