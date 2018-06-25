<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\ImagenesInstitucioneducativa;

class ImagenesController extends Controller
{
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    
    public function galeriaFotosCentroAction(){
        
        $id_usuario = $this->session->get('userId');       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "  select a.* from
            imagenes_institucioneducativa a inner join imagen_tipo b on a.imagen_tipo_id = b.id
            where institucioneducativa_id = '".$this->session->get('ie_id')."' and imagen_tipo_id = 1";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();

        $countfotos = count($po);

        return $this->render('SieHerramientaAlternativaBundle:Imagenes:galleryPhotos.html.twig',array('po'=>$po));
    }

    public function galeriaFotosEditarFotoAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }


        return $this->render('SieHerramientaAlternativaBundle:Imagenes:index.html.twig');
    }

    public function galeriaFotosCentroUploadAction(Request $request){        
        $file1 = $_FILES['archivo1'];
        $file2 = $_FILES['archivo2'];
        $file3 = $_FILES['archivo3'];
        $file4 = $_FILES['archivo4'];
		$response = new JsonResponse();

        $countFile = 0;
        $valfondo = 0;
        if (isset($var)) {
            $valfondo = $_REQUEST['group1'];        
        };
        
        $em = $this->getDoctrine()->getManager();        
        //try {
        
        $db = $em->getConnection();            
        $query = "  select a.* from
                imagenes_institucioneducativa a inner join imagen_tipo b on a.imagen_tipo_id = b.id
                where institucioneducativa_id = '".$this->session->get('ie_id')."' and imagen_tipo_id = 1";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();

        foreach ($po as $element){
            $objdel = $em->getRepository('SieAppWebBundle:ImagenesInstitucioneducativa')->find($element["id"]);
            $em->remove($objdel);
        }
        $em->flush();

        //dump($_FILES['archivo1']['type']);  die;

		if($_FILES['archivo1']['name'] != ""){
            $countFile = $countFile + 1;

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/institutoseducativos';
            $tipo = explode('/',$_FILES['archivo1']['type']);
            $nombre_archivoa = $this->session->get('ie_id').'_1_'.$countFile.'.'.$tipo[1];
                              
            $a = new ImagenesInstitucioneducativa();
            $a->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            
            if (($valfondo == 1) || ($valfondo == 0)){
                $nombre_archivoa = $this->session->get('ie_id').'_fondo';            
            };

            $a->setImagenTipo($em->getRepository('SieAppWebBundle:ImagenTipo')->find('1'));
            $a->setNombreArchivo($nombre_archivoa);                        
		    $archivador = $ruta.'/'.$nombre_archivoa;
            move_uploaded_file($_FILES['archivo1']['tmp_name'], $archivador);
            $a->setDescripcion("...");
            $em->persist($a);
            $em->flush();
        }

        if($_FILES['archivo2']['name'] != ""){
            $countFile = $countFile + 1;

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/institutoseducativos';
            $tipo = explode('/',$_FILES['archivo2']['type']);
            $nombre_archivob = $this->session->get('ie_id').'_1_'.$countFile.'.'.$tipo[1];
                              
            $a = new ImagenesInstitucioneducativa();
            $a->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            
            if (($valfondo == 2) || ($valfondo == 0)){
                $nombre_archivob = $this->session->get('ie_id').'_fondo';            
            };

            $a->setImagenTipo($em->getRepository('SieAppWebBundle:ImagenTipo')->find('1'));
            $a->setNombreArchivo($nombre_archivob);                        
		    $archivador = $ruta.'/'.$nombre_archivob;
            move_uploaded_file($_FILES['archivo2']['tmp_name'], $archivador);
            $a->setDescripcion("...");
            $em->persist($a);
            $em->flush();
        }

        if($_FILES['archivo3']['name'] != ""){           
			$countFile = $countFile + 1;

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/institutoseducativos';
            $tipo = explode('/',$_FILES['archivo3']['type']);
            $nombre_archivoc = $this->session->get('ie_id').'_1_'.$countFile.'.'.$tipo[1];
                              
            $a = new ImagenesInstitucioneducativa();
            $a->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            
            if (($valfondo == 3) || ($valfondo == 0)){
                $nombre_archivoc = $this->session->get('ie_id').'_fondo';            
            };

            $a->setImagenTipo($em->getRepository('SieAppWebBundle:ImagenTipo')->find('1'));
            $a->setNombreArchivo($nombre_archivoc);                        
		    $archivador = $ruta.'/'.$nombre_archivoc;
            move_uploaded_file($_FILES['archivo3']['tmp_name'], $archivador);
            $a->setDescripcion("...");
            $em->persist($a);
            $em->flush();
        }

        if($_FILES['archivo4']['name'] != ""){            
			$countFile = $countFile + 1;

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/institutoseducativos';
            $tipo = explode('/',$_FILES['archivo4']['type']);
            $nombre_archivod = $this->session->get('ie_id').'_1_'.$countFile.'.'.$tipo[1];
                              
            $a = new ImagenesInstitucioneducativa();
            $a->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            
            if (($valfondo == 4) || ($valfondo == 0)){
                $nombre_archivod = $this->session->get('ie_id').'_fondo';            
            };

            $a->setImagenTipo($em->getRepository('SieAppWebBundle:ImagenTipo')->find('1'));

            $a->setNombreArchivo($nombre_archivod);                        
		    $archivador = $ruta.'/'.$nombre_archivod;
            move_uploaded_file($_FILES['archivo4']['tmp_name'], $archivador);
            $a->setDescripcion("...");
            $em->persist($a);
            $em->flush();
        }
        
        $msg = 'Archivo actualizado correctamente';
		    return $response->setData(array(
		    	'status'=>201,
		    	'msg'=>$msg	    	
            ));
            
        //} catch (Exception $ex) {
            //$em->getConnection()->rollback();
        //}    

		/*return $response->setData(array(
			'status'=>404,
			'msg'=>'Ocurrio un error al subir el archivo. Debe seleccionar un archivo PDF válido'
		));*/

    }
                    
    public function galeriaFotosCentroActualizarAction(Request $request){
		$archivo = $request->get('archivo');
		//$em = $this->getDoctrine()->getManager();
        //$grupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);        
		return $this->render('SieHerramientaAlternativaBundle:Imagenes:actualizar.html.twig', array('archivo'=>$archivo));
	}
    
}