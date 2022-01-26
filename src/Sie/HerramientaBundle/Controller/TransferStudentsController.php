<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcionCambioestado;

class TransferStudentsController extends Controller{
    public $session;
    public $currentyear;
    public $userlogged;
    public $em;

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

        return $this->render('SieHerramientaBundle:TransferStudents:index.html.twig', array(
                'sie'=> ($this->session->get('ie_id') >0)?$this->session->get('ie_id'):''
            ));    
    }

    public function lookforStudentsAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $sie =  $request->get('sie');
        $gestion =  $this->currentyear;
        
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListTransferStudents($sie, $gestion);
        $arrStudents = array();
        if(sizeof($objStudents)>0){
           
            foreach ($objStudents as $student) {
                $arrInfoTransfer = json_decode($student['json'],true) ;
               
                $objCursoInfo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($student['cursoId']);
                $objMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($student['matriculaId']);
                $objUEOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrInfoTransfer['unidadOrigen']['sie']);
                $objUECurrent = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrInfoTransfer['unidadActual']['sie']);
                
                $objInscriptionOld = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoTransfer['unidadOrigen']['eInsId']);
                $objInscriptionCurrent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoTransfer['unidadActual']['eInsId']);
               
              
                $arrStudents[]= array(
                  "studentId" => $student['studentId'],
                  "carnetIdentidad" => $student['carnetIdentidad'],
                  "complemento" => $student['complemento'],
                  "codigoRude" => $student['codigoRude'],
                  "paterno" => $student['paterno'],
                  "materno" => $student['materno'],
                  "nombre" => $student['nombre'],
                  "generoId" => $student['generoId'],
                  "genero" => $student['genero'],
                  "segipId" => $student['segipId'],
                  "eInsId" => $student['eInsId'],
                  "eiceId" => $student['eiceId'],
                  "cursoId" => $student['cursoId'],
                  "fechaNacimiento" =>$student['fechaNacimiento']->format('d-m-Y'),
                  "nivel" =>$objCursoInfo->getNivelTipo()->getNivel(),
                  "grado" =>$objCursoInfo->getGradoTipo()->getGrado(),
                  "paralelo" =>$objCursoInfo->getParaleloTipo()->getParalelo(),
                  "turno" =>$objCursoInfo->getTurnoTipo()->getTurno(),
                  "sieOld" =>$objUEOld->getId(),
                  "sieOldName" =>$objUEOld->getInstitucioneducativa(),
                  "sieCurrent" =>$objUECurrent->getId(),
                  "sieCurrentName" =>$objUECurrent->getInstitucioneducativa(),
                  "matriculaOld" =>$objInscriptionOld->getEstadomatriculaTipo()->getEstadomatricula(),
                  "matriculaCurrent" =>$objInscriptionCurrent->getEstadomatriculaTipo()->getEstadomatricula(),
                 // "matricula" =>$objMatricula->getEstadomatricula(),


                  
                );

            }




            $status = 'success';
            $code = 200;
            $message = "Datos encontrados";
            $swprocess = true; 
            
        }else{

            $status = 'error';
            $code = 400;
            $message = "No existen estudiantes registrados";
            $swprocess = false;
            $arrStudents = false; 
    }


      $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swprocessue' => $swprocess,    
        'arrStudents' => $arrStudents, 
        
        );
      $response = new JsonResponse();
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

        
    }

}
