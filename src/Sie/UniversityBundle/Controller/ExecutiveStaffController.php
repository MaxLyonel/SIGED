<?php

namespace Sie\UniversityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;



class ExecutiveStaffController extends Controller{
    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }

    public function indexAction(){
        return $this->render('SieUniversityBundle:ExecutiveStaff:index.html.twig', array(
                // ...
            ));    
    }

    public function lookforpersonAction(Request $request){
      //ini json var
      	$response = new JsonResponse();    	
    	$swperson = false;
    	$em = $this->getDoctrine()->getManager();

        $arrayCondition2['carnet'] = $request->get('cibuscar');
        $arrayCondition2['segipId'] = 1;
        if($request->get('complementoval')){
          $arrayCondition2['complemento'] = $request->get('complementoval');
        }else{
          $arrayCondition2['complemento'] = "";
        }
        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrayCondition2);  
        $arrPerson = array(
	          'paterno'     =>'',
	          'materno'     =>'',
	          'nombre'      =>'',
	          'fecNac'      =>'',
	          'carnet'      => $request->get('cibuscar'),
	          'genero'      =>'',
	          'generoId'    =>'',
	          'complemento' => $request->get('complementoval'),
	          'expedido'    =>'',
	          'expedidoId'  =>'',
	          'expedidoId2' =>'',
	          'personId'    =>'',
	          'foreign'    =>'',
        );
        if($objPerson){
        	$swperson=true;
        	$arrPerson = array(
	          'paterno'     =>$objPerson->getPaterno(),
	          'materno'     =>$objPerson->getMaterno(),
	          'nombre'     =>$objPerson->getNombre(),
	          'fecNac'      =>$objPerson->getFechaNacimiento()->format('d-m-Y'),
	          'carnet'      =>$objPerson->getCarnet(),
	          'genero'      =>$objPerson->getGeneroTipo()->getGenero(),
	          'generoId'    =>$objPerson->getGeneroTipo()->getId(),
	          'complemento' =>$objPerson->getComplemento(),
	          'expedido'    =>$objPerson->getExpedido()->getSigla(),
	          'expedidoId'  =>$objPerson->getExpedido()->getId(),
	          'expedidoId2'  =>$objPerson->getExpedido()->getId(),
	          'personId'    =>$objPerson->getId(),  
	          'foreign'    =>'',     		
        	);
        }


	      $arrResponse = array(
	            'answer'                   => true,
	            'arrPerson'                => $arrPerson,
	            'swperson'                 => $swperson,
	      );
	      // dump($arrResponse);die;
	      $response->setStatusCode(200);
	      $response->setData($arrResponse);        
	      return $response;
    }

}
