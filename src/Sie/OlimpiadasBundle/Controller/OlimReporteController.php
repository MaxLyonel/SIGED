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
				'fechaNacimiento'=> new \DateTime($formulario['fechaNacimiento'])
			));

			if($estudiante === null){
				$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
					'codigoRude'=> $formulario['carnet'],
					'complemento'=>$formulario['complemento'],
					'fechaNacimiento'=> new \DateTime($formulario['fechaNacimiento'])
				));
			}

			$inscripciones = null;
			if($estudiante){
				$inscripciones = $em->createQueryBuilder()
								->select('oro.gestionTipoId as gestion, oro.nombreOlimpiada, omt.materia, orot.categoria, nt.nivel, gt.grado')
								->from('SieAppWebBundle:EstudianteInscripcion','ei')
								->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
								->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
								->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
								->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
								->innerJoin('SieAppWebBundle:OlimEstudianteInscripcion','oei','with','oei.estudianteInscripcion = ei.id')
								->innerJoin('SieAppWebBundle:OlimReglasOlimpiadasTipo','orot','with','oei.olimReglasOlimpiadasTipo = orot.id')
								->innerJoin('SieAppWebBundle:OlimMateriaTipo','omt','with','orot.olimMateriaTipo = omt.id')
								->innerJoin('SieAppWebBundle:OlimRegistroOlimpiada','oro','with','omt.olimRegistroOlimpiada = oro.id')
								->where('e.id = :idEstudiante')
								->setParameter('idEstudiante', $estudiante->getId())
								->getQuery()
								->getResult();
			}

			//dump($inscripciones);die;

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