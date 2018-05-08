<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OlimEstudianteInscripcionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('telefonoEstudiante')
            ->add('correoEstudiante')
            ->add('fechaRegistro')
            ->add('carnetCodepedis')
            ->add('carnetIbc')
            ->add('navegador')
            ->add('fotoEstudiante')
            ->add('fechaModificacion')
            ->add('usuarioRegistroId')
            ->add('usuarioModificacionId')
            ->add('reglasOlimpiadasTipo')
            ->add('categoriaTipo')
            ->add('materiaTipo')
            ->add('discapacidadTipo')
            ->add('estudianteInscripcion')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\OlimEstudianteInscripcion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_olimestudianteinscripcion';
    }
}
