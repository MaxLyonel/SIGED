<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimReporte controller.
 *
 */
class OlimReporteController extends Controller{

	public function indexaction(Request $request){
		$form = $this->createFormulario();
		$form->handleRequest($request);

		if($form->isSubmitted()){
			$em = $this->getDoctrine()->getManager();
			$formulario = $request->get('form');
			$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
				'carnetIdentidad'=> $formulario['carnet'],
				'complemento'=>$formulario['complemento'],
				'fechaNacimiento'=>new \DateTime($formulario['fechaNacimiento'])
			));

			if(!$estudiante){
				$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
					'codigoRude'=> $formulario['carnet'],
					'complemento'=>$formulario['complemento'],
					'fechaNacimiento'=>new \DateTime($formulario['fechaNacimiento'])
				));				
			}

			$inscripciones = null;
			if($estudiante){
				$inscripciones = $em->createQueryBuilder()
								->select('oei')
								->from('SieAppWebBundle:EstudianteInscripcion','ei')
								->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
								->innerJoin('SieAppWebBundle:OlimEstudianteInscripcion','oei','with','oei.estudianteInscripcion = ei.id')
								->where('e.id = :idEstudiante')
								->setParameter('idEstudiante', $estudiante->getId())
								->getQuery()
								->getResult();
			}

			return $this->render('SieOlimpiadasBundle:OlimReporte:index.html.twig', array(
				'form'=>$form->createView(),
				'estudiante'=>$estudiante,
				'inscripciones'=>$inscripciones
			));
		}

		return $this->render('SieOlimpiadasBundle:OlimReporte:index.html.twig', array('form'=>$form->createView()));
	}

	private function createFormulario(){
		$form = $this->createFormBuilder()
					->setAction($this->generateUrl('olimreporte'))
	                ->add('carnet', 'text', array('required' => true))
	                ->add('complemento', 'text', array('required' => false))
	                ->add('fechaNacimiento', 'text', array('required' => true))
	                ->getForm();

	    return $form;
	}
}