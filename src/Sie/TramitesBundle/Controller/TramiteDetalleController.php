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
    public function setProcesaTramite($tramiteId,$flujoProcesoId,$usuarioId,$obs){
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $em = $this->getDoctrine()->getManager();

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
            $return = "Trámite no encontrado, intente nuevamente";
        }
        return $return;
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

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,14);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_recepcion_lista',0,0,0,0)->createView(),
            'titulo' => 'Recepción',
            'subtitulo' => 'Trámite',
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
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_recepcion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Recepción',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_recepcion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_recepcion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_recepcion_busca'));
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_recepcion_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId." group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 17 limit 1)
            ) as td on td.tramite_id = t.id
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
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
                    $obs = "TRÁMITE RECEPCIONADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('recepcionar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_recepcion_lista');
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_recepcion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_recepcion_busca'));
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

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,15);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_autorizacion_lista',0,0,0,0)->createView(),
            'titulo' => 'Autorización',
            'subtitulo' => 'Trámite',
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
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_autorizacion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Autorización',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_autorizacion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_autorizacion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_autorizacion_busca'));
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_autorizacion_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 18 limit 1)
            ) as td on td.tramite_id = t.id
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
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
                    $obs = "TRÁMITE AUTORIZADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('autorizar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_autorizacion_lista');
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_autorizacion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_autorizacion_busca'));
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

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,16);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_impresion_lista',0,0,0,0)->createView(),
            'titulo' => 'Impresión',
            'subtitulo' => 'Trámite',
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

                    $entityDocumentoSerie = $documentoController->getSerieTipo('6,7,8');
                    $entituDocumentoGestion = $documentoController->getGestionTipo('6,7,8');

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:impresionIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_impresion_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Impresión',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'series' => $entityDocumentoSerie,
                        'gestiones' => $entituDocumentoGestion,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_impresion_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_impresion_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_impresion_busca'));
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
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
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
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
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_tec_basico_tec_auxiliar_v2_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
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
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
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
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_tec_basico_tec_auxiliar_ci_v2_rcm.rptdesign&sie='.$sie.'&ges='.$ges.'&esp='.$especialidad.'&niv='.$n.'&sie='.$sie.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 19 limit 1)
            ) as td on td.tramite_id = t.id
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
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
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

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
                    $obs = "DOCUMENTO GENERADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                $numeroCarton = $request->get('numeroSerie');
                $serieCarton = $request->get('serie');
                //$gestionCarton = $request->get('gestion');
                //$fechaCarton = $request->get('fecha');
                $fechaCarton = $fechaActual;

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('imprimir', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_impresion_lista');
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
                            $msgContenido = $tramiteController->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);

                            $documentoController = new documentoController();
                            $documentoController->setContainer($this->container);

                            $numCarton = str_pad($numeroCarton, 6, "0", STR_PAD_LEFT);
                            $serCarton = $serieCarton.$documentoTipoSerie;

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
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        if ($flujoSeleccionado == 'Adelante'){
                            $tramiteDetalleId = $this->setProcesaTramiteSiguiente($tramiteId, $id_usuario, $obs, $em);
                            $msgContenidoDocumento = $documentoController->setDocumento($tramiteId, $id_usuario, $documentoTipoId, $numCarton, $serCarton, $fechaCarton);
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_impresion_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_impresion_busca'));
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

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,14);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_envio_lista',0,0,0,0)->createView(),
            'titulo' => 'Envío',
            'subtitulo' => 'Trámite',
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
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_envio_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Envío',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_envio_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_envio_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_envio_busca'));
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_envio_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 20 limit 1)
            ) as td on td.tramite_id = t.id
            inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
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
                    $obs = "TRÁMITE ENVIADO";
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }

                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('enviar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_envio_lista');
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

                            $entidadDocumento = $documentoController->getDocumentoTramite($tramiteId, $documentoTipoId);
                            if($entidadDocumento){
                                $documentoId = $documentoController->setDocumentoEstado($entidadDocumento->getId(), 2);
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_envio_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_envio_busca'));
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

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,13);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_entrega_lista',0,0,0,0)->createView(),
            'titulo' => 'Entrega',
            'subtitulo' => 'Trámite',
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
                        'formBusqueda' => $tramiteController->creaFormBuscaCentroEducacionAlternativaTecnica('sie_tramite_detalle_certificado_tecnico_entrega_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Entrega',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_entrega_lista'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_entrega_lista'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_entrega_busca'));
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_entrega_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            inner join (
                select * from tramite_detalle where id in (
                select max(trad.id) from tramite_detalle as trad
                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                where trad.tramite_estado_id <> 4 and tram.flujo_tipo_id = 4 and tram.gestion_id = ".$gestionId."::double precision group by trad.tramite_id
                ) and flujo_proceso_id in (select flujo_proceso_id_ant from flujo_proceso_detalle where id = 21 limit 1)
            ) as td on td.tramite_id = t.id
            inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la entrega del trámite de los participantes selecionados
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
                    return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_entrega_lista');
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

                            $documentoController = new documentoController();
                            $documentoController->setContainer($this->container);

                            $entidadDocumento = $documentoController->getDocumentoTramite($tramiteId, $documentoTipoId);
                            if($entidadDocumento){
                                $documentoId = $documentoController->setDocumentoEstado($entidadDocumento->getId(), 2);
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_entrega_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_detalle_certificado_tecnico_entrega_busca'));
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
            $msg = 'El trámite con número '.$tramiteId.' no fue concluido';
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
}
