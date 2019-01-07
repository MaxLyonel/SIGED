<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\EstudianteNotaSolicitud;
use Sie\AppWebBundle\Entity\EstudianteNotaSolicitudDetalle;

/**
 * EstudianteInscripcion controller.
 *
 */
class SolicitudModificacionCalificacionesController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct(){
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $idUsuario = $this->session->get('userId');
            $rolUsuario = $this->session->get('roluser');

            if($rolUsuario == 7){ // PAra departamentos
                $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario'=>$idUsuario,'rolTipo'=>$rolUsuario));
                $lugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($usuarioRol->getLugarTipo()->getId());
                $idDepartamento = $lugarTipo->getCodigo();


                $solicitudes = $em->createQueryBuilder()
                                ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                          e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                                ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                ->where('enm.tipo = 1')
                                ->andWhere('enm.departamentoTipoId = :departamento')
                                ->setParameter('departamento',$idDepartamento)
                                ->orderBy('enm.id','DESC')
                                ->getQuery()
                                ->getResult();

                if(isset($_GET['filterValue']) and $_GET['filterValue'] != '' and $_GET['filterField'] != 'all'){
                    $campo = $_GET['filterField'];
                    switch ($campo) {
                        case 'id':
                            $solicitudes = $em->createQueryBuilder()
                                ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                          e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                                ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                ->where('enm.tipo = 1')
                                ->andWhere('enm.departamentoTipoId = :departamento')
                                ->andWhere('enm.id = :idSolicitud')
                                ->setParameter('departamento',$idDepartamento)
                                ->setParameter('idSolicitud',$_GET['filterValue'])
                                ->orderBy('enm.id','DESC')
                                ->getQuery()
                                ->getResult();
                            break;
                        case 'ie':
                            $solicitudes = $em->createQueryBuilder()
                                ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                          e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                                ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                ->where('enm.tipo = 1')
                                ->andWhere('enm.departamentoTipoId = :departamento')
                                ->andWhere('enm.institucioneducativaId = :institucion')
                                ->setParameter('departamento',$idDepartamento)
                                ->setParameter('institucion',$_GET['filterValue'])
                                ->orderBy('enm.id','DESC')
                                ->getQuery()
                                ->getResult();
                            break;
                        
                        default:
                            $solicitudes = null;
                            break;
                    }
                }

            }else{ // PAra nacional
                if($rolUsuario == 8){
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
                }else{ // Para otros usuarios
                    $solicitudes = $em->createQueryBuilder()
                                ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                          e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                                ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                ->where('enm.tipo = 1')
                                ->andWhere('ie.id = :idInstitucion')
                                // ->andWhere('enm.usuarioId = :user')
                                // ->setParameter('user',$idUsuario)
                                ->setParameter('idInstitucion',$this->session->get('ie_id'))
                                ->orderBy('enm.id','DESC')
                                ->getQuery()
                                ->getResult();
                }

            }
            $em->getConnection()->commit();

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $solicitudes,
                $request->query->getInt('page',1),
                10
            );


            return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:index.html.twig',array('solicitudes'=>$pagination));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }

    }

    public function solicitudesEstudianteAction(Request $request){
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $valor = $form['rudeOSolicitud'];
        $tipo = null;
        if(is_numeric($valor)){
            if($valor > 80000000){
                $tipo = 'rude';
                $valor = mb_strtoupper($valor,'utf-8');
            }else{
                $tipo = 'solicitud';
            }
        }else{
            $tipo = 'rude';
            $valor = mb_strtoupper($valor,'utf-8');
        }
        if($tipo == 'rude'){
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$valor));
            if($estudiante){
                // Hacemos la busqueda por codigo rude
                $solicitudes = $em->createQueryBuilder()
                                ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                          e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                                ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                ->where('enm.tipo = 1')
                                ->andWhere('enm.detalle LIKE :rude')
                                ->orderBy('enm.id','DESC')
                                ->setParameter('rude', '%'.$valor.'%')
                                ->getQuery()
                                ->getResult();
            }else{
                $this->get('session')->getFlashBag()->add('solicitudEstudianteError', 'El Código Rude ingresado no es válido');
                return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
            }
        }else{
            // Hacemos la busqueda por id de solicitud
            $solicitudes = $em->createQueryBuilder()
                            ->select('enm.id,enm.remitente,enm.receptor,enm.fecha,enm.hora,enm.detalle,enm.motivo,enm.estado,enm.estudianteInscripcionId,
                                      e.paterno,e.materno,e.nombre,e.codigoRude,nt.nivel,gt.grado,pt.paralelo, ie.institucioneducativa, enm.gestionTipoId, enm.institucioneducativaId')
                            ->from('SieAppWebBundle:EstudianteNotaSolicitud','enm')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','enm.estudianteInscripcionId = ei.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','WITH','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                            ->where('enm.tipo = 1')
                            ->andWhere('enm.id = :idSolicitud')
                            ->orderBy('enm.id','DESC')
                            ->setParameter('idSolicitud',$valor)
                            ->getQuery()
                            ->getResult();
            if(!$solicitudes){
                $this->get('session')->getFlashBag()->add('solicitudEstudianteError', 'El número de solicitud de modificaciòn no existe');
                return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $solicitudes,
            $request->query->getInt('page',1),
            10
        );

        return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:index.html.twig',array('solicitudes'=>$pagination));

    }

    public function formSolicitudesEstudiante(){
        return $this->createFormBuilder()
                            ->setAction($this->generateUrl('solicitudesEstudiante'))
                            ->add('rudeOSolicitud','text',array('label'=>'Código Rude / Nro de Solicitud','data'=>'','attr'=>array('class'=>'form-control jnumbersletters jupper','placeholder'=>'Ingrese el Código Rude o Número de solicitud','pattern'=>'[0-9a-zA-Z]{1,20}','autocomplete'=>'off','maxlength'=>'20','autofocus'=>true)))
                            ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                            ->getForm();
    }

    public function searchAction(Request $request, $op) {
        try {
            // generar los titulos para los diferentes sistemas
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');

            // Validamos que el usuario sea director o tecnico nacional o administrativo
            $rolUsuario = $request->getSession()->get('roluser');
            $userName = $request->getSession()->get('userName');
            if($rolUsuario != 5 and $rolUsuario != 9 and $rolUsuario != 8){
                if($rolUsuario == 7 and ($userName == '8955627' or $userName == '3468944')){

                }else{
                    return $this->redirectToRoute('solicitudModificacionCalificaciones');
                }
            }

            ////////////////////////////////////////////////////
            $rolUsuario = $this->session->get('roluser');

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
                // Verificamos si existe el estudiante
                if(!$estudiante){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante con el código ingresado no existe!');
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

                // Verificamos si la unidad educativa tiene tucion sobre el sie ingresado
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['idInstitucion']);
                $query->bindValue(':roluser', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();
                if($aTuicion[0]['get_ue_tuicion'] == false){
                    $this->get('session')->getFlashBag()->add('noSearch', 'No tiene tuicion sobre el código Sie ingresado!');
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

                // Verificamos si el estudiante tiene inscripcion en la unidad educativa y gestion seleccionada
                $inscripcion = $em->createQuery(
                    'SELECT ei.id, iec.id as idCurso, nt.id as idNivel, gt.id as idGrado
                    FROM SieAppWebBundle:EstudianteInscripcion ei
                    LEFT JOIN ei.estudiante e
                    LEFT JOIN ei.institucioneducativaCurso iec
                    LEFT JOIN iec.nivelTipo nt
                    LEFT JOIN iec.gradoTipo gt
                    LEFT JOIN ei.estadomatriculaTipo emt
                    WHERE e.codigoRude = :rude
                    AND iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND emt.id IN (:idMatricula)')
                    ->setParameter('rude',$form['codigoRude'])
                    ->setParameter('idInstitucion',$form['idInstitucion'])
                    ->setParameter('gestion',$form['gestion'])
                    ->setParameter('idMatricula',array(4,5,11,55,26))
                    ->getResult();

                if(!$inscripcion){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con inscripcion en la unidad educativa y la gestion seleccionada');
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                $idInscripcion = $inscripcion[0]['id'];
                $this->session->set('idInscripcion', $idInscripcion);
                $this->session->set('idEstudiante', $estudiante->getId());
                $this->session->set('idGestion',$form['gestion']);
                $this->session->set('idInstitucion',$form['idInstitucion']);
                $idEstudiante = $estudiante->getId();
                $gestion = $form['gestion'];
                $idInstitucion = $form['idInstitucion'];
            } else {
                /*
                 * Verificamos si se tiene que mostrar el formulario de busqueda
                 */
                if($op == 'search'){
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * Verificar si existe la session de la persona y gestion
                 */
                if($this->session->get('idEstudiante')){
                    $idEstudiante = $this->session->get('idEstudiante');
                    $gestion = $this->session->get('idGestion');
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
                    $idInstitucion = $this->session->get('idInstitucion');
                    $idInscripcion = $this->session->get('idInscripcion');
                }else{
                    return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }

            //VAlidamos que la gestion sea del 2010 para arriba
            if($gestion < 2008 or $gestion > 2018){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden hacer modificacion de calificaciones entre las gestiones 2008 a 2018!');
                return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            // Obtenemos el curso en el que esta inscrito el estudiante
            $curso = $em->createQueryBuilder()
                        ->select('iec.id, nt.id as idNivel, gt.id as idGrado')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion',$idInscripcion)
                        ->getQuery()
                        ->getResult();

            $nivel = $curso[0]['idNivel'];
            /**
             * Obtenemos la cantidad de notas
             */
            switch($gestion){
                case 2008:
                case 2009:
                case 2010:
                case 2011:
                case 2012:
                            $tipo_nota = 'Trimestre';
                            $cantidad_notas = 3;
                            $valor_inicial = 6;
                            $valor_final = 8;
                            break;
                case 2013:  switch($curso[0]['idGrado']){
                                case 1: $tipo_nota = 'Bimestre';
                                        $cantidad_notas = 4;
                                        $valor_inicial = 1;
                                        $valor_final = 4;
                                        break;
                                default:
                                        $tipo_nota = 'Trimestre';
                                        $cantidad_notas = 3;
                                        $valor_inicial = 6;
                                        $valor_final = 8;
                                        break;

                            }
                            break;
                case 2014:
                case 2015:
                case 2016:
                case 2017:  
                case 2018:  
                            if ($gestion == $this->session->get('currentyear')) {
                                $tipo_nota = 'Bimestre';
                                $cantidad_notas = 4;
                                $valor_inicial = 1;
                                $valor_final = $this->get('funciones')->obtenerOperativo($idInstitucion,$gestion) - 1;
                                if ($valor_final < 0) {
                                    $valor_final = 0;
                                }
                            }else{
                                $tipo_nota = 'Bimestre';
                                $cantidad_notas = 4;
                                $valor_inicial = 1;
                                $valor_final = 4;
                            }
                            break;
            }

            vuelta:

            /*
             * Listamos las asignaturas del estudiante
             */
            $asignaturas = $em->createQueryBuilder()
                                        ->select('at.id, at.asignatura, ei.id as idEstudianteInscripcion, ea.id as idEstudianteAsignatura, ieco.id as idCursoOferta')
                                        ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                                        ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                        ->where('ei.id = :idInscripcion')
                                        ->orderBy('at.id','ASC')
                                        ->setParameter('idInscripcion',$idInscripcion)
                                        ->getQuery()
                                        ->getResult();
            /* Verificamos si la cantidad de materias del estudiante es igual al del curso */
            $asignaturasCurso = $em->createQuery(
                    'SELECT DISTINCT at.id, ieco.id as idCursoOferta
                    FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.insitucioneducativaCurso iec
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE iec.id = :idCurso')
                    ->setParameter('idCurso', $curso[0]['id'])
                    ->getResult();

            $arrayAsignaturasEstudiante = array();


            if(count($asignaturas) != count($asignaturasCurso)){
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['id'];
                }


                
                // Registramos las areas del estudiante
                foreach ($asignaturasCurso as $ac) {
                    // Preguntamos si la materia del curso ya la tiene el estudiante
                    if(!in_array($ac['id'], $arrayAsignaturasEstudiante)){
                        // Reiniciar el primary key de las tabla estudiante_asignatura
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        $newAsignatura = new EstudianteAsignatura();
                        $newAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newAsignatura->setFechaRegistro(new \DateTime('now'));
                        $newAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ac['idCursoOferta']));
                        $em->persist($newAsignatura);
                        
                        $em->flush();

                        //dump($newAsignatura);die;
                    }//die('adsf');

                }
                goto vuelta;

            }else if(count($asignaturasCurso) == 0){
                $arrayAsignaturasEstudiante = array(1);
            }

            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $em->createQuery(
                                        'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, en.notaCualitativa, nt.notaTipo as notaTipo, nt.id as idNotaTipo, ea.id as idEstudianteAsignatura
                                        FROM SieAppWebBundle:EstudianteNota en
                                        JOIN en.notaTipo nt
                                        JOIN en.estudianteAsignatura ea
                                        JOIN ea.estudianteInscripcion ei
                                        JOIN ea.institucioneducativaCursoOferta ieco
                                        JOIN ieco.asignaturaTipo at
                                        WHERE ei.id = :idInscripcion
                                        AND at.id = :idAsignatura
                                        ORDER BY at.id, nt.id')
                                        ->setParameter('idInscripcion',$a['idEstudianteInscripcion'])
                                        ->setParameter('idAsignatura',$a['id'])
                                        ->getResult();
                switch ($tipo_nota) {
                    case 'Bimestre':
                        $cont2 = 0;
                        // Notas de 1er a cuarto Bimestre
                        for($i=$valor_inicial;$i<=$valor_final;$i++){
                            $cont2 = 0;
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($nivel != 11){
                                    $valorNota = $an['notaCuantitativa'];
                                }else{
                                    $valorNota = $an['notaCualitativa'];
                                }
                                if($i == $an['idNotaTipo']){
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$valorNota,
                                                            'idNotaTipo'=>$i,
                                                            'idFila'=>$an['id'].''.$i,
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                        );
                                    $existe = 'si';
                                    $cont2++;
                                }

                            }
                            if($existe == 'no'){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>$this->literal($i)['titulo'],
                                                            'nota'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idFila'=>$a['id'].''.$i,
                                                            'idEstudianteAsignatura'=>$a['idEstudianteAsignatura']
                                                        );
                                $cont2++;
                            }
                        }
                        if($nivel != 11){
                            // Para el promedio
                            foreach ($asignaturasNotas as $an) {
                                $existe = 'no';
                                if($an['idNotaTipo'] == 5){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$cont2,
                                                                'idEstudianteNota'=>'promedio',
                                                                'bimestre'=>$an['notaTipo'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'idNotaTipo'=>5,
                                                                'idFila'=>$an['id'].'5',
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                            );
                                    $existe = 'si';
                                }
                            }
                            if($existe == 'no'){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>'promedio',
                                                            'bimestre'=>'Promedio',
                                                            'nota'=>'',
                                                            'idNotaTipo'=>5,
                                                            'idFila'=>$a['id'].'5',
                                                            'idEstudianteAsignatura'=>$a['idEstudianteAsignatura']
                                                        );
                                $cont2++;
                            }
                        }

                        break;

                    case 'Trimestre':
                        if($nivel != 11 and $nivel != 1 and $nivel != 403){
                            $tn = array(30,27,6,31,28,7,32,29,8,9,10,11);
                            // Notas de 1er a 3er trimestre
                            for($i=0;$i<count($tn);$i++){

                                $cont2 = 0;
                                $existe = 'no';
                                foreach ($asignaturasNotas as $an) {
                                    if($nivel != 11){
                                        $valorNota = $an['notaCuantitativa'];
                                    }else{
                                        $valorNota = $an['notaCualitativa'];
                                    }
                                    if($tn[$i] == $an['idNotaTipo']){
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$cont2,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'bimestre'=> $this->literal($tn[$i])['abrev'],
                                                                'nota'=>$valorNota,
                                                                'idNotaTipo'=>$tn[$i],
                                                                'idFila'=>$an['id'].''.$tn[$i],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                            );
                                        $existe = 'si';

                                    }
                                    $cont2++;
                                }
                                if($existe == 'no'){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$tn[$i],
                                                                'idEstudianteNota'=>'ninguno',
                                                                'bimestre'=>$this->literal($tn[$i])['abrev'],
                                                                'nota'=>'',
                                                                'idNotaTipo'=>$tn[$i],
                                                                'idFila'=>$a['id'].''.$tn[$i],
                                                                'idEstudianteAsignatura'=>$a['idEstudianteAsignatura']
                                                            );
                                    $cont2++;
                                }
                            }
                        }else{
                            for($i=6;$i<=8;$i++){
                                $cont2 = 0;
                                $existe = 'no';
                                foreach ($asignaturasNotas as $an) {
                                    if($nivel != 11){
                                        $valorNota = $an['notaCuantitativa'];
                                    }else{
                                        $valorNota = $an['notaCualitativa'];
                                    }
                                    if($i == $an['idNotaTipo']){
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$cont2,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'bimestre'=>$this->literal($tn[$i]['abrev']),
                                                                'nota'=>$valorNota,
                                                                'idNotaTipo'=>$i,
                                                                'idFila'=>$an['id'].''.$i,
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                            );
                                        $existe = 'si';

                                    }
                                    $cont2++;
                                }
                                if($existe == 'no'){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'idEstudianteNota'=>'ninguno',
                                                                'bimestre'=>$this->literal($i)['abrev'],
                                                                'nota'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idFila'=>$a['id'].''.$i,
                                                                'idEstudianteAsignatura'=>$a['idEstudianteAsignatura']
                                                            );
                                    $cont2++;
                                }
                            }
                        }/*
                        // Para el promedio anual
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            if($an['idNotaTipo'] == 9){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'idNotaTipo'=>9,
                                                            'idFila'=>$an['id'].'9'
                                                        );
                                $existe = 'si';
                                $cont2++;
                            }
                        }
                        if($existe == 'no'){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$cont2,
                                                        'idEstudianteNota'=>'ninguno',
                                                        'bimestre'=>'Promedio Anual',
                                                        'nota'=>'',
                                                        'idNotaTipo'=>9,
                                                        'idFila'=>$a['id'].'9'
                                                    );
                            $cont2++;
                        }
                        // Para el reforzamiento
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            if($an['idNotaTipo'] == 10){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'idNotaTipo'=>10,
                                                            'idFila'=>$an['id'].'10'
                                                        );
                                $existe = 'si';
                                $cont2++;
                            }
                        }
                        if($existe == 'no'){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$cont2,
                                                        'idEstudianteNota'=>'ninguno',
                                                        'bimestre'=>'Reforzamiento',
                                                        'nota'=>'',
                                                        'idNotaTipo'=>10,
                                                        'idFila'=>$a['id'].'10'
                                                    );
                            $cont2++;
                        }
                        // Para el promedio final
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            if($an['idNotaTipo'] == 11){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'idNotaTipo'=>11,
                                                            'idFila'=>$an['id'].'11'
                                                        );
                                $existe = 'si';
                                $cont2++;
                            }
                        }
                        if($existe == 'no'){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$cont2,
                                                        'idEstudianteNota'=>'ninguno',
                                                        'bimestre'=>'Promedio Final',
                                                        'nota'=>'',
                                                        'idNotaTipo'=>11,
                                                        'idFila'=>$a['id'].'11'
                                                    );
                            $cont2++;
                        }*/
                        break;
                    default:
                        # code...
                        break;
                }
                $cont++;
            }

            $idInscripcionEstudiante = $idInscripcion;

            if(!$notasArray){
                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, debe realizar la adicion de areas y calificaciones.');
                return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }


            // Verificamos si el mismo estudiante tiene una solicitud pendiente
            $solicitudPendiente = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->findOneBy(array('estudianteInscripcionId'=>$idInscripcionEstudiante,'estado'=>1,'tipo'=>1));
            if($solicitudPendiente){
                $this->get('session')->getFlashBag()->add('sendError', 'Ya hay una solicitud pendiente (S-'.$solicitudPendiente->getId().') para el estudiante, debe esperar a que se responda la solicitud anterior');
                return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y obtenemos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcionEstudiante);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();

            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            $titulos = $notasArray[0]['notas'];

            for($i=0;$i<count($titulos);$i++){
                $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
            }
            //dump($notasArray);
            //print_r($titulos_notas);die;
            /*
            // Creamos la variable de session del nivel
            */
            $this->session->set('idNivel',$idNivel);

            /**
             * Listamos las valoraciones cualitativas
             */
            if($nivel != 11 and $nivel != 1 and $nivel != 403){
                // Para niveles primaria y secundaria
                $cualitativas = array();
                for($i=$valor_inicial;$i<=$valor_final;$i++){
                    $notaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('notaTipo'=>$i,'estudianteInscripcion'=>$idInscripcion));
                    if($notaCualitativa){
                        $cualitativas[] = array('id'=>'cuant-'.$i,'idEstudianteNota'=>$notaCualitativa->getId(),'idEstudianteInscripcion'=>$idInscripcion,'bimestre'=>$notaCualitativa->getNotaTipo()->getNotaTipo(),'notaCualitativa'=>$notaCualitativa->getNotaCualitativa(),'idFila'=>$idInscripcion.''.$i,'notaTipo'=>$i);
                    }else{
                        $notaTipos = $em->createQueryBuilder()
                                        ->select('nt.notaTipo')
                                        ->from('SieAppWebBundle:NotaTipo','nt')
                                        ->where('nt.id = :id')
                                        ->setParameter('id',$i)
                                        ->getQuery()
                                        ->getSingleResult();

                        $notaTipos = $notaTipos['notaTipo'];
                        $cualitativas[] = array('id'=>'cuant-'.$i,'idEstudianteNota'=>'ninguno','idEstudianteInscripcion'=>$idInscripcion,'bimestre'=>$notaTipos,'notaCualitativa'=>'','idFila'=>$idInscripcion.''.$i,'notaTipo'=>$i);
                    }
                }
            }else{
                $cualitativas = array();

                $notaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('notaTipo'=>18,'estudianteInscripcion'=>$idInscripcion));
                if($notaCualitativa){
                    if($notaCualitativa->getNotaCualitativa() == ""){
                        $em->remove($notaCualitativa);
                        $em->flush();
                        $notaTipos = $em->createQueryBuilder()
                                    ->select('nt.notaTipo')
                                    ->from('SieAppWebBundle:NotaTipo','nt')
                                    ->where('nt.id = :id')
                                    ->setParameter('id',18)
                                    ->getQuery()
                                    ->getSingleResult();

                        $notaTipos = $notaTipos['notaTipo'];
                        $cualitativas[] = array('id'=>'cuant-18','idEstudianteNota'=>'ninguno','idEstudianteInscripcion'=>$idInscripcion,'bimestre'=>$notaTipos,'notaCualitativa'=>'','idFila'=>$idInscripcion.'18','notaTipo'=>18);
                    }else{
                        $cualitativas[] = array('id'=>'cuant-18','idEstudianteNota'=>$notaCualitativa->getId(),'idEstudianteInscripcion'=>$idInscripcion,'bimestre'=>$notaCualitativa->getNotaTipo()->getNotaTipo(),'notaCualitativa'=>$notaCualitativa->getNotaCualitativa(),'idFila'=>$idInscripcion.'18','notaTipo'=>18);
                    }
                }else{
                    $notaTipos = $em->createQueryBuilder()
                                    ->select('nt.notaTipo')
                                    ->from('SieAppWebBundle:NotaTipo','nt')
                                    ->where('nt.id = :id')
                                    ->setParameter('id',18)
                                    ->getQuery()
                                    ->getSingleResult();

                    $notaTipos = $notaTipos['notaTipo'];
                    $cualitativas[] = array('id'=>'cuant-18','idEstudianteNota'=>'ninguno','idEstudianteInscripcion'=>$idInscripcion,'bimestre'=>$notaTipos,'notaCualitativa'=>'','idFila'=>$idInscripcion.'18','notaTipo'=>18);
                }

            }

            // Obtenemos el nombre del usuario
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId'));
            $nombreUsuario = $usuario->getPersona()->getPaterno()." ".$usuario->getPersona()->getMaterno()." ".$usuario->getPersona()->getNombre();

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:new.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso,
                        'cualitativas'=>$cualitativas,
                        'idEstudianteInscripcion'=>$idInscripcionEstudiante,
                        'nombreUsuario'=>$nombreUsuario
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
            echo 'Error';
        }
    }

    function literal($idNota){
        $em = $this->getDoctrine()->getManager();
        $tipoNota = $em->getRepository('SieAppWebBundle:NotaTipo')->find($idNota);
        $bim = array('titulo'=>$tipoNota->getNotaTipo(),'abrev'=>$tipoNota->getAbrev());
        
        return $bim;
    }

    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        $gestiones = array();
        for($i=2008;$i<=$gestion;$i++){
            $gestiones[$i] = $i;
        }

        $rolUsuario = $this->session->get('roluser');
        if($rolUsuario == 8){
            return $this->createFormBuilder()
                ->setAction($this->generateUrl('solicitudModificacionCalificaciones_search'))
                ->add('codigoRude','text',array('label'=>'Código Rude','data'=>'','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                ->add('idInstitucion','text',array('label'=>'Sie','data'=>'','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
        }else{
            return $this->createFormBuilder()
                    ->setAction($this->generateUrl('solicitudModificacionCalificaciones_search'))
                    ->add('codigoRude','text',array('label'=>'Código Rude','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                    ->add('idInstitucion','text',array('label'=>'Sie','data'=>'','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                    ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                    ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
        }
    }

    public function enviarAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            // Obtenemos los nombres del emisor, receptor, estudianteinscripcion

            $remitente = mb_strtoupper($request->get('emisor'),'utf-8');
            //$receptor = mb_strtoupper($request->get('receptor'),'utf-8');
            $idEstudianteInscripcion = $request->get('idEstudianteInscripcion');
            $idGestion = $request->get('idGestion');
            $sie = $request->get('sie');
            
            $motivo = mb_strtoupper($request->get('motivo'),'utf-8');


            // Obtenemos el departamento de la unidad educativa
            $dep = $em->createQueryBuilder()
                            ->select('depTipo.id, depTipo.departamento')
                            ->from('SieAppWebBundle:Institucioneducativa','ie')
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','ie.leJuridicciongeografica = jg.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo','dt','WITH','jg.distritoTipo = dt.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo','depTipo','WITH','dt.departamentoTipo = depTipo.id')
                            ->where('ie.id = :sie')
                            ->setParameter('sie',$sie)
                            ->getQuery()
                            ->getResult();
            if($dep){
                $idDepartamento = $dep[0]['id'];
            }else{
                $idDepartamento = 0;
            }
            /**
             * Registramos la solicitud
             */
            $nuevaSolicitud = new EstudianteNotaSolicitud();
            $nuevaSolicitud->setUsuarioId($this->session->get('userId'));
            $nuevaSolicitud->setRemitente($remitente);
            $nuevaSolicitud->setReceptor('');
            $nuevaSolicitud->setFecha(new \DateTime('now'));
            $nuevaSolicitud->setHora(new \DateTime('now'));
            $nuevaSolicitud->setEstudianteInscripcionId($idEstudianteInscripcion);
            $estIns = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion);
            $detalle = $estIns->getEstudiante()->getCodigoRude().','.$estIns->getEstudiante()->getPaterno()." ".$estIns->getEstudiante()->getMaterno()." ".$estIns->getEstudiante()->getNombre();
            $nuevaSolicitud->setDetalle($detalle);
            $nuevaSolicitud->setMotivo($motivo);
            $nuevaSolicitud->setEstado(1);
            $nuevaSolicitud->setTipo(1);
            $nuevaSolicitud->setInstitucioneducativaId($sie);
            $nuevaSolicitud->setGestionTipoId($idGestion);
            $nuevaSolicitud->setDepartamentoTipoId($idDepartamento);
            $em->persist($nuevaSolicitud);
            $em->flush();
            $idSolicitud = $nuevaSolicitud->getId();

            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion);
            $nivel = $estudianteInscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            switch($nivel){
                case '11':
                case '1':
                case '403': 
                         $valoracionTipo = 'cualitativa';break; // Para inicial
                default: $valoracionTipo = 'cuantitativa';break; // PAra primaria y secundaria
            }

            /**
             * Registramos el detalle de la solicitud
             */
            // Obtenemos el array de los id's de las notas actuales
            $idNotaActual = $request->get('idNotaActual');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            // Obtenemos las nuevas notas
            $notas = $request->get('nota');
            // Obtenemos los ids de las notas cualitativas acutuales

            for($i=0;$i<count($idNotaActual);$i++){
                // Detalle de la nota
                if($idNotaActual[$i] != 'ninguno'){

                    $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idNotaActual[$i]);
                    // Registro
                    $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                    $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));
                    $detalleSolicitud->setEstudianteNotaId($nota->getId());
                    $detalleSolicitud->setAsignatura($nota->getEstudianteAsignatura()->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura());
                    $detalleSolicitud->setPeriodo($nota->getNotaTipo()->getNotaTipo());
                    if($nivel == 11){ // PAra inicial
                        $detalleSolicitud->setNotaCualitativaPrev($nota->getNotaCualitativa());
                        $detalleSolicitud->setNotaCualitativaNew($notas[$i]);
                    }else{ // Para primaria y secundaria
                        $detalleSolicitud->setNotaCuantitativaPrev($nota->getNotaCuantitativa());
                        $detalleSolicitud->setNotaCuantitativaNew($notas[$i]);
                    }
                    $detalleSolicitud->setValoracionTipo($valoracionTipo);
                    $em->persist($detalleSolicitud);
                    $em->flush();

                }else{

                    $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                    $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));

                    // Obtenemos los nombres de asignatura y periodo
                    $asignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]);
                    $notaTipoN = $em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]);

                    $detalleSolicitud->setAsignatura($asignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura());
                    $detalleSolicitud->setPeriodo($notaTipoN->getNotaTipo());
                    if($nivel == 11 or $nivel == 1 or $nivel == 403){ // PAra inicial
                        //$detalleSolicitud->setNotaCualitativaPrev($nota->getNotaCuantitativa());
                        $detalleSolicitud->setNotaCualitativaNew(mb_strtoupper($notas[$i],'utf-8'));
                    }else{ // Para primaria y secundaria
                        //$detalleSolicitud->setNotaCuantitativaPrev($nota->getNotaCuantitativa());
                        $detalleSolicitud->setNotaCuantitativaNew($notas[$i]);
                    }
                    $detalleSolicitud->setValoracionTipo($valoracionTipo);
                    $detalleSolicitud->setNotaTipoId($idNotaTipo[$i]);
                    $detalleSolicitud->setEstudianteAsignaturaId($idEstudianteAsignatura[$i]);
                    $em->persist($detalleSolicitud);
                    $em->flush();
                }
            }



            //$idNotaCualitativaActual = $request->get('idNotaCualitativaActual');
            $idEstudianteNotaC = $request->get('idEstudianteNotaC');
            $idNotaTipoC = $request->get('idNotaTipoC');
            // Obtenemos las nuevas notas cualitativas
            $notasCualitativas = $request->get('notaCualitativa');

            switch($nivel){
                case '11': 
                case '1':
                case '403':
                         $valoracionTipo = 'cualitativa_inicial';break; // Para inicial
                default: $valoracionTipo = 'cualitativa_primaria_secundaria';break; // PAra primaria y secundaria
            }
            for($i=0;$i<count($idEstudianteNotaC);$i++){

                if($idEstudianteNotaC[$i] != 'ninguno'){
                    // Detalle de la nota
                    $notaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaC[$i]);
                    // Registro
                    $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                    $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));
                    $detalleSolicitud->setEstudianteNotaId($notaCualitativa->getId());
                    $detalleSolicitud->setPeriodo($notaCualitativa->getNotaTipo()->getNotaTipo());
                    $detalleSolicitud->setNotaCualitativaPrev($notaCualitativa->getNotaCualitativa());
                    $detalleSolicitud->setNotaCualitativaNew(mb_strtoupper($notasCualitativas[$i],'utf-8'));
                    $detalleSolicitud->setValoracionTipo($valoracionTipo);
                    $em->persist($detalleSolicitud);
                    $em->flush();
                }else{
                    // Registro
                    $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                    $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));
                    //$detalleSolicitud->setEstudianteNotaId($notaCualitativa->getId());
                    $notaTipoN = $em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoC[$i]);

                    $detalleSolicitud->setPeriodo($notaTipoN->getNotaTipo());
                    //$detalleSolicitud->setNotaCualitativaPrev($notaCualitativa->getNotaCualitativa());
                    $detalleSolicitud->setNotaCualitativaNew(mb_strtoupper($notasCualitativas[$i],'utf-8'));
                    $detalleSolicitud->setValoracionTipo($valoracionTipo);
                    $detalleSolicitud->setNotaTipoId($idNotaTipoC[$i]);
                    $em->persist($detalleSolicitud);
                    $em->flush();
                }

                
            }

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('sendOk', 'La solicitud fue enviada');
            return $this->redirect($this->generateUrl('solicitudModificacionCalificaciones'));

        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Detalle de solicitud
     */
    public function detalleSolicitudAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $usuarioRol = $this->session->get('roluser');

        $idSolicitud = $request->get('idSolicitud');
        $solicitud = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud);
        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId());

        $cuantitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$idSolicitud,'valoracionTipo'=>array('cuantitativa','cualitativa')));
        $cualitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$idSolicitud,'valoracionTipo'=>array('cualitativa_inicial','cualitativa_primaria_secundaria')));

        $departamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($solicitud->getDepartamentoTipoId());
        $responsable = $em->createQueryBuilder()
                            ->select('p.paterno, p.materno, p.nombre')
                            ->from('SieAppWebBundle:Usuario','u')
                            ->innerJoin('SieAppWebBundle:Persona','p','with','u.persona = p.id')
                            ->where('u.id = :idUsuario')
                            ->setParameter('idUsuario',$solicitud->getUsuarioIdResp())
                            ->getQuery()
                            ->getResult();
        if(!$responsable){
            $responsable[0] = array('paterno'=>'','materno'=>'','nombre'=>'');
        }

        $usuarioRoles = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario'=>$solicitud->getUsuarioIdResp(),'rolTipo'=>array(7,8,10)));

        if($usuarioRoles){
            $rol = $em->getRepository('SieAppWebBundle:RolTipo')->find($usuarioRoles->getRolTipo()->getId());
        }else{
            $rol = null;
        }

        return $this->render('SieRegularBundle:SolicitudModificacionCalificaciones:detalle.html.twig',array(
            'solicitud'=>$solicitud,
            'estudianteInscripcion'=>$estudianteInscripcion,
            'cuantitativas'=>$cuantitativas,
            'cualitativas'=>$cualitativas,
            'usuarioRol'=>$usuarioRol,
            'departamento'=>$departamento,
            'responsable'=>$responsable,
            'rol'=>$rol));
    }

    /**
     * Aprobar o rechazar la solicitud
     */
    public function solicitudCambiarestadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $solicitud = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($request->get('idSolicitud'));

            $inscripcion = $em->createQueryBuilder()
                                ->select('nt.id as nivel, gt.id as grado, emt.id as estadoMatricula')
                                ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','WITH','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','WITH','ei.estadomatriculaTipo = emt.id')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion',$solicitud->getEstudianteInscripcionId())
                                ->getQuery()
                                ->getResult();

            $idInscripcion = $solicitud->getEstudianteInscripcionId();

            $sie = $solicitud->getInstitucioneducativaId();
            $gestion = $solicitud->getGestionTipoId();
            $nivel = $inscripcion[0]['nivel'];
            $grado = $inscripcion[0]['grado'];

            $gestionActual = $this->session->get('currentyear');

            if($gestion == $gestionActual){
                // VERIFICAMOS SI LA UNIDAD EDUCATIVA BAJO SU ARCHIVO
                $operativoLog = $em->createQueryBuilder()
                                    ->select('ieol')
                                    ->from('SieAppWebBundle:InstitucioneducativaOperativoLog','ieol')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaOperativoLogTipo','ieolt','with','ieol.institucioneducativaOperativoLogTipo = ieolt.id')
                                    ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ieol.institucioneducativa = ie.id')
                                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','ieol.gestionTipoId = gt.id')
                                    ->where('ie.id = :sie')
                                    ->andWhere('gt.id = :gestion')
                                    ->andWhere('ieolt.id in (1,2)')
                                    ->setParameter('sie', $sie)
                                    ->setParameter('gestion', $gestion)
                                    ->orderBy('ieol.id','desc')
                                    ->setMaxResults(1)
                                    ->getQuery()
                                    ->getResult()[0];
                if($operativoLog){
                    $accion = $operativoLog->getInstitucioneducativaOperativoLogTipo()->getId();
                    if($accion == 1){
                        $this->get('session')->getFlashBag()->add('error', 'La unidad educativa actualmente esta trabajando con la herramienta de escritorio, podra aprobar o rechazar la solicitud una vez que la unidad educativa haya consolidado su información');
                        return $this->redirectToRoute('solicitudModificacionCalificaciones');
                    }
                    // VERIFICAMOS SI SE SUBIO EL ARCHIVO id = 2
                    if($accion == 2){
                        $operativo = $operativoLog->getPeriodoTipo()->getId();
                        $operativoActual = $this->get('funciones')->obtenerOperativo($sie, $gestion);
                        if( in_array($this->session->get('roluser'), array(7,8,10))) {
                            $operativoActual = $operativoActual + 1;
                        }
                        if($operativoActual < $operativo){
                            $this->get('session')->getFlashBag()->add('error', 'La unidad educativa actualmente esta trabajando con la herramienta de escritorio, podra aprobar o rechazar la solicitud una vez que la unidad educativa haya consolidado su información');
                            return $this->redirectToRoute('solicitudModificacionCalificaciones');
                        }
                    }
                }
            }

            $idSolicitud = $request->get('idSolicitud');
            // Verificamos si la solicitud sigue en estado 1, para aprobralo 2 o rechazarlo 3
            if($solicitud->getEstado() != 1){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('error', 'La solicitud ya fue aprobada o rechazada');
                return $this->redirect($this->generateUrl('solicitudModificacionCalificaciones'));
            }else{

                $nuevoEstado = $request->get('state');
                if($nuevoEstado == 'approved'){
                    // Reiniciar el primary key de las tablas estudiante_nota y estudiante_nota_cualitativa
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                    $query->execute();
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');");
                    $query->execute();
                    // Registramos las nuevas notas y cambiamos de estado las notas anteriores
                    $detalleSolicitud = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$solicitud->getId()));
                    foreach ($detalleSolicitud as $ds) {
                        switch($ds->getValoracionTipo()){
                            case 'cuantitativa':
                            case 'cualitativa':
                                        if($ds->getEstudianteNotaId() != null){
                                            if($nivel == 11 or $nivel == 1 or $nivel == 403){
                                                $datosNota = $this->get('notas')->modificarNota($ds->getEstudianteNotaId(),0,$ds->getNotaCualitativaNew());
                                            }else{
                                                $datosNota = $this->get('notas')->modificarNota($ds->getEstudianteNotaId(),$ds->getNotaCuantitativaNew(),'');
                                            }
                                        }else{
                                            if($nivel == 11 or $nivel == 1 or $nivel == 403){
                                                $datosNota = $this->get('notas')->registrarNota($ds->getNotaTipoId(),$ds->getEstudianteAsignaturaId(),0,$ds->getNotaCualitativaNew());
                                            }else{
                                                $datosNota = $this->get('notas')->registrarNota($ds->getNotaTipoId(),$ds->getEstudianteAsignaturaId(),$ds->getNotaCuantitativaNew(),'');
                                            }
                                        }

                                        // Actualizar los promedios si fuere necesario
                                        if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                            switch ($gestion) {
                                                case 2008:
                                                case 2009:
                                                case 2010:
                                                case 2011:
                                                case 2012:  // Notas trimestrales

                                                            $this->get('notas')->calcularPromediosTrimestrales($datosNota->getEstudianteAsignatura()->getId());
                                                            break;
                                                case 2013:
                                                            if($grado != 1){
                                                                $this->get('notas')->calcularPromediosTrimestrales($datosNota->getEstudianteAsignatura()->getId());
                                                            }else{
                                                                $this->get('notas')->calcularPromedioBimestral($datosNota->getEstudianteAsignatura()->getId());
                                                            }
                                                            break;
                                                default:
                                                            $this->get('notas')->calcularPromedioBimestral($datosNota->getEstudianteAsignatura()->getId());
                                                            break;
                                            }
                                        }
                                        break;

                            case 'cualitativa_primaria_secundaria':
                            case 'cualitativa_inicial':
                                    if($ds->getEstudianteNotaId() != null){
                                        $this->get('notas')->modificarNotaCualitativa($ds->getEstudianteNotaId(), $ds->getNotaCualitativaNew());
                                    }else{
                                        $this->get('notas')->registrarNotaCualitativa($ds->getNotaTipoId(),$solicitud->getEstudianteInscripcionId(),$ds->getNotaCualitativaNew());
                                    }
                        }
                    }

                    // LLamamos a la funcion de actualizacion de estado de matricula
                    $this->get('notas')->actualizarEstadoMatricula($idInscripcion);


                    // CAmbiamos el estado de la solicitud a aprobado 2
                    $solicitud->setEstado(2);
                    $solicitud->setRespuesta($request->get('observacion'));
                    $solicitud->setUsuarioIdResp($this->session->get('userId'));
                    $solicitud->setFechaResp(new \DateTime('now'));
                    $em->flush();


                    $em->getConnection()->commit();
                    $this->get('session')->getFlashBag()->add('approved', 'La solicitud fue aprobada');
                    return $this->redirect($this->generateUrl('solicitudModificacionCalificaciones'));
                }else{
                    $em->getConnection()->commit();
                    $solicitud->setEstado(3);
                    $solicitud->setRespuesta($request->get('observacion'));
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('rejected', 'La solicitud fue rechazada');
                    return $this->redirect($this->generateUrl('solicitudModificacionCalificaciones'));
                }
            }
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
}
