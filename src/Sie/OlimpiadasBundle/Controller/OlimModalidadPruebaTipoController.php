<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sie\AppWebBundle\Entity\OlimModalidadPruebaTipo;

class OlimModalidadPruebaTipoController extends Controller
{
	private $session;

	public function __construct(){
		$this->session = new Session();
	}

	public function indexAction(){

		return $this->render('SieOlimpiadasBundle:OlimModalidadPruebaTipo:index.html.twig', $this->datosVista());
	}

	private function datosVista(){

		$em = $this->getDoctrine()->getManager();

		$id = $this->session->get('idOlimpiada');

		$olimpiada = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id);

		$activos = $em->createQueryBuilder()
                            ->select('ompt')
                            ->from('SieAppWebBundle:OlimModalidadPruebaTipo','ompt')
                            ->innerJoin('SieAppWebBundle:OlimModalidadTipo','omt','with','ompt.olimModalidadTipo = omt.id')
                            ->where('ompt.olimRegistroOlimpiada = :id')
                            ->setParameter('id', $id)
                            ->orderBy('omt.id','ASC')
                            ->getQuery()
                            ->getResult();
		$array = [];
		foreach ($activos as $ac) {
			$array[] = $ac->getOlimModalidadTipo()->getId();
		}

		$modalidades = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->findAll();

		$faltantes = [];

		foreach ($modalidades as $e) {
			if(!in_array($e->getId(), $array)){
				$faltantes[] = $e;
			}
		}

		return array(
			'olimpiada'=>$olimpiada,
			'activos'=>$activos,
			'faltantes'=>$faltantes
		);
	}

	/**
	 * Function new
	 */
	public function newAction($id){

		$idOlimpiada = $this->session->get('idOlimpiada');

		$em = $this->getDoctrine()->getManager();
		$registro = $em->getRepository('SieAppWebBundle:OlimModalidadPruebaTipo')->findBy(array(
			'olimRegistroOlimpiada'=>$idOlimpiada,
			'olimModalidadTipo'=>$id
		));

		if(!$registro){
			$entity = new OlimModalidadPruebaTipo();
			$entity->setOlimRegistroOlimpiada($em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($idOlimpiada));
			$entity->setOlimModalidadTipo($em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($id));
			$em->persist($entity);
			$em->flush();

			$status = 200;
			$mensaje = 'Registro realizado correctamente';
		}else{
			$status = 300;
			$mensaje = 'El registro ya existe';
		}

		$data = $this->datosVista();
		$data['status'] = $status; 
		$data['mensaje'] = $mensaje; 

		return $this->render('SieOlimpiadasBundle:OlimModalidadPruebaTipo:response.html.twig', $data);

	}

	/**
	 * Function delete
	 */
	public function deleteAction($id){

		try {
			$em = $this->getDoctrine()->getManager();
			$registro = $em->getRepository('SieAppWebBundle:OlimModalidadPruebaTipo')->find($id);

			if($registro){
				$estado = $em->remove($registro);
				$em->flush();
				$status = 200;
				$mensaje = 'Registro eliminado correctamente';
			}else{
				$status = 300;
				$mensaje = 'No se pudo eliminar el registro';
			}

			
		} catch (Exception $e) {
			$status = 500;
			$mensaje = 'El registro ya esta siendo usado';
		}

		$data = $this->datosVista();
		$data['status'] = $status; 
		$data['mensaje'] = $mensaje; 

		return $this->render('SieOlimpiadasBundle:OlimModalidadPruebaTipo:response.html.twig', $data);

	}


}