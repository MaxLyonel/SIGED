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
class AreasController extends Controller {

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
     * Muestra la lista de areas
     */
    public function listAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecAreaFormacionTipo a 
                                    WHERE a.institucioneducativaTipo in (:idTipo)
                                 ORDER BY a.areaFormacion ASC')
                    ->setParameter('idTipo', array(7));                                
        $areas = $query->getResult(); 

        $query = $em->createQuery('SELECT a
                                    FROM SieAppWebBundle:TtecAreaFormacionTipo a 
                                WHERE a.institucioneducativaTipo in (:idTipo)
                                ORDER BY a.areaFormacion ASC')
                            ->setParameter('idTipo', array(8));                                
        $areas1 = $query->getResult(); 


        return $this->render('SieRieBundle:Areas:list.html.twig', array('areas' => $areas, 'areas1' => $areas1));
     }   

    /**
     * Muestra formulario new de areas
     */
    public function newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $tipoArray = array();
        $query = $em->createQuery('SELECT it
                                    FROM SieAppWebBundle:InstitucioneducativaTipo it
                                    WHERE it.id IN (:idTipo)')                                    
                        ->setParameter('idTipo', array(7, 8));
        $datos = $query->getResult();
        foreach($datos as $dato)
        {
            $tipoArray[$dato->getId()] = $dato->getDescripcion();
        }

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('area_rie_create'))
        ->add('institucioneducativaTipo', 'choice', array('label' => 'Tipo de Institución Educativa', 'required' => true,'choices'=>$tipoArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('area', 'text', array('label' => 'Area', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '130') ))
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            
        return $this->render('SieRieBundle:Areas:new.html.twig', array('form' => $form->getForm()->createView()));
    }


    /**
     * Guarda datos de form new
     */
     public function createAction(Request $request) {
    	try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $query = $em->createQuery('SELECT at
                                         FROM SieAppWebBundle:TtecAreaFormacionTipo at
                                        WHERE at.areaFormacion = :aForm ')                                    
                            ->setParameter('aForm', $form['area']);
            $datos = $query->getResult();
            if($datos)
            {
                $this->get('session')->getFlashBag()->add('mensaje', 'Ya existe el Area de Formacion');
                return $this->redirect($this->generateUrl('area_rie_list'));  
            }
            else
            {
                $entity = new TtecAreaFormacionTipo();
                $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucioneducativaTipo']));
                $entity->setAreaFormacion(strtoupper($form['area']));
                $entity->setFechaRegistro(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();     
            }
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el área de formación.');
        }
        return $this->redirect($this->generateUrl('area_rie_list'));  
    }            


    /**
     * Muestra formulario para modificar la oferta académica
     */
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $tipoArray = array();
        $query = $em->createQuery('SELECT it
                                    FROM SieAppWebBundle:InstitucioneducativaTipo it
                                    WHERE it.id IN (:idTipo)')                                    
                        ->setParameter('idTipo', array(7, 8));
        $datos = $query->getResult();
        foreach($datos as $dato)
        {
            $tipoArray[$dato->getId()] = $dato->getDescripcion();
        }

        $area = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($request->get('idArea'));

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('area_rie_update'))
        ->add('idArea', 'hidden', array('data' => $area->getId()))
        ->add('institucioneducativaTipo', 'choice', array('label' => 'Tipo de Institución Educativa', 'data' => $area->getInstitucioneducativaTipo()->getId(), 'required' => true,'choices'=>$tipoArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('area', 'text', array('label' => 'Area', 'data'=> $area->getAreaFormacion() , 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '130') ))
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            
        return $this->render('SieRieBundle:Areas:edit.html.twig', array('form' => $form->getForm()->createView()));        
    }

    /**
     * Guarda datos de las modificaciones 
     */
    public function updateAction(Request $request) {
    	try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $area = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($form['idArea']);
            $area->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucioneducativaTipo']));
            $area->setAreaFormacion(strtoupper($form['area']));
            $area->setFechaModificacion(new \DateTime('now'));
            $em->persist($area);
            $em->flush();   
                        
            //Validando los datos
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al modificar los datos');
        }  
        
        return $this->redirect($this->generateUrl('area_rie_list'));          
    }

  
    /**
     * Elimina el registro de oferta académica
     */
    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $area = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($request->get('idArea'));

        $em->remove($area);
        $em->flush();

        return $this->redirect($this->generateUrl('area_rie_list'));
        
    }

}
