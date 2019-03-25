<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;

class DesunificationRudeController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * index download distrito
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':DesunificationRude:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->add('rude', 'text', array('label' => 'Rude', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9 A-Z a-z \sñÑ]{2,4}', 'maxlength' => '20', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('rudeb', 'text', array('label' => 'Rude', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9 A-Z a-z \sñÑ]{2,4}', 'maxlength' => '20', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'button', array('label' => 'VER HISTORIALES', 'attr' => array('class' => 'btn btn-success btn-large glyphicon glyphicon-search', 'onclick' => 'lookForInscriptions()')))
                        ->getForm();
    }

    /**
     * find the distrito per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function buildAction(Request $request) {
        $rude = $request->get('rude');
        $rude2 = $request->get('rudeb');
        $em = $this->getDoctrine()->getManager();
        
        //$objEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //$objEstudiante2 = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude2));

        $val = 0;

        //VERIFICANDO QUE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS
        /*$sqla = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudea'
        and a.esactivo is true";
        $queryverdipa = $em->getConnection()->prepare($sqla);
        $queryverdipa->execute();
        $dataInscriptionJsonVerDipa = $queryverdipa->fetchAll();

        $sqlb = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudeb'
        and a.esactivo is true";
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        
        if ((count($dataInscriptionJsonVerDipa) > 0) || (count($dataInscriptionJsonVerDipb) > 0)) {
            $val = 2;
        }

        $queryver = $em->getConnection()->prepare("select * from get_estudiantes_verificacion_historiales('" . $rude . "','" . $rude2 . "');");
        $queryver->execute();
        $dataInscriptionJsonVer = $queryver->fetchAll();
        if (count($dataInscriptionJsonVer) > 0){
           $val = 1; 
        }*/
        
        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rude . "');");
        $query->execute();
        $dataInscriptionJson = $query->fetchAll();
        foreach ($dataInscriptionJson as $key => $inscription) {
            $dataInscription [] = json_decode($inscription['get_estudiante_historial_json'], true);
        }

        $query2 = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rude2 . "');");
        $query2->execute();
        $dataInscriptionJson2 = $query2->fetchAll();
        foreach ($dataInscriptionJson2 as $key => $inscription2) {
            $dataInscription2 [] = json_decode($inscription2['get_estudiante_historial_json'], true);
        }

        $objEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude2));
        
//        dump($dataInscription);
//        dump(isset($objEstudiante));
//        die;
        if (!isset($dataInscription2)) {
            $dataInscription2 = array();
        }
        
        if (isset($dataInscription,$objEstudiante)) {
            $dataExist = ($dataInscription) ? 1 : 0;
            return $this->render($this->session->get('pathSystem') . ':DesunificationRude:unificationInfo.html.twig', array(
                        'val' => $val,
                        'datastudenta' => $dataInscription,
                        'objEstudiante' => $objEstudiante,
                        'datastudentb' => $dataInscription2,
                        'dataExist' => $dataExist,
                        'idStudent' => $dataInscription[0]['eid'],
                        'idStudentb' => $objEstudiante->getId(),
                        'form' => $this->createFormDesunification()->createView()
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':DesunificationRude:unificationInfo.html.twig', array(
                        'objDistrito' => array(),
                        'dataExist' => 0));
        }
    }

    private function createFormDesunification() {
        return $this->createFormBuilder()
                        ->add('desunificar', 'button', array('label' => ' ENVIAR INSCRIPCIONES', 'attr' => array('class' => 'btn btn-primary btn-large glyphicon glyphicon-share-alt', 'onclick' => 'desunificarrude()')))                        
                        ->getForm();
    }

    /**
     * to do the desunification of rudes
     * @param Request $request
     * @return \Sie\RegularBundle\Controller\Exception
     */
    public function exectAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $aStudent = $request->get('forme');            
            $aInscription = $request->get('form');            
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($aStudent['idStudent']);            
            
            //$sSie = substr($objStudent->getCodigoRude(), '0', '8');            
            //$digits = 4;
            //$mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
            //$rude = $sSie . $this->session->get('currentyear') . $mat . $this->generarRude($sSie . $this->session->get('currentyear') . $mat);
            
            $objStudentb = $em->getRepository('SieAppWebBundle:Estudiante')->find($aStudent['idStudentb']);            
            while ($val = current($aInscription)) {
                //change the inscription to the new student
                //dump($val);die;
                if (key($aInscription) != '_token') {
                    $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($val);
                    $objInscription->setEstudiante($objStudentb);
                    $em->persist($objInscription);
                    $em->flush();
                }
                next($aInscription);
            }
            
            $llave = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave' => $objStudent->getCodigoRude(), 'validacionReglaTipo' => 11, 'solucionTipoId' => 0));
            if($llave) {
                $llave->setEsActivo(true);
                $em->persist($llave);
                $em->flush();
            }
            
            $em->getConnection()->commit();
            
            $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $objStudent->getCodigoRude() . "');");
            $query->execute();
            $dataInscriptionJson = $query->fetchAll();            
            foreach ($dataInscriptionJson as $key => $inscription) {              
              $dataInscription [] = json_decode($inscription['get_estudiante_historial_json'],true);
            }
            
            $query2 = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $objStudentb->getCodigoRude() . "');");
            $query2->execute();
            $dataInscriptionJson2 = $query2->fetchAll();            
            foreach ($dataInscriptionJson2 as $key => $inscription2) {
              $dataInscription2 [] = json_decode($inscription2['get_estudiante_historial_json'],true);
            }
            if (!isset($dataInscription2)) {
               $dataInscription2 = array();
            }
//            dump($dataInscription);
//            dump($dataInscription2);
//            die;
            $objEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $objStudentb->getCodigoRude()));
            return $this->render($this->session->get('pathSystem') . ':DesunificationRude:result.html.twig', array(
                        'oldStudent' => $dataInscription,
                        'objEstudiante' => $objEstudiante,
                        'newStudent' => $dataInscription2
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    /**
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
    private function generarRude($cadena) {
        $codigoRude = "";
        $codigoVerificacion = "123456789A0";
        $peso = 2;
        $sum = 0;
        $int = 0;
        while ($int < strlen($cadena)) {
            if ($peso == 7)
                $peso = 2;
            $sum = $sum + ($peso * ord(substr($cadena, $int, 1)));
            $peso = $peso + 1;
            $int = $int + 1;
        }
        return substr($codigoVerificacion, 10 - ($sum % 11), 1);
    }

}
