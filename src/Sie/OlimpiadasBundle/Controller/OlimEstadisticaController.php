<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * OlimEstadistica controller.
 *
 */
class OlimEstadisticaController extends Controller{

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

	public function nacionalIndexAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$query = $em->getConnection()->prepare("
			select dep.id, dep.codigo, dep.lugar as nombre, COALESCE(oli.cantidad,0) as cantidad
			from (select * from lugar_tipo where lugar_nivel_id = 1) as dep
			left join (
			select lt4.id, count(*) as cantidad from olim_estudiante_inscripcion oei
			inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
			inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
			inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
			inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
			left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
			left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
			left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
			left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
			left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
			where oei.gestion_tipo_id = :gestion:: double precision and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
			group by lt4.id
			) as oli on oli.id = dep.id
			union all 
			select 0 as id, '1' as codigo, 'Bolivia' as nombre, count(*) as cantidad from olim_estudiante_inscripcion oei
			where oei.gestion_tipo_id = :gestion:: double precision
			order by cantidad desc
		");
		$query->bindValue(':gestion', 2018);
		$query->execute();
		$inscritos = $query->fetchAll();

		return $this->render('SieOlimpiadasBundle:OlimEstadistica:nacional.html.twig', array(
			'estadistica'=>$inscritos,
		));
	}
	
	/**
     * Imprime reportes estadisticos a nivel nacional en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function nacionalPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
        } else {
		}
		
		$gestion = $gestionActual;

        $arch = 'Olim_Bolivia'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'olim_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
	
	/**
     * Imprime reportes estadisticos a nivel nacional en formato XLS
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function nacionalXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
        } else {
		}
		
		$gestion = $gestionActual;

        $arch = 'Olim_Bolivia'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'olim_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
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
}