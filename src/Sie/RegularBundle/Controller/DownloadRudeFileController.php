<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class DownloadRudeFileController extends Controller{

	public $session;
	public function __construct() {
        //init the session values
        $this->session = new Session();
     }

    public function indexAction(Request $request){

    	//get data user
    	$roluser = $this->session->get('roluser');
    	//get all data to send    	
    	return $this->redirectToRoute('downloadrudefile_listIndex');
    	// dump($objStatistics);die;

    	
    }

    public function listIndexAction(Request $request){
        //get data user
        $roluser = $this->session->get('roluser');
        $data = array('roluser'=>$roluser, 'gestion'=>$this->session->get('currentyear'));
        $objStatistics = array();
        switch ($roluser) {
            case 8:
                # code...
                $objStatistics = $this->get('funciones')->statisticsRudeFileNac($data);
                break;
            
            default:
                # code...
                break;
        }
  //       dump($objStatistics);die;
  //   	$datadecode = base64_decode($objStatistics);
		// dump($datadecode) ;
  //   	$objStatistics = json_decode($datadecode,true);

    	// dump('krlos');die;
        return $this->render('SieRegularBundle:DownloadRudeFile:index.html.twig', array(
                'form' => $this->formOperativoRude(array())->createView(),
                'objStatistics' => $objStatistics
            ));    


    }


    private function formOperativoRude($data){

    	return $this->createFormBuilder()
    			->add('data', 'hidden', array('data'=>$data))
    			->add('gestion', 'hidden', array('data'=>$this->session->get('currentyear')))
    			->add('codigoSie','text', array('label'=>'SIE', 'attr'=>array('class'=>'form-control')))
    			->add('downOperativoRude','button',array('label'=>'Generar Archivo Rude', 'attr'=>array( 'class'=> 'btn btn-default', 'onclick'=>'downOperativoRudeup()')))
    			->getForm();

    }


        public function downOperativoRudeAction(Request $request){

	      // create DB conexion
	      $em = $this->getDoctrine()->getManager();
	      // dump($request);die;
	      //get values send
	      $form = $request->get('form');
	      $form['id'] = $form['codigoSie'];
	      // dump($form);die;
	      // conver json values to array
	      // $arrData = json_decode($form['data'],true);
	      $arrData = $form;
	      //to generate the file execute de function
	      $cabecera = 'R';
	      $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_rude_txt('" . $arrData['id'] . "','" . $arrData['gestion'] . "','" . $cabecera . "');");
	      $query->execute();

	      $newGenerateFile = $arrData['id'] . '-' . date('Y-m-d') . '_' . 'R';
	      //get the file to generate the new file
	      $dir = '/archivos/descargas/';

	      //decode base64
	      $outputdata = system('base64 '.$dir.''.$newGenerateFile. '.sie  >> ' . $dir . 'NR' . $newGenerateFile . '.sie');

	      system('rm -fr ' . $dir . $newGenerateFile.'.sie');
	      exec('mv ' . $dir . 'NR' .$newGenerateFile . '.sie ' . $dir . $newGenerateFile . '.sie ');

	      //name the file
	     exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir  . $newGenerateFile . '.sie');
	     exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
	     
	     $form['data'] = json_encode($form);
	     return $this->render($this->session->get('pathSystem') . ':DownloadRudeFile:downOperativoRude.html.twig', array(
	        'file' => $newGenerateFile . '.igm ',
	        'datadownload' => $form['data'],

	     ));

    }

     public function downloadAction(Request $request, $file,$datadownload) {
      // dump($datadownload);die;
      $form = json_decode($datadownload,true);
      $form['operativoTipo']=5;
      // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,1);
      $objOperativoLog = $this->get('funciones')->saveOperativoLog($form);
        //get path of the file
        //$dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
        $dir = '/archivos/descargas/';
        //remove space on the post values
        $file = preg_replace('/\s+/', '', $file);
        $file = str_replace('%20', '', $file);
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



}
