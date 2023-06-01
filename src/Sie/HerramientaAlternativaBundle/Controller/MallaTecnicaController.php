<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

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
        $newobjUeducativa = [];
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



            //nuevo modulo de mallas
            $newdb = $em->getConnection();
            $newquery = "  select e.id as siea, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipo
--, q.id as superior_turno_tipo_id, q.turno_superior
, a.codigo as facultad_area_cod, a.facultad_area as facultad_area, b.id as especialidad_id, b.codigo as ciclo_id, b.especialidad as especialidad, d.codigo as acreditacion_cod, d.acreditacion as acreditacion
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id                                                                                                          
                                               --     inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
                                                     --   inner join superior_turno_tipo q on h.turno_tipo_id = q.id
                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                        and a.codigo in ('18','19','20','21','22','23','24','25')
                        order by a.codigo, b.codigo, d.codigo ";
            $newstmt = $newdb->prepare($newquery);
            //        dump($query);
            //        die;
            $params = array();
            $newstmt->execute($params);
            $newpo = $newstmt->fetchAll();
        //    dump($newpo);die;
            if (!$newpo) {
                $message = 'No existe información de Mallas Técnicas para la gestión seleccionada.';
                $this->addFlash('warninresult', $message);
                $newexist = false;
            }
            $newobjUeducativa = $newpo;
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

        $newexist = true;
        $newaInfoUnidadEductiva = array();
        if ($newobjUeducativa) {
            foreach ($newobjUeducativa as $newuEducativa) {
                $newsinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('especialidad' => $newuEducativa['especialidad'], 'facultad_area' => $newuEducativa['facultad_area']),
                    'ueducativaInfoId' => array('siea' => $newuEducativa['siea'], 'facultad_area_cod' => $newuEducativa['facultad_area_cod'], 'especialidad_cod' => $newuEducativa['ciclo_id'],'especialidad_id' => $newuEducativa['especialidad_id'])
                ));
                $newaInfoUnidadEductiva[$newuEducativa['facultad_area']][$newuEducativa['especialidad']] = array('infoMallTec' => $newsinfoUeducativa);
            }
          //  dump($newaInfoUnidadEductiva);die;
        } else {
            $newmessage = 'No existe información de Mallas Técnicas para la gestión seleccionada.';
            $this->addFlash('warninresult', $newmessage);
            $newexist = false;
        }


        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:index.html.twig', array(            
            'exist' => $exist,
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
            'newexist' => $newexist,
            'newaInfoUnidadEductiva' => $newaInfoUnidadEductiva,
        ));
    }

    public function mallaseemodulosAction(Request $request) {

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
					
					where sat.id in (1,20,32) 
					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc

    ";//
        $mallanivel = $db->prepare($query);
        $params = array();
        $mallanivel->execute($params);
        $mallaniv = $mallanivel->fetchAll();
        // dump($mallaniv);die;
        $db = $em->getConnection();
        $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
					where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
) dat
group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip
        
    ";
        $especialidadnivel = $db->prepare($query);
        $params = array();
        $especialidadnivel->execute($params);
        $po = $especialidadnivel->fetchAll();
     //  dump($po);die();

        $db = $em->getConnection();
        $query = " select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad 
                    from (
						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion, case when sest.es_oficial = true then 1 else 0 end sw_esoficial
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
				    where sat.id in (1,20,32) 
					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
	from superior_acreditacion_especialidad sae
			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
											 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
                    and sest.es_vigente = true
    order by smt.codigo
	) dat
	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
) as v on v.idacr = nivel.idacreditacion
    ";
        $final = $db->prepare($query);
        $params = array();
        $final->execute($params);
        $mallafinal = $final->fetchAll();
        // dump($mallafinal);die;

//        dump($aInfoUeducativa['ueducativaInfoId']['especialidad_id']);
//        dump($this->session->get('ie_gestion'));
//        dump($this->session->get('ie_id'));
//        dump($this->session->get('ie_per_cod'));
//        dump($this->session->get('ie_subcea'));
//        die();

        if ($po||$mallafinal){
            $exist = true;
        }
        else{
            $exist = false;
        }

        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:seemodulosnew.html.twig', array(
            'exist' => $exist,
            'malla' => $po,
            'mallaniv' => $mallaniv,
            'mallafin' => $mallafinal,
            'area' => $aInfoUeducativa['ueducativaInfo']['facultad_area'],
            'especialidad' => $aInfoUeducativa['ueducativaInfo']['especialidad'],
            // 'turno' => $aInfoUeducativa['ueducativaInfo']['turno_superior'],
            'infoUe' => $infoUe,
        ));
    }


    public function seemodulosAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);        
//
//
       // dump($aInfoUeducativa);
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
                       and l.esvigente =false
                    order by d.codigo, l.modulo";
//        print_r($query);
//        die;
   //      dump($aInfoUeducativa['ueducativaInfoId']['facultad_area_cod'],$aInfoUeducativa['ueducativaInfoId']['especialidad_cod'],$aInfoUeducativa['ueducativaInfoId']['superior_turno_tipo_id']);die;
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

      //  dump($periodos);die;
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
                         and l.esvigente =false
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
                      and l.esvigente =false
                    order by d.codigo, l.modulo";
//        print_r($query);
//        die;
       // dump($aInfoUeducativa['ueducativaInfoId']['facultad_area_cod'],$aInfoUeducativa['ueducativaInfoId']['especialidad_cod']);die;
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

        //dump($sieat);die;
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
                        and l.esvigente =false
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
                $this->session->getFlashBag()->add('messagetec', 'No se puede eliminar el módulo por que se encuentra asignado a algun curso.');                
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

    /******habilita nueva malla*********/
    public function mallanuevaacreditacionAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        return $this->redirectToRoute('herramienta_alter_malla_tecnica_index');///temporal
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $area= $em->getRepository('SieAppWebBundle:SuperiorFacultadAreaTipo')->findAll();
        $areaArray = array();
        foreach($area as $value){
            if( ($value->getCodigo()==18)||($value->getCodigo()==19)||($value->getCodigo()==20)||($value->getCodigo()==21)||($value->getCodigo()==22)||($value->getCodigo()==23)||($value->getCodigo()==24)||($value->getCodigo()==25))
            {
                $areaArray[$value->getId()] = $value->getFacultadArea();
            }
        }
        //     dump($areaArray);die;
//        $query = $em->getConnection()->prepare('
//               	select seti.id, seti.especialidad
//            from superior_especialidad_tipo seti
//			inner join superior_facultad_area_tipo sfat on sfat.id = seti.superior_facultad_area_tipo_id
//				where sfat.codigo in (18,19,20,21,22,23,24,25)
//
//        ');
//        $query->execute();
//        $espobj= $query->fetchAll();
//
//        $espArray = array();
//        foreach ($espobj as $value) {
//            $espArray[$value['id']] =$value['especialidad'];
//        }

        $espArray = array();
          //dump($espArray);die;
        $nivelesArray = array();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_malla_acreditacion_alternativa_new'))
            ->add('area', 'choice', array('required' => true, 'choices' => $areaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarEspecialidades(this.value)')))
            ->add('especialidad', 'choice', array('required' => true, 'choices' => $espArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarNivelesAlt(this.value)')))
            //->add('especialidad', 'choice', array('required' => true, 'choices' => $especialidadesArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('nivel', 'choice', array('required' => true, 'choices' => $nivelesArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true)))
            ->getForm();
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:nuevacreditacion.html.twig', array(
            //'cursos'=>$cursosCortos,
            'form' => $form->createView()
        ));

    }

    public function mallanewacreditacionAction(Request $request) {
//dump($request);die;
        $form =  $request->get('form');
       // dump($form);die;
        $idcea=$this->session->get('ie_id');
        $idespec=$form['especialidad'];
        $idnivel = $form['nivel'];
        $idarea = $form['area'];
        //   dump($idespec);dump($idnivel);die;
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
        //comprueba si existe la expecialidad y el nivel en la ie
        try {
            $query = "   select e.id as siea
                               from superior_facultad_area_tipo a
                                  inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                      inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                          inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                              inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                                  inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                      inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                            --  inner join superior_turno_tipo q on e.superior_turno_tipo_id = q.id
                                                               --   inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
                              where f.gestion_tipo_id=".$this->session->get('ie_gestion')." 
                               and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                             and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." 
                              and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                              and a.id =".$idarea."
							  and b.id = ".$idespec."
						      and d.id =".$idnivel."
                              order by a.codigo, b.codigo, d.codigo
                                ";
            $sieav = $db->prepare($query);
            $params = array();
            $sieav->execute($params);
            $saerow = $sieav->fetchAll();
         //   dump($saerow);die;
//            die;


            if (count($saerow) == 0){
                //Comprueba que exista dicha combinacion con especialidad
                $query = "
                            select c.id as saeid, d.*
                              from superior_facultad_area_tipo a
                                  inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                      inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                          inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                              where 
                               b.id=".$idespec."
	  						 and d.id=".$idnivel."
                                                            
                              order by d.codigo";

                $sae= $db->prepare($query);
                $params = array();
                $sae->execute($params);
                $saeid = $sae->fetchAll();
//dump($saeid);die;

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findOneBy(array('id' =>$saeid['0']['saeid'] )));
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find(10));
                $em->persist($siea);
                $em->flush();
                //   dump($siea);die;

                // dump($em);die;
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                $siep = new SuperiorInstitucioneducativaPeriodo();
                $siep->setSuperiorInstitucioneducativaAcreditacion($siea);
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find('2'));
                $em->persist($siep);
                $em->flush();

//
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
                return $this->redirect($this->generateUrl('herramienta_alter_malla_tecnica_index'));
                //  return $this->render('SiePermanenteBundle:MallaTecnica:index.html.twig');
            }
            else{
                //return $response->setData(array('mensaje'=>'¡Ya existe la especialidad!'));
                // return $this->render('SiePermanenteBundle:MallaTecnica:index.html.twig');
                $this->get('session')->getFlashBag()->add('newOk', '¡Ya existe la especialidad!');
                return $this->redirect($this->generateUrl('herramienta_alter_malla_tecnica_index'));
            }

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje'=>'Proceso detenido.'.$ex));
        }
    }

    public function listarEspecialidadesAction($idarea) {

        dump($idarea);
        // die;

        try {
            $em = $this->getDoctrine()->getManager();

        //     $query = $em->getConnection()->prepare('
        //        	select seti.id, seti.especialidad
        //     from superior_especialidad_tipo seti
		// 	inner join superior_facultad_area_tipo sfat on sfat.id = seti.superior_facultad_area_tipo_id
		// 		where sfat.id =:idarea

        // ');
        $query = $em->getConnection()->prepare('
               	select seti.id, 
                case when seti.es_oficial = true then \'* \'||seti.especialidad else seti.especialidad end as especialidad
            from superior_especialidad_tipo seti
			inner join superior_facultad_area_tipo sfat on sfat.id = seti.superior_facultad_area_tipo_id
				where sfat.id =:idarea
                and seti.es_vigente = true

        ');
            $query->bindValue(':idarea', $idarea);
            $query->execute();
            $espobj= $query->fetchAll();

            $espArray = array();
            foreach ($espobj as $value) {
                $espArray[$value['id']] =$value['especialidad'];
            }

            //dump($areaobj);dump($areaobjArray);die;
            $response = new JsonResponse();
            return $response->setData(array('listarespecialidades' => $espArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function listarNivelesAltAction($idesp) {

        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->getConnection()->prepare('
              
                select * from superior_acreditacion_tipo where id in (1,20,32)
        ');
//            $query->bindValue(':idesp', $idesp);
//            $query->bindValue(':gestion', $idesp);
//            $query->bindValue(':sie', $idesp);
            $query->execute();
            $nivelobj= $query->fetchAll();
//dump($nivelobj);die;
            $nivelobjArray = array();
            foreach ($nivelobj as $value) {
                $nivelobjArray[$value['id']] =$value['acreditacion'];
            }

            // dump($nivelobjArray);die;
            $response = new JsonResponse();
            return $response->setData(array('listarniveles' => $nivelobjArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function showModuloNuevoAction(Request $request)
    {
        //dump($request);die;
        $idsip = $request->get('idsip');
        $idesp = $request->get('idesp');
        $totalhoras = $request->get('totalhoras');
        //$horas= [80,100,120];
        $horas= [100];
       // $horasmodulo = $request->get('horas');
        $idacreditacion =$request->get('idacred');
        //  dump($request);die;
        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_add_areatem'))

            ->add('modulo', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
            ->add('horas', 'choice', array('choices'=> $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
           // ->add('seccioniiProvincia', 'choice', array('choices' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'guardarModulo();')))
            ->add('idsip', 'hidden', array('data' => $idsip))
            ->add('idesp', 'hidden', array('data' => $idesp))
            ->add('totalhoras', 'hidden', array('data' => $totalhoras))
            ->getForm();
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:nuevomodulo.html.twig', array(
            'form' => $form->createView(),
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion
           // 'horasmodulo'=>$horasmodulo
            // 'idsip'=>$idsip
        ));
    }

    public function showModuloEditAction(Request $request)
    {  $em = $this->getDoctrine()->getManager();
        // dump($request);die;
        $horas= [80,100,120];
        $idmodulo = $request->get('idmodulo');
        $idspm = $request->get('idspm');
        $modulo = $request->get('modulo');
        $horasmodulo = $request->get('horas');
        $idesp = $request->get('idesp');
        $totalhoras = $request->get('totalhoras');
        $idacreditacion =$request->get('idacred');
       for($i=0;$i<=2;$i++)
       {
           if($horas[$i]==$horasmodulo){
               $horasid = $i;
           }
       }

        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
            ->add('modulo', 'text', array('required' => true,'data' => $modulo, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
            ->add('horas', 'choice', array('choices'=> $horas,'data' => $horasid, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'updateModulo();')))
            ->add('idmodulo', 'hidden', array('data' => $idmodulo))
            ->add('idspm', 'hidden', array('data' => $idspm))
            ->add('idesp', 'hidden', array('data' => $idesp))
            ->add('totalhoras', 'hidden', array('data' => $totalhoras))
            ->getForm();
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:editarmodulo.html.twig', array(

            'form' => $form->createView(),
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion,
            'horasmodulo'=>$horasmodulo

        ));
    }

    public function updateModuloNuevoAction(Request $request)
    {

        $form = $request->get('form');
       // dump($form);die;
        $modulo = strtoupper($form['modulo']);
        $idspm = $form['idspm'];
        $idesp = $form['idesp'];

        $horas= [80,100,120];
        $horasid = ($form['horas']);
        $horasmodulo = $horas[$horasid];



        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findOneBy(array('id' => $form['idmodulo']));
            $supmodtipo -> setModulo($modulo);
            $em->persist($supmodtipo);
            $em->flush($supmodtipo);

            //  dump($smtipo);die;
            $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' => $form['idspm']));
            $supmodper->setHorasModulo($horasmodulo);
            $em->persist($supmodper);
            $em->flush($supmodper);

            //  dump($smperiodo);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            $db = $em->getConnection();
            $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
					where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
) dat
group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

        
    ";
//        print_r($query);
//        die;
            $especialidadnivel = $db->prepare($query);
            $params = array();
            $especialidadnivel->execute($params);
            $po = $especialidadnivel->fetchAll();

            $db = $em->getConnection();
            $query = "  select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
				    where sat.id in (1,20,32) 
					and sest.id='".$idesp."'
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
	from superior_acreditacion_especialidad sae
			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
											 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
	) dat
	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
) as v on v.idacr = nivel.idacreditacion
        
    ";
            $final = $db->prepare($query);
            $params = array();
            $final->execute($params);
            $mallafinal = $final->fetchAll();

            // dump($po);die();
            if ($po){
                $exist = true;
            }
            else{
                $exist = false;
            }

            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist' => $exist,
                'malla' => $po,
                'mallafin' => $mallafinal,
            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }

    }

    public function showModuloDeleteAction(Request $request)
    {  $em = $this->getDoctrine()->getManager();
       // dump($request);die;
        $idmodulo = $request->get('idmodulo');
        $idspm = $request->get('idspm');
        $modulo = $request->get('modulo');
        $horas = $request->get('horas');
        $idesp = $request->get('idesp');


        $em->getConnection()->beginTransaction();
     //   dump($idspm);die;
        try{
            $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' => $idspm));
            $em->remove($supmodper);
            $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('superiorModuloTipo' => $idmodulo));
                if(count($supmodtipo)<=0)
                {
                    $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findOneBy(array('id' => $idmodulo));
                    $em->remove($supmodtipo);
                }

            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron eliminados correctamente.');

            $db = $em->getConnection();

            $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
					
					where sat.id in (1,20,32) 
					and sest.id='".$idesp."'
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc

    ";

            $mallanivel = $db->prepare($query);
            $params = array();
            $mallanivel->execute($params);
            $mallaniv = $mallanivel->fetchAll();


            $db = $em->getConnection();
            $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
					where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
) dat
group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

        
    ";

            $especialidadnivel = $db->prepare($query);
            $params = array();
            $especialidadnivel->execute($params);
            $po = $especialidadnivel->fetchAll();

            if ($po){
                $exist = true;
            }
            else{
                $exist = false;
            }
            $db = $em->getConnection();
            $query = "  select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
				    where sat.id in (1,20,32) 
					and sest.id='".$idesp."'
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
	from superior_acreditacion_especialidad sae
			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
											 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
	) dat
	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
) as v on v.idacr = nivel.idacreditacion
        
    ";
            $final = $db->prepare($query);
            $params = array();
            $final->execute($params);
            $mallafinal = $final->fetchAll();

            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist' => $exist,
                'malla' => $po,
                'mallafin' => $mallafinal,
                'mallaniv' => $mallaniv,

            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(Exception $ex)
        {

            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron eliminados, Asegurese de que el modulo no se esta utilizando.');
            return $this->redirect($this->generateUrl('herramienta_alter_malla_tecnica_index'));
        }

    }

    public function createModuloNuevoAction(Request $request)
    {
        $form = $request->get('form');
        // $horas= [80,100,120];
        $horas= [100];
        //dump($form);die;
        //  $form = $request->get('form');
        // dump($form);die;
        //  $modulo = strtoupper($form['modulo']);     // dump($tecbas); dump($tecaux); dump($tecmed);die;
          $horasid = ($form['horas']);

         $horasmodulo = $horas[$horasid];
       // dump($horasmodulo);die;
        $modulo = strtoupper($form['modulo']);
        $idsip = $form['idsip'];
        $idesp = $form['idesp'];
       // dump($idesp);die;
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();
            $smtipo = new SuperiorModuloTipo();
            $smtipo -> setModulo($modulo);
            $smtipo -> setEsvigente(true);
            $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
            $em->persist($smtipo);
            $em->flush($smtipo);

            //  dump($smtipo);die;



            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
            $smperiodo = new SuperiorModuloPeriodo();
            $smperiodo ->setSuperiorModuloTipo($smtipo);
            $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($form['idsip']));
            $smperiodo ->setHorasModulo($horasmodulo);
            $em->persist($smperiodo);
            $em->flush($smperiodo);

            //  dump($smperiodo);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            $db = $em->getConnection();
            $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
					
					where sat.id in (1,20,32) 
					and sest.id='".$idesp."'
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc

    ";
//        print_r($query);
//        die;
            $mallanivel = $db->prepare($query);
            $params = array();
            $mallanivel->execute($params);
            $mallaniv = $mallanivel->fetchAll();
            //dump($mallaniv);die;

            $db = $em->getConnection();
            $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
					where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
) dat
group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

        
    ";

            $especialidadnivel = $db->prepare($query);
            $params = array();
            $especialidadnivel->execute($params);
            $po = $especialidadnivel->fetchAll();

            if ($po){
                $exist = true;
            }
            else{
                $exist = false;
            }
            $db = $em->getConnection();
            $query = " select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
				    where sat.id in (1,20,32) 
					and sest.id='".$idesp."'
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
	from superior_acreditacion_especialidad sae
			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
											 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
						where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id='".$idesp."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
	) dat
	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
) as v on v.idacr = nivel.idacreditacion
        
    ";
            $final = $db->prepare($query);
            $params = array();
            $final->execute($params);
            $mallafinal = $final->fetchAll();


            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist' => $exist,
                'mallaniv' => $mallaniv,
                'mallafin' => $mallafinal,
                'malla' => $po

            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }
//dump($especialidad);die;
        // return $this->render('SiePermanenteBundle:CursoPermanente:nuevaespecialidad.html.twig', array(

        // 'form' => $form->createView()

        //   ));
    }

}
