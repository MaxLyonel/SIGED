<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\UsuarioGeneracionRude;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteInscripcionExtranjero;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
/**
 * Estudiante controller.
 *
 */
class InscriptionExtranjerosController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $arrYears;
    public $labelOption;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
      $this->arrYears = array($this->session->get('currentyear') => $this->session->get('currentyear')/*,'2015' => '2015', '2014' => '2014'*/);
    }

    public function indexAction() {

        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // $arrUserAllowed = array('13828182');
        // if(!in_array($this->session->get('userId'),$arrUserAllowed)){
        //   return $this->redirect($this->generateUrl('login'));
        // }
        return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:chooseIncription.html.twig', array(
                    'form' => $this->chooseIncriptionForm()->createView(),
        ));
    }

    private function chooseIncriptionForm(){

    if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8){
        $arrOptionInscription = array('19' => 'Extranjero', '59'=>'Incial/Primaria', '100'=>'Incial/Primaria R.M. No 2378/2017' );
    }else{
        $arrOptionInscription = array('19' => 'Extranjero', '59'=>'Incial/Primaria' );
    }

      
      $form = $this->createFormBuilder()
              ->setAction($this->generateUrl('inscription_extranjeros_main'))
              ->add('optionInscription', 'choice', array('mapped' => false, 'label' => 'Inscripción', 'choices' => $arrOptionInscription, 'attr' => array('class' => 'form-control')))
              ->add('buscar', 'submit', array('label' => 'Continuar'))
              ->getForm();
      return $form;
    }

    /**
     * Lists index inscription.
     *
     */
    public function mainAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //define the options
        $arrOptionInscription = array('19' => 'Extranjero', '59'=>'Incial/Primaria', '100'=>'Incial/Primaria R.M. No 2378/2017');
        //get the data send
        $formData = $request->get('form');//dump($formData);die;
        $this->labelOption = array('label'=> $arrOptionInscription[$formData['optionInscription']], 'id'=>$formData['optionInscription']);
        $form = json_encode($formData);

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:index.html.twig', array(
                    'form' => $this->createSearchForm($form)->createView(),
                    'labelInscription' => $this->labelOption,
                    'optionData'=>$form

        ));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm($data) {
        $estudiante = new Estudiante();
        $arrYears = array($this->session->get('currentyear') => $this->session->get('currentyear')/*,'2015' => '2015', '2014' => '2014'*/);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestión', 'choices' => $this->arrYears, 'attr' => array('class' => 'form-control')))
                ->add('dataOption', 'hidden', array('data'=>$data ))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
//->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
    }

    /**
     * build the new student extranjero
     * @return type
     */
    public function norudeAction(Request $request) {
      //get the values to send
      $optionInscription = $request->get('optionInscription');

        return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:norude.html.twig', array(
                    'form' => $this->formnorude($optionInscription)->createView(),
        ));
    }

    private function formnorude($optionInscription) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_extranjeros_saveext'))
                        ->add('pais', 'entity', array('label' => 'País', 'class' => 'SieAppWebBundle:PaisTipo', 'attr' => array('class' => 'form-control')))
                        ->add('ueprocedencia', 'text', array('label' => 'Unidad Educativa Procedencia', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,85}')))
                        ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestión', 'choices' => $this->arrYears, 'attr' => array('class' => 'form-control')))
                        ->add('ci', 'text', array('required' => false, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,8}', 'required' => false)))
                        ->add('complemento', 'text', array('required' => false, 'label' => 'Complemento', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9 a-z A-Z]{1,2}', 'maxlength' => '2', 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                        ->add('paterno', 'text', array('required' => false, 'label' => 'Paterno', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('materno', 'text', array('required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('nombre', 'text', array('attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('fnac', 'text', array('label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                        ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control')))
                        ->add('optionInscription','hidden', array('data'=>$optionInscription))
                        ->add('save', 'submit', array('label' => 'Buscar'))
                        ->getForm();
    }

    /**
     * save inscription extranjero first time
     * @param Request $request
     * @return type
     */
    public function saveextAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        //encontramos las coincidencias
        $sameStudents = $this->getCoincidenciasStudent($form);

        return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:resultnorude.html.twig', array(
                    'newstudent' => $form,
                    'samestudents' => $sameStudents,
                    'formninguno' => $this->nobodyform($form)->createView(),
                    'gestion'=>$form['gestion']
        ));
    }

    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_extranjeros_savenew'))
                        ->add('paterno', 'hidden', array('data' => strtoupper($data['paterno']), 'required' => false))
                        ->add('materno', 'hidden', array('data' => strtoupper($data['materno']), 'required' => false))
                        ->add('nombre', 'hidden', array('data' => strtoupper($data['nombre'])))
                        ->add('fnac', 'hidden', array('data' => $data['fnac']))
                        ->add('ci', 'hidden', array('data' => $data['ci']))
                        ->add('complemento', 'hidden', array('data' => $data['complemento']))
                        ->add('pais', 'hidden', array('data' => $data['pais']))
                        ->add('ueprocedencia', 'hidden', array('data' => $data['ueprocedencia']))
                        ->add('genero', 'hidden', array('data' => $data['genero']))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('optionInscription','hidden', array('data'=>$data['optionInscription']))
                        ->add('ninguno', 'submit', array('label' => 'Nueva Inscripción', 'attr' => array('class' => 'btn btn-warning btn-sm btn-block')))
                        ->getForm();
    }

    /**
     * get the same students with the data send
     * @param type $data
     * @return type
     * return obj sutudent list
     */
    private function getCoincidenciasStudent($data) {
        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
        $query = $repository->createQueryBuilder('e')
                ->where('e.paterno like :paterno')
                ->andWhere('e.materno like :materno')
                ->andWhere('e.nombre like :nombre')
                ->setParameter('paterno', strtoupper($data['paterno']) . '%')
                ->setParameter('materno', strtoupper($data['materno']) . '%')
                ->setParameter('nombre', strtoupper($data['nombre']) . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();


        try {
            return $query->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
//echo $exc->getTraceAsString();
            return array();
        }
    }

    public function savenewAction(Request $request) {
        $form = $request->get('form');

        $sw = 0;
        $data = array();
        $formExtranjeros = $this->createFormNewExtranjeros(0, $sw, $form);
        return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:newextranjero.html.twig', array(
                    'datastudent' => $form,
                    'formExtranjeros' => $formExtranjeros->createView()
        ));
    }

    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormNewExtranjeros($idStudent, $sw, $data) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_savenewextranjero'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('gestion', 'hidden', array('data' => $data['gestion']))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('optionInscription','hidden', array('data'=>$data['optionInscription']))
                ->add('save', 'button', array('label' => 'Verificar y Registrar', 'attr'=>array('onclick'=>'checkInscriptionExtranjero()')))
                ->getForm();
    }

    /**
     * todo the registration of extranjero
     * @param Request $request
     *
     */
    public function savenewextranjeroAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        $optionInscription = json_decode($form['optionInscription'],true);

        try {
            //get info old about inscription
            //$dataNivel = $this->getInfoInscriptionStudent($form['nivel'], $form['grado']);

            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //create rude and get the student info
            $digits = 3;
            $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
            $rude = $form['institucionEducativa'] . $form['gestion'] . $mat . $this->gererarRude($form['institucionEducativa'] . $form['gestion'] . $mat);
            $newStudent = unserialize($form['newdata']);
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
            $query->execute();
            //echo $rude;die('krlos');
            $student = new Estudiante();
            $student->setPaterno($newStudent['paterno']);
            $student->setMaterno($newStudent['materno']);
            $student->setNombre($newStudent['nombre']);
            $student->setCarnetIdentidad($newStudent['ci']);
            $student->setComplemento($newStudent['complemento']);
            $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['genero']));
            $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
            $student->setFechaNacimiento(new \DateTime($newStudent['fnac']));
            $student->setCodigoRude($rude);
            //falta from UE && pais
            $em->persist($student);
            $em->flush();
            //print_r($currentInscrip);
            //get the id of course
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestion']
            ));
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($optionInscription['optionInscription']));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();

            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(), $form['gestion']);
            //to register the new rude and the user
            $UsuarioGeneracionRude = new UsuarioGeneracionRude();
            $UsuarioGeneracionRude->setUsuarioId($this->session->get('userId'));
            $UsuarioGeneracionRude->setFechaRegistro(new \DateTime('now'));
            $aDatosCreacion = array('sie' => $form['institucionEducativa'], 'rude' => $rude);
            $UsuarioGeneracionRude->setDatosCreacion(serialize($aDatosCreacion));
            $em->persist($UsuarioGeneracionRude);
            $em->flush();

            // obtenemos las notas
            $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($studentInscription->getId());
            //dump($arrayNotas);die;
            // Try and commit the transaction
            $em->getConnection()->commit();

            return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:notasIncriptions.html.twig', array(
                'asignaturas'=>$arrayNotas['notas'],'cualitativas'=>$arrayNotas['cualitativas'],'operativo'=>$arrayNotas['operativo'],
                'nivel'=>$form['nivel'],'idInscripcion'=>$studentInscription->getId()
            ));
            /*
            //todo the validation
//            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('SELECT * from sp_corrije_inscrip_transac_estudiante_obser(:rude::VARCHAR, :inscripcionid ::VARCHAR)');
            $query->bindValue(':rude', $rude);
            $query->bindValue(':inscripcionid', $studentInscription->getId());
            $query->execute();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Estudiante Extranjero ya fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));*/
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $gestion
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
            $query->execute();
            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
    }

    /**
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
    private function gererarRude($cadena) {
        $codigoRude = "";
        $codigoVerificacion = "123456789A0";
        $peso = 2;
        $sum = 0;
        $int = 0;
        while ($int < strlen($cadena)) {
            if ($peso == 7)
                $peso = 2;
            $sum = $sum + ($peso * ord(substr($cadena, $int, 1)));
            $peso = $peso + 1;
            $int = $int + 1;
        }
        return substr($codigoVerificacion, 10 - ($sum % 11), 1);
    }

//////////////////////////////////////////////////////////////////////////////

    /**
     * build the cursos in a array
     * @param type $limitA
     * @param type $limitB
     * @param type $limitC
     * return array with the courses
     */
    private function fillCursos() {
        $this->aCursos = array(
            ('11-1-1'),
            ('11-1-2'),
            ('12-1-1'),
            ('12-1-2'),
            ('12-1-3'),
            ('12-2-4'),
            ('12-2-5'),
            ('12-2-6'),
            ('13-1-1'),
            ('13-1-2'),
            ('13-2-3'),
            ('13-2-4'),
            ('13-3-5'),
            ('13-3-6')
        );
        return($this->aCursos);
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //flag to know is a new estranjero student
        $sw = 0;
        $data = array();
        $form = $request->get('form');
        /**
         * add validation QA
         * @var [type]
         */
        $objObservation = $this->get('seguimiento')->getStudentObservationQA($form);
        if($objObservation){
             $message = "Estudiante observado - rude " . $form['codigoRude'] . " :";
            $this->addFlash('notiext', $message);
            $observaionMessage='';
            // foreach ($objObservation as $key => $value) {
            //   $observaionMessage .=$value['obs']."-".$value['institucion_educativa_id']."*****";
            // }
            $observaionMessage = 'Estudiante presenta inconsistencia, se sugiere corregirlos por las opciones de calidad...';
            $this->addFlash('studentObservation', $observaionMessage);
            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
        }

        if ($request->get('form')) {
            $form = $request->get('form');
            $gestion = $form['gestion'];
            $data = array("id" => $request->get('id'), "codigoRude" => $form['codigoRude'], "pais" => $request->get('pais'), "ueprocedencia" => $request->get('ueprocedencia'), 'gestion'=>$form['gestion'], 'dataOption'=> $form['dataOption']);
        } else {
            // is turn on if the student exists
            $sw = 1;
            $data = array("id" => $request->get('id'), "codigoRude" => $request->get('codigoRude'), "pais" => $request->get('pais'), "ueprocedencia" => $request->get('ueprocedencia'), 'dataOption'=> $form['dataOption']);
            $form['codigoRude'] = $request->get('codigoRude');
            $gestion = $request->get('gestion');
        }
        //define the options
        $arrOptionInscription = array('19' => 'Extranjero', '59'=>'Incial/Primaria','100'=>'Incial/Primaria RM No 2378/2017');
//dump($form);
        $formData = json_decode($form['dataOption'],true);
        //$labelInscription = $
        // dump($formData);die;
        $this->labelOption = array('label'=> $arrOptionInscription[$formData['optionInscription']], 'id'=>$formData['optionInscription']);
//die;
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //check if the student exists
        if ($student) {
            //check the tuicion
//            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
//            $query->bindValue(':user_id', $this->session->get('userId'));
//            $query->bindValue(':sie', $studentInscription->getInstitucioneducativa()->getId());
//            $query->bindValue(':roluser', $this->session->get('roluser'));
//            $query->execute();
//            $aTuicion = $query->fetchAll();
//            //check the tuicion
//            if (!$aTuicion[0]['get_ue_tuicion']) {
//                $this->session->getFlashBag()->add('notiext', 'Usuario no tiene tuición');
//                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
//            }
            //get data about inscription
            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo IN (:matricula)')
                    ->setParameter('id', $student->getId())
                    ->setParameter('gestion', $gestion)
                    ->setParameter('matricula', array(4,5))
                    ->getQuery();
            $studentInscription = $query->getResult();
            //chedk if the student has inscription
            if ($studentInscription) {
                $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción para la presente gestión');
                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
            }

            //verificamos si tiene inscripcion en la gestion actual
            //get los historico de cursos
            $inscriptions = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
            $formExtranjeros = $this->createFormExtranjeros($student->getId(), $sw, $data, $gestion);
            //everything is ok build the info
            return $this->render($this->session->get('pathSystem') . ':InscriptionExtranjeros:result.html.twig', array(
                        'datastudent' => $student,
                        'currentInscription' => $inscriptions,
                        'labelInscription' => $this->labelOption,
                        'formExtranjeros' => $formExtranjeros->createView()
            ));
        } else {
            $message = "Estudiante con rude " . $form['codigoRude'] . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('inscription_extranjeros_index');
        }
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
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', $id)
                ->orderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormExtranjeros($idStudent, $sw, $data, $gestion) {

        $aDataOption = json_decode($data['dataOption'],true);
// dump($aDataOption);die;
        $typeElement = ($aDataOption['optionInscription']==19)?'text':'hidden';
        $em = $this->getDoctrine()->getManager();

         $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_save'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('gestion', 'hidden', array('data'=>$gestion))
                ->add('save', 'hidden', array('label' => 'Verificar y Registrar', 'attr'=>array('onclick'=>'checkInscriptionExtranjero()')))
                ;
                if($aDataOption['optionInscription']==19){
                  $formOmitido =$formOmitido->add('institucionEducativaDe', 'text', array('label' => 'Unidad Educativa De:', 'attr' => array('maxlength' => 50, 'class' => 'form-control')))
                    ->add('cursoVencido', 'text', array('label' => 'Curso Vencido:', 'attr' => array('maxlength' => 50, 'class' => 'form-control')))
                    ->add('imgInsExtranjero', 'file', array('mapped'=>'false','label' => 'Subir img', 'required' => false, 'data_class' => null))
                    ->add('pais', 'entity', array('label' => 'Del Pais', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                        'query_builder' => function (EntityRepository $e) {
                          return $e->createQueryBuilder('pt')
                                  ->orderBy('pt.id', 'ASC');
                          }, 'property' => 'pais',));
                }

                if($aDataOption['optionInscription']==100){
                  $formOmitido = $formOmitido                    
                    ->add('imgInsExtranjero', 'file', array('mapped'=>'false','label' => 'Subir img', 'required' => false, 'data_class' => null))
                    ;
                }


                $formOmitido=$formOmitido->getForm();

        return $formOmitido;
    }

        private function getCurrentInscriptionsByGestoinValida($id, $gestion) {
        //$session = new Session();
            $swInscription = false;
            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $entity->createQueryBuilder('e')
                    ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                    ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                    ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                    ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                    ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                    ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                    ->where('e.codigoRude = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                    ->setParameter('id', $id)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('mat', array( 3,4,5,6 ))
                    ->orderBy('iec.gestionTipo', 'DESC')
                    ->getQuery();

                $objInfoInscription = $query->getResult();
                if(sizeof($objInfoInscription)>=1)
                  return $objInfoInscription;
                else
                  return false;

          }
    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function saveAction(Request $request) {



        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');

        $aDataStudent = unserialize($form['newdata']);
        $aDataOption = json_decode($aDataStudent['dataOption'],true);
// dump($aDataOption);die;

        try {

          //validation inscription in the same U.E
          $objCurrentInscriptionStudent = $this->getCurrentInscriptionsByGestoinValida($aDataStudent['codigoRude'],$aDataStudent['gestion']);

            if($objCurrentInscriptionStudent){
              if ($objCurrentInscriptionStudent[0]['sie']==$form['institucionEducativa']){
                $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción en la misma Unidad Educativa, realize el cambio de estado');
                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
              }
            }
          //check if the user can do the inscription
          //validate allow access
            $arrAllowAccessOption = array(7,8);
            if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
              $defaultController = new DefaultCont();
              $defaultController->setContainer($this->container);

              $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$form['gestion']));
              //validate if the user download the sie file
              if($swAccess){
                $message = 'No se puede realizar la inscripción debido a que ya descargo el archivo SIE';
                $this->addFlash('notiext', $message);
                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
              }
            }

            switch ($aDataOption['optionInscription']) {
                case 19:
                case 100:
                        $oFile = $request->files->get('siefile');
                        //get the name of file upload
                        $originalName = $oFile->getClientOriginalName();
                        //get the extension
                        $aName = explode('.', $originalName);
                        $fileType = $aName[sizeof($aName) - 1];
                        //validate the allows extensions
                        $aValidTypes = array('png, jpg');

                        if (in_array(strtolower($fileType), $aValidTypes)) {
                            $this->session->getFlashBag()->add('notiext', 'El archivo que intenta subir No tiene la extension correcta');
                            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                        }
                        //validate the files weight
                        if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
                            $this->session->getFlashBag()->add('notiext', 'El archivo que intenta subir no tiene el tamaño correcto');
                            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                        }

                    break;
                case 59:  
                        //validate the year of student
                        $idStudent = $form ['idStudent'];
                        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
                        $tiempo = $this->tiempo_transcurrido($objStudent->getFechaNacimiento()->format('d-m-Y'), '30-6-2018');

                        switch ($tiempo[0]) {
                          case 3:
                            # code...
                            if($form['nivel']=='11' && $form['grado']=='0'){
                              //good
                            }else{
                              $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                              return $this->redirect($this->generateUrl('inscription_extranjeros_index'));

                            }
                            break;
                          case 4:

                            # code...
                            if($form['nivel']=='11' && $form['grado']=='1'){
                              //good
                            }else{
                              $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                              return $this->redirect($this->generateUrl('inscription_extranjeros_index'));

                            }
                            break;
                          case 5:
                            # code...
                            if($form['nivel']=='11' && $form['grado']=='2'){
                              //good
                            }else{
                              $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                              return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                            }
                            break;
                          case 6:
                            # code...
                            if($form['nivel']=='12' && $form['grado']=='1'){
                              //good
                            }else{
                              $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                              return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                            }
                            break;
                          case 7 or 15:
                            if($form['nivel']=='12' && $form['grado']=='1'){
                              //good
                            }else{
                              $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                              return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                            }
                            break;

                          default:
                            # code...
                            $this->session->getFlashBag()->add('notiext', 'El estudiante no cumple con lo requerido en edad');
                            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                            break;
                        }

                    break;
                
                default:
                    # code...
                    break;
            }

          //check if the inscription is extranjero
          // if($aDataOption['optionInscription']==19){    
           
          // }else{

          // }

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestion']
            ));
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            if($aDataOption['optionInscription']==19){
                $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($aDataOption['optionInscription']));
            }else{
                $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(59));
            }
            $em->persist($studentInscription);
            $em->flush();
            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(),$form['gestion']);
            // save the file and register into DB
            switch ($aDataOption['optionInscription']) {
                case 19:                           
                      $dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/insExtranjeros/';
                      if (!file_exists($dirtmp)) {
                          mkdir($dirtmp, 0777);
                      }
                      //move the file emp to the directory temp
                      // $file = $oFile->move($dirtmp, $originalName);
                      $file = $oFile->move($dirtmp, $studentInscription->getId().'_'.$form['gestion']);
                      //save info extranjero inscription
                      $objEstudianteInscripcionExtranjero = new EstudianteInscripcionExtranjero();
                      $objEstudianteInscripcionExtranjero->setInstitucioneducativaOrigen($form['institucionEducativaDe']);
                      $objEstudianteInscripcionExtranjero->setCursoVencido($form['cursoVencido']);
                      $objEstudianteInscripcionExtranjero->setRutaImagen($dirtmp.$studentInscription->getId().'_'.$form['gestion']);
                      $objEstudianteInscripcionExtranjero->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                      $objEstudianteInscripcionExtranjero->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
                      $em->persist($objEstudianteInscripcionExtranjero);
                      $em->flush();
                    break;
                case 100:                           
                      $dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/insExtranjeros/';
                      if (!file_exists($dirtmp)) {
                          mkdir($dirtmp, 0777);
                      }
                      //move the file emp to the directory temp
                      // $file = $oFile->move($dirtmp, $originalName);
                      $file = $oFile->move($dirtmp, $studentInscription->getId().'_'.$form['gestion']);
                      //save info extranjero inscription
                      $objEstudianteInscripcionExtranjero = new EstudianteInscripcionExtranjero();
                      $objEstudianteInscripcionExtranjero->setInstitucioneducativaOrigen('rm2378/2017');
                      $objEstudianteInscripcionExtranjero->setCursoVencido('rm2378/2017');
                      $objEstudianteInscripcionExtranjero->setRutaImagen($dirtmp.$studentInscription->getId().'_'.$form['gestion']);
                      $objEstudianteInscripcionExtranjero->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                      $objEstudianteInscripcionExtranjero->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find(1));
                      $em->persist($objEstudianteInscripcionExtranjero);
                      $em->flush();
                    break;
                
                
                default:
                    # code...
                    break;
            }
            // if($aDataOption['optionInscription']==19){
            // }

            // obtenemos las notas
            // $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($studentInscription->getId());
            //dump($arrayNotas);die;

            // Registro de materia curso oferta en el log
            /*$this->get('funciones')->setLogTransaccion(
                $studentInscription->getId(),
                'estudiante_inscripcion',
                'C',
                '',
                $studentInscription,
                '',
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );*/
            // Try and commit the transaction
            $em->getConnection()->commit();

            $message = 'Datos Registrados Correctamente';
            $this->addFlash('goodext', $message);
            //return $this->redirect($this->generateUrl('inscription_extranjeros_index'));

            return $this->forward('SieRegularBundle:RegularizarNotas:show',array(
              'idInscripcion'=>$studentInscription->getId()
              ));


            // return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:menssageInscription.html.twig', array());
            //this for the notas
            // return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:notasIncriptions.html.twig', array(
            //     'asignaturas'=>$arrayNotas['notas'],'cualitativas'=>$arrayNotas['cualitativas'],'operativo'=>$arrayNotas['operativo'],
            //     'nivel'=>$form['nivel'],'idInscripcion'=>$studentInscription->getId()
            // ));

            //todo the validation
            /*$query = $em->getConnection()->prepare('SELECT * from sp_corrije_inscrip_transac_estudiante_obser(:rude::VARCHAR, :inscripcionid ::VARCHAR)');
            $query->bindValue(':rude', $aDataStudent['codigoRude']);
            $query->bindValue(':inscripcionid', $studentInscription->getId());
            $query->execute();
            // everything ok, do the commit on db
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Estudiante Extranjero ya fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
            */
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    function tiempo_transcurrido($fecha_nacimiento, $fecha_control) {
      // slist($year,$month, $day) = explode('-', $fecha_nacimiento);
      // $fecha_nacimiento = $year.'-'.$month.'-'.$day;

            $fecha_actual = $fecha_control;

            if (!strlen($fecha_actual)) {
                $fecha_actual = date('d-m-Y');
            }

            // separamos en partes las fechas
            $array_nacimiento = explode("-", str_replace('/', '-', $fecha_nacimiento));
            $array_actual = explode("-", $fecha_actual);

            $anos = $array_actual[2] - $array_nacimiento[2]; // calculamos años
            $meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses
            $dias = $array_actual[0] - $array_nacimiento[0]; // calculamos días
            //ajuste de posible negativo en $días
            if ($dias < 0) {
                --$meses;

                //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual
                switch ($array_actual[1]) {
                    case 1:
                        $dias_mes_anterior = 31;
                        break;
                    case 2:
                        $dias_mes_anterior = 31;
                        break;
                    case 3:
                        if (bisiesto($array_actual[2])) {
                            $dias_mes_anterior = 29;
                            break;
                        } else {
                            $dias_mes_anterior = 28;
                            break;
                        }
                    case 4:
                        $dias_mes_anterior = 31;
                        break;
                    case 5:
                        $dias_mes_anterior = 30;
                        break;
                    case 6:
                        $dias_mes_anterior = 31;
                        break;
                    case 7:
                        $dias_mes_anterior = 30;
                        break;
                    case 8:
                        $dias_mes_anterior = 31;
                        break;
                    case 9:
                        $dias_mes_anterior = 31;
                        break;
                    case 10:
                        $dias_mes_anterior = 30;
                        break;
                    case 11:
                        $dias_mes_anterior = 31;
                        break;
                    case 12:
                        $dias_mes_anterior = 30;
                        break;
                }

                $dias = $dias + $dias_mes_anterior;

                if ($dias < 0) {
                    --$meses;
                    if ($dias == -1) {
                        $dias = 30;
                    }
                    if ($dias == -2) {
                        $dias = 29;
                    }
                }
            }

            //ajuste de posible negativo en $meses
            if ($meses < 0) {
                --$anos;
                $meses = $meses + 12;
            }

            $tiempo[0] = $anos;
            $tiempo[1] = $meses;
            $tiempo[2] = $dias;

            return $tiempo;
        }

        function bisiesto($anio_actual) {
            $bisiesto = false;
            //probamos si el mes de febrero del año actual tiene 29 días
            if (checkdate(2, 29, $anio_actual)) {
                $bisiesto = true;
            }
            return $bisiesto;
        }


    /**
     *
     * @param type $nivel
     * @param type $grado
     */
    public function getInfoInscriptionStudent($nivel, $grado) {

        $sw = 1;
        $cursos = $this->aCursos;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            $anivel = explode("-", current($cursos));
            if ($anivel[0] == $nivel && $anivel[2] == $grado) {
                $inscriptionInfo = $anivel;
                $sw = 0;
            }
            next($cursos);
        }
        return ($inscriptionInfo);
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
         */
        $aniveles = array();
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
        $em = $this->getDoctrine()->getManager();
        //get the Niveles

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $id)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->getQuery();
        $aNiveles = $query->getResult();
        foreach ($aNiveles as $nivel) {
            $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
        }

        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();
        foreach ($aGrados as $grado) {
            $agrados[$grado[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado();
        }

        $response = new JsonResponse();
        return $response->setData(array('agrados' => $agrados));
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function findparaleloAction($grado, $sie, $nivel, $gestion) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
        }

        $response = new JsonResponse();
        return $response->setData(array('aparalelos' => $aparalelos));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturnoAction($paralelo, $sie, $nivel, $grado, $gestion) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

    public function verificarRegistrarACtion(Request $request){
      dump($request);die;
    }

}
