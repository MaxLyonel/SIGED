<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\Institucioneducativa; 
use Sie\AppWebBundle\Entity\EveEstudianteInscripcionEvento; 
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog; 
use Sie\AppWebBundle\Entity\HabextrEstudianteInscripcion; 

use Sie\AppWebBundle\Entity\HabextrFaseTipo;
use Sie\AppWebBundle\Entity\HabextrAreasCamposTipo;


class TalentoInscriptionController extends Controller
{
 /*   public function index1Action(){
        
        return $this->render('SieHerramientaBundle:TalentoInscription:index.html.twig', array(
                // ...
            ));    
    }
*/

    public $session;
    public $limitDay;
    public function __construct() {
        $this->session = new Session();
        $this->limitDay = '20-06-2023';
    }       

    public function indexAction(){
        
        $ie_id=$this->session->get('ie_id');
        
            $swregistry = false;
            $id_usuario = $this->session->get('userId');
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }        
            $disableElement=0;
            if(in_array($this->session->get('roluser'),array(9))){
                $sie=$ie_id=$this->session->get('ie_id');
                $disableElement=1;
            }else{
                if(in_array($this->session->get('roluser'),array(7,8,10))){
                    $sie='';
                }
            }


            return $this->render('SieHerramientaBundle:TalentoInscription:index.html.twig',array(
                'codsie'=>$sie,
                'disableElement'=>$disableElement,
                
            ));        
    }

    public function findUEDataAction(Request $request){
        // get the vars send        
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $sie);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        
        $existUE = 0;
        $arrModalidades = array();        
        // $arrLevel = array(
        //     array('id'=>12,'level'=>'Educación Primaria Comunitaria Vocacional'),
        //     array('id'=>13,'level'=>'Educación Secundaria Comunitaria Productiva'),
        // ); 

        $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;   
        // start this secction validate the last day to report the inscription INFO
        $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);          
        // end this secction validate the last day to report the inscription INFO                     

        // get fase and Area
        $query="select * from habextr_fase_tipo where id = 1";
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrFases = $statement->fetchAll();

        $query="select * from habextr_areas_campos_tipo";
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrAreas = $statement->fetchAll();

        // if ($aTuicion[0]['get_ue_tuicion'] == true){
        if (1){
            $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            
            if(sizeof($objUE)>0){
                $existUE = 1;
                $objModalidades = $em->getRepository('SieAppWebBundle:EveModalidadesTipo')->findAll();
                if(sizeof($objModalidades) > 0 ){
                    foreach ($objModalidades as $value) {
                        $arrModalidades[]=array('id'=>$value->getId(), 'modalidad'=>$value->getDescripcion() );
                    }
                }
                $arrResponse = array(
                    'sie'                 => $objUE->getId(),
                    'institucioneducativa'=> $objUE->getInstitucioneducativa(),
                    'existUE'         => $existUE,                                    
                    'arrFases'         => $arrFases,                
                    'arrAreas'         => $arrAreas,                
                    'swcloseevent'         => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );               
            }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'No tiene tuición sobre la institución educatia o no existe codigo SIE ',
                    'existUE'             => $existUE,
                    'arrFases'         => $arrFases,
                    'arrAreas'         => $arrAreas,
                    'swcloseevent'            => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );   
            }            
        }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'N',
                    'existUE'         => $existUE,
                    'arrFases'         => $arrFases,
                    'arrAreas'         => $arrAreas,
                    'swcloseevent'         => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                ); 
        }

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;        
    }

    public function getAllInfoUEAction(Request $request){
    	// get the vars send        
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        if($institucion){
        	
	        $em = $this->getDoctrine()->getManager();
	        //get the Niveles

	        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
	        $query = $entity->createQueryBuilder('iec')
	                ->select('(iec.nivelTipo)')
	                ->where('iec.institucioneducativa = :sie')
	                ->andwhere('iec.gestionTipo = :gestion')
	                ->andwhere('iec.nivelTipo IN (:nivel)')
	                ->setParameter('sie', $sie)
	                // ->setParameter('gestion', $this->session->get('currentyear') )
	                ->setParameter('gestion', $this->session->get('currentyear') )
	                ->setParameter('nivel', array(12,13))
	                ->orderBy('iec.nivelTipo', 'ASC')
	                ->distinct()
	                ->getQuery();
	        $aNiveles = $query->getResult();
	        
	        if($aNiveles){
		        $arrniveles = array();
		        foreach ($aNiveles as $nivel) {
		            $arrniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
		        }
			
		    }


	     }        
        // end   look for the level, grado, parallel & turno


        // if ($aTuicion[0]['get_ue_tuicion'] == true){
        if (1){
            $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            
            if(sizeof($objUE)>0){
                $existUE = 1;
                $objModalidades = $em->getRepository('SieAppWebBundle:EveModalidadesTipo')->findAll();
                if(sizeof($objModalidades) > 0 ){
                    foreach ($objModalidades as $value) {
                        $arrModalidades[]=array('id'=>$value->getId(), 'modalidad'=>$value->getDescripcion() );
                    }
                }
                $arrResponse = array(
                    'sie'                 => $objUE->getId(),
                    'institucioneducativa'=> $objUE->getInstitucioneducativa(),
                    'existUE'         => $existUE,                
                    'arrModalidades'  => $arrModalidades,                
                    'arrLevel'         => $arrniveles,                
                    // 'swcloseevent'         => $swcloseevent,
                    // 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );               
            }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'No tiene tuición sobre la institución educatia o no existe codigo SIE ',
                    'existUE'             => $existUE,
                    'arrModalidades'      => $arrModalidades,
                    'arrLevel'            => $arrniveles,                    
                    // 'swcloseevent'            => $swcloseevent,
                    // 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );   
            }            
        }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'N',
                    'existUE'         => $existUE,
                    'arrModalidades'         => $arrModalidades,
                    'arrLevel'         => $arrniveles,
                    // 'swcloseevent'         => $swcloseevent,
                    // 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                ); 
        }

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;        	
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function getGradoAction(Request  $request) {
    	
    	// ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $idnivel = $request->get('levelId');
        $gestionselected = $this->session->get('currentyear') ;

	    $agrados = array();

        if($idnivel>0){
	        //get grado
	        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
	        $query = $entity->createQueryBuilder('iec')
	                ->select('(iec.gradoTipo)')
	                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
	                ->where('iec.institucioneducativa = :sie')
	                ->andWhere('iec.nivelTipo = :idnivel')
	                ->andwhere('iec.gestionTipo = :gestion')
	                // ->andwhere('iec.gradoTipo IN (:agrados)')
	                ->setParameter('sie', $sie)
	                ->setParameter('idnivel', $idnivel)
	                ->setParameter('gestion', $gestionselected)
	                // ->setParameter('agrados', array(5,6))
	                ->distinct()
	                ->orderBy('iec.gradoTipo', 'ASC')
	                ->getQuery();
	        $aGrados = $query->getResult();

	        $agrados = array();
	        
	        foreach ($aGrados as $grado) {
	        	$agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
	        }

	    }

      
      $arrResponse = array(
      	'arrGrado' => $agrados,
      	'existUE' => 1,

      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
        
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function getParallelAction(Request $request) {
    	// ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('levelId');
        $grado = $request->get('gradoId');
        $gestion = $this->session->get('currentyear') ;
		//get paralelo
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        $aparalelos = array();
        foreach ($aParalelos as $paralelo) {
        	$aparalelos[$paralelo[1]] = array('id'=>$paralelo[1], 'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo() );    
        }

      $arrResponse = array(
      	'arrParalelo' => $aparalelos,
      	'existUE' => 1,
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function getTurnoAction(Request $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('levelId');
        $grado = $request->get('gradoId');
        $paralelo = $request->get('parallelId');
        $gestion = $this->session->get('currentyear') ;
		//get turno
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[] =array('id'=>$turno[1], 'turno'=> $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno());
        }

      $arrResponse = array(
      	'arrTurno' => $aturnos,
      	'existUE' => 1,
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }


    public function dataSelectedAction(Request $request){
        // dump($request);die;
        // get the vars send        
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $levelId = $request->get('levelId');
        $gradoId = $request->get('gradoId');
        $parallelId = $request->get('parallelId');
        $turnoId = $request->get('turnoId');
        $year = $this->session->get('currentyear');

		$habextrFaseId = $request->get('habextrFaseId');
		$habextrAreaId = $request->get('habextrAreaId');
        
        
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $levelLabel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($levelId);
        $gradoLabel = $em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoId);
        $parallelLabel = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($parallelId);
        $turnoLabel = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turnoId);
        
        $areaLabel = $em->getRepository('SieAppWebBundle:HabextrAreasCamposTipo')->find($habextrAreaId);
        $faseLabel = $em->getRepository('SieAppWebBundle:HabextrFaseTipo')->find($habextrFaseId);

        // get rules of inscription
        $query = "select * from habextr_areas_campos_tipo where id = ".$habextrAreaId;
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrAreaRule = $statement->fetchAll();
        
        //start get the students
        $query="
                select e.carnet_identidad, e.complemento ,e.codigo_rude ,e.paterno ,e.materno ,e.nombre ,e.genero_tipo_id , ei.id as inscriptionId, gt.genero, e.fecha_nacimiento
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join genero_tipo gt on (e.genero_tipo_id=gt.id)
                where ic.nivel_tipo_id =$levelId and ic.grado_tipo_id =$gradoId and ic.paralelo_tipo_id = '".$parallelId."' and ic.turno_tipo_id = $turnoId and ic.institucioneducativa_id =$sie and ic.gestion_tipo_id =$year  and  ei.estadomatricula_tipo_id = 4 
                order by e.paterno, e.materno, e.nombre
        ";


        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();
        
        $arrStudentsList = array();

        foreach ($arrStudents as $value) {

        	// get the student yearOld
	        $sql = "select public.sp_obtener_edad(to_date('".date('Y-m-d', strtotime($value['fecha_nacimiento']))."','YYYY-MM-DD'),to_date('2023-06-15','YYYY-MM-DD'))";
	        $query = $em->getConnection()->prepare($sql);
	        $query->execute();
	        $dataStudent = $query->fetch();        	
	        
	        if($dataStudent['sp_obtener_edad']<=$arrAreaRule[0]['edad']){
	        	
	            $query=" select * from habextr_estudiante_inscripcion
	            where habextr_fase_tipo_id =".$habextrFaseId." and habextr_areas_campos_tipo_id =".$habextrAreaId." and estudiante_inscripcion_id = ".$value['inscriptionid']."  ";

	            $statement = $em->getConnection()->prepare($query);
	            $statement->execute();
	            $arrStudentsRegistered = $statement->fetchAll();
	            if(sizeof($arrStudentsRegistered)>0){
	            }else{
	                $arrStudentsList[] = $value;
	            }
	        }
        }

        //end   get the students
      
        $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;            
        // start this secction validate the last day to report the inscription INFO
        $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);  
        // end this secction validate the last day to report the inscription INFO        
        // get students data
        $arrEveStudentsTalento = $this->getAllRegisteredInscription($sie,$habextrFaseId,$habextrAreaId);

        $arrResponse = array(
        	'levelLabel'=>$levelLabel->getNivel(),
        	'gradoLabel'=>$gradoLabel->getGrado(),
        	'parallelLabel'=>$parallelLabel->getParalelo(),
        	'turnoLabel'=>$turnoLabel->getTurno(),

        	'faseLabel'=>$faseLabel->getFase(),
        	'areaLabel'=>$areaLabel->getAreasCampos(),
        	'ruleYearOld'=>$arrAreaRule[0]['edad'],

        	'arrStudents' => $arrStudentsList,
            'arrEveStudentsTalento' => $arrEveStudentsTalento,
            'existSelectData' => 1,    
            'swcloseevent'    => $swcloseevent,   
            'existStudent'         => 1,    
        ); 

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    public function validateTutorAction(Request $request){

        $dataStudent = $request->get('dataStudent');
        $apoderadoInput = $request->get('apoderado');
        $em = $this->getDoctrine()->getManager();

        $query="
                select p.*, ai.id as apoderadoInscripId
                from apoderado_inscripcion ai
                inner join persona p on (ai.persona_id = p.id)
                inner join estudiante_inscripcion ei on (ai.estudiante_inscripcion_id = ei.id)
                inner join estudiante e on (ei.estudiante_id = e.id)
                inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
                where e.codigo_rude =  '".$dataStudent['codigo_rude']."'  and iec.gestion_tipo_id = 2020
        ";

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrDataStudents = $statement->fetchAll();
        
        $apoderadoOutput = array();
        if(sizeof($arrDataStudents)>0){
            $apoderadoOutput = array(
              "paterno" => $arrDataStudents[0]['paterno'],
              "materno" => $arrDataStudents[0]['materno'],
              "nombre" => $arrDataStudents[0]['nombre'],
              "carnet" => $arrDataStudents[0]['carnet'],
              "complemento" => $arrDataStudents[0]['complemento'],
              "fecNacimiento" =>date('d-m-Y',strtotime($arrDataStudents[0]['fecha_nacimiento']) ) ,
              
              
            );
            $apoderadoOutput = array_map("strtoupper", $apoderadoOutput);

        }
        $apoderadoInput['fecNacimiento'] = str_replace('/', '-', $apoderadoInput['fecNacimiento']);
        
        $apoderadoInput = array_map("strtoupper", $apoderadoInput);
        
        // check if the values of parent/tutor are the same
        $swparent = 0;
        if(($apoderadoInput == $apoderadoOutput)){
            $apoderadoOutput['apoderadoinscripid'] = $arrDataStudents[0]['apoderadoinscripid'];
            $swparent = 1;
        }
            $apoderadoOutput['apoderadoinscripid'] = $arrDataStudents[0]['apoderadoinscripid'];

        $arrResponse = array(
            'apoderadoOutput' => $apoderadoOutput,
            'swparent' => $swparent,
        ); 
                        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          


    }

     function strtoupper($ele){        
        return strtoupper($ele);
    }

    public function saveStudentAction(Request  $request){

        $dataInfoUE = json_decode($request->get('infoUE'),true);
        $em = $this->getDoctrine()->getManager();
        
        if(isset($_FILES['informe'])){
            $file = $_FILES['informe'];

            if ($file['name'] != "") {
                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = $dataInfoUE['dataStudent']['inscriptionid'].'-'.date('YmdHis').'.'.$extension;

                // GUARDAMOS EL ARCHIVO
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/habiltalen/' . $dataInfoUE['sie'];
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $archivador = $directorio.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }
                
                // CREAMOS LOS DATOS DE LA IMAGEN
                $informe = array(
                    'name' => $name,
                    'type' => $type,
                    'tmp_name' => 'nueva_ruta',
                    'size' => $size,
                    'new_name' => $new_name
                );
            }else{
                $informe = null;
            }
        }else{
            $informe = null;
        }

        $CenEstudianteInscripcionObj = new HabextrEstudianteInscripcion();
        
        $CenEstudianteInscripcionObj->setEstudianteInscripcionId(($dataInfoUE['dataStudent']['inscriptionid']));
        $CenEstudianteInscripcionObj->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataInfoUE['sie']));
        $CenEstudianteInscripcionObj->setDocadjunto($dataInfoUE['sie']."/".$new_name);
        $CenEstudianteInscripcionObj->setTitulo(mb_strtoupper($dataInfoUE['inscriptionData']['titulo'], 'utf-8'));
        $CenEstudianteInscripcionObj->setUrldocumento($dataInfoUE['inscriptionData']['url']);
        $CenEstudianteInscripcionObj->setDescripcion(mb_strtoupper($dataInfoUE['inscriptionData']['descripcion'], 'utf-8'));

        $CenEstudianteInscripcionObj->setHabextrFaseTipo($em->getRepository('SieAppWebBundle:HabextrFaseTipo')->find($dataInfoUE['habextrFaseId']));
        $CenEstudianteInscripcionObj->setHabextrAreasCamposTipo($em->getRepository('SieAppWebBundle:HabextrAreasCamposTipo')->find($dataInfoUE['habextrAreaId']));
        
        $CenEstudianteInscripcionObj->setFechaRegistro(new \DateTime('now'));

        $em->persist($CenEstudianteInscripcionObj);
        $em->flush();

        $arrEveStudents = $this->getAllRegisteredInscription($dataInfoUE['sie'],$dataInfoUE['habextrFaseId'],$dataInfoUE['habextrAreaId']);
        
        $arrResponse = array(
            
            'arrEveStudentsTalento' => $arrEveStudents,
            
        ); 

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
        die;
    }   

    private function getLastDayRegistryOpeCheesEventStatus($limitDay){
        $today = date('d-m-Y');
        $swcloseevent =  (strtotime($today) >= strtotime($limitDay))?1:0;  
        return $swcloseevent;
    }
    private function checkOperativeChees($sie){
        // create db conexion
        $em=$this->getDoctrine()->getManager();

          $objDownloadFilenewOpe = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
            'institucioneducativa'=>$sie,
            'institucioneducativaOperativoLogTipo'=>13,
            'gestionTipoId'=>$this->session->get('currentyear')
          ));

        return $objDownloadFilenewOpe;        
    }   


    private function getAllRegisteredInscription($sie, $faseId, $areaId){
        $em = $this->getDoctrine()->getManager();
        $query = "SELECT e.codigo_rude,e.paterno,e.materno,e.nombre,e.carnet_identidad,e.complemento,nt.nivel,gt.grado,pt.paralelo, eeie.id as eveinscriptionid,  concat('../../uploads/archivos/habiltalen/', docadjunto) as pathdoc, eeie.urldocumento
                FROM habextr_estudiante_inscripcion eeie
                inner join estudiante_inscripcion ei on (eeie.estudiante_inscripcion_id = ei.id)
                inner join estudiante e on (ei.estudiante_id = e.id)
                inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
                inner join nivel_tipo nt on (iec.nivel_tipo_id=nt.id)
                inner join grado_tipo gt on (iec.grado_tipo_id=gt.id)
                inner join paralelo_tipo pt on (iec.paralelo_tipo_id=pt.id)
                where eeie.habextr_fase_tipo_id = ". $faseId." and eeie.habextr_areas_campos_tipo_id= ". $areaId ." and iec.institucioneducativa_id = " . $sie ;

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrEveStudents = $statement->fetchAll();
         
        return $arrEveStudents;        

    }    


    public function closeEventGralAction(Request $request) {
        
        // get the send values
        $sie = $request->get('sie');        
        //conexion to DB
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();
        try {
          //save the log data
          
          
          $objDownloadFilenewOpe = $this->checkOperativeChees($sie);
          if(!is_object($objDownloadFilenewOpe)){
            $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
          }
          
          $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(13));
          $objDownloadFilenewOpe->setGestionTipoId($this->session->get('currentyear'));
          $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
          $objDownloadFilenewOpe->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
          $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
          $objDownloadFilenewOpe->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
          $objDownloadFilenewOpe->setDescripcion('habextr');
          $objDownloadFilenewOpe->setEsexitoso('t');
          $objDownloadFilenewOpe->setEsonline('t');
          $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
          $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
          $objDownloadFilenewOpe->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
          $em->persist($objDownloadFilenewOpe);
          $em->flush();
           // $em->getConnection()->commit();

          $swcloseevent = 1;

        } catch (Exception $e) {
            $swcloseevent = 0;
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }   

        $arrResponse = array(
            'sie'          => $sie,
            'swcloseevent' => $swcloseevent,
            'swcloseevent' => $swcloseevent,
            'urlreporte'=> $this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie))
        ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;                
    }

    public function removeInscriptionCensoAction(Request $request){
    	
        // get the send values
        $sie = $request->get('sie');
        $habextrFaseId = $request->get('habextrFaseId');
        $habextrAreaId = $request->get('habextrAreaId');
        $institucioneducativa = $request->get('institucioneducativa');
        $remoinscriptionid = $request->get('remoinscriptionid');
        // $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
    
        $existRemoveStudent=0;
        $objEveStudentInscription = $em->getRepository('SieAppWebBundle:HabextrEstudianteInscripcion')->find($remoinscriptionid);
        
        if(is_object($objEveStudentInscription) ){
            $em->remove($objEveStudentInscription);
            // $em->persist($objEveStudentInscription);
            $em->flush();
            $existRemoveStudent=1;
        }
        
        $arrEveStudents = $this->getAllRegisteredInscription($sie, $habextrFaseId,$habextrAreaId);

        $arrResponse = array(
            'sie'            => $sie,
            'arrEveStudents' => $arrEveStudents,
            'existRemoveStudent'         => $existRemoveStudent,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }



}
