<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sie\AppWebBundle\Entity\OlimEtapaPeriodo;

class OlimEtapaPeriodoController extends Controller
{
	private $session;

	public function __construct(){
		$this->session = new Session();
	}

	public function indexAction(){

		

		return $this->render('SieOlimpiadasBundle:OlimEtapaPeriodo:index.html.twig', $this->datosVista());
	}

	private function datosVista(){
		$em = $this->getDoctrine()->getManager();

		$id = $this->session->get('idOlimpiada');

		$olimpiada = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id);

		$activos = $em->createQueryBuilder()
                            ->select('oep')
                            ->from('SieAppWebBundle:OlimEtapaPeriodo','oep')
                            ->innerJoin('SieAppWebBundle:OlimEtapaTipo','oet','with','oep.olimEtapaTipo = oet.id')
                            ->where('oep.olimRegistroOlimpiada = :id')
                            ->setParameter('id', $id)
                            ->orderBy('oet.id','ASC')
                            ->getQuery()
                            ->getResult();
		$array = [];
		foreach ($activos as $ac) {
			$array[] = $ac->getOlimEtapaTipo()->getId();
		}

		$etapas = $em->getRepository('SieAppWebBundle:OlimEtapaTipo')->findAll();

		$faltantes = [];

		foreach ($etapas as $e) {
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
	public function newAction($idEtapa){

		$id = $this->session->get('idOlimpiada');

		$em = $this->getDoctrine()->getManager();
		$registro = $em->getRepository('SieAppWebBundle:OlimEtapaPeriodo')->findBy(array(
			'olimRegistroOlimpiada'=>$id,
			'olimEtapaTipo'=>$idEtapa
		));

		if(!$registro){
			$entity = new OlimEtapaPeriodo();
			$entity->setOlimRegistroOlimpiada($em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id));
			$entity->setOlimEtapaTipo($em->getRepository('SieAppWebBundle:OlimEtapaTipo')->find($idEtapa));
			$entity->setFechaInicio(new \DateTime('now'));
			$entity->setFechaFin(new \DateTime('now'));
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

		return $this->render('SieOlimpiadasBundle:OlimEtapaPeriodo:response.html.twig', $data);

	}

	/**
	 * Function delete
	 */
	public function deleteAction($idEtapaPeriodo){

		$idEtapaPeriodo = explode('_',$idEtapaPeriodo);
		$idEtapaPeriodo = $idEtapaPeriodo[1];

		$id = $this->session->get('idOlimpiada');

		$em = $this->getDoctrine()->getManager();
		$registro = $em->getRepository('SieAppWebBundle:OlimEtapaPeriodo')->find($idEtapaPeriodo);

		if($registro){
			$em->remove($registro);
			$em->flush();
		
			$status = 200;
			$mensaje = 'Registro eliminado correctamente';
		}else{
			$status = 300;
			$mensaje = 'No se pudo eliminar el registro';
		}

		$data = $this->datosVista();
		$data['status'] = $status; 
		$data['mensaje'] = $mensaje;

		return $this->render('SieOlimpiadasBundle:OlimEtapaPeriodo:response.html.twig', $data);

	}

	public function saveFechasAction(Request $request){
		$etapaId = $request->get('etapaId');
		$fechaInicio = $request->get('fechaInicio');
		$fechaFin = $request->get('fechaFin');

		$em = $this->getDoctrine()->getManager();

		for ($i=0; $i < count($etapaId); $i++) { 
			$etapaPeriodo = $em->getRepository('SieAppWebBundle:OlimEtapaPeriodo')->find($etapaId[$i]);
			if($etapaPeriodo){
				$etapaPeriodo->setFechaInicio(new \DateTime($fechaInicio[$i]));
				$etapaPeriodo->setFechaFin(new \DateTime($fechaFin[$i]));

				// $em->persist($etapaPeriodo);
				$em->flush();
			}
		}
		return $this->redirectToRoute('olimmateriatipo');
	}

}