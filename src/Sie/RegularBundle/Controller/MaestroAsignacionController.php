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

/**
 * MaestroAsignacion controller.
 *
 */
class MaestroAsignacionController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     */
    public function indexAction(Request $request) {
        if ($request->getMethod() == 'POST'){
            $form = $request->get('form');
            $idCursoOferta = $form['llave'];
            $idValidacion = $form['idDetalle'];
            $this->session->set('idCursoOferta',$idCursoOferta);
            $this->session->set('idValidacion',$idValidacion);
        }else{
            $idCursoOferta = $this->session->get('idCursoOferta');
            $idValidacion = $this->session->get('idValidacion');
        }
        $em = $this->getDoctrine()->getManager();
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
        $maestros = $this->get('maestroAsignacion')->listar($idCursoOferta);

        return $this->render('SieRegularBundle:MaestroAsignacion:index.html.twig',array(
            'cursoOferta'=>$cursoOferta,
            'maestros'=>$maestros,
            'idValidacion'=>$idValidacion
        ));
    }

    public function asignarAction(Request $request){
        $idCursoOferta = $request->get('cursoOfertaId');
        $idValidacion = $request->get('idValidacion');

        $response = $this->get('maestroAsignacion')->asignar($request);

        // Actualizamos el regitro de validación_proceso

        $em = $this->getDoctrine()->getManager();
        $regValidacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idValidacion);
        $regValidacion->setEsActivo(true);
        $em->flush();

        return $this->redirectToRoute('maestroAsignacion');
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega el fomulario para registrar o modificar la asignacion de un maestro
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaAction(Request $request) {
        $validacionProcesoId = $request->get('vp_id');
        $maestroInscripcionId = $request->get('id');
        $gestion = $request->get('gestion');
        
        $turnoId = null;
        $nivelId = null;
        $gradoId = null;
        $paraleloId = null;
        $asignaturaId = null;
        $turnoTipoEntidad = array();
        $nivelTipoEntidad = array();
        $gradoTipoEntidad = array();
        $paraleloTipoEntidad = array();
        $asignaturaTipoEntidad = array();
  
        $em = $this->getDoctrine()->getManager();
        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);

        $institucionEducativaId = $maestroInscripcionEntidad->getInstitucioneducativa()->getId();
        $gestionId = $maestroInscripcionEntidad->getGestionTipo()->getId();
        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
        $turnoTipoEntidad = $this->getTurnoInstitucionEducativaCurso($institucionEducativaId,$gestionId);

        $turnoTipoArray = array();
        foreach ($turnoTipoEntidad as $data) {
            $turnoTipoArray[$data['id']] = $data['turno'];
        }

        $info = base64_encode(json_encode(array('sie'=>$institucionEducativaId, 'gestion'=>$gestionId, 'vp_id'=>$validacionProcesoId)));

        if($validacionProcesoId > 0){
            $validacionProcesoEntidad = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        } 
                

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct tt.id, tt.turno")
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.nivelTipo in (11,12,13)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->orderBy('tt.id', 'ASC');
        // $maestros = $this->get('maestroAsignacion')->listar($idCursoOferta);
        $form = $this->createFormBuilder()
                ->add('info', 'hidden', array('label' => 'Info', 'attr' => array('value' => $info)))
                ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'Seleccione Turno', 'choices' => $turnoTipoArray, 'data' => $turnoId, 'attr' => array('class' => 'form-control', 'onchange' => 'listar_nivel()', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $nivelTipoEntidad, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_grado_asignatura()')))
                ->add('grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Seleccione Grado', 'choices' => $gradoTipoEntidad, 'data' => $gradoId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_paralelo()')))
                ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccione Paralelo', 'choices' => $paraleloTipoEntidad, 'data' => $paraleloId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_asignatura_curso()')))
                ->add('asignatura', 'choice', array('label' => 'Asignatura', 'empty_value' => 'Seleccione Asignatura', 'choices' => $asignaturaTipoEntidad, 'data' => $asignaturaId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_curso_asignatura()')))
                ->add('buscar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'style' => 'display: none;')))
                ->getForm()->createView();
        
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaIndex.html.twig',array(
            'form' => $form
        ));
    }

    public function getTurnoInstitucionEducativaCurso($institucionEducativaId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct tt.id, tt.turno")
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.nivelTipo in (11,12,13)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->orderBy('tt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el nivel 
    // PARAMETROS: sie, gestion, turno
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaListaNivelAction(Request $request) {
        $turnoId = $request->get('turno');        
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        // $validacionProcesoId = $info["vp_id"];
                
        $response = new JsonResponse();
        return $response->setData(array(
            'niveles' => $this->getNivelInstitucionEducativaCursoTurno($institucionEducativaId, $gestionId, $turnoId),
        ));
    }

    public function getNivelInstitucionEducativaCursoTurno($institucionEducativaId, $gestionId, $turnoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct nt.id, nt.nivel")
                ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = iec.nivelTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('nt.id in (11,12,13,2,3)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->orderBy('nt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el grado (primaria) o asignatura (secundaria)
    // PARAMETROS: sie, gestion, turno, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaListaGradoAsignaturaAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');        
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        // $validacionProcesoId = $info["vp_id"];
                
        $response = new JsonResponse();
        if($nivelId == 13) {
            return $response->setData(array(
                'asignaturas' => $this->getAsignaturaInstitucionEducativaCursoTurnoNivel($institucionEducativaId, $gestionId, $turnoId, $nivelId),
            ));
        } else {
            return $response->setData(array(
                'grados' => $this->getGradoInstitucionEducativaCursoTurnoNivel($institucionEducativaId, $gestionId, $turnoId, $nivelId),
            ));
        }
    }

    public function getGradoInstitucionEducativaCursoTurnoNivel($institucionEducativaId, $gestionId, $turnoId, $nivelId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct gt.id, gt.grado")
                ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->orderBy('gt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getAsignaturaInstitucionEducativaCursoTurnoNivel($institucionEducativaId, $gestionId, $turnoId, $nivelId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct at.id, at.asignatura")
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.insitucioneducativaCurso = iec.id')
                ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'at.id = ieco.asignaturaTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->orderBy('at.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los paralelos
    // PARAMETROS: sie, gestion, turno, nivel, grado
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMaterialistaParaleloAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');      
        $gradoId = $request->get('grado');        
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        // $validacionProcesoId = $info["vp_id"];
                
        $response = new JsonResponse();
        return $response->setData(array(
            'paralelos' => $this->getParaleloInstitucionEducativaCursoTurnoNivelGrado($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId),
        ));
    }

    public function getParaleloInstitucionEducativaCursoTurnoNivelGrado($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct pt.id, pt.paralelo")
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->orderBy('pt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista de cursos para la asignacion de maestros  
    // PARAMETROS: sie, gestion, turno, nivel, grado, paralelo
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMaterialistaCursoAsignaturaAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');      
        $asignaturaId = $request->get('asignatura');        
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        // $validacionProcesoId = $info["vp_id"];

        $listaAsignatura = $this->getCursoInstitucionEducativaCursoTurnoNivelAsignatura($institucionEducativaId, $gestionId, $turnoId, $nivelId, $asignaturaId);
        
        if(count($listaAsignatura)>0){
            $countPeriodo = 0;
            foreach($listaAsignatura[0] as $key => $value){  
                if(isset($value['estado'])){
                    if($value['estado'] == "t"){
                        $countPeriodo = $countPeriodo + 1;
                    }
                }
            }
            if($countPeriodo == 0) {
                $countPeriodo = 1;
            }
        }
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaDetalle.html.twig', array('listaCurso' => $listaAsignatura, 'periodos'=> $countPeriodo));
    }

    public function getCursoInstitucionEducativaCursoTurnoNivelAsignatura($institucionEducativaId, $gestionId, $turnoId, $nivelId, $asignaturaId){
        $em = $this->getDoctrine()->getManager();        
        $institucionEducativaEntidad = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionEducativaId);
        $institucionEducativaTipoId = $institucionEducativaEntidad->getInstitucioneducativaTipo()->getId();
        $query = $em->getConnection()->prepare('SELECT sp_genera_maestro_asignacion_asignatura(:institucionEducativaId::VARCHAR,:gestionId::VARCHAR,:nivelId::VARCHAR,:turnoId::VARCHAR,:asignaturaId::VARCHAR,:institucionEducativaTipoId::VARCHAR)');
        $query->bindValue(':institucionEducativaId', $institucionEducativaId);
        $query->bindValue(':gestionId', $gestionId);
        $query->bindValue(':turnoId', $turnoId);
        $query->bindValue(':nivelId', $nivelId);
        $query->bindValue(':asignaturaId', $asignaturaId);
        $query->bindValue(':institucionEducativaTipoId', $institucionEducativaTipoId);
        $query->execute();
        $entity = $query->fetchAll();
        $array = array();
        foreach($entity as $key => $value){            
            $arrayVal = json_decode($value['sp_genera_maestro_asignacion_asignatura'],true);
            $arrayMod = array();
            $arrayId = 0;
            $arrayName = "";
            foreach($arrayVal as $name => $val){
                if($name != "institucioneducativa_curso_oferta_id" and $name != "institucioneducativa_curso_id"){
                    switch ($name){
                        case "institucioneducativa_curso_oferta_id": 
                            $nameAux = "Id"; 
                            break;
                        case "grado_tipo_id": 
                            $nameAux = "Grado"; 
                            break;
                        case "paralelo": 
                            $nameAux = "Paralelo"; 
                            break;
                        case "insc": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>0))), 'estado'=>$val);
                            $nameAux = "I.G."; 
                            break;
                        case "b1": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>1))), 'estado'=>$val);
                            $nameAux = "B1"; 
                            break;
                        case "b2": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>2))), 'estado'=>$val);
                            $nameAux = "B2"; 
                            break;
                        case "b3": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>3))), 'estado'=>$val);
                            $nameAux = "B3"; 
                            break;
                        case "b4": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>4))), 'estado'=>$val);
                            $nameAux = "B4"; 
                            break;
                        case "t1": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>6))), 'estado'=>$val);
                            $nameAux = "T1"; 
                            break;
                        case "t2": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>7))), 'estado'=>$val);
                            $nameAux = "T2"; 
                            break;
                        case "t3": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>8))), 'estado'=>$val);
                            $nameAux = "T3"; 
                            break;
                        default: $name = $name;
                    } 
                    $arrayMod[$nameAux] = $val;
                } else {
                    $arrayId = $val;
                    $arrayName = $name;
                }
            }
            $array[$key] = $arrayMod;            
        }

        return $array;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista de asignaturas por curso para la asignacion de maestros  
    // PARAMETROS: sie, gestion, turno, nivel, asignatura
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMaterialistaAsignaturaCursoAction(Request $request) {
        $turnoId = $request->get('turno');
        $nivelId = $request->get('nivel');      
        $gradoId = $request->get('grado');     
        $paraleloId = $request->get('paralelo');        
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        // $validacionProcesoId = $info["vp_id"];
        $listaCurso = $this->getAsignaturaInstitucionEducativaCursoTurnoNivelGradoParalelo($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId, $paraleloId);
        if(count($listaCurso)>0){
            $countPeriodo = 0;
            foreach($listaCurso[0] as $key => $value){  
                if(isset($value['estado'])){
                    if($value['estado'] == "t"){
                        $countPeriodo = $countPeriodo + 1;
                    }
                }
            }
            if($countPeriodo == 0) {
                $countPeriodo = 1;
            }
        }
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaDetalle.html.twig', array('listaAsignatura' => $listaCurso, 'periodos'=> $countPeriodo));
    }

    public function getAsignaturaInstitucionEducativaCursoTurnoNivelGradoParalelo($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId, $paraleloId){
        $em = $this->getDoctrine()->getManager();        
        $institucionEducativaCursoEntidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array('institucioneducativa'=>$institucionEducativaId,'gestionTipo'=>$gestionId,'turnoTipo'=>$turnoId,'nivelTipo'=>$nivelId,'gradoTipo'=>$gradoId,'paraleloTipo'=>$paraleloId));
        if(count($institucionEducativaCursoEntidad)>0){
            $institucionEducativaCursoEntidad = $institucionEducativaCursoEntidad[0];
            $institucionEducativaTipoId = $institucionEducativaCursoEntidad->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId();
            $institucionEducativaCursoId = $institucionEducativaCursoEntidad->getId();
            $query = $em->getConnection()->prepare('SELECT sp_genera_maestro_asignacion_curso(:institucionEducativaCursoId::VARCHAR,:institucionEducativaTipoId::VARCHAR)');
            $query->bindValue(':institucionEducativaCursoId', $institucionEducativaCursoId);
            $query->bindValue(':institucionEducativaTipoId', $institucionEducativaTipoId);
            $query->execute();
            $entity = $query->fetchAll();
        } else {
            $entity = array();
        }        
        $array = array();
        $arrayId = array();
        foreach($entity as $key => $value){            
            $arrayVal = json_decode($value['sp_genera_maestro_asignacion_curso'],true);
            $arrayMod = array();
            $arrayId = 0;
            $arrayName = "";
            foreach($arrayVal as $name => $val){
                if($name != "institucioneducativa_curso_oferta_id"){
                    switch ($name){
                        case "institucioneducativa_curso_oferta_id": 
                            $nameAux = "Id"; 
                            break;
                        case "asignatura": 
                            $nameAux = "Asignatura"; 
                            break;
                        case "insc": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>0))), 'estado'=>$val);
                            $nameAux = "I.G."; 
                            break;
                        case "b1": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>1))), 'estado'=>$val);
                            $nameAux = "B1"; 
                            break;
                        case "b2": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>2))), 'estado'=>$val);
                            $nameAux = "B2"; 
                            break;
                        case "b3": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>3))), 'estado'=>$val);
                            $nameAux = "B3"; 
                            break;
                        case "b4": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>4))), 'estado'=>$val);
                            $nameAux = "B4"; 
                            break;
                        case "t1": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>6))), 'estado'=>$val);
                            $nameAux = "T1"; 
                            break;
                        case "t2": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>7))), 'estado'=>$val);
                            $nameAux = "T2"; 
                            break;
                        case "t3": 
                            $val = array('value'=>base64_encode(json_encode(array($arrayName=>$arrayId, 'nota_tipo_id'=>8))), 'estado'=>$val);
                            $nameAux = "T3"; 
                            break;
                        default: $name = $name;
                    }
                    $arrayMod[$nameAux] = $val;
                } else {
                    $arrayId = $val;
                    $arrayName = $name;
                }
            }
            $array[$key] = $arrayMod;            
        }
        return $array;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que visualiza el formulario para guardar o modificar la asignacion del maestro
    // PARAMETROS: datos
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaFormularioAction(Request $request) {
        $info = json_decode(base64_decode($request->get('info')), true);
        $val = json_decode(base64_decode($request->get('val')), true);
        
        $maestroInscripcionId = 0;
        $listaMaestros = $this->getMaestrosInstitucionEducativaCursoOferta($val['institucioneducativa_curso_oferta_id'], $val['nota_tipo_id']);
        $detalleCursoOferta = $this->getInstitucionEducativaCursoOferta($val['institucioneducativa_curso_oferta_id']);
        if(count($detalleCursoOferta )>0){
            $detalleCursoOferta = $detalleCursoOferta[0];
        }
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        
        $em = $this->getDoctrine()->getManager();
        $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($val['nota_tipo_id']);

        $maestroInscripcionId = 0;
        $item = "";
        $fechaInicio = "";
        $fechaFin = "";
        $financiamientoTipoEntity = array();
        $maestroInscripcioEntity = array();
        $formEnable = true;

        if(isset($info["vp_id"])){
            $validacionProcesoId = $info["vp_id"];  
            $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
            if(count($validacionProcesoEntity)>0){
                if($validacionProcesoEntity->getValidacionReglaTipo()->getId() == 57){
                    $llave = explode("|",$validacionProcesoEntity->getLlave());
                    if (count($llave) == 2){
                        $maestroInscripcionId = $llave[0];
                    } 
                } else {
                    $maestroInscripcionId = $validacionProcesoEntity->getLlave();
                }
            }
            $maestroInscripcioEntity = $this->getMaestroInscripcion($maestroInscripcionId);
            if(count($maestroInscripcioEntity)>0){
                $maestroInscripcioEntity = $maestroInscripcioEntity[0];
                $maestroInscripcioEntity['institucioneducativaCursoOfertaId'] = $val['institucioneducativa_curso_oferta_id'];
            }
            $val['maestro_inscripcion_id'] = $maestroInscripcionId;

            $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $val['institucioneducativa_curso_oferta_id'], 'notaTipo' => $val['nota_tipo_id']));
            //dump($institucioneducativaCursoOfertaMaestroEntity);die;
            if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
                $item = $institucioneducativaCursoOfertaMaestroEntity->getItem();
                $fechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-Y');
                $fechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-Y');
                $financiamientoTipoEntity = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo();
            } 
            if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
                $formEnable = false;
            } 
        }
        
        $notaTipo = $notaTipoEntity->getNotaTipo();
        
        if (isset($info["vp_id"])){
            $form = $this->getFormOfertaMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity);
        } else {
            $maestroInscripcionLista = $this->listaMaestroInscripcion($institucionEducativaId,$gestionId);
            $arrayMaestroInscripcionLista = array();
            foreach ($maestroInscripcionLista as $data) {
                if($data['complemento'] != ""){
                    $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad']."-".$data['complemento'].")";
                } else {
                    $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad'].")";
                }
            }
            $form = $this->getFormRegistroMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $arrayMaestroInscripcionLista, 0);
        }
        $arrayRangoFecha = array('inicio'=>"01-01-".$gestionId,'final'=>"31-12-".$gestionId);
        $arrayFormulario = array('titulo' => "Registro / Modificación de maestro", 'detalleCursoOferta' => $detalleCursoOferta, 'listaMaestros' => $listaMaestros, 'notaTipo'=>$notaTipo, 'casoMaestro'=>$maestroInscripcioEntity, 'formNuevo'=>$form, 'formEnable'=>$formEnable, 'rangoFecha'=>$arrayRangoFecha);;
                
        if (isset($info["vp_id"])){
            return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaFormulario.html.twig', $arrayFormulario);
        } else {            
            return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMaestroFormulario.html.twig', $arrayFormulario);
        }
    }

    public function getFormOfertaMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity){
        $form = $this->createFormBuilder()
        ->add('ofertaMaestro', 'hidden', array('label' => 'Info', 'attr' => array('value' => base64_encode(json_encode($val)))))
        ->add('item', 'text', array('label' => 'Item', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $item, 'style' => 'text-transform:uppercase', 'placeholder' => 'Item' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación (ej.: 01-01-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación (ej.: 01-01-2020)' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaFin', 'text', array('label' => 'Fecha final de asignación (ej.: 31-12-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha fin de asignación (ej.: 31-12-2020)' , 'maxlength' => 10, 'required' => true)))
        ->add('financiamiento', 'entity', array('data' => $financiamientoTipoEntity, 'label' => 'Financiamiento', 'empty_value' => 'Seleccione Financiamiento', 'class' => 'Sie\AppWebBundle\Entity\FinanciamientoTipo',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ft')
                        ->orderBy('ft.id', 'ASC');
            },
        ))
        ->getForm()->createView();
        return $form;
    }

    public function getMaestrosInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId, $notaTipoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $entity->createQueryBuilder('iecom')
                ->select("distinct iecom.id as institucioneducativaCursoOfertaMaestroId, mi.id as maestroInscripcionId, nota.notaTipo, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento, ft.financiamiento, ft.id as financiamientoId, iecom.asignacionFechaInicio, iecom.asignacionFechaFin, iecom.item")                
                ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.id = iecom.maestroInscripcion')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')
                ->innerJoin('SieAppWebBundle:NotaTipo', 'nota', 'WITH', 'nota.id = iecom.notaTipo')
                ->innerJoin('SieAppWebBundle:FinanciamientoTipo', 'ft', 'WITH', 'ft.id = iecom.financiamientoTipo')
                ->where('iecom.institucioneducativaCursoOferta = :institucionEducativaCursoOfertaId')
                ->andWhere('nota.id = :notaTipoId')
                ->setParameter('institucionEducativaCursoOfertaId', $institucionEducativaCursoOfertaId)
                ->setParameter('notaTipoId', $notaTipoId)
                ->orderBy('iecom.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta');
        $query = $entity->createQueryBuilder('ieco')
                ->select("distinct ie.id as institucioneducativaId, ie.institucioneducativa, tt.turno, nt.nivel, gt.grado, pt.paralelo, at.asignatura")
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ieco.insitucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'at.id = ieco.asignaturaTipo')
                ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = iec.nivelTipo')
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->where('ieco.id = :institucionEducativaCursoOfertaId')
                ->setParameter('institucionEducativaCursoOfertaId', $institucionEducativaCursoOfertaId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getMaestroInscripcion($maestroInscripcioId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
            ->select("distinct mi.id as maestroInscripcionId, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento")                
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')
            ->where('mi.id = :maestroInscripcionId')
            ->setParameter('maestroInscripcionId', $maestroInscripcioId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que guardar la asignacion del maestro
    // PARAMETROS: institucioneducativaCursoOfertaId, maestroInscripcionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $form = $request->get('form');
        $info = json_decode(base64_decode($form['ofertaMaestro']), true);
        
        $notaTipoId = $info['nota_tipo_id'];
        if(isset($form['maestro'])){
            $maestroInscripcionId = base64_decode($form['maestro']);
        } else {
            $maestroInscripcionId = $info['maestro_inscripcion_id'];
        }
        $institucioneducativaCursoOfertaId = $info['institucioneducativa_curso_oferta_id'];

        $response = new JsonResponse();
        
        if($form['item'] == ""){
            $msg = "Debe ingresar el item de la asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $item = $form['item'];
        }        
        if($form['financiamiento'] == "0" or $form['financiamiento'] == ""){
            $msg = "Debe ingresar el financiamiento del item";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $financiamientoId = $form['financiamiento'];
        }
        if($form['fechaInicio'] == ""){
            $msg = "Debe ingresar la fecha inicial de la asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $fechaInicio = new \DateTime($form['fechaInicio']);
        }
        if($form['fechaFin'] == ""){
            $msg = "Debe ingresar la fecha final de la asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $fechaFin = new \DateTime($form['fechaFin']);
        }
        

        if($fechaInicio > $fechaFin){
            $msg = "Rango de fechas (".date_format($fechaInicio,'d-m-Y')." al ".date_format($fechaFin,'d-m-Y').") no valido, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        
        $msg = "";
        $estado = true;
        $em = $this->getDoctrine()->getManager();
        $maestroInscripcionEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
        if(count($maestroInscripcionEntity) <= 0){
            $msg = "No existe la inscripcion del maestro que solicita asignar, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }

        $institucioneducativaCursoOfertaEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativaCursoOfertaId);
        if(count($maestroInscripcionEntity) <= 0){
            $msg = "No existe el curso al cual quiere registrar, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        $horasMes = $institucioneducativaCursoOfertaEntity->getHorasmes();
        $gestionId = $institucioneducativaCursoOfertaEntity->getInsitucioneducativaCurso()->getGestionTipo()->getId();

        $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipoId);
        if(count($notaTipoEntity) <= 0){
            $msg = "No existe el tipo de nota al cual quiere registrar, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        
        $financiamientoTipoEntity = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($financiamientoId);
        if(count($financiamientoTipoEntity) <= 0){
            $msg = "No existe el tipo de financiamiento al cual quiere registrar, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }

        $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId, 'notaTipo' => $notaTipoId));
        if(count($institucioneducativaCursoOfertaMaestroEntity) > 0){
            $msg = "Ya existe la asignación del maestro al curso seleccionado";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }

        $institucioneducativaCursoOfertaMaestroLista = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId, 'notaTipo' => $notaTipoId));
        if(count($institucioneducativaCursoOfertaMaestroLista) > 0){
            $validacionFechaSecuencial = $this->getValidacionFechaSecuencial($institucioneducativaCursoOfertaMaestroLista, $gestionId, $fechaInicio, $fechaFin, 0);
            if(!$validacionFechaSecuencial['estado']){                
                return $response->setData(array('estado'=>$validacionFechaSecuencial['estado'], 'msg'=>$validacionFechaSecuencial['msg']));
            }
        } 

        //dump($maestroInscripcionId);dump($institucioneducativaCursoOfertaId);dump($notaTipoId);die;

        $em->getConnection()->beginTransaction();
        try {                
            $InstitucioneducativaCursoOfertaMaestroEntity = new InstitucioneducativaCursoOfertaMaestro();            
            $InstitucioneducativaCursoOfertaMaestroEntity->setInstitucionEducativaCursoOferta($institucioneducativaCursoOfertaEntity);
            $InstitucioneducativaCursoOfertaMaestroEntity->setMaestroInscripcion($maestroInscripcionEntity);
            $InstitucioneducativaCursoOfertaMaestroEntity->setHorasMes($horasMes);
            $InstitucioneducativaCursoOfertaMaestroEntity->setFechaRegistro($fechaActual);
            $InstitucioneducativaCursoOfertaMaestroEntity->setNotaTipo($notaTipoEntity);
            $InstitucioneducativaCursoOfertaMaestroEntity->setEsVigenteMaestro(true);
            $InstitucioneducativaCursoOfertaMaestroEntity->setItem($item);
            $InstitucioneducativaCursoOfertaMaestroEntity->setFinanciamientoTipo($financiamientoTipoEntity);
            $InstitucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaInicio($fechaInicio);
            $InstitucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaFin($fechaFin);
            $em->persist($InstitucioneducativaCursoOfertaMaestroEntity);

            foreach ($institucioneducativaCursoOfertaMaestroLista as $data) {
                $data->setEsVigenteMaestro(false);
                $em->persist($data);
            }

            $em->flush();
            $em->getConnection()->commit();
            $msg = "Maestro asignado correctamente";
            $estado = true;
            $maestroAsignadoNombre = $InstitucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getNombre();
            $maestroAsignadoPaterno = $InstitucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getPaterno();
            $maestroAsignadoMaterno = $InstitucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getMaterno();
            $maestroAsignadoCI = $InstitucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getCarnet();
            $maestroAsignadoId = base64_encode($InstitucioneducativaCursoOfertaMaestroEntity->getId());
            $maestroAsignadoItem = $InstitucioneducativaCursoOfertaMaestroEntity->getItem();
            $maestroAsignadoFinanciamiento = $InstitucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getFinanciamiento();
            $maestroAsignadoFinanciamientoId = $InstitucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getId();
            $maestroAsignadoFechaInicio = date_format($InstitucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-yy');
            $maestroAsignadoFechaFin = date_format($InstitucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-yy');
            $maestroAsignado = array('maestroOferta'=>$maestroAsignadoId,'maestroItem'=>$maestroAsignadoItem,'maestroNombre'=>$maestroAsignadoNombre,'maestroPaterno'=>$maestroAsignadoPaterno,'maestroMaterno'=>$maestroAsignadoMaterno,'maestroCI'=>$maestroAsignadoCI,'maestroFinanciamientoId'=>$maestroAsignadoFinanciamientoId,'maestroFinanciamiento'=>$maestroAsignadoFinanciamiento,'maestroFechaInicio'=>$maestroAsignadoFechaInicio,'maestroFechaFin'=>$maestroAsignadoFechaFin, 'maestroInscripcionId'=>base64_encode($maestroInscripcionId));
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg, 'maestro'=>$maestroAsignado));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $msg = "Dificultades al realizar el registro, intente nuevamente";
            $estado = false;
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg));
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que elimina la asignacion del maestro
    // PARAMETROS: institucioneducativaCursoOfertaMaestroId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaEliminaAction(Request $request) {
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $em = $this->getDoctrine()->getManager();

        $institucioneducativaCursoOfertaMaestroId = base64_decode($request->get('ofertaMaestro'));

        $response = new JsonResponse();

        $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($institucioneducativaCursoOfertaMaestroId);

        if(count($institucioneducativaCursoOfertaMaestroEntity) <= 0){
            $msg = "No existe la asignación del maestro seleccionado";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $institucioneducativaCursoOfertaId = $institucioneducativaCursoOfertaMaestroEntity->getInstitucionEducativaCursoOferta()->getId();
            $notaTipoId = $institucioneducativaCursoOfertaMaestroEntity->getNotaTipo()->getId();
             
            $em->getConnection()->beginTransaction();
            try {                
                $em->remove($institucioneducativaCursoOfertaMaestroEntity);
                
                $institucioneducativaCursoOfertaMaestroLista = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId, 'notaTipo' => $notaTipoId), array('id'=>'DESC'));
                
                if(count($institucioneducativaCursoOfertaMaestroLista) > 0){
                    $countArray = 0;
                    foreach ($institucioneducativaCursoOfertaMaestroLista as $key => $data) {
                        if ($institucioneducativaCursoOfertaMaestroId != $data->getId()){
                            if($countArray == 0){
                                $data->setEsVigenteMaestro(true);
                            } else {
                                $data->setEsVigenteMaestro(false);
                            }
                            $countArray = $countArray + 1;
                        }
                        $em->persist($data);
                    }
                }
                

                $em->flush();
                $em->getConnection()->commit();
                $msg = "Asignación de maestro eliminado";
                $estado = true;
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg = "Dificultades al realizar el registro, intente nuevamente";
                $estado = false;
            }
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg));
        }       
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que finaliza la asignacion del maestro
    // PARAMETROS: maestroInscripcionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaFinalizarAction(Request $request) {
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $info = json_decode(base64_decode($request->get('info')), true);
        $validacionProcesoId = $info["vp_id"];  
        $gestionId = $info["gestion"];  
        $em = $this->getDoctrine()->getManager();
        $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        if(count($validacionProcesoEntity)>0){
            $maestroInscripcionId = $validacionProcesoEntity->getLlave();
        }
        $listaAsignacionesMaestro = $this->getInstitucionEducativaCursoOfertaMaestroGestion($maestroInscripcionId, $gestionId);
        
        $response = new JsonResponse();

        if(count($listaAsignacionesMaestro) > 0){
            $em->getConnection()->beginTransaction();
            try {     
                $validacionProcesoEntity->setEsActivo(true);
                $em->flush();
                $em->getConnection()->commit();
                $msg = "Observación finalizada";
                return $response->setData(array('estado'=>true, 'msg'=>$msg));
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg = "Dificultades al realizar el registro, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
        } else {            
            $msg = "No asigno el maestro observado, debe asignarlo para finalizar, declararlo como comisionado o eliminar si corresponde";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }       
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que elimina la inscripcion del maestro en caso de no asignar a ningun paralelo
    // PARAMETROS: maestroInscripcionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaEliminarAction(Request $request) {
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $info = json_decode(base64_decode($request->get('info')), true);
        $validacionProcesoId = $info["vp_id"];  
        $gestionId = $info["gestion"];  
        $em = $this->getDoctrine()->getManager();
        $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        if(count($validacionProcesoEntity)>0){
            $maestroInscripcionId = $validacionProcesoEntity->getLlave();
        }
        $listaAsignacionesMaestro = $this->getInstitucionEducativaCursoOfertaMaestroGestion($maestroInscripcionId, $gestionId);
        
        $response = new JsonResponse();

        if(count($listaAsignacionesMaestro) > 0){      
            $msg = "El maestro observado ya se encuentra asignado, para eliminar su registro no debe contar con ninguna asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {      
            $em->getConnection()->beginTransaction();
            try {     
                $maestroInscripcionEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);

                $maestroInscripcionIdiomaEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion'=>$maestroInscripcionId));
            
                foreach ($maestroInscripcionIdiomaEntity as $registro) {
                    $em->remove($registro);
                }
                
                $em->remove($maestroInscripcionEntity);

                $validacionProcesoEntity->setEsActivo(true);

                $em->flush();
                $em->getConnection()->commit();
                $msg = "Maestro eliminado y observación finalizada";
                return $response->setData(array('estado'=>true, 'msg'=>$msg));
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg = "Dificultades al eliminar el registro, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
        }       
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que modifica el cargo del maestro a "COMISIONADO - ORGANIZACIÓN SOCIAL"
    // PARAMETROS: maestroInscripcionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaModificarCargoOrganizacionSocialAction(Request $request) {
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $info = json_decode(base64_decode($request->get('info')), true);
        $validacionProcesoId = $info["vp_id"];  
        $gestionId = $info["gestion"];  
        $em = $this->getDoctrine()->getManager();
        $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        if(count($validacionProcesoEntity)>0){
            $maestroInscripcionId = $validacionProcesoEntity->getLlave();
        }
        $listaAsignacionesMaestro = $this->getInstitucionEducativaCursoOfertaMaestroGestion($maestroInscripcionId, $gestionId);
        
        $response = new JsonResponse();

        if(count($listaAsignacionesMaestro) > 0){
            $msg = "El maestro observado ya se encuentra asignado, para modificar el cargo a COMISIONADO - ORGANIZACIÓN SOCIAL, el maestro no debe contar con ninguna asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {            
            $em->getConnection()->beginTransaction();
            try {     
                $maestroInscripcionEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
                $cargoTipoEntity = $em->getRepository('SieAppWebBundle:CargoTipo')->find(70);
                $maestroInscripcionEntity->setCargoTipo($cargoTipoEntity);

                $validacionProcesoEntity->setEsActivo(true);

                $em->flush();
                $em->getConnection()->commit();
                $msg = "Cargo modificado y observación finalizada";
                return $response->setData(array('estado'=>true, 'msg'=>$msg));
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg = "Dificultades al realizar el registro, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
        }       
    }

    public function getInstitucionEducativaCursoOfertaMaestroGestion($maestroInscripcioId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $entity->createQueryBuilder('iecom')
                ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.id = iecom.maestroInscripcion')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.id = iecom.institucioneducativaCursoOferta')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ieco.insitucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = iec.gestionTipo')
                ->where('mi.id = :maestroInscripcioId')
                ->andWhere('gt.id = :gestionId')
                ->setParameter('maestroInscripcioId', $maestroInscripcioId)
                ->setParameter('gestionId', $gestionId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que edita la asignacion del maestro
    // PARAMETROS: institucioneducativaCursoOfertaId, maestroInscripcionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaEditaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        $form = $request->get('form');
        $institucioneducativaCursoOfertaMaestroId = base64_decode($form['ofertaMaestro']);
        // $notaTipoId = $info['nota_tipo_id'];
        // $maestroInscripcionId = $info['maestro_inscripcion_id'];
        // $institucioneducativaCursoOfertaId = $info['institucioneducativa_curso_oferta_id'];
        $item = $form['item'];
        $financiamientoId = $form['financiamiento'];
        $fechaInicio = new \DateTime($form['fechaInicio']);
        $fechaFin = new \DateTime($form['fechaFin']);

        $response = new JsonResponse();
        
        $msg = "";
        $estado = true;
        $em = $this->getDoctrine()->getManager(); 

        if($fechaInicio > $fechaFin){
            $msg = "Rango de fechas (".date_format($fechaInicio,'d-m-Y')." al ".date_format($fechaFin,'d-m-Y').") no valido, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        
        $financiamientoTipoEntity = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($financiamientoId);
        if(count($financiamientoTipoEntity) <= 0){
            $msg = "No existe el tipo de financiamiento al cual quiere registrar, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }

        $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($institucioneducativaCursoOfertaMaestroId);
        if(count($institucioneducativaCursoOfertaMaestroEntity) <= 0){
            $msg = "No existe la asignación del maestro en el curso seleccionado";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        $gestionId = $institucioneducativaCursoOfertaMaestroEntity->getinstitucioneducativaCursoOferta()->getInsitucioneducativaCurso()->getGestionTipo()->getId();

        $institucioneducativaCursoOfertaId = $institucioneducativaCursoOfertaMaestroEntity->getInstitucioneducativaCursoOferta()->getId();
        $notaTipoId = $institucioneducativaCursoOfertaMaestroEntity->getNotaTipo()->getId();
        
        $listaMaestros = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId, 'notaTipo' => $notaTipoId), array('asignacionFechaInicio'=>'asc'));
        
        $validacionFechaSecuencial = $this->getValidacionFechaSecuencial($listaMaestros, $gestionId, $fechaInicio, $fechaFin, $institucioneducativaCursoOfertaMaestroId);
        if(!$validacionFechaSecuencial['estado']){
            return $response->setData(array('estado'=>$validacionFechaSecuencial['estado'], 'msg'=>$validacionFechaSecuencial['msg']));
        }
        //$horasMes = $institucioneducativaCursoOfertaMaestroEntity->getInstitucioneducativaCursoOferta()->getHorasmes();
        $em->getConnection()->beginTransaction();
        try {     
            if(isset($form['maestro'])){
                $maestroInscripcionId = base64_decode($form['maestro']);
                $maestroInscripcionEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
                if(count($maestroInscripcionEntity) <= 0){
                    $msg = "No existe el maestro seleccionado";
                    return $response->setData(array('estado'=>false, 'msg'=>$msg));
                }
                $institucioneducativaCursoOfertaMaestroEntity->setMaestroInscripcion($maestroInscripcionEntity);
            } else {
                $maestroInscripcionId = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getId();
            } 
            
            $institucioneducativaCursoOfertaMaestroEntity->setItem($item);
            $institucioneducativaCursoOfertaMaestroEntity->setFinanciamientoTipo($financiamientoTipoEntity);
            $institucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaInicio($fechaInicio);
            $institucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaFin($fechaFin);
            $em->persist($institucioneducativaCursoOfertaMaestroEntity);
            $em->flush();
            $em->getConnection()->commit();
            $msg = "Asignación del maestro modificado correctamente";
            $estado = true;
            $maestroAsignadoNombre = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getNombre();
            $maestroAsignadoPaterno = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getPaterno();
            $maestroAsignadoMaterno = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getMaterno();
            $maestroAsignadoCI = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getCarnet();
            $maestroAsignadoId = base64_encode($institucioneducativaCursoOfertaMaestroEntity->getId());
            $maestroAsignadoFinanciamiento = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getFinanciamiento();
            $maestroAsignadoFinanciamientoId = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getId();
            $maestroAsignadoFechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-Y');
            $maestroAsignadoFechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-Y');
            $maestroAsignadoItem = $institucioneducativaCursoOfertaMaestroEntity->getItem();
            $maestroAsignado = array('maestroOferta'=>$maestroAsignadoId,'maestroItem'=>$maestroAsignadoItem,'maestroNombre'=>$maestroAsignadoNombre,'maestroPaterno'=>$maestroAsignadoPaterno,'maestroMaterno'=>$maestroAsignadoMaterno,'maestroCI'=>$maestroAsignadoCI,'maestroFinanciamientoId'=>$maestroAsignadoFinanciamientoId,'maestroFinanciamiento'=>$maestroAsignadoFinanciamiento,'maestroFechaInicio'=>$maestroAsignadoFechaInicio,'maestroFechaFin'=>$maestroAsignadoFechaFin,'maestroInscripcionId'=>base64_encode($maestroInscripcionId));
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg, 'maestro'=>$maestroAsignado));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $msg = "Dificultades al realizar el registro, intente nuevamente";
            $estado = false;
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg));
        }
    }

    public function getValidacionFechaSecuencial($listaMaestros, $gestionId, $fechaInicio, $fechaFin, $institucioneducativaCursoOfertaMaestroId){
        $ingresa = false;
        $error = false;
        $msg = "";
        if(count($listaMaestros)>0){       
            foreach ($listaMaestros as $key => $data) {
                $dataInstitucioneducativaCursoOfertaMaestroId = $data->getId();
                if ($institucioneducativaCursoOfertaMaestroId != $dataInstitucioneducativaCursoOfertaMaestroId){
                    if($data->getAsignacionFechaInicio() == null or $data->getAsignacionFechaInicio() == "" or $data->getAsignacionFechaFin() == null or $data->getAsignacionFechaFin() == ""){
                        $error = true;
                        $msg = "No cuenta con fecha de asinacion, el maestro ".$data->getMaestroInscripcion()->getPersona()->getPaterno();
                    }
                    if($data->getAsignacionFechaInicio() < $data->getAsignacionFechaInicio()){
                        $error = true;
                        $msg = "No cuenta con fecha de asinacion valida, el maestro ".$data->getMaestroInscripcion()->getPersona()->getPaterno();
                    }
                    if($key == 0){
                        $dataFechaInicioAnterior = new \DateTime("31-12-".($gestionId-1));
                        $dataFechaFinAnterior = new \DateTime("31-12-".($gestionId-1));
                    } else {
                        $dataFechaInicioAnterior = $listaMaestros[$key-1]->getAsignacionFechaInicio();
                        $dataFechaFinAnterior = $listaMaestros[$key-1]->getAsignacionFechaFin();
                    }
                    $dataFechaInicio = $data->getAsignacionFechaInicio();
                    $dataFechaFin = $data->getAsignacionFechaFin();
                    if ($dataFechaFinAnterior < $fechaInicio and $fechaFin < $dataFechaInicio){
                        $ingresa = true;
                    }   
                    if(!isset($listaMaestros[$key+1])){  
                        $dataFechaInicioAnterior = $dataFechaInicio;
                        $dataFechaFinAnterior = $dataFechaFin;
                        $dataFechaInicio = new \DateTime("01-01-".($gestionId+1));
                        $dataFechaFin = new \DateTime("01-01-".($gestionId+1));
                        if ($dataFechaFinAnterior < $fechaInicio and $fechaFin < $dataFechaInicio){
                            $ingresa = true;
                        }
                    }
                } else {
                    if(count($listaMaestros)==1){
                        $ingresa = true;     
                    }  
                }       
            }
        }
        if($error){
            return array('estado'=>false, 'msg'=>$msg);
        } else {
            if($ingresa){
                return array('estado'=>true, 'msg'=>"");
            } else {
                $msg = "La fecha de asignación (".date_format($fechaInicio,'d-m-Y')." al ".date_format($fechaFin,'d-m-Y').") debe ser secuencial y dentro de la gestión ".$gestionId;
                return array('estado'=>false, 'msg'=>$msg);
            }
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega el fomulario para registrar la asignacion de un maestro e una asignatura en especifico
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroAction(Request $request) {
        $validacionProcesoId = $request->get('vp_id');
        $llave = explode("|",$request->get('id'));
        $institucionEducativaCursoOfertaId = $llave[0];
        $notaTipoId = $llave[1];
        $gestionId = $request->get('gestion');
        $turnoId = null;
        $nivelId = null;
        $gradoId = null;
        $paraleloId = null;
        $asignaturaId = null;
        $turnoTipoEntidad = array();
        $nivelTipoEntidad = array();
        $gradoTipoEntidad = array();
        $paraleloTipoEntidad = array();
        $asignaturaTipoEntidad = array();
  
        $em = $this->getDoctrine()->getManager();

        $institucionEducativaCursoOfertaEntidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucionEducativaCursoOfertaId);

        $institucionEducativaId = $institucionEducativaCursoOfertaEntidad->getInsitucioneducativaCurso()->getInstitucioneducativa()->getId();

        $maestroInscripcionLista = $this->listaMaestroInscripcion($institucionEducativaId,$gestionId);

        $info = base64_encode(json_encode(array('sie'=>$institucionEducativaId, 'gestion'=>$gestionId, 'vp_id'=>$validacionProcesoId)));

        if($validacionProcesoId > 0){
            $validacionProcesoEntidad = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        } 

        $info = json_decode(base64_decode($request->get('info')), true);
        $val = array();
        $maestroInscripcionId = 0;
        $listaMaestros = $this->getMaestrosInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId, $notaTipoId);
        $detalleCursoOferta = $this->getInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId);
        if(count($detalleCursoOferta )>0){
            $detalleCursoOferta = $detalleCursoOferta[0];
        }
   
        // $em = $this->getDoctrine()->getManager();
        $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipoId);
        // $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        // if(count($validacionProcesoEntity)>0){
        //     $maestroInscripcionId = $validacionProcesoEntity->getLlave();
        // }
        // $maestroInscripcioEntity = $this->getMaestroInscripcion($maestroInscripcionId);
        // if(count($maestroInscripcioEntity)>0){
        //     $maestroInscripcioEntity = $maestroInscripcioEntity[0];
        //     $maestroInscripcioEntity['institucioneducativaCursoOfertaId'] = $val['institucioneducativa_curso_oferta_id'];
        // }
        // dump($maestroInscripcioEntity);
        $notaTipo = $notaTipoEntity->getNotaTipo();
        $val['institucioneducativa_curso_oferta_id'] = $institucionEducativaCursoOfertaId;
        $val['nota_tipo_id'] = $notaTipoId;
        $val['vp_id'] = $validacionProcesoId;

        // dump($val);
        // $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $val['institucioneducativa_curso_oferta_id'], 'notaTipo' => $val['nota_tipo_id']));
        // //dump($institucioneducativaCursoOfertaMaestroEntity);die;
        // if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
        //     $item = $institucioneducativaCursoOfertaMaestroEntity->getItem();
        //     $fechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-Y');
        //     $fechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-Y');
        //     $financiamientoTipoEntity = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo();
        // } else {
            $item = "";
            $fechaInicio = "";
            $fechaFin = "";
            $financiamientoTipoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(0);;
        // }
        
        $arrayMaestroInscripcionLista = array();
        foreach ($maestroInscripcionLista as $data) {
            if($data['complemento'] != ""){
                $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad']."-".$data['complemento'].")";
            } else {
                $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad'].")";
            }
        }

        $form = $this->getFormRegistroMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $arrayMaestroInscripcionLista, 0);
        $arrayFormulario = array('titulo' => "Registro / Modificación de maestro", 'detalleCursoOferta' => $detalleCursoOferta, 'listaMaestros' => $listaMaestros, 'notaTipo'=>$notaTipo, 'formNuevo'=>$form);;

        $arrayFormulario['formEnable'] = true;

        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMaestroFormulario.html.twig', $arrayFormulario);
    }

    public function getFormRegistroMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $maestroInscripcionLista, $maestroInscripcionId){
        $form = $this->createFormBuilder()
        ->add('ofertaMaestro', 'hidden', array('label' => 'Info', 'attr' => array('value' => base64_encode(json_encode($val)))))
        ->add('item', 'text', array('label' => 'Item', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $item, 'style' => 'text-transform:uppercase', 'placeholder' => 'Item' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación (ej.: 01-01-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaFin', 'text', array('label' => 'Fecha fin de asignación (ej.: 31-12-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha fin de asignación' , 'maxlength' => 10, 'required' => true)))
        ->add('maestro', 'choice', array('choices' => $maestroInscripcionLista, 'label' => 'Financiamiento', 'empty_value' => 'Seleccione Maestro', 'data' => $maestroInscripcionId, 'attr' => array('onchange' => 'dato_maestro()')))
        ->add('financiamiento', 'entity', array('data' => $financiamientoTipoEntity, 'label' => 'Financiamiento', 'empty_value' => 'Seleccione Financiamiento', 'class' => 'Sie\AppWebBundle\Entity\FinanciamientoTipo',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ft')
                        ->orderBy('ft.id', 'ASC');
            },
        ))
        ->getForm()->createView();
        return $form;
    }

    public function listaMaestroInscripcion($institucionEducativaId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
            ->select("distinct mi.id as maestroInscripcionId, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento")                
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')             
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = mi.institucioneducativa')
            ->where('ie.id = :institucionEducativaId')
            ->andWhere('mi.gestionTipo = :gestionId')
            ->andWhere('mi.cargoTipo = 0')
            ->setParameter('institucionEducativaId', $institucionEducativaId)
            ->setParameter('gestionId', $gestionId)
            ->orderBy('p.paterno', 'ASC', 'p.materno', 'ASC', 'p.nombre', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que finaliza la asignacion del maestro a un paralelo y asignatura
    // PARAMETROS: institucioneducativaCursoOfertaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroFinalizarAction(Request $request) {
        // $institucioneducativaCursoOfertaId = base64_decode($request->get('oferta'));
        // $maestroInscripcionId = base64_decode($request->get('maestro'));
        // dump($request);
        $info = json_decode(base64_decode($request->get('info')), true);
        $institucioneducativaCursoOfertaId = $info["institucioneducativa_curso_oferta_id"];  
        $notaTipoId = $info["nota_tipo_id"];  
        $validacionProcesoId = $info["vp_id"];  
        $em = $this->getDoctrine()->getManager();
        $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        if(count($validacionProcesoEntity)<=0){
            $msg = "Inconsistencia inexistente, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        
        $listaAsignacionesMaestro = $this->getInstitucionEducativaCursoOfertaNotaTipoGestion($institucioneducativaCursoOfertaId, $notaTipoId);
        
        $response = new JsonResponse();

        if(count($listaAsignacionesMaestro) > 0){
            $em->getConnection()->beginTransaction();
            try {     
                $validacionProcesoEntity->setEsActivo(true);
                $em->flush();
                $em->getConnection()->commit();
                $msg = "Observación finalizada";
                return $response->setData(array('estado'=>true, 'msg'=>$msg));
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg = "Dificultades al realizar el registro, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
        } else {            
            $msg = "No asigno ningún maestro, debe realizar la asignación para finalizar";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }       
    }

    public function getInstitucionEducativaCursoOfertaNotaTipoGestion($institucionEducativaCursoOfertaId, $notaTipoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $entity->createQueryBuilder('iecom')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.id = iecom.institucioneducativaCursoOferta')
                ->innerJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'nt.id = iecom.notaTipo')
                ->where('ieco.id = :institucionEducativaCursoOfertaId')
                ->andWhere('nt.id = :notaTipoId')
                ->setParameter('institucionEducativaCursoOfertaId', $institucionEducativaCursoOfertaId)
                ->setParameter('notaTipoId', $notaTipoId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega el fomulario para registrar o modificar la fuente de financiamiento de un maestro
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    
    //modulo imcompleto
    
    public function asignarFuenteFinanciamientoAction(Request $request) {
        $validacionProcesoId = $request->get('vp_id');
        $maestroInscripcionId = $request->get('id');
        $gestion = $request->get('gestion');
          
        $em = $this->getDoctrine()->getManager();
        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
     
        $validacionProcesoEntidad = arra();
        if($validacionProcesoId > 0){
            $validacionProcesoEntidad = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        } 
        
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct tt.id, tt.turno")
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.nivelTipo in (11,12,13)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->orderBy('tt.id', 'ASC');
        $institucioneducativaCursoOfertaMaestroEntidad = $this->get('maestroAsignacion')->listar($idCursoOferta);



        $institucionEducativaId = $maestroInscripcionEntidad->getInstitucioneducativa()->getId();
        $gestionId = $maestroInscripcionEntidad->getGestionTipo()->getId();
        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
        $turnoTipoEntidad = $this->getTurnoInstitucionEducativaCurso($institucionEducativaId,$gestionId);

        $turnoTipoArray = array();
        foreach ($turnoTipoEntidad as $data) {
            $turnoTipoArray[$data['id']] = $data['turno'];
        }

        $info = base64_encode(json_encode(array('sie'=>$institucionEducativaId, 'gestion'=>$gestionId, 'vp_id'=>$validacionProcesoId)));

        $form = $this->createFormBuilder()
                ->add('info', 'hidden', array('label' => 'Info', 'attr' => array('value' => $info)))
                ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'Seleccione Turno', 'choices' => $turnoTipoArray, 'data' => $turnoId, 'attr' => array('class' => 'form-control', 'onchange' => 'listar_nivel()', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $nivelTipoEntidad, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_grado_asignatura()')))
                ->add('grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Seleccione Grado', 'choices' => $gradoTipoEntidad, 'data' => $gradoId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_paralelo()')))
                ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccione Paralelo', 'choices' => $paraleloTipoEntidad, 'data' => $paraleloId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_asignatura_curso()')))
                ->add('asignatura', 'choice', array('label' => 'Asignatura', 'empty_value' => 'Seleccione Asignatura', 'choices' => $asignaturaTipoEntidad, 'data' => $asignaturaId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_curso_asignatura()')))
                ->add('buscar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'style' => 'display: none;')))
                ->getForm()->createView();
        
        return $this->render('SieRegularBundle:MaestroAsignacion:fuenteFinanciamientoFormulario.html.twig',array(
            'form' => $form
        ));
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que despliega las asignaciones de un maestro
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroListaAction(Request $request) {
        $validacionProcesoId = $request->get('vp_id');
        $llave = explode("|",$request->get('id'));

        $maestroInscripcionId = 0;
        if (count($llave) == 2){
            $maestroInscripcionId = $llave[0];
        }               
          
        $institucioneducativaCursoOfertaMaestro = array();
        if ($maestroInscripcionId != 0){
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
            $query = $entity->createQueryBuilder('iecom')
                    ->select("iecom.id, tt.turno, nt.nivel, gt.grado, pt.paralelo, at.asignatura, ntt.abrev as nota, ntt.id as notaId, ieco.id as institucioneducativaCursoOfertaId")
                    ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.id = iecom.maestroInscripcion')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.id = iecom.institucioneducativaCursoOferta')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ieco.insitucioneducativaCurso')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'at.id = ieco.asignaturaTipo')
                    ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                    ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = iec.nivelTipo')
                    ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                    ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                    ->innerJoin('SieAppWebBundle:NotaTipo', 'ntt', 'WITH', 'ntt.id = iecom.notaTipo')
                    ->where('mi.id = :maestroInscripcionId')
                    ->setParameter('maestroInscripcionId', $maestroInscripcionId)
                    ->orderBy('tt.id, nt.id, gt.id, pt.id, ntt.id, at.id', 'ASC');
            $institucioneducativaCursoOfertaMaestro = $query->getQuery()->getResult();
        }

        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
        $sie = $maestroInscripcionEntidad->getInstitucioneducativa()->getId();
        $gestion = $maestroInscripcionEntidad->getGestionTipo()->getId();
        $lista = array();
        $cabecera = array('Turno', 'Nivel', 'Año de Escolaridad', 'Paralelo', 'Asignatura');
        $contenido = array();
        foreach ($institucioneducativaCursoOfertaMaestro as $data) {
            $contenido[$data['turno']][$data['nivel']][$data['grado']][$data['paralelo']][$data['asignatura']][$data['nota']] = base64_encode(json_encode(array('institucioneducativa_curso_oferta_id'=>$data['institucioneducativaCursoOfertaId'], 'nota_tipo_id'=>$data['notaId'], 'institucioneducativa_curso_oferta_maestro_id'=>$data['id'])));
            array_push($cabecera,$data['nota']);
        }
        $cabecera = array_unique($cabecera);
        $lista = array('cabecera'=>$cabecera,'contenido'=>$contenido);
        $info = base64_encode(json_encode(array('sie'=>$sie,'gestion'=>$gestion,'vp_id'=>$validacionProcesoId)));
                
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMaestroLista.html.twig',array(
            'lista' => $lista,
            'info' => $info
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega el fomulario para registrar o modificar la asignacion de un maestro de la gestion actual
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaIndexAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        
        $gestionId = $fechaActual->format('Y');
        // $gestionId = 2019;
        
        $turnoId = null;
        $nivelId = null;
        $gradoId = null;
        $paraleloId = null;
        $asignaturaId = null;
        $turnoTipoEntidad = array();
        $nivelTipoEntidad = array();
        $gradoTipoEntidad = array();
        $paraleloTipoEntidad = array();
        $asignaturaTipoEntidad = array();
  
        $rolId = $this->session->get('roluser');
        $institucionEducativaId = 0;
        if($rolId == 9){
            $institucionEducativaId = $this->session->get('ie_id');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $turnoTipoEntidad = $this->getTurnoInstitucionEducativaCurso($institucionEducativaId,$gestionId);

        $turnoTipoArray = array();
        foreach ($turnoTipoEntidad as $data) {
            $turnoTipoArray[$data['id']] = $data['turno'];
        }
              
        $info = base64_encode(json_encode(array('sie'=>$institucionEducativaId, 'gestion'=>$gestionId)));
        $form = $this->createFormBuilder()
                ->add('info', 'hidden', array('label' => 'Info', 'attr' => array('value' => $info)))
                ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'Seleccione Turno', 'choices' => $turnoTipoArray, 'data' => $turnoId, 'attr' => array('class' => 'form-control', 'onchange' => 'listar_nivel()', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $nivelTipoEntidad, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_grado_asignatura()')))
                ->add('grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Seleccione Grado', 'choices' => $gradoTipoEntidad, 'data' => $gradoId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_paralelo()')))
                ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccione Paralelo', 'choices' => $paraleloTipoEntidad, 'data' => $paraleloId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_asignatura_curso()')))
                ->add('asignatura', 'choice', array('label' => 'Asignatura', 'empty_value' => 'Seleccione Asignatura', 'choices' => $asignaturaTipoEntidad, 'data' => $asignaturaId, 'attr' => array('class' => 'form-control', 'style' => 'display: none;', 'required' => true, 'onchange' => 'listar_curso_asignatura()')))
                ->add('buscar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'style' => 'display: none;')))
                ->getForm()->createView();
        
        return $this->render('SieAppWebBundle:MaestroAsignacion:asignacionMateriaIndex.html.twig',array(
            'form' => $form
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que despliega las asignaciones de un maestro
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaMaestroDatoAction(Request $request) {
        $form = $request->get('form');
        if(isset($form)){
            $maestroInscripcionId = base64_decode($form['maestro']);
        }         

        $response = new JsonResponse();
        
        $maestroInscripcmaestroInscripcionEntidadionEntity = array();
        if ($maestroInscripcionId != 0){
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
            $query = $entity->createQueryBuilder('mi')
                    ->select("mi.id, mi.item, ft.id as financiamientoId, ft.financiamiento, gt.id as gestionId")
                    ->innerJoin('SieAppWebBundle:FinanciamientoTipo', 'ft', 'WITH', 'ft.id = mi.financiamientoTipo')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = mi.gestionTipo')
                    ->where('mi.id = :maestroInscripcionId')
                    ->setParameter('maestroInscripcionId', $maestroInscripcionId)
                    ->orderBy('mi.id', 'DESC');
            $maestroInscripcionEntidad = $query->getQuery()->getResult();
            if (count($maestroInscripcionEntidad)>0){
                $maestroInscripcionEntidad = $maestroInscripcionEntidad[0];
                $gestionId = $maestroInscripcionEntidad['gestionId'];
                $item = $maestroInscripcionEntidad['item'];
                $financiamientoId = $maestroInscripcionEntidad['financiamientoId'];
                $financiamiento = $maestroInscripcionEntidad['financiamiento'];
                $fechaInicio = "01-01-".$gestionId;
                $fechaFin = "31-12-".$gestionId;
                $dato = array('gestionId'=>$gestionId,'item'=>$item,'financiamientoId'=>$financiamientoId,'financiamiento'=>$financiamiento,'fechaInicio'=>$fechaInicio,'fechaFin'=>$fechaFin);
                return $response->setData(array('estado'=>true, 'msg'=>"", 'maestro'=>$dato));  
            } 
        }

        return $response->setData(array('estado'=>false, 'msg'=>"No existe datos del maestro"));              
                
    }

    
}
