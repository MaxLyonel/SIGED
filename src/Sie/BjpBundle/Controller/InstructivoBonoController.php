<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * EstudianteInscripcion controller.
 *
 */
class InstructivoBonoController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request){
      //crear conexion base de datos
      $em = $this->getDoctrine()->getManager();
      // Verificacmos si existe la session de usuario
      if (!$this->session->get('userId')) {
          return $this->redirect($this->generateUrl('login'));
      }
 
          return $this->render('SieBjpBundle:InstructivoBono:index.html.twig', array(
                'form' => $this->seleccionUeForm()->createView()
            ));
    }

    private function seleccionUeForm(){
        return $this->createFormBuilder()
                    ->add('sie','hidden',array('label'=>'La impresión del instructivo se la realiza de forma única, debido a que será reproducida por la imprenta','attr'=>array('class'=>'form-control')))
                    ->add('buscar', 'button', array('label' => 'Generar Instructivo', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))
                    ->getForm();

    }


    public function buscarInfoUeAction(Request $request){
      return $this->render('SieBjpBundle:PagosBono:index.html.twig', array(
            'form' => $this->seleccionUeForm()->createView()
        ));
    }
}

