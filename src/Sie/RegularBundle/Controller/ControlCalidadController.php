<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\ValidacionOmisionHistoricaEstudiante;

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

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9') {
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

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9') {
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
                    ->getQuery();
                break;
        }
        
        $lista_inconsistencias = $query->getResult();
        dump($lista_inconsistencias);die;
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

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '9') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $regla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($id);

        $roluserlugarid = $this->session->get('roluserlugarid');
        
        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $repository = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo');

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
                    ->addOrderBy('vp.gestionTipo', 'desc')
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
                    ->addOrderBy('vp.gestionTipo', 'desc')
                    ->getQuery();
                break;
        }

        $lista_detalle = $query->getResult();

        return $this->render('SieRegularBundle:ControlCalidad:lista_detalle.html.twig', array('lista_detalle' => $lista_detalle, 'regla' => $regla));
    }

    public function omitirAction(Request $request) {

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $gestion = $this->session->get('idGestionCalidad');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $form = $request->get('form');

            // Antes
            $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
            $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
            $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

            $arrayRegistro = null;

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

            $vproceso->setEsActivo(true);//1 - $vproceso->getEsActivo()
            $vproceso->setOmitido(1);


            $em->persist($vproceso);
            $em->flush();
            $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);

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
            $message = 'Se realizó el proceso satisfactoriamente.';
            $this->addFlash('success', $message);

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'No se pudo realizar el proceso.';
            $this->addFlash('warning', $message);
        }

        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));
    }

    public function verificarAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());

        switch ($vregla->getId()) {
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

}
