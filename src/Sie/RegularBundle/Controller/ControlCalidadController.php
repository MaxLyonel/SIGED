<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\ValidacionOmisionHistoricaEstudiante;
use Sie\AppWebBundle\Entity\IdiomaTipo;

/**
 * Gestión de Menú Controller.
 */
class ControlCalidadController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    // Para Distrito
    public function indexAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9' && $rol_usuario != '31') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $currentyear = $this->session->get('currentyear');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $em = $this->getDoctrine()->getManager();

        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $repository = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo');

        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('vret')
                    ->select('vret.id, vret.entidad, vret.obs')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->where('vp.lugarTipoIdDistrito = :lugarDistrito')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->setParameter('lugarDistrito', $usuario_lugar)
                    ->setParameter('esActivo', true)
                    ->addGroupBy('vret.id, vret.entidad, vret.obs')
                    ->addOrderBy('vret.id')
                    ->getQuery();
                break;
            case 9://unidad educativa
                $query = $repository->createQueryBuilder('vret')
                    ->select('vret.id, vret.entidad, vret.obs')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->where('vp.institucionEducativaId = :sie')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->setParameter('sie', $this->session->get('ie_id'))
                    ->setParameter('esActivo', true)
                    ->addGroupBy('vret.id, vret.entidad, vret.obs')
                    ->addOrderBy('vret.id')
                    ->getQuery();
                break;
            case 7://departamento
                $query = $repository->createQueryBuilder('vret')
                    ->select('vret.id, vret.entidad, vret.obs')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->where('lt.lugarTipo = :lugarDepartamento')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->setParameter('lugarDepartamento', $usuario_lugar)
                    ->setParameter('esActivo', true)
                    ->addGroupBy('vret.id, vret.entidad, vret.obs')
                    ->addOrderBy('vret.id')
                    ->getQuery();
                break;
            case 31://nacional (super usuario)
                $query = $repository->createQueryBuilder('vret')
                    ->select('vret.id, vret.entidad, vret.obs')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->setParameter('esActivo', true)
                    ->addGroupBy('vret.id, vret.entidad, vret.obs')
                    ->addOrderBy('vret.id')
                    ->getQuery();
                break;
        }

        $entidades = $query->getResult();

        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('vret')
                    ->select('gt.id')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'vp.gestionTipo = gt.id')
                    ->where('vp.lugarTipoIdDistrito = :lugarDistrito')
                    ->setParameter('lugarDistrito', $usuario_lugar)
                    ->addGroupBy('gt.id')
                    ->addOrderBy('gt.id', 'DESC')
                    ->getQuery();
                break;
            case 9://unidad educativa
                $query = $repository->createQueryBuilder('vret')
                    ->select('gt.id')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'vp.gestionTipo = gt.id')
                    ->where('vp.institucionEducativaId = :sie')
                    ->setParameter('sie', $this->session->get('ie_id'))
                    ->addGroupBy('gt.id')
                    ->addOrderBy('gt.id', 'DESC')
                    ->getQuery();
                break;
            case 7://departamento
                $query = $repository->createQueryBuilder('vret')
                    ->select('gt.id')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'vp.gestionTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->where('lt.lugarTipo = :lugarDepartamento')
                    ->setParameter('lugarDepartamento', $usuario_lugar)
                    ->addGroupBy('gt.id')
                    ->addOrderBy('gt.id', 'DESC')
                    ->getQuery();
                break;
            case 31://nacional (super usuario)
                $query = $repository->createQueryBuilder('vret')
                    ->select('gt.id')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'vp.gestionTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->addGroupBy('gt.id')
                    ->addOrderBy('gt.id', 'DESC')
                    ->getQuery();
                break;
        }

        $gestiones = $query->getResult();

        return $this->render('SieRegularBundle:ControlCalidad:index.html.twig', array(
                    'entidades' => $entidades,
                    'gestiones' => $gestiones,
                    'currentyear' => $currentyear,
                    'lugar_tipo_id' => $usuario_lugar->getId()
        ));
    }

    public function listAction(Request $request, $id, $gestion) {
        
        $request->getSession()->set('idGestionCalidad',$gestion);
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9' && $rol_usuario != '31') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $entidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($id);

        $roluserlugarid = $this->session->get('roluserlugarid');
        
        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $repository = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo');

        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('vrt')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->where('vrt.validacionReglaEntidadTipo = :reglaEntidad')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.lugarTipoIdDistrito = :lugarDistrito')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->andWhere('vrt.id != :fisquim')
                    ->setParameter('reglaEntidad', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('lugarDistrito', $usuario_lugar)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('esActivo', true)
                    ->setParameter('fisquim', 27)
                    ->orderBy('vrt.id', 'ASC')
                    ->getQuery();
                break;
            case 9://unidad educativa
                $query = $repository->createQueryBuilder('vrt')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->where('vrt.validacionReglaEntidadTipo = :reglaEntidad')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.institucionEducativaId = :sie')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->andWhere('vrt.id != :fisquim')
                    ->setParameter('reglaEntidad', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('sie', $this->session->get('ie_id'))
                    ->setParameter('gestion', $gestion)
                    ->setParameter('esActivo', true)
                    ->setParameter('fisquim', 27)
                    ->orderBy('vrt.id', 'ASC')
                    ->getQuery();
                break;
            case 7://departamento
                $query = $repository->createQueryBuilder('vrt')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->where('vrt.validacionReglaEntidadTipo = :reglaEntidad')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('lt.lugarTipo = :lugarDepartamento')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->andWhere('vrt.id != :fisquim')
                    ->setParameter('reglaEntidad', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('lugarDepartamento', $usuario_lugar)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('esActivo', true)
                    ->setParameter('fisquim', 27)
                    ->orderBy('vrt.id', 'ASC')
                    ->getQuery();
                break;
            case 31://nacional (super usuario)
                $query = $repository->createQueryBuilder('vrt')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = vp.lugarTipoIdDistrito')
                    ->where('vrt.validacionReglaEntidadTipo = :reglaEntidad')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->andWhere('vrt.esActivo = :esActivo')
                    ->andWhere('vrt.id != :fisquim')
                    ->setParameter('reglaEntidad', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('gestion', $gestion)
                    ->setParameter('esActivo', true)
                    ->setParameter('fisquim', 27)
                    ->orderBy('vrt.id', 'ASC')
                    ->getQuery();
                break;
        }
        
        $lista_inconsistencias = $query->getResult();

        return $this->render('SieRegularBundle:ControlCalidad:list.html.twig', array(
                    'lista_inconsistencias' => $lista_inconsistencias,
                    'entidad' => $entidad,
                    'gestion' => $gestion
        ));

    }

    public function detalleAction($id) {
        $id_usuario = $this->session->get('userId');
        $gestion = $this->session->get('idGestionCalidad');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9' && $rol_usuario != '31') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $regla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($id);

        $roluserlugarid = $this->session->get('roluserlugarid');
        
        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $repository = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo');

        $qb = $em->createQueryBuilder();
        $idiomas=$qb->select('i')
             ->from('SieAppWebBundle:IdiomaTipo', 'i')
             ->where('i.esVigente = :esVigente')
             ->addOrderBy('i.id','asc')
             ->setParameter('esVigente','true')
             ->getQuery()
             ->getResult();

        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('vrt')
                    ->select('vp')->distinct()
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->where('vrt.id = :reglaTipo')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.lugarTipoIdDistrito = :lugarDistrito')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->setParameter('reglaTipo', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('lugarDistrito', $usuario_lugar)
                    ->setParameter('gestion', $gestion)
                    ->orderBy('vp.gestionTipo', 'DESC')
                    ->addOrderBy('vp.institucionEducativaId', 'ASC')
                    ->getQuery();
                break;
            case 9://unidad educativa
                $query = $repository->createQueryBuilder('vrt')
                    ->select('vp')->distinct()
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->where('vrt.id = :reglaTipo')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.institucionEducativaId = :sie')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->setParameter('reglaTipo', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('sie', $this->session->get('ie_id'))
                    ->setParameter('gestion', $gestion)
                    ->orderBy('vp.gestionTipo', 'DESC')
                    ->addOrderBy('vp.institucionEducativaId', 'ASC')
                    ->getQuery();
                break;
            case 31://nacional (super usuario)
                $query = $repository->createQueryBuilder('vrt')
                    ->select('vp')->distinct()
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                    ->where('vrt.id = :reglaTipo')
                    ->andWhere('vp.esActivo = :esactivo')
                    ->andWhere('vp.gestionTipo = :gestion')
                    ->setParameter('reglaTipo', $id)
                    ->setParameter('esactivo', 'f')
                    ->setParameter('gestion', $gestion)
                    ->orderBy('vp.gestionTipo', 'DESC')
                    ->addOrderBy('vp.institucionEducativaId', 'ASC')
                    ->getQuery();
                break;
        }

        $lista_detalle = $query->getResult();

        return $this->render('SieRegularBundle:ControlCalidad:lista_detalle.html.twig', array('lista_detalle' => $lista_detalle, 'regla' => $regla,'idiomas'=>$idiomas));
    }

    public function omitirAction(Request $request) {
        $gestion = $this->session->get('idGestionCalidad');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $form = $request->get('form');

            $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
            $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
            $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

            $this->ratificar($vproceso);
            $em->getConnection()->commit();
            $message = 'Se realizó el proceso satisfactoriamente.';
            $this->addFlash('success', $message);

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'No se pudo realizar el proceso.';
            $this->addFlash('warning', $message);
        }

        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    public function ratificarEdadAction(Request $request) {

        $gestion = $this->session->get('idGestionCalidad');

        $em = $this->getDoctrine()->getManager();
        
            $form = $request->get('form');

            $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
            $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
            $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($vproceso->getLlave());
            if($estudiante){
                if($estudiante->getCarnetIdentidad()){
                    $resultadoEdadEstudiante = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($estudiante->getCarnetIdentidad(),array('fecha_nacimiento'=>$estudiante->getFechaNacimiento()->format('d-m-Y')),'prod','academico');
    
                    if($resultadoEdadEstudiante){
                        $mensaje = "Se realizó el proceso satisfactoriamente. Los datos de la/el estudiante se validaron correctamente con SEGIP.";
                        $this->ratificar($vproceso);
                        $this->addFlash('success', $mensaje);
                    } else {
                        $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de el la/el estudiante.";
                        $this->addFlash('warning', $mensaje);
                    }                
                } else {
                    $mensaje = "Se realizó el proceso satisfactoriamente. La validación con SEGIP fue omitida debido a que la/el estudiante no cuenta con C.I.";
                    $this->ratificar($vproceso);
                    $this->addFlash('success', $mensaje);
                }
            } else {
                $mensaje = "Se realizó el proceso satisfactoriamente.";
                $this->ratificar($vproceso);
                $this->addFlash('success', $mensaje);
            }
            
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    private function ratificar($vproceso){
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $arrayRegistro = null;

        try {
            // Antes
            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $antes = json_encode($arrayRegistro);

            // despues
            $arrayRegistro = null;

            $vproceso->setEsActivo(true);
            $vproceso->setOmitido(1);


            $em->persist($vproceso);
            $em->flush();
            // $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);

            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $despues = json_encode($arrayRegistro);

            // registro del log
            $resp = $defaultController->setLogTransaccion(
                $vproceso->getId(),
                'validacion_proceso',
                'U',
                json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
                $this->session->get('userId'),
                '',
                $despues,
                $antes,
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();           
        }
    }

    public function verificarAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        switch ($vregla->getId()) {
            case 1://MATERIAS SIN MAESTROS
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_mate_sin_maestros (:tipo, :bim, :ieco, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':bim', '');
                $query->bindValue(':ieco', $form['llave']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 2://EDAD
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_edad (:tipo, :rude, :sie, :gestion, :lim1, :lim2)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':sie', $form['institucionEducativa']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->bindValue(':lim1', '3');
                $query->bindValue(':lim2', '30');
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 3://GÉNERO
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_genero (:tipo, :rude, :sie, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':sie', $form['institucionEducativa']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 4://NOTAS 4TO BIMESTRE
                $estudiante_inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['llave']);
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudiante_inscripcion->getEstudiante());

                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_insc_estado (:tipo, :rude, :sie, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $estudiante->getCodigoRude());
                $query->bindValue(':sie', $form['institucionEducativa']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 6://ESTADOS
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_estados (:tipo, :rude, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 8://DOS RUDES
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_dos_RUDES (:tipo, :rude, :param, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':param', '');
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 12://INSCRIPCIÓN INCORRECTA
                $query = $em->getConnection()->prepare("SELECT sp_sist_calidad_incorr_insc (:tipo, :rude, :param, :gestion)");
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':param', '');
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 13://DOBLE INSCRIPCIÓN
                $query = $em->getConnection()->prepare("SELECT sp_sist_calidad_doble_insc (:tipo, :rude, :param, :gestion)");
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':param', '');
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 14://MAESTRO SIN MATERIA
                $query = $em->getConnection()->prepare("SELECT sp_sist_calidad_maes_sin_materia (:tipo, :mi_id, :sie, :gestion)");
                $query->bindValue(':tipo', '2');
                $query->bindValue(':mi_id', $form['llave']);
                $query->bindValue(':sie', $form['institucionEducativa']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 16://SIN HISTORIAL
                $query = $em->getConnection()->prepare("SELECT sp_sist_calidad_sin_historial (:tipo, :rude, :param, :gestion)");
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
                $query->bindValue(':param', '');
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 25:
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_cedula_duplicada_nom_diferentes (:tipo, :idDetalle)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':idDetalle', $form['idDetalle']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;

            case 27://PROMEDIOS
                $llave = $form['llave'];
                $parametros = explode('|', $llave);
                $idInscripcion = $parametros[0];
                $idNotaTipo = $parametros[1];

                $query = $em->getConnection()->prepare('select * from sp_sist_calidad_prom_fisquim(:param, :idInscripcion, :idNotaTipo, :sie, :gestion);');
                $query->bindValue(':param', '2');
                $query->bindValue(':idInscripcion', $idInscripcion);
                $query->bindValue(':idNotaTipo', $idNotaTipo);
                $query->bindValue(':sie', $form['institucionEducativa']);
                $query->bindValue(':gestion', $form['gestion']);
                $query->execute();
                $resultado = $query->fetchAll();
                break;
        }

        $message = 'Se realizó la validación satisfactoriamente para la observación: ' . $vproceso->getObs();
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('ccalidad_list', array(
            'id' => $vreglaentidad->getId(),
            'gestion' => $form['gestion']
        )));
    }

    //Gráficos
    public function graficoAction ($id) {
        $id_usuario = $this->session->get('userId');
        $gestion = $this->session->get('idGestionCalidad');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '10' && $rol_usuario != '7') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $regla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($id);

        $repository = $em->getRepository('SieAppWebBundle:Usuario');

        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('u')
                    ->select('lt')
                    ->innerJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'ur.usuario = u.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'ur.lugarTipo = lt.id')
                    ->where('u.id = :idUsuario')
                    ->andwhere('lt.lugarNivel = :nivel')
                    ->andwhere('ur.rolTipo = :rol')
                    ->setParameter('idUsuario', $id_usuario)
                    ->setParameter('nivel', 7)
                    ->setParameter('rol', $rol_usuario)
                    ->getQuery();
                break;
            case 7://departamento
            case 8://nacional
                $query = $repository->createQueryBuilder('u')
                    ->select('lt')
                    ->innerJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'ur.usuario = u.id')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'ur.lugarTipo = lt.id')
                    ->where('u.id = :idUsuario')
                    ->andwhere('ur.rolTipo = :rol')
                    ->setParameter('idUsuario', $id_usuario)
                    ->setParameter('rol', $rol_usuario)
                    ->getQuery();
                break;
        }

        $usuario_lugar = $query->getResult()[0];

        switch($rol_usuario){
            case 10://distrito
                $query = $em->getConnection()->prepare("select a.*
                    from (
                    select vp.gestion_tipo_id,vp.validacion_regla_tipo_id,vp.lugar_tipo_id_distrito,lt.lugar
                    ,sum(case when es_activo=cast('f' as boolean) then 1 else 0 end) as sin_corregir
                    ,sum(case when es_activo=cast('t' as boolean) then 1 else 0 end) as corregido
                    from validacion_proceso vp
                    inner join lugar_tipo lt on vp.lugar_tipo_id_distrito = lt.id
                    where vp.gestion_tipo_id = ".$gestion." and vp.validacion_regla_tipo_id=:regla and vp.lugar_tipo_id_distrito = :distrito
                    group by vp.gestion_tipo_id,vp.validacion_regla_tipo_id,vp.lugar_tipo_id_distrito,lt.lugar) a
                    order by a.lugar_tipo_id_distrito ASC");
                $query->bindValue(':distrito', $usuario_lugar->getId());
                $query->bindValue(':regla', $id);
                $query->execute();
                $datosGrafico = $query->fetchAll();
                break;
            case 7://departamento
            case 8://nacional
                $query = $em->getConnection()->prepare("select a.*
                    from (
                    select vp.gestion_tipo_id,vp.validacion_regla_tipo_id,vp.lugar_tipo_id_distrito,lt.lugar
                    ,sum(case when es_activo=cast('f' as boolean) then 1 else 0 end) as sin_corregir
                    ,sum(case when es_activo=cast('t' as boolean) then 1 else 0 end) as corregido
                    from validacion_proceso vp
                    inner join lugar_tipo lt on vp.lugar_tipo_id_distrito = lt.id
                    where vp.gestion_tipo_id = ".$gestion." and vp.validacion_regla_tipo_id= :regla and lt.lugar_tipo_id = :departamento
                    group by vp.gestion_tipo_id,vp.validacion_regla_tipo_id,vp.lugar_tipo_id_distrito,lt.lugar) a
                    order by a.lugar_tipo_id_distrito ASC");
                $query->bindValue(':departamento', $usuario_lugar->getId());
                $query->bindValue(':regla', $id);
                $query->execute();
                $datosGrafico = $query->fetchAll();
                break;
        }

        return $this->render('SieRegularBundle:ControlCalidad:grafico.html.twig', array(
            'gestion' => $gestion,
            'datosGrafico' => $datosGrafico,
            'regla' => $regla,
            'usuario_lugar' => $usuario_lugar));
    }

    public function repDistritoAction($lugar_tipo_id) {
        $arch = 'REPORTE_Dist_'.$lugar_tipo_id.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_inconsistencias_estadistica_v1_afv.rptdesign&lugar_tipo_id='.$lugar_tipo_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function solucionHistoricoAction($vp_id, $llave, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from sp_calcula_historial_sin_grados_completos_web('".$llave."','".$gestion."')");
        $query->execute();
        $inconsistencias = $query->fetchAll();
        
        return $this->render('SieRegularBundle:ControlCalidad:solucion_historico.html.twig', array(
            'inconsistencias' => $inconsistencias,
            'vp_id' => $vp_id,
            'llave' => $llave,
            'gestion' => $gestion,
            'nroInconsistencias' => sizeof($inconsistencias)
        ));
    }

    /*public function omitirHistoricoAction(Request $request) {
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $gestion = $this->session->get('idGestionCalidad');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('validacion_omision_historica_estudiante');")->execute();
            $omision_historica_estudiante = new ValidacionOmisionHistoricaEstudiante();
            $omision_historica_estudiante->setFechaProceso(new \DateTime('now'));
            $omision_historica_estudiante->setGestionTipoId($form['gestion']);
            $omision_historica_estudiante->setNivelTipoId($form['nivel']);
            $omision_historica_estudiante->setGradoTipoId($form['grado']);
            $omision_historica_estudiante->setCodigoRude($form['codigoRude']);
            $em->persist($omision_historica_estudiante);
            $em->flush();

            $em->getConnection()->commit();
            $message = 'Se realizó el proceso satisfactoriamente.';
            $this->addFlash('success', $message);

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'No se pudo realizar el proceso.';
            $this->addFlash('warning', $message);
        }

        return $this->redirect($this->generateUrl('ccalidad_list', array(
            'id' => $vreglaentidad->getId(), 
            'gestion' => 2017
        )));
    }*/

    public function omitirHistoricoAction($vp_id, $llave, $gestion, $nivel, $grado) {
        
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($vp_id);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());
        $gestioncalidad = $this->session->get('idGestionCalidad');
        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('validacion_omision_historica_estudiante');")->execute();
            $omision_historica_estudiante = new ValidacionOmisionHistoricaEstudiante();
            $omision_historica_estudiante->setFechaProceso(new \DateTime('now'));
            $omision_historica_estudiante->setGestionTipoId($gestion);
            $omision_historica_estudiante->setNivelTipoId($nivel);
            $omision_historica_estudiante->setGradoTipoId($grado);
            $omision_historica_estudiante->setCodigoRude($llave);
            $em->persist($omision_historica_estudiante);
            $em->flush();

            $em->getConnection()->commit();
            $message = 'Se realizó el proceso satisfactoriamente.';
            $this->addFlash('success', $message);

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'No se pudo realizar el proceso.';
            $this->addFlash('warning', $message);
        }

        $query = $em->getConnection()->prepare("select * from sp_calcula_historial_sin_grados_completos_web('".$llave."','".$gestioncalidad."')");
        $query->execute();
        $inconsistencias = $query->fetchAll();
        
        return $this->render('SieRegularBundle:ControlCalidad:solucion_historico.html.twig', array(
            'inconsistencias' => $inconsistencias,
            'vp_id' => $vp_id,
            'llave' => $llave,
            'gestion' => $gestioncalidad
        ));
    }

    public function validarEstudianteSegipAction(Request $request) {

        $gestion = $this->session->get('idGestionCalidad');

        $em = $this->getDoctrine()->getManager();
        
        $form = $request->get('form');

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($vproceso->getLlave());

        $datos = array(
            'complemento'=>$estudiante->getComplemento(),
            'primer_apellido'=>$estudiante->getPaterno(),
            'segundo_apellido'=>$estudiante->getMaterno(),
            'nombre'=>$estudiante->getNombre(),
            'fecha_nacimiento'=>$estudiante->getFechaNacimiento()->format('d-m-Y')
        );
        
        if($estudiante){
            if($estudiante->getCarnetIdentidad()){
                $resultadoEstudiante = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($estudiante->getCarnetIdentidad(),$datos,'prod','academico');

                if($resultadoEstudiante){
                    $mensaje = "Se realizó el proceso satisfactoriamente. Los datos de la/el estudiante:".$estudiante->getCodigoRude().", se validaron correctamente con SEGIP.";
                    $estudiante->setSegipId(1);
                    $em->persist($estudiante);
                    $em->flush();
                    $this->ratificar($vproceso);
                    $this->addFlash('success', $mensaje);
                } else {
                    $mensaje = "No se realizó la validación con SEGIP. Verifique la información de el la/el estudiante.";
                    $this->addFlash('warning', $mensaje);
                }                
            } else {
                $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de el la/el estudiante.";
                $this->addFlash('warning', $mensaje);
            }
        } else {
            $mensaje = "No se realizó la validación con SEGIP. No existe información de la /el estudiante con el código RUDE proporcionado.";
            $this->addFlash('warning', $mensaje);
        }
            
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    public function justificarEstudianteSegipAction(Request $request) {
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $gestion = $this->session->get('idGestionCalidad');
        $form = $request->get('formJ');
        $justificacion= mb_strtoupper($form['justificacion'], 'utf-8');
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($vproceso->getLlave());

        try {
            $antes = json_encode([
                'carnet_identidad'=>$estudiante->getCarnetIdentidad(),
                'complemento'=>$estudiante->getCarnetIdentidad(),
                'paterno'=>$estudiante->getPaterno(),
                'materno'=>$estudiante->getMaterno(),
                'nombre'=>$estudiante->getNombre(),
                'fecha_nacimiento'=>$estudiante->getFechaNacimiento(),
                'segip_id'=>$estudiante->getSegipId()
                
            ]);

            $estudiante->setCarnetIdentidad('');
            $estudiante->setComplemento('');
            $em->flush();

            $despues = json_encode([
                'carnet_identidad'=>$estudiante->getCarnetIdentidad(),
                'complemento'=>$estudiante->getCarnetIdentidad(),
                'paterno'=>$estudiante->getPaterno(),
                'materno'=>$estudiante->getMaterno(),
                'nombre'=>$estudiante->getNombre(),
                'fecha_nacimiento'=>$estudiante->getFechaNacimiento(),
                'segip_id'=>$estudiante->getSegipId()
            ]);

            // registro del log
            $resp = $defaultController->setLogTransaccion(
                $estudiante->getId(),
                'estudiante',
                'U',
                json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
                $this->session->get('userId'),
                '',
                $despues,
                $antes,
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            if($vproceso){
                $mensaje = "Se realizó el proceso satisfactoriamente: ".$justificacion.".";
                $vproceso->setJustificacion($justificacion);
                $em->persist($vproceso);
                $em->flush();
                $this->ratificar($vproceso);
                $this->addFlash('success', $mensaje);
            } else {
                $mensaje = "No se encontró la inconsistencia.";
                $this->addFlash('warning', $mensaje);
            }

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();           
        }
            
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    public function justificarHomonimoGemeloAction(Request $request) {
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $gestion = $this->session->get('idGestionCalidad');
        $form = $request->get('formH');
        $justificacion= mb_strtoupper($form['justificacion'], 'utf-8');
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        try {
            if($vproceso){
                $mensaje = "Se realizó el proceso satisfactoriamente: ".$justificacion.".";
                $vproceso->setJustificacion($justificacion);
                $em->persist($vproceso);
                $em->flush();
                $this->ratificar($vproceso);
                $this->addFlash('success', $mensaje);
            } else {
                $mensaje = "No se encontró la inconsistencia.";
                $this->addFlash('warning', $mensaje);
            }

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
            
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    public function justificarModificarDatosAction(Request $request) {
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $gestion = $this->session->get('idGestionCalidad');
        $form = $request->get('formMD');
        $justificacion= mb_strtoupper($form['justificacion'], 'utf-8');
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        try {
            if($vproceso){
                $mensaje = "Se realizó el proceso satisfactoriamente: ".$justificacion.".";
                $vproceso->setJustificacion($justificacion);
                $em->persist($vproceso);
                $em->flush();
                $this->ratificar($vproceso);
                $this->addFlash('success', $mensaje);
            } else {
                $mensaje = "No se encontró la inconsistencia.";
                $this->addFlash('warning', $mensaje);
            }

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
            
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    /**
     * Esta funcion corrige la observacion  de: "Inconsistencias respecto a Grados sin desayuno escolar respecto a la gestion 2014" (validacion_regla_tipo=17)
     *
     * @return json ($data=id,$status,$msj)
     * @author lnina
     **/
    public function calidad_resolverInconsistenciasGradosSinDesayunoAction(Request $request)
    {
        $esAjax=$request->isXmlHttpRequest();
        if($esAjax)
        {
            $em                     = $this->getDoctrine()->getManager();
            $form                   = $request->request->all();

            $request_llave          = filter_var($form['id'],FILTER_SANITIZE_NUMBER_INT);
            $request_desayuno       = filter_var($form['desayunoEscolar'],FILTER_SANITIZE_NUMBER_INT);
            $request_financiamiento = filter_var($form['finDesEscolarTipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_inconsistencia = filter_var($form['inconsistencia'],FILTER_SANITIZE_NUMBER_INT);
            $request_ue             = filter_var($form['ue'],FILTER_SANITIZE_NUMBER_INT);

            $request_llave          = empty($request_llave)?-1:$request_llave;
            $request_desayuno       = (empty($request_desayuno) && !is_numeric($request_desayuno))?-1:$request_desayuno;
            $request_financiamiento = empty($request_financiamiento)?-1:$request_financiamiento;
            $request_inconsistencia = empty($request_inconsistencia)?-1:$request_inconsistencia;
            $request_ue             = empty($request_ue)?-1:$request_ue;

            $data   = NULL;
            $status = 404;
            $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';

            if($request_llave>0 && $request_inconsistencia>0 && $request_ue>0)
            {
                $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(array('id'=>$request_inconsistencia,'InstitucionEducativa'=>$request_ue));
                $curso       = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find(array('id'=>$request_llave));

                if($curso && $observacion)
                {
                    $em->getConnection()->beginTransaction();
                    if($request_desayuno == 0 || $request_desayuno == 1)
                    {
                        if($request_desayuno == 1)
                        {
                            if($request_financiamiento>0)
                            {
                                $financiamiento = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($request_financiamiento);
                            }
                            else
                            {
                                $financiamiento = null;
                            }
                        }
                        else //$request_desayuno=1
                        {
                            $financiamiento=$em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find(0);
                        }

                        if($financiamiento)
                        {
                            //asignamos los datos del desayuno
                            $curso->setDesayunoEscolar($request_desayuno);
                            $curso->setFinDesEscolarTipo($financiamiento);

                            //ahora cambiamos el estado de la observación
                            $observacion->setEsActivo('t');

                            //guardamos los datos
                            $em->persist($curso);
                            $em->persist($observacion);
                            $em->flush();
                            //Confirmamos los cambios
                            $em->getConnection()->commit();
                            $data   = $observacion->getId();
                            $status = 200;
                            $msj    = 'La observación fue corregida exitosamente';
                        }
                        else
                        {
                            $em->getConnection()->rollback();
                            $data   = NULL;
                            $status = 404;
                            $msj    = 'Debe seleccionar una fuente de financiamiento del desayuno';
                        }
                    }
                    else
                    {
                        $em->getConnection()->rollback();
                        $data   = NULL;
                        $status = 404;
                        $msj    = 'Debe seleccionar SI se proporciona o NO desayuno escolar al curso';
                    }
                }
                else
                {
                    $data   = NULL;
                    $status = 404;
                    $msj    = 'La observación no existe o el curso seleccionado no existe, por favor vuelva a intentarlo';
                }
            }
            else
            {
                $data   = NULL;
                $status = 404;
                $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';
            }
            $response = new JsonResponse($data,$status);
            $response->headers->set('Content-Type', 'application/json');
            return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
        }
        else
        {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    /**
     * Esta funcion corrige la observacion  de: "Inconsistencias respecto a Grados sin idioma de enseñanza en el aula respecto a la gestion 2015" (validacion_regla_tipo=19)
     * Esta funcion corrige la observacion  de: "Grados sin idioma que hablan los estudiantes respecto a la gestion 2015" (validacion_regla_tipo=18)
     *
     * @return json ($data=id,$status,$msj)
     * @author lnina
     **/
    public function calidad_resolverInconsistenciasGradosSinEnsenanzaAulaAction(Request $request)
    {
        $esAjax=$request->isXmlHttpRequest();
        if($esAjax)
        {
            $em                     = $this->getDoctrine()->getManager();
            $form                   = $request->request->all();

            $request_idioma_1       = filter_var($form['priLenEnsenanzaTipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_idioma_2       = filter_var($form['segLenEnsenanzaTipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_idioma_3       = filter_var($form['terLenEnsenanzaTipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_tipo           = filter_var($form['tipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_llave          = filter_var($form['id'],FILTER_SANITIZE_NUMBER_INT);
            $request_inconsistencia = filter_var($form['inconsistencia'],FILTER_SANITIZE_NUMBER_INT);
            $request_ue             = filter_var($form['ue'],FILTER_SANITIZE_NUMBER_INT);

            $request_idioma_1       = empty($request_idioma_1)?-1:$request_idioma_1;
            $request_idioma_2       = empty($request_idioma_2)?-1:$request_idioma_2;
            $request_idioma_3       = empty($request_idioma_3)?-1:$request_idioma_3;
            $request_tipo           = empty($request_tipo)?-1:$request_tipo;
            $request_tipo           = ($request_tipo==18 || $request_tipo==19)?$request_tipo:-1;
            $request_llave          = empty($request_llave)?-1:$request_llave;
            $request_inconsistencia = empty($request_inconsistencia)?-1:$request_inconsistencia;
            $request_ue             = empty($request_ue)?-1:$request_ue;

            $data   = NULL;
            $status = 404;
            $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';

            if( $request_idioma_1>0 && $request_idioma_2>0 && $request_idioma_3>0  && $request_tipo >0)
            {
                if($request_llave>0 && $request_inconsistencia>0 && $request_ue>0)
                {
                    $curso       = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find(array('id'=>$request_llave));
                    $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(array('id'=>$request_inconsistencia,'InstitucionEducativa'=>$request_ue));

                    if($curso && $observacion)
                    {
                        $em->getConnection()->beginTransaction();

                        $tmpIdioma1=$em->getRepository('SieAppWebBundle:IdiomaTipo')->find($request_idioma_1);
                        $tmpIdioma2=$em->getRepository('SieAppWebBundle:IdiomaTipo')->find($request_idioma_2);
                        $tmpIdioma3=$em->getRepository('SieAppWebBundle:IdiomaTipo')->find($request_idioma_3);

                        //verificamos que los idiomas existan en la tabla
                        if($tmpIdioma1 && $tmpIdioma2 && $tmpIdioma3)
                        {
                            if($request_tipo==19)
                            {
                                //asignamos los idiomas
                                $curso->setPriLenEnsenanzaTipo( $tmpIdioma1 );
                                $curso->setSegLenEnsenanzaTipo( $tmpIdioma2 );
                                $curso->setTerLenEnsenanzaTipo( $tmpIdioma3 );
                            }
                            else // $request_tipo==18
                            {
                                $curso->setIdiomaMasHabladoTipo( $tmpIdioma1 );
                                $curso->setIdiomaRegHabladoTipo( $tmpIdioma2 );
                                $curso->setIdiomaMenHabladoTipo( $tmpIdioma3 );
                            }

                            //ahora cambiamos el estado de la observación
                            $observacion->setEsActivo('t');

                            //guardamos los datos
                            $em->persist($curso);
                            $em->persist($observacion);
                            $em->flush();
                            //Confirmamos los cambios
                            $em->getConnection()->commit();
                            $data   = $observacion->getId();
                            $status = 200;
                            $msj    = 'La observación fue corregida exitosamente';
                        }
                        else
                        {
                            $em->getConnection()->rollback();
                            $data   = NULL;
                            $status = 404;
                            if($request_tipo==19)
                                $msj    = 'Debe selecionar la primera, segunda y tercera lengua del proceso pedagógico';
                            else
                                $msj    = 'Debe selecionar la primera, segunda y tercera lengua en el ámbito escolar';
                        }
                    }
                    else
                    {
                        $data   = NULL;
                        $status = 404;
                        $msj    = 'La observación no existe o el curso seleccionado no existe, por favor vuelva a intentarlo';
                    }
                }
                else
                {
                    $data   = NULL;
                    $status = 404;
                    $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';
                }
            }
            else
            {
                $data   = NULL;
                $status = 404;
                if($request_tipo==19)
                    $msj    = 'Debe selecionar la primera, segunda y tercera lengua del proceso pedagógico';
                else
                    $msj    = 'Debe selecionar la primera, segunda y tercera lengua en el ámbito escolar';
            }
            $response = new JsonResponse($data,$status);
            $response->headers->set('Content-Type', 'application/json');
            return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
        }
        else
        {
            return $this->redirect($this->generateUrl('login'));
        }
    }

}
