<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\ValidacionOmisionHistoricaEstudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcionCambioestado;
use Sie\AppWebBundle\Entity\IdiomaTipo;
use Sie\AppWebBundle\Entity\Persona;

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

        // if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        // }

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
        $antyear = $currentyear - 1;
        switch($rol_usuario){
            case 10://distrito
                $query = $repository->createQueryBuilder('vret')
                    ->select('gt.id')
                    ->innerJoin('SieAppWebBundle:ValidacionReglaTipo', 'vrt', 'WITH', 'vrt.validacionReglaEntidadTipo = vret.id')
                    ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vp.validacionReglaTipo = vrt.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'vp.gestionTipo = gt.id')
                    ->where('vp.lugarTipoIdDistrito = :lugarDistrito')
                    ->andWhere('gt.id >= :gestion')  
                    ->setParameter('gestion', $antyear)
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
                    ->andWhere('gt.id >= :gestion')  
                    ->setParameter('gestion', $antyear)
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
                    ->andWhere('gt.id >= :gestion')  
                    ->setParameter('gestion', $antyear)
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
                    ->where('gt.id >= :gestion')  
                    ->setParameter('gestion', $antyear)
                    ->addGroupBy('gt.id')
                    ->addOrderBy('gt.id', 'DESC')
                    ->getQuery();
                break;
        }

        $gestiones = $query->getResult();

        return $this->render($this->session->get('pathSystem').':ControlCalidad:index.html.twig', array(
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

        return $this->render($this->session->get('pathSystem').':ControlCalidad:list.html.twig', array(
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

        if ($rol_usuario != '10' && $rol_usuario != '7' && $rol_usuario != '8' && $rol_usuario != '9' && $rol_usuario != '31') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $regla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($id);

        $roluserlugarid = $this->session->get('roluserlugarid');
        
        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);
        $dep = $usuario_lugar->getCodigo();
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
                case 7://departamento
                    $query = $repository->createQueryBuilder('vrt')
                        ->select('vp')->distinct()
                        ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
                        ->innerJoin('SieAppWebBundle:LugarTipo', 'l', 'WITH', 'vp.lugarTipoIdDistrito = l.id')
                        ->where('vrt.id = :reglaTipo')
                        ->andWhere('vp.esActivo = :esactivo')
                        ->andWhere('vp.gestionTipo = :gestion')
                        ->andWhere('l.codigo like :codigo')
                        ->setParameter('reglaTipo', $id)
                        ->setParameter('esactivo', 'f')
                        ->setParameter('gestion', $gestion)
                        ->setParameter('codigo', $dep .'%')
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
      //dump($lista_detalle);die;
        // return $this->render('SieRegularBundle:ControlCalidad:lista_detalle.html.twig', array('lista_detalle' => $lista_detalle, 'regla' => $regla,'idiomas'=>$idiomas));

        //listar estado
        $query = $em->getConnection()->prepare("SELECT id,estadomatricula from estadomatricula_tipo  WHERE (id='4' OR id='6' OR id='10') ORDER BY estadomatricula asc");
        $query->execute();
        $lista_estado = $query->fetchAll();
        // dump($lista_estado);die;
        return $this->render($this->session->get('pathSystem').':ControlCalidad:lista_detalle.html.twig', array('lista_detalle' => $lista_detalle, 'regla' => $regla,'idiomas'=>$idiomas,'lista_estado'=>$lista_estado));
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
            
            case 11://INSCRIPCIÓN INCORRECTA
                $query = $em->getConnection()->prepare("SELECT sp_sist_calidad_desdoble (:tipo, :rude)");
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
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

            case 24:
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_cedula_duplicada_nom_iguales (:tipo, :idDetalle)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':idDetalle', $form['idDetalle']);
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

            case 26:
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_similitud_nombres_certf_nac (:tipo, :rude)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $form['llave']);
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

        return $this->render($this->session->get('pathSystem').':ControlCalidad:grafico.html.twig', array(
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
        
        return $this->render($this->session->get('pathSystem').':ControlCalidad:solucion_historico.html.twig', array(
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
        
        return $this->render($this->session->get('pathSystem').':ControlCalidad:solucion_historico.html.twig', array(
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

        // if ($vproceso->getValidacionReglaTipo()->getId() == 37 ){
        //     $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$vproceso->getLlave()));
        // } else {
        //     $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($vproceso->getLlave());
        // }
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($vproceso->getLlave());

        $datos = array(
            'complemento'=>$estudiante->getComplemento(),
            'primer_apellido'=>$estudiante->getPaterno(),
            'segundo_apellido'=>$estudiante->getMaterno(),
            'nombre'=>$estudiante->getNombre(),
            'fecha_nacimiento'=>$estudiante->getFechaNacimiento()->format('d-m-Y')
        );

        $cedulaTipoId = 1;
        if($estudiante->getCedulaTipo()){
            $cedulaTipoId = $estudiante->getCedulaTipo()->getId();
        }
        if($cedulaTipoId == 2){
            $datos['extranjero'] = 'E';
        } 
        
        if($estudiante){
            if($estudiante->getCarnetIdentidad()){
                $resultadoEstudiante = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($estudiante->getCarnetIdentidad(),$datos,'prod','academico');

                if($resultadoEstudiante){
                    $mensaje = "Se realizó el proceso satisfactoriamente. Los datos de la/el estudiante:".$estudiante->getCodigoRude().", se validaron correctamente con SEGIP.";
                    $estudiante->setSegipId(1);
                    $estudiante->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipoId) );
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


    public function calidad_buscarPersonaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $esAjax=$request->isXmlHttpRequest();
        $request_id = $request->get('id');
        $request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
        $request_id = is_numeric($request_id)?$request_id:-1;

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo.';
        try
        {
            if($esAjax && $request_id >0)
            {
                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request_id);
                if($persona)
                {
                    $serializer = $this->get('serializer');
                    $data= array(
                        'carnet' => $persona->getCarnet(),
                        'complemento' => $persona->getComplemento(),
                        'fechaNacimiento' => $persona->getFechaNacimiento()->format('d-m-Y'),
                        'nombre' => $persona->getNombre(),
                        'paterno' => $persona->getPaterno(),
                        'materno' => $persona->getMaterno(),
                        'extranjero' => $persona->getEsExtranjero(),
                    );
                    //$data = $serializer->serialize($persona, 'json');
                    $status= 200;
                    $msj='Persona encontrada';
                }
                else
                {
                    $data=null;
                    $status= 404;
                    $msj='No existe la persona.';
                }
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='Ocurrio un error, por favor vuelva a intentarlo.';
            }
        }
        catch(Exception $e)
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo.';
        }
        
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }

    public function verificaRelacionTablaMaestroInscripcion ($idPersona)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $query ='
            SELECT *
            FROM
            "public".persona
            INNER JOIN "public".maestro_inscripcion ON "public".maestro_inscripcion.persona_id = "public".persona."id"
            INNER JOIN "public".cargo_tipo ON "public".maestro_inscripcion.cargo_tipo_id = "public".cargo_tipo."id"
            WHERE
            "public".persona."id" = ?
        ';
        $stmt = $db->prepare($query);
        $params = array($idPersona);
        $stmt->execute($params);
        $datos=$stmt->fetchAll();

        return $datos;
    }

    /*
    //regla nro. 60
    public function calidad_resolver_inconsistencias_apoderado ($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia)
    {
        $em = $this->getDoctrine()->getManager();
        $data   = NULL;
        $status = 404;
        $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';

        list($request_carnet, $request_complemento, $request_fechaNacimiento) = $datosPersona;
        list($request_llave , $request_inconsistencia , $request_ue) = $datosInconsistencia;

        if( $personaValida )
        {
            $arrayDatosPersona['carnet']=$request_carnet;
            unset($arrayDatosPersona['fecha_nacimiento']);
            $arrayDatosPersona['fechaNacimiento']=$request_fechaNacimiento;
            //$persona = $this->get('buscarpersonautils')->buscarPersonav2($arrayDatosPersona,$conCI=true, $segipId=1);
            $persona = $em->getRepository('SieAppWebBundle:Persona')->find(array('id'=>$request_llave));
            $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(array('id'=>$request_inconsistencia,'InstitucionEducativa'=>$request_ue));
            if($persona && $observacion)
            {
                try
                {
                    $em->getConnection()->beginTransaction();
                    //asignamos el carnet
                    $persona->setCarnet($request_carnet);
                    $persona->setComplemento($request_complemento);
                    $persona->setSegipId(1);
                    //ahora cambiamos el estado de la observación
                    $observacion->setEsActivo('t');
                    //guardamos los datos
                    $em->persist($persona);
                    $em->persist($observacion);
                    $em->flush();
                    //Confirmamos los cambios
                    $em->getConnection()->commit();
                    $data   = $observacion->getId();
                    $status = 200;
                    $msj    = 'La observación fue corregida exitosamente';
                }
                catch (Exception $e)
                {
                    $em->getConnection()->rollback();
                    $data   = NULL;
                    $status = 404;
                    $msj    = 'La observación no pudo ser corregida, ocurrio un error desconocido, por favor vuelva a intentarlo.';
                }
            }
            else
            {
                $data   = NULL;
                $status = 404;
                $msj    = 'No existe la persona, por favor vuelva a intentarlo';
            }
        }
        else
        {
            $data   = NULL;
            $status = 404;
            $msj    = 'Los datos enviados no son validos según SEGIP.';
        }
        return array($data,$status,$msj);
    }
    //regla nro. 61
    public function calidad_resolver_inconsistencias_apoderadoAdministrativo ($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia)
    {
    }
    //regla nro. 62
    public function calidad_resolver_inconsistencias_maestroAdministrativo ($personaA, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia)
    */

    public function calidad_resolver_inconsistencias_persona ($personaObs, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia,$request_tipo,$observacion)
    {
        $em = $this->getDoctrine()->getManager();
        list($request_carnet, $request_complemento, $request_fechaNacimiento) = $datosPersona;
        list($request_llave , $request_inconsistencia , $request_ue) = $datosInconsistencia;

        $personaTieneRelacionOtraTabla = $this->verificaRelacionTablaMaestroInscripcion($request_llave);

        $data = NULL;
        $status = 404;
        $msj = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo.';
        
        if($personaValida)
        {
            if($personaObs)
            {
                try
                {
                    $em->getConnection()->beginTransaction();
                    $qb = $em->createQueryBuilder();
                    //$personaOk = $em->getRepository('SieAppWebBundle:Persona')->findBy(array('carnet'=>$personaObs->getCarnet(), 'complemento'=>$personaObs->getComplemento(),'fechaNacimiento'=>$personaObs->getFechaNacimiento() ,'id <>'=>$request_llave) );

                    $personaOk = $qb->select('p')
                         ->from('SieAppWebBundle:Persona', 'p')
                         ->where('p.carnet = :carnet')
                         ->andWhere('p.complemento = :complemento')
                         ->andWhere('p.fechaNacimiento = :fechaNacimiento')
                         ->setParameters( array('carnet'=>$request_carnet, 'complemento'=>$request_complemento,'fechaNacimiento'=>new \DateTime($request_fechaNacimiento)) )
                         ->getQuery()
                         ->getOneOrNullResult();

                    if( $personaOk )
                    {
                        $personaOk->setNombre($arrayDatosPersona['nombre']);
                        $personaOk->setPaterno($arrayDatosPersona['primer_apellido']);
                        $personaOk->setMaterno($arrayDatosPersona['segundo_apellido']);
                        $em->persist($personaOk);
                    }

                    $idPersonaObs = -1;
                    $idPersonaOk = -1;
                    if($personaObs)
                        $idPersonaObs = $personaObs->getId();

                    if($personaOk)
                        $idPersonaOk = $personaOk->getId();

                    if( $personaOk == null || ($idPersonaObs == $idPersonaOk) )
                    {
                        $personaObs->setCarnet($request_carnet);
                        $personaObs->setComplemento($request_complemento);
                        $personaObs->setFechaNacimiento(new \DateTime($request_fechaNacimiento));

                        $personaObs->setNombre($arrayDatosPersona['nombre']);
                        $personaObs->setPaterno($arrayDatosPersona['primer_apellido']);
                        $personaObs->setMaterno($arrayDatosPersona['segundo_apellido']);
                        $em->persist($personaObs);
                    }
                    else
                    {
                        //BJP 2021-Apoderados con SEGIP INCONSISTENTE (regla 60)
                        if( $request_tipo == 60 )
                        {
                            //apoderado - inscripcion
                            $apoderadoInscripciones = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($apoderadoInscripciones as $ai)
                            {
                                if($ai)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $ai->getId() ) );
                                    //if($ai->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $ai->setPersona($personaOk);
                                        $em->persist($ai);
                                    }
                                }
                            }

                            //bjp - apoderado - inscripcion
                            $bjpApoderadoInscripciones = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($bjpApoderadoInscripciones as $bai)
                            {
                                if($bai)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $bai->getId() ) );
                                    //dump($ai->getPersona()->getId().' != '.$personaOk->getId());
                                    //if($ai->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $bai->setPersona($personaOk);
                                        $em->persist($bai);
                                    }
                                }
                            }
                        }

                        //BJP 2021-Usuarios APODERADOS Y ADMINISTRATIVOS con SEGIP INCONSISTENTE (regla 61)
                        if( $request_tipo == 61 )
                        {
                            //apoderado - inscripcion
                            $apoderadoInscripciones = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($apoderadoInscripciones as $ai)
                            {
                                if($ai)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $ai->getId() ) );
                                    //if($ai->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $ai->setPersona($personaOk);
                                        $em->persist($ai);
                                    }
                                }
                            }

                            //bjp - apoderado - inscripcion
                            $bjpApoderadoInscripciones = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($bjpApoderadoInscripciones as $bai)
                            {
                                if($bai)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $bai->getId() ) );
                                    //dump($ai->getPersona()->getId().' != '.$personaOk->getId());
                                    //if($ai->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $bai->setPersona($personaOk);
                                        $em->persist($bai);
                                    }
                                }
                            }

                            //administrativos - inscripcion
                            $maestroInscripciones = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($maestroInscripciones as $mi)
                            {
                                if($mi)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $mi->getId() ) );
                                    //if($mi->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $mi->setPersona($personaOk);
                                        $em->persist($mi);
                                    }
                                }
                            }
                        }

                        //BJP 2021-Maestros MAESTROS Y ADMINISTRATIVOS con SEGIP INCONSISTENTE (regla 62)
                        if( $request_tipo == 62 )
                        {
                            //apoderado - inscripcion
                            $apoderadoInscripciones = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($apoderadoInscripciones as $ai)
                            {
                                if($ai)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $ai->getId() ) );
                                    //dump($ai->getPersona()->getId().' != '.$personaOk->getId());
                                    //if($ai->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $ai->setPersona($personaOk);
                                        $em->persist($ai);
                                    }
                                }
                            }

                            //maestro - inscripcion
                            $maestroInscripciones = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array( 'persona' => $personaObs ) );
                            foreach ($maestroInscripciones as $mi)
                            {
                                if($mi)
                                {
                                    $existe = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array( 'persona' => $personaOk , 'id' => $mi->getId() ) );
                                    //dump($mi->getPersona()->getId().' != '.$personaOk->getId());
                                    //if($mi->getPersona()->getId() != $personaOk->getId())
                                    if(!$existe)
                                    {
                                        $mi->setPersona($personaOk);
                                        $em->persist($mi);
                                    }
                                }
                            }
                        }
                        $em->remove($personaObs);
                    }
                    $em->flush();
                    $em->getConnection()->commit();

                    $data   = $observacion->getId();
                    $status = 200;
                    $msj    = 'La observación fue corregida exitosamente';
                }
                catch (Exception $e)
                {
                    $em->getConnection()->rollback();
                    $data   = NULL;
                    $status = 404;
                    $msj    = 'Acaba de ocurrir un error desconocido, no se resolvio la inconsistencia por favor vuelva a intentarlo.';
                }
            }
            else
            {
                $data   = NULL;
                $status = 404;
                $msj    = 'La persona con la inconsistencia no existe.';
            }
        }
        else
        {
            $data   = NULL;
            $status = 404;
            $msj    = 'Los datos enviados no son validos según SEGIP.';
        }
        return array($data,$status,$msj);
    }

    public function calidad_resolverInconsistenciasSegipAction(Request $request)
    {
        $esAjax=$request->isXmlHttpRequest();
        if($esAjax)
        {
            $em = $this->getDoctrine()->getManager();
            $form = $request->request->all();

            $request_carnet = filter_var(trim($form['carnet']),FILTER_SANITIZE_NUMBER_INT);
            $request_extranjero = isset($form['extranjero'])?true:false;
            $request_complemento = trim($form['complemento']);
            $request_fechaNacimiento = trim($form['fecha_nacimiento']);

            $request_nombre = trim($form['nombre']);
            $request_paterno = trim($form['paterno']);
            $request_materno = trim($form['materno']);

            $request_nombre = isset($request_nombre) ? $request_nombre : '' ;
            $request_paterno = isset($request_paterno) ? $request_paterno : '' ;
            $request_materno = isset($request_materno) ? $request_materno : '' ;

            $request_tipo = filter_var($form['tipo'],FILTER_SANITIZE_NUMBER_INT);
            $request_llave = filter_var($form['id'],FILTER_SANITIZE_NUMBER_INT);
            $request_inconsistencia = filter_var($form['inconsistencia'],FILTER_SANITIZE_NUMBER_INT);
            $request_ue = filter_var($form['ue'],FILTER_SANITIZE_NUMBER_INT);

            $request_carnet = empty($request_carnet)?-1:$request_carnet;
            $request_fechaNacimiento = empty($request_fechaNacimiento)?-1:$request_fechaNacimiento;

            $request_tipo = empty($request_tipo)?-1:$request_tipo;

            $request_tipo = ($request_tipo==60 || $request_tipo==61 || $request_tipo==62)?$request_tipo:-1;
            $request_llave = empty($request_llave)?-1:$request_llave;
            $request_inconsistencia = empty($request_inconsistencia)?-1:$request_inconsistencia;
            $request_ue = empty($request_ue)?-1:$request_ue;

            $data   = NULL;
            $status = 404;
            $msj    = 'Acaba de ocurrir un error desconocido, por favor vuelva a intentarlo';

            if( $request_carnet >0 && $request_fechaNacimiento>0 && $request_tipo >0 && strlen($request_nombre) >0 && strlen($request_paterno)>0 && strlen($request_materno)>0)
            {
                if($request_llave>0 && $request_inconsistencia>0 && $request_ue>0)
                {

                    $fecha = str_replace('-','/',$request_fechaNacimiento);
                    $complemento = strlen($request_complemento) == 0 ? '':$request_complemento;
                    $arrayDatosPersona = array(
                        //'carnet'=>$form['carnet'],
                        'nombre' => $request_nombre,
                        'primer_apellido' => $request_paterno,
                        'segundo_apellido' => $request_materno,
                        'complemento'=>$complemento,
                        'fecha_nacimiento' => $fecha
                    );

                    if($request_extranjero)
                        $arrayDatosPersona['extranjero']='extranjero';
                    
                    $personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($request_carnet, $arrayDatosPersona, 'prod', 'academico');
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->find(array('id'=>$request_llave));
                    $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find(array('id'=>$request_inconsistencia,'InstitucionEducativa'=>$request_ue));

                    if($observacion)
                    {
                        $datosPersona = array($request_carnet , $request_complemento , $request_fechaNacimiento);
                        $datosInconsistencia = array($request_llave , $request_inconsistencia , $request_ue);

                        /*
                        switch ($request_tipo)
                        {
                            case 60: //apoderados con segip inconsistente
                                list($data,$status,$msj) = $this->calidad_resolver_inconsistencias_apoderado($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia);
                            break;

                            case 61: //apoderados - administrativos con segip inconsistente
                                list($data,$status,$msj) = $this->calidad_resolver_inconsistencias_apoderadoAdministrativo($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia);
                            break;

                            case 62: //maestros - administrativos con segip inconsistente
                                list($data,$status,$msj) = $this->calidad_resolver_inconsistencias_maestroAdministrativo($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia);
                            break;
                        }
                        */
                        list($data,$status,$msj) = $this->calidad_resolver_inconsistencias_persona($persona, $personaValida,$arrayDatosPersona,$datosPersona,$datosInconsistencia,$request_tipo,$observacion);
                        if( $status == 200 )
                        {
                            //guardamos los datos
                            $observacion->setEsActivo('t');
                            $em->persist($observacion);
                            $em->flush();
                        }
                    }
                    else
                    {
                        $data   = NULL;
                        $status = 404;
                        $msj    = 'Acaba de ocurrir un error, no existe la inconsistencia.';
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
                $msj    = 'Por favor complete los campos requeridos.';
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
    
    /////////////////////////////////////////////////////////////////////////////////modifcar estado
        public function incosistenciaEstadoEstudianteSegipAction(Request $request) {

            $defaultController = new DefaultCont();
            $defaultController->setContainer($this->container);
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try{
                $gestion = $this->session->get('idGestionCalidad');
                $form = $request->get('formFer');


                // dump($form); exit();
                $idestado= $form['estado'];
                $id= $form['idDetalle'];
                $rude= $form['llave'];
                $idgestion= $form['idgestion'];


                $datos = $em->createQuery('SELECT ei
                FROM SieAppWebBundle:Estudiante AS e
                INNER JOIN SieAppWebBundle:EstudianteInscripcion AS ei WITH e.id = ei.estudiante
                INNER JOIN SieAppWebBundle:InstitucioneducativaCurso AS ic WITH ei.institucioneducativaCurso = ic.id
                WHERE e.codigoRude = :codigo_rude1
                AND ic.gestionTipo = :gestion_tipo_id1 ')
                ->setParameter('codigo_rude1', $rude)
                ->setParameter('gestion_tipo_id1', $idgestion);

                $dato = $datos->getResult();

                $valor=$dato['0'];
                // dump($valor); exit();

                $e= ($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($valor->getId()));
                // dump($e);
                $e->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($idestado));

                // dump($e); exit();

                /*$objEstInsCambioestado = new EstudianteInscripcionCambioestado();
                $objEstInsCambioestado->setEstadomatriculaTipo($form['idestado']);
                $objEstInsCambioestado->setUsuarioId($this->session->get('userId'));*/
                // $objEstInsCambioestado->setEstudianteInscripcion( $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dato[6]->getId()) );
                $em->flush();
                $em->persist($e);
                // dump($form['idDetalle']); exit();

                // retiramos la observacion regla 63 estado_matricula inconsistente
                $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave'=>$form['llave']));
                // dump($observacion); exit();
                if($observacion)
                {
                // dump($observacion); exit();
                  $observacion->setEsActivo('t');
                  $em->flush();
                  $em->persist($observacion);
                }
                $em->getConnection()->commit(); 

            }catch(Exception $ex){
                $em->getConnection()->rollback();
            }

        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $vregla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($vproceso->getValidacionReglaTipo());
        $vreglaentidad = $em->getRepository('SieAppWebBundle:ValidacionReglaEntidadTipo')->findOneById($vregla->getValidacionReglaEntidadTipo());
        return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $vreglaentidad->getId(), 'gestion' => $gestion)));

            // dump($this->redirect($this->generateUrl('ccalidad_list', array('id'=>5, 'gestion' => $gestion)))); exit();
        // return $this->redirect($this->generateUrl('ccalidad_list', array('id' => $e->getId(), 'gestion' => $gestion)));
        // return $this->redirect($this->generateUrl('ccalidad_list', array('id' => 5, 'gestion' => $gestion)));
        }
    /////////////////////////////////////////////////////////////////////////////////modifcar estado fin


}
