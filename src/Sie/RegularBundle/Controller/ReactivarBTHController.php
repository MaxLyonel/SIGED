<?php

namespace Sie\RegularBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo;
use Sie\AppWebBundle\Entity\RehabilitacionBth;




/**
 * ChangeMatricula controller.
 *
 */
class ReactivarBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) {

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $this->createFormBuilder()
            ->add('nro', 'text', array('label' => 'Nro.', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary','onclick'=>'buscarTramite()')))
            ->getForm();
        return $this->render('SieRegularBundle:ReactivarBTH:index.html.twig',array(
            'form' => $form->createView()
            )
        );
    }
    public function buscaTramiteBTHAction(Request $request){
        
        $id = $request->get('nro');
        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if($ie){
            $idInstitucion = $ie->getId();
        }else{
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id'=>$id,'flujoTipo'=>6));
            if($tramite){
                $idInstitucion = $tramite->getInstitucioneducativa()->getId();
            }else{
                $idInstitucion = null;
            }
        }
        
        if ($idInstitucion){
            $tramites = $em->getRepository('SieAppWebBundle:Tramite')->createQueryBuilder('t')
                    ->select('SUM(CASE WHEN t.fechaFin IS NULL THEN 1 ELSE 0 END) AS pendientes, SUM(CASE WHEN t.fechaFin IS NOT NULL THEN 1 ELSE 0 END) AS concluidos,COUNT(t.institucioneducativa) AS cantidad')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = t.institucioneducativa')
                    ->where('t.institucioneducativa = :id')
                    ->andwhere('t.tramiteTipo IN (:tipo)')
                    ->andwhere('t.flujoTipo = 6')
                    ->groupBy('ie.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('tipo', array(27,28,31))
                    ->getQuery()
                    ->getResult();
            //dump($tramites);die;
            if($tramites){
                if($tramites[0]['cantidad'] > 0){
                    if($tramites[0]['pendientes'] > 0){ //TIENE PENDIENTES
                        $response = new JsonResponse();    
                        if($tramites[0]['cantidad'] == 1){
                            $msg = 'La Unidad Educativa tiene un trámite BTH pendiente. Finalize su trámite para poder rehabilitar BTH';
                        }else{
                            $msg = 'La Unidad Educativa ya rehabilitó su trámite para BTH. Finalize su último trámite para poder rehabilitar nuevamente';
                        }
                        return $response->setData(array(
                            'msg' => $msg,
                        ));    
                    }else{ //NO TIENE TRAMITES PENDIENTES
                        //$tr = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('institucioneducativa'=>$id,'flujoTipo'=>6),array('id'=>'DESC'),1);
                        $tr = $em->getRepository('SieAppWebBundle:Tramite')->createQueryBuilder('t')
                            ->select('t.id,ie.id as codsie,ie.institucioneducativa,t.fechaRegistro,t.fechaFin,g.id as grado,tt.tramiteTipo,tt.id as tramiteTipoId,f.id as flujoTipoId')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = t.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico', 'h', 'WITH', 'ie.id = h.institucioneducativaId')
                            ->innerJoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'g.id = h.gradoTipo')
                            ->innerJoin('SieAppWebBundle:FlujoTipo', 'f', 'WITH', 'f.id = t.flujoTipo')
                            ->where('t.institucioneducativa = :id')
                            ->andwhere('t.tramiteTipo IN (:tipo)')
                            ->andwhere('h.gestionTipoId = (select max(h1.gestionTipoId) from SieAppWebBundle:InstitucioneducativaHumanisticoTecnico h1 where h1.institucioneducativaId=' . $idInstitucion.')')
                            ->andwhere('t.flujoTipo = 6')
                            ->orderBy('t.id','DESC')
                            ->setParameter('id', $idInstitucion)
                            ->setParameter('tipo', array(27,28,31))
                            ->getQuery()
                            ->getResult();
                        //dump($tr);die;
                        if($tr[0]['tramiteTipoId'] == 31){
                            $response = new JsonResponse();    
                            $msg = 'La Unidad Educativa ya rehabilitó su trámite para BTH.';
                            return $response->setData(array(
                                'msg' => $msg,
                            ));    
                        }
                        $form = $this->createFormBuilder()
                            ->setAction($this->generateUrl('reactivarbth_buscar_nuevo_guardar'))
                            ->add('idtramite', 'hidden', array('data' => $tr[0]['id']))
                            ->add('codsie', 'hidden', array('data' => $tr[0]['codsie']))
                            ->add('obs', 'textarea', array('label' => 'Observación', 'required' => true, 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase;')))
                            ->add('adjunto', 'file', array('label' => 'Adjuntar respaldo (Máximo permitido 3M)', 'attr' => array('title'=>"Adjuntar Respaldo",'accept'=>"application/pdf,.doc,.docx")))
                            ->add('guardar', 'submit', array('label' => 'Reactivar Trámite', 'attr' => array('class' => 'btn btn-primary')))
                            ->getForm();

                        return $this->render('SieRegularBundle:ReactivarBTH:reactivarBthGuardar.html.twig',array(
                                'formDatos' => $form->createView(),
                                'tramite'=> $tr,
                        ));
            
                    }
                }else{
                    $response = new JsonResponse();    
                    return $response->setData(array(
                        'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
                    ));    
                }
            }else{
                $response = new JsonResponse();    
                return $response->setData(array(
                    'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
                ));    
            }
        }else{
            $response = new JsonResponse();    
            return $response->setData(array(
                'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
            ));    
        }
    }

    public function buscaTramiteBTHGuardarAction(Request $request){
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $request->get('form');
        $documento = $request->files->get('form')['adjunto'];

        /*Validacion para que guarde el docuemnto*/
        if(!empty($documento)){
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'];
            //$root_bth_path = 'uploads/archivos/flujos/'.$form['codsie'];
            //dump($root_bth_path);die;
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            //$destination_path = 'uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
        //dump($form,$imagen,$sesion->get('currentyear'));die;
        $ieht = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$form['codsie'],'gestionTipoId'=>$sesion->get('currentyear')));
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($form['idtramite']);
        //dump($form,$imagen,$ieht);die;
        $em->getConnection()->beginTransaction();
        try {
            /**
             * registro de reactivacion bth
            */
            $rehabilitacionBth = new RehabilitacionBth();    
            $rehabilitacionBth->setObs(mb_strtoupper($form['obs'],'UTF-8') );
            $rehabilitacionBth->setFechaInicio(new \DateTime(date('Y-m-d')));
            $rehabilitacionBth->setAdjunto($imagen);
            $rehabilitacionBth->setTramite($tramite);
            $rehabilitacionBth->setInstitucioneducativaHumanisticoTecnico($ieht);
            $rehabilitacionBth->setUsuarioRegistroId($id_usuario);
            $rehabilitacionBth->setInstitucioneducativaId($ieht->getInstitucioneducativaId());
            $em->persist($rehabilitacionBth);
            $em->flush();
            
            //Modificacion tabla institucioneducativa_humanistico_tecnico
            $ieht->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));
            $ieht->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));
            $ieht->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $em->flush();

            //Modificacion tipo de tramite a Regularizacion en tramite
            $tramite->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find(31));
            $em->flush();

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('success', 'El tramite fué reactivado, la Unidad Educativa puede volver a iniciar un nuevo trámite como Registro Nuevo.');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error al reactivar el trámite, vuelva a intentar.');
        }
        
        return $this->redirectToRoute('reactivarbth_index');

    }
    public function historialBTHAction(Request $request){
        //$datosUeTramite = $this->obtenerDatosTramiteUe($request->get('id'));
        $datosUe = $this->obtenerInformacionUE($request->get('id'),$request->get('sie'));
        $obtenerDatosTramiteUe = $this->obtenerInformacionTramite($request->get('id'));
        //dump($datosUe['ubicacionUe']['institucioneducativa']);die;
        return $this->render('SieRegularBundle:ReactivarBTH:historial.html.twig',array(
        'idtramite'     => $request->get('id'),
        'institucion'   => $request->get('sie'),
        'institucioneducativa'   => $datosUe['ubicacionUe']['institucioneducativa'],
        'ubicacion'   => $datosUe['ubicacionUe'],
        'director'      => $datosUe['director'],
        'especialidadarray'=> $obtenerDatosTramiteUe['especialidades'],
        'grado'         => $obtenerDatosTramiteUe['grado'],
        'documentoDistrito'=>$obtenerDatosTramiteUe['documentoDistrito'],
        'documentoDepartamento'=>$obtenerDatosTramiteUe['documentoDepartamento']));
    }
    public function obtenerInformacionUE($tramiteId,$sie){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
            FROM tramite trm 
            INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
            INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
            WHERE trm.id=$tramiteId
            ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $gestion    = $infoUE['gestion_id'];
        //Datos de Ubicacion de la UE
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        //Datos del Director de la Unidad Educativa
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        return array('ubicacionUe' => $ubicacionUe ,'director' => $director);
    }
    public function obtenerInformacionTramite($tramiteId){
        $em = $this->getDoctrine()->getManager();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
        ->select('wf')
        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
        ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
        ->where('t.id =' . $tramiteId)
        ->orderBy('wf.id', 'desc')
        ->setMaxResults('1')
        ->getQuery()
        ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $especialidadarray = array();
        foreach ($datos[2]['select_especialidad'] as $value) {
            $especialidad = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->findOneById($value);
            $especialidadarray[] = $especialidad->getEspecialidad();
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
        ->select('wfd')
        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
        ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
        ->where('td.tramite='.$tramiteId)
        ->andWhere('fp.orden in (3,5)')
        ->andWhere("wfd.esValido=true")
        ->orderBy("td.flujoProceso")
        ->getQuery()
        ->getResult();
        $documentoDistrito = json_decode($resultDatos[0]->getDatos(),true);
        $documentoDepartamento =json_decode($resultDatos[1]->getDatos(),true);
        return array('especialidades' => $especialidadarray ,
            'grado' => ($datos[5]['grado'])?$datos[5]['grado']:'',
            'documentoDistrito'=>$documentoDistrito[5],
            'documentoDepartamento'=>$documentoDepartamento[6]);
    }
}
/*
$em = $this->getDoctrine()->getManager();
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
        ->select('wfd')
        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
        ->where('td.tramite='.$tramiteId)
        ->andWhere("wfd.esValido=true")
        ->orderBy("td.flujoProceso")
        ->getQuery()
        ->getResult();

$resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
->select('wfd')
->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
->where('td.tramite='.$tramite_id)
->andWhere('fp.orden=1')
->andWhere("wfd.esValido=true")
->orderBy("td.flujoProceso")
->getQuery()
->getSingleResult();
        */