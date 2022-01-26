<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Form\OlimTutorType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * DownloadArchs controller.
 *
 */
class DownloadArchsController extends Controller{
    
    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimTutor entities.
     *
     */
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $gestion = 2018;
        //$port = 1929;
        // Generamos Archivo
        // $query = $em->getConnection()->prepare("select * from sp_genera_archs_olimpiadas_txt('".$gestion."')");
        // $query->execute();
        // $result = $query->fetchAll();
        $sftp = new SFTP('172.20.0.103:1929');
        
        if (!$sftp->login('afiengo', 'ContraFieng0$')) {
            throw new \Exception('¡No tienes acceso a este servidor!');
        }
        $sftp->chdir('/aplicacion_upload');
        $archivos = $sftp->rawlist();

        dump($archivos);die;
        //$filepath = '/aplicacion_upload/estudiantes_olimp_2018-07-12.txt';

        // the get method returns the content of a remote file
        //$filecontent = $sftp->get($filepath);

        // Alternatively, instead of retrieve the content, download the file
        // copy the file into a local directory providing the local path as second parameter
        //$sftp->get($filepath, '/myPC/folder/myfile.txt');

        //create response to donwload the file
        $response = new Response();
        // //then send the headers to foce download the zip file
        // $response->headers->set('Content-Type', 'application/zip');
        // $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filecontent));
        // $response->setContent(file_get_contents($dir) . $file);
        // $response->headers->set('Pragma', "no-cache");
        // $response->headers->set('Expires', "0");
        // $response->headers->set('Content-Transfer-Encoding', "binary");
        // $response->sendHeaders();
        // $response->setContent(readfile($dir . $file));
        return $response;
        if($this->session->get('ie_id') <= 0){
            //call find sie template
            $modeview = 1;
        }else{
            $modeview = 0;
           // return $this->redirectToRoute('olimtutor_listTutorBySie');
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:index.html.twig', array(
                'form'=>$this->formFindTutor()->createView(),
                'modeview' => $modeview
            ));
       
    }

}
