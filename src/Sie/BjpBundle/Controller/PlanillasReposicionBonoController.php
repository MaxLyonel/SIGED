<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * EstudianteInscripcion controller.
 *
 */
class PlanillasReposicionBonoController extends Controller {

	public $session;
	public $idInstitucion;

	public function __construct() {
		$this->session = new Session();
	}

	/**
	 * Lista de estudiantes inscritos en la institucion educativa
	 *
	 */
	public function indexAction(Request $request){
		//crear conexion base de datos
		$em = $this->getDoctrine()->getManager();
		// Verificacmos si existe la session de usuario
		if (!$this->session->get('userId')) {
			return $this->redirect($this->generateUrl('login'));
		}
		/*$consultaAsig = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion');
		 $query = $consultaAsig->createQueryBuilder('bjpa')
		 ->leftjoin('SieAppWebBundle:BonojuancitoUnidadmilitar', 'bjpum', 'WITH', 'bjpa.bonojuancitoUnidadmilitar = bjpum.id')
		 ->where('bjpum.usuario = :id')
		 ->setParameter('id', $this->session->get('userId'))
		 ->getQuery();

		 $resultAsig = $query->getResult();

		 dump($resultAsig);die;*/
		$consultaFuerza = $em->getRepository('SieAppWebBundle:BonojuancitoFuerzaTipo');
		$query = $consultaFuerza->createQueryBuilder('bjpf')
		->getQuery();
		$resultFuerza = $query->getResult();
		$sieArray = array();
		foreach ($resultFuerza as $key => $value)
		{
			$sieArray[$value->getId()] = $value->getFuerza();

		}
		//dump($sieArray);die;


		// $resultGum = $query->getResult();
		// $gumArray = array();
		// foreach ($resultGum as $key => $value)
		// {
		//   $gumArray[$value->getId()] = $value->getGranUnidadmilitar();

		// }
		//dump($sieArray);die;
		return $this->render('SieBjpBundle:PlanillasReposicionBono:index.html.twig', array(
				'form' => $this->seleccionUeForm($sieArray)->createView()));
		echo '<script language="javascript">alert("juas0");</script>';
	}


	private function seleccionUeForm($sieArray){
		return $this->createFormBuilder()

		->add('idGestion', 'hidden', array('data' => $this->session->get('currentyear')))
		->add('fuerza', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGum()')))
		//->add('gum', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
		//->add('gum', 'choice', array('label' => 'Gran Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUnidadMilitar()')))
		->add('gum', 'choice', array('label' => 'Gran Unidad Militar' , 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUm()')))
		->add('um', 'choice', array('label' => 'Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarIe()')))
		->add('idInstitucion', 'choice', array('label' => 'Institución Educativa','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
		->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
        ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
        ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
		//->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $nivelArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
		//->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
		//->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
		->add('buscar', 'button', array('label' => 'Generar Planillas', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))
		->getForm();

	}

	public function cargarGumAction($fuerza)
	{
		//echo '<script language="javascript">alert("'.$fuerza.'");</script>';
		try {
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();
			// $query = $em->createQuery(
			//                    'SELECT  IDENTITY(bjp.gradoTipo) as gradoId,bjp.grado
			//            FROM SieAppWebBundle:BonojuancitoPaga bjp
			//            WHERE bjp.institucioneducativa = :id
			//            AND bjp.gestionTipo = :gestion
			//            AND bjp.nivelTipo = :nivel
			//            ORDER BY bjp.gradoTipo')
			//            ->setParameter('id', $idInstitucion)
			//            ->setParameter('gestion', $this->session->get('currentyear')-1)
			//            ->setParameter('nivel', $nivel);
			//    $grados = $query->getResult();
			/* $query = $em->createQuery(
			 'SELECT  fm.fuerza
			 FROM SieAppWebBundle:BonojuancitoGranUnidadmilitar bjp, SieAppWebBundle:BonojuancitoFuerzaTipo fm
			 WHERE bjp.bonojuancitoFuerzaTipo = :fuerza')

			->setParameter('fuerza', $fuerza);
			$gum = $query->getResult();*/
			 
			$consultaGranUnidadMil = $em->getRepository('SieAppWebBundle:BonojuancitoGranUnidadmilitar');
			$query = $consultaGranUnidadMil->createQueryBuilder('bjpgum')
			->leftjoin('SieAppWebBundle:BonojuancitoFuerzaTipo', 'bjpft', 'WITH', 'bjpgum.bonojuancitoFuerzaTipo = bjpft.id')
			->where('bjpft.id = :id')
			->setParameter('id', $fuerza)
			->getQuery();
			//dump($query->getSQL());die;
			//       $resultGranUnidadMil = $query->getResult();
			// dump($resultGranUnidadMil);die;

			//                 $gumArray = array();
			//   //              dump($gum);die;
			//             for ($i = 0; $i < count($resultGranUnidadMil); $i++) {
			//                 $gumArray[$resultGranUSnidadMil[$i]['id']] = $resultGranUnidadMil[$i]['fuerza'];
			//             }
			//             $em->getConnection()->commit();
			$resultFuerza = $query->getResult();
			$sieArray = array();
			foreach ($resultFuerza as $key => $value)
			{
				$sieArray[$value->getId()] = $value->getGranUnidadmilitar();

			}
			// dump($sieArray);die;
			$response = new JsonResponse();
			return $response->setData(array('fuerza' => $sieArray));

		} catch (Exception $ex) {
			//$em->getConnection()->rollback();
		}
	}

	public function cargarUmAction($gum)
	{
		//dump($gum);die;

		try {
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();
	
			$consultaUnidadMil = $em->getRepository('SieAppWebBundle:BonojuancitoUnidadmilitar');
			$query = $consultaUnidadMil->createQueryBuilder('bjpum')
			->leftjoin('SieAppWebBundle:BonojuancitoGranUnidadmilitar', 'bjpgum', 'WITH', 'bjpum.bonojuancitoGranUnidadmilitar= bjpgum.id')
			->where('bjpgum.id = :id')
			->setParameter('id', $gum)
	
			->getQuery();
			$resultGum = $query->getResult();
			//dump($resultGum);die;
			$sieArray = array();
			foreach ($resultGum as $key => $value)
			{
				$sieArray[$value->getId()] = $value->getUnidadmilitar();
	
			}
			//dump($sieArray);die;
			$response = new JsonResponse();
			return $response->setData(array('gum' => $sieArray));
	
		} catch (Exception $ex) {
			//$em->getConnection()->rollback();
		}
	}



	public function cargarIeAction($gum)
	{
		
		//dump($gum);die;
		
		try {
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();
			$consultaAsig = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion');
			$query = $consultaAsig->createQueryBuilder('bjpa')
			->leftjoin('SieAppWebBundle:BonojuancitoUnidadmilitar', 'bjpum', 'WITH', 'bjpa.bonojuancitoUnidadmilitar = bjpum.id')
			->where('bjpum.id = :id')
			->setParameter('id', $gum)
			->getQuery();
			
			$resultGum = $query->getResult();
			//dump($resultGum);die;
			//echo '<script language="javascript">alert("'.$resultGum.'");</script>';
			$sieArray = array();
			foreach ($resultGum as $key => $value)
			{
				$sieArray[$value->getId()] = $value->getInstitucionEducativa();

			}
			//dump($sieArray);die;
			$response = new JsonResponse();
			return $response->setData(array('um' => $sieArray));

		} catch (Exception $ex) {
			//$em->getConnection()->rollback();
		}
	}


	////////****/////

	 public function cargarNivelesAction ($idInstitucion , $gestion){
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //$formulario = $request->get('form');
      // Lista de turnos validos para la unidad educativa
      //dump($idInstitucion, $gestion);die;
      $query = $em->createQuery(
                      'SELECT DISTINCT  IDENTITY(bjp.nivelTipo) as nivelId, bjp.nivel
                        FROM SieAppWebBundle:BonojuancitoPaga bjp
                        LEFT JOIN SieAppWebBundle:BonojuancitoAsignacion bja WITH (bjp.institucioneducativa = bja.codSie)
                        WHERE bja.id = :id
                        AND bjp.gestionTipo = :gestion
                        ORDER BY bjp.nivelTipo')
              ->setParameter('id', $idInstitucion)
              ->setParameter('gestion', $this->session->get('currentyear')-1);
      $nivel = $query->getResult();
      //dump($nivel);die;

      $nivelArray = array();
      for ($i = 0; $i < count($nivel); $i++) {
          $nivelArray[$nivel[$i]['nivelId']] = $nivel[$i]['nivel'];
      }
      $em->getConnection()->commit();
      $response = new JsonResponse();
      return $response->setData(array('nivel' => $nivelArray));
  }	




	 public function cargarGradosAction($idInstitucion, $gestion, $nivel) {
            try {
            	//dump($gestion,$idInstitucion,$nivel);die;
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                
                $query = $em->createQuery(
                                'SELECT DISTINCT  IDENTITY(bjp.gradoTipo) as gradoId,bjp.grado
                        FROM SieAppWebBundle:BonojuancitoPaga bjp
                        LEFT JOIN SieAppWebBundle:BonojuancitoAsignacion bja WITH (bjp.institucioneducativa = bja.codSie)
                        WHERE bja.id = :id
                        AND bjp.gestionTipo = :gestion
                        AND bjp.nivelTipo = :nivel
                        ORDER BY bjp.gradoTipo')
                        ->setParameter('id', $idInstitucion)
                        ->setParameter('gestion', $this->session->get('currentyear')-1)
                        ->setParameter('nivel', $nivel);
                $grados = $query->getResult();

                $gradosArray = array();
                for ($i = 0; $i < count($grados); $i++) {
                    $gradosArray[$grados[$i]['gradoId']] = $grados[$i]['grado'];
                }
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('grados' => $gradosArray));
            } catch (Exception $ex) {
                //$em->getConnection()->rollback();
            }
        }


        public function cargarParalelosAction($idInstitucion, $gestion, $nivel, $grado) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                
                $query = $em->createQuery(
                                'SELECT DISTINCT  IDENTITY(bjp.paraleloTipo) as paraleloId,bjp.paralelo
                        FROM SieAppWebBundle:BonojuancitoPaga bjp
                        LEFT JOIN SieAppWebBundle:BonojuancitoAsignacion bja WITH (bjp.institucioneducativa = bja.codSie)
                        WHERE bja.id = :id
                        AND bjp.gestionTipo = :gestion
                        AND bjp.nivelTipo = :nivel
                         AND bjp.gradoTipo = :grado
                        ORDER BY bjp.paraleloTipo')
                        ->setParameter('id', $idInstitucion)
                        ->setParameter('gestion', $this->session->get('currentyear')-1)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado);
                $paralelos = $query->getResult();

                $paralelosArray = array();
                for ($i = 0; $i < count($paralelos); $i++) {
                    $paralelosArray[$paralelos[$i]['paraleloId']] = $paralelos[$i]['paralelo'];
                }
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('paralelos' => $paralelosArray));
            } catch (Exception $ex) {
                //$em->getConnection()->rollback();
            }
        }
	
	
	
	/////////////////////////////
	public function buscarInfoUeAction(Request $request){
		return $this->render('SieBjpBundle:PlanillasReposicionBono:index.html.twig', array(
				'form' => $this->seleccionUeForm()->createView()
		));
	}
}

