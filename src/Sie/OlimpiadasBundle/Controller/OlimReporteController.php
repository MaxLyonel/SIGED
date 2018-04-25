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

			$array = null;
			$inscripcionActual = null;
			$tutor = null;

			if($estudiante){
				// OBTENEMOS LA INSCRIPCION DEL ESTUDIANTE EN EL SIGED
				$inscripcionActual = $em->createQueryBuilder()
									->select('ei')
									->from('SieAppWebBundle:EstudianteInscripcion','ei')
									->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
									->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
									->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
									->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
									->where('e.id = :idEstudiante')
									->andWhere('gt.id = :gestion')
									->andWhere('emt.id IN (:estados)')
									->setParameter('idEstudiante', $estudiante->getId())
									->setParameter('gestion', date('Y'))
									->setParameter('estados', array(4,5,11,55))
									->setMaxResults(1)
									->getQuery()
									->getResult();

				if($inscripcionActual){
					// OBTENEMOS LAS INSCRIPCIONES EN OLIMPIADAS
					$inscripcionesOlim = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array(
						'estudianteInscripcion'=>$inscripcionActual[0]->getId()
					));

					$cont = 0;
					foreach ($inscripcionesOlim as $io) {
						$array[$cont]['inscripcion'] = $io;
						$array[$cont]['regla'] = $io->getOlimReglasOlimpiadasTipo();
						// NIVELES
						$primaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
							'olimReglasOlimpiadasTipo'=>$io->getOlimReglasOlimpiadasTipo()->getId(), 'nivelTipo'=>12
						), array('gradoTipo'=>'ASC'));
						$secundaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
							'olimReglasOlimpiadasTipo'=>$io->getOlimReglasOlimpiadasTipo()->getId(), 'nivelTipo'=>13
						), array('gradoTipo'=>'ASC'));

						$array[$cont]['primaria'] = $primaria;
						$array[$cont]['secundaria'] = $secundaria;

						if($io->getOlimReglasOlimpiadasTipo()->getModalidadParticipacionTipo()->getId() == 1){
							// INDIVIDUAL
							$array[$cont]['tipo'] = 'Individual';
							$array[$cont]['tutor'] = $io->getOlimTutor();

						}else{
							// GRUPO
							// dump('Grupo');
							$array[$cont]['tipo'] = 'Grupo';
							$grupo = $em->createQueryBuilder()
										->select('ogp')
										->from('SieAppWebBundle:OlimEstudianteInscripcion','oei')
										->leftJoin('SieAppWebBundle:OlimInscripcionGrupoProyecto','oigp','with','oigp.olimEstudianteInscripcion = oei.id')
										->leftJoin('SieAppWebBundle:OlimGrupoProyecto','ogp','with','oigp.olimGrupoProyecto = ogp.id')
										->where('oei.id = :olimInscripcion')
										->setParameter('olimInscripcion', $io->getId())
										->getQuery()
										->getResult();

							$array[$cont]['grupo'] = $grupo;
						}
						// INACRIPCION SUPERIOR
						$inscripcionSuperior = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcionCursoSuperior')->findOneBy(array(
							'olimEstudianteInscripcion'=>$io->getId()
						));

						$array[$cont]['superior'] = $inscripcionSuperior;

						$cont++;
					}
				}


				// dump($inscripcionActual);die;

				// $inscripciones = $em->createQueryBuilder()
				// 				->select('gest.id as gestion, oro.nombreOlimpiada, omt.materia, orot.categoria, nt.nivel, gt.grado')
				// 				->from('SieAppWebBundle:EstudianteInscripcion','ei')
				// 				->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
				// 				->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
				// 				->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
				// 				->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
				// 				->innerJoin('SieAppWebBundle:OlimEstudianteInscripcion','oei','with','oei.estudianteInscripcion = ei.id')
				// 				->innerJoin('SieAppWebBundle:OlimReglasOlimpiadasTipo','orot','with','oei.olimReglasOlimpiadasTipo = orot.id')
				// 				->innerJoin('SieAppWebBundle:OlimMateriaTipo','omt','with','orot.olimMateriaTipo = omt.id')
				// 				->innerJoin('SieAppWebBundle:OlimRegistroOlimpiada','oro','with','omt.olimRegistroOlimpiada = oro.id')
				// 				->innerJoin('SieAppWebBundle:GestionTipo','gest','with','oro.gestionTipo = gest.id')
				// 				->where('e.id = :idEstudiante')
				// 				->setParameter('idEstudiante', $estudiante->getId())
				// 				->getQuery()
				// 				->getResult();
			}

			return $this->render('SieOlimpiadasBundle:OlimReporte:index.html.twig', array(
				'form'=>$form->createView(),
				'estudiante'=>$estudiante,
				'inscripcionActual'=>$inscripcionActual,
				'tutor'=>$tutor,
				'array'=>$array
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