<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;

class ResetBjpController extends Controller
{
  private $session;

  /**
   * the class constructor
   */
  public function __construct() {
      $this->session = new Session();
  }

    public function indexAction(Request $request){

      $em = $this->getDoctrine()->getManager();
      //check if the user is logged
      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      return $this->render($this->session->get('pathSystem') . ':ResetBjp:index.html.twig', array(
                  'form' => $this->createSearchForm()->createView(),
      ));

    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('history_new_inscription_index'))
                ->add('codigoSie', 'text', array('label' => 'SIE', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Buscar', 'attr'=> array('onclick'=>'looksie()')))
                ->getForm();
        return $form;
    }

    public function looksieAction(Request $request){

      // create db conexion
      $em = $this->getDoctrine()->getManager();
      // get the values send
      $form = $request->get('form');
      // $objStudent = array();
      $objInstitucionEducativa = array();
      $objValidacionBjp = array();
      $objHoras = array();
      $existUe = false;
      $message = false;
      $validacionId = false;
      // look for the UE

      $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codigoSie']);

      if($objInstitucionEducativa){
        // look for confirmation register
        $objValidacionBjp = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaValidacion')->findOneBy(array(
          'institucioneducativaId' => $form['codigoSie'],
          'gestionTipoId' => $this->session->get('currentyear') - 1
        ));
          
        //chech it the ue is gonna to register the BJP
        if($objValidacionBjp){
            if($this->session->get('roluser') == 8) {
                $validacionId = $objValidacionBjp->getId();
                $existUe = true;
            } else {
                if($objValidacionBjp->getObs()>=5){
                    $message = 'La Unidad Educativa ya fue reseteada la cantidad de veces permitidas para el regitro del BJP';
                    $existUe = false;
                } else {
                    if($objValidacionBjp->getEsactivo()== true && ($objValidacionBjp->getObs()<=5)){
      
                        $existUe = true;
                        $validacionId = $objValidacionBjp->getId();
                    } else {
                        $message = 'Unidad Educativa no concluida su reporte BJP';
                        $existUe = false;
                    }
                }
            }
        } else {
            $message = 'No existe registro para el reporte del BJP para esta Unidad Educativa';
            $existUe = false;
        }

      }else {
        // no UE
        $message = 'No existe Unidad Educativa';
        $existUe = false;
      }


      // render the view page
        return $this->render($this->session->get('pathSystem') . ':ResetBjp:looksie.html.twig', array(
            'objInstitucionEducativa' => $objInstitucionEducativa,
            'objValidacionBjp' => $objValidacionBjp,
            'validacionId' => base64_encode($validacionId),
            'sw' => $existUe,
            'message' => $message
        ));

    }

    public function resetbjpAction(Request $request){
        
        //get values send
        $validacionId = $request->get('validacionId');
        $validacionId = base64_decode($validacionId);
        $done = false;
        // create DB conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $objValidacionBjp = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaValidacion')->find($validacionId);
            $objValidacionBjp->setEsactivo('f');
            if($this->session->get('roluser') == 8) {
                $objValidacionBjp->setObs(null);
                $objValidacionBjp->setFechaFinVal(null);
                $objValidacionBjp->setFechaFinEdit(null);
            }
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $done = true;
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$e->getMessage();
            $this->addFlash('warningremoveins', $message);
            $done = false;
            // return $this->redirectToRoute('restart_hour_index');
        }

        return $this->render('SieRegularBundle:ResetBjp:resetbjp.html.twig', array(
            'done'=> $done
        ));
    }

}
