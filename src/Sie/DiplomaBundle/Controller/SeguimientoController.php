<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Documento;
use Doctrine\ORM\EntityRepository;

class SeguimientoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Despliega formulario de busqueda de los trámites de diploma
     * @return type
     */
    public function busquedaDiplomaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_detalle', '', '', 1)->createView()
                    , 'titulo' => 'Búsqueda'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los trámites de diploma por rude
     * @return type
     */
    public function busquedaDiplomaRudeAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_rude_detalle', '', '', 2)->createView()
                    , 'titulo' => 'Búsqueda'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los trámites de diploma por rude
     * @return type
     */
    public function busquedaDiplomaSerieAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_serie_detalle', '', '', 3)->createView()
                    , 'titulo' => 'Búsqueda'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }
    
    /**
     * Despliega formulario de busqueda y lista del flujo de los trámites de diploma 
     * @return type
     */
    public function busquedaDiplomaDetalleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        if ($form) {
            $tramite = $form['tramite'];
            $identificador = $form['identificador'];    
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("select 
                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno, iec.institucioneducativa_id as sie
                , td.fecha_envio, u1.username as usuario_remitente, u2.username as usuario_destinatario, pt.proceso_tipo as proceso, td.obs as comentario, d.documento_serie_id as serie
                , t.gestion_id as gestion_tramite
                from tramite_detalle as td 
                left join tramite as t on t.id = td.tramite_id
                left join (select * from documento where id in (select max(id) from documento where documento_estado_id = 1 and documento_tipo_id in (1,3,4,5) group by tramite_id)) as d on d.tramite_id = t.id
                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                left join estudiante as e on e.id = ei.estudiante_id
                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                left join proceso_tipo as pt on pt.id = fp.proceso_id
                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
                left join usuario as u1 on u1.id = td.usuario_remitente_id
                left join usuario as u2 on u2.id = td.usuario_destinatario_id
                where t.id = :tramiteId order by td.id asc");
            $query->bindValue(':tramiteId', $tramite);
            $query->execute();
            $entity = $query->fetchAll();
            
//            $em = $this->getDoctrine()->getManager();
//            $query = $em->getConnection()->prepare(" 
//                select distinct
//                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno
//                from tramite_detalle as td 
//                left join tramite as t on t.id = td.tramite_id
//                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
//                left join estudiante as e on e.id = ei.estudiante_id
//                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
//                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
//                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
//                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
//                where e.codigo_rude = '".$rude."' order by t.id desc
//                ");
//            $query->execute();
//            $entityCabecera = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_seguimiento_busqueda');
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_detalle', '', '', 1)->createView()
                    , 'entities' =>  $entity
                    , 'titulo' => 'Seguimiento'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }
    
    /**
     * Despliega formulario de busqueda y lista del flujo de los trámites de diploma por rude
     * @return type
     */
    public function busquedaDiplomaRudeDetalleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        if ($form) {
            $rude = $form['tramite'];
            $identificador = $form['identificador'];    
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("select 
                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno, iec.institucioneducativa_id as sie
                , td.fecha_envio, u1.username as usuario_remitente, u2.username as usuario_destinatario, pt.proceso_tipo as proceso, td.obs as comentario, d.documento_serie_id as serie
                , t.gestion_id as gestion_tramite
                from tramite_detalle as td 
                left join tramite as t on t.id = td.tramite_id
                left join (select * from documento where id in (select max(id) from documento where documento_estado_id = 1 and documento_tipo_id in (1,3,4,5) group by tramite_id)) as d on d.tramite_id = t.id
                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                left join estudiante as e on e.id = ei.estudiante_id
                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                left join proceso_tipo as pt on pt.id = fp.proceso_id
                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
                left join usuario as u1 on u1.id = td.usuario_remitente_id
                left join usuario as u2 on u2.id = td.usuario_destinatario_id
                where e.codigo_rude = '".$rude."' order by td.id asc");
            $query->execute();
            $entity = $query->fetchAll();
            
//            $em = $this->getDoctrine()->getManager();
//            $query = $em->getConnection()->prepare(" 
//                select distinct
//                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno
//                from tramite_detalle as td 
//                left join tramite as t on t.id = td.tramite_id
//                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
//                left join estudiante as e on e.id = ei.estudiante_id
//                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
//                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
//                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
//                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
//                where e.codigo_rude = '".$rude."' order by t.id desc
//                ");
//            $query->execute();
//            $entityCabecera = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_seguimiento_busqueda_rude');
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_rude_detalle', '', '', 2)->createView()
                    , 'entities' =>  $entity
                    , 'titulo' => 'Seguimiento'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }


    /**
     * Despliega formulario de busqueda y lista del flujo de los trámites de diploma por rude
     * @return type
     */
    public function busquedaDiplomaSerieDetalleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        if ($form) {
            $serie = $form['tramite'];
            $identificador = $form['identificador'];    
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("select 
                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno, iec.institucioneducativa_id as sie
                , td.fecha_envio, u1.username as usuario_remitente, u2.username as usuario_destinatario, pt.proceso_tipo as proceso, td.obs as comentario, d.documento_serie_id as serie
                , t.gestion_id as gestion_tramite
                from tramite_detalle as td 
                left join tramite as t on t.id = td.tramite_id
                left join (select * from documento where id in (select max(id) from documento where documento_estado_id = 1 and documento_tipo_id in (1,3,4,5) group by tramite_id)) as d on d.tramite_id = t.id
                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                left join estudiante as e on e.id = ei.estudiante_id
                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                left join proceso_tipo as pt on pt.id = fp.proceso_id
                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
                left join usuario as u1 on u1.id = td.usuario_remitente_id
                left join usuario as u2 on u2.id = td.usuario_destinatario_id
                where d.documento_serie_id = '".$serie."' order by td.id asc");
            $query->execute();
            $entity = $query->fetchAll();
            
//            $em = $this->getDoctrine()->getManager();
//            $query = $em->getConnection()->prepare(" 
//                select distinct
//                ft.flujo as flujo_tramite, t.id as numero_tramite, e.codigo_rude, e.carnet_identidad, e.nombre, e.paterno, e.materno
//                from tramite_detalle as td 
//                left join tramite as t on t.id = td.tramite_id
//                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
//                left join estudiante as e on e.id = ei.estudiante_id
//                left join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id                        
//                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
//                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
//                left join flujo_tipo as ft on ft.id = fp.flujo_tipo_id 
//                where e.codigo_rude = '".$rude."' order by t.id desc
//                ");
//            $query->execute();
//            $entityCabecera = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_seguimiento_busqueda_serie');
        }
        return $this->render('SieDiplomaBundle:Seguimiento:BusquedaTramiteDiploma.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_seguimiento_busqueda_serie_detalle', '', '', 3)->createView()
                    , 'entities' =>  $entity
                    , 'titulo' => 'Seguimiento'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }
      

    private function creaFormularioBuscador($routing, $value1, $value2, $identificador) {
        
        if ($identificador == 1){
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('tramite', 'text', array('label' => 'Nro. Trámite', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,8}',  'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        }  
        if ($identificador == 2){
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('tramite', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        }
        if ($identificador == 3){
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('tramite', 'text', array('label' => 'Numeror y Serie', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        }
        
        return $form;
    }    
}