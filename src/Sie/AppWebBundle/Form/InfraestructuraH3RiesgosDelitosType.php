<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfraestructuraH3RiesgosDelitosType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('n21RoboCantidad')
            ->add('n21EsHuboAsalto','choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n11EsInundacion')
            ->add('n11InundacionFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11InundacionFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsIncendio')
            ->add('n11IncendioFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11IncendioFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsSequia')
            ->add('n11SequiaFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11SequiaFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsDeslizamiento')
            ->add('n11DeslizamientoFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11DeslizamientoFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsRiada')
            ->add('n11RiadaFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11RiadaFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsSismo')
            ->add('n11SismoFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11SismoFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsViento')
            ->add('n11VientoFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11VientoFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsGranizada')
            ->add('n11GranizadaFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11GranizadaFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsHelada')
            ->add('n11HeladaFechainicial', null, array('attr'=>array('placeholder'=>'Mes inicial'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11HeladaFechafinal', null, array('attr'=>array('placeholder'=>'Mes final'), 'widget'=>'single_text', 'format'=>'dd-MM-yyyy'))
            ->add('n11EsHundimiento')
            ->add('n12EsBarranco')
            ->add('n12EsBosque')
            ->add('n12EsRio')
            ->add('n12EsCerro')
            ->add('n12EsCentrominero')
            ->add('n12EsBotadero')
            ->add('n12EsFabrica')
            ->add('n12EsPasofrontera')
            ->add('n12EsBar')
            ->add('n12EsEstacionelectrica')
            ->add('n12EsZonariezgo')
            ->add('n13SiSuspendieronclases', 'choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n14SiAlbergue', 'choice', array(
                'choices'=>array('1'=>'Si','0'=>'No'),
                'multiple'=>false,
                'expanded'=>true
            ))
            ->add('n15EsTimbrepanico')
            ->add('n15EsExtintor')
            ->add('n15EsSalidaemergencia')
            ->add('n15EsCamaraseguridad')
            ->add('n15SiFuncionaCamaraseguridad','choice', array(
                'multiple'=>false,
                'expanded'=>true,
                'choices'=>array('1'=>'Si','0'=>'No')
            ))
            ->add('n15EsSenaleticaevac')
            ->add('n15EsDepositoagua')
            ->add('n15InfraestructuraH3SenaleticaEvacTipo')
            ->add('n13InfraestructuraH3SuspencionClasesTiempoTipo')
            //->add('infraestructuraJuridiccionGeografica')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_infraestructurah3riesgosdelitos';
    }
}
