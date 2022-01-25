<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocioeconomicoAlternativaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('atenmedicaTipoId')
            ->add('sangreTipoId')
            ->add('gestionId')
            ->add('dicapacidadId')
            ->add('direccionZona')
            ->add('direccionCalle')
            ->add('direccionNro')
            ->add('direccionTelefono')
            ->add('direccionCelular')
            ->add('nroHijos')
            ->add('seguro')
            ->add('empleo')
            ->add('motivoInterrupcion')
            ->add('aniosInterrupcion')
            ->add('ultimoCurso')
            ->add('estadoCivilId')
            ->add('modalidadId')
            ->add('usuarioId')
            ->add('fechaLastUpdate')
            ->add('etniaTipo')
            ->add('idiomaTipo')
            ->add('idiomaTipo2')
            ->add('idiomaTipo3')
            ->add('idiomaTipo4')
            ->add('idiomaTipo5')
            ->add('idiomaTipo6')
            ->add('estudianteInscripcion')
            ->add('gestionTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\SocioeconomicoAlternativa'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_socioeconomicoalternativa';
    }
}
