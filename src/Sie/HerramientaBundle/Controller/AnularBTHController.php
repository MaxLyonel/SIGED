<?php

namespace Sie\HerramientaBundle\Controller;

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





/**
 * ChangeMatricula controller.
 *
 */
class AnularBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) {
        return $this->render('SieHerramientaBundle:AnularBTH:index.html.twig');
    }

    public function busquedasieAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('sie'));
        //validate UE
        if (!$institucionEducativa) {
            $estado = 1;
            $msg = "No existe Unidad Educativa";
            return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
        }else{
            /**
             * Verificamos la tuicion sobre la UE
             */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $request->get('sie'));
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            if ($aTuicion[0]['get_ue_tuicion'] == true) {
                $gestion = $request->getSession()->get('currentyear');
                $sie = $request->get('sie');
                $query = $em->getConnection()->prepare("SELECT *  FROM	institucioneducativa_humanistico_tecnico WHERE grado_tipo_id = 0 AND institucioneducativa_id = $sie and gestion_tipo_id = $gestion" );
                $query->execute();
                $institucionEducativabth = $query->fetchAll();
                    if($institucionEducativabth){
                        $query = $em->getConnection()->prepare("SELECT id from flujo_tipo WHERE flujo = 'SOLICITUD BTH'");
                        $query->execute();
                        $flujo = $query->fetchAll();
                        $flujo_id = $flujo[0]['id'];
                        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('institucioneducativa'=>$sie,'gestionId'=>$this->session->get('currentyear'),'flujoTipo'=>$flujo_id));
                        if($tramite->getFechaFin() == null){
                            return new JsonResponse(array('estado'=>3,
                                'sie'=>$institucionEducativabth[0]['institucioneducativa_id'],
                                'ue'=>$institucionEducativabth[0]['institucioneducativa'])
                            );
                        }else{
                            $estado = 4;
                            $msg = "El trámte ya fue finalizado";
                            return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                        }
                    }
                    else{
                        $estado = 5;
                        $msg = " No puede anular el trámite de la Unidad Educativa debido a las siguientes razones:
                                1.- La Unidad Educativa aún no inicio su trámite.
                                2.- Ya fue cancelado el trámite de solicitud BTH.
                                3.- El tramite de la Unidad Educativa ya fue finalizado. ";
                        return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                    }
            } else {
                $estado = 2;
                $msg = "No tiene tuición sobre la unidad educativa";
                return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
            }
        }
    }
    private function obtieneflujo(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT id from flujo_tipo WHERE flujo = 'SOLICITUD BTH'");
        $query->execute();
        $flujo = $query->fetchAll();
        $flujo_id = $flujo[0]['id'];
        return $flujo_id;
    }
    public  function anulabthAction(Request $request){
        $sie = $request->get('sie');
        $gestion = $request->getSession()->get('currentyear');
        //1.- registrar en la tabla institucioneducativa_operativo_log para el resguardo
        //$em->getConnection()->beginTransaction();
        //ump($sie);die;
        try {
            $em = $this->getDoctrine()->getManager();
          //3.- Finalizar el tramite

            $flujo_id = $this->obtieneflujo();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('institucioneducativa'=>$sie,'gestionId'=>$this->session->get('currentyear'),'flujoTipo'=>$flujo_id));

            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            //dump($tramiteDetalle->getTramiteEstado()->getId());die;
            if($tramiteDetalle->getFlujoProceso()->getId() == 26 and $tramiteDetalle->getTramiteEstado()->getId() ==3 ){
                $msg = "El trámite esta en la bandeja del distrito y esta recepcionado, debe hacer la devolución a la Unidad Educativa";
                $estado = 3;
                return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                //dump("el tramite esta en la bandeja del distrito y esta recepcionado, debe hacer la devolucion a la Unidad Educativa" );die;
            }elseif(($tramiteDetalle->getFlujoProceso()->getId() == 26 and $tramiteDetalle->getTramiteEstado()->getId() == 4) or ($tramiteDetalle->getFlujoProceso()->getId() == 25 and $tramiteDetalle->getTramiteEstado()->getId() == 3) )
                {

                    if($tramite->getFechaFin() == null) {
                        //1.- registrar en la tabla institucioneducativa_operativo_log para el resguardo
                        $em = $this->getDoctrine()->getManager();
                        $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
                        $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(8));
                        $institucioneducativaOperativoLog->setGestionTipoId($gestion);
                        $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                        $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
                        $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
                        $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                        $institucioneducativaOperativoLog->setDescripcion('Anulacion BTH - Habilitacion descarga IGM');
                        $institucioneducativaOperativoLog->setEsexitoso('t');
                        $institucioneducativaOperativoLog->setEsonline('t');
                        $institucioneducativaOperativoLog->setUsuario($this->session->get('userId'));
                        $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
                        $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
                        $em->persist($institucioneducativaOperativoLog);
                        $em->flush();
                        //$em->getConnection()->commit();
                        //2.- Eliminar el registro de la tabla institucioneducaivahumanisticotecnico
                        $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $sie, 'gestionTipoId' => $this->session->get('currentyear')));
                        if ($institucionBth){
                            $em->remove($institucionBth);
                            $em->flush();
                            $tramite->setFechaFin(new \DateTime('now'));
                            $em->persist($tramite);
                            $em->flush();
                            $estado = 1;
                            $msg = "Se finalizó el trámite de la Unidad Educativa " . $sie . "la cual No podrá realizar nuevamente su solucitud como BTH";
                            return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                        }
                    }else{
                            $estado = 2;
                            $msg = "El trámte ya fue finalizado";
                            return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                        }

                }else if ($tramiteDetalle->getFlujoProceso()->getId() == 30 and $tramiteDetalle->getTramiteEstado()->getId() ==4){
                    $msg = "El trámite esta es su bandeja de Recibidos como devuelto y aun no fue recepcionado.";
                    $estado = 4;
                    return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                    //dump("el tramite esta en la bandeja del distrital como devuelto y aun no fue recepcionado");die;
            }else{
                $msg = "El trámite se encuentra en otra instancia.";
                $estado = 5;
                return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
                //dump("el tramite  se encuentra en otra instancia");die;
            }


        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $estado = 6;
            $msg = "Falla al finalizar el trámite";
            return new JsonResponse(array('estado' => $estado, 'msg' => $msg));
        }

    }
}
