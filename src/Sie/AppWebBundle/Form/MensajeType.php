<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MensajeType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('asunto', null, array('label' => 'Asunto'))
                ->add('mensaje', null, array('label' => 'Mensaje'))
                ->add('fecha', 'datetime', array('data' => new \DateTime('now'), 'label' => 'Fecha'))
                ->add('adjunto1', 'file', array('label' => 'Adjunto 1', 'required' => false, 'data_class' => null))
                ->add('adjunto2', 'file', array('label' => 'Adjunto 2', 'required' => false, 'data_class' => null))
                ->add('receptor', 'entity', array(
                    'class' => 'Sie\AppWebBundle\Entity\Usuario',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                                ->innerJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'ur.usuario = u.id')
                                ->where('ur.rolTipo = :rol')
                                ->andWhere('p.activo = :activo')
                                ->setParameter('rol', 8)
                                ->setParameter('activo', 't');
                    },
                    'mapped' => false,
                    'required' => true,
                    'multiple' => true,
                    'label' => 'Para'
                ))
                ->add('emisor', 'text', array('mapped' => false, 'label' => 'De', 'read_only' => 'true'))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\Mensaje'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_mensaje';
    }

}
