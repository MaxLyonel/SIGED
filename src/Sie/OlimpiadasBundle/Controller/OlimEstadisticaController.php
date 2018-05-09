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

	public function registradosIndexAction(Request $request){
		/*
		* Define la zona horaria y halla la fecha actual
		*/
	   	date_default_timezone_set('America/La_Paz');
	   	$fechaActual = new \DateTime(date('Y-m-d'));
	   	$gestionActual = date_format($fechaActual,'Y');

		$codigo = 0;
		$nivel = 0;	
		$nivelSiguiente = 0;

		if ($request->isMethod('POST')) {
            $codigo = base64_decode($request->get('codigo'));
			$nivel = $request->get('nivel');
			if ($nivel == 0){
				$nivelSiguiente = 1;	
			} else if($nivel == 1){
				$nivelSiguiente = 7;	
			} else {
				$nivelSiguiente = 0;
			}	
        } else {
            $codigo = 0;
			$nivel = 0;	
			$nivelSiguiente = 1;	
		}
		
		$inscritos = $this->getRegistradosEtapa1($nivel,$codigo,$gestionActual);
		//dump($nivel);die;

		return $this->render('SieOlimpiadasBundle:OlimEstadistica:registrados.html.twig', array(
			'estadistica'=>$inscritos,
			'nivel'=>$nivel,
			'nivelSiguiente'=>$nivelSiguiente,
		));
	}

	private function getRegistradosEtapa1($nivel,$codigo,$gestion){
		$em = $this->getDoctrine()->getManager();

		if($nivel == 0){
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
				where oei.gestion_tipo_id = :gestion::double precision and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id
				) as oli on oli.id = dep.id
				union all 
				select 1 as id, '0' as codigo, 'Bolivia' as nombre, count(*) as cantidad from olim_estudiante_inscripcion oei
				where oei.gestion_tipo_id = :gestion::double precision
				order by cantidad desc, nombre asc
			");
		}

		if($nivel == 1){
			$query = $em->getConnection()->prepare("
				select dis.id, dis.codigo, UPPER(dis.lugar) as nombre, COALESCE(oli.cantidad,0) as cantidad
				from (select lt.* from lugar_tipo as lt inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id where lt.lugar_nivel_id = 7 and lt1.codigo = '".$codigo."') as dis
				left join (
				select lt5.id, count(*) as cantidad from olim_estudiante_inscripcion oei
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
				where oei.gestion_tipo_id = :gestion::double precision and lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt5.id
				) as oli on oli.id = dis.id
				union all 
				select lt4.id, lt4.codigo, UPPER(lt4.lugar) as nombre, count(*) as cantidad from olim_estudiante_inscripcion oei
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
				where oei.gestion_tipo_id = :gestion::double precision and lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id, lt4.codigo, lt4.lugar
				order by cantidad desc, nombre asc
			");
		}

		if($nivel == 7){
			$query = $em->getConnection()->prepare("
			select ue.id, ue.codigo, UPPER(ue.nombre) as nombre, COALESCE(oli.cantidad,0) as cantidad
			from (
			select ie.id, cast(ie.id as varchar) as codigo, ie.institucioneducativa as nombre from lugar_tipo as lt 
			inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
			inner join institucioneducativa as ie on ie.le_juridicciongeografica_id = jg.id
			where lt.codigo = '".$codigo."' and ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 1 and ie.id not in (1,2,3,4,5,6,7,8,9)
			and ie.estadoinstitucion_tipo_id = 10
			) as ue
			left join (
			select ie.id, count(*) as cantidad from olim_estudiante_inscripcion oei
			inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
			inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
			inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
			inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
			left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
			where oei.gestion_tipo_id = :gestion::double precision and lt.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
			group by ie.id
			) as oli on oli.id = ue.id
			union all 
			select lt.id, lt.codigo, UPPER(lt.lugar) as nombre, count(*) as cantidad from olim_estudiante_inscripcion oei
			inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
			inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
			inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
			inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
			left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
			where oei.gestion_tipo_id = :gestion::double precision and lt.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0			
			group by lt.id, lt.codigo, lt.lugar
			order by cantidad desc, nombre asc
			");
		}
		
		$query->bindValue(':gestion', $gestion);
		$query->execute();
		$inscritos = $query->fetchAll();
		return $inscritos;
	}
	
	/**
     * Imprime reportes estadisticos a nivel nacional en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function registradosPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $codigo = 0;
		$nivel = 0;	
		$nivelSiguiente = 0;

		if ($request->isMethod('POST')) {
            $codigo = base64_decode($request->get('codigo'));
			$nivel = $request->get('nivel');
        } else {
            $codigo = 0;
			$nivel = 0;	
		}		
		$gestion = $gestionActual;

        $arch = 'Olim_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));        
        if($nivel == 0){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Departamental_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Distrital_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		}        
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
    public function registradosXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $codigo = 0;
		$nivel = 0;	
		$nivelSiguiente = 0;

		if ($request->isMethod('POST')) {
            $codigo = base64_decode($request->get('codigo'));
			$nivel = $request->get('nivel');
        } else {
            $codigo = 0;
			$nivel = 0;	
		}
		
		$gestion = $gestionActual;

        $arch = 'Olim_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($nivel == 0){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Departamental_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Distrital_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		}  
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