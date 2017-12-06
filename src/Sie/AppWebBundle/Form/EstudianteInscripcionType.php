<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstudianteInscripcionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numMatricula')
            ->add('codUeProcedenciaId')
            ->add('observacionId')
            ->add('observacion')
            ->add('fechaInscripcion')
            ->add('apreciacionFinal')
            ->add('operativoId')
            ->add('modalidadTipoId')
            ->add('acreditacionnivelTipoId')
            ->add('permanenteprogramaTipoId')
            ->add('lugarcurso')
            ->add('organizacion')
            ->add('facilitadorcurso')
            ->add('fechainiciocurso')
            ->add('fechafincurso')
            ->add('estadomatriculaTipo')
            ->add('paraleloTipo')
            ->add('cicloTipo')
            ->add('estudiante')
            ->add('gestionTipo')
            ->add('gradoTipo')
            ->add('institucioneducativa')
            ->add('nivelTipo')
            ->add('periodoTipo')
            ->add('sucursalTipo')
            ->add('turnoTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\EstudianteInscripcion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_estudianteinscripcion';
    }
}
