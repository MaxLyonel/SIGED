<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\AltModuloemergente;
use Sie\AppWebBundle\Entity\SuperiorModuloTipo;
use Sie\AppWebBundle\Entity\SuperiorMallaModuloPeriodo;
use Sie\AppWebBundle\Entity\Mensaje;
use Symfony\Component\Validator\Constraints\Length;

/**
 * EstudianteInscripcion controller.
 *
 */
class AreasController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {

        //die; 
        // ----- TEMP V1.
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the send values
        $infoUe = $request->get('infoUe');
        $arrInfoUe = unserialize($infoUe);
        
        $idCurso = $arrInfoUe['ueducativaInfoId']['iecId'];
        // dump($idCurso);die;
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($idCurso);
        $mallaActual = $curso->getModalidadTipoId();
        //dump($curso); die;
        // check if the course is PRIMARIA
        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe) ){
            $primaria = true;
            //set the All data about curricula on the course
            //$createNewCurricula = $this->get('funciones')->loadCurriculaCurso($infoUe);
            // $templateToView = 'indexprimaria.html.twig';
        }else{
            $primaria = false;
            // $templateToView = 'index.html.twig';
        }
        $templateToView = 'index.html.twig';

        // $modules = $this->getAreasCajon( $arrInfoUe );
        // dump($modules);die;

        $data = $this->getAreas($infoUe);

        $data['primaria'] = $primaria;
        $data['mallaActual'] = $mallaActual;
        
        $data['superiorAcreditacionTipoId'] = $arrInfoUe['ueducativaInfo']['superiorAcreditacionTipoId'];
        // $data['arrInfoUe'] = $arrInfoUe; 
        // dump("in modal");
        // unset($this->session['arrInfoUe']);

        // dump($data['asignaturas']);die;

        // $this->session->set('arrInfoUe', $arrInfoUe);
        // $this->session->set('arrInfoUe', $arrInfoUe);
        // dump($this->session->get('arrInfoUe'));die;        
        return $this->render('SieHerramientaAlternativaBundle:Areas:'.$templateToView, $data);
        
    }

    public function getAreasCajon( $dataUE ){



        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $nivelId = $dataUE['ueducativaInfo']['superiorAcreditacionTipoId']; 
        // dump($this->session->get('ie_subcea'));
        // dump($dataUE['ueducativaInfoId']['iecId']);
        // dump($this->session->get('ie_id'));
        // dump($dataUE['ueducativaInfo']['superiorAcreditacionTipoId']);die;
        // dump($dataUE['ueducativaInfoId']['setId']);
        // dump($dataUE['ueducativaInfoId']['periodoId']);
        // dump($dataUE);
        // die;
        $querySearchSIP = $db->prepare("select ic.superior_institucioneducativa_periodo_id as sipid from institucioneducativa_curso ic where ic.id=".$dataUE['ueducativaInfoId']['iecId']."");
        $querySearchSIP->execute();
        $resultSearchSIP = $querySearchSIP->fetch();

        $querySecond = $db->prepare("select * from institucioneducativa_curso ic 
                                        inner join superior_institucioneducativa_periodo sip on sip.id=ic.superior_institucioneducativa_periodo_id 
                                        inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
                                        inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id
                                        where ic.superior_institucioneducativa_periodo_id=".$resultSearchSIP['sipid']."
                                        ");
        $querySecond->execute();
        $resultSecond = $querySecond->fetchAll();

        $queryCheckOficial = $db->prepare("select set2.id, set2.especialidad, set2.es_oficial from superior_institucioneducativa_periodo sip 
                                            inner join superior_institucioneducativa_acreditacion sia on sip.superior_institucioneducativa_acreditacion_id=sia.id 
                                            inner join superior_acreditacion_especialidad sae on sia.acreditacion_especialidad_id=sae.id
                                            inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id
                                            where sip.id=".$resultSearchSIP['sipid']."
                                            ");
        $queryCheckOficial->execute();
        $resultOficial = $queryCheckOficial->fetch();

        // if( count($resultSecond) == 0 && $resultOficial['es_oficial'] ){

        /*
        if( $resultOficial['es_oficial'] ){
                // EXTRAER MODULOS DE LA TABLA ctr_altenativa_planes
            $queryThird = $db->prepare("select * from ctr_altenativa_planes cap 
                                            where cap.superior_acreditacion_tipo=".$dataUE['ueducativaInfo']['superiorAcreditacionTipoId']."
                                            and cap.superior_especialidad_tipo_id=".$dataUE['ueducativaInfoId']['setId']."
                                            order by cap.id asc");
            $queryThird->execute();
            $resultThird = $queryThird->fetchAll();

            $contModulesTM = 0;
            foreach ($resultThird as $value) {

                $modulo = $value['modulo'];

                $querySeven = $db->prepare("select * from superior_modulo_tipo smt where 
                                                smt.superior_especialidad_tipo_id=".$dataUE['ueducativaInfoId']['setId']." 
                                                and smt.modulo like '".$modulo."'");
                $querySeven->execute();
                $superiorModuloTipo = $querySeven->fetch();

                if(!$superiorModuloTipo){

                    $smtipo = new SuperiorModuloTipo();
                    $smtipo -> setModulo($modulo);
                    $smtipo -> setEsvigente(true);
                    $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                    $smtipo -> setSuperiorEspecialidadTipo($em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($dataUE['ueducativaInfoId']['setId']));

                    $em->persist($smtipo);
                    $em->flush();

                    $smtipo = $smtipo->getId();
                }else{
                    $smtipo = $superiorModuloTipo['id'];
                }

                $queryFour = $db->prepare("select * from superior_modulo_periodo smp2 where 
                                                    smp2.superior_modulo_tipo_id=".$smtipo."
                                                    and smp2.institucioneducativa_periodo_id=".$resultSearchSIP['sipid']."");
                $queryFour->execute();
                $superiorModuloPeriodo = $queryFour->fetch();

                if( !$superiorModuloPeriodo ){

                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smperiodo = new SuperiorModuloPeriodo();
                    $smperiodo ->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($smtipo));
                    $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($resultSearchSIP['sipid']));
                    $smperiodo ->setHorasModulo('100');
                    //$em->persist($smperiodo);
                    //$em->flush($smperiodo);

                    $smperiodo = $smperiodo->getId();
                }else{
                    $smperiodo = $superiorModuloPeriodo['id'];
                }

                $queryLast = $db->prepare("select * from superior_malla_modulo_periodo smmp where smmp.superior_modulo_periodo_id=".$smperiodo."");
                $queryLast->execute();
                $superiorMallaModuloPeriodo = $queryLast->fetch();

                $superiorPeriodoTipoId = 0;
                switch ($nivelId) {
                    case 1:
                        $superiorPeriodoTipoId = 2;       
                        break;
                    case 20:
                        $superiorPeriodoTipoId = 3;
                        break;
                    case 32:
                        $contModulesTM = $contModulesTM + 1;
                        $superiorPeriodoTipoId = ( $contModulesTM <= 5 ) ? 4 : 5;
                        break;
                    default:
                        $superiorPeriodoTipoId = 0;
                        break;
                }

                if( !$superiorMallaModuloPeriodo ){
                    $superiorPeriodoTipo = $em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find( $superiorPeriodoTipoId );
                    $smmp = new SuperiorMallaModuloPeriodo();
                    $smmp->setSuperiorPeriodoTipo( $superiorPeriodoTipo );
                    $smmp->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smperiodo));
                    $smmp->setFechaRegistro(new \DateTime('now'));
                    $smmp->setFechaModificacion(new \DateTime('now'));
                    //$em->persist($smmp);
                    //$em->flush($smmp);
                }

            } 

        }
            */

        if( $resultOficial['es_oficial'] ){
            // dump($dataUE['ueducativaInfo']['superiorAcreditacionTipoId']);
            // dump($dataUE['ueducativaInfoId']['setId']);
            // dump($dataUE['ueducativaInfoId']['periodoId']);
            // dump($resultSearchSIP['sipid']);
            // dump($this->session->get('ie_subcea'));
            // die;
            $query = $db->prepare("select smt.id as smtid, smp.id as smpid, sia.id as siaid, smt.modulo, smt.codigo, smt.esvigente, sip.id as sipid, smmp.superior_periodo_tipo_id as superiorPeriodoTipoId, set2.id, set2.es_oficial 
                                from superior_acreditacion_especialidad sae 
                                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
                                inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id 
                                inner join superior_institucioneducativa_acreditacion sia on sae.id=sia.acreditacion_especialidad_id 
                                inner join institucioneducativa_sucursal is2 on sia.institucioneducativa_sucursal_id=is2.id 
                                inner join superior_institucioneducativa_periodo sip on sia.id=sip.superior_institucioneducativa_acreditacion_id 
                                inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
                                inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id 
                                left join superior_malla_modulo_periodo smmp on smmp.superior_modulo_periodo_id=smp.id
                                where
                                is2.gestion_tipo_id=".$this->session->get('ie_gestion')."
                                and is2.institucioneducativa_id=".$this->session->get('ie_id')."
                                and sat.id=".$dataUE['ueducativaInfo']['superiorAcreditacionTipoId']."
                                and set2.id=".$dataUE['ueducativaInfoId']['setId']."
                                and is2.periodo_tipo_id=".$dataUE['ueducativaInfoId']['periodoId']."
                                and sip.id=".$resultSearchSIP['sipid']."
                                and is2.sucursal_tipo_id=".$this->session->get('ie_subcea')."
                                and smt.esvigente=true
                                and set2.es_vigente=true
                                order by smt.id asc
                                ");

        }else{

            $query = $db->prepare("select smt.id as smtid, smp.id as smpid, sia.id as siaid, smt.modulo, smt.codigo, smt.esvigente, sip.id as sipid, smmp.superior_periodo_tipo_id as superiorPeriodoTipoId, set2.id, set2.es_oficial 
                                from superior_acreditacion_especialidad sae 
                                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
                                inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id 
                                inner join superior_institucioneducativa_acreditacion sia on sae.id=sia.acreditacion_especialidad_id 
                                inner join institucioneducativa_sucursal is2 on sia.institucioneducativa_sucursal_id=is2.id 
                                inner join superior_institucioneducativa_periodo sip on sia.id=sip.superior_institucioneducativa_acreditacion_id 
                                inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
                                inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id 
                                left join superior_malla_modulo_periodo smmp on smmp.superior_modulo_periodo_id=smp.id
                                where
                                is2.gestion_tipo_id=".$this->session->get('ie_gestion')."
                                and is2.institucioneducativa_id=".$this->session->get('ie_id')."
                                and sat.id=".$dataUE['ueducativaInfo']['superiorAcreditacionTipoId']."
                                and set2.id=".$dataUE['ueducativaInfoId']['setId']."
                                and is2.periodo_tipo_id=".$dataUE['ueducativaInfoId']['periodoId']."
                                and is2.sucursal_tipo_id=".$this->session->get('ie_subcea')."
                                and smt.esvigente=true
                                and set2.es_vigente=true
                                order by smt.id asc
                                ");
                                
        }

        $query->execute();
        $modules = $query->fetchAll();
        // dump($modules);die;
        return $modules;

    }

    public function areasaddAction(Request $request) {

        $infoUe = $request->get('infoUe');
        $idAsignatura = $request->get('ida');

        $gestion = $this->session->get('ie_gestion');

        $aInfoUeducativa = unserialize($infoUe);

        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
    
        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe)
          ){
            $primaria = true;
        }else{
            $primaria = false;
        }
        try {
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            
            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($idAsignatura);
            $codigo = $smp->getSuperiorModuloTipo()->getCodigo();
            // dump($codigo);die;
            if ($codigo != '415'){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                
                $ieco = new InstitucioneducativaCursoOferta();
                $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(3));
                $ieco->setInsitucioneducativaCurso($curso);
                $ieco->setSuperiorModuloPeriodo($smp);
                $ieco->setHorasmes(0);
                $em->persist($ieco);
                $em->flush();
                $em->getConnection()->commit();

            }
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            // dump($data);die;
            $data['primaria'] = $primaria;
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function addAreasAllAction(Request $request) {
        try {

            $infoUe = $request->get('infoUe');
            $asignaturas = unserialize($request->get('asignaturas'));
            // $idAsignatura = $request->get('ida');
    
            // $gestion = $this->session->get('ie_gestion');
            $aInfoUeducativa = unserialize($infoUe);

            $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            $em->getConnection()->beginTransaction();
        
            if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe) ){
                $primaria = true;
            }else{
                $primaria = false;
            }
    
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
    
            // $asignaturas = $this->getAreas($infoUe);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            //  VERIFICAR SI CUMPLE CON EL SIP DE LA MALLA
            $queryCurso = $db->prepare("select * from institucioneducativa_curso ic where ic.id=".$idCurso."");
            $queryCurso->execute();
            $resultCurso = $queryCurso->fetch();

            if( count($asignaturas) > 0 ){

                if( !isset($asignaturas[0]['sipid']) ){
    
                    $queryFindSIP = $db->prepare("select smp.id as smpid, sip.id as sipid from superior_modulo_periodo smp 
                                                    inner join superior_institucioneducativa_periodo sip on sip.id=smp.institucioneducativa_periodo_id 
                                                    where smp.id=".$asignaturas[0]['smpid']."");
                    $queryFindSIP->execute();
                    $resultSIP = $queryFindSIP->fetch();
    
                    $sipId = $resultSIP['sipid'];
                }else{
                    $sipId = $asignaturas[0]['sipid'];
                }
    
                if( $sipId !== $resultCurso['superior_institucioneducativa_periodo_id'] ){
    
                    $queryFindIds = $db->prepare("select sia.id  as siaid, sip.id as sipid, sae.id as saeid from institucioneducativa_curso ic 
                                                    inner join superior_institucioneducativa_periodo sip on sip.id=ic.superior_institucioneducativa_periodo_id 
                                                    inner join superior_institucioneducativa_acreditacion sia on sia.id=sip.superior_institucioneducativa_acreditacion_id 
                                                    inner join superior_acreditacion_especialidad sae on sae.id=sia.acreditacion_especialidad_id 
                                                    where 
                                                    ic.id=".$idCurso."
                                                    and ic.gestion_tipo_id=".$this->session->get('ie_gestion')."
                                                    and sip.id=".$resultCurso['superior_institucioneducativa_periodo_id']."");
                    $queryFindIds->execute();
                    $resultFound = $queryFindIds->fetch();
                    // dump($resultFound);die;
                    $updateCurso = $db->prepare("update institucioneducativa_curso set superior_institucioneducativa_periodo_id=".$sipId." where id=".$idCurso."");
                    $updateCurso->execute();
    
                    // $deleteSIP = $db->prepare("delete from superior_institucioneducativa_periodo where id=".$resultFound['sipid']."");
                    // $deleteSIP->execute();
    
                    // $deleteSIA = $db->prepare("delete from superior_institucioneducativa_acreditacion where id=".$resultFound['siaid']."");
                    // $deleteSIA->execute();
    
                    // $deleteSAE = $db->prepare("delete from superior_acreditacion_especialidad where id=".$resultFound['saeid']."");
                    // $deleteSAE->execute();
                    
                }
                
                // foreach ($asignaturas['asignaturas'] as $value) {
                foreach ($asignaturas as $value) {
                    
                    if ($value['codigo'] != '415'){    
    
                        // VERIFICAR SI ESTA REGISTRADO EN InstitucioneducativaCursoOferta
                        $queryCheckOferta = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                        where 
                                                        ico.superior_modulo_periodo_id=".$value['smpid']."
                                                        and ico.insitucioneducativa_curso_id=".$idCurso."");
                        $queryCheckOferta->execute();
                        $resultCheck = $queryCheckOferta->fetch(); 
    
                        if(!$resultCheck){
                            $ieco = new InstitucioneducativaCursoOferta();
                            $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(3));
                            $ieco->setInsitucioneducativaCurso($curso);
                            $ieco->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($value['smpid']));
                            $ieco->setHorasmes(0);
                            $em->persist($ieco);
                            $em->flush();
                        }
    
                    }
                }
                
                $em->getConnection()->commit();

            }else{

                $response = new JsonResponse();
    
                return $response->setData(array(
                    'statusCode' => 401,
                    'message'    => "Ya se agregaron todos lo modulos"
                ));

            }

            // Mostramos nuevamente las areas del curso
            $asignaturas = $this->getAreas($infoUe);
    
            $asignaturas['superiorAcreditacionTipoId'] = unserialize($infoUe)['ueducativaInfo']['superiorAcreditacionTipoId'];
            $asignaturas['primaria'] = $primaria;
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $asignaturas);
            
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function checkIsMedioAction( Request $request ){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $asignaturas = unserialize($request->get('asignaturas'));
        
        $cont = 0;
        foreach ($asignaturas as $value) {

            $query = $db->prepare("select * from superior_malla_modulo_periodo smmp 
                                    inner join superior_modulo_periodo smp on smmp.superior_modulo_periodo_id=smp.id
                                    where smp.id=".$value['smpid']."");
            $query->execute();
            $result = $query->fetch();

            if( $result ) $cont = $cont + 1;

        }

        $response = new JsonResponse();

        return $response->setData(array(
            'statusCode' => 200,
            'data'    => $cont == 10 ? true : false,
        )); 
        
    }

    public function addMedioAction( Request $request ){

        $unidadS = $request->get('infoUe');
        $unidad = unserialize($unidadS);
        $asignaturas = unserialize($request->get('asignaturas'));

        $instucioneducativaCursoId = $unidad['ueducativaInfoId']['iecId'];
        $typeMedio = ($request->get('type') == 1) ? 4 : 5;

        $esOficial = $asignaturas[0]['es_oficial'];

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $response = new JsonResponse();

        // VERIFICAR SI ES UNA CARRERA UNIFICADA
        if( $esOficial ){
            // VERIFICAR SI TIENE REGISTROS DEL MEDIO 1 Y MEDIO 2
            $queryFirst = $db->prepare("select smt.id as smtid, smp.id as smpid, sia.id as siaid, smt.modulo, smt.codigo, smt.esvigente, sip.id as sipid, smmp.superior_periodo_tipo_id as superiorPeriodoTipoId, set2.id, set2.es_oficial, 
                                            smmp.superior_periodo_tipo_id 
                                            from superior_acreditacion_especialidad sae 
                                            inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
                                            inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id=set2.id 
                                            inner join superior_institucioneducativa_acreditacion sia on sae.id=sia.acreditacion_especialidad_id 
                                            inner join institucioneducativa_sucursal is2 on sia.institucioneducativa_sucursal_id=is2.id 
                                            inner join superior_institucioneducativa_periodo sip on sia.id=sip.superior_institucioneducativa_acreditacion_id
                                            inner join superior_modulo_periodo smp on sip.id=smp.institucioneducativa_periodo_id 
                                            inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id=smt.id 
                                            inner join superior_malla_modulo_periodo smmp on smmp.superior_modulo_periodo_id=smp.id
                                            where
                                            is2.gestion_tipo_id=".$this->session->get('ie_gestion')."
                                            and is2.institucioneducativa_id=".$this->session->get('ie_id')."
                                            and sat.id=".$unidad['ueducativaInfo']['superiorAcreditacionTipoId']."
                                            and set2.id=".$unidad['ueducativaInfoId']['setId']."
                                            and is2.periodo_tipo_id=".$unidad['ueducativaInfoId']['periodoId']."
                                            and is2.sucursal_tipo_id=".$this->session->get('ie_subcea')."
                                            and smmp.superior_periodo_tipo_id=".$typeMedio."
                                            and smt.esvigente=true
                                            and set2.es_vigente=true
                                        ");
            $queryFirst->execute();
            $resultFirst = $queryFirst->fetchAll();
    
            if( count($resultFirst) == 0 ){
        
                return $response->setData(array(
                    'statusCode' => 401,
                    'message'    => "No puede agregar modulos, primero tiene que agregar el Medio 1 y Medio 2 de la malla"
                ));
            }

            // VERIFICAR MODULOS REGISTRADOS EN OFERTA
            $querySeven = $db->prepare("select ico.id from institucioneducativa_curso ic 
                                            inner join institucioneducativa_curso_oferta ico ON ic.id=ico.insitucioneducativa_curso_id 
                                            where ic.id=".$instucioneducativaCursoId."");
            $querySeven->execute();
            $resultSeven = $querySeven->fetchAll();
            // if( count($resultSeven) > 5 ){
                // VERIFICAR SI TIENE ESTUDIANTE ASIGNATURA O NOTAS
                foreach ($resultSeven as $value) {
                    // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_asignatura
                    $queryAsignatura = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                        inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                                                        where ico.id=".$value['id']."");
                    $queryAsignatura->execute();
                    $resultAsignatura = $queryAsignatura->fetchAll();
                    if( count($resultAsignatura) > 0 ){
        
                        $response = new JsonResponse();
            
                        return $response->setData(array(
                            'statusCode' => 401,
                            'message'    => "Uno o mas modulos no fueron actualizados, verifique que ningun modulo este asignado al participante o se encuentre con registro de notas"
                        ));
        
                    }
        
                    // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_nota
                    $queryNota = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                                                inner join estudiante_nota en on en.estudiante_asignatura_id=ea.id
                                                where ico.id=".$value['id']."");
                    $queryNota->execute();
                    $resultNota = $queryNota->fetchAll();
                    if( count($resultNota) > 0 ){
        
                        $response = new JsonResponse();
            
                        return $response->setData(array(
                            'statusCode' => 401,
                            'message'    => "Uno o mas modulos no fueron actualizados, verifique que ningun modulo este asignado al participante o se encuentre con registro de notas"
                        ));
        
                    }
    
                }
    
                //  ELIMINAR DESPUES DE VERIFICAR
                foreach ($resultSeven as $value) {
                
                    $ico = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBy(array('id' => $value['id']));
                    if( $ico ){
        
                        $institucioneducativaCursoOferta = (object) [
                            'id' => $ico->getId(), 
                            'asignaturaTipo' => $ico->getAsignaturaTipo()->getId(), 
                            'horasmes' => $ico->getHorasmes(),
                            'insitucioneducativa_curso_id'=> $ico->getInsitucioneducativaCurso()->getId(),
                            'superiorModuloPeriodoId'=> $ico->getSuperiorModuloPeriodo()->getId()
                        ];
                        
                        $this->get('funciones')->setLogTransaccion(
                            $ico->getId(),
                            'InstitucioneducativaCursoOferta',
                            'D',
                            '',
                            '',
                            $institucioneducativaCursoOferta,
                            'ALTERNATIVA',
                            ''
                        );
        
                        $em->remove($ico);
        
                        $em->flush();
                        $em->clear();
                    }
    
                }
    
            // }
    
            // VERIFICAR SI TIENE MODULOS REGISTRADOS A ESTE CURSO
            $querySecond = $db->prepare("select * from institucioneducativa_curso ic 
                                            inner join superior_institucioneducativa_periodo sip on ic.superior_institucioneducativa_periodo_id=sip.id
                                            inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id=smp.id 
                                            where 
                                            ic.id=".$instucioneducativaCursoId."
                                            ");
            $querySecond->execute();
            $resultSecond = $querySecond->fetch();
    
            if( !$resultSecond ){
    
                $queryFour = $db->prepare("select * from institucioneducativa_curso ic 
                                            where ic.id=".$instucioneducativaCursoId."");
                $queryFour->execute();
                $resultFour = $queryFour->fetch();
    
                $superiorInstitucioneducativaPeriodo = $resultFour['superior_institucioneducativa_periodo_id'];
                
                foreach ($resultFirst as $value) {
    
                    $modulo = $value['modulo'];
    
                    $queryThird = $db->prepare("select * from superior_modulo_tipo smt where 
                                                    smt.superior_especialidad_tipo_id=".$unidad['ueducativaInfoId']['setId']." 
                                                    and smt.modulo like '".$modulo."'");
                    $queryThird->execute();
                    $superiorModuloTipo = $queryThird->fetch();
    
                    if(!$superiorModuloTipo){
                        $smtipo = new SuperiorModuloTipo();
                        $smtipo -> setModulo($modulo);
                        $smtipo -> setEsvigente(true);
                        $smtipo -> setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                        $smtipo -> setSuperiorEspecialidadTipo($em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($unidad['ueducativaInfoId']['setId']));
                        $em->persist($smtipo);
                        $em->flush();
    
                        $smtipo = $smtipo->getId();
                    }else{
                        $smtipo = $superiorModuloTipo['id'];
                    }
    
                    $queryFour = $db->prepare("select * from superior_modulo_periodo smp2 where 
                                                        smp2.superior_modulo_tipo_id=".$smtipo."
                                                        and smp2.institucioneducativa_periodo_id=".$superiorInstitucioneducativaPeriodo."");
                    $queryFour->execute();
                    $superiorModuloPeriodo = $queryFour->fetch();
    
                    if( !$superiorModuloPeriodo ){
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                        $smperiodo = new SuperiorModuloPeriodo();
                        $smperiodo ->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($smtipo));
                        $smperiodo ->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($superiorInstitucioneducativaPeriodo));
                        $smperiodo ->setHorasModulo('100');
                        $em->persist($smperiodo);
                        $em->flush($smperiodo);
    
                        $smperiodo = $smperiodo->getId();
                    }else{
                        $smperiodo = $superiorModuloPeriodo['id'];
                    }
    
                    // VERIFICAR SI ESTA REGISTRADO EN InstitucioneducativaCursoOferta
                    $queryFive = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                    where 
                                                    ico.superior_modulo_periodo_id=".$smperiodo."
                                                    and ico.insitucioneducativa_curso_id=".$instucioneducativaCursoId."");
                    $queryFive->execute();
                    $resultCheck = $queryFive->fetch(); 
    
                    if(!$resultCheck){
                        $ieco = new InstitucioneducativaCursoOferta();
                        $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(3));
                        $ieco->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($instucioneducativaCursoId));
                        $ieco->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($value['smpid']));
                        $ieco->setHorasmes(0);
                        $em->persist($ieco);
                        $em->flush();
                    }
        
                }
    
            }
        
        }else{

            // VERIFICAR SI ESTA NO UNIFICADA ES MEDIO 1 O MEDIO 2
            $siaId = $asignaturas[0]['siaid'];

            $queryOne = $db->prepare("select * from superior_modulo_periodo smp
                                        inner join superior_institucioneducativa_periodo sip on smp.institucioneducativa_periodo_id=sip.id 
                                        inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                        where sip.superior_institucioneducativa_acreditacion_id=".$siaId."
                                        ");
            $queryOne->execute();
            $resultOne = $queryOne->fetchAll();

            // SI NO ES MEDIO 1 Y MEDIO 2 ENTONCES ES UNA CARRERA CON REGISTRO ANTIGUO SE REGISTRA DE CAJON   
            if( $resultOne ){

                // VERIFICAMOS QUE TENGA TODOS LOS REGISTRO DE LA MALLA DE MEDIO 1 Y MEDIO 2
                $verifyCareers = count($resultOne) < 4 ? false : true; 
                if( !$verifyCareers ){
                    
                    return $response->setData(array(
                        'statusCode' => 401,
                        'message'    => "No puede agregar modulos, tiene que completar con el registro de los modulos de la malla Medio 1 y Medio 2"
                    ));
    
                }

                // ELMINAR PARA AGREGAR OTRO MEDIO
                $querySeven = $db->prepare("select ico.id from institucioneducativa_curso ic 
                                            inner join institucioneducativa_curso_oferta ico ON ic.id=ico.insitucioneducativa_curso_id 
                                            where ic.id=".$instucioneducativaCursoId."");
                $querySeven->execute();
                $resultSeven = $querySeven->fetchAll();
                
                // VERIFICAR SI TIENE ESTUDIANTE ASIGNATURA O NOTAS
                foreach ($resultSeven as $value) {
                    // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_asignatura
                    $queryAsignatura = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                        inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                                                        where ico.id=".$value['id']."");
                    $queryAsignatura->execute();
                    $resultAsignatura = $queryAsignatura->fetchAll();
                    if( count($resultAsignatura) > 0 ){
        
                        $response = new JsonResponse();
            
                        return $response->setData(array(
                            'statusCode' => 401,
                            'message'    => "Uno o mas modulos no fueron actualizados, verifique que ningun modulo este asignado al participante o se encuentre con registro de notas"
                        ));
        
                    }
        
                    // VERIFICAR SI EL MODULO TIENE RELACION CON estudiante_nota
                    $queryNota = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id=ico.id
                                                inner join estudiante_nota en on en.estudiante_asignatura_id=ea.id
                                                where ico.id=".$value['id']."");
                    $queryNota->execute();
                    $resultNota = $queryNota->fetchAll();
                    if( count($resultNota) > 0 ){
        
                        $response = new JsonResponse();
            
                        return $response->setData(array(
                            'statusCode' => 401,
                            'message'    => "Uno o mas modulos no fueron actualizados, verifique que ningun modulo este asignado al participante o se encuentre con registro de notas"
                        ));
        
                    }
    
                }
    
                //  ELIMINAR DESPUES DE VERIFICAR
                foreach ($resultSeven as $value) {
                
                    $ico = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBy(array('id' => $value['id']));
                    if( $ico ){
        
                        $institucioneducativaCursoOferta = (object) [
                            'id' => $ico->getId(), 
                            'asignaturaTipo' => $ico->getAsignaturaTipo()->getId(), 
                            'horasmes' => $ico->getHorasmes(),
                            'insitucioneducativa_curso_id'=> $ico->getInsitucioneducativaCurso()->getId(),
                            'superiorModuloPeriodoId'=> $ico->getSuperiorModuloPeriodo()->getId()
                        ];
                        
                        $this->get('funciones')->setLogTransaccion(
                            $ico->getId(),
                            'InstitucioneducativaCursoOferta',
                            'D',
                            '',
                            '',
                            $institucioneducativaCursoOferta,
                            'ALTERNATIVA',
                            ''
                        );
        
                        $em->remove($ico);
        
                        $em->flush();
                        $em->clear();
                    }
    
                }

                // PREPARAR PARA EL REGISTRO DE MEDIO 1 O MEDIO 2
                $queryTwo = $db->prepare("select * from superior_modulo_periodo smp
                                            inner join superior_institucioneducativa_periodo sip on smp.institucioneducativa_periodo_id=sip.id 
                                            inner join superior_malla_modulo_periodo smmp on smp.id=smmp.superior_modulo_periodo_id 
                                            where smmp.superior_periodo_tipo_id=".$typeMedio."
                                            and sip.superior_institucioneducativa_acreditacion_id=".$siaId."");
                $queryTwo->execute();
                $resultTwo = $queryTwo->fetchAll();

                foreach ($resultTwo as $value) {
                    
                    $smpId = $value['superior_modulo_periodo_id'];
                    //  VERIFICAR SI ESTA REGISTRADO EN InstitucioneducativaCursoOferta
                    $queryFive = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                where 
                                                ico.superior_modulo_periodo_id=".$smpId."
                                                and ico.insitucioneducativa_curso_id=".$instucioneducativaCursoId."");
                    $queryFive->execute();
                    $resultCheck = $queryFive->fetch(); 
    
                    if(!$resultCheck){
                        $ieco = new InstitucioneducativaCursoOferta();
                        $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(3));
                        $ieco->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($instucioneducativaCursoId));
                        $ieco->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpId));
                        $ieco->setHorasmes(0);
                        $em->persist($ieco);
                        $em->flush();
                    }

                }

            }else{

                $siaId = $asignaturas[0]['siaid'];

                $queryOne = $db->prepare("select smp.id from superior_modulo_periodo smp
                                            inner join superior_institucioneducativa_periodo sip on smp.institucioneducativa_periodo_id=sip.id 
                                            where sip.superior_institucioneducativa_acreditacion_id=".$siaId."
                                            ");
                $queryOne->execute();
                $resultOne = $queryOne->fetchAll();

                foreach ($resultOne as $value) {
                    
                    $smpId = $value['id'];
                    //  VERIFICAR SI ESTA REGISTRADO EN InstitucioneducativaCursoOferta
                    $queryFive = $db->prepare("select * from institucioneducativa_curso_oferta ico 
                                                where 
                                                ico.superior_modulo_periodo_id=".$smpId."
                                                and ico.insitucioneducativa_curso_id=".$instucioneducativaCursoId."");
                    $queryFive->execute();
                    $resultCheck = $queryFive->fetch(); 
    
                    if(!$resultCheck){
                        $ieco = new InstitucioneducativaCursoOferta();
                        $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(3));
                        $ieco->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($instucioneducativaCursoId));
                        $ieco->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpId));
                        $ieco->setHorasmes(0);
                        $em->persist($ieco);
                        $em->flush();
                    }

                }

            }

        }

        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$unidadS) ){
            $primaria = true;
        }else{
            $primaria = false;
        }

        // Mostramos nuevamente las areas del curso
        $asignaturas = $this->getAreas($unidadS);

        $asignaturas['superiorAcreditacionTipoId'] = $unidad['ueducativaInfo']['superiorAcreditacionTipoId'];
        $asignaturas['primaria'] = $primaria;
        return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $asignaturas);

    }

    public function areaemergenteaddAction(Request $request) {

        $infoUe = $request->get('infoUe');
        $gestion = $this->session->get('ie_gestion');

        $aInfoUeducativa = unserialize($infoUe);
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe)
          ){
            $primaria = true;
            //set the All data about curricula on the course
            //$createNewCurricula = $this->get('funciones')->loadCurriculaCurso($infoUe);
            // $templateToView = 'indexprimaria.html.twig';
        }else{
            $primaria = false;
            // $templateToView = 'index.html.twig';
        }

        try {
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($idCurso);
            
            $sip = $curso->getSuperiorInstitucioneducativaPeriodo();

            $moduloPeriodo = $em->createQueryBuilder()
                ->select('l')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $idCurso)
                ->getQuery()
                ->getResult();

            $sw = false;
            foreach ($moduloPeriodo as $key => $value) {
                if ($value->getCodigo() == '415') {
                    $sw = true;
                }
            }
                
            if(!$sw){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();
                $newSuperiorModuloTipo = new SuperiorModuloTipo();
                $newSuperiorModuloTipo->setModulo('MÓDULO EMERGENTE');
                $newSuperiorModuloTipo->setObs('');
                $newSuperiorModuloTipo->setCodigo('415');
                $newSuperiorModuloTipo->setSigla('MIE');
                $newSuperiorModuloTipo->setOficial(1);
                $newSuperiorModuloTipo->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                $em->persist($newSuperiorModuloTipo);
                $em->flush();

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                $smp = new SuperiorModuloPeriodo();
                $smp->setSuperiorModuloTipo($newSuperiorModuloTipo);
                $smp->setInstitucioneducativaPeriodo($sip);
                $smp->setHorasModulo(0);
                $em->persist($smp);
                $em->flush();

                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                $ieco = new InstitucioneducativaCursoOferta();
                $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
                $ieco->setInsitucioneducativaCurso($curso);
                $ieco->setSuperiorModuloPeriodo($smp);
                $ieco->setHorasmes(0);
                $em->persist($ieco);
                $em->flush();
            }

            $em->getConnection()->commit();
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            $data['primaria'] = $primaria;
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function areasdeleteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $infoUe = $request->get('infoUe');
        $coid = $request->get('idco');
        $smpid = $request->get('smpId');
        $aInfoUeducativa = unserialize($infoUe);
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($idCurso);
        $satId = $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'];
        $mallaActual = $curso->getModalidadTipoId();

        $db = $em->getConnection();
        // VERIFICAR QUE NO SE PUEDA ELIMINAR < 500
        $gestion = $this->session->get('ie_gestion');
        if( $gestion >= 2023 && $satId == 32 ){

            // ENCONTRAR MODULOS BY SIP
            $queryOne = $db->prepare("select ico.id from institucioneducativa_curso_oferta ico 
                                        where ico.insitucioneducativa_curso_id=".$idCurso."");
            $queryOne->execute();
            $resultOne = $queryOne->fetchAll();
            
            // VERIFICAR CANTIDAD DE MODULOS ANTES DE ELIMINAR
            if( count($resultOne) == 5 ){
                
                $response = new JsonResponse();

                return $response->setData(array(
                    'statusCode' => 401,
                    'message'    => "No puede eliminar mas modulos"
                )); 

            }
        }

        // eliminamos el area del curso
        $em->getConnection()->beginTransaction();

        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe)
          ){
            $primaria = true;
            //set the All data about curricula on the course
            //$createNewCurricula = $this->get('funciones')->loadCurriculaCurso($infoUe);
            // $templateToView = 'indexprimaria.html.twig';
        }else{
            $primaria = false;
            // $templateToView = 'index.html.twig';
        }
        
        $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid);
        $smt = $smp->getSuperiorModuloTipo();

        try {
            
            $iecoen = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($coid);
            $iecoma = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $iecoen));
            $easig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $iecoen));
            $enota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $easig));

            foreach($enota as $enota_aux){
                $em->remove($enota_aux);
            }
            
            foreach($easig as $easig_aux){
                $em->remove($easig_aux);
            }
            
            foreach($iecoma as $iecoma_aux){
                $em->remove($iecoma_aux);
            }

            $em->remove($iecoen);
            if ($smt->getCodigo() == '415') {
                $em->remove($smp);
                $em->remove($smt);
            }
            
            $em->flush();

            $em->getConnection()->commit();
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            $data['primaria'] = $primaria;
            $data['mallaActual'] = $mallaActual;
            $data['superiorAcreditacionTipoId'] = $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'];

            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
//            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function getAreas($infoUe) {
        
        //dump('here'); die;

        $aInfoUeducativa = unserialize($infoUe);

        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $setId = $aInfoUeducativa['ueducativaInfoId']['setId'];
        $satCodigo = $aInfoUeducativa['ueducativaInfoId']['satCodigo'];
        
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turnoId = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        //dump($nivel); die; 22 gastronomia

        $em = $this->getDoctrine()->getManager();

        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($iecId);
        //dump($curso);die;

        if($curso->getModalidadTipoId() == 1) {
            $nuevamalla = true;
        } else {
            $nuevamalla = false;
        }

        if($nuevamalla == 1) {
            $turnoId = 10;
        } else {
            $turnoId = $curso->getTurnoTipo()->getId();
        }
        
        if($nivel == 15 || $nivel == 5){
                            
            $iePeriodo = $em->createQueryBuilder()
                ->select('g')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $iecId)
                ->getQuery()
                ->getResult();
            
            $moduloPeriodo = $em->createQueryBuilder()
                ->select('l.codigo')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $iecId)
                ->getQuery()
                ->getResult();
            
            if($moduloPeriodo) {                    
                $modulos = $em->createQueryBuilder()
                ->select('l')
                ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                ->where('l.codigo IN (:codigos)')
                ->andWhere('l.codigo NOT IN (:modulos)')
                ->setParameter('codigos', array(401,402,403,404,408))
                ->setparameter('modulos', $moduloPeriodo)
                ->getQuery()
                ->getResult();
            }else {
                $modulos = $em->createQueryBuilder()
                ->select('l')
                ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                ->where('l.codigo IN (:codigos)')
                ->setParameter('codigos', array(401,402,403,404,408))
                ->getQuery()
                ->getResult();
            }
            if($modulos) {
                /*
                $em->getConnection()->beginTransaction();
                try {    
                    foreach ($modulos as $modulo) {
                        //die("abc");        
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                        $smp = new SuperiorModuloPeriodo();
                        $smp->setSuperiorModuloTipo($modulo);
                        $smp->setInstitucioneducativaPeriodo($iePeriodo[0]);
                        $smp->setHorasModulo(0);
                        $em->persist($smp);
                        $em->flush();
                    }            
                    $em->getConnection()->commit();
                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                }*/

            }
        }

        // CONSULTA PARA OBTENER EL CODIGO DE ACREDITACION Y DE ESPECIALIDAD
        // CON LO CUAL SE PUEDE DEFINIR SI ES PRIMARIA O SECUNDARIA , ETC
        // $cursoTipo = $em->createQueryBuilder()
        //                 ->select('sat.codigo as codigosat, sest.codigo as codigosest')
        //                 ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
        //                 ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','siep','with','iec.superiorInstitucioneducativaPeriodo = siep.id')
        //                 ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','siea','with','siep.superiorInstitucioneducativaAcreditacion = siea.id')
        //                 ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','siea.acreditacionEspecialidad = sae.id')
        //                 ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo','sat','with','sae.superiorAcreditacionTipo = sat.id')
        //                 ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','sest','with','sae.superiorEspecialidadTipo = sest.id')
        //                 ->where('iec.id = :idCurso')
        //                 ->setParameter('idCurso', $iecId)
        //                 ->getQuery()
        //                 ->getResult();

        // VERIFICAMOS SI EL CURSO ES PRIMARIA  SUPERIOR_ESPECIALIDAD_TIPO 1 Y SPERIOR_ACREDITRACION_TIPO 1 O 2
        // if($cursoTipo[0]['codigosest'] == 1 and ($cursoTipo[0]['codigosat'] == 1 or $cursoTipo[0]['codigosat'] == 2) and $gestion >= 2019){
        if($this->get('funciones')->validatePrimariaCourse($iecId)){
            // VERIFICAMOS SI EL CURSO YA TIENE REGISTRADO LA OFERTA CON TODAS LAS MATERIAS INCLUIDO EL EMERGENTE
            $cursoOferta = $em->createQueryBuilder()
                            ->select('smt')
                            ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo','smp','with','ieco.superiorModuloPeriodo = smp.id')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloTipo','smt','with','smp.superiorModuloTipo = smt.id')
                            ->where('ieco.insitucioneducativaCurso = :idCurso')
                            ->setParameter('idCurso', $iecId)
                            ->getQuery()
                            ->getResult();
            $codigosCursoOferta = [];
            foreach ($cursoOferta as $key => $value) {
                $codigosCursoOferta[] = $value->getCodigo();
            }

            // DEFINIMOS LAS MATERIAS OBLIGATORIAS
            $materiasObligatorias = array(401,402,403,404,415);

            foreach ($materiasObligatorias as $mo) {
                // VERIFICAMO SI NO TIENE LA MATERIA EN CURSO OFERTA
                if(!in_array($mo, $codigosCursoOferta)){

                    // OBTENEMOS EL MODULO PERIODO PARA REGISTRARLO EN CURSO OFERTA
                    $cursoModulo = $em->createQueryBuilder()
                            ->select('smp')
                            ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                            ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'siep', 'WITH', 'iec.superiorInstitucioneducativaPeriodo = siep.id')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'smp', 'WITH', 'smp.institucioneducativaPeriodo = siep.id')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'smt', 'WITH', 'smp.superiorModuloTipo = smt.id')
                            ->where('iec.id = :idCurso')
                            ->andWhere('smt.codigo = :codigoModulo')
                            ->setParameter('idCurso', $iecId)
                            ->setParameter('codigoModulo', $mo)
                            ->getQuery()
                            ->getResult();

                    // SI NO EXISTE LA MATERIA EN EL CURSO Y ADEMAS ES MATERIA EMERGENTE, LO CREAMOS
                    // SOLO VALIDAMOS LA MATERIA EMERGENTE PORQUE PREVIAMENTE YA SE REGISTRAN LAS DEMAS MATERIAS LINEA 330
                    if(!$cursoModulo and $mo == 415){
                        $newSuperiorModuloTipo = new SuperiorModuloTipo();
                        $newSuperiorModuloTipo->setModulo('MÓDULO EMERGENTE');
                        $newSuperiorModuloTipo->setObs('');
                        $newSuperiorModuloTipo->setCodigo('415');
                        $newSuperiorModuloTipo->setSigla('MIE');
                        $newSuperiorModuloTipo->setOficial(1);
                        $newSuperiorModuloTipo->setSuperiorAreaSaberesTipo($em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                        //$em->persist($newSuperiorModuloTipo);
                        //$em->flush();

                        $smp = new SuperiorModuloPeriodo();
                        $smp->setSuperiorModuloTipo($newSuperiorModuloTipo);
                        $smp->setInstitucioneducativaPeriodo($iePeriodo[0]);
                        $smp->setHorasModulo(0);
                        //$em->persist($smp);
                        //$em->flush();

                        $cursoModulo = $smp;

                    }else{
                        $cursoModulo = $cursoModulo[0];
                    }

                    // REGISTRAMOS EL CURSO OFERTA
                    $ieco = new InstitucioneducativaCursoOferta();
                    $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
                    $ieco->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                    $ieco->setSuperiorModuloPeriodo($cursoModulo);
                    $ieco->setHorasmes(0);
                    //$em->persist($ieco);
                    //$em->flush();

                }
            }

        }

        // Curso oferta asignaturas del curso
        $cursoOferta = $em->createQueryBuilder()
                ->select('l.id as smpid, k.modulo, g.id as iecoid, k.codigo as codigo, k.esvigente')
                ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.id = g.insitucioneducativaCurso')                
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'l', 'WITH', 'l.id = g.superiorModuloPeriodo')              
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'k', 'WITH', 'k.id = l.superiorModuloTipo')              
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $iecId)
                ->orderBy('k.id', 'ASC')
                ->getQuery()
                ->getResult();
//                ->select('h.id as iecid, l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId, m.id as iecoid, g.id as siep')
//                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'm', 'WITH', 'm.superiorModuloPeriodo=k.id')
//                ->where('h.id = :idCurso')
//                ->setParameter('idCurso', $iecId)
//                ->getQuery()
//                ->getResult();
// dump($cursoOferta);
        $tieneCursoOferta = true;
        $actuales = array();
        foreach ($cursoOferta as $co) {
            $actuales[] = $co['smpid'];
        }
        // dump($nivel);die;  // 18
        if($nivel == 15 || $nivel == 5){
            if($actuales){
                $curso = $em->createQueryBuilder()                
                ->select('smt.id as id, smt.modulo as modulo, smt.codigo as codigo, smt.esvigente, smp.id as smpid')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'sip')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'sip.id = iec.superiorInstitucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'smp', 'WITH', 'sip.id = smp.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'smt', 'WITH', 'smt.id = smp.superiorModuloTipo')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.id = sip.superiorInstitucioneducativaAcreditacion')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sae.id = sia.acreditacionEspecialidad')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sat.id = sae.superiorAcreditacionTipo')
                ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'seti', 'WITH', 'seti.id = sae.superiorEspecialidadTipo')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.id = sia.institucioneducativaSucursal')
                ->where('seti.id = :setId')
                ->andWhere('sat.codigo = :satCodigo')
                ->andWhere('isuc.periodoTipoId = :periodoId')
                ->andWhere('isuc.gestionTipo = :gestion')
                ->andWhere('isuc.institucioneducativa = :institucion')
                ->andWhere('isuc.id = :sucursal')
                ->andWhere('smp.id NOT IN (:actuales)')
                ->andWhere('iec.id = :cursoId')
                ->setParameter('setId', $setId)
                ->setParameter('satCodigo', $satCodigo)
                ->setParameter('periodoId', $periodo)
                ->setParameter('gestion', $gestion)
                ->setParameter('institucion', $institucion)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('actuales', $actuales)
                ->setParameter('cursoId', $iecId)
                ->getQuery()
                ->getResult();
            }
            else{
                $curso = $em->createQueryBuilder()
                ->select('smt.id as id, smt.modulo as modulo, smt.codigo as codigo, smt.esvigente, smp.id as smpid')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'sip')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'sip.id = iec.superiorInstitucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'smp', 'WITH', 'sip.id = smp.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'smt', 'WITH', 'smt.id = smp.superiorModuloTipo')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.id = sip.superiorInstitucioneducativaAcreditacion')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sae.id = sia.acreditacionEspecialidad')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sat.id = sae.superiorAcreditacionTipo')
                ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'seti', 'WITH', 'seti.id = sae.superiorEspecialidadTipo')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.id = sia.institucioneducativaSucursal')
                ->where('seti.id = :setId')
                ->andWhere('sat.codigo = :satCodigo')
                ->andWhere('isuc.periodoTipoId = :periodoId')
                ->andWhere('isuc.gestionTipo = :gestion')
                ->andWhere('isuc.institucioneducativa = :institucion')
                ->andWhere('isuc.id = :sucursal')
                ->andWhere('iec.id = :cursoId')
                ->setParameter('setId', $setId)
                ->setParameter('satCodigo', $satCodigo)
                ->setParameter('periodoId', $periodo)
                ->setParameter('gestion', $gestion)
                ->setParameter('institucion', $institucion)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('cursoId', $iecId)
                ->getQuery()
                ->getResult();
            }
        } else {

            /*dump('aqui'); 
            dump($actuales); 
            die;*/

            if($actuales){
                $tieneCursoOferta = true;
                $curso = $em->createQueryBuilder()                
                ->select('smt.id as id, smt.modulo as modulo, smt.codigo as codigo, smt.esvigente, smp.id as smpid')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'sip')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'smp', 'WITH', 'sip.id = smp.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'smt', 'WITH', 'smt.id = smp.superiorModuloTipo')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.id = sip.superiorInstitucioneducativaAcreditacion')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sae.id = sia.acreditacionEspecialidad')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sat.id = sae.superiorAcreditacionTipo')
                ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'seti', 'WITH', 'seti.id = sae.superiorEspecialidadTipo')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.id = sia.institucioneducativaSucursal')
                ->where('seti.id = :setId')
                ->andWhere('sat.codigo = :satCodigo')
                ->andWhere('isuc.periodoTipoId = :periodoId')
                ->andWhere('isuc.gestionTipo = :gestion')
                ->andWhere('isuc.institucioneducativa = :institucion')
                ->andWhere('isuc.id = :sucursal')
                ->andWhere('smp.id NOT IN (:actuales)')
                ->andWhere('smt.esvigente = :nuevamalla');
                if($nuevamalla != 1){
                    $curso = $curso->andWhere('sia.superiorTurnoTipo = :turno')
                    ->setParameter('turno', $turnoId);
                }
                $curso = $curso->setParameter('setId', $setId)
                ->setParameter('satCodigo', $satCodigo)
                ->setParameter('periodoId', $periodo)
                ->setParameter('gestion', $gestion)
                ->setParameter('institucion', $institucion)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('actuales', $actuales)
                ->setParameter('nuevamalla', $nuevamalla)
                ->getQuery()
                ->getResult();
            }else{
                $tieneCursoOferta = false;
                $curso = $em->createQueryBuilder()
                ->select('smt.id as id, smt.modulo as modulo, smt.codigo as codigo, smt.esvigente, smp.id as smpid')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'sip')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'smp', 'WITH', 'sip.id = smp.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'smt', 'WITH', 'smt.id = smp.superiorModuloTipo')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.id = sip.superiorInstitucioneducativaAcreditacion')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sae.id = sia.acreditacionEspecialidad')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sat.id = sae.superiorAcreditacionTipo')
                ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'seti', 'WITH', 'seti.id = sae.superiorEspecialidadTipo')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.id = sia.institucioneducativaSucursal')
                ->where('seti.id = :setId')
                ->andWhere('sat.codigo = :satCodigo')
                ->andWhere('isuc.periodoTipoId = :periodoId')
                ->andWhere('isuc.gestionTipo = :gestion')
                ->andWhere('isuc.institucioneducativa = :institucion')
                ->andWhere('isuc.id = :sucursal')
                ->andWhere('smt.esvigente = :nuevamalla');
                if($nuevamalla != 1){
                    // dump($nuevamalla);
                    $curso = $curso->andWhere('sia.superiorTurnoTipo = :turno')
                    ->setParameter('turno', $turnoId);
                }
                $curso = $curso->andWhere('smt.esvigente = :nuevamalla')
                ->setParameter('setId', $setId)
                ->setParameter('satCodigo', $satCodigo)
                ->setParameter('periodoId', $periodo)
                ->setParameter('gestion', $gestion)
                ->setParameter('institucion', $institucion)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('nuevamalla', $nuevamalla)
                ->getQuery()
                ->getResult();



                // dump($curso);
                // die;
            }
        }
//        dump($iecId);
        // dump($curso); die;

//        $todas = array();
//        foreach ($curso as $c) {
//            $todas[] = $c['id'];
//        }
//
//        $codAsignaturas = $todas;

        // Asignaturas faltantes que le corresponde al curso
//        $asignaturas = $em->createQueryBuilder()
//                ->select('smt')
//                ->from('SieAppWebBundle:SuperiorModuloTipo', 'smt')
//                ->where('smt.id IN (:idAsignaturas)')             
//                ->setParameter('idAsignaturas', $codAsignaturas)
//                ->getQuery()
//                ->getResult();
        
//        $asignaturas = $em->createQueryBuilder()
//                ->select('l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId')
//                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
//                ->where('h.id = :idCurso')
//                ->andWhere('l.id IN (:idAsignaturas)')
//                ->setParameter('idCurso', $iecId)
//                ->setParameter('idAsignaturas', $codAsignaturas)
//                ->getQuery()
//                ->getResult();

        $sest_esoficial = true;
        $contAsg = count($curso);
        $resultSMMP = [];
        
        if( $this->session->get('ie_gestion') >= 2023 && ( $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 1 || $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 20 || $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 32 )  ){

            //dump('aquiiii'); die;

            $curso = $this->getAreasCajon( $aInfoUeducativa );
            $contAsg = count($curso) - count($cursoOferta);
            $sest_esoficial = ( count($curso) == 0 ) ? true : $curso[0]['es_oficial'];

            //  VERIFICAR PARA BLOQUEAR
            $db = $em->getConnection();
            $querySMMP = $db->prepare("select distinct smmp.superior_periodo_tipo_id from institucioneducativa_curso_oferta ico 
                                        inner join superior_modulo_periodo smp on ico.superior_modulo_periodo_id=smp.id
                                        inner join superior_malla_modulo_periodo smmp on smmp.superior_modulo_periodo_id=smp.id
                                        where 
                                        ico.insitucioneducativa_curso_id=".$iecId."");
            $querySMMP->execute();
            $resultSMMP = $querySMMP->fetchAll();

        }

        $nivelCurso = $aInfoUeducativa['ueducativaInfo']['ciclo'];
        $gradoParaleloCurso = $aInfoUeducativa['ueducativaInfo']['grado'] . " - " . $aInfoUeducativa['ueducativaInfo']['paralelo'];

        $cursoS = serialize($curso);
        return array('tieneCursoOferta' => $tieneCursoOferta, 'cursoOferta' => $cursoOferta, 'asignaturas' => $cursoS, 'infoUe' => $infoUe, 'operativo' => '', 'nivel' => $nivel, 'grado' => $grado, 'nivelCurso' => $nivelCurso, 'gradoParaleloCurso' => $gradoParaleloCurso, 'count' => $contAsg, 'sest_esoficial' => $sest_esoficial, 'countPeriodo'=>count($resultSMMP) );
    }

    public function areamaestroAction(Request $request) {

        $ieco = $request->get('idco');
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco);
        
        $notaT = $em->getRepository('SieAppWebBundle:NotaTipo')->find(0);

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $repository->createQueryBuilder('iecom')
                ->where('iecom.institucioneducativaCursoOferta = :curso')
                //->andWhere('iecom.notaTipo = :nota')
                ->setParameter('curso', $cursoOferta)
                //->setParameter('nota', $notaT)
                ->getQuery();

        $maestrosMateria = $query->getResult();
        
        $arrayMaestros = array();

        if ($maestrosMateria) {
            foreach ($maestrosMateria as $mm) {
                $arrayMaestros[] = array(
                    'id' => $mm->getId(),
                    'idmi' => $mm->getMaestroInscripcion()->getId(),
                    'horas' => $mm->getHorasMes(),
                    'idNotaTipo' => 0,
                    'periodo' => $periodo,
                    'idco' => $ieco);
            }
        } else {
            $arrayMaestros[] = array(
                'id' => 'nuevo',
                'idmi' => '',
                'horas' => '',
                'idNotaTipo' => 0,
                'periodo' => $periodo,
                'idco' => $ieco);
        }

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(2));
        $cargosArray = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mi')
                ->select('mi')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'mi.institucioneducativaSucursal = isuc.id')
                ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'with', 'mi.cargoTipo = ct.id')
                ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'with', 'ct.rolTipo = rt.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->andWhere('mi.periodoTipo = :periodo')
                ->andWhere('mi.institucioneducativaSucursal = :sucursal')
                ->andWhere('mi.esVigenteAdministrativo = :vigente')
                ->setParameter('idInstitucion', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('vigente', 't')
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $maestros = $query->getResult();

        return $this->render($this->session->get('pathSystem') . ':Areas:maestros.html.twig', array(
                    'maestrosCursoOferta' => $arrayMaestros,
                    'maestros' => $maestros,
                    'ieco' => $ieco,
                    'operativo' => $periodo)
        );
    }

    public function maestrosAsignarAction(Request $request){
        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        // $horas = $request->get('horas');

        //dump('here'); die;
        
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
        for($i=0;$i<count($iecom);$i++){
            $horasNum = 0;
            if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                // $query = $em->getConnection()->prepare('
                //     INSERT INTO institucioneducativa_curso_oferta_maestro(id,horas_mes,fecha_registro,es_vigente_maestro,maestro_inscripcion_id,nota_tipo_id,institucioneducativa_curso_oferta_id)
                //     VALUES(:id,:horas_mes, :fecha_registro, :es_vigente_maestro, :maestro_inscripcion_id, :nota_tipo_id, :institucioneducativa_curso_oferta_id)
                // ');
                
                // $query->bindValue(':id', intval('(select max(id) from institucioneducativa_curso_oferta_maestro) + 1'));
                // $query->bindValue(':horas_mes', $horasNum);
                // $query->bindValue(':fecha_registro', date('Y-m-d'));
                // $query->bindValue(':es_vigente_maestro', true);
                // $query->bindValue(':maestro_inscripcion_id', $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i])->getId());
                // $query->bindValue(':nota_tipo_id', $em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i])->getId());
                // $query->bindValue(':institucioneducativa_curso_oferta_id', $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i])->getId());
                // $query->execute();

                $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                $newCOM->setHorasMes($horasNum);
                $newCOM->setFechaRegistro(new \DateTime('now'));
                $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                $newCOM->setEsVigenteMaestro('t');
                $em->persist($newCOM);
                $em->flush();
            }else{
                if($idmi[$i] != ''){
                    $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                    $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $updateCOM->setHorasMes($horasNum);
                    $updateCOM->setFechaModificacion(new \DateTime('now'));
                    $updateCOM->setEsVigenteMaestro('t');
                    $em->flush();
                }
            }
        }
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0]));
    }

    public function areaEmergenteAction(Request $request) {
        $smpid = $request->get('smpid');

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $superiorModuloPeriodo = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid);
        $superiorModuloTipo = $superiorModuloPeriodo->getSuperiorModuloTipo();

        return $this->render($this->session->get('pathSystem') . ':Areas:emergente.html.twig', array(
                'smpid' => $smpid,
                'modulo' => $superiorModuloTipo,
                'moduloPeriodo' => $superiorModuloPeriodo,
            )
        );
    }

    public function emergenteAsignarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $smpid = $request->get('smpid');
        $smtid = $request->get('smtid');
        $emergente = mb_strtoupper($request->get('emergente'), 'utf-8');
        $emergente_horas = mb_strtoupper($request->get('emergente_horas'), 'utf-8');
        
        $moduloEmergente = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($smtid);
        $moduloEmergentePeriodo = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid);

        if($moduloEmergente){
            $moduloEmergente->setModulo($emergente);
            $em->persist($moduloEmergente);
            $em->flush();
        }

        if($moduloEmergentePeriodo){
            $moduloEmergentePeriodo->setHorasModulo($emergente_horas);
            $em->persist($moduloEmergentePeriodo);
            $em->flush();
        }

        $response = new JsonResponse();
        return $response->setData(array(
            'smpid'=>$smpid,
            'smtid'=>$smtid,
            'emergente'=>$emergente
        ));
    }

    public function registerNameModIntEmerAction(Request $request){
        // get the send values
        $infoUe = $request->get('infoUe');
        $nameModIntEmer = $request->get('nameModIntEmer');
        $codMIE = $request->get('codMIE');
        try {
            $em =  $this->getDoctrine()->getManager();
            $objSupModTipo = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find($codMIE);
            if($objSupModTipo){
                $objSupModTipo->setModulo(mb_strtoupper(trim($nameModIntEmer), 'UTF-8'));
                $em->persist($objSupModTipo);
                $em->flush();
                $arrResponse = array('status'=>true, 'message'=>'changed');
            }else{
                $arrResponse = array('status'=>false, 'message'=>'NO changed');
            }
            
        } catch (Exception $e) {
            
        }
        
        $response = new JsonResponse();
        return $response->setData($arrResponse);
        
    }

    public function mallasAddAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        $infoUe = $request->get('infoUe');
        $mallaId = $request->get('mallaId');
        //$malla = $mallaId == 1 ? 'Nueva' : 'Antigua';
        $malla = 'Nueva';
        $mallaId == 1; 
        $gestion = $this->session->get('ie_gestion');
        $aInfoUeducativa = unserialize($infoUe);
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($idCurso);
        $mallaActual = $curso->getModalidadTipoId();

        if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('ie_gestion'),$infoUe)
          ){
            $primaria = true;
        }else{
            $primaria = false;
        }

        $db = $em->getConnection();
        if($mallaId == 1){
            $query = "select distinct g.id as siepid
            from superior_facultad_area_tipo a  
                inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                            inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                    inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
					inner join superior_modulo_periodo h on h.institucioneducativa_periodo_id=g.id
						inner join superior_modulo_tipo i on h.superior_modulo_tipo_id=i.id
            where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."
            and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
            and a.codigo = '".$aInfoUeducativa['ueducativaInfoId']['sfatCodigo']."' and b.codigo = '".$aInfoUeducativa['ueducativaInfoId']['setCodigo']."' and d.codigo = '".$aInfoUeducativa['ueducativaInfoId']['satCodigo']."' and i.esvigente = 't' and a.codigo in (18,19,20,21,22,23,24,25)";
        } else {
            $query = "select distinct g.id as siepid
            from superior_facultad_area_tipo a  
                inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                            inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                    inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
					inner join superior_modulo_periodo h on h.institucioneducativa_periodo_id=g.id
						inner join superior_modulo_tipo i on h.superior_modulo_tipo_id=i.id
            where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."
            and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
            and a.codigo = '".$aInfoUeducativa['ueducativaInfoId']['sfatCodigo']."' and b.codigo = '".$aInfoUeducativa['ueducativaInfoId']['setCodigo']."' and d.codigo = '".$aInfoUeducativa['ueducativaInfoId']['satCodigo']."'
            and e.superior_turno_tipo_id = ".$curso->getTurnoTipo()->getId()." and (i.esvigente = 'f' or i.esvigente is null) and a.codigo in (18,19,20,21,22,23,24,25)";
        }

        $cursos= $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();

        if(count($cur)<1){
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            $data['primaria'] = $primaria;
            $data['mallaActual'] = $mallaActual;
            $data['mensaje'] = '¡No existe el tipo de malla seleccionada!';
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        }

        if(count($cur)>1){
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            $data['primaria'] = $primaria;
            $data['mallaActual'] = $mallaActual;
            $data['mensaje'] = '¡Cuenta con mallas duplicadas, verifique y corrija!';
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        }
        
        $siep = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->findOneById($cur[0]['siepid']);
        
        try {
            $curso->setSuperiorInstitucioneducativaPeriodo($siep);
            $curso->setModalidadTipoId(intval($mallaId));
            $em->persist($curso);
            $em->flush();
            $em->getConnection()->commit();
            
            $data = $this->getAreas($infoUe);
            $mallaActual = $curso->getModalidadTipoId();
            $data['mensaje'] = 'Asignación de malla correcta: '.$malla;
            $data['primaria'] = $primaria;
            $data['mallaActual'] = $mallaActual;
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));
        }
    }

}
