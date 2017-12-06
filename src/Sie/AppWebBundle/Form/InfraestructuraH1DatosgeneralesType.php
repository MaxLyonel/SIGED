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

            ->add('n110Zonabarrio',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n111Macrodistrito',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n112Distritomun',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n21Calleavenida',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n22Descripcionacceso','textarea',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170)))
            ->add('n31Tramotroncal',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n32Tramocomplementaria',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n33Tramovecinal',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100)))
            ->add('n34TerrestreDias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34TerrestreHrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34TerrestreMin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34TerrestreCosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34TerrestreDescripcion',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170)))
            ->add('n34FluvialDias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34FluvialHrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34FluvialMin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34FluvialCosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34FluvialDescripcion',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170)))
            ->add('n34AereoDias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34AereoHrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34AereoMin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34AereoCosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34AereoDescripcion',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170)))
            ->add('n34CombinacionDias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34CombinacionHrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34CombinacionMin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34CombinacionCosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 70px','maxlength'=>3)))
            ->add('n34CombinacionDescripcion',null,array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170)))
            ->add('n5Obs','textarea',array('label'=>'Observaciones','attr'=>array('class'=>'form-control jupper','maxlength'=>200,'rows'=>5)))
            //->add('fecharegistro')
            //->add('infraestructuraJuridiccionGeografica')
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
