<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH6AmbienteadministrativoInterDormitoriosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n34AmbienteCantidad')
            ->add('n34AmbienteArea')
            ->add('n34AmbienteCamaLiteras')
            ->add('n34AmbienteCamaSimples')
            ->add('n34AmbienteCamaOtros')
            ->add('n34EsAmbienteTecho')
            ->add('n34EsAmbienteCieloFal')
            ->add('n34EsAmbientePuerta')
            ->add('n34EsAmbienteVentana')
            ->add('n34EsAmbienteMuros')
            ->add('n34EsAmbienteRevestimiento')
            ->add('n34EsAmbientePiso')
            ->add('obs')
            ->add('fecharegistro')
            ->add('estadoTipo')
            ->add('n342AbreTipo')
            ->add('n34AmbientePisoMatTipo')
            ->add('n34AmbienteRevestMatTipo')
            ->add('n34AmbienteMuroCaracTipo')
            ->add('n34AmbienteMuroMatTipo')
            ->add('n34AmbienteVentanaTipo')
            ->add('n341SeguroTipo')
            ->add('n34AmbientePisoCaracTipo')
            ->add('n34AmbienteRevestCaracTipo')
            ->add('n34AmbienteCieloFalTipo')
            ->add('n34AmbienteTipo')
            ->add('infraestructuraH6AmbienteadministrativoInternadoEst')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInterDormitorios'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah6ambienteadministrativointerdormitorios';
    }
}
