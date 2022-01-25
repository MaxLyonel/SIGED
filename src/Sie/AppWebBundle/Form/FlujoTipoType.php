<?php

namespace Sie\AppWebBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sie\AppWebBundle\Entity\WfFlujoInstitucioneducativaTipo;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
// ...

class FlujoTipoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @param OptionsResolver $resolver
     */

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /* public function __construct(array $options = array()) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }
     public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([ 'entityManager' => null, ]);
        //dump($resolver);die;
    } */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$em = $this->getDoctrine()->getManager();
        $data = $options['data'];
        //dump($data);die;
        $builder
            ->add('flujo')
            ->add('obs');
        if($data->getId()){
            $builder
            ->add('institucioneducativaTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\InstitucioneducativaTipo',
                'query_builder'=>function(\Doctrine\ORM\EntityRepository $iet){
                            return $iet->createQueryBuilder('iet')->where('iet.id<>0')->orderBy('iet.descripcion','ASC');},
                'mapped' => false,
                'required' => true,
                'multiple' => true,
                'label' => 'Sistema',
                'empty_value' => 'Seleccionar sistema',
                'property'=>'descripcion',
                'data'=> $this->em->createQueryBuilder()
                            ->select('iet')
                            ->from('SieAppWebBundle:WfFlujoInstitucioneducativaTipo', 'wf')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaTipo','iet', 'with','wf.institucioneducativaTipo=iet.id')
                            ->where('wf.flujoTipo ='. $data->getId())
                            ->orderBy('iet.descripcion')
                            ->getQuery()
                            ->getResult(),
                'expanded'=>false
            ));
        }else{
            $builder
            ->add('institucioneducativaTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\InstitucioneducativaTipo',
                'query_builder'=>function(\Doctrine\ORM\EntityRepository $iet){
                            return $iet->createQueryBuilder('iet')->where('iet.id<>0')->orderBy('iet.descripcion','ASC');},
                'mapped' => false,
                'required' => true,
                'multiple' => true,
                'label' => 'Sistema',
                'empty_value' => 'Seleccionar sistema',
                'property'=>'descripcion',
                'expanded'=>false
            ));
        }
            
            //dump($builder);die;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\FlujoTipo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_appwebbundle_flujotipo';
    }


    
}
/* class YourFormType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver)
    { $resolver->setDefaults([ 'entityManager' => null, ]);
    }
    public function buildForm(FormBuilderInterface $builder, array $options) { // Entity Manager is set in: $options['entityManager'] } }
 */
/* public function __construct(array $options = array()) {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
}
public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array( 'host' => 'smtp.example.org', 'username' => 'user', 'password' => 'pa$$word', 'port' => 25, 'encryption' => null, ));
} */