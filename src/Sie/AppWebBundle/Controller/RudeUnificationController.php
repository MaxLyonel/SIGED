<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Entity\EstudianteBack;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;


class RudeUnificationController extends Controller{

    private $session;
    public $inicialPrimaria;
    public $inicialPrimariaCase2;
    public $currentyear;
    public $userlogged;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->inicialPrimaria = false;
        $this->inicialPrimariaCase2 = false;
        $this->unificationNormal = false;
        $this->unificationForeign = false;
        $this->unificationForeignCase2 = false;        
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }    
    

    public function indexAction(Request $request){

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        // db conexion
        $em = $this->getDoctrine()->getManager();
        // ini vars
         $arrStudent = array( 
                            'rudea' => '',
                            'rudeb' => '',
                            'messageqa' => '',
                        ); 
        //get info 
        $form = $request->get('form');
        if($form){
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rude']));
                if(!$student){
                    $arrStudent['messageqa'] = 'Rudes no encontrados para unificar.';
                    $vala = '';
                    $valb = '';
                }else{
                    $students = $em->createQueryBuilder()
                                ->select('DISTINCT e.codigoRude')
                                ->from('SieAppWebBundle:Estudiante','e')
                                ->where('e.paterno = :paterno')
                                ->andWhere('e.materno = :materno')
                                ->andWhere('e.nombre = :nombre')
                                ->andWhere('e.fechaNacimiento = :fechaNacimiento')
                                ->setParameter('paterno', $student->getPaterno())
                                ->setParameter('materno', $student->getMaterno())
                                ->setParameter('nombre', $student->getNombre())
                                ->setParameter('fechaNacimiento', $student->getFechaNacimiento())
                                ->getQuery()
                                ->getResult();

                    if(sizeof($students)<=1){
                        $arrStudent = array( 
                            'rudea' => '',
                            'rudeb' => '',   
                            'messageqa' => 'No existen dos rudes para Unificar.',
                        ); 
                    }else{
                         $arrStudent = array( 
                            'rudea' => $students[0]['codigoRude'],
                            'rudeb' => $students[1]['codigoRude'],
                            'messageqa' => '',   
                        );
                    }
            }
        }

        return $this->render('SieAppWebBundle:RudeUnification:index.html.twig', array(
                'rudeStudent' => $arrStudent
            ));    
    }
    
    public function lookforStudentsHistoryAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        // get the send data
        $rudea = mb_strtoupper($request->get('rudea')) ;
        $rudeb = mb_strtoupper($request->get('rudeb')) ;
        // creete db conexion
        $em = $this->getDoctrine()->getManager();
         $swresponse=true;
         $swhistory=true;
         $message=false;
         $status='success';
         $code = 200;
         $student = array();
         $arrMessage = array();
        // validate the cod RUDE
        $rudea = trim($rudea);
        $rudeb = trim($rudeb);
        if ($rudea == $rudeb) {            
             $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Los códigos rudes deben ser distintos.',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;

        }


        // get the data students by rudes
        $studenta = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudea));
        if(!$studenta){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante con código Rude: '.$rudea.' no existe',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;
        }else{
            $arrStudenta = array(
                'id' => $studenta->getId(),
                'codigoRude' => $studenta->getCodigoRude(),
                'paterno' => $studenta->getPaterno(),
                'materno' => $studenta->getMaterno(),
                'nombre' => $studenta->getNombre(),
                'fechaNac' => $studenta->getfechaNacimiento()->format('d-m-Y'),
                
            );
        }
        $studentb = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeb));
        if(!$studentb){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante con código Rude: '.$rudeb.' no existe',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;            
        }else{
            $arrStudentb = array(
                'id' => $studentb->getId(),
                'codigoRude' => $studentb->getCodigoRude(),
                'paterno' => $studentb->getPaterno(),
                'materno' => $studentb->getMaterno(),
                'nombre' => $studentb->getNombre(),
                'fechaNac' => $studentb->getfechaNacimiento()->format('d-m-Y'),
            );
        }

        // check if the students data personal if egual
        if(      (trim($studenta->getNombre()) == trim($studentb->getNombre()))
                && (trim($studenta->getPaterno()) == trim($studentb->getPaterno()))
                && (trim($studenta->getMaterno()) == trim($studentb->getMaterno()))
                && (trim($studenta->getgeneroTipo()) == trim($studentb->getgeneroTipo()))
                && (trim($studenta->getfechaNacimiento()->format('Y-m-d')) == trim($studentb->getfechaNacimiento()->format('Y-m-d')))
            ) {
        }else {

            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Los datos del Estudiante no son iguales',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );


            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;
        }


        // get the students history
        $dataInscriptiona = $this->getHistorial($rudea);
        $dataInscriptionb = $this->getHistorial($rudeb);

        // validate the histories info inscription
        if(sizeof($dataInscriptiona)==0 || sizeof($dataInscriptionb)==0){

             $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'No presentan historial de Inscripciones',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;

        }

        // valdiate DIPLOMAS
        $tramitea=$this->get('seguimiento')->getDiploma($rudea);
        $tramiteb=$this->get('seguimiento')->getDiploma($rudeb);
        
        if($tramitea || $tramiteb){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante cuenta con tramite de DIPLOMAS',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );
            $swresponse = false;
            $arrMessage[]='Estudiante cuenta con registro de DIPLOMAS';
            /*$response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;*/
        }

        // validate JUEGOS
        $juegosa=$this->get('seguimiento')->getJuegos($rudea);
        $juegosb=$this->get('seguimiento')->getJuegos($rudeb);
        if($juegosa || $juegosb){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante cuenta con registro en JUEGOS',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );
            $swresponse = false;
            $arrMessage[]='Estudiante cuenta con registro en JUEGOS';

            // $response->setStatusCode(200);
            // $response->setData($arrResponse);
            // return $response;
        }

        // validate OLIMPIADAS
        $olimpiadasa=$this->get('seguimiento')->getOlimpiadas($rudea);
        $olimpiadasb=$this->get('seguimiento')->getOlimpiadas($rudeb);
        if($olimpiadasa || $olimpiadasb){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante cuenta con registro en OLIMPIADAS',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );
            $swresponse = false;
            $arrMessage[]='Estudiante cuenta con registro en OLIMPIADAS';

            // $response->setStatusCode(200);
            // $response->setData($arrResponse);
            // return $response;
        }
        // validate BACHILLER DESTACADO
        $bdestacadoa=$this->get('seguimiento')->getBachillerDestacado($rudea);
        $bdestacadob=$this->get('seguimiento')->getBachillerDestacado($rudeb);
        if($bdestacadoa || $bdestacadob){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante cuenta con registro en BACHILLER DESTACADO',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );
            $swresponse = false;
            $arrMessage[]='Estudiante cuenta con registro en BACHILLER DESTACADO';

            // $response->setStatusCode(200);
            // $response->setData($arrResponse);
            // return $response;
        }

        if(($studenta->getSegipId() == 1 || $studentb->getSegipId() == 1)){
            if ($studenta->getSegipId() == 1 and $studentb->getSegipId() == 0){
                $corr = $rudea;
                $inc = $rudeb;

                $segipmessage = '<strong>¡TOME NOTA!: </strong></br>-El <strong>RUDE: '. $corr .'</strong> cuenta con el Carnet de Identidad validado por Segip.</br>-Si elige el <strong>RUDE: '.$inc.'</strong> actualize los datos personales del estudiante/participante para su correspondiente validación por Segip.'; 
            } 
            if ($studentb->getSegipId() == 1 and $studenta->getSegipId() == 0){
                $corr = $rudeb;
                $inc = $rudea;

                $segipmessage = '<strong>¡TOME NOTA!: </strong></br>-El <strong>RUDE: '. $corr .'</strong> cuenta con el Carnet de Identidad validado por Segip.</br>-Si elige el <strong>RUDE: '.$inc.'</strong> actualize los datos personales del estudiante/participante para su correspondiente validación por Segip.'; 
            }
            $swresponse=true;
            $swhistory=true;
            $status='success';
            $code = 200;
            $student = array();
            
        }

        // this is the new by krlos to do the new action when the level is INI 1,2 & PRI 1
        /*$arrLevelsAllows = array(111,112,121,122);*/
        $arrLevelsAllows = array(11,12,13);
        $insriptinoStudentA = $this->getCurrentInscriptionsByGestoinValida($rudea, $this->session->get('currentyear'));
        $insriptinoStudentB = $this->getCurrentInscriptionsByGestoinValida($rudeb, $this->session->get('currentyear'));
        
        // get student history on Regular
         $inscriptionsA =$this->get('funciones')->getAllInscriptionRegular($rudea);
         $inscriptionsB =$this->get('funciones')->getAllInscriptionRegular($rudeb);
        // validation to INI 1,2 & PRI 1

        if(
            in_array($insriptinoStudentA['nivelId'], $arrLevelsAllows) &&
            in_array($insriptinoStudentB['nivelId'], $arrLevelsAllows) &&
            $insriptinoStudentA['nivelId'].$insriptinoStudentA['gradoId'] == $insriptinoStudentB['nivelId'].$insriptinoStudentB['gradoId'] && (sizeof($inscriptionsA)==1 || sizeof($inscriptionsB)==1)
        ){
            if($insriptinoStudentA['sie'] ==  $insriptinoStudentB['sie']){
                $this->inicialPrimaria = true;
            }else{
                //do the unifcation
                $this->inicialPrimariaCase2 = true;
            }
        }else{

            // SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON ESTADOS SIMILARES EN LA MISMA GESTION
            $validaEstadosRegular = $this->getVerificaEstadosGestion($rudea,$rudeb);
            if (count($validaEstadosRegular) > 0) {

                $arrResponse = array(
                'status'          => 'error',
                'code'            => 400,
                'message'         => "Se ha detectado inconsistencia de datos :".$validaEstadosRegular[0]['subsistema']." ".$validaEstadosRegular[0]['observacion']." ".$validaEstadosRegular[0]['gestion'],
                'swresponse' => false,
                'swhistory' => false,
                'dataHistoryA' => array(),
                'dataHistoryB' => array(),
                );

                $swresponse = false;
                $arrMessage[]="Se ha detectado inconsistencia de datos :".$validaEstadosRegular[0]['subsistema']." ".$validaEstadosRegular[0]['observacion']." ".$validaEstadosRegular[0]['gestion'];

                // $response->setStatusCode(200);
                // $response->setData($arrResponse);
                // return $response;
                
            }

            //SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON DOBLES INSCRIPCIONES EN LA MISMA UE Y GESTION
            $validaDobleInscripcionRegular = $this->getVerificaDobleInscripcion($rudea,$rudeb);
            if (count($validaDobleInscripcionRegular) > 0) {

                $arrResponse = array(
                'status'          => 'error',
                'code'            => 400,
                'message'         => "Se ha detectado inconsistencia de datos :".$validaDobleInscripcionRegular[0]['subsistema']." ".$validaDobleInscripcionRegular[0]['observacion']." en la gestión: ".$validaDobleInscripcionRegular[0]['gestion']." SIE:". $validaDobleInscripcionRegular[0]['institucioneducativa_id'],
                'swresponse' => false,
                'swhistory' => false,
                'dataHistoryA' => array(),
                'dataHistoryB' => array(),
                );

                $swresponse = false;
                $arrMessage[]="Se ha detectado inconsistencia de datos :".$validaDobleInscripcionRegular[0]['subsistema']." ".$validaDobleInscripcionRegular[0]['observacion']." en la gestión: ".$validaDobleInscripcionRegular[0]['gestion']." SIE:". $validaDobleInscripcionRegular[0]['institucioneducativa_id'];

                // $response->setStatusCode(200);
                // $response->setData($arrResponse);
                // return $response;
                
            }
            // dump($insriptinoStudentA);
            // dump($insriptinoStudentB);die;
            $arrMatricula = array(1,7);
            // dump($insriptinoStudentA['matriculaInicio']);
            // dump($insriptinoStudentB['matriculaInicio']);
            // die;
            if(in_array($insriptinoStudentA['matriculaInicio'] , $arrMatricula ) && in_array($insriptinoStudentB['matriculaInicio'] , $arrMatricula ) 
                // && ($insriptinoStudentA['sie'] ==  $insriptinoStudentB['sie'] && $insriptinoStudentA['gestion'] ==  $insriptinoStudentB['gestion'])
        ) {
                $this->unificationNormal = true;
          }else{
                $this->unificationForeign = true;
            }

        }


        $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swresponse' => $swresponse,
            'swhistory' => $swhistory,
            'dataHistoryA' => $dataInscriptiona,
            'dataHistoryB' => $dataInscriptionb,
            'studentA' => $arrStudenta,
            'studentB' => $arrStudentb,
            'unificationIniPri' => $this->inicialPrimaria,
            'unificationIniPriCase2' => $this->inicialPrimariaCase2,
            'unificationNormal' => $this->unificationNormal,
            'unificationForeign' => $this->unificationForeign,
            'unificationForeignCase2' => $this->unificationForeignCase2,
            'rudea' => $rudea,
            'rudeb' => $rudeb,           
            'currentyear' => $this->currentyear,           
            'arrMessage' => $arrMessage,           
        );

        // dump($arrResponse);die;

      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
  
    }
    private function getCurrentInscriptionsByGestoinValida($id, $gestion) {
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('ei.id as studentInscriptionId','n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(ei.estadomatriculaInicioTipo) as matriculaInicio')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 4,6 ))
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>0){
                // validate to status 6 = no incorporado check if it has only one register like history
                if($objInfoInscription[0]['estadoMatriculaId']!=4){
                    if(( $objInfoInscription[0]['estadoMatriculaId']==6 && (sizeof($this->get('funciones')->getAllInscriptionRegular($id)) )==1 )){
                        return $objInfoInscription[0];
                    }else{
                        return false;
                    }
                }                
              return $objInfoInscription[0];
            }else
              return false;

      }     



    public function getHistorial($rude){


        $dataInscriptionR = array();
        $dataInscriptionA = array();
        $dataInscriptionE = array();
        $dataInscriptionP = array();

        $dataInscriptionAll = array();
        
        $em = $this->getDoctrine()->getManager();


        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();

        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[] = $inscription;
                    break;
                case '5':
                    if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 14)$bloquep ='Segundo';
                    if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 15)$bloquep = 'Tercero';
                    if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 16)$bloquep = 'Quinto';
                    if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 17)$bloquep = 'Sexto';
                    $dataInscriptionP[] = array(
                      'gestion'=> $inscription['gestion_tipo_id_raep'],
                      'institucioneducativa'=> $inscription['institucioneducativa_raep'],
                      'partp'=> ($inscription['parte_p']==1 ||$inscription['parte_p']==2)?'Antiguo':'Actual',
                      'bloquep'=> $bloquep,
                      'fini'=> $inscription['fech_ini_p'],
                      'ffin'=> $inscription['fech_fin_p'],
                      'curso'=> $inscription['institucioneducativa_curso_id_raep'],
                      'matricula'=> $inscription['estadomatricula_p'],
                    );
                    break;
            }
        }

        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;


        $dataInscriptionAll = array(

            'dataInscriptionR' => $dataInscriptionR,
            'dataInscriptionA' => $dataInscriptionA,
            'dataInscriptionE' => $dataInscriptionE,
            'dataInscriptionP' => $dataInscriptionP

        );

        
        return $dataInscriptionAll;
    }

    public function getVerificaEstadosGestion($rudecor,$rudeinc){
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select cast('Regular' as varchar) as subsistema, cast('Mismo estado en la misma gestión' as varchar) as observacion, gestion_rude_b as gestion, estadomatricula_rude_b as estadomatricula from (
            select * from (            
            select gestion_tipo_id_raep as gestion_rude_b, estadomatricula_tipo_id_fin_r as estadomatricula_rude_b
            from sp_genera_estudiante_historial('".$rudeinc."') 
            where institucioneducativa_tipo_id_raep = 1
            and estadomatricula_tipo_id_fin_r not in ('6','9')
            ) b 
            INNER JOIN
            (
            select gestion_tipo_id_raep as gestion_rude_c, estadomatricula_tipo_id_fin_r as estadomatricula_rude_c
            from sp_genera_estudiante_historial('".$rudecor."') 
            where institucioneducativa_tipo_id_raep = 1
            and estadomatricula_tipo_id_fin_r not in ('6','9')
            ) c 
            ON b.gestion_rude_b = c.gestion_rude_c
            AND b.estadomatricula_rude_b = c.estadomatricula_rude_c) regular");

        $query->execute();
        return $query->fetchAll();
    }  
    public function getVerificaDobleInscripcion($rudea,$rudeb){
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select cast('Regular' as varchar) as subsistema, cast('Doble inscripcion en la misma Unidad Educativa ' as varchar) as observacion, gestion_rude_b as gestion,institucioneducativa_id_c as institucioneducativa_id,institucioneducativa_c as institucioneducativa,estadomatricula_rude_b,estadomatricula_rude_c,codigo_rude_b,codigo_rude_c
        from (
        select gestion_tipo_id_raep as gestion_rude_b, estadomatricula_fin_r as estadomatricula_rude_b,institucioneducativa_id_raep as institucioneducativa_id_b,institucioneducativa_raep as institucioneducativa_b,codigo_rude_raep as codigo_rude_b
        from sp_genera_estudiante_historial('". $rudea ."') 
        where institucioneducativa_tipo_id_raep = 1) b 
        INNER JOIN
        (select gestion_tipo_id_raep as gestion_rude_c, estadomatricula_fin_r as estadomatricula_rude_c,institucioneducativa_id_raep as institucioneducativa_id_c,institucioneducativa_raep as institucioneducativa_c,codigo_rude_raep as codigo_rude_c
        from sp_genera_estudiante_historial('". $rudeb ."') 
        where institucioneducativa_tipo_id_raep = 1
        ) c  ON b.gestion_rude_b = c.gestion_rude_c AND b.institucioneducativa_id_b=c.institucioneducativa_id_c");

        $query->execute();
        return $query->fetchAll();
    }

    public function doUnificationAction(Request $request){

        // get the send data
        $rudeCorrect = $request->get('rudeCorrect');
        $rudeWrong = $request->get('rudeWrong');
        $unificationIniPri = ($request->get('unificationIniPri')=='true')?true:false;
        $unificationIniPriCase2 = ($request->get('unificationIniPriCase2')=='true')?true:false;
        $unificationNormal = ($request->get('unificationNormal')=='true')?true:false;
        $unificationForeign = ($request->get('unificationForeign')=='true')?true:false;
        $swChanteStatusCorrectInscription = ($request->get('swChanteStatusCorrectInscription')=='true')?true:false;

        // set the ini var
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        $arrDataUnification = array();
        $arrInscriptionsWrong = array();
        $arrApoderadoInscripcion = array();

        //check about the history and status and levels
        $validaDobleInscripcionRegular = $this->getVerificaDobleInscripcion($rudeCorrect,$rudeWrong);
        if(sizeof($validaDobleInscripcionRegular) > 0 && $validaDobleInscripcionRegular[0]['gestion']!=$this->currentyear ){
            $status='error';
            $code = 400;            
            $swDoneUnification = false;
            $message="Se ha detectado inconsistencia de datos :".$validaDobleInscripcionRegular[0]['subsistema']." ".$validaDobleInscripcionRegular[0]['observacion']." en la gestión: ".$validaDobleInscripcionRegular[0]['gestion']." SIE:". $validaDobleInscripcionRegular[0]['institucioneducativa_id'];

            $arrResponse = array(
                    'status'          => $status,
                    'code'            => $code,
                    'messageDoneUnification' => $message,
                    'swDoneUnification' => $swDoneUnification,                    
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);

            return $response;
        }
        $validaEstadosRegular = $this->getVerificaEstadosGestion($rudeCorrect,$rudeWrong);
        if(sizeof($validaEstadosRegular) > 0 && $validaEstadosRegular[0]['gestion']!=$this->currentyear ){
            $status='error';
            $code = 400;            
            $swDoneUnification = false;
            $message="Se ha detectado inconsistencia de datos :".$validaEstadosRegular[0]['subsistema']." ".$validaEstadosRegular[0]['observacion']." ".$validaEstadosRegular[0]['gestion'];

            $arrResponse = array(
                    'status'          => $status,
                    'code'            => $code,
                    'messageDoneUnification' => $message,
                    'swDoneUnification' => $swDoneUnification,                    
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);

            return $response;
        }        
        // get the student info
        $studentinc = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeWrong));
        $studentcor = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeCorrect));

        /*get info about wrong student*/
        $arrStudentWrong = array(
            'codigoRude'=> $studentinc->getCodigoRude(),
            'paterno'=> $studentinc->getPaterno(),
            'materno'=> $studentinc->getMaterno(),
            'nombre'=> $studentinc->getNombre(),
            'ci'=> $studentinc->getCarnetIdentidad(),
            'complemento'=> $studentinc->getComplemento(),
            'genero'=> $studentinc->getGeneroTipo()->getId(),
            'oficilia'=> $studentinc->getOficialia(),
            'libro'=> $studentinc->getLibro(),
            'partida'=> $studentinc->getPartida(),
            'folio'=> $studentinc->getFolio(),
            'pais'=> $studentinc->getgeneroTipo()?$studentinc->getgeneroTipo()->getId():null,
            'lugarNacTipo'=> $studentinc->getLugarNacTipo()?$studentinc->getLugarNacTipo()->getId():null,
            'lugarProvNacTipo'=> $studentinc->getLugarProvNacTipo()?$studentinc->getLugarProvNacTipo()->getId():null,
            'localidadNac'=> $studentinc->getLocalidadNac(),
        );
        /*end get info about wrong student*/

        // get the inscription info
        $inscripinco = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('estudiante' => $studentinc), array('fechaInscripcion'=>'DESC'));

        $inscripcorr = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('estudiante' => $studentcor), array('fechaInscripcion'=>'DESC'));

        // dump($inscripinco[0]);
        // dump($inscripcorr);
        // die;

        $studentDatPerInco = $em->getRepository('SieAppWebBundle:EstudianteDatopersonal')->findBy(array('estudiante' => $studentinc));        
        $studentPnpRecSabInco = $em->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->findBy(array('estudiante' => $studentinc));
        

        //*************GENERANDO BACKUP DEL RUDE INCORRECTO
        $sqlb = "select * from sp_genera_repaldo_rude('".$rudeWrong."')";
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        //*************GENERANDO BACKUP DEL RUDE INCORRECTO
        
        try{

            

            // review in other relations
            //***********ESTUDIANTE DATOS PERSONALES
            $arrStudentDataPersonal = array();
            if ($studentDatPerInco) {
                foreach ($studentDatPerInco as $stuDatPer) {
                    $arrStudentDataPersonal[] = $stuDatPer;
                    $stuDatPer->setEstudiante($studentcor);
                    $em->persist($stuDatPer);
                    $em->flush();
                }                    
            }
            //***********PNP RECONOCIMIENTO SABERES
            $arrStudentPnpRecSab=array();
            if ($studentPnpRecSabInco) {
                foreach ($studentPnpRecSabInco as $stuPnpinco) {
                    $arrStudentPnpRecSab[] = $stuPnpinco;
                    $stuPnpinco->setEstudiante($studentcor);
                    $em->persist($stuPnpinco);
                    $em->flush();
                }                    
            }

            //***********to RUDE information
            $arrStudentRudeInfo=array();
            if(sizeof($inscripinco)>0){
                foreach ($inscripinco as $value) {
                    $objStudentRude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion' => $value->getId()));
                    if ($objStudentRude) {
                        foreach ($objStudentRude as $studentRude) {
                            $arrStudentRudeInfo[] = $studentRude;
                            $em->remove($studentRude);  
                        }   
                        $em->flush();                 
                    }                    
                }                
            }
            
            //***********to EstudianteDocumento information
            $EstudianteDocumento = $em->getRepository('SieAppWebBundle:EstudianteDocumento')->findBy(array('estudiante' => $studentinc)); 

            $arrEstudianteDocumento=array();
            if(sizeof($EstudianteDocumento)>0){
                foreach ($EstudianteDocumento as $value) {
                    $arrEstudianteDocumento[] = $value;
                     $em->remove($value);     
                }   
                $em->flush();                   
            }           

            //***********ELIMINAMOS ESTUDIANTE_HISTORIAL_MODIFICACION*******////
            $objStudentHistoryModification = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->findBy(array('estudiante' =>  $studentinc->getId()));
            
            $arrStudentHistoryModification = array();
            if(sizeof($objStudentHistoryModification)>0){
                foreach ($objStudentHistoryModification as $valueHistory) {
                    # code...
                    $arrStudentHistoryModification[] = $valueHistory;
                    $em->remove($valueHistory);
                }                
            }
            /*$students = $em->createQueryBuilder()
                ->delete('SieAppWebBundle:EstudianteHistorialModificacion','ehm')
                ->where('ehm.estudiante = :id')
                ->setParameter('id', $studentinc->getId())
                ->getQuery()
                ->execute();*/
            $inscriptionCorrect = $this->getCurrentInscriptionsByGestoinValida($rudeCorrect, $this->session->get('currentyear')); 

            // check the inscription info todo the remove or update ON UNIFICATION
            if($unificationIniPriCase2 && $swChanteStatusCorrectInscription){
                $objInscriptionCorrect = $this->getCurrentInscriptionsByGestoinValida($rudeCorrect, $this->session->get('currentyear'));
                $updateCurrenteInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objInscriptionCorrect['studentInscriptionId']);
                 $updateCurrenteInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
                 $em->flush();
            }   

            if(($inscripinco) && ($studentcor)){
                foreach ($inscripinco as $inscrip) {

                    $arrInscriptionsWrong[] = array(
                        'institucioneducativaCursoId'=>$inscrip->getInstitucioneducativaCurso()->getId(),
                    );

                    $objApoderadoInscripcions = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array(
                             'estudianteInscripcion' => $inscrip->getId()
                    ));
                    $arrApoderadoInscripcion = array();
                    if(sizeof($objApoderadoInscripcions)>0){

                        foreach ($objApoderadoInscripcions as $objApoderadoInscripcion) {
                              
                            // get backup fot ApoderadoInscripcion
                            $arrApoderadoInscripcion[] = array(
                                'idApoInsc'=>$objApoderadoInscripcion->getId(),
                                'obs'=>$objApoderadoInscripcion->getObs(),
                                'esValidado'=>$objApoderadoInscripcion->getEsValidado(),
                                'personaId'=>$objApoderadoInscripcion->getPersona()->getId(),
                                'apoderadoTipoId'=>$objApoderadoInscripcion->getApoderadoTipo()->getId(),
                                'estudianteInscripcion'=>$objApoderadoInscripcion->getEstudianteInscripcion()->getId(),
                                'fechaRegistro'=>$objApoderadoInscripcion->getFechaRegistro(),
                                'fechaModificacion'=>$objApoderadoInscripcion->getFechaModificacion(),
                            );

                            if($objApoderadoInscripcion->getEsValidado()){
                                // look for the current inscription correct rude
                                $arrayConditionInscription = array(
                                    'codigoRude'=>$rudeCorrect,
                                    'matriculaId'=>4,
                                    'gestion'=>$this->currentyear,
                                );
                                $arrCurrenteInscription = $this->get('funciones')->getCurrentInscriptionByRudeAndGestionAndMatricula($arrayConditionInscription);
                                if(sizeof($arrCurrenteInscription)>0){
                                    $objApoderadoInscripcionCorrect = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
                                        'estudianteInscripcion' => $arrCurrenteInscription[0]['studenInscriptionId']
                                    ));

                                    if(sizeof($objApoderadoInscripcionCorrect)>0){
                                        if(!$objApoderadoInscripcionCorrect->getEsValidado()){
                                            // do update on apoderadoInscripcion
                                            $objApoderadoInscripcionCorrect->setEsValidado($objApoderadoInscripcion->getEsValidado());
                                            $objApoderadoInscripcionCorrect->setPersona($objApoderadoInscripcion->getPersona() );

                                            $em->persist($objApoderadoInscripcionCorrect);
                                            $em->flush();
                                        }

                                    }else{
                                        // do insert on the apo;deradoInscripcion table
                                        $nuevoApoderado = new ApoderadoInscripcion();
                                        $nuevoApoderado->setApoderadoTipo($objApoderadoInscripcion->getApoderadoTipo());
                                        $nuevoApoderado->setPersona($objApoderadoInscripcion->getPersona() );
                                        $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrCurrenteInscription[0]['studenInscriptionId']));
                                        $nuevoApoderado->setObs('');
                                        $nuevoApoderado->setEsValidado($objApoderadoInscripcion->getEsValidado());
                                        $nuevoApoderado->setFechaRegistro(new \DateTime('now'));
                                        $em->persist($nuevoApoderado);
                                        $em->flush(); 
                                    }                                    
                                }

                            }
                            
                            $em->remove($objApoderadoInscripcion);
                        }
                    }

                    //***********to estudiante_inscripcion_extranjero
                    $objEstudianteInscripcionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion' => $inscrip->getId() ));
                       
                    $arrEstudianteInscripcionExtranjero=array();
                    if(sizeof($objEstudianteInscripcionExtranjero)>0){
                        $arrEstudianteInscripcionExtranjero = array(
                            'InstitucioneducativaOrigen'=>$objEstudianteInscripcionExtranjero->getInstitucioneducativaOrigen(),
                            'CursoVencido'=>$objEstudianteInscripcionExtranjero->getCursoVencido(),
                            'CursoVencido'=>$objEstudianteInscripcionExtranjero->getCursoVencido(),
                            'RutaImagen'=>$objEstudianteInscripcionExtranjero->getRutaImagen(),
                            'EstudianteInscripcion'=>$objEstudianteInscripcionExtranjero->getEstudianteInscripcion(),
                            'PaisTipo'=>$objEstudianteInscripcionExtranjero->getPaisTipo(),

                        );
                      
                        $em->remove($objEstudianteInscripcionExtranjero);     
                        
                    } 

                    if($unificationIniPri){ 
                        $em->remove($inscrip);  
                    }else{

                        $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscrip->getInstitucioneducativaCurso());                        

                        if($unificationNormal || $unificationForeign || $unificationIniPriCase2){
                            if($unificationIniPriCase2 && $inscrip->getEstadomatriculaTipo()->getId() == 4 && !$swChanteStatusCorrectInscription){
                                // to change the matricula student
                                $inscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
                                $em->remove($inscrip);

                            }  
                                                          
                            if($unificationNormal){
                                if(!$inscriptionCorrect ){

                                    if(
                                        // $inscriptionCorrect['nivelId'].$inscriptionCorrect['gradoId'] == $objCurso->getNivelTipo()->getId().$objCurso->getGradoTipo()->getId() &&
                                        $inscriptionCorrect['gestion'] == $objCurso->getGestionTipo()->getId() &&
                                         $inscriptionCorrect['sie'] ==  $objCurso->getInstitucioneducativa()->getId()
                                    ){
                                       $em->remove($inscrip);    
                                   }
                                }

                            }
                            
                            if($unificationForeign){
                                // check the foreign inscription to the update
                                if($inscriptionCorrect){
                                        if( $inscriptionCorrect['gestion'] == $objCurso->getGestionTipo()->getId() && $inscriptionCorrect['nivelId'].$inscriptionCorrect['gradoId']>=$objCurso->getNivelTipo()->getId().$objCurso->getGradoTipo()->getId() ){
                                            $em->remove($inscrip);
                                        }
                                        if( $inscriptionCorrect['gestion']== $objCurso->getGestionTipo()->getId() &&  $objCurso->getNivelTipo()->getId().$objCurso->getGradoTipo()->getId()>$inscriptionCorrect['nivelId'].$inscriptionCorrect['gradoId']){
                                             $inscripincoCorrect = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscriptionCorrect['studentInscriptionId']);
                                            $em->remove($inscripincoCorrect);
                                            $inscrip->setEstudiante($studentcor);
                                        }  
                                }
                            }
                        }

                        if($this->session->get('currentyear') != $objCurso->getGestionTipo()->getId() )
                            $inscrip->setEstudiante($studentcor);
                    }
                    $em->flush();
                }
                //die;
            } 
            
            
          
            
            //***********REGISTRANDO CAMBIO DE ESTADO EN CONTROL DE CALIDAD       
            if ($studentinc) {
                $antes = $studentinc->getId(); 
                $arrResult = $this->getDataLogEstudiante($antes);                
                

                $this->get('funciones')->setLogTransaccion(
                   $studentcor->getId(),
                   'estudiante',
                   'D',
                   'UNIFICATION',
                   $arrResult,
                   $antes,
                   'SIGED',
                   json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                );
            }
            // get all data about the RUDE wrong
            $arrDataUnification = array(
                'studentInfo'=>$arrStudentWrong,
                'inscriptionInfo'=>$arrInscriptionsWrong,
                'arrStudentDataPersonal'=>$arrStudentDataPersonal,
                'arrStudentPnpRecSab'=>$arrStudentPnpRecSab,
                'arrStudentRudeInfo'=>$arrStudentRudeInfo,
                'arrStudentHistoryModification'=>$arrStudentHistoryModification,
                'arrEstudianteDocumento'=>$arrEstudianteDocumento,
                'arrApoderadoInscripcion'=>$arrApoderadoInscripcion,
            );            
            //guardado de log antiguo de datos de unificacion            
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcorr[0]->getInstitucioneducativaCurso()->getId());
            $ur = new UnificacionRude();
            $ur->setRudeinco($rudeWrong);
            $ur->setRudecorr($rudeCorrect);                
            $ur->setEstadomatriculaTipoInco($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripcorr[0]->getEstadomatriculaTipo()->getId()));
            $ur->setEstadomatriculaTipoCorr($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripcorr[0]->getEstadomatriculaTipo()->getId()));
            $ur->setGestionTipoCorr($em->getRepository('SieAppWebBundle:GestionTipo')->find($curso->getGestionTipo()->getId()));
            $ur->setGestionTipoInco($em->getRepository('SieAppWebBundle:GestionTipo')->find($curso->getGestionTipo()->getId()));
            $ur->setSiecorr($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($curso->getInstitucioneducativa()->getId()));
            $ur->setSieinco($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($curso->getInstitucioneducativa()->getId()));                
            $ur->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            $ur->setFechaRegistro(new \DateTime('now'));
            $ur->setJsontxt(json_encode($arrDataUnification));
            $em->persist($ur);
            $em->flush();


            //****PARA EL PROCESO DE CALIDAD******//
            $valproceso = $em->createQueryBuilder()
                                ->select('v')
                                ->from('SieAppWebBundle:ValidacionProceso','v')
                                ->where('v.esActivo is false')
                                ->where('v.validacionReglaTipo = 8 and v.solucionTipoId = 2 and (v.llave = :llave1 or v.llave = :llave2)')
                                ->orWhere('v.validacionReglaTipo in (24,25,26) and v.solucionTipoId = 0 and (v.llave = :llave3 or v.llave = :llave4)')
                                ->setParameter('llave1', $rudeCorrect)
                                ->setParameter('llave2', $rudeWrong)
                                ->setParameter('llave3', $rudeCorrect.'|'.$rudeWrong)
                                ->setParameter('llave4', $rudeWrong.'|'.$rudeCorrect)
                                ->getQuery()
                                ->getResult();
            
            if($valproceso){
                foreach($valproceso as $v){
                    $v->setEsActivo(true);
                    $em->flush();
                }
                $gestion = $valproceso[0]->getGestionTipo()->getId();
            }else{
                $gestion = $inscripcorr[0]->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            }
            
            //**PARA LA REGLA 8 La/el estudiante cuenta con más de un RUDE***/
            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_dos_RUDES (:tipo, :rude, :param, :gestion)');
            $query->bindValue(':tipo', '2');
            $query->bindValue(':rude', $rudeCorrect);
            $query->bindValue(':param', '');
            $query->bindValue(':gestion', $gestion);
            $query->execute();
            $resultado = $query->fetchAll();

            //**PARA LA REGLA 26 Estudiantes con similitud de nombres y fecha de nacimiento***/
            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_similitud_nombres_certf_nac (:tipo, :codigo_rude)');
            $query->bindValue(':tipo', '2');
            $query->bindValue(':codigo_rude', $rudeCorrect);
            $query->execute();
            $resultado = $query->fetchAll();
            //****FIN DEL PROCESO DE CALIDAD******//                        




            $this->get('funciones')->setLogTransaccion(
                   $studentcor->getId(),
                    'estudiante',
                    'U',
                    '',
                    $arrDataUnification,
                    $arrDataUnification,
                    'SIGED',
                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );
            
            //ELIMINANDO RUDE ANTIGUO            
            $em->remove($studentinc);
            $em->flush();

            $em->getConnection()->commit();
            
            $status='success';
            $code = 200;
            $message = 'Unificacion realizado correctamente';
            $swDoneUnification = true;

            $arrResponse = array(
                    'status'          => $status,
                    'code'            => $code,
                    'messageDoneUnification' => $message,
                    'swDoneUnification' => $swDoneUnification,                    
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);

            return $response;


        } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $message = 'Se ha detectado inconsistencia de datos. Comuniquese con la nacional para resolver el caso.';
                    $this->addFlash('notihistory', $message);            
                    return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
        }

        

        



    }    

    private function getDataLogEstudiante($idEstudiante){        
        $em = $this->getDoctrine()->getManager();
        $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
        $arrResult = [];        
        if (isset($objInscription)) {
            $arrResult['Id'] = $objInscription->getId();
            $arrResult['codigo_rude'] = $objInscription->getCodigoRude();
            $arrResult['carnet_identidad'] = $objInscription->getCarnetIdentidad();
            $arrResult['paterno'] = $objInscription->getPaterno();
            $arrResult['materno'] = $objInscription->getMaterno();
            $arrResult['nombre'] = $objInscription->getNombre();
            $arrResult['fecha_nacimiento'] = $objInscription->getFechaNacimiento();
        }
        return $arrResult;
    }            

}
