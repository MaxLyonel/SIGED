<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Form\DepartamentoReportType;
use Sie\AppWebBundle\Form\DistritoReportType;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * HistoryInscription controller.
 *
 */
class ReportesController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction() {
        
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $this->createForm(new DepartamentoReportType(), null, array('action' => $this->generateUrl('download_ue_reportes_departamento'), 'method' => 'GET',));
        
        $formdis = $this->createForm(new DistritoReportType(), null, array('action' => $this->generateUrl('download_ue_reportes_distrito'), 'method' => 'GET',));        
        
        return $this->render($this->session->get('pathSystem') . ':Reportes:index.html.twig', array(           
            'formdepto'   => $form->createView(),
            'formdistri'   => $formdis->createView(),
        )); 
                
    }   

}
