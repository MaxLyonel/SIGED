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
class SolicitudAdicionCalificacionesController extends Controller {

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

            if($rolUsuario == 7){ // PAra nacional y/o  departamental
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
                                ->where('enm.tipo = 2')
                                ->andWhere('enm.departamentoTipoId = :departamento')
                                ->setParameter('departamento',$idDepartamento)
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
                                ->where('enm.tipo = 2')
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
                                ->where('enm.tipo = 2')
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

            }else{
                if($rolUsuario == 8){
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
                }else{
                    // Para unidades educativas
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
                                ->where('enm.tipo = 2')
                                ->andWhere('enm.usuarioId = :user')
                                ->setParameter('user',$idUsuario)
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

            return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:index.html.twig',array('solicitudes'=>$pagination));
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
                                ->where('enm.tipo = 2')
                                ->andWhere('enm.detalle LIKE :rude')
                                ->orderBy('enm.id','DESC')
                                ->setParameter('rude', '%'.$valor.'%')
                                ->getQuery()
                                ->getResult();
            }else{
                $this->get('session')->getFlashBag()->add('solicitudEstudianteError', 'El Código Rude ingresado no es válido');
                return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
            }
        }else{
            // hacemos la busqueda por numero de solicitud
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
                                ->where('enm.tipo = 2')
                                ->andWhere('enm.id = :idSolicitud')
                                ->orderBy('enm.id','DESC')
                                ->setParameter('idSolicitud',$valor)
                                ->getQuery()
                                ->getResult();
            if(!$solicitudes){
                $this->get('session')->getFlashBag()->add('solicitudEstudianteError', 'El número de solicitud de adición no existe');
                return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:solicitudesEstudiante.html.twig',array('form'=>$this->formSolicitudesEstudiante()->createView()));
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $solicitudes,
            $request->query->getInt('page',1),
            10
        );

        return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:index.html.twig',array('solicitudes'=>$pagination));

    }

    public function formSolicitudesEstudiante(){
        return $this->createFormBuilder()
                            ->setAction($this->generateUrl('solicitudesAdicionEstudiante'))
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

            ////////////////////////////////////////////////////
            $rolUsuario = $this->session->get('roluser');

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
                // Verificamos si existe el estudiante
                if(!$estudiante){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante con el código ingresado no existe!');
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

                // Verificamos si la unidad educativa tiene tucion sobre el sie ingresado
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['idInstitucion']);
                $query->bindValue(':roluser', $this->session->get('roluser'));
                $query->execute();

                //dump($this->session->get('userName'));die;
                //dump($this->session->get('roluser'));
                //die;
                $aTuicion = $query->fetchAll();
                //dump($aTuicion[0]['get_ue_tuicion']);die;
                if($aTuicion[0]['get_ue_tuicion'] == false){
                    /*if($this->session->get('userName') == '81981400'){

                    }else{*/
                    $this->get('session')->getFlashBag()->add('noSearch', 'No tiene tuicion sobre el código Sie ingresado!');
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    //}
                }
                //dump($form);die;
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
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con inscripcion en la unidad educativa y la gestion seleccionada, o su estado de matricula es de retirado.');
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }
            //VAlidamos que la gestion sea del 2010 para arriba
            if($gestion < 2008 or $gestion > 2016){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden adicionar notas de las gestiones 2008 a 2016');
                return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            /**
             * Obtenemos el operativo de la unidad educativa
             */
            if($gestion == 2016){
                $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($idInstitucion,$gestion);
                // Verificamos que la unidad educativa haya consolidado su informacion de cuarto bimestre
                // Y el operativo este en 4 o 5
                if($operativo < 4){
                    $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa aun no consolido su informacion de cuarto bimestre para poder realizar una solicitud de adicion.');
                    return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }

            // Lugar donde vuelve luego de haber registrado las asignaturas
            vuelta:

            // Obtenemos los datos del curso
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
            //dump($nivel);die;
            /**
             * Obtenemos la cantidad de notas
             */
            switch($gestion){
                case 2008:
                case 2009:
                case 2010:
                case 2011:
                case 2012:  $tipo_nota = 'Trimestre';
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
                            $tipo_nota = 'Bimestre';
                            $cantidad_notas = 4;
                            $valor_inicial = 1;
                            $valor_final = 4;
                            break;

                case 2016:  $tipo_nota = 'Bimestre';
                            $cantidad_notas = 4;
                            $valor_inicial = 1;
                            $valor_final = 4;
                            break;

            }
            //dump($idInscripcion);die;
            /*
             * Listamos las asignaturas del estudiante
             */
            $asignaturas = $em->createQueryBuilder()
                                        ->select('at.id, at.asignatura, ei.id as idEstudianteInscripcion')
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
//dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            $arrayAsignaturasEstudiante = array();
            foreach ($asignaturas as $a) {
                $arrayAsignaturasEstudiante[] = $a['id'];
                $notasArray[$cont] = array('idAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
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
                //dump($asignaturasNotas);die;
                switch ($tipo_nota) {
                    case 'Bimestre':
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
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$valorNota,
                                                            'notaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'idFila'=>$an['id'].''.$i
                                                        );
                                    $existe = 'si';
                                    $cont2++;
                                }

                            }
                            if($existe == 'no'){
                                $estAsignatura = $em->createQuery(
                                                'SELECT ea.id FROM SieAppWebBundle:EstudianteAsignatura ea
                                                INNER JOIN ea.estudianteInscripcion ei
                                                INNER JOIN ea.institucioneducativaCursoOferta ieco
                                                INNER JOIN ieco.asignaturaTipo at
                                                WHERE ei.id = :idEstInsc
                                                AND at.id = :idAsignatura')
                                                ->setParameter('idEstInsc',$a['idEstudianteInscripcion'])
                                                ->setParameter('idAsignatura',$a['id'])
                                                ->getResult();
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>$this->literal($i)." ".$tipo_nota,
                                                            'nota'=>'',
                                                            'notaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                            'idFila'=>$estAsignatura[0]['id'].''.$i
                                                        );
                                $cont2++;
                            }
                        }
                        if($nivel != 11 and $gestion != 2016){
                            // Para el promedio
                            foreach ($asignaturasNotas as $an) {
                                $existe = 'no';
                                if($an['idNotaTipo'] == 5){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$cont2,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'notaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'idFila'=>$an['id'].''.$i
                                                            );
                                    $existe = 'si';
                                }
                            }
                            if($existe == 'no'){

                                $estAsignatura = $em->createQuery(
                                                'SELECT ea.id FROM SieAppWebBundle:EstudianteAsignatura ea
                                                INNER JOIN ea.estudianteInscripcion ei
                                                INNER JOIN ea.institucioneducativaCursoOferta ieco
                                                INNER JOIN ieco.asignaturaTipo at
                                                WHERE ei.id = :idEstInsc
                                                AND at.id = :idAsignatura')
                                                ->setParameter('idEstInsc',$a['idEstudianteInscripcion'])
                                                ->setParameter('idAsignatura',$a['id'])
                                                ->getResult();

                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>'promedio',
                                                            'bimestre'=>'Promedio',
                                                            'nota'=>'',
                                                            'notaTipo'=>5,
                                                            'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                            'idFila'=>$estAsignatura[0]['id'].''.$cont2
                                                        );
                                $cont2++;
                            }
                        }
                        break;
                    case 'Trimestre':
                        // Notas de 1er a 3er trimestre
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
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$valorNota,
                                                            'notaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'idFila'=>$an['id'].''.$i
                                                        );
                                    $existe = 'si';

                                }
                                $cont2++;
                            }
                            if($existe == 'no'){
                                $estAsignatura = $em->createQuery(
                                                'SELECT ea.id FROM SieAppWebBundle:EstudianteAsignatura ea
                                                INNER JOIN ea.estudianteInscripcion ei
                                                INNER JOIN ea.institucioneducativaCursoOferta ieco
                                                INNER JOIN ieco.asignaturaTipo at
                                                WHERE ei.id = :idEstInsc
                                                AND at.id = :idAsignatura')
                                                ->setParameter('idEstInsc',$a['idEstudianteInscripcion'])
                                                ->setParameter('idAsignatura',$a['id'])
                                                ->getResult();
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>$this->literal($i)." ".$tipo_nota,
                                                            'nota'=>'',
                                                            'notaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                            'idFila'=>$estAsignatura[0]['id'].''.$i
                                                        );
                                $cont2++;
                            }
                        }

                        $estAsignatura = $em->createQuery(
                                            'SELECT ea.id FROM SieAppWebBundle:EstudianteAsignatura ea
                                            INNER JOIN ea.estudianteInscripcion ei
                                            INNER JOIN ea.institucioneducativaCursoOferta ieco
                                            INNER JOIN ieco.asignaturaTipo at
                                            WHERE ei.id = :idEstInsc
                                            AND at.id = :idAsignatura')
                                            ->setParameter('idEstInsc',$a['idEstudianteInscripcion'])
                                            ->setParameter('idAsignatura',$a['id'])
                                            ->getResult();

                        // Para el promedio anual
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            if($an['idNotaTipo'] == 9){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'notaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'idFila'=>$an['id'].''.$cont2
                                                        );
                                $existe = 'si';
                                $cont2++;
                            }
                        }
                        if($existe == 'no'){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$cont2,
                                                        'idEstudianteNota'=>'promedio',
                                                        'bimestre'=>'Promedio Anual',
                                                        'nota'=>'',
                                                        'notaTipo'=>9,
                                                        'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                        'idFila'=>$estAsignatura[0]['id'].''.$cont2
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
                                                            'notaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'idFila'=>$an['id'].''.$cont2
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
                                                        'notaTipo'=>10,
                                                        'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                        'idFila'=>$estAsignatura[0]['id'].''.$cont2
                                                    );
                            $cont2++;
                        }
                        // Para el promedio final
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            if($an['idNotaTipo'] == 11){
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'notaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'idFila'=>$an['id'].''.$cont2
                                                        );
                                $existe = 'si';
                                $cont2++;
                            }
                        }
                        if($existe == 'no'){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$cont2,
                                                        'idEstudianteNota'=>'promedio',
                                                        'bimestre'=>'Promedio Final',
                                                        'nota'=>'',
                                                        'notaTipo'=>11,
                                                        'idEstudianteAsignatura'=>$estAsignatura[0]['id'],
                                                        'idFila'=>$estAsignatura[0]['id'].''.$cont2
                                                    );
                            $cont2++;
                        }
                        break;
                    default:
                        # code...
                        break;
                }

                $cont++;
            }
            // Si el estudiante no cuenta con asignaturas entonces las creamos
            // Obtenemos las materias del curso
            $query = $em->createQuery(
                    'SELECT DISTINCT at.id, ieco.id as idCursoOferta
                    FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.insitucioneducativaCurso iec
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE iec.id = :idCurso')
                    ->setParameter('idCurso', $curso[0]['id']);

            $nuevasAsignaturas = $query->getResult();

            // VErificamos si el array de materias del estudiante esta vacio; si es asi lo llenamos con un dato para que no de error
            if(count($arrayAsignaturasEstudiante)==0){
                $arrayAsignaturasEstudiante = array(1);
            }
            //dump($nuevasAsignaturas);die;
            // Pregunstamos si lno tiene notas y o materias o si la catidad de materias del estudiante es diferente al del curso
            if((!$notasArray and $gestion != 2016) or (count($nuevasAsignaturas) != count($asignaturas))){
                if($nuevasAsignaturas){
                    // Reiniciar el primary key de las tabla estudiante_asignatura
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                    $query->execute();
                    // Registramos las areas del estudiante
                    foreach ($nuevasAsignaturas as $na) {
                        // Preguntamos si la materia del curso ya la tiene el estudiante
                        if(!in_array($na['id'], $arrayAsignaturasEstudiante)){
                            $newAsignatura = new EstudianteAsignatura();
                            $newAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $newAsignatura->setFechaRegistro(new \DateTime('now'));
                            $newAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($na['idCursoOferta']));
                            $em->persist($newAsignatura);
                            $em->flush();
                        }

                    }
                    goto vuelta;
                }

                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, debe realizar la adicion de areas y calificaciones.');
                return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            // Verificamos si el mismo estudiante tiene una solicitud pendiente
            $solicitudPendiente = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->findOneBy(array('estudianteInscripcionId'=>$idInscripcion,'estado'=>1,'tipo'=>2));
            if($solicitudPendiente){
                $this->get('session')->getFlashBag()->add('sendError', 'Ya hay una solicitud pendiente (S-'.$solicitudPendiente->getId().') para el estudiante, debe esperar a que se responda la solicitud anterior');
                return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }


            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y obtenemos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();

            /**
             * Verificamos si existen notas por lo menos del primer bimestre o trimestre
             */

            $titulos_notas = array();
            $titulos = $notasArray[0]['notas'];

            for($i=0;$i<count($titulos);$i++){
                $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
            }
            /*
            // Creamos la variable de session del nivel
            */
            $this->session->set('idNivel',$idNivel);

            /**
             * Listamos las valoraciones cualitativas
             */
            if($nivel != 11){
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
            return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:new.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso,
                        'cualitativas'=>$cualitativas,
                        'idEstudianteInscripcion'=>$idInscripcion,
                        'nombreUsuario'=>$nombreUsuario
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
            echo 'Error';
        }
    }

    function literal($bimestre){
        switch ($bimestre) {
            case '1': $bim = 'Primer'; break;
            case '2': $bim = 'Segundo'; break;
            case '3': $bim = 'Tercer'; break;
            case '4': $bim = 'Cuarto'; break;
            // Para trimestrales
            case '6': $bim = 'Primer'; break;
            case '7': $bim = 'Segundo'; break;
            case '8': $bim = 'Tercer'; break;
            default : $bim = $bimestre;break;
        }
        return $bim;
    }

    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        $gestiones = array();
        for($i=2014;$i<=$gestion;$i++){
            $gestiones[$i] = $i;
        }

        $rolUsuario = $this->session->get('roluser');
        if($rolUsuario == 8){
            return $this->createFormBuilder()
                ->setAction($this->generateUrl('solicitudAdicionCalificaciones_search'))
                ->add('codigoRude','text',array('label'=>'Código Rude','data'=>'','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                ->add('idInstitucion','text',array('label'=>'Sie','data'=>'','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
        }else{
            return $this->createFormBuilder()
                    ->setAction($this->generateUrl('solicitudAdicionCalificaciones_search'))
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
            $nuevaSolicitud->setTipo(2);
            $nuevaSolicitud->setInstitucioneducativaId($sie);
            $nuevaSolicitud->setGestionTipoId($idGestion);
            $nuevaSolicitud->setDepartamentoTipoId($idDepartamento);
            $em->persist($nuevaSolicitud);
            $em->flush();
            $idSolicitud = $nuevaSolicitud->getId();
            /**
             * Definimos el clasificador del tipo de valoracion segun el nivel
             */
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion);
            $nivel = $estudianteInscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            switch($nivel){
                case '11': $valoracionTipo = 'cualitativa';break; // Para inicial
                default: $valoracionTipo = 'cuantitativa';break; // PAra primaria y secundaria
            }

            /**
             * Registramos el detalle de la solicitud
             */

            // Obtenemos los arrays de las calificaciones cuantitativas
            $notas = $request->get('nota');
            $notaTipo = $request->get('notaTipoN');
            $estudianteAsignatura = $request->get('estudianteAsignaturaN');

            for($i=0;$i<count($notas);$i++){
                // Registro
                $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));

                // Obtenemos los nombres de asignatura y periodo
                $asignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($estudianteAsignatura[$i]);
                $notaTipoN = $em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipo[$i]);

                $detalleSolicitud->setAsignatura($asignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura());
                $detalleSolicitud->setPeriodo($notaTipoN->getNotaTipo());
                if($nivel == 11){ // PAra inicial
                    //$detalleSolicitud->setNotaCualitativaPrev($nota->getNotaCuantitativa());
                    $detalleSolicitud->setNotaCualitativaNew(mb_strtoupper($notas[$i],'utf-8'));
                }else{ // Para primaria y secundaria
                    //$detalleSolicitud->setNotaCuantitativaPrev($nota->getNotaCuantitativa());
                    $detalleSolicitud->setNotaCuantitativaNew($notas[$i]);
                }
                $detalleSolicitud->setValoracionTipo($valoracionTipo);
                $detalleSolicitud->setNotaTipoId($notaTipo[$i]);
                $detalleSolicitud->setEstudianteAsignaturaId($estudianteAsignatura[$i]);
                $em->persist($detalleSolicitud);
                $em->flush();
            }

            switch($nivel){
                case '11': $valoracionTipo = 'cualitativa_inicial';break; // Para inicial
                default: $valoracionTipo = 'cualitativa_primaria_secundaria';break; // PAra primaria y secundaria
            }
            // Obtenemos las nuevas notas cualitativas
            $notasCualitativas = $request->get('notaCualitativa');
            $notaTipoCualitativa = $request->get('notaTipoCualitativa');

            for($i=0;$i<count($notasCualitativas);$i++){
                // Registro
                $detalleSolicitud = new EstudianteNotaSolicitudDetalle();
                $detalleSolicitud->setEstudianteNotaSolicitud($em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud));
                //$detalleSolicitud->setEstudianteNotaId($notaCualitativa->getId());
                $notaTipoN = $em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipoCualitativa[$i]);
                $detalleSolicitud->setPeriodo($notaTipoN->getNotaTipo());
                //$detalleSolicitud->setNotaCualitativaPrev($notaCualitativa->getNotaCualitativa());
                $detalleSolicitud->setNotaCualitativaNew(mb_strtoupper($notasCualitativas[$i],'utf-8'));
                $detalleSolicitud->setValoracionTipo($valoracionTipo);
                $detalleSolicitud->setNotaTipoId($notaTipoCualitativa[$i]);
                $em->persist($detalleSolicitud);
                $em->flush();
            }

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('sendOk', 'La solicitud fue enviada');
            return $this->redirect($this->generateUrl('solicitudAdicionCalificaciones'));

        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Detalle de solicitud
     */
    public function solicitudAdicionDetalleAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $usuarioRol = $this->session->get('roluser');

        $idSolicitud = $request->get('idSolicitud');
        $solicitud = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitud')->find($idSolicitud);
        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId());

        //$cuantitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$idSolicitud,'valoracionTipo'=>'cuantitativa'));
        $cualitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$idSolicitud,'valoracionTipo'=>array('cualitativa_primaria_secundaria','cualitativa_inicial')));

        /*$cuantitativas = $em->createQuery(
            'SELECT ensd FROM SieAppWebBundle:EstudianteNotaSolicitudDetalle ensd
            WHERE ensd.estudianteNotaSolicitud = :idSolicitud
            AND ensd.valoracionTipo = :tipo
            ORDER BY ensd.estudianteAsignaturaId ASC, ensd.notaTipoId')
            ->setParameter('idSolicitud',$idSolicitud)
            ->setParameter('tipo','cuantitativa')
            ->getResult();*/

        $cuantitativas = $em->createQueryBuilder()
                            ->select('ensd')
                            ->from('SieAppWebBundle:EstudianteNotaSolicitudDetalle','ensd')
                            ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ensd.estudianteAsignaturaId = ea.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                            ->where('ensd.estudianteNotaSolicitud = :idSolicitud')
                            ->andWhere('ensd.valoracionTipo IN (:tipo)')
                            ->setParameter('idSolicitud',$idSolicitud)
                            ->setParameter('tipo',array('cuantitativa','cualitativa'))
                            ->orderBy('at.id','ASC')
                            ->getQuery()
                            ->getResult();
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
        //$usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($solicitud->getUsuarioIdResp());
        $usuarioRoles = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario'=>$solicitud->getUsuarioIdResp(),'rolTipo'=>array(7,8,10)));
        if($usuarioRoles){
            $rol = $em->getRepository('SieAppWebBundle:RolTipo')->find($usuarioRoles->getRolTipo()->getId());
        }else{
            $rol = null;
        }

        return $this->render('SieRegularBundle:SolicitudAdicionCalificaciones:detalle.html.twig',array(
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
    public function solicitudAdicionCambiarestadoAction(Request $request){
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

            $gestion = $solicitud->getGestionTipoId();
            $nivel = $inscripcion[0]['nivel'];
            $grado = $inscripcion[0]['grado'];

            // Verificamos si la solicitud sigue en estado 1, para aprobralo 2 o rechazarlo 3
            if($solicitud->getEstado() != 1){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('error', 'La solicitud ya fue aprobada o rechazada');
                return $this->redirect($this->generateUrl('solicitudAdicionCalificaciones'));
            }else{
                $nuevoEstado = $request->get('state');
                if($nuevoEstado == 'approved'){
                    // Reiniciar el primary key de las tablas estudiante_nota y estudiante_nota_cualitativa
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                    $query->execute();
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');");
                    $query->execute();
                    // Registramos las nuevas notas y cambiamos de estado las notas anteriores
                    //$detalleSolicitud = $em->getRepository('SieAppWebBundle:EstudianteNotaSolicitudDetalle')->findBy(array('estudianteNotaSolicitud'=>$solicitud->getId()));
                    $detalleSolicitud = $em->createQueryBuilder()
                            ->select('ensd')
                            ->from('SieAppWebBundle:EstudianteNotaSolicitudDetalle','ensd')
                            ->where('ensd.estudianteNotaSolicitud = :idSolicitud')
                            ->setParameter('idSolicitud',$solicitud->getId())
                            ->orderBy('ensd.notaTipoId','ASC')
                            ->getQuery()
                            ->getResult();
                    //dump($detalleSolicitud);die;
                    foreach ($detalleSolicitud as $ds) {
                        switch($ds->getValoracionTipo()){
                            case 'cuantitativa':
                            case 'cualitativa':
                                    // Verificamos si la nota ya existe en el sistema
                                    $existeNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$ds->getNotaTipoId(),'estudianteAsignatura'=>$ds->getEstudianteAsignaturaId()));
                                    if($existeNota){
                                        $em->getConnection()->rollback();
                                        $this->get('session')->getFlashBag()->add('error', 'La solicitud no procede, las calificaciones que desea adicionar ya existen !!!');
                                        return $this->redirect($this->generateUrl('solicitudAdicionCalificaciones'));
                                    }

                                    $nuevaNota = new EstudianteNota();
                                    $nuevaNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($ds->getNotaTipoId()));
                                    $nuevaNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ds->getEstudianteAsignaturaId()));
                                    if($nivel == 11){
                                        $nuevaNota->setNotaCuantitativa(0);
                                        $nuevaNota->setNotaCualitativa($ds->getNotaCualitativaNew());
                                    }else{
                                        $nuevaNota->setNotaCuantitativa($ds->getNotaCuantitativaNew());
                                        $nuevaNota->setNotaCualitativa('');
                                    }
                                    $nuevaNota->setRecomendacion('');
                                    $nuevaNota->setUsuarioId($this->session->get('userId'));
                                    $nuevaNota->setFechaRegistro(new \DateTime('now'));
                                    $nuevaNota->setFechaModificacion(new \DateTime('now'));
                                    $nuevaNota->setObs('');
                                    $em->persist($nuevaNota);
                                    $em->flush();
                                    if($nivel != 11){
                                        // ACtualizacion de promedios
                                        switch ($gestion) {
                                            case 2008:
                                            case 2009:
                                            case 2010:
                                            case 2011:
                                            case 2012:
                                                $notas = $em->createQuery(
                                                        'SELECT en FROM SieAppWebBundle:EstudianteNota en
                                                        WHERE en.estudianteAsignatura = :estAsig')
                                                        ->setParameter('estAsig',$nuevaNota->getEstudianteAsignatura()->getId())
                                                        ->getResult();

                                                //dump($notas);die;
                                                $cont = 0;
                                                $suma = 0;
                                                $promedioAnual = null;
                                                $reforzamiento = null;
                                                $promedioFinal = null;
                                                foreach ($notas as $n) {
                                                    if($n->getNotaTipo()->getId() == 6 or $n->getNotaTipo()->getId() == 7 or $n->getNotaTipo()->getId() == 8){
                                                        $suma = $suma + $n->getNotaCuantitativa();
                                                        $cont++;
                                                    }
                                                    if($n->getNotaTipo()->getId() == 9){ $promedioAnual = $n; }
                                                    if($n->getNotaTipo()->getId() == 10){ $reforzamiento = $n; }
                                                    if($n->getNotaTipo()->getId() == 11){ $promedioFinal = $n; }
                                                }
                                                if($cont == 3){
                                                    // Promedio Anual
                                                    $newPromedioAnual = round($suma/3);
                                                    if($promedioAnual){
                                                        $promedioAnual->setNotaCuantitativa($newPromedioAnual);
                                                        $em->flush();
                                                    }else{
                                                        $nuevoPromedio = new EstudianteNota();
                                                        $nuevoPromedio->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(9));
                                                        $nuevoPromedio->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ds->getEstudianteAsignaturaId()));
                                                        $nuevoPromedio->setNotaCuantitativa($newPromedioAnual);
                                                        $nuevoPromedio->setNotaCualitativa('');
                                                        $nuevoPromedio->setRecomendacion('');
                                                        $nuevoPromedio->setUsuarioId($this->session->get('userId'));
                                                        $nuevoPromedio->setFechaRegistro(new \DateTime('now'));
                                                        $nuevoPromedio->setFechaModificacion(new \DateTime('now'));
                                                        $nuevoPromedio->setObs('');
                                                        $em->persist($nuevoPromedio);
                                                        $em->flush();
                                                    }

                                                    if($reforzamiento){
                                                        // Promedio Final
                                                        $suma2 = $newPromedioAnual + $reforzamiento->getNotaCuantitativa();
                                                        $newPromedioFinal = round($suma2/2);

                                                        if($promedioFinal){
                                                            $promedioFinal->setNotaCuantitativa($newPromedioFinal);
                                                            $em->flush();
                                                        }else{
                                                            $nuevoPromedioFinal = new EstudianteNota();
                                                            $nuevoPromedioFinal->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(11));
                                                            $nuevoPromedioFinal->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ds->getEstudianteAsignaturaId()));
                                                            $nuevoPromedioFinal->setNotaCuantitativa($newPromedioFinal);
                                                            $nuevoPromedioFinal->setNotaCualitativa('');
                                                            $nuevoPromedioFinal->setRecomendacion('');
                                                            $nuevoPromedioFinal->setUsuarioId($this->session->get('userId'));
                                                            $nuevoPromedioFinal->setFechaRegistro(new \DateTime('now'));
                                                            $nuevoPromedioFinal->setFechaModificacion(new \DateTime('now'));
                                                            $nuevoPromedioFinal->setObs('');
                                                            $em->persist($nuevoPromedioFinal);
                                                            $em->flush();
                                                        }
                                                    }
                                                    // Actualizamos el estado de matricula si corresponde
                                                    // Obtenemos las asignaturas
                                                    $asignaturas = $em->createQuery(
                                                            'SELECT ea FROM SieAppWebBundle:EstudianteAsignatura ea
                                                            INNER JOIN ea.estudianteInscripcion ei
                                                            WHERE ei.id = :id')
                                                            ->setParameter('id',$solicitud->getEstudianteInscripcionId())
                                                            ->getResult();
                                                    // Creamos el array de promedios
                                                    $arrayPromedios = array();
                                                    foreach ($asignaturas as $a) {
                                                        $promedioFinal = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>11));
                                                        if($promedioFinal){
                                                            $arrayPromedios[] = $promedioFinal->getNotaCuantitativa();
                                                        }else{
                                                            $promedioAnual = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                                                            if($promedioAnual){
                                                                $arrayPromedios[] = $promedioAnual->getNotaCuantitativa();
                                                            }else{
                                                                $arrayPromedios[] = 0;
                                                            }
                                                        }
                                                    }
                                                    // Verificamos si todas las materias tienen promedio
                                                    if(!in_array(0, $arrayPromedios)){
                                                        $newEstadoMatricula = 5;
                                                        for($i=0;$i<count($arrayPromedios);$i++){
                                                            if($arrayPromedios[$i]<36){
                                                                $newEstadoMatricula = 11;
                                                                break;
                                                            }
                                                        }
                                                        if($inscripcion[0]['estadoMatricula'] != 26){
                                                            $datosInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId());
                                                            $datosInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($newEstadoMatricula));
                                                            $em->flush();
                                                        }
                                                    }
                                                }

                                                break;
                                            case 2013:
                                                if($grado != 1){
                                                    // Trimestrales
                                                    $notas = $em->createQuery(
                                                            'SELECT en FROM SieAppWebBundle:EstudianteNota en
                                                            WHERE en.estudianteAsignatura = :estAsig')
                                                            ->setParameter('estAsig',$nuevaNota->getEstudianteAsignatura()->getId())
                                                            ->getResult();

                                                    $cont = 0;
                                                    $suma = 0;
                                                    $promedioAnual = null;
                                                    $reforzamiento = null;
                                                    $promedioFinal = null;
                                                    foreach ($notas as $n) {
                                                        if($n->getNotaTipo()->getId() == 6 or $n->getNotaTipo()->getId() == 7 or $n->getNotaTipo()->getId() == 8){
                                                            $suma = $suma + $n->getNotaCuantitativa();
                                                            $cont++;
                                                        }
                                                        if($n->getNotaTipo()->getId() == 9){ $promedioAnual = $n; }
                                                        if($n->getNotaTipo()->getId() == 10){ $reforzamiento = $n; }
                                                        if($n->getNotaTipo()->getId() == 11){ $promedioFinal = $n; }
                                                    }
                                                    if($cont == 3){
                                                        // Promedio Anual
                                                        $newPromedioAnual = round($suma/3);
                                                        if($promedioAnual){
                                                            $promedioAnual->setNotaCuantitativa($newPromedioAnual);
                                                            $promedioAnual->setFechaModificacion(new \DateTime('now'));
                                                            $em->flush();
                                                        }else{
                                                            $nuevoPromedio = new EstudianteNota();
                                                            $nuevoPromedio->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(9));
                                                            $nuevoPromedio->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ds->getEstudianteAsignaturaId()));
                                                            $nuevoPromedio->setNotaCuantitativa($newPromedioAnual);
                                                            $nuevoPromedio->setNotaCualitativa('');
                                                            $nuevoPromedio->setRecomendacion('');
                                                            $nuevoPromedio->setUsuarioId($this->session->get('userId'));
                                                            $nuevoPromedio->setFechaRegistro(new \DateTime('now'));
                                                            $nuevoPromedio->setFechaModificacion(new \DateTime('now'));
                                                            $nuevoPromedio->setObs('');
                                                            $em->persist($nuevoPromedio);
                                                            $em->flush();
                                                        }

                                                        if($reforzamiento){
                                                            // Promedio Final
                                                            $suma2 = $newPromedioAnual + $reforzamiento->getNotaCuantitativa();
                                                            $newPromedioFinal = round($suma2/2);

                                                            if($promedioFinal){
                                                                $promedioFinal->setNotaCuantitativa($newPromedioFinal);
                                                                $em->flush();
                                                            }else{
                                                                $nuevoPromedioFinal = new EstudianteNota();
                                                                $nuevoPromedioFinal->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(11));
                                                                $nuevoPromedioFinal->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ds->getEstudianteAsignaturaId()));
                                                                $nuevoPromedioFinal->setNotaCuantitativa($newPromedioFinal);
                                                                $nuevoPromedioFinal->setNotaCualitativa('');
                                                                $nuevoPromedioFinal->setRecomendacion('');
                                                                $nuevoPromedioFinal->setUsuarioId($this->session->get('userId'));
                                                                $nuevoPromedioFinal->setFechaRegistro(new \DateTime('now'));
                                                                $nuevoPromedioFinal->setFechaModificacion(new \DateTime('now'));
                                                                $nuevoPromedioFinal->setObs('');
                                                                $em->persist($nuevoPromedioFinal);
                                                                $em->flush();
                                                            }
                                                        }
                                                        // Actualizamos el estado de matricula si corresponde
                                                        // Obtenemos las asignaturas
                                                        $asignaturas = $em->createQuery(
                                                                'SELECT ea FROM SieAppWebBundle:EstudianteAsignatura ea
                                                                INNER JOIN ea.estudianteInscripcion ei
                                                                WHERE ei.id = :id')
                                                                ->setParameter('id',$solicitud->getEstudianteInscripcionId())
                                                                ->getResult();
                                                        // Creamos el array de promedios
                                                        $arrayPromedios = array();
                                                        foreach ($asignaturas as $a) {
                                                            $promedioFinal = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>11));
                                                            if($promedioFinal){
                                                                $arrayPromedios[] = $promedioFinal->getNotaCuantitativa();
                                                            }else{
                                                                $promedioAnual = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                                                                if($promedioAnual){
                                                                    $arrayPromedios[] = $promedioAnual->getNotaCuantitativa();
                                                                }else{
                                                                    $arrayPromedios[] = 0;
                                                                }
                                                            }
                                                        }
                                                        // Verificamos si todas las materias tienen promedio
                                                        if(!in_array(0, $arrayPromedios)){
                                                            $newEstadoMatricula = 5;
                                                            for($i=0;$i<count($arrayPromedios);$i++){
                                                                if($arrayPromedios[$i]<36){
                                                                    $newEstadoMatricula = 11;
                                                                    break;
                                                                }
                                                            }
                                                            if($inscripcion[0]['estadoMatricula'] != 26){
                                                                $datosInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId());
                                                                $datosInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($newEstadoMatricula));
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    // Bimestrales
                                                    $idInscripcion = $solicitud->getEstudianteInscripcionId();
                                                    /*
                                                     * Listamos las asignaturas del estudiante
                                                     */
                                                    $query = $em->createQuery(
                                                            'SELECT DISTINCT at.id, at.asignatura, ei.id as idInscripcion
                                                            FROM SieAppWebBundle:EstudianteAsignatura ea
                                                            JOIN ea.estudianteInscripcion ei
                                                            JOIN ei.estudiante e
                                                            JOIN ei.institucioneducativaCurso iec
                                                            JOIN ea.institucioneducativaCursoOferta ieco
                                                            JOIN ieco.asignaturaTipo at
                                                            WHERE ei.id = :idInscripcion
                                                            ORDER BY at.id')
                                                            ->setParameter('idInscripcion',$idInscripcion);

                                                    $asignaturas = $query->getResult();
                                                    //dump($asignaturas);die;
                                                    $notasArray = array();
                                                    $cont = 0;

                                                    foreach ($asignaturas as $a) {
                                                        $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                                                        $asignaturasNotas = $em->createQuery(
                                                                                'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, nt.id as notaTipo, ea.id as idEstudianteAsignatura
                                                                                FROM SieAppWebBundle:EstudianteNota en
                                                                                JOIN en.notaTipo nt
                                                                                JOIN en.estudianteAsignatura ea
                                                                                JOIN ea.estudianteInscripcion ei
                                                                                JOIN ea.institucioneducativaCursoOferta ieco
                                                                                JOIN ieco.asignaturaTipo at
                                                                                WHERE ei.id = :idInscripcion
                                                                                AND at.id = :idAsignatura
                                                                                AND nt.id IN (:promedios)
                                                                                ORDER BY at.id, nt.id')
                                                                                ->setParameter('idInscripcion',$a['idInscripcion'])
                                                                                ->setParameter('idAsignatura',$a['id'])
                                                                                ->setParameter('promedios',array(1,2,3,4,5))
                                                                                ->getResult();

                                                        $cont = 0;
                                                        $existePromedio = 'no';
                                                        if($asignaturasNotas){
                                                            $sumaNotas = 0;

                                                            foreach ($asignaturasNotas as $an) {
                                                                if($an['notaTipo'] == 5){
                                                                    $existePromedio = 'si';
                                                                    $promedio = round($sumaNotas/4);
                                                                    $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($an['idNota']);
                                                                    $notaPromedio->setNotaCuantitativa($promedio);
                                                                    $notaPromedio->setFechaModificacion(new \DateTime('now'));
                                                                    $em->flush();
                                                                }else{
                                                                    $sumaNotas = $sumaNotas + $an['notaCuantitativa'];
                                                                    $cont++;
                                                                    $idEstudianteAsignatura = $an['idEstudianteAsignatura'];
                                                                }
                                                            }
                                                        }
                                                        // Si no existe el promedio registramos el promedio
                                                        if($existePromedio == 'no' and $cont == 4){
                                                            $promedio = round($sumaNotas/4);
                                                            $nuevoPromedio = new EstudianteNota();
                                                            $nuevoPromedio->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                                                            $nuevoPromedio->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura));
                                                            $nuevoPromedio->setNotaCuantitativa($promedio);
                                                            $nuevoPromedio->setNotaCualitativa('');
                                                            $nuevoPromedio->setRecomendacion('');
                                                            $nuevoPromedio->setUsuarioId($this->session->get('userId'));
                                                            $nuevoPromedio->setFechaRegistro(new \DateTime('now'));
                                                            $nuevoPromedio->setFechaModificacion(new \DateTime('now'));
                                                            $nuevoPromedio->setObs('');
                                                            $em->persist($nuevoPromedio);
                                                            $em->flush();
                                                        }
                                                        $cont++;
                                                    }

                                                    /**
                                                     * Ejecutamos la funcion de base de datos
                                                     */
                                                    if($inscripcion[0]['estadoMatricula'] != 26){
                                                        $query = $em->getConnection()->prepare("select * from sp_calcula_estadomatricula_inscripcion(".$idInscripcion.")");
                                                        $query->execute();
                                                    }
                                                    break;
                                                }
                                                break;
                                            case 2014:
                                            case 2015:
                                            case 2016:
                                            case 2017:
                                            case 2018:
                                                $idInscripcion = $solicitud->getEstudianteInscripcionId();
                                                /*
                                                 * Listamos las asignaturas del estudiante
                                                 */
                                                $query = $em->createQuery(
                                                        'SELECT DISTINCT at.id, at.asignatura, ei.id as idInscripcion
                                                        FROM SieAppWebBundle:EstudianteAsignatura ea
                                                        JOIN ea.estudianteInscripcion ei
                                                        JOIN ei.estudiante e
                                                        JOIN ei.institucioneducativaCurso iec
                                                        JOIN ea.institucioneducativaCursoOferta ieco
                                                        JOIN ieco.asignaturaTipo at
                                                        WHERE ei.id = :idInscripcion
                                                        ORDER BY at.id')
                                                        ->setParameter('idInscripcion',$idInscripcion);

                                                $asignaturas = $query->getResult();
                                                //dump($asignaturas);die;
                                                $notasArray = array();
                                                $cont = 0;

                                                foreach ($asignaturas as $a) {
                                                    $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                                                    $asignaturasNotas = $em->createQuery(
                                                                            'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, nt.id as notaTipo, ea.id as idEstudianteAsignatura
                                                                            FROM SieAppWebBundle:EstudianteNota en
                                                                            JOIN en.notaTipo nt
                                                                            JOIN en.estudianteAsignatura ea
                                                                            JOIN ea.estudianteInscripcion ei
                                                                            JOIN ea.institucioneducativaCursoOferta ieco
                                                                            JOIN ieco.asignaturaTipo at
                                                                            WHERE ei.id = :idInscripcion
                                                                            AND at.id = :idAsignatura
                                                                            AND nt.id IN (:promedios)
                                                                            ORDER BY at.id, nt.id')
                                                                            ->setParameter('idInscripcion',$a['idInscripcion'])
                                                                            ->setParameter('idAsignatura',$a['id'])
                                                                            ->setParameter('promedios',array(1,2,3,4,5))
                                                                            ->getResult();

                                                    //dump($asignaturasNotas);die;
                                                    $existePromedio = 'no';
                                                    $cont = 0;
                                                    if($asignaturasNotas){
                                                        $sumaNotas = 0;
                                                        foreach ($asignaturasNotas as $an) {
                                                            if($an['notaTipo'] == 5){
                                                                $existePromedio = 'si';
                                                                $promedio = round($sumaNotas/4);
                                                                $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($an['idNota']);
                                                                $notaPromedio->setNotaCuantitativa($promedio);
                                                                $notaPromedio->setFechaModificacion(new \DateTime('now'));
                                                                $em->flush();
                                                            }else{
                                                                $sumaNotas = $sumaNotas + $an['notaCuantitativa'];
                                                                $cont++;
                                                                $idEstudianteAsignatura = $an['idEstudianteAsignatura'];
                                                            }
                                                        }
                                                    }
                                                    // Si no existe el promedio registramos el promedio
                                                    if($existePromedio == 'no' and $cont == 4){
                                                        $promedio = round($sumaNotas/4);
                                                        $nuevoPromedio = new EstudianteNota();
                                                        $nuevoPromedio->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                                                        $nuevoPromedio->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura));
                                                        $nuevoPromedio->setNotaCuantitativa($promedio);
                                                        $nuevoPromedio->setNotaCualitativa('');
                                                        $nuevoPromedio->setRecomendacion('');
                                                        $nuevoPromedio->setUsuarioId($this->session->get('userId'));
                                                        $nuevoPromedio->setFechaRegistro(new \DateTime('now'));
                                                        $nuevoPromedio->setFechaModificacion(new \DateTime('now'));
                                                        $nuevoPromedio->setObs('');
                                                        $em->persist($nuevoPromedio);
                                                        $em->flush();
                                                    }
                                                    $cont++;
                                                }

                                                /**
                                                 * Ejecutamos la funcion de base de datos
                                                 */
                                                if($inscripcion[0]['estadoMatricula'] != 26){
                                                    $query = $em->getConnection()->prepare("select * from sp_calcula_estadomatricula_inscripcion(".$idInscripcion.")");
                                                    $query->execute();
                                                }
                                                break;
                                            }
                                        }else{
                                            // PAra inicial
                                            $idInscripcion = $solicitud->getEstudianteInscripcionId();
                                            $inscripcionActual = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                                            $inscripcionActual->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                                            $em->flush();
                                        }
                                        break;


                            case 'cualitativa_primaria_secundaria':
                                        $nuevaNotaCualitativa = new EstudianteNotaCualitativa();
                                        $nuevaNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($ds->getNotaTipoId()));
                                        $nuevaNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId()));
                                        $nuevaNotaCualitativa->setNotaCuantitativa(0);
                                        $nuevaNotaCualitativa->setNotaCualitativa($ds->getNotaCualitativaNew());
                                        $nuevaNotaCualitativa->setRecomendacion('');
                                        $nuevaNotaCualitativa->setUsuarioId($this->session->get('userId'));
                                        $nuevaNotaCualitativa->setFechaRegistro(new \DateTime('now'));
                                        $nuevaNotaCualitativa->setFechaModificacion(new \DateTime('now'));
                                        $nuevaNotaCualitativa->setObs('');
                                        $em->persist($nuevaNotaCualitativa);
                                        $em->flush();
                                        break;

                            case 'cualitativa_inicial':
                                        $nuevaNotaCualitativa = new EstudianteNotaCualitativa();
                                        $nuevaNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($ds->getNotaTipoId()));
                                        $nuevaNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($solicitud->getEstudianteInscripcionId()));
                                        $nuevaNotaCualitativa->setNotaCuantitativa(0);
                                        $nuevaNotaCualitativa->setNotaCualitativa(mb_strtoupper($ds->getNotaCualitativaNew(),'utf-8'));
                                        $nuevaNotaCualitativa->setRecomendacion('');
                                        $nuevaNotaCualitativa->setUsuarioId($this->session->get('userId'));
                                        $nuevaNotaCualitativa->setFechaRegistro(new \DateTime('now'));
                                        $nuevaNotaCualitativa->setFechaModificacion(new \DateTime('now'));
                                        $nuevaNotaCualitativa->setObs('');
                                        $em->persist($nuevaNotaCualitativa);
                                        $em->flush();
                                        break;
                        }
                    }


                    // CAmbiamos el estado de la solicitud a aprobado 2
                    $solicitud->setEstado(2);
                    $solicitud->setRespuesta($request->get('observacion'));
                    $solicitud->setUsuarioIdResp($this->session->get('userId'));
                    $solicitud->setFechaResp(new \DateTime('now'));
                    $em->flush();

                    // llamar a la funcion de registro de promedios



                    $em->getConnection()->commit();
                    $this->get('session')->getFlashBag()->add('approved', 'La solicitud fue aprobada');
                    return $this->redirect($this->generateUrl('solicitudAdicionCalificaciones'));
                }else{
                    $em->getConnection()->commit();
                    $solicitud->setEstado(3);
                    $solicitud->setRespuesta($request->get('observacion'));
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('rejected', 'La solicitud fue rechazada');
                    return $this->redirect($this->generateUrl('solicitudAdicionCalificaciones'));
                }
            }
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
}
