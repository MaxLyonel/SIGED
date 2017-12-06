<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Documento;
use Doctrine\ORM\EntityRepository;

class DocumentoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Despliega formulario de busqueda para buscar el diploma
     * @return type
     */
    public function certificadoSupletorioAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        return $this->render('SieDiplomaBundle:Documento:CertificadoSupletorio.html.twig', array(
                    'form' => $this->creaFormularioCertificadoSupletorioBusca('sie_diploma_tramite_documento_certificado_supletorio_index', '', 17)->createView()
                    , 'titulo' => 'Certificado Supletorio'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Crea formulario para la busqueda de un diploma de bachiller por numero de serie
     * @return type
     */
    private function creaFormularioCertificadoSupletorioBusca($routing, $serie, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('serie', 'text', array('label' => 'SERIE', 'attr' => array('value' => $serie, 'placeholder' => 'Número y Serie diploma', 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))                    
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }


    /**
     * Crea formulario para la busqueda de un diploma de bachiller por numero de serie
     * @return type
     */
    private function creaFormularioCertificadoSupletorioGenera($routing, $serie, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('certificado', 'text', array('label' => 'Serie - Supletorio', 'attr' => array('value' => '', 'placeholder' => 'Número y Serie del cartón', 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))                    
                ->add('serie', 'hidden', array('attr' => array('value' => $serie)))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Generar Certificado Supletorio', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }


    /**
     * Despliega el listado de certificaciones realizadas segun el numero de serie seleccionado
     * @return type
     */
    public function certificadoSupletorioIndexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged    
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }   
        if ($request->isMethod('POST')) {            
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');

            if ($form) {
                $numeroSerie = $form['serie'];
                $identificador = $form['identificador']; 
                $entityDocumento = $this->buscaDiplomaBachillerPorSerie($numeroSerie);  
                if (count($entityDocumento)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Diploma de bachiller no encontrado, intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
                } 
                $codTramite = $entityDocumento[0]["tramite"];
                $entityDocumentoSupletorio = $this->buscaCertificadoSupletorioPorTramite($codTramite);   
                $infoBusqueda = serialize(array(
                    'serie' => $numeroSerie,
                    'identificador' => $identificador
                ));
                
                return $this->render('SieDiplomaBundle:Documento:CertificadoSupletorioIndex.html.twig', array(
                            'form' => $this->creaFormularioCertificadoSupletorioGenera('sie_diploma_tramite_documento_certificado_supletorio_guarda', $numeroSerie, $identificador)->createView()
                            , 'titulo' => 'Certificado Supletorio'
                            , 'subtitulo' => 'Diplomas de Bachiller'
                            , 'infoBusqueda' => $infoBusqueda
                            , 'entityDocumento' => $entityDocumento
                            , 'entityDocumentoSupletorio' => $entityDocumentoSupletorio
                ));      
            }  else {  
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
        }   
    }

    /**
     * Busca certificaciones realizadas por numero de serie
     * @return type
     */
    private function buscaCertificadoSupletorioPorTramite($tramite) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("d.id as id, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, de.documentoEstado as estado, de.id as estadoid")
                ->innerJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo') 
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->where('de.id = :codDocumentoEstado')
                ->andWhere('dt.id = :codDocumentoTipo')
                ->andWhere('t.id = :codTramite')
                ->setParameter('codDocumentoEstado', 1)
                ->setParameter('codDocumentoTipo', 9)
                ->setParameter('codTramite', $tramite);
        $entityDocumento = $query->getQuery()->getResult();
        return $entityDocumento;
    }

    /**
     * Busca diploma de bachiller por numero de serie
     * @return type
     */
    private function buscaDiplomaBachillerPorSerie($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento")
                ->innerJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')         
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')       
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = iec.gestionTipo')   
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo') 
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo') 
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')   
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('de.id = :codDocumentoEstado')
                ->andWhere('dt.id = :codDocumentoTipo')
                ->andWhere('ds.id = :codDocumentoSerie')
                ->setParameter('codDocumentoEstado', 1)
                ->setParameter('codDocumentoTipo', 1)
                ->setParameter('codDocumentoSerie', $serie);
        $entityDocumento = $query->getQuery()->getResult();
        return $entityDocumento;
    }

    /**
     * Registra el certificado supletorio
     * @return type
     */
    public function certificadoSupletorioGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        $guarda = 1;
        $mensaje = "";
        //validation if the user is logged        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }   
        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');
            $infoBusqueda = $request->get('info');
            $infoBusqueda = unserialize($infoBusqueda);
            //get the values throght the infoUe
            $numeroSerieDiploma = $infoBusqueda['serie'];
            if ($form) {
                $numeroSerieSupletorio = $form['certificado'];
                $identificador = $form['identificador']; 
                //print_r($numeroSerieDiploma.' '.$numeroSerieSupletorio.' '.$identificador);
                //die();
                $usuarioLugarId = $this->buscaCodigoLugarRol($id_usuario,$identificador);
                if (count($usuarioLugarId)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Lugar de procedencia del usuario inexistente, intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
                } 
                
                $entityDocumento = $this->buscaDiplomaBachillerPorSerie($numeroSerieDiploma);  
                if (count($entityDocumento)<1) {  
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Diploma de bachiller no encontrado, intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
                } 
                $codTramite = $entityDocumento[0]["tramite"];
                
                $infoBusqueda = serialize(array(
                    'serie' => $numeroSerieDiploma,
                    'identificador' => $identificador
                ));

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {

                    $entityNumeroSerie = $this->buscaNumeroSerie($numeroSerieSupletorio);                    
                    $entityNumeroSerieActivo = $this->validaNumeroSerieActivo($numeroSerieSupletorio);                    
                    $entityNumeroSerieTuicion = $this->validaNumeroSerieTuicion($numeroSerieSupletorio,$usuarioLugarId);
                    $entityCertificadoSupletorioAsignadoPorSerie = $this->buscaCertificadoSupletorioAsignadoPorSerie($numeroSerieSupletorio);
                    $idDocumento = $this->generaDocumento($codTramite, $id_usuario, 9, $numeroSerieSupletorio, '', $entityNumeroSerie->getGestion()->getId(), $fechaActual);
                    
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El certificado supletorio con numero de serie "'.$numeroSerieSupletorio.'" fue generado'));
                    $em->getConnection()->commit();     
                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $ex->getMessage()));                    
                }  

                return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio_index', [
                    'request' => $request, 'form' => $this->creaFormularioCertificadoSupletorioGenera('sie_diploma_tramite_documento_certificado_supletorio_guarda', $numeroSerieDiploma, $identificador)
                ], 307);

                $entityDocumentoSupletorio = $this->buscaCertificadoSupletorioPorTramite($codTramite); 

                return $this->render('SieDiplomaBundle:Documento:CertificadoSupletorioIndex.html.twig', array(
                            'form' => $this->creaFormularioCertificadoSupletorioGenera('sie_diploma_tramite_documento_certificado_supletorio_guarda', $numeroSerieDiploma, $identificador)->createView()
                            , 'titulo' => 'Certificado Supletorio'
                            , 'subtitulo' => 'Diplomas de Bachiller'
                            , 'infoBusqueda' => $infoBusqueda
                            , 'entityDocumento' => $entityDocumento
                            , 'entityDocumentoSupletorio' => $entityDocumentoSupletorio
                )); 
            }  else {                
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');
        }   
    }

    /**
     * Busca existencia del numero de serie en estado activo
     * @return type
     */
    private function buscaNumeroSerie($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie));

        if(count($entityDocumentoSerie)>0){
            return $entityDocumentoSerie;
        } else {
            throw new Exception("El número de serie ".$serie." no existe.");
        }
    }

    /**
     * Valida existencia del numero de serie en estado activo
     * @return type
     */
    private function validaNumeroSerieActivo($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'esanulado' => 'f'));

        if(count($entityDocumentoSerie)>0){
            return $entityDocumentoSerie;
        } else {
            throw new Exception("El número de serie ".$serie." se encuentra anulado.");
        }
    }



    /**
     * Valida tuicion del numero de serie en estado activo
     * @return type
     */
    private function validaNumeroSerieTuicion($serie,$departamento) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDepartamentoTipo = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamento));
        $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie));

        if($entityDocumentoSerie->getDepartamentoTipo()->getId() == $entityDepartamentoTipo->getId()){
            return $entityDocumentoSerie;
        } else {
            throw new Exception("El número de serie ".$serie." no se encuentra en tuición del departamento de ".$entityDepartamentoTipo->getDepartamento());
        }
    }

    /**
     * Busca codigo lugar del usuario
     * @return type
     */
    private function buscaCodigoLugarRol($usuarioId,$rolId) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getManager();
        $entityUsuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol');
        if($rolId == 15 or $rolId == 16 or $rolId == 17){
            $query = $entityUsuarioRol->createQueryBuilder('ur')
                    ->select('u.id as codusuario, rt.id as codrol, dt.departamento as lugar, dt.id as codlugar')
                    ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'u.id = ur.usuario')
                    ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'rt.id = ur.rolTipo')  
                    ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dt', 'WITH', 'dt.id = ur.lugarTipo')                 
                    ->where('u.id = :codUsuario')
                    ->andWhere('rt.id = :codRol')
                    ->setParameter('codUsuario', $usuarioId)
                    ->setParameter('codRol', $rolId);
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

    /**
     * Busca certificado supletorio de bachiller por numero de serie
     * @return type
     */
    private function buscaCertificadoSupletorioAsignadoPorSerie($serie) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityDocumento->createQueryBuilder('d')
                ->select("t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento")
                ->innerJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->innerJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->innerJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 't.id = d.tramite')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')         
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')       
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = iec.gestionTipo')   
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo') 
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo') 
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')   
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->where('de.id = :codDocumentoEstado')
                //->andWhere('dt.id = :codDocumentoTipo')
                ->andWhere('ds.id = :codDocumentoSerie')
                ->setParameter('codDocumentoEstado', 1)
                //->setParameter('codDocumentoTipo', 9)
                ->setParameter('codDocumentoSerie', $serie);
        $entityDocumento = $query->getQuery()->getResult();

        if(count($entityDocumento)>0){
            throw new Exception("El cartón con número de serie ".$serie." ya fue asignado, al estudiante con codigo rude ".$entityDocumento[0]['rude']);
        } else {
            return $entityDocumento;
        }
    }


    /**
     * Valida estado del documento por id md5
     * @return type
     */
    private function validaDocumentoEstadoPorIdMd5($id) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select d.id as documento_id, d.tramite_id as tramite_id, cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) as numero_serie, right(d.documento_serie_id,2) as tipo_serie
            , mt.id as estadomatricula_id, mt.estadomatricula as estadomatricula
            , case mt.id when 5 then true when 26 then true when 37 then true when 55 then true when 58 then true else false end as promovido
            , case d.documento_estado_id when 1 then true else false end as documento_estado
            , e.codigo_rude as rude, ds.departamento_tipo_id as departamento_id, ds.gestion_id as gestion, ds.id as serie
            from documento as d 
            inner join documento_serie as ds on ds.id = d.documento_serie_id 
            inner join tramite as t on t.id = d.tramite_id
            inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
            where d.documento_tipo_id = 9 and d.documento_estado_id = 1 and md5(cast(d.id as varchar)) = '".$id."'
        ");
        $query->execute();
        $entityDocumento = $query->fetchAll();

        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            throw new Exception("El documento se encuentra anulado, intente con un documento habilitado");
        }
    }


    /**
     * Valida estado del documento por id md5
     * @return type
     */
    private function validaDiplomaHumanidadesEstadoPorIdMd5($id) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select d.id as documento_id, d.tramite_id as tramite_id, cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) as numero_serie, right(d.documento_serie_id,2) as tipo_serie
            , mt.id as estadomatricula_id, mt.estadomatricula as estadomatricula
            , case mt.id when 5 then true when 26 then true when 37 then true when 55 then true when 58 then true else false end as promovido
            , case d.documento_estado_id when 1 then true else false end as documento_estado
            , e.codigo_rude as rude, ds.departamento_tipo_id as departamento_id, ds.gestion_id as gestion, ds.id as serie
            from documento as d 
            inner join documento_serie as ds on ds.id = d.documento_serie_id 
            inner join tramite as t on t.id = d.tramite_id
            inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
            where d.documento_tipo_id = 1 and d.documento_estado_id = 1 and md5(cast(d.id as varchar)) = '".$id."'
        ");
        $query->execute();
        $entityDocumento = $query->fetchAll();

        if(count($entityDocumento)>0){
            return $entityDocumento;
        } else {
            throw new Exception("El documento se encuentra anulado, intente con un documento habilitado");
        }
    }

    private function generaDocumento($tramiteId, $usuarioId, $documentoTipo, $numeroSerie, $tipoSerie, $gestion, $fecha) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$fechaManual = new \DateTime($fecha);
        $em = $this->getDoctrine()->getManager();

        /*
         * Halla datos del tramite en el cual se trabaja
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $entityTramite->createQueryBuilder('t')
                ->leftJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'jg.id = ie.leJuridicciongeografica')
                ->leftJoin('SieAppWebBundle:DistritoTipo', 'dt', 'WITH', 'dt.id = jg.distritoTipo')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'det', 'WITH', 'det.id = dt.departamentoTipo')
                ->where('t.id = :codTramite')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramite = $query->getQuery()->getResult();

        if (count($entityTramite) > 0) {
            /*
             * Define el conjunto de valores a ingresar - Documento
             */
            $serie = $numeroSerie.$tipoSerie;
            $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneBy(array('id' => $documentoTipo));
            if ($gestion > 2014) {
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => str_pad($numeroSerie.$tipoSerie, 7, "0", STR_PAD_LEFT)));
            } else {
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $numeroSerie.$tipoSerie));
            }            
            $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 1));
            $entityDocumento = new Documento();
            $entityDocumento->setDocumento('');
            $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
            $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
            $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
            $entityDocumento->setUsuarioId($usuarioId);
            $entityDocumento->setFechaImpresion($fecha);
            $entityDocumento->setFechaRegistro($fechaActual);
            $entityDocumento->setTramite($entityTramite[0]);
            $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
            $em->persist($entityDocumento);
            $em->flush();   
            return $entityDocumento->getId();
        } else {
            throw new Exception("El documento no fue registrado, intente nuevamente");
        }
    }

    /**
     * Despliega archivo pdf del certificado supletorio para su respectiva impresion
     * @return type
     */
    public function certificadoSupletorioPdfAction(Request $request) {
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
        
        $infoBusqueda = $request->get('info');
        $infoBusqueda = unserialize($infoBusqueda);
        $numeroSerieDiploma = $infoBusqueda['serie'];
        $identificador = $infoBusqueda['identificador'];

        $codigoDocumento = base64_decode($request->get('supletorio'));
        $token = $request->get('_token');

        $em = $this->getDoctrine()->getManager();
        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('id' => $codigoDocumento));

        if (!$this->isCsrfTokenValid($codigoDocumento, $token)) {
            return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio_index', [
                'request' => $request, 'form' => $this->creaFormularioCertificadoSupletorioGenera('sie_diploma_tramite_documento_certificado_supletorio_guarda', $numeroSerieDiploma, $identificador)
            ], 307);
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

    /**
     * Despliega archivo pdf del certificado supletorio para su reimpresion por codigo qr
     * @return type
     */
    public function certificadoSupletorioQrAction($supletorio, Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            $this->session->getFlashBag()->add('info', 'Debe acceder al sistema para hacer uso del módulo "verificacion de diplomas por Código QR"');
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $entityDocumento = $this->validaDocumentoEstadoPorIdMd5($supletorio);
            $codigoDocumento = $entityDocumento[0]["documento_id"];
            $codigoDocumentoSerie = $entityDocumento[0]["serie"];
            $gestionIdDocumentoSerie = $entityDocumento[0]["gestion"];
            $departamentoIdDocumentoSerie = $entityDocumento[0]["departamento_id"];
            $lk = "http://diplomas.sie.gob.bo/diplomas/documento/certificado/supletorio/qr/".$supletorio;

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
      
            $arch = 'CertificadoSupletorio_'.$codigoDocumentoSerie.'_'.date('YmdHis').'.pdf';            
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($fileContent));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;    
        } catch (Exception $ex) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $ex->getMessage()));     
            return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');               
        }          
    } 

    /**
     * Despliega archivo pdf del diploma de bachiller humanistico para su reimpresion por codigo qr
     * @return type
     */
    public function bachillerHumanidadesQrAction($documento, Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            $this->session->getFlashBag()->add('info', 'Debe acceder al sistema para hacer uso del módulo "verificacion de diplomas por Código QR"');
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $entityDocumento = $this->validaDiplomaHumanidadesEstadoPorIdMd5($documento);
            $codigoDocumento = $entityDocumento[0]["documento_id"];
            $codigoDocumentoSerie = $entityDocumento[0]["serie"];
            $gestionIdDocumentoSerie = $entityDocumento[0]["gestion"];
            $departamentoIdDocumentoSerie = $entityDocumento[0]["departamento_id"];
            $lk = "http://diplomas.sie.gob.bo/diplomas/documento/bachiller/humanidades/qr/".$documento;

            $fileContent = ""; 

            $form = ['texto'=>$codigoDocumentoSerie, 'identificador'=>17];
            return $this->redirectToRoute('sie_diploma_tramite_legalizacion_save', array('form'=>$form), 307);
                   
        } catch (Exception $ex) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $ex->getMessage()));     
            return $this->redirectToRoute('sie_diploma_tramite_documento_certificado_supletorio');               
        }          
    } 
}