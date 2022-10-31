<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use \Sie\AppWebBundle\Entity\Apoderado;
use Sie\AppWebBundle\Entity\PersonaObservacion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;

class ReviewApoderadoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * declaracion jurada Index
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // this is for try with this users
        /*if ($id_usuario=='92490223' || $sesion->get('roluser')==8 ) {

        }else{
            return $this->redirect($this->generateUrl('login'));
        }*/
        return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        //->add('bimestre', 'choice', array('label' => 'Bimestre', 'attr' => array('class' => 'form-control', 'empty_data' => 'Seleccionar...')))
                        ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'choice', array('label' => 'Grado', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'choice', array('label' => 'Turno', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'lookforstudents()')))
                        ->getForm();
    }

    private function createFormToBuild($sie, $gestion, $bim) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'hidden', array('data' => $sie))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('bimestre', 'hidden', array('data' => $bim))
                        ->add('generar', 'button', array('label' => 'Generar Archivo', 'attr' => array('class' => 'btn btn-link', 'onclick' => 'buildAgain()')))
                        ->getForm()
        ;
    }

    public function getgestionAction($sie) {

        $em = $this->getDoctrine()->getManager();
        $objGestion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getGestionBySie($sie);

        $aNivel = array();
        foreach ($objGestion as $ogestion) {
            $aGestion[$ogestion['gestionTipo']] = $ogestion['gestionTipo'];
        }

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $response = new JsonResponse();
        return $response->setData(array('gestion' => $aGestion, 'ue' => $objUe->getInstitucioneducativa()));
    }

    public function getnivelAction($sie, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $objNivel = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($sie, $gestion);
        $aNivel = array();
        foreach ($objNivel as $onivel) {
            $aNivel[$onivel['nivelTipo']] = $onivel['nivel'];
        }
        $response = new JsonResponse();
        return $response->setData(array('nivel' => $aNivel));
    }

    public function getgradoAction($sie, $nivel, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $objGrado = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getGradolBySieAndGestion($sie, $nivel, $gestion);
        $aGrado = array();
        foreach ($objGrado as $ogrado) {
            $aNivel[$ogrado['gradoTipo']] = $ogrado['grado'];
        }
        $response = new JsonResponse();
        return $response->setData(array('grado' => $aNivel));
    }

    public function getparaleloAction($sie, $nivel, $grado, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $objParalelo = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getParaleloBySieAndGestion($sie, $nivel, $grado, $gestion);
        $aParalelo = array();
        foreach ($objParalelo as $oparalelo) {
            $aParalelo[$oparalelo['paraleloTipo']] = $oparalelo['paralelo'];
        }
        $response = new JsonResponse();
        return $response->setData(array('paralelo' => $aParalelo));
    }

    public function getturnoAction($sie, $nivel, $grado, $paralelo, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $objTurno = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnoBySieAndGestion($sie, $nivel, $grado, $paralelo, $gestion);
        $aTurno = array();
        foreach ($objTurno as $oturno) {
            $aTurno[$oturno['turnoTipo']] = $oturno['turno'];
        }
        $response = new JsonResponse();
        return $response->setData(array('turno' => $aTurno));
    }

    public function studentsAction(request $request) {
        $em = $this->getDoctrine()->getManager();

        //$objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getStudentsBySieAndGestion($request->get('sie'), $request->get('nivel'), $request->get('grado'), $request->get('paralelo'), $request->get('turno'), $request->get('gestion'));
        $objStudents = $this->getStudentsBySieAndGestionInscription($request->get('sie'), $request->get('nivel'), $request->get('grado'), $request->get('paralelo'), $request->get('turno'), $request->get('gestion'));

        $dataSelected = serialize(array('sie' => $request->get('sie'), 'nivel' => $request->get('nivel'), 'grado' => $request->get('grado'), 'paralelo' => $request->get('paralelo'), 'turno' => $request->get('turno'), 'year' => $request->get('gestion')));
        return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:students.html.twig', array(
                    'students' => $objStudents,
                    'form' => $this->createFormTransferMassive($dataSelected)->createView()
        ));
    }
    private function getStudentsBySieAndGestionInscription($sie, $nivel, $grado, $paralelo, $turno, $gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                  SELECT i0_.id AS iecId, e1_.id AS studentInscId, e2_.id AS studentId, e2_.carnet_identidad AS carnetIdentidad, e2_.codigo_rude AS codigoRude,
                  e2_.paterno AS paterno, e2_.materno AS materno, e2_.nombre AS nombre, e2_.fecha_nacimiento AS fechaNacimiento,
                  (select distinct case when estudiante_inscripcion_id >0 then cast('t' as boolean) else cast('f' as boolean) end from apoderado_inscripcion as ap
                  where  e1_.id = ap.estudiante_inscripcion_id  and ap.es_validado = 't' limit 1) as validado
                  FROM institucioneducativa_curso i0_
                  LEFT JOIN estudiante_inscripcion e1_ ON (e1_.institucioneducativa_curso_id = i0_.id)
                  LEFT JOIN estudiante e2_ ON (e2_.id = e1_.estudiante_id)
                  WHERE i0_.institucioneducativa_id = '".$sie."'
                  AND i0_.gestion_tipo_id = '".$gestion."'
                  AND i0_.nivel_tipo_id = '".$nivel."'
                  AND i0_.grado_tipo_id = '".$grado."'
                  AND i0_.paralelo_tipo_id = '".$paralelo."'
                  AND i0_.turno_tipo_id = '".$turno."'
                  ORDER BY e2_.paterno ASC
        ");
        $query->execute();
        $objStudents = $query->fetchAll();
        return $objStudents;
    }
    private function createFormTransferMassive($data) {
        return $this->createFormBuilder()
                        //->add('sienew', 'text', array('label' => 'SIE TRANSFERNECIA', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        //->add('massivetransfer', 'button', array('label' => 'Aceptar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'desunificarrude()')))
                        ->add('dataselected', 'hidden', array('data' => $data))
                        //->add('datasend', 'text')
                        ->getForm();
    }

    /**
     * to do the desunification of rudes
     * @param Request $request
     * @return \Sie\RegularBundle\Controller\Exception
     */
    public function infoApoderadosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $arrDataselected = unserialize($request->get('dataselected'));
        $arrDataselected['rude']=$request->get('rude');

        try {
            //$rude = $request->get('rude');
            //look for apoderados
            //$objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($request->get('rude'), $request->get('gestion'));
            $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoInscriptionData($request->get('rude'), $request->get('gestion'),$request->get('studentInscId') );
            //dump($objApoderadoInscription);
            //check if ue has the curso
          //  if ($objApoderados) {
          //      //return to the index page
          //      foreach ($objApoderados as $apoderadoInsc) {
          //        # code...
          //        $objApoderadoInscriptionUpdate = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($apoderadoInsc['aid']);
          //        $objApoderadoInscriptionUpdate->setEsValidado(1);
          //        $em->persist($objApoderadoInscriptionUpdate);
          //        $em->flush();
          //      }
          //  }
            $em->getConnection()->commit();
                        //die;
            $arrDataselected['rude'] = $request->get('rude');
            $arrDataselected['studentInscId'] = $request->get('studentInscId');
            //dump($arrDataselected);die;
            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:apoderados.html.twig', array(
                        'apoderados' => $objApoderados,
                        'student' => $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $request->get('rude'))),
                        'gestionSelected' => $request->get('gestion'),
                        'studentInscId'=>$request->get('studentInscId'),
                        'dataselected'=>$request->get('dataselected'),
                        'form'=>$this->confirmForm($arrDataselected)->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }
    private function confirmForm($data){
      return $this->createFormBuilder()
                  //->add('review', 'button', array('label'=>'Confirmar', 'attr'=>array('class'=>'btn btn-primary btn-xs')))
                  ->setAction($this->generateUrl('download_file_sie_build'))
                  ->add('sie', 'hidden', array('label' => 'SIE','data'=>$data['sie'], 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                  ->add('gestion', 'hidden', array('label' => 'Gestión', 'data'=>$data['year'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  //->add('bimestre', 'choice', array('label' => 'Bimestre', 'attr' => array('class' => 'form-control', 'empty_data' => 'Seleccionar...')))
                  ->add('nivel', 'hidden', array('label' => 'Nivel','data'=>$data['nivel'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('grado', 'hidden', array('label' => 'Grado','data'=>$data['grado'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('paralelo', 'hidden', array('label' => 'Paralelo','data'=>$data['paralelo'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('turno', 'hidden', array('label' => 'Turno','data'=>$data['turno'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('rude', 'hidden', array('label' => 'Turno','data'=>$data['rude'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('studentInscId', 'hidden', array('label' => 'Turno','data'=>$data['studentInscId'], 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                  ->add('confirmar', 'button', array('label' => 'Confirmar', 'attr' => array('class' => 'btn btn-primary btn-xs', 'onclick' => 'lookforstudentsandconfirm()')))
                  ->getForm();

    }

    public function dataApoderadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            //look for apoderados
            //$objApoderado = $em->getRepository('SieAppWebBundle:Estudiante')->dataApoderado($request->get('persid'), $request->get('apodeid'));
            $objApoderado = $em->getRepository('SieAppWebBundle:Estudiante')->dataApoderadoInsc($request->get('persid'), $request->get('apodeid'));
            //get the children
            //$objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->dataChildren($request->get('persid'), $request->get('gestion'));
            $objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->dataChildrenApoderado($request->get('persid'), $request->get('gestion'));
            //dump($objChildren);die;
            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:apoderado.html.twig', array(
                        'form' => $this->craeteformApoderado($objApoderado[0], $request->get('rude'))->createView(),
                        'apoderado' => $objApoderado[0],
                        'children' => $objChildren
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    private function craeteformApoderado($data, $rude) {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('review_apoderado_sie_updateApoderado'))
                        ->add('carnet', 'text', array('label' => 'Carnet', 'data' => $data['carnet'], 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('materno', 'text', array('label' => 'Materno', 'data' => $data['materno'], 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('fechaNacimiento', 'date', array('label' => 'Fecha Nacimiento', 'data' => $data['fechaNacimiento'], 'attr' => array('class' => 'form-control', 'pattern' => '', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        //->add('telefono', 'text', array('label' => 'Telefono', 'data' => $data['telefono'], 'attr' => array('required' => false, 'class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('celular', 'text', array('label' => 'Celular', 'data' => $data['celular'], 'attr' => array('required' => false, 'class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('pid', 'hidden', array('data' => $data['pid']))
                        //->add('aid', 'hidden', array('data' => $data['aid']))
                        ->add('rude', 'hidden', array('data' => $rude))
                        ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'saveApoderado()')))
                        ->getForm();
    }

    public function updateApoderadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        try {
            //look for persona
            $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($form['pid']);
            $objPersona->setCarnet($form['carnet']);
            $objPersona->setPaterno($form['paterno']);
            $objPersona->setMaterno($form['materno']);
            $objPersona->setNombre($form['nombre']);
            //$objPersona->setFechaNacimiento();
            $objPersona->setCelular($form['celular']);

            //look for apoderados
            $objApoderado = $em->getRepository('SieAppWebBundle:Apoderado')->find($form['aid']);
            $objApoderado->setTelefono($form['telefono']);

            $em->flush();
            $em->getConnection()->commit();
            //get apoderados
            $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($form['rude'], $this->session->get('currentyear'));

            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:apoderados.html.twig', array(
                        'apoderados' => $objApoderados,
                        'student' => $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude']))
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    public function removeApoderadoAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');
        $data = unserialize($request->get('dataselected'));
        $data['rude']= $request->get('rude');
        $data['studentInscId']= $request->get('studentInscId');

        try {
            //look for apoderados
            //$objApoderado = $em->getRepository('SieAppWebBundle:Apoderado')->find($request->get('aid'));
            $objApoderadoInscription = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
              'persona'=>$request->get('pid'),
              'estudianteInscripcion'=>$request->get('studentInscId')
            ));
            //remove the relation
            $em->remove($objApoderadoInscription);
            $em->flush();

            $em->getConnection()->commit();
            //$objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($request->get('rude'), $request->get('gestion'));
            $this->addFlash('msgremoveapoderado', 'Eliminación realizada correctamente...');
            $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoInscriptionData($request->get('rude'), $request->get('gestion'),$request->get('studentInscId'));
            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:apoderados.html.twig', array(
                        'apoderados' => $objApoderados,
                        'student' => $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $request->get('rude'))),
                        'gestionSelected' => $request->get('gestion'),
                        'dataselected'=>$request->get('dataselected'),
                        'form'=>$this->confirmForm($data)->createView(),
                        'rude' => $request->get('rude'),
                        'studentInscId' => $request->get('studentInscId')
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    public function newApoderadoAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $em->getConnection()->commit();
            //get apoderados
            //$objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($request->get('rude'), $this->session->get('currentyear'));
            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:newApoderado.html.twig', array(
                        'form' => $this->craeteformNewApoderado($request->get('rude'), $request->get('gestion'), $request->get('studentInscId'), $request->get('dataselected'))->createView(),
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    private function craeteformNewApoderado($rude, $gestion, $studentInscId, $dataselected) {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('review_apoderado_sie_updateApoderado'))
                        ->add('parentescoId', 'entity', array('label' => 'Parentesco', 'attr' => array('class' => 'form-control'),
                            'mapped' => false, 'class' => 'SieAppWebBundle:ApoderadoTipo'
                        ))
                        ->add('ci', 'text', array('label' => 'Carnet de Identidad', 'data' => '', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\]{2,10}', 'maxlength' => '10', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'data' => '', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('materno', 'text', array('label' => 'Materno', 'data' => '', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('nombre', 'text', array('label' => 'Nombre', 'data' => '', 'attr' => array('required' => true, 'class' => 'form-control', 'pattern' => '[A-Za-Z0-9\sñÑ]{2,50}', 'maxlength' => '40', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('rude', 'hidden', array('data' => $rude))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('dataselected', 'hidden', array('data' => $dataselected))
                        ->add('studentInscId', 'hidden', array('data' => $studentInscId))
                        ->add('find', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary btn-xs btn-block', 'onclick' => 'lookForApoderado()')))
                        ->getForm();
    }

    public function lookForApoderadoAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');
        //$objApoderadosStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($form['rude'], $form['gestion']);
        $objApoderadosStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoInscriptionData($form['rude'], $form['gestion'],$form['studentInscId']);
        //check if the tipo of apoderado exists
        reset($objApoderadosStudent);
        $condition = true;
        while (($val = current($objApoderadosStudent) ) && $condition) {
            if ($val['idparentesco'] == $form['parentescoId']) {
                $message = 'Ya cuenta con ese tipo de apoderado, necesita eliminar apoderado para realizar la adición...';
                $this->addFlash('errorapo', $message);
                $condition = false;
            }
            next($objApoderadosStudent);
        }
        //get the data send
        $ci = $form['ci'];
        $paterno = mb_strtoupper($form['paterno'], 'utf8');
        $materno = mb_strtoupper($form['materno'], 'utf8');
        $nombre = mb_strtoupper($form['nombre'], 'utf8');

        try {
            //look for apoderados
            $criteria = array('paterno' => $paterno, 'materno' => $materno, 'nombre' => $nombre);
            //look for the person data with ci or paterno, materno, nombre criteria
            //$objApoderados = $em->getRepository('SieAppWebBundle:Persona')->findBy($criteria);
            $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getDataPersonByCiOrdataPerson($ci, $paterno, $materno, $nombre);

            if (sizeof($objApoderados) == 0 && $condition) {
                $message = 'Información no encontrada';
                $this->addFlash('errorapo', $message);
                $condition = false;
            }
            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:lookforapoderados.html.twig', array(
                        'apoderados' => $objApoderados,
                        'form' => $this->createFormSelectApoderados($form['rude'], $form['parentescoId'], $form['gestion'],$form['studentInscId'],$form['dataselected'])->createView(),
                        'condition' => $condition,
                        'gestionSelected' => $form['gestion']
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    private function createFormSelectApoderados($rude, $parentescoId, $gestion, $studentInscId, $dataselected) {
        return $this->createFormBuilder()
                        ->add('aceptar', 'button', array('label' => 'Aceptar', 'attr' => array('class' => 'btn btn-primary btn-xs', 'onclick' => 'addApoderado()')))
                        ->add('rude', 'hidden', array('data' => $rude))
                        ->add('studentInscId', 'hidden', array('data' => $studentInscId))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('dataselected', 'hidden', array('data' => $dataselected))
                        ->add('parentescoId', 'hidden', array('data' => $parentescoId))
                        ->getForm();
    }

    public function addApoderadoAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');
        //to refresh on the same page
        $data = unserialize($form['dataselected']);
        $data['rude']= $form['rude'];
        $data['studentInscId']= $form['studentInscId'];

        try {
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');");
            $query->execute();
            //add the relation between persona, apoderado and student
            $objApoderadoInscription = new ApoderadoInscripcion();
            $objApoderadoInscription->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['parentescoId']));
            $objApoderadoInscription->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['idpersona']));
            $objApoderadoInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['studentInscId']));
            $objApoderadoInscription->setEsValidado('t');
            $em->persist($objApoderadoInscription);
            $em->flush();
            /*
            $objApoderado = new Apoderado();
            $objApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['parentescoId']));
            $objApoderado->setPersonaEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude'])));
            $objApoderado->setPersonaApoderado($em->getRepository('SieAppWebBundle:Persona')->find($form['idpersona']));
            $objApoderado->setGestion($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $objApoderado->setEsactivo('t');
            $em->persist($objApoderado);
            $em->flush();*/

            $objPersonObs = new PersonaObservacion();
            $objPersonObs->setObservacionPersonaTipo($em->getRepository('SieAppWebBundle:ObservacionPersonaTipo')->find(1));
            $objPersonObs->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->find('3'));
            $objPersonObs->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['idpersona']));
            $objPersonObs->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $objPersonObs->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            $objPersonObs->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $em->persist($objPersonObs);
            $em->flush();

            //everything ok to do insert
            $em->getConnection()->commit();
            $this->addFlash('msgaddapoderado', 'Adición realizada correctamente...');
            //$objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoData($form['rude'], $form['gestion']);
            $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoInscriptionData($form['rude'], $form['gestion'],$form['studentInscId']);

            return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:apoderados.html.twig', array(
                        'apoderados' => $objApoderados,
                        'student' => $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude'])),
                        'gestionSelected' => $form['gestion'],
                        'studentInscId' => $form['studentInscId'],
                        'dataselected'=>$form['dataselected'],
                        'form'=>$this->confirmForm($data)->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

    /*
    * this method is the confirm the apoderados
    */
    public function studentsAndConfirmAction(request $request) {
        $em = $this->getDoctrine()->getManager();
        //to update the apoderado to the student
        $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoInscriptionData($request->get('rude'), $request->get('gestion'),$request->get('studentInscId') );
        //dump($objApoderadoInscription);
        //check if ue has the curso
        if ($objApoderados) {
            //return to the index page
            foreach ($objApoderados as $apoderadoInsc) {
              # code...
              $objApoderadoInscriptionUpdate = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($apoderadoInsc['aid']);
              $objApoderadoInscriptionUpdate->setEsValidado(1);
              $em->persist($objApoderadoInscriptionUpdate);
              $em->flush();
            }

        }
        //$objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getStudentsBySieAndGestion($request->get('sie'), $request->get('nivel'), $request->get('grado'), $request->get('paralelo'), $request->get('turno'), $request->get('gestion'));
        $objStudents = $this->getStudentsBySieAndGestionInscription($request->get('sie'), $request->get('nivel'), $request->get('grado'), $request->get('paralelo'), $request->get('turno'), $request->get('gestion'));

        $dataSelected = serialize(array('sie' => $request->get('sie'), 'nivel' => $request->get('nivel'), 'grado' => $request->get('grado'), 'paralelo' => $request->get('paralelo'), 'turno' => $request->get('turno'), 'year' => $request->get('gestion')));
        return $this->render($this->session->get('pathSystem') . ':ReviewApoderado:students.html.twig', array(
                    'students' => $objStudents,
                    'form' => $this->createFormTransferMassive($dataSelected)->createView()
        ));
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////that's all
}
