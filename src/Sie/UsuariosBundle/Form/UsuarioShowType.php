<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class UsuarioShowType extends AbstractType
{
    protected $values;

    public function __construct(array $values) {
        $this->values['rolids'] = $values['rolids'];
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            //USUARIO
            ->add('usuario', 'text', array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase", "disabled" => "true"), "label_attr" => array("class" => "help-block"), 'label' => 'USUARIO:', 'max_length' => '4', 'required' => true))
            ->add('password', 'text', array("attr" => array("class" => "form-control input-sm", "disabled" => "true"), "label_attr" => array("class" => "help-block"), 'label' => 'CONTRASEÑA:', 'max_length' => '4', 'required' => true))
            ->add('rolTipo', 'entity', array(
                'class' => 'SieAppWebBundle:RolTipo',
                'query_builder' => function(EntityRepository $er) {
                              return $er->createQueryBuilder('u')
                                        ->where('u.id IN(:ids)')
                                        ->setParameter('ids', $this->values['rolids']);
                        },
                'property' => 'rol',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'ROLES',
                'attr' => array('class' => 'form-control js-example-basic-multiple', "disabled" => "true"), 
                'label_attr' => array('class' => 'help-block'),
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_show_form';
    }
}
