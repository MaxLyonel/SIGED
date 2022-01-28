<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OlimGrupoProyectoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            // ->add('fechaRegistro')
            // ->add('esVigente')
            // ->add('documentoPdf1')
            // ->add('documentoPdf2')
            // ->add('documentoPdf3')
            // ->add('fechaModificacion')
            // ->add('usuarioRegistroId')
            // ->add('usuarioModificacionId')
            // ->add('gestionTipoId')
            // ->add('olimTutor')
            // ->add('olimReglasOlimpiadasTipo')
            // ->add('periodoTipo')
            // ->add('categoriaTipo')
            // ->add('materiaTipo')
            // ->add('olimGrupoProyectoTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\OlimGrupoProyecto'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_olimgrupoproyecto';
    }
}
