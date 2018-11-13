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
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada;
use Sie\AppWebBundle\Entity\TtecResolucionCarrera;
use Sie\AppWebBundle\Entity\TtecResolucionTipo;
use Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo;
use Sie\AppWebBundle\Entity\TtecCarreraTipo;
use Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo;
use Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo;

use Sie\AppWebBundle\Form\InstitucioneducativaType;

/**
 * Institucioneducativa controller.
 *
 */
class CarrerasController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    /**
     * Muestra formulario de Busqueda de la institución educativa
     */
    public function indexAction(Request $request) {
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:prueba.html.twig');
    }

    /**
     * Muestra la lista de carreras
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT carrera.*, area.area_formacion, count(autorizada.id) AS cantidad
                    FROM ttec_carrera_tipo AS carrera
              INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id 
               LEFT JOIN ttec_institucioneducativa_carrera_autorizada AS autorizada ON carrera.id = autorizada.ttec_carrera_tipo_id 
                   WHERE carrera.ttec_area_formacion_tipo_id < 200 
                GROUP BY carrera.id, area.area_formacion
                ORDER BY carrera.nombre ASC ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $carreras = $stmt->fetchAll();

        return $this->render('SieRieBundle:Carreras:list.html.twig', array('carreras' => $carreras));
     }   

    /**
     * Muestra formulario new de carreras
     */
    public function newAction(){
        $em = $this->getDoctrine()->getManager();
        $arrayArea = $this->obtenerAreas();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('carrera_rie_create'))
                ->add('ttecAreaFormacionTipo', 'choice', array('label' => 'Área de Formación', 'required' => true,'choices'=>$arrayArea ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('carrera', 'text', array('label' => 'Carrera', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '100', 'style' => 'text-transform:uppercase') ))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            
        return $this->render('SieRieBundle:Carreras:new.html.twig', array('form' => $form->getForm()->createView()));
    }


    /**
     * Guarda datos de form new
     */
     public function createAction(Request $request) {
    	try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            //buscando la carrera
            $query = $em->createQuery('SELECT ca
                                         FROM SieAppWebBundle:TtecCarreraTipo ca
                                        WHERE UPPER(ca.nombre) LIKE :nombreCarrera')
                                    ->setParameter('nombreCarrera', trim(strtoupper($form['carrera'])));        
            $dato = $query->getResult(); 

            if($dato){
                $this->get('session')->getFlashBag()->add('mensaje', 'Duplicidad al registrar la carrera.');
            }else{
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_carrera_tipo');")->execute();
                $entity = new TtecCarreraTipo();
                $entity->setNombre(strtoupper($form['carrera']));
                $entity->setFechaRegistro(new \DateTime('now'));
                $entity->setTtecAreaFormacionTipo($em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($form['ttecAreaFormacionTipo']));
                $entity->setTtecEstadoCarreraTipo($em->getRepository('SieAppWebBundle:TtecEstadoCarreraTipo')->findOneById(1));
                $em->persist($entity);
                $em->flush();     
            }
        } catch (Exception $ex){
            $em->getConnection()->rollback();
            //$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la carrera.');
        }
        return $this->redirect($this->generateUrl('carrera_rie_list'));  
    }            


    /**
     * Muestra formulario para modificar la oferta académica
     */
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findById($request->get('idCarrera'));
        $arrayArea = $this->obtenerAreas();

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('carrera_rie_update'))
        ->add('idCarrera', 'hidden', array('data' => $request->get('idCarrera')))
        ->add('ttecAreaFormacionTipo', 'choice', array('label' => 'Área de Formación', 'data' => $carrera[0]->getTtecAreaFormacionTipo()->getId(), 'required' => true,'choices'=>$arrayArea ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))        
        ->add('carrera', 'text', array('label' => 'Carrera', 'data' => $carrera[0]->getNombre(), 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '100') ))
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            
        return $this->render('SieRieBundle:Carreras:edit.html.twig', array('form' => $form->getForm()->createView()));           
    
    }

    /**
     * Guarda datos de las modificaciones 
     */
    public function updateAction(Request $request) {
    	try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($form['idCarrera']);

            //buscando la carrera
            $query = $em->createQuery('SELECT ca
                                         FROM SieAppWebBundle:TtecCarreraTipo ca
                                        WHERE UPPER(ca.nombre) LIKE :nombreCarrera
                                        AND ca.ttecAreaFormacionTipo = :ttecArea')
                            ->setParameter('nombreCarrera', trim(strtoupper($form['carrera'])))
                            ->setParameter('ttecArea', $form['ttecAreaFormacionTipo']);        
            $dato = $query->getResult(); 

            if($dato){
                $this->get('session')->getFlashBag()->add('mensajeError', 'Duplicidad al modificar la carrera.');
            }else{
                $carrera->setNombre(strtoupper($form['carrera']));
                $carrera->setFechaModificacion(new \DateTime('now'));
                $carrera->setTtecAreaFormacionTipo($em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($form['ttecAreaFormacionTipo']));
                $em->persist($carrera);
                $em->flush();   
                $this->get('session')->getFlashBag()->add('mensajeOk', 'Registro actualizado correctamente.');
            }    
                
            //Validando los datos
        }catch (Exception $ex){
            //$em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al modificar los datos');
        }  
        
        return $this->redirect($this->generateUrl('carrera_rie_list'));   
    }

    /**
     * Elimina el registro de oferta académica
     */
    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($request->get('idCarrera'));
        $em->remove($carrera);
        $em->flush();
        
        return $this->redirect($this->generateUrl('carrera_rie_list')); 
    }

    /**
     *  Obtiene lista de areas de formacion
     */
    public function obtenerAreas(){
        $em = $this->getDoctrine()->getManager();
        $arrayArea = array();
        $db = $em->getConnection();
        $query = "SELECT area.*
                    FROM ttec_area_formacion_tipo AS area
                   WHERE area.id < 200 
                ORDER BY area_formacion ASC";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $datos = $stmt->fetchAll();
        foreach($datos as $dato){
            $arrayArea[$dato['id']] = $dato['area_formacion'];
        }   
        return $arrayArea;
    }
}
