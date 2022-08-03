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
use Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridad; 
use Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntas; 
use Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntasBrigada; 
// use \Datetime;
use DateTime;
use DatePeriod;
use DateInterval;

class BioSecurityFormController extends Controller{
    public $session;
    public $currentyear;
    public $userlogged;
    public $mounth;
     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
        $this->mounth = array('nothing','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
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
      		$arrUe['mounth']= $this->mounth[date('n')];
      		$arrUe['week']=date('W');

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
    	$objectoBioInstitucion = $this->getBioInstitucionEdu($gestion, $sie);
    
    	// dump($DBInstitucionEdu);die;
        
        $response->setStatusCode(200);
        
        $response->setData(array(
        	'DBInstitucionEdu'=>$DBInstitucionEdu[0], 
        	'objectoBioInstitucion'=>$objectoBioInstitucion, 
        	'swExistinfoUE' => sizeof(($DBInstitucionEdu)>0)?true:false,
        ));
       
        return $response;      	
    }

	private function get_dates($year = 0, $week = 0){

		$nuevaFecha = mktime(0,0,0,date('m'),date('d'),date('Y')); 
		$diaDeLaSemana = date("w", $nuevaFecha);
		$nuevaFecha = $nuevaFecha - ($diaDeLaSemana*24*3600); 
		$fecha1=date ("Y-m-d",$nuevaFecha);		
		$fecha7=date ("Y-m-d",($nuevaFecha+6*24*3600));
		return [$fecha1, $fecha7];
		
		// date_default_timezone_set('America/La_Paz');
		// // dump($year, $week);die;
	 //    // Se crea objeto DateTime del 1/enero del año ingresado
	 //    $fecha = DateTime::createFromFormat('Y-m-d', $year . '-1-2');
	 //    dump($fecha);
	 //    $w = $fecha->format('W'); // Número de la semana
	 //    dump($w, $week);die;
	 //    // Se agrega semanas hasta igualar
	 //    while ($week > $w) {
	 //        $fecha->add(DateInterval::createfromdatestring('+1 week'));
	 //        $w = $fecha->format('W');
	 //    }
	 //    // Ahora $fecha pertenece a la semana buscada
	 //    // Se debe obtener el primer y el último día

	 //    // Si $fecha no es el primer día de la semana, se restan días
	 //    if ($fecha->format('N') > 1) {
	 //        $format = '-' . ($fecha->format('N') - 1) . ' day';
	 //        $fecha->add(DateInterval::createfromdatestring($format));
	 //    }
	 //    // Ahora $fecha es el primer día de esa semana

	 //    // Se clona la fecha en $fecha2 y se le agrega 6 días
	 //    $fecha2 = clone($fecha);
	 //    $fecha2->add(DateInterval::createfromdatestring('+6 day'));

	 //    // Devuelve un array con ambas fechas
	 //    return [$fecha, $fecha2];   
	}    

    private function getBioInstitucionEdu($gestion, $sie){
    	$em = $this->getDoctrine()->getManager();

    	$query = $em->getConnection()->prepare("
    		select *
    		from Bio_Institucioneducativa_Bioseguridad ibio
    		inner join institucioneducativa inst on (ibio.institucioneducativa_id = inst.id)
    		where ibio.gestion_tipo_id = ".$gestion." and ibio.institucioneducativa_id = ".$sie." ");
    	$query->execute();
        $objectoBioInstitucion = $query->fetchAll();		

        
    	
    	// $objectoBioInstitucion = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findBy(array('gestionTipo'=>$gestion, 'institucioneducativa'=>$sie));

    	return (sizeof($objectoBioInstitucion)>0)?$objectoBioInstitucion:false;
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

	public function saveQuestion1Action(Request $request){
		// ini vars
		$em = $this->getDoctrine()->getManager();
		// get the vars send
		$answer1 = $request->get('answer1', null);

		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$nummount = date('m');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));

		if(!$objBioInstitucioneducativaBioseguridad){
			$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
			$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		}else{
			$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		}
		$objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		$objBioInstitucioneducativaBioseguridad->setMes($nummount);
		$objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		$objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		$em->persist($objBioInstitucioneducativaBioseguridad);
		
		
		// check if exists data to question 1
		$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array('bioInstitucioneducativaBioseguridad' => $objBioInstitucioneducativaBioseguridad->getId(), 'bioCuestionarioTipo' => 1 ));
		if(!$objBioInstitucioneducativaBioseguridadPreguntas){
			$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
			$objBioInstitucioneducativaBioseguridadPreguntas->setFechaRegistro(new \DateTime('now'));
			$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
			$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find(1));
		}else{
			$objBioInstitucioneducativaBioseguridadPreguntas->setFechaModificacion(new \DateTime('now'));
		}
		$objBioInstitucioneducativaBioseguridadPreguntas->setRespSiNo($answer1['swquestion1']);
		if($answer1['swquestion1'] =='false'){
			$objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto($answer1['nooneone']);
		}else{
            $objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto($answer1['answeroneone']);
        }

		$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);
		

		// to save sub question 1.2 - 1.4
		$answerSW1 = $answer1['swquestion1'];		
		unset($answer1['swquestion1']);
		

		if($answerSW1 =='false'){
			// delete sub questions answers one 
			$objBioInstitucioneducativaBioseguridadPreguntasBrigada = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntasBrigada')->findBy(array(
					'bioInstitucioneducativaBioseguridadPreguntas'=>$objBioInstitucioneducativaBioseguridadPreguntas->getId(),
				));
			if($objBioInstitucioneducativaBioseguridadPreguntasBrigada){
				foreach ($objBioInstitucioneducativaBioseguridadPreguntasBrigada as $value) {					
					$em->remove($value);
				}
				$em->flush();
			}

		}else{
			$numBrigada = $answer1['answeroneone'];
			unset($answer1['answeroneone']);
			
			ksort($answer1);
			$currentKey = key($answer1);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($numQuestionOne as $value) {
				$objBioInstitucioneducativaBioseguridadPreguntasBrigada = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntasBrigada')->findOneBy(array(
					'bioInstitucioneducativaBioseguridadPreguntas'=>$objBioInstitucioneducativaBioseguridadPreguntas->getId(),
					'bioCuestionarioBrigadaTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntasBrigada){
					$objBioInstitucioneducativaBioseguridadPreguntasBrigada = new BioInstitucioneducativaBioseguridadPreguntasBrigada();
					$objBioInstitucioneducativaBioseguridadPreguntasBrigada->setBioInstitucioneducativaBioseguridadPreguntas($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->find($objBioInstitucioneducativaBioseguridadPreguntas->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntasBrigada->setBioCuestionarioBrigadaTipo($em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntasBrigada->setRespSiNo(($answer1[$currentKey]=='SI')?true:false);
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntasBrigada);

				next($answer1);
				$currentKey = key($answer1);
			}

			

		}

		$em->flush();
		die;

	}
	public function saveQuestion2Action(Request $request){
		// get vars send
		$answer2 = $request->get('answer2', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 2 ));

		if($objBioCuestionarioTipo){


			ksort($answer2);
			$currentKey = key($answer2);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setRespSiNo(($answer2[$currentKey]=='SI')?true:false);
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer2);
				$currentKey = key($answer2);
			}			


		}else{}
		
		$em->flush();
		die;

	}
	public function saveQuestion3Action(Request $request){

		// get vars send
		$answer3 = $request->get('answer3', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 3 ));

		if($objBioCuestionarioTipo){


			ksort($answer3);
			$currentKey = key($answer3);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setRespSiNo(($answer3[$currentKey]=='SI')?true:false);
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer3);
				$currentKey = key($answer3);
			}			


		}else{}
		
		$em->flush();
		
		die;

	}
	public function saveQuestion4Action(Request $request){

		// get vars send
		$answer4 = $request->get('answer4', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 4 ));

		if($objBioCuestionarioTipo){


			ksort($answer4);
			$currentKey = key($answer4);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setRespSiNo(($answer4[$currentKey]=='SI')?true:false);
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer4);
				$currentKey = key($answer4);
			}			


		}else{}
		
		$em->flush();
		
		die;
	}
	public function saveQuestion5Action(Request $request){

		// get vars send
		$answer5 = $request->get('answer5', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 5 ));

		if($objBioCuestionarioTipo){


			ksort($answer5);
			$currentKey = key($answer5);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto(($answer5[$currentKey]));
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer5);
				$currentKey = key($answer5);
			}			


		}else{}
		
		$em->flush();
		
		die;

	}
	public function saveQuestion6Action(Request $request){

		// get vars send
		$answer6 = $request->get('answer6', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 6 ));

		if($objBioCuestionarioTipo){


			ksort($answer6);
			$currentKey = key($answer6);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto(($answer6[$currentKey]));
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer6);
				$currentKey = key($answer6);
			}			


		}else{}
		
		$em->flush();
		die;

	}
	public function saveQuestion7Action(Request $request){

		// get vars send
		$answer7 = $request->get('answer7', null);
		// create ini var
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 7 ));

		if($objBioCuestionarioTipo){


			ksort($answer7);
			$currentKey = key($answer7);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto(($answer7[$currentKey]));
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer7);
				$currentKey = key($answer7);
			}			


		}else{}
		
		$em->flush();
		
		die;

	}
	public function saveQuestion8Action(Request $request){

		// get vars send
		$answer8 = $request->get('answer8', null);
		// create ini var
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$sie = ($request->get('sie'));
		$numWeek = date('W');
		$currentyear = $this->currentyear;
		// check if exists data to the SIE
		$objBioInstitucioneducativaBioseguridad = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('gestionTipo'=>$currentyear, 'semana'=> $numWeek, 'institucioneducativa' => $sie ));
		
		// if(!$objBioInstitucioneducativaBioseguridad){
		// 	$objBioInstitucioneducativaBioseguridad = new BioInstitucioneducativaBioseguridad();
		// 	$objBioInstitucioneducativaBioseguridad->setFechaRegistro(new \DateTime('now'));
		// }else{
		// 	$objBioInstitucioneducativaBioseguridad->setFechaModificacion(new \DateTime('now'));
		// }
		// $objBioInstitucioneducativaBioseguridad->setSemana($numWeek);
		// $objBioInstitucioneducativaBioseguridad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($currentyear));
		// $objBioInstitucioneducativaBioseguridad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));

		// $em->persist($objBioInstitucioneducativaBioseguridad);
		$objBioCuestionarioTipo = $em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->findBy(array('bioClasificadorPreguntaTipo' => 8 ));

		if($objBioCuestionarioTipo){


			ksort($answer8);
			$currentKey = key($answer8);
			$numQuestionOne = $em->getRepository('SieAppWebBundle:BioCuestionarioBrigadaTipo')->findAll();
			foreach ($objBioCuestionarioTipo as $value) {

				$objBioInstitucioneducativaBioseguridadPreguntas = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridadPreguntas')->findOneBy(array(
					'bioInstitucioneducativaBioseguridad'=>$objBioInstitucioneducativaBioseguridad->getId(),
					'bioCuestionarioTipo'=>$value->getId()
				));
				// check if exist sub question to question 1
				if(!$objBioInstitucioneducativaBioseguridadPreguntas){
					$objBioInstitucioneducativaBioseguridadPreguntas = new BioInstitucioneducativaBioseguridadPreguntas();
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioInstitucioneducativaBioseguridad($em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->find($objBioInstitucioneducativaBioseguridad->getId()));
					$objBioInstitucioneducativaBioseguridadPreguntas->setBioCuestionarioTipo($em->getRepository('SieAppWebBundle:BioCuestionarioTipo')->find($value->getId()));
				}else{

				}
				$objBioInstitucioneducativaBioseguridadPreguntas->setPregTexto(($answer8[$currentKey]));
				$em->persist($objBioInstitucioneducativaBioseguridadPreguntas);

				next($answer8);
				$currentKey = key($answer8);
			}			


		}else{}
		
		$em->flush();

		$objectoBioInstitucion = $this->getBioInstitucionEdu($currentyear, $sie);

        $response->setStatusCode(200);
        
        $response->setData(array(        	
        	'objectoBioInstitucion'=>$objectoBioInstitucion, 
        ));
       
        return $response;  		


	}

	public function showAnswerAction(Request $request){


		$response = new JsonResponse();
		$data = $request->get('data', null);
		$em = $this->getDoctrine()->getManager();

	

		switch ($data['option']) {
			case 1:
				# code...
				$query = "
		    		select *
		    		from Bio_Institucioneducativa_Bioseguridad ibio
		    		inner join Bio_Institucioneducativa_Bioseguridad_preguntas ibiop on (ibio.id = ibiop.bio_institucioneducativa_bioseguridad_id)
		    		where ibio.gestion_tipo_id = ".$data['year']." and ibio.institucioneducativa_id = ".$data['sie']." and ibio.mes = ".$data['month']." and ibio.semana =".$data['week']." and ibiop.bio_cuestionario_tipo_id=".$data['option'];	
		    		
		    	$query = $em->getConnection()->prepare($query);
		    	$query->execute();
		        $objectoBioInstitucion = $query->fetchAll();			
				// dump($objectoBioInstitucion);die;
		        // {"swquestion1":true,"answeroneone":"2","a121":"SI","a122":"SI","a124":"SI","a123":"SI","a125":"SI","a131":"SI","a132":"SI","a133":"SI","a141":"SI","a142":"SI","a143":"SI","a144":"SI","a145":"SI"}
		        if(sizeof($objectoBioInstitucion)>0){
		        	if($objectoBioInstitucion[0]['resp_si_no']){
		        		$answer1['swquestion1']=$objectoBioInstitucion[0]['resp_si_no'];
		        		$answer1['answeroneone']=$objectoBioInstitucion[0]['preg_texto'];

		        		$query = "
			    		select *
			    		from Bio_Institucioneducativa_Bioseguridad_preguntas_brigada ibiob
			    		where ibiob.bio_institucioneducativa_bioseguridad_preguntas_id = ".$objectoBioInstitucion[0]['id']." order by bio_cuestionario_brigada_tipo_id ASC";
			    		// dump($query);
				    	$query = $em->getConnection()->prepare($query);
				    	$query->execute();
				        $objectoBioInstitucionquest = $query->fetchAll();
				        if(sizeof($objectoBioInstitucionquest)>0){
				        	foreach ($objectoBioInstitucionquest as $value) {
				        		$answer1['vi'.$value['bio_cuestionario_brigada_tipo_id']] = ($value['resp_si_no'])?'SI':'NO';
				        	}
				        }
				        
				        // dump($objectoBioInstitucionquest);die;

			        	// $answer1 =array('swquestion1'=>$objectoBioInstitucion[0]['resp_si_no'], 'nooneone'=>$objectoBioInstitucion[0]['preg_texto']);
			        }else{
			        	$answer1 =array('swquestion1'=>$objectoBioInstitucion[0]['resp_si_no'], 'nooneone'=>$objectoBioInstitucion[0]['preg_texto']);
			        }

		        }else{
		        	$answer1 = array();
		        }

				break;
			// case 2:

			// 	break;
			
			default:
				# code...
				$objData = $em->getRepository('SieAppWebBundle:BioInstitucioneducativaBioseguridad')->findOneBy(array('institucioneducativa'=>$data['sie'],'semana'=>$data['week']));


				$query = "
		    		select * 
					from Bio_Institucioneducativa_Bioseguridad_preguntas a 
					left join bio_cuestionario_tipo bct on (a.bio_cuestionario_tipo_id = bct.id)
					where  a.Bio_Institucioneducativa_Bioseguridad_id = ".$objData->getId()." and bct.bio_clasificador_pregunta_tipo_id =  ".$data['option'];				
		    	$query = $em->getConnection()->prepare($query);
		    	$query->execute();
		        $objectAnswer = $query->fetchAll();	
		        // dump($data['option']);
		        // dump($objectAnswer);
		        foreach ($objectAnswer as $value) {
		        	$answer1['vi'.$value['bio_cuestionario_tipo_id']] = ($data['option']>=5)?$value['preg_texto']:[($value['resp_si_no'])?'SI':'NO'];
		        }			
		        // dump($objectAnswer);die;			
				break;
		}
		// dump($answer1);die;
		
        $response->setStatusCode(200);
        
        $response->setData(array(        	
        	'answer1'=>$answer1, 
        ));
       
        return $response;  			
	}

	// private function getAnswer



}
