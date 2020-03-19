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

    public function asignarMaestroAction(Request $request){
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
        $validacionProcesoId = $info["vp_id"];
                
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
        $validacionProcesoId = $info["vp_id"];
                
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
        $validacionProcesoId = $info["vp_id"];
                
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
        $validacionProcesoId = $info["vp_id"];

        $listaAsignatura = $this->getCursoInstitucionEducativaCursoTurnoNivelAsignatura($institucionEducativaId, $gestionId, $turnoId, $nivelId, $asignaturaId);
       
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaDetalle.html.twig', array('listaCurso' => $listaAsignatura));
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
        //dump($entity);die;

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
        $validacionProcesoId = $info["vp_id"];
        $listaCurso = $this->getAsignaturaInstitucionEducativaCursoTurnoNivelGradoParalelo($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId, $paraleloId);
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaDetalle.html.twig', array('listaAsignatura' => $listaCurso));
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
        dump($info);dump($listaMaestros);dump($detalleCursoOferta);
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        $validacionProcesoId = $info["vp_id"];  
        dump($info);
        $em = $this->getDoctrine()->getManager();
        $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($val['nota_tipo_id']);
        $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        if(count($validacionProcesoEntity)>0){
            $maestroInscripcionId = $validacionProcesoEntity->getLlave();
        }
        $maestroInscripcioEntity = $this->getMaestroInscripcion($maestroInscripcionId);
        if(count($maestroInscripcioEntity)>0){
            $maestroInscripcioEntity = $maestroInscripcioEntity[0];
            $maestroInscripcioEntity['institucioneducativaCursoOfertaId'] = $val['institucioneducativa_curso_oferta_id'];
        }
        dump($maestroInscripcioEntity);
        $notaTipo = $notaTipoEntity->getNotaTipo();
        $val['maestro_inscripcion_id'] = $maestroInscripcionId;

        dump($val);
        $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $val['institucioneducativa_curso_oferta_id'], 'notaTipo' => $val['nota_tipo_id']));
        //dump($institucioneducativaCursoOfertaMaestroEntity);die;
        if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
            $item = $institucioneducativaCursoOfertaMaestroEntity->getItem();
            $fechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-Y');
            $fechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-Y');
            $financiamientoTipoEntity = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo();
        } else {
            $item = "";
            $fechaInicio = "";
            $fechaFin = "";
            $financiamientoTipoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(0);;
        }
        $form = $this->getFormOrfertaMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity);
        $arrayFormulario = array('titulo' => "Registro / Modificación de docente", 'detalleCursoOferta' => $detalleCursoOferta, 'listaMaestros' => $listaMaestros, 'notaTipo'=>$notaTipo, 'casoMaestro'=>$maestroInscripcioEntity, 'formNuevo'=>$form);;
        if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
            $arrayFormulario['formEnable'] = false;
        } else {
            $arrayFormulario['formEnable'] = true;
        }
        
        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaFormulario.html.twig', $arrayFormulario);
    }

    public function getFormOrfertaMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity){
        $form = $this->createFormBuilder()
        ->add('ofertaMaestro', 'hidden', array('label' => 'Info', 'attr' => array('value' => base64_encode(json_encode($val)))))
        ->add('item', 'text', array('label' => 'Item', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $item, 'style' => 'text-transform:uppercase', 'placeholder' => 'Item' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaFin', 'text', array('label' => 'Fecha fin de asignación', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha fin de asignación' , 'maxlength' => 10, 'required' => true)))
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
                ->select("distinct iecom.id as institucioneducativaCursoOfertaMaestroId, nota.notaTipo, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento, ft.financiamiento, ft.id as financiamientoId, iecom.asignacionFechaInicio, iecom.asignacionFechaFin, iecom.item")                
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
        $maestroInscripcionId = $info['maestro_inscripcion_id'];
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
            $maestroAsignado = array('maestroOferta'=>$maestroAsignadoId,'maestroItem'=>$maestroAsignadoItem,'maestroNombre'=>$maestroAsignadoNombre,'maestroPaterno'=>$maestroAsignadoPaterno,'maestroMaterno'=>$maestroAsignadoMaterno,'maestroCI'=>$maestroAsignadoCI,'maestroFinanciamientoId'=>$maestroAsignadoFinanciamientoId,'maestroFinanciamiento'=>$maestroAsignadoFinanciamiento,'maestroFechaInicio'=>$maestroAsignadoFechaInicio,'maestroFechaFin'=>$maestroAsignadoFechaFin);
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
            $em->getConnection()->beginTransaction();
            try {                
                $em->remove($institucioneducativaCursoOfertaMaestroEntity);
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
        // dump($institucioneducativaCursoOfertaMaestroEntity);

        $institucioneducativaCursoOfertaId = $institucioneducativaCursoOfertaMaestroEntity->getInstitucioneducativaCursoOferta()->getId();
        $notaTipoId = $institucioneducativaCursoOfertaMaestroEntity->getNotaTipo()->getId();
        
        $listaMaestros = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId, 'notaTipo' => $notaTipoId), array('asignacionFechaInicio'=>'asc'));
        
        // dump($listaMaestros);

        $validacionFechaSecuencial = $this->getValidacionFechaSecuencial($listaMaestros, $gestionId, $fechaInicio, $fechaFin, $institucioneducativaCursoOfertaMaestroId);
        if(!$validacionFechaSecuencial['estado']){
            return $response->setData(array('estado'=>$validacionFechaSecuencial['estado'], 'msg'=>$validacionFechaSecuencial['msg']));
        }
        //$horasMes = $institucioneducativaCursoOfertaMaestroEntity->getInstitucioneducativaCursoOferta()->getHorasmes();

        //dump($financiamientoTipoEntity);dump($institucioneducativaCursoOfertaMaestroEntity);die;

        $em->getConnection()->beginTransaction();
        try {                            
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
            $maestroAsignado = array('maestroOferta'=>$maestroAsignadoId,'maestroItem'=>$maestroAsignadoItem,'maestroNombre'=>$maestroAsignadoNombre,'maestroPaterno'=>$maestroAsignadoPaterno,'maestroMaterno'=>$maestroAsignadoMaterno,'maestroCI'=>$maestroAsignadoCI,'maestroFinanciamientoId'=>$maestroAsignadoFinanciamientoId,'maestroFinanciamiento'=>$maestroAsignadoFinanciamiento,'maestroFechaInicio'=>$maestroAsignadoFechaInicio,'maestroFechaFin'=>$maestroAsignadoFechaFin);
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

}
