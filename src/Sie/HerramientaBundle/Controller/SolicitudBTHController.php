<?php

namespace Sie\HerramientaBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\ProcesosBundle\Controller\TramiteRueController;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteTipo;

/**
 * SolicitudBTH controller.
 *
 */
class SolicitudBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    /*************Inicio Unificacion*************/
    public function nuevaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $institucion_id = $request->getSession()->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        $flujotipo = 0;
        $documento = '';
        $tipo_tramite = 27;// mejorar los Id de tipoTramite
        
        $gestion_sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :institucion_id')
            ->setParameter('institucion_id', $institucion_id)
            ->getQuery()
            ->getSingleResult();
        $gestion_sucursal = ($gestion_sucursal)?$gestion_sucursal:'';

        $objGrados = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
            ->select('(iec.gradoTipo)')
            ->where('iec.institucioneducativa = :sie')
            ->andWhere('iec.nivelTipo = :idnivel')
            ->andwhere('iec.gestionTipo = :gestion')
            ->andWhere('iec.gradoTipo in (:arrgrados)')
            ->setParameter('sie', $institucion_id)
            ->setParameter('idnivel', 13)
            ->setParameter('gestion', $gestion)
            ->setParameter('arrgrados', array(3,4,5,6))
            ->distinct()
            ->orderBy('iec.gradoTipo', 'ASC')
            ->getQuery()
            ->getResult();
        $grados = array();
        foreach ($objGrados as $data) {
            $grados[$data[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data[1])->getGrado();
        }

        // Especialidades para Nuevo o Ratificación
        $especialidad_anterior = $especialidad_vigente = array();
        $especialidades = $em->getConnection()->prepare("SELECT eth.id, eth.especialidad
            FROM institucioneducativa_especialidad_tecnico_humanistico a
            INNER JOIN especialidad_tecnico_humanistico_tipo eth on a.especialidad_tecnico_humanistico_tipo_id=eth.id
            WHERE institucioneducativa_id=$institucion_id and gestion_tipo_id=2018
            ORDER BY eth.especialidad");
        $especialidades->execute();
        $especialidad_homologacion = $especialidades->fetchAll();
        foreach ($especialidad_homologacion as $value) {
            $esp_id = $value['id'];
            if ($value['id'] == 55 ) {
                $esp_id = 3;
            } elseif ($value['id'] == 45 ) {
                $esp_id = 62;
            } elseif ($value['id'] == 44 ) {
                $esp_id = 63;
            } elseif ($value['id'] == 2 ) {
                $esp_id = 61;
            } elseif ($value['id'] == 9 ) {
                $esp_id = 66;
            } elseif ($value['id'] == 6 ) {
                $esp_id = 4;
            }
            $especialidad = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->findOneById($esp_id);
            if ($especialidad)
                $especialidad_anterior[] = array('id'=>$especialidad->getId(), 'especialidad'=>$especialidad->getEspecialidad());
        }

        $especialidad_vigente = $this->obtieneespecialidadesrestantes($institucion_id, 2018);

        if ($request->get('tipo') == 'idflujo') {
            $flujotipo = $request->get('id');
            /* $regularizacion = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->createQueryBuilder('ieth')
                ->select('ieth')
                ->innerJoin('SieAppWebBundle:RehabilitacionBth', 'rbth', 'WITH', 'rbth.institucioneducativaHumanisticoTecnico = ieth.id')
                ->where('rbth.institucioneducativa = :institucion_id')
                ->setParameter('institucion_id', $institucion_id)
                ->getQuery()
                ->getResult(); */
            $arrRegularizacion = $em->getConnection()->prepare("SELECT * 
                FROM institucioneducativa_humanistico_tecnico a  INNER JOIN rehabilitacion_bth b on a.id = b.institucioneducativa_humanistico_tecnico_id
                WHERE b.institucioneducativa_id = $institucion_id");
            $arrRegularizacion->execute();
            $regularizacion = $arrRegularizacion->fetchAll();
            if (count($regularizacion) > 0) {
                $tipo_tramite = 31;
            }
        } else {
            $tramite_id = $request->get('id');
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);//'1652454'
            $flujotipo = $tramite->getFlujoTipo()->getId();
            $tipo_tramite = $tramite->getTramiteTipo()->getid();

            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                ->where('t.id ='.$tramite_id)
                ->orderBy('wf.id', 'desc')
                ->setMaxResults('1')
                ->getQuery()
                ->getSingleResult();
            $datos = json_decode($wfSolicitudTramite->getDatos(), true);
            if (isset($datos[5])) {
                $documento = $datos[5];
            }

            // Verificar si va las valicaciones para devolución
            // $verificarinicioTramite = $this->verificatramite($institucion_id, $gestion, $flujotipo);
            $datosForm = $this->datosFormulario($institucion_id, $gestion_sucursal);
            return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDirector.html.twig', array(
                'institucion' => $datosForm[0],
                'ubicacion' => $datosForm[1],
                'especialidad' => $especialidad_vigente,
                'especialidad_anterior' => $especialidad_anterior,
                'grados' => $grados,
                'idflujo' => $flujotipo,
                'tipo_tramite' => $tipo_tramite,
                'tramite_id' => $tramite_id,
                'estado' => 0,
                'documento' => $documento,
                'sw' => 1
            ));
        }
        $verificarinicioTramite = 0;
        if ($flujotipo != 0) {
            $verificarinicioTramite = $this->verificatramite($institucion_id, $gestion, $flujotipo);
        }

        if ($tipo_tramite == 31) {
            // Especialidades para Regularización
            $query = $em->getConnection()->prepare("SELECT eth.id, eth.especialidad
                FROM institucioneducativa_especialidad_tecnico_humanistico a
                INNER JOIN especialidad_tecnico_humanistico_tipo eth on a.especialidad_tecnico_humanistico_tipo_id =eth.id
                WHERE eth.es_vigente is TRUE and institucioneducativa_id = $institucion_id and gestion_tipo_id = $gestion
                ORDER BY 2");
            $query->execute();
            $especialidad_anterior = $query->fetchAll();
            $especialidad_vigente = $this->obtieneespecialidadesrestantes($institucion_id, $gestion);
            $tipo_tramite = 27;
        }
        
        $datosForm = $this->datosFormulario($institucion_id, $gestion_sucursal);
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDirector.html.twig',
            array('institucion' => $datosForm[0],
                'ubicacion' => $datosForm[1],
                'especialidad' => $especialidad_vigente,
                'especialidad_anterior' => $especialidad_anterior,
                'grados' => $grados,
                'idflujo' => $flujotipo,
                'tipo_tramite' => $tipo_tramite,
                'tramite_id' => 0,
                'estado' => $verificarinicioTramite,
                'documento' => $documento,
                'sw' => 0
            ));
    }

    public function guardaNuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');

        $flujotipo = $request->get('idflujotipo');
        $datos = $request->get('ipt');
        $institucion_id = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
        $id_tipoTramite = $request->get('idsolicitud');
        $id_distrito = $request->get('id_distrito');
        
        $sw = $request->get('sw');

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
        $tarea = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $datos = ($request->get('ipt'));
        $idTramite = '';
        if($sw == 0) {
            // Envio de solicitud nuevo
            $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $institucion_id, '', $id_tipoTramite, '', $idTramite,$datos, '', $id_distrito);
        } else {
            // Envio devolucion por el distrital
            $idTramite = $request->get('tramite_id');
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $institucion_id, '', '', $idTramite, $datos, '', $id_distrito);
        }
        if ($mensaje['dato'] == true) {
            $res = 1;
            $msg = $mensaje['msg'];
            if($sw == 0) {
                $idTramite = $mensaje['idtramite'];
            }
        } else {
            $res = 2;
            $msg = '';
        }
        // Adecuacion: se registra como plena a las unidades que inicien su tramite.
        if ($res == 1 and $sw == 0) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:InstitucionEducativa')->find($institucion_id);
            $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucion_id));
            if(empty($objrehabilitationbth)) {
                try {
                    // Si la Unidad Educativa ya esta registrado como BTH, lo actualiza, caso contrario lo registra
                    $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucion_id, 'gestionTipoId'=>$gestion));
                    if (empty($entity)) {
                        $entity = new InstitucioneducativaHumanisticoTecnico();
                        $entity->setGestionTipoId($gestion);
                        $entity->setInstitucioneducativaId($institucion_id);
                        $entity->setInstitucioneducativa($institucioneducativa->getInstitucioneducativa());
                        $entity->setEsimpreso(false);
                    }
                    $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));
                    $entity->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                    $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));
                    $em->persist($entity);
                    $em->flush();
                } catch (Exception $exceptione) {
                    $respuesta = $this->get('wftramite')->eliminarTramteNuevo($idTramite);
                }
                
            }
        }
        return  new JsonResponse(array('estado' => $res, 'msg' => $msg));
    }

    // DISTRITAL
    public function formularioDistritalAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_tramite = $request->get('id');
        $sw = 0;
        $query = $em->getConnection()->prepare("select td.* from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$id_tramite);
        $query->execute();
        $td = $query->fetch();
        if (isset($td['tramite_detalle_id'])) {
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId () == 4) {
                $sw = 1;
            }
        }
        // Obtenemios la información del trámite
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id ='.$id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getSingleResult();
        $datos = json_decode($wfSolicitudTramite->getDatos(), true);
        $informe = $datos[0]['informe'];
        $institucion_id = $datos[1]['institucionid'];
        $gestion = $request->getSession()->get('currentyear');
        
        $director = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion_id)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1, 12))
            ->setParameter('esvigente',true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
   
        $especialidadarray = array();
        foreach ($datos[2]['select_especialidad'] as $value) {
            $especialidad = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->findOneById($value);
            if ($especialidad) {
                $especialidadarray[] = array('id' => $especialidad->getId(), 'especialidad' => $especialidad->getEspecialidad());
            }
        }

        $datosForm = $this->datosFormulario($institucion_id, $gestion);
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDistrito.html.twig', array(
            'institucion' => $datosForm[0],
            'ubicacion' => $datosForm[1],
            'director' => $director,
            'especialidadarray' => $especialidadarray,
            'informe' => $informe,
            'id_tramite' => $id_tramite,
            'grado' => $sw==0?$datos[3]['grado']:$datos[5]['grado'],
            'sw' => $sw
        ));
    }

    public function guardaDistritalAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $documento = $request->files->get('docpdf');
        if(!empty($documento)) {
            /* $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid');
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid').'/bth/'; */
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        } else {
            $imagen='default-2x.pdf';
        }

        $id_rol = $this->session->get('roluser');
        $id_usuario = $this->session->get('userId');
        $institucionid = $request->get('institucionid');
        $id_distrito = $request->get('id_distrito');
        $evaluacion = $request->get('evaluacion');
        $id_tramite = $request->get('id_tramite');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($id_tramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $obs = $request->get('obstxt');
        $datos = json_decode($request->get('ipt'));

        array_push($datos, $imagen);
        $datos = json_encode($datos);
        // ELABORA INFORME
        $res = 1;
        $msg = "";
        try{
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$id_tramite,$datos,'',$id_distrito);
            if ($mensaje['dato'] == true) {
                if ($evaluacion == 'SI') {
                    $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
                    $tarea1 = $flujoproceso->getId();//elaborainfrorme y envia BTH

                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1, $id_tramite);
                    if($mensaje['dato']==true){
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$id_tramite,$datos,'',$id_distrito);
                        if($mensaje['dato'] == true) {
                            $msg = $mensaje['msg'];
                            $res = 1;
                        } else {
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($id_tramite);
                            $respuesta = $this->get('wftramite')->eliminarTramteEnviado($id_tramite, $id_usuario);
                            $msg = $mensaje['msg'];
                            $res = 4;
                        }
                    } else {
                        $respuesta = $this->get('wftramite')->eliminarTramteEnviado($id_tramite,$id_usuario);
                        $msg = $mensaje['msg'];
                        $res = 4;
                    }
                }
            } else {
                $msg = $mensaje['msg'];
                $res = 4;
            }
        } catch (Exception $exceptione) {
            $res = 0;
        }
        return  new JsonResponse(array('estado' => $res, 'msg' => $msg));
    }

    // DEPARTAMENTAL
    public function formularioDepartamentalAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $id_tramite = $request->get('id');
        $sw = 0;
        $query = $em->getConnection()->prepare("select td.* from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$id_tramite);
        $query->execute();
        $td = $query->fetch();
        if (isset($td['tramite_detalle_id'])) {
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId () == 4) {
                $sw = 1;
            }
        }
        // Obtenemios la información del trámite
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id ='.$id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getSingleResult();
        $datos = json_decode($wfSolicitudTramite->getDatos(), true);
        $informe = $datos[0]['informe'];
        $institucion_id = $datos[1]['institucionid'];
        $gestion = $request->getSession()->get('currentyear');
        // dump($datos);die;
        if (!empty($datos[4]['grado'])){
           $documento= ($datos[5])?$datos[5]:'';
        } else {
            $documento= ($datos[4])?$datos[4]:'';
        }
        
        $director = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion_id)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1, 12))
            ->setParameter('esvigente',true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
   
        $especialidadarray = array();
        foreach ($datos[2]['select_especialidad'] as $value) {
            $especialidad = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->findOneById($value);
            if ($especialidad) {
                $especialidadarray[] = array('id' => $especialidad->getId(), 'especialidad' => $especialidad->getEspecialidad());
            }
        }

        $datosForm = $this->datosFormulario($institucion_id, $gestion);
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDepartamento.html.twig',array(
            'institucion' => $datosForm[0],
            'ubicacion' => $datosForm[1],
            'director' => $director,
            'especialidadarray'=> $especialidadarray,
            'informe' => $informe,
            'id_tramite' => $id_tramite,
            'documento' => $documento,
            'grado'=>$datos[4]['grado']
        ));
    }

    public function guardaDepartamentalAction(Request $request){
        $documento = $request->files->get('docpdf');
        if(!empty($documento)) {
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid');
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0775);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';           
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0775);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
        $id_rol = $this->session->get('roluser');
        $id_usuario = $this->session->get('userId');
        $institucionid = $request->get('institucionid');
        $id_distrito = $request->get('id_distrito');
        $evaluacion = $request->get('evaluacion');
        $evaluacion2 = $request->get('evaluacion2');
        $idtramite = $request->get('id_tramite');
     
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idtramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); 
        $tarea   = $flujoproceso->getId();

        $datos = json_decode($request->get('ipt'));
        $datosAux = json_decode($request->get('ipt'),true);
        $gradoSelected = 0;
        if(isset($datosAux[5]['grado'])){
            $gradoSelected = $datosAux[5]['grado'];
        }else{            
            return new JsonResponse(array('estado' => 3, 'msg' => 'El trámite de la Unidad Educativa no cuenta con el grado correspondiente, se sugiere realizar la devolución del trámite.'));  
        }
        array_push($datos, $imagen);
        $datos = json_encode($datos);
        $obs = $request->get('obstxt');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
        $tarea1 = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $res = 1;
        $msg = "";
        try {
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);
            if($mensaje['dato'] == true) {
                if ($evaluacion == 'SI') {
                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
                    if($mensaje['dato'] == true) {
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,$obs,'',$idtramite,$datos,'',$id_distrito);
                        if($mensaje['dato'] == true) {
                            // Volcado a la base de datoa
                            // Recuperado de datos del tramite
                            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                                ->select('wf')
                                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                                ->where('t.id =' . $idtramite)
                                ->orderBy('wf.id', 'desc')
                                ->setMaxResults('1')
                                ->getQuery()
                                ->getResult();
                            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
                            // Una vez finalizado el tramite se registra el la tabla correspondiente
                            //Definimos el tipo de la UE
                            if ($gradoSelected == 3 or $gradoSelected == 4) {
                                $estado_grado_tipo = 7;
                            } elseif ($gradoSelected == 5 or $gradoSelected == 6) {
                                $estado_grado_tipo = 1;
                            }
                            //Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                            $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                            if ($institucionBth){                                                                    
                                $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoSelected));
                                $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                                $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));
                                $em->persist($institucionBth);
                                $em->flush();
                            }
                                
                            // Registra las especilidades
                            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucionid);
                            for($i=0; $i<count($datos[2]['select_especialidad']); $i++) {
                                $idespecialidad = $datos[2]['select_especialidad'][$i];
                                $especialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy(array(
                                    'institucioneducativa'=> $institucionid,
                                    'especialidadTecnicoHumanisticoTipo'=> $idespecialidad,
                                    'gestionTipo' => $request->getSession()->get('currentyear')
                                ));
                                if (empty($especialidad)) {
                                    $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                                    $entity->setInstitucioneducativa($institucioneducativa);
                                    $entity->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad));
                                    $entity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                                    $entity->setFechaRegistro(new \DateTime(date('Y-m-d')));
                                    $em->persist($entity);
                                    $em->flush();
                                }
                            }
                            // Actualiza la fecha de finalización la tabla de Rehabilitados BTH
                            $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucionid));  
                            if($objrehabilitationbth){
                                $rehabilitacion = $em->getRepository('SieAppWebBundle:RehabilitacionBth')->find($objrehabilitationbth[0][1]);
                                $rehabilitacion->setFechaFin(new \DateTime(date('Y-m-d')));
                                $em->persist($rehabilitacion);
                                $em->flush();
                            }
                        } else {
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                            $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $id_usuario);
                            $msg = $mensaje['msg'];
                            $res = 4;
                        }
                    } else {
                        $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite, $id_usuario);
                        $msg = $mensaje['msg'];
                        $res = 4;
                    }
                } else {
                    // Si la evaluacion es NO, recepciona tramite y envia
                    $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 6));
                    $tarea2 = $flujoproceso->getId();
                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                    if($mensaje['dato'] == true) {
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea2, $tabla, $institucionid, $obs, $evaluacion2, $idtramite, $datos, '', $id_distrito);
                        if($mensaje['dato'] == true) {
                            // Al dar como respuesta NO se elimina el registro de la UE de la tabla InstitucioneducativaHumanisticoTecnico y no se registran las especialidades
                            if($evaluacion2 =='NO') {
                                $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                                if($institucionBth) {
                                    $em->remove($institucionBth);
                                    $em->flush();
                                }
                            }
                            $msg = $mensaje['msg'];
                        } else {
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($id_tramite);
                            $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite, $id_usuario);
                            $msg = $mensaje['msg'];
                            $res = 4;
                        }
                    } else {
                        $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite, $id_usuario);
                        $msg = $mensaje['msg'];
                        $res = 4;
                    }
                }

            } else {
                // Si existe error en el primer guardar tramite
                $msg = $mensaje['msg'];
                $res = 4;
            }
        } catch (Exception $exceptione){
            $res = 0;
        }
        return new JsonResponse(array('estado' => $res, 'msg' => $msg));
    }
    
    // FUNCIONES ADICIONALES
    public function datosFormulario($institucion_id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        // $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion_id);
        $infoUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
            ->select('ie')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            ->where('ie.id = :institucion_id')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('institucion_id', $institucion_id)
            ->setParameter('gestion', $gestion)
            ->getQuery()
            ->getSingleResult();
            
        $localizacion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->createQueryBuilder('jg')
            ->select('lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS seccion, lt1.lugar AS canton, lt.lugar AS localidad,
                dist.id AS codigo_distrito,
                dist.distrito,
                orgt.orgcurricula,
                dept.dependencia,
                jg.id AS codigo_le,
                inst.id,
                inst.institucioneducativa,
                lt.area2001,
                estt.estadoinstitucion,
                inss.direccion,
                jg.direccion,
                jg.zona,
                jg.lugarTipoIdDistrito')
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
            ->setParameter('idInstitucion', $institucion_id)
            ->setParameter('gestion', $gestion)
            ->getQuery()
            ->getSingleResult();
        
            return array($infoUe, $localizacion);
    }

    public function verificatramite($id_Institucion, $gestion, $flujotipo) {
        // Verifica si la UE inicio tramite de BHT
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT COUNT(tr.id) AS  cantidad_tramite_bth FROM tramite tr  
            WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
            AND tr.tramite_tipo <> 31
            AND tr.gestion_id = $gestion");//verificar la gestion para solicutudes posteriores
        $query->execute();
        $tramite_ue = $query->fetch(); //dump($tramite_ue);die;
        $tramite_iniciado=$tramite_ue['cantidad_tramite_bth'];
        if($tramite_iniciado==0){
            // Verifica si la unidad educativa que inicio el tramite tenga estudiantes para el nivel SECUNDARIA
            $query = $em->getConnection()->prepare("SELECT count(ei.id) AS estudiantes
                FROM estudiante_inscripcion ei
                INNER JOIN institucioneducativa_curso iec ON ei.institucioneducativa_curso_id = iec.id
                WHERE iec.nivel_tipo_id = 13 AND ei.estadomatricula_tipo_id = 4 AND iec.gestion_tipo_id = $gestion AND iec.institucioneducativa_id = $id_Institucion
                group by iec.institucioneducativa_id");
            $query->execute();
            $cant_alumnos = $query->fetch(); 
            $cantidad_estudiantes = $cant_alumnos['estudiantes'];

            // verificar si el director de la UE esta vigente
            $query = $em->getConnection()->prepare("SELECT COUNT(*) as director_valido from maestro_inscripcion m
            join usuario u on m.persona_id=u.persona_id
            where m.institucioneducativa_id= $id_Institucion and m.gestion_tipo_id=$gestion and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
            $query->execute();
            $director = $query->fetch();
            $director_valido = $director['director_valido'];
            if ($cantidad_estudiantes==0 or $director_valido==0){
                $tramite_iniciado=1;
            }else{
                $tramite_iniciado=0;
            }            
            return $tramite_iniciado;
        }else{
            return $tramite_iniciado;
        }
    }

    public function obtieneespecialidadesrestantes($id_institucion, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $especialidades = $em->getConnection()->prepare("SELECT espt.id
            FROM institucioneducativa_especialidad_tecnico_humanistico ieth
            INNER JOIN especialidad_tecnico_humanistico_tipo espt ON ieth.especialidad_tecnico_humanistico_tipo_id = espt.id
            WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion");
        $especialidades->execute();
        $especialidades_id = $especialidades->fetchAll();
        if ($gestion == 2018) {
            foreach ($especialidades_id as $value) {
                $esp_id = "";
                switch ($value['id']) {
                    case 55:
                        $esp_id = 3;
                        break;
                    case 45:
                        $esp_id = 62;
                        break;
                    case 44:
                        $esp_id = 63;
                        break;
                    case 2:
                        $esp_id = 61;
                        break;
                    case 9:
                        $esp_id = 66;
                        break;
                    case 6:
                        $esp_id = 4;
                        break;
                    default:
                        $esp_id = $value['id'];
                        break;
                }
                $especialidad = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->findOneById($esp_id);
                if ($especialidad) {
                    $especialidad_homologado[] = $especialidad->getId();
                }
            }
        } else {
            $especialidad_homologado = $especialidades_id;
        }
        $especialidades_restantes = $em->createQueryBuilder()
            ->select('e.id, e.especialidad')
            ->from('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo','e')
            ->where('e.id not in (:noEspecilidades)')
            ->andWhere('e.esVigente = :estado')
            ->setParameter('noEspecilidades', $especialidad_homologado)
            ->setParameter('estado', true)
            ->orderBy('e.especialidad')
            ->getQuery()
            ->getResult();
        return $especialidades_restantes;
    }

    public function imprimirDirectorAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($tramite_id);
        $gestion = $tramite->getGestionId();
        $institucion_id = $tramite->getInstitucioneducativa()->getId();

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
        $datos = json_decode($resultDatos->getDatos());
        $grado_reporte = $datos[3];

        $arch = 'FORMULARIO_'.$request->get('idUE').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'bth_infoue_v1_gq.rptdesign&idUE='.$institucion_id.'&gestion='.$gestion.'&idtramite='.$tramite_id.'&grado='.$grado_reporte.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**************Fin Unificacion***************/
    //Director
    public function indexAction (Request $request) {
        $this->session  = $request->getSession();
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $institucion = $request->getSession()->get('ie_id');
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
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
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);


        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();

        $director = $query->getOneOrNullResult();

        //llamada a la  funcion que devuelve las tareas pendientes
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);

        $lista = $TramiteController->tramiteTarea(34,34,7,$id_usuario,$id_rol,$ie_id);

        return $this->render($this->session->get('pathSystem') . ':SolicitudBTH:index.html.twig', array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            'maestros'=>$maestros,
            'listatareas'=>$lista['tramites']
        ));
    }
    public function nuevasolicitudAction(Request $request){
        $id_Institucion =  $request->getSession()->get('ie_id');
        $gestion =  $request->getSession()->get('currentyear');
        $flujotipo = $request->get('id');
        $verificarinicioTramite = $this->verificatramite($id_Institucion,$gestion,$flujotipo);

            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("select td.*
                                                from tramite t
                                                join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
            $query->execute();
            $td = $query->fetchAll();
            if (isset($td[0]['tramite_detalle_id'])){
                $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
                if($tramiteDetalle->getTramiteEstado()->getId()== 4){
                    $iUE = $tramiteDetalle->getTramite()->getInstitucioneducativa()->getId();
                    return $this->redirectToRoute('solicitud_bth_formulario',array('iUE'=>$iUE,'id_tramite'=>$request->get('id')));
                }
            }else{

                $query = $em->getConnection()->prepare("SELECT * from tramite tr
                                                    WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
                                                    AND tr.tramite_tipo = 31
                                                    AND tr.gestion_id = $gestion");
                $query->execute();
                $tramite_ue = $query->fetchAll(); 
                // dump($tramite_ue);die;
                if($tramite_ue){
                    $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.id=27");
                }else{
                    $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.obs='BTH'");
                }

                $query->execute();
                $tramite_tipo = $query->fetchAll();
                
                
                $tramite_tipoArray = array();
                for ($i = 0; $i < count($tramite_tipo); $i++) {
                    $tramite_tipoArray[$tramite_tipo[$i]['id']] = trim($tramite_tipo[$i]['tramite_tipo']);
                }
                //Informacion de la U.E. y del director
                $institucion = $request->getSession()->get('ie_id');
                $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
                $query = $repository->createQueryBuilder('inss')
                    ->select('max(inss.gestionTipo)')
                    ->where('inss.institucioneducativa = :idInstitucion')
                    ->setParameter('idInstitucion', $institucion)
                    ->getQuery();
                $inss = $query->getResult();
                $gestion = $inss[0][1];
                //$gestion = 2018;
                $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
                $query = $repository->createQueryBuilder('ie')
                    ->select('ie, ies')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                    ->where('ie.id = :idInstitucion')
                    ->andWhere('ies.gestionTipo in (:gestion)')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $inss)
                    ->getQuery();
                $infoUe = $query->getResult();
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
                        inss.direccion,
                        jg.direccion,
                        jg.zona,
                        jg.lugarTipoIdDistrito')
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
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $inss)
                    ->getQuery();
                $ubicacionUe = $query->getSingleResult();
                /*
                 * obtenemos datos de la unidad educativa
                 */
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
                $institucionid = $request->getSession()->get('ie_id');
                // Lista de cursos institucioneducativa
                $query = $em->getConnection()->prepare("SELECT  iue.grado_tipo_id,gt.grado  
                                                FROM institucioneducativa_curso iue 
		                                        INNER JOIN grado_tipo gt ON iue.grado_tipo_id=gt.id  
                                                WHERE iue.institucioneducativa_id=$institucionid
                                                AND iue.gestion_tipo_id=$gestion
                                                AND iue.nivel_tipo_id = 3");
                $query->execute();
                $cursos = $query->fetchAll();
                /*
                 * Listamos los maestros inscritos en la unidad educativa
                 */
                $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
                $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
                $query = $repository->createQueryBuilder('mins')
                    ->select('per.carnet, per.paterno, per.materno, per.nombre')
                    ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                    ->where('mins.institucioneducativa = :idInstitucion')
                    ->andWhere('mins.gestionTipo = :gestion')
                    ->andWhere('mins.cargoTipo IN (:cargo)')
                    ->andWhere('mins.esVigenteAdministrativo = :esvigente')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('cargo', array(1,12))
                    ->setParameter('esvigente', true)
                    ->setMaxResults(1)
                    ->getQuery();
                $director = $query->getOneOrNullResult();
                /*Grado para el cual se aplica la ratificacion BTH*/
                /**
                 * Se aplica a las gestion  2018 mientras pase las inscripciones $gestion = 2018
                 */
                //$gestion = 2018;
                /*$query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht
                                                    WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestion");
                $query->execute();*/
                $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                WHERE ieht.institucioneducativa_id = $institucionid and gestion_tipo_id = 2018 and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)
                ORDER BY gestion_tipo_id DESC limit 1");
                $query->execute();
                $grado = $query->fetch();

                if($grado){
                     if ((int)$grado['grado_tipo_id'] < 6){                  
                    $grado = (int)$grado['grado_tipo_id']+1;    
                    }else{
                    $grado = (int)$grado['grado_tipo_id'];
                    }
                } else{
                    $grado = 3;
                } 

               
                /** Adecuacion a las equivalencias de especialidades */
                $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad
                                                FROM institucioneducativa_especialidad_tecnico_humanistico a
                                                INNER JOIN especialidad_tecnico_humanistico_tipo eth on a.especialidad_tecnico_humanistico_tipo_id =eth.id
                                                WHERE eth.es_vigente is TRUE and institucioneducativa_id = $id_Institucion and gestion_tipo_id = $gestion
                                                ORDER BY 2");
                $query->execute();
                $especialidad_actual = $query->fetchAll(); //dump($especialidad);die;
                $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$id_Institucion));
                if(!$objrehabilitationbth){
                    $gestion = 2018;
                }

                $especialidades_adicionar=$this->obtieneespecialidadesrestantes($id_Institucion,$gestion);
               
                //get all course of the UE 
                $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
               $query = $entity->createQueryBuilder('iec')
                       ->select('(iec.gradoTipo)')
                       ->where('iec.institucioneducativa = :sie')
                       ->andWhere('iec.nivelTipo = :idnivel')
                       ->andwhere('iec.gestionTipo = :gestion')
                       ->andWhere('iec.gradoTipo in (:arrgrados)')
                       ->setParameter('sie', $institucion->getId())
                       ->setParameter('idnivel', 13)
                       ->setParameter('gestion', $gestion)
                       ->setParameter('arrgrados', array(3,4,5,6))
                       ->distinct()
                       ->orderBy('iec.gradoTipo', 'ASC')
                       ->getQuery();
                       // dump($query->getSQL());die;
               $objGrados = $query->getResult();
               $arrGrados = array();
               foreach ($objGrados as $data) {
                   $arrGrados[$data[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data[1])->getGrado();
               }
                // dump($arrGrados);die;

                $form= $this->createFormBuilder()
                    ->add('solicitud', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $tramite_tipoArray, 'attr' => array('class' => 'form-control chosen-select','onchange' => 'cargarFormularioSolicitud()')))
                    ->getForm();
                return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDirec.html.twig',array( 'form' => $form->createView(),'ieducativa' => $infoUe,
                    'institucion'   => $institucion,
                    'gestion'       => $gestion,
                    'ubicacion'     => $ubicacionUe,
                    'director'      => $director,
                    'cursos'        => $cursos,
                    'maestros'      => $maestros,
                    'especialidad'  => $especialidades_adicionar,
                    'especialidad_actual'  => $especialidad_actual,
                    'grados'        => $arrGrados,
                    'grado'         => $grado,
                    'idflujo'=>$request->get('id'),
                    'estado'=>$verificarinicioTramite
                ));
            }


    }
    public function guardasolicitudAction(Request $request){
        $datos          = ($request->get('ipt')); //dump($datos);die;
        $id_Institucion = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
        $idsolicitud = $request->get('idsolicitud');
        $flujotipo = $request->get('idflujotipo');
        $em = $this->getDoctrine()->getManager();
        $sw = $request->get('sw');
        if($sw==0){//nuevo

            /**
             * Adecuacion solo para que todas las unidades-BTH puedan realizar su regularizacion
             */
            $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$id_Institucion));
            if($objrehabilitationbth){
                $es_plena = 0;
            }else {
                $query = $em->getConnection()->prepare("SELECT COUNT(ieht.id) as cantidad_ue_plena FROM institucioneducativa_humanistico_tecnico ieht
                                                INNER JOIN institucioneducativa  ie on ieht.institucioneducativa_id=ie.id 
                                                INNER JOIN institucioneducativa_nivel_autorizado iena ON iena.institucioneducativa_id = ieht.institucioneducativa_id  
                                                WHERE ieht.institucioneducativa_id=$id_Institucion AND ie.institucioneducativa_tipo_id=1 AND ie.estadoinstitucion_tipo_id=10 AND iena.nivel_tipo_id = 13
                                                AND ieht.institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                                                ORDER BY 1");
                $query->execute();
                $ue_bth = $query->fetchAll();
                $es_plena=$ue_bth[0]['cantidad_ue_plena']; 
            }
            
            /**
             * Verificicacion de que la UE inicio un tramite
             */
             $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
                                                    AND tr.tramite_tipo <> 31
                                                    AND tr.gestion_id = $gestion");            
            $query->execute();
            $tramite_ue = $query->fetchAll();
            $tramite_iniciado=$tramite_ue[0]['cantidad_tramite_bth'];
        } else{//tramite devuelto
            $tramite_iniciado=0;

            $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$id_Institucion));
            if($objrehabilitationbth){
                $es_plena=0;
            }else{

                $query = $em->getConnection()->prepare("SELECT COUNT(ieht.id) as cantidad_ue_plena FROM institucioneducativa_humanistico_tecnico ieht  
                                                    WHERE ieht.institucioneducativa_id=$id_Institucion 
                                                    ORDER BY 1");
                $query->execute();
                $ue_bth = $query->fetchAll();
                $es_plena=$ue_bth[0]['cantidad_ue_plena'];
                if($es_plena>=1){
                    $es_plena=1;
                }else{
                    $es_plena=0;
                }
            }




        }
         /**
         * verificamos el tipo de solicitud 45 tramite nuevo
         */

        if( trim($request->get('solicitud')) == 'Registro Nuevo' ){
            if($tramite_iniciado < 1){ //si tiene tramite iniciado
                /**
                 * Verificacion de las especialiade de la U.E
                 */
                $query = $em->getConnection()->prepare("SELECT COUNT(*) AS cantidad_especialidades
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $id_Institucion 
                                                ORDER BY 1");
                $query->execute();
                $especialidades = $query->fetchAll();
                $cantidad_especialidades= $especialidades[0]['cantidad_especialidades'];
                $query = $em->getConnection()->prepare("SELECT grado_tipo_id FROM institucioneducativa_humanistico_tecnico WHERE institucioneducativa_id = $id_Institucion and gestion_tipo_id = 2018");
                $query->execute();
                $grado = $query->fetch();
                $grado_id = $grado['grado_tipo_id'];
                if($es_plena==0 or $cantidad_especialidades==0 or $grado_id==4){ // si la ue se  encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico.  si la ue es bth para cualquier gestion

                    $id_rol         = $this->session->get('roluser');
                    //$id_usuario     = $this->session->get('userId');
                    $id_Institucion = $request->get('institucionid');
                    $id_distrito    = $request->get('id_distrito');
                    /***
                     * Adecuacion para obtener el usuario del director.
                     * 2018-  where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                     * where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
                    $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                    join usuario u on m.persona_id=u.persona_id
                    where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    if($uDestinatario){
                        $id_usuario = $uDestinatario[0]['id'];
                    }else{
                        $mensaje="Verificar si el Director de la Unidad Educativa es vigente";
                        return  new JsonResponse(array('estado' => 4, 'msg' => $mensaje));

                    }
                   // dump($uid);die;
                    /**
                     * Obtenemos el flujo_proceso y obtenemos la tarea
                     */
                    $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
                    $tarea          = $flujoproceso->getId();//34 Solicita BTH
                    $tabla          = 'institucioneducativa';
                    $datos          = ($request->get('ipt')); 
                    $id_tipoTramite = $idsolicitud; //Registro Nuevo
                    $idTramite='';
                    //$wfTramiteController = new WfTramiteController();
                    //$this->get('wftramite')->setContainer($this->container);
                    if($sw == 0){//primer envio de solicitud
                        $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,'',$id_distrito);
                    }
                    else{//se hizo la devolucion por el distrital
                        $idTramite = $request->get('id_tramite');
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'','',$idTramite,$datos,'',$id_distrito);
                    }
                    if ($mensaje['dato']==true){
                        $res = 1;
                    }else{
                        $res = 4;
                    }
                }
                else{
                    $res = 2;  //No puede iniciar su trámite como nuevo
                }
            }else{
                $res = 3;
            }
        }
        /**
         * verificamos el tipo de solicitud 46 RATIFICACION
         */
        else{ //dump($tramite_iniciado);dump($es_plena);die;
            if($tramite_iniciado < 1) {
                $query = $em->getConnection()->prepare("SELECT grado_tipo_id FROM institucioneducativa_humanistico_tecnico WHERE institucioneducativa_id = $id_Institucion and gestion_tipo_id = 2018");
                $query->execute();
                $grado = $query->fetch();
                $grado_id = $grado['grado_tipo_id'];
                if ($es_plena > 0 and $grado_id != 4) {// La ue se encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico y le corresponderia tramite de Ratificacion
                    /**
                     * Verificacion de las especialiade de la U.E
                     */
                    $query = $em->getConnection()->prepare("SELECT COUNT(*) AS cantidad_especialidades
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                WHERE ieth.institucioneducativa_id = $id_Institucion 
                                                ORDER BY 1");
                    $query->execute();
                    $especialidades = $query->fetchAll();
                    $cantidad_especialidades = $especialidades[0]['cantidad_especialidades'];
                    if ($cantidad_especialidades > 0){
                        $id_rol = $this->session->get('roluser');
                       // $id_usuario = $this->session->get('userId');
                        $id_Institucion = $request->get('institucionid');
                        $id_distrito = $request->get('id_distrito');
                        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
                        $tarea   = $flujoproceso->getId();//34 Solicita BTH
                        $tabla = 'institucioneducativa';
                        $id_tipoTramite = $idsolicitud;//Ratificacion
                        $idTramite = '';
                        //$wfTramiteController = new WfTramiteController();
                        //$this->get('wftramite')->setContainer($this->container);
                        $datos = ($request->get('ipt')); //dump ($datos);DIE;
                        /***
                         * Adecuacion para obtener el usuario del director.
                         * 2018 ---> where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                         * where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                        */
                        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
                        $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                        join usuario u on m.persona_id=u.persona_id
                        where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                        $query->execute();
                        $uDestinatario = $query->fetchAll();
                        if($uDestinatario){
                            $id_usuario = $uDestinatario[0]['id'];
                        }else{
                            return false;
                        }
                        if ($sw == 0) {//primer envio de solicitud como ratificacion
                            $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', $id_tipoTramite, '', $idTramite, $datos, '', $id_distrito);
                        } else {// se hizo la devolucion de tramite de ratificacion por el distrital
                            $idTramite = $request->get('id_tramite');
                            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', '', $idTramite, $datos, '', $id_distrito);
                            //dump($mensaje);die;
                        }
                        if ($mensaje['dato']==true){
                            $res = 1;
                        }else{
                            $res = 4;
                        }


                    }else{
                        $res = 2;//debe iniciar como  Nuevo ya que es BTH pero no tiene especialidades
                    }
                } else {
                    $res = 2;//debe iniciar su tramite como nuevo
                }
            }else{
                $res = 3; // esq ya inicio un tramite
            }
        }

        /**
         * Adecuacion: se regustra comoo plena a las unidades q inicien su tramite.
         *
         */
        
        if ($res == 1 and (int)$sw == 0) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:InstitucionEducativa')->find($id_Institucion);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();

            $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$id_Institucion));

            if(!$objrehabilitationbth){
                $entity = new InstitucioneducativaHumanisticoTecnico();
                $entity->setGestionTipoId($this->session->get('currentyear'));
                $entity->setInstitucioneducativaId($id_Institucion);
                $entity->setInstitucioneducativa($institucioneducativa->getInstitucioneducativa());
                $entity->setEsimpreso(false);
                $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));//3
                $entity->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));//7
                $em->persist($entity);
                $em->flush();
            }

        }
        if(isset($mensaje['msg'])){
            $mensaje = $mensaje['msg'];
        }else{
            $mensaje = '';
        }
        return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    // function to find UE-s with RehabilitacionBth
    private function getUeRehabilitation($data){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
         // find if UE has RehabilitacionBth data
         $entity = $em->getRepository('SieAppWebBundle:RehabilitacionBth');
            $query = $entity->createQueryBuilder('rbth')
                    ->select('(rbth)')
                    ->where('rbth.institucioneducativaId = :sie')
                    ->andwhere('rbth.fechaFin IS NULL')
                    ->setParameter('sie', $data['sie'])
                    ->getQuery();
            $objrehabilitationbth = $query->getResult();
        // return the find value
            return $objrehabilitationbth;
    }
    public function formularioDirectorAction(Request $request){
        $id_tramite = $request->get('id_tramite');//ID de Tramite
        /*
         * Obtenemios la informacion de la UE
         * */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
       // $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
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
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $informe= $datos[0]['informe'];
        if (!empty($datos[4]['grado'])){
            $documento= empty($datos[5])?'':$datos[5];
        }else{
            $documento= empty($datos[4])?'':$datos[4];
        }
        ///obtenemos la lista de las especialidades de la unidad educativa
        $institucion_id = $infoUE['institucioneducativa_id'];///revisar
        $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucion_id));
        if($objrehabilitationbth){
            $gestion_especialidades = 2019;
        }else{

        
            /// EXTRAER LA ULTIMA GESTION REGISTRADAD COMO BTH ....
            $query = $em->getConnection()->prepare("SELECT gestion_tipo_id as gestion
                                                    FROM institucioneducativa_humanistico_tecnico ieht  
                                                    WHERE ieht.institucioneducativa_id=$institucion_id
                                                    ORDER BY gestion_tipo_id DESC LIMIT 2");
            $query->execute();
            $ultima_gestion_ue = $query->fetchAll();

            if(count($ultima_gestion_ue)==2){
                $gestion_especialidades = $ultima_gestion_ue[1]['gestion'];
            } else{
                $gestion_especialidades = $ultima_gestion_ue[0]['gestion'];
            }
        }
        

        ////////
        $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad,ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $institucion_id AND ieth.gestion_tipo_id = $gestion_especialidades
                                                ORDER BY 1");
        $query->execute();
        $lista_especialidad = $query->fetchAll();
        $lista_especialidadarray = array();
        for($i=0;$i<count($lista_especialidad);$i++){
            /**
             * Verificacion de especialidades
             */
            if ($lista_especialidad[$i]['id'] == 55 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=3 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
            } elseif ($lista_especialidad[$i]['id'] == 45 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=62 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 44 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=63 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 2 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=61 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 9 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=66 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 6 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=4 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
            } else {
                $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'], 'activo'=>false );
            }
        }
        //buscar y armar las especialidades
        $lista_especialidadRegNuearray = array();
        $especialidadinfo = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $lista_especialidadRegNuearray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            array_push($especialidadinfo, $idespecialidad);
        } //dump($especialidadinfo);die;
        /**
         * Cuanto el Trámite es nuevo, envia todas las especialidades que esten vigentes.
         */
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth.es_vigente IS TRUE ORDER BY 2 ");
        $query->execute();
        $especialidadlista = $query->fetchAll();
        $tipoTramite    = $infoUE['tramite_tipo'];
        $tramite_tipo   = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipoTramite)->getTramiteTipo();
        $flujotipo      = $em->getRepository('SieAppWebBundle:Tramite')->find($id_tramite)->getFlujoTipo();

        $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                WHERE ieht.institucioneducativa_id = $institucion_id and institucioneducativa_humanistico_tecnico_tipo_id in(7,1) and gestion_tipo_id = 2018");
                $query->execute();
                $grado = $query->fetch(); //dump($institucion_id);die;

                if($grado){
                     if ((int)$grado['grado_tipo_id'] < 6){                  
                    $grado = (int)$grado['grado_tipo_id']+1;    
                    }else{
                    $grado = (int)$grado['grado_tipo_id'];
                    }
                } else{
                    $grado = 3;
                } 
                $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
               $query = $entity->createQueryBuilder('iec')
                       ->select('(iec.gradoTipo)')
                       ->where('iec.institucioneducativa = :sie')
                       ->andWhere('iec.nivelTipo = :idnivel')
                       ->andwhere('iec.gestionTipo = :gestion')
                       ->andWhere('iec.gradoTipo in (:arrgrados)')
                       ->setParameter('sie', $institucion->getId())
                       ->setParameter('idnivel', 13)
                       ->setParameter('gestion', $gestion)
                       ->setParameter('arrgrados', array(3,4,5,6))
                       ->distinct()
                       ->orderBy('iec.gradoTipo', 'ASC')
                       ->getQuery();
                       // dump($query->getSQL());die;
               $objGrados = $query->getResult();
               $arrGrados = array();
               foreach ($objGrados as $data) {
                   $arrGrados[$data[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data[1])->getGrado();
               }
                $arrGrados = array();
                foreach ($objGrados as $data) {
                    $arrGrados[$data[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data[1])->getGrado();
                }
                $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucion_id));
                if(!$objrehabilitationbth){
                    $gestion = 2018;
                }
                $especialidades_adicionar=$this->obtieneespecialidadesrestantes($institucion_id,$gestion);

                return $this->render('SieHerramientaBundle:SolicitudBTH:formularioBTHDirec.html.twig',array(
                    'ieducativa'    => $infoUe,
                    'institucion'   => $institucion,
                    'gestion'       => $gestion,
                    'ubicacion'     => $ubicacionUe,
                    'director'      => $director,
                    'cursos'        => $cursos,
                    'maestros'      =>$maestros,
                    'especialidad'  =>$lista_especialidadRegNuearray,
                    'especialidadarray' =>$lista_especialidadarray,
                    'especialidadlista' =>$especialidadlista,
                    'especialidadinfo'  =>$especialidadinfo,
                    'informe'       =>$informe,
                    'id_tramite'    =>$id_tramite,
                    'documento'     =>$documento,
                    'tipoTramite'   =>$tipoTramite,
                    'tramite_tipo'  =>trim($tramite_tipo),
                    'flujotipo'     => $flujotipo->getId(),
                    'grado'         =>$grado,
                    'grados'        => $arrGrados,
                    'especialidad_adicionar'  => $especialidades_adicionar,

                ));

    }
    public function ListaEspecialidadesAction(Request $request){
        /**
         * funcion que permite obtener las especialedades en caso de que el tramite sea Ratificacion
         * se obtiene las especialidades de la UE de la ultima gestion registrada en la tabla de
         * institucioneducativa_humanistico_tecnico.
         */
        $id_institucion = $request->get('institucionid');
        $em = $this->getDoctrine()->getManager();
        /**
         * Obtenemos la ultima gestion en la que la UE fue registrada en la tabla
         * institucioneducativa_humanistico_tecnico
         */
        $query = $em->getConnection()->prepare("SELECT  MAX(ieht.gestion_tipo_id)
                                                FROM institucioneducativa_humanistico_tecnico ieht  
                                                 WHERE ieht.institucioneducativa_id=$id_institucion");
        $query->execute();
        $ultima_gestion_registrada = $query->fetchAll();
        $gestion=$ultima_gestion_registrada[0]['max']; // ultima gestion en que fue registrada la UE como BTH

        if($gestion != null){//Si la UE nunca fue declarada como BTH
            $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad, ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion
                                                ORDER BY 1");
            $query->execute();
            $lista_especialidad = $query->fetchAll();
            $lista_especialidadarray = array();
            //dump($lista_especialidad);die;
            for($i=0;$i<count($lista_especialidad);$i++) {
                if ($lista_especialidad[$i]['id'] == 55 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=3 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
                } elseif ($lista_especialidad[$i]['id'] == 45 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=62 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 44 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=63 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 2 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=61 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 9 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=66 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 6 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=4 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
                } else {
                    $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'], 'activo'=>false );
                }
            }
            $lista_especialidadarray = array_unique($lista_especialidadarray, SORT_REGULAR);
            return new JsonResponse($lista_especialidadarray);
        }else{
             $lista_especialidadarray=array();
             return new JsonResponse($lista_especialidadarray);
        }
     }
    //Distrital
    public function  VerSolicitudBTHDisAction(Request $request){
         $id_tramite = $request->get('id');//ID de Tramite
         $em = $this->getDoctrine()->getManager();
         $query = $em->getConnection()->prepare("select td.*
                                                 from tramite t
                                                 join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
         $query->execute();
         $td = $query->fetchAll();
         $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
             if($tramiteDetalle->getTramiteEstado()->getId()==4){
                 return $this->redirectToRoute('solicitud_bth_formularioDis',array('lista_tramites_id'=>$request->get('id')));
             }
        
         // Obtenemios la informacion de la UE
         
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                    FROM tramite trm 
                                                    INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                    INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                    WHERE trm.id=$id_tramite
                                                    ORDER BY wfsol.id DESC limit 1");
            $query->execute();
            $infoUE = $query->fetch();
            $institucion  = $infoUE['institucioneducativa_id'];
            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
            $query = $repository->createQueryBuilder('inss')
                ->select('max(inss.gestionTipo)')
                ->where('inss.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion)
                ->getQuery();
            $inss = $query->getResult();
             $gestion = $inss[0][1];
            // $gestion = 2018;
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
            $query = $repository->createQueryBuilder('ie')
                ->select('ie, ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();
            $infoUe = $query->getResult();
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
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();
            $ubicacionUe = $query->getSingleResult();
            
             // obtenemos datos de la unidad educativa
             
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            // Lista de cursos institucioneducativa
            $query = $em->createQuery(
                'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                        WHERE iec.institucioneducativa = :idInstitucion
                        AND iec.gestionTipo = :gestion
                        AND iec.nivelTipo IN (:niveles)
                        ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
            $cursos = $query->getResult();
            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            //$maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
            //dump ($maestros);die;
               //no hay maestros inscritos para la gestion 2019
             //$gestion=2018;
            $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
            $query = $repository->createQueryBuilder('mins')
                ->select('per.carnet, per.paterno, per.materno, per.nombre')
                ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                ->where('mins.institucioneducativa = :idInstitucion')
                ->andWhere('mins.gestionTipo = :gestion')
                ->andWhere('mins.cargoTipo IN (:cargo)')
                ->andWhere('mins.esVigenteAdministrativo = :esvigente')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargo', array(1, 12))
                ->setParameter('esvigente',true)
                ->setMaxResults(1)
                ->getQuery();
            $director = $query->getOneOrNullResult();
            //dump($director);die;
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                ->where('t.id =' . $id_tramite)
                ->orderBy('wf.id', 'desc')
                ->setMaxResults('1')
                ->getQuery()
                ->getResult();

            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
            //dump($datos);die;
            $informe= $datos[0]['informe'];
            $especialidadarray = array();
            for($i=0;$i<count($datos[2]['select_especialidad']);$i++) {
                $idespecialidad = $datos[2]['select_especialidad'][$i];
                $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
                $query->execute();
                $especialidad = $query->fetch();
                $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
                $especialidadifno[$i] = $idespecialidad;
            }
            /*Obtenemos el Grado  al que le corresponde en la presente gestión*/
                
            $institucion_id = (int)$institucion->getId();    
            // $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
            //         WHERE ieht.institucioneducativa_id = $institucion_id and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)and gestion_tipo_id = 2018");
            //         $query->execute();
            //         $grado = $query->fetch(); //dump($grado);die;

            //         if($grado){
            //              if ((int)$grado['grado_tipo_id'] < 6){                  
            //             $grado = (int)$grado['grado_tipo_id']+1;    
            //             }else{
            //             $grado = (int)$grado['grado_tipo_id'];
            //             }
            //         } else{
            //             $grado = 3;
            //         } 
            return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDis.html.twig',array(
                'ieducativa' => $infoUe,
                'institucion' => $institucion,
                'gestion' => $gestion,
                'ubicacion' => $ubicacionUe,
                'director' => $director,
                'cursos'   => $cursos,
                //'maestros'=>$maestros,
                'especialidadarray'=>$especialidadarray,
                'informe'=>$informe,
                'id_tramite'=>$id_tramite,
                // 'grado'=>$grado
                'grado'=>$datos[3]['grado']
                ));
    }
    public function guardasolicitudDepAction(Request $request){
        $documento = $request->files->get('docpdf');
        /*Validacion para que guarde el docuemnto*/
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid');
            //dump($root_bth_path);die;
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            // $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/adielimespec/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }

        /*if(!empty($documento)){            
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }*/

        $em = $this->getDoctrine()->getManager();
          $id_rol= $this->session->get('roluser');
          $id_usuario= $this->session->get('userId');
          $institucionid    = $request->get('institucionid');
          $id_distrito      = (int)$request->get('id_distrito');
          $evaluacion       = $request->get('evaluacion');
          $id_tramite       = $request->get('id_tramite');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($id_tramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea   = $flujoproceso->getId();//35//RECEPCIONA
          $tabla            = 'institucioneducativa';
          $obs              = $request->get('obstxt');
          $datos = json_decode($request->get('ipt'));
          array_push($datos, $imagen);
          $datos = json_encode($datos);
          //ELABORA INFORME
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH
        $res = 1;
        $msg = "";
          try{
              $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$id_tramite,$datos,'',$id_distrito);
              if ($mensaje['dato']==true){
                  if ($evaluacion=='SI')
                  {
                      $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$id_tramite); 
                          if($mensaje['dato']==true){
                            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$id_tramite,$datos,'',$id_distrito);
                            if($mensaje['dato']==true){
                                $res =1;
                            }else{
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($id_tramite);
                            $respuesta = $this->get('wftramite')->eliminarTramteEnviado($id_tramite,$id_usuario);
                            //dump($mensaje);die;
                            $mensaje=$mensaje['msg'];
                            $res = 4;
                            }
                          }else{
                            $respuesta = $this->get('wftramite')->eliminarTramteEnviado($id_tramite,$id_usuario);
                            $mensaje=$mensaje['msg'];
                            $res = 4;
                          }
                  }
              }else{
                  $mensaje=$mensaje['msg'];
                  $res = 4;
              }
          }
          catch (Exception $exceptione){
              $res = 0;
          }
         return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    public function FormularioBTHDisAction(Request $request){
        $id_tramite = $request->get('lista_tramites_id');//ID de Tramite
        /**
         * Obtenemos la informacion de la Unidad Educativa
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        //$gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
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
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019
        //$gestion = 2018; //borrar
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $gradoSelected = isset($datos[5]['grado'])?$datos[5]['grado']:'';
        // dump($datos[5]['grado']);die;
        /**
         * EL DEPARTAMENTO YA NO ENVIA UN INFORME EN CASO DE DEVOLUCION)
         */
         $informe= $datos[0]['informe'];
          if (!empty($datos[4]['grado'])){
                $documento= empty($datos[5])?'':$datos[5]; 
            } else{
                $documento= empty($datos[4])?'':$datos[4];
            }
         $especialidadarray = array();
         for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
             $idespecialidad = $datos[2]['select_especialidad'][$i];
             $query = $em->getConnection()->prepare("SELECT eth.id, eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
             $query->execute();
             $especialidad = $query->fetch();
             $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
             $especialidadifno[$i] = $idespecialidad;
         }
        /*lista del catalogo de especialidades */
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth ORDER BY 1 ");
        $query->execute();
        $especialidad = $query->fetchAll();
        /*Obtenemos el grado a cual le corresponde en la presente gestion*/
        $institucion_id = (int)$institucion->getId();    
        $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                WHERE ieht.institucioneducativa_id = $institucion_id and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)and gestion_tipo_id = 2018");
                $query->execute();
                $grado = $query->fetch(); //dump($institucion_id);die;

                if ($gradoSelected == 3 or $gradoSelected == 4) {
                    $estado_grado_tipo = 7;
                } elseif ($gradoSelected == 5 or $gradoSelected == 6) {
                    $estado_grado_tipo = 1;
                }
        return $this->render('SieHerramientaBundle:SolicitudBTH:FormularioBTHDis.html.twig',array(
            'ieducativa'    => $infoUe,
            'institucion'   => $institucion,
            'gestion'       => $gestion,
            'ubicacion'     => $ubicacionUe,
            'director'      => $director,
            'cursos'        => $cursos,
            'maestros'      =>$maestros,
            'especialidadarray'=>$especialidadarray,
            'informe'       =>$informe,
            'id_tramite'    =>$id_tramite,
            'documento'     =>$documento,
            'grado'         =>$gradoSelected
        ));
    }
    //Departamental
    public function VerSolicitudBTHDepAction(Request $request){
        $id_tramite = $request->get('id');//ID de Tramite
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
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
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019
        //$gestion = 2018; //borrar
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true); 
        // dump($datos);die; 
        if (!empty($datos[4]['grado'])){
           $documento= empty($datos[5])?'':$datos[5]; 
        } else{
            $documento= empty($datos[4])?'':$datos[4];
        }     
        $informe= $datos[0]['informe'];
        //$documento= empty($datos[4])?'':$datos[4];
        $especialidadarray = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            $especialidadifno[$i] = $idespecialidad;
        }
        /*Obtenemos el grado al que le corresponde*/
        $institucion_id = (int)$institucion->getId();    
        // $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
        //         WHERE ieht.institucioneducativa_id = $institucion_id and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)and gestion_tipo_id = 2018");
        //         $query->execute();
        //         $grado = $query->fetch(); //dump($institucion_id);die;

        //         if($grado){
        //             if ((int)$grado['grado_tipo_id'] < 6){                  
        //                 $grado = (int)$grado['grado_tipo_id']+1;    
        //             } else {
        //                 $grado = (int)$grado['grado_tipo_id'];
        //             }
        //         } else{
        //             $grado = 3;
        //         } 

        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDep.html.twig',array(
            'ieducativa'    => $infoUe,
            'institucion'   => $institucion,
            'gestion'       => $gestion,
            'ubicacion'     => $ubicacionUe,
            'director'      => $director,
            'cursos'        => $cursos,
            'maestros'      => $maestros,
            'especialidadarray'=> $especialidadarray,
            'informe'       => $informe,
            'id_tramite'    => $id_tramite,
            'documento'     => $documento,
            // 'grado'         => $grado
            'grado'=>$datos[4]['grado']
        ));

    }
    public function guardasolicitudDepartamentalAction(Request $request){
        $documento = $request->files->get('docpdf');
        /*Validacion para que guarde el docuemnto*/
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid');
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0775);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            // $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/adielimespec/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0775);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        /*
        if(!empty($documento)){
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }*/


        $em = $this->getDoctrine()->getManager();
        $id_rol         = $this->session->get('roluser');
        $id_usuario     = $this->session->get('userId');
        $institucionid  = $request->get('institucionid');
        $id_distrito    = $request->get('id_distrito');
        $evaluacion     = $request->get('evaluacion');
        $evaluacion2     = $request->get('evaluacion2');
        $idtramite     = $request->get('id_tramite');
       // dump($evaluacion,$evaluacion2);die;

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idtramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea   = $flujoproceso->getId();//35//RECEPCIONA
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$idtramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $datos = json_decode($request->get('ipt'));
        $datosAux = json_decode($request->get('ipt'),true);
        $gradoSelected = $datosAux[5]['grado'];
        
        array_push($datos, $imagen);
        $datos = json_encode($datos);
        $obs            = $request->get('obstxt');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH DEPARTAMENTO - ORDEN 5
        $tabla          = 'institucioneducativa';
        //$wfTramiteController = new WfTramiteController();
        //$this->get('wftramite')->setContainer($this->container);
        $res = 1;
        $msg = "";
        try {
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);
            if($mensaje['dato'] == true) {
                if ($evaluacion == 'SI') {   //dump($tarea1);die;
                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
                    if($mensaje['dato'] == true) {
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,$obs,'',$idtramite,$datos,'',$id_distrito);
                        if($mensaje['dato'] == true) {
                            //volcado a la base de datoa
                            //Recuperamos los datos del tramite
                            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                                ->select('wf')
                                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                                ->where('t.id =' . $idtramite)
                                ->orderBy('wf.id', 'desc')
                                ->setMaxResults('1')
                                ->getQuery()
                                ->getResult();
                            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);

                            /*Recuperamos datos de la UE*/
                            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
                            $query = $repository->createQueryBuilder('inss')
                                ->select('max(inss.gestionTipo)')
                                ->where('inss.institucioneducativa = :idInstitucion')
                                ->setParameter('idInstitucion', $institucionid)
                                ->getQuery();
                            $inss = $query->getResult();
                            $gestiontipo = $inss[0][1];

                            //$gestiontipo=2018; //pase las inscripciones
                            $query = $em->getConnection()->prepare("SELECT * from institucioneducativa  ie 
                                INNER JOIN institucioneducativa_sucursal ies on ies.institucioneducativa_id= ie.id
                                WHERE ie.id = $institucionid and ies.gestion_tipo_id= $gestiontipo ");
                            $query->execute();
                            $datosUe  = $query->fetch();

                            /*Una vez finalizado el tramite se registra el la tabla correspondiente 45 = Nuevo Registro 46 = Ratificacion*/
                            if($tramite->getTramiteTipo()->getTramiteTipo() == 'Registro Nuevo'){
                                //modificacion par el grado
                                // $query = $em->getConnection()->prepare("SELECT *
                                //     FROM institucioneducativa_humanistico_tecnico ieht  
                                //     WHERE ieht.institucioneducativa_id=$institucionid and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)and gestion_tipo_id =2018");
                                // $query->execute();
                                // $ultima_gestion_ue = $query->fetchAll();
                                // if(count($ultima_gestion_ue) == 1) {
                                //     $grado_tipo_id = $ultima_gestion_ue[0]['grado_tipo_id']+1;
                                //     if ($grado_tipo_id == 3 or $grado_tipo_id == 4) {
                                //         $estado_grado_tipo = 7;
                                //     } elseif ($grado_tipo_id == 5 or $grado_tipo_id == 6) {
                                //         $estado_grado_tipo = 1;
                                //     }
                                //     if($grado_tipo_id==7) {
                                //         $grado_tipo_id=6;
                                //         $estado_grado_tipo = 1;
                                //     }
                                // } else{
                                //     $estado_grado_tipo = 7;
                                //     $grado_tipo_id = 3;
                                // }                                
                                if ($gradoSelected == 3 or $gradoSelected == 4) {
                                    $estado_grado_tipo = 7;
                                } elseif ($gradoSelected == 5 or $gradoSelected == 6) {
                                    $estado_grado_tipo = 1;
                                }
                                
                                //Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                                $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));

                                // $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));//3
                                $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoSelected));//3
                                $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                                $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                                $em->persist($institucionBth);
                                $em->flush();

                                for($i=0; $i<count($datos[2]['select_especialidad']); $i++) {
                                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                                    $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                                    $idespecialidad = $datos[2]['select_especialidad'][$i];
                                    $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                                    $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                                    $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                                    $entity->setInstitucioneducativa($ue);
                                    $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                                    $entity->setGestionTipo($gestiontipo);
                                    $entity->setFechaRegistro(new \DateTime(date('Y-m-d')));
                                    $em->persist($entity);
                                    $em->flush();
                                }
                                 $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucionid));
                                 

                                 if($objrehabilitationbth){
                                    $rehabilitacion = $em->getRepository('SieAppWebBundle:RehabilitacionBth')->find($objrehabilitationbth[0][1]);
                                    $rehabilitacion->setFechaFin(new \DateTime(date('Y-m-d')));
                                    $em->persist($rehabilitacion);
                                    $em->flush();
                                 }
                            } else {
                                // En caso de ratificacion
                                // Se consulta si la UE quiere ratificar para una gestion nueva o en la misma gestion
                                // Si es en la misma gestion solo se actualiza el campo de esimpreso en la tabla de
                                // institucioneducativa_humanistico_tecnico
                                // Si es en una gestion distinta se hace un nuevo registro en la misma tabla pero
                                // con la gestion actual

                                //Query que obtiene la ultima gestion donde la UE hizo su registro como BTH
                                $query = $em->getConnection()->prepare("SELECT gestion_tipo_id as gestion, grado_tipo_id
                                    FROM institucioneducativa_humanistico_tecnico ieht                                    WHERE ieht.institucioneducativa_id=$institucionid
                                    ORDER BY gestion_tipo_id DESC LIMIT 2");
                                $query->execute();
                                $grado_ue = $query->fetchAll();
                                $ultima_gestion = $grado_ue[1]['gestion'];
                                $gestion_actual =  $request->getSession()->get('currentyear');
                                if ($ultima_gestion<$gestion_actual) {
                                        if ($gradoSelected == 3 or $gradoSelected == 4) {
                                                $estado_grado_tipo = 7;
                                        } elseif ($gradoSelected == 5 or $gradoSelected == 6) {
                                            $estado_grado_tipo = 1;
                                        }

                                    //Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                                    $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                                    $institucionBth->setGestionTipoId($this->session->get('currentyear'));
                                    $institucionBth->setInstitucioneducativaId($institucionid);
                                    $institucionBth->setInstitucioneducativa($datosUe['institucioneducativa']);
                                    $institucionBth->setEsimpreso(false);
                                    $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoSelected));//3
                                    $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                                    $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                                    $em->persist($institucionBth);
                                    $em->flush();
                                    // check if the Ue has RehabilitacionBth data
                                    $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucionid));
                                    // if the UE has RehabilitacionBth data remove the specialities
                                    /*if($objrehabilitationbth){
                                        ///remove the speciality to save the new specialities
                                        $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(array(
                                            'institucioneducativa'=> $institucionid,
                                            'getsttionTipo' => $request->getSession()->get('currentyear')
                                        ));
                                        foreach ($objSpeciality as $key => $value) {
                                            # code...
                                            $em->remove($value);
                                        }
                                        $em->flush();
                                    }*/

                                    for ($i = 0; $i < count($datos[2]['select_especialidad']); $i++) {
                                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                                        $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                                        $idespecialidad = (int)$datos[2]['select_especialidad'][$i];
                                        $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                                        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                                        $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                                        $entity->setInstitucioneducativa($ue);
                                        $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                                        $entity->setGestionTipo($gestiontipo);
                                        $entity->setFechaRegistro(new \DateTime(date('Y-m-d')));
                                        $em->persist($entity);
                                        $em->flush();
                                    }
                                   $objrehabilitationbth = $this->getUeRehabilitation(array('sie'=>$institucionid));

                                 if($objrehabilitationbth){
                                    $gestion_buscar = 2019;
                                 } else {
                                    $gestion_buscar = 2018;
                                 }

                                 $query = $em->getConnection()->prepare("SELECT especialidad_tecnico_humanistico_tipo_id from institucioneducativa_especialidad_tecnico_humanistico WHERE institucioneducativa_id = $institucionid and gestion_tipo_id = $gestion_buscar");
                                $query->execute();
                                $especialidades_adicionar_bth = $query->fetchAll();
                                foreach ($especialidades_adicionar_bth as $key => $value) {
                                  $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                                        $idespecialidad = $value['especialidad_tecnico_humanistico_tipo_id'];
                                        $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                                        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                                        $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                                        $entity->setInstitucioneducativa($ue);
                                        $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                                        $entity->setGestionTipo($gestiontipo);
                                        $entity->setFechaRegistro(new \DateTime(date('Y-m-d')));
                                        $em->persist($entity);
                                        $em->flush();
                                }

                                

                                } 
                            }
                            // fin else
                            // FIN DE volcado a la base de datos
                        } else {
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                            $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$id_usuario);
                            $msg = $mensaje['msg'];
                            $res = 4;
                        }
                    } else {
                        $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite,$id_usuario);
                        $msg = $mensaje['msg'];
                        $res = 4;
                    }
                } else {
                    // si la evaluacion es NO

                    $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 6));
                    $tarea2   = $flujoproceso->getId();//6 realiza observacion
                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                    if($mensaje['dato'] == true) {
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,$obs,$evaluacion2,$idtramite,$datos,'',$id_distrito);
                        if($mensaje['dato'] == true) {
                            // Al dar como respuesta NO se elimina el registro de la UE de la tabla InstitucioneducativaHumanisticoTecnico y
                            // no se registran las especialidades
                            if($evaluacion2 =='NO') {
                                $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                                if($institucionBth) {
                                    $em->remove($institucionBth);
                                    $em->flush();
                                }
                            }
                            $msg = $mensaje['msg'];
                        } else {
                            $respuesta = $this->get('wftramite')->eliminarTramiteRecibido($id_tramite);
                            $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite,$id_usuario);
                            $msg = $mensaje['msg'];
                            $res = 4;
                        }
                    } else {
                        $respuesta = $this->get('wftramite')->eliminarTramiteEnviado($id_tramite,$id_usuario);
                        $msg = $mensaje['msg'];
                        $res = 4;
                    }
                }

            } else {
                //si existe error en el primer guardar tramite
                $msg = $mensaje['msg'];
                $res = 4;
            }
        } catch (Exception $exceptione){
            $res = 0;
        }
        return new JsonResponse(array('estado' => $res, 'msg' => $msg));
    }
    
}
