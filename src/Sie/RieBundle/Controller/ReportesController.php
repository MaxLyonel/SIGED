<?php

namespace Sie\RieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

/**
 * EstudianteInscripcion controller.
 *
 */
class ReportesController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

     /***
     *  Reporte para Certificado de ITT
     */
    public function repUnoAction(Request $request) {
        $arch = 'CERTIFICADO_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_cert_certificadottec_v2_afv.rptdesign&institucioneducativa_id='.$request->get('idInstitucion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Reportes para mostrar datos de certificados
     */
    public function repRieCertificadosAction(Request $request) {
        $arch = 'CERTIFICADOS_'.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_certificados_itt_v1_oyq.rptdesign&idCertificados='.$request->get('idCertificados').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }    

    /***
     *  Reporte de Institutos Vigentes
     */
    public function listAction(){
        $datos = $this->obtieneDatosPrincipal();
        return $this->render('SieRieBundle:Reportes:list.html.twig', array('rie' => $datos));
    }
      
    public function reportegeneralAction(Request $request){
        $listados = $this->obtieneListadoDepartamento($request->get('opc'));
        switch($request->get('opc')){
            case 2:  $depto = 'CHUQUISACA'; break;
            case 3:  $depto = 'LA PAZ';     break;
            case 4:  $depto = 'COCHABAMBA'; break;
            case 5:  $depto = 'ORURO';      break;
            case 6:  $depto = 'POTOSI';     break;
            case 7:  $depto = 'TARIJA';     break;
            case 8:  $depto = 'SANTA CRUZ'; break;
            case 9:  $depto = 'BENI';       break;
            case 10: $depto = 'PANDO';      break;
        }

        return $this->render('SieRieBundle:Reportes:listtabla.html.twig',  array('listados' => $listados, 'depto'=>$depto));        
    }

    /***
     *  Reporte de Institutos Vigentes
     */
    public function institutoVigente(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT se
                                     FROM SieAppWebBundle:TtecInstitucioneducativaSede se
                                     JOIN se.institucioneducativa ie 
                                    WHERE ie.institucioneducativaTipo in (:idTipo)
                                      AND ie.estadoinstitucionTipo in (:idEstado)
                                      AND se.estado = :estadoSede
                                 ORDER BY ie.id ')
                        ->setParameter('idTipo', array(7, 8, 9))
                        ->setParameter('idEstado', 10)
                        ->setParameter('estadoSede', TRUE);        
        $entities = $query->getResult();         
        return $entities;
    }

    /***
     * Obtiene listado de institutos según departamento
     */
     public function obtieneListadoDepartamento($dep){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = " SELECT
                        inst.id AS ie_id,
                        inst.institucioneducativa,
                        TO_CHAR(inst.fecha_resolucion, 'dd/mm/yyyy') AS fecha_resolucion,
                        inst.nro_resolucion,
                        instipo.descripcion AS tipo,
                        string_agg(nivt.nivel,', ') as nivelautorizado,
                        lt4.lugar AS departamento,
                        lt3.lugar AS provincia,
                        lt2.lugar AS municipio,
                        lt1.lugar AS canton,
                        lt.lugar AS localidad,
                        dept.dependencia,
                        inst.le_juridicciongeografica_id,
                        jurg.zona,
                        jurg.direccion,
                        case when dependencia_tipo_id = '3' then TO_CHAR(inst.fecha_resolucion + interval '6 year', 'dd/mm/yyyy') else 'INDEFINIDO' end as vigente,
                        estinst.estadoinstitucion AS estado,
                        (case when instsede.sede = inst.id then 'Sede Central' else 'Subsede' end) AS sede
                        FROM
                        public.institucioneducativa AS inst
                        INNER JOIN public.institucioneducativa_tipo AS instipo ON inst.institucioneducativa_tipo_id = instipo.id
                        INNER JOIN public.estadoinstitucion_tipo AS estinst ON inst.estadoinstitucion_tipo_id = estinst.id
                        INNER JOIN public.ttec_institucioneducativa_sede AS instsede ON instsede.institucioneducativa_id = inst.id
                        INNER JOIN public.jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
                        LEFT JOIN public.lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
                        LEFT JOIN public.lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                        LEFT JOIN public.lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                        LEFT JOIN public.lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                        LEFT JOIN public.lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                        INNER JOIN public.dependencia_tipo AS dept ON inst.dependencia_tipo_id = dept.id
                        INNER JOIN public.institucioneducativa_nivel_autorizado AS instnivaut ON instnivaut.institucioneducativa_id = inst.id
                        INNER JOIN public.nivel_tipo AS nivt ON instnivaut.nivel_tipo_id = nivt.id
                        WHERE lt4.id = ".$dep." AND inst.institucioneducativa_tipo_id IN (7,8,9) 
                        AND estinst.id IN (10, 19)
                        AND institucioneducativa_acreditacion_tipo_id = 2
                        GROUP BY
                        inst.id,
                        inst.institucioneducativa,
                        inst.fecha_resolucion,
                        inst.nro_resolucion,
                        instipo.descripcion,
                        estinst.estadoinstitucion,
                        lt4.lugar,
                        lt3.lugar,
                        lt2.lugar,
                        lt1.lugar,
                        lt.lugar,
                        dept.dependencia,
                        inst.le_juridicciongeografica_id,
                        jurg.zona,
                        jurg.direccion,
                        instsede.sede        
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po=$stmt->fetchAll();
        return $po;        
    } 

    /***
     * Obtiene datos para página principal de RIE
     */
    public function obtieneDatosPrincipal(){
        $nacional = array(); 
        $nal_fiscal  = 0; $nal_convenio= 0; $nal_privado = 0;

        $nacional['BNI']['fiscal']   = $this->nroinstitutosDepartamento(9, 1);
        $nacional['BNI']['convenio'] = $this->nroinstitutosDepartamento(9, 2);
        $nacional['BNI']['privado']  = $this->nroinstitutosDepartamento(9, 3);
        $nal_fiscal  = $nal_fiscal  + $nacional['BNI']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['BNI']['convenio'];
        $nal_privado = $nal_privado + $nacional['BNI']['privado'];

        $nacional['CHQ']['fiscal']   = $this->nroinstitutosDepartamento(2, 1);
        $nacional['CHQ']['convenio'] = $this->nroinstitutosDepartamento(2, 2);
        $nacional['CHQ']['privado']  = $this->nroinstitutosDepartamento(2, 3);        
        $nal_fiscal  = $nal_fiscal  + $nacional['CHQ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['CHQ']['convenio'];
        $nal_privado = $nal_privado + $nacional['CHQ']['privado'];        

        $nacional['CBA']['fiscal']   = $this->nroinstitutosDepartamento(4, 1);
        $nacional['CBA']['convenio'] = $this->nroinstitutosDepartamento(4, 2);
        $nacional['CBA']['privado']  = $this->nroinstitutosDepartamento(4, 3);   
        $nal_fiscal  = $nal_fiscal  + $nacional['CBA']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['CBA']['convenio'];
        $nal_privado = $nal_privado + $nacional['CBA']['privado'];           

        $nacional['LPZ']['fiscal']  = $this->nroinstitutosDepartamento(3, 1);
        $nacional['LPZ']['convenio'] = $this->nroinstitutosDepartamento(3, 2);
        $nacional['LPZ']['privado']  = $this->nroinstitutosDepartamento(3, 3); 
        $nal_fiscal  = $nal_fiscal  + $nacional['LPZ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['LPZ']['convenio'];
        $nal_privado = $nal_privado + $nacional['LPZ']['privado'];           
        
        $nacional['ORU']['fiscal']   = $this->nroinstitutosDepartamento(5, 1);
        $nacional['ORU']['convenio'] = $this->nroinstitutosDepartamento(5, 2);
        $nacional['ORU']['privado']  = $this->nroinstitutosDepartamento(5, 3);   
        $nal_fiscal  = $nal_fiscal  + $nacional['ORU']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['ORU']['convenio'];
        $nal_privado = $nal_privado + $nacional['ORU']['privado'];  

        $nacional['PND']['fiscal']   = $this->nroinstitutosDepartamento(10, 1);
        $nacional['PND']['convenio'] = $this->nroinstitutosDepartamento(10, 2);
        $nacional['PND']['privado']  = $this->nroinstitutosDepartamento(10, 3);  
        $nal_fiscal  = $nal_fiscal  + $nacional['PND']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['PND']['convenio'];
        $nal_privado = $nal_privado + $nacional['PND']['privado'];  
        
        $nacional['PSI']['fiscal']   = $this->nroinstitutosDepartamento(6, 1);
        $nacional['PSI']['convenio'] = $this->nroinstitutosDepartamento(6, 2);
        $nacional['PSI']['privado']  = $this->nroinstitutosDepartamento(6, 3);  
        $nal_fiscal  = $nal_fiscal  + $nacional['PSI']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['PSI']['convenio'];
        $nal_privado = $nal_privado + $nacional['PSI']['privado'];                   
        
        $nacional['SCZ']['fiscal']   = $this->nroinstitutosDepartamento(8, 1);
        $nacional['SCZ']['convenio'] = $this->nroinstitutosDepartamento(8, 2);
        $nacional['SCZ']['privado']  = $this->nroinstitutosDepartamento(8, 3);
        $nal_fiscal  = $nal_fiscal  + $nacional['SCZ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['SCZ']['convenio'];
        $nal_privado = $nal_privado + $nacional['SCZ']['privado'];            

        $nacional['TJA']['fiscal']  = $this->nroinstitutosDepartamento(7, 1);
        $nacional['TJA']['convenio'] = $this->nroinstitutosDepartamento(7, 2);
        $nacional['TJA']['privado']  = $this->nroinstitutosDepartamento(7, 3); 
        $nal_fiscal  = $nal_fiscal  + $nacional['TJA']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['TJA']['convenio'];
        $nal_privado = $nal_privado + $nacional['TJA']['privado'];            

        $nacional['NAL']['fiscal']   = $nal_fiscal; 
        $nacional['NAL']['convenio'] = $nal_convenio;
        $nacional['NAL']['privado']  = $nal_privado;

        return $nacional;
    }

    /***
     * Obtiene datos de Cantidad de Institutos por Departamento
     */
    public function nroinstitutosDepartamento($departamento, $dependencia){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();       
        

        //por bloqueo de base de datos
        //12/12/2023

        // $query = 'SELECT
        // 0 AS cantidad';


        /*$query = 'SELECT
                    COUNT(inst.id) AS cantidad 
                    FROM
                    public.institucioneducativa AS inst
                    INNER JOIN public.institucioneducativa_tipo AS instipo ON inst.institucioneducativa_tipo_id = instipo.id
                    INNER JOIN public.estadoinstitucion_tipo AS estinst ON inst.estadoinstitucion_tipo_id = estinst.id
                    INNER JOIN public.jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
                    LEFT JOIN public.lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
                    LEFT JOIN public.lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                    INNER JOIN public.dependencia_tipo AS dept ON inst.dependencia_tipo_id = dept.id
                    WHERE lt4.id = '.$departamento.' AND inst.institucioneducativa_tipo_id IN (7,8,9) 
                    AND estinst.id IN (10) 
                    AND institucioneducativa_acreditacion_tipo_id = 2
                    AND dept.id = '.$dependencia;*/
        // $stmt = $db->prepare($query);
        /*$params = array();
        $stmt->execute($params);*/
        // $po=$stmt->fetchAll();
        // return $po[0]['cantidad']; 
        return 0;                 
    }

    /**
     * Reportes de sedes y subsedes existentes
     */
    public function reporteSubsedeAction(){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT ie.* 
                    FROM institucioneducativa AS ie
                    INNER JOIN ttec_institucioneducativa_sede AS sede ON ie.id = sede.sede
                    GROUP BY ie.id
                    ORDER BY ie.institucioneducativa
                 ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $sedes=$stmt->fetchAll();   
        $principal = array();
        $i = 0;

        foreach($sedes as $sede){
            $principal[$i]['idRie'] = $sede['id'];
            $principal[$i]['idLe'] = $sede['le_juridicciongeografica_id'];
            $principal[$i]['denominacion'] = $sede['institucioneducativa'];
            $principal[$i]['estado'] = $sede['estadoinstitucion_tipo_id'];
            $principal[$i]['fecha'] = $sede['fecha_resolucion'];
            $principal[$i]['resolucion'] = $sede['nro_resolucion'];
            $principal[$i]['sede'] = 'SEDE';

            $query = "SELECT ie.id, se.sede, ie.institucioneducativa, ie.le_juridicciongeografica_id, ie.estadoinstitucion_tipo_id, ie.fecha_resolucion, ie.nro_resolucion
                        FROM institucioneducativa AS ie
                        INNER JOIN ttec_institucioneducativa_sede AS se ON ie.id = se.institucioneducativa_id
                        WHERE se.sede = ".$sede['id']." 
                          AND ie.id != ".$sede['id']."
                        ORDER BY ie.institucioneducativa";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $datos=$stmt->fetchAll();   
            $subsedes = array(); 
            $j = 0;   
            foreach($datos as $dato){
                $subsedes[$j]['idRie'] = $dato['id'];
                $subsedes[$j]['idLe'] = $dato['le_juridicciongeografica_id'];
                $subsedes[$j]['denominacion'] = $dato['institucioneducativa'];
                $subsedes[$j]['estado'] = $dato['estadoinstitucion_tipo_id'];
                $subsedes[$j]['fecha'] = $dato['fecha_resolucion'];
                $subsedes[$j]['resolucion'] = $dato['nro_resolucion'];
                $principal[$i]['sede'] = 'SUBSEDE';
                $j++;
            }
            $principal[$i]['subsedes'] = $subsedes;
            $i++;
        }

        return $this->render('SieRieBundle:Reportes:listsede.html.twig', array('listado' => $principal));
    }

    /***
     * Obtiene listado de institutos según departamento
     */
    public function obtieneListadoDepartamentoXX($dep){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT se
                                    FROM SieAppWebBundle:TtecInstitucioneducativaSede se
                                    JOIN se.institucioneducativa ie
                                    JOIN ie.leJuridicciongeografica le
                                    JOIN le.lugarTipoLocalidad lo
                                    JOIN lo.lugarTipo ca
                                    JOIN ca.lugarTipo sec
                                    JOIN sec.lugarTipo pr
                                    JOIN pr.lugarTipo de
                                    WHERE ie.institucioneducativaTipo in (:idTipo)
                                    AND de.id = :idDep
                                    AND se.estado = :estadoSede
                                ORDER BY ie.id ')
                            ->setParameter('idDep', $dep)    
                            ->setParameter('idTipo', array(7, 8, 9))
                            ->setParameter('estadoSede', TRUE);        
        $datos = $query->getResult();         
        return $datos;
    } 
}
