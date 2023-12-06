<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo;
use Sie\AppWebBundle\Entity\SuperiorMallaModuloPeriodo;
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Sie\AppWebBundle\Entity\SuperiorModuloTipo;
use Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad;
use Sie\AppWebBundle\Services\Funciones;
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

//         $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
// 						, sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
// 						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
					
// 					where sat.id in (1,20,32) 
// 					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 							order by sat.id asc, sae.id, sest.id  ,sia.id desc

//     ";//
//         $mallanivel = $db->prepare($query);
//         $params = array();
//         $mallanivel->execute($params);
//         $mallaniv = $mallanivel->fetchAll();
        
//         $db = $em->getConnection();
//         $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 					where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
// ) dat
// group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip
        
//     ";
//         $especialidadnivel = $db->prepare($query);
//         $params = array();
//         $especialidadnivel->execute($params);
//         $po = $especialidadnivel->fetchAll();
        
//         $db = $em->getConnection();
//         $query = " select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.periodo_medio, v.malla_modulo_periodo_id, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad 
//                     from (
// 						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion, case when sest.es_oficial = true then 1 else 0 end sw_esoficial
// 						, sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
// 						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 				    where sat.id in (1,20,32) 
// 					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
// left join (
// select idsae,idacr
// --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
// , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(periodo_medio::character varying, ',') as periodo_medio, string_agg(malla_modulo_periodo_id::character varying, ',') as malla_modulo_periodo_id, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// 	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smmp.superior_periodo_tipo_id as periodo_medio, smmp.id as malla_modulo_periodo_id, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// 	from superior_acreditacion_especialidad sae
// 			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 											inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
//                                                 left join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id
// 						where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
//                     and sest.es_vigente = true
//     order by smmp.superior_periodo_tipo_id
// 	) dat
// 	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
// ) as v on v.idacr = nivel.idacreditacion
//     ";
//         $final = $db->prepare($query);
//         $params = array();
//         $final->execute($params);
//         $mallafinal = $final->fetchAll();

        // set2.id=271

        // $queryCheckNiveles = $db->prepare("select distinct(sae.superior_acreditacion_tipo_id) as nivel from superior_acreditacion_especialidad sae 
        //                                     inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
        //                                     inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id 
        //                                     inner join superior_institucioneducativa_acreditacion sia on sae.id=sia.acreditacion_especialidad_id 
        //                                     inner join institucioneducativa_sucursal is2 on sia.institucioneducativa_sucursal_id=is2.id 
        //                                     inner join superior_institucioneducativa_periodo sip on sia.id=sip.superior_institucioneducativa_acreditacion_id 
        //                                     inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
        //                                     inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id 
        //                                     where
        //                                     is2.gestion_tipo_id=".$this->session->get('ie_gestion')."
        //                                     and is2.institucioneducativa_id=".$this->session->get('ie_id')."
        //                                     and sat.id in (1, 20, 32)
        //                                     and set2.id=".$aInfoUeducativa['ueducativaInfoId']['especialidad_id']."
        //                                     and is2.id=".$this->session->get('ie_suc_id')."
        //                                     and smt.esvigente=true
        //                                     and set2.es_vigente=true");
        // $queryCheckNiveles->execute();
        // $resultsNivels = $queryCheckNiveles->fetchAll();
        
        
        // $nivelIds = [1,20,32];
        
        // $cont = count($nivelIds);
        // $cont2 = count($resultsNivels);
        
        // $nivels = []; 
        // for ($i=0; $i < $cont; $i++) {
            
        //     $cont2 = count($resultsNivels);
            
        //     for ($j=0; $j < $cont2; $j++) { 
        //         if( $resultsNivels[$j]['nivel'] !== $nivelIds[$i] ){
        //             // $content[] = $nivelIds[$i];
        //             // dump($nivelIds[$i]);
        //             switch ($nivelIds[$i]) {
        //                 case '1':
        //                     $nivels[] = 'Tecnico Basico'; 
        //                     break;
        //                 case '20':
        //                     $nivels[] = 'Tecnico Auxiliar'; 
        //                     break;
        //                 case '32':
        //                     $nivels[] = 'Tecnico Medio'; 
        //                     break;
        //             }
        //         }

        //     }
        // }

        $nivels = [1=>"Tecnico Basico", 2=>"Tecnico Auxiliar", 3=>"Tecnico Medio"];        
        
        $form = $this->createFormBuilder()
            ->add('nivel', 'choice', array('choices'=> $nivels, 'empty_value' => 'Seleccione', 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('sestId', 'hidden', array('data' => $aInfoUeducativa['ueducativaInfoId']['especialidad_id']))
            ->add('guardar', 'button', array('label' => 'Agregar', 'attr' => array('class' => 'btn btn-primary m-t-25', 'enabled' => true, 'onclick'=>'addNivel();')))
            ->getForm();
        
        $modules = $this->findModulesBySest( $aInfoUeducativa['ueducativaInfoId']['especialidad_id'] );
        
        $superiorMallaModuloPeriodo = $this->checkIsNewMallaModuloPeriodo( $modules );

        // dump($modules['mallafinal']);die;
        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:seemodulosnew.html.twig', array(
            'exist'     => $modules['exist'],
            'malla'     => $modules['po'],
            'mallafin'  => $modules['mallafinal'],
            'mallaniv'  => $modules['mallaniv'],
            'area'      => $aInfoUeducativa['ueducativaInfo']['facultad_area'],
            'especialidad'  => $aInfoUeducativa['ueducativaInfo']['especialidad'],
            'infoUe'        => $infoUe,
            'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'], 
            'form'          => $form->createView(),
            'isSuperiorMallaModuloPeriodo' => $superiorMallaModuloPeriodo
        ));

    }

    public function seemodulosAction(Request $request) {
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
        $selectOld = true;
        // dump($idsip);die;
        $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
                ->add('modulo', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
                ->add('horas', 'choice', array('choices'=> $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
               // ->add('seccioniiProvincia', 'choice', array('choices' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'guardarModulo();')))
                ->add('idsip', 'hidden', array('data' => $idsip))
                ->add('idesp', 'hidden', array('data' => $idesp))
                ->add('selectOld', 'hidden', array('data' => $selectOld))
                ->add('totalhoras', 'hidden', array('data' => $totalhoras))
                ->getForm(); 

        $resultsModules = 0;
        $periodoTecnicoMedio = [1=>'Medio 1', 2=>'Medio 2'];    
        if( $idacreditacion == 32 ){

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            // VERIFICAR SI CUENTA CON MODULOS ASIGNADOS
            $queryCheckEmpty = $db->prepare("select * from superior_institucioneducativa_periodo sip 
                                                inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
                                                inner join superior_modulo_tipo smt on smt.id=smp.superior_modulo_tipo_id 
                                                where sip.id=".$idsip."");
            $queryCheckEmpty->execute();
            $resultCheckEmpty = count($queryCheckEmpty->fetchAll());

            // VERIFICAR SI CUENTA CON REGISTROS EN superior_malla_modulo_periodo
            $queryCheckMallaModulo = $db->prepare("select smp.id, smmp.superior_periodo_tipo_id, smt.modulo from superior_institucioneducativa_periodo sip 
                                                    inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id
                                                    inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id
                                                    inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                    where sip.id=".$idsip."
                                                    and smt.esvigente=true");
            $queryCheckMallaModulo->execute();
            $resultsModules = count($queryCheckMallaModulo->fetchAll());
            
            if( $resultCheckEmpty == 0 || $resultsModules > 0 ){
                $selectOld = false;
                $form = $this->createFormBuilder()
                    // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
                    ->add('modulo', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase' )))
                    ->add('horas', 'choice', array('choices'=> $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                    ->add('periodoTecnicoMedio', 'choice', array('choices'=> $periodoTecnicoMedio, 'empty_value' => 'Seleccione', 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                   // ->add('seccioniiProvincia', 'choice', array('choices' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>"guardarModulo(".$selectOld.");")))
                    ->add('idsip', 'hidden', array('data' => $idsip))
                    ->add('idesp', 'hidden', array('data' => $idesp))
                    ->add('selectOld', 'hidden', array('data' => $selectOld))
                    ->add('totalhoras', 'hidden', array('data' => $totalhoras))
                    ->getForm();
            }else{
                $selectOld = true;
                $form = $this->createFormBuilder()
                    // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
                    ->add('modulo', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
                    ->add('horas', 'choice', array('choices'=> $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                // ->add('seccioniiProvincia', 'choice', array('choices' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>"guardarModulo(".$selectOld.");")))
                    ->add('idsip', 'hidden', array('data' => $idsip))
                    ->add('idesp', 'hidden', array('data' => $idesp))
                    ->add('selectOld', 'hidden', array('data' => $selectOld))
                    ->add('totalhoras', 'hidden', array('data' => $totalhoras))
                    ->getForm(); 

            }
             
        }

        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:nuevomodulo.html.twig', array(
            'form' => $form->createView(),
            'selectOld' => $selectOld, 
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion,
            'modules'=>$resultsModules
           // 'horasmodulo'=>$horasmodulo
            // 'idsip'=>$idsip
        ));
    }

    public function showModuloEditAction(Request $request)
    {  
        // $horas= [80,100,120]; // SE ESTABLECIO 100 HORAS SEGUN DGEA
        $idmodulo = $request->get('idmodulo');
        $idspm = $request->get('idspm');
        $modulo = $request->get('modulo');
        $horasmodulo = $request->get('horas');
        $idesp = $request->get('idesp');
        $totalhoras = $request->get('totalhoras');
        $idacreditacion =$request->get('idacred');
        $periodoTecnicoMedio = $request->get('periodoTecnicoMedio');
        $mallaModuloPeriodoId = $request->get('mallaModuloPeriodoId');
        $sipId = $request->get('idsip');
        // dump($request->get('selectOld'));die;
        $horas = 1;
        $arrayPeriodoTM = ['Medio 1','Medio 2'];    
        $periodoTecnicoMedio = $periodoTecnicoMedio == 4 ? 'Medio 1' : 'Medio 2';
        
        $existMallaModuloPeriodo = false;

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        for($i=0;$i<=1;$i++){
            if($arrayPeriodoTM[$i]==$periodoTecnicoMedio){
                $periodosid = $i;
            }
        }

        // VERIFICAR SI ESTA CONSOLIDADO EL MODULO
        $queryAsg = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                        inner join superior_modulo_periodo smp on ico.superior_modulo_periodo_id=smp.id
                                        where smp.id=".$idspm."
                                        ");
        $queryAsg->execute();
        $isEnable = ($queryAsg->fetch()) ? true : false;

        // VERIFICAR LAS HORAS QUE LLEGAN
        if( $horasmodulo != 100 ){

            $horasSelect = array();

            array_push( $horasSelect, $horasmodulo );
            array_push( $horasSelect, '100' );
            
            for($j=0;$j<=1;$j++){
                if($horasSelect[$j]==$horasmodulo){
                    $horas = $j;
                }
            }

        }else{
            $horasSelect = [100];
        }

        // VERIFICAR SI TIENE MEDIO 1 O MEDIO 2
        if( $idacreditacion == 32 ){
            $horasSelect = $request->get('horas');
            
            $queryMallaModuloPeriodo = $db->prepare("select * from superior_modulo_periodo smp 
                                                        inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                        where smp.id=".$idspm."");
            $queryMallaModuloPeriodo->execute();
            $existMallaModuloPeriodo = ($queryMallaModuloPeriodo->fetch()) ? true : false;
        }
        

        // $response = new JsonResponse();
        //     return $response->setData(array(
        //         'statusCode' => 401,
        //         'message'    => 'No puede realizar esta accion porque el modulo se encuentra consolidado'
        //     ));
        // dump($horasSelect);die;

        if( $idacreditacion == 32 && $existMallaModuloPeriodo ){

            $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
                ->add('modulo', 'text', array('required' => true,'data' => $modulo, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
                // ->add('horas', 'choice', array('choices'=> [$horasmodulo],'data' => 1, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('horas', 'choice', array('choices'=> $horasSelect,'data' => $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                // ->add('horas', 'choice', array('choices'=> $horas,'data' => $arrayPeriodoTM, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))

                ->add('periodoTecnicoMedio', 'choice', array('choices'=> $arrayPeriodoTM, 'data'=> $periodosid, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'updateModulo();')))
                ->add('idmodulo', 'hidden', array('data' => $idmodulo))
                ->add('idspm', 'hidden', array('data' => $idspm))
                ->add('idesp', 'hidden', array('data' => $idesp))
                ->add('idacreditacion', 'hidden', array('data' => $idacreditacion))
                ->add('mallaModuloPeriodoId', 'hidden', array('data' => $mallaModuloPeriodoId))
                ->add('totalhoras', 'hidden', array('data' => $totalhoras))
                ->getForm();

        }else{

            $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
                ->add('modulo', 'text', array('required' => true,'data' => $modulo, 'attr' => array('class' => 'form-control', 'enabled' => true, 'disabled'=> $isEnable, 'style' => 'text-transform:uppercase')))
                ->add('modulo', 'text', array('data' => $modulo, 'attr' => array('class' => 'form-control', 'enabled' => true, 'hidden'=> $isEnable, 'style' => 'text-transform:uppercase')))
                ->add('horas', 'choice', array('choices'=> $horasSelect,'data' => $horas, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'updateModulo();')))
                ->add('idmodulo', 'hidden', array('data' => $idmodulo))
                ->add('idspm', 'hidden', array('data' => $idspm))
                ->add('idesp', 'hidden', array('data' => $idesp))
                ->add('idacreditacion', 'hidden', array('data' => $idacreditacion))
                ->add('mallaModuloPeriodoId', 'hidden', array('data' => $mallaModuloPeriodoId))
                ->add('totalhoras', 'hidden', array('data' => $totalhoras))
                ->getForm();

        }

        // $form = $this->createFormBuilder()
        //     // ->setAction($this->generateUrl('herramienta_per_add_areatem'))
        //     ->add('modulo', 'text', array('required' => true,'data' => $modulo, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
        //     ->add('horas', 'choice', array('choices'=> [$horasmodulo],'data' => 1, 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
        //     ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'updateModulo();')))
        //     ->add('idmodulo', 'hidden', array('data' => $idmodulo))
        //     ->add('idspm', 'hidden', array('data' => $idspm))
        //     ->add('idesp', 'hidden', array('data' => $idesp))
        //     ->add('totalhoras', 'hidden', array('data' => $totalhoras))
        //     ->getForm();

        return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:editarmodulo.html.twig', array(
            'form' => $form->createView(),
            'totalhoras'=>$totalhoras,
            'idacreditacion'=>$idacreditacion,
            'horasmodulo'=>$horasmodulo,
            'superiorMallaModuloPeriodoId'=>$existMallaModuloPeriodo,
            'mallaModuloPeriodoId'=>$mallaModuloPeriodoId
        ));
    }

    public function checkConsolidateAction( Request $request ){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
       
        // $query = "select ic.id, sia.institucioneducativa_id,sia.acreditacion_especialidad_id, smp.superior_modulo_tipo_id,smp.institucioneducativa_periodo_id, smp.horas_modulo 
        //             from institucioneducativa_curso ic
        //             inner join superior_institucioneducativa_periodo sip on ic.superior_institucioneducativa_periodo_id=sip.id
        //             inner join superior_institucioneducativa_acreditacion sia on ic.superior_institucioneducativa_periodo_id=sia.id
        //             inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id
        //             where smp.id=".$request->get('idspm')."";
        
        $query = $db->prepare("select * from superior_modulo_periodo smp 
                                inner join institucioneducativa_curso_oferta ico on smp.id=ico.superior_modulo_periodo_id 
                                where smp.id=".$request->get('idspm')."");
        $query->execute();
        $result = $query->fetch();

        $edit = false;
        if( !$result ){
            $edit=true;
        }

        $response = new JsonResponse();
        return $response->setData(array('data' => $edit));
    }

    public function updateModuloNuevoAction(Request $request){
        
        $form = $request->get('form');
        // dump($form);die;
        $modulo = strtoupper($form['modulo']);

        $idspm = $form['idspm'];
        $idesp = $form['idesp'];
        $mallaModuloPeriodoId = $form['mallaModuloPeriodoId'];
        // $horas= [80,100,120];
        $horas= [100];
        $horasid = ($form['horas']);
        $acreditacionId = $form['idacreditacion'];
        
        $horasmodulo = $horas[0];

        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $db = $em->getConnection();

            // VERIFICAR SI ESTA CONSOLIDADO EL MODULO
            // $query = $db->prepare("select * from superior_modulo_periodo smp 
            //                     inner join institucioneducativa_curso_oferta ico on smp.id=ico.superior_modulo_periodo_id 
            //                     where smp.id=".$idspm."");
            // $query->execute();
            // $result = $query->fetch();

            // if( $result ){
            //     $response = new JsonResponse();
            //     return $response->setData(array(
            //         'statusCode' => 401,
            //         'message'    => 'No puede realizar esta accion porque el modulo se encuentra consolidado'
            //     ));
            // }

            $supmodtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findOneBy(array('id' => $form['idmodulo']));
            $supmodtipo -> setModulo($modulo);
            $em->persist($supmodtipo);
            $em->flush($supmodtipo);

            //  dump($smtipo);die;
            $supmodper = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' => $form['idspm']));
            $supmodper->setHorasModulo($horasmodulo);
            $em->persist($supmodper);
            $em->flush($supmodper);

            // VERIFICAR SI ESTA REGISTRADO EN LA TABLA superior_malla_modulo_periodo
            $queryMallaModuloPeriodo = $db->prepare("select * from superior_modulo_periodo smp 
                                                    inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                    where smp.id=".$idspm."");
            $queryMallaModuloPeriodo->execute();
            $existMallaModuloPeriodo = ($queryMallaModuloPeriodo->fetch()) ? true : false; 

            if( $acreditacionId == 32 && $existMallaModuloPeriodo ){

                $periodoTecnicoMedio = $form['periodoTecnicoMedio'] == 0 ? 4 : 5; 

                $sptipo = $em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->findOneBy(array('id' => $periodoTecnicoMedio));
    
                $smmperiodo = $em->getRepository('SieAppWebBundle:SuperiorMallaModuloPeriodo')->findOneBy(array('id'=>$mallaModuloPeriodoId));
                $smmperiodo->setSuperiorPeriodoTipo( $sptipo );
                $em->persist($smmperiodo);
                $em->flush($smmperiodo);
            
            }

            //  dump($smperiodo);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            
//             $db = $em->getConnection();
//             $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 					where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id='".$idesp."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
// ) dat
// group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

        
//     ";
// //        print_r($query);
// //        die;
//             $especialidadnivel = $db->prepare($query);
//             $params = array();
//             $especialidadnivel->execute($params);
//             $po = $especialidadnivel->fetchAll();

//             $db = $em->getConnection();
//             $query = "  select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
// 						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
// 						, sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
// 						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 				    where sat.id in (1,20,32) 
// 					and sest.id='".$idesp."'
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
// left join (
// select idsae,idacr
// --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
// , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// 	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// 	from superior_acreditacion_especialidad sae
// 			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 											 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id='".$idesp."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
// 	) dat
// 	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
// ) as v on v.idacr = nivel.idacreditacion
        
//     ";
//             $final = $db->prepare($query);
//             $params = array();
//             $final->execute($params);
//             $mallafinal = $final->fetchAll();

            $modules = $this->findModulesBySest( $idesp );

            // return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
            //     'exist'     => $modules['exist'],
            //     'malla'     => $modules['po'],
            //     'mallafin'  => $modules['mallafinal'],
            //     'mallaniv'  => $modules['mallaniv'],
            // ));

            $superiorMallaModuloPeriodo = false;
            if( count($modules['mallafinal']) > 2 ){

                $db = $em->getConnection();

                $queryCheckModules = $db->prepare("select smp.id, smmp.superior_periodo_tipo_id, smt.modulo from superior_institucioneducativa_periodo sip 
                                                        inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id
                                                        inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id
                                                        inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                        where sip.id=".$modules['mallafinal'][2]['idsip']."
                                                        and smt.esvigente=true");
                $queryCheckModules->execute();
                $resultsModules = count($queryCheckModules->fetchAll());
                
                if( $resultsModules > 0  ){ $superiorMallaModuloPeriodo = true; }
            }else{
                $superiorMallaModuloPeriodo = false;
            }

            // dump($po);die();
            // if ($po){
            //     $exist = true;
            // }
            // else{
            //     $exist = false;
            // }
            
            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist'     => $modules['exist'],
                'malla'     => $modules['po'],
                'mallafin'  => $modules['mallafinal'],
                'mallaniv'  => $modules['mallaniv'],
                'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'],
                'isSuperiorMallaModuloPeriodo'=>$superiorMallaModuloPeriodo
            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(\Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }

    }
    public function checkIsNewMallaModuloPeriodo( $mallaFinal ){
        // dump($mallaFinal);
        $superiorMallaModuloPeriodo = false;
        // dump( count($mallaFinal['mallafinal']) );
        // dump($mallaFinal['mallafinal'][2]['idsip']);
        // die;

        if( count($mallaFinal['mallafinal']) > 2 ){

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            $queryCheckModules = $db->prepare("select smp.id, smmp.superior_periodo_tipo_id, smt.modulo from superior_institucioneducativa_periodo sip 
                                                    inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id
                                                    inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id
                                                    inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                    where sip.id=".$mallaFinal['mallafinal'][2]['idsip']."
                                                    and smt.esvigente=true");
            $queryCheckModules->execute();
            $resultsModules = count($queryCheckModules->fetchAll());
            
            if( $resultsModules > 0  ){ $superiorMallaModuloPeriodo = true; }
        }else{
            $superiorMallaModuloPeriodo = false;
        }

        return $superiorMallaModuloPeriodo;

    }

    public function showModuloDeleteAction(Request $request)
    {  
        $em = $this->getDoctrine()->getManager();

        $idmodulo = $request->get('idmodulo');
        $idspm = $request->get('idspm');
        $modulo = $request->get('modulo');
        $horas = $request->get('horas');
        $idesp = $request->get('idesp');

        // $em->getConnection()->beginTransaction();

        try{
            $smtipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findOneBy(array('id' => $idmodulo));
            if($smtipo){
                $em->remove($smtipo);
            }
            
            $smperiodo = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' => $idspm));
            if( $smperiodo ){
                $em->remove($smperiodo);
            }

            $smmp = $em->getRepository('SieAppWebBundle:SuperiorMallaModuloPeriodo')->findOneBy(array('superiorModuloPeriodo' => $idspm));
            if( $smmp ){
                $em->remove($smmp);
            }

            $em->flush();
            $em->clear();
            // $em->getConnection()->commit();
            // $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron eliminados correctamente.');
            
            $modules = $this->findModulesBySest( $idesp );

            $superiorMallaModuloPeriodo = $this->checkIsNewMallaModuloPeriodo( $modules );

            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist'     => $modules['exist'],
                'malla'     => $modules['po'],
                'mallafin'  => $modules['mallafinal'],
                'mallaniv'  => $modules['mallaniv'],
                'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'],
                'isSuperiorMallaModuloPeriodo'=>$superiorMallaModuloPeriodo
            ));
        
        }catch(\Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron eliminados, Asegurese de que el modulo no se esta utilizando.');
            return $this->redirect($this->generateUrl('herramienta_alter_malla_tecnica_index'));
        }

    }

    public function deleteModuleTecnicoAction( Request $request ){

        try {

            $smpId = $request->get('smpid');
            $sestId = $request->get('sestId');
            $sipId = $request->get('sipId'); 
            $satId = $request->get('satId');

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            $response = new JsonResponse();

            // VERIFICAR QUE NO SE PUEDA ELIMINAR < 500
            $gestion = $this->session->get('ie_gestion');
            if( $gestion >= 2023 ){

                // ENCONTRAR MODULOS BY SIP
                $queryOne = $db->prepare("select * from superior_modulo_periodo smp 
                                            inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id 
                                            where smp.institucioneducativa_periodo_id=".$sipId."");
                $queryOne->execute();
                $resultOne = $queryOne->fetchAll();
                
                // VERIFICAR CANTIDAD DE MODULOS ANTES DE ELIMINAR
                if( count($resultOne) == 5 && $satId != 32 ){

                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "No puede eliminar mas modulos"
                    ));

                }

                if( count($resultOne) == 10 && $satId == 32 ){
                        
                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "No puede eliminar mas modulos"
                    )); 

                }

                // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_asignatura
                $queryAsignatura = $db->prepare("select * from superior_modulo_periodo smp 
                                        inner join institucioneducativa_curso_oferta ico on ico.superior_modulo_periodo_id=smp.id
                                        inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id 
                                        where smp.id=".$smpId."");
                $queryAsignatura->execute();
                $resultAsignatura = $queryAsignatura->fetchAll();
                if( count($resultAsignatura) > 0 ){
        
                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "Los datos no fueron eliminados, asegurese de que el modulo no esta siendo utilizado"
                    ));
    
                }
    
                // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_nota
                $queryNota = $db->prepare("select * from superior_modulo_periodo smp 
                                        inner join institucioneducativa_curso_oferta ico on ico.superior_modulo_periodo_id=smp.id
                                        inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                                        inner join estudiante_nota en on en.estudiante_asignatura_id=ea.id 
                                        where smp.id=".$smpId."");
                $queryNota->execute();
                $resultNota = $queryNota->fetchAll();
                if( count($resultNota) > 0 ){
    
                    $response = new JsonResponse();
        
                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "Los datos no fueron eliminados, asegurese de que el modulo no esta siendo utilizado"
                    ));
    
                }
        
                $samp = $em->getRepository('SieAppWebBundle:SuperiorMallaModuloPeriodo')->findOneBy(array('superiorModuloPeriodo' => $smpId));
                if( $samp ){
    
                    $superiorMallaModuloPeriodo = (object) [
                        'id' => $samp->getId(), 
                        'superiorPeriodoTipoId' => $samp->getSuperiorPeriodoTipo()->getId(), 
                        'superiorModuloPeriodo' => $samp->getSuperiorModuloPeriodo()->getId(),
                        'observacion'=> $samp->getObservacion(),
                        'fechaRegistro'=> $samp->getFechaRegistro()->format('d-m-Y H:i:s'),
                        'fechaModificacion'=>$samp->getFechaModificacion()->format('d-m-Y H:i:s')
                    ];
                    
                    $this->get('funciones')->setLogTransaccion(
                        $samp->getId(),
                        'SuperiorMallaModuloPeriodo',
                        'D',
                        '',
                        '',
                        $superiorMallaModuloPeriodo,
                        'ALTERNATIVA',
                        ''
                    );
    
                    $em->remove($samp);
                }
        
                $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' => $smpId));
                if( $smp ){
    
                    $superiorModuloPeriodo = (object) [
                        'id' => $smp->getId(),
                        'superiorModuloTipoId' => $smp->getSuperiorModuloTipo()->getId(), 
                        'institucioneducativaPeriodoId'=>$smp->getInstitucioneducativaPeriodo()->getId(),
                        'obs' => $smp->getObs(), 
                        'horasModulo'=>$smp->getHorasModulo()
                    ];
                                    
                    $this->get('funciones')->setLogTransaccion(
                        $smp->getId(),
                        'SuperiorModuloPeriodo',
                        'D',
                        '',
                        '',
                        $superiorModuloPeriodo,
                        'ALTERNATIVA',
                        ''
                    );
    
                    $em->remove($smp);
                }
    
                $em->flush();
                $em->clear();
        
                $modules = $this->findModulesBySest( $sestId );
        
                $superiorMallaModuloPeriodo = $this->checkIsNewMallaModuloPeriodo( $modules );
        
                return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                    'exist'     => $modules['exist'],
                    'malla'     => $modules['po'],
                    'mallafin'  => $modules['mallafinal'],
                    'mallaniv'  => $modules['mallaniv'],
                    'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'],
                    'isSuperiorMallaModuloPeriodo'=>$superiorMallaModuloPeriodo
                ));

            }

        }catch(\Exception $ex){
            // $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron eliminados, asegurese de que el modulo no esta siendo utilizado');
            // return $this->redirect($this->generateUrl('herramienta_alter_malla_tecnica_index'));
        }

    }

    public function findModulesBySest( $idesp ){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();


//         $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
//                     , sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
//     inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
//         inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
//             inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
//                 inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
//                     inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
//                     inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                
//                 where sat.id in (1,20,32) 
//                 and sest.id='".$idesp."'
//                 and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
//                 and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
//                 and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
//                 and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
//                         order by sat.id asc, sae.id, sest.id  ,sia.id desc

// ";

//         $mallanivel = $db->prepare($query);
//         $params = array();
//         $mallanivel->execute($params);
//         $mallaniv = $mallanivel->fetchAll();


//         $db = $em->getConnection();
//         $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// from superior_acreditacion_especialidad sae
//     inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
//         inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
//             inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
//                 inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
//                         inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
//                             left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
//                                 left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
//                                     inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
//                 where sat.id in (1,20,32) 
//                 and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
//                 and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
//                 and sfat.codigo in ('18','19','20','21','22','23','24','25')
//                 and sest.id='".$idesp."'
//                 and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
//                 and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
//                 and smt.esvigente =true
// ) dat
// group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

    
// ";

//         $especialidadnivel = $db->prepare($query);
//         $params = array();
//         $especialidadnivel->execute($params);
//         $po = $especialidadnivel->fetchAll();

//         if ($po){
//             $exist = true;
//         }
//         else{
//             $exist = false;
//         }
//         $db = $em->getConnection();
//         $query = "  select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
//                     select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
//                     , sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
//     inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
//         inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
//             inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
//                 inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
//                     inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
//                     inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
//                 where sat.id in (1,20,32) 
//                 and sest.id='".$idesp."'
//                 and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
//                 and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
//                 and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
//                 and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
//                         order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
// left join (
// select idsae,idacr
// --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
// , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// from superior_acreditacion_especialidad sae
//         inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
//             inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
//                 inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
//                     inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
//                             inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
//                                 left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
//                                     left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
//                                         inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
//                     where sat.id in (1,20,32) 
//                 and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
//                 and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
//                 and sfat.codigo in ('18','19','20','21','22','23','24','25')
//                 and sest.id='".$idesp."'
//                 and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
//                 and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
//                 and smt.esvigente =true
// ) dat
// group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
// ) as v on v.idacr = nivel.idacreditacion
    
// ";
//         $final = $db->prepare($query);
//         $params = array();
//         $final->execute($params);
//         $mallafinal = $final->fetchAll();

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
                            and sest.id=".$idesp."
                            and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
                            and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
                            and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
                            and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
                                    order by sat.id asc, sae.id, sest.id  ,sia.id desc";
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
					and sest.id=".$idesp."
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
) dat
group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip";
        $especialidadnivel = $db->prepare($query);
        $params = array();
        $especialidadnivel->execute($params);
        $po = $especialidadnivel->fetchAll();

        // if ($po){
        //     $exist = true;
        // }else{
        //     $exist = false;
        // }
        // dump($this->session->get('ie_subcea'));
        // die;
        $db = $em->getConnection();
        $query = "select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.periodo_medio, v.malla_modulo_periodo_id, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad 
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
					and sest.id=".$idesp."
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
left join (
select idsae,idacr
--,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
, string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(periodo_medio::character varying, ',') as periodo_medio, string_agg(malla_modulo_periodo_id::character varying, ',') as malla_modulo_periodo_id, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smmp.superior_periodo_tipo_id as periodo_medio, smmp.id as malla_modulo_periodo_id, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
	from superior_acreditacion_especialidad sae
			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
											inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
                                                left join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id
						where sat.id in (1,20,32) 
					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
					and sfat.codigo in ('18','19','20','21','22','23','24','25')
					and sest.id=".$idesp."
					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
					and smt.esvigente =true
                    and sest.es_vigente = true
    order by smmp.superior_periodo_tipo_id
	) dat
	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
) as v on v.idacr = nivel.idacreditacion";
        $final = $db->prepare($query);
        $params = array();
        $final->execute($params);
        $mallaFinal = $final->fetchAll();

        if ($po||$mallaFinal){
            $exist = true;
        }else{
            $exist = false;
        }

        // dump("sest.id: ".$idesp);
        // dump("sia.gestion_tipo_id: ".$this->session->get('ie_gestion'));
        // dump("sia.institucioneducativa_id: ".$this->session->get('ie_id'));
        // dump("f.periodo_tipo_id: ".$this->session->get('ie_per_cod'));
        // dump("f.sucursal_tipo_id: ".$this->session->get('ie_subcea'));
        // die;

        return array(
            'mallaniv'  =>$mallaniv,
            'po'        =>$po,
            'mallafinal'=>$mallaFinal,
            'exist'     =>$exist
        );

    }

    public function addNivelMallaAction( Request $request ){

        try {

            $form = $request->get('form');
            $nivelId = $form['nivel'];
            $sestId = $form['sestId'];
            switch ($nivelId) {
                case '1':
                    $nivelId = 1;
                    break;
                case '2':
                    $nivelId = 20;
                    break;
                case '3':
                    $nivelId = 32;
                    break;
                default:
                    break;
            }
            
            $em = $this->getDoctrine()->getManager();
    
            $db = $em->getConnection();

            $query = $db->prepare("select distinct(sae.superior_acreditacion_tipo_id), sae.id from superior_acreditacion_especialidad sae 
                                    inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id 
                                    where 
                                    sae.superior_acreditacion_tipo_id=".$nivelId."
                                    and set2.id=".$sestId."
                                    order by sae.id desc
                                    limit 1");
            $query->execute();
            $superiorAcreditacionEspecialidad = $query->fetch();

            if( !$superiorAcreditacionEspecialidad ){
                // dump("here 1");die;
                $sae = new SuperiorAcreditacionEspecialidad();                
                $sae->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($nivelId));
                $sae->setSuperiorEspecialidadTipo($em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($sestId));
                $em->persist($sae);
                $em->flush();
    
                $superiorAcreditacionEspecialidad = $sae->getId();
            }else{
                $superiorAcreditacionEspecialidad = $superiorAcreditacionEspecialidad['id'];
            }
            
            $queryOne = $db->prepare("select * from superior_institucioneducativa_acreditacion sia 
                                        where 
                                        institucioneducativa_id=".$this->session->get('ie_id')."
                                        and sia.acreditacion_especialidad_id=".$superiorAcreditacionEspecialidad."
                                        and sia.institucioneducativa_sucursal_id=".$this->session->get('ie_suc_id')."
                                        and sia.gestion_tipo_id=".$this->session->get('ie_gestion')."");
            $queryOne->execute();
            $superiorInstitucioneducativaAcreditacion = $queryOne->fetch();

            if( !$superiorInstitucioneducativaAcreditacion ){
                // dump("here 2");die;
                $querySucu = $db->prepare("select * from institucioneducativa_sucursal is2 where is2.id=".$this->session->get('ie_suc_id')." order by is2.id desc limit 1");
                $querySucu->execute();
                $institucioneducativaSucursal = $querySucu->fetch();

                $superiorTurnoTipo = $institucioneducativaSucursal['turno_tipo_id'];

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();                
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->find($superiorAcreditacionEspecialidad));
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find($superiorTurnoTipo));
                $em->persist($siea);
                $em->flush();
    
                $superiorInstitucioneducativaAcreditacion = $siea->getId();
    
            }else{
                $superiorInstitucioneducativaAcreditacion = $superiorInstitucioneducativaAcreditacion['id'];
            }
            
            $queryThree = $db->prepare("select * from superior_institucioneducativa_periodo sip 
                                            where sip.superior_institucioneducativa_acreditacion_id=".$superiorInstitucioneducativaAcreditacion."");
            $queryThree->execute();
            $superiorInstitucioneducativaPeriodo = $queryThree->fetch();

            if( !$superiorInstitucioneducativaPeriodo ){
                if( $nivelId == 1 ){
                    $superiorPeriodoTipo = 2;
                }else if( $nivelId == 20 ){
                    $superiorPeriodoTipo = 3;
                }else{
                    $superiorPeriodoTipo = 4;
                }

                $siea = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($superiorInstitucioneducativaAcreditacion);
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                $siep = new SuperiorInstitucioneducativaPeriodo();                
                $siep->setSuperiorInstitucioneducativaAcreditacion($siea);
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find($superiorPeriodoTipo));              
                $em->persist($siep);
                $em->flush();
    
                $superiorInstitucioneducativaPeriodo = $siep->getId();
    
            }else{
                $superiorPeriodoTipo = $superiorInstitucioneducativaPeriodo['superior_periodo_tipo_id'];
                $superiorInstitucioneducativaPeriodo = $superiorInstitucioneducativaPeriodo['id'];
            }

            $queryFour = $db->prepare("select * from superior_modulo_periodo smp where smp.institucioneducativa_periodo_id=".$superiorInstitucioneducativaPeriodo."");
            $queryFour->execute();
            $superiorModuloPeriodo = $queryFour->fetchAll();

            $contModules = count($superiorModuloPeriodo);

            // if( $contModules == 0 ){
                $queryFive = $db->prepare("select * from ctr_altenativa_planes cap where 
                                            cap.superior_acreditacion_tipo=".$nivelId."
                                            and cap.superior_especialidad_tipo_id=".$sestId."");
                $queryFive->execute();
                $modules = $queryFive->fetchAll();
                // dump($modules);die;
                $contModulesTM = 0; 
                foreach ($modules as $value) {

                    $modulo = $value['modulo'];

                    $querySeven = $db->prepare("select * from superior_modulo_tipo smt where 
                                                    smt.superior_especialidad_tipo_id=".$sestId." 
                                                    and smt.modulo like '".$modulo."'");
                    $querySeven->execute();
                    $superiorModuloTipo = $querySeven->fetch();

                    if(!$superiorModuloTipo){
                        $smtipo = new SuperiorModuloTipo();
                        $smtipo -> setModulo($modulo);
                        $smtipo -> setEsvigente(true);
                        $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                        $smtipo -> setSuperiorEspecialidadTipo($em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($sestId));

                        $em->persist($smtipo);
                        $em->flush();

                        $smtipo = $smtipo->getId();
                    }else{
                        $smtipo = $superiorModuloTipo['id'];
                    }
                    // dump("out");
                    $queryEight = $db->prepare("select * from superior_modulo_periodo smp2 where 
                                                    smp2.superior_modulo_tipo_id=".$smtipo."
                                                    and smp2.institucioneducativa_periodo_id=".$superiorInstitucioneducativaPeriodo."");
                    $queryEight->execute();
                    $superiorModuloPeriodo = $queryEight->fetch();

                    if( !$superiorModuloPeriodo ){
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                        $smperiodo = new SuperiorModuloPeriodo();
                        $smperiodo ->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($smtipo));
                        $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($superiorInstitucioneducativaPeriodo));
                        $smperiodo ->setHorasModulo('100');
                        $em->persist($smperiodo);
                        $em->flush($smperiodo);

                        $smperiodo = $smperiodo->getId();
                    }else{

                        $smperiodo = $superiorModuloPeriodo['id'];
                        
                        //  FUNCIONAL TEMPORAL PARA ELIMINAR REGISTROS DOBLES QUE NO ESTAN RELACIONADO CON LAS NOTAS
                        // $queryCheckNota = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                        //                                     inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                        //                                     inner join estudiante_nota en on ea.id=en.estudiante_asignatura_id 
                        //                                     where ico.superior_modulo_periodo_id=".$smperiodo."");
                        // $queryCheckNota->execute();
                        // $resultChecking = $queryCheckNota->fetch();

                        // dump($resultChecking);die;

                    }
                    // dump("out 2");die;
                    // TABLE NEW FOR IDENTIFY MIDDLE ONE Y TWO
                    $superiorPeriodoTipoId = 0;
                    switch ($nivelId) {
                        case 1:
                            $superiorPeriodoTipoId = 2;       
                            break;
                        case 20:
                            $superiorPeriodoTipoId = 3;
                            break;
                        case 32:
                            $contModulesTM = $contModulesTM + 1;
                            $superiorPeriodoTipoId = ( $contModulesTM <= 5 ) ? 4 : 5;
                            break;
                        default:
                            $superiorPeriodoTipoId = 0;
                            break;
                    }

                    $queryLast = $db->prepare("select * from superior_malla_modulo_periodo smmp where smmp.superior_modulo_periodo_id=".$smperiodo."");
                    $queryLast->execute();
                    $superiorMallaModuloPeriodo = $queryLast->fetch();

                    if( !$superiorMallaModuloPeriodo ){
                        $superiorPeriodoTipo = $em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find( $superiorPeriodoTipoId );
                        $smmp = new SuperiorMallaModuloPeriodo();
                        $smmp->setSuperiorPeriodoTipo( $superiorPeriodoTipo );
                        $smmp->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smperiodo));
                        $smmp->setFechaRegistro(new \DateTime('now'));
                        $smmp->setFechaModificacion(new \DateTime('now'));
                        $em->persist($smmp);
                        $em->flush($smmp);
                    }

                }
    
            // }
    
            $modules = $this->findModulesBySest( $sestId );

            $superiorMallaModuloPeriodo = $this->checkIsNewMallaModuloPeriodo( $modules );

            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist'         => $modules['exist'],
                'malla'         => $modules['po'],
                'mallafin'      => $modules['mallafinal'],
                'mallaniv'      => $modules['mallaniv'],
                'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'],
                'isSuperiorMallaModuloPeriodo'=>$superiorMallaModuloPeriodo
            ));

        } catch (\Throwable $th) {
            
        }


    }

    public function createModuloNuevoAction(Request $request){

        $form = $request->get('form');
        // dump($form);die;
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
        $selectOld = $form['selectOld']; 
        // dump($form['selectOld']);die;

        $superiorAcreditacionTipoId = $request->get('idacreditacion');

        // $superiorAcreditacionTipoId = $form['idacred'];
        // dump($superiorAcreditacionTipoId);
        // dump($form);
        // die;
        try{
            
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            
            // VERIFY HOW MANY MODULES WERE CREATED
            if( $superiorAcreditacionTipoId == 32 && $selectOld == false ){

                $queryCheckMedios = $db->prepare("select smp.id, smmp.superior_periodo_tipo_id from superior_institucioneducativa_periodo sip 
                                                    inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id
                                                    inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id
                                                    inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                                    where sip.id=".$idsip."
                                                    and smt.esvigente=true
                                                    order by smmp.superior_periodo_tipo_id asc");
                // $params = array();
                $queryCheckMedios->execute();
                $results = $queryCheckMedios->fetchAll();
    
                $periodoTecnicoMedio = $form['periodoTecnicoMedio'];
    
                $cont4 = 0;
                $cont5 = 0;
                foreach ($results as $value) {
                    
                    $cont4 = ( $value['superior_periodo_tipo_id'] == 4 ) ? $cont4 = $cont4 + 1 : $cont4 = $cont4;
                    $cont5 = ( $value['superior_periodo_tipo_id'] == 5 ) ? $cont5 = $cont5 + 1 : $cont5 = $cont5;
    
                }
    
                if( ($periodoTecnicoMedio == 1 && $cont4 == 5) || ($periodoTecnicoMedio == 2 && $cont5 == 5) ){
                    $response = new JsonResponse();
    
                    $messagePeriodo = ( $periodoTecnicoMedio == 1 ) ? "Medio 1" : "Medio 2";
    
                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "Solo puede agregar 5 modulos del ".$messagePeriodo." de Tecnico Medio"
                    ));
                }

            }

            $em->getConnection()->beginTransaction();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();
            $smtipo = new SuperiorModuloTipo();
            $smtipo -> setModulo($modulo);
            $smtipo -> setEsvigente(true);
            $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
            $em->persist($smtipo);
            $em->flush($smtipo);

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
            $smperiodo = new SuperiorModuloPeriodo();
            $smperiodo ->setSuperiorModuloTipo($smtipo);
            $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($form['idsip']));
            $smperiodo ->setHorasModulo($horasmodulo);
            $em->persist($smperiodo);
            $em->flush($smperiodo);

            if( $selectOld == false ){
                
                $superiorPeriodoTipoId = 0;
                switch ($superiorAcreditacionTipoId) {
                    case 1:
                        $superiorPeriodoTipoId = 2;       
                        break;
                    case 20:
                        $superiorPeriodoTipoId = 3;
                        break;
                    case 32:
                        $superiorPeriodoTipoId = ( $periodoTecnicoMedio == 1 ) ? 4 : 5;
                        break;
                    default:
                        $superiorPeriodoTipoId = 0;
                        break;
                }
    
                $superiorPeriodoTipo = $em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find( $superiorPeriodoTipoId );
    
                $smmp = new SuperiorMallaModuloPeriodo();
                $smmp->setSuperiorPeriodoTipo( $superiorPeriodoTipo );
                $smmp->setSuperiorModuloPeriodo( $smperiodo );
                $smmp->setFechaRegistro(new \DateTime('now'));
                $smmp->setFechaModificacion(new \DateTime('now'));
                $em->persist($smmp);
                $em->flush($smmp);

            }


            //  dump($smperiodo);die;
//             $em->getConnection()->commit();
//             $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');

//             $query = "select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
// 						, sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
// 						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
					
// 					where sat.id in (1,20,32) 
// 					and sest.id='".$idesp."'
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 							order by sat.id asc, sae.id, sest.id  ,sia.id desc

//     ";
// //        print_r($query);
// //        die;
//             $mallanivel = $db->prepare($query);
//             $params = array();
//             $mallanivel->execute($params);
//             $mallaniv = $mallanivel->fetchAll();
//             //dump($mallaniv);die;

//             $db = $em->getConnection();
//             $query = "select idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 							inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 								left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 									left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 										 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 					where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id='".$idesp."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
// ) dat
// group by  idsae,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip

        
//     ";

//             $especialidadnivel = $db->prepare($query);
//             $params = array();
//             $especialidadnivel->execute($params);
//             $po = $especialidadnivel->fetchAll();

//             if ($po){
//                 $exist = true;
//             }
//             else{
//                 $exist = false;
//             }
//             $db = $em->getConnection();
//             $query = " select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.periodo_medio, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
// 						select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
// 						, sia.id as idsia, sip.id as idsip
// from superior_acreditacion_especialidad sae
// 		inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 			inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 				inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 					inner join superior_institucioneducativa_acreditacion sia on sae.id = sia.acreditacion_especialidad_id
// 						 inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id 
// 						 inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 				    where sat.id in (1,20,32) 
// 					and sest.id='".$idesp."'
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 							order by sat.id asc, sae.id, sest.id  ,sia.id desc) as nivel
// left join (
// select idsae,idacr
// --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
// , string_agg(modulo, '|') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(periodo_medio::character varying, ',') as periodo_medio, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
// 	from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smmp.superior_periodo_tipo_id as periodo_medio, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
// 	from superior_acreditacion_especialidad sae
// 			inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
// 				inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
// 					inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
// 						inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
// 								inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
// 									left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
// 										left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
// 											inner join institucioneducativa_sucursal f on sia.institucioneducativa_sucursal_id=f.id
//                                                 left join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id  
// 						where sat.id in (1,20,32) 
// 					and sia.gestion_tipo_id= '".$this->session->get('ie_gestion')."'
// 					and sia.institucioneducativa_id ='".$this->session->get('ie_id')."'
// 					and sfat.codigo in ('18','19','20','21','22','23','24','25')
// 					and sest.id='".$idesp."'
// 					and f.periodo_tipo_id='".$this->session->get('ie_per_cod')."'
// 					and f.sucursal_tipo_id ='".$this->session->get('ie_subcea')."'
// 					and smt.esvigente =true
//                     order by smmp.superior_periodo_tipo_id
// 	) dat
// 	group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
// ) as v on v.idacr = nivel.idacreditacion
        
//     ";
//             $final = $db->prepare($query);
//             $params = array();
//             $final->execute($params);
//             $mallafinal = $final->fetchAll();

            $em->getConnection()->commit();
            // $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');

            $modules = $this->findModulesBySest( $idesp );

            $superiorMallaModuloPeriodo = $this->checkIsNewMallaModuloPeriodo( $modules );

            return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
                'exist'     => $modules['exist'],
                'malla'     => $modules['po'],
                'mallafin'  => $modules['mallafinal'],
                'mallaniv'  => $modules['mallaniv'],
                'sw_esoficial'  => $modules['mallafinal'][0]['sw_esoficial'],
                'isSuperiorMallaModuloPeriodo'=>$superiorMallaModuloPeriodo
            ));


            // return $this->render('SieHerramientaAlternativaBundle:MallaTecnica:listModulos.html.twig', array(
            //     'exist' => $exist,
            //     'mallaniv' => $mallaniv,
            //     'mallafin' => $mallafinal,
            //     'malla' => $po
            // ));


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
