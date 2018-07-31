<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\DocumentoController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Vista controller.
 *
 */
class TramiteEstadisticaController extends Controller {
    private $route_anterior;
    private $var_anterior;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function defaultAction() {
        $bimestre = 1;
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity,$bimestre);
        
        return $this->render('SieEspecialBundle:Estadistica:consolidacion.html.twig', array('periodo'=>$bimestre,'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
    } 
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista la estadística de diplomas humanisticos impresos segun la gestión de egreso del estudiante y el nivel autorizado que tiene el usuario
    // PARAMETROS: usuarioId, rolId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEgresoAction(Request $request) {    
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	$gestionActual = date_format($fechaActual,'Y');
		// dump($gestionActual);die;
		$sesion = $request->getSession();
		$id_usuario = $sesion->get('userId');	

		//validation if the user is logged
		if (!isset($id_usuario)) {
			return $this->redirect($this->generateUrl('login'));
		}

		$defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

		$activeMenu = $defaultTramiteController->setActiveMenu();
		
		if(empty($activeMenu)){
			$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Módulo inhabilitado por el administrador, comuniquese con su Técnico SIE'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
		} 	

		if ($request->isMethod('POST')) {
			$gestion = $request->get('gestion');
			$lugarNivel = base64_decode($request->get('nivel'));
			$lugar = base64_decode($request->get('codigo'));	
		} else {
			$gestion = $gestionActual;
			$entidadUsuarioRol = $defaultTramiteController->getUserRoles($id_usuario);	
			if(!empty($entidadUsuarioRol)){
				$em = $this->getDoctrine()->getManager();
				$entidadLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $entidadUsuarioRol[0]['rollugarid']));
				$lugar = $entidadLugarTipo->getCodigo();	
				$lugarNivel = $entidadUsuarioRol[0]['orden'];
			} else {
				$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Su usuario no esta autorizado para acceder al sistema, comuniquese con su Técnico SIE y verifique los roles de acceso'));
            	return $this->redirect($this->generateUrl('tramite_homepage'));
			}	
		}
		
		$entidad = $this->buscaDipHumGestionEgreso($lugarNivel,$lugar,$gestion);
		
		$entityGestion = $defaultTramiteController->getGestiones(2009);

		$total_general = 0;
		foreach ($entidad as $key => $dato) {
			$total_general = $total_general + $dato['cantidad'];
		}

		if(count($entidad)>0 and isset($entidad)){
            foreach ($entidad as $key => $dato) {
                $entidad[$key]['total_general'] = $total_general;
            }
		} 

		if(count($entidad)>0){
			return $this->render($this->session->get('pathSystem') . ':Estadistica:dipHumEgreso.html.twig', array(
				'infoEntidad'=>$entidad,
				'gestiones'=>$entityGestion,
				'gestion'=>$gestion,
			)); 
		} else {
			return $this->render($this->session->get('pathSystem') . ':Estadistica:dipHumEgreso.html.twig', array(
				'gestiones'=>$entityGestion,
				'gestion'=>$gestion,
			)); 
		}		
	}
	
	public function buscaDipHumGestionEgreso($lugarNivel,$lugar,$gestion) {
		$em = $this->getDoctrine()->getManager();	
		if ($lugarNivel == 1) {
			$queryEntidad = $em->getConnection()->prepare("
				select 'Departamentos' as nombre_nivel, ie.departamento_id as id, ie.departamento_codigo as codigo, ie.departamento as nombre
				, ".$lugarNivel." as nivel_id, 2 as siguiente_nivel_id, '".$lugar."' as lugar_codigo
				, sum(case iec.nivel_tipo_id when 3 then 1 when 13 then 1 else 0 end) cantidad_reg
				, sum(case iec.nivel_tipo_id when 5 then 1 when 15 then 1 else 0 end) cantidad_alt
				, count(d.id) as cantidad
				from tramite as t
				inner join documento as d on d.tramite_id = t.id
				inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = t.estudiante_inscripcion_id
				inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
				inner join (
				select dep.id as departamento_id, dep.codigo as departamento_codigo, dep.lugar as departamento
				, dis.id as distrito_id, dis.codigo as distrito_codigo, dis.lugar as distrito
				, ins.id as institucioneducativa_id, cast(ins.id as varchar) as institucioneducativa_codigo, ins.institucioneducativa
				from institucioneducativa as ins
				inner join jurisdiccion_geografica as jg on jg.id = ins.le_juridicciongeografica_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id, area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id		
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
				) as ie on ie.institucioneducativa_id = iec.institucioneducativa_id
				where iec.gestion_tipo_id = ".$gestion."::double precision and iec.nivel_tipo_id in (3,13,5,15) and d.documento_tipo_id in (1,3,4,5) and d.documento_estado_id = 1 -- and ie.departamento_codigo = '1'
				group by ie.departamento_id, ie.departamento_codigo, ie.departamento 
				order by ie.departamento_id
            ");
		} elseif ($lugarNivel == 2) {
			$queryEntidad = $em->getConnection()->prepare("
				select 'Distritos Educativos' as nombre_nivel, ie.distrito_id as id, ie.distrito_codigo as codigo, ie.distrito as nombre
				, ".$lugarNivel." as nivel_id, 7 as siguiente_nivel_id, '".$lugar."' as lugar_codigo
				, sum(case iec.nivel_tipo_id when 3 then 1 when 13 then 1 else 0 end) cantidad_reg
				, sum(case iec.nivel_tipo_id when 5 then 1 when 15 then 1 else 0 end) cantidad_alt
				, count(d.id) as cantidad
				from tramite as t
				inner join documento as d on d.tramite_id = t.id
				inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = t.estudiante_inscripcion_id
				inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
				inner join (
				select dep.id as departamento_id, dep.codigo as departamento_codigo, dep.lugar as departamento
				, dis.id as distrito_id, dis.codigo as distrito_codigo, dis.lugar as distrito
				, ins.id as institucioneducativa_id, cast(ins.id as varchar) as institucioneducativa_codigo, ins.institucioneducativa
				from institucioneducativa as ins
				inner join jurisdiccion_geografica as jg on jg.id = ins.le_juridicciongeografica_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id, area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id		
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
				) as ie on ie.institucioneducativa_id = iec.institucioneducativa_id
				where iec.gestion_tipo_id = ".$gestion."::double precision and iec.nivel_tipo_id in (3,13,5,15) and ie.departamento_codigo = '".$lugar."' and d.documento_tipo_id in (1,3,4,5) and d.documento_estado_id = 1
				group by ie.distrito_id, ie.distrito_codigo, ie.distrito 
				order by ie.distrito_id 
            ");
				
		} elseif ($lugarNivel == 7) {
			$queryEntidad = $em->getConnection()->prepare("
				select 'U.E./C.E.A.' as nombre_nivel, ie.id as id, ie.id as codigo, ie.institucioneducativa as nombre, ".$lugarNivel." as nivel_id
				, 0 as siguiente_nivel_id, '".$lugar."' as lugar_codigo
				, sum(case iec.nivel_tipo_id when 3 then 1 when 13 then 1 else 0 end) cantidad_reg
				, sum(case iec.nivel_tipo_id when 5 then 1 when 15 then 1 else 0 end) cantidad_alt
				, count(d.id) as cantidad
				from tramite as t
				inner join documento as d on d.tramite_id = t.id
				inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = t.estudiante_inscripcion_id
				inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
				inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
				inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
				where iec.gestion_tipo_id = ".$gestion."::double precision and iec.nivel_tipo_id in (3,13,5,15) and dis.codigo = '".$lugar."' and d.documento_tipo_id in (1,3,4,5) and d.documento_estado_id = 1
				group by ie.id, ie.institucioneducativa
				order by ie.id
            ");
		} else {
			$queryEntidad = $em->getConnection()->prepare("
				select 'Departamentos' as nombre_nivel, ie.departamento_id as id, ie.departamento_codigo as codigo, ie.departamento as nombre
				, ".$lugarNivel." as nivel_id, 2 as siguiente_nivel_id, '".$lugar."' as lugar_codigo
				, sum(case iec.nivel_tipo_id when 3 then 1 when 13 then 1 else 0 end) cantidad_reg
				, sum(case iec.nivel_tipo_id when 5 then 1 when 15 then 1 else 0 end) cantidad_alt
				, count(d.id) as cantidad
				from tramite as t
				inner join documento as d on d.tramite_id = t.id
				inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = t.estudiante_inscripcion_id
				inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
				inner join (
				select dep.id as departamento_id, dep.codigo as departamento_codigo, dep.lugar as departamento
				, dis.id as distrito_id, dis.codigo as distrito_codigo, dis.lugar as distrito
				, ins.id as institucioneducativa_id, cast(ins.id as varchar) as institucioneducativa_codigo, ins.institucioneducativa
				from institucioneducativa as ins
				inner join jurisdiccion_geografica as jg on jg.id = ins.le_juridicciongeografica_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id, area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id		
				inner join (SELECT id, codigo, lugar, lugar_tipo_id FROM lugar_tipo WHERE lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
				) as ie on ie.institucioneducativa_id = iec.institucioneducativa_id
				where iec.gestion_tipo_id = ".$gestion."::double precision and iec.nivel_tipo_id in (3,13,5,15) and d.documento_tipo_id in (1,3,4,5) and d.documento_estado_id = 1 -- and ie.departamento_codigo = '1'
				group by ie.departamento_id, ie.departamento_codigo, ie.departamento 
				order by ie.departamento_id
            ");		
		}
		
		$queryEntidad->execute();
		$objEntidad = $queryEntidad->fetchAll();
		if (count($objEntidad)>0 ){
			return $objEntidad;
		} else {
			return array();
		}		
    }    
}

