<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;

class ConsultaLibretaController extends Controller {

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * visualizamos paramentos de busqueda
     * @param Request $request
     * @return object form to look for libreta
     */
    public function indexAction(Request $request) {
        $this->session->set('currentyear', date('Y'));
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario)
                ->setAction($this->generateUrl('consultalibreta_buscar'))
                ->setMethod('POST')
                ->add('rudeoci', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio'))
                ->add('fechaNacimiento', 'text', array('mapped' => false, 'label' => 'Fecha de Nacimiento', 'attr' => array('class' => 'form-control', 'maxlength'=> '10', 'readonly'=>true)))
                ->add('save', 'submit', array('label' => 'Aceptar'))
                ->getForm();

        return $this->render('SieAppWebBundle:ConsultaLibreta:downindex.html.twig', array("form" => $form->createView()));
    }

    function canonicalize_path($path, $cwd=null) {

        // don't prefix absolute paths
        if (substr($path, 0, 1) === "/") {
          $filename = $path;
        }

        // prefix relative path with $root
        else {
          $root      = is_null($cwd) ? getcwd() : $cwd;
          $filename  = sprintf("%s/%s", $root, $path);
        }

        // get realpath of dirname
        $dirname   = dirname($filename);
        $canonical = realpath($dirname);

        // trigger error if $dirname is nonexistent
        if ($canonical === false) {
          trigger_error(sprintf("Directory `%s' does not exist", $dirname), E_USER_ERROR);
        }

        // prevent double slash "//" below
        if ($canonical === "/") $canonical = null;

        // return canonicalized path
        return sprintf("%s",  basename($filename));
      }

    /**
     *
     * buscar libreta del estudiante
     * @param Request $request
     * @return array libreta, estudiante, inst. educativa
     */
    public function buscarAction(Request $request) {

        $form = $request->get('form');
        
        if( strpos($form["fechaNacimiento"], "../") ){
          $this->session->getFlashBag()->add('P.T.A. B-(');
          return $this->redirect($this->generateUrl('consultalibreta'));
        }
        $form["fechaNacimiento"]=$this->canonicalize_path($form["fechaNacimiento"],null);
        if ((strlen($form['fechaNacimiento']) < 10 ) ) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $this->session->getFlashBag()->add('notice', 'No existe el Estudiante, revise datos de entrada(rude/ci o fecha nacimiento)');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        //if ($request->getMethod() == 'POST') {
        list($form['day'], $form['month'], $form['year']) = explode('-',  $form['fechaNacimiento']);
        $this->session->set('rudeoci', $form['rudeoci']);
        $this->session->set('year', $form['year']);
        $this->session->set('month', $form['month']);
        $this->session->set('day', $form['day']);
        $this->session->set('fnac', $form['fechaNacimiento']);
        //}
        $form['rudeoci'] = ($this->session->get('rudeoci')) ? $this->session->get('rudeoci') : $form['rudeoci'];
        $form['year'] = ($this->session->get('year')) ? $this->session->get('year') : $form['year'];
        $form['month'] = ($this->session->get('month')) ? $this->session->get('month') : $form['month'];
        $form['day'] = ($this->session->get('day')) ? $this->session->get('day') : $form['day'];
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getDataStudent($form['rudeoci'], $form['year'] . '-' . $form['month'] . '-' . $form['day']);

        //check if the student exists
        if (!$objStudent) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $this->session->getFlashBag()->add('notice', 'No existe el Estudiante');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        if (sizeof($objStudent)>1) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $this->session->getFlashBag()->add('notice', 'Estudiante presenta mas de  un registro en el sistema, favor regularizar con su respectivo ténico');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        //$form['codigoRude'] = ($sesion->get('rude')) ? $sesion->get('rude') : $estudiante[0]['codigoRude'];
        //$form['codigoRude'] = $estudiante[0]['codigoRude'];
        // dump($form);die;
        // $form['gestion'] = isset($form['gestion']) ? $form['gestion'] : $this->session->get('currentyear');

// dump(($form));die;
        if(isset($form['gestion'])){
          $objInscriptionStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentInscriptionData($objStudent[0]['id'], $form['gestion']);
          if (!($objInscriptionStudent)) {
              //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
              $this->session->getFlashBag()->add('notice', 'Estudiante no presenta Historial con estado EFECTIVO/PROMOVIDO en la gestión '.$form['gestion']);
              return $this->redirect($this->generateUrl('consultalibreta'));
          }
          $gestionUse = $form['gestion'];
        }else {
          //this for the new to look for the last inscription
          //get the last incription
          $inscriptions = $this->getCurrentInscriptionsStudent($objStudent[0]['codigoRude']);
          reset($inscriptions);
          $sw = true;
          $arrAllowMatriculas = array(4,5,56,57,58);
          //look for the current inscription on this year
          while($sw &&  ($inscription = current($inscriptions))){

              if(in_array($inscription['estadoMatriculaId'], $arrAllowMatriculas)){
                $objInscriptionStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentInscriptionData($objStudent[0]['id'], $inscription['gestion']);
                $gestionUse = $inscription['gestion'];
                $sw=false;
              }
            next($inscriptions);
          }

          if (($sw)) {
              //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
              $this->session->getFlashBag()->add('notice', 'Estudiante no presenta Historial con estado EFECTIVO/PROMOVIDO en la gestión '.$this->session->get('currentyear'));
              return $this->redirect($this->generateUrl('consultalibreta'));
          }
        }

        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentNew( $objInscriptionStudent[0]['inscripcionid'],$objStudent[0]['id'], $objInscriptionStudent[0]['nivel'], $objInscriptionStudent[0]['grado'], $objInscriptionStudent[0]['paralelo'], $objInscriptionStudent[0]['turno'], $objInscriptionStudent[0]['gestion'], $objInscriptionStudent[0]['sie']);

        $aNota = array();
        $aBim = array();
        //build the nota
        foreach ($objNota as $nota) {
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = ($objInscriptionStudent[0]['nivel'] == 11) ? $nota['notaCualitativa'] : $nota['notaCuantitativa'] : '';
            ($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
        }

        $aBim = ($aBim) ? $aBim : array();

        $arrGetsion = array();
        for($i=0;$i<5;$i++){
          $arrGetsion[date('Y')-$i]=date('Y')-$i;
        }

        $formsearch = $this->createFormBuilder()
                ->setAction($this->generateUrl('consultalibreta_buscar'))
                ->setMethod('POST')
                ->add('rudeoci', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $objStudent[0]['codigoRude']))
                ->add('year', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $this->session->get('year')))
                ->add('month', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $this->session->get('month')))
                ->add('day', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $this->session->get('day')))
                ->add('fechaNacimiento', 'hidden', array('data' => $form['fechaNacimiento'],'attr'=>array( 'readonly'=>true) ))
                ->add('gestion', 'choice', array('mapped' => false, 'choices' => $arrGetsion, 'required' => true, 'invalid_message' => 'Campor 2 obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();

        return $this->render('SieAppWebBundle:ConsultaLibreta:resultlibreta.html.twig', array(
                    'gestion' => $gestionUse,
                    'bimestres' => $aBim,
                    //'datastudent' => $datastudent,
                    'notastudent' => $aNota,
                    'notacualitativostudent' => $aNota,
                    "form" => $formsearch->createView(),
        ));
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {
//$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('it = :idTipo')
                ->setParameter('id', $id)
                ->setParameter('idTipo',1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
