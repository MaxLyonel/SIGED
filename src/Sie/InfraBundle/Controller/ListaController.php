<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ListaController extends Controller
{
  public $session;

  public function __construct(){
    $this->session = new Session();
  }
  public function indexAction(Request $request) {
      //create the db connexion
      $em=$this->getDoctrine()->getManager();
      //get the session's values
      $this->session = $request->getSession();
      $id_usuario = $this->session->get('userId');

      //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
      $this->unidadEducativa = $this->session->get('ie_id');

      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      return $this->render($this->session->get('pathSystem') . ':Lista:index.html.twig', array(

      ));
  }

    public function listAction()
    {
        return $this->render('SieInfraBundle:Lista:list.html.twig', array(
                // ...
            ));
          }

    public function openAction(request $request){
      // dump($request);die;
      //get the values to send
      $data = $request->get('form');

      return $this->render($this->session->get('pathSystem') . ':Lista:open.html.twig', array(
                  'sheetOneform'   => $this->openSheetAction('infra_sheet_one_index',   'Datos Generales', $data)->createView(),
                  'sheetTwoform'   => $this->openSheetAction('infra_sheet_two_index',   'Caracteristicas del Edificio',$data)->createView(),
                  'sheetThreeform' => $this->openSheetAction('infra_sheet_three_index', 'Riesgos y Delitos',$data)->createView(),
                  'sheetFourform'  => $this->openSheetAction('infraestructurah4servicio',  'Servicios del Edificio',$data)->createView(),
                  'sheetFiveform'  => $this->openSheetAction('infra_sheet_five_index',  'Ambientes Pedagogicos',$data)->createView(),
                  'sheetSixform'   => $this->openSheetAction('infra_sheet_six_index',   'Ambientes No Pedagogicos',$data)->createView(),
                  'data'           => $data
      ));
    }
    /**
     * the function to open some sheet on infra option
     */
    private function openSheetAction($urlTheSheet,$option,$data){
      $form = $this->createFormBuilder()
        ->setAction($this->generateUrl($urlTheSheet))
        ->add('gestion', 'hidden', array('data'=>$data['gestion']))
        ->add('sie', 'hidden', array('data'=>$data['sie']))
        ->add('infraestructuraJuridiccionGeografica', 'hidden', array('data'=>$data['infraestructuraJuridiccionGeograficaId']))
        ->add('opensheet', 'submit',array('label'=>"$option", 'attr'=>array('class'=>'cbp-singlePage cbp-l-caption-buttonLeft')))
        ->add('labelsheet', 'hidden',array('label'=>$option))
        ;
        return $form->getForm();
    }

}
