<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo;
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Sie\AppWebBundle\Entity\SuperiorModuloTipo;

/**
 * EstudianteInscripcion controller.
 *
 */
class MallaTecnicaController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction() {
        $objUeducativa = [];
        if ($this->session->get('ie_per_estado') != '-1'){
            $em = $this->getDoctrine()->getManager();
            //$em = $this->getDoctrine()->getEntityManager();
            $db = $em->getConnection();            
            $query = "  select e.id as siea, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipo, q.id as superior_turno_tipo_id, q.turno_superior, a.codigo as facultad_area_cod, a.facultad_area as facultad_area, b.id as especialidad_id, b.codigo as ciclo_id, b.especialidad as especialidad, d.codigo as acreditacion_cod, d.acreditacion as acreditacion
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id                                                                                                             
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
                                                        inner join superior_turno_tipo q on h.turno_tipo_id = q.id
                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                        and a.codigo in ('18','19','20','21','22','23','24','25')
                        order by q.id, a.codigo, b.codigo, d.codigo ";
            $stmt = $db->prepare($query);
    //        dump($query);
    //        die;
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            if (!$po) {
                $message = 'No existe información de Mallas Técnicas para la gestión seleccionada.';
                $this->addFlash('warninresult', $message);
                $exist = false;
            }
            $objUeducativa = $po;
        }
        
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('especialidad' => $uEducativa['especialidad'], 'facultad_area' => $uEducativa['facultad_area'], 'turno_superior' => $uEducativa['turno_superior']),
                    'ueducativaInfoId' => array('siea' => $uEducativa['siea'], 'superior_turno_tipo_id' => $uEducativa['superior_turno_tipo_id'], 'facultad_area_cod' => $uEducativa['facultad_area_cod'], 'especialidad_cod' => $uEducativa['ciclo_id'])
                ));
                $aInfoUnidadEductiva[$uEducativa['turno_superior']][$uEducativa['facultad_area']][$uEducativa['especialidad']] = array('infoMallTec' => $sinfoUeducativa);              
            }
        } else {
            $message = 'No existe información de Mallas Técnicas para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:index.html.twig', array(            
            'exist' => $exist,
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
        ));
    }
    
    public function seemodulosAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);        
//        dump($aInfoUeducativa);
//        die;     
        $em = $this->getDoctrine()->getManager();   
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = "  select distinct e.id as sieaid, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipoid, x.id as sptid, x.periodo_superior, a.codigo as nivel_id,b.codigo as ciclo_id,d.codigo as grado_id
                    ,d.acreditacion as acreditacion,l.id as smtid, l.modulo, h.id as area_saberes_id, h.area_superior, k.id as mod_per_hor_id, k.horas_modulo
                     from superior_facultad_area_tipo a  
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                inner join institucioneducativa_curso w on w.superior_institucioneducativa_periodo_id = g.id 
                                                    inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id 
                                                        inner join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id
                                                            inner join superior_area_saberes_tipo h on l.superior_area_saberes_tipo_id = h.id
                                                                inner join superior_periodo_tipo x on g.superior_periodo_tipo_id = x.id
                    where f.gestion_tipo_id=".$this->session->get('ie_gestion')."
                      and f.institucioneducativa_id=".$this->session->get('ie_id')."
                      and f.sucursal_tipo_id=".$this->session->get('ie_subcea')."
                      and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
                      and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']."
                      and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
                      and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3))
                      and w.turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."
                    order by d.codigo, l.modulo";
//        print_r($query);
//        die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //$periodos = array();
//        $ultimoperiodo = array();
        
        //$sieaid = $po[0]['sieaid'];
        //$aInfoUeducativa['ueducativaInfoId']['siea']
        //AVERIGUA CANTIDAD DE SEMESTRES DE LA CARRERRA
//        $query = "  select distinct e.id as sieaid, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipoid, x.id as sptid, x.periodo_superior, a.codigo as nivel_id,
//                        b.codigo as ciclo_id,d.codigo as grado_id ,d.acreditacion as acreditacion
//
//                        from superior_facultad_area_tipo a 
//                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
//                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
//                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
//                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
//                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
//                        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
//                        inner join superior_periodo_tipo x on g.superior_periodo_tipo_id = x.id 
//
//                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')."
//                      and f.institucioneducativa_id=".$this->session->get('ie_id')."
//                      and f.sucursal_tipo_id=".$this->session->get('ie_subcea')."
//                      and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
//                      and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']."
//                      and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
//                      and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3))
//                      and e.superior_turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."
//                      order by d.codigo";
        
        $query = "  
                    select c.id as saeid, d.acreditacion
                        from superior_facultad_area_tipo a
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                        where
                         a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']." and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
                        order by d.codigo
                   ";
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $periodos = $sieat->fetchAll();

//        dump($query);
//        die;
        
        
        if ($po){
            $exist = true;
        }
        else{
            $exist = false;        
        }
        
        if ($periodos){
            $existper = true;            
        }
        else{
            $existper = false;        
        }
        
        //SUMA LA CANTIDAD DE HORAS POR ACREDITACIÓN
        $query = "  
                    	select  sum(
                        CASE
                          WHEN aa.codigo = 1 THEN aa.horas_modulo
                            ELSE 0
                          END) AS totbas,
                        sum(  
                        CASE
                          WHEN aa.codigo = 2 THEN aa.horas_modulo
                            ELSE 0
                          END) AS totaux,
                        sum(  
                        CASE
                          WHEN aa.codigo = 3 THEN aa.horas_modulo
                            ELSE 0
                          END) AS totmed
                          from 
			 (select
			  k.id,d.codigo, k.horas_modulo                        
                          from superior_facultad_area_tipo a
                          inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                          inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                          inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                          inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                          inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                          inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                          inner join institucioneducativa_curso w on w.superior_institucioneducativa_periodo_id = g.id
                          inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id
                          inner join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id
                          where
                        f.gestion_tipo_id=".$this->session->get('ie_gestion')." 
                        and f.institucioneducativa_id=".$this->session->get('ie_id')." 
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." 
                        and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
                        and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']." 
                        and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']." 
                        and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3)) 
                        and w.turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."
                          group by k.id,d.codigo, k.horas_modulo) aa";
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $tothoras = $sieat->fetchAll();
//        dump($query);
//        die;
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:seemodulos.html.twig', array(
            'exist' => $exist,
            'existper' => $existper,
            'siea' => $aInfoUeducativa['ueducativaInfoId']['siea'],
            'modulos' => $po,
            'tothoras' => $tothoras,
            'periodos' => $periodos,            
            'area' => $aInfoUeducativa['ueducativaInfo']['facultad_area'],
            'especialidad' => $aInfoUeducativa['ueducativaInfo']['especialidad'],            
            'turno' => $aInfoUeducativa['ueducativaInfo']['turno_superior'],            
            'infoUe' => $infoUe,
        ));        
    }
    
    public function seemodulosverAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);        
//        dump($aInfoUeducativa);
//        die;      
        $em = $this->getDoctrine()->getManager();  
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = "  select distinct e.id as sieaid, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipoid, x.id as sptid, x.periodo_superior, a.codigo as nivel_id,b.codigo as ciclo_id,d.codigo as grado_id
                    ,d.acreditacion as acreditacion,l.id as smtid, l.modulo, h.id as area_saberes_id, h.area_superior, k.id as mod_per_hor_id, k.horas_modulo
                     from superior_facultad_area_tipo a  
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id 
                                                    inner join institucioneducativa_curso w on w.superior_institucioneducativa_periodo_id = g.id 
                                                        inner join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id
                                                            inner join superior_area_saberes_tipo h on l.superior_area_saberes_tipo_id = h.id
                                                                inner join superior_periodo_tipo x on g.superior_periodo_tipo_id = x.id
                    where f.gestion_tipo_id=".$this->session->get('ie_gestion')."
                      and f.institucioneducativa_id=".$this->session->get('ie_id')."
                      and f.sucursal_tipo_id=".$this->session->get('ie_subcea')."
                      and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
                      and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']."
                      and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
                      and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3))
                      and w.turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."
                    order by d.codigo, l.modulo";
//        print_r($query);
//        die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //$periodos = array();
//        $ultimoperiodo = array();
        
        //$sieaid = $po[0]['sieaid'];
        //$aInfoUeducativa['ueducativaInfoId']['siea']
        //AVERIGUA CANTIDAD DE SEMESTRES DE LA CARRERRA
//        $query = "  select distinct e.id as sieaid, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipoid, x.id as sptid, x.periodo_superior, a.codigo as nivel_id,
//                        b.codigo as ciclo_id,d.codigo as grado_id ,d.acreditacion as acreditacion
//
//                        from superior_facultad_area_tipo a 
//                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
//                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
//                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
//                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
//                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
//                        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
//                        inner join superior_periodo_tipo x on g.superior_periodo_tipo_id = x.id 
//
//                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')."
//                      and f.institucioneducativa_id=".$this->session->get('ie_id')."
//                      and f.sucursal_tipo_id=".$this->session->get('ie_subcea')."
//                      and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
//                      and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']."
//                      and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
//                      and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3))
//                      and e.superior_turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."
//                      order by d.codigo";
        
        $query = "  
                    select c.id as saeid, d.acreditacion
                        from superior_facultad_area_tipo a
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                        where
                         a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']." and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']."
                        order by d.codigo
                   ";
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $periodos = $sieat->fetchAll();

//        dump($periodos);
//        die;
        
        
        if ($po){
            $exist = true;
        }
        else{
            $exist = false;        
        }
        
        if ($periodos){
            $existper = true;            
        }
        else{
            $existper = false;        
        }
        
        //SUMA LA CANTIDAD DE HORAS POR ACREDITACIÓN
        $query = "  select
                        sum(
                        CASE
                          WHEN d.codigo = 1 THEN k.horas_modulo
                            ELSE 0
                          END) AS totbas,
                        sum(  
                        CASE
                          WHEN d.codigo = 2 THEN k.horas_modulo
                            ELSE 0
                          END) AS totaux,
                        sum(  
                        CASE
                          WHEN d.codigo = 3 THEN k.horas_modulo
                            ELSE 0
                          END) AS totmed
                        from superior_facultad_area_tipo a 
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                        inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id 
                        inner join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id 

                        where
                        f.gestion_tipo_id=".$this->session->get('ie_gestion')." 
                        and f.institucioneducativa_id=".$this->session->get('ie_id')." 
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." 
                        and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
                        and a.codigo = ".$aInfoUeducativa['ueducativaInfoId']['facultad_area_cod']." 
                        and b.codigo = ".$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']." 
                        and ((d.codigo = 1) or (d.codigo = 2) or (d.codigo = 3)) 
                        and e.superior_turno_tipo_id = ".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id'];
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $tothoras = $sieat->fetchAll();
//        dump($tothoras);
//        die;
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:modulos.html.twig', array(
            'exist' => $exist,
            'existper' => $existper,
            'siea' => $aInfoUeducativa['ueducativaInfoId']['siea'],
            'modulos' => $po,
            'tothoras' => $tothoras,
            'periodos' => $periodos,            
            'area' => $aInfoUeducativa['ueducativaInfo']['facultad_area'],
            'especialidad' => $aInfoUeducativa['ueducativaInfo']['especialidad'],            
            'turno' => $aInfoUeducativa['ueducativaInfo']['turno_superior'],            
            'infoUe' => $infoUe,
        ));      
    }
    
    public function changeareaAction(Request $request) {        
        $var = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        try {
            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($var['pk']);
            $smp->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find($var['value']));
            $em->persist($smp);
            $em->flush();            
            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function changehorasAction(Request $request) {        
        $var = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse(); 
        try {
            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($var['pk']);
            $smp->setHorasModulo($var['value']);
            $em->persist($smp);
            $em->flush();            
            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function changesemestreAction(Request $request) {
        $var = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse(); 
        try {
            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($var['pk']);
            $smp->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($var['value']));
            $em->persist($smp);
            $em->flush();            
            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function changeduracionAction(Request $request) {        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $var = $request->request->all();
        $query = "  
                    select b.*
                    from superior_institucioneducativa_acreditacion a 
                            inner join superior_institucioneducativa_periodo b on b.superior_institucioneducativa_acreditacion_id = a.id
                                inner join superior_periodo_tipo c on b.superior_periodo_tipo_id = c.id
                    where a.id = '".$var['pk']."'";
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $duracion = $sieat->fetchAll();
        $duraacioncount = count($duracion) + 1;
        $semes = intval($var['value']);
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse(); 
        try {
            if (count($duracion) == $semes){
                die('es igual no se hace nada');
            }
            if ($duraacioncount < $semes){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                for ($i = $duraacioncount; $i < $semes; $i++) {
//                    print_r($duraacioncount.' - '.$semes);
//                    die;
                    $siep = new SuperiorInstitucioneducativaPeriodo();
                    $siep->setSuperiorInstitucioneducativaAcreditacion($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($var['pk']));
                    $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find($i+1));
                    $siep->setHorasPeriodo('0');
                    $em->persist($siep);
                    $em->flush();
                }
                $em->getConnection()->commit();
                return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
            }
            if (count($duracion) >$semes){
                die('eliminar periodos');
            }        
//        dump($duracion);
//        print_r($var['value']);
//        die;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function changeduracionauxAction() {        
        print_r('cambio de duración');
        die;
    }
    
    public function changeduracionmedAction() {        
        print_r('cambio de duración');
        die;
    }
    
    public function changeduracionbasAction() {        
        print_r('cambio de duración');
        die;
    }
    
    public function changeduracionnewAction(Request $request) {        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $var = $request->request->all();

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse(); 
        try {            
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
            for ($i = 2; $i < $var['value']; $i++) {
                $siep = new SuperiorInstitucioneducativaPeriodo();
                $siep->setSuperiorInstitucioneducativaAcreditacion($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($var['pk']));
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find($i));
                $siep->setHorasPeriodo('0');
                $em->persist($siep);
                $em->flush();
            }
            $em->getConnection()->commit();
//            return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));                
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function addmoduloAction(Request $request) {
        $var = $request->request->all();
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
//        dump($aInfoUeducativa);
//        die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        $response = new JsonResponse(); 
        try {
            $query = "      select a.id as sieaid, c.id as siepid
                              from superior_institucioneducativa_acreditacion a
                                    inner join superior_institucioneducativa_periodo c on c.superior_institucioneducativa_acreditacion_id = a.id
					inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
                                            inner join institucioneducativa_curso d on d.superior_institucioneducativa_periodo_id = c.id
                            where a.institucioneducativa_id = '".$this->session->get('ie_id')."'
                            and a.gestion_tipo_id = '".$this->session->get('ie_gestion')."'
                            and a.institucioneducativa_sucursal_id = '".$this->session->get('ie_suc_id')."'
                            and d.turno_tipo_id = '".$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']."'
                            and b.periodo_tipo_id = '".$this->session->get('ie_per_cod')."'
                            and a.acreditacion_especialidad_id = '".$var['acreditacionid']."'
                    ";
            $sieav = $db->prepare($query);
            $params = array();
            $sieav->execute($params);
            $saerow = $sieav->fetchAll();
//            dump(count($saerow));
//            die;
            if (count($saerow) == 0){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();                
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->find($var['acreditacionid']));////
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find($aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']));
                $em->persist($siea);
                $em->flush();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                $siep = new SuperiorInstitucioneducativaPeriodo();                
                $siep->setSuperiorInstitucioneducativaAcreditacion($siea);
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find('2'));              
                $em->persist($siep);
                $em->flush();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();
                $iec = new InstitucioneducativaCurso();
                $iec->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find('4'));
                $iec->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find('0'));
                $iec->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find('99'));
                $iec->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find('1'));
                $iec->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $iec->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find('15'));
                $iec->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find('0'));
                $iec->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']));
                $iec->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $iec->setSuperiorInstitucioneducativaPeriodo($siep);
                $em->persist($iec);
                $em->flush();
                
                $smt = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findBy(array('modulo'=>$var['modulo'],'superiorAreaSaberesTipo' => $var['area']));
                if (count($smt) == 1){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($smt[0]);
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo($var['horas']);
                    $em->persist($smp);
                    $em->flush();
                }
                else{
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();            
                    $smtn = new SuperiorModuloTipo();
                    $smtn->setModulo($var['modulo']);
                    $smtn->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find($var['area']));
                    $em->persist($smtn);
                    $em->flush();

                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($smtn);
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo($var['horas']);
                    $em->persist($smp);
                    $em->flush();
                }              
            }
            else{
                $smt = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findBy(array('modulo'=>$var['modulo'],'superiorAreaSaberesTipo' => $var['area']));
//                dump(count($smt));
//                die;
                if (count($smt) == 1){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($smt[0]);
                    $smp->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($saerow['0']['siepid']));
                    $smp->setHorasModulo($var['horas']);
                    $em->persist($smp);
                    $em->flush();
                }
                else{
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();            
                    $smtn = new SuperiorModuloTipo();
                    $smtn->setModulo($var['modulo']);
                    $smtn->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find($var['area']));
                    $em->persist($smtn);
                    $em->flush();

                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($smtn);
                    $smp->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($saerow['0']['siepid']));
                    $smp->setHorasModulo($var['horas']);
                    $em->persist($smp);
                    $em->flush();
                }
            }
           $em->getConnection()->commit();
//           return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!')); 
           return $this->redirectToRoute('herramienta_alter_malla_tecnica_modulos_ver', ['request' => $request], 307);
            
        } catch (Exception $ex) {
            $em->getConnection()->rollback(); 
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }
    
    public function modulodeleteAction(Request $request, $smpid) {
        //$var = $request->request->all();
//        dump($smpid);
//        die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse(); 
        try {
            $ieco = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBySuperiorModuloPeriodo($smpid);
            
            if ($ieco){                
                $this->session->getFlashBag()->add('messagetec', '¡No se puede eliminar el módulo por que se encuentra asignado a algun curso!');                
                return $this->redirectToRoute('herramienta_alter_malla_tecnica_modulos_ver', ['request' => $request], 307);
            }
            else{
                $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid);
                $em->remove($smp);
                $em->flush();

                $em->getConnection()->commit();

                //return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
                return $this->redirectToRoute('herramienta_alter_malla_tecnica_modulos_ver', ['request' => $request], 307);
            }    
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }    
    
    public function nuevaacreditacionAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:nuevaacreditacion.html.twig');        
    }
    
    public function newacreditacionAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $var = $request->request->all();
        $response = new JsonResponse();
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $em->getConnection()->beginTransaction();
        try {
            $query = "      select e.id as siea
                               from superior_facultad_area_tipo a
                                  inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                      inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                          inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                              inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                                  inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                      inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                              inner join superior_turno_tipo q on e.superior_turno_tipo_id = q.id
                                                                  inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
                              where f.gestion_tipo_id='".$this->session->get('ie_gestion')."' and f.institucioneducativa_id='".$this->session->get('ie_id')."'
                              and f.sucursal_tipo_id='".$this->session->get('ie_subcea')."' and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
                              and h.turno_tipo_id = '".$var['turnocod']."'
                              and a.codigo = '".$var['areacod']."' and b.id = '".$var['nivelcod']."'
                              order by q.id, a.codigo, b.codigo, d.codigo
                    ";
            $sieav = $db->prepare($query);
            $params = array();
            $sieav->execute($params);
            $saerow = $sieav->fetchAll();
//            dump($saerow);
//            die;
            if (count($saerow) == 0){
                $query = "
                            select c.id as saeid, d.*
                              from superior_facultad_area_tipo a
                                  inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                      inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                          inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                              where
                               a.codigo = ".$var['areacod']." and b.id = ".$var['nivelcod']."
                              order by d.codigo";
                $sae= $db->prepare($query);
                $params = array();
                $sae->execute($params);
                $saeid = $sae->fetchAll();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();                
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->find($saeid['0']['saeid']));
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find($var['turnocod']));
                $em->persist($siea);
                $em->flush();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                $siep = new SuperiorInstitucioneducativaPeriodo();                
                $siep->setSuperiorInstitucioneducativaAcreditacion($siea);
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find('2'));              
                $em->persist($siep);
                $em->flush();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();
                $iec = new InstitucioneducativaCurso();
                $iec->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find('4'));
                $iec->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find('0'));
                $iec->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find('99'));
                $iec->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find('1'));
                $iec->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $iec->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find('15'));
                $iec->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find('0'));
                $iec->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($var['turnocod']));
                $iec->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $iec->setSuperiorInstitucioneducativaPeriodo($siep);
                $em->persist($iec);
                $em->flush();
                
                $em->getConnection()->commit();
                return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));
            }
            else{
                return $response->setData(array('mensaje'=>'¡Ya existe la especialidad!'));
            }
            
        } catch (Exception $ex) {                       
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'.$ex));
        }        
    } 
    
}
