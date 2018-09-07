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
use Sie\TramitesBundle\Controller\TramiteController as tramiteController;

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
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
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
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
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
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt1', 'WITH', 'gt1.id = iec.gestionTipo')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
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
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
                ->where('t.id = :codTramite')
                ->setParameter('codTramite', $tramite)
                ->orderBy('d.fechaImpresion', 'DESC');
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el detalle de documento supletorios generados dentro de un trámite
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoSupletorioDetalle($tramite) {
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
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
                ->where('t.id = :codTramite')
                ->andWhere('de.id = :codDocumentoEstado')
                ->andWhere('dt.id = :codDocumentoTipo')
                ->setParameter('codTramite', $tramite)
                ->setParameter('codDocumentoEstado', 1)
                ->setParameter('codDocumentoTipo', 9)
                ->orderBy('d.fechaImpresion', 'DESC');
        $entityDocumento = $query->getQuery()->getResult();
        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los documentos generados segun la institucion educativa y gestion de la serie
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoInstitucionEducativaGestion($institucionEducativaId, $gestionId, $documentoTipoIds) {     
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select
            ie.id as institucioneducativa_id, ie.institucioneducativa, iec.gestion_tipo_id,
            nt.id as nivel_tipo_id, nt.nivel,
            pt.id as paralelo_tipo_id, pt.paralelo,
            tt.id as turno_tipo_id, tt.turno,
            e.id as estudiante_id, e.codigo_rude, e.pasaporte,
            e.carnet_identidad as carnet,  e.complemento,
            cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad,
            e.paterno, e.materno, e.nombre, e.segip_id,
            to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento,
            emt.id as estadomatricula_tipo_id, emt.estadomatricula,
            ei.id as estudiante_inscripcion_id,
            e.localidad_nac, case pat.id when 1 then lt2.lugar when 0 then '' else pat.pais end as lugar_nacimiento,
            e.lugar_prov_nac_tipo_id as lugar_nacimiento_id, lt2.codigo as depto_nacimiento_id, lt2.lugar as depto_nacimiento,
            CASE
            	WHEN iec.nivel_tipo_id = 13 THEN
            	'Regular Humanística'
            	WHEN iec.nivel_tipo_id = 15 THEN
            	'Alternativa Humanística'
            	WHEN iec.nivel_tipo_id > 17 THEN
            	'Alternativa Técnica'
            END AS subsistema,
            t.id as tramite_id, d.id as documento_id, d.documento_serie_id as documento_serie_id
            from tramite as t
            inner join tramite_detalle as td on td.tramite_id = t.id
            inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join nivel_tipo as nt on nt.id = iec.nivel_tipo_id
            inner join paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
            inner join turno_tipo as tt on tt.id = iec.turno_tipo_id
            inner join estadomatricula_tipo as emt on emt.id = ei.estadomatricula_tipo_id
            inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (".$documentoTipoIds.") and d.documento_estado_id = 1
            inner join documento_serie as ds on ds.id = d.documento_serie_id
            left join lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo as pat on pat.id = e.pais_tipo_id
            where iec.gestion_tipo_id = ".$gestionId."::double precision and iec.institucioneducativa_id = ".$institucionEducativaId."::INT
            and td.tramite_estado_id <> 4 and td.flujo_proceso_id = 5
            order by e.paterno, e.materno, e.nombre
        ");
        $queryEntidad->execute();
        $entityDocumento = $queryEntidad->fetchAll();

        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return array();
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
          end as serie, gestion_id
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
    // Funcion que valida el estado activo y el tipo de documento de un determinado numero de serie para generar el certificado supletorio
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieParaSupletorio($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();

        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt1', 'WITH', 'gt1.id = iec.gestionTipo')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('ds.id = :serie')
                ->andWhere('dt.id in (1,3,4,5)')
                ->andWhere('de.id in (1)')
                ->setParameter('serie', $serie);
        $entityDocumento = $query->getQuery()->getResult();
        
        if(count($entityDocumento)>0){
            return "";
        } else {
            return "El documento con número de serie ".$serie." no es válido para generar un certificado supletorio.";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el estado activo y el tipo de documento de un determinado numero de serie para su legalizacion
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieParaLegalizar($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();

        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt1', 'WITH', 'gt1.id = iec.gestionTipo')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('ds.id = :serie')
                ->andWhere('dt.id in (1,3,4,5,6,7,8)')
                ->andWhere('de.id in (1)')
                ->setParameter('serie', $serie);
        $entityDocumento = $query->getQuery()->getResult();
        
        if(count($entityDocumento)>0){
            return "";
        } else {
            return "El documento con número de serie ".$serie." no es válido para generar un certificado supletorio.";
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
                    ->select('u.id as codusuario, rt.id as codrol, lt.lugar as lugar, lt.codigo as codlugar, lt.codigo as codigoLugar')
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
                    ->select('u.id as codusuario, rt.id as codrol, lt.lugar as lugar, lt.id as codlugar, lt.codigo as codigoLugar')
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
    // Funcion cambia estado de los documentos de un trámite
    // PARAMETROS: serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setTramiteDocumentoEstado($tramiteId, $estadoId) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$fechaManual = new \DateTime($fecha);
        $em = $this->getDoctrine()->getManager();

        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findBy(array('tramite' => $tramiteId));
        /*
         * Define el conjunto de valores a ingresar - Documento
         */

        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => $estadoId));
        
        $msg = "";
        $em->getConnection()->beginTransaction();
        try {
            foreach ($entityDocumento as $registro)
            {
                $id = $registro->getId();
                $entity = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('id' => $id));
                $entity->setDocumentoEstado($entityDocumentoEstado);
                $em->persist($entity);
                $em->flush();
            }
            $em->getConnection()->commit();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $msg = "Error al anular los documentos, intente nuevamente por favor";
            $em->getConnection()->rollback();
        }

        return $msg;
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
                ->setParameter('codSerie', $serie);
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
    // Funcion que genera un formulario paraa la busqueda de documentos por numero de serie
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaInstitucionEducativaSerie($routing, $sie, $serie, $documentoTipoArrayId) {
        $entityDocumentoSerie = $this->getSerieTipo($documentoTipoArrayId);

        $serieEntity = array();
        foreach ($entityDocumentoSerie as $key => $dato) {
            $serieEntity[$dato['gestion_id']] = $dato['serie'];
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('sie', 'text', array('label' => 'Código S.I.E.', 'attr' => array('value' => $sie, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'placeholder' => 'Código de institución educativa', 'style' => 'text-transform:uppercase')))
            ->add('gestion',
                      'choice',  
                      array('label' => 'Serie',
                            'choices' => $serieEntity,
                            'data' => $serie,
                            'attr' => array('class' => 'form-control'),
                            )
                )
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
        $route = $request->get('_route');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu($route);

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
                $msgContenido = ($msgContenido=="") ? "No existe el documento con número de serie ".$serie : "No existe el documento con número de serie ".$serie.", ".$valSerieAsigando;
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

            $msgvalidaNumeroSerieTipoDocumento = $this->validaNumeroSerieParaLegalizar($serie);

            if ($msgvalidaNumeroSerieTipoDocumento != ""){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgvalidaNumeroSerieTipoDocumento));
                return $this->redirectToRoute('tramite_documento_supletorio');
            }  

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

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para la busqueda de documentos por numero de serie
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionInstitucionEducativaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        $route = $request->get('_route');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $rolPermitido = array(8,17);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Documento:legalizaInstitucionEducativaIndex.html.twig', array(
            'form' => $this->creaFormBuscaInstitucionEducativa('tramite_documento_legalizacion_institucion_educativa_pdf', '',$gestionActual)->createView()
            , 'titulo' => 'Legalización'
            , 'subtitulo' => 'Documentos'
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario par ala busqueda de instituciones educativas por gestion
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaInstitucionEducativa($routing, $institucionEducativaId, $gestionId) {
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        if($institucionEducativaId==0){
            $institucionEducativaId = "";
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control', 'placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga la legalización de un documento segun el numero de serie enviado en formato pdf
    // PARAMETROS: documentoLegalizadoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function legalizacionInstitucionEducativaPdfAction(Request $request) {
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
            $form = $request->get('form');
            if ($form){  
                $sie = $form['sie'];  
                $ges = $form['gestion']; 

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);
                $rolPermitido = array(8,17);
                $usuarioRol = $this->session->get('roluser');
                $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($usuarioId, $sie, $usuarioRol, 'DIPLOMAS');

                if ($verTuicionUnidadEducativa != ''){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Su usuario no cuenta con la respectiva tuición de la institución educativa'.$sie));
                    return $this->redirectToRoute('tramite_documento_legalizacion_institucion_educativa');
                }

                $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
                $response = new Response();
                $response->headers->set('Content-type', 'application/pdf');
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_ue_v1.rptdesign&sie='.$sie.'&gestion='.$ges.'&&__format=pdf&'));
                $response->setStatusCode(200);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
                return $response;
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar el formulario, intente nuevamente'));
                return $this->redirectToRoute('tramite_documento_legalizacion_institucion_educativa');
            } 
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la operación, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_legalizacion_institucion_educativa');
        }                       
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para la busqueda de documentos por numero de serie
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function supletorioAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $route = $request->get('_route');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $rolPermitido = array(8,17);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Documento:supletorioIndex.html.twig', array(
            'form' => $this->creaFormBuscaDocumentoSerie('tramite_documento_supletorio_detalle', '')->createView()
            , 'titulo' => 'Certificado Supletorio'
            , 'subtitulo' => 'Busca'
        ));
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el detalle del documento y sus certificados supletorio segun el numero de serie enviado
    // PARAMETROS: numeroSerie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function supletorioDetalleAction(Request $request) {
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

        $rolPermitido = 16;

        $form = $request->get('form');

        if ($form) {
            $serie = $form['serie'];
            $msgContenido = "";
            // VALIDACION DE LA ASIGNACION DE UN NUMERO DE SERIE A UN DOCUMENTO
            $valSerieAsigando = $this->validaNumeroSerieAsignado($serie);

            if($valSerieAsigando == ""){
                $msgContenido = ($msgContenido=="") ? "No existe el documento con número de serie ".$serie : "No existe el documento con número de serie ".$serie.", ".$valSerieAsigando;
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
                return $this->redirectToRoute('tramite_documento_supletorio');
            }

            $entityDocumento = $this->getDocumentoSerieActivo($serie);

            $msgvalidaNumeroSerieTipoDocumento = $this->validaNumeroSerieParaSupletorio($serie);

            if ($msgvalidaNumeroSerieTipoDocumento != ""){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgvalidaNumeroSerieTipoDocumento));
                return $this->redirectToRoute('tramite_documento_supletorio');
            } 

            $entityDocumentoDetalle = $this->getDocumentoSupletorioDetalle($entityDocumento['tramite']);
            
            return $this->render($this->session->get('pathSystem') . ':Documento:supletorioDetalle.html.twig', array(
                'form' => $this->creaFormularioSupletorio('tramite_documento_supletorio_guarda', '', $entityDocumento['id'])->createView(),
                'listaDocumento' => $entityDocumento,
                'listaDocumentoDetalle' => $entityDocumentoDetalle,
                'titulo' => 'Certificado Supletorio',
                'subtitulo' => 'Registra'
            ));
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_supletorio');
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la generación de certificados supletorio
    // PARAMETROS: numeroSerie, documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function creaFormularioSupletorio($routing, $serie, $documentoId) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('serie', 'text', array('label' => 'Serie - Supletorio', 'attr' => array('value' => '', 'placeholder' => 'Número y Serie del cartón', 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))                    
                ->add('codigo', 'hidden', array('attr' => array('value' => base64_encode($documentoId))))
                ->add('search', 'submit', array('label' => 'Generar Certificado Supletorio', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra el certificado supletorio de un documento segun el numero de serie enviado
    // PARAMETROS: documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function supletorioGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $mensaje = "";
        $rolPermitido = 16;
        //validation if the user is logged        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }   
        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');
            //get the values throght the infoUe
            if ($form) {
                $numeroSerieSupletorio = $form['serie'];
                $documentoId = base64_decode($form['codigo']); 

                $entityDocumento = $this->getDocumento($documentoId);                
                if (count($entityDocumento)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Diploma de bachiller no encontrado, intente nuevamente'));
                    return $this->redirectToRoute('tramite_documento_supletorio');
                } 

                $formBusqueda = array('serie'=>$entityDocumento['serie']);

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);

                $usuarioLugarId = $this->getCodigoLugarRol($id_usuario,$rolPermitido);
                if (count($usuarioLugarId)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Lugar de procedencia del usuario inexistente, intente nuevamente'));
                    return $this->redirectToRoute('tramite_documento_supletorio_detalle', ['form'=>$formBusqueda], 307);
                } 
                
                $codTramite = $entityDocumento["tramite"];

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $msgContenidoDocumento = $this->getDocumentoValidación($numeroSerieSupletorio, '', $fechaActual, $id_usuario, $rolPermitido, 9);
                    
                    if($msgContenidoDocumento == ""){
                        $idDocumento = $this->setDocumento($codTramite, $id_usuario, 9, $numeroSerieSupletorio, '', $fechaActual); 
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El certificado supletorio con numero de serie "'.$numeroSerieSupletorio.'" fue generado'));
                        $em->getConnection()->commit();
                    } else {
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenidoDocumento));
                        $em->getConnection()->rollback();
                    }             
                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $ex->getMessage()));                    
                }  
                return $this->redirectToRoute('tramite_documento_supletorio_detalle', ['form' => $formBusqueda], 307);
            }  else {                
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
                return $this->redirectToRoute('tramite_documento_supletorio');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_supletorio');
        }   
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga el certificado supletorio de un documento  en formato pdf segun el numero de serie enviado
    // PARAMETROS: documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function supletorioPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //$cod_url = urlencode($cadena); 
        //$deco_url = urldecode($cod_url); 

        //$codifica1 = base64_encode($cadena); 
        //$decodifica2 = base64_decode($codifica1); 

        //$codifica3 = convert_uuencode($cadena); 
        //$decodifica4 = convert_uudecode($codifica3);
        

        $codigoDocumento = base64_decode($request->get('supletorio'));
        $token = $request->get('_token');

        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('id' => $codigoDocumento));

        if (!$this->isCsrfTokenValid($codigoDocumento, $token)) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('tramite_documento_supletorio');
        } 

        $lk = "http://diplomas.sie.gob.bo/diplomas/documento/certificado/supletorio/qr/".md5($codigoDocumento);
      
        ///////return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
        $arch = 'CertificadoSupletorio_'.$entityDocumento->getDocumentoSerie()->getId().'_'.date('YmdHis').'.pdf';

        $departamentoIdDocumentoSerie = $entityDocumento->getDocumentoSerie()->getDepartamentoTipo()->getId();
        $gestionIdDocumentoSerie = $entityDocumento->getDocumentoSerie()->getGestion()->getId();
        $fileContent = "";      

        switch ($gestionIdDocumentoSerie) {
            case 2017:
                if ($departamentoIdDocumentoSerie == 1) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_ch.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 2) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_lp.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 3) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_cba.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 4) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_or.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 5) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_pt.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 6) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_tj.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 7) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_scz.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 8) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_bn.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 9) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_pn.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                }else {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_ch.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                }
                break;                
            default :
                if ($departamentoIdDocumentoSerie == 1) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_ch.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 2) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_lp.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 3) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_cba.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 4) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_or.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 5) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_pt.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 6) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_tj.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 7) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_scz.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 8) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_bn.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                } elseif ($departamentoIdDocumentoSerie == 9) {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_pn.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                }else {
                    $fileContent = $this->container->getParameter('urlreportweb') . 'gen_dpl_certificadoSupletorio_estudiante_2017_v1_ch.rptdesign&supletorio='.md5($codigoDocumento).'&lk='.$lk.'&&__format=pdf&';
                }
                break; 
        }
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($fileContent));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }   



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario par ala busqueda de instituciones educativas por gestion
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaInstitucionEducativaHumanisticaSerie($routing, $institucionEducativaId, $serieId) {
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        if($institucionEducativaId==0){
            $institucionEducativaId = "";
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control', 'placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }
}
