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
class ImpresionAvanceDAreaBonoController extends Controller {

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
      return $this->render('SieBjpBundle:ImpresionAvanceDAreaBono:index.html.twig', array(
             'form' => $this->seleccionUeForm($sieArray)->createView()));
              //echo '<script language="javascript">alert("juas0");</script>'; 
    }


    private function seleccionUeForm($sieArray){
        return $this->createFormBuilder()
                    
                    //->add('fuerza', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    //->add('gum', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    //->add('gum', 'choice', array('label' => 'Gran Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUnidadMilitar()')))
        			//->add('gum', 'choice', array('label' => 'Gran Unidad Militar' , 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUm()')))
                    //->add('um', 'choice', array('label' => 'Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                      ->add('buscar', 'button', array('label' => 'Mostrar Reporte', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))
                    //->add('dueDate', 'date', ['widget' => 'single_text', 'format' => 'dd-MM-yyyy'])
                   ->add('c_fechaInscripcion', 'text', array('label' => 'Fecha Consulta', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->getForm();

    }

   
/////////////////////////////
    public function buscarInfoUeAction(Request $request){
      return $this->render('SieBjpBundle:PagosBono:index.html.twig', array(
            'form' => $this->seleccionUeForm()->createView()
        ));
    }
}