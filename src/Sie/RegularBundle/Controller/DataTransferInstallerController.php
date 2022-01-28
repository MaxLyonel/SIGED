<?php

namespace Sie\RegularBundle\Controller;

use Sie\AppWebBundle\Entity\ControlInstalador;
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

class DataTransferInstallerController extends Controller {
    
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $em->getConnection()->commit();
            $query = $em->getConnection()->prepare('select * from control_instalador ci order by 1 desc ');
            $query->execute();
            $instalador= $query->fetchAll();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('data_transfer_installer_upload'))
            ->add('crear', 'submit', array('label' => 'Generar Reportes', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $this->render($this->session->get('pathSystem') . ':DataTransferInstaller:uploadInstaller.html.twig', array(
            'form' => $form->createView(),
            'instalador'=>$instalador
        ));
    }

    public function uploadInstallerAction(Request $request) {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $nombre = $request->get('namefile');
        $detalle= $request->get('detail');
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        //dump($fechaActual);die;
        $gestion = $this->session->get('currentyear');

        if ($request->getMethod() == 'POST') {

                try {
                //echo "<pre>";
                //get the form file
                $oFile = $request->files->get('siefile');
                // dump($oFile);die;
                //get the name of file upload
                $originalName = $oFile->getClientOriginalName();
                 // dump($nombre);die;
                //get the extension
                $aName = explode('.', $originalName);
                $fileType = $aName[sizeof($aName) - 1];
                //validate the allows extensions
                $aValidTypes = array('exe');
                //validate the files extension .exe
                if (!in_array(strtolower($fileType), $aValidTypes)) {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir No tiene la extension correcta');
                    return $this->redirect($this->generateUrl('data_transfer_installer_index'));
                }

                //validate the files weight
                if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir no tiene el tamaño correcto');
                    return $this->redirect($this->generateUrl('data_transfer_installer_index'));
                }

                // make the temp dir name
                $dirtmp = $this->get('kernel')->getRootDir() . '/../web/uploads/instaladores/';

                if (is_readable($this->get('kernel')->getRootDir() . '/../web/uploads/instaladores/')) {
                }else{
                    $session->getFlashBag()->add('warningcons', 'Problemas con permisos al intentar subir el instalador');
                    return $this->redirect($this->generateUrl('data_transfer_installer_index'));   
                }
                $instalador=$em->getRepository('SieAppWebBundle:ControlInstalador')->findOneBy(array('instalador'=>$nombre));
                $gestionInstaller=$em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('gestion'=>$gestion));
                //$gestionInstaller=$gestionInstaller->getId();
               // dump($gestionInstaller);die;
                if($instalador)
                {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir ya fue cargado al repositorio'); 
                }else {

                    $file = $oFile->move($dirtmp, $nombre);
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('control_instalador');")->execute();
                    $controlInstaller = new ControlInstalador();
                    $controlInstaller -> setInstalador($nombre);
                    $controlInstaller -> setPath($dirtmp);
                    $controlInstaller -> setDetalle($detalle);
                    $controlInstaller -> setCreatedAt($fechaActual);
                    $controlInstaller -> setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionInstaller));
                    $controlInstaller -> setActivo(false);
                    $em->persist($controlInstaller);
                    //dump($controlInstaller);die;
                    $em->flush($controlInstaller);
                   // dump($controlInstaller);die;
                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                echo 'Excepción capturada: ', $ex->getMessage(), "\n";
            }
            $session->getFlashBag()->add('successcons', 'Archivo cargado correctamente');
            return $this->redirect($this->generateUrl('data_transfer_installer_index'));
        } else {
            $session->getFlashBag()->add('warningcons', 'Datos enviados incorrectamente');
            return $this->redirect($this->generateUrl('data_transfer_installer_index'));
        }
    }

    public function activateInstallerAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        // dump($form);die;
        $instalador = $em->getRepository('SieAppWebBundle:ControlInstalador')->findOneBy(array('id' => $id));
        $activo = !($instalador->getActivo());
        // dump($activo);die;
        $em->getConnection()->beginTransaction();
        try {
            $instalador ->setActivo($activo);
            $em->persist($instalador);
            $em->flush();
            $em->getConnection()->commit();
            $query = $em->getConnection()->prepare('select * from control_instalador ci order by 1 desc ');
            $query->execute();
            $instalador= $query->fetchAll();
           // $session->getFlashBag()->add('successcons', 'Cambio de estado del Instalador realizado con exito');
            return $this->render('SieRegularBundle:DataTransferInstaller:listInstaller.html.twig',array(
            'instalador'=>$instalador
            ));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
          //  $this->get('session')->getFlashBag()->add('warningcons', 'El cambio de estado no fue realizado.');
            return $this->render('SieRegularBundle:DataTransferInstaller:listInstaller.html.twig');
        }
    }

    public function downloadInstallerAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //get path of the file
        $id = $request->get('id');
        //dump($form);die;
        $instalador=$em->getRepository('SieAppWebBundle:ControlInstalador')->findOneBy(array('id'=>$id));
        $nombre=$instalador->getInstalador();
        $path=$instalador->getPath();
        //dump($nombre);die;
      //  $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/instaladores/';
     //   $file = 'instalador_SIGED_SIE_v1292.exe'
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/exe');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $nombre));
        $response->setContent(file_get_contents($path. $nombre) );
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($path . $nombre));
        return $response;
    }
}