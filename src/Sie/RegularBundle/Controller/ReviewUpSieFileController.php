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
use Sie\AppWebBundle\Entity\UploadFileControl;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;

class ReviewUpSieFileController extends Controller{
    public $session;
     /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        //get the user session id
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieRegularBundle:ReviewUpSieFile:index.html.twig', array(
                'form' => $this->distritoform()->createView()
            ));    
    }

    public function distritoform(){
        $arrOperativos = array(99=>'Seleccionar',1=>'inicio',2=>'1er Bim',3=>'2do Bim',4=>'3ro Bim',5=>'4to Bim',0=>'Rude');
         $form = $this->createFormBuilder()
            // ->add('data', 'hidden', array('data'=>$data))
            ->add('find','button',array('label'=>'buscar', 'attr'=>array('class'=>'btn btn-success mr-5', 'onclick'=> 'findSieFiles()') ))
            ->add('distrito', 'text', array('label' => 'Distrito', 'attr' => array('maxlength' => 4, 'class' => 'form-control popovers','placeholder'=>'Introduzca Distrito')))
            ->add('operativo', 'choice', array('label' => 'Operativo', 'choices' => $arrOperativos ,'attr' => array( 'class' => 'form-control','placeholder'=>'Introduzca Distrito')))
            ->add('gestion', 'hidden', array('data' => $this->session->get('currentyear')  ) )
            ->getForm();    
        return $form;

    }

    public function findAction(Request $request){
        //get the user session id
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the send values
        $form = $request->get('form');
        
        // create db c onexion
        $em = $this->getDoctrine()->getManager();
        //get the lost emp files
        $bimestre = $form['operativo']-1;
        // $query = $em->getConnection()->prepare("select * from sp_verifica_archivos_emp('" . $form['gestion'] . "','','" . $form['distrito'] . "','','" . $bimestre . "') ;");
        // $query->execute();
        //get all files to the distrito selected
        $objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByDistritoAndOperativo($form);
        $arrUeUploadFiles = array();
        reset($objUeUploadFiles);
        while (($arrData = current($objUeUploadFiles)) !== FALSE) {

            if($bimestre == 0){
                $objInfoConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
                'gestion'=>$arrData['gestion'],
                'unidadEducativa'=>$arrData['id'],
                ));
            }else{
                if($bimestre == -1){
                    //things todo after to consolidation of file
                    $objInfoConsolidation = false;
                }else{
                    $objInfoConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
                    'gestion'=>$arrData['gestion'],
                    'unidadEducativa'=>$arrData['id'],
                    'bim'.$arrData['bimestre']=>$arrData['bimestre'],
                    ));
                }
            }

            if($objInfoConsolidation){
                $arrData['statusInfoConsolidation'] = true;    
            }else{
                $arrData['statusInfoConsolidation'] = false;    
            }
            $arrUeUploadFiles[] = $arrData;   
            next($objUeUploadFiles);
        }
        
        return $this->render('SieRegularBundle:ReviewUpSieFile:find.html.twig', array(
                'ueuploadfiles' => $arrUeUploadFiles,
            ));    
    }

    public function downloadFileAction(){

        return $this->render('SieRegularBundle:ReviewUpSieFile:downloadFile.html.twig', array(
                // ...
            ));    }

}
