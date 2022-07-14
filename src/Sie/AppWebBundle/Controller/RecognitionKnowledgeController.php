<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteNota;

class RecognitionKnowledgeController extends Controller{


    public $session;

    public function __construct() {
        $this->session = new Session();
    }	

    public function indexAction(){

        return $this->render('SieAppWebBundle:RecognitionKnowledge:index.html.twig', array(
                // ...
            ));    
    }

    private function validarSegip($form, $carnet){

        $arrParametros = array(       
        	'complemento'=>$form['complemento'],
            'primer_apellido'=>$form['paterno'],
            'segundo_apellido'=>$form['materno'],
            'nombre'=>$form['nombre'],
            'fecha_nacimiento'=>$form['fechaNacimiento']);

        if(strlen($form['extranjero'])!=0)
            $arrParametros['extranjero'] = $form['extranjero'];

        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['carnet'],$arrParametros,'prod', 'academico');    	

        if($answerSegip){
            return true;
        }

        return false;
    }    

    public function lookforCompetitorAction(Request $request){
        $response = new JsonResponse();
        // dump($request);die;
        // $apoderado = $request->get('apoderado', null);
        $estudiante = $request->get('estudiante', null);
        // dump($estudiante);
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];
        $opcion = $request->get('opcion', null);

        $em = $this->getDoctrine()->getManager();
        // VALIDAMOS DATOS DEL ESTUDIANTE
        
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                break;
            case 3:
                $nombre = mb_strtoupper($estudiante['nombre'], 'utf-8');
                $paterno = mb_strtoupper($estudiante['paterno'], 'utf-8');
                $materno = mb_strtoupper($estudiante['materno'], 'utf-8');
                $fechaNacimiento = $estudiante['fechaNacimiento'];

                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                    'nombre'=>$nombre,
                    'paterno'=>$paterno,
                    'materno'=>$materno,
                    'fechaNacimiento'=>new \DateTime($fechaNacimiento)
                ));
                break;
        }

//         dump($estudiante);
// dump(!is_object($estudiante));
// dump($estudiante);
// die;
		$dataInscriptionR = array();
		$dataInscriptionA = array();
		$dataInscriptionE = array();
		$sendStudent = array();

        if ($opcion == 1 && (!is_object($estudianteObj)))  {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del participante no son válidos'
            ]);
        }else{
        }

        // VERIFICAMOS SI LA OPCION ES POR CARNET PARA VALIDAR CON EL SEGIP
        if ($opcion == 3 && (!is_object($estudianteObj)) ) {
        	
            $validarSegip = $this->validarSegip($estudiante, $estudiante['carnet'].$estudiante['complemento']);

            if (!$validarSegip) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'Los datos del participante no son válidos por segip'
                ]);
            }else{
            	$sendStudent = $estudiante;
            	$sendStudent['codigoRude'] = '';
            	$dataInscriptionR = array();
            	$dataInscriptionA = array();
            	$dataInscriptionE = array();
            }
        }

        if(is_object($estudianteObj)){
            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $estudianteObj->getCodigoRude() . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
            $query->execute();
            
        $dataInscription = $query->fetchAll();
        
        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[$key] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[$key] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[$key] = $inscription;
                    break;
                case '5':
                    break;
            }
        }

        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        // $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;

            $sendStudent['codigoRude'] = $estudianteObj->getCodigoRude();
            $sendStudent['paterno'] = $estudianteObj->getPaterno();
            $sendStudent['materno'] = $estudianteObj->getMaterno();
            $sendStudent['nombre'] = $estudianteObj->getNombre();
            $sendStudent['carnet'] = $estudianteObj->getCarnetIdentidad();
            $sendStudent['complemento'] = $estudianteObj->getComplemento();
            $sendStudent['fechaNacimiento'] = $estudianteObj->getFechaNacimiento()->format('m-d-Y');        	
        }


        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'statusApoderado'=>'success',
                'msgApoderado'=>'informacion del participante',
                'statusEstudiante'=>'success',
                'sendStudent'=>$sendStudent,
                'dataInscriptionR'=>$dataInscriptionR,
                'dataInscriptionA'=>$dataInscriptionA,
                'dataInscriptionE'=>$dataInscriptionE,
            )
        ]);
    }  

    public function infoCentroAction(Request $request){
  		$response = new JsonResponse();

  		$institucionEducativaId = $this->session->get('ie_id');
  		$gestionId = $this->session->get('currentyear');
  		
  		$entidadEspecialidadTipo = $this->getEspecialidadCentroEducativoTecnica(40730321, $gestionId);


        return $response->setData([
            'status'=>'success',
            'datos'=>array(                
                'entidadEspecialidadTipo'=>$entidadEspecialidadTipo,
            )
        ]);    	

    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las especialidades de un centro de educacion alternativa segun la gestión
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getEspecialidadCentroEducativoTecnica($institucionEducativaId, $gestionId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                    select distinct sest.id as especialidad_id, sest.especialidad as especialidad
                    from superior_facultad_area_tipo as sfat
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                    where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                    order by sest.especialidad
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    public function getNivelCentroEducativoTecnica($institucionEducativaId, $gestionId, $especialidadId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select distinct sat.codigo as nivel_id, sat.acreditacion as nivel
                from superior_facultad_area_tipo as sfat
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                order by sat.codigo
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }        


}
