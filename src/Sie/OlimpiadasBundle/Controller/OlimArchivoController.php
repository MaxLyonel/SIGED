<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * OlimArchivo controller.
 *
 */
class OlimArchivoController extends Controller {

	public function indexAction(Request $request){
		$id = $request->get('id');

		$loader = $this->get('kernel')->getRootDir() . '/web/manual_olimpiadas.pdf';

		$em = $this->getDoctrine()->getManager();
		$grupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

		return $this->render('SieOlimpiadasBundle:OlimArchivo:index.html.twig', array(
			'id'=>$id,
			// 'path'=> $this->get('kernel')->getRootDir() . '/../web/manual_olimpiadas.pdf'
			'path'=> $loader,
			'grupo'=> $grupo
		));
	}

	public function uploadAction(Request $request){

		$id = $request->get('id');
		$file = $_FILES['archivo'];

		$response = new JsonResponse();

		//dump($file);die;

		// if($file and $_FILES['archivo']['error'] != 1){
		if($file){

			$em = $this->getDoctrine()->getManager();
		    $grupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);
		    $sie = $grupo->getOlimTutor()->getInstitucioneducativa()->getId();
		    $gestion = $grupo->getGestionTipoId();
		    $grupoId = $grupo->getId();

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/olimpiadas/documentos';

		    /**
		     * Verificamos si existe el archivo para eliminarlo
		     */
		    if ($grupo->getDocumentoPdf1() != "" and $grupo->getDocumentoPdf1() != null) {
		    	$nombreAnterior = $ruta.'/'.$grupo->getDocumentoPdf1();
		    	unlink($nombreAnterior);	
		    }


		    $tipo = explode('/',$_FILES['archivo']['type']);


		    // $nombre_archivo = md5(uniqid()).'.'.$tipo[1];
		    $nombre_archivo = $sie.'_'.$gestion.'_'.$grupoId.'.pdf';
		    $archivador = $ruta.'/'.$nombre_archivo;
		    //unlink($archivador);
		    if(move_uploaded_file($_FILES['archivo']['tmp_name'], $archivador)){
		    	
		    	$archivoAnterior = $grupo->getDocumentoPdf1();
		    	if($archivoAnterior == null or $archivoAnterior == ''){
		    		$msg = 'Archivo subido correctamente';
		    	}else{
		    		$msg = 'Archivo actualizado correctamente';
		    	}

		    	$grupo->setDocumentoPdf1($nombre_archivo);
		    	$em->persist($grupo);
		    	$em->flush();

		    	return $response->setData(array(
		    		'status'=>201,
		    		'msg'=>$msg,
		    		'categoriaId'=>$grupo->getOlimReglasOlimpiadasTipo()->getId(),
		    		'archivo'=>$nombre_archivo,
		    		'grupoId'=>$grupo->getId()
		    	));

		    }

		}

		return $response->setData(array(
			'status'=>404,
			'msg'=>'Ocurrio un error al subir el archivo. Debe seleccionar un archivo PDF válido',
			'categoriaId'=>null,
			'archivo'=>'',
			'grupoId'=>''
		));

	}

	public function cargarPlantillaAction(Request $request){
		$id = $request->get('id');
		$em = $this->getDoctrine()->getManager();
		$grupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

		return $this->render('SieOlimpiadasBundle:OlimArchivo:actualizar.html.twig', array('grupo'=>$grupo));
	}
}