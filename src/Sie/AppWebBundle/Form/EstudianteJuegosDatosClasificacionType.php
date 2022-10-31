<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;


class EstudianteJuegosDatosClasificacionType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('estatura','number', array('label' => 'Estatura (Ej.: 1,55)','required' => true))
            ->add('peso','number', array('label' => 'Peso (60,10)','required' => true))
            ->add('talla',
                      'choice',  
                      array('label' => 'Talla (Selecciona)',
                            'choices' => array( '8' => 'XXXS - 8'
                                                ,'10' => 'XXS - 10'
                                                ,'12' => 'XS - 12'
                                                ,'14' => 'S - 14'
                                                ,'16' => 'M - 16'
                                                ,'18' => 'L - 18'
                                                ,'20' => 'XL - 20'
                                                ,'22' => 'XXL - 22'
                                                ,'24' => 'XXXL - 24'
                                                ),
                ))
            ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true, 'data_class' => null)) 
            ->add('gestionTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('gt')
                        ->orderBy('gt.id', 'DESC')
                        ->setMaxResults(1);
                },
            ))         
            ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default')))
        ;        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\JdpEstudianteDatopersonal'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'estudiantedatopersonal_juegos_deportistas_fase3_form';
    }
}
