<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstudianteInscripcionSocioeconomicoAlternativaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seccioniiHijos')
            ->add('seccioniiEsserviciomilitar')
            ->add('seccioniiEsserviciomilitarCea')
            ->add('seccioniiiZona')
            ->add('seccioniiiAvenida')
            ->add('seccioniiiNumero')
            ->add('seccioniiiTelefonofijo')
            ->add('seccioniiiTelefonocelular')
            ->add('seccionivEscarnetDiscapacidad')
            ->add('seccionivNumeroCarnetDiscapacidad')
            ->add('seccionivCarnetIbc')
            ->add('seccionivNumeroCarnetIbc')
            ->add('seccionivEscegueratotal')
            ->add('seccionvEstudianteEsnacionoriginaria')
            ->add('seccionvEstudianteEsocupacion')
            ->add('seccionvEstudianteEsseguroSalud')
            ->add('seccionvEstudianteSeguroSaludDonde')
            ->add('seccionvEstudianteDemoraLlegarCentroHoras')
            ->add('seccionvEstudianteDemoraLlegarCentroMinutos')
            ->add('seccionviEstudiantePorqueInterrupcionservicios')
            ->add('lugar')
            ->add('fecha')
            ->add('fechaRegistro')
            ->add('fechaModificacion')
            ->add('seccionvEstudianteGrupoSanguineoTipo')
            ->add('seccionvEstudianteNacionoriginariaTipo')
            ->add('estudianteInscripcion')
            ->add('seccionivGradoTipo')
            ->add('seccionivDiscapacitadTipo')
            ->add('seccioniiiLocalidadTipo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_estudianteinscripcionsocioeconomicoalternativa';
    }
}
