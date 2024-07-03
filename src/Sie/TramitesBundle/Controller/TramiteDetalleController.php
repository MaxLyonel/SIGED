<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sie\AppWebBundle\Entity\TramiteDetalle;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\TramiteController as tramiteController;
use Sie\TramitesBundle\Controller\DocumentoController as documentoController;



class TramiteDetalleController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el detalle de un tramite
    // PARAMETROS: tramiteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getTramiteDetalle($tramiteId) {
        /*
         * Halla datos del documento supletorio en caso de existir
         */
        $em = $this->getDoctrine()->getManager();
        //$entidadTramite = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $serie, 'documentoSerie' => $serie));
        $entidad = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entidad->createQueryBuilder('td')
                ->select("
                    td.id as tramite_detalle_id, t.id as tramite_id, tt.tramiteTipo as tramite_tipo, te.tramiteEstado as tramite_estado, pt.procesoTipo as proceso
                    , CONCAT(CONCAT(CONCAT(CONCAT(pd.paterno, ' '), pd.materno), ' '), pd.nombre) as persona_destinatario
                    , CONCAT(CONCAT(CONCAT(CONCAT(pr.paterno, ' '), pr.materno), ' '), pr.nombre) as persona_remitente
                    , td.fechaRegistro as fecha_proceso, td.obs as observacion, te1.id as anterior_estado_id, te1.tramiteEstado as tramite_anterior_estado
                    , fp.id as flujo_proceso_id, fpn.id as siguiente_flujo_proceso_id, fpp.id as anterior_flujo_proceso_id, ptn.procesoTipo as siguiente_proceso
                    ")
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'WITH', 'td.tramite = t.id')
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:TramiteEstado', 'te', 'WITH', 'te.id = td.tramiteEstado')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'fp.id = td.flujoProceso')
                ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
                ->leftJoin('SieAppWebBundle:usuario', 'ud', 'WITH', 'ud.id = td.usuarioDestinatario')
                ->leftJoin('SieAppWebBundle:Persona', 'pd', 'WITH', 'pd.id = ud.persona')
                ->leftJoin('SieAppWebBundle:Usuario', 'ur', 'WITH', 'ur.id = td.usuarioRemitente')
                ->leftJoin('SieAppWebBundle:Persona', 'pr', 'WITH', 'pr.id = ur.persona')
                ->leftJoin('SieAppWebBundle:TramiteDetalle', 'td1', 'WITH', 'td1.id = td.tramiteDetalle')
                ->leftJoin('SieAppWebBundle:TramiteEstado', 'te1', 'WITH', 'te1.id = td1.tramiteEstado')
                ->leftJoin('SieAppWebBundle:FlujoProcesoDetalle', 'fpd', 'WITH', 'fpd.id = fp.id')
                ->leftJoin('SieAppWebBundle:FlujoProceso', 'fpn', 'WITH', 'fpn.id = fpd.flujoProcesoSig')
                ->leftJoin('SieAppWebBundle:ProcesoTipo', 'ptn', 'WITH', 'ptn.id = fpn.proceso')
                ->leftJoin('SieAppWebBundle:FlujoProceso', 'fpp', 'WITH', 'fpp.id = fpd.flujoProcesoAnt')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'ASC')
                ->setParameter('codTramite', $tramiteId);
        $entidad = $query->getQuery()->getResult();
        if(count($entidad)>0){
            return $entidad;
        } else {
            return array();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que registra un trámite a un proceso deseado
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramite($tramiteId,$flujoProcesoId,$usuarioId,$obs,$em){
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        //$em = $this->getDoctrine()->getManager();

        /*
         * Actualiza el ultimo proceso del tramite
         */
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id','desc')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();
        if (count($entityTramiteDetalle) > 0) {
            /*
             * Actualiza el estado, observacion y usuario responsable de procesar la informacion
             */
            $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 3));
            $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
            $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstado);
            //$entityTramiteDetalle[0]->setObs($entityTramiteDetalle[0]->getObs().' - REACTIVADO');
            $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuario);
            $em->persist($entityTramiteDetalle[0]);
            $em->flush();

            /*
             * Se registra el proceso para llevar el tramite a la badeja de impresion
             */

            $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
            $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $entityTramiteDetalle[0]->getTramite()->getId()));
            $entityTramiteDetalleEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 1));
            $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('id' => $flujoProcesoId));

            $entityTramiteDetalleNew = new TramiteDetalle();
            $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
            $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
            $entityTramiteDetalleNew->setObs(strtoupper($obs));
            $entityTramiteDetalleNew->setTramite($entityTramite);
            $entityTramiteDetalleNew->setTramiteEstado($entityTramiteDetalleEstado);
            $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
            $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
            $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
            $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso);
            $em->persist($entityTramiteDetalleNew);
            $em->flush();

            $return = "";
        } else {
            $return = "Expediente no encontrado, intente nuevamente";
        }
        return $return;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que modifica el estado del ultimo proceso realizado en un tramite
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setEstadoUltimoProcesoTramite($tramiteId,$tramiteEstadoId,$usuarioId,$fecha,$em){
        /*
         * Actualiza el ultimo proceso del tramite
         */
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id','desc')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();
        if (count($entityTramiteDetalle) > 0) {
            $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));
            $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
            $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstado);
            $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuario);
            $entityTramiteDetalle[0]->setFechaModificacion($fecha);
            $em->persist($entityTramiteDetalle[0]);
            $em->flush();
            return "";
        } else {
            return "Error al modificar el estado del proceso";
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que inicia el flujo un tramite
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramiteInicio($tramiteId, $usuarioId, $obs, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $tramiteEstadoId = 1;
        $tramiteEstadoSiguienteId = 3;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        /*
         * Proceso inicial del tramite seleccionado
         */
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                    ->where('fp.flujoTipo = :codFlujoTipo')
                    ->andWhere('fp.orden <> 0 ')
                    ->orderBy('fp.orden', 'ASC')
                    ->setParameter('codFlujoTipo', $entityTramite->getFlujoTipo())
                    ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        $entityTramiteDetalleNew->setObs($obs);
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        $em->persist($entityTramiteDetalleNew);
        $em->flush();

        return $entityTramiteDetalleNew->getId();
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que procesa un tramite a la siguiente instancia
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramiteSiguiente($tramiteId, $usuarioId, $obs, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $tramiteEstadoId = 1;
        $tramiteEstadoSiguienteId = 3;

        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        $entityUsuarioDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        /*
         * Esyado del tramite segun flujo seleccionado
         */
        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoSiguienteId));

        /*
         * Extrae la posicion del flujo que debe seguir
         */
        $entityFlujoProcesoDetalle = $em->getRepository('SieAppWebBundle:FlujoProcesoDetalle');
        $query2 = $entityFlujoProcesoDetalle->createQueryBuilder('fpd')
                ->where('fpd.id = :codFlujoProceso')
                ->setParameter('codFlujoProceso', $entityTramiteDetalle[0]->getFlujoProceso()->getId())
                ->setMaxResults('1');
        $entityFlujoProcesoDetalle = $query2->getQuery()->getResult();

        /*
         * Proceso inicial del tramite seleccionado
         */
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->where('fp.id = :codFlujoProceso')
                ->orderBy('fp.obs', 'ASC')
                ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoSig()->getId())
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
        $entityTramiteDetalleNew->setObs($obs);
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        $em->persist($entityTramiteDetalleNew);
        $em->flush();

        $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuarioDestinatario);
        $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
        $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);

        $em->persist($entityTramiteDetalle[0]);
        $em->flush();

        return $entityTramiteDetalleNew->getId();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que procesa un tramite a la anterior instancia
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramiteAnterior($tramiteId, $usuarioId, $obs, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $tramiteEstadoId = 1;
        $tramiteEstadoSiguienteId = 4;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityUsuarioDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        /*
         * Esyado del tramite segun flujo seleccionado
         */
        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoSiguienteId));

        //dump($entityTramiteEstadoSiguiente);die;

        /*
         * Extrae la posicion del flujo que debe seguir
         */
        $entityFlujoProcesoDetalle = $em->getRepository('SieAppWebBundle:FlujoProcesoDetalle');
        $query2 = $entityFlujoProcesoDetalle->createQueryBuilder('fpd')
                ->where('fpd.id = :codFlujoProceso')
                ->setParameter('codFlujoProceso', $entityTramiteDetalle[0]->getFlujoProceso()->getId())
                ->setMaxResults('1');
        $entityFlujoProcesoDetalle = $query2->getQuery()->getResult();

        /*
         * Proceso inicial del tramite seleccionado
         */
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->where('fp.id = :codFlujoProceso')
                ->orderBy('fp.obs', 'ASC')
                ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoAnt()->getId())
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
        $entityTramiteDetalleNew->setObs($obs);
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        $em->persist($entityTramiteDetalleNew);
        $em->flush();

        $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuarioDestinatario);
        $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
        $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);

        $em->persist($entityTramiteDetalle[0]);
        $em->flush();

        if ($entityFlujoProceso[0]->getOrden() == 0){
            $entityTramite->setEsactivo('0');
            $em->persist($entityTramite);
            $em->flush();
        }

        return $entityTramiteDetalleNew->getId();
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que anula un tramite
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramiteAnula($tramiteId, $usuarioId, $obs, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $tramiteEstadoId = 1;
        $tramiteEstadoSiguienteId = 2;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityUsuarioDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        /*
         * Esyado del tramite segun flujo seleccionado
         */
        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoSiguienteId));


        /*
         * Extrae la posicion inicial del flujo actual
         */
        $entityFlujoInicio = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query3 = $entityFlujoInicio->createQueryBuilder('fp')
                ->where('fp.flujoTipo = :codFlujo')
                ->orderBy('fp.orden', 'ASC')
                ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                ->setMaxResults('1');
        $entityFlujoInicio = $query3->getQuery()->getResult();

        /*
         * Proceso inicial del tramite seleccionado
         */
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                            ->where('fp.id = :codFlujoProceso')
                            ->orderBy('fp.obs', 'ASC')
                            ->setParameter('codFlujoProceso', $entityFlujoInicio[0]->getId())
                            ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
        $entityTramiteDetalleNew->setObs($obs);
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        $em->persist($entityTramiteDetalleNew);
        $em->flush();

        $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuarioDestinatario);
        $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
        $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);

        $em->persist($entityTramiteDetalle[0]);
        $em->flush();

        if ($entityFlujoProceso[0]->getOrden() == 0){
            $entityTramite->setEsactivo('0');
            $em->persist($entityTramite);
            $em->flush();
        }

        return $entityTramiteDetalleNew->getId();
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que finaliza un tramite
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setProcesaTramiteFinaliza($tramiteId, $usuarioId, $obs, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $tramiteEstadoId = 1;
        $tramiteEstadoSiguienteId = 3;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoId));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityUsuarioDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        /*
         * Esyado del tramite segun flujo seleccionado
         */
        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => $tramiteEstadoSiguienteId));


        /*
         * Proceso inicial del tramite seleccionado
         */
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->where('fp.flujoTipo = :codFlujo')
                ->orderBy('fp.orden', 'DESC')
                ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
        $entityTramiteDetalleNew->setObs($obs);
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        $em->persist($entityTramiteDetalleNew);
        $em->flush();

        $entityTramiteDetalle[0]->setUsuarioDestinatario($entityUsuarioDestinatario);
        $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
        $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);

        $em->persist($entityTramiteDetalle[0]);
        $em->flush();

        return $entityTramiteDetalleNew->getId();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para recepcionar en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRecepcionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,14);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_recepcion_lista',0,0,0,0)->createView(),
            'titulo' => 'Recepción',
            'subtitulo' => '',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la recpecion en direccion departamental de su tramite
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRecepcionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityAutorizacionCentro = $tramiteController->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getCertTecTramiteRecepcion($sie,$gestion,$especialidad,$nivel);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:recepcionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_recepcion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Recepción',
                        'subtitulo' => '',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_recepcion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_recepcion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_recepcion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites recepcionados por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRecepcionListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_recepcion_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_recepcion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes para su recepcion según el centro de educacion alternativa, gestión, especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteRecepcion($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , (case e.cedula_tipo_id when 2 then 'E-' else '' end) || cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id, td.id as tramite_detalle_id, ei.estadomatricula_tipo_id, segip_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            /*
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId." group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 17 limit 1)
            ) as td on td.tramite_id = t.id
            */
            inner join tramite_detalle as td on td.tramite_id = t.id and td.tramite_estado_id = 1 and td.flujo_proceso_id = 16 and t.flujo_tipo_id = 4 
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) -- and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la repepcion del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRecepcionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $especialidadId = 0;
        $nivelId = 0;
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "RECEPCIONADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('recepcionar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_recepcion_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                        $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                        $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                        $msg = array('0'=>true, '1'=>$participante);

                        $tramiteController = new tramiteController();
                        $tramiteController->setContainer($this->container);

                        if ($flujoSeleccionado == 'Adelante'){
                            $msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        switch ($nivelId) {
                            case 1:
                                $tramiteTipoId = 6;
                                break;
                           case 2:
                                $tramiteTipoId = 7;
                                break;
                           case 3:
                                $tramiteTipoId = 8;
                                break;
                            default:
                                $tramiteTipoId = 0;
                                break;
                        }

                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_recepcion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_recepcion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para autorizar en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecAutorizacionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(15,44,8);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_autorizacion_lista',0,0,0,0)->createView(),
            'titulo' => 'Autorización',
            'subtitulo' => '',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la autorizacion en direccion departamental de su tramite
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecAutorizacionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityAutorizacionCentro = $tramiteController->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getCertTecTramiteAutorizacion($sie,$gestion,$especialidad,$nivel);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:autorizacionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_autorizacion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Autorización',
                        'subtitulo' => '',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_autorizacion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_autorizacion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_autorizacion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites autorizados por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecAutorizacionListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_autorizacion_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_autorizacion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteAutorizacion($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , (case e.cedula_tipo_id when 2 then 'E-' else '' end) || cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id, td.id as tramite_detalle_id, ei.estadomatricula_tipo_id, segip_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            /*
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 18 limit 1)
            ) as td on td.tramite_id = t.id
            */            
            inner join tramite_detalle as td on td.tramite_id = t.id and td.tramite_estado_id = 1 and td.flujo_proceso_id = 16 and t.flujo_tipo_id = 4
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) -- and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la autorización del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecAutorizacionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $gestionActual = $gestionActual->format('Y');
        $especialidadId = 0;
        $periodoId = 3;
        $nivelId = 0;
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "AUTORIZADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('autorizar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_autorizacion_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();                    
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                        $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                        $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                        $msg = array('0'=>true, '1'=>$participante);

                        $tramiteController = new tramiteController();
                        $tramiteController->setContainer($this->container);

                        if ($flujoSeleccionado == 'Adelante'){
                            $entidadSucursal = $tramiteController->getInstitucionEducativaPeriodoGestionActual($institucionEducativaId, $gestionActual);

                            if(count($entidadSucursal) > 0){
                                $periodoId = $entidadSucursal[0]['periodo_tipo_id'];
                            } else {
                                $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();
                            }

                            $tipoMallaEstudianteInscripcion = $tramiteController->getCertTecTipoMallaInscripcion($estudianteInscripcionId, $especialidadId, $nivelId);
                        
                            $mallaNueva = false;
                            if(count($tipoMallaEstudianteInscripcion)>0){
                                $mallaNueva = $tipoMallaEstudianteInscripcion[0]['vigente'];
                            }

                            $msg = array('0'=>true, '1'=>$participante);
                            $msgContenido = $tramiteController->getCertTecValidacionInicio($participanteId, $especialidadId, $nivelId, $gestionId, $periodoId, $mallaNueva);
                            // $msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        switch ($nivelId) {
                            case 1:
                                $tramiteTipoId = 6;
                                break;
                           case 2:
                                $tramiteTipoId = 7;
                                break;
                           case 3:
                                $tramiteTipoId = 8;
                                break;
                            default:
                                $tramiteTipoId = 0;
                                break;
                        }

                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_autorizacion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_autorizacion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para imprimir en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(15,16,8,44,42);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_impresion_lista',0,0,0,0)->createView(),
            'titulo' => 'Asignación de cartón',
            'subtitulo' => '',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la impresión en direccion departamental de su tramite
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                $nivelCertificacionId = 0;
                switch ($nivel) {
                    case 1:
                        $nivelCertificacionId = 6;
                        break;
                    case 2:
                        $nivelCertificacionId = 7;
                        break;
                    case 3:
                        $nivelCertificacionId = 8;
                        break;
                    default:
                        $nivelCertificacionId = 0;
                }

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityAutorizacionCentro = $tramiteController->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getCertTecTramiteImpresion($sie,$gestion,$especialidad,$nivel);

                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $documentoTipoId = $nivelCertificacionId;
                    $rolPermitido = 16;
                    $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                    $entityFirma = $documentoController->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

                    $entityDocumentoSerie = $documentoController->getSerieTipo($nivelCertificacionId);
                    $entituDocumentoGestion = $documentoController->getGestionTipo('6,7,8');

                    $datosBusqueda = base64_encode(serialize($form));
                    $firmaHabilitada = true;

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:impresionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_impresion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Asignar cartón',
                        'subtitulo' => '',
                        'listaParticipante' => $entityParticipantes,
                        'listaFirma' => $entityFirma,
                        'series' => $entityDocumentoSerie,
                        'gestiones' => $entituDocumentoGestion,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                        'firmaHabilitada' => $firmaHabilitada
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_impresion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_impresion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_impresion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites impresos por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_impresion_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera los certificados generados para impresión por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionCertificadoPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
        $departamentoCodigo = $departamentoCodigo + 1;

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            switch ($nivel) {
                case 1:
                    $n = 6;
                    break;
                case 2:
                    $n = 7;
                    break;
                case 3:
                    $n = 8;
                    break;
            }

            $arch = $sie.'_'.$ges.'_certificado_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            if ($ges >= 2018){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_v3_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            } else {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_v2_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            }
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera los certificados generados para impresión con el formato de CI por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionCertificadoCiPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
        $departamentoCodigo = $departamentoCodigo + 1;

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            switch ($nivel) {
                case 1:
                    $n = 6;
                    break;
                case 2:
                    $n = 7;
                    break;
                case 3:
                    $n = 8;
                    break;
            }

            $arch = $sie.'_'.$ges.'_certificado_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            if ($ges >= 2018){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_ci_v3_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            } else {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_ci_v2_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            }  
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera los certificados generados para impresión con el formato de CI por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionCertificadoFirmaPdfAction(Request $request) {
        set_time_limit(1000);
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
        $departamentoCodigo = $departamentoCodigo + 1;

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $institucionEducativaEntity = $this->getInstitucionEducativaLugar($sie);

            switch ($nivel) {
                case 1:
                    $n = 6;
                    break;
                case 2:
                    $n = 7;
                    break;
                case 3:
                    $n = 8;
                    break;
            }

            $arch = $sie.'_'.$ges.'_certificado_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));


            $options = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Accept-language: en\r\n",
                    "Cookie: foo=bar\r\n",  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0"
                )
            );            
            $context = stream_context_create($options);
            
            switch ($nivel) {
                case 1:
                    if ($institucionEducativaEntity['departamento_codigo'] == "1" or $institucionEducativaEntity['departamento_codigo'] == 1){
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_basico_ch_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    } elseif($institucionEducativaEntity['departamento_codigo'] == "4" or $institucionEducativaEntity['departamento_codigo'] == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_basico_or_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_basico_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    }
                    break;
                case 2:
                    if ($institucionEducativaEntity['departamento_codigo'] == "1" or $institucionEducativaEntity['departamento_codigo'] == 1){
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_auxiliar_ch_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    } elseif($institucionEducativaEntity['departamento_codigo'] == "4" or $institucionEducativaEntity['departamento_codigo'] == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_auxiliar_or_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_auxiliar_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    }
                    break;
                case 3:
                   // $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_medio_v4_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&',false, $context));
                    break;
            }           
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
        }
    }


    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que detalla el lugar geográfico de la institucion educativa
    // PARAMETROS: por POST  institucionId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getInstitucionEducativaLugar($institucionId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $entity->createQueryBuilder('ie') 
            ->select('ie.id as institucioneducativa_id, ie.institucioneducativa, lt6.id as distrito_id, lt6.codigo as distrito_codigo, lt6.lugar as distrito, lt5.id as departamento_id, lt5.codigo as departamento_codigo, lt5.lugar as departamento')            
            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','jg.id = ie.leJuridicciongeografica')      
            ->innerJoin('SieAppWebBundle:LugarTipo','lt1','WITH','lt1.id = jg.lugarTipoLocalidad')        
            ->innerJoin('SieAppWebBundle:LugarTipo','lt2','WITH','lt2.id = lt1.lugarTipo')        
            ->innerJoin('SieAppWebBundle:LugarTipo','lt3','WITH','lt3.id = lt2.lugarTipo')       
            ->innerJoin('SieAppWebBundle:LugarTipo','lt4','WITH','lt4.id = lt3.lugarTipo')       
            ->innerJoin('SieAppWebBundle:LugarTipo','lt5','WITH','lt5.id = lt4.lugarTipo') 
            ->innerJoin('SieAppWebBundle:LugarTipo','lt6','WITH','lt6.id = jg.lugarTipoIdDistrito')
            ->where('ie.id = :codInstitucion')
            ->setParameter('codInstitucion', $institucionId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity[0]; 
        } else {
            return array(); 
        } 
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes para su impresion según el centro de educacion alternativa, gestión, especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteImpresion($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , (case e.cedula_tipo_id when 2 then 'E-' else '' end) || cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id, td.id as tramite_detalle_id, ei.estadomatricula_tipo_id, segip_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            /*
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 19 limit 1)
            ) as td on td.tramite_id = t.id
            */
            inner join tramite_detalle as td on td.tramite_id = t.id and td.tramite_estado_id = 1 and td.flujo_proceso_id = 18 and t.flujo_tipo_id = 4
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) -- and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la impresión del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecImpresionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }


        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        if (!$form){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_busca');
        }

        $institucionEducativaId = $form['sie'];
        $gestionId = $form['gestion'];
        $gestionActual = $gestionActual->format('Y');
        $periodoId = 3;
        $especialidadId = $form['especialidad'];
        $nivelId = $form['nivel'];
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        $formacionEducacionId = 3;

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DOCUMENTO GENERADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                $numeroCarton = $request->get('numeroSerie');
                $serieCarton = $request->get('serie');

                //$gestionCarton = $request->get('gestion');
                $fechaCarton = new \DateTime($request->get('fechaSerie'));
            //$fechaCarton = $fechaActual;
                $documentoFirmaId = base64_decode($request->get('firma'));
                
                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('imprimir', $token)) {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista');
                }

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $numCarton =$numeroCarton;
                $serCarton = $serieCarton;

                if (count($tramites) > 0) {                
                    switch ($form["nivel"]) {
                        case 1:
                            $documentoTipoId = 6;
                            break;
                        case 2:
                            $documentoTipoId = 7;
                            break;
                        case 3:
                            $documentoTipoId = 8;
                            break;
                        default:
                            $documentoTipoId = 0;
                            break;
                    }
                } else {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error, no se enviarion tramites para procesar, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista');
                }
                
                if ($form["nivel"] == 3){
                    $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('personaTipo' => 3));
                    $documentoFirmaMinId = $entidadDocumentoFirma->getId();
                    
                    $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('personaTipo' => 5));
                    $documentoFirmaVicId = $entidadDocumentoFirma->getId();

                    $documentoFirmas = array(0=>array('documentoFirmaId'=>$documentoFirmaMinId, 'personaTipoId'=>3), 1=>array('documentoFirmaId'=>$documentoFirmaVicId, 'personaTipoId'=>5));
                    // validacion no realizada, por lo que se declara como valido la disponilidad de firmas
                    $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');

                } else {
                    if($documentoFirmaId != 0 and $documentoFirmaId != ""){
                        $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                        if (count($entidadDocumentoFirma)>0) {
                            $firmaPersonaId = $entidadDocumentoFirma->getPersona()->getId();    
                            $valFirmaDisponible =  $documentoController->verFirmaAutorizadoDisponible($firmaPersonaId,count($tramites),$documentoTipoId);
                        } else {
                            $valFirmaDisponible = array(0 => false, 1 => 'Firma no habilitada, intente nuevamente');
                        }
                    } else {
                        $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');
                        $documentoFirmaId = 0;
                    }
                }
                

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                $messageCorrecto = "";
                $messageError = "";
                if($valFirmaDisponible[0]){
                    foreach ($tramites as $tramite) {
                        $tramiteId = (Int) base64_decode($tramite);
                        $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                        $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();                    
                        $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                        //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                        $msgContenido = "";
                        $msgContenidoDocumento = "";
                        if(count($entidadEstudianteInscripcion)>0){
                            $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                            $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                            $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                            $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                            $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                            $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                            $msg = array('0'=>true, '1'=>$participante);

                            switch ($nivelId) {
                                case 1:
                                    $tramiteTipoId = 6;
                                    $documentoTipoId = 6;
                                    $documentoTipoSerie = "B";
                                    break;
                            case 2:
                                    $tramiteTipoId = 7;
                                    $documentoTipoId = 7;
                                    $documentoTipoSerie = "A";
                                    break;
                            case 3:
                                    $tramiteTipoId = 8;
                                    $documentoTipoId = 8;
                                    $documentoTipoSerie = "M";
                                    break;
                                default:
                                    $tramiteTipoId = 0;
                                    $documentoTipoId = 0;
                                    $documentoTipoSerie = "";
                                    break;
                            }

                            if ($flujoSeleccionado == 'Adelante'){
                                $entidadSucursal = $tramiteController->getInstitucionEducativaPeriodoGestionActual($institucionEducativaId, $gestionActual);

                                if(count($entidadSucursal) > 0){
                                    $periodoId = $entidadSucursal[0]['periodo_tipo_id'];
                                } else {
                                    $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();
                                }

                                $tipoMallaEstudianteInscripcion = $tramiteController->getCertTecTipoMallaInscripcion($estudianteInscripcionId, $especialidadId, $nivelId);
                            
                                $mallaNueva = false;
                                if(count($tipoMallaEstudianteInscripcion)>0){
                                    $mallaNueva = $tipoMallaEstudianteInscripcion[0]['vigente'];
                                }

                                $msg = array('0'=>true, '1'=>$participante);

                                //$msgContenido = $tramiteController->getCertTecValidacionInicio($participanteId, $especialidadId, $nivelId, $gestionId, $periodoId, $mallaNueva);                                
                                $msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId, $mallaNueva);
                                $msgContenido = ""; /// commnetar para produccion

                                $documentoController = new documentoController();
                                $documentoController->setContainer($this->container);

                                $numCarton = str_pad($numeroCarton, 6, "0", STR_PAD_LEFT);
                                if ($serieCarton == 'ALT'){
                                    $serCarton = $serieCarton.$documentoTipoSerie;
                                } else {
                                    if($nivelId == 3){
                                        $serCarton = 'TT'.$documentoTipoSerie.$serieCarton;
                                    } else {
                                        $serCarton = 'ALT'.$documentoTipoSerie.$serieCarton;
                                    }                                    
                                }                               
                                
                                if($nivelId == 3){
                                    $msgContenidoDocumento = $documentoController->getDocumentoFormacionValidacion($numCarton, $serCarton, $fechaCarton, $id_usuario, $rolPermitido, $documentoTipoId, $formacionEducacionId);
                                } else {
                                    $msgContenidoDocumento = $documentoController->getDocumentoValidación($numCarton, $serCarton, $fechaCarton, $id_usuario, $rolPermitido, $documentoTipoId);
                                }
                                
                            }

                            if($msgContenido != ""){
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.', '.$msgContenidoDocumento.')');
                                } else {
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                                }
                            } else {
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenidoDocumento.')');
                                }
                            }
                        } else {
                            $msg = array('0'=>false, '1'=>'participante no encontrado');
                        }
                        if ($msg[0]) {
                            if ($flujoSeleccionado == 'Adelante'){
                                $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                                //$documentoFirmaId = 0;
                                if($nivelId == 3){
                                    $msgContenidoDocumento = $documentoController->setDocumentoFirmas($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton, $documentoFirmas);                                
                                } else {
                                    $msgContenidoDocumento = $documentoController->setDocumento($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton, $documentoFirmaId);
                                }
                                
                            }

                            if ($flujoSeleccionado == 'Atras'){
                                $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                            }

                            $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                        } else {
                            $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                        }
                        $numeroCarton = $numeroCarton + 1;
                    }
                } else {
                    $messageError = $valFirmaDisponible[1];
                }
                // if($messageCorrecto!=""){
                //     $em->getConnection()->commit();
                //     $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                // }
                // if($messageError!=""){
                //     $em->getConnection()->rollback();
                //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                // }

                if($messageError!=""){   
                    //$em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
                if($messageCorrecto!=""){
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                $em->getConnection()->commit();
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_impresion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para enviar de la direccion departamental al distrito educativo
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEnvioBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,14);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_envio_lista',0,0,0,0)->createView(),
            'titulo' => 'Envío',
            'subtitulo' => '',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para enviar de direccion departamental al disrito educativo
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEnvioListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityAutorizacionCentro = $tramiteController->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getCertTecTramiteEnvio($sie,$gestion,$especialidad,$nivel);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:envioIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_envio_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Envío',
                        'subtitulo' => '',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_envio_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_envio_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_envio_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites enviados por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEnvioListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_envio_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_envio_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes para envio a la direccion distrital según el centro de educacion alternativa, gestión, especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteEnvio($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , (case e.cedula_tipo_id when 2 then 'E-' else '' end) || cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id, td.id as tramite_detalle_id, ei.estadomatricula_tipo_id, segip_id, d.documento_serie_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            /*
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 20 limit 1)
            ) as td on td.tramite_id = t.id
            */
            inner join tramite_detalle as td on td.tramite_id = t.id and td.tramite_estado_id = 1 and td.flujo_proceso_id = 19 and t.flujo_tipo_id = 4
            inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) -- and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra el envio del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEnvioGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $especialidadId = 0;
        $nivelId = 0;
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "ENVIADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('enviar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_envio_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                        $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                        $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                        $msg = array('0'=>true, '1'=>$participante);

                        if ($flujoSeleccionado == 'Adelante'){
                            //$msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        switch ($nivelId) {
                            case 1:
                                $tramiteTipoId = 6;
                                $documentoTipoId = 6;
                                break;
                           case 2:
                                $tramiteTipoId = 7;
                                $documentoTipoId = 7;
                                break;
                           case 3:
                                $tramiteTipoId = 8;
                                $documentoTipoId = 8;
                                break;
                            default:
                                $tramiteTipoId = 0;
                                $documentoTipoId = 0;
                                break;
                        }

                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);

                            $entidadDocumento = $documentoController->getDocumentoTramiteTipo($tramiteId, $documentoTipoId);
                            if($entidadDocumento){
                                $documentoId = $documentoController->setDocumentoEstado($entidadDocumento->getId(), 2, $em);
                            }
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_envio_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_envio_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para la entrega del distrito educativo al participante
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEntregaBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        // $rolPermitido = array(8,13);
        $rolPermitido = array(9,8);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_entrega_lista',0,0,0,0)->createView(),
            'titulo' => 'Entrega',
            'subtitulo' => '',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la entrega del disrito educativo al participante
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEntregaListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityAutorizacionCentro = $tramiteController->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getCertTecTramiteEntrega($sie,$gestion,$especialidad,$nivel);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:entregaIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_detalle_certificado_tecnico_entrega_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Entrega',
                        'subtitulo' => '',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_entrega_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_entrega_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_entrega_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites finalizados y entregados por la direccion distrital al interesado en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEntregaListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_entrega_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_entrega_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes para entrega a centro y/o  participante según el centro de educacion alternativa, gestión, especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteEntrega($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , (case e.cedula_tipo_id when 2 then 'E-' else '' end) || cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id, td.id as tramite_detalle_id, ei.estadomatricula_tipo_id, segip_id, d.documento_serie_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            /*
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 21 limit 1)
            ) as td on td.tramite_id = t.id
            */
            inner join tramite_detalle as td on td.tramite_id = t.id and td.tramite_estado_id = 1 and td.flujo_proceso_id = 19 and t.flujo_tipo_id = 4
            left join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) -- and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la entrega del certificado técnico a nivel basico o auxiliar de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEntregaGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $especialidadId = 0;
        $nivelId = 0;
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DOCUMENTO ENTREGADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('entregar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_certificado_tecnico_entrega_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                        $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                        $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                        $msg = array('0'=>true, '1'=>$participante);

                        $tramiteController = new tramiteController();
                        $tramiteController->setContainer($this->container);

                        if ($flujoSeleccionado == 'Adelante'){
                            // $msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        switch ($nivelId) {
                            case 1:
                                $tramiteTipoId = 6;
                                $documentoTipoId = 6;
                                break;
                           case 2:
                                $tramiteTipoId = 7;
                                $documentoTipoId = 7;
                                break;
                           case 3:
                                $tramiteTipoId = 8;
                                $documentoTipoId = 8;
                                break;
                            default:
                                $tramiteTipoId = 0;
                                $documentoTipoId = 0;
                                break;
                        }

                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);

                            $documentoController = new documentoController();
                            $documentoController->setContainer($this->container);

                            $entidadDocumento = $documentoController->getDocumentoTramiteTipo($tramiteId, $documentoTipoId);
                            if($entidadDocumento){
                                $documentoId = $documentoController->setDocumentoEstado($entidadDocumento->getId(), 2, $em);
                            }
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_entrega_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_certificado_tecnico_entrega_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida si un documento en especifico concluyo su trámite
    // PARAMETROS: tramiteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function valUltimoProcesoFlujoTramite($tramiteId) {
        $msg = '';
        $em = $this->getDoctrine()->getManager();
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $queryEntidad = $em->getConnection()->prepare("
            select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.id = ".$tramiteId." group by trad.tramite_id
            )
        ");
        $queryEntidad->execute();
        $entityTramiteDetalle = $queryEntidad->fetchAll();
        $entidadFlujoProceso = $this->getUltimoProcesoFlujo($entityTramite->getFlujoTipo()->getId());
        if($entityTramiteDetalle[0]['flujo_proceso_id']!=$entidadFlujoProceso->getId()){
            $msg = 'La emisión '.$tramiteId.' no fue concluido';
        }
        return $msg;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el proceso donde debe concluirse un trámite según el tipo de flujo
    // PARAMETROS: flujoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getUltimoProcesoFlujo($flujoId){
        /*
         * Proceso inicial del tramite seleccionado
         */
        $em = $this->getDoctrine()->getManager();
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->where('fp.flujoTipo = :codFlujo')
                ->orderBy('fp.orden', 'DESC')
                ->setParameter('codFlujo', $flujoId)
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();
        return $entityFlujoProceso[0];
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el proceso donde debe imprimirse el documento según el tipo de flujo
    // PARAMETROS: flujoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getImpresionProcesoFlujo($flujoId){
        /*
         * Proceso inicial del tramite seleccionado
         */
        $em = $this->getDoctrine()->getManager();
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->innerJoin('SieAppWebBundle:FlujoProcesoDetalle', 'fpd', 'WITH', 'fpd.id = fp.id')
                ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
                ->where('fp.flujoTipo = :codFlujo')
                ->andWhere('pt.id = :codProceso')
                ->orderBy('fp.orden', 'DESC')
                ->setParameter('codFlujo', $flujoId)
                ->setParameter('codProceso', 5)
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();
        return $entityFlujoProceso[0];
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista el proceso donde debe anularse el documento y tramite según el tipo de flujo
    // PARAMETROS: flujoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getAnuladoProcesoFlujo($flujoId){
        /*
         * Proceso inicial del tramite seleccionado
         */
        $em = $this->getDoctrine()->getManager();
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $entityFlujoProceso->createQueryBuilder('fp')
                ->innerJoin('SieAppWebBundle:FlujoProcesoDetalle', 'fpd', 'WITH', 'fpd.id = fp.id')
                ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
                ->where('fp.flujoTipo = :codFlujo')
                ->andWhere('pt.id = :codProceso')
                ->orderBy('fp.orden', 'DESC')
                ->setParameter('codFlujo', $flujoId)
                ->setParameter('codProceso', 1)
                ->setMaxResults('1');
        $entityFlujoProceso = $query->getQuery()->getResult();
        return $entityFlujoProceso[0];
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes según su proceso, institucion educativa y gestión
    // PARAMETROS: flujoProcesoId, institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumTramiteProceso($institucionEducativaId, $gestionId, $flujoProcesoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select
            ie.id as institucioneducativa_id, ie.institucioneducativa, iec.gestion_tipo_id,
            nt.id as nivel_tipo_id, nt.nivel,
            pt.id as paralelo_tipo_id, pt.paralelo,
            tt.id as turno_tipo_id, tt.turno,
            e.id as estudiante_id, e.codigo_rude, e.pasaporte,
            (case e.cedula_tipo_id when 2 then 'E-' else '' end) || e.carnet_identidad as carnet,  e.complemento,
            (case e.cedula_tipo_id when 2 then 'E-' else '' end) ||
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
            t.id as tramite_id, d.id as documento_id, d.documento_serie_id as documento_serie_id, eid.documento_numero as documento_diplomatico
            , ei.estadomatricula_inicio_tipo_id as estadomatricula_inicio_tipo_id
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
            left join lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo as pat on pat.id = e.pais_tipo_id
            left join documento as d on d.tramite_id = t.id and documento_tipo_id in (1,9) and d.documento_estado_id = 1
            left join estudiante_inscripcion_diplomatico as eid on eid.estudiante_inscripcion_id = ei.id
            where td.tramite_estado_id = 1 and td.flujo_proceso_id = ".$flujoProcesoId." and iec.gestion_tipo_id = ".$gestionId."::INT and iec.institucioneducativa_id = ".$institucionEducativaId."::INT
            order by e.paterno, e.materno, e.nombre
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para recepcionar el trámite de diploma humanistico (Regular o Alternativa) en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRecepcionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(14,16,8,43,42);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_recepcion_lista',0,0)->createView(),
            'titulo' => 'Recepción',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la recepcion de su trámite de diploma humanistico (Regular o Alternativa) en direccion departamental
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRecepcionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_busca'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_busca'));
                    }

                    $entityAutorizacionInstitucionEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionInstitucionEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es regular_posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 2; //codigo del flujo y proceso de los tramites registrados
                    $entityParticipantes = $this->getDipHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumRecepcionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_recepcion_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Recepción',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionInstitucionEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista de tramites en formato pdf los trámites recepcionados por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRecepcionListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 3;
            $ids = "";

            $arch = 'AUTORIZACION_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$ids.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_recepcion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la recepcion en la direccion departamental del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRecepcionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(8,14);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "AUTORIZADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('recepcionar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_diploma_humanistico_recepcion_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $institucionEducativaId = $form['sie'];
                        $gestionId = $form['gestion'];

                        $msg = array('0'=>true, '1'=>$participante);

                        $tramiteController = new tramiteController();
                        $tramiteController->setContainer($this->container);

                        if ($flujoSeleccionado == 'Adelante'){
                            // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                            $valDocumentoEstudiante = $tramiteController->getDipHumDocumentoEstudiante($participanteId);
                            if(count($valDocumentoEstudiante) > 0){
                                $msgContenido = 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                            }
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Anular'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Autorizado', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_recepcion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_recepcion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para autorizar el trámite de diploma humanistico (Regular o Alternativa) en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizacionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(15,16,8,42);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_autorizacion_lista',0,0)->createView(),
            'titulo' => 'Autorización',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la autorizacion de su trámite de diploma humanistico (Regular o Alternativa) en direccion departamental
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizacionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_busca'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_busca'));
                    }

                    $entityAutorizacionInstitucionEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionInstitucionEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 3; //codigo del flujo y proceso de los tramites registrados
                    $entityParticipantes = $this->getDipHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumAutorizacionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_autorizacion_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Autorización',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionInstitucionEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista en formato pdf los trámites autorizados por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizacionListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 4;
            $ids = "";

            $arch = 'AUTORIZACION_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$ids.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_autorizacion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la autorización del trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizacionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(8,15);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "AUTORIZADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('autorizar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_diploma_humanistico_autorizacion_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $institucionEducativaId = $form['sie'];
                        $gestionId = $form['gestion'];

                        $msg = array('0'=>true, '1'=>$participante);

                        $tramiteController = new tramiteController();
                        $tramiteController->setContainer($this->container);

                        if ($flujoSeleccionado == 'Adelante'){
                            // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                            $valDocumentoEstudiante = $tramiteController->getDipHumDocumentoEstudiante($participanteId);
                            if(count($valDocumentoEstudiante) > 0){
                                $msgContenido = 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                            }
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Anular'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Autorizado', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_autorizacion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_autorizacion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para asignar el numero de serie y imprimir diploma humanistico (Regular o Alternativa) en la direccion departamental - legalizaciones
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(16,8,42);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_impresion_lista',0,0)->createView(),
            'titulo' => 'Impresión',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para asignar el numero de serie y imprimir diploma humanistico (Regular o Alternativa) en direccion departamental - legalizaciones
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
                    }

                    $entityAutorizacionInstitucionEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionInstitucionEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 4; //codigo del flujo y proceso de los tramites autorizados
                    $entityParticipantes = $this->getDipHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $documentoTipoId = 1;
                    $rolPermitido = 16;
                    $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                    $entityFirma = $documentoController->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

                    $entityDocumentoSerie = $documentoController->getSerieTipo('1');
                    $entituDocumentoGestion = $documentoController->getGestionTipo('1');
                    
                    $datosBusqueda = base64_encode(serialize($form));

                    $cierreOperativo = false;
                    if($entitySubsistemaInstitucionEducativa['msg'] == ''){
                        if($entitySubsistemaInstitucionEducativa['id'] == '1' or $entitySubsistemaInstitucionEducativa['id'] == 1){
                            $cierreOperativo = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestion);
                        } else {
                            $cierreOperativo = true;
                        }
                    }
                    
                    $sieHomologacion = array(1,2,3,4,5,6,7,8,9);
                    if ($gestion <= 2021 or in_array($sie,$sieHomologacion)){
                        $cierreOperativo = true;
                    }

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumImpresionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_impresion_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Impresión',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'listaFirma' => $entityFirma,
                        'series' => $entityDocumentoSerie,
                        'gestiones' => $entituDocumentoGestion,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionInstitucionEducativa,
                        'datosBusqueda' => $datosBusqueda,
                        'cierreOperativo' => $cierreOperativo,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la asignacion de cartones a los bachilleres humanísticos (Regular y Alternativa) en direccion departamental - legalizaciones
    // PARAMETROS: estudiantes[], numeroSerieInicial, tipoSerie, fechaEmision, boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $documentoTipoId = 1;
        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        $institucionEducativaId = $form['sie'];
        $gestionId = $form['gestion'];
        
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DIPLOMA DE BACHILLER HUMANÍSTICO GENERADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }
                $numeroCarton = $request->get('numeroSerie');
                $serieCarton = $request->get('serie');
                $documentoFirmaId = base64_decode($request->get('firma'));
                //dump($request->get('firma'));die;
                //$gestionCarton = $request->get('gestion');
                $fechaCarton = new \DateTime($request->get('fechaSerie'));
                //$fechaCarton = $fechaActual;

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('imprimir', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                }

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $numCarton =$numeroCarton;
                $serCarton = $serieCarton;
                
                if($documentoFirmaId != 0 and $documentoFirmaId != ""){
                    $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                    //dump($documentoFirmaId);die;
                    if (count($entidadDocumentoFirma)>0) {
                        $firmaPersonaId = $entidadDocumentoFirma->getPersona()->getId();    
                        // $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                        $valFirmaDisponible =  $documentoController->verFirmaAutorizadoDisponible($firmaPersonaId,count($tramites),$documentoTipoId);

                    } else {
                        $valFirmaDisponible = array(0 => false, 1 => 'Firma no habilitada, intente nuevamente');
                        // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No se encontro la firma ingresada, intente nuevamente'));
                        // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                    }
                } else {
                    $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');
                    $documentoFirmaId = 0;
                }
                
                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                $messageCorrecto = "";
                $messageError = "";
                if ($valFirmaDisponible[0]){
                    foreach ($tramites as $tramite) {
                        if ($serieCarton == 'A' or $serieCarton == 'A1' or $serieCarton == 'B' or $serieCarton == 'C' or $serieCarton == 'C1' or $serieCarton == 'D'){
                            $numCarton =$numCarton;
                        } else {    
                            $numCarton = str_pad($numCarton, 6, "0", STR_PAD_LEFT);
                        }
                        $tramiteId = (Int) base64_decode($tramite);
                        $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                        $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                        $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                        //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                        $msgContenido = "";
                        $msgContenidoDocumento = "";
                        if(count($entidadEstudianteInscripcion)>0){
                            $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                            $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();

                            $msg = array('0'=>true, '1'=>$participante);

                            if ($flujoSeleccionado == 'Adelante'){
                                // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                                $valDocumentoEstudiante = $tramiteController->getDipHumDocumentoEstudiante($participanteId);
                                if(count($valDocumentoEstudiante) > 0){
                                    $msgContenido = 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                                }

                                // $documentoController = new documentoController();
                                // $documentoController->setContainer($this->container);
                                
                                $msgContenidoDocumento = $documentoController->getDocumentoValidación($numCarton, $serCarton, $fechaCarton, $id_usuario, $rolPermitido, $documentoTipoId);
                            }

                            if($msgContenido != ""){
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.', '.$msgContenidoDocumento.')');
                                } else {
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                                }
                            } else {
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenidoDocumento.')');
                                }
                            }
                        } else {
                            $msg = array('0'=>false, '1'=>'Estudiante no encontrado');
                        }

                        if ($msg[0]) {
                            if ($flujoSeleccionado == 'Adelante'){
                                $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                                $msgContenidoDocumento = $documentoController->setDocumento($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton, $documentoFirmaId);
                            }

                            if ($flujoSeleccionado == 'Atras'){
                                $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                            }

                            if ($flujoSeleccionado == 'Anular'){
                                $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                            }

                            $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                        } else {
                            $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                        }
                        $numCarton = $numCarton + 1;
                    }
                } else {
                    $messageError = $valFirmaDisponible[1];
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $datosBusqueda = base64_encode(serialize($form));

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista en formato pdf los trámites donde fueron impresos sus diplomas por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionActaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));

            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 5;
            $participantes = $request->get('participantes');
            
            $listaParticipantes = "";
            if ($participantes != ''){
                foreach($participantes as $estudiante){
                    if ($listaParticipantes == ""){
                        $listaParticipantes = base64_decode($estudiante);
                    } else {
                        $listaParticipantes = $listaParticipantes.",".base64_decode($estudiante)."";
                    }
                }
            }
            
            $arch = 'ACTA_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_acta_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$listaParticipantes.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga Diplomas para imprimir en formato pdf de los trámites que cuentan con numeros de serie asignado por la direccion departamental - legalizaciones
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionCartonPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $form = $request->get('form');
            if ($form) {
                $tipoImp = 2;
                $sie = $form['sie'];
                $ges = $form['gestion'];
            } else {
                $tipoImp = 1;
                $info = $request->get('info');
                $form = unserialize(base64_decode($info));
                $sie = $form['sie'];
                $ges = $form['gestion'];
            }
          
            $tipLis = 5;
            $ids = "";
            $rolPermitido = 16;

            $documentoController = new documentoController();
            $documentoController->setContainer($this->container);
            $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);

            $em = $this->getDoctrine()->getManager();
            $entidadDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoCodigo));

            $dep = $entidadDepartamento->getSigla();
            $arch = 'CARTON_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

            $options = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Accept-language: en\r\n",
                    "Cookie: foo=bar\r\n",  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0"
                )
            );            
            $context = stream_context_create($options);

            // $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&',false, $context));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            //dump($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&');die;
            
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una institucion educativa segun tipo serie para imprimir el contenido del diploma humanistico (Regular o Alternativa) en la direccion departamental - legalizaciones
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionCartonBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);
		
		// if(empty($activeMenu)){
		// 	$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Módulo inhabilitado por el administrador, comuniquese con su Técnico SIE'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
		// } 

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $rolPermitido = array(16,8,42);
        
        return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumImpresionCartonIndex.html.twig', array(
            'formBusqueda' => $documentoController->creaFormBuscaInstitucionEducativaSerie('tramite_detalle_diploma_humanistico_impresion_carton_pdf','','','1')->createView(),
            'titulo' => 'Impresión Cartón',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una institucion educativa segun tipo serie para imprimir el acta del diploma humanistico (Regular o Alternativa) en la direccion departamental - legalizaciones
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionActaBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);
		
		// if(empty($activeMenu)){
		// 	$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Módulo inhabilitado por el administrador, comuniquese con su Técnico SIE'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }        
        
        $rolPermitido = array(14,16,8,42,43);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumImpresionActaIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_impresion_acta_lista',0,0)->createView(),
            'titulo' => 'Impresión Acta',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes con diplomas de bachiller humanistico para imprimir el acta correspondiente (Regular o Alternativa)
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionActaListaAction(Request $request) {

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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                                
                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_homepage'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_homepage'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 5; //codigo del flujo y proceso de los tramites impresos

                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);


                    $documentoTipoId = '1,3,4,5';

                    $entityParticipantes = $documentoController->getDocumentoInstitucionEducativaGestion($sie,$gestion,$documentoTipoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumImpresionActaIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_impresion_acta_lista',$sie,$gestion,'1,3,4,5')->createView(),
                        'titulo' => 'Impresión Acta',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_acta_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_acta_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_acta_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una rango de series para imprimir el contenido del diploma humanistico (Regular o Alternativa) en la direccion departamental - legalizaciones
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionCartonLoteBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);
        // $activeMenu = 'asds';
		
		// if(empty($activeMenu)){
		// 	$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Módulo inhabilitado por el administrador, comuniquese con su Técnico SIE'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
		// } 

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $rolPermitido = array(16,8,42);
        
        return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumImpresionCartonLoteIndex.html.twig', array(
            'formBusqueda' => $documentoController->creaFormBuscaInstitucionEducativaSerieLote('tramite_detalle_diploma_humanistico_impresion_carton_lote_pdf','','','','1')->createView(),
            'titulo' => 'Impresión Cartón en Lote',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga Diplomas para imprimir en formato pdf segun el rango ingresado por la direccion departamental - legalizaciones
    // PARAMETROS: numeroInicial, nummeroFinal, serie, tipoDocumentoId, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumImpresionCartonLotePdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $form = $request->get('form');
            //dump($form);die;
            if ($form) {
                $tipoImp = 3;
                $num1 = $form['numeroInicial'];
                $num2 = $form['numeroFinal'];
                $serie = $form['serie'];
                $sie = $num1.",".$num2;
                if ($serie == 'A' or $serie == 'A1' or $serie == 'B' or $serie == 'C' or $serie == 'C1' or $serie == 'D'){
                    $numeroSerie1 = $num1.$serie;
                    $numeroSerie2 = $num2.$serie;
                } else {    
                    $numeroSerie1 = (str_pad($num1, 6, "0", STR_PAD_LEFT)).$serie;
                    $numeroSerie2 = (str_pad($num2, 6, "0", STR_PAD_LEFT)).$serie;
                }
            } else {
                $tipoImp = 0;
                $sie = 0;
                $num1 = 0;
                $num2 = 0;
                $serie = '';
                $numeroSerie1 = $num1.$serie;
                $numeroSerie2 = $num2.$serie;
            }
          
            $ids = "";
            $rolPermitido = 16;
            $msgContenido = "";

            if($num1 <= $num2){
                $msgContenido = "";
            } else {
                $msgContenido = "El primer número deber ser menor o igual al segundo npumero  ingresado (".$num1." - ".$num2.")";
            }

            if(($num2 - $num1) > 100){
                $msgContenido = ($msgContenido=="") ? "El rango de números no debe excceder de 100 registros (".($num2 - $num1).")" : $msgContenido.", "."El rango de números no debe excceder de 100 registros (".$num2 - $num1.")";
            }   
            
            $documentoController = new documentoController();
            $documentoController->setContainer($this->container);

            // VALIDACION DE LA ASIGNACION DE UN NUMERO DE SERIE A UN DOCUMENTO (NUMERO SERIE 1)
            $valSerieAsigando = $documentoController->validaNumeroSerieAsignado($numeroSerie1);
            if($valSerieAsigando == ""){
                $msgContenido = ($msgContenido=="") ? "No existe el documento con número de serie ".$numeroSerie1 : $msgContenido.", "."No existe el documento con número de serie ".$numeroSerie1;
            }

            // VALIDACION DE LA ASIGNACION DE UN NUMERO DE SERIE A UN DOCUMENTO (NUMERO SERIE 2)
            $valSerieAsigando = $documentoController->validaNumeroSerieAsignado($numeroSerie2);
            if($valSerieAsigando == ""){
                $msgContenido = ($msgContenido=="") ? "No existe el documento con número de serie ".$numeroSerie2 : $msgContenido.", "."No existe el documento con número de serie ".$numeroSerie2;
            }

            $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);

            if ($departamentoCodigo == 0){
                $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para los documentos" : $msgContenido.", "."el usuario no cuenta con autorizacion para los el documentos ";
            } else {
                // VALIDACION DE TUICION DEL CARTON
                $valSerieTuicion = $documentoController->validaNumeroSerieTuicion($numeroSerie1, $departamentoCodigo);
                if($valSerieTuicion != ""){
                    $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
                }
                // VALIDACION DE TUICION DEL CARTON
                $valSerieTuicion = $documentoController->validaNumeroSerieTuicion($numeroSerie2, $departamentoCodigo);
                if($valSerieTuicion != ""){
                    $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
                }
            }

            if ($msgContenido == ""){
                $em = $this->getDoctrine()->getManager();
                $entidadDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoCodigo));
                $dep = $entidadDepartamento->getSigla();
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $numeroSerie1));
                $ges = $entityDocumentoSerie->getGestion()->getId();
                $numSerieCarton = "";
                $listaNumSerieCarton = "";
                $num = $num1;

                while ($num <= $num2) {
                    if ($serie == 'A' or $serie == 'A1' or $serie == 'B' or $serie == 'C' or $serie == 'C1' or $serie == 'D'){
                        $numCarton =$num;
                    } else {    
                        $numCarton = str_pad($num, 6, "0", STR_PAD_LEFT);
                    }
                    $numSerieCarton = $numCarton.$serie;
                    $num = $num + 1;
                    if ($listaNumSerieCarton == ""){
                        $listaNumSerieCarton = $numSerieCarton;
                    } else {
                        $listaNumSerieCarton = $listaNumSerieCarton.",".$numSerieCarton;
                    }
                    
                }

                $sie = $listaNumSerieCarton;
                
                $arch = 'CARTON_'.$numeroSerie1.'_al_'.$numeroSerie2.'_'.date('YmdHis').'.pdf';
                
                $response = new Response();
                $response->headers->set('Content-type', 'application/pdf');
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

                $options = array(
                    'http'=>array(
                        'method'=>"GET",
                        'header'=>"Accept-language: en\r\n",
                        "Cookie: foo=bar\r\n",  // check function.stream-context-create on php.net
                        "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0"
                    )
                );            
                $context = stream_context_create($options);

                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&',false, $context));
                $response->setStatusCode(200);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');

                //dump($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&');die;
                
                return $response;
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_carton_lote_busca', ['form' => $form], 307);
            }            
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_carton_lote_busca', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para enviar el trámite de diploma humanistico (Regular o Alternativa) en la direccion departamental
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEnvioBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(14,16,8,42,43);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_envio_lista',0,0)->createView(),
            'titulo' => 'Envío',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para la recepcion de su trámite de diploma humanistico (Regular o Alternativa) en direccion departamental
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEnvioListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_busca'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_busca'));
                    }

                    $entityAutorizacionInstitucionEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionInstitucionEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 5; //codigo del flujo y proceso de los tramites que fueron asignados su carton
                    $entityParticipantes = $this->getDipHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumEnvioIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_envio_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Envío',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionInstitucionEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista de tramites en formato pdf los trámites recepcionados por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEnvioListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 3;
            $ids = "";

            $arch = 'ENVIO_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$ids.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_envio_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra en envio de la direccion departamental del trámite  de los participantes selecionados a la direccion repartamental
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEnvioGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = array(8,14);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "AUTORIZADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('enviar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_diploma_humanistico_envio_lista');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    
                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $institucionEducativaId = $form['sie'];
                        $gestionId = $form['gestion'];

                        $msg = array('0'=>true, '1'=>$participante);


                        if ($flujoSeleccionado == 'Adelante'){
                            // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                            $valDocumentoEstudiante = $tramiteController->getDipHumDocumentoEstudiante($participanteId);
                            if(count($valDocumentoEstudiante) > 1){
                                $msgContenido = 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                            }
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras' or $flujoSeleccionado == 'Anular'){
                            $entityDocumento = $documentoController->getDocumentoTramiteTipo($tramiteId,1);
                            if (count($entityDocumento) > 0){
                              $documentoId = $documentoController->setDocumentoEstado($entityDocumento->getId(),2, $em);
                            }
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Anular'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Autorizado', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_envio_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_envio_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca una unidad educativa para registrar la entrega o no del diploma humanistico (Regular o Alternativa) en la direccion distrital
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEntregaBuscaAction(Request $request) {
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

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(13,14,16,8,10,41,42,43);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_entrega_lista',0,0)->createView(),
            'titulo' => 'Entrega',
            'subtitulo' => 'Diploma Humanísico',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para registrar la entrega o no del diploma humanistico (Regular o Alternativa) en direccion distrital
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEntregaListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
                    }

                    $entityAutorizacionInstitucionEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionInstitucionEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    // Funcion que lista los trámites registrados de participantes para su autorizacion según el centro de educacion alternativa, gestión, especialidad y nivel
                    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
                    $flujoProcesoId = 6; //codigo del flujo y proceso de los tramites con cartones asignados
                    $entityParticipantes = $this->getDipHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumEntregaIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_detalle_diploma_humanistico_entrega_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Entrega',
                        'subtitulo' => 'Diploma Humanístico',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionInstitucionEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la entrega de cartones a los bachilleres humanísticos (Regular y Alternativa) en direccion distrital
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEntregaGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // $rolPermitido = array(8,13);
        // $rolPermitido = array(9);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $documentoTipoId = 1;
        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DIPLOMA DE BACHILLER HUMANÍSTICO ENTREGADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE Y DIPLOMA DE BACHILLER HUMANÍSTICO ANULADO";
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('entregar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_detalle_diploma_humanistico_entrega_lista');
                }

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                $messageCorrecto = "";
                $messageError = "";
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    $msgContenidoDocumento = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                        $institucionEducativaId = $form['sie'];
                        $gestionId = $form['gestion'];

                        $msg = array('0'=>true, '1'=>$participante);

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'Estudiante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Atras' or $flujoSeleccionado == 'Anular'){
                            $documentoController = new documentoController();
                            $documentoController->setContainer($this->container);
                            $entityDocumento = $documentoController->getDocumentoTramiteTipo($tramiteId,1);
                            if (count($entityDocumento) > 0){
                              $documentoId = $documentoController->setDocumentoEstado($entityDocumento->getId(),2, $em);
                            }
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Anular'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            $datosBusqueda = base64_encode(serialize($form));

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_entrega_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_entrega_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista en formato pdf los trámites con el respectivo carton impreso que fueron enviados a la direccion distrital para su entrega a las unidades educativas y estudiantes
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEntregaListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 7;
            $ids = "";

            $arch = 'AUTORIZACION_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$ids.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_diploma_humanistico_entrega_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra una tramite para bachillerato técnico humanistico
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularImpresionBuscaAction(Request $request) {
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

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);

        // // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        // $rolPermitido = array(13,14,16,8,10,41,42,43);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularImpresionIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_bachillerato_tecnico_humanistico_regular_impresion_lista',0,0)->createView(),
            'titulo' => 'Asignación de cartón',
            'subtitulo' => 'Trámite - BTH',
        ));      
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de bachilleres con técnico humanístico para la asignacion de carton de su tramite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularImpresionListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $response = new JsonResponse();

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                try {
                    $tramiteController = new tramiteController();
                    $tramiteController->setContainer($this->container);

                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));                        
                        // $result = array('estado'=>false, 'msg'=>$verTuicionUnidadEducativa);
                        // return $response->setData($result);
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>$verTuicionUnidadEducativa));
                    }

                    $entityAutorizacionUnidadEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionUnidadEducativa)<=0){
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                        //$result = array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente');
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        // return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>$entitySubsistemaInstitucionEducativa['msg']));
                    }

                    //$entityParticipantes = $this->getEstudiantesRegularBachillerTecnicoHumanistica($sie,$gestion);
                    $flujoProcesoId = 161; //codigo del flujo y proceso de los tramites registrados
                    $entityParticipantes = $this->getBachTecHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $documentoTipoId = 8;
                    $rolPermitido = 16;
                    $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                    $entityFirma = $documentoController->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

                    $entityDocumentoSerie = $documentoController->getSerieEducacionTipo('8',2);
                    $entituDocumentoGestion = $documentoController->getGestionEducacionTipo('8',2);

                    $firmaHabilitada = true;

                    $cierreOperativo = false;
                    if($entitySubsistemaInstitucionEducativa['msg'] == ''){
                        if($entitySubsistemaInstitucionEducativa['id'] == '1' or $entitySubsistemaInstitucionEducativa['id'] == 1){
                            $cierreOperativo = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestion);
                        } 
                    }   

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularImpresionLista.html.twig', array(    
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                        'datosBusqueda' => $datosBusqueda,                        
                        'listaFirma' => $entityFirma,
                        'series' => $entityDocumentoSerie,
                        'gestiones' => $entituDocumentoGestion,
                        'firmaHabilitada' => $firmaHabilitada,
                        'cierreOperativo' => $cierreOperativo

                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'Error al procesar la información, intente nuevamente'));
                }
            }  else {
                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));
                $response->setStatusCode(201);
                return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
            }
        } else {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));            
            $response->setStatusCode(201);
            return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
        }
        //return $this->redirect($this->generateUrl('login'));
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los trámites registrados de participantes según su proceso, institucion educativa y gestión
    // PARAMETROS: flujoProcesoId, institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getBachTecHumTramiteProceso($institucionEducativaId, $gestionId, $flujoProcesoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select
            ie.id as institucioneducativa_id, ie.institucioneducativa, iec.gestion_tipo_id,
            nt.id as nivel_tipo_id, nt.nivel,
            pt.id as paralelo_tipo_id, pt.paralelo,
            tt.id as turno_tipo_id, tt.turno,
            e.id as estudiante_id, e.codigo_rude, e.pasaporte,
            (case e.cedula_tipo_id when 2 then 'E-' else '' end) || e.carnet_identidad as carnet,  e.complemento,
            (case e.cedula_tipo_id when 2 then 'E-' else '' end) ||
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
            t.id as tramite_id, d.id as documento_id, d.documento_serie_id as documento_serie_id, eid.documento_numero as documento_diplomatico
            , ei.estadomatricula_inicio_tipo_id as estadomatricula_inicio_tipo_id
            , case coalesce(bcte.estudiante_inscripcion_id,0) when 0 then false else true end as estado_bth
            , etht.especialidad
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
            left join estudiante_inscripcion_humnistico_tecnico as eiht on eiht.estudiante_inscripcion_id = ei.id
            left join especialidad_tecnico_humanistico_tipo as etht on etht.id = eiht.especialidad_tecnico_humanistico_tipo_id          
            left join bth_cut_ttm_estudiante as bcte on bcte.estudiante_inscripcion_id = ei.id
            left join lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo as pat on pat.id = e.pais_tipo_id
            left join documento as d on d.tramite_id = t.id and documento_tipo_id in (1,9) and d.documento_estado_id = 1
            left join estudiante_inscripcion_diplomatico as eid on eid.estudiante_inscripcion_id = ei.id
            where td.tramite_estado_id = 1 and td.flujo_proceso_id = ".$flujoProcesoId." and iec.gestion_tipo_id = ".$gestionId."::INT and iec.institucioneducativa_id = ".$institucionEducativaId."::INT
            order by etht.especialidad, e.paterno, e.materno, e.nombre
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la asignacion de cartones a los bachilleres humanísticos (Regular y Alternativa) en direccion departamental - legalizaciones
    // PARAMETROS: estudiantes[], numeroSerieInicial, tipoSerie, fechaEmision, boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularImpresionGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $documentoTipoId = 8;
        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        $institucionEducativaId = $form['sie'];
        $gestionId = $form['gestion'];

        $response = new JsonResponse();
        
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DIPLOMA DE BACHILLER HUMANÍSTICO GENERADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }
                $numeroCarton = $request->get('numeroSerie');
                $serieCarton = $request->get('serie');
                $datosBusqueda = $request->get('_info');
                $documentoFirmaId = base64_decode($request->get('firma'));
                //dump($request->get('firma'));die;
                //$gestionCarton = $request->get('gestion');
                $fechaCarton = new \DateTime($request->get('fechaSerie'));
                //$fechaCarton = $fechaActual;                
                $formBusqueda = unserialize(base64_decode($datosBusqueda));
                $sie = $formBusqueda['sie'];
                $gestionId = $formBusqueda['gestion'];

                $formacionEducacionId = 2;

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('imprimir', $token)) {
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
                }

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $numCarton =$numeroCarton;
                $serCarton = 'TTM'.$serieCarton;
                                
                //dump($tramiteTipoId,$formacionEducacionId,$documentoFirmaId);die;
                // VALIDACION DE FIRMA 
                // if($documentoFirmaId != 0 and $documentoFirmaId != ""){
                //     $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                //     //dump($documentoFirmaId);die;
                //     if (count($entidadDocumentoFirma)>0) {
                //         $firmaPersonaId = $entidadDocumentoFirma->getPersona()->getId();    
                //         // $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                //         $valFirmaDisponible =  $documentoController->verFirmaAutorizadoDisponible($firmaPersonaId,count($tramites),$documentoTipoId);

                //     } else {
                //         $valFirmaDisponible = array(0 => false, 1 => 'Firma no habilitada, intente nuevamente');
                //         // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No se encontro la firma ingresada, intente nuevamente'));
                //         // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                //     }
                // } else {
                //     $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');
                //     $documentoFirmaId = 0;
                // }
                
                $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('personaTipo' => 3));
                $documentoFirmaMinId = $entidadDocumentoFirma->getId();
                
                $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('personaTipo' => 4));
                $documentoFirmaVicId = $entidadDocumentoFirma->getId();

                $documentoFirmas = array(0=>array('documentoFirmaId'=>$documentoFirmaMinId, 'personaTipoId'=>3), 1=>array('documentoFirmaId'=>$documentoFirmaVicId, 'personaTipoId'=>4));

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                $messageCorrecto = "";
                $messageError = "";

                // validacion no realizada, por lo que se declara como valido la disponilidad de firmas
                $valFirmaDisponible = array(0 => true, 1 => 'Generar documento sin firma');

                if ($valFirmaDisponible[0]){
                    foreach ($tramites as $tramite) {
                        $numCarton = str_pad($numCarton, 6, "0", STR_PAD_LEFT);
                        $tramiteId = (Int) base64_decode($tramite);
                        $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                        $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                        $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                        //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                        $msgContenido = "";
                        $msgContenidoDocumento = "";
                        if(count($entidadEstudianteInscripcion)>0){
                            $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                            $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();

                            $msg = array('0'=>true, '1'=>$participante);

                            if ($flujoSeleccionado == 'Adelante'){
                                // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                                $valDocumentoEstudiante = $tramiteController->getBachTecHumDocumentoEstudiante($participanteId);
                                if(count($valDocumentoEstudiante) > 0){
                                    $msgContenido = 'ya cuenta con el Título de Bachillerato Técnico Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                                }
                                
                                $msgContenidoDocumento = $documentoController->getDocumentoFormacionValidacion($numCarton, $serCarton, $fechaCarton, $id_usuario, $rolPermitido, $documentoTipoId, $formacionEducacionId);
                            }

                            if($msgContenido != ""){
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.', '.$msgContenidoDocumento.')');
                                } else {
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                                }
                            } else {
                                if($msgContenidoDocumento != ""){
                                    $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenidoDocumento.')');
                                }
                            }
                        } else {
                            $msg = array('0'=>false, '1'=>'Estudiante no encontrado');
                        }

                        if ($msg[0]) {
                            if ($flujoSeleccionado == 'Adelante'){
                                $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                                $msgContenidoDocumento = $documentoController->setDocumentoFirmas($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton, $documentoFirmas);
                                //dump($tramiteDetalleId,$msgContenidoDocumento);die;
                            }

                            if ($flujoSeleccionado == 'Atras'){
                                $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                            }

                            if ($flujoSeleccionado == 'Anular'){
                                $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                            }

                            $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                        } else {
                            $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                        }
                        $numCarton = $numCarton + 1;
                    }
                } else {
                    $messageError = $valFirmaDisponible[1];
                }
                if($messageCorrecto==""){
                    // $em->getConnection()->commit();
                    // $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>$messageError)); 
                }
                // if($messageError!=""){bachTecHum
                //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                // }
                
                $em->getConnection()->commit();

                $flujoProcesoId = 161; //codigo del flujo y proceso de los tramites registrados
                $entityParticipantes = $this->getBachTecHumTramiteProceso($sie,$gestionId,$flujoProcesoId);

                $entityAutorizacionUnidadEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                if(count($entityAutorizacionUnidadEducativa)<=0){
                    // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    //$result = array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente');
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                }

                $documentoTipoId = 8;
                $rolPermitido = 16;
                $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                $entityFirma = $documentoController->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

                $entityDocumentoSerie = $documentoController->getSerieEducacionTipo('8',2);
                $entituDocumentoGestion = $documentoController->getGestionEducacionTipo('8',2);

                $firmaHabilitada = true;

                $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($institucionEducativaId);
                $cierreOperativo = false;
                if($entitySubsistemaInstitucionEducativa['msg'] == ''){
                    if($entitySubsistemaInstitucionEducativa['id'] == '1' or $entitySubsistemaInstitucionEducativa['id'] == 1){
                        $cierreOperativo = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestionId);
                    } 
                }  

                return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularImpresionLista.html.twig', array(    
                    'listaParticipante' => $entityParticipantes,
                    'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                    'datosBusqueda' => $datosBusqueda,                        
                    'listaFirma' => $entityFirma,
                    'series' => $entityDocumentoSerie,
                    'gestiones' => $entituDocumentoGestion,
                    'firmaHabilitada' => $firmaHabilitada,                    
                    'msgs'=>array('success'=>$messageCorrecto, 'error'=>$messageError),
                    'cierreOperativo' => $cierreOperativo
                ));



            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                $response->setStatusCode(201);
                return $response->setData(array('estado'=>false, 'msg'=>'Error al procesar la información, intente nuevamente'));
            }

            //$datosBusqueda = base64_encode(serialize($form));

            // $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $formBusqueda], 307);
        } else {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            // return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
            $response->setStatusCode(201);
            return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga lista en formato pdf los trámites donde fueron impresos sus diplomas por la direccion departamental - legalizaciones en formato pdf
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumImpresionActaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));

            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 162;
            $participantes = $request->get('participantes');
            
            $listaParticipantes = "";
            if ($participantes != ''){
                foreach($participantes as $estudiante){
                    if ($listaParticipantes == ""){
                        $listaParticipantes = base64_decode($estudiante);
                    } else {
                        $listaParticipantes = $listaParticipantes.",".base64_decode($estudiante)."";
                    }
                }
            }
            
            $arch = 'ACTA_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));            
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_tituloBachiller_tecnico_humanistico_acta_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$listaParticipantes.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $form], 307);
            
            return $this->redirectToRoute('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que descarga Diplomas para imprimir en formato pdf de los trámites que cuentan con numeros de serie asignado por la direccion departamental - legalizaciones
    // PARAMETROS: sie, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumImpresionCartonPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $form = $request->get('form');
            if ($form) {
                $tipoImp = 2;
                $sie = $form['sie'];
                $ges = $form['gestion'];
            } else {
                $tipoImp = 1;
                $info = $request->get('info');
                $form = unserialize(base64_decode($info));
                $sie = $form['sie'];
                $ges = $form['gestion'];
            }
          
            $tipLis = 162;
            $ids = "";
            $rolPermitido = 16;

            $documentoController = new documentoController();
            $documentoController->setContainer($this->container);
            $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);

            $em = $this->getDoctrine()->getManager();
            $entidadDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoCodigo));

            $dep = $entidadDepartamento->getSigla();
            $arch = 'CARTON_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

            $options = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Accept-language: en\r\n",
                    "Cookie: foo=bar\r\n",  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0"
                )
            );            
            $context = stream_context_create($options);

            // $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_bth_tituloEstudiante_unidadeducativa_'.$ges.'_v1.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&',false, $context));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            //dump($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_'.$ges.'_'.strtolower($dep).'_v3.rptdesign&unidadeducativa='.$sie.'&gestion_id='.$ges.'&tipo='.$tipoImp.'&&__format=pdf&');die;
            
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $form], 307);
            return $this->redirectToRoute('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca');
            
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra una tramite para bachillerato técnico humanistico
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularEntregaBuscaAction(Request $request) {
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

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);

        // // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        // $rolPermitido = array(13,14,16,8,10,41,42,43);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularEntregaIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaUnidadEducativaHumanistica('tramite_bachillerato_tecnico_humanistico_regular_entrega_lista',0,0)->createView(),
            'titulo' => 'Entrega',
            'subtitulo' => 'Trámite - BTH',
        ));      
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de bachilleres con técnico humanístico para la asignacion de carton de su tramite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularEntregaListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $response = new JsonResponse();

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                try {
                    $tramiteController = new tramiteController();
                    $tramiteController->setContainer($this->container);

                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $tramiteController->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));                        
                        // $result = array('estado'=>false, 'msg'=>$verTuicionUnidadEducativa);
                        // return $response->setData($result);
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>$verTuicionUnidadEducativa));
                    }

                    $entityAutorizacionUnidadEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionUnidadEducativa)<=0){
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                        //$result = array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente');
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    }

                    $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($sie);
                    if($entitySubsistemaInstitucionEducativa['msg'] != ''){
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
                        // return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
                        $response->setStatusCode(201);
                        return $response->setData(array('estado'=>false, 'msg'=>$entitySubsistemaInstitucionEducativa['msg']));
                    }

                    //$entityParticipantes = $this->getEstudiantesRegularBachillerTecnicoHumanistica($sie,$gestion);
                    $flujoProcesoId = 162; //codigo del flujo y proceso de los tramites registrados
                    $entityParticipantes = $this->getBachTecHumTramiteProceso($sie,$gestion,$flujoProcesoId);

                    $datosBusqueda = base64_encode(serialize($form));

                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $documentoTipoId = 8;
                    $rolPermitido = 9;
                    // $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,$rolPermitido);
                    // $entityFirma = $documentoController->getPersonaFirmaAutorizada($departamentoCodigo,$documentoTipoId);

                    // $entityDocumentoSerie = $documentoController->getSerieEducacionTipo('8',2);
                    // $entituDocumentoGestion = $documentoController->getGestionEducacionTipo('8',2);

                    // $firmaHabilitada = true;

                    $cierreOperativo = false;
                    if($entitySubsistemaInstitucionEducativa['msg'] == ''){
                        if($entitySubsistemaInstitucionEducativa['id'] == '1' or $entitySubsistemaInstitucionEducativa['id'] == 1){
                            $cierreOperativo = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestion);
                        } 
                    }   

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularEntregaLista.html.twig', array(    
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                        'datosBusqueda' => $datosBusqueda,    
                        'cierreOperativo' => $cierreOperativo

                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'Error al procesar la información, intente nuevamente'));
                }
            }  else {
                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));
                $response->setStatusCode(201);
                return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
            }
        } else {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            // return $this->redirect($this->generateUrl('tramite_bachillerato_tecnico_humanistico_regular_impresion_busca'));            
            $response->setStatusCode(201);
            return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
        }
        //return $this->redirect($this->generateUrl('login'));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la asignacion de cartones a los bachilleres humanísticos (Regular y Alternativa) en direccion departamental - legalizaciones
    // PARAMETROS: estudiantes[], numeroSerieInicial, tipoSerie, fechaEmision, boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function bachTecHumRegularEntregaGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 9;

        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $documentoTipoId = 8;
        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        $info = $request->get('_info');
        $form = unserialize(base64_decode($info));

        $institucionEducativaId = $form['sie'];
        $gestionId = $form['gestion'];

        $response = new JsonResponse();
        
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $tramites = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                    $obs = "DIPLOMA DE BACHILLER HUMANÍSTICO GENERADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                    $obs = "TRAMITE ANULADO";
                }
                
                $datosBusqueda = $request->get('_info');
                
                //$fechaCarton = $fechaActual;                
                $formBusqueda = unserialize(base64_decode($datosBusqueda));
                $sie = $formBusqueda['sie'];
                $gestionId = $formBusqueda['gestion'];

                $formacionEducacionId = 2;

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('entregar', $token)) {
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista');
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
                }                
                
                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                $messageCorrecto = "";
                $messageError = "";

                
                foreach ($tramites as $tramite) {
                    $tramiteId = (Int) base64_decode($tramite);
                    $entidadTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
                    $estudianteInscripcionId = $entidadTramite->getEstudianteInscripcion()->getId();
                    $entidadEstudianteInscripcion = $entidadTramite->getEstudianteInscripcion();
                    //$entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $msgContenido = "";
                    $msgContenidoDocumento = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                        $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();

                        $msg = array('0'=>true, '1'=>$participante);

                        // if ($flujoSeleccionado == 'Adelante'){
                        //     // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
                        //     $valDocumentoEstudiante = $tramiteController->getBachTecHumDocumentoEstudiante($participanteId);
                        //     if(count($valDocumentoEstudiante) > 0){
                        //         $msgContenido = 'ya cuenta con el Título de Bachillerato Técnico Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
                        //     }
                            
                        //     $msgContenidoDocumento = $documentoController->getDocumentoFormacionValidacion($numCarton, $serCarton, $fechaCarton, $id_usuario, $rolPermitido, $documentoTipoId, $formacionEducacionId);
                        // }

                        if($msgContenido != ""){
                            if($msgContenidoDocumento != ""){
                                $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.', '.$msgContenidoDocumento.')');
                            } else {
                                $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                            }
                        } else {
                            if($msgContenidoDocumento != ""){
                                $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenidoDocumento.')');
                            }
                        }
                    } else {
                        $msg = array('0'=>false, '1'=>'Estudiante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                            //$msgContenidoDocumento = $documentoController->setDocumentoFirmas($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton, $documentoFirmas);
                            //dump($tramiteDetalleId,$msgContenidoDocumento);die;
                        }

                        if ($flujoSeleccionado == 'Atras' or $flujoSeleccionado == 'Anular'){
                            $documentoController = new documentoController();
                            $documentoController->setContainer($this->container);
                            $entityDocumento = $documentoController->getDocumentoTramiteTipo($tramiteId,8);
                            if (count($entityDocumento) > 0){
                              $documentoId = $documentoController->setDocumentoEstado($entityDocumento->getId(),2, $em);
                            }
                        }

                        if ($flujoSeleccionado == 'Atras'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnterior($tramiteId, $id_usuario, $obs, $em);
                        }

                        if ($flujoSeleccionado == 'Anular'){
                            $tramiteDetalleId = $this->setProcesaTramiteAnula($tramiteId, $id_usuario, $obs, $em);
                        }

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto==""){
                    // $em->getConnection()->commit();
                    // $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>$messageError)); 
                }
                // if($messageError!=""){
                //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                // }
                
                $em->getConnection()->commit();

                $flujoProcesoId = 162; //codigo del flujo y proceso de los tramites registrados
                $entityParticipantes = $this->getBachTecHumTramiteProceso($sie,$gestionId,$flujoProcesoId);

                $entityAutorizacionUnidadEducativa = $tramiteController->getAutorizacionUnidadEducativa($sie);
                if(count($entityAutorizacionUnidadEducativa)<=0){
                    // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    //$result = array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente');
                    $response->setStatusCode(201);
                    return $response->setData(array('estado'=>false, 'msg'=>'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                }

                $documentoTipoId = 8;
                $rolPermitido = 9;
                              
                $entitySubsistemaInstitucionEducativa = $tramiteController->getSubSistemaInstitucionEducativa($institucionEducativaId);
                $cierreOperativo = false;
                if($entitySubsistemaInstitucionEducativa['msg'] == ''){
                    if($entitySubsistemaInstitucionEducativa['id'] == '1' or $entitySubsistemaInstitucionEducativa['id'] == 1){
                        $cierreOperativo = $this->get('funciones')->verificarSextoSecundariaCerrado($sie,$gestionId);
                    } 
                }  

                return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:bachTecHumRegularEntregaLista.html.twig', array(    
                    'listaParticipante' => $entityParticipantes,
                    'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                    'datosBusqueda' => $datosBusqueda,                        
                    'msgs'=>array('success'=>$messageCorrecto, 'error'=>$messageError),
                    'cierreOperativo' => $cierreOperativo
                ));



            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                $response->setStatusCode(201);
                return $response->setData(array('estado'=>false, 'msg'=>'Error al procesar la información, intente nuevamente'));
            }

            //$datosBusqueda = base64_encode(serialize($form));

            // $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
            // return $this->redirectToRoute('tramite_detalle_diploma_humanistico_impresion_lista', ['form' => $formBusqueda], 307);
        } else {
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            // return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_impresion_busca'));
            $response->setStatusCode(201);
            return $response->setData(array('estado'=>false, 'msg'=>'Error al enviar el formulario, intente nuevamente'));
        }
    }



}
