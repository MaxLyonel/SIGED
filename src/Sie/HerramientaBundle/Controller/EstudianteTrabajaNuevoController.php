<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteTrabajoRemuneracion;

class EstudianteTrabajaNuevoController extends Controller{

     public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get send parameters
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);

        $data = array(

            'sie'      => $iec->getInstitucioneducativa()->getId(),
            'gestion'  => $iec->getGestionTipo()->getId(),
            'nivel'    => $iec->getNivelTipo()->getId(),
            'grado'    => $iec->getGradoTipo()->getId(),
            'paralelo' => $iec->getParaleloTipo()->getId(),
            'turno'    => $iec->getTurnoTipo()->getId(),
            'iecId'    => $aInfoUeducativa['ueducativaInfoId']['iecId'],


        );
        $jsonData = json_encode($data);
        
        

        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:index.html.twig', array(
                'form' => $this->findStudent($jsonData)->createView()
            ));    
    }

    private function findStudent($data){
        return $this->createFormBuilder()
                ->add('rudeoci', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'attr'=>array('class'=>'form-control')))
                ->add('data', 'text', array('data'=>$data))
                ->add('find', 'button', array('label' => 'Buscar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'findStudent();')))
                ->getForm();
    }

    public function findAction(Request $request){
        dump($request);die;
        
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:find.html.twig', array(
                // ...
            ));    
    }

    public function resultAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:result.html.twig', array(
                // ...
            ));    }

    public function doInscriptionAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:doInscription.html.twig', array(
                // ...
            ));    }

    public function listAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:list.html.twig', array(
                // ...
            ));    }

}
