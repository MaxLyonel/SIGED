<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstudianteEspecialIndirectaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('institucioneducativaId')
            ->add('institucioneducativaTipoId')
            ->add('disIntelectualGenerlal')
            ->add('disIntelectualDown')
            ->add('disIntelectualAutismo')
            ->add('disVisualTotal')
            ->add('disVisualBaja')
            ->add('disAuditiva')
            ->add('disFisicomotora')
            ->add('disMultiple')
            ->add('disOtros')
            ->add('obs')
            ->add('fechaRegistro')
            ->add('usuarioId')
            ->add('gestionTipo')
            ->add('estudiante')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\EstudianteEspecialIndirecta'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_estudianteespecialindirecta';
    }
}
