<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4ServicioBateriaBanosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n5Areatotalm2')
            // ->add('n5BanioConagua')
            // ->add('n5BanioSinagua')

            ->add('n5LetrinaFunciona')
            ->add('n5LetrinaNofunciona')
            ->add('n5InodoroFunciona')
            ->add('n5InodoroNofunciona')
            ->add('n5UrinarioFunciona')
            ->add('n5UrinarioNofunciona')
            ->add('n5LavamanoFunciona')
            ->add('n5LavamanoNofunciona')
            ->add('n5DuchaFunciona')
            ->add('n5DuchaNofunciona')
            
            // ->add('obs')
            // ->add('fecharegistro')
            // ->add('n5EsFuncionaAmbiente')
            // ->add('n52EsTieneTecho')
            // ->add('n52EsTieneCieloFalso')
            // ->add('n52EsTienePuerta')
            // ->add('n52EsTieneVentana')
            // ->add('n52EsTieneMuros')
            // ->add('n52EsTieneRevest')
            // ->add('n52EsTienePiso')
            // ->add('estadoTipo')
            // ->add('n522AbreTipo')
            // ->add('n52TienePisoMaterTipo')
            // ->add('n52TieneRevestMaterTipo')
            // ->add('n52TieneMurosCaracTipo')
            // ->add('n52TieneMurosMaterTipo')
            // ->add('n52TieneVentanaCaracTipo')
            // ->add('n521SeguroTipo')
            // ->add('n52TienePisoCaracTipo')
            // ->add('n52TieneRevestCaracTipo')
            // ->add('n52TieneCieloFalsoCaracTipo')
            // ->add('n5CieloEstadogeneralTipo')
            // ->add('n5PisoEstadogeneralTipo')
            // ->add('n5TechoEstadogeneralTipo')
            // ->add('n5ParedEstadogeneralTipo')
            ->add('n5AmbienteBanoTipo')
            // ->add('infraestructuraH4Servicio')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4ServicioBateriaBanos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4serviciobateriabanos';
    }
}
