<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Form\SelectIeType;

/**
 * FAEA Controller.
 *
 */
class FaeaController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createSearchIeForm();

        return $this->render('SieAppWebBundle:Faea:index.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*
     * Formulario de búsqueda de Institucion Educativa
     */

    private function createSearchIeForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('faea_ie_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Buscar'));

        return $form;
    }

    /*
     * Muestra el resultado de la búsqueda de Institución Educativa
     */

    public function resultSearchIeAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createSearchIeForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $formulario = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $faea = $em->getRepository('SieAppWebBundle:Faea2014')->find($formulario['institucioneducativa']);

            if (!$faea) {
                $this->get('session')->getFlashBag()->add('searchIe', 'El código ingresado no es válido o la Institución Educativa no existe y/o no es beneficiaria del FAEA');
                return $this->redirect($this->generateUrl('faea_index'));
            }
            
            return $this->render('SieAppWebBundle:Faea:resultSearchIe.html.twig', array(
                        'faea' => $faea,
            ));
        }
    }

}
