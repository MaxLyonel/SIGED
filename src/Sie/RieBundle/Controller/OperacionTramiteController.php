<?php

namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\TtecAreaFormacionTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaAreaFormacionAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaSede;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico;
use Sie\AppWebBundle\Form\InstitucioneducativaType;


/**
 * OperacionTramiteController controller.
 *
 */
class OperacionTramiteController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    /**
     * Index
     */
    public function indexAction(Request $request) {

    }

    /**
     * Muestra listado de resoluciones de la carrera
     */    
    public function listAction(Request $request){    
        $em = $this->getDoctrine()->getManager(); 
        $resol = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($request->get('idresolucion'));
        $idRie = $resol->getTtecInstitucioneducativaCarreraAutorizada()->getInstitucioneducativa()->getId();
        $idCarrera = $resol->getTtecInstitucioneducativaCarreraAutorizada()->getTtecCarreraTipo()->getId();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($idCarrera);
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecResolucionCarrera a 
                                     JOIN a.ttecInstitucioneducativaCarreraAutorizada b
                                     JOIN b.ttecCarreraTipo c
                                    WHERE c.id = :idCarrera
                                 ORDER BY a.fecha DESC');                       
        $query->setParameter('idCarrera', $idCarrera);
        $resoluciones = $query->getResult();   

        return $this->render('SieRieBundle:OperacionTramite:list.html.twig', array('entity' => $entity, 'resoluciones' => $resoluciones, 'carrera' => $carrera, 'idresol' => $resol->getId()));        
    }

    /***
     * Realiza la operación de trámite RATIFICACIÓN y CIERRE DE CARRERA
     */ 
    public function newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($request->get('idresolucion'));
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')
                                                ->findOneById($datResolucion->getTtecInstitucioneducativaCarreraAutorizada()->getId());
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($datAutorizado->getInstitucioneducativa()->getId());
        
        if(!$datAutorizado){
            throw $this->createNotFoundException('No se puede encontrar la Oferta Académica');
        }else{
            $areasArray = $this->obtieneInstitucionAreaFormArray($institucion->getId());
            $nivelesArray = $this->obtieneInstitucionNivelesFormArray($institucion->getId());
            $regimenEstudioArray = $this->obtieneRegimenEstudio();
            
            if($institucion->getDependenciaTipo()->getId() == 3){
                $tipoOperacionArray = array('RATIFICACION'=>'RATIFICACIÓN DE CARRERA', 'CIERRE' =>'CIERRE DE CARRERA');
            }else{
                $tipoOperacionArray = array('CIERRE' =>'CIERRE DE CARRERA');
            }

            $tiempoEstArray = $this->obtieneTiempoEstudio($datResolucion->getNivelTipo()->getId(), $datResolucion->getTtecRegimenEstudioTipo()->getId());

            $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('oac_update_tramite'))
            ->add('idRie', 'hidden', array('data' => $institucion->getId()))
            ->add('id', 'hidden', array('data' => $datAutorizado->getId()))
            ->add('idresolucion', 'hidden', array('data' => $request->get('idresolucion')))
            ->add('ttecAreaFormacion', 'text', array('label' => 'Area de Formación', 'data' => $datAutorizado->getTtecCarreraTipo()->getTtecAreaFormacionTipo()->getAreaFormacion(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
            ->add('ttecCarreraTipo', 'text', array('label' => 'Carrera', 'data' => $datAutorizado->getTtecCarreraTipo()->getNombre(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
            ->add('tiempoEstudio', 'choice', array('label' => 'Tiempo de Estudio', 'data'=> $datResolucion->getTiempoEstudio(), 'required' => true,'choices'=>$tiempoEstArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('cargaHoraria', 'text', array('label' => 'Carga Horaria (Sólo números)', 'data'=>$datResolucion->getCargaHoraria(), 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '4') ))
            ->add('nivelTipo', 'choice', array('label' => 'Nivel de Formación', 'data'=>$datResolucion->getNivelTipo()->getId(), 'required' => true,'choices'=>$nivelesArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper'))) 
            ->add('regimenEstudio', 'choice', array('label' => 'Regimen de Estudio', 'data'=>$datResolucion->getTtecRegimenEstudioTipo()->getId(), 'required' => true,'choices'=>$regimenEstudioArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
            ->add('resolucion', 'text', array('label' => 'Resolución', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
            ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
            ->add('operacion', 'choice', array('label' => 'Operación Trámite', 'required' => true,'choices'=>$tipoOperacionArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            return $this->render('SieRieBundle:OfertaAcademica:edittramite.html.twig', array('form' => $form->getForm()->createView(), 'institucion'=> $institucion, 'datAutorizado' => $datAutorizado, 'datResolucion'=>$datResolucion));
            
        }        
    }

    /***
     * Crea una nueva resolución, dependiendo del tipo de trámite: RATIFICACION - CIERRE
     */
    public function createAction(Request $request){
    	try{
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($form['id']);
            $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($form['idresolucion']);

            //Registrando una resolucion de carrera, según trámite
            $resolucion = new TtecResolucionCarrera();
            $resolucion->setNumero($form['resolucion']);
            $resolucion->setFecha(new \DateTime($form['fechaResolucion']));
            $resolucion->setTtecResolucionTipo($em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findOneById(1)); //Por defecto guardando tipo de Resolucion = R.M.
            $resolucion->setTtecInstitucioneducativaCarreraAutorizada($datAutorizado);
            $resolucion->setFechaRegistro(new \DateTime('now'));
            $resolucion->setTiempoEstudio($form['tiempoEstudio']);
            $resolucion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($form['nivelTipo']));
            $resolucion->setCargaHoraria($form['cargaHoraria']);
            $resolucion->setTtecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findOneById($form['regimenEstudio']));
            $resolucion->setOperacion($form['operacion']);
            $em->persist($resolucion);
            $em->flush(); 
            
            if($form['operacion'] == 'CIERRE'){
                $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($form['id']);
                $datAutorizado->setEsVigente(FALSE);
                $datAutorizado->setFechaModificacion(new \DateTime('now'));
                $em->persist($datAutorizado);
                $em->flush();   
            }

            //Validando los datos
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar los datos');
        }  
        
        return $this->redirect($this->generateUrl('operacion_list', array('idRie' => $institucion->getId())));          
    }
}
