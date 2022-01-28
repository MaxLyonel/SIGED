<?php

namespace Sie\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ListaUsuarioPorRolType extends AbstractType
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
            ->add('rolTipo', 'entity', array(
                'placeholder' => 'Roles',
                'class' => 'SieAppWebBundle:RolTipo',
                'query_builder' => function(EntityRepository $er) {
                              return $er->createQueryBuilder('u')
                                        ->where('u.id IN(:ids)')
                                        ->setParameter('ids', $this->values['rolids'])
                                        ->orderBy('u.rol','ASC');
                        },
                'property' => 'rol',
                'mapped' => true,
                'multiple' => false,
                'required' => true,
                'label' => 'Seleccione un rol para ver la Lista',
                'attr' => array('class' => 'form-control js-example-basic-multiple'), 
                'label_attr' => array('class' => 'help-block'),
            )) 

            /*->add('status', 'choice', array(
                'choices' => array(
                    0 => 'por rol',
                    1 => 'por jurisdicción'
                ),
                'data' => 1,
                'label' => 'Seleccione el tipo de Lista',
                'attr' => array('class' => 'form-control js-example-basic-multiple'), 
                'label_attr' => array('class' => 'help-block'),
            ))*/

            ->add('save', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success btn-large glyphicon glyphicon-ok-sign"), 'label' => ' Listar'))             
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
        return 'lista_usuarios_por_rol';
    }
}
