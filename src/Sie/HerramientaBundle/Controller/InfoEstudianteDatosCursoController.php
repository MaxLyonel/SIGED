<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteDatosCursoController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){

    	$infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $cursoId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoId);
        if(!$entity){
        	$entity = new InstitucioneducativaCurso();
        }

        $totalCursoOferta = count($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$entity->getId())));

        $form = $this->createFormCurso($entity);
		$form->handleRequest($request);

    	return $this->render('SieHerramientaBundle:InfoEstudianteDatosCurso:index.html.twig', array(
    		'curso' => $entity,
    		'form' => $form->createView(),
    		'totalCursoOferta' => $totalCursoOferta
    	));
    }

    private function createFormCurso($entity){
    	$form = $this->createFormBuilder($entity)
    				->add('id','hidden',array('data'=>$entity->getId()))
    	            ->add('idiomaMasHabladoTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,98,99,0));
    	            	},
    	            	'required' => true
    	            ))
    	            ->add('idiomaRegHabladoTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,99,0));
    	            	},
    	            	'required' => true
    	            ))
    	            ->add('idiomaMenHabladoTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,99,0));
    	            	},
    	            	'required' => true
    	            ))

    	            ->add('priLenEnsenanzaTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,98,99,0));
    	            	},
    	            	'required' => true))
    	            ->add('segLenEnsenanzaTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,99,0));
    	            	},
    	            	'required' => true))
    	            ->add('terLenEnsenanzaTipo', null, array(
    	            	'class'=>'SieAppWebBundle:IdiomaTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('it')
    	            				->select('it')
    	            				->where('it.id NOT IN (:ids)')
    	            				->setParameter('ids', array(97,99,0));
    	            	},
    	            	'required' => true))

    	            ->add('desayunoEscolar', 'choice', array(
    	            	'required' => true,
    	            	'choices'=>array('1'=>'Si','0'=>'No'),
    	            	'expanded'=>true,
    	            	'multiple'=>false
    	            ))
    	            ->add('finDesEscolarTipo', null, array(
    	            	'class'=>'SieAppWebBundle:FinanciamientoTipo',
    	            	'query_builder'=> function(EntityRepository $er){
    	            		return $er->createQueryBuilder('ft')
    	            				->select('ft')
    	            				->where('ft.id NOT IN (:ids)')
    	            				->setParameter('ids', array(0));
    	            	},
    	            	'required' => true))

    	            ->getForm();
    	return $form;
    }

    public function saveAction(Request $request){
    	$em = $this->getDoctrine()->getManager();
    	$form = $request->get('form');
    	if($form['id'] != ""){
    		$curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['id']);
    		$curso->setIdiomaMasHabladoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMasHabladoTipo']));
    		$curso->setIdiomaRegHabladoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaRegHabladoTipo']));
    		$curso->setIdiomaMenHabladoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMenHabladoTipo']));
    		$curso->setPriLenEnsenanzaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['priLenEnsenanzaTipo']));
    		$curso->setSegLenEnsenanzaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['segLenEnsenanzaTipo']));
    		$curso->setTerLenEnsenanzaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['terLenEnsenanzaTipo']));
    		$curso->setDesayunoEscolar($form['desayunoEscolar']);
            if($form['desayunoEscolar'] == 1){
                $curso->setFinDesEscolarTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($form['finDesEscolarTipo']));
            }else{
                $curso->setFinDesEscolarTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find(0));
            }
    		$em->persist($curso);
    		$em->flush();

    		$data = array(
    			'msg'=> 'Datos actualizados correctamente',
    			'status' => 201
    		);
    	}else{
    		$data = array(
    			'msg' => 'Los datos ingresados son incorrectos',
    			'status' => 500
    		);
    	}

    	$response = new JsonResponse();

    	return $response->setData($data);
    }

}
