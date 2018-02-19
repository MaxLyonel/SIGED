<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH5AmbientepedagogicoDeportivoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n53EsRecreativo')
            ->add('n53EsDeportivo')
            ->add('n53EsCultural')
            ->add('n53EsUsoUniversal')
            ->add('n53AmbienteAreaMts')
            ->add('n53AmbienteTipo')
            ->add('n53AmbienteCapacidad')
            ->add('n53EsGraderia')
            ->add('n53EsIluminacionElectrica')
            ->add('n53EsIluminacionNatural')
            ->add('n53EsTechado')
            ->add('n53EsAmbienteCieloFal')
            ->add('n53AmbienteCieloFalTipo')
            ->add('n53EsAmbienteMuros')
            ->add('n53AmbienteMuroCaracTipo')
            ->add('n53AmbienteMuroMatTipo')
            ->add('n53EsAmbientePuerta')
            ->add('n511SeguroTipo')
            ->add('n512AbreTipo')
            ->add('n53EsAmbienteRevestimiento')
            ->add('n53AmbienteRevestMatTipo')
            ->add('n53AmbienteRevestCaracTipo')
            ->add('n53EsAmbienteVentana')
            ->add('n53AmbienteVentanaTipo')
            ->add('n53AmbientePisoMatTipo')
            ->add('n53AmbientePisoCaracTipo')
            ->add('n53EsAmbientePiso')
            ->add('n53AmbienteGraderiaTipo')
            ->add('estadoTipo')
          // ->add('infraestructuraJuridiccionGeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah5ambientepedagogicodeportivo';
    }
}
