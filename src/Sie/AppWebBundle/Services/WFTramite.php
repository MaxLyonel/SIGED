<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Services\Funciones;
use Sie\AppWebBundle\Services\Notas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;

class WFTramite {

	protected $em;
	protected $router;
    protected $session;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
        $this->session = new Session();
	}

    /**
     * funcion general para guardar un nuevo tramite
     */
    public function guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    {
        //dump($lugarTipoLocalidad_id,$lugarTipoDistrito_id);die;
        
        $flujoproceso = $this->em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $this->em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(15);
        $flujotipo = $this->em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
        $tramitetipo = $this->em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
        
        $this->em->getConnection()->beginTransaction();
        try {
            /**
            * insert tramite
            */
            $tramite = new Tramite();
            $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
            $tramite->setFlujoTipo($flujotipo);
            $tramite->setTramiteTipo($tramitetipo);
            $tramite->setFechaTramite(new \DateTime(date('Y-m-d')));
            $tramite->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramite->setEsactivo(true);
            $tramite->setGestionId((new \DateTime())->format('Y'));
            switch ($tabla) {
                case 'institucioneducativa':
                    if ($id_tabla){
                        $institucioneducativa = $this->em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                        $tramite->setInstitucioneducativa($institucioneducativa);
                    }
                    break;
                case 'estudiante_inscripcion':
                    $estudiante = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id_tabla);
                    $tramite->setestudianteInscripcion($estudiante);
                    break;
                case 'apoderado_inscripcion':
                    $apoderado = $this->em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($id_tabla);
                    $tramite->setApoderadoInscripcion($apoderado);
                    break;
                case 'maestro_inscripcion':
                    $maestro = $this->em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id_tabla);
                    $tramite->setMaestroInscripcion($maestro);
                    break;
            }
            $this->em->persist($tramite);
            $this->em->flush();
            /**
            * insert tramite_detalle 
            */
            $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
            $tramiteDetalle = new TramiteDetalle();    
            $tramiteDetalle->setTramite($tramite);
            $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFlujoProceso($flujoproceso);
            $tramiteDetalle->setUsuarioRemitente($usuario);
            $tramiteDetalle->setObs(mb_strtoupper($observacion, 'UTF-8'));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $this->em->persist($tramiteDetalle);
            $this->em->flush();

            /**
            * insert datos propios de la solicitud
            */
            if ($datos){
                $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();   
                $wfSolicitudTramite = new WfSolicitudTramite();
                $wfSolicitudTramite->setTramiteDetalle($tramiteDetalle);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoLocalidadId((int)$lugarTipoLocalidad_id?$lugarTipoLocalidad_id:null);
                $wfSolicitudTramite->setLugarTipoDistritoId((int)$lugarTipoDistrito_id?$lugarTipoDistrito_id:null);
                //dump($wfSolicitudTramite);die;
                $this->em->persist($wfSolicitudTramite);
                $this->em->flush();
            }
            if ($flujoproceso->getEsEvaluacion() == true) 
            {
                $tramiteDetalle->setValorEvaluacion($varevaluacion);
                $wfcondiciontarea = $this->em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
                $tarea_sig_id = $wfcondiciontarea[0]->condicionTareaSiguiente();
            }else{
                $tarea_sig_id = $flujoproceso->getTareaSigId();
            }
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
            
            if($uDestinatario == false){
                $this->em->getConnection()->rollback();
                $mensaje['dato'] = false;
                $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                return $mensaje;
            }else{
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }
            $this->em->flush();
            /**
            * actualizamos ultima tarea del tramite
            */
            $tramite->setTramite($tramiteDetalle->getId());
            $this->em->flush();
            $this->em->getConnection()->commit();
            $mensaje['dato'] = true;
            $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se guardó correctamente';
            $mensaje['idtramite'] = $tramite->getId();
            return $mensaje;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Ocurrio un error al guardar el trámite.!';
            return $mensaje;    
        }
    }

    /**
     * funcion general para guardar una tarea de un tramite
     */
    public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    {
        $flujoproceso = $this->em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $this->em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $this->em->getConnection()->beginTransaction();
        try {
            /**
            * asigana usuario destinatario
            */
            if ($flujoproceso->getEsEvaluacion() == true) 
            {
                $tramiteDetalle->setValorEvaluacion($varevaluacion);
                //dump($tramiteDetalle);die;
                $wfcondiciontarea = $this->em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
                //dump($wfcondiciontarea);die;
                if ($wfcondiciontarea[0]->getCondicionTareaSiguiente() != null){
                    $tarea_sig_id = $wfcondiciontarea[0]->getCondicionTareaSiguiente();
                    $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                    //dump($uDestinatario);die;
                    if($uDestinatario == false){
                        //dump($uDestinatario);die;
                        $this->em->getConnection()->rollback();
                        $mensaje['dato'] = false;
                        $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                        return $mensaje;
                    }else{
                        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
                    }
                    
                }else{
                    // si despues de la evaluacion termina el tramite
                    $tarea_sig_id = null;
                }
            }else{
                if ($flujoproceso->getTareaSigId() != null){
                    $tarea_sig_id = $flujoproceso->getTareaSigId();
                    $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                    if($uDestinatario == false){
                        $this->em->getConnection()->rollback();
                        $mensaje['dato'] = false;
                        $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                        return $mensaje;
                    }else{
                        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
                    }
                }else{
                    $tarea_sig_id = null;
                }
            }
            /**
            * guarda tramite enviado/devuelto
            */
            if (($flujoproceso->getTareaSigId() != null and $flujoproceso->getEsEvaluacion() == false ) or ($tarea_sig_id != null and $flujoproceso->getEsEvaluacion() == true)){
                if($tarea_sig_id > $flujoproceso->getId()){
                    $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
                }else{
                    $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(4); //devuelto
                }
            }else{
                $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
            }
            $tramiteDetalle->setObs(mb_strtoupper($observacion,'UTF-8'));
            $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $this->em->flush();
        
            /**
            * inserta datos propios de la solicitud en esta tarea
            */
            if ($datos){
                $wfDatos = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                            ->select('wf')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                            ->where('fp.id =' . $tarea)
                            ->andwhere('t.id =' . $idtramite)
                            ->andwhere('wf.esValido =true')
                            ->getQuery()
                            ->getResult();
                if($wfDatos){
                    $wfDatos[0]->setEsValido(false);
                    $wfDatos[0]->setFechaModificacion(new \DateTime(date('Y-m-d H:i:s')));
                }
                $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();   
                $wfSolicitudTramite = new WfSolicitudTramite();
                $wfSolicitudTramite->setTramiteDetalle($tramiteDetalle);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoLocalidadId($lugarTipoLocalidad_id?(int)$lugarTipoLocalidad_id:null);
                $wfSolicitudTramite->setLugarTipoDistritoId($lugarTipoDistrito_id?(int)$lugarTipoDistrito_id:null);
                $this->em->persist($wfSolicitudTramite);
                $this->em->flush();
            }
            /**
             * si es la ultima tarea del tramite se finaliza el tramite
             */
            if (($flujoproceso->getTareaSigId() == null and $flujoproceso->getEsEvaluacion() == false ) or ($tarea_sig_id == null and $flujoproceso->getEsEvaluacion() == true))
            {
                $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
                $this->em->flush();
                $mensaje['msg'] = 'TOME NOTA, el trámite Nro. '. $tramite->getId() .' a finalizado.';
            }else{
               $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se envió correctamente.';
            }
            $this->em->getConnection()->commit();
            $mensaje['dato'] = true;
            return $mensaje;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Ocurrio un error al enviar el trámite.!';
            return $mensaje;
        }
    }

    /**
     * funcion q guarda un tramite como recibido
     */
    public function guardarTramiteRecibido($usuario,$tarea,$idtramite)
    {

        $flujoproceso = $this->em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $this->em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(3);
        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        
        $this->em->getConnection()->beginTransaction();
        try {
            /**
             * guarda tramite recibido
            */
            $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
            $tramiteDetalle = new TramiteDetalle();    
            $tramiteDetalle->setTramite($tramite);
            $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $tramiteDetalle->setFlujoProceso($flujoproceso);
            $tramiteDetalle->setUsuarioRemitente($usuario);
            $tramiteDetalle->setUsuarioDestinatario($usuario);
            /**
            * Guardamos tarea anterior en tramite detalle  
            */
            $td_anterior = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tramiteDetalle->setTramiteDetalle($td_anterior);
            $this->em->persist($tramiteDetalle);
            $this->em->flush();
            $tramite->setTramite($tramiteDetalle->getId());
            $this->em->flush();
            $this->em->getConnection()->commit();
            $mensaje['dato'] = true;
            $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se recibió correctamente';
            return $mensaje;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = 'Ocurrio un error al guardar el trámite.';
            return $mensaje;
        }
    }

    public function eliminarTramiteNuevo($idtramite)
    {

        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $wfSolicitudTramite = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->findOneBy(array('tramiteDetalle'=>$tramiteDetalle->getId()));
        $this->em->remove($wfSolicitudTramite);
        $this->em->remove($tramiteDetalle);
        $this->em->remove($tramite);
        $this->em->flush();
        return true;
    }
    public function eliminarTramiteRecibido($idtramite)
    {
        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramite->setTramite($tramiteDetalle->getTramiteDetalle()->getId());
        $this->em->flush();
        $this->em->remove($tramiteDetalle);
        $this->em->flush();
        return true;
    }

    public function eliminarTramteEnviado($idtramite,$idusuario)
    {
        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramiteDetalle->setValorEvaluacion(null);
        $tramiteDetalle->setUsuarioDestinatario($this->em->getRepository('SieAppWebBundle:Usuario')->find($idusuario));
        $tramiteDetalle->setObs(null);
        $tramiteDetalle->setFechaEnvio(null);
        $tramiteDetalle->setTramiteEstado($this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(3));
        $this->em->flush();
        $query = $this->em->getConnection()->prepare("delete from wf_solicitud_tramite where tramite_detalle_id =". $tramiteDetalle->getId());
        $query->execute();
        $wfDatos = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                    ->select('wf')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                    ->where('fp.id =' . $tramiteDetalle->getFlujoProceso()->getId())
                    ->andwhere('t.id =' . $idtramite)
                    ->andwhere('wf.esValido =false')
                    ->getQuery()
                    ->getResult();
        if($wfDatos){
            $wfDatos[0]->setEsValido(true);
            $wfDatos[0]->setFechaModificacion(null);
        }
        return true;
    }

}