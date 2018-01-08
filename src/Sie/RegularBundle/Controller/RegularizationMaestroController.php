<?php

namespace Sie\RegularBundle\Controller;

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
use Sie\AppWebBundle\Entity\LogRegularizacionMaestro;

/**
 * RegularizationMaestro controller.
 *
 */
class RegularizationMaestroController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR':
                    if($this->session->get('roluser') == 9){
                        $this->session->set('tituloTipo','Asignación de Maestros');
                    }else{
                        $this->session->set('tituloTipo', 'Regularización Maestros');
                    }
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                    $this->session->set('layout', 'layoutAlternativa.html.twig');
                    break;
                default: $this->session->set('tituloTipo', 'Adicion');
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
            }

            ////////////////////////////////////////////////////
            $dataReg = array();
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $dataReg = $form;
                /**
                 * VErificamos si la gestion es 2015
                 */
                if ($form['gestion'] < 2008 or $form['gestion'] > $this->session->get('currentyear')) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida.');
                    return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * verificamos si tiene tuicion
                 */
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['institucioneducativa']);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];
                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                //check if the operativo is closed
                $operativo = $this->operativo($form['institucioneducativa'],$form['gestion']);
                if($operativo < 5){
                  $this->get('session')->getFlashBag()->add('noSearchInfo', 'Información sin consolidar');
                  return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

                //check if the ue did it  the validation
                $objRegularization = $em->getRepository('SieAppWebBundle:LogRegularizacionMaestro')->findOneBy(
                    array('institucioneducativaId'=>$form['institucioneducativa'])
                  );

                if($objRegularization){
                  $this->get('session')->getFlashBag()->add('noSearch', 'Ya se encuentra Validado la Información de esta Unidad Educativa '.$form['institucioneducativa']);
                  return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }


            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 1) { // si no es estudiante
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');

                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {
                            return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = date('Y') - 1;
                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = date('Y') - 1;
                        //echo $institucion;die;
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Lista de turnos validos para la unidad educativa
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $institucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            /**
             * Creamos el formulario de busqueda de turno nivel grado y paralelo
             */
            $form = $this->createFormBuilder()
                    ->add('idInstitucion', 'hidden', array('data' => $institucion))
                    ->add('idGestion', 'hidden', array('data' => $gestion))
                    ->add('turno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    ->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
                    ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
                    ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
                    ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
                    ->getForm();
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */


            $em->getConnection()->commit();

            $jsonDataReg = (sizeof($dataReg)>0)?json_encode($dataReg):'';

            return $this->render('SieRegularBundle:RegularizationMaestro:index.html.twig', array(
                        'turnos' => $turnosArray,
                        'institucion' => $institucion,
                        'gestion' => $gestion,
                        'form' => $form->createView(),
                        'formReg' => $this->formRegularizationMaestro($jsonDataReg)->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formRegularizationMaestro($jsonDataReg){
      return $this->createFormBuilder()
              ->setAction($this->generateUrl('regularizationmaestro_save_regularization'))
              ->add('dataReg', 'hidden',array('data'=>$jsonDataReg))
              // ->add('save', 'button', array('label'=>'Guardar Cambios de Actualición','attr'=>array('class'=>'btn btn-success btn-md', 'onclick'=>'saveRegularizationMaestro()')))
              ->add('save', 'button', array('label'=>'Guardar Cambios de Actualición','attr'=>array('class'=>'btn btn-success btn-md', 'onclick'=>'goNextStep()'  )))
              ->getForm()
              ;
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    public function saveRegularizationAction(Request $request){

      //create db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values to send
      $form = $request->get('form');
      $arrData = json_decode($form['dataReg'],true );
      //dump($arrData);die;
      try {
        //save the data on db
        $LogRegularizacionMaestro = new LogRegularizacionMaestro();
        $LogRegularizacionMaestro->setFecha(new \DateTime('now'));
        $LogRegularizacionMaestro->setInstitucioneducativaId($arrData['institucioneducativa']);
        $LogRegularizacionMaestro->setUsuarioId($this->session->get('userId'));
        $LogRegularizacionMaestro->setBrowser($request->headers->get('User-Agent'));
        $LogRegularizacionMaestro->setEsModificado('t');

        $em->persist($LogRegularizacionMaestro);
        $em->flush();

        // Try and commit the transaction
        $em->getConnection()->commit();

        return $this->redirect($this->generateUrl('regularizationmaestro'));
        // $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
        // return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));

      } catch (Exception $e) {
        echo $exc->getTraceAsString();
        $em->getConnection()->rollback();
        $em->close();
        throw $e;
      }

      dump($form);die;

    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array();
        for($i=$this->session->get('currentyear')-1;$i>=$this->session->get('currentyear')-2;$i--){
            $gestiones[$i] = $i;
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizationmaestro'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Funcion para cargar los grados segun el nivel, para el nuevo curso
     */
    public function listargradosAction($nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //$dep = $em->getRepository('SieAppWebBundle:GradoTipo')->findAll();
            if ($nivel == 11) {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2));
            } else {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2, 3, 4, 5, 6));
            }
            $gra = $query->getResult();
            $lista = array();
            foreach ($gra as $gr) {
                $lista[$gr->getId()] = $gr->getGrado();
            }
            $list = $lista;
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('listagrados' => $list));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax para la seleccion de nivel grado y paralelo
     */

    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion, $gestion) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('turnos' => $turnosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */

    public function cargarNivelesAction($idInstitucion, $gestion, $turno) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT nt.id,nt.nivel
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.nivelTipo nt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    ORDER BY nt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno);
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('niveles' => $nivelesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarGradosAction($idInstitucion, $gestion, $turno, $nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT gt.id,gt.grado
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.gradoTipo gt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    AND iec.nivelTipo = :nivel
                    ORDER BY gt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel);
            $grados = $query->getResult();
            $gradosArray = array();
            for ($i = 0; $i < count($grados); $i++) {
                $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('grados' => $gradosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarParalelosAction($idInstitucion, $gestion, $turno, $nivel, $grado) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT pt.id,pt.paralelo
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.paraleloTipo pt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    AND iec.nivelTipo = :nivel
                    AND iec.gradoTipo = :grado
                    ORDER BY pt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel)
                    ->setParameter('grado', $grado);
            $paralelos = $query->getResult();
            $paralelosArray = array();
            for ($i = 0; $i < count($paralelos); $i++) {
                $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('paralelos' => $paralelosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Lista de areas segun el nivel
     * ventana modal
     */

    public function lista_areas_nivelAction($idNivel, $idCurso) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();

            // Creamos los arrays de materias segun la gestion
            $matpri = array(203, 204, 205, 207, 250, 252, 253, 254, 257);
            $matsec = array(203, 204, 205, 207, 251, 252, 253, 254, 257, 258);

            $matiniant = array(101, 102, 103, 104, 105);
            $matpriant = array(201, 203, 204, 205, 206, 207, 209, 210);
            $matsecantter = array(301, 302, 304, 305, 309, 313, 316, 317, 318, 319);
            $matsecantcua = array(301, 302, 303, 304, 305, 309, 313, 316, 317, 318, 319);
            $matsecantquisex = array(301, 302, 303, 305, 307, 308, 310, 311, 312, 313, 316, 317, 318, 319);


            $matinia = array(1000, 1001, 1002, 1003);
            $matpria = array(1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019);

            $matseca = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1044);
            $matsecb = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1043, 1044);
            $matsecc = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1041, 1042, 1043, 1044);

            $matsecd = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044);
            $matsece = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045);
            $matsecf = array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045);

            $matsecg = array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045); // PAra gestiones 2016 en adelante grados 5 y 6

            // Para unidades educativas nocturnas
            $matnocta = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044); // para 1 y 2
            $matnoctb = array(1031,1032,1033,1037,1040,1043,1044,1045); // para 3,4,5 y 6



            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getGradoTipo()->getId();
            $turno = $curso->getTurnoTipo()->getId();

            // VAlidamos q la gestion sea mayor o igual a 2008
            if($gestion < 2008){
                echo "La gestion seleccionada no es válida !!!";
                die;
            }

            switch ($gestion) {
                case 2008:
                case 2009:
                case 2010:
                case 2011:
                case 2012:
                case 2013:
                    switch ($nivel) {
                        case 11:
                        case 1:
                            $idsAsignaturas = $matiniant;
                            break;
                        case 12:
                        case 2:
                            switch ($grado) {
                                case 1:
                                    $idsAsignaturas = $matpri;
                                    break;
                                case 2:
                                case 3:
                                case 4:
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matpriant;
                                    break;
                            }
                            break;
                        case 13:
                        case 3:
                            switch ($grado) {
                                case 1:
                                    $idsAsignaturas = $matsec;
                                    break;
                                case 2:
                                    $idsAsignaturas = $matpriant;
                                    break;
                                case 3:
                                    $idsAsignaturas = $matsecantter;
                                    break;
                                case 4:
                                    $idsAsignaturas = $matsecantcua;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecantquisex;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2014:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    $idsAsignaturas = $matseca;
                                    break;
                                case 3:
                                case 4:
                                    $idsAsignaturas = $matsecc;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecc;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2015:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    $idsAsignaturas = $matsecd;
                                    break;
                                case 3:
                                case 4:
                                    $idsAsignaturas = $matsece;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecf;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2016:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnocta;
                                    }else{
                                        $idsAsignaturas = $matsecd;
                                    }
                                    break;
                                case 3:
                                case 4:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnoctb;
                                    }else{
                                        $idsAsignaturas = $matsece;
                                    }
                                    break;
                                case 5:
                                case 6:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnoctb;
                                    }else{
                                        $idsAsignaturas = $matsecg;
                                    }
                                    break;
                            }
                            break;
                    }
                    break;
            }
            //dump($idsAsignaturas);die;
            $asignaturas = $em->createQuery(
                    'SELECT at
                    FROM SieAppWebBundle:AsignaturaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
            )->setParameter('ids',$idsAsignaturas)
            ->getResult();

            $areasNivel = $asignaturas;
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));

            $areasArray = array();
            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areasNivel[$i]->getId() == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $check = 'checked';
                        $bloqueado = 'disabled';
                    }
                }
                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') {
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
                }
            }
            $maestros = $em->createQueryBuilder()
                           ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet')
                           ->from('SieAppWebBundle:MaestroInscripcion','mi')
                           ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                           ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                           ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                           ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                           ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                           ->where('ie.id = :idInstitucion')
                           ->andWhere('gt.id = :gestion')
                           ->andWhere('rt.id = :rol')
                           ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                           ->setParameter('gestion',$this->session->get('idGestion'))
                           ->setParameter('rol',2)
                           ->orderBy('p.paterno','asc')
                           ->addOrderBy('p.materno','asc')
                           ->addOrderBy('p.nombre','asc')
                           ->getQuery()
                           ->getResult();

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:RegularizationMaestro:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
            //return $this->render('SieRegularBundle:Areas:show.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */

    public function lista_areas_cursoAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //echo $request->get('divResultado')."<br>";
            //echo $request->get('idInstitucionCurso');
            $form = $request->get('form');
            if ($form['nivel'] != 10) {
                $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                    'institucioneducativa' => $form['idInstitucion'],
                    'gestionTipo' => $form['idGestion'],
                    'turnoTipo' => $form['turno'],
                    'nivelTipo' => $form['nivel'],
                    'gradoTipo' => $form['grado'],
                    'paraleloTipo' => $form['paralelo']));
                if ($curso) {
                    $idCurso = $curso->getId();
                    $mensaje = '';
                } else {
                    $mensaje = "No hay asignaturas";
                    //return $mensaje;
                }
                $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
                $cargos = $queryCargos->getResult();
                $cargosArray = array();

                foreach ($cargos as $c) {
                    $cargosArray[$c->getId()] = $c->getId();
                }

                $areasCurso = $em->createQuery(
                                        'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE ieco.insitucioneducativaCurso = :iecId
                    ORDER BY at.id ASC')
                                ->setParameter('iecId', $idCurso)->getResult();
                //dump($areasCurso);die;
                $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $idCurso)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();
                $array = array();
                foreach ($totalAreasCurso as $tac) {
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => 'Area',
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura']);
                }
                $areasCurso = $array;
                $operativo = $this->operativo($form['idInstitucion'],$form['idGestion']);
                $em->getConnection()->commit();
                return $this->render('SieRegularBundle:RegularizationMaestro:listaAreasCurso.html.twig', array(
                            'areasCurso' => $areasCurso,
                            'curso' => $curso,
                            'mensaje' => $mensaje,
                            'operativo'=>$operativo

                ));
            } else {
                echo "La adición de areas no se puede aplicar a nivel Inicial";
                die;
            }
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }


    /**
     * Fcunrion para eadicionar y elimiar areas
     */
    public function lista_areas_curso_adicionar_eliminarAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session;
            $idCurso = $request->get('idInstitucionCurso');
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            /*
             * Areas a registrar nuevos
             */
            $areas = $request->get('areas');
            /*
             * Areas registradas anteriormente
             */
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));
            /**
             * Aplicamos la funcion para actualizar el primary key
             */
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            for ($i = 0; $i < count($areas); $i++) {
                $existe = 'no';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areas[$i] == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $existe = 'si';
                    }
                }
                if($this->session->get('idGestion') == 2016 and $areas[$i] == 1039){
                    $existe = 'si';
                }
                if ($existe == 'no') {
                    //echo $areas[$i]." - ".$request->get('idInstitucionCurso')."<br>";
                    $newArea = new InstitucioneducativaCursoOferta();
                    $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                    $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                    $newArea->setHorasmes(0);
                    $em->persist($newArea);
                    $em->flush();

                    // Verificamos si registraron el maestro pra registrarlo en la tabla curso oferta maestro
                    if ($request->get($areas[$i])) {
                         $idMaestroInscripcion = $request->get($areas[$i]);
                         //dump($idMaestroInscripcion);
                         //dump($newArea->getId());
                         //die;
                         $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                         $nuevoCOM = new InstitucioneducativaCursoOfertaMaestro();
                         $nuevoCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                         $nuevoCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroInscripcion));
                         $nuevoCOM->setHorasMes(0);
                         $nuevoCOM->setFechaRegistro(new \DateTime('now'));
                         $nuevoCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                         $nuevoCOM->setEsVigenteMaestro('t');
                         $em->persist($nuevoCOM);
                         $em->flush();
                    }

                    // Listamos los estudinates inscritos
                    // para registrar el curso a los estudiantes
                    $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso, 'estadomatriculaTipo'=>array(4,5,11)));
                    foreach ($inscritos as $ins) {
                        // Verificamos si el estudiante ya tiene la asignatura
                        $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
                        if(!$estInscripcion){
                            //echo "agregando notas a ". $ins->getEstudiante()->getNombre() ." <br>";
                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                            $query->execute();
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                            $estAsignaturaNew->setFechaRegistro(new \DateTime('now'));
                            $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                            $query->execute();

                        }
                    }
                }
            }

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                                    INNER JOIN ieco.asignaturaTipo at
                                    WHERE ieco.insitucioneducativaCurso = :iecId
                                    ORDER BY at.id ASC')
                                    ->setParameter('iecId', $idCurso)->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                    ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $idCurso)
                    ->orderBy('at.id','ASC')
                    ->getQuery()
                    ->getResult();
            $array = array();
            foreach ($totalAreasCurso as $tac) {
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura']);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:RegularizationMaestro:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => ''));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * Eliminar un area
     */
    public function deleteAction($idCursoOferta) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());
            /**
             * Si existe el curso y el curso oferta entonces eliminamos el curso oferta
             */
            if ($cursoOferta and $curso) {
                //Verificamos si el curso oferta no tiene estudidantes asociados en la tabla estudiante asignatura
                /* $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
                  if($estudianteAsignatura){
                  $mensaje='No se puede eliminar el área, porque tiene estudiantes asociados';
                  $eliminar='no';
                  } */


                // VErificamos si tiene maestro asignado
                /* if($cursoOferta->getMaestroInscripcion() != null){
                  $mensaje = 'No se puede eliminar el área si tiene un maestro asignado, primero debe quitar el maestro';
                  $eliminar='no';
                  } */

                $cursosOfertasSimilares = $em->createQuery(
                                'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.insitucioneducativaCurso iec
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE iec.id = :idCurso
                    AND at.id = :idAsignatura')
                        ->setParameter('idCurso', $curso->getId())
                        ->setParameter('idAsignatura', $cursoOferta->getAsignaturaTipo()->getId())
                        ->getResult();


                foreach ($cursosOfertasSimilares as $co) {
                    $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $co->getId()));

                    foreach ($estudianteAsignatura as $ea) {
                        // Eliminamos las notas
                        $em->createQuery(
                                        'DELETE FROM SieAppWebBundle:EstudianteNota en
                            WHERE en.estudianteAsignatura = :idEstAsig')
                                ->setParameter('idEstAsig', $ea->getId())->execute();
                    }

                    // Eliminamos en la tabla estudiante asignatura
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:EstudianteAsignatura ea
                        WHERE ea.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Eliminamos los registros de maestros
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                        WHERE iecom.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Registramos en la tabla de control
                    // Eliminamos el curso oferta

                    $cursoOfertaEliminar = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());
                    $em->remove($cursoOfertaEliminar);


                }
                $em->flush();
                $mensaje = 'Se elimino el área del curso';
            } else {
                $mensaje = 'No se puede eliminar el área';
            }

            $this->session = new Session();

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $curso->getId())->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        //->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $curso->getId())
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();
                $array = array();
                //dump($totalAreasCurso);
                foreach ($totalAreasCurso as $tac) {
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        //'area' => $tac['area'],
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura']);
                }
                $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:RegularizationMaestro:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => $mensaje));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Asignar maestro al area
     */

    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');

        $em = $this->getDoctrine()->getManager();
        $maestrosMateria = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$ieco));
        //dump('ss');die;
        // Obtener datos del curso
        $curso = $em->createQueryBuilder()
                    ->select('ie.id as sie, gt.id as gestion')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                    ->where('ieco.id = :idCursoOferta')
                    ->setParameter('idCursoOferta',$ieco)
                    ->getQuery()
                    ->getResult();

        $sie = $curso[0]['sie'];
        $gestion = $curso[0]['gestion'];

        $arrayMaestros = array();
        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
            // trimestrales
            $inicio = 6;
            $fin = 8;
        }else{
            // Bimestrales
            $inicio = 0;
            $operativo = $this->operativo($sie,$gestion);
            if($operativo == 5){
                $fin = 4; //4;
            }else{
                $fin = $operativo - 1;
            }

        }
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($maestrosMateria as $mm) {
                if($mm->getNotaTipo()->getId() == $i){
                    $arrayMaestros[] = array(
                                            'id'=>$mm->getId(),
                                            'idmi'=>$mm->getMaestroInscripcion()->getId(),
                                            'horas'=>$mm->getHorasMes(),
                                            'idNotaTipo'=>$mm->getNotaTipo()->getId(),
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
                    $existe = true;
                    break;
                }
            }
            if($existe == false){
                $arrayMaestros[] = array(
                                            'id'=>'nuevo',
                                            'idmi'=>'',
                                            'horas'=>'',
                                            'idNotaTipo'=>$i,
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
            }
        }

        $maestros = $em->createQueryBuilder()
                        ->select('mi')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','mi.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','mi.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                        ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                        ->where('ie.id = :sie')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('rt.id = 2')
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();

        $operativo = $this->operativo($sie,$gestion);

        //dump($arrayMaestros);die;

        return $this->render('SieRegularBundle:RegularizationMaestro:maestros.html.twig',array('maestrosCursoOferta'=>$arrayMaestros, 'maestros'=>$maestros,'ieco'=>$ieco,'operativo'=>$operativo));
    }

    public function maestrosAsignarAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        $horas = $request->get('horas');

        if(count($ieco)>0){
            for($i=0;$i<count($ieco);$i++){
                if($horas[$i] == ''){
                    $horasNum = 0;
                }else{
                    $horasNum = $horas[$i];
                }
                if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                    $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                    $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                    $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $newCOM->setHorasMes($horasNum);
                    $newCOM->setFechaRegistro(new \DateTime('now'));
                    $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                    $newCOM->setEsVigenteMaestro('t');
                    $em->persist($newCOM);
                    $em->flush();
                }else{
                    if($idmi[$i] != ''){
                        $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                        $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                        $updateCOM->setHorasMes($horasNum);
                        $updateCOM->setFechaModificacion(new \DateTime('now'));
                        $updateCOM->setEsVigenteMaestro('t');
                        $em->flush();
                    }
                }
            }
            $mensaje = "Cambios guardados";
        }else{
            $mensaje = "No registrado";
        }


        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0],'mensaje'=>$mensaje));
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($registroOperativo);die;
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
        }
        return $lit;
    }


    public function resetAction(Request $request){
      //build the search form
      return $this->render('SieRegularBundle:RegularizationMaestro:reset.html.twig', array('form' => $this->formReset($request->getSession()->get('currentyear'))->createView()));

    }
    /*
     * Formulario de reset de institucion educativa
     */

    private function formReset($gestionactual) {
        $gestiones = array();
        for($i=2016;$i>=2016;$i--){
            $gestiones[$i] = $i;
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizationmaestro_reset_eliminate'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function eliminateAction(Request $request){
      //get form data
      $form = $request->get('form');
      //get db conexion
      $em = $this->getDoctrine()->getManager();
      // get the UE data
      /*
       * verificamos si tiene tuicion
       */
      $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
      $query->bindValue(':user_id', $this->session->get('userId'));
      $query->bindValue(':sie', $form['institucioneducativa']);
      $query->bindValue(':rolId', $this->session->get('roluser'));
      $query->execute();
      $aTuicion = $query->fetchAll();

      if ($aTuicion[0]['get_ue_tuicion']) {
          $institucion = $form['institucioneducativa'];
          $gestion = $form['gestion'];
      } else {
          $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
          return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
      }

      //check if the ue did it  the validation
      $objRegularization = $em->getRepository('SieAppWebBundle:LogRegularizacionMaestro')->findOneBy(
          array('institucioneducativaId'=>$form['institucioneducativa'])
        );
      if(!$objRegularization){
        $this->get('session')->getFlashBag()->add('noTuicion', 'Unidad Educativa no regularizo Maestros');
        return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
      }
      //find the UE info
      $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucioneducativa']);
 // dump($objUe);
  // dump($objRegularization);;  die;
      return $this->render('SieRegularBundle:RegularizationMaestro:eliminate.html.twig', array(
        'objUe' => $objUe,
        'objRegularization' => $objRegularization,
        'form' => $this->restartForm(($objRegularization->getId()))->createView()
      ));

    }

    private function restartForm($objRegularization){
      return $this->createFormBuilder()
              ->setAction($this->generateUrl('regularizationmaestro_reset_restart'))
              ->add('data', 'hidden', array('data'=>$objRegularization))
              ->add('restablecer', 'submit', array('label'=>'Restablecer', 'attr'=>array('class'=>'btn btn-warning btn-block btn-xs')))
              ->getForm()
              ;
    }

    public function restartAction( Request $request){
      //create cDB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get data send
      $form = $request->get('form');

      try {
        $objRegularizationRemove = $em->getRepository('SieAppWebBundle:LogRegularizacionMaestro')->find((int)$form['data']);
        //remove the obj dataa
        $em->remove($objRegularizationRemove);
        $em->flush();
        $em->getConnection()->commit();

        $this->get('session')->getFlashBag()->add('noSearch', 'Unidad Educativa Restablecida.');
        return $this->render('SieRegularBundle:RegularizationMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));

      } catch (Exception $e) {

        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }

    }



}
