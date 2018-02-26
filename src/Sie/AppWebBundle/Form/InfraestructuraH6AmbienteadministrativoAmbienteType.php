<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH6AmbienteadministrativoAmbienteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('n11NumeroBueno')
            // ->add('n11NumeroRegular')
            // ->add('n11NumeroMalo')
            // ->add('obs')
            // ->add('fecharegistro')
            ->add('n61AmbienteAreaAdm')
            ->add('n61EsAmbienteTecho')
            ->add('n61EsAmbienteCieloFal')
            ->add('n61EsAmbientePuerta')
            ->add('n61EsAmbienteVentana')
            ->add('n61EsAmbienteMuros')
            ->add('n61EsAmbienteRevestimiento')
            ->add('n61EsAmbientePiso')
            ->add('n612AbreTipoId')
            ->add('estadoTipo')
            ->add('n61AmbientePisoMatTipo')
            ->add('n61AmbienteRevestMatTipo')
            ->add('n61AmbienteMuroCaracTipo')
            ->add('n61AmbienteMuroMatTipo')
            ->add('n61AmbienteVentanaTipo')
            ->add('n611SeguroTipo')
            ->add('n61AmbientePisoCaracTipo')
            ->add('n61AmbienteRevestCaracTipo')
            ->add('n61AmbienteCieloFalTipo')
            ->add('n11AmbienteadministrativoTipo')
            // ->add('infraestructuraH6Ambienteadministrativo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah6ambienteadministrativoambiente';
    }
}
