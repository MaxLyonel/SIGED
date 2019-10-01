<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\AppWebBundle\Entity\DownloadFileControl;
use Sie\AppWebBundle\Entity\InstitucioneducativaControlOperativoMenus;
use Sie\AppWebBundle\Entity\ControlArchivosBajada;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;

/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for downloading the infra file
 * Date: 30-09-2019
 *
 *
 * DownloadInfraFileController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */
class DownloadInfraFileController extends Controller{

    private $session;
    public  $userlogged;
    public function __construct() {        
        $this->session = new Session();        
        $this->userlogged = $this->session->get('userId');
    }
    
    public function indexAction(Request $request){
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieHerramientaBundle:DownloadInfraFile:index.html.twig', array(
                'form' => $this->craeteformsearchsie()->createView()
            ));    
    }
    
    // form to donwload the sie selected
    private function craeteformsearchsie() {
        $arrYears = array($this->session->get('currentyear') => $this->session->get('currentyear'));
        $arrOpera = array('1'=>1,2=>2);
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices'=>$arrYears , 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('operativo', 'choice', array('label' => 'Operativo', 'choices'=>$arrOpera, 'attr' => array('class' => 'form-control', 'empty_data' => 'Seleccionar...')))
                        ->add('search', 'button', array('label' => 'Generar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'generateInfraFile()')))
                        ->getForm();
    }    

    public function generateAction(Request $request){

        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $form = $request->get('form');
        // the main path
        $mainPath = '/archivos/descargas/ifr/';
        $ifrFile = $form['sie'].'_'.$form['gestion'].'_'.$form['gestion'].'-'.date('m').'-'.date('d').'_b64.ifr';
        // check if the file exist
        if(is_readable($mainPath.$form['sie'].'_2019_2019-09-12_b64.ifr')){
            system('rm -fr ' . $mainPath);
        }
        // generate the ifr file
        $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_infraestructura_txt('" . $form['sie'] . "','" . $form['gestion'] . "');");        
        // $query->execute();
        dump(is_readable($mainPath.$form['sie'].'_2019_2019-09-12_b64.ifr'));
        // get the sie data
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

   
        return $this->render('SieHerramientaBundle:DownloadInfraFile:generate.html.twig', array(
            'uEducativa'   => $objUe[0],
            'datadownload' => json_encode($form),
            // 'file'         => base64_encode($ifrFile),
            'file'         => base64_encode($form['sie'].'_2019_2019-09-12_b64.ifr'),
        ));
    }
    
    private function createFormToBuild($sie, $gestion, $bim) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'hidden', array('data' => $sie))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('bimestre', 'hidden', array('data' => $bim))
                        ->add('generar', 'button', array('label' => 'Generar Archivo', 'attr' => array('class' => 'btn btn-link', 'onclick' => 'buildAgain()')))
                        ->getForm()
        ;
    }    

    // public function downloadFileAction(Request $request){
    //     dump($request);
    //     die;
    //     // $userInfo = $this->getUserInfo($form);
    //     // die;
    //     return $this->render('SieHerramientaBundle:DownloadInfraFile:downloadFile.html.twig', array(
    //             // ...
    //         ));    
    // }

    public function downloadFileAction(Request $request, $file,$datadownload) {
      // dump($datadownload);die;
      $form = json_decode($datadownload,true);
      $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,1);
        //get path of the file
        //$dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
        $dir = '/archivos/descargas/ifr/';
        //remove space on the post values
        $file = base64_decode($file);
        $file = preg_replace('/\s+/', '', $file);
        $file = str_replace('%20', '', $file);
        // dump($file);
        // die;
        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;    
    }

    private function setCtrlOpeMenuInfo($data,$switch){

      //ini db conexion
      $em = $this->getDoctrine()->getManager();
      // $em->getConnection()->beginTransaction();

      try {
        $objCtrlOpeMenu = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array(
          'institucioneducativa' => $data['sie'],
          'gestionTipoId' => $data['gestion'],
          'notaTipo' => $data['operativo'],
        ));
        // dump($objCtrlOpeMenu);
        if($objCtrlOpeMenu){
          $objCtrlOpeMenu->setEstadoMenu($switch);
        }else{
          $objCtrlOpeMenu = new InstitucioneducativaControlOperativoMenus();
          $objCtrlOpeMenu->setGestionTipoId($data['gestion']);
          $objCtrlOpeMenu->setEstadoMenu($switch);
          $objCtrlOpeMenu->setFecharegistro(new \DateTime('now'));
          $objCtrlOpeMenu->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($data['operativo']));
          $objCtrlOpeMenu->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['sie']));

        }
        $em->persist($objCtrlOpeMenu);
        $em->flush();
        // $em->getConnection()->commit();

      } catch (Exception $e) {
        // $em->getConnection()->rollback();
        echo "we have this problem ".$e;
        return array();
      }
      // die('krlos');
      return 'done';
    }    



}
