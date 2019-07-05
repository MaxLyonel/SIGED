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
        
        if( !$usuario or $tramiteDetalle->getUsuarioRemitente()->getId() != $usuario->getId()){
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Error, tramite no enviado pues el usuario remitente no corresponde.!';
            return $mensaje;
        }

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
     * funcion que guarda un tramite como recibido
     */
    public function guardarTramiteRecibido($usuario,$tarea,$idtramite)
    {

        $flujoproceso = $this->em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $this->em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $this->em->getRepository('SieAppWebBundle:TramiteEstado')->find(3);
        $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        
        $verifica = $this->verificaUsuarioRemitente($usuario,$flujoproceso,$tramite);
        if($verifica == false){
            $mensaje['dato'] = false;
            $mensaje['msg'] = 'El usuario, no corresponde para recibir la tarea <strong>'. $flujoproceso->getProceso()->getProcesoTipo() . '</strong>.';
            return $mensaje;
        }
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
    
    /**
     * funcion que elimina tramite nuevo
     */
    public function eliminarTramiteNuevo($idtramite)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
            $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $wfSolicitudTramite = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->findOneBy(array('tramiteDetalle'=>$tramiteDetalle->getId()));
            $this->em->remove($wfSolicitudTramite);
            $this->em->remove($tramiteDetalle);
            $this->em->remove($tramite);
            $this->em->flush();
            $this->em->getConnection()->commit();
            return true;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            return false;
        }
    }
    
    /**
    * funcion que elimina tramite recibido
    */
    public function eliminarTramiteRecibido($idtramite)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $tramite = $this->em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
            $tramiteDetalle = $this->em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tramite->setTramite($tramiteDetalle->getTramiteDetalle()->getId());
            //dump($tramite,$tramiteDetalle);die;
            $this->em->persist($tramite);
            $this->em->flush();
            $this->em->remove($tramiteDetalle);
            $this->em->flush();
            //dump($tramite);die;
            $this->em->getConnection()->commit();
            return true;
            
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            return false;
        }
        
    }

    /**
    * funcion que elimina tramite enviado
    */
    public function eliminarTramteEnviado($idtramite,$idusuario)
    {
                
        $this->em->getConnection()->beginTransaction();
        try {
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

            $this->em->getConnection()->commit();
            return true;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            return false;
        }
        
    }

    /**
     * funcion para asignar el usuario destinatario de la tarea actual
     */
    public function obtieneUsuarioDestinatario($tarea_actual,$tarea_sig_id,$id_tabla,$tabla,$tramite)
    {
        
        $flujoprocesoSiguiente = $this->em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea_sig_id);
        $nivel = $flujoprocesoSiguiente->getRolTipo()->getLugarNivelTipo();
        //dump($nivel);die;
        switch ($tabla) {
            case 'institucioneducativa':
                if ($tramite->getInstitucioneducativa()){
                    $institucioneducativa = $this->em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                    $lugar_tipo_distrito = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
                    $lugar_tipo_departamento = $institucioneducativa->getleJuridicciongeografica()->getlugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugartipo()->getCodigo();
                }else{
                    $wfdatos = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                        ->select('wfd')
                        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                        ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                        ->where('td.tramite='.$tramite->getId())
                        ->andWhere("fp.orden=1")
                        ->andWhere("wfd.esValido=true")
                        ->getQuery()
                        ->getResult();
                    $lugar_tipo_distrito = $wfdatos[0]->getLugarTipoDistritoId();
                    $lt = $this->em->getRepository('SieAppWebBundle:LugarTipo')->find($lugar_tipo_distrito);
                    $lugar_tipo_departamento = $lt->getLugarTipo()->getCodigo();
                }
                break;
            case 'estudiante_inscripcion':
                break;
            case 'apoderado_inscripcion':
                break;
            case 'maestro_inscripcion':
                break;
        }

        switch ($nivel->getId()) {
            case 7:   // Distrito
                //dump($lugar_tipo_distrito);die;
                $query = $this->em->getConnection()->prepare("select * from wf_usuario_flujo_proceso where flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and esactivo is true and  lugar_tipo_id=".$lugar_tipo_distrito);
                $query->execute();
                $uDestinatario = $query->fetchAll();
                if($uDestinatario){
                    if(count($uDestinatario)>1){
                        $uid = $this->asignaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$uDestinatario[0]['lugar_tipo_id']);
                    }else{
                        $uid = $uDestinatario[0]['usuario_id'];
                    }
                }else{
                    return false;
                }
                
                break;
            case 6:   // Departamento
            case 8:
                //dump($lugar_tipo_departamento);die;
                $query = $this->em->getConnection()->prepare("select ufp.* from wf_usuario_flujo_proceso ufp join lugar_tipo lt on ufp.lugar_tipo_id=lt.id where ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and ufp.esactivo is true and cast(lt.codigo as int)=".$lugar_tipo_departamento);
                $query->execute();
                $uDestinatario = $query->fetchAll();
                if($uDestinatario){
                    //dump($uDestinatario);die;
                    if(count($uDestinatario)>1){
                        $uid = $this->asignaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$uDestinatario[0]['lugar_tipo_id']);
                    }else{
                        $uid = $uDestinatario[0]['usuario_id'];
                    }   
                }else{
                    return false;
                }
                
                break;
            case 0://nivel nacional
                //dump($flujoprocesoSiguiente->getRolTipo()->getId());die;
                if($flujoprocesoSiguiente->getRolTipo()->getId() == 9){  // si es director
                    $query = $this->em->getConnection()->prepare("select u.* from maestro_inscripcion m
                    join usuario u on m.persona_id=u.persona_id
                    where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    //where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    //dump($uDestinatario);die;
                    if($uDestinatario){
                        $uid = $uDestinatario[0]['id'];
                    }else{
                        return false;
                    }
                }elseif($flujoprocesoSiguiente->getRolTipo()->getId() == 8){ // si es tecnico nacional
                    $query = $this->em->getConnection()->prepare("select * from wf_usuario_flujo_proceso ufp where ufp.esactivo is true and ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and lugar_tipo_id=1");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    if($uDestinatario){
                        //dump(count($uDestinatario));die;
                        if(count($uDestinatario)>1){
                            $uid = $this->asignaUsuarioDestinatario($tarea_actual,$tarea_sig_id,1);
                        }else{
                            $uid = $uDestinatario[0]['usuario_id'];
                        }
                    }else{
                        return false;
                    }
                }
                break;
        }

        $usuario = $this->em->getRepository('SieAppWebBundle:Usuario')->find($uid);
        return $usuario;
    }

    /**
     * funcion que asigna usuario destinatario si la tarea tiene mas de un usuario registrado
     */
    public function asignaUsuarioDestinatario($tarea_actual_id,$tarea_sig_id,$lugar_tipo)
    {
        $query = $this->em->getConnection()->prepare("select a.usuario_id,case when b.nro is null then 0 else b.nro end as nro
        from 
        (select usuario_id from wf_usuario_flujo_proceso wf
        where wf.flujo_proceso_id=". $tarea_sig_id ." and wf.esactivo is true and wf.lugar_tipo_id=". $lugar_tipo .")a
        left join 
        (select td.usuario_destinatario_id,count(*) as nro
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
        where flujo_proceso_id=". $tarea_actual_id ." and (td.tramite_estado_id=15 or td.tramite_estado_id=4) group by td.usuario_destinatario_id)b on a.usuario_id=b.usuario_destinatario_id  order by b.nro desc");
        $query->execute();
        $usuarios = $query->fetchAll();
        //dump($usuarios);die;
        $uid = $usuarios[0]['usuario_id'];
        //dump($uid);die;
        return $uid;
    }

    public function lugarTipoUE($sie, $gestion){
        $repository = $this->em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
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
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();

        return $ubicacionUe;
    }

    /**
     * funcion que verifica usuario remitente
     */

    public function verificaUsuarioRemitente($usuario,$flujoproceso,$tramite)
    {
        if (!$usuario or !$flujoproceso or !$tramite){
            $valida = false;
            return $valida; 
        }
        $nivel = $flujoproceso->getRolTipo()->getLugarNivelTipo();

        if($tramite->getInstitucioneducativa()){
            $institucioneducativa = $tramite->getInstitucioneducativa();
        }elseif($tramite->getEstudianteInscripcion()){
            $institucioneducativa = $tramite->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getInstitucioneducativa();
        }elseif($tramite->getMaestroInscripcion()){
            $institucioneducativa = $tramite->getMaestroInscripcion()->getInstitucioneducativa();
        }elseif($tramite->getApoderadoInscripcion()){
            $institucioneducativa = $tramite->getApoderadoInscripcion()->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getInstitucioneducativa();
        }else{
            $institucioneducativa = null;
        }
        //Obtenemos lugar tipo de la tarea en funcion al tramite
        if ($institucioneducativa){
            $lugar_tipo_distrito = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
        }else{
            $wfdatos = $this->em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("fp.orden=1")
                ->andWhere("wfd.esValido=true")
                ->getQuery()
                ->getResult();
            $lugar_tipo_distrito = $wfdatos[0]->getLugarTipoDistritoId();
        }
        
        $lt = $this->em->getRepository('SieAppWebBundle:LugarTipo')->find($lugar_tipo_distrito);
        $lugar_tipo_departamento = $lt->getLugarTipo()->getId();

        switch ($nivel->getId()) {
            case 7:   // Distrito
                $lugarTipoId = $lugar_tipo_distrito;                
                break;
            case 6:   // Departamento
            case 8:
                $lugarTipoId = $lugar_tipo_departamento;
                break;
            case 0://nivel nacional
                if($flujoproceso->getRolTipo()->getId() == 8){ // si es tecnico nacional
                    $lugarTipoId = 1;
                }
                break;
        }

        if($flujoproceso->getRolTipo()->getId() == 9 ){ //director
            $uRemitente = $this->em->getRepository('SieAppWebBundle:MaestroInscripcion')->createQueryBuilder('mi')
                        ->select('u')
                        ->innerJoin('SieAppWebBundle:Usuario','u','with','mi.persona = u.persona')
                        ->where('mi.institucioneducativa = '. $institucioneducativa->getId())
                        ->andWhere('mi.gestionTipo = '. (new \DateTime())->format('Y'))   
                        ->andWhere("mi.cargoTipo in (1,12)")
                        ->andWhere("mi.esVigenteAdministrativo=true")
                        ->andWhere("u.esactivo=true")
                        ->andWhere("u.id=". $usuario->getId())
                        ->getQuery()
                        ->getResult();
         }else{
            $uRemitente = $this->em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfu')
                        ->select('wfu')
                        ->where('wfu.usuario = '. $usuario->getId())
                        ->andWhere('wfu.flujoProceso = '. $flujoproceso->getId())   
                        ->andWhere("wfu.esactivo=true")
                        ->andWhere("wfu.lugarTipoId=". $lugarTipoId)
                        ->getQuery()
                        ->getResult();
        }
        if ($uRemitente){
            $valida = true;
        }else{
            $valida = false;
        }
        return $valida;
    }

}