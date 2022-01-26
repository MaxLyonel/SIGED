<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH1DatosgeneralesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n19Zonabarrio')
            ->add('n110Direccion')
            ->add('n31Tramotroncal')
            ->add('n32Tramocomplementaria')
            ->add('n33Tramovecinal')
            ->add('n34VehicularDistDias')
            ->add('n34VehicularDistHrs')
            ->add('n34VehicularDistMin')
            ->add('n34VehicularDistCosto')
            ->add('n34FluvialDistDias')
            ->add('n34FluvialDistHrs')
            ->add('n34FluvialDistMin')
            ->add('n34FluvialDistCosto')
            ->add('n34AereoDistDias')
            ->add('n34AereoDistHrs')
            ->add('n34AereoDistMin')
            ->add('n34AereoDistCosto')
            ->add('n5Obs', 'textarea')
            ->add('n34PeatonalDistDias')
            ->add('n34PeatonalDistHrs')
            ->add('n34PeatonalDistMin')
            ->add('n34PeatonalDistCosto')
            ->add('n34PeatonalMunDias')
            ->add('n34PeatonalMunHrs')
            ->add('n34PeatonalMunMin')
            ->add('n34PeatonalMunCosto')
            ->add('n34VehicularMunDias')
            ->add('n34VehicularMunHrs')
            ->add('n34VehicularMunMin')
            ->add('n34VehicularMunCosto')
            ->add('n34FluvialMunDias')
            ->add('n34FluvialMunHrs')
            ->add('n34FluvialMunMin')
            ->add('n34FluvialMunCosto')
            ->add('n34AereoMunDias')
            ->add('n34AereoMunHrs')
            ->add('n34AereoMunMin')
            ->add('n34AereoMunCosto')
            //->add('n21FotografiaPrincipal')
            //->add('n21FotografiaFrontal')
            //->add('n21FotografiaLateral')
            //->add('n21FotografiaPanoramica')
            ->add('n34PeatonalEsDist')
            ->add('n34VehicularEsDist')
            ->add('n34FluvialEsDist')
            ->add('n34AereoEsDist')
            ->add('n34PeatonalEsMun')
            ->add('n34VehicularEsMun')
            ->add('n34FluvialEsMun')
            ->add('n34AereoEsMun')
            ->add('n34InfraestructuraH1AccesoAereoTipoMun')
            ->add('n34InfraestructuraH1AccesoFluvialTipoMun2')
            ->add('n34InfraestructuraH1AccesoFluvialTipoMun1')
            ->add('n34InfraestructuraH1AccesoVehicularTipoMun3')
            ->add('n34InfraestructuraH1AccesoVehicularTipoMun2')
            ->add('n34InfraestructuraH1AccesoVehicularTipoMun1')
            ->add('n34InfraestructuraH1AccesoAereoTipoDist')
            ->add('n34InfraestructuraH1AccesoFluvialTipoDist2')
            ->add('n34InfraestructuraH1AccesoFluvialTipoDist1')
            ->add('n34InfraestructuraH1AccesoVehicularTipoDist3')
            ->add('n34InfraestructuraH1AccesoVehicularTipoDist2')
            ->add('n34InfraestructuraH1AccesoVehicularTipoDist1')
            ->add('n110InfraestructuraH1DireccionTipo', 'entity' ,array('class'=>'SieAppWebBundle:InfraestructuraH1DireccionTipo', 'multiple'=>false, 'expanded'=>true))
            ->add('filePrincipal', 'file', array('mapped'=>false, 'required'=>false))
            ->add('fileFrontal', 'file', array('mapped'=>false, 'required'=>false))
            ->add('fileLateral', 'file', array('mapped'=>false, 'required'=>false))
            ->add('filePanoramica', 'file', array('mapped'=>false, 'required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH1Datosgenerales'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah1datosgenerales';
    }
}
