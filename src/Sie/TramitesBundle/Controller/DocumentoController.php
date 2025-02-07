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

use phpseclib\Crypt\RSA;

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
                ->select("d.id as id, t.id as tramite, ei.id as estudianteInscripcionId, ds.id as serie, d.fechaImpresion as fechaemision, dept.id as departamentoemisionid, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
    // Funcion que lista un documento mediante un id md5
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoMd5($id) {
        $em = $this->getDoctrine()->getManager();
 
        $queryEntidad = $em->getConnection()->prepare("
            select
            d.id as id, t.id as tramite, ei.id as estudianteInscripcionId, ds.id as serie, case d.documento_tipo_id when 2 then to_char(d1.fecha_impresion, 'dd/mm/YYYY') else to_char(d.fecha_impresion, 'dd/mm/YYYY')  end as fechaemision
            , dept.id as departamentoemisionid, dept.departamento as departamentoemision, e.codigo_rude as rude, e.paterno as paterno, e.materno as materno
            , e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, to_char(e.fecha_nacimiento, 'dd/mm/YYYY') as fechanacimiento
            , (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documento_tipo as documentoTipo
            , (case e.complemento when '' then e.carnet_identidad when 'null' then e.carnet_identidad else CONCAT(CONCAT(e.carnet_identidad,'-'),e.complemento) end) as carnetIdentidad
            , tt.tramite_tipo as tramiteTipo, d.documento_firma_id as documentoFirmaId, d.token_privado as keyprivado, d.token_impreso, dt.id as documentoTipoId, de.id as documentoEstadoId
            , p.nombre || ' ' || p.paterno || ' ' || p.materno as personafirma
            from documento as d
            inner join documento_estado as de on de.id = d.documento_estado_id
            inner join documento_tipo as dt on dt.id = d.documento_estado_id
            inner join documento_serie as ds on ds.id = d.documento_serie_id
            inner join tramite as t on t.id = d.tramite_id
            inner join tramite_tipo as tt on tt.id = t.tramite_tipo
            inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join gestion_tipo as gt on gt.id = ds.gestion_id
            inner join pais_tipo as pt on pt.id = e.pais_tipo_id
            inner join departamento_tipo as dept on dept.id = ds.departamento_tipo_id
            left join documento_firma as df on df.id = d.documento_firma_id
            left join persona as p on p.id = df.persona_id
            left join lugar_tipo as ltp on ltp.id = e.lugar_prov_nac_tipo_id
            left join lugar_tipo as ltd on ltd.id = ltp.lugar_tipo_id
            left join documento as d1 on d1.tramite_id = d.tramite_id and d1.documento_tipo_id in (1,9) and d1.documento_estado_id = 1
            where md5(cast(d.id as varchar)) = :id and dt.id in (1,2,3,4,5,6,7,8,9) and de.id in (1)
            order by e.paterno, e.materno, e.nombre
        ");
        $queryEntidad->bindValue(':id', $id);
        $queryEntidad->execute();
        $entityDocumento = $queryEntidad->fetchAll();

        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            return $entityDocumento;
        }
    }

    //****************************************************************************************************
// DESCRIPCION DEL METODO:
// Funcion que lista un documento mediante un id md5
// PARAMETROS: id
// AUTOR: RCANAVIRI
//****************************************************************************************************
public function getDocumentoTokenImpreso($token) {
    $em = $this->getDoctrine()->getManager();

    $queryEntidad = $em->getConnection()->prepare("
        select
        d.id as id, t.id as tramite, ei.id as estudianteInscripcionId, ds.id as serie, to_char(d.fecha_impresion, 'dd/mm/YYYY') as fechaemision
        , dept.id as departamentoemisionid, dept.departamento as departamentoemision, e.codigo_rude as rude, e.paterno as paterno, e.materno as materno
        , e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, to_char(e.fecha_nacimiento, 'dd/mm/YYYY') as fechanacimiento
        , (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documento_tipo as documentoTipo
        , dt.id as documentotipoid, ds.formacion_educacion_tipo_id as educaciontipoid
        , (case e.complemento when '' then e.carnet_identidad when 'null' then e.carnet_identidad else CONCAT(CONCAT(e.carnet_identidad,'-'),e.complemento) end) as carnetIdentidad
        , tt.tramite_tipo as tramiteTipo, d.documento_firma_id as documentoFirmaId, d.token_privado as keyprivado, d.token_impreso, de.id as documentoEstadoId
        , p.nombre || ' ' || p.paterno || ' ' || p.materno as personafirma
        from documento as d
        inner join documento_estado as de on de.id = d.documento_estado_id
        inner join documento_tipo as dt on dt.id = d.documento_tipo_id
        inner join documento_serie as ds on ds.id = d.documento_serie_id
        inner join tramite as t on t.id = d.tramite_id
        inner join tramite_tipo as tt on tt.id = t.tramite_tipo
        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
        inner join estudiante as e on e.id = ei.estudiante_id
        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
        inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
        inner join gestion_tipo as gt on gt.id = ds.gestion_id
        inner join pais_tipo as pt on pt.id = e.pais_tipo_id
        inner join departamento_tipo as dept on dept.id = ds.departamento_tipo_id
        left join documento_firma as df on df.id = d.documento_firma_id
        left join persona as p on p.id = df.persona_id
        left join lugar_tipo as ltp on ltp.id = e.lugar_prov_nac_tipo_id
        left join lugar_tipo as ltd on ltd.id = ltp.lugar_tipo_id
        where d.token_impreso = :token and dt.id in (1,2,3,4,5,6,7,8,9)
        order by e.paterno, e.materno, e.nombre
    ");
    $queryEntidad->bindValue(':token', $token);
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
    // Funcion que lista un documento
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoLegalizado($id) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ei.id as estudianteInscripcionId, ds.id as serie, d.fechaImpresion as fechaemision, dept.id as departamentoemisionid, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
    // Funcion que lista los documentos supletorios de un diploma
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoSupletorio($tramite) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, t.id as tramite, ei.id as estudianteInscripcionId, ds.id as serie, d.fechaImpresion as fechaemision, dept.id as departamentoemisionid, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.id as documentoTipoId, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt1', 'WITH', 'gt1.id = iec.gestionTipo')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
                ->where('t.id = :codTramite')
                ->andWhere('dt.id in (9)')
                ->andWhere('de.id in (1)')
                ->setParameter('codTramite', $tramite);
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
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.id as documentoTipoId, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
                ->orderBy('d.fechaImpresion', 'DESC', 'd.id', 'DESC');
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
                ->andWhere('dt.id = :codDocumentoTipo')
                ->setParameter('codTramite', $tramite)
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
            and td.tramite_estado_id <> 4 and (td.flujo_proceso_id = 5 or td.flujo_proceso_id = 9)
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
          when ds.documento_tipo_id in (6,7,8) then (case when ds.gestion_id >= 2018 then left(right(ds.id,1),1) else left(right(ds.id,4),3) end)  
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
    // Funcion que busca los tipos de serie de un o varios tipos de documento por tipo de educacion
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getSerieEducacionTipo($tipos,$educacion) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          select distinct
          case
          when ds.documento_tipo_id in (1,2,3,4,5)  then (case ds.gestion_id when 2010 then right(ds.id,2) when 2013 then right(ds.id,2) else right(ds.id,1) end)
          when ds.documento_tipo_id in (6,7,8) then (case when ds.gestion_id >= 2018 then left(right(ds.id,1),1) else left(right(ds.id,4),3) end)  
          when ds.documento_tipo_id in (9) then right(ds.id,2)
          else right(ds.id,1)
          end as serie, gestion_id
          from documento_serie as ds where ds.documento_tipo_id in (".$tipos.") and formacion_educacion_tipo_id = ".$educacion." order by serie desc
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
    // Funcion que busca las gestiones de serie registrados de un o varios tipos de documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getGestionEducacionTipo($tipos,$educacion) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct gestion_id as gestion from documento_serie as ds where ds.documento_tipo_id in (".$tipos.") and formacion_educacion_tipo_id = ".$educacion." order by gestion_id desc
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

    //************************gestiones****************************************************************************
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
                ->select("d.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, dept.id as departamentoemision_codigo, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, gt1.id as gestionMatricula, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.id as documentoTipoId, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
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
            return "";
        } else {
            return "El documento con número de serie ".$serie." no puede legalizarse.";
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
    // Funcion que valida la tuicion de un determinado numero de serie segun el lugar geográfico
    // PARAMETROS: serie, departamento
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validaNumeroSerieFormacionTuicion($serie, $departamento, $formacion) {
        /*getDocumento
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entidad = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'departamentoTipo' => $departamento, 'formacionEducacionTipo' => $formacion));
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
    public function setDocumento($tramiteId, $usuarioId, $documentoTipo, $numeroSerie, $tipoSerie, $fecha, $documentoFirmaId) {
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
        //inactivado cuando piden fecha exacta 
       // $ges = $entityDocumentoSerie->getGestion()->getId();

       // if($ges == 2024 and $documentoTipo == 1){
          //  $fecha = new \DateTime('2024-12-09');
       // }
        //if ($ges == 2024 and $documentoTipo == 8 and $tipoSerie=='TTMI') {
        //    $fecha = new \DateTime('2024-12-09');
       // }
        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 1));
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityDocumento = new Documento();
        $entityDocumento->setDocumento('');
        $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
        $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
        $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
        $entityDocumento->setUsuario($entityUsuario);
        $entityDocumento->setFechaImpresion($fecha);
        $entityDocumento->setFechaRegistro($fechaActual);
        $entityDocumento->setTramite($entityTramite);
        $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
        $em->persist($entityDocumento);
        $em->flush();

        $documentoId = $entityDocumento->getId();
        if($documentoTipo == 1 or $documentoTipo == 2 or $documentoTipo == 6 or $documentoTipo == 7){
            if ($documentoFirmaId != 0 and $documentoFirmaId != ""){
                if($documentoTipo == 2){
                    $entityDocumentoGenerado = $this->getDocumentoLegalizado($documentoId);
                } else {
                    $entityDocumentoGenerado = $this->getDocumento($documentoId);
                }

                $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));

                $personaTipoId = 0;
                switch ($documentoTipo) {
                    case 1:
                        $personaTipoId = 1;
                        break;
                    case 2:
                        $personaTipoId = 2;
                        break;
                    case 6:
                        $personaTipoId = 1;
                        break;
                    case 7:
                        $personaTipoId = 1;
                        break;
                    default:
                        $personaTipoId = 0;
                }

                $getDocumentoFirma = $this->getDocumentoFirmaLugar($entityDocumentoFirma->getPersona()->getId(), $entityDocumentoGenerado['departamentoemisionid'], $personaTipoId);
               
                // dump($getDocumentoFirma);die;
                $lugarNacimiento = "";
                if($entityDocumentoGenerado['departamentonacimiento'] == ""){
                    $lugarNacimiento = $entityDocumentoGenerado['paisnacimiento'];
                } else {
                    $lugarNacimiento = $entityDocumentoGenerado['departamentonacimiento']." - ".$entityDocumentoGenerado['paisnacimiento'];
                }    
                
                // $dateNacimiento = date_create($entityDocumentoGenerado['fechanacimiento']);
                // $dateEmision = date_create($entityDocumentoGenerado['fechaemision']);
                $dateNacimiento = ($entityDocumentoGenerado['fechanacimiento']);
                $dateEmision = ($entityDocumentoGenerado['fechaemision']);
                // dump($dateNacimiento);dump($dateEmision);dump($dateEmision->format('d/m/Y'));die;
                            
                // $datos = array(
                //     'inscripcion'=>$entityDocumentoGenerado['estudianteInscripcionId'],
                //     'tramite'=>$entityDocumentoGenerado['tramite'],
                //     'serie'=>$entityDocumentoGenerado['serie'],
                //     'codigorude'=>$entityDocumentoGenerado['rude'],
                //     'sie'=>$entityDocumentoGenerado['sie'],
                //     'gestionegreso'=>$entityDocumentoGenerado['gestion'],
                //     'nombre'=>$entityDocumentoGenerado['nombre'],
                //     'paterno'=>$entityDocumentoGenerado['paterno'],
                //     'materno'=>$entityDocumentoGenerado['materno'],
                //     'nacimientolugar'=>$lugarNacimiento,
                //     'nacimientofecha'=>date_format($dateNacimiento, 'd/m/Y'),
                //     'cedulaidentidad'=>$entityDocumentoGenerado['carnetIdentidad'],
                //     'emisiondepartamento'=>$entityDocumentoGenerado['departamentoemision'],
                //     'emisionfecha'=>date_format($dateEmision, 'd/m/Y'),
                //     'tokenfirma'=>base64_encode($getDocumentoFirma['id'])
                // );
                $datos = array(
                    'inscripcion'=>$entityDocumentoGenerado['estudianteInscripcionId'],
                    'tramite'=>$entityDocumentoGenerado['tramite'],
                    'serie'=>$entityDocumentoGenerado['serie'],
                    'codigorude'=>$entityDocumentoGenerado['rude'],
                    'sie'=>$entityDocumentoGenerado['sie'],
                    'gestionegreso'=>$entityDocumentoGenerado['gestion'],
                    'nombre'=>$entityDocumentoGenerado['nombre'],
                    'paterno'=>$entityDocumentoGenerado['paterno'],
                    'materno'=>$entityDocumentoGenerado['materno'],
                    'nacimientolugar'=>$lugarNacimiento,
                    'nacimientofecha'=>$dateNacimiento->format('d/m/Y'),
                    'cedulaidentidad'=>$entityDocumentoGenerado['carnetIdentidad'],
                    'emisiondepartamento'=>$entityDocumentoGenerado['departamentoemision'],
                    'emisionfecha'=>$dateEmision->format('d/m/Y'),
                    'tokenfirma'=>base64_encode($getDocumentoFirma['id'])
                );
                $keys = $this->getEncodeRSA($datos);

                // registro de la firma en el documento generado
                $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $getDocumentoFirma['id']));
                $entityDocumento->setDocumentoFirma($entityDocumentoFirma);
                $entityDocumento->setTokenPublico($keys['keyPublica']);
                $entityDocumento->setTokenPrivado($keys['keyPrivada']);
                $entityDocumento->setTokenImpreso($keys['token']);
                $em->persist($entityDocumento);
                // $em->flush();

                // dump($entityDocumento);dump($entityDocumentoFirma);dump($datos);die;    
                
                // incremento de la firma usada en la tabla de parametros documentoFirmaAutorizada
                $documentoFirmaAutorizadaId = $this->getDocumentoFirmaAutorizadaIncrementar($entityDocumentoFirma->getPersona()->getId(), $documentoTipo);
                $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id' => $documentoFirmaAutorizadaId));
                $cantidadIncremento = ($entityDocumentoFirmaAutorizada->getUsado()) + 1;
                $entityDocumentoFirmaAutorizada->setUsado($cantidadIncremento);
                $em->persist($entityDocumentoFirmaAutorizada);
                // $em->flush();

                $em->flush();
            } 

            // $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id' => $documentoFirmaAutorizadaId['id']));
        }
        return $documentoId;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que registra el documento
    // PARAMETROS: tramiteId, usuarioId, documentoTipo, numeroSerie, tipoSerie, fecha
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setDocumentoFirmas($tramiteId, $usuarioId, $documentoTipo, $numeroSerie, $tipoSerie, $fecha, $documentoFirmas) {
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
        //inactivado cuando piden fecha exacta 
        //$ges = $entityDocumentoSerie->getGestion()->getId();
      //  if($ges == 2024 and $documentoTipo == 1){
        //    $fecha = new \DateTime('2024-12-09');
       // }
       // if ($ges == 2024 and $documentoTipo == 8 and $tipoSerie=='TTMI') {
       //     $fecha = new \DateTime('2024-12-09');
       // }
        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 1));
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityDocumento = new Documento();
        $entityDocumento->setDocumento('');
        $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
        $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
        $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
        $entityDocumento->setUsuario($entityUsuario);
        $entityDocumento->setFechaImpresion($fecha);
        $entityDocumento->setFechaRegistro($fechaActual);
        $entityDocumento->setTramite($entityTramite);
        $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
        $em->persist($entityDocumento);
        $em->flush();

        $documentoId = $entityDocumento->getId();
        if($documentoTipo == 1 or $documentoTipo == 2 or $documentoTipo == 6 or $documentoTipo == 7 or $documentoTipo == 8){
            if (count($documentoFirmas)>0){
                if($documentoTipo == 2){
                    $entityDocumentoGenerado = $this->getDocumentoLegalizado($documentoId);
                } else {
                    $entityDocumentoGenerado = $this->getDocumento($documentoId);
                }

                $departamentoEmisionId = $entityDocumentoGenerado['departamentoemisionid'];
               
                $arrayFirmas = array();
                $getDocumentoFirmaId = 0;
                foreach ($documentoFirmas as $firma) {
                    $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $firma['documentoFirmaId']));

                    $personaTipoId = 0;
                    switch ($documentoTipo) {
                        case 1:
                            $personaTipoId = 1;
                            break;
                        case 2:
                            $personaTipoId = 2;
                            break;
                        case 6:
                            $personaTipoId = 1;
                            break;
                        case 7:
                            $personaTipoId = 1;
                            break;
                        case 8:
                            $departamentoEmisionId = 0;
                            $personaTipoId = $firma['personaTipoId'];
                            break;
                        default:
                            $personaTipoId = 0;
                    }
                    
                    $getDocumentoFirma = $this->getDocumentoFirmaLugar($entityDocumentoFirma->getPersona()->getId(), $departamentoEmisionId, $personaTipoId);
                    
                    if($getDocumentoFirmaId == 0){
                        $getDocumentoFirmaId = $getDocumentoFirma['id'];
                    }
                    array_push($arrayFirmas, $getDocumentoFirma['id']);
                }

                // dump($getDocumentoFirma);die;
                $lugarNacimiento = "";
                if($entityDocumentoGenerado['departamentonacimiento'] == ""){
                    $lugarNacimiento = $entityDocumentoGenerado['paisnacimiento'];
                } else {
                    $lugarNacimiento = $entityDocumentoGenerado['departamentonacimiento']." - ".$entityDocumentoGenerado['paisnacimiento'];
                }    
                
                // $dateNacimiento = date_create($entityDocumentoGenerado['fechanacimiento']);
                // $dateEmision = date_create($entityDocumentoGenerado['fechaemision']);
                $dateNacimiento = ($entityDocumentoGenerado['fechanacimiento']);
                $dateEmision = ($entityDocumentoGenerado['fechaemision']);
                // dump($dateNacimiento);dump($dateEmision);dump($dateEmision->format('d/m/Y'));die;
                            
                // $datos = array(
                //     'inscripcion'=>$entityDocumentoGenerado['estudianteInscripcionId'],
                //     'tramite'=>$entityDocumentoGenerado['tramite'],
                //     'serie'=>$entityDocumentoGenerado['serie'],
                //     'codigorude'=>$entityDocumentoGenerado['rude'],
                //     'sie'=>$entityDocumentoGenerado['sie'],
                //     'gestionegreso'=>$entityDocumentoGenerado['gestion'],
                //     'nombre'=>$entityDocumentoGenerado['nombre'],
                //     'paterno'=>$entityDocumentoGenerado['paterno'],
                //     'materno'=>$entityDocumentoGenerado['materno'],
                //     'nacimientolugar'=>$lugarNacimiento,
                //     'nacimientofecha'=>date_format($dateNacimiento, 'd/m/Y'),
                //     'cedulaidentidad'=>$entityDocumentoGenerado['carnetIdentidad'],
                //     'emisiondepartamento'=>$entityDocumentoGenerado['departamentoemision'],
                //     'emisionfecha'=>date_format($dateEmision, 'd/m/Y'),
                //     'tokenfirma'=>base64_encode($getDocumentoFirma['id'])
                // );
                $datos = array(
                    'inscripcion'=>$entityDocumentoGenerado['estudianteInscripcionId'],
                    'tramite'=>$entityDocumentoGenerado['tramite'],
                    'serie'=>$entityDocumentoGenerado['serie'],
                    'codigorude'=>$entityDocumentoGenerado['rude'],
                    'sie'=>$entityDocumentoGenerado['sie'],
                    'gestionegreso'=>$entityDocumentoGenerado['gestion'],
                    'nombre'=>$entityDocumentoGenerado['nombre'],
                    'paterno'=>$entityDocumentoGenerado['paterno'],
                    'materno'=>$entityDocumentoGenerado['materno'],
                    'nacimientolugar'=>$lugarNacimiento,
                    'nacimientofecha'=>$dateNacimiento->format('d/m/Y'),
                    'cedulaidentidad'=>$entityDocumentoGenerado['carnetIdentidad'],
                    'emisiondepartamento'=>$entityDocumentoGenerado['departamentoemision'],
                    'emisionfecha'=>$dateEmision->format('d/m/Y'),
                    'tokenfirma'=>base64_encode(serialize($arrayFirmas))
                );
                $keys = $this->getEncodeRSA($datos);

                // registro de la firma en el documento generado
                // solo se referencia con la primera firma enviada en caso de emitir documentos con mas de una firma
                $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $getDocumentoFirmaId));
                $entityDocumento->setDocumentoFirma($entityDocumentoFirma);
                $entityDocumento->setTokenPublico($keys['keyPublica']);
                $entityDocumento->setTokenPrivado($keys['keyPrivada']);
                $entityDocumento->setTokenImpreso($keys['token']);
                $em->persist($entityDocumento);
                // $em->flush();

                // dump($entityDocumento);dump($entityDocumentoFirma);dump($datos);die;    
                
                // incremento de la firma usada en la tabla de parametros documentoFirmaAutorizada
                // $documentoFirmaAutorizadaId = $this->getDocumentoFirmaAutorizadaIncrementar($entityDocumentoFirma->getPersona()->getId(), $documentoTipo);
                // $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id' => $documentoFirmaAutorizadaId));
                // $cantidadIncremento = ($entityDocumentoFirmaAutorizada->getUsado()) + 1;
                // $entityDocumentoFirmaAutorizada->setUsado($cantidadIncremento);
                // $em->persist($entityDocumentoFirmaAutorizada);
                // $em->flush();

                $em->flush();
            } 

            // $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id' => $documentoFirmaAutorizadaId['id']));
        }
        return $documentoId;
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
        
        //if ($departamentoCodigo == 0 and $msgContenido == ""){
        //    $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para los documentos" : $msgContenido.", "."el usuario no cuenta con autorizacion para los el documentos ";
        //} else {
        //    // VALIDACION DE TUICION DEL CARTON
        //    $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);
        //    if($valSerieTuicion != "" and $msgContenido == ""){
        //        $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
        //    }
        //}

        // VALIDACION DE TUICION DEL CARTON
        $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);
        if($valSerieTuicion != "" and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
        }

        return $msgContenido;
    }

    

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la truision de un determinado numero de serie segun el lugar geográfico
    // PARAMETROS: serie, departamento
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoFormacionValidacion($numeroSerie, $tipoSerie, $fecha, $usuarioId, $rolId, $documentoTipoId, $formacionEducacionId) {
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
        
        //if ($departamentoCodigo == 0 and $msgContenido == ""){
        //    $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para los documentos" : $msgContenido.", "."el usuario no cuenta con autorizacion para los el documentos ";
        //} else {
        //    // VALIDACION DE TUICION DEL CARTON
        //    $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);
        //    if($valSerieTuicion != "" and $msgContenido == ""){
        //        $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
        //    }
        //}

        // VALIDACION DE TUICION DEL CARTON
        $valSerieTuicion = $this->validaNumeroSerieFormacionTuicion($serie, $departamentoCodigo, $formacionEducacionId);
        if($valSerieTuicion != "" and $msgContenido == ""){
            $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
        }

        return $msgContenido;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los documentos activos en función al trámite y tipo de documento
    // PARAMETROS: tramiteId, documentoTipoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoTramiteTipo($tramiteId, $documentoTipoId) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tramite' => $tramiteId, 'documentoTipo' => $documentoTipoId, 'documentoEstado' => 1));
        return $entityDocumento;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los documentos activos en función al trámite
    // PARAMETROS: tramiteId, documentoTipoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoTramite($tramiteId) {
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tramite' => $tramiteId, 'documentoEstado' => 1));
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
            ->add('serie', 'text', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'class' => 'form-control', 'placeholder' => 'Número y Serie', 'pattern' => '^@?(\w){1,12}$', 'maxlength' => '12', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('obs', 'textarea', array('label' => 'OBS.', 'attr' => array('value' => $obs, 'class' => 'form-control', 'placeholder' => 'Comentario', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de documentos por numero de serie que sera anulados al reactivar su trámite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormReactivaDocumentoSerie($routing, $serie, $obs, $documentoId) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('documento', 'hidden', array('label' => 'DOCUMENTO', 'attr' => array('value' => base64_encode($documentoId))))
            ->add('serie', 'hidden', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'class' => 'form-control', 'placeholder' => 'Número y Serie', 'pattern' => '^@?(\w){1,12}$', 'maxlength' => '12', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('obs', 'hidden', array('label' => 'OBS.', 'attr' => array('value' => $obs, 'class' => 'form-control', 'placeholder' => 'Comentario', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => 'Reactivar Documento', 'attr' => array('class' => 'btn btn-primary')))
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
            ->add('serie', 'text', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'class' => 'form-control', 'placeholder' => 'Número y Serie', 'pattern' => '^@?(\w){1,12}$', 'maxlength' => '12', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
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
    // Funcion que genera un formulario para la busqueda de documentos por numero de serie en lote
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaInstitucionEducativaSerieLote($routing, $numero1, $numero2, $serie, $documentoTipoArrayId) {
        $entityDocumentoSerie = $this->getSerieTipo($documentoTipoArrayId);

        $serieEntity = array();
        foreach ($entityDocumentoSerie as $key => $dato) {
            $serieEntity[$dato['serie']] = $dato['serie'];
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('numeroInicial', 'number', array('label' => 'Número Inicial', 'attr' => array('value' => $numero1, 'class' => 'form-control', 'pattern' => '[0-9]{1,6}', 'maxlength' => '6', 'autocomplete' => 'on', 'placeholder' => 'Número inicial')))
            ->add('numeroFinal', 'number', array('label' => 'Número Final', 'attr' => array('value' => $numero2, 'class' => 'form-control', 'pattern' => '[0-9]{1,6}', 'maxlength' => '6', 'autocomplete' => 'on', 'placeholder' => 'Número final')))
            ->add('serie',
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

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $rolPermitido = array(14,16,17,8,42,43);

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

            if (count($entityDocumento) <= 0){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe el número de serie '.$serie));
                return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
            }  

            $msgvalidaNumeroSerieTipoDocumento = $this->validaNumeroSerieParaLegalizar($serie);

            if ($msgvalidaNumeroSerieTipoDocumento != ""){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgvalidaNumeroSerieTipoDocumento));
                return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
            }  

            $entityDocumentoDetalle = $this->getDocumentoDetalle($entityDocumento['tramite']);

            $documentoTipoId = 2;
            $entityFirma = $this->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);
            
            return $this->render($this->session->get('pathSystem') . ':Documento:legalizaDetalle.html.twig', array(
                'listaDocumento' => $entityDocumento,
                'listaDocumentoDetalle' => $entityDocumentoDetalle,
                'listaFirma' => $entityFirma,
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
        $documentoFirmaId = 0;

        if ($request->getMethod() == 'POST') {
            $documentoId = base64_decode($request->get('codigo'));    
            $documentoFirmaId = base64_decode($request->get('firma')); 
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

            if (!is_numeric($documentoFirmaId)){
                $documentoFirmaId = 0;
            }
            
            if (count($entity) > 0){
                if (count($entity) < 2){
                    /*
                     * Extrae la observacion si el estudiante esta con procesos juridicos
                     */
                    if($entity[0]["observacion"] != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estudiante con código rude '.$entity[0]["rude"].' tiene la siguiente observación: '.$entity[0]["observacion"]));
                        // return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                        return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
                    }

                    if (!$entity[0]["estadofintramite"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" y con número de serie "'.$entity[0]["serie"].'" no concluyo su emisión, conluya e intente nuevamente'));
                        // return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                        return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
                    } elseif (!$entity[0]["estadodocumento"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con numero de serie "'.$entity[0]["serie"].'" se encuentra anulado, no es posible realizar su legalicación'));
                        // return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                        return $this->redirectToRoute('tramite_documento_legalizacion_numero_serie');
                    } else {
                        $em->getConnection()->beginTransaction();
                        try {
                            if($documentoFirmaId != 0 and $documentoFirmaId != ""){
                                $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                                //dump($documentoFirmaId);die;
                                if (count($entidadDocumentoFirma)>0) {
                                    $firmaPersonaId = $entidadDocumentoFirma->getPersona()->getId();    
                                    // $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                                    $valFirmaDisponible =  $this->verFirmaAutorizadoDisponible($firmaPersonaId,1,2);
                                } else {
                                    $documentoFirmaId = 0;
                                    throw new exception('Dificultades al realizar el registro, intente nuevamente');
                                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No se encontro la firma ingresada, intente nuevamente'));
                                    // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                                }
                            } else {
                                $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');
                                $documentoFirmaId = 0;
                            }
                            if ($valFirmaDisponible[0]){
                                $idDocumento = $this->setDocumento($entity[0]["tramite_id"], $usuarioId, 2, $entity[0]["serie"], "", $fechaActual, $documentoFirmaId);
                                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El documento con numero de serie "'.$entity[0]["serie"].'" fue legalizado'));
                                $em->getConnection()->commit();        
                            } else {
                                throw new exception('Dificultades al realizar el registro, '.$valFirmaDisponible[1].', intente nuevamente');
                            }
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

        //$activeMenu = $defaultTramiteController->setActiveMenu($route);

        $rolPermitido = 16;

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        $documentoTipoId = 2;
        $departamentoCodigo = $this->getCodigoLugarRol($id_usuario,$rolPermitido);

        $entityFirma = $this->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

        $arrayFirma = array(''=>'Seleccione la persona que firmara');
        if (count($entityFirma)>0){
            foreach ($entityFirma as $registro)
            {
                $arrayFirma[base64_encode($registro['documento_firma_id'])] = $registro['nombre']." ".$registro['paterno']." ".$registro['materno'];
            }
        }
        $arrayFirma[base64_encode('0')] = 'SIN FIRMA EN EL DOCUMENTO';
                
        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Documento:legalizaInstitucionEducativaIndex.html.twig', array(
            'form' => $this->creaFormBuscaInstitucionEducativaFirma('tramite_documento_legalizacion_institucion_educativa_pdf', '',$gestionActual, $arrayFirma)->createView()
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
    public function creaFormBuscaInstitucionEducativaFirma($routing, $institucionEducativaId, $gestionId, $firma) {
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
                ->add('firma','choice',
                      array('label' => 'Firma',
                            'choices' => $firma,
                            'data' => '', 'attr' => array('class' => 'form-control')))
                ->add('search', 'submit', array('label' => 'Legalizar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
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
                $documentoFirmaId = base64_decode($form['firma']); 
                
                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);
                $rolPermitido = array(8,17);
                $usuarioRol = $this->session->get('roluser');
                $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($usuarioId, $sie, $usuarioRol, 'DIPLOMAS');

                if ($verTuicionUnidadEducativa != ''){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Su usuario no cuenta con la respectiva tuición de la institución educativa'.$sie));
                    return $this->redirectToRoute('tramite_documento_legalizacion_institucion_educativa');
                }

                // validar cantidad de firmas
                $em = $this->getDoctrine()->getManager();
                
                $entityDocumentoInstitucionEducativa = $this->getDocumentoInstitucionEducativaGestion($sie, $ges, '1');
                
                $cantidadSolicitada = count($entityDocumentoInstitucionEducativa);
                
                $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                
                if (count($entityDocumentoFirma)>0) {
                    $firmaPersonaId = $entityDocumentoFirma->getPersona()->getId();     
                    // $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                    $valFirmaDisponible =  $this->verFirmaAutorizadoDisponible($firmaPersonaId,$cantidadSolicitada,2);
                    //dump($valFirmaDisponible);die;
                    if($valFirmaDisponible[0]){
                        // incremento de la firma usada en la tabla de parametros documentoFirmaAutorizada
                        
                        $cantidadSolicitadaParcial = $cantidadSolicitada;
                        while ($cantidadSolicitadaParcial > 0) {
                            $documentoFirmaAutorizadaId = $this->getDocumentoFirmaAutorizadaIncrementar($entityDocumentoFirma->getPersona()->getId(), 2);
                            $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id' => $documentoFirmaAutorizadaId));
                            $maximo = $entityDocumentoFirmaAutorizada->getMaximo();
                            $usado = $entityDocumentoFirmaAutorizada->getUsado();
                            $disponible = $maximo - $usado;
                            if(($disponible - $cantidadSolicitadaParcial)<0){
                                $cantidadSolicitadaParcial = $cantidadSolicitadaParcial - $disponible;
                                $cantidadIncremento = ($usado) + $disponible;
                            } else {
                                $cantidadIncremento = ($usado) + $cantidadSolicitadaParcial;
                                $cantidadSolicitadaParcial = 0;
                            }       
                            $entityDocumentoFirmaAutorizada->setUsado($cantidadIncremento);
                            $em->persist($entityDocumentoFirmaAutorizada);
                            $em->flush();
                        }
                    } else {
                        $formBusqueda = array('sie'=>$sie,'gestion'=>$ges);
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $valFirmaDisponible[1]));
                        return $this->redirectToRoute('tramite_documento_legalizacion_institucion_educativa', ['form' => $formBusqueda], 307);
                    }
                } else {
                    $valFirmaDisponible = array(0 => true, 1 => '');
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No se encontro la firma ingresada, intente nuevamente'));
                    // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                }

                $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
                $response = new Response();
                $response->headers->set('Content-type', 'application/pdf');
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                if($documentoFirmaId == 211){
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_ue_ch_v2.rptdesign&sie='.$sie.'&gestion='.$ges.'&firma='.$documentoFirmaId.'&&__format=pdf&'));
                } else { 
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_ue_v2.rptdesign&sie='.$sie.'&gestion='.$ges.'&firma='.$documentoFirmaId.'&&__format=pdf&'));
                }
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

        //$activeMenu = $defaultTramiteController->setActiveMenu($route);

        $rolPermitido = array(16,17,8,42);

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
                $em = $this->getDoctrine()->getManager();
                $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('documentoSerie' => $serie));
                $tramiteId = $entityDocumento->getTramite()->getId();
                $entityDocumento = $this->getDocumentoSupletorio($tramiteId);      
                if (count($entityDocumento)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El Documento con número de serie '.$serie.' no es válido para generar un certificado supletorio, intente nuevamente'));
                    return $this->redirectToRoute('tramite_documento_supletorio');
                } 
            } 

            $documentoId = $entityDocumento['id'];

            $entityDocumentoDetalle = $this->getDocumentoSupletorioDetalle($entityDocumento['tramite']);
            //dump($entityDocumentoDetalle);die;
            
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
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Documento no encontrado, intente nuevamente'));
                    return $this->redirectToRoute('tramite_documento_supletorio');
                } 

                $formBusqueda = array('serie'=>$entityDocumento['serie']);

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
                        $documentoFirmaId = 0;
                        $idDocumento = $this->setDocumento($codTramite, $id_usuario, 9, $numeroSerieSupletorio, '', $fechaActual, $documentoFirmaId); 
                        $documentoAnuladoId = $this->setDocumentoEstado($documentoId, 2, $em);
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El certificado supletorio con numero de serie "'.$numeroSerieSupletorio.'" fue generado, anulado el documento'.$entityDocumento['serie']));
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

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que extrae una firma activa de forma aleatoria de una persona
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoFirmaLugar($personaId, $lugarCodigo, $personaTipoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select df.id as id, df.firma as imagen, df.obs, df.fecha_registro as fechaRegistro, df.token_firma as tokenFirma, p.nombre, p.paterno, p.materno, lt.id as lugarId, lt.codigo, lt.lugar 
            from documento_firma as df
            inner join persona as p on p.id = df.persona_id
            inner join persona_tipo as pt on pt.id = df.persona_tipo_id
            inner join lugar_tipo as lt on lt.id = df.lugar_tipo_id
            where lt.codigo = '".$lugarCodigo."' and p.id = ".$personaId." and pt.id = ".$personaTipoId." and df.esactivo = true
            order by random() limit 1
        ");
        $queryEntidad->execute();
        $entityDocumentoFirma = $queryEntidad->fetchAll();
        if(count($entityDocumentoFirma)>0){
            return $entityDocumentoFirma[0];
        } else {
            return $entityDocumentoFirma;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que encripta un texto mediante una llave pública
    // PARAMETROS: texto, llave, 
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getEncodeRSA($datos) {
        
        $datos = (serialize($datos));      
        // $salt = '123456'.'abc';   

        $rsa = new RSA;
        $key = $rsa->createkey(1024); 
        $keyPublica = $key['publickey'];   
        $keyPrivada = $key['privatekey'];   

        $rsa->loadKey($keyPublica);
        // $text = implode("|",($datos));
        $datosEncrypt = $rsa->encrypt($datos);
        $datosEncryptEncode = base64_encode($datosEncrypt);
        
        return array('token'=>$datosEncryptEncode, 'keyPublica'=>$keyPublica, 'keyPrivada'=>$keyPrivada);
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que desencripta un texto con una llave privada
    // PARAMETROS: texto, llave, 
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDecodeRSA($datos, $keyPrivada) {
        $datosEncryptDecode = base64_decode($datos);
        $rsa = new RSA;
        $rsa->loadKey($keyPrivada);
        $datos = $rsa->decrypt($datosEncryptDecode);
        // explode("|",$datos)
        return $datos;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que verifica si aun cuenta con firmas para usarse según el propietario (persona) y la cantidad maxima parametrizada
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function verFirmaAutorizadoDisponible($personaId,$cantidadSolicitado,$documentoTipoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select dfa.persona_id, df1.lugar_tipo_id, sum(dfa.maximo) as cupo, sum(dfa.usado) as cantidad 
            from documento_firma_autorizada as dfa
            inner join persona as p on p.id = dfa.persona_id
            inner join (select distinct on (df.persona_id, df.lugar_tipo_id) df.id, df.persona_id, df.lugar_tipo_id from documento_firma as df inner join persona as p on p.id = df.persona_id inner join lugar_tipo as lt on lt.id = df.lugar_tipo_id where df.persona_id = ".$personaId." and df.esactivo = true order by df.persona_id, df.lugar_tipo_id) as df1 on df1.persona_id = dfa.persona_id
            inner join lugar_tipo as lt on lt.id = df1.lugar_tipo_id
            where dfa.esactivo = true and dfa.documento_tipo_id = ".$documentoTipoId."
            group by dfa.persona_id, df1.lugar_tipo_id
            ");
        $queryEntidad->execute();
        $entity = $queryEntidad->fetchAll();

        $cantidadDisponible = 0;
        if(count($entity)>0){
            $cupo = $entity[0]['cupo'];
            $cantidad = $entity[0]['cantidad'];
            $cantidadDisponible = $cupo - $cantidad;
        } else {
            $cupo = 0;
            $cantidad = 0;
            $cantidadDisponible = 0;
        }
        
        if (($cantidadDisponible - $cantidadSolicitado)>=0){
            return array('0'=>true, '1'=>'');
        } else {
            if($cantidadDisponible == 0){
                return array('0'=>false, '1'=>'Ya no cuenta con firmas autorizadas para la(s) '.$cantidadSolicitado.' firma(s) solicitada(s)');
            } else {
                return array('0'=>false, '1'=>'Solo cuenta con '.$cantidadDisponible.' firma(s) autorizada(s) para la(s) '.$cantidadSolicitado.' firma(s) solicitada(s)');
            }
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las personasc que firman segun el departamento y tipo de documento
    // PARAMETROS: lugarCodigo, documentoTipoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getPersonaFirmaAutorizada($lugarCodigo,$documentoTipoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
        select distinct df1.id as documento_firma_id, p.id as persona_id, p.nombre, p.paterno, p.materno, lt.id as lugarId, lt.codigo as lugarCodigo, lt.lugar as cantidad 
            from documento_firma_autorizada as dfa
            inner join persona as p on p.id = dfa.persona_id
            inner join (select distinct on (df.persona_id, df.lugar_tipo_id) df.id, df.persona_id, df.lugar_tipo_id from documento_firma as df inner join persona as p on p.id = df.persona_id inner join lugar_tipo as lt on lt.id = df.lugar_tipo_id where lt.codigo = '".$lugarCodigo."' and df.esactivo = true order by df.persona_id, df.lugar_tipo_id) as df1 on df1.persona_id = dfa.persona_id
            inner join lugar_tipo as lt on lt.id = df1.lugar_tipo_id
            where dfa.esactivo = true and dfa.documento_tipo_id = ".$documentoTipoId."
        ");
        $queryEntidad->execute();
        $entity = $queryEntidad->fetchAll();
       
        if(count($entity)>0){
            return $entity;
        } else {
            return array();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que controla las firmas utilizadas según el propietario (persona)
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDocumentoFirmaAutorizadaIncrementar($personaId, $documentoTipoId) {
        $em = $this->getDoctrine()->getManager();
        $entidadDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findBy(array('persona' => $personaId, 'esactivo' => true, 'documentoTipo' => $documentoTipoId), array('id' => 'ASC'));
        $cantidadAutorizada = 0;
        $cantidadUtilizada = 0;
        $documentoFirmaAutorizadaId = 0;
        foreach ($entidadDocumentoFirmaAutorizada as $dato)
        {
            $cantidadAutorizada = $dato->getMaximo();
            $cantidadUtilizada = $dato->getUsado();
            if(($cantidadAutorizada - $cantidadUtilizada) > 0 and $documentoFirmaAutorizadaId == 0){
                $documentoFirmaAutorizadaId = $dato->getId();
            }
        }
        return $documentoFirmaAutorizadaId;
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las autorizaciones de una persona siempre y cuando cuente con firmas
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getFirmaAutorizada($personaId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select dfa.id, dfa.maximo, dfa.usado, dfa.fecha_registro, dfa.obs, dfa.esactivo, dt.documento_tipo, p.nombre, p.paterno, p.materno, lt.id as lugar_id, lt.codigo as lugar_codigo, lt.lugar, pt.persona
            from documento_firma_autorizada as dfa
            inner join persona as p on p.id = dfa.persona_id
            inner join documento_tipo as dt on dt.id = dfa.documento_tipo_id
            inner join (select distinct on (df.persona_id, df.lugar_tipo_id) df.id, df.persona_id, df.lugar_tipo_id, df.persona_tipo_id from documento_firma as df inner join persona as p on p.id = df.persona_id inner join lugar_tipo as lt on lt.id = df.lugar_tipo_id where df.persona_id = ".$personaId." and df.esactivo = true order by df.persona_id, df.lugar_tipo_id) as df1 on df1.persona_id = dfa.persona_id
            inner join lugar_tipo as lt on lt.id = df1.lugar_tipo_id
            inner join persona_tipo as pt on pt.id = df1.persona_tipo_id
            -- where dfa.esactivo = true
            order by dfa.fecha_registro desc, dfa.id desc
        ");
        $queryEntidad->execute();
        $entity = $queryEntidad->fetchAll();
        
        if (count($entity)>0){
            $entity = $entity;
        } else {
            $entity = array();
        }

        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el listado de numeros de serie disponibles para su asignacion
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function serieDisponibleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $route = $request->get('_route');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(16,8,42);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $usuarioLugarId = $this->getCodigoLugarRol($id_usuario,8);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,array(8));
        
        if ($esValidoUsuarioRol){
            $usuarioLugarId = 0;
        } else {
            $usuarioLugarId = $this->getCodigoLugarRol($id_usuario,16);
        }
        $gestionId = $gestionActual->format('Y');
        $formGestion = $request->get('gestion');
        if ($formGestion) {
            $gestionId = $formGestion;
        } 

        $lista = $this->getSerieDisponible($usuarioLugarId,$gestionId);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $gestionEntity = $tramiteController->getGestiones(2010);
        // dump($lista);die;

        return $this->render($this->session->get('pathSystem') . ':Documento:serieDisponible.html.twig', array(
            'series' => $lista
            , 'gestiones' => $gestionEntity
            , 'gestion' => $gestionId
            , 'titulo' => 'Numero y Serie'
            , 'subtitulo' => 'Disponible'
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los números de serie registrados
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getSerie($departamentoCodigo,$gestionId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select vv.*, dot.documento_tipo, dt.departamento from (
                select v.departamento_tipo_id, v.gestion_id, v.documento_tipo_id
                , array_agg(distinct v.id order by v.id) as ids, string_agg(distinct v.serie, ',') as serie, array_length(array_agg(distinct v.id),1) as count
                , (array_agg(distinct v.id))[1] as primer, (array_agg(distinct v.id))[array_length(array_agg(distinct v.id),1)] as ultimo
                from (
                    select (cast((case when ds.gestion_id in (2010,2013) then substring(ds.id from 1 for LENGTH(ds.id)-2) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from 1 for LENGTH(ds.id)-1) else substring(ds.id from 1 for 6) end) as integer)) - (row_number() over (partition by ds.departamento_tipo_id, ds.gestion_id
                    , ds.documento_tipo_id order by ds.id)) as group_id
                    , case when ds.gestion_id in (2010,2013) then substring(ds.id from 1 for LENGTH(ds.id)-2) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from 1 for LENGTH(ds.id)-1) else substring(ds.id from 1 for 6) end as numero
                    , case when ds.gestion_id in (2010,2013) then substring(ds.id from LENGTH(ds.id)-1 for LENGTH(ds.id)) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from LENGTH(ds.id) for LENGTH(ds.id)) else substring(ds.id from 7 for 11)  end as serie
                    , (row_number() over (partition by ds.departamento_tipo_id, ds.gestion_id, ds.documento_tipo_id order by ds.id)) as row_num
                    , ds.*
                    from documento_serie as ds
                    where ds.documento_tipo_id in (1,9,6,7,8) and ds.gestion_id = ".$gestionId." and ds.esanulado = false and case ".$departamentoCodigo." when 0 then true else ds.departamento_tipo_id = ".$departamentoCodigo." end
                    order by ds.id
                ) as v
                group by v.departamento_tipo_id, v.gestion_id, v.documento_tipo_id, v.group_id
            ) as vv
            inner join departamento_tipo as dt on vv.departamento_tipo_id = dt.id
            inner join documento_tipo as dot on vv.documento_tipo_id = dot.id
            order by vv.gestion_id, vv.departamento_tipo_id, vv.documento_tipo_id
        ");
        $queryEntidad->execute();
        $entity = $queryEntidad->fetchAll();
        
        if (count($entity)>0){
            $entity = $entity;
        } else {
            $entity = array();
        }

        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los números de serie disponibles
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getSerieDisponible($departamentoCodigo,$gestionId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select vv.*, dot.documento_tipo, dt.departamento from (
                select v.departamento_tipo_id, v.gestion_id, v.documento_tipo_id
                , array_agg(distinct v.id order by v.id) as ids, string_agg(distinct v.serie, ',') as serie, array_length(array_agg(distinct v.id),1) as count
                , (array_agg(distinct v.id))[1] as primer, (array_agg(distinct v.id))[array_length(array_agg(distinct v.id),1)] as ultimo
                from (
                    select (cast((case when ds.gestion_id in (2010,2013) then substring(ds.id from 1 for LENGTH(ds.id)-2) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from 1 for LENGTH(ds.id)-1) else substring(ds.id from 1 for 6) end) as integer)) - (row_number() over (partition by ds.departamento_tipo_id, ds.gestion_id
                    , ds.documento_tipo_id order by ds.id)) as group_id
                    , case when ds.gestion_id in (2010,2013) then substring(ds.id from 1 for LENGTH(ds.id)-2) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from 1 for LENGTH(ds.id)-1) else substring(ds.id from 1 for 6) end as numero
                    , case when ds.gestion_id in (2010,2013) then substring(ds.id from LENGTH(ds.id)-1 for LENGTH(ds.id)) when ds.gestion_id in (2009,2011,2012,2014) then substring(ds.id from LENGTH(ds.id) for LENGTH(ds.id)) else substring(ds.id from 7 for 11)  end as serie
                    , (row_number() over (partition by ds.departamento_tipo_id, ds.gestion_id, ds.documento_tipo_id order by ds.id)) as row_num
                    , ds.*
                    from documento_serie as ds
                    left join documento as d on d.documento_serie_id = ds.id
                    where ds.documento_tipo_id in (1,9,6,7,8) and ds.gestion_id = ".$gestionId." and esanulado = false and d.id is null and case ".$departamentoCodigo." when 0 then true else ds.departamento_tipo_id = ".$departamentoCodigo." end
                    order by ds.id
                ) as v
                group by v.departamento_tipo_id, v.gestion_id, v.documento_tipo_id, v.group_id
            ) as vv
            inner join departamento_tipo as dt on vv.departamento_tipo_id = dt.id
            inner join documento_tipo as dot on vv.documento_tipo_id = dot.id
            order by vv.gestion_id, vv.departamento_tipo_id, vv.documento_tipo_id
        ");
        $queryEntidad->execute();
        $entity = $queryEntidad->fetchAll();
        
        if (count($entity)>0){
            $entity = $entity;
        } else {
            $entity = array();
        }

        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el listado de numeros y serie
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function serieAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $route = $request->get('_route');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(8);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);
        $gestion = $request->get('gestion');
        
        if (isset($gestion)) {
            $gestionId = $gestion;
        } else {
            $gestionId = $gestionActual->format('Y');
        }

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $documentoSerieLista = $this->getSerie(0,$gestionId);

        
        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $gestionEntity = $tramiteController->getGestiones(2010);
        
        return $this->render($this->session->get('pathSystem') . ':Documento:serie.html.twig',array(
            // 'form'=>$this->creaFormularioSerie()->createView(),         
            'series' => $documentoSerieLista,   
            'gestiones' => $gestionEntity, 
            'gestion' => $gestionId,
            'titulo' => 'Número y Serie',
            'subtitulo' => 'Registro',
    	));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para registro de numeros de serie
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function serieNuevoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        
        return $this->render($this->session->get('pathSystem') . ':Documento:serieFormulario.html.twig',array(
            'form'=>$this->creaFormularioSerie()->createView(),         
    	));
    }


    private function creaFormularioSerie()
    { 
        $form = $this->createFormBuilder()     
            ->add('numInicial', 'number', array('label' => 'Número Inicial', 'attr' => array('value' => "", 'class' => 'form-control', 'pattern' => '[0-9]{1,6}', 'maxlength' => '6', 'autocomplete' => 'on', 'placeholder' => 'Número Inicial', 'required'=>'true')))      
            ->add('numFinal', 'number', array('label' => 'Número Final', 'attr' => array('value' => "", 'class' => 'form-control', 'pattern' => '[0-9]{1,6}', 'maxlength' => '6', 'autocomplete' => 'on', 'placeholder' => 'Número Final', 'required'=>'true')))      
            ->add('serie', 'text', array('label' => 'Serie', 'attr' => array('value' => "", 'class' => 'form-control', 'maxlength' => '6', 'autocomplete' => 'on', 'placeholder' => 'Serie del cartón', 'required'=>'true')))
            ->add('documentoTipo', 'entity', array('data' => array(), 'empty_value' => 'Seleccione el tipo de Documento', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\DocumentoTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('dt')
                                ->where('dt.id in (1,9,6,7,8)')
                                ->orderBy('dt.id', 'ASC');
                    },
            ))
            ->add('gestion', 'entity', array('data' => array(), 'empty_value' => 'Seleccione la Gestión', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2009')
                                ->orderBy('gt.id', 'DESC');
                    },
            ))
            ->add('departamento', 'entity', array('data' => array(), 'empty_value' => 'Seleccione Departamento', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\LugarTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = 6')
                                ->andWhere('lt.id != 31352')
                                ->orderBy('lt.lugar', 'ASC');
                    },
            ))
            ->add('obs', 'text', array('label' => 'Observación', 'attr' => array('value' => "", 'class' => 'form-control', 'autocomplete' => 'on', 'placeholder' => 'Observación', 'required'=>'false')))
            ->add('guardar', 'button', array('label' => 'Guardar', 'attr' => array('onclick'=>'guardar()')))
            ->getForm();
        return $form;        
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que guarda los numeros de serie solicitados
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function serieNuevoGuardaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $route = $request->get('_route');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $msg = "";
        $estado = true;
        $info = array();

        $rolPermitido = array(8);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }
        
        $form = $request->get('form');
        $numInicial = $form['numInicial'];
        $numFinal = $form['numFinal'];
        $serie = trim($form['serie']);
        $lugarTipoId = $form['departamento'];
        $gestionTipoId = $form['gestion'];
        $documentoTipoId = $form['documentoTipo'];

        $em = $this->getDoctrine()->getManager();
     
        $entidadLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarTipoId);
        $lugarTipoCodigo = $entidadLugarTipo->getCodigo();
        $departamento = $entidadLugarTipo->getLugar();

        $entidadDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->find($documentoTipoId);
        $documentoTipo = $entidadDocumentoTipo->getDocumentoTipo();

        $query = $em->getConnection()->prepare('select * from sp_genera_numero_serie_documento (:numInicial, :numFinal, :serie, :gestionTipoId, :lugarTipoCodigo, :documentoTipoId);');
        $query->bindValue(':numInicial', $numInicial);
        $query->bindValue(':numFinal', $numFinal);
        $query->bindValue(':serie', $serie);
        $query->bindValue(':lugarTipoCodigo', $lugarTipoCodigo);
        $query->bindValue(':gestionTipoId', $gestionTipoId);
        $query->bindValue(':documentoTipoId', $documentoTipoId);
        $query->execute();
        $resultado = $query->fetchAll();

        $info = array();

        if($resultado[0]['sp_genera_numero_serie_documento'] != ""){
            $msg = "Error: ".$resultado[0]['sp_genera_numero_serie_documento'];
            $estado = false;
        } else {
            $msg = "Correcto: La asiganción de los numeros de serie fue registrado.";
            $info = array('departamento'=>$departamento, 'tipo'=>$documentoTipo, 'serie'=>$serie, 'rango'=>str_pad($numInicial, 6, "0", STR_PAD_LEFT).$serie.' al '.str_pad($numFinal, 6, "0", STR_PAD_LEFT).$serie, 'cantidad'=>($numFinal-$numInicial+1));
            $estado = true;
        }

        $response = new JsonResponse();
        return $response->setData(array('msg' => $msg, 'estado' => $estado, 'info' => $info));
    }

    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que verifica la valides del documento emitido con firma electronica
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validarDocumentoElectronicoAction(Request $request, $qr){
        $em = $this->getDoctrine()->getManager();
        
        $datosEnviados = str_replace(' ', '+', $qr);
        $datosEnviados = str_replace('%20', '+', $datosEnviados);    
        $msg = '';
        $estado = true;            
        $datosDocumentoValidado = array();

        //dump($request);dump($datosEnviados);die;
        //$entityDocumento = $this->getDocumentoMd5($documento);
        // $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tokenImpreso' => $datosEnviados));
        $entityDocumento = $this->getDocumentoTokenImpreso($datosEnviados);
        //dump(count($entityDocumento));die;
        $entityDocumentoTipoId = 0;
        $entityEducacionTipoId = 0;
        
        if(count($entityDocumento)>0){
            if(count($entityDocumento)>1){
                $msg = 'Documento observado por duplicidad en su registro, verifique con la Dirección Distrital o Dirección Departamental';
                $estado = false;
            } else {
                $entityDocumento = $entityDocumento[0];
                $entityDocumentoEstadoId = $entityDocumento['documentoestadoid'];
                $entityDocumentoTipoId = $entityDocumento['documentotipoid'];
                $entityEducacionTipoId = $entityDocumento['educaciontipoid'];
                if($entityDocumentoEstadoId != 1){
                    $msg = 'Documento no vigente, verifique con la Dirección Distrital o Dirección Departamental';
                    $estado = false;
                } else {            
                    if ($entityDocumento['departamentonacimiento'] == ""){
                        $lugarNacimiento = $entityDocumento['departamentonacimiento'];
                    } else {
                        $lugarNacimiento = $entityDocumento['departamentonacimiento'] . ' - ' . $entityDocumento['paisnacimiento'];
                    }
                    $datos = array(
                        'inscripcion'=>$entityDocumento['estudianteinscripcionid'],
                        'tramite'=>$entityDocumento['tramite'],
                        'serie'=>$entityDocumento['serie'],
                        'codigorude'=>$entityDocumento['rude'],
                        'sie'=>$entityDocumento['sie'],
                        'gestionegreso'=>$entityDocumento['gestion'],
                        'nombre'=>$entityDocumento['nombre'],
                        'paterno'=>$entityDocumento['paterno'],
                        'materno'=>$entityDocumento['materno'],
                        'nacimientolugar'=>$lugarNacimiento,
                        'nacimientofecha'=>$entityDocumento['fechanacimiento'],
                        'cedulaidentidad'=>$entityDocumento['carnetidentidad'],
                        'emisiondepartamento'=>$entityDocumento['departamentoemision'],
                        'emisionfecha'=>$entityDocumento['fechaemision'],
                        'documentotipo'=>$entityDocumento['documentotipo'],
                        'personafirma'=>$entityDocumento['personafirma']
                    );
                    if($entityDocumentoTipoId == 8) {
                        if($entityEducacionTipoId == 2){
                            $datos['tokenfirma']=base64_encode(serialize(array(0=>$entityDocumento['documentofirmaid'], 1=>0)));
                        } elseif ($entityEducacionTipoId == 2) {
                            $datos['tokenfirma']=base64_encode(serialize(array(0=>$entityDocumento['documentofirmaid'], 1=>0)));
                        }                        
                    } else {                        
                        $datos['tokenfirma']=base64_encode($entityDocumento['documentofirmaid']);
                    }
                    // dump($datos);
                    $keys = $this->getEncodeRSA($datos);
                    $datosRegistrados = $datos;

                    $keyPrivado = $entityDocumento['keyprivado'];
                    // $keyPrivado = $ll;
                    // dump(md5(3197208));dump($entityDocumento['token_impreso']);dump($datos);dump($datosEnviados);dump($ll);
                    try {
                        $datosEnviadosDecode = $this->getDecodeRSA($datosEnviados, $keyPrivado);
                        $datosEnviadosDecode = unserialize($datosEnviadosDecode);
                        if($entityDocumentoTipoId == 8) {
                            if(
                                $datosEnviadosDecode['inscripcion'] == $datosRegistrados['inscripcion'] and 
                                $datosEnviadosDecode['tramite'] == $datosRegistrados['tramite'] and 
                                $datosEnviadosDecode['serie'] == $datosRegistrados['serie'] and 
                                $datosEnviadosDecode['codigorude'] == $datosRegistrados['codigorude'] and 
                                $datosEnviadosDecode['sie'] == $datosRegistrados['sie'] and 
                                $datosEnviadosDecode['gestionegreso'] == $datosRegistrados['gestionegreso'] and 
                                $datosEnviadosDecode['nombre'] == $datosRegistrados['nombre'] and 
                                $datosEnviadosDecode['paterno'] == $datosRegistrados['paterno'] and 
                                $datosEnviadosDecode['materno'] == $datosRegistrados['materno'] and 
                                $datosEnviadosDecode['cedulaidentidad'] == $datosRegistrados['cedulaidentidad'] and 
                                $datosEnviadosDecode['emisiondepartamento'] == $datosRegistrados['emisiondepartamento'] 
                                ){
                                $msg = 'Documento válido';
                                $estado = true;
                                $datosDocumentoValidado = $datosRegistrados;
                            } else {
                                $msg = 'Datos modificados, documento no válido';
                                $estado = false;
                            }
                        } else {
                            if(
                                $datosEnviadosDecode['inscripcion'] == $datosRegistrados['inscripcion'] and 
                                $datosEnviadosDecode['tramite'] == $datosRegistrados['tramite'] and 
                                $datosEnviadosDecode['serie'] == $datosRegistrados['serie'] and 
                                $datosEnviadosDecode['codigorude'] == $datosRegistrados['codigorude'] and 
                                $datosEnviadosDecode['sie'] == $datosRegistrados['sie'] and 
                                $datosEnviadosDecode['gestionegreso'] == $datosRegistrados['gestionegreso'] and 
                                $datosEnviadosDecode['nombre'] == $datosRegistrados['nombre'] and 
                                $datosEnviadosDecode['paterno'] == $datosRegistrados['paterno'] and 
                                $datosEnviadosDecode['materno'] == $datosRegistrados['materno'] and 
                                $datosEnviadosDecode['cedulaidentidad'] == $datosRegistrados['cedulaidentidad'] and 
                                $datosEnviadosDecode['emisiondepartamento'] == $datosRegistrados['emisiondepartamento'] and 
                                $datosEnviadosDecode['tokenfirma'] == $datosRegistrados['tokenfirma']
                                ){
                                $msg = 'Documento válido';
                                $estado = true;
                                $datosDocumentoValidado = $datosRegistrados;
                            } else {
                                $msg = 'Datos modificados, documento no válido';
                                $estado = false;
                            }
                        }
                    } catch (\Exception $e) {
                        $msg = 'Documento no válido';
                        $estado = false;
                        // dump("no decodificado");die;
                    }
                }
            }
        } else {
            $msg = 'Documento inexistente, verifique con la Dirección Distrital o Dirección Departamental';
            $estado = false;
        }
        // dump($this->session->get('pathSystem'));die;

        if($entityDocumentoTipoId == 1 or $entityDocumentoTipoId == 2 or $entityDocumentoTipoId == 9){
            $subtitulo = "Diploma de Bachiller";
        } elseif ($entityDocumentoTipoId == 6){
            $subtitulo = "Técnico Básico";
        } elseif ($entityDocumentoTipoId == 7){
            $subtitulo = "Técnico Auxiliar";
        } elseif ($entityDocumentoTipoId == 8){
            if($entityEducacionTipoId == 2){                
                $subtitulo = "Bachillerato Técnico Humanístico";
            } elseif ($entityEducacionTipoId == 3){
                $subtitulo = "Técnica Medio";
            } else {
                $subtitulo = "";
            }
        } else{
            $subtitulo = "";
        }
        return $this->render('SieTramitesBundle:Documento:validacion.html.twig',array(
            'listaDocumento' => $datosDocumentoValidado,   
            'estado' => $estado, 
            'msg' => $msg,
            'titulo' => 'Validación del documento',
            'subtitulo' => $subtitulo,
    	));
        
    }    
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que verifica la valides del documento emitido con firma electronica
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function validarDocumentoAction(Request $request, $qr){
        $em = $this->getDoctrine()->getManager();
        
        $datosEnviados = str_replace(' ', '+', $qr);
        $datosEnviados = str_replace('%20', '+', $datosEnviados);    
        $msg = '';
        $estado = true;            
        $datosDocumentoValidado = array();

        $entityDocumento = $this->getDocumentoMd5($datosEnviados);
        // dump($request);dump($datosEnviados);dump($entityDocumento);die;
        // $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tokenImpreso' => $datosEnviados));
        // $entityDocumento = $this->getDocumentoTokenImpreso($datosEnviados);
        //dump(count($entityDocumento));die;
        
        if(count($entityDocumento)>0){
            if(count($entityDocumento)>1){
                $msg = 'Documento observado por duplicidad en su registro, verifique con la Dirección Distrital o Dirección Departamental';
                $estado = false;
            } else {
                $entityDocumento = $entityDocumento[0];
                $entityDocumentoEstadoId = $entityDocumento['documentoestadoid'];
                if($entityDocumentoEstadoId != 1){
                    $msg = 'Documento no vigente, verifique con la Dirección Distrital o Dirección Departamental';
                    $estado = false;
                } else {            
                    if ($entityDocumento['departamentonacimiento'] == ""){
                        $lugarNacimiento = $entityDocumento['departamentonacimiento'];
                    } else {
                        $lugarNacimiento = $entityDocumento['departamentonacimiento'] . ' - ' . $entityDocumento['paisnacimiento'];
                    }
                    $datos = array(
                        'inscripcion'=>$entityDocumento['estudianteinscripcionid'],
                        'tramite'=>$entityDocumento['tramite'],
                        'serie'=>$entityDocumento['serie'],
                        'codigorude'=>$entityDocumento['rude'],
                        'sie'=>$entityDocumento['sie'],
                        'gestionegreso'=>$entityDocumento['gestion'],
                        'nombre'=>$entityDocumento['nombre'],
                        'paterno'=>$entityDocumento['paterno'],
                        'materno'=>$entityDocumento['materno'],
                        'nacimientolugar'=>$lugarNacimiento,
                        'nacimientofecha'=>$entityDocumento['fechanacimiento'],
                        'cedulaidentidad'=>$entityDocumento['carnetidentidad'],
                        'emisiondepartamento'=>$entityDocumento['departamentoemision'],
                        'emisionfecha'=>$entityDocumento['fechaemision'],
                        'tokenfirma'=>'',
                        'documentotipo'=>$entityDocumento['documentotipo'],
                        'personafirma'=>$entityDocumento['personafirma']
                    );
                    // $keys = $this->getEncodeRSA($datos);
                    $datosRegistrados = $datos;
                    //dump($datosRegistrados);die;

                    // $keyPrivado = $entityDocumento['keyprivado'];
                    // $keyPrivado = $ll;
                    // dump(md5(3197208));dump($entityDocumento['token_impreso']);dump($datos);dump($datosEnviados);dump($ll);
                    try {
                        // $datosEnviadosDecode = $this->getDecodeRSA($datosEnviados, $keyPrivado);
                        // $datosEnviadosDecode = unserialize($datosEnviadosDecode);
                        // dump($datosEnviadosDecode);die;
                        // if(
                        //     $datosEnviadosDecode['inscripcion'] == $datosRegistrados['inscripcion'] and 
                        //     $datosEnviadosDecode['tramite'] == $datosRegistrados['tramite'] and 
                        //     $datosEnviadosDecode['serie'] == $datosRegistrados['serie'] and 
                        //     $datosEnviadosDecode['codigorude'] == $datosRegistrados['codigorude'] and 
                        //     $datosEnviadosDecode['sie'] == $datosRegistrados['sie'] and 
                        //     $datosEnviadosDecode['gestionegreso'] == $datosRegistrados['gestionegreso'] and 
                        //     $datosEnviadosDecode['nombre'] == $datosRegistrados['nombre'] and 
                        //     $datosEnviadosDecode['paterno'] == $datosRegistrados['paterno'] and 
                        //     $datosEnviadosDecode['materno'] == $datosRegistrados['materno'] and 
                        //     $datosEnviadosDecode['cedulaidentidad'] == $datosRegistrados['cedulaidentidad'] and 
                        //     $datosEnviadosDecode['emisiondepartamento'] == $datosRegistrados['emisiondepartamento'] and 
                        //     $datosEnviadosDecode['tokenfirma'] == $datosRegistrados['tokenfirma']
                        //     ){
                        //     $msg = 'Documento válido';
                        //     $estado = true;
                        //     $datosDocumentoValidado = $datosRegistrados;
                        // } else {
                        //     $msg = 'Datos modificados, documento no válido';
                        //     $estado = false;
                        // }                        
                        $msg = 'Documento válido';
                        $estado = true;
                        $datosDocumentoValidado = $datosRegistrados;
                    } catch (\Exception $e) {
                        $msg = 'Documento no válido';
                        $estado = false;
                        // dump("no decodificado");die;
                    }
                }
            }
        } else {
            $msg = 'Documento inexistente, verifique con la Dirección Distrital o Dirección Departamental';
            $estado = false;
        }
        // dump($this->session->get('pathSystem'));die;


        return $this->render('SieTramitesBundle:Documento:validacion.html.twig',array(
            'listaDocumento' => $datosDocumentoValidado,   
            'estado' => $estado, 
            'msg' => $msg,
            'titulo' => 'Validación del documento',
            'subtitulo' => 'Diploma de Bachiller',
    	));
        
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera un formulario de busqueda para reactivar el documento anulado
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaBuscaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();
        $route = $request->get('_route');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(16,8,42);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Documento:reactivaIndex.html.twig', array(
            'formBusqueda' => $this->creaFormAnulaDocumentoSerie('tramite_documento_reactiva_lista','','')->createView(),
            'titulo' => 'Reactivar',
            'subtitulo' => 'Documento',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del documento requerido para su reactivación
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaListaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $serie = $form['serie'];
                $obs = $form['obs'];
                try {
                    $msgContenido = "";
                    $valNumeroSerie = $this->getNumeroSerie($serie);
                    if($valNumeroSerie != ""){
                        $msgContenido = ($msgContenido=="") ? $valNumeroSerie : $msgContenido.", ".$valNumeroSerie;
                    }
                    $valNumeroSerieActivo = $this->validaNumeroSerieActivo($serie);
                    if($valNumeroSerieActivo != ""){
                        $msgContenido = ($msgContenido=="") ? $valNumeroSerieActivo : $msgContenido.", ".$valNumeroSerieActivo;
                    }
                    $valNumeroSerieAsignado = $this->validaNumeroSerieAsignado($serie);
                    if($valNumeroSerieAsignado == ""){
                        $msgContenido = ($msgContenido=="") ? "Documento ".$serie." no asignado" : $msgContenido.", "."Documento ".$serie." no asignado";
                    }
                    $departamentoCodigo = $this->getCodigoLugarRol($id_usuario,16);
                    
                    if ($departamentoCodigo == 0 ){
                        // $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con la tuisión para reactivar el documento ".$serie : $msgContenido.", "."el usuario no cuenta con tuisión para reactivar el documento ".$serie;
                    } else {
                        // VALIDACION DE TUICION DEL CARTON
                        $valSerieTuicion = $this->validaNumeroSerieTuicion($serie, $departamentoCodigo);                
                        if($valSerieTuicion != ""){
                            $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
                        }
                    }

                    $entityDocumentoAnulado = $this->getDocumentoSerieEstado($serie,2);

                    if(count($entityDocumentoAnulado)<=0){
                        $msgContenido = ($msgContenido=="") ? "El documento con número de serie ".$serie." no esta anulado" : $msgContenido.", "."El documento con número de serie ".$serie." no esta anulado";
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                        return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                    }

                    // verifica tramite con documento
                    $tramiteId = $entityDocumentoAnulado['tramite'];
                    $documentoId = $entityDocumentoAnulado['documento'];
                    $valTramiteDocumentoActivo = $this->getDocumentoTramite($tramiteId);
                    if(count($valTramiteDocumentoActivo) > 0){
                        $msgContenido = ($msgContenido=="") ? "La emisión ".$tramiteId." del documento ".$serie." cuenta con ".count($valTramiteDocumentoActivo)." documento(s) activos(s)" : $msgContenido.", "."La emisión ".$tramiteId." del documento ".$serie." cuenta con ".count($valTramiteDocumentoActivo)." documento(s) activos(s)";
                    }
                                     
                    $tramiteProcesoController = new tramiteProcesoController();
                    $tramiteProcesoController->setContainer($this->container);

                    //$valUltimoProcesoFlujoTramite = $tramiteProcesoController->valUltimoProcesoFlujoTramite($tramiteId);

                    $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($tramiteId);

                    $entityDocumentoDetalle = $this->getDocumentoDetalle($tramiteId);

                    
                    $tramiteController = new tramiteController();
                    $tramiteController->setContainer($this->container);
                    $entityDocumento = $tramiteController->getTramite($tramiteId);
                    if(count($entityDocumento)>0){
                        $entityDocumento = $entityDocumento[0];
                    }

                    //dump($valUltimoProcesoFlujoTramite);dump($entityTramiteDetalle);dump($entityDocumentoDetalle );die;

                    if($msgContenido != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido." ; no será posible reactivar el documento solicitado"));
                    }
                    //$msgContenido = "";

                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                        'formBusqueda' => $this->creaFormReactivaDocumentoSerie('tramite_documento_reactiva_guarda',$serie,$obs,$documentoId)->createView(),
                        'titulo' => 'Reactivar',
                        'subtitulo' => 'Documento',
                        'msgReactivaDocumento' => $msgContenido,
                        'listaDocumento' => $entityDocumento,
                        'listaTramiteDetalle' => $entityTramiteDetalle,
                        'listaDocumentoDetalle' => $entityDocumentoDetalle,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la reactivacion de un documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaGuardaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $fecha = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            $serie = $form['serie'];
            $obs = $form['obs'];
            $documentoId = base64_decode($form['documento']);
            $tramiteId = 0;
            $formBusqueda = array('serie'=>$serie,'obs'=>$obs);
            if ($serie != "" and $obs != ""){

                $msgContenido = "";

                $em = $this->getDoctrine()->getManager();
                $entidadDocumento = $em->getRepository('SieAppWebBundle:Documento')->find($documentoId);
                if(count($entidadDocumento)>0){
                    $serieSend = $entidadDocumento->getDocumentoSerie()->getId();
                } else {
                    $msgContenido = ($msgContenido=="") ? "No existe el documento solicitado" : $msgContenido.", "."No existe el documento solicitado";
                }
                
                if($serie != $serieSend){
                    $msgContenido = ($msgContenido=="") ? "Inconsistencia en el envio de la serie" : $msgContenido.", "."Inconsistencia en el envio de la serie";
                } else {
                    $tramiteId = $entidadDocumento->getTramite()->getId();
                }

                if($msgContenido != ""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                    return $this->redirectToRoute('tramite_documento_reactiva_lista', ['form' => $formBusqueda], 307);
                }

                $valNumeroSerie = $this->getNumeroSerie($serie);
                if($valNumeroSerie != ""){
                    $msgContenido = ($msgContenido=="") ? $valNumeroSerie : $msgContenido.", ".$valNumeroSerie;
                }

                $valNumeroSerieActivo = $this->validaNumeroSerieActivo($serie);
                if($valNumeroSerieActivo != ""){
                    $msgContenido = ($msgContenido=="") ? $valNumeroSerieActivo : $msgContenido.", ".$valNumeroSerieActivo;
                }

                $valNumeroSerieAsignado = $this->validaNumeroSerieAsignado($serie);
                if($valNumeroSerieAsignado == ""){
                    $msgContenido = ($msgContenido=="") ? "Documento ".$serie." no asignado" : $msgContenido.", "."Documento ".$serie." no asignado";
                }
                
                $entityDocumentoAnulado = $this->getDocumentoSerieEstado($serie,2);
                if(count($entityDocumentoAnulado)<=0){
                    $msgContenido = ($msgContenido=="") ? "El documento con número de serie ".$serie." no esta anulado" : $msgContenido.", "."El documento con número de serie ".$serie." no esta anulado";
                }

                $valTramiteDocumentoActivo = $this->getDocumentoTramite($tramiteId);
                if(count($valTramiteDocumentoActivo) > 0){
                    $msgContenido = ($msgContenido=="") ? "La emisión ".$tramiteId." del documento ".$serie." cuenta con ".count($valTramiteDocumentoActivo)." documento(s) activos(s)" : $msgContenido.", "."La emisión ".$tramiteId." del documento ".$serie." cuenta con ".count($valTramiteDocumentoActivo)." documento(s) activos(s)";
                }
                
                $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->find($tramiteId);

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);
                $entityFlujoProceso = $tramiteProcesoController->getImpresionProcesoFlujo($entityTramite->getFlujoTipo()->getId());
                $entityFlujoProcesoDetalle = $em->getRepository('SieAppWebBundle:FlujoProcesoDetalle')->findOneBy(array('id' => $entityFlujoProceso->getId()));
                // $valUltimoProcesoFlujoTramite = $tramiteProcesoController->valUltimoProcesoFlujoTramite($tramiteId);

                $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
                $query = $entityTramiteDetalle->createQueryBuilder('td')
                        ->where('td.tramite = :codTramite')
                        ->orderBy('td.id','desc')
                        ->setParameter('codTramite', $tramiteId)
                        ->setMaxResults('1');
                $entityTramiteDetalle = $query->getQuery()->getResult();

                $procesosId = $entityTramiteDetalle[0]->getFlujoProceso()->getProceso()->getId();
                $tramiteEstado = $entityTramite->getEsactivo();
                
                //dump($entityTramite);dump($entityFlujoProceso);dump($entityFlujoProcesoDetalle);dump($procesosId);die;
                if($msgContenido == ""){
                    $em->getConnection()->beginTransaction();
                    try {   
                        if (($procesosId == 1 and $tramiteEstado == false) or ($procesosId != 7 and $tramiteEstado == true)) {                            
                            $obs = "DOCUMENTO REACTIVADO Y PROCESADO COMO IMPRESO (".$fecha->format('d/m/Y h:i:s')."): ".$obs; 
                            $valProcesaTramite = $tramiteProcesoController->setProcesaTramite($tramiteId,$entityFlujoProceso->getId(),$id_usuario,$obs,$em);
                            $documentoAnuladoId = $this->setDocumentoEstado($documentoId, 1, $em); 
                            //$retEstadoUltimoProcesoTramite = $tramiteProcesoController->setEstadoUltimoProcesoTramite($tramiteId,3,$id_usuario,$fecha,$em);
                            $retEstadoUltimoProcesoTramite = "";
                            $msgContenido = ($msgContenido=="") ? $retEstadoUltimoProcesoTramite : $msgContenido.", ".$retEstadoUltimoProcesoTramite;         
                            if ($procesosId == 1 and $tramiteEstado == false) {
                                $entityTramite->setEsactivo(true);
                                $em->persist($entityTramite);
                            }
                            if ($msgContenido == ""){
                                $em->flush();
                                $em->getConnection()->commit();
                                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Se reactivó el documento y fue procesado la asignación del cartón para su respectivo envío"));
                                
                                $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($tramiteId);
                                $entityDocumentoDetalle = $this->getDocumentoDetalle($tramiteId);                                
                                $tramiteController = new tramiteController();
                                $tramiteController->setContainer($this->container);
                                $entityDocumento = $tramiteController->getTramite($tramiteId);
                                if(count($entityDocumento)>0){
                                    $entityDocumento = $entityDocumento[0];
                                }
                                return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                                    'titulo' => 'Reactivar',
                                    'subtitulo' => 'Documento',
                                    'listaDocumento' => $entityDocumento,
                                    'listaTramiteDetalle' => $entityTramiteDetalle,
                                    'listaDocumentoDetalle' => $entityDocumentoDetalle,
                                ));

                                // return $this->redirectToRoute('tramite_seguimiento_documento_detalle', ['codigo' => base64_encode($tramiteId)], 307);                                
                            } 
                        }
                        // // reactivar documentos que fueron anulados por finalizar tramite (anular tramite)
                        // if ($procesosId == 1 and $tramiteEstado == false) {
                        //     $obs = "Documento reactivado y trámite finalizado (".$fecha->format('d/m/Y h:i:s')."): ".$obs;
                        //     //dump("reactiva tramite anulado");die;
                        //     $valProcesaTramite = $tramiteProcesoController->setProcesaTramiteFinaliza($tramiteId,$id_usuario,$obs,$em);
                            
                        //     $documentoAnuladoId = $this->setDocumentoEstado($documentoId, 1, $em);   

                        //     $retEstadoUltimoProcesoTramite = $tramiteProcesoController->setEstadoUltimoProcesoTramite($tramiteId,3,$id_usuario,$fecha,$em);
                        //     $msgContenido = ($msgContenido=="") ? $retEstadoUltimoProcesoTramite : $msgContenido.", ".$retEstadoUltimoProcesoTramite;         
                            
                        //     $entityTramite->setEsactivo(true);
                        //     $em->persist($entityTramite);

                        //     if ($msgContenido == ""){
                        //         $em->flush();
                        //         $em->getConnection()->commit();
                        //         $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Se reactivó el documento y el trámite se finalizó exitosamente"));
                        //         return $this->redirectToRoute('tramite_seguimiento_documento_detalle', ['codigo' => base64_encode($tramiteId)], 307);
                        //     }        
                        // }

                        // // reactivar documentos que fueron anulados por devolucion
                        // if (($procesosId != 7 and $tramiteEstado == true)) { 
                        //     $obs = "Documento reactivado y trámite procesado como impreso (".$fecha->format('d/m/Y h:i:s')."): ".$obs;         
                        //     dump("reactiva documento anulado");die;       
                        //     $valProcesaTramite = $tramiteProcesoController->setProcesaTramite($tramiteId,$entityFlujoProceso->getId(),$id_usuario,$obs,$em);

                        //     $documentoAnuladoId = $this->setDocumentoEstado($documentoId, 1, $em);  
                            
                        //     $retEstadoUltimoProcesoTramite = $tramiteProcesoController->setEstadoUltimoProcesoTramite($tramiteId,3,$id_usuario,$fecha,$em);
                        //     $msgContenido = ($msgContenido=="") ? $retEstadoUltimoProcesoTramite : $msgContenido.", ".$retEstadoUltimoProcesoTramite;         

                        //     if ($msgContenido == ""){
                        //         $em->flush();
                        //         $em->getConnection()->commit();
                        //         $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Se reactivó el documento y el trámite fue procesado como impreso para su respectivo envío"));
                        //         return $this->redirectToRoute('tramite_seguimiento_documento_detalle', ['codigo' => base64_encode($tramiteId)], 307);
                        //     }    
                        // }

                        if ($msgContenido != ""){
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                        } else {
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "No se realizo ningún proceso. intente nuevamente"));
                        } 
                        return $this->redirectToRoute('tramite_documento_reactiva_lista', ['form' => $formBusqueda], 307);                                  

                    } catch (\Doctrine\ORM\NoResultException $exc) {
                        $em->getConnection()->rollback();
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                        return $this->redirect($this->generateUrl('tramite_documento_reactiva_lista'));
                    }
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                    return $this->redirect($this->generateUrl('tramite_documento_reactiva_lista'));
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_documento_reactiva_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_documento_reactiva_lista'));
        }
    }

}
