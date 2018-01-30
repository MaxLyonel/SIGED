<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH4ServicioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n11EsEnergiaelectrica')
            ->add('n12FuenteElectricaTipo')
            ->add('n12FuenteElectricaOtro')
            ->add('n13EsTipoInstalacion')
            ->add('n14DisponibilidadTipo')
            ->add('n15NumeroAmbientesPedagogicos')
            ->add('n15NumeroAmbientesNoPedagogicos')
            ->add('n15NumeroBanios')
            // ->add('n15NumeroMedidores')
            // ->add('n16NumeroMedidoresFuncionan')
            // ->add('n16NumeroMedidoresNoFuncionan')
                                    
            ->add('n21EsDiponeAgua')
            // ->add('n22MedioAguaOtro')
            ->add('n23EsCuentaTanqueAgua')
            ->add('n24EsCuentaBombaAgua')
            // ->add('n25EsCuentaRedAgua')
            ->add('n25NumeroAmbientesAgua')
            ->add('n29AmbientesConAgua')
            ->add('n28PurificadorAguaTipo')
            ->add('n27UsoAguaTipo')
            ->add('n26AccesoAguaTipo')
            ->add('n22MedioAguaTipo')
            // 
            ->add('n31EsInstalacionSaneamiento')
            ->add('n32ExcretasEsAlcantarillado')
            ->add('n32ExcretasEsSeptica')
            ->add('n32ExcretasEsPozo')
            ->add('n32ExcretasEsCieloAbierto')
            ->add('n32ExcretasEsOtro')
            // ->add('n33ExcretasEsNosabe')
            ->add('n34EsBuenascondiciones')
            ->add('n34EsBuenasventilacion')
            ->add('n34EsPrivacidad')
            ->add('n34PeriodicidadTipo')
            ->add('n33ElminacionBasuraTipo')

            // ->add('n41EliminacionBasuraOtro')
            ->add('n41EsCentroSalud')
            ->add('n41MetrosCentroSalud')
            ->add('n42EsCentroPolicial')
            ->add('n42MetrosCentroPolicial')
            ->add('n43EsServicioTelecentro')
            ->add('n43MetrosServicioTelecentro')
            ->add('n43EsDnaSlim')
            ->add('n43MetrosDnaSlim')
            ->add('n43EsUesProx')
            ->add('n43MetrosUesProx')
            ->add('n43EsEstBomberos')
            ->add('n43MetrosEstBomberos')
            ->add('n43EsMercadoProxim')
            ->add('n43MetrosMercadoProxim')
            ->add('n43EsComunitariaProxim')
            ->add('n43MetrosComunitariaProxim')
            ->add('n43EsUniversidadProxim')
            ->add('n43MetrosUniversidadProxim')
            ->add('n43EsTecnologicoProxim')
            ->add('n43MetrosTecnologicoProxim')
            
            // ->add('n53EsCentroEsparcimiento')
            // ->add('n53MetrosCentroEsparcimiento')
            // ->add('n54EsCentroCultural')
            // ->add('n54MetrosCentroCultural')
            // ->add('n55EsCentroIglesia')
            // ->add('n55MetrosCentroIglesia')
            // ->add('n56EsCentroInternet')
            // ->add('n56MetrosCentroInternet')
            // ->add('n57EsCentroCorreo')
            // ->add('n57MetrosCentroCorreo')
            // ->add('n58EsCentroTelefono')
            // ->add('n58MetrosCentroTelefono')
            // ->add('n59EsCentroNucleoEducativo')
            // ->add('n59MetrosCentroNucleoEducativo')
            // ->add('n510EsCentroRadiocomunicacion')
            // ->add('n510MetrosCentroRadiocomunicacion')
            // ->add('n511EsServicioEnfermeria')
            // ->add('n512EsServicioInternet')
            
            // ->add('n514EsServicioGas')
            // ->add('n515EsSenalDiscapacidad')
            // ->add('n516EsFuncionaCee')
            // ->add('n517EsRampasacceso')
            // ->add('n518EsGuiasDiscapicidadVisual')
            // ->add('n519EsAmbientesPedagogicosDiscapacidad')
            // ->add('obs')
            // ->add('fecharegistro')
            // ->add('n518GuiasDiscapicidadEnlugarTipo')
            // ->add('n517RampasaccesoEnlugarTipo')
            // ->add('n515EsSenaliomaTipo')
            // ->add('infraestructuraJuridiccionGeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH4Servicio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah4servicio';
    }
}
