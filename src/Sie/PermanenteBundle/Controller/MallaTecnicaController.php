<?php

    namespace Sie\PermanenteBundle\Controller;

use Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad;
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
            $query = " select e.id as siea, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipo, a.codigo as facultad_area_cod, a.facultad_area as facultad_area, b.id as especialidad_id, b.codigo as ciclo_id, b.especialidad as especialidad, d.codigo as acreditacion_cod,d.id as acreditacion_id, d.acreditacion as acreditacion, c.id as espnivelid
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id                                                                                                            
                                                 -- inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id                   
                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                        and a.id=40 
                        order by a.codigo, b.codigo, d.codigo ";
            $stmt = $db->prepare($query); 
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
                    'ueducativaInfo' => array('especialidad' => $uEducativa['especialidad'], 'facultad_area' => $uEducativa['facultad_area']), //'turno_superior' => $uEducativa['turno_superior']),
                    'ueducativaInfoId' => array('especialidad_id' => $uEducativa['especialidad_id'])
                ));
                $aInfoUnidadEductiva[$uEducativa['facultad_area']][$uEducativa['especialidad']] = array('infoMallTec' => $sinfoUeducativa);
            }
         //   dump($aInfoUnidadEductiva);die;

        } else {
            $message = 'No existe información de Mallas Técnicas para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        
        return $this->render('SiePermanenteBundle:MallaTecnica:index.html.twig', array(            
            'exist' => $exist,
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
        ));
    }

    public function seemodulosAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe); //dump($aInfoUeducativa);        
        $em = $this->getDoctrine()->getManager();   
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "select sae.id, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip--, smp.id,smt.modulo --,sfat.facultad_area
                from superior_acreditacion_especialidad sae
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
                and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
				and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'";
        $mallanivel = $db->prepare($query);
        $params = array();
        $mallanivel->execute($params);
        $mallaniv = $mallanivel->fetchAll(); //dump($mallaniv);
        $db = $em->getConnection();            
        $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
        from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
        from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
		inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
		inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
		inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
		inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
		left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
		left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']." 
                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and smt.esvigente =true
                    ) dat
        group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip";
        $especialidadnivel = $db->prepare($query);
        $params = array();
        $especialidadnivel->execute($params);
        $po = $especialidadnivel->fetchAll();
        //dump($po);
        if ($po){
            $exist = true;
        }
        else{
            $exist = false;        
        }

        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
						, sia.id as idsia, sip.id as idsip
                from superior_acreditacion_especialidad sae
		        inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
				inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
				inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
				--	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
				--		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']." 
	                and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                )as nivel
            left join (
                select idsae,idacr
                --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
                , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                    from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                    from superior_acreditacion_especialidad sae
                    inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                    inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                    left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                    left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']." 
                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and smt.esvigente =true
                    ) dat
                    group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
                    )as v on v.idacr = nivel.idacreditacion";
        $final = $db->prepare($query); 
        $params = array();
        $final->execute($params);
        $mallafinal = $final->fetchAll();
               // dump($po); dump($mallaniv);dump($mallafinal);die;
        return $this->render('SiePermanenteBundle:MallaTecnica:seemodulosnew.html.twig', array(
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
    
    public function seemodulosverAction(Request $request) {
     //   dump($request);die;
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe); 
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
                                                    --inner join institucioneducativa_curso w on w.superior_institucioneducativa_periodo_id = g.id 
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
                    
                    order by d.codigo, l.modulo";

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
               
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
                        and e.superior_turno_tipo_id = 10";
        $sieat = $db->prepare($query);
        $params = array();
        $sieat->execute($params);
        $tothoras = $sieat->fetchAll();


        return $this->render('SiePermanenteBundle:MallaTecnica:modulos.html.twig', array(
            'exist' => $exist,
            'existper' => $existper,
            'siea' => $aInfoUeducativa['ueducativaInfoId']['siea'],
            'modulos' => $po,
            'tothoras' => $tothoras,
            'periodos' => $periodos,            
            'area' => $aInfoUeducativa['ueducativaInfo']['facultad_area'],
            'especialidad' => $aInfoUeducativa['ueducativaInfo']['especialidad'],            
        //    'turno' => $aInfoUeducativa['ueducativaInfo']['turno_superior'],
            'infoUe' => $infoUe,
        ));      
    }
  
    public function addmoduloAction(Request $request) {
        $var = $request->request->all();
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
       // dump($var);die;
        $modulotipo= $var['modulo'];
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
                            
                            and b.periodo_tipo_id = '".$this->session->get('ie_per_cod')."'
                            and a.acreditacion_especialidad_id = '".$var['acreditacionid']."'
                    ";
            $sieav = $db->prepare($query);
            $params = array();
            $sieav->execute($params);
       //     dump(($sieav));
            $saerow = $sieav->fetchAll();
    
            if (count($saerow) == 0){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();                
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->find($var['acreditacionid']));////
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find(10));
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
                $iec->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find('10'));
                $iec->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $iec->setSuperiorInstitucioneducativaPeriodo($siep);
                $em->persist($iec);
                $em->flush();
                
                $smt = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('1');

            }
            else{
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                $smt = new SuperiorModuloTipo();
                $smt->setModulo($modulotipo);
                $smt->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find('2'));
                $em->persist($smt);
                $em->flush();

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                $smp = new SuperiorModuloPeriodo();
                $smp->setSuperiorModuloTipo($smt);
                $smp->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($saerow['0']['siepid']));
                $smp->setHorasModulo($var['horas']);
                $em->persist($smp);
                $em->flush();
            //  dump($smp);die;

            }
           $em->getConnection()->commit();
            //           return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!')); 
           return $this->redirectToRoute('permanente_malla_tecnica_modulos_ver', ['request' => $request], 307);
            
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
                return $this->redirectToRoute('permanente_malla_tecnica_modulos_ver', ['request' => $request], 307);
            }
            else{
                $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid);
                $em->remove($smp);
                $em->flush();

                $em->getConnection()->commit();

                //return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.'));
                return $this->redirectToRoute('permanente_malla_tecnica_modulos_ver', ['request' => $request], 307);
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
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $programa= $em->getRepository('SieAppWebBundle:SuperiorFacultadAreaTipo')->findAll();
        $programaArray = array();
        foreach($programa as $value){
            if( ($value->getId()==40)||($value->getId()==41)||($value->getId()==42))
            {
                $programaArray[$value->getId()] = $value->getFacultadArea();
            }
        }
       

        $query = $em->getConnection()->prepare('
               	SELECT * from superior_especialidad_tipo where superior_facultad_area_tipo_id =40
        ');
        $query->execute();
        $espobj= $query->fetchAll();

        $espArray = array();
        foreach ($espobj as $value) {
            $espArray[$value['id']] =$value['especialidad'];
        }


        //   dump($programaArray);die;
        $nivelesArray = array();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('permanente_herramienta_acreditacion_new'))
           // ->add('programa', 'choice', array('required' => true, 'choices' => $programaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('especialidad', 'choice', array('required' => true, 'choices' => $espArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarNiveles(this.value)')))
            //->add('especialidad', 'choice', array('required' => true, 'choices' => $especialidadesArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('nivel', 'choice', array('required' => true, 'choices' => $nivelesArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:MallaTecnica:nuevacreditacion.html.twig', array(
            //'cursos'=>$cursosCortos,
            'form' => $form->createView()
        ));

    }
    
    public function newacreditacionAction(Request $request) {
//dump($request);die;
        $form =  $request->get('form');
        $idcea=$this->session->get('ie_id');
        $idespec=$form['especialidad'];
        $idnivel = $form['nivel'];
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
            $query = "     select e.id as siea, g.id as siepid, g.superior_periodo_tipo_id as superior_periodo_tipo, a.codigo as facultad_area_cod, a.facultad_area as facultad_area, b.id as especialidad_id, b.codigo as ciclo_id, b.especialidad as especialidad, d.codigo as acreditacion_cod, d.acreditacion as acreditacion
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id                                                                                                             
                                             --       inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id                   
                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                        and a.id =40 and b.id=".$idespec." and c.id =".$idnivel."
                        order by a.codigo, b.codigo, d.codigo 
                    ";
            $sieav = $db->prepare($query);
            $params = array();
            $sieav->execute($params);
            $saerow = $sieav->fetchAll();
   //         dump($saerow);die;
//            die;


            if (count($saerow) == 0){
                //Comprueba que exista dicha combinacion con especialidad

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findOneBy(array('id' =>$idnivel )));
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
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find('1'));
                $em->persist($siep);
                $em->flush();

//
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
                return $this->redirect($this->generateUrl('permanente_malla_tecnica_index'));
              //  return $this->render('SiePermanenteBundle:MallaTecnica:index.html.twig');
            }
            else{
                //return $response->setData(array('mensaje'=>'¡Ya existe la especialidad!'));
               // return $this->render('SiePermanenteBundle:MallaTecnica:index.html.twig');
                $this->get('session')->getFlashBag()->add('newOk', '¡Ya existe la especialidad!');
                return $this->redirect($this->generateUrl('permanente_malla_tecnica_index'));
            }
            
        } catch (Exception $ex) {                       
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'.$ex));
        }        
    }


    public function createEspecialidadAction(Request $request) {

    }

    public function shoModuloNuevoAction(Request $request)
    {//dump($request);die;

        $idsip = $request->get('idsip');
        $idesp = $request->get('idesp');
        $totalhoras = $request->get('totalhoras');
        $horas= [80,100,120];
        $idacreditacion =$request->get('idacred');
        $form = $this->createFormBuilder()
            ->add('modulo', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
            ->add('horas', 'choice', array('choices'=> $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'guardarModulo();')))
            ->add('idsip', 'hidden', array('data' => $idsip))
            ->add('idesp', 'hidden', array('data' => $idesp))
            ->add('totalhoras', 'hidden', array('data' => $totalhoras))
            ->getForm();

           // dump($totalhoras);die;
            return $this->render('SiePermanenteBundle:MallaTecnica:nuevomodulo.html.twig', array(
                'form' => $form->createView(),
                'totalhoras'=>$totalhoras,
                'idacreditacion'=>$idacreditacion
        ));
    }
    public function createModuloNuevoAction(Request $request){
      
        $form = $request->get('form');
        $horas= [80,100,120];
        $horasid = ($form['horas']);
        $horasmodulo = $horas[$horasid];
        $modulo = strtoupper($form['modulo']);
        $idsip = $form['idsip'];
        $idesp = $form['idesp'];
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        try{
            $em->getConnection()->beginTransaction();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();

            $query = $em->getConnection()->prepare("select max(id) as ultimo from superior_modulo_tipo");            
            $query->execute();
            $entity = $query->fetchAll();
            $nextid = $entity[0]['ultimo'] + 1;
           
            /*$smtipo = new SuperiorModuloTipo();
            $smtipo -> setId($nextid +1);
            $smtipo -> setModulo($modulo);
            $smtipo -> setEsvigente(true);
            $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
            $em->persist($smtipo);
            $em->flush($smtipo);*/
           

            $query ="INSERT INTO superior_modulo_tipo (id,modulo, superior_area_saberes_tipo_id, esvigente) VALUES (?,?,?,?);";                
            $stmt = $db->prepare($query);
            $params = array($nextid, $modulo,1,true);
            $stmt->execute($params);    
            //$em->getConnection()->lastInsertId() ;
            //dump($stmt);
            //dump($mt);
            //die;

            $query = $em->getConnection()->prepare("select max(id) as ultimo from superior_modulo_periodo");            
            $query->execute();
            $entity = $query->fetchAll();
            $nextid2 = $entity[0]['ultimo'] + 1;

            /*$smtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find(1);

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
            $smperiodo = new SuperiorModuloPeriodo();
            $smperiodo ->setSuperiorModuloTipo($smtipo);
            $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($form['idsip']));
            $smperiodo ->setHorasModulo($horasmodulo);
            $em->persist($smperiodo);
            $em->flush($smperiodo);*/

            $query ="INSERT INTO superior_modulo_periodo (id,superior_modulo_tipo_id, institucioneducativa_periodo_id, horas_modulo) VALUES (?,?,?,?);";                
            $stmt = $db->prepare($query);
            $params = array($nextid2, $nextid,$idsip,$horasmodulo);
            $stmt->execute($params); 

          //  dump($smperiodo);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            $db = $em->getConnection();
            $query = "select sae.id, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip--, smp.id,smt.modulo --,sfat.facultad_area
                        from superior_acreditacion_especialidad sae
                        inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                        inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                        inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                        inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                        inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                        --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                        --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                        where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$form['idesp']."
                        and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                        and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'";
            $mallanivel = $db->prepare($query);
            $params = array();
            $mallanivel->execute($params);
            $mallaniv = $mallanivel->fetchAll();
            $db = $em->getConnection();
            $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                      from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                        from superior_acreditacion_especialidad sae
		                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				        inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
						inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
						left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
						left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                        where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$form['idesp']."
                        and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                        and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                        and smt.esvigente =true) dat
                        group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip";
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
                        inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                        inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                        --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                        --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                        where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$form['idesp']." 
                        and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                        and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                        )as nivel
                        left join (
                        select idsae,idacr
                        --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
                        , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                            from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                            from superior_acreditacion_especialidad sae
                                    inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                                        inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                                            inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                                                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                                        inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                            left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                                                left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                                where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$form['idesp']."
                                                and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                                and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                                                and smt.esvigente =true) dat
                            group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
                            )as v on v.idacr = nivel.idacreditacion";
            $final = $db->prepare($query);
            $params = array();
            $final->execute($params);
            $mallafinal = $final->fetchAll();
            if ($po){
                $exist = true;
            }
            else{
                $exist = false;
            }
            
            return $this->render('SiePermanenteBundle:MallaTecnica:listModulos.html.twig', array(
                'exist' => $exist,
                'mallaniv' => $mallaniv,
                'mallafin' => $mallafinal,
                'malla' => $po                
            ));
            //return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }
    }


    public function listarNivelesAction($idesp) {

       //  dump($idesp);die;

        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->getConnection()->prepare('
               	select sae.id, sest.especialidad, sat.acreditacion,sfat.facultad_area

from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id=:idesp
        ');
            $query->bindValue(':idesp', $idesp);
            $query->execute();
            $nivelobj= $query->fetchAll();
//dump($nivelobj);die;
            $nivelobjArray = array();
            foreach ($nivelobj as $value) {
                $nivelobjArray[$value['id']] =$value['acreditacion'];
            }

            //dump($areaobj);dump($areaobjArray);die;
            $response = new JsonResponse();
            return $response->setData(array('listarniveles' => $nivelobjArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }
    public function showModuloEditAction(Request $request)
    {   $em = $this->getDoctrine()->getManager();
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
        return $this->render('SiePermanenteBundle:MallaTecnica:editarmodulo.html.twig', array(
            'form' => $form->createView(),
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion,
            'horasmodulo'=>$horasmodulo

        ));
    }

    public function showModuloNuevoAction(Request $request)
    {//dump($request);die;
        $idsip = $request->get('idsip');
        $idesp = $request->get('idesp');
        $totalhoras = $request->get('totalhoras');
        $horas= [80,100,120];
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
        return $this->render('SiePermanenteBundle:MallaTecnica:nuevomodulo.html.twig', array(
             'form' => $form->createView(),
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion
            // 'idsip'=>$idsip
        ));
    }

    public function updateModuloNuevoAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
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
            $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$form['idesp']."
                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and smt.esvigente =true) dat
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
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
							--	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
							--		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$form['idesp']."
                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."' 
)as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
from superior_acreditacion_especialidad sae
		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
					where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$form['idesp']." 
                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and smt.esvigente =true) dat
group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
)as v on v.idacr = nivel.idacreditacion
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
            return $this->render('SiePermanenteBundle:MallaTecnica:listModulos.html.twig', array(
                'exist' => $exist,
                'malla' => $po,
                'mallafin' => $mallafinal, 
            ));
        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }

    }

    public function showModuloDeleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();       
        $idmodulo = $request->get('idmodulo');
        $idspm = $request->get('idspm');//dump($idspm);die;
        $modulo = $request->get('modulo');
        $horas = $request->get('horas');
        $idesp = $request->get('idesp');
        $gestion = $this->session->get('ie_gestion');
        $institucionEducativa= $this->session->get('ie_id');
         /* dump($idmodulo);
        dump($idspm);
        dump($modulo);
        dump($horas);
        dump($idesp); die;  */
        //Se verifica si el modulo a eliminar fue asignado a un estudiante
        $em = $this->getDoctrine()->getManager();                    
        $query= $em->getConnection()->prepare( 'select COUNT(*) as cantidad FROM  institucioneducativa_curso_oferta ico --limit  10
                                                INNER JOIN institucioneducativa_curso ic on ico.insitucioneducativa_curso_id =ic.id
                                                INNER JOIN estudiante_inscripcion ei on ic.id=ei.institucioneducativa_curso_id
                                                WHERE superior_modulo_periodo_id =:idspm and ic.gestion_tipo_id =:gestion and institucioneducativa_id=:institucionEducativa');
        $query->bindValue(':idspm', $idspm);
        $query->bindValue(':gestion', $gestion);
        $query->bindValue(':institucionEducativa', $institucionEducativa);
        $query->execute();       
        $cantidad = $query->fetchAll();
        //dump((int)($cantidad[0]['cantidad']));die;        
        if(((int)($cantidad[0]['cantidad']))>0){
                $this->get('session')->getFlashBag()->add('validacion', 'No puede eliminar el módulo. Antes debe eliminar lo siguiente:  módulo asignado al curso, participantes y notas.');

                $db = $em->getConnection();
                $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                            from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                            from superior_acreditacion_especialidad sae
                            inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                            inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                            inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                            inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                            inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$idesp."
                                and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                                and smt.esvigente =true) dat
                            group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip";
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
                                        inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                                --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                        where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$idesp."
                                        and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                        and sia.institucioneducativa_id ='".$this->session->get('ie_id')."')as nivel                                    
                            left join (
                                select idsae,idacr
                                --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
                                , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                                    from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                                from superior_acreditacion_especialidad sae
                                        inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                                            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                                                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                                                    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                                left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                                                    left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                                    where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$idesp."
                                                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                                    and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                                                    and smt.esvigente =true) dat
                                                    group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
                                )as v on v.idacr = nivel.idacreditacion";
                $final = $db->prepare($query);
                $params = array();
                $final->execute($params);
                $mallafinal = $final->fetchAll();    
                if ($po){
                    $exist = true;
                }
                else{
                    $exist = false;
                }    
                return $this->render('SiePermanenteBundle:MallaTecnica:listModulos.html.twig', array(
                    'exist' => $exist,                  
                    'mallafin' => $mallafinal,
                    'malla' => $po,
                     
                ));
        }else{//El mòdulo no esta asiganado a ningun estudiante             
            try{    //dump($idspm,$idmodulo);     die;       
                //verificar si el modulo a eliminar esta asigando a un maestro
                $query= $em->getConnection()->prepare( 'select count(*) as cantidad from institucioneducativa_curso_oferta_maestro iem
                                                        INNER JOIN institucioneducativa_curso_oferta ico on iem.institucioneducativa_curso_oferta_id= ico.id
                                                        INNER JOIN institucioneducativa_curso ic on ico.insitucioneducativa_curso_id =ic.id
                                                WHERE ico.superior_modulo_periodo_id =:idspm and ic.gestion_tipo_id =:gestion and institucioneducativa_id=:institucionEducativa');
                $query->bindValue(':idspm', $idspm);
                $query->bindValue(':gestion', $gestion);
                $query->bindValue(':institucionEducativa', $institucionEducativa);
                $query->execute();       
                $cantidadMaestros = $query->fetchAll(); 
                $em->getConnection()->beginTransaction();               
                if(((int)($cantidadMaestros[0]['cantidad']))>0){ 
                    //eliminar de curso ofertaMaestro
                    $idcom = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBySuperiorModuloPeriodo($idspm);                 
                    $curso_ofertamaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('institucioneducativaCursoOferta'=>$idcom[0]->getId())); 
                    $em->remove($curso_ofertamaestro);
                    $em->flush();                   
                    $curso_oferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBySuperiorModuloPeriodo($idspm);
                    $em->remove($curso_oferta);
                    $em->flush();                  
                    $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($idspm);  
                    $em->remove($supmodper);
                    $em->flush();                  
                    $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($idmodulo);
                    $em->remove($supmodtipo);
                    $em->flush();                   
                }else { //dump($idspm);die;
                    //$em->getConnection()->beginTransaction();
                    //preguntamos si ya se registro el curso con el modulo a eliminar
                    $verifica = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBySuperiorModuloPeriodo($idspm);  
                    if ($verifica){
                        $curso_ofertamaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBySuperiorModuloPeriodo($idspm);
                        $em->remove($curso_ofertamaestro);
                        $em->flush();

                        $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($idspm);  
                        $em->remove($supmodper);
                        $em->flush();
            
                        $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($idmodulo);
                        $em->remove($supmodtipo);
                        $em->flush();

                    }else{
                        
                        $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($idspm);  
                        $em->remove($supmodper);
                        $em->flush();
            
                        $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($idmodulo);
                        $em->remove($supmodtipo);
                        $em->flush();
                    }
                    
                }                
    
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron eliminados correctamente.');    
                $db = $em->getConnection();
                $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                            from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                            from superior_acreditacion_especialidad sae
                            inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                            inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                            inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                            inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                            inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$idesp."
                                and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                                and smt.esvigente =true) dat
                            group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip";
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
                            inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                            --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                            --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                            where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$idesp."
                            and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                            and sia.institucioneducativa_id ='".$this->session->get('ie_id')."')as nivel                                    
                            left join (
                                select idsae,idacr
                                --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
                                , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
                                from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                                from superior_acreditacion_especialidad sae
                                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                                    where sat.id in (1,20,32) and sfat.id=40 and sest.id=".$idesp."
                                                    and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                                                    and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                                                    and smt.esvigente =true) dat
                                                    group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
                                )as v on v.idacr = nivel.idacreditacion";
                $final = $db->prepare($query);
                $params = array();
                $final->execute($params);
                $mallafinal = $final->fetchAll();    
                if ($po){
                    $exist = true;
                }
                else{
                    $exist = false;
                }                
                return $this->render('SiePermanenteBundle:MallaTecnica:listModulos.html.twig', array(
                    'exist' => $exist,                  
                    'mallafin' => $mallafinal,
                    'malla' => $po,                    
                ));
            }
            catch(Exception $ex)
            {
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron eliminados, Asegurese de que el modulo no se esta utilizando.');
                return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
            }
        }
    }

}


