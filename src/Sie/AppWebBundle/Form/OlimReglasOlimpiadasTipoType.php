<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OlimReglasOlimpiadasTipoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantidadEquipos')
            ->add('cantidadInscritosGrado')
            ->add('edadInicial')
            ->add('edadFinal')
            ->add('fechaComparacion')
            ->add('siSubirDocumento','choice', array(
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('siNombreEquipo','choice', array(
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('siNombreProyecto','choice', array(
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            // ->add('gestionTipoId')
            // ->add('periodoTipoId')
            // ->add('fechaRegistro')
            // ->add('fechaModificacion')
            // ->add('usuarioRegistroId')
            // ->add('usuarioModificacionId')
            ->add('categoria')
            // ->add('olimMateriaTipo')
            // ->add('olimCategoriaTipo')
            ->add('modalidadNumeroIntegrantesTipo')
            ->add('modalidadParticipacionTipo',null, array('empty_value'=>false))
            //->add('niveles','entity',array('class'=>'SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo','mapped'=>false))
            ->add('pri1','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][0])))
            ->add('pri2','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][1])))
            ->add('pri3','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][2])))
            ->add('pri4','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][3])))
            ->add('pri5','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][4])))
            ->add('pri6','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][5])))

            ->add('sec1','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][6])))
            ->add('sec2','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][7])))
            ->add('sec3','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][8])))
            ->add('sec4','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][9])))
            ->add('sec5','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][10])))
            ->add('sec6','checkbox',array('mapped'=>false, 'required'=>false, 'attr'=> array('checked'=>$options['niveles'][11])))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo',
            'niveles'=>array()
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_olimreglasolimpiadastipo';
    }
}
