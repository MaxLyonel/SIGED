<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * EstudianteInscripcion controller.
 *
 */
class PreinscripcionSorteoController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':PreinscripcionSorteovista:index.html.twig', array(
            'form' => $this->formSearch()->createView(),
        ));
    }

    private function formSearch() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('preinscripcion_sorteo_result'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function resultAction(Request $request) {

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucionHabilitada = $em->getRepository('SieAppWebBundle:InstitucioneducativaRueRegularizacion')->findOneBy(array('institucioneducativa' => $institucion));
                if($institucionHabilitada) {
                    if($institucionHabilitada->getEsactivo()){
                        $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa ya realizó el "Sorteo de Fecha".');
                        return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
                    } else {
                        return $this->render($this->session->get('pathSystem') . ':PreinscripcionSorteovista:result.html.twig', array(
                            'institucion' => $institucion,
                            'form' => $this->formFechaFundacion($institucion->getId())->createView(),
                        ));
                    }
                } else {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra habilitada para este operativo.');
                    return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
                }
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa ya realizó el sorteo.');
            return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
        }
    }

    private function formFechaFundacion($sie) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('preinscripcion_sorteo_save'))
                ->add('sie', 'hidden', array('data' => $sie))
                ->add('fechaFundacion', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','required' => true))
                // ->add('adjunto', 'file', array('attr' => array('title'=>"Adjuntar Respaldo",'accept'=>"application/pdf,.doc,.docx",'class'=>'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar'))
                ->getForm();
        return $form;
    }

    public function saveAction(Request $request) {

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucionHabilitada = $em->getRepository('SieAppWebBundle:InstitucioneducativaRueRegularizacion')->findOneBy(array('institucioneducativa' => $institucion));
                
                if($institucionHabilitada) {
                    if($institucionHabilitada->getEsactivo()){
                        $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa ya realizó el "Sorteo de Fecha".');
                        return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
                    } 

                    else {
                        $documento = $request->files->get('form')['adjunto'];

                        if(!empty($documento)){
                            $root_rue_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/sorteo/'.$form['sie'];
                           
                            if (!file_exists($root_rue_path)) {
                                mkdir($root_rue_path, 0777);
                            }
                            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/sorteo/'.$form['sie'];
                            
                            if (!file_exists($destination_path)) {
                                mkdir($destination_path, 0777);
                            }
                            $imagen = $form['sie'].date('YmdHis').'.'.$documento->getClientOriginalExtension();
                            $documento->move($destination_path, $imagen);
                            
                            $institucionHabilitada->formFechaFundacion(new \DateTime($form['fechaFundacion']));
                            $institucionHabilitada->setRutaAdjunto($imagen);
                            $institucionHabilitada->setFecharegistro(new \DateTime('now'));
                            $em->persist($institucionHabilitada);
                            $em->flush();

                            $this->get('session')->getFlashBag()->add('newOk', 'Registro realizado satisfactoriamente.');

                            return $this->render($this->session->get('pathSystem') . ':PreinscripcionSorteovista:saved.html.twig', array(
                                'institucion' => $institucionHabilitada
                            ));
                        }else{
                            
                            // $this->get('session')->getFlashBag()->add('newError', 'No se ha cargado ningún archivo adjunto.');
                            // return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
                            return $this->render($this->session->get('pathSystem') . ':PreinscripcionSorteovista:saved.html.twig', array(
                                'institucion' => $institucionHabilitada
                            ));
                        }
                    }
              
                } else {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra habilitada para este operativo.');
                    return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
                }
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra registrada.');
            return $this->redirect($this->generateUrl('preinscripcion_sorteo_index'));
        }
    }

    public function impresionDDJJAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('ddjjRue')['sie']);
        $institucionHabilitada = $em->getRepository('SieAppWebBundle:InstitucioneducativaRueRegularizacion')->findOneBy(array('institucioneducativa' => $institucion));
        $institucionHabilitada->setEsactivo('t');
        $em->persist($institucionHabilitada);
        $em->flush();

        $arch = 'DDJJ_RUE_FECHAFUNDACION' . $request->get('ddjjRue')['sie'] . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_rue_fechafundacion_v1_afv.rptdesign&__format=pdf&&codue=' . $request->get('ddjjRue')['sie'] .'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function impresionSeguimientoAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();

        $arch = 'SEGUIMIENTO_RUE_FECHAFUNDACION' . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_seguimiento_rue_fechafundacion_v1_afv.rptdesign&__format=pdf&&dpto=' . $request->get('seguimientoRue')['dpto'] .'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
