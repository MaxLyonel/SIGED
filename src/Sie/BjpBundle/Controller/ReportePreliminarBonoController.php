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
class ReportePreliminarBonoController extends Controller {

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
      return $this->render('SieBjpBundle:ReportePreliminarBono:index.html.twig', array(
             'form' => $this->seleccionUeForm($sieArray)->createView()));
              echo '<script language="javascript">alert("juas0");</script>'; 
    }

    
    public function registroresultadoAction(Request $request){      
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getRegistroResultados();
        //dump($repository);    
        //die;
        return $this->render('SieBjpBundle:ReportePreliminarBono:list.html.twig', array(
                'miObjeto' => $repository,
        ));
       
      
    }
    
    
    public function registroresultadopreAction(Request $request, $sie){      
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
               
        $rol_usuario = $this->session->get('roluser');

        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getRegistroResultados();

        $datosUnidadEduAsignacion = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getInfoAgenteUnidad($sie);

        //dump($datosUnidadEduAsignacion[0]);die;
        
        return $this->render('SieBjpBundle:ReportePreliminarBono:registroResultados.html.twig', array(
                'form' => $this->resultadosRegistrarForm($datosUnidadEduAsignacion[0])->createView(),
                'miObjeto' => $repository,
                'info' => $datosUnidadEduAsignacion[0],
        ));
      
    }

    public function registrarresultadosAction(Request $request){
        $form = $request->get('form');

        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        try {
             $em = $this->getDoctrine()->getManager();
             $em->getConnection()->beginTransaction();
               
             $object = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->find($form['c_hiddenid']);
             
             $object->setPagadoEnPeriodo($form['c_registros']);
             $object->setTotalPlanificado($form['c_pagados']);
             $object->setPagadoRezagado($form['c_rezagados']);
             
             
             $em->persist($object);
             $em->flush();    
             
             $em->getConnection()->commit();
             //bjpa.pagadoEnPeriodo, bjpa.pagadoRezagado, bjpa.totalPlanificado
             //dump($object);die;   
             return $this->redirect($this->generateUrl('RegistroResultados'));
             //return $response->setData(array('fuerza' => $sieArray));

        } catch (Exception $ex) {
             $em->getConnection()->rollback();
        }
        
        
        
    }    
    
    
    private function resultadosRegistrarForm($sieArray){
        return $this->createFormBuilder()
//                    ->setAction($this->generateUrl('sie_bono_resultado_insert'))
                    ->add('c_registros', 'text', array('label' => 'TOTAL DE REGISTROS:', 'required' => true, 'attr' => array('class' => 'form-control', 'value' => $sieArray['totalPlanificado'] )))
                    ->add('c_pagados', 'text', array('label' => 'TOTAL DE PAGADOS EN TIEMPO:', 'required' => true, 'attr' => array('class' => 'form-control', 'value' => $sieArray['pagadoEnPeriodo'])))
                    ->add('c_rezagados', 'text', array('label' => 'TOTAL DE REZAGADOS:', 'required' => true, 'attr' => array('class' => 'form-control', 'value' => $sieArray['pagadoRezagado'])))
                    ->add('c_hiddenid', 'hidden', array('attr' => array('class' => 'form-control', 'value' => $sieArray['bjpaId'])))
                    ->add('c_guardar', 'submit', array("attr" => array("id" => "submit", "type" => "button", "class" => "btn btn-success glyphicon glyphicon-ok-sign"), 'label' => ' Guardar')) 
//                    ->add('c_guardar', 'submit', array('label' => ' Guardar', 'attr' => array('class' => 'btn btn-default btn-large glyphicon glyphicon-remove-sign')))
                    ->getForm();

    }    
    
    private function seleccionUeForm($sieArray){
        return $this->createFormBuilder()
                    
                    ->add('fuerza', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    //->add('gum', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    //->add('gum', 'choice', array('label' => 'Gran Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUnidadMilitar()')))
        			->add('gum', 'choice', array('label' => 'Gran Unidad Militar' , 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUm()')))
                    ->add('um', 'choice', array('label' => 'Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    ->add('buscar', 'button', array('label' => 'Generar Planillas', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))
                    //->add('dueDate', 'date', ['widget' => 'single_text', 'format' => 'dd-MM-yyyy'])
                   ->add('c_fechaInscripcion', 'text', array('label' => 'Fecha Consulta', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->getForm();

    }

    public function cargarNivelesAction($fuerza) 
   {
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


/////////////////////////////
    public function buscarInfoUeAction(Request $request){
      return $this->render('SieBjpBundle:PagosBono:index.html.twig', array(
            'form' => $this->seleccionUeForm()->createView()
        ));
    }
}

