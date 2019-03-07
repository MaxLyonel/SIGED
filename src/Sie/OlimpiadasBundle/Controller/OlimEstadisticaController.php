<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * OlimEstadistica controller.
 * Autor: rcanaviri
 */
class OlimEstadisticaController extends Controller{

	/**
     * Controlador que descarga la lista de registrados en general según nivel de desagregación y código de lugar
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
	public function registradosIndexAction(Request $request){
		/*
		* Define la zona horaria y halla la fecha actual
		*/
	   	date_default_timezone_set('America/La_Paz');
	   	$fechaActual = new \DateTime(date('Y-m-d'));
		$gestionActual = date_format($fechaActual,'Y');
		   		
		$sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $id_rol = $sesion->get('roluser');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

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
			if ($id_rol == 8 or $id_rol == 35 or $id_rol == 20){
				$nivel = 0;	
				$nivelSiguiente = 1;
			} else if($id_rol == 7){
				$nivel = 1;
				$nivelSiguiente = 7;
			} else if($id_rol == 10){
				$nivel = 7;	
				$nivelSiguiente = 0;
			} else {
				$nivel = 0;
				$nivelSiguiente = 1;
			}	
			$em = $this->getDoctrine()->getManager();
			$entidadUsuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id_usuario, 'rolTipo' => $id_rol));
			
			if (count($entidadUsuario)>0){
				$codigo = $entidadUsuario->getLugarTipo()->getCodigo();
			} else {
				$codigo = 0;
			}         
		}
		
		$inscritos = $this->getRegistradosEtapa1($nivel,$codigo,$gestionActual);
		//dump($inscritos);die;

		return $this->render('SieOlimpiadasBundle:OlimEstadistica:registrados.html.twig', array(
			'estadistica'=>$inscritos,
			'nivel'=>$nivel,
			'nivelSiguiente'=>$nivelSiguiente,
		));
	}

	/**
     * Busca la cantidad de registros en general de la etapa 1 según el nivel de desagregacion, codigo del lugar y la gestión
     * Autor: rcanaviri
     * @param $nivel,$codigo,$gestion
     * @return $entity
     */
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
				from (select distinct lt.* from lugar_tipo as lt inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id where lt.lugar_nivel_id = 7 and lt1.codigo = '".$codigo."') as dis
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
     * Imprime reportes estadisticos en general segun nivel de desagregación y codigo de lugar en formato PDF
     * Autor: rcanaviri
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
     * Imprime reportes estadisticos en general segun nivel de desagregación y codigo de lugar en formato XLS
     * Autor: rcanaviri
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
	

	/**
     * Controlador que descarga la lista de registrados por área según nivel de desagregación y código de lugar
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
	public function registradosAreaIndexAction(Request $request){
		/*
		* Define la zona horaria y halla la fecha actual
		*/
	   	date_default_timezone_set('America/La_Paz');
	   	$fechaActual = new \DateTime(date('Y-m-d'));
	   	$gestionActual = date_format($fechaActual,'Y');

		$sesion = $request->getSession();
		$id_usuario = $sesion->get('userId');
        $id_rol = $sesion->get('roluser');
		//validation if the user is logged
		if (!isset($id_usuario)) {
			return $this->redirect($this->generateUrl('login'));
		}

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
            if ($id_rol == 8 or $id_rol == 35 or $id_rol == 20){
				$nivel = 0;	
				$nivelSiguiente = 1;
			} else if($id_rol == 7){
				$nivel = 1;
				$nivelSiguiente = 7;
			} else if($id_rol == 10){
				$nivel = 7;	
				$nivelSiguiente = 0;
			} else {
				$nivel = 0;
				$nivelSiguiente = 1;
			}	
			$em = $this->getDoctrine()->getManager();
			$entidadUsuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id_usuario, 'rolTipo' => $id_rol));
			
			if (count($entidadUsuario)>0){
				$codigo = $entidadUsuario->getLugarTipo()->getCodigo();
			} else {
				$codigo = 0;
			} 	
		}
		$inscritos = $this->getRegistradosAreaEtapa1($nivel,$codigo,$gestionActual);


		return $this->render('SieOlimpiadasBundle:OlimEstadistica:registradosArea.html.twig', array(
			'estadistica'=>$inscritos,
			'nivel'=>$nivel,
			'nivelSiguiente'=>$nivelSiguiente,
		));
	}

	/**
     * Busca la cantidad de registros por área de la etapa 1 según el nivel de desagregacion, codigo del lugar y la gestión
     * Autor: rcanaviri
     * @param $nivel,$codigo,$gestion
     * @return $entity
     */
	private function getRegistradosAreaEtapa1($nivel,$codigo,$gestion){
		$em = $this->getDoctrine()->getManager();

		if($nivel == 0){
			$query = $em->getConnection()->prepare("				
				select dep.id, dep.codigo, dep.lugar as nombre
				, COALESCE(oli.area1,0) as area1, COALESCE(oli.area2,0) as area2, COALESCE(oli.area3,0) as area3, COALESCE(oli.area4,0) as area4
				, COALESCE(oli.area5,0) as area5, COALESCE(oli.area6,0) as area6, COALESCE(oli.area7,0) as area7, COALESCE(oli.area8,0) as area8
				, COALESCE(oli.area9,0) as area9, COALESCE(oli.cantidad,0) as cantidad
				from (select * from lugar_tipo where lugar_nivel_id = 1) as dep
				left join (
				select lt4.id as departamento_id
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
				) as oli on oli.departamento_id = dep.id
				union all 
				select 1 as id, '0' as codigo, 'Bolivia' as nombre
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				where oei.gestion_tipo_id = :gestion::double precision
				order by cantidad desc, nombre asc
			");
		}

		if($nivel == 1){
			$query = $em->getConnection()->prepare("				
				select dis.id, dis.codigo, UPPER(dis.lugar) as nombre
				, COALESCE(oli.area1,0) as area1, COALESCE(oli.area2,0) as area2, COALESCE(oli.area3,0) as area3, COALESCE(oli.area4,0) as area4
				, COALESCE(oli.area5,0) as area5, COALESCE(oli.area6,0) as area6, COALESCE(oli.area7,0) as area7, COALESCE(oli.area8,0) as area8
				, COALESCE(oli.area9,0) as area9, COALESCE(oli.cantidad,0) as cantidad
				from (select distinct lt.* from lugar_tipo as lt inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id where lt.lugar_nivel_id = 7 and lt1.codigo = '".$codigo."') as dis
				left join (
				select lt5.id
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
				select lt4.id, lt4.codigo, UPPER(lt4.lugar) as nombre
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
				select ue.id, ue.codigo, UPPER(ue.nombre) as nombre
				, COALESCE(oli.area1,0) as area1, COALESCE(oli.area2,0) as area2, COALESCE(oli.area3,0) as area3, COALESCE(oli.area4,0) as area4
				, COALESCE(oli.area5,0) as area5, COALESCE(oli.area6,0) as area6, COALESCE(oli.area7,0) as area7, COALESCE(oli.area8,0) as area8
				, COALESCE(oli.area9,0) as area9, COALESCE(oli.cantidad,0) as cantidad
				from (
				select ie.id, cast(ie.id as varchar) as codigo, ie.institucioneducativa as nombre from lugar_tipo as lt 
				inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
				inner join institucioneducativa as ie on ie.le_juridicciongeografica_id = jg.id
				where lt.codigo = '".$codigo."' and ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 1 and ie.id not in (1,2,3,4,5,6,7,8,9)
				and ie.estadoinstitucion_tipo_id = 10
				) as ue
				left join (
				select ie.id
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
				where oei.gestion_tipo_id = :gestion::double precision and lt.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by ie.id
				) as oli on oli.id = ue.id
				union all 
				select lt.id, lt.codigo, UPPER(lt.lugar) as nombre
				, SUM(case omt.id when 10 then 1 else 0 end) as area1
				, SUM(case omt.id when 11 then 1 else 0 end) as area2
				, SUM(case omt.id when 12 then 1 else 0 end) as area3
				, SUM(case omt.id when 13 then 1 else 0 end) as area4
				, SUM(case omt.id when 14 then 1 else 0 end) as area5
				, SUM(case omt.id when 15 then 1 else 0 end) as area6
				, SUM(case omt.id when 16 then 1 else 0 end) as area7
				, SUM(case omt.id when 17 then 1 else 0 end) as area8
				, SUM(case omt.id when 18 then 1 else 0 end) as area9
				, count(*) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
     * Imprime reportes estadisticos por área segun nivel de desagregación y codigo de lugar en formato PDF
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaPdfAction(Request $request) {
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Departamental_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Distrital_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		}        
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
	
	/**
     * Imprime reportes estadisticos por área segun nivel de desagregación y codigo de lugar en formato XLS
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaXlsAction(Request $request) {
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Departamental_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Distrital_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		}  
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
	}

	/**
     * Controlador que descarga la lista de registrados por área y grado de escolaridad según nivel de desagregación y código de lugar
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
	public function registradosAreaGradoIndexAction(Request $request){
		/*
		* Define la zona horaria y halla la fecha actual
		*/
	   	date_default_timezone_set('America/La_Paz');
	   	$fechaActual = new \DateTime(date('Y-m-d'));
	   	$gestionActual = date_format($fechaActual,'Y');

		$sesion = $request->getSession();
		$id_usuario = $sesion->get('userId');
        $id_rol = $sesion->get('roluser');
		//validation if the user is logged
		if (!isset($id_usuario)) {
			return $this->redirect($this->generateUrl('login'));
		}

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
            if ($id_rol == 8 or $id_rol == 35 or $id_rol == 20){
				$nivel = 0;	
				$nivelSiguiente = 1;
			} else if($id_rol == 7){
				$nivel = 1;
				$nivelSiguiente = 7;
			} else if($id_rol == 10){
				$nivel = 7;	
				$nivelSiguiente = 0;
			} else {
				$nivel = 0;
				$nivelSiguiente = 1;
			}	
			$em = $this->getDoctrine()->getManager();
			$entidadUsuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id_usuario, 'rolTipo' => $id_rol));
			
			if (count($entidadUsuario)>0){
				$codigo = $entidadUsuario->getLugarTipo()->getCodigo();
			} else {
				$codigo = 0;
			} 	
		}
		
		//dump($codigo);die;
		$entity = $this->getRegistradosAreaGradoEtapa1($nivel,$codigo,$gestionActual);
		$aInfo = array();
		$dDato = array();
		$aDato = array();
		foreach ($entity as $key => $dato) {
			$aDato = array('pri1' => $dato['pri1'],'pri2' => $dato['pri2'],'pri3' => $dato['pri3'],'pri4' => $dato['pri4'],'pri5' => $dato['pri5'],'pri6' => $dato['pri6'],'pri' => $dato['pri'],'sec1' => $dato['sec1'],'sec2' => $dato['sec2'],'sec3' => $dato['sec3'],'sec4' => $dato['sec4'],'sec5' => $dato['sec5'],'sec6' => $dato['sec6'],'sec' => $dato['sec'],'cantidad' => $dato['cantidad']);
			//send the values to the next steps
			$aInfo[$dato['codigo']]['codigo'] = $dato['codigo'];
			$aInfo[$dato['codigo']]['dato'][$dato['nombre']][$dato['materia']] = $aDato;
		}
		//dump($aInfo);die;
		$inscritos = $aInfo;
		return $this->render('SieOlimpiadasBundle:OlimEstadistica:registradosAreaGrado.html.twig', array(
			'estadistica'=>$inscritos,
			'nivel'=>$nivel,
			'nivelSiguiente'=>$nivelSiguiente,
		));
	}

	/**
     * Busca la cantidad de registros por área y grado de escolaridad de la etapa 1 según el nivel de desagregacion, codigo del lugar y la gestión
     * Autor: rcanaviri
     * @param $nivel,$codigo,$gestion
     * @return $entity
     */
	private function getRegistradosAreaGradoEtapa1($nivel,$codigo,$gestion){
		$em = $this->getDoctrine()->getManager();

		if($nivel == 0){
			$query = $em->getConnection()->prepare("				
				select dep.id, dep.codigo, UPPER(dep.lugar) as nombre, omt.id as materia_id, omt.materia
				, COALESCE(oli.pri1,0) as pri1, COALESCE(oli.pri2,0) as pri2, COALESCE(oli.pri3,0) as pri3, COALESCE(oli.pri4,0) as pri4, COALESCE(oli.pri5,0) as pri5, COALESCE(oli.pri6,0) as pri6, COALESCE(oli.pri,0) as pri
				, COALESCE(oli.sec1,0) as sec1, COALESCE(oli.sec2,0) as sec2, COALESCE(oli.sec3,0) as sec3, COALESCE(oli.sec4,0) as sec4, COALESCE(oli.sec5,0) as sec5, COALESCE(oli.sec6,0) as sec6, COALESCE(oli.sec,0) as sec
				, COALESCE(oli.cantidad,0) as cantidad
				from (select * from lugar_tipo where lugar_nivel_id = 1) as dep
				inner join (
				select lt4.id as departamento_id, omt.id as materia_id
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id, omt.id
				) as oli on oli.departamento_id = dep.id
				inner join olim_materia_tipo as omt on omt.id = oli.materia_id
				union all 
				select 1 as id, '0' as codigo, upper('Bolivia') as nombre, omt.id as materia_id, omt.materia
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision
				group by omt.id
				order by codigo asc, materia asc
			");
		}

		if($nivel == 1){
			$query = $em->getConnection()->prepare("	
				select dis.id, dis.codigo, UPPER(dis.lugar) as nombre, omt.id as materia_id, omt.materia
				, COALESCE(oli.pri1,0) as pri1, COALESCE(oli.pri2,0) as pri2, COALESCE(oli.pri3,0) as pri3, COALESCE(oli.pri4,0) as pri4, COALESCE(oli.pri5,0) as pri5, COALESCE(oli.pri6,0) as pri6, COALESCE(oli.pri,0) as pri
				, COALESCE(oli.sec1,0) as sec1, COALESCE(oli.sec2,0) as sec2, COALESCE(oli.sec3,0) as sec3, COALESCE(oli.sec4,0) as sec4, COALESCE(oli.sec5,0) as sec5, COALESCE(oli.sec6,0) as sec6, COALESCE(oli.sec,0) as sec
				, COALESCE(oli.cantidad,0) as cantidad
				from (select distinct lt.* from lugar_tipo as lt inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id where lt.lugar_nivel_id = 7 and lt1.codigo = '".$codigo."') as dis
				inner join (
				select lt5.id, omt.id as materia_id
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision and lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt5.id, omt.id
				) as oli on oli.id = dis.id
				inner join olim_materia_tipo as omt on omt.id = oli.materia_id
				union all
				select lt4.id, lt4.codigo, UPPER(lt4.lugar) as nombre, omt.id as materia_id, omt.materia
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
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
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision and lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id, lt4.codigo, lt4.lugar, omt.id
				order by codigo asc, materia asc	
			");
		}

		if($nivel == 7){
			$query = $em->getConnection()->prepare("
				select ue.id, ue.codigo, UPPER(ue.nombre) as nombre, omt.id as materia_id, omt.materia
				, COALESCE(oli.pri1,0) as pri1, COALESCE(oli.pri2,0) as pri2, COALESCE(oli.pri3,0) as pri3, COALESCE(oli.pri4,0) as pri4, COALESCE(oli.pri5,0) as pri5, COALESCE(oli.pri6,0) as pri6, COALESCE(oli.pri,0) as pri
				, COALESCE(oli.sec1,0) as sec1, COALESCE(oli.sec2,0) as sec2, COALESCE(oli.sec3,0) as sec3, COALESCE(oli.sec4,0) as sec4, COALESCE(oli.sec5,0) as sec5, COALESCE(oli.sec6,0) as sec6, COALESCE(oli.sec,0) as sec
				, COALESCE(oli.cantidad,0) as cantidad
				from (
				select ie.id, cast(ie.id as varchar) as codigo, ie.institucioneducativa as nombre from lugar_tipo as lt 
				inner join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
				inner join institucioneducativa as ie on ie.le_juridicciongeografica_id = jg.id
				where lt.codigo = '".$codigo."' and ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 1 and ie.id not in (1,2,3,4,5,6,7,8,9)
				and ie.estadoinstitucion_tipo_id = 10
				) as ue
				inner join (
				select ie.id, omt.id as materia_id
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision and lt.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by ie.id, omt.id
				) as oli on oli.id = ue.id
				inner join olim_materia_tipo as omt on omt.id = oli.materia_id
				union all 
				select lt.id, lt.codigo, UPPER(lt.lugar) as nombre, omt.id as materia_id, omt.materia
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 12 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 12 else iec.nivel_tipo_id = 12 end) then 1 else 0 end),0) as pri
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 1 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 1 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec1
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 2 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 2 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec2
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 3 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 3 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec3
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 4 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 4 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec4
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 5 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 5 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec5
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.grado_tipo_id = 6 and oeics.nivel_tipo_id = 13 else iec.grado_tipo_id = 6 and iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec6
				, COALESCE(SUM(case when (case when oeics.id != null or oeics.id is not null then oeics.nivel_tipo_id = 13 else iec.nivel_tipo_id = 13 end) then 1 else 0 end),0) as sec
				, COALESCE(count(*),0) as cantidad 
				from olim_estudiante_inscripcion oei
				inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
				left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
				where oei.gestion_tipo_id = :gestion::double precision and lt.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0			
				group by lt.id, lt.codigo, lt.lugar, omt.id
				order by codigo asc, materia asc	
			");
		}
		
		$query->bindValue(':gestion', $gestion);
		$query->execute();
		$inscritos = $query->fetchAll();
		return $inscritos;
	}

		/**
     * Imprime reportes estadisticos por área y grado de escolaridad segun nivel de desagregación y codigo de lugar en formato PDF
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaGradoPdfAction(Request $request) {
		
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Departamental_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Distrital_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		}        
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
		return $response;
    }
	
	/**
     * Imprime reportes estadisticos por área y grado de escolaridad segun nivel de desagregación y codigo de lugar en formato XLS
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaGradoXlsAction(Request $request) {
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Departamental_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Distrital_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Grado_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		}  
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
	}

	/**
     * Controlador que descarga la lista de registrados por área y categoría según nivel de desagregación y código de lugar
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
	public function registradosAreaCategoriaIndexAction(Request $request){
		/*
		* Define la zona horaria y halla la fecha actual
		*/
	   	date_default_timezone_set('America/La_Paz');
	   	$fechaActual = new \DateTime(date('Y-m-d'));
	   	$gestionActual = date_format($fechaActual,'Y');

		$sesion = $request->getSession();
		$id_usuario = $sesion->get('userId');
        $id_rol = $sesion->get('roluser');
		//validation if the user is logged
		if (!isset($id_usuario)) {
			return $this->redirect($this->generateUrl('login'));
		}

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
            if ($id_rol == 8 or $id_rol == 35 or $id_rol == 20){
				$nivel = 0;	
				$nivelSiguiente = 1;
			} else if($id_rol == 7){
				$nivel = 1;
				$nivelSiguiente = 7;
			} else if($id_rol == 10){
				$nivel = 7;	
				$nivelSiguiente = 0;
			} else {
				$nivel = 0;
				$nivelSiguiente = 1;
			}	
			$em = $this->getDoctrine()->getManager();
			$entidadUsuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id_usuario, 'rolTipo' => $id_rol));
			
			if (count($entidadUsuario)>0){
				$codigo = $entidadUsuario->getLugarTipo()->getCodigo();
			} else {
				$codigo = 0;
			} 	
		}

		$entity = $this->getRegistradosAreaCategoriaEtapa1($nivel,$codigo,$gestionActual);
		$aInfo = array();
		foreach ($entity as $key => $dato) {
			//send the values to the next steps
			$aInfo[$dato['codigo']]['key'] = $codigo;
			$aInfo[$dato['codigo']]['codigo'] = $dato['codigo'];
			$aInfo[$dato['codigo']]['dato'][$dato['nombre']][$dato['materia']][$dato['categoria']] = $dato['cantidad'];
			// $aInfo[$dato['codigo']][$dato['nombre']][$dato['materia']][$dato['categoria']] = $dato['cantidad'];
		}
		$inscritos = $aInfo;
		return $this->render('SieOlimpiadasBundle:OlimEstadistica:registradosAreaCategoria.html.twig', array(
			'estadistica'=>$inscritos,
			'nivel'=>$nivel,
			'nivelSiguiente'=>$nivelSiguiente,
		));
	}

	/**
     * Busca la cantidad de registros por área y categoria de la etapa 1 según el nivel de desagregacion, codigo del lugar y la gestión
     * Autor: rcanaviri
     * @param $nivel,$codigo,$gestion
     * @return $entity
     */
	private function getRegistradosAreaCategoriaEtapa1($nivel,$codigo,$gestion){
		$em = $this->getDoctrine()->getManager();

		if($nivel == 0){
			$query = $em->getConnection()->prepare("			
				select lt4.id, lt4.codigo, UPPER(lt4.lugar) as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				where iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id, lt4.codigo, lt4.lugar, omt.id, omt.materia, orot.categoria
				union all
				select 1 as id, '0' as codigo, upper('Bolivia') as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				where iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by omt.id, omt.materia, orot.categoria
				order by id, codigo, nombre, materia_id, materia, categoria
			");
		}

		if($nivel == 1){
			$query = $em->getConnection()->prepare("				
				select lt5.id, lt5.codigo, UPPER(lt5.lugar) as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
				where lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt5.id, lt5.codigo, lt5.lugar, omt.id, omt.materia, orot.categoria
				union all
				select lt4.id, lt4.codigo, UPPER(lt4.lugar) as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
				left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
				left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
				left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
				left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
				where lt4.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt4.id, lt4.codigo, lt4.lugar, omt.id, omt.materia, orot.categoria
				order by id, codigo, nombre, materia_id, materia, categoria		
			");
		}

		if($nivel == 7){
			$query = $em->getConnection()->prepare("
				select ie.id, cast(ie.id as varchar) as codigo, UPPER(ie.institucioneducativa) as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
				where lt5.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by ie.id, ie.institucioneducativa, omt.id, omt.materia, orot.categoria
				union all
				select lt5.id, lt5.codigo as codigo, UPPER(lt5.lugar) as nombre, omt.id as materia_id, UPPER(omt.materia) as materia, UPPER(orot.categoria) as categoria, count(*) as cantidad from (
				select oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id, min(oei1.estudiante_inscripcion_id) as estudiante_inscripcion_id from olim_inscripcion_grupo_proyecto as oigp1
				inner join olim_estudiante_inscripcion as oei1 on oei1.id = oigp1.olim_estudiante_inscripcion_id
				inner join olim_grupo_proyecto as ogp1 on ogp1.id = oigp1.olim_grupo_proyecto_id
				where oei1.gestion_tipo_id = :gestion::double precision
				group by oigp1.olim_grupo_proyecto_id, ogp1.olim_reglas_olimpiadas_tipo_id
				union all
				select 0 as olim_grupo_proyecto_id, orot2.id as olim_reglas_olimpiadas_tipo_id, oei2.estudiante_inscripcion_id as estudiante_inscripcion_id from olim_estudiante_inscripcion as oei2
				inner join olim_reglas_olimpiadas_tipo as orot2 on orot2.id = oei2.olim_reglas_olimpiadas_tipo_id
				left join olim_inscripcion_grupo_proyecto as oigp2 on oigp2.olim_estudiante_inscripcion_id = oei2.id
				where oei2.gestion_tipo_id = :gestion::double precision and oigp2.id is null and (orot2.categoria is not null and orot2.categoria != '' and orot2.categoria != 'General')
				) as oigp
				inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oigp.olim_reglas_olimpiadas_tipo_id
				inner join olim_materia_tipo as omt on omt.id = orot.olim_materia_tipo_id
				inner join estudiante_inscripcion ei on ei.id = oigp.estudiante_inscripcion_id
				inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id  =  iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
				where lt5.codigo = '".$codigo."' and iec.nivel_tipo_id in (12,13) and iec.grado_tipo_id <> 0
				group by lt5.id, lt5.codigo, lt5.lugar, omt.id, omt.materia, orot.categoria
				order by id, codigo, nombre, materia_id, materia, categoria	
			");
		}		
		$query->bindValue(':gestion', $gestion);
		$query->execute();
		$inscritos = $query->fetchAll();
		return $inscritos;
	}

		/**
     * Imprime reportes estadisticos por área y categoria segun nivel de desagregación y codigo de lugar en formato PDF
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaCategoriaPdfAction(Request $request) {
		
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Departamental_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Distrital_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Nacional_f1_v1_rcm.rptdesign&__format=pdf&codges='.$gestion));
		}        
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
	
	/**
     * Imprime reportes estadisticos por área y categoria segun nivel de desagregación y codigo de lugar en formato XLS
     * Autor: rcanaviri
     * @param Request $request
     * @return type
     */
    public function registradosAreaCategoriaXlsAction(Request $request) {
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
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		} else if ($nivel == 1){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Departamental_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddep='.$codigo));
		} else if ($nivel == 7){
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Distrital_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion.'&coddis='.$codigo));	
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_est_Estudiantes_Participaciones_Area_Categoria_Nacional_f1_v1_rcm.rptdesign&__format=xls&codges='.$gestion));
		}  
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
	}
}