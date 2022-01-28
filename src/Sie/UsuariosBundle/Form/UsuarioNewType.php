<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UsuarioNewType extends AbstractType
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
//            ->add('maestroinsid', 'text', array("attr" => array('readonly' => true), "label_attr" => array("class" => "help-block"), 'label' => 'Direc Id:', 'required' => false))
            ->add('depid', 'text', array("attr" => array('readonly' => true), "label_attr" => array("class" => "help-block"), 'label' => 'Departamento Id:', 'required' => true))
            ->add('disid', 'text', array("attr" => array('readonly' => true), "label_attr" => array("class" => "help-block"), 'label' => 'Distrito Id:', 'required' => true))
            ->add('lugtipids', 'text', array("attr" => array('readonly' => true), "label_attr" => array("class" => "help-block"), 'label' => 'Lugares Ids:', 'max_length' => '10', 'required' => true))                
            
            ->add('usuario', 'text', array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase"), "label_attr" => array("class" => "help-block"), 'label' => 'USUARIO:', 'max_length' => '4', 'required' => true, 'disabled' => 'true'))
            //->add('password', 'text', array("attr" => array('placeholder' => 'Contraseña', "class" => "form-control input-sm numericOnly"), "label_attr" => array("class" => "help-block"), 'label' => 'CONTRASEÑA:', 'required' => true))
//            ->add('password', 'repeated', array(                    
//                  'type' => 'password',
//                  'invalid_message' => 'La contraseña no es correcta.',
//                  'options' => array('attr' => array('class' => 'password-field')),
//                  'required' => true,
//                  'first_options'  => array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase"), 'label' => 'Contraseña ', "label_attr" => array("class" => "help-block")),
//                  'second_options' => array("attr" => array("class" => "form-control input-sm", "style" => "text-transform:uppercase"), 'label' => 'Repita Contraseña', "label_attr" => array("class" => "help-block")),
//                 ))                
            ->add('rolTipo', 'entity', array(
                'placeholder' => 'Roles',
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
                'attr' => array('onchange' => 'lugarOn();', 'class' => 'form-control js-example-basic-multiple'), 
                'label_attr' => array('class' => 'help-block'),
            )) 
            //->add('fechaRegistro', 'date', array('widget'=> 'single_text', 'format' => 'dd/MM/yyyy', "attr" => array("class" => "form-control input-sm  txtCalendario", "style" => "text-transform:uppercase"), "label_attr" => array("class" => "help-block"), 'label' => 'Fecha Registro:', 'max_length' => '4', 'required' => true))
            ->add('idpersona', 'hidden')
            ->add('idusuario', 'hidden')
            ->add('accion', 'hidden')
            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign", "disabled" => "true"), 'label' => ' Guardar'))             
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
//    public function setDefaultOptions(OptionsResolverInterface $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => 'Senape\DJAdminBundle\Entity\Entidades'
//        ));
//    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_usuarios_form';
    }
}
