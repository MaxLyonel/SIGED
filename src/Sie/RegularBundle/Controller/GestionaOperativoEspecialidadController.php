<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 

class GestionaOperativoEspecialidadController extends Controller{

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

        
        return $this->render('SieRegularBundle:GestionaOperativoEspecialidad:index.html.twig', array(
                // ...
            ));    
    }

    public function lookforSieAction(Request $request){
        // set variable to send the Response
        $response = new JsonResponse();
        // crate db conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $siebuscar = $request->get('siebuscar');

        // check if the UE is Plena
        $arrCondition1 = array(
            'institucioneducativaId'=>$siebuscar,
            'gestionTipoId' => $this->session->get('currentyear') ,
            'gradoTipo'=>6,
            'institucioneducativaHumanisticoTecnicoTipo' => 1,
        );
        $objUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy($arrCondition1);
        $message='';
        $arrInfoUe='';
        $result = false;

        if($objUePlena){
            // check if the user is end its operativo 
            $arrCondition2 = array(
                'institucioneducativaId'=>$siebuscar,
                'estadoOperativo'=>true,
                'gestionTipoId' => $this->session->get('currentyear') ,
            );
            $objCtrlOperativo = $em->getRepository('SieAppWebBundle:BthControlOperativoModificacionEspecialidades')->findOneBy($arrCondition2);
            
            if($objCtrlOperativo){
                $result = true;
                $message='RESULTADO ENCONTRADO';
                $objInfoUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($siebuscar);
                $arrInfoUe = array('sie'=>$objInfoUe->getId(), 'institucioneducativa'=>$objInfoUe->getInstitucioneducativa());
            }else{
                $message='UNIDAD EDUCATIVA NO CERRO SU OPERATIVO';
            }

        }else{
            $message='LA UNIDAD EDUCATIVA NO ES PLENA';
        }

        

        $response->setStatusCode(200);
        $response->setData(array(
            'result'    => $result,
            'message'   => $message,
            'arrInfoUe' => $arrInfoUe,
        ));
       
        return $response;       
    }

    public function changeStatusOperativoAction(Request $request){
        // set variable to send the Response
        $response = new JsonResponse();     
        // get the send values
        $siebuscar = $request->get('siebuscar');
       // crate db conexion
        $em = $this->getDoctrine()->getManager();        

        $message = 'REALIZADO';
        $arrCondition2 = array(
            'institucioneducativaId'=>$siebuscar,
            'estadoOperativo'=>true,
            'gestionTipoId' => $this->session->get('currentyear') ,
        );

        $objCtrlOperativo = $em->getRepository('SieAppWebBundle:BthControlOperativoModificacionEspecialidades')->findOneBy($arrCondition2);
        $objCtrlOperativo->setEstadoOperativo('f');
        $em->persist($objCtrlOperativo);
        $em->flush();

        $response->setStatusCode(200);
        $response->setData(array(
            'message'   => $message,
            'status' => true,
            'result'    => false,
            'message'   => $message,
        ));
       
        return $response;   
        // return $this->render('SieRegularBundle:GestionaOperativoEspecialidad:changeStatusOperativo.html.twig', array(
        //         // ...
        //     ));    
    }

}
