<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for load all additional connections; if exist in a particular proccess
 * Date: 10-10-2017
 *
 *
 * ListCloseRudeController
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */
class SeguimientoGISController extends Controller
{   //set GLOBALS vars
    public $session;
    /**
     * [__construct description]
     */
    public function __construct(){
      $this->session = new Session();
    }
    /**
     * y
     * @return [type] [description]
     */
    public function indexAction(){
      //check if the user is sie,dep or distrito
      if($this->session->get('roluser')==10){
        //get the cod distrito and ue to distrito
        $codDistrito            = $this->get('seguimiento')->getDistritoByPersonaId($this->session->get('personaId'));
        //get ue by ditrito
        $arrUeCloseOperativoGis = $this->get('seguimiento')->getUesGisByCodDistrito($codDistrito);
        return $this->render('SieRegularBundle:SeguimientoGIS:close.html.twig', array(
                'sw' => $codDistrito,
                 'arrResult' => $arrUeCloseOperativoGis,
                 'codDistrito' => base64_encode($codDistrito)
            ));
      }
        return $this->render('SieRegularBundle:SeguimientoGIS:index.html.twig', array(
                  'form' => $this->createSearchForm()->createView(),
            ));
    }
    /**
     * [createSearchForm description]
     * @return [type] [description]
     */
    private function createSearchForm(){
      return $this->createFormBuilder()
          ->setAction($this->generateUrl('seguimiento_gis_find'))

          ->add('codDistrito', 'text', array('mapped' => false, 'label' => 'Codigo Distrito', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{4,4}', 'maxlength' => '4', 'autocomplete' => 'off')))
          ->add('gestion', 'hidden', array('data'=>$this->session->get('currentyear')))
          ->add('buscar', 'submit', array('label' => 'Buscar'))
          ->getForm();

    }
    /**
     * [findAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function findAction(Request $request){
      // get the send values
      $form = $request->get('form');
      $codDistrito = $form['codDistrito'];

      $arrUeCloseOperativoGis = $this->get('seguimiento')->getUesGisByCodDistrito($codDistrito);

      return $this->render('SieRegularBundle:SeguimientoGIS:close.html.twig', array(
              'sw' => $codDistrito,
               'arrResult' => $arrUeCloseOperativoGis,
               'codDistrito' => base64_encode($codDistrito)
          ));

    }

    public function disableAction(Request $request){
      //create DB conexxion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form = $request->get('form');
      //get the cod distrito and ue to distrito
      $codDistrito = base64_decode($form['distrito']);
      $jurgId = $form['jurgId'];
      try {
        $objJurisdiccionGeografica = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($jurgId);
        $objJurisdiccionGeografica->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->find(0));
        $em->persist($objJurisdiccionGeografica);
        $em->flush();
        // Try and commit the transaction
        $em->getConnection()->commit();

        //get ue by ditrito
        $arrUeCloseOperativoGis = $this->get('seguimiento')->getUesGisByCodDistrito($codDistrito);
        return $this->render('SieRegularBundle:SeguimientoGIS:close.html.twig', array(
                'sw' => $codDistrito,
                 'arrResult' => $arrUeCloseOperativoGis,
                 'codDistrito' => base64_encode($codDistrito)
            ));
      } catch (\Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        die;
      }


    }

}
