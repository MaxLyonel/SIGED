<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstitucioneducativanoacreditadoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('dependenciaTipo', 'entity', array('disabled' => false, 'label' => 'Dependencia', 'class' => 'SieAppWebBundle:DependenciaTipo', 'attr' => array('class' => 'form-control')))
                ->add('orgcurricularTipo', 'entity', array('disabled' => false, 'label' => 'Org. Curricular', 'class' => 'SieAppWebBundle:OrgcurricularTipo', 'attr' => array('class' => 'form-control')))
                ->add('institucioneducativa', 'text', array('disabled' => false, 'label' => 'Unidad Educativa', 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('codDis', 'text', array('disabled' => false, 'label' => 'Código Distrito', 'attr' => array('class' => 'form-control')))

        ;
        //->add('pais', 'entity', array('label' => 'País', 'class' => 'SieAppWebBundle:PaisTipo', 'attr' => array('class' => 'form-control')))
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Institucioneducativanoacreditado'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_institucioneducativanoacreditado';
    }

}
