<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4ServicioVestuariosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n6Areatotalm2')
            ->add('n6EsFuncionaAmbiente')
            ->add('fecharegistro')
            ->add('n62EsTieneTecho')
            ->add('n62EsTieneCieloFalso')
            ->add('n62EsTienePuerta')
            ->add('n62EsTieneVentana')
            ->add('n62EsTieneMuros')
            ->add('n62EsTieneRevest')
            ->add('n62EsTienePiso')
            ->add('estadoTipo')
            ->add('n622AbreTipo')
            ->add('n62TienePisoMaterTipo')
            ->add('n62TieneRevestMaterTipo')
            ->add('n62TieneMurosCaracTipo')
            ->add('n62TieneMurosMaterTipo')
            ->add('n62TieneVentanaCaracTipo')
            ->add('n621SeguroTipo')
            ->add('n62TienePisoCaracTipo')
            ->add('n62TieneRevestCaracTipo')
            ->add('n62TieneCieloFalsoCaracTipo')
            ->add('n6ServicioAmbienteTipo')
            ->add('infraestructuraH4Servicio')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4ServicioVestuarios'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4serviciovestuarios';
    }
}
