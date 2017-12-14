<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecPensum;

/**
 * Institucioneducativa Controller.
 *
 */
class PensumController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    public function indexAction(Request $request) {
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $ieducativa_id = $request->getSession()->get('idInstitucion');
        $gestion_id = $request->getSession()->get('idGestion');
        $carrera_id = $request->getSession()->get('idCarrera');
        $denominacion_id = $request->getSession()->get('idDenominacion');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $pensum = $em->getRepository('SieAppWebBundle:TtecPensum')->findBy(array('ttecDenominacionTituloProfesionalTipo' => $denominacion_id));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);

        return $this->render($this->session->get('pathSystem') . ':Pensum:index.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $gestion_id,
            'pensum' => $pensum,
            'denominacion' => $denominacion_id
        ));
    }    

    /*
     * Llamamos al formulario para nuevo operativo/calendario
     */

    public function newAction(Request $request) {
        
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');
        
        $ieducativa_id = $form['idInstitucion'];
        $gestion_id = $form['idGestion'];
        $denominacion_id = $form['idDenominacion'];
        $carrera_id = $request->getSession()->get('idCarrera');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);
        $denominacion = $em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')->findOneById($denominacion_id);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera_id);

        return $this->render($this->session->get('pathSystem') . ':Pensum:new.html.twig', array(
                    'form' => $this->newPensumForm($ieducativa_id, $gestion_id, $denominacion_id)->createView(),
                    'institucion' => $institucion,
                    'denominacion' => $denominacion,
                    'carrera' => $carrera,
                    'gestion' => $gestion_id
        ));
    }

     /*
     * formulario de nueva/o operativo
     */
    private function newPensumForm($idInstitucion, $idGestion, $idDenominacion) {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dgesttla_carrera_pensum_create'))
            ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
            ->add('idGestion', 'hidden', array('data' => $idGestion))
            ->add('idDenominacion', 'hidden', array('data' => $idDenominacion))
            ->add('pensum', 'text', array('label' => 'Nombre del Pensum:', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('resolucionAdm', 'text', array('label' => 'Resolución Administrativa:', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('nroResolucion', 'text', array('label' => 'Número de Resolución:', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
            
        return $form;
    }

    /*
     * registramos el nuevo operativo
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            $pensum = $em->getRepository('SieAppWebBundle:TtecPensum')->findOneById($form['idDenominacion']);

            if ($pensum) {
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, el pensum ya se encuentra registrado.');
                return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
            }

            // Registro pensum
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_pensum');")->execute();
            $pensumNew = new TtecPensum();
            $pensumNew->setPensum(mb_strtoupper($form['pensum']), 'utf-8');
            $pensumNew->setTtecDenominacionTituloProfesionalTipo($em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')->findOneById($form['idDenominacion']));
            $pensumNew->setGestionTipoId($form['idGestion']);
            $pensumNew->setResolucionAdministrativa(mb_strtoupper($form['resolucionAdm']), 'utf-8');
            $pensumNew->setNroResolucion(mb_strtoupper($form['nroResolucion']), 'utf-8');
            $pensumNew->setFechaRegistro(new \DateTime('now'));
            $pensumNew->setEsVigente(1);
            $em->persist($pensumNew);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Llamar al formulario de edicion
     */
    public function editAction(Request $request) {
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('formE');

        $ieducativa_id = $form['idInstitucion'];
        $gestion_id = $form['idGestion'];
        $denominacion_id = $form['idDenominacion'];
        $pensum_id = $form['idPensum'];
        $carrera_id = $request->getSession()->get('idCarrera');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);
        $denominacion = $em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')->findOneById($denominacion_id);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera_id);

        return $this->render($this->session->get('pathSystem') . ':Pensum:edit.html.twig', array(
                    'form' => $this->editPensumForm($ieducativa_id, $gestion_id, $pensum_id)->createView(),
                    'institucion' => $institucion,
                    'denominacion' => $denominacion,
                    'carrera' => $carrera,
                    'gestion' => $gestion_id
        ));
    }

    /*
    * formulario de edicion
    */
    private function editPensumForm($idInstitucion, $idGestion, $idPensum) {
        $em = $this->getDoctrine()->getManager();
        $pensum = $em->getRepository('SieAppWebBundle:TtecPensum')->findOneById($idPensum);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dgesttla_carrera_pensum_update'))
            ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
            ->add('idGestion', 'hidden', array('data' => $idGestion))
            ->add('idPensum', 'hidden', array('data' => $idPensum))
            ->add('pensum', 'text', array('label' => 'Nombre del Pensum:', 'required' => true, 'data' => $pensum->getPensum(), 'attr' => array('class' => 'form-control')))
            ->add('resolucionAdm', 'text', array('label' => 'Resolución Administrativa:', 'required' => true, 'data' => $pensum->getResolucionAdministrativa(), 'attr' => array('class' => 'form-control')))
            ->add('nroResolucion', 'text', array('label' => 'Número de Resolución:', 'required' => true, 'data' => $pensum->getNroResolucion(), 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar cambios', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $form;
    }

    /*
    * guardar datos de modificacion
    */
    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            
            // Actiualización pensum
            $pensumEdit = $em->getRepository('SieAppWebBundle:TtecPensum')->findOneById($form['idPensum']);
            $pensumEdit->setPensum(mb_strtoupper($form['pensum']), 'utf-8');
            $pensumEdit->setGestionTipoId($form['idGestion']);
            $pensumEdit->setResolucionAdministrativa(mb_strtoupper($form['resolucionAdm']), 'utf-8');
            $pensumEdit->setNroResolucion(mb_strtoupper($form['nroResolucion']), 'utf-8');
            $pensumEdit->setFechaModificacion(new \DateTime('now'));
            $em->persist($pensumEdit);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        }
    }

    /*
    * Eliminacion de operativo
    */
    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('formD');
        $pensumDelete = $em->getRepository('SieAppWebBundle:TtecPensum')->findOneById($form['idPensum']);

        $em->getConnection()->beginTransaction();
        try {
            //eliminamos el registro
            $em->remove($pensumDelete);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar.');
            $em->getConnection()->rollback();
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        }
    }

    /*
    * guardar datos de modificacion
    */
    public function esVigenteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('formV');
        
        try {           
            // Actiualización pensum
            $pensumVigente = $em->getRepository('SieAppWebBundle:TtecPensum')->findOneById($form['idPensum']);
            $pensumVigente->setEsVigente(1 - $pensumVigente->getEsVigente());
            $em->persist($pensumVigente);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_pensum'));
        }
    }

}
