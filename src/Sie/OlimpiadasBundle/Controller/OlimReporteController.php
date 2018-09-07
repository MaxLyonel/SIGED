<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;


use Doctrine\DBAL\Types\Type;
Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTimeType');

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

			// $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
			// 	'carnetIdentidad'=> $formulario['carnet'],
			// 	'complemento'=>$formulario['complemento'],
			// 	'fechaNacimiento'=> new \DateTime($formulario['fechaNacimiento'])
			// ));
			// 
			$longitud = strlen($formulario['carnet']);
			if($longitud < 12){
				// ENTONCES EL VALOR ENVIADO ES UN NUMERO DE CARNET
				$estudiante = $em->createQueryBuilder()
								->select('e')
								->from('SieAppWebBundle:Estudiante','e')
								->where('e.carnetIdentidad = :carnet')
								->andWhere('e.complemento = :complemento')
								->andWhere('e.fechaNacimiento = :fechaNacimiento')
								->setParameter('carnet', $formulario['carnet'])
								->setParameter('complemento', $formulario['complemento'])
								->setParameter('fechaNacimiento', new \DateTime($formulario['fechaNacimiento']))
								// ->setMaxResults(1)
								->getQuery()
								->getOneOrNullResult();
			}else{
				// COMO EL VALOR ENVIADO TIENE MAS DE 12 DIGITOS SE TRATA DE UN CODIGO RUDE
				$estudiante = $em->createQueryBuilder()
								->select('e')
								->from('SieAppWebBundle:Estudiante','e')
								->where('e.codigoRude = :rude')
								->andWhere('e.complemento = :complemento')
								->andWhere('e.fechaNacimiento = :fechaNacimiento')
								->setParameter('rude', $formulario['carnet'])
								->setParameter('complemento', $formulario['complemento'])
								->setParameter('fechaNacimiento', new \DateTime($formulario['fechaNacimiento']))
								// ->setMaxResults(1)
								->getQuery()
								->getOneOrNullResult();
			}

			$array = null;
			$inscripcionActual = null;
			$tutor = null;

			if($estudiante){

				// OBTENEMOS LA INSCRIPCION DEL ESTUDIANTE EN EL SIGED
				$inscripcionActual = $em->createQueryBuilder()
									->select('ei.id')
									->from('SieAppWebBundle:EstudianteInscripcion','ei')
									// ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
									// ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
									->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
									// ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
									->where('ei.estudiante = :idEstudiante')
									->andWhere('iec.gestionTipo = :gestion')
									->andWhere('ei.estadomatriculaTipo IN (:estados)')
									->setParameter('idEstudiante', $estudiante->getId())
									->setParameter('gestion', date('Y'))
									->setParameter('estados', array(4,5,11,55))
									->setMaxResults(1)
									->getQuery()
									->getResult();

									// dump($inscripcionActual);die;

				if($inscripcionActual){
					// OBTENEMOS LAS INSCRIPCIONES EN OLIMPIADAS
					$inscripcionesOlim = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array(
						'estudianteInscripcion'=>$inscripcionActual[0]['id']
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

						//----------------------------------------------------------------------
						// VERIFICAR SI SE DEBE MOSTRAR LA SELECCION DE MODALIDAD DE EVALUACION
						//----------------------------------------------------------------------

						$olimEstudianteNota = $em->createQueryBuilder()
											->select('oenp')
											->from('SieAppWebBundle:OlimEstudianteNotaPrueba','oenp')
											->innerJoin('SieAppWebBundle:OlimEtapaTipo','oet','with','oenp.olimEtapaTipo = oet.id')
											->where('oenp.olimEstudianteInscripcion = :idInscripcion')
											->andWhere('oenp.estado = :estado')
											->setParameter('idInscripcion', $io->getId())
											->setParameter('estado', true)
											->orderBy('oet.id','DESC')
											->setMaxResults(1)
											->getQuery()
											->getResult();

						$mostrarSeleccion = false;
						$registroNota = null;
						$modalidades = null;

						if(count($olimEstudianteNota)>0){

							$siguienteEtapa = $olimEstudianteNota[0]->getOlimEtapaTipo()->getId() + 1;
							
							// FECHAS DE LA ETAPA
							$fechas = $em->getRepository('SieAppWebBundle:OlimEtapaPeriodo')->findOneBy([
								'olimRegistroOlimpiada'=>$io->getOlimReglasOlimpiadasTipo()->getOlimMateriaTipo()->getOlimRegistroOlimpiada()->getId(),
								'olimEtapaTipo'=>$siguienteEtapa
							]);

							// OBTENEMOS LA FECHA ACTUAL
							$fechaActual = new \DateTime(date('d-m-Y'));

							if($fechas){

								// dump($fechaActual);
								// dump($fechas->getFechaInicio());die;

								if($fechaActual >= $fechas->getFechaInicio() and $fechaActual <= $fechas->getFechaFin()){

									$mostrarSeleccion = true;
									$registroNota = $olimEstudianteNota[0];
									$modalidades = $em->getRepository('SieAppWebBundle:OlimModalidadPruebaTipo')->findBy(
										[
										'olimRegistroOlimpiada'=> $io->getOlimReglasOlimpiadasTipo()->getOlimMateriaTipo()->getOlimRegistroOlimpiada()->getId()
										],['olimModalidadTipo'=>'ASC']
									);
								}

							}

						}

						$array[$cont]['mostrar'] = $mostrarSeleccion;
						$array[$cont]['registroNota'] = $registroNota;
						$array[$cont]['modalidades'] = $modalidades;


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

			// dump($array);die;

			return $this->render('SieOlimpiadasBundle:OlimReporte:index.html.twig', array(
				'form'=>$form->createView(),
				'estudiante'=>$estudiante,
				// 'inscripcionActual'=>$inscripcionActual,
				'tutor'=>$tutor,
				'array'=>$array
			));
		}

		return $this->render('SieOlimpiadasBundle:OlimReporte:index.html.twig', array('form'=>$form->createView()));
	}

	public function exportAction(){
		$id = 100;
        $conn = $this->get('database_connection');
        $response = new StreamedResponse(function() use($conn, $id) {
            $handle = fopen('php://output', 'w+');
            // Add the header
            fputcsv($handle, ['id', 'telefono_estudiante', 'correo_estudiante'],';');
            // Query data from database
            $results = $conn->query("select id, telefono_estudiante, correo_estudiante from olim_estudiante_inscripcion where id = ".$id);
            // $results = $conn->query("select id, etapa from olim_etapa_tipo");
            while($row = $results->fetch()) {
                fputcsv($handle, array($row['id'], $row['telefono_estudiante'], $row['correo_estudiante']), ';');
            }
            fclose($handle);
        });
        // dump($response);die;

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;

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

	public function actualizarModalidadAction($registroId, $modalidadId){
		$em = $this->getDoctrine()->getManager();
		$registro = $em->getRepository('SieAppWebBundle:OlimEstudianteNotaPrueba')->find($registroId);
		if($registro){
			$registro->setOlimModalidadTipo($em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($modalidadId));
			$em->persist($registro);
			$em->flush();
			// $em->flush();

			$status = 200;
		}else{
			$status = 500;
		}

		$response = new JsonResponse();

		return $response->setData([
			'status'=> $status
		]);
	}
}