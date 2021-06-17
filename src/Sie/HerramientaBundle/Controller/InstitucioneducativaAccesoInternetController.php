<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaAccesoInternet;
use Sie\AppWebBundle\Entity\InstitucioneducativaAccesoInternetDatos;

/**
 * InstitucioneducativaAccesoInternet Controller
 */
class InstitucioneducativaAccesoInternetController extends Controller {

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

        return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:index.html.twig', array(
            'form' => $this->formSearch()->createView(),
        ));
    }

    private function formSearch() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('ie_acceso_internet_result'))
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
        $gestion = $request->getSession()->get('currentyear');

        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'esactivo' => true));

                if($iai) {
                    $this->get('session')->getFlashBag()->add('newError', 'La Institución Educativa ya realizó el reporte de información.');
                    return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
                }

                return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:result.html.twig', array(
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'form' => $this->formAccesoInternet($institucion->getId(), $gestion)->createView(),
                ));
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra registrada.');
            return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
        }
    }

    private function formAccesoInternet($sie, $gestion) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('ie_acceso_internet_save'))
            ->add('sie', 'hidden', array(
                'data' => $sie
            ))
            ->add('gestion', 'hidden', array(
                'data' => $gestion
            ))
            ->add('tieneAcceso', 'choice', array(
                'required' => true,
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('proveedor', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => false,
                'class' => 'SieAppWebBundle:AccesoInternetProveedorTipo',
                'property' => 'proveedor',
                'empty_value' => 'Seleccionar...',
                'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width:100%')
            ))
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
        
        $sie = $form['sie'];
        $gestionid = $form['gestion'];
        $tieneAcceso = $form['tieneAcceso'];
        if($tieneAcceso == '1') {
            $proveedorArray = $form['proveedor'];
        } else {
            $proveedorArray = [];
        }

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
        $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestionid);
        
        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $datos = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternetDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));                

                if($iai) {
                    if($iai->getEsactivo()) {
                        $this->get('session')->getFlashBag()->add('newError', 'La Institución Educativa ya realizó el reporte de información.');
                        return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
                    }
                    $em->remove($iai);
                    $em->flush();
                }

                foreach ($datos as $key => $value) {
                    $em->remove($value);
                    $em->flush();
                }
                
                $nuevoIAI = new InstitucioneducativaAccesoInternet();
                $nuevoIAI->setInstitucioneducativa($institucion);
                $nuevoIAI->setGestionTipo($gestion);
                $nuevoIAI->setTieneInternet($tieneAcceso == '1' ? true : false);
                $nuevoIAI->setEsactivo(false);
                $nuevoIAI->setFechaRegistro(new \DateTime('now'));                    
                $em->persist($nuevoIAI);
                $em->flush();                

                foreach ($proveedorArray as $key => $value) {
                    $proveedor = $em->getRepository('SieAppWebBundle:AccesoInternetProveedorTipo')->findOneById($value);

                    $nuevoIAID = new InstitucioneducativaAccesoInternetDatos();
                    $nuevoIAID->setInstitucioneducativa($institucion);
                    $nuevoIAID->setGestionTipo($gestion);
                    $nuevoIAID->setAccesoInternetProveedorTipo($proveedor);
                    $nuevoIAID->setFechaRegistro(new \DateTime('now'));
                    $em->persist($nuevoIAID);
                    $em->flush();
                }

                $iai_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $datos_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternetDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));                
                
                $this->get('session')->getFlashBag()->add('newOk', 'Registro realizado satisfactoriamente.');

                return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:saved.html.twig', array(
                    'institucion' => $institucion,
                    'iai' => $iai_fin,
                    'datos' => $datos_fin
                ));
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra registrada.');
            return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
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

        $form = $request->get('ddjjIAI');
        $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneById($form['iai']);        
        $iai->setEsactivo(true);
        $em->persist($iai);
        $em->flush();

        $this->get('session')->getFlashBag()->add('newOk', 'Registro realizado satisfactoriamente.');
        return $this->redirect($this->generateUrl('ie_acceso_internet_index'));        
    }
}
