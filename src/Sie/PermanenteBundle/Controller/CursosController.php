<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
/**
 * EstudianteInscripcion controller.
 *
 */
class CursosController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
//dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $uEducativa['ciclo'], 'nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelId'], 'cicloId' => $uEducativa['cicloId'], 'gradoId' => $uEducativa['gradoId'], 'turnoId' => $uEducativa['turnoId'], 'paraleloId' => $uEducativa['paraleloId'], 'iecId' => $uEducativa['iecId'], 'setCodigo'=>$uEducativa['setCodigo'], 'satCodigo'=>$uEducativa['satCodigo'])
                ));

                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['ciclo']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'nivelId' => $uEducativa['nivelId']);

            }
        } else {
            $message = 'No existe información del Centro de Educación Alternativa para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
//        dump($aInfoUnidadEductiva);die;
//        dump($objUeducativa);
//        die;
        return $this->render($this->session->get('pathSystem') . ':Cursos:index.html.twig', array(
                    'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                    'exist' => $exist,
        ));
    }

    public function seeStudentsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $exist = true;
        $objStudents = array();
        $dataUe=(unserialize($infoUe));
//        dump($dataUe); die;
        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
//        dump($aInfoUeducativa['ueducativaInfoId']['iecId']);
//        die;
        $dupcursover = $this->verificarcursoduplicado($aInfoUeducativa, $aInfoUeducativa['ueducativaInfoId']['iecId']);
        if ($dupcursover != '-1'){
            $message = '¡Se ha detectado inconsistencia de datos! Existe un curso duplicado para este Nivel/Especialidad, Etapa/Grado, debe corregir este problema lo antes posivle, consulte con su técnico distrital.';
            $this->addFlash('errorcursoduplicado', $message);
            $this->addFlash('idcursodup', $dupcursover);
            //dump($dupcursover); die;
        }
        

        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] != '15'){
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }
        else{
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }

        //dump($objStudents); die;

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist,
                    'existins' => $existins,
                    'infoUe' => $infoUe,
                    'etapaespecialidad' => $etapaespecialidad,
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'totalInscritos'=>count($objStudents)
        ));
    }

    public function verificarcursoduplicado($aInfoUeducativa, $idcurso) {
        //dump($aInfoUeducativa);die;
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = " select h.id as idcurso, f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id, stt.turno_superior, a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion
                    ,pt.id as paralelo_id, pt.paralelo
                    --,j.codigo_rude, j.fecha_nacimiento,date_part('year',age(j.fecha_nacimiento)) as edad ,j.genero_tipo_id
                    from superior_facultad_area_tipo a  
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                inner join	superior_turno_tipo stt on stt.id = e.superior_turno_tipo_id
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id			
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id 
                                    inner join paralelo_tipo pt on pt.id = h.paralelo_tipo_id
                                        --inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id 
                                            --inner join estudiante j on i.estudiante_id=j.id
                                                                        inner join superior_turno_tipo z on h.turno_tipo_id=z.id 
                                                inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                                                    inner join turno_tipo q on h.turno_tipo_id=q.id
                    ------
                    where f.gestion_tipo_id in (".$this->session->get('ie_gestion').") and f.periodo_tipo_id in ('".$this->session->get('ie_per_cod')."')
                    and h.institucioneducativa_id = '".$this->session->get('ie_id')."'
                    and a.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['nivelId']."
                    and b.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['cicloId']."
                    and d.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['gradoId']."
                    and stt.turno_superior = '".$aInfoUeducativa['ueducativaInfo']['turno']."'
                    and pt.id = '".$aInfoUeducativa['ueducativaInfoId']['paraleloId']."'
                    and f.sucursal_tipo_id = ".$this->session->get('ie_subcea')."
                    ------
                    group by h.id, f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,stt.turno_superior,a.codigo, a.facultad_area,b.codigo,b.especialidad,d.codigo,d.acreditacion,pt.id,pt.paralelo";
//        print_r($query); die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //dump($po); die;     
        if (count($po) == 0){
            return '-1';
        }
        if (count($po) == 1){
            return '-1';
        }
        if (count($po) > 1){
            $idcur = '0';
            foreach($po as $reg){
                if ( $reg['idcurso'] != $idcurso)
                    $idcur = $reg['idcurso'];
            }
            return $idcur;
        }

    }


    public function cursoduplicadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $idcursodup = $request->get('idcursodup');
        
        $aInfoUeducativa = unserialize($infoUe);
        $exist = true;
        $objStudents = array();
        $dataUe=(unserialize($infoUe));
        
        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($idcursodup);
        //dump($objStudents);die;437588441

        
        $message = '¡Se ha detectado inconsistencia de datos! Este curso esta duplicado, ocacionando distintos tipo de problemas. Debe eliminar alguno de los cursos.';
        $this->addFlash('seedatoscursoduplicado', $message);
        //dump($dupcursover); die;

        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] != '15'){
            $etapaespecialidad = 'DUPLICADO :'.$aInfoUeducativa['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }
        else{
            $etapaespecialidad = 'DUPLICADO :'.$aInfoUeducativa['ueducativaInfo']['grado'];
        }

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist,
                    'existins' => $existins,
                    'infoUe' => $infoUe,
                    'etapaespecialidad' => $etapaespecialidad,
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'totalInscritos'=>count($objStudents)
        ));
    }


    /**
    * funcion to get the students will be register on the new level
    *
    **/
    public function getStudentsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        //dump($this->session->get('ie_id'));
        //dump($aInfoUeducativa);die;

        //dump($aInfoUeducativa['ueducativaInfoId']['iecId']);die;

        $exist = true;
        $objStudents = array();
        //get students to be registered

        ////AUTOMATIZACION DE PROCESOS DE INSCRIPCION
        if ($this->session->get('ie_per_cod') == '2'){
            $periodo = '3';
            $gestion = $this->session->get('ie_gestion') - 1;
        }else if ($this->session->get('ie_per_cod') == '3'){
            $periodo = '2';
            $gestion = $this->session->get('ie_gestion');
        }
        $objPrevStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseTodoInscriptionAlter(
                $this->session->get('ie_id'), $gestion, $this->session->get('ie_subcea'), $periodo,
                $aInfoUeducativa['ueducativaInfoId']['nivelId'],$aInfoUeducativa['ueducativaInfoId']['setCodigo'],$aInfoUeducativa['ueducativaInfoId']['satCodigo'],
                $aInfoUeducativa['ueducativaInfoId']['paraleloId'],$aInfoUeducativa['ueducativaInfoId']['turnoId']
              );

        if(!($objPrevStudents>0)){
          $message = 'No existe información de Estudiantes...';
          $this->addFlash('warningstudents', $message);
          return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:students.html.twig', array(
                      'exist' => false
          ));
        }
        foreach($objPrevStudents as $student){
          //find student if has an inscription on the current course
          $postulantStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                                                                                                    'estudiante'=>$student['id'],
                                                                                                    'institucioneducativaCurso'=>$aInfoUeducativa['ueducativaInfoId']['iecId']
                                                                                                    ));
          //check if the student exists
          if(!$postulantStudent){
            $objStudents[]=$student;
          }
        }//end foreach
        //dump($objStudents);
        //die;
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:students.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist,
                    'infoUe' => $infoUe,
                    'iecId'=>$aInfoUeducativa['ueducativaInfoId']['iecId']
        ));
        //dump($objPrevStudents);die;
        //$objCurrentStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
        //get students will be registered
        /*if($aInfoUeducativa['ueducativaInfoId']['nivelId']==15){
                  $indPrevInscription = $this->getCourse(
                                                        $aInfoUeducativa['ueducativaInfoId']['nivelId'],
                                                        $aInfoUeducativa['ueducativaInfoId']['cicloId'],
                                                        $aInfoUeducativa['ueducativaInfoId']['gradoId'],
                                                        '1') ;


                    $objStudentsInscirption = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseTodoInscriptionAlter($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $aInfoUeducativa['ueducativaInfoId']['nivelId']);

                    //check if the course exist
                    if($indPrevInscription<0){
                      $message = 'No existe información de Estudiantes...';
                      $this->addFlash('warningstudents', $message);
                      return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:students.html.twig', array(
                                  'exist' => false
                      ));
                    }
                    //get the nivel, grado and paralelo
                    list($nivel, $ciclo, $grado)=explode('-',$this->aCursos[$indPrevInscription]);
                    $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                                                                                                            'nivelTipo'=>$nivel,
                                                                                                            'cicloTipo'=>$ciclo,
                                                                                                            'gradoTipo'=>$grado,
                                                                                                            'gestionTipo'=>$this->session->get('ie_gestion')-1,
                                                                                                            'institucioneducativa'=>$this->session->get('ie_id')
                                                                                                        ));


          $objPrevStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($objCurso->getId());
        } else {
          $objPrevStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseTodoInscriptionAlter($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $aInfoUeducativa['ueducativaInfoId']['nivelId']);
        }*/
    }


    public function inscriptionAction(Request $request) {
        // create the conexion to DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send data
        //$dataInscription = $request->get('formdata');
        $infoUe = $request->get('formdata')['data'];
        $arraInfoUe = unserialize($infoUe);
        $dataStudents = $request->get('form');

        try {
            //do the inscription to the next level
            reset($dataStudents);
            while ($valStudents = current($dataStudents)) {
              if(isset($valStudents['student'])){
                $altersocio = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findByEstudianteInscripcion($valStudents['eInsId']);

                if (count($altersocio) == 1){
                    $altsocioaux = clone $altersocio[0];

                    $studentInscription = new EstudianteInscripcion();
                    $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                    $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                    $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                    $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($valStudents['student']));
                    $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
                    $studentInscription->setObservacion(1);
                    $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                    $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                    $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($arraInfoUe['ueducativaInfoId']['iecId']));
                    //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
                    $studentInscription->setCodUeProcedenciaId(0);
                    $em->persist($studentInscription);
                    $em->flush();

                    $altsocioaux->setEstudianteInscripcion($studentInscription);
                    $em->persist($altsocioaux);
                    $em->flush();

                    $altersocioAccesso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAcceso')->findByEstudianteInscripcionSocioeconomicoAlternativa($altersocio[0]->getId());
                    $altersocioHabla = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHabla')->findByEstudianteInscripcionSocioeconomicoAlternativa($altersocio[0]->getId());
                    $altersocioOcupacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacion')->findByEstudianteInscripcionSocioeconomicoAlternativa($altersocio[0]->getId());
                    $altersocioTransporte = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporte')->findByEstudianteInscripcionSocioeconomicoAlternativa($altersocio[0]->getId());

                    $altersocioAccessoAux = clone $altersocioAccesso[0];
                    $altersocioAccessoAux->setEstudianteInscripcionSocioeconomicoAlternativa($altsocioaux);
                    $em->persist($altsocioaux);
                    $em->flush();

                    $altersocioHablaAux = clone $altersocioHabla[0];
                    $altersocioHablaAux->setEstudianteInscripcionSocioeconomicoAlternativa($altsocioaux);
                    $em->persist($altersocioHablaAux);
                    $em->flush();

                    $altersocioOcupacionAux = clone $altersocioOcupacion[0];
                    $altersocioOcupacionAux->setEstudianteInscripcionSocioeconomicoAlternativa($altsocioaux);
                    $em->persist($altersocioOcupacionAux);
                    $em->flush();

                    $altersocioTransporteAux = clone $altersocioTransporte[0];
                    $altersocioTransporteAux->setEstudianteInscripcionSocioeconomicoAlternativa($altsocioaux);
                    $em->persist($altersocioTransporteAux);
                    $em->flush();
                    //add the areas to the student
                    //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
                }
              }
              next($dataStudents);
            }
            $em->getConnection()->commit();

            if ($arraInfoUe['ueducativaInfoId']['nivelId'] != '15'){
                   $etapaespecialidad = $arraInfoUe['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $arraInfoUe['ueducativaInfo']['grado'];
            }else{
                   $etapaespecialidad = $arraInfoUe['ueducativaInfo']['grado'];
            }

            $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($arraInfoUe['ueducativaInfoId']['iecId']);
            return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                        'objStudents' => $objStudents,
                        'form' => $this->createFormStudentInscription($infoUe)->createView(),
                        'exist' => true,
                        'existins' => (count($objStudents) > 0)?true:false,
                        'infoUe' => $infoUe,
                        'etapaespecialidad' => $etapaespecialidad,
                        'dataUe'=> $arraInfoUe['ueducativaInfo'],
                        'totalInscritos'=>count($objStudents)
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();

            if ($arraInfoUe['ueducativaInfoId']['nivelId'] != '15'){
                   $etapaespecialidad = $arraInfoUe['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $arraInfoUe['ueducativaInfo']['grado'];
            }else{
                   $etapaespecialidad = $arraInfoUe['ueducativaInfo']['grado'];
            }
            $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($arraInfoUe['ueducativaInfoId']['iecId']);
            return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                        'objStudents' => $objStudents,
                        'form' => $this->createFormStudentInscription($infoUe)->createView(),
                        'exist' => true,
                        'existins' => (count($objStudents) > 0)?true:false,
                        'infoUe' => $infoUe,
                        'etapaespecialidad' => $etapaespecialidad,
                        'dataUe'=> $arraInfoUe['ueducativaInfo'],
                        'totalInscritos'=>count($objStudents)
            ));
        }
    }

    /**
    * to remove the students inscription by krlos
    * values to send infoUe and infoStudent
    **/
    public function removeInscriptionAction(Request $request){
        // create the conexion to DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send values
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $arrInfoUe = unserialize($infoUe);
        $arrInfoStudent = json_decode($infoStudent, true);
        //dump($arrInfoStudent['eInsId']); die;
        try {
            //look for estudiante asignatura info
            $objStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $arrInfoStudent['eInsId']));
            foreach ($objStudentAsignatura as $key => $asignatura) {
                # look for notas student by asignatura
                $objStudentNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $asignatura->getId()));
                //dump($objStudentNota[$key]);
                foreach ($objStudentNota as $key => $nota) {
                    # remove the student's note
                    $objNotaRemove = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($nota->getId());
                    if (!$objNotaRemove) {
                        throw $this->createNotFoundException('No se puede eliminar por ' . $id);
                    }
                    $em->remove($objNotaRemove);
                    $em->flush();
                }
                //remove the asignatura
                $objStudentAsignaturaRemove = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($asignatura->getId());
                $em->remove($objStudentAsignaturaRemove);
                $em->flush();
            }

            //find estudiante_inscripcion_socioeconomico_alternativa data to delete
            $objSocioEconomicos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findBy(array(
              'estudianteInscripcion' => $arrInfoStudent['eInsId']
            ));

            foreach ($objSocioEconomicos as $socionEco) {
              # code...

              //dump($socionEco->getId());
                /*eliminar idiomas*/
                $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHabla')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId() ));
                foreach ($idiomas as $value) {
                    $em->remove($value);
                }
                $em->flush();

                /*eliminar ocupacion*/
                $ocupacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacion')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId() ));
                foreach ($ocupacion as $value) {
                    $em->remove($value);
                }
                $em->flush();

                /*eliminar acceso*/
                $acceso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAcceso')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId() ));
                foreach ($acceso as $value) {
                    $em->remove($value);
                }
                $em->flush();

                /*eliminar transporte*/
                $transporte = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporte')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId()));
                foreach ($transporte as $value) {
                    $em->remove($value);
                }
                $em->flush();

                /*
                //eliminar unidad militar
                $unimili = $em->getRepository('SieAppWebBundle:UnidadMilitar')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId()));
                foreach ($unimili as $value) {
                    $em->remove($value);
                }
                $em->flush();
                //eliminar penal
                $penal = $em->getRepository('SieAppWebBundle:Penal')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId()));
                foreach ($penal as $value) {
                    $em->remove($value);
                }
                $em->flush();
                //eliminar educacion diversa
                $eddiv = $em->getRepository('SieAppWebBundle:EducacionDiversa')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socionEco->getId()));
                foreach ($eddiv as $value) {
                    $em->remove($value);
                }
                $em->flush();
                */
                //remove estudiante_inscripcion_socioeconomico_alternativa
                //dump($socionEco);
                $em->remove($socionEco);
                $em->flush();

            }

            //INI new relation to remove by krlos

            //step 4 delete socio economico data
            $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $arrInfoStudent['eInsId'] ));
            //dump($objSocioEco);die;
            foreach ($objSocioEco as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 5 delete apoderado_inscripcion data
            $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $arrInfoStudent['eInsId'] ));
            //dump($objApoIns);die;

            foreach ($objApoIns as $element) {
                $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
                foreach ($objApoInsDat as $element1){
                    $em->remove($element1);
                }
                $em->remove($element);
            }
            $em->flush();


            //remove attached file
            $objStudentInscriptionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion'=>$arrInfoStudent['eInsId']));
            if($objStudentInscriptionExtranjero){
              $em->remove($objStudentInscriptionExtranjero);
              $em->flush();
            }

           //paso 6 borrando apoderados
            $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $arrInfoStudent['eInsId'] ));
            foreach ($apoderados as $element) {
                $em->remove($element);
            }
            $em->flush();

           //paso 7 borrando apoderados
            //dump($arrInfoStudent);
            //dump($arrInfoStudent['eInsId']);die;

            $objEstudianteInscripcionSocioeconomicoRegular = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $arrInfoStudent['eInsId'] ));

            if(($objEstudianteInscripcionSocioeconomicoRegular)){
              $objEstudianteInscripcionSocioeconomicoRegNacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegNacion as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $objEstudianteInscripcionSocioeconomicoRegInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegInternet as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $objEstudianteInscripcionSocioeconomicoRegHablaFrec = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegHablaFrec as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $em->remove($objEstudianteInscripcionSocioeconomicoRegular);
              $em->flush();
            }
            //END new relation to remove by krlos
//dump($objEstudianteInscripcionSocioeconomicoRegular);die;
            //find the inscription to delete $arrInfoStudent['eInsId']
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $arrInfoStudent['id'], 'institucioneducativaCurso' => $arrInfoUe['ueducativaInfoId']['iecId']));
            if($objStudentInscription){
                $em->remove($objStudentInscription);
                $em->flush();
            }else{
                $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoStudent['eInsId']);
                $em->remove($objStudentInscription);
                $em->flush();
            }

            //everythinng ok
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodinscription', 'Inscripcion Eliminada... ');

            //$exist = true;
            $objStudents = array();

            if ($arrInfoUe['ueducativaInfoId']['nivelId'] != '15'){
                $etapaespecialidad = $arrInfoUe['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $arrInfoUe['ueducativaInfo']['grado'];
            }
            else{
                $etapaespecialidad = $arrInfoUe['ueducativaInfo']['grado'];
            }


            $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($arrInfoUe['ueducativaInfoId']['iecId']);
            if (count($objStudents) > 0) {
                $existins = true;
            } else {
                $existins = false;
            }
            return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                        'objStudents' => $objStudents,
                        'form' => $this->createFormStudentInscription($infoUe)->createView(),
                        'exist' => true,
                        'existins' => $existins,
                        'infoUe' => $infoUe,
                        'etapaespecialidad' => $etapaespecialidad,
                        'dataUe' => $arrInfoUe['ueducativaInfo'],
                        'totalInscritos' => count($objStudents)
            ));
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            //            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
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

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:inscriptionWithRude.html.twig', array(
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
        //die;
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:rudeinscription.html.twig', array(
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
            $em->persist($studentInscription);
            $em->flush();
            //add the areas to the student
            //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
            $message = 'Inscripción realizada...';
            $this->addFlash('successExeInscription', $message);
        } else {
            $message = 'Inscripción no realizada. No se tiene información del nivel selecionado...';
            $exist = false;
            $this->addFlash('warningExeInscription', $message);
        }

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:exeInscription.html.twig', array(
                    'exist' => $exist,
        ));
    }

    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
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
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
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
        return $studentInscription;
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
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
      return (isset($ind)?$ind-=1:-1);
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
            ('15-1-1'),
            ('15-1-2'),
            ('15-2-1'),
            ('15-2-2'),
            ('15-2-3'),
        );
        return($this->aCursos);
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:open.html.twig', array());
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:turnos.html.twig', array(
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

    public function directfindrudeAction(Request $request) {

//        dump($this->session->get('ie_gestion'));
//        die;
        $form = $request->get('form');
        if ( ($this->session->get('ie_gestion')) && ($form['Inputrude']) ){
            $em = $this->getDoctrine()->getManager();
            //$em = $this->getDoctrine()->getEntityManager();
            $db = $em->getConnection();
            $query = "
                select  f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                        ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                        ,h.turno_tipo_id,x.paralelo, tt.turno
                        ,j.codigo_rude,j.paterno,j.materno,j.nombre,case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                        ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac

                         from superior_facultad_area_tipo a
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                        inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                        inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                        inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                            inner join estudiante j on i.estudiante_id=j.id
                        where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."
                        and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." and codigo_rude = '".$form['Inputrude']."'";

            $stmt = $db->prepare($query);
//            dump($query);
//            die;

            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            if (!$po) {
                return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                    'personas' => $po,
                    'mensaje' => 'NO SE HA ENCUENTRA PRESENTE INSCRIPCIONES EN ESTE PERIODO PARA EL RUDE :'.$form['Inputrude'],
                ));
            }
            else{
                return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                    'personas' => $po,
                    'mensaje' => 'Inscripciones actuales para el rude :'.$form['Inputrude'],
                ));
            }
        }
        else{
            $po = array();
            return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                    'personas' => $po,
                    'mensaje' => '¡AUN NO SE HA SELECCIONADO CEA - GESTION - PERIODO - RUDE!',
                ));
        }
    }

}
