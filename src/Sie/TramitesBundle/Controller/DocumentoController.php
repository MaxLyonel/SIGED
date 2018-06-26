<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sie\AppWebBundle\Entity\Documento;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\TramiteDetalleController as tramiteProcesoController;

class DocumentoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un documento
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumento($id) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('d.id = :codDocumento')
                ->andWhere('dt.id in (1,3,4,5,6,7,8,9)')
                ->andWhere('de.id in (1)')
                ->setParameter('codDocumento', $id);
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento[0];
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un documento
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoLegalizado($id) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('d.id = :codDocumento')
                ->andWhere('dt.id in (2)')
                ->andWhere('de.id in (1)')
                ->setParameter('codDocumento', $id);
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento[0];
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un documento en función al número de serie
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoSerieActivo($serie) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('ds.id = :serie')
                ->andWhere('dt.id in (1,3,4,5,6,7,8,9)')
                ->andWhere('de.id in (1)')
                ->setParameter('serie', $serie);
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento[0];
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el detalle de documento generados dentro de un trámite
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoDetalle($tramite) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.id as documentoTipoId, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo, de.documentoEstado as documentoEstado, de.id as documentoEstadoId, d.fechaRegistro as fechaRegistro, d.obs as observacion")
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('t.id = :codTramite')
                ->setParameter('codTramite', $tramite)
                ->orderBy('d.fechaRegistro', 'DESC');
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que busca los tipos de serie de un o varios tipos de documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getSerieTipo($tipos) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          select distinct
          case
          when ds.documento_tipo_id in (1,2,3,4,5)  then (case ds.gestion_id when 2010 then right(ds.id,2) when 2013 then right(ds.id,2) else right(ds.id,1) end)
          when ds.documento_tipo_id in (6,7,8) then left(right(ds.id,4),3)
          when ds.documento_tipo_id in (9) then right(ds.id,2)
          else right(ds.id,1)
          end as serie
          from documento_serie as ds where ds.documento_tipo_id in (".$tipos.") order by serie desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que busca las gestiones de serie registrados de un o varios tipos de documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getGestionTipo($tipos) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct gestion_id as gestion from documento_serie as ds where ds.documento_tipo_id in (".$tipos.") order by gestion_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que busca un numero de serie en especifico
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getNumeroSerie($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */

        $em = $this->getDoctrine()->getManager();
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie));
        if(count($entityDocumentoSerie)>0){
            return "";
        } else {
            return "El número de serie ".$serie." no existe.";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el estado activo de un determinado numero de serie
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieActivo($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'esanulado' => 'f'));

        if(count($entityDocumentoSerie)>0){
            return "";
        } else {
            return "El número de serie ".$serie." se encuentra anulado.";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el estado activo de un determinado numero de serie
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieTipoDocumento($serie, $documentoTipoId) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'documentoTipo' => $documentoTipoId));
        $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneBy(array('id' => $documentoTipoId));

        if(count($entityDocumentoTipo)>0){
            $documentoTipo = $entityDocumentoTipo->getDocumentoTipo();
        } else {
            $documentoTipo = '';
        }

        if(count($entityDocumentoSerie)>0){
            return "";
        } else {
            return "El número de serie ".$serie." no esta asignado para ".$documentoTipo.".";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la no asignacion del numero de serie a un documento
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieAsignado($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();

        //$entidadTramite = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'documentoSerie' => $serie));
        $entidad = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entidad->createQueryBuilder('d')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->where('ds.id = :codSerie')
                ->setParameter('codSerie', $serie);
        $entidad = $query->getQuery()->getResult();
        if(count($entidad)>0){
            return "El cartón con número de serie ".$serie." ya fue impreso.";
        } else {
            return "";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la tuicion de un determinado numero de serie segun el lugar geográfico
    // PARAMETROS: serie, departamento
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieTuicion($serie, $departamento) {
        /*getDocumento
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entidad = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'departamentoTipo' => $departamento));
        if(count($entidad)>0){
            return "";
        } else {
            return "El número de serie ".$serie." no se encuentra en su tuición";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el codigo del lugar geografico a cargo segun el rol designado
    // PARAMETROS: usuarioId, rolId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCodigoLugarRol($usuarioId,$rolId) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityUsuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol');
        if($rolId == 15 or $rolId == 16 or $rolId == 17){
            $query = $entityUsuarioRol->createQueryBuilder('ur')
                    ->select('u.id as codusuario, rt.id as codrol, lt.lugar as lugar, lt.codigo as codlugar')
                    ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'u.id = ur.usuario')
                    ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'rt.id = ur.rolTipo')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = ur.lugarTipo')
                    ->where('u.id = :codUsuario')
                    ->andWhere('rt.id = :codRol')
                    ->setParameter('codUsuario', $usuarioId)
                    ->setParameter('codRol', $rolId)
                    ->orderBy('ur.id', 'DESC');
        } else {
            $query = $entityUsuarioRol->createQueryBuilder('ur')
                    ->select('u.id as codusuario, rt.id as codrol, lt.lugar as lugar, lt.id as codlugar')
                    ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'u.id = ur.usuario')
                    ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'rt.id = ur.rolTipo')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'lt.id = ur.lugarTipo')
                    ->where('u.id = :codUsuario')
                    ->andWhere('rt.id = :codRol')
                    ->setParameter('codUsuario', $usuarioId)
                    ->setParameter('codRol', $rolId);
        }
        $entityUsuarioRol = $query->getQuery()->getResult();
        if(count($entityUsuarioRol)>0){
            return $entityUsuarioRol[0]['codlugar'];
        } else {
            return 0;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que registra el documento
    // PARAMETROS: tramiteId, usuarioId, documentoTipo, numeroSerie, tipoSerie, fecha
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setDocumento($tramiteId, $usuarioId, $documentoTipo, $numeroSerie, $tipoSerie, $fecha) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$fecha = new \DateTime($fecha);
        $em = $this->getDoctrine()->getManager();

        /*
         * Define el conjunto de valores a ingresar - Documento
         */
        $serie = $numeroSerie.$tipoSerie;
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneBy(array('id' => $documentoTipo));
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $numeroSerie.$tipoSerie));
        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 1));
        $entityDocumento = new Documento();
        $entityDocumento->setDocumento('');
        $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
        $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
        $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
        $entityDocumento->setUsuarioId($usuarioId);
        $entityDocumento->setFechaImpresion($fecha);
        $entityDocumento->setFechaRegistro($fechaActual);
        $entityDocumento->setTramite($entityTramite);
        $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
        $em->persist($entityDocumento);
        $em->flush();
        return $entityDocumento->getId();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion cambia estado de un determinado documento
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setDocumentoEstado($documentoId, $estadoId) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$fechaManual = new \DateTime($fecha);
        $em = $this->getDoctrine()->getManager();

        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('id' => $documentoId));
        /*
         * Define el conjunto de valores a ingresar - Documento
         */
        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => $estadoId));
        $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
        $em->persist($entityDocumento);
        $em->flush();
        return $entityDocumento->getId();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la truision de un determinado numero de serie segun el lugar geográfico
    // PARAMETROS: serie, departamento
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoValidación($numeroSerie, $tipoSerie, $fecha, $usuarioId, $rolId, $documentoTipoId) {
        $msgContenido = "";
        $serie = $numeroSerie.$tipoSerie;

        // VALIDACION DE LA EXISTENCIA DEL CARTON
        $valSerie = $this->getNumeroSerie($serie);
        if($valSerie != ""){
            $msgContenido = ($msgContenido=="") ? $valSerie : $msgContenido.", ".$valSerie;
        }

        // VALIDACION DEl ESTADO ACTIVO DEL CARTON
        $valSerieActivo = $this->validaNumeroSerieActivo($serie);
        if($valSerieActivo != "" and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? $valSerieActivo : $msgContenido.", ".$valSerieActivo;
        }

        // VALIDACION DEl TIPO DE DOCUMENTO DEL CARTON
        $valSerieActivo = $this->validaNumeroSerieTipoDocumento($serie, $documentoTipoId);
        if($valSerieActivo != "" and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? $valSerieActivo : $msgContenido.", ".$valSerieActivo;
        }

        // VALIDACION DE LA ASIGNACION DE UN NUMERO DE SERIE A UN DOCUMENTO
        $valSerieAsigando = $this->validaNumeroSerieAsignado($serie);
        if($valSerieAsigando != "" and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? $valSerieAsigando : $msgContenido.", ".$valSerieAsigando;
        }

        $departamentoCodigo = $this->getCodigoLugarRol($usuarioId,$rolId);

        if ($departamentoCodigo == 0 and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para los documentos" : $msgContenido.", "."el usuario no cuenta con autorizacion para los el documentos ";
        } else {
            // VALIDACION DE TUICION DEL CARTON
            $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);
            if($valSerieTuicion != "" and $msgContenido == ""){
                $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
            }
        }

        return $msgContenido;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los documentos activos en función al trámite y tipo de documento
    // PARAMETROS: tramiteId, documentoTipoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoTramite($tramiteId, $documentoTipoId) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tramite' => $tramiteId, 'documentoTipo' => $documentoTipoId, 'documentoEstado' => 1));
        return $entityDocumento;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el documento de un numero de serie
    // PARAMETROS: serie, estado
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoSerieEstado($serie,$estado) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as documento, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, tt.tramiteTipo as tramiteTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad")
                ->innerJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('de.id = :codDocumentoEstado')
                ->andWhere('ds.id = :codDocumentoSerie')
                ->setParameter('codDocumentoEstado', $estado)
                ->setParameter('codDocumentoSerie', $serie);
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento[0];
        } else {
            return $entityDocumento;
        }

    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el documento de un numero de serie
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoSerie($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo, de.id as documentoEstadoId, de.documentoEstado as documentoEstado")
                ->innerJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('ds.id like :codSerie')
                ->andWhere('dt.id in (1,3,4,5,6,7,8,9)')
                ->setParameter('codSerie', '%'.$serie.'%');
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return $entityDocumento;
        }

    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de documentos por numero de serie que sera anulados al reactivar su trámite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormAnulaDocumentoSerie($routing, $serie, $obs) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('serie', 'text', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'class' => 'form-control', 'placeholder' => 'Número y Serie', 'pattern' => '^@?(\w){1,10}$', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('obs', 'textarea', array('label' => 'OBS.', 'attr' => array('value' => $obs, 'class' => 'form-control', 'placeholder' => 'Comentario', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario paraa la busqueda de documentos por numero de serie
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaDocumentoSerie($routing, $serie) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('serie', 'text', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'class' => 'form-control', 'placeholder' => 'Número y Serie', 'pattern' => '^@?(\w){1,10}$', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para la busqueda de documentos por numero de serie
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionNumeroSerieAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $rolPermitido = array(8,17);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Documento:legalizaIndex.html.twig', array(
            'form' => $this->creaFormBuscaDocumentoSerie('tramite_documento_legalizacion_numero_serie_detalle', '')->createView()
            , 'titulo' => 'Legalización'
            , 'subtitulo' => 'Documentos'
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de documentos por numero de serie
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function creaFormBuscaDocumentoSerieObs($routing, $value1, $value2, $boton) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('texto', 'text', array('label' => 'SERIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('obs', 'textarea', array('label' => 'OBS.', 'attr' => array('value' => $value2, 'class' => 'form-control', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => $boton, 'attr' => array('class' => 'btn btn-blue')))
            ->getForm();   
        return $form;
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el detalle del documento y sus legalizaciones segun el numero de serie enviado
    // PARAMETROS: numeroSerie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionNumeroSerieDetalleAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $usuarioId = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($usuarioId)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 17;

        $form = $request->get('form');

        if ($form) {
            $serie = $form['serie'];
            $msgContenido = "";
            // VALIDACION DE LA ASIGNACION DE UN NUMERO DE SERIE A UN DOCUMENTO
            $valSerieAsigando = $this->validaNumeroSerieAsignado($serie);
            if($valSerieAsigando == ""){
                $msgContenido = ($msgContenido=="") ? $valSerieAsigando : $msgContenido.", ".$valSerieAsigando;
            }

            $departamentoCodigo = $this->getCodigoLugarRol($usuarioId,$rolPermitido);

            if ($departamentoCodigo == 0){
                $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para los documentos" : $msgContenido.", "."el usuario no cuenta con autorizacion para los el documentos ";
            } else {
                // VALIDACION DE TUICION DEL CARTON
                $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);
                if($valSerieTuicion != ""){
                    $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
                }
            }
            // $msgContenido = "";
            if($msgContenido != ""){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
            }

            $entityDocumento = $this->getDocumentoSerieActivo($serie);

            $entityDocumentoDetalle = $this->getDocumentoDetalle($entityDocumento['tramite']);
            
            return $this->render($this->session->get('pathSystem') . ':Documento:legalizaDetalle.html.twig', array(
                'listaDocumento' => $entityDocumento,
                'listaDocumentoDetalle' => $entityDocumentoDetalle,
                'titulo' => 'Legalización',
                'subtitulo' => 'Documentos'
            ));
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la legalización de un documento segun el numero de serie enviado
    // PARAMETROS: documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionNumeroSerieGuardaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $usuarioId = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($usuarioId)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 17;

        if ($request->getMethod() == 'POST') {
            $documentoId = base64_decode($request->get('codigo'));     
            $em = $this->getDoctrine()->getManager();       
            $query = $em->getConnection()->prepare("
                select d.tramite_id as tramite_id, d.documento_serie_id as serie
                , pt.id as proceso_id, pt.proceso_tipo as proceso, mt.id as estadomatricula_id, mt.estadomatricula as estadomatricula
                , case mt.id when 5 then true when 26 then true when 37 then true when 55 then true when 58 then true else false end as promovido
                , case pt.id when 7 then true else false end as estadofintramite
                , case d.documento_estado_id when 1 then true else false end as estadodocumento
                , e.codigo_rude as rude, e.observacion
                from documento as d
                inner join documento_serie as ds on ds.id = d.documento_serie_id
                inner join tramite as t on t.id = d.tramite_id
                inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                left join (select * from tramite_detalle where id in (select max(id) from tramite_detalle where flujo_proceso_id in (select id from flujo_proceso where proceso_id = 7) group by tramite_id)) as td on td.tramite_id = t.id
                left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                left join proceso_tipo as pt on pt.id = fp.proceso_id
                where d.documento_tipo_id in (1,3,4,5,9) and d.id = ". $documentoId ."
            ");
            $query->execute();
            $entity = $query->fetchAll();
            if (count($entity) > 0){
                if (count($entity) < 2){
                    /*
                     * Extrae la observacion si el estudiante esta con procesos juridicos
                     */
                    if($entity[0]["observacion"] != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estudiante con código rude '.$entity[0]["rude"].' tiene la siguiente observación: '.$entity[0]["observacion"]));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    }

                    if (!$entity[0]["estadofintramite"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" y con número de serie "'.$entity[0]["serie"].'" no concluyo su trámite, conluya su tramite e intente nuevamente'));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    } elseif (!$entity[0]["estadodocumento"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con numero de serie "'.$entity[0]["serie"].'" se encuentra anulado, no es posible realizar su legalicación'));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    } else {
                        $em->getConnection()->beginTransaction();
                        try {
                            $idDocumento = $this->setDocumento($entity[0]["tramite_id"], $usuarioId, 2, $entity[0]["serie"], "", $fechaActual);
                            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El documento con numero de serie "'.$entity[0]["serie"].'" fue legalizado'));
                            $em->getConnection()->commit();        
                            $formBusqueda = array('serie'=>$entity[0]["serie"]);      
                            return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie_detalle', ['form' => $formBusqueda], 307);  
                        } catch (Exception $ex) {
                            $em->getConnection()->rollback();
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
                            return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
                        }
                    }
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, inconsistencia de datos en el documento, intente nuevamente'));
                    return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento no existe, intente nuevamente'));
                return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga la legalización de un documento segun el numero de serie enviado en formato pdf
    // PARAMETROS: documentoLegalizadoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionNumeroSeriePdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $usuarioId = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($usuarioId)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->getMethod() == 'POST') {
            $documentoId = base64_decode($request->get('codigo'));
            $entityDocumento = $this->getDocumentoLegalizado($documentoId);
            $arch = $entityDocumento['serie'].'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_Departamento_v3.rptdesign&id='.$documentoId.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response; 
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la operación, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
        }                       
    }
}
