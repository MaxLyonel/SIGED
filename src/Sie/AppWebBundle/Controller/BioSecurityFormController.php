<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 

class BioSecurityFormController extends Controller{
    public $session;
    public $currentyear;
    public $userlogged;
     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }

    public function indexAction(Request $request){
        // get the rol user
        $rolUsuario = $this->session->get('roluser');

      	$em = $this->getDoctrine()->getManager();


                
        //validation if the user is logged
        // if (!isset($id_usuario)) {
        //     return $this->redirect($this->generateUrl('login'));
        // }    	
    	// dump($this->session->get('pathSystem'));die;
      	
      	// get Info aobout UE
      	$arrUe = array('sie'=>'','gestion'=>$this->session->get('currentyear'));
      	if($this->session->get('ie_id')>0){
      		$objInstitucionEdu = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
      		$arrUe['sie']=$objInstitucionEdu->getId();
      		$arrUe['gestion']=$this->session->get('currentyear');
      	}



        return $this->render($this->session->get('pathSystem').':BioSecurityForm:index.html.twig', array(
                'arrUe' => $arrUe
            ));    
    }

    public function lookforInfoUEAction(Request $request){
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();

    	// get the data send
        $sie = $request->get('sie');
        $gestion = $request->get('year');

    	$DBInstitucionEdu = $this->getInfoUeInformation($gestion, $sie);

    	// dump($DBInstitucionEdu);die;
        
        $response->setStatusCode(200);
        
        $response->setData(array(
        	'DBInstitucionEdu'=>$DBInstitucionEdu[0], 
        	'swExistinfoUE' => sizeof(($DBInstitucionEdu)>0)?true:false,
        ));
       
        return $response;      	
    }

	private function getInfoUeInformation($ges, $sie){
		$em = $this->getDoctrine()->getManager();
          $query = $em->getConnection()->prepare("
                    select a.id as cod_ue_id,a.institucioneducativa as desc_ue,b.sucursal_tipo_id as sub_cea,b.gestion_tipo_id as gestion_id,2 as operativo_id,b.periodo_tipo_id as periodo_id
                    ,b.nombre_subcea,b.telefono1,b.telefono2,b.referencia_telefono2,b.fax,b.email,b.casilla,c.ci_director,c.director, case c.item_director when 1 then 'SI' else 'NO' end as item_director
                    ,b.cod_cerrada_id/*, b.turno_tipo_id as turno_id, iec.turno*/, iec.turno as turno_abrv,current_date as fecha_consolidacion,a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id
                    ,a.dependencia_tipo_id as cod_dependencia_id,a.convenio_tipo_id as cod_convenio_id,d.cod_dep as id_departamento,d.des_dep as desc_departamento
                    ,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
                    ,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,b.zona,b.direccion,d.cod_dis as cod_distrito,d.des_dis as distrito
                    ,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, case b.cod_cerrada_id when 10 then 'NO' else 'SI' end as institucioneducativa_cerrado
                    ,dt.dependencia, case d.desc_area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as desc_area
                    from (
                                    institucioneducativa a 
                                    inner join dependencia_tipo as dt on dt.id = a.dependencia_tipo_id
                                    inner join institucioneducativa_sucursal b on a.id=b.institucioneducativa_id 
                                    --inner join turno_tipo as tt on tt.id = b.turno_tipo_id
                                    inner join (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,desc_area
                                                                                    from jurisdiccion_geografica a 
                                                                                    inner join (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001, e.area2001 as desc_area
                                                                                                                                    from (select id,codigo as cod_dep,lugar_tipo_id,lugar	from lugar_tipo where lugar_nivel_id=1) a 
                                                                                                                                    inner join (select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                                                                                                                                    inner join (select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                                                                                                                                    inner join (select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id
                                                                                                                                    inner join (select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                                                                                                                                    ) b on a.lugar_tipo_id_localidad=b.id
                                                                                                                                    inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                                                                                    ) d on a.le_juridicciongeografica_id=d.cod_le
                                    ) 
                                    left join (select institucioneducativa_id, carnet as ci_director,paterno||' '||materno||' '||nombre as director,cargo_tipo_id as item_director from maestro_inscripcion a 
                                                                                    inner join persona b on a.persona_id=b.id 
                                                                                    where a.gestion_tipo_id=".$ges." and a.institucioneducativa_id=".$sie." and cargo_tipo_id in (1,12) limit 1
                                                                            ) c on a.id=c.institucioneducativa_id
                                    left join (
                                        select string_agg(turno,'-' order by turno_id) as turno, institucioneducativa_id from (
                                            select turno, case turno when 'M' then 1 when 'T' then 2 when 'N' then 3 else 0 end as turno_id, institucioneducativa_id from (
                                            select unnest(string_to_array(string_agg(distinct case tt1.abrv when 'MTN' then 'M-T-N' when 'MN' then 'M-N' else tt1.abrv end,'-'),'-','')) as turno, iec1.institucioneducativa_id from institucioneducativa_curso as iec1 
                                            inner join estudiante_inscripcion as ei1 on ei1.institucioneducativa_curso_id = iec1.id
                                            inner join turno_tipo as tt1 on tt1.id = iec1.turno_tipo_id
                                            where iec1.gestion_tipo_id = ".$ges." and iec1.institucioneducativa_id in (".$sie.")
                                            group by iec1.institucioneducativa_id
                                            ) as v
                                            group by institucioneducativa_id, turno
                                            order by turno_id
                                        ) as vv
                                        group by institucioneducativa_id
                                    ) as iec on iec.institucioneducativa_id = a.id
                    where a.id=".$sie." and b.gestion_tipo_id=".$ges.";
                ");
            
            $query->execute();
            $ueEntity = $query->fetchAll();		

            return $ueEntity;


	}    

}
