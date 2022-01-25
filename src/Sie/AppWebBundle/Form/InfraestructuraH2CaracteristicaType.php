<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH2CaracteristicaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n11Areaconstruida')
            ->add('n11Arearecreacion')
            ->add('n11Areanoconstruida')
            ->add('n11Areatotal')
            ->add('n11AreaHuerto')
            ->add('n11AreaInvernadero')
            ->add('n11AreaGranjaEscolar')

            ->add('n12InfraestructuraH2TopografiaTipo',null, array('empty_value'=>'Seleccionar...','required'=>true))

            ->add('n13Muroperimetral')
            ->add('n13EdificacionAmurallada', null, array('empty_value'=>'Seleccionar...', 'required'=>true))
            ->add('n13MuroperimetralActual')
            
            
            //->add('n21EsConstruidaEducativo')
            ->add('n22AnioConstruccion')
            ->add('n23AnioRefaccion')
            ->add('n24AnioAmpliacion')
            ->add('n26Razonsocial')
            //->add('n29DocumentoObs')
            ->add('n29DocumentoNroPartida')
            ->add('n210EsPlanoAprobado','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))

            
            ->add('n22AnioConstruccionEsDiscap','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n23AnioRefaccionEsDiscap','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n24AnioAmpliacionEsDiscap','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n27TipoDocumentacion')
            ->add('n28FolioRealTarjetaObs','textarea')
            ->add('n211AdjDocumentacionFolio')
            ->add('n212SiRampas','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            //->add('n212SiTieneRampa')
            
            ->add('n212SiSenaletica','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            //->add('n213SenalesTipo')
            ->add('n212SenalesiomaTipo2',null, array('empty_value'=>'Seleccione idioma...'))
            ->add('n212SenalesiomaTipo1',null, array('empty_value'=>'Seleccione idioma...'))
            //->add('n215TipoRampas')
            ->add('n21InfraestructuraH2ConstruidaEducativoTipo', null, array('empty_value'=>'Seleccionar...', 'required'=>true))
            //->add('n29DocumentoTipo')
            //->add('n28CieloEstadogeneralTipo')
            //->add('n28PisoEstadogeneralTipo')
            //->add('n28ParedEstadogeneralTipo')
            //->add('n28TechoEstadogeneralTipo')
            //->add('n27EstadogeneralTipo')
            ->add('n25InfraestructuraH2PropiedadTipo', null, array('empty_value'=>'Seleccionar...', 'required'=>true))
            
            
            //->add('infraestructuraJuridiccionGeografica')
            
            ->add('fileFolio','file', array('mapped'=>false, 'required'=>false))

            ->add('senalesTipo', 'entity', array('class'=>'SieAppWebBundle:InfraestructuraH2SenalesTipo','mapped'=>false,'multiple'=>true, 'expanded'=>true))
            ->add('rampasTipo', 'entity', array('class'=>'SieAppWebBundle:InfraestructuraH2RampaTipo','mapped'=>false,'multiple'=>true, 'expanded'=>true))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah2caracteristica';
    }
}
