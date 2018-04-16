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

		if($file){

		    $ruta = $this->get('kernel')->getRootDir() . '/../web/uploads/olimpiadas/documentos';
		    $tipo = explode('/',$_FILES['archivo']['type']);
		    $nombre_archivo = md5(uniqid()).'.'.$tipo[1];
		    $archivador = $ruta.'/'.$nombre_archivo;
		    move_uploaded_file($_FILES['archivo']['tmp_name'], $archivador);

		    $em = $this->getDoctrine()->getManager();
		    $grupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);
		    $grupo->setDocumentoPdf1($nombre_archivo);
		    $em->persist($grupo);
		    $em->flush();

		    return $response->setData(array(
		    	'status'=>201,
		    	'msg'=>'Archivo subido correctamente',
		    	'categoriaId'=>$grupo->getOlimReglasOlimpiadasTipo()->getId(),
		    	'archivo'=>$nombre_archivo
		    ));

		}

		return $response->setData(array(
			'status'=>404,
			'msg'=>'Debe seleccionar un archivo PDF',
			'categoriaId'=>null
		));

	}
}