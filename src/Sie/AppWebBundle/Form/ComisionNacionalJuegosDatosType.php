<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class ComisionNacionalJuegosDatosType extends AbstractType
{    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $comisionPri = array('10' => 'Comite Organizador','20' => 'Comite Técnico','70' => 'Invitado Especial','30' => 'Juez','40' => 'Prensa','50' => 'Salud','60' => 'Seguridad','126' => 'Apoyo','12' => 'Acompañante Maestro','13' => 'Acompañante Padre de Familia','102' => 'Acompañante Delegado','139' => 'Acompañante Entrenador','11' => 'Reponsable Departamental de Juegos','103' => 'Jefe de Misión (Director Departamental)','107' => 'Técnico de Acreditación','109' => 'Equipo Médico','110' => 'Equipo de Seguridad Patrulla','145' => 'Hospedaje - Padres de Familia');

        $comisionSec = array('115' => 'Comite Organizador','117' => 'Comite Técnico','121' => 'Invitado Especial','144' => 'Juez','118' => 'Prensa','119' => 'Salud','120' => 'Seguridad','131' => 'Apoyo','143' => 'Acompañante Maestro','141' => 'Acompañante Padre de Familia','142' => 'Acompañante Delegado','140' => 'Acompañante Entrenador','154' => 'Reponsable Departamental de Juegos','146' => 'Jefe de Misión (Director Departamental)','156' => 'Técnico Encargado de Juegos','150' => 'Técnico de Acreditación','152' => 'Equipo Médico','153' => 'Equipo de Seguridad Patrulla','155' => 'Hospedaje - Padres de Familia');

        $builder
            ->add('carnetIdentidad','text', array('label' => 'Carnet de Identidad','required' => true))
            ->add('nombre','text', array('label' => 'Nombre','required' => true))
            ->add('paterno','text', array('label' => 'Apellido Paterno','required' => true))
            ->add('materno','text', array('label' => 'Apellido Materno','required' => true))            
            ->add('celular','number', array('label' => 'Celular','required' => true))             
            ->add('posicion','number', array('label' => 'posicion','required' => false))   
            ->add('correo','email', array('label' => 'Correo','required' => false))  
            ->add('obs','text', array('label' => 'Obs.','required' => false)) 
            ->add('comisionTipoId',
                      'choice',  
                      array('label' => 'Comisión Tipo',
                            'choices' => $comisionSec,
                            )
                )
            ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => false, 'data_class' => null)) 
            ->add('generoTipo',
                      'choice',  
                      array('label' => 'Género',
                            'choices' => array( '1' => 'Masculino'
                                                ,'2' => 'Femenino'
                                                ),
                            )
                )            
            ->add('pruebaTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\PruebaTipo',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('pt')
                        ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                        ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = dt.nivelTipo')
                        ->where('nt.id = :nivel')
                        ->orderBy('pt.id', 'ASC')
                        ->setParameter('nivel', 13);
                },
                'label' => 'Prueba',
            ))          
            ->add('departamentoTipo', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('dt')
                        ->orderBy('dt.id', 'ASC');
                },
                'label' => 'Departamento',
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
            'data_class' => 'Sie\AppWebBundle\Entity\ComisionJuegosDatos'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sie_juegos_comision_nacional_lista_index';
    }
}
