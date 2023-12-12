<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Sie\AppWebBundle\Entity\BthEstudianteInscripcionGestionEspecialidad;
use Sie\AppWebBundle\Entity\BthControlOperativoModificacionEspecialidades;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\GestionTipo;

// use Monolog\Handler\NewRelicHandlerTest;

 
use Doctrine\DBAL\Types\Type;
Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTimeType');

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
//        $id_usuario = $this->session->get('userId');
//        //validation if the user is logged
//        if (!isset($id_usuario)) {
//            return $this->redirect($this->generateUrl('login'));
//        }
//        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array());
        //get the value to send
        $formResponse = $request->get('form');
// dump($formResponse);die;
        $em = $this->getDoctrine()->getManager();
        //find the levels from UE
        //levels gestion -1
        //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
        
        // get the QA observactions
        $form['reglas'] = '2,3,6,8,10,12,13,15,16,20,24,25,26';
        $form['sie'] = hex2bin($formResponse['sie']);
        $form['gestion'] = hex2bin($formResponse['gestion']);                    
        
        if ($form['gestion'] == $this->session->get('currentyear')) {
            // $objObsQA = $this->getObservationQA($form);        temporalmente solo 3er trimestre
            $objObsQA = null;
        } else {
            $objObsQA = null;
        }       
        // check QA on UE
        if($objObsQA){
            $swRegisterCalifications = false;
        }else{
            $swRegisterCalifications = true;
        }
    
        // get the QA BJP observactions
        $form['reglas'] = '12,13,26,24,25,8,15,20,11,37,63,60,61,62';
        $form['gestion'] = $form['gestion'];
        $form['sie'] = $form['sie'];
        if ($form['gestion'] == $this->session->get('currentyear')) {
            // $objObsQAbjp = $this->getObservationQA($form);     
            $objObsQAbjp = null;
        } else {
            $objObsQAbjp = null;
        }       
        // check QA on UE
        if($objObsQAbjp){
            $swRegisterPersonBjp = false;
        }else{
            $swRegisterPersonBjp = true;
        }
        $objObsQA = null;
        $objObsQA = $objObsQAbjp;
        //dump($form);die;
        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoUeducativaBySieGestion($form['sie'], $form['gestion']);
        //dump($objUeducativa);die;
        $exist = true;
        $tieneSextoSec = false;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId']),
                    'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'], 'swRegisterCalifications' => $swRegisterCalifications, 'swRegisterPersonBjp' => $swRegisterPersonBjp)
                ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa,'nivelId'=> $uEducativa['nivelId'],'gradoId'=> $uEducativa['gradoId']);

                if($uEducativa['nivelId'] == 13 and $uEducativa['gradoId'] == 6){
                    $tieneSextoSec = true;
                }
            }
        } else {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        //dump($aInfoUnidadEductiva);die;
        // check if the UE close the rude task
        $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
          'institucioneducativa' => $form['sie'],
          'gestionTipoId'  => $form['gestion'],
          'institucioneducativaOperativoLogTipo' => 4
        ));
        // create the var to show the message about close opertive
        $this->session->set('closeRude',(!$objinstitucioneducativaOperativoLogExist)?false:true);

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        //$objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);die('krlossdfdfdfs');
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);


        // Verificamos si se cerro el sexto grado de acuerdo al operativo
        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
        if($operativo >3){
            $this->session->set('donwloadLibreta', true);
        }else{
            $this->session->set('donwloadLibreta', false);
        }

        $mostrarSextoCerrado = false;
        if($tieneSextoSec and $gestion >= 2018 and $operativo == 4){
            $validacionSexto = $this->get('funciones')->verificarGradoCerrado($sie, $gestion);
            if(!$validacionSexto){
                $mostrarSextoCerrado = true;
            }
        }
       //evaluar el estado del operatvo de modificar/eiminar especialidades, t= operativoCerrado f = operativoHabilitado 
        $entity = $em->getRepository('SieAppWebBundle:BthControlOperativoModificacionEspecialidades')->findOneBy(array('institucioneducativaId'=>$sie, 'gestionTipoId'=>$gestion,'estadoOperativo'=>true));
        //dump($entity);die;
        //evaluar si la ue es plena 

             $query = $em->getConnection()->prepare("SELECT * 
            from institucioneducativa_humanistico_tecnico 
            WHERE institucioneducativa_id = $sie and gestion_tipo_id = $gestion
            and institucioneducativa_humanistico_tecnico_tipo_id = 1 and grado_tipo_id in (5,6)");
            $query->execute();
            $entity_validacion = $query->fetchAll();
            //dump($entity_validacion);die;


        $ue_plena =($entity_validacion)?true:false;
        //dump($ue_plena);die;

        if($ue_plena){
            if($entity){
            $estado = false;
            }else{
                $estado =  true;
            }
        }else{
            $estado = false;
        }


        //get variables to show and hidde the close sexto secc operativo
        $query = $em->getConnection()->prepare("select * from institucioneducativa_curso where institucioneducativa_id = " . $sie . " and gestion_tipo_id = " . $this->session->get('currentyear') . " and nivel_tipo_id = 13 and grado_tipo_id = 6");
        $query->execute();
        $objDataOperativo = $query->fetchAll();
        $haslevel = false;
        $hasgrado = false;
        if(sizeof($objDataOperativo)>0){
            $haslevel = 13;
            $hasgrado = 6;
        }
        $closeopesextosecc = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestion);
        
        // set variables to show and ejecute the close operativo sexto fo secc 
        $arrLevelandGrado = array('haslevel'=> $haslevel, 'hasgrado' => $hasgrado, 'closeopesextosecc' => $closeopesextosecc, 'gestion' => $gestion, 'operativo' => $operativo);
        // dump($arrLevelandGrado);die;


        
        // if($entity){
        //     if($ue_plena == false){
        //         $estado = false;
        //     }

        // }else{
        //     $estado =  true;
        // }
        //dump($estado);die;

        /**
         * idiomas para impresion de libretas
         */

        $sql="
        select distinct idioma_tipo.* from trad_paquete_idioma
        inner join idioma_tipo on idioma_tipo.id = trad_paquete_idioma.idioma_tipo_id and idioma_tipo.id <> 48
        order by 2";

        $db = $em->getConnection(); 
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $idiomasArray = $stmt->fetchAll();
        

        //obterner el grado para los reportes de  operatvo de modificar/eiminar
        $grado = ($this->session->get('gradoTipoBth'))?$this->session->get('gradoTipoBth'):[0];
        $gradoId = implode(",",$grado);
        // dump($this->session->get('pathSystem'));die;
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array(
                    'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                    'sie' => $form['sie'],
                    'gestion' => $form['gestion'],
                    'objUe' => $objUe,
                    'objObsQA' => $objObsQA,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist,
          //          'levelAutorizados' => $objInfoAutorizadaUe,
                    'odataUedu' => $odataUedu,
                    'mostrarSextoCerrado'=>$mostrarSextoCerrado,
                    'estado'=>$estado,
                    'arrLevelandGrado'=>$arrLevelandGrado,
                    'gradoId'=>$gradoId,
                    'idiomasArray' => $idiomasArray
        ));
    }
    private function getObservationQA($data){
      // added to 2021 about qa
      $years = $data['gestion'].' ,'.$data['gestion'];

      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                                select vp.*
                                                from validacion_proceso vp
                                                where vp.institucion_educativa_id = '".$data['sie']."' and vp.gestion_tipo_id in (".$years.")
                                                and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
                                                and vp.es_activo = 'f'
                                            ");
          //
      $query->execute();
      $objobsQA = $query->fetchAll();

      return $objobsQA;
    }    

    public function getStudentsAction(Request $request) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
  //      dump($aInfoUeducativa);
//        die;
        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        //get the before level, ciclo and grado
        list($beforeNivel, $beforeCiclo, $beforeGrado) = $this->getBeforeCourse($nivel, $ciclo, $grado);

        //get the next level to do the inscription
        $objNextCursos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
            'institucioneducativa' => $sie,
            'gestionTipo' => $gestion
        ));

        $objParalelos = array();
        $aNewInscription = array();
        $objTurnos = array();
        $exist = true;
        //check if exists previuos info
        if ($objNextCursos) {
            //get students to inscription
            // $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
            $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion - 1, $beforeNivel, $beforeGrado, $paralelo, $turno);

            //get paralelos to data selected
            $objParalelos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
                'institucioneducativa' => $sie,
                'nivelTipo' => $nivel,
                'turnoTipo' => $turno,
                'gradoTipo' => $grado,
                'gestionTipo' => $gestion
            ));

            //get the turno for grado
            $objTurnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnoBySieInfo($sie, $nivel, $turno, $grado, $gestion);
            if ($objParalelos) {

                //get the student to do the inscription
                reset($objStudentsNewInscription);
                while ($val = current($objStudentsNewInscription)) {
                    //change the inscription to the new student
                    $studentInscription = $this->gettheInscriptionStudent($val['id'], $gestion);
                    //check if exist the inscription
                    if (!$studentInscription) {
                        $aNewInscription[] = $val;
                    }
                    next($objStudentsNewInscription);
                }
                //check if the data exist
                if (!$aNewInscription) {
                    $message = 'No existe estudiantes para inscribir...';
                    $this->addFlash('warningstudents', $message);
                    $exist = false;
                }
            } else {
                $exist = false;
                $message = 'No existe información de la siguiente curso ...';
                $this->addFlash('warningstudents', $message);
            }
        } else {
            $exist = false;
            $message = 'No existe información de la siguiente gestión ...';
            $this->addFlash('warningstudents', $message);
        }

        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'iecId' => $iecId, 'ciclo' => $ciclo, 'gestion' => $gestion, 'gradoname' => $gradoname, 'paraleloname' => $paraleloname));
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:students.html.twig', array(
                    'objStudents' => $aNewInscription,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    'objParalelos' => $objParalelos,
                    'objTurnos' => $objTurnos,
                    'paraleloSelected' => $paralelo,
                    'turnoSelected' => $turno,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist
        ));
    }

    public function inscriptionAction(Request $request) {

        // create the conexion to DB
        $em = $this->getDoctrine()->getManager();
//        echo "<pre>";
        //get the send data
        $dataInscription = $request->get('formdata');

        $dataStudents = $request->get('form');
        $dataInscription = unserialize($dataInscription['data']);

        reset($dataStudents);
        while ($valStudents = current($dataStudents)) {
            if (strcmp(key($dataStudents), '_token') !== 0) {
                if (isset($valStudents['student'])) {
                    //get the next level info
                    //$positionCurso = $this->getCourse($dataInscription['nivel'], $dataInscription['ciclo'], $dataInscription['grado'], $valStudents['matricula']);
                    //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);
                    // print_r($dataNextLevel);
                    //get next level
                    $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                        'institucioneducativa' => $dataInscription['sie'],
                        'nivelTipo' => $dataInscription['nivel'],
                        'cicloTipo' => $dataInscription['ciclo'],
                        'gradoTipo' => $dataInscription['grado'],
                        'paraleloTipo' => $valStudents['paralelo'],
                        'turnoTipo' => $dataInscription['turno'],
                        'gestionTipo' => $dataInscription['gestion']
                    ));

//                    echo $dataInscription['gestion'] . '<pre>';
//                    print_r($valStudents);
//                    print_r($objNextCurso->getId());
//                    echo '</pre>';

                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                    $query->execute();
                    $matriculas = array('5', '11');
                    if (in_array($valStudents['matricula'], $matriculas)) {

                        $studentInscription = new EstudianteInscripcion();
                        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataInscription['sie']));
                        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($dataInscription['gestion'] + 1));
                        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($valStudents['student']));
                        $studentInscription->setCodUeProcedenciaId($dataInscription['sie']);
                        $studentInscription->setObservacion(1);
                        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextCurso->getId()));
                        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
                        $studentInscription->setCodUeProcedenciaId(0);
                        $em->persist($studentInscription);
                        $em->flush();
                        //add the areas to the student
                        $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
                    }
                } else {

                }
            }
            next($dataStudents);
        }
        //die;
        //die;
        /* data: ({iecId: iecId, nivel: nivel, sie: sie, gestion: gestion, grado: grado, paralelo: paralelo, turno: turno, ciclo: ciclo}), */

        return $this->redirect($this->generateUrl('herramienta_info_estudiante_get_students', array(
                            'iecId' => $dataInscription['iecId'],
                            'nivel' => $dataInscription['nivel'],
                            'sie' => $dataInscription['sie'],
                            'gestion' => $dataInscription['gestion'],
                            'grado' => $dataInscription['grado'],
                            'paralelo' => $dataInscription['paralelo'],
                            'turno' => $dataInscription['turno'],
                            'ciclo' => $dataInscription['ciclo'],
                            'gradoname' => $dataInscription['gradoname'],
                            'paraleloname' => $dataInscription['paraleloname'],
                            'infoUe' => $request->get('form')['infoUe']
        )));
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
                $studentAsignatura->setFerchaLastUpdate(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
            }
        }
        return true;
    }

    private function getBeforeCourse($nivel, $ciclo, $grado) {

        reset($this->aCursos);
        $sw = true;
        while ($arrInfoCurso = current($this->aCursos) and $sw) {
            list ($level, $cicle, $grade) = (explode('-', $arrInfoCurso));
            if ($nivel == $level and $ciclo == $cicle and $grado == $grade) {
                $Key = key($this->aCursos);
                $sw = false;
            }

            next($this->aCursos);
        }
        $arrayCurso = ($Key > 0) ? explode('-', $this->aCursos[$Key - 1]) : array(11, 1, 1);
        return $arrayCurso;
    }

    public function inscriptionWithRudeAction(Request $request) {

        $dataInscription = $request->get('aData');
        $aDataInscription = unserialize($dataInscription);
        //print_r($aDataInscription);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:inscriptionWithRude.html.twig', array(
                    'form' => $this->formInscriptionWithRude($dataInscription)->createView(),
                    'exist' => true
        ));
    }

    private function formInscriptionWithRude($data) {
        return $this->createFormBuilder()
                        ->add('rude', 'text', array('label' => 'Rude', 'attr' => array('class' => 'form-control', 'placeholder' => 'Rude', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{13,18}')))
                        ->add('find', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'doRudeInscription()')))
                        ->add('dataInscription', 'hidden', array('data' => $data))
                        ->getForm();
    }

    public function rudeInscriptionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get data send
        $rude = $request->get('rude');
        $dataInscription = $request->get('dataInscription');
        $aDataInscription = unserialize($dataInscription);

        //print_r($aDataInscription);

        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

        $exist = true;
        if ($objStudent) {
            $objInscriptionStudent = $this->gettheInscriptionStudent($objStudent->getId(), $aDataInscription['gestion']);

            if ($objInscriptionStudent) {
                $exist = false;
                $message = 'Estudiante ya cuenta con inscripción...';
                $this->addFlash('warninrudeInscription', $message);
                //echo 'estudiante ya tiene inscripcion';
            }
        } else {
            $exist = false;
            $message = 'Estudiante no existe...';
            $this->addFlash('warninrudeInscription', $message);
        }
        //get the inscription on gestion - 1 and matricula = 5
        $objCurrentInscriptionMinus = $this->getCurrentInscriptionMinus($objStudent->getId(), $aDataInscription['gestion']-1);
        //dump($objCurrentInscriptionMinus);die;
        //die;
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:rudeinscription.html.twig', array(
                    'exist' => $exist,
                    'objStudent' => $objStudent ? $objStudent : array(),
                    'form' => $this->formRudeInscription($dataInscription, $rude, ($objStudent) ? $objStudent->getId() : '')->createView()
        ));
    }

    private function formRudeInscription($data, $rude, $idStudent) {
        return $this->createFormBuilder()
                        ->add('dataInscription', 'hidden', array('data' => $data))
                        ->add('dataStudent', 'hidden', array('data' => serialize(array('rude' => $rude, 'idStudent' => $idStudent))))
                        ->add('doInscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-success btn-block btn-xs', 'onclick' => 'exeInscription()')))
                        ->getForm();
    }
    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
    private function getCurrentInscriptionMinus($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo', 'IDENTITY(iec.nivelTipo) as nivelId, IDENTITY(iec.cicloTipo) as cicloId, IDENTITY(iec.gradoTipo) as gradoId' )
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '5')
                ->getQuery();

        $studentInscription = $query->getResult();
        return $studentInscription;
    }
    /**
     * todo the inscription - Student with rude
     * @param Request $request
     */
    public function exeInscriptionAction(Request $request) {
        //get the conexion DB
        $em = $this->getDoctrine()->getManager();
        //get the data send
        $dataInscription = $request->get('dataInscription');
        $dataStudent = $request->get('dataStudent');
        //convert the values
        $aDataInscription = unserialize($dataInscription);
        $aDataStudent = unserialize($dataStudent);

        //get the next level information
        $positionCurso = $this->getCourse($aDataInscription['nivel'], $aDataInscription['ciclo'], $aDataInscription['grado'], '5');
        $dataNextLevel = explode('-', $this->aCursos[$positionCurso]);


        // print_r($dataNextLevel);
        //get next level
        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $aDataInscription['sie'],
            'nivelTipo' => $dataNextLevel[0],
            'cicloTipo' => $dataNextLevel[1],
            'gradoTipo' => $dataNextLevel[2],
            'paraleloTipo' => $aDataInscription['paralelo'],
            'turnoTipo' => $aDataInscription['turno'],
            'gestionTipo' => $aDataInscription['gestion']
        ));
        $exist = true;
        if ($objNextCurso) {
            //insert the new inscription record
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($aDataInscription['sie']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($aDataInscription['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($aDataStudent['idStudent']));
            $studentInscription->setCodUeProcedenciaId($aDataInscription['sie']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextCurso->getId()));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
            $studentInscription->setCodUeProcedenciaId(0);
            //$em->persist($studentInscription);
            //$em->flush();
            //add the areas to the student
            //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
            $message = 'Inscripción realizada...';
            $this->addFlash('successExeInscription', $message);
        } else {
            $message = 'Inscripción no realizada. No se tiene información del nivel selecionado...';
            $exist = false;
            $this->addFlash('warningExeInscription', $message);
        }

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:exeInscription.html.twig', array(
                    'exist' => $exist,
        ));
    }

    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        //->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'hidden', array('data' => $data))
                        ->getForm();
    }

    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
    private function gettheInscriptionStudent($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        /*$inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '4')
                ->getQuery();

        $studentInscription = $query->getResult();
        */
        $queryInscription = "
           select ei.id as id, ei.estadomatricula_tipo_id as estadomatriculaTipo
           from estudiante_inscripcion ei
           left join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id=iec.id)
           where ei.estudiante_id = ".$id." and gestion_tipo_id::double precision = ".$gestion."
        ";
        $query = $em->getConnection()->prepare($queryInscription);
        $query->execute();
        $studentInscription = $query->fetchAll();        
        
        return $studentInscription;
    }

    public function seeStudentsAction(Request $request) {
        // dump('here'); die;
        //get the info ue
        $infoUe = $request->get('infoUe'); 
      //  dump($infoUe);die;
        $aInfoUeducativa = unserialize($infoUe);

        /*
            para seleccion de idioma
        */
        $idiomaId = $request->get('idiomaId');
        if (!isset($idiomaId)) {
            $idiomaId = 48; //castellano        
        }

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie']; 
        //$swRegisterCalifications = $aInfoUeducativa['requestUser']['swRegisterCalifications'];
        $swRegisterCalifications = true;
        // $swRegisterPersonBjp = $aInfoUeducativa['requestUser']['swRegisterPersonBjp'];
        $swRegisterPersonBjp = true;
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        $nivelname = $aInfoUeducativa['ueducativaInfo']['nivel'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();

        $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$sie,'gestion'=>$aInfoUeducativa['requestUser']['gestion']));
        $arrAllowInscription=array(1,2,3,4,5);
        $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
        if(in_array((sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100,$arrAllowInscription)){
          $this->session->set('allowInscription',true);
        }else{
          $this->session->set('allowInscription',false);
        }

        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
        
        /*if($operativo > 1){
            $this->session->set('donwloadLibreta', true);
        }else{
            $this->session->set('donwloadLibreta', false);
        }*/
        
        //dcastillo para notas
        if($operativo == 2){
            //los que aun no hah registrado 2 trmestre
            $this->session->set('donwloadLibreta', false);
        }
        if($operativo == 3){
            //los que ya cerraron operativo
            $this->session->set('donwloadLibreta', false);
        }


        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        // $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
        $posicionCurso = ($aInfoUeducativa['requestUser']['gestion'] > 2010) ? $this->getCourse($nivel, $ciclo, $grado, '5') : $this->getCourseOld($nivel, $ciclo, $grado, '5');
        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

        //get next level data
        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $sie,
            'nivelTipo' => $nivel,
            'cicloTipo' => $ciclo,
            'gradoTipo' => $grado,
            'paraleloTipo' => $paralelo,
            'turnoTipo' => $turno,
            'gestionTipo' => $gestion
        ));

        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
            //get students list

            $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));

        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);



        //to add the especialidad
        $UePlenasAddSpeciality=false;
        $arrUePlenasAddSpeciality = array(
          '81410160',
          '81410080',
          '40730250',
          '81410037',
          '81410134',
          '82220009',
          '80480060',
          '81981445',
          '80660080',
          '81340038',
          '81340065',
          '80730395',
          '80730391',
          '71170009',
          '60730046',
          '71170010',
          '81410157',

          '60900064',
          '81981463',
          '81480060',
          '80630028',
          '81470005',
          '81470069',
          '80980556',
          '80920034',
          '80980514',
          '71480114',
          '40730531',
          '82220001',
          '81170016',
          '80480163'
        );
        $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

        // Impresion de libretas
        $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

        $imprimirLibreta = false;
        $estadosPermitidosImprimir = array(4,5,11,55,28);

        if($gestion == 2020){
            $estadosPermitidosImprimir = array(5,55);
        }
        // dump($operativo);exit();

        if($tipoUE){
            /*
             *  GESTION ACTUAL
             */
            if($gestion == $this->session->get('currentyear')){
                // Unidades educativas plenas, modulares y humanisticas
                // if(in_array($tipoUE['id'], array(1,3,5,6,7)) and (($operativo >= 2 and $gestion < 2019) or ($gestion >= 2019 and $operativo >= 5))) {
                if(in_array($tipoUE['id'], array(1,3,5,6,7)) and $operativo > 3 ) { ///////>=
                    $imprimirLibreta = true;
                }
                // dump($imprimirLibreta);exit();
                // Unidades educativas tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $operativo >= 4){
                    $imprimirLibreta = true; 
                }
            }

            /*
             * GESTIONES PASADAS
             */
            if($gestion < $this->session->get('currentyear')){
                // Para unidades educativas en gestiones pasadas
                if(in_array($tipoUE['id'], array(1,2,3,4,5,6,7)) and $gestion > 2014 and $gestion < $this->session->get('currentyear') and $operativo >= 4){
                    $imprimirLibreta = true; 
                }
                // PAra ues tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $gestion >= 2011){
                    $imprimirLibreta = true; 
                }

                // // Caso especial de la unidad educativa AMERINST
                // YA SE TIENEN LAS MISMAS VALIDACIONES EN LA FUNCION DE IMPRESION DE LIBRETA
                // if($sie == '80730460' and $gestion <= 2015){
                //     $imprimirLibreta = false;
                //     if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                //         $imprimirLibreta = true;
                //     }
                //     if($gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                //         $imprimirLibreta = true;
                //     }
                //     if($gestion >= 2009 and $gestion <= 2013){
                //         $imprimirLibreta = true;
                //     }
                // }
            }
        }else{
            if($gestion > 2014 and $operativo >= 4 and $gestion < 2019){
                $imprimirLibreta = true; 
            }
        }
       // dump($imprimirLibreta);die;
      $aRemovesUeAllowed = array(
      '61710014',
      '61710089',
      '61710076',
      '61710068',
      '61710090',
      '61710042',
      '61710087',
      '61710084',
      '61710083',
      '61710085',
      '61710088',
      '61710063',
      '61710028',
      '61710086',
      '61710041',
      '61710043',
      '61710062',
      '61710031',
      '61710077',
      '61710021',
      '61710022',
      '61710036',
      '61710038',
      '61710091',
      '61710092',
      '61710093',
      '61710004',
      '60900064'
      );
    //   dump($operativo); die;
    $mostrarSextoCerrado = false;
    if($gestion >= 2018 and $operativo == 4 and $nivel == 13 and $grado == 6){
        $validacionSexto = $this->get('funciones')->verificarGradoCerrado($sie, $gestion);
        if(!$validacionSexto){
            $mostrarSextoCerrado = true;
        }
    }
    /**se habilito para validar que registren notas todas excepto de la lista UesSinRepoete2022 */
    $mostrarUeSinReporte2022 = false;
    $uESinReporte = $em->getRepository('SieAppWebBundle:UesSinReporte2022')->findOneById($sie);
    if($uESinReporte)
        $mostrarUeSinReporte2022 = true;

    $this->session->set('optionFormRude', false);
    $this->session->set('optionReporteRude', false);

    if($operativo)
    {
        $this->session->set('optionFormRude',true);
    }
    // if the rude operative was closed, so hidden the form rude option
    $registroConsolRude = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $sie, 'gestion' => $gestion, 'rude' => 1));
    if($registroConsolRude){
        $this->session->set('optionFormRude', false);
        $this->session->set('optionReporteRude', true);
    }

  $this->session->set('removeInscriptionAllowed', false);
  if(in_array($this->session->get('ie_id'),$aRemovesUeAllowed))
    $this->session->set('removeInscriptionAllowed',true);

        $uesWenayek = [61710087,61710043,61710089,61710083,61710063,61710028,61710014,61710093,61710031,61710068,61710091,61710076,61710021,61710084,61710092,61710038,61710085,61710004,61710086,61710041,61710062,61710077,61710042,61710090,61710036,61710088,61710022];
        $wenayekBono = in_array($sie, $uesWenayek)? true: false;

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $dependencia = $institucioneducativa->getDependenciaTipo()->getId(); // 3 privada


    // add validation to show califications option
    $showOptInscription = true;    
    //dpto no habilitados (st,lp,bn);
    // echo ">".date('d');exit();
    /*if ((date('d')=='30') or (date('d')=='01')) {
        $valor= array('7','2','8');
    }
    //dpto no habilitados (cbb,ch,tj,or,pt);
    if((date('d')=='02') or (date('d')=='03')){
        $valor= array('3','1','6','4','5');
        
    }
    if(date('d')>'02'){       
            $showOptInscription = false;
    }  
    if($this->get('funciones')->getuserAccessToCalifications($this->session->get('userId'),$valor)){
            $showOptInscription = false;
    }*/  
   
    $this->session->set('showOptInscription',$showOptInscription);
    //verificacion de estado de la UE en la gestion actual
    $estado = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->createQueryBuilder('op')
                ->select('op')               
                ->where('op.institucioneducativa='.$sie)
                ->andWhere("op.gestionTipoId=".$gestion)
                ->getQuery()
                ->getResult();
    if($estado){
        $this->session->set('estado',$estado[0]->getEstadoMenu()); 
    }else{
        $this->session->set('estado',''); 
    }   


        /* Operativo de sexto 2021 */
        $query = $em->getConnection()->prepare("select * from institucioneducativa_curso where institucioneducativa_id = " . $sie . " and gestion_tipo_id = " . $this->session->get('currentyear') . " and nivel_tipo_id = 13 and grado_tipo_id = 6");
        $query->execute();
        $objDataOperativo = $query->fetchAll();
        $haslevel = false;
        $hasgrado = false;
        if(sizeof($objDataOperativo)>0)
        {
            $haslevel = 13;
            $hasgrado = 6;
        }
        $closeopesextosecc = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestion);
        
        $arrLevelandGrado = array('haslevel'=> $haslevel, 'hasgrado' => $hasgrado, 'closeopesextosecc' => $closeopesextosecc, 'gestion' => $gestion, 'operativo' => $operativo);
        
        if ($closeopesextosecc == true and $nivel == 13 and $grado == 6){
            $this->session->set('donwloadLibreta', true);
        } 

        // to enable 1er Trim 
        $objUe1erTrin = $em->getRepository('SieAppWebBundle:TmpInstitucioneducativaApertura2021')->findOneBy(array('institucioneducativaId'=>$sie));
        if(sizeof($objUe1erTrin)>0){
            $this->session->set('unablePrimerTrim',true);
        }
//dump($operativo);die;
    // end add validation to show califications option
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'iecId'=>$iecId,
                    'sie' => $sie,
                    'swRegisterCalifications' => $swRegisterCalifications,
                    'swRegisterPersonBjp' => $swRegisterPersonBjp,
                    'turno' => $turno,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'paralelo' => $paralelo,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'infoUe' => $infoUe,
                    'exist' => $exist,
                    'itemsUe'=>$itemsUe,
                    'ciclo'=>$ciclo,
                    'operativo'=>$operativo,
                    'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
                    'imprimirLibreta'=>$imprimirLibreta,
                    'estadosPermitidosImprimir'=>$estadosPermitidosImprimir,
                    'mostrarSextoCerrado'=>$mostrarSextoCerrado,
                    'mostrarUeSinReporte2022'=>$mostrarUeSinReporte2022,
                    'sextoCerrado'=>$this->get('funciones')->verificarSextoSecundariaCerrado($sie, $gestion),
                    'wenakeyBono'=>$wenayekBono,
                    'dependencia'=>$dependencia,
                    'cerrarOperativoSexto' => $closeopesextosecc,
                    'nivelGradoSexto' => $arrLevelandGrado,
                    'idiomaId' =>$idiomaId
        ));
    }

    /**
     * obtiene el nivel, ciclo y grado
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return nivel, ciclo grado del estudiante
     */
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
        //get the array of courses
        $cursos = $this->aCursos;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        $ind = 0;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }

    private function getCourseOld($nivel, $ciclo, $grado, $matricula) {
      //get the array of courses
        $cursos = $this->aCursosOld;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        $ind = 0;
        while (( $acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }

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

    private function fillCursosOld() {
        $this->aCursosOld = array(
            ('1-2-1'),
            ('1-3-2'),
            ('2-1-1'),
            ('2-1-2'),
            ('2-1-3'),
            ('2-2-4'),
            ('2-2-5'),
            ('2-2-6'),
            ('2-3-7'),
            ('2-3-8'),
            ('3-1-1'),
            ('3-1-2'),
            ('3-3-3'),
            ('3-3-4')
        );
        return($this->aCursosOld);
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:open.html.twig', array());
    }

    /**
     * get the turnos UE
     * @param Request $request
     * @return array with turnos UE
     */
    public function getTurnosAction(Request $request) {
        //get the values
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $nivelname = $request->get('nivelname');
        $typeInscription = $request->get('typeInscription');

        $em = $this->getDoctrine()->getManager();
        //get turnos
        $objturnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnosBySieAndGestion($sie, $nivel, $gestion);

        $exist = true;
        //check if the data exist
        if (!$objturnos) {
            $message = 'Código SIE no existe';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:turnos.html.twig', array(
                    'objturnos' => $objturnos,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'nivelname' => $nivelname,
                    'typeInscription' => $typeInscription,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist
        ));
    }


  public function removeInscriptionAcaAction(Request $request) {

        //get the info ue
    $response = new JsonResponse();
    $infoUe = $request->get('infoUe');
    $aInfoUeducativa = unserialize($infoUe);

    //get the values throght the infoUe
    $sie = $aInfoUeducativa['requestUser']['sie'];
    $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
    $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
    $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
    $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
    $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
    $gestion = $aInfoUeducativa['requestUser']['gestion'];
    $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
    $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
    $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
    //get db connexion
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    //get data student
    $infoStudent = $request->get('infoStudent');

    $aInfoStudent = json_decode($infoStudent,true);
    $eiid = $aInfoStudent['eInsId'];

    //start removeInscription
    try {
      $objJuegos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcion' => $eiid));
      if ($objJuegos) {
          $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Juegos Plurinacionales";
          $this->addFlash('warningremoveins', $message);
          // return $this->redirectToRoute('remove_inscription_sie_index');
      }
      //get the student's inscription
      $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
      //get institucioneducativaCurso info
      $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
      $arrAllowAccessOption = array(7,8);

      //step 1 remove the inscription observado
      $objStudentInscriptionObservados = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findBy(array('estudianteInscripcion' => $eiid));
      //dump($objStudentInscriptionObservados);
      //die;
      if ($objStudentInscriptionObservados){
          foreach ($objStudentInscriptionObservados as $element) {
              $em->remove($element);
          }
          $em->flush();
          //$em->remove($objStudentInscriptionObservados);
          //$em->flush();
          $obs = $element->getObs();
      }
      else{
          $obs = '';
      }

//            step 2 delete nota
      $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $eiid, 'gestionTipo' => $gestion ));


      //step 3 delete asignatura
      foreach ($objEstAsig as $element) {
          $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $element));
          foreach($objNota as $element2)
          {
              $em->remove($element2);
          }
          $em->remove($element);
      }
      $em->flush();

      //dump($objEstAsig);die;
      $objNotaC = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $eiid));
      foreach ($objNotaC as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 4 delete socio economico data
      $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $eiid ));
      //dump($objSocioEco);die;
      foreach ($objSocioEco as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 5 delete apoderado_inscripcion data
      $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
      //dump($objApoIns);die;

      foreach ($objApoIns as $element) {
          $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
          foreach ($objApoInsDat as $element1){
              $em->remove($element1);
          }
          $em->remove($element);
      }
      $em->flush();

      //dump($objApoIns);die;
      //remove attached file
      $objStudentInscriptionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion'=>$eiid));
      if($objStudentInscriptionExtranjero){
        $em->remove($objStudentInscriptionExtranjero);
        $em->flush();
      }

     //paso 6 borrando apoderados
      $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
      foreach ($apoderados as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 6 copy data to control table and remove teh inscription
      $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);

      $studentInscription = new EstudianteInscripcionEliminados();
      $studentInscription->setEstudianteInscripcionId($objStudentInscription->getId());
      $studentInscription->setEstadomatriculaTipoId($objStudentInscription->getEstadoMatriculaTipo()->getId());
      $studentInscription->setEstudianteId($objStudentInscription->getEstudiante()->getId());
      $studentInscription->setNumMatricula($objStudentInscription->getNumMatricula());
      $studentInscription->setObservacionId($objStudentInscription->getObservacionId());
      $studentInscription->setObservacion($objStudentInscription->getObservacion());
      $studentInscription->setFechaInscripcion($objStudentInscription->getFechaInscripcion());
      $studentInscription->setApreciacionFinal($objStudentInscription->getApreciacionFinal());
      $studentInscription->setOperativoId($objStudentInscription->getOperativoId());
      $studentInscription->setFechaRegistro($objStudentInscription->getFechaRegistro());
      $studentInscription->setOrganizacion($objStudentInscription->getOrganizacion());
      $studentInscription->setFacilitadorpermanente($objStudentInscription->getFacilitadorpermanente());
      $studentInscription->setModalidadTipoId($objStudentInscription->getModalidadTipoId());
      $studentInscription->setAcreditacionnivelTipoId($objStudentInscription->getAcreditacionnivelTipoId());
      $studentInscription->setPermanenteprogramaTipoId($objStudentInscription->getPermanenteprogramaTipoId());
      $studentInscription->setFechaInicio($objStudentInscription->getFechaInicio());
      $studentInscription->setFechaFin($objStudentInscription->getFechaFin());
      $studentInscription->setCursonombre($objStudentInscription->getCursonombre());
      $studentInscription->setLugar($objStudentInscription->getLugar());
      $studentInscription->setLugarcurso($objStudentInscription->getLugarcurso());
      $studentInscription->setFacilitadorcurso($objStudentInscription->getFacilitadorcurso());
      $studentInscription->setFechainiciocurso($objStudentInscription->getFechainiciocurso());
      $studentInscription->setFechafincurso($objStudentInscription->getFechafincurso());
      $studentInscription->setCodUeProcedenciaId($objStudentInscription->getCodUeProcedenciaId());
      $studentInscription->setInstitucioneducativaCursoId($objStudentInscription->getInstitucioneducativaCurso()->getId());
      if(($objStudentInscription->getEstadomatriculaInicioTipo()))
        $studentInscription->setEstadomatriculaInicioTipoId($objStudentInscription->getEstadomatriculaInicioTipo()->getId());
      $studentInscription->setObsEliminacion($obs);
      $studentInscription->setUsuarioId($this->session->get('userId'));
      $studentInscription->setFechaEliminacion(new \DateTime('now'));
      $studentInscription->setDoc('false');
      $em->persist($studentInscription);
      $em->flush();

      $em->remove($objStudentInscription);
      $em->flush();

      // Try and commit the transaction
      $em->getConnection()->commit();
      $message = "Proceso realizado exitosamente.";
      $this->addFlash('successremoveins', $message);

      return $response->setData(array('status'=>'success','infoUe'=>$infoUe));
      // return $this->redirectToRoute('remove_inscription_sie_index');
    } catch (Exception $e) {
      $em->getConnection()->rollback();
      $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
      $this->addFlash('warningremoveins', $message);
      return $response->setData(array('status'=>'error', 'msg'=>'error'));
    }

    //end removeInscription



    $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$sie,'gestion'=>$aInfoUeducativa['requestUser']['gestion']));
    $arrAllowInscription=array(1,2,3,4,5);
    $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
    if(in_array((sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100,$arrAllowInscription)){
      $this->session->set('allowInscription',true);
    }else{
      $this->session->set('allowInscription',false);
    }
    //get turnos
    //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
    //get th position of next level

    // $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
    $posicionCurso = ($aInfoUeducativa['requestUser']['gestion'] > 2010) ? $this->getCourse($nivel, $ciclo, $grado, '5') : $this->getCourseOld($nivel, $ciclo, $grado, '5');
    //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

    //get next level data
    $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
        'institucioneducativa' => $sie,
        'nivelTipo' => $nivel,
        'cicloTipo' => $ciclo,
        'gradoTipo' => $grado,
        'paraleloTipo' => $paralelo,
        'turnoTipo' => $turno,
        'gestionTipo' => $gestion
    ));

    $exist = true;
    $objStudents = array();
    $aData = array();
    //check if the data exist
    if ($objNextCurso) {
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
        //get students list
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
    } else {
        $message = 'No existen estudiantes inscritos...';
        $this->addFlash('warninsueall', $message);
        $exist = false;
    }

    // Para el centralizador
    $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

    $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);



    //to add the especialidad
    $UePlenasAddSpeciality=false;
    $arrUePlenasAddSpeciality = array(
      '81410160',
      '81410080',
      '40730250',
      '81410037',
      '81410134',
      '82220009',
      '80480060',
      '81981445',
      '80660080',
      '81340038',
      '81340065',
      '80730395',
      '80730391',
      '71170009',
      '60730046',
      '71170010',
      '81410157',

      '60900064',
      '81981463',
      '81480060',
      '80630028',
      '81470005',
      '81470069',
      '80980556',
      '80920034',
      '80980514',
      '71480114',
      '40730531',
      '82220001',
      '81170016',
      '80480163'
    );
    $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

    // Impresion de libretas
    $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);
    $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

    $imprimirLibreta = false;
    $estadosPermitidosImprimir = array(4,5,11,55);

    if($tipoUE){
        /*
         *  GESTION ACTUAL
         */
        if($gestion == $this->session->get('currentyear')){
            // Unidades educativas plenas, modulares y humanisticas
            if(in_array($tipoUE['id'], array(1,3,5)) and $operativo >= 2){
                $imprimirLibreta = true;
            }
            // Unidades educativas tecnicas tecnologicas
            if(in_array($tipoUE['id'], array(2)) and $operativo >= 4){
                $imprimirLibreta = true;
            }
        }


        /*
         * GESTIONES PASADAS
         */
        if($gestion < $this->session->get('currentyear')){
            // Para unidades educativas en gestiones pasadas
            if(in_array($tipoUE['id'], array(1,2,3,5)) and $gestion >= 2014 and $gestion < $this->session->get('currentyear') and $operativo >= 4){
                $imprimirLibreta = true;
            }

            // PAra ues tecnicas tecnologicas
            if(in_array($tipoUE['id'], array(2)) and $gestion >= 2011){
                $imprimirLibreta = true;
            }

            // Caso especial de la unidad educativa AMERINST
            if($sie == '80730460' and $gestion <= 2015){
                $imprimirLibreta = false;
                if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                    $imprimirLibreta = true;
                }
                if($gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                    $imprimirLibreta = true;
                }
            }
        }
    }

    return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'sie' => $sie,
                'turno' => $turno,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'gestion' => $gestion,
                'aData' => $aData,
                'gradoname' => $gradoname,
                'paraleloname' => $paraleloname,
                // 'nivelname' => $nivelname,
                'form' => $this->createFormStudentInscription($infoUe)->createView(),
                'infoUe' => $infoUe,
                'exist' => $exist,
                'itemsUe'=>$itemsUe,
                'ciclo'=>$ciclo,
                'operativo'=>$operativo,
                'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
                'imprimirLibreta'=>$imprimirLibreta,
                'estadosPermitidosImprimir'=>$estadosPermitidosImprimir
    ));
      }

    public function cerrarRudeAction(Request $request){
        //get the values
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');

        //conexion to DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //look for the record on the DB to this sie and gestion
        $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
          'institucioneducativa' => $sie,
          'gestionTipoId'  => $gestion,
          'institucioneducativaOperativoLogTipo' => 4
        ));
        // dump($objinstitucioneducativaOperativoLogExist);

        if(!$objinstitucioneducativaOperativoLogExist){

          $query = $em->getConnection()->prepare('select * from sp_validacion_regular_socioeconomicos(:gestion, :sie)');
          $query->bindValue(':gestion', $gestion);
          $query->bindValue(':sie', $sie);
          $query->execute();
          $inconsistenciaRude = $query->fetchAll();
          if(!$inconsistenciaRude){

            //get values
          $form = array(
            'sie'=>$sie,
            'gestion' => $gestion,
            'bimestre' => 1
          );
          //save in donwload File Control
          //$objinstitucioneducativaOperativoLog = $this->saveInstitucioneducativaOperativoLog($form);
           try {
            //save the log data
            $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
            $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(4));
            $institucioneducativaOperativoLog->setGestionTipoId($form['gestion']);
            $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($form['bimestre']+1));
            $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']));
            $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
            $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($form['bimestre']));
            $institucioneducativaOperativoLog->setDescripcion('...');
            $institucioneducativaOperativoLog->setEsexitoso('t');
            $institucioneducativaOperativoLog->setEsonline('t');
            $institucioneducativaOperativoLog->setUsuario($this->session->get('userId'));
            $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
            $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
            $em->persist($institucioneducativaOperativoLog);
            $em->flush();
            $em->getConnection()->commit();
            // dump($institucioneducativaOperativoLog);
            // return 'krlos';
            return new Response('Datos guardados correctamente... ');
           } catch (Exception $e) {
             $em->getConnection()->rollback();
             //   echo 'Excepción capturada: ', $ex->getMessage(), "\n";
           }
         }else{
           //see inconsistenciaRude
           return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:inconsistenciaRude.html.twig', array(
                       'inconsistenciaRude' => $inconsistenciaRude,
           ));
          }

          //dump($objinstitucioneducativaOperativoLog);
        }else{
          // return new Response('Proceso anteriormente realizado... ');
          $response = new Response('Proceso anteriormente realizado... '.$sie, Response::HTTP_OK);

          return $response;
          //die;
        }
        //die('...');

    }


    public function cerrarSextoGradoAction(Request $request){
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $em = $this->getDoctrine()->getManager();

        $registroConsolidacion = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
            'unidadEducativa'=>$sie,
            'gestion'=>$gestion
        ));

        if($registroConsolidacion){
            $registroConsolidacion->setCierreSextosec(true);
            $em->flush();
        }

        // Llamar funcion para cerrar sexto grado
            
        return new Response('Curso cerrado con exito!!');

    }

    public function getStudentsBthAction(Request $request){
        // get the send values
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        //view the template response
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:getStudentsBth.html.twig', array(
            'infoUe' => $infoUe,
            'iecId'  => $aInfoUeducativa['ueducativaInfoId']['iecId']
        ));

    }

    private function getBthStudents($iecId, $optionCallFunction){

        $em = $this->getDoctrine()->getManager();
        switch ($optionCallFunction) {
            case 1:
                // get studen
                $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseBTH($iecId);
                break;
            case 2:
                // get studen
                $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseOnlyBTH($iecId);
                break;            
            default:
                # code...
                break;
        }
        
        //get the students has BTH
        $arrStudents = array();
        $arrStudentsStatus = array(4,5,11,28);
        if($objStudents){
            foreach ($objStudents as $value) {
                if( in_array($value['estadomatriculaId'], $arrStudentsStatus)){
                    $arrStudents[] = array(
                        
                        'studentId'=>$value['id'],
                        'carnetIdentidad'=>$value['carnetIdentidad'],
                        'complemento'=>$value['complemento'],
                        'codigoRude'=>$value['codigoRude'],
                        'paterno'=>$value['paterno'],
                        'materno'=>$value['materno'],
                        'nombre'=>$value['nombre'],
                        'estadomatricula'=>$value['estadomatricula'],
                        'estadomatriculaId'=>$value['estadomatriculaId'],
                        'eInsId'=>$value['eInsId'],
                        'studentId'=>$value['id'],
                        'specialty'=>$value['especialidad'],
                        'studentSpecialtyId'=>$value['ethtId'],
                        'studentSpecialtyIdNew'=>($value['ethtId']!=null)?$value['ethtId']:false,
                        'deletebthOption'=>false,
                        'updatebthOption'=>false,
                        'mainoption'=>true,
                        'justificativo'=>'',
                        
                    );
                }
            }
        }

        return $arrStudents;
    }
    private function getSpeciality(){

        $arrdata = array('currentSie'=>$this->session->get('ie_id'),'currentGestion'=>$this->session->get('currentyear'));
        $objSpeciality = $this->get('funciones')->getSpeciality($arrdata);
        $arrSpeciality = array();
        if($objSpeciality){
            foreach ($objSpeciality as $value) {
                $arrSpeciality[]=array('specialtyId'=>$value->getId(), 'specialty'=>$value->getEspecialidad());
            }
        }
        
        return($arrSpeciality);        
    }
    
    /**
    *to do the add and/or update the speciality
    *parameters: var iec id
    *return json data(studnets, specialityies)
    **/
    public function addupdateStudentbthAction(Request $request){
               
        //get the send values
        $iecId = $request->get('iecId');
        // create var to send the next values
        $response = new JsonResponse();

        // check if the Ue has finished the all operativo
        $operativo = $this->get('funciones')->obtenerOperativo($this->session->get('ie_id'), $this->session->get('currentyear'));
        //set the values to continue the process
        $arrStudents   = array();
        $arrSpeciality = array();
        $operativoUeEnd = false;

        if($operativo!=5){
            // get students bth
            $arrStudents = $this->getBthStudents($iecId,1);
            // get specialities             
            $arrSpeciality = $this->getSpeciality();
        }else{
            $operativoUeEnd = true;
        }
        $arrdata = array('currentSie'=>$this->session->get('ie_id'),'currentGestion'=>$this->session->get('currentyear'));
        
        //return values
        $response->setStatusCode(200);
        $response->setData(array(
            'students'       => $arrStudents,
            'swaddupdate'    => true,
            'DBspeciality'   => $arrSpeciality,
            'operativoUeEnd' => $operativoUeEnd
        ));
        return $response;   
    }

    /**
    *to do the removing the speciality
    *parameters: var iec id
    *return json data(studnets, specialityies)
    **/
    public function removeStudentBthAction(Request $request){
        //get the send values
        $iecId = $request->get('iecId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // create var to send the next values
        $response = new JsonResponse();
        // get students bth        
        $arrStudents = $this->getBthStudents($iecId,2);
        // $arrStudents = $this->getRemoveAsigBthStudents($iecId,1);
        $arrStudentsWithoutBTH = $this->getRemoveAsigBthStudents($iecId,3);
        
        // get specialities 
        $arrdata = array('currentSie'=>$this->session->get('ie_id'),'currentGestion'=>$this->session->get('currentyear'));
        $arrSpeciality = $this->getSpeciality();
        
        //return values
        $response->setStatusCode(200);
        $response->setData(array(
            'studentsBTH'     => $arrStudents,
            'studentswithoutBTH'     => $arrStudentsWithoutBTH,
            'swremove'  => true,
            'DBspeciality' => $arrSpeciality
        ));
        return $response;   
    }

        private function getRemoveAsigBthStudents($iecId,$operativoType){

        $em = $this->getDoctrine()->getManager();

        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListRemoveAsigBthStudents($iecId,$operativoType);
        //get the students has BTH
        $arrStudents = array();
        if($objStudents){
            foreach ($objStudents as $value) {
                $arrStudents[] = array(
                    
                    'studentId'=>$value['id'],
                    'carnetIdentidad'=>$value['carnetIdentidad'],
                    'complemento'=>$value['complemento'],
                    'codigoRude'=>$value['codigoRude'],
                    'paterno'=>$value['paterno'],
                    'materno'=>$value['materno'],
                    'nombre'=>$value['nombre'],
                    'estadomatricula'=>$value['estadomatricula'],
                    'estadomatriculaId'=>$value['estadomatriculaId'],
                    'eInsId'=>$value['eInsId'],
                    'studentId'=>$value['id'],
                    
                );
            }
        }

        return $arrStudents;
    }    





    public function addupStudentbthAction(Request $request){
        // get the send values
        $eInsId = $request->get('eInsId');
        $specialityId = $request->get('specialityId');
        $iecId = $request->get('iecId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        // create var to send the next values
        $response = new JsonResponse();
        try {
            
            // look for the speciality by student
            $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$eInsId));
            // check if the student has speciality to do the save or update        
            $operativoSpeciality = 1;
            $arrEstudianteInscripcionHumnisticoTecnico = array();
            if(!$especialidadEstudiante){    
                $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
                $operativoSpeciality = 2;
            }else{
                //get the EstudianteInscripcionHumnisticoTecnico
                $arrEstudianteInscripcionHumnisticoTecnico = array(
                    'horas'=>$especialidadEstudiante->getHoras(),
                    'InstitucioneducativaHumanisticoTecnicoTipo'=>$especialidadEstudiante->getInstitucioneducativaHumanisticoId(),
                    'EspecialidadTecnicoHumanisticoTipo'=>$especialidadEstudiante->getEspecialidadTecnicoHumanisticoTipo()->getId(),
                );
            }
            
            if($specialityId != $especialidadEstudiante->getEspecialidadTecnicoHumanisticoTipo()->getId()){    

                // set tthe add or update
                $arrCondition = array('institucioneducativa'=>$this->session->get('ie_id'), 'gestionTipo'=>$this->session->get('currentyear'), 'especialidadTecnicoHumanisticoTipo'=>$specialityId);
                    $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy($arrCondition);
                $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
                $especialidadEstudiante->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId));
                $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($specialityId));
                $especialidadEstudiante->setHoras(0);
                $em->persist($especialidadEstudiante);

                  //set the backup info
                $objBthEstudianteInscripcionGestionEspecialidad = new BthEstudianteInscripcionGestionEspecialidad();
                $objBthEstudianteInscripcionGestionEspecialidad->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId));
                $objBthEstudianteInscripcionGestionEspecialidad->setOperativoGestionEspecialidadTipo($em->getRepository('SieAppWebBundle:OperativoGestionEspecialidadTipo')->find($operativoSpeciality));
                $objBthEstudianteInscripcionGestionEspecialidad->setData(json_encode($arrEstudianteInscripcionHumnisticoTecnico));
                $objBthEstudianteInscripcionGestionEspecialidad->setFechaRegistro(new \DateTime('now'));
                $objBthEstudianteInscripcionGestionEspecialidad->setUsuarioId($this->session->get('userId'));
                $em->persist($objBthEstudianteInscripcionGestionEspecialidad);


                $em->flush();
            }

            // get the set data on students
            $arrStudents = $this->getBthStudents($iecId,1);
            $response->setStatusCode(200);
            $response->setData(array(
                'students'     => $arrStudents,
                'status'     => 'goog',
            ));
            return $response;   

        } catch (Exception $e) {
            
        }

    }

    public function doRemoveStudentBthAction(Request $request){
        
        // // get the send values
        $jsonData = $request->get('datos');
        $arrData = json_decode($jsonData,true);

        $eInsId = $arrData['eInsId'];
        $specialityId = $arrData['studentSpecialtyId'];
        $justificativo = $arrData['justificativo'];
        $iecId = $request->get('iecId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // create var to send the next values
        $response = new JsonResponse();

        // set the backup info after to remove it
        // look for the speciality by student
        $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$eInsId));

        // check if the student has speciality to do the save or update        
        $operativoSpeciality = 3;
        $arrEstudianteInscripcionHumnisticoTecnico = array();
        if($especialidadEstudiante){     
            //get the EstudianteInscripcionHumnisticoTecnico
            $arrEstudianteInscripcionHumnisticoTecnico = array(
                'horas'=>$especialidadEstudiante->getHoras(),
                'InstitucioneducativaHumanisticoTecnicoTipo'=>$especialidadEstudiante->getInstitucioneducativaHumanisticoId(),
                'EspecialidadTecnicoHumanisticoTipo'=>$especialidadEstudiante->getEspecialidadTecnicoHumanisticoTipo()->getId(),
            );
        }

        //get the info to do the remove about the student to remove
        $arrInfoStudent = $this->getInfoStudent($eInsId);
        if(sizeof($arrInfoStudent)>0){
            foreach ($arrInfoStudent as $value) {                
                // // remove the nota
                $objStudentNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($value['enotaId']);
                $arrEstudianteInscripcionHumnisticoTecnico['nota'][]= array('usuario'=> $objStudentNota->getUsuarioId(), 'notaTipo'=>$objStudentNota->getNotaTipo()->getId(), 'nota'=>$objStudentNota->getNotaCuantitativa());
                $em->remove($objStudentNota);            
                // // set the materia        
                $arrEstudianteInscripcionHumnisticoTecnico['estudianteAsignatura']= $value['easigId'];
                
            }
            // remove the materia 
            if($arrEstudianteInscripcionHumnisticoTecnico['estudianteAsignatura']>0){
                $objStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($arrEstudianteInscripcionHumnisticoTecnico['estudianteAsignatura']);
                $em->remove($objStudentAsignatura);                
            }
        }

        // check if the file exists
        if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = date('YmdHis').'.'.$extension;

                $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId);

                // GUARDAMOS EL ARCHIVO
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/bthGestionStudentSpeciality/' .date('Y');
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/bthGestionStudentSpeciality/' .date('Y').'/'.$objStudentInscription->getEstudiante()->getId();
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }

                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/bthGestionStudentSpeciality/' .date('Y').'/'.$objStudentInscription->getEstudiante()->getId().'/'.$eInsId;
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }

                // CREAMOS LOS DATOS DE LA IMAGEN
                $informe = array(
                    'name' => $name,
                    'type' => $type,
                    'tmp_name' => 'nueva_ruta',
                    'size' => $size,
                    'new_name' => $new_name
                );
            }else{
                $informe = null;
                $archivador = 'empty';
            }

        //set the backup info
        $objBthEstudianteInscripcionGestionEspecialidad = new BthEstudianteInscripcionGestionEspecialidad();
        $objBthEstudianteInscripcionGestionEspecialidad->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId));
        $objBthEstudianteInscripcionGestionEspecialidad->setOperativoGestionEspecialidadTipo($em->getRepository('SieAppWebBundle:OperativoGestionEspecialidadTipo')->find($operativoSpeciality));
        $objBthEstudianteInscripcionGestionEspecialidad->setData(json_encode($arrEstudianteInscripcionHumnisticoTecnico));
        $objBthEstudianteInscripcionGestionEspecialidad->setFechaRegistro(new \DateTime('now'));
        $objBthEstudianteInscripcionGestionEspecialidad->setUsuarioId($this->session->get('userId'));
        $objBthEstudianteInscripcionGestionEspecialidad->setJustificativo($justificativo);
        $objBthEstudianteInscripcionGestionEspecialidad->setRutaArchivo($archivador);
        $em->persist($objBthEstudianteInscripcionGestionEspecialidad);

        // remove the speciality
        $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$eInsId));
        $em->remove($especialidadEstudiante);
        $em->flush();


        $arrStudents = $this->getBthStudents($iecId,1);


        $response->setStatusCode(200);
        $response->setData(array(
            'students'     => $arrStudents,
            'status'     => 'goog',
        ));
        return $response;   
    }
    
        // select eno.id as enotaId, ieco.id as iecoId, easig.id as easigId
        // from estudiante_inscripcion ei
        // left join estudiante_asignatura easig on (ei.id  = easig.estudiante_inscripcion_id)
        // left join institucioneducativa_curso_oferta ieco on (easig.institucioneducativa_curso_oferta_id = ieco.id)
        // left join estudiante_nota eno on (easig.id = eno.estudiante_asignatura_id)
        // where ei.id = 458269781 and ieco.asignatura_tipo_id = 1037
        /**
     * to get the info about the notas and asignaura
     * @param type array data
     * @return type - array data ids nota and asignatura
     */
        //IDENTITY(eno.id) as enotaId,IDENTITY(ieco.id) as iecoId,IDENTITY(easig.id) as easigId
    private function getInfoStudent($eInsId) {
        $em = $this->getDoctrine()->getManager();
        $inscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription->createQueryBuilder('ei')
                ->select('eno.id as enotaId, ieco.id as iecoId, easig.id as easigId')
                ->leftjoin('SieAppWebBundle:EstudianteAsignatura', 'easig', 'WITH', 'ei.id  = easig.estudianteInscripcion')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'easig.institucioneducativaCursoOferta = ieco.id')
                ->leftjoin('SieAppWebBundle:EstudianteNota', 'eno', 'WITH', 'easig.id = eno.estudianteAsignatura')
                ->where('ei.id = :id')
                ->andwhere('ieco.asignaturaTipo  = :asigTipo')
                ->setParameter('id', $eInsId)
                ->setParameter('asigTipo', 1039)
                ->getQuery();

        $studentInscription = $query->getResult();

        return $studentInscription;
    }

    public function operativoEspecialidadesBthAction ( Request $request){ 
        $em = $this->getDoctrine()->getManager();
        try {
            $bthOperativo=new bthControlOperativoModificacionEspecialidades();
            $bthOperativo->setInstitucioneducativaId($request->get('sie'));
            $bthOperativo->setGestionTipoId($request->get('gestion'));
            $bthOperativo->setInstitucioneducativaOperativoLogTipo ($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(9));
            $bthOperativo->setEstadoOperativo(true);
            $bthOperativo->setFechaCierre(new \DateTime('now'));
           
            $em->persist($bthOperativo);
            $em->flush();
            $res = 1;
            $msg = "Se cerro el operativo correctamente";
            return  new JsonResponse(array('estado' => $res, 'msg' => $msg));
        }catch (Exception $ex) {
            //$em->getConnection()->rollback();
            $res = 0;
            $msg = "Error al cerrar el Operativo";
            return  new JsonResponse(array('estado' => $res, 'msg' => $msg));
        }

    }

    public function closeOperativoSextoSeccAction(Request $request){
        
        $response = new JsonResponse();
        // get the send values
        $sie     = $request->get('sie');
        $gestion = $request->get('gestion');
        $bimestre = 8;
        $level = 13;
        $grado = 6;

        $em = $this->getDoctrine()->getManager();

        try {
            // check if the UE has observation in level 13 and grado 6
            $query = $em->getConnection()->prepare("select * from sp_validacion_regular_web_gen('" . $gestion . "','" . $sie . "','" . $bimestre . "','" . $level . "','" . $grado . "');");
           // $query = $em->getConnection()->prepare("select * from sp_validacion_regular_web2022_fg('" . $gestion . "','" . $sie . "','" . $bimestre . "');");
            $query->execute();
            $responseOpe = $query->fetchAll();//function db
            $arrResponse = array();
            // chek if the validation has error
            if(sizeof($responseOpe)>0){
            //if(false){ // ya no validamos las observaciones, preguntar
            
                // error; send the errors to show on the view
                $swObservations = true;
                $arrResponse = $responseOpe;                
            }else{
                $swObservations = false;
                // no error save the success validation
                $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
                $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(10));
                $institucioneducativaOperativoLog->setGestionTipoId($gestion);
                $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
                $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
                $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($this->session->get('lastOperativo')));
                $institucioneducativaOperativoLog->setDescripcion('...');
                $institucioneducativaOperativoLog->setEsexitoso('t');
                $institucioneducativaOperativoLog->setEsonline('t');
                $institucioneducativaOperativoLog->setUsuario($this->session->get('userId'));
                $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
                $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
                $em->persist($institucioneducativaOperativoLog);
                $em->flush();

                // Procesa CUT_TTM BTH
                $query = $em->getConnection()->prepare("select * from sp_bth_institucioneducativa_cutttm ('" . $sie . "','" . $gestion . "','" . $this->session->get('userId') . "');");
                $query->execute();

                return $response->setData(array('reloadIt'=>true, 'mssg'=>'Se verifico el operativo Sexto de Secundaria sin problemas'));

            }
            
          
            $msg = "Se cerro el operativo correctamente";
        }catch (Exception $ex) {
            //$em->getConnection()->rollback();
            $res = 0;
            $msg = "Error al cerrar el Operativo";
        }

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:closeOperativoSextoSecc.html.twig', array(
                'arrResponse' => $arrResponse,
                'swObservations' => $swObservations,
        ));
    }

    public function validarEstudianteSegipAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $codigoRude = $request->get('codigoRude');
        $persona = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $codigoRude));
        $estado = false;
        $mensaje = "";
        $cedulaTipo = "";

        if ($persona->getCedulaTipo() === null){
            $cedulaTipo = 1;
        }else{
            $cedulaTipo = $persona->getCedulaTipo()->getId();
        }
        
        if($persona){
            $datos = array(
                'complemento'=>$persona->getComplemento(),
                'primer_apellido'=>$persona->getPaterno(),
                'segundo_apellido'=>$persona->getMaterno(),
                'nombre'=>$persona->getNombre(),
                'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y'),
                'tipo_persona' => $cedulaTipo
            );
            
            if($persona->getCarnetIdentidad()){
                $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($persona->getCarnetIdentidad(),$datos,'prod','academico');
                
                if($resultadoPersona){
                    $persona->setSegipId(1);
                    $persona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipo));
                    $em->persist($persona);
                    $em->flush();
                    $mensaje = "Válido SEGIP";
                    $estado = true;
                } else {
                    $mensaje = "No se realizó la validación con SEGIP. Debe actualizar la información a través del módulo: Modificación de Datos.";
                }                
            } else {
                $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de la/el estudiante.";
            }
        } else {
            $mensaje = "No se realizó la validación con SEGIP. No existe información de la/el estudiante.";
        }

        return new JsonResponse(array('estado' => $estado, 'mensaje' => $mensaje));
    }
    public function visualizarContenidoAction(Request $request) {

        // $response = new JsonResponse();
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $infoStudent = $request->get('infoStudent');

        $aInfoStudent = json_decode($infoStudent,true);

        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $idest = $aInfoStudent['id'];
     
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT
            ei.observacion,
            iec.gestion_tipo_id,
            ie.institucioneducativa,
            ie.id
            FROM  estudiante_inscripcion AS ei
            INNER JOIN institucioneducativa_curso AS iec ON iec.id = ei.institucioneducativa_curso_id
            INNER JOIN institucioneducativa AS ie ON ie.id = iec.institucioneducativa_id
            INNER JOIN gestion_tipo gt ON gt.id=iec.gestion_tipo_id
            WHERE ei.estudiante_id = '$idest' AND gt.gestion = '$gestion' AND (ei.cod_ue_procedencia_id IS NOT null)
            ORDER BY ei.id DESC LIMIT 1 ");
        $query->execute();
        $valor= $query->fetch();
        return $this->render($this->session->get('pathSystem').':InfoEstudiante:descripcionTrasladoUEst.html.twig',array('valor'=>$valor));

    }
}
