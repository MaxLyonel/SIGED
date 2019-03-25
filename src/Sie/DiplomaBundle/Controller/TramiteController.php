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

class TramiteController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Despliega formulario de busqueda de los distritos
     * @return type
     */
    public function recepcionDistritoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 13)->createView()
                    , 'titulo' => 'Recepción Distrito'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
        //return $this->render('SieDiplomaBundle:Proceso:recepcionDistrito.html.twig');
    }

    /**
     * Despliega formulario de busqueda de los distritos
     * @return type
     */
    public function recepcionAlternativaDistritoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesCentroEducacionAlternativa.html.twig', array(
                    'form' => $this->creaFormularioBuscadorAlternativa('sie_diploma_tramite_busca_cea', '', $gestionActual,'', '', 13)->createView()
                    , 'titulo' => 'Recepción Distrito'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
        //return $this->render('SieDiplomaBundle:Proceso:recepcionDistrito.html.twig');
    }

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function recepcionDepartamentoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 14)->createView()
                    , 'titulo' => 'Recepción Departamento'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function autorizacionDiplomaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 15)->createView()
                    , 'titulo' => 'Autorización'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function impresionDiplomaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 16)->createView()
                    , 'titulo' => 'Impresión'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function entregaDepartamentoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 17)->createView()
                    , 'titulo' => 'Entrega Departamento'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function entregaDistritoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 18)->createView()
                    , 'titulo' => 'Entrega Distrito'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    /**
     * Despliega formulario de busqueda para la impresion de listados
     * @return type
     */
    public function impresionListadoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ListaTramitesImpresion.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', '', $gestionActual, '', 0)->createView()
                    , 'titulo' => 'Impresión Listado'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }


    /**
     * Despliega formulario de registro para una legalizacion de diploma
     * @return type
     */
    public function legalizacionDiplomaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:Legalizacion.html.twig', array(
                    'form' => $this->creaFormularioLegalizador('sie_diploma_tramite_legalizacion_save', '', $gestionActual, 17, 'Legalizar')->createView()
                    , 'titulo' => 'Legalización'
                    , 'subtitulo' => 'Diplomas de Bachiller'
                    , 'obs' => 0
        ));
    }


    /**
     * Despliega formulario de busqueda para descargar las legalizaciones de diploma de la ue
     * @return type
     */
    public function legalizacionUeDiplomaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieDiplomaBundle:Tramite:LegalizacionUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioLegalizadorUe('sie_diploma_tramite_legalizacion_ue_pdf', '', $gestionActual, 17, '')->createView()
                    , 'titulo' => 'Legalización por Unidad Educativa'
                    , 'subtitulo' => 'Diplomas de Bachiller'
                    , 'obs' => 1
        ));
    }

    /**
     * Despliega formulario de registro para una legalizacion de diploma
     * @return type
     */
    public function legalizacionUeDiplomaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $sie = $form['sie'];
        $ges = $form['gestion'];
        $identificador = $form['identificador'];

        ///////return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
        $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_ue_v1.rptdesign&sie='.$sie.'&gestion='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Registro de la legalizacion de diploma
     * @return type
     */
    public function legalizacionDiplomaSaveAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($form) {
            $serie = $form['texto'];
            //$ges = $form['gestion'];
            $identificador = $form['identificador'];
            /*
             * Extrae en codigo de departamento del usuario
             */
            $query = $em->getConnection()->prepare("
               select lugar_tipo_id from usuario_rol where usuario_id = ".$id_usuario."  and rol_tipo_id = ".$identificador." limit 1
            ");
            $query->execute();
            $entityUsuario = $query->fetchAll();


            /*
             * Extrae la gestion del numero de serie
             */
            $query = $em->getConnection()->prepare("
               select id, gestion_id, departamento_tipo_id from documento_serie where id like '".$serie."' limit 1
            ");
            $query->execute();
            $entitySerie = $query->fetchAll();
            if(count($entitySerie) > 0){
                $ges = $entitySerie[0]["gestion_id"];
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento con número de serie "'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
            }


            $departamentoUsuario = 0;
            if (count($entityUsuario) > 0){
                $departamentoUsuario = $entityUsuario[0]["lugar_tipo_id"];
            }

            if ($ges > 2014) {
                $query = $em->getConnection()->prepare("
                    select d.tramite_id as tramite_id, case d.documento_tipo_id when 9 then cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(2))) as integer) else cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) end as numero_serie
                    , case d.documento_tipo_id when 9 then right(d.documento_serie_id,2) else (right(d.documento_serie_id,(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) end as tipo_serie
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
                    where d.documento_tipo_id in (1,3,4,5,9) and d.documento_serie_id in ('".str_pad(strtoupper($serie), 7, "0", STR_PAD_LEFT)."') and ds.gestion_id = ".$ges." and ds.departamento_tipo_id = ".$departamentoUsuario."
                ");
            } else {
                $query = $em->getConnection()->prepare("
                    select d.tramite_id as tramite_id, case d.documento_tipo_id when 9 then cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(2))) as integer) else cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) end as numero_serie
                    , case d.documento_tipo_id when 9 then right(d.documento_serie_id,2) else (right(d.documento_serie_id,(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) end as tipo_serie
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
                    where d.documento_tipo_id in (1,3,4,5,9) and d.documento_serie_id in ('".strtoupper($serie)."') and ds.gestion_id = ".$ges." and ds.departamento_tipo_id = ".$departamentoUsuario."
                ");
            }
            $query->execute();
            $entity = $query->fetchAll();
            if (count($entity) > 0){
                if (count($entity) < 2){

//                    if (!$entity[0]["promovido"]){
//                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" no fue promovido, favor verificar el estado del estudiante e intente nuevamente'));
//                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
//                    } elseif (!$entity[0]["estadofintramite"]){
//                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" y con número de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" no concluyo su trámite, conluya su tramite e intente nuevamente'));
//                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
//                    } elseif (!$entity[0]["estadodocumento"]){
//                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con numero de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" se encuentra anulado, no es posible realizar su legalicación'));
//                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
//                    } else {
//                        $this->generaDocumento($entity[0]["tramite_id"], $id_usuario, 2, $entity[0]["numero_serie"], $entity[0]["tipo_serie"], $ges, $fechaActual);
//                    }

                    /*
                     * Extrae la observacion si el estudiante esta con procesos juridicos
                     */
                    if($entity[0]["observacion"] != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estudiante con código rude '.$entity[0]["rude"].' tiene la siguiente observación: '.$entity[0]["observacion"]));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    }

                    if (!$entity[0]["estadofintramite"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" y con número de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" no concluyo su trámite, conluya su tramite e intente nuevamente'));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    } elseif (!$entity[0]["estadodocumento"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con numero de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" se encuentra anulado, no es posible realizar su legalicación'));
                        return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                    } else {
                        $em->getConnection()->beginTransaction();
                        try {
                            $idDocumento = $this->generaDocumento($entity[0]["tramite_id"], $id_usuario, 2, $entity[0]["numero_serie"], $entity[0]["tipo_serie"], $ges, $fechaActual);
                            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El documento con numero de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" fue legalizado'));
                            $em->getConnection()->commit();
                            ///////return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                            $arch = $serie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
                            $response = new Response();
                            $response->headers->set('Content-type', 'application/pdf');
                            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                            if ($ges > 2014){
                                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_Departamento_v1.rptdesign&serie='.str_pad($serie, 7, "0", STR_PAD_LEFT).'&&__format=pdf&'));
                            } else {
                                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_Departamento_v1.rptdesign&serie='.$serie.'&&__format=pdf&'));
                            }
                            $response->setStatusCode(200);
                            $response->headers->set('Content-Transfer-Encoding', 'binary');
                            $response->headers->set('Pragma', 'no-cache');
                            $response->headers->set('Expires', '0');
                            return $response;
                        } catch (Exception $ex) {
                            $em->getConnection()->rollback();
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
                            return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                        }
                    }
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, inconsistencia de datos con el documento "'. $serie .'" , intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento con número de serie "'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
        }
    }

    private function creaFormularioBuscador($routing, $value1, $value2, $value3, $identificador) {
        if ($identificador == 0){
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                    ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('gt')
                                    ->where('gt.id > 2008')
                                    ->orderBy('gt.id', 'DESC');
                        },
                    ))
                    ->add('lista','choice',
                      array('label' => 'Lista',
                            'choices' => array( '2' => 'Recepción Distrito'
                                                ,'3' => 'Recepción Departamento'
                                                ,'4' => 'Autorización'
                                                ,'5' => 'Impresión'
                                                ,'6' => 'Entrega Departamento'
                                                ,'7' => 'Entrega Distrito'
                                                ,'1' => 'Trámite Observado'
                                                ,'100' => 'Diplomas Impresos'
                                                ,'101' => 'Diplomas Anulados'
                                                ),
                            'data' => $value3))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        } else {
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                    ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('gt')
                                    ->where('gt.id > 2008')
                                    ->orderBy('gt.id', 'DESC');
                        },
                    ))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        }
        return $form;
    }


    private function creaFormularioBuscadorAlternativa($routing, $value1, $value2, $value3, $value4, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('sucursal', 'entity', array('data' => $value3, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\SucursalTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('st')
                                ->orderBy('st.id', 'ASC');
                    },
                ))
                ->add('periodo', 'entity', array('data' => $value4, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\PeriodoTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('pt')
                                ->where('pt.id in (2,3)')
                                ->orderBy('pt.id', 'ASC');
                    },
                ))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function impresionDiplomaSerieFormAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:ImpresionDiplomaSerie.html.twig', array(
                    'form' => $this->creaFormularioImpresion('sie_diploma_tramite_impresion_pdf', '', $gestionActual, 2, 13)->createView()
                    , 'titulo' => 'Impresion por Tipo de Serie'
                    , 'subtitulo' => 'Diplomas de Bachiller'
        ));
    }

    private function creaFormularioImpresion($routing, $value1, $value2, $value3, $identificador) {
            //asljhdjksahdkjhsajkdhsjkahdjkshajkdhsjakhdjkjjjjjjjjjjjjjjjjjjjjj
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("
                    select distinct ds.gestion_id as id, cast(right (ds.id,((case ds.gestion_id when 2013 then 2 when 2010 then 2 else 1 end))) as varchar) as gestion_tipo
                    from documento_serie as ds
                    ");
            $query->execute();
            $entity = $query->fetchAll();
            $aGestion = array();
            foreach ($entity as $ges) {

                //send the values to the next steps
                $aGestion[$ges['id']] = $ges['gestion_tipo'];
            }

            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                    ->add('gestion','choice',
                      array('label' => 'Lista',
                            'choices' => $aGestion,
                            'data' => $value2))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('tipoImpresion', 'hidden', array('attr' => array('value' => $value3)))
                    ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();

        return $form;
    }

    private function creaFormularioLegalizador($routing, $value1, $value2, $identificador, $boton) {
        if($boton == "Reactivar"){
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('texto', 'text', array('label' => 'SERIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('obs', 'textarea', array('label' => 'OBS.', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                //->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                //    'query_builder' => function(EntityRepository $er) {
                //        return $er->createQueryBuilder('gt')
                //                    ->where('gt.id > 2008')
                //                    ->orderBy('gt.id', 'DESC');
                //    },
                //))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => $boton, 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        } else {
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('texto', 'text', array('label' => 'SERIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '^@?(\w){1,8}$', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                    //->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    //    'query_builder' => function(EntityRepository $er) {
                    //        return $er->createQueryBuilder('gt')
                    //                ->where('gt.id > 2008')
                    //                ->orderBy('gt.id', 'DESC');
                    //    },
                    //))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search', 'submit', array('label' => $boton, 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        }
        return $form;
    }


    private function creaFormularioLegalizadorUe($routing, $value1, $value2, $identificador, $boton) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => $boton, 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function buscaUnidadEducativaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $sie = 0;
        $ges = 0;
        $identificador = 0;
        $lista = '';
        $save = $this->session->get('save');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if (!isset($save)) {
            return $this->redirectToRoute('sie_diploma_homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($this->session->get('save')) {
            $form['sie'] = $this->session->get('datosBusqueda')['sie'];
            $form['gestion'] = $this->session->get('datosBusqueda')['gestion'];
            $form['identificador'] = $this->session->get('datosBusqueda')['identificador'];
            if ($this->session->get('datosBusqueda')['identificador'] == 0){
                $form['lista'] = $this->session->get('datosBusqueda')['lista'];
            }
        }

        if ($form) {
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $identificador = $form['identificador'];
            if ($identificador == 0){
                $lista = $form['lista'];
            }

            $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe Unidad Educativa'));
                if ($identificador == 13){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_recepcion_distrito');
                } elseif ($identificador == 14){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_recepcion_departamento');
                } elseif ($identificador == 15){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_autorizacion');
                } elseif ($identificador == 16){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_impresion');
                } elseif ($identificador == 17){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_entrega_departamento');
                } elseif ($identificador == 18){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_entrega_distrito');
                } elseif ($identificador == 0){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_impresion_listados_detalle');
                } else {
                    $retorna = $this->redirectToRoute('sie_diploma_homepage');
                }
                return $retorna;
            }

            $bachilleres = $this->buscaEstudiantesUnidadEducativa($sie, $ges, $identificador, $lista);

            $query = $em->getConnection()->prepare('SELECT * FROM flujo_tipo order by id');
            $query->execute();
            $flujos = $query->fetchAll();

            if ($identificador == 13) {
                $subtitulo = "Recepcion de Trámite - Distrito";
            } elseif ($identificador == 14) {
                $subtitulo = "Recepcion  de Trámite - Departamental";
            } elseif ($identificador == 15) {
                $subtitulo = "Autorización Trámite de Diploma";
            } elseif ($identificador == 16) {
                $subtitulo = "Impresion Diploma de Bachiller";
            } elseif ($identificador == 17) {
                $subtitulo = "Entrega de Diploma de Bachiller - Departamental";
            } elseif ($identificador == 18) {
                $subtitulo = "Entrega de Diploma de Bachiller - Distrito";
            } elseif ($identificador == 0) {
                $subtitulo = "Impresión Listados";
            }

            if($identificador == 0){
                return $this->render('SieDiplomaBundle:Tramite:ListaTramitesImpresion.html.twig', array(
                        'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', $sie, $ges, $lista, $identificador)->createView()
                        , 'bachilleres' => $bachilleres
                        , 'unidadEducativa' => $institucionEducativa
                        , 'gestion' => $ges
                        , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                        , 'subtitulo' => $subtitulo
                        , 'identificador' => $identificador
                        , 'flujos' => $flujos
                        , 'lista' => $lista
                    ));
            }

            if ($identificador == 13) {
                return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                        'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', $sie, $ges, '', $identificador)->createView()
                        , 'bachilleres' => $bachilleres
                        , 'unidadEducativa' => $institucionEducativa
                        , 'gestion' => $ges
                        , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                        , 'subtitulo' => $subtitulo
                        , 'identificador' => $identificador
                        , 'flujos' => $flujos
                    ));
            } else {
                if ($identificador == 16) {
                    $em = $this->getDoctrine()->getManager();
                    $query = $em->getConnection()->prepare("select distinct case gestion_id when 2013 then RIGHT(id,2) when 2010 then RIGHT(id,2) else RIGHT(id,1) end as serie from documento_serie order by serie");
                    $query->execute();
                    $entitySerie = $query->fetchAll();

                    $query = $em->getConnection()->prepare("select * from gestion_tipo order by id desc");
                    $query->execute();
                    $entityGestion = $query->fetchAll();

                    return $this->render('SieDiplomaBundle:Tramite:ListaTramitesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', $sie, $ges, '', $identificador)->createView()
                                , 'bachilleres' => $bachilleres
                                , 'unidadEducativa' => $institucionEducativa
                                , 'gestion' => $ges
                                , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                                , 'subtitulo' => $subtitulo
                                , 'identificador' => $identificador
                                , 'gestiones' => $entityGestion
                                , 'series' => $entitySerie
                    ));
                } else {
                    return $this->render('SieDiplomaBundle:Tramite:ListaTramitesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', $sie, $ges, '', $identificador)->createView()
                                , 'bachilleres' => $bachilleres
                                , 'unidadEducativa' => $institucionEducativa
                                , 'gestion' => $ges
                                , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                                , 'subtitulo' => $subtitulo
                                , 'identificador' => $identificador
                    ));
                }
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
        }
    }

    public function buscaCentroEducacionAlternativaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $sie = 0;
        $ges = 0;
        $suc = 0;
        $per = 0;
        $identificador = 0;
        $lista = '';
        $save = $this->session->get('save');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if (!isset($save)) {
            return $this->redirectToRoute('sie_diploma_homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if (!$form and $this->session->get('save')) {
            $form['sie'] = $this->session->get('datosBusqueda')['sie'];
            $form['gestion'] = $this->session->get('datosBusqueda')['gestion'];
            $form['sucursal'] = $this->session->get('datosBusqueda')['sucursal'];
            $form['periodo'] = $this->session->get('datosBusqueda')['periodo'];
            $form['identificador'] = $this->session->get('datosBusqueda')['identificador'];
            if ($this->session->get('datosBusqueda')['identificador'] == 0){
                $form['lista'] = $this->session->get('datosBusqueda')['lista'];
            }
        }

        if ($form) {
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $suc = $form['sucursal'];
            $per = $form['periodo'];
            $identificador = $form['identificador'];
            if ($identificador == 0){
                $lista = $form['lista'];
            }

            $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe Unidad Educativa'));
                if ($identificador == 13){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_recepcion_distrito');
                } elseif ($identificador == 14){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_recepcion_departamento');
                } elseif ($identificador == 15){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_autorizacion');
                } elseif ($identificador == 16){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_impresion');
                } elseif ($identificador == 17){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_entrega_departamento');
                } elseif ($identificador == 18){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_entrega_distrito');
                } elseif ($identificador == 0){
                    $retorna = $this->redirectToRoute('sie_diploma_tramite_impresion_listados_detalle');
                } else {
                    $retorna = $this->redirectToRoute('sie_diploma_homepage');
                }
                return $retorna;
            }
            //die($sie." - ".$ges." - ".$suc." - ".$per." - ".$identificador." - ".$lista);
            $bachilleres = $this->buscaEstudiantesCentroEducacionAlternativa($sie, $ges, $suc, $per, $identificador, $lista);
            $query = $em->getConnection()->prepare('SELECT * FROM flujo_tipo order by id');
            $query->execute();
            $flujos = $query->fetchAll();

            if ($identificador == 13) {
                $subtitulo = "Recepcion de Trámite - Distrito";
            } elseif ($identificador == 14) {
                $subtitulo = "Recepcion  de Trámite - Departamental";
            } elseif ($identificador == 15) {
                $subtitulo = "Autorización Trámite de Diploma";
            } elseif ($identificador == 16) {
                $subtitulo = "Impresion Diploma de Bachiller";
            } elseif ($identificador == 17) {
                $subtitulo = "Entrega de Diploma de Bachiller - Departamental";
            } elseif ($identificador == 18) {
                $subtitulo = "Entrega de Diploma de Bachiller - Distrito";
            } elseif ($identificador == 0) {
                $subtitulo = "Impresión Listados";
            }

            if($identificador == 0){
                return $this->render('SieDiplomaBundle:Tramite:ListaTramitesImpresion.html.twig', array(
                        'form' => $this->creaFormularioBuscador('sie_diploma_tramite_busca_ue', $sie, $ges, $lista, $identificador)->createView()
                        , 'bachilleres' => $bachilleres
                        , 'unidadEducativa' => $institucionEducativa
                        , 'gestion' => $ges
                        , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                        , 'subtitulo' => $subtitulo
                        , 'identificador' => $identificador
                        , 'flujos' => $flujos
                        , 'lista' => $lista
                        , 'sucursal' => $suc
                        , 'periodo' => $per
                    ));
            }

            if ($identificador == 13) {
                return $this->render('SieDiplomaBundle:Tramite:ListaEstudiantesCentroEducacionAlternativa.html.twig', array(
                        'form' => $this->creaFormularioBuscadorAlternativa('sie_diploma_tramite_busca_cea', $sie, $ges, $suc, $per, $identificador)->createView()
                        , 'bachilleres' => $bachilleres
                        , 'unidadEducativa' => $institucionEducativa
                        , 'gestion' => $ges
                        , 'titulo' => $institucionEducativa->getId() . ' - ' . $institucionEducativa->getInstitucioneducativa()
                        , 'subtitulo' => $subtitulo
                        , 'identificador' => $identificador
                        , 'flujos' => $flujos
                        , 'sucursal' => $suc
                        , 'periodo' => $per
                    ));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_busca_cea');
        }
    }

    public function registraTramiteDiplomaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //valida si el usuario ha iniciado sesión
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            /*
             * Recupera datos del formulario
             */
            $estudiantes = $request->get('estudiantes');
            $gestion = $request->get('gestion');
            $ue = $request->get('sie');
            $identificador = $request->get('identificador');
            if ($identificador == 13) {
                $flujoTipoId = $request->get('flujo');
            } else {
                $flujoTipoId = 0;
            }

            if ($identificador == 13) {
                $retorna = 'sie_diploma_tramite_recepcion_distrito';
            } elseif ($identificador == 14) {
                $retorna = 'sie_diploma_tramite_recepcion_departamento';
            } elseif ($identificador == 15) {
                $retorna = 'sie_diploma_tramite_autorizacion';
            } elseif ($identificador == 16) {
                $retorna = 'sie_diploma_tramite_impresion';
            } elseif ($identificador == 17) {
                $retorna = 'sie_diploma_tramite_entrega_departamento';
            } elseif ($identificador == 18) {
                $retorna = 'sie_diploma_tramite_entrega_distrito';
            } else {
                $retorna = 'sie_diploma_homepage';
            }

            /*
             * Verifica si el estudiante figura como promovido
             */
            $habilitado = $this->verificaNivelUnidadEducativa($ue);
            if ($habilitado == 0){
                 $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$ue.' no puede extender diplomas de bachiller'));
                return $this->redirectToRoute($retorna);
            }

            if (isset($_POST['botonAceptar'])) {
                $flujoSeleccionado = 'Adelante';
            }
            if (isset($_POST['botonDevolver'])) {
                $flujoSeleccionado = 'Atras';
            }
            if (isset($_POST['botonImpRecDistrito'])) {
                $flujoSeleccionado = 'ImpRecDistrito';
                $listaEstudiantes = "";
                if ($estudiantes != ''){
                    foreach($estudiantes as $estudiante){
                        if ($listaEstudiantes == ""){
                            $listaEstudiantes = $estudiante;
                        } else {
                            $listaEstudiantes = $listaEstudiantes.",".$estudiante."";
                        }
                    }
                }
                return $this->forward('SieDiplomaBundle:Tramite:printReportesDistritoPdf',array('ue' => $ue,'gestion' => $gestion,'ids' => $listaEstudiantes));
            }
            if (isset($_POST['botonEntregar'])) {
                $flujoSeleccionado = 'Finalizar';
            }

            try {
                /*
                 * Denine el tipo de tramite y flujo que se aplicara al trámite
                 */
                $tramiteTipoId = 1;
                //$flujoTipoId = 1;

                /*
                 * Recorre lista de estudiantes que ingresan su trámite
                 */
                $mensajeerror = '';
                $mensaje = '';
                foreach ($estudiantes as $estudiante) {
                    $estudianteInscripcionId = (Int) $estudiante;
                    $em = $this->getDoctrine()->getManager();


                    $entityEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    /*
                     * Verifica si el estudiante ya cuenta con un tramite o documento en funcion a su codigo rude
                     */
                    //$verificaEstudianteRude = $this->verificaEstudianteConDobleTramite($estudianteInscripcionId,1);
                    $verificaEstudianteDiploma = "";
                    $verificaEstudianteDiploma = $this->verificaEstudianteConDiplomaActivo($entityEstudianteInscripcion->getEstudiante()->getId(),1);
//                    if ($verificaEstudianteRude == 1){
//                        $em->getConnection()->rollback();
//                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con código '.$rude.' ya cuenta con un trámite en diplomas de bachiller'));
//                        return $this->redirectToRoute($retorna);
//                    }

                    if ($verificaEstudianteDiploma != ""){
                        /*
                         * Verifica si el estudiante ya tiene un diploma impreso
                         */
                        //$mensaje = '';
                        //$mensaje = $this->verificaEstudianteConDiplomaActivo($estudianteInscripcionId,1);
                        if ($mensajeerror == ""){
                            $mensajeerror = $verificaEstudianteDiploma;
                        } else {
                            $mensajeerror = $mensajeerror.", ".$verificaEstudianteDiploma;
                        }
                    }

//                    if ($verificaEstudianteRude == 3){
//                        $em->getConnection()->rollback();
//                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con código '.$rude.' no se encuentra registrado en el sistema'));
//                        return $this->redirectToRoute($retorna);
//                    }


                    if ($verificaEstudianteDiploma == ""){
                        $entityTramite = new Tramite();
                        $entityTramiteDetalle = new TramiteDetalle();
                        /*
                         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
                         */
                        $entityTramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('id' => $tramiteTipoId));
                        $entityFlujoTipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->findOneBy(array('id' => $flujoTipoId));

                        /*
                         * Define el conjunto de valores a ingresar - Tramite
                         */
                        $entityTramite->setTramite($entityTramite->getId());
                        $entityTramite->setEstudianteInscripcion($entityEstudianteInscripcion);
                        $entityTramite->setFlujoTipo($entityFlujoTipo);
                        $entityTramite->setTramiteTipo($entityTramiteTipo);
                        $entityTramite->setFechaTramite($fechaActual);
                        $entityTramite->setFechaRegistro($fechaActual);
                        $entityTramite->setEsactivo('1');
                        $entityTramite->setGestionId($gestion);
                        $em->persist($entityTramite);
                        $em->flush();

                        $entityTramite->setTramite($entityTramite->getId());
                        $em->persist($entityTramite);
                        $em->flush();

                        /*
                         * Extra el id del registro ingresado de la tabla tramite
                         */
                        $tramiteId = $entityTramite->getId();

                        $error = $this->procesaTramite($tramiteId, $id_usuario, $flujoSeleccionado,'');
                    }
                }
                /*
                 * Genera alfanumerico y número aleatorio
                 */
                // $generator = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',1)),0.5);
                // $generator = rand();
                // Confirmar la transacción
                $em->getConnection()->commit();
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Trámite registrado'));
                if($mensajeerror != ""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "Los estudiantes ".$mensajeerror." ya cuentan con Diploma de Bachiller Humanístico." ));
                }
                $this->session->set('save', true);
                $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador));
                //$request->initialize();

//                if ($identificador == 13) {
//                    return $this->forward('SieDiplomaBundle:Tramite:printReportesPdf',array('sie'=>$ue,'gestion'=>$gestion,'datos'=>'80730459','tipo'=>1));
//                }
                return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al ingresar registro, intente nuevamente'));
                return $this->redirectToRoute($retorna);
            }
            $valores = array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador);
            //return $this->redirect($this->generateUrl('sie_diploma_tramite_busca_ue', array('valores'=> $valores,)));
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Datos invalidos, intente nuevamente'));
            $valores = array('sie' => 0, 'gestion' => 0, 'identificador' => 0);
            return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
        }
    }

    public function procesaTramiteDiplomaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //valida si el usuario ha iniciado sesión
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $estudiantes = array();

        try {
            /*
             * Recupera datos del formulario
             */
            if ($request->isMethod('POST')) {
                $estudiantes = $request->get('estudiantes');
                $gestion = $request->get('gestion');
                $ue = $request->get('sie');
                $identificador = $request->get('identificador');
                $obs = '';

                /*
                 * Extrae en codigo de departamento del usuario
                 */
                $query = $em->getConnection()->prepare("
                   select lugar_tipo_id from usuario_rol where usuario_id = ".$id_usuario."  and rol_tipo_id = ".$identificador." order by id asc limit 1
                ");
                $query->execute();
                $entityUsuario = $query->fetchAll();

                $departamentoUsuario = 0;
                if (count($entityUsuario) > 0){
                    $departamentoUsuario = $entityUsuario[0]["lugar_tipo_id"];
                }


                if ($identificador == 13) {
                    $retorna = 'sie_diploma_tramite_recepcion_distrito';
                } elseif ($identificador == 14) {
                    $retorna = 'sie_diploma_tramite_recepcion_departamento';
                } elseif ($identificador == 15) {
                    $retorna = 'sie_diploma_tramite_autorizacion';
                } elseif ($identificador == 16) {
                    $retorna = 'sie_diploma_tramite_impresion';
                } elseif ($identificador == 17) {
                    $retorna = 'sie_diploma_tramite_entrega_departamento';
                } elseif ($identificador == 18) {
                    $retorna = 'sie_diploma_tramite_entrega_distrito';
                } else {
                    $retorna = 'sie_diploma_homepage';
                }

                /*
                 * Verifica si la Unidad Educativa esta habilitada para la entrega de diplomas
                 */
                $habilitado = $this->verificaNivelUnidadEducativa($ue);
                if ($habilitado == 0){
                     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$ue.' no puede extender diplomas de bachiller'));
                    return $this->redirectToRoute($retorna);
                }

                /*
                 * Verifica si preciono en boton adelante o atras
                 */
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                }
                if (isset($_POST['botonEntregar'])) {
                    $flujoSeleccionado = 'Finalizar';
                }

                /*
                 * Halla cantidad de registros a procesar
                 */
                $cantidadEstudiantes = sizeof($estudiantes);

                /*
                 * Verifica si el rango de serie esta disponible para la impresion de diplomas
                 */
                if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
                    $numeroSerie = $request->get('numeroSerie');
                    $numSerieIni = $numeroSerie;
                    $numSerieFin = $numeroSerie+$cantidadEstudiantes;
                    $tipoSerie = $request->get('serie');
                    $gestion = $request->get('gestion');
                    $fecha = new \DateTime($request->get('fecha'));
                    $posicionArray = 0;
                    $seriesArray[] = array();
                    for($i = $numeroSerie; $i < ($numeroSerie+$cantidadEstudiantes);$i++) {
                        $seriesArray[$posicionArray] = $i;
                        $posicionArray = $posicionArray + 1;
                    }
                    $rangoDisponible = $this->serieRangoDisponible($cantidadEstudiantes,$seriesArray,$tipoSerie,$gestion,$departamentoUsuario);
                    if ($rangoDisponible != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $rangoDisponible));
                        $this->session->set('save', true);
                        $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador));
                        return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
                    }
                }
                /*
                 * Recorre lista de estudiantes que ingresan su trámite y sus documentos en caso de realizar la impresion
                 */
                $mensajeerror = "";
                $countSerieArray = 0;
                if(count($estudiantes)>0){
                    foreach ($estudiantes as $estudiante) {
                        $tramiteId = (Int) $estudiante;
                        //$verificaEstudianteRude = $this->verificaEstudianteConDobleTramite($tramiteId,2);
                        $verificaEstudianteDiploma = "";
    
                        if ($flujoSeleccionado == 'Adelante' and $identificador < 17) {
    
                            $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
    
                            $verificaEstudianteDiploma = $this->verificaEstudianteConDiplomaActivo($entityTramite->getEstudianteInscripcion()->getEstudiante()->getId(),1);
    //                        /*
    //                         * Verifica si el estudiante ya cuenta con un tramite o documento en funcion a su codigo rude
    //                         */
    //    //                    if ($verificaEstudianteRude == 1){
    //    //                        $em->getConnection()->rollback();
    //    //                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con código '.$rude.' ya cuenta con un trámite en diplomas de bachiller'));
    //    //                        return $this->redirectToRoute($retorna);
    //    //                    }
    
                            if ($verificaEstudianteDiploma != ""){
                                /*
                                 * Verifica si el estudiante ya tiene un diploma impreso
                                 */
                                //$mensaje = '';
                                //$mensaje = $this->verificaEstudianteConDiplomaActivo($tramiteId,2);
                                if ($mensajeerror == ""){
                                    $mensajeerror = $verificaEstudianteDiploma;
                                } else {
                                    $mensajeerror = $mensajeerror.", ".$verificaEstudianteDiploma;
                                }
                            }
    //
    //                        if ($verificaEstudianteRude == 3){
    //                            $em->getConnection()->rollback();
    //                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con código '.$rude.' no se encuentra registrado en el sistema'));
    //                            return $this->redirectToRoute($retorna);
    //                        }
                        }
    
                        if ($verificaEstudianteDiploma == ""){
                            $error = $this->procesaTramite($tramiteId, $id_usuario, $flujoSeleccionado,$obs);
    
                            /*
                             * Genera documento diploma
                             */
                            if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
                                $documentosGenerados[$countSerieArray] = $this->generaDocumento($tramiteId, $id_usuario, 1, $seriesArray[$countSerieArray], $tipoSerie, $gestion, $fecha);
                                $countSerieArray = $countSerieArray + 1;
                            }
                        }
    
                        /*
                         * Anula documento diploma
                         */
                        if ($identificador == 17 and $flujoSeleccionado == 'Atras') {
                            $error = $this->anulaDocumento($tramiteId,$obs);
                        }
                    }
                }

                $em->getConnection()->commit();
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => sizeof($estudiantes).' Trámite(s) procesado(s)'));
                if($mensajeerror != ""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "Los estudiantes ".$mensajeerror." ya cuentan con Diploma de Bachiller Humanístico." ));
                }
                $this->session->set('save', true);
                $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador));

//                if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
//                    $resp = $this->forward('SieDiplomaBundle:Tramite:printDocumento',array('numSerieIni' => $numSerieIni,'numSerieFin' => $numSerieFin,'tipoSerie' => $tipoSerie));
//                } else {
//                    return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
//                }
                /*
                 * Genera el o los diplomas de bachiller en formato PDF
                 */
//                if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
//                    //$pdfGenerado = $this->printDocumento($numSerieIni, $numSerieFin, $tipoSerie);
//                    //$httpKernel = $this->container->get('http_kernel');
//                    $arch = '1_ESTUDIANTESF3_'.date('YmdHis').'.pdf';
//                    $response = new Response();
//                    $response->headers->set('Content-type', 'application/pdf');
//                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
//                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegosSec_Participaciones_foto_v3.rptdesign&depto=1&&__format=pdf&'));
//                    $response->headers->set('Content-Transfer-Encoding', 'binary');
//                    $response->setStatusCode(200);
//                    $response->headers->set('Pragma', 'no-cache');
//                    $response->headers->set('Expires', '0');
//                    $response->send();
//                }

                /*
                 * Genera alfanumerico y número aleatorio
                 */
                // $generator = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',1)),0.5);
                // $generator = rand();
                // Confirmar la transacción
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado'));
            }
            return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al ingresar registro, intente nuevamente'));
            return $this->redirectToRoute($retorna);
        }
        //return $this->forward('SieDiplomaBundle:Tramite:buscaUnidadEducativa',['sie'=>$ue, 'gestion'=>$gestion,'identificador' => $identificador]);
    }

    private function serieRangoDisponible($cantidadEstudiantes,$numerosSerie,$tipoSerie,$gestion,$departamentoUsuario){
        $em = $this->getDoctrine()->getManager();
        $series = '';
        foreach($numerosSerie as $numeroSerie){
            if ($gestion > 2014){
                if ($series == ""){
                    $series = "'".str_pad($numeroSerie.$tipoSerie, 7, "0", STR_PAD_LEFT)."'";
                } else {
                    $series = $series.", '".str_pad($numeroSerie.$tipoSerie, 7, "0", STR_PAD_LEFT)."'";
                }
            } else {
                if ($series == ""){
                    $series = "'".$numeroSerie.$tipoSerie."'";
                } else {
                    $series = $series.", '".$numeroSerie.$tipoSerie."'";
                }
            }
        }

        if ($cantidadEstudiantes>0){
            /*
             * Verifica si todos los series estan disponibles
             */
            $query = $em->getConnection()->prepare("
                select ds.id as id from documento_serie as ds where ds.esanulado = 'false' and ds.departamento_tipo_id = ".$departamentoUsuario." and gestion_id = ".$gestion." and not exists (select * from documento as d where d.documento_serie_id = ds.id) and ds.id in (".$series.")
            ");
            $query->execute();
            $entity = $query->fetchAll();
            if (count($entity)>0){
                if(count($entity) === sizeof($numerosSerie)){
                    $return = "";
                } else {
                    $seriesNoDisponibles = "";
                    foreach ($numerosSerie as $numSer)
                    {
                        $disponible = "";
                        $noDisponible = "";
                        for($i = 0; $i < count($entity); $i++) {
                            if ((string)($entity[$i]["id"]) === (string)($numSer.$tipoSerie)){
                                $disponible = $entity[$i]["id"];
                            }
                        }
                        if($disponible === ""){
                            if ($seriesNoDisponibles == ""){
                                $seriesNoDisponibles = "'".$numSer.$tipoSerie."'";
                            } else {
                                $seriesNoDisponibles = $seriesNoDisponibles.",'".$numSer.$tipoSerie."'";
                            }
                        }
                    }
                    $this->session->set('save', false);
                    $return = "De los número de serie solicitados (".$series."), no se ecuentra disponible el número de serie (".$seriesNoDisponibles.")" ;
                }
            } else {
                $this->session->set('save', false);
                $return = "No tiene asignado los Números de Serie (".$series.") en la gestión ".$gestion." o el numero de serie ya fue utilizada";
            }
        } else {
            $this->session->set('save', false);
            $return = "No seleccionaron bachilleres, favor de intentar nuevamente";
        }
        return $return;
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
$entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => str_pad(strtoupper($numeroSerie), 6, "0", STR_PAD_LEFT).$tipoSerie));
            } else {
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $numeroSerie.$tipoSerie));
            }
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
            $entityDocumento->setTramite($entityTramite[0]);
            $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
            $em->persist($entityDocumento);
            $em->flush();
            return $entityDocumento->getId();
        } else {
            throw new Exception('Se ha producido un error muy grave.');
        }
    }

    private function anulaDocumento($tramiteId, $obs) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $em = $this->getDoctrine()->getManager();

        /*
         * Halla datos del tramite en el cual se trabaja
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityTramite->createQueryBuilder('d')
                ->where('d.tramite = :codTramite')
                ->andWhere('d.documentoTipo = :codTramiteTipo')
                ->orderBy('d.id','desc')
                ->setParameter('codTramite', $tramiteId)
                ->setParameter('codTramiteTipo', 1)
                ->setMaxResults('1');
        $entityDocumento = $query->getQuery()->getResult();

        if (count($entityDocumento) > 0) {
            /*
             * Actualiza el estado del documento a "Anulado"
             */
            $obsEntity = (' Documento anulado por '.$obs.' el '.date_format($fechaActual,"Y/m/d - H:i:s").'');
            $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 2));
            $entityDocumento[0]->setDocumentoEstado($entityDocumentoEstado);
            $entityDocumento[0]->setObs($obsEntity);
            $em->persist($entityDocumento[0]);
            $em->flush();
            $return = "";
        } else {
            $return = "Trámite no encontrado, intente nuevamente";
        }
        return $return;
    }

    public function printReportesDistritoPdfAction(Request $request) {
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $estudiantes = $request->get('ids');
        $arch = $sie.'_distrito_bachilleres_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'JuegosInscripcionPyS_previa.rptdesign&codue='.'80730459'.'&&__format=pdf&'));
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica el tipo de sub sistema al cual pertence la unidad educativa
         */
        $query = $em->getConnection()->prepare("
                select ot.id as codigo, ot.orgcurricula as tipo from institucioneducativa as ie
                inner join orgcurricular_tipo as ot on ot.id = ie.orgcurricular_tipo_id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $sie);
        $query->execute();
        $entitySistema = $query->fetchAll();

        if (count($entitySistema)>0){
            if ($entitySistema[0]["codigo"] == 2){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistritoAlternativa_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
            } else {
                if ($estudiantes != ''){
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistrito_Seleccionado_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_ids='.$estudiantes.'&&__format=pdf&'));
                } else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistrito_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
                }
            }
        } else {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistrito_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
        //return $response;
    }

    /**
     * Imprime el listado en pdf segun el tipo de listado seleccionado impresion de listados
     * @return type
     */
    public function impresionListadoPdfAction(Request $request) {
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $estudiantes = $request->get('estudiantes');
        $lista = $request->get('lista');
        $listaEstudiantes = '';
        if ($estudiantes != ''){
            foreach($estudiantes as $estudiante){
                if ($listaEstudiantes == ""){
                    $listaEstudiantes = $estudiante;
                } else {
                    $listaEstudiantes = $listaEstudiantes.",".$estudiante."";
                }
            }
        }
        $arch = $sie.'_distrito_bachilleres_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'JuegosInscripcionPyS_previa.rptdesign&codue='.'80730459'.'&&__format=pdf&'));
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica el tipo de sub sistema al cual pertence la unidad educativa
         */
        $query = $em->getConnection()->prepare("
                select ot.id as codigo, ot.orgcurricula as tipo from institucioneducativa as ie
                inner join orgcurricular_tipo as ot on ot.id = ie.orgcurricular_tipo_id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $sie);
        $query->execute();
        $entitySistema = $query->fetchAll();
        if (count($entitySistema)>0){
            if ($lista == 100){
                if ($estudiantes != ''){
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesDiplomaImpreso_Seleccionado_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_ids='.$listaEstudiantes.'&&__format=pdf&'));
                } else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesDiplomaImpreso_Todo_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
                }
            } elseif ($lista == 101){
                if ($estudiantes != ''){
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesDiplomaAnulado_Seleccionado_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_ids='.$listaEstudiantes.'&&__format=pdf&'));
                } else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesDiplomaAnulado_Todo_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
                }
            } else {
                if ($entitySistema[0]["codigo"] == 2){
                    //$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistritoAlternativa_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
                    if ($estudiantes != ''){
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteBandejaAlternativa_Seleccionado_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_ids='.$listaEstudiantes.'&p_tipoLista='.$lista.'&&__format=pdf&'));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteBandejaAlternativa_Todo_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_tipoLista='.$lista.'&&__format=pdf&'));
                    }
                } else {
                    if ($estudiantes != ''){
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteBandeja_Seleccionado_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_ids='.$listaEstudiantes.'&p_tipoLista='.$lista.'&&__format=pdf&'));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteBandeja_Todo_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&p_tipoLista='.$lista.'&&__format=pdf&'));
                    }
                }
            }
        } else {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDistrito_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
        //return $response;
    }

    public function printReportesDepartamentalPdfAction(Request $request) {
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $arch = $sie.'_departamental_bachilleres_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_lst_EstudiantesTramiteDepartamento_UnidadEducativa_v1.rptdesign&p_codUE='.$sie.'&p_gestion='.$gestion.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
        //return $response;
    }

    public function printDiplomaPdfAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */
        $estudiantes = $request->get('estudiantes');
        $gestion = $request->get('gestion');
        $ue = $request->get('sie');
        $identificador = $request->get('identificador');

        /*
         * Verifica si tiene diplomas por imprimir
         */
        $query = $em->getConnection()->prepare("
                select * from documento as d
                left join documento_serie as ds on ds.id = d.documento_serie_id
                left join tramite as t on t.id = d.tramite_id
                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                left join estudiante as e on e.id = ei.estudiante_id
                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where d.documento_estado_id = 1 and d.documento_tipo_id = 1 and iec.institucioneducativa_id = :sie::INT and ds.gestion_id = :gestion::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $entity = $query->fetchAll();

        /*
         * Halla el departamento de la Unidad Educativa
         */
        $query = $em->getConnection()->prepare("
                select dt.departamento_tipo_id as codigo from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join distrito_tipo as dt on dt.id = jg.distrito_tipo_id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entityDepto = $query->fetchAll();

        if (count($entityDepto)>0){
            $depto = $entityDepto[0]['codigo'];
        } else {
            $depto = 1;
        }

            $arch = $ue.'_'.$gestion.'_DIPLOMA_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

            if ($depto == 1) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 2) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_lp.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 3) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_cba.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 4) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_tj.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 5) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_bn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 6) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_pn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 7) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_pt.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 8) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_or.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } elseif ($depto == 9) {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_scz.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            } else {
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
            }

            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            //die($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion);
            //$response->send();
            return $response;
    }

    public function impresionDiplomaPdfAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */


        $tipoImpresion = $request->get('tipoImpresion');

        $estudiantes = $request->get('estudiantes');
        $gestion = $request->get('gestion');
        $ue = $request->get('sie');
        $identificador = $request->get('identificador');
        $form = $request->get('form');
        if ($form) {
            $tipoImpresion = $form['tipoImpresion'];
            $ue = $form['sie'];
            $gestion = $form['gestion'];
            $identificador = $form['identificador'];
        } else {
            $tipoImpresion = 1;
        }

        /*
         * Verifica si tiene diplomas por imprimir
         */
        $query = $em->getConnection()->prepare("
                select * from documento as d
                left join documento_serie as ds on ds.id = d.documento_serie_id
                left join tramite as t on t.id = d.tramite_id
                left join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                left join estudiante as e on e.id = ei.estudiante_id
                left join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where d.documento_estado_id = 1 and d.documento_tipo_id = 1 and iec.institucioneducativa_id = :sie::INT and ds.gestion_id = :gestion::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $entity = $query->fetchAll();

        /*
         * Halla el departamento de la Unidad Educativa
         */
        $query = $em->getConnection()->prepare("
                select dt.departamento_tipo_id as codigo from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join distrito_tipo as dt on dt.id = jg.distrito_tipo_id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entityDepto = $query->fetchAll();

        if (count($entityDepto)>0){
            $depto = $entityDepto[0]['codigo'];
        } else {
            $depto = 1;
        }

        //if (count($entity)>0){
            $arch = $ue.'_'.$gestion.'_DIPLOMA_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

            switch ($gestion) {
                case 2009:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2009_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion));
                    }
                    break;
                case 2010:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2010_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2011:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2012 :
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2012_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2014:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2014_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2015:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_lp.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_cba.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_or.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_pt.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_tj.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_scz.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_bn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v3_pn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2016:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_ch.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_lp.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_cba.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_or.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_pt.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_tj.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_scz.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_bn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_pn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2016_v3_ch.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                case 2017:
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_ch.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_lp.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_cba.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_or.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_pt.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_tj.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_scz.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_bn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_pn.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_2017_v3_ch.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
                default :
                    if ($depto == 1) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 2) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 3) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 4) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 5) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 6) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 7) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 8) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    } elseif ($depto == 9) {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }else {
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_default_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo='.$tipoImpresion));
                    }
                    break;
            }


            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            //die($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion);
            //$response->send();
            return $response;

        //} else {
        //    $this->session->getFlashBag()->set('danger', array('title' => 'Alerta!', 'message' => 'La Unidad Educativa '.$ue.' no cuenta con diplomas de bachiller por imprimir'));
        //    $this->session->set('save', true);
        //    $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador));
        //    return $this->redirectToRoute('sie_diploma_tramite_busca_ue');
        //}
    }

    public function printDiplomaListaPdfAction(Request $request) {
        /*
         * Recupera datos del formulario
         */
        $estudiantes = $request->get('estudiantes');
        $gestion = $request->get('gestion');
        $ue = $request->get('sie');
        $identificador = $request->get('identificador');

        $arch = $ue.'_'.$gestion.'_DIPLOMA_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_numeroSerie_v1.rptdesign&__format=pdf&p_codUE='.$ue.'&p_gestion='.$gestion));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        //die($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaEstudiante_unidadeducativa_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion);
        //$response->send();
        return $response;
    }

    private function verificaNivelUnidadEducativa($ue){
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica si la Unidad Educativa puede otorgar diplomas de bachiller o registrar para titulos tecnico medio alternativa
         */
        $query = $em->getConnection()->prepare("
                select * from institucioneducativa as ie
                inner join (select * from institucioneducativa_nivel_autorizado where nivel_tipo_id in (13,15) or nivel_tipo_id > 17) as iena on iena.institucioneducativa_id = ie.id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entity = $query->fetchAll();

        if (count($entity)>0){
            return 1;
        } else {
            return 0;
        }
    }

    private function verificaEstudianteConDobleTramite($id,$tipo){
        $em = $this->getDoctrine()->getManager();

        if($tipo == 1){
            /*
             * Verifica si el estudiante ya tiene un registro o diploma por su inscripcion en siged
             */
            $query = $em->getConnection()->prepare("
                    select e.codigo_rude as rude from estudiante_inscripcion as ei
                    inner join estudiante as e on e.id = ei.estudiante_id
                    where ei.id = ".$id."
                    ");
            $query->execute();
            $entityEst = $query->fetchAll();
        } elseif($tipo == 2) {
            /*
             * Verifica si el estudiante ya tiene un registro o diploma por su inscripcion en diplomas
             */
            $query = $em->getConnection()->prepare("
                    select e.codigo_rude as rude
                    from tramite as t
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    where t.id = ".$id."
                    ");
            $query->execute();
            $entityEst = $query->fetchAll();
        } else {
            /*
             * Verifica si el estudiante ya tiene un registro o diploma  por su inscripcion en siged
             */
            $query = $em->getConnection()->prepare("
                    select e.codigo_rude as rude from estudiante_inscripcion as ei
                    inner join estudiante as e on e.id = ei.estudiante_id
                    where ei.id = ".$id."
                    ");
            $query->execute();
            $entityEst = $query->fetchAll();
        }



        if (count($entityEst)>0){
            $rude = $entityEst[0]["rude"];
            /*
             * Verifica si el estudiante ya tiene un registro o diploma
             */
            $query = $em->getConnection()->prepare("
                    select t.id as tramite_id, e.codigo_rude, t.esactivo as estado_tramite, d.id as documento_id, d.documento_serie_id as serie_id,
                    CASE
                        WHEN iec.nivel_tipo_id = 13 THEN 'regular humanistica'
                        WHEN iec.nivel_tipo_id = 15 THEN 'alternativa humanistica'
                        WHEN iec.nivel_tipo_id > 17 THEN 'alternativa tecnica'
                    END AS subsistema
                    from tramite as t
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    left join (select * from documento where documento_estado_id = 1 and documento_tipo_id = 1) as d on d.tramite_id = t.id
                    where e.codigo_rude = '".$rude."' --and iec.nivel_tipo_id in (13,15)
                    ");
            $query->execute();
            $entity = $query->fetchAll();

            if (count($entity)>0){
                for($i = 0; $i < count($entity); $i++) {
                    if (($entity[$i]["serie_id"]) != ""){
                        return 2;
                    }
                }
                return 1;
            } else {
                return 0;
            }
        } else {
            return 3;
        }
    }

    private function verificaEstudianteConDiplomaActivo($id,$tipo){
        $em = $this->getDoctrine()->getManager();

        /*
         * Verifica si el estudiante ya tiene un registro o diploma
         */
        if ($tipo == 1) {
            $query = $em->getConnection()->prepare("
                    select t.id as tramite_id, e.codigo_rude, t.esactivo as estado_tramite, d.id as documento_id, d.documento_serie_id as serie_id
                    from tramite as t
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join (select * from documento where documento_estado_id = 1 and documento_tipo_id = 1) as d on d.tramite_id = t.id
                    where e.id = ".$id."
                    ");
        } else {
            $query = $em->getConnection()->prepare("
                    select t.id as tramite_id, e.codigo_rude, t.esactivo as estado_tramite, d.id as documento_id, d.documento_serie_id as serie_id
                    from tramite as t
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join (select * from documento where documento_estado_id = 1 and documento_tipo_id = 1) as d on d.tramite_id = t.id
                    where t.id = ".$id."
                    ");
        }
        $query->execute();
        $entity = $query->fetchAll();

        if (count($entity)>0){
            return $entity[0]['codigo_rude']." (".$entity[0]['serie_id'].")";
        } else {
            return "";
        }
    }


    private function procesaTramite($tramiteId, $usuarioId, $flujoSeleccionado,$obs) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $em = $this->getDoctrine()->getManager();
        $tramiteAnulado = 0;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        //$entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->createQuery()->where('tramite = :codTramite')->orderBy('flujoProceso', 'DESC')->setParameter('codTramite', $tramiteId)->setMaxResults('1')->getResult();
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();

        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 1));

        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));

        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');
        if ($entityTramiteDetalle) {
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
             * Extrae la posicion inicial del flujo actual
             */
            $entityFlujoInicio = $em->getRepository('SieAppWebBundle:FlujoProceso');
            $query3 = $entityFlujoInicio->createQueryBuilder('fp')
                    ->where('fp.flujoTipo = :codFlujo')
                    ->orderBy('fp.orden', 'ASC')
                    ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                    ->setMaxResults('1');
            $entityFlujoInicio = $query3->getQuery()->getResult();

            if ($flujoSeleccionado == 'Adelante') {
                $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 3));
                $query = $entityFlujoProceso->createQueryBuilder('fp')
                        ->where('fp.id = :codFlujoProceso')
                        ->orderBy('fp.obs', 'ASC')
                        ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoSig()->getId())
                        ->setMaxResults('1');
            } else {
                if ($flujoSeleccionado == 'Anular') {
                    $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 2));
                    $query = $entityFlujoProceso->createQueryBuilder('fp')
                            ->where('fp.id = :codFlujoProceso')
                            ->orderBy('fp.obs', 'ASC')
                            ->setParameter('codFlujoProceso', $entityFlujoInicio[0]->getId())
                            ->setMaxResults('1');
                } else {
                    if ($flujoSeleccionado == 'Finalizar') {
                        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 3));
                        /*
                         * Extrae la posicion final del flujo actual
                         */
                        $query = $entityFlujoProceso->createQueryBuilder('fp')
                                ->where('fp.flujoTipo = :codFlujo')
                                ->orderBy('fp.orden', 'DESC')
                                ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                                ->setMaxResults('1');
                    } else {
                        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 4));
                        $query = $entityFlujoProceso->createQueryBuilder('fp')
                                ->where('fp.id = :codFlujoProceso')
                                ->orderBy('fp.obs', 'ASC')
                                ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoAnt()->getId())
                                ->setMaxResults('1');
                    }
                }
            }
        } else {
            $query = $entityFlujoProceso->createQueryBuilder('fp')
                    ->where('fp.flujoTipo = :codFlujoTipo')
                    ->andWhere('fp.orden <> 0 ')
                    ->orderBy('fp.orden', 'ASC')
                    ->setParameter('codFlujoTipo', $entityTramite->getFlujoTipo())
                    ->setMaxResults('1');
        }
        $entityFlujoProceso = $query->getQuery()->getResult();

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle
         */
        $entityTramiteDetalleNew = new TramiteDetalle();
        $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
        if ($entityTramiteDetalle) {
            $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
            if ($flujoSeleccionado == 'Adelante'){
                $entityTramiteDetalleNew->setObs('TRÁMITE - PROCESADO');
            } else {
                if ($flujoSeleccionado == 'Anular') {
                    $entityTramiteDetalleNew->setObs('TRÁMITE - ANULADO');
                } else {
                    $entityTramiteDetalleNew->setObs('TRÁMITE - ENTREGADO');
                }
            }
        } else {
            $entityTramiteDetalleNew->setObs('TRÁMITE - RECEPCION DISTRITO');
        }
        $entityTramiteDetalleNew->setTramite($entityTramite);
        $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
        $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
        $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
        $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
        $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

        /*
         * Define el conjunto de valores a ingresar - Tramite Detalle Anterior
         */
        if ($entityTramiteDetalle) {
            $entityTramiteDetalle[0]->setUsuarioDestinatarioId($usuarioId);
            $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
            $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);
            $em->persist($entityTramiteDetalle[0]);
            $em->flush();
        }

        $em->persist($entityTramiteDetalleNew);
        $em->flush();


        if ($entityFlujoProceso[0]->getOrden() == 0){
            $entityTramite->setEsactivo('0');
            $em->persist($entityTramite);
            $em->flush();
        }
    }

    public function buscaEstudiantesUnidadEducativa($sie, $gestion, $identificador, $lista) {
        $em = $this->getDoctrine()->getManager();

        /*
         * Ingresa si es para ver listados para la impresion
         */
        if ($identificador == 0 and $lista < 100){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                from (select * from institucioneducativa_curso where institucioneducativa_id = :sie::INT and gestion_tipo_id = :gestion::double precision) as iec
                inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id
                inner join (select * from tramite_detalle where id in (select max(td2.id) as id from tramite_detalle as td2 inner join tramite as t2 on t2.id = td2.tramite_id where td2.tramite_estado_id <> 4 and td2.flujo_proceso_id = :flujo::INT group by td2.tramite_id)) as td on td.tramite_id = t.id
                inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                left join documento as d on d.tramite_id = t.id and documento_estado_id = 1 and documento_tipo_id in (1,3,4,5)
                left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join pais_tipo as pt on pt.id = e.pais_tipo_id
            ");
        }


        /*
         * Ingresa si es para ver listados para los diplomas impresos
         */
        if ($identificador == 0 and $lista == 100){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                FROM tramite t
                INNER JOIN estudiante_inscripcion ei ON ei.id = t.estudiante_inscripcion_id
                INNER JOIN estudiante e ON e.id = ei.estudiante_id
                INNER JOIN genero_tipo gt ON gt.id = e.genero_tipo_id
                INNER JOIN estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                INNER JOIN institucioneducativa_curso iec ON iec.id = ei.institucioneducativa_curso_id
                INNER JOIN institucioneducativa ie ON ie.id = iec.institucioneducativa_id
                INNER JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                INNER JOIN pais_tipo pt ON pt.id = e.pais_tipo_id
                INNER JOIN lugar_tipo lt2 ON lt2.id = jg.lugar_tipo_id_distrito
                INNER JOIN ciclo_tipo ct ON ct.id = iec.ciclo_tipo_id
                INNER JOIN (select * from documento where documento_estado_id = 1 and documento_tipo_id in (1,3,4,5)) as d on d.tramite_id = t.id
                LEFT JOIN lugar_tipo lt ON lt.id = e.lugar_prov_nac_tipo_id
                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                WHERE t.esactivo = true AND iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                ORDER BY e.paterno, e.materno, e.nombre
            ");
        }

        /*
         * Ingresa si es para ver listados para los diplomas anulados
         */
        if ($identificador == 0 and $lista == 101){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                FROM tramite t
                INNER JOIN estudiante_inscripcion ei ON ei.id = t.estudiante_inscripcion_id
                INNER JOIN estudiante e ON e.id = ei.estudiante_id
                INNER JOIN genero_tipo gt ON gt.id = e.genero_tipo_id
                INNER JOIN estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                INNER JOIN institucioneducativa_curso iec ON iec.id = ei.institucioneducativa_curso_id
                INNER JOIN institucioneducativa ie ON ie.id = iec.institucioneducativa_id
                INNER JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                INNER JOIN pais_tipo pt ON pt.id = e.pais_tipo_id
                INNER JOIN lugar_tipo lt2 ON lt2.id = jg.lugar_tipo_id_distrito
                INNER JOIN ciclo_tipo ct ON ct.id = iec.ciclo_tipo_id
                INNER JOIN (select * from documento where documento_estado_id = 2 and documento_tipo_id  in (1,3,4,5)) as d on d.tramite_id = t.id
                LEFT JOIN lugar_tipo lt ON lt.id = e.lugar_prov_nac_tipo_id
                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                WHERE t.esactivo = true AND iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                ORDER BY e.paterno, e.materno, e.nombre
            ");
        }

        /*
         * Ingresa si es para recepcionar distrito
         */
        if ($identificador == 13) {
            $query = $em->getConnection()->prepare('
                        select v.*, lt2.codigo as depto_nacimiento_id, lt2.lugar as depto_nacimiento from get_estudiante_candidatos_bachiller(:sie::INT,:gestion::INT) as v
                        left join lugar_tipo as lt1 on lt1.id = v.lugar_nacimiento_id
                        left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    ');
        }
        /*
         * Ingresa si es para recepcionar departamento
         */
        if ($identificador == 14) {
            $query = $em->getConnection()->prepare("
                        select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                        CASE
                            WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                            WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                            WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                        END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id
                        from tramite as t
                        inner join (
                            select * from tramite_detalle where id in (
                                select max(trad.id) from tramite_detalle as trad
                                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                                INNER JOIN estudiante_inscripcion as esti on esti.id = tram.estudiante_inscripcion_id
                                INNER JOIN institucioneducativa_curso as insc on insc.id = esti.institucioneducativa_curso_id
                                where tramite_estado_id <> 4 and insc.institucioneducativa_id = :sie::INT and insc.gestion_tipo_id  = :gestion::INT group by trad.tramite_id
                            ) and flujo_proceso_id = 2
                        ) as td on td.tramite_id = t.id
                        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                        left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                        left join pais_tipo as pt on pt.id = e.pais_tipo_id
                        where td.flujo_proceso_id = 2 and iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                        order by e.paterno, e.materno, e.nombre
                    ");
        }
        /*
         * Ingresa si es para autorizar diploma
         */
        if ($identificador == 15) {
            $query = $em->getConnection()->prepare("
                        select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                        CASE
                            WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                            WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                            WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                        END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id
                        from tramite as t
                        inner join (
                            select * from tramite_detalle where id in (
                                select max(trad.id) from tramite_detalle as trad
                                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                                INNER JOIN estudiante_inscripcion as esti on esti.id = tram.estudiante_inscripcion_id
                                INNER JOIN institucioneducativa_curso as insc on insc.id = esti.institucioneducativa_curso_id
                                where tramite_estado_id <> 4 and insc.institucioneducativa_id = :sie::INT and insc.gestion_tipo_id  = :gestion::INT group by trad.tramite_id
                            ) and flujo_proceso_id = 3
                        ) as td on td.tramite_id = t.id
                        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                        left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                        left join pais_tipo as pt on pt.id = e.pais_tipo_id
                        where td.flujo_proceso_id = 3 and iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                        order by e.paterno, e.materno, e.nombre
                    ");
        }
        /*
         * Ingresa si es para imprimir diploma
         */
        if ($identificador == 16) {
            $query = $em->getConnection()->prepare("
                        select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                        CASE
                            WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                            WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                            WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                        END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id
                        from tramite as t
                        inner join (
                            select * from tramite_detalle where id in (
                                select max(trad.id) from tramite_detalle as trad
                                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                                INNER JOIN estudiante_inscripcion as esti on esti.id = tram.estudiante_inscripcion_id
                                INNER JOIN institucioneducativa_curso as insc on insc.id = esti.institucioneducativa_curso_id
                                where tramite_estado_id <> 4 and insc.institucioneducativa_id = :sie::INT and insc.gestion_tipo_id  = :gestion::INT group by trad.tramite_id
                            ) and flujo_proceso_id = 4
                        ) as td on td.tramite_id = t.id
                        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                        left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                        left join pais_tipo as pt on pt.id = e.pais_tipo_id
                        where td.flujo_proceso_id = 4 and iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                        order by e.paterno, e.materno, e.nombre
                    ");
        }
        /*
         * Ingresa si es para entregar diploma por el departamental
         */
        if ($identificador == 17) {
            $query = $em->getConnection()->prepare("
                        select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                        CASE
                            WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                            WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                            WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                        END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id
                        from tramite as t
                        inner join (
                            select * from tramite_detalle where id in (
                                select max(trad.id) from tramite_detalle as trad
                                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                                INNER JOIN estudiante_inscripcion as esti on esti.id = tram.estudiante_inscripcion_id
                                INNER JOIN institucioneducativa_curso as insc on insc.id = esti.institucioneducativa_curso_id
                                where tramite_estado_id <> 4 and insc.institucioneducativa_id = :sie::INT and insc.gestion_tipo_id  = :gestion::INT group by trad.tramite_id
                            ) and flujo_proceso_id = 5
                        ) as td on td.tramite_id = t.id
                        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                        left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                        left join pais_tipo as pt on pt.id = e.pais_tipo_id
                        where td.flujo_proceso_id = 5 and iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                        order by e.paterno, e.materno, e.nombre
                    ");
        }
        /*
         * Ingresa si es para entregar diploma por el distrito
         */
        if ($identificador == 18) {
            $query = $em->getConnection()->prepare("
                        select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                        CASE
                            WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                            WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                            WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                        END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id
                        from tramite as t
                        inner join (
                            select * from tramite_detalle where id in (
                                select max(trad.id) from tramite_detalle as trad
                                INNER JOIN tramite as tram on tram.id = trad.tramite_id
                                INNER JOIN estudiante_inscripcion as esti on esti.id = tram.estudiante_inscripcion_id
                                INNER JOIN institucioneducativa_curso as insc on insc.id = esti.institucioneducativa_curso_id
                                where tramite_estado_id <> 4 and insc.institucioneducativa_id = :sie::INT and insc.gestion_tipo_id = :gestion::INT group by trad.tramite_id
                            ) and flujo_proceso_id = 6
                        ) as td on td.tramite_id = t.id
                        inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                        left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                        left join pais_tipo as pt on pt.id = e.pais_tipo_id
                        where td.flujo_proceso_id = 6 and iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                        order by e.paterno, e.materno, e.nombre
                    ");
        }
        $query->bindValue(':sie', $sie);
        $query->bindValue(':gestion', $gestion);
        if ($identificador == 0 and $lista < 100){
            $query->bindValue(':flujo', $lista);
        }
        $query->execute();
        $entity = $query->fetchAll();
        return $entity;
    }

    public function buscaEstudiantesCentroEducacionAlternativa($sie, $gestion, $sucursal, $periodo, $identificador, $lista) {

        $em = $this->getDoctrine()->getManager();

        /*
         * Ingresa si es para ver listados para la impresion
         */
        if ($identificador == 0 and $lista < 100){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, td.id as tramite_detalle_id, t.flujo_tipo_id as flujo_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento, td.flujo_proceso_id,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                from tramite as t
                inner join (select * from tramite_detalle where id in (select max(id) from tramite_detalle where tramite_estado_id <> 4 and flujo_proceso_id = :flujo::INT group by tramite_id)) as td on td.tramite_id = t.id
                inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                left join (select * from documento where id in (select max(id) from documento where documento_estado_id = 1 group by tramite_id)) as d on d.tramite_id = t.id
                left join lugar_tipo as lt on lt.id = e.lugar_prov_nac_tipo_id
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join pais_tipo as pt on pt.id = e.pais_tipo_id
                where iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                order by e.paterno, e.materno, e.nombre
            ");
        }


        /*
         * Ingresa si es para ver listados para los diplomas impresos
         */
        if ($identificador == 0 and $lista == 100){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                FROM tramite t
                INNER JOIN estudiante_inscripcion ei ON ei.id = t.estudiante_inscripcion_id
                INNER JOIN estudiante e ON e.id = ei.estudiante_id
                INNER JOIN genero_tipo gt ON gt.id = e.genero_tipo_id
                INNER JOIN estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                INNER JOIN institucioneducativa_curso iec ON iec.id = ei.institucioneducativa_curso_id
                INNER JOIN institucioneducativa ie ON ie.id = iec.institucioneducativa_id
                INNER JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                INNER JOIN pais_tipo pt ON pt.id = e.pais_tipo_id
                INNER JOIN lugar_tipo lt2 ON lt2.id = jg.lugar_tipo_id_distrito
                INNER JOIN ciclo_tipo ct ON ct.id = iec.ciclo_tipo_id
                INNER JOIN (select * from documento where documento_estado_id = 1 and documento_tipo_id = 1) as d on d.tramite_id = t.id
                LEFT JOIN lugar_tipo lt ON lt.id = e.lugar_prov_nac_tipo_id
                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                WHERE t.esactivo = true AND iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                ORDER BY e.paterno, e.materno, e.nombre
            ");
        }

        /*
         * Ingresa si es para ver listados para los diplomas anulados
         */
        if ($identificador == 0 and $lista == 101){
            $query = $em->getConnection()->prepare("
                select t.id as tramite_id, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.fecha_nacimiento, mt.estadomatricula as desc_estado_matricula, t.tramite as numero_tramite, case pt.id when 1 then lt1.lugar else pt.pais end as depto_nacimiento, pt.pais as pais_nacimiento,
                CASE
                    WHEN iec.nivel_tipo_id = 13 THEN 'REGULAR HUMANISTICA'
                    WHEN iec.nivel_tipo_id = 15 THEN 'ALTERNATIVA HUMANISTICA'
                    WHEN iec.nivel_tipo_id > 17 THEN 'ALTERNATIVA TÉCNICA'
                END AS subsistema, pet.id as periodo_id, pet.periodo as periodo, iec.nivel_tipo_id as nivel_id, d.documento_serie_id as titulo
                FROM tramite t
                INNER JOIN estudiante_inscripcion ei ON ei.id = t.estudiante_inscripcion_id
                INNER JOIN estudiante e ON e.id = ei.estudiante_id
                INNER JOIN genero_tipo gt ON gt.id = e.genero_tipo_id
                INNER JOIN estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                INNER JOIN institucioneducativa_curso iec ON iec.id = ei.institucioneducativa_curso_id
                INNER JOIN institucioneducativa ie ON ie.id = iec.institucioneducativa_id
                INNER JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
                INNER JOIN pais_tipo pt ON pt.id = e.pais_tipo_id
                INNER JOIN lugar_tipo lt2 ON lt2.id = jg.lugar_tipo_id_distrito
                INNER JOIN ciclo_tipo ct ON ct.id = iec.ciclo_tipo_id
                INNER JOIN (select * from documento where documento_estado_id = 2 and documento_tipo_id = 1) as d on d.tramite_id = t.id
                LEFT JOIN lugar_tipo lt ON lt.id = e.lugar_prov_nac_tipo_id
                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                WHERE t.esactivo = true AND iec.gestion_tipo_id = :gestion::INT and iec.institucioneducativa_id = :sie::INT
                ORDER BY e.paterno, e.materno, e.nombre
            ");
        }

        /*
         * Ingresa si es para recepcionar distrito
         */

        if ($identificador == 13) {
            if ($gestion > 2011){
                $query = $em->getConnection()->prepare("
                    select distinct
                      dt.id as dependencia_tipo_id,
                      dt.dependencia,
                      oct.id as orgcurricular_tipo_id,
                      oct.orgcurricula,
                      ie.le_juridicciongeografica_id,
                      ie.id as institucioneducativa_id,
                      ie.institucioneducativa,
                      f.gestion_tipo_id,
                      a.codigo as nivel_tipo_id,
                      a.facultad_area as nivel,
                      b.codigo as ciclo_tipo_id,
                      b.especialidad as ciclo,
                      d.codigo as grado_tipo_id,
                      d.acreditacion as grado,
                      p.id as paralelo_tipo_id,
                      p.paralelo,
                      q.id as turno_tipo_id,
                      q.turno,
                      f.periodo_tipo_id,
                      case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo,
                      j.id as estudiante_id,
                      j.codigo_rude,
                      j.carnet_identidad,
                      j.pasaporte,
                      j.paterno,
                      j.materno,
                      j.nombre,
                      j.genero_tipo_id,
                      case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,
                      j.fecha_nacimiento,
                      j.localidad_nac,
                      emt.id as estadomatricula_tipo_id,
                      emt.estadomatricula,
                      j.complemento,
                      i.id as estudiante_inscripcion_id,
                      CASE
                        WHEN a.codigo = 13 THEN
                        'Regular Humanística'
                        WHEN a.codigo = 15 THEN
                        'Alternativa Humanística'
                        WHEN a.codigo > 17 THEN
                        'Alternativa Técnica'
                      END AS subsistema,
                      j.lugar_prov_nac_tipo_id as lugar_nacimiento_id,
                      lt2.codigo as depto_nacimiento_id,
                      lt2.lugar as depto_nacimiento

                    /*f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,f.periodo_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                    ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                    ,q.turno,p.paralelo,j.codigo_rude,j.paterno,j.materno,j.nombre,case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                    ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac
                    ,l.modulo,r.nota_tipo,o.nota_cuantitativa,o.nota_cualitativa*/
                     from superior_facultad_area_tipo a
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                            inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                            inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                            inner join institucioneducativa as ie on ie.id = h.institucioneducativa_id
                                inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                inner join estudiante j on i.estudiante_id=j.id
                                left join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id
                                    left join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id
                                    left join institucioneducativa_curso_oferta m on m.superior_modulo_periodo_id=k.id
                                    and m.insitucioneducativa_curso_id=h.id
                                    left join estudiante_asignatura n on n.institucioneducativa_curso_oferta_id=m.id
                                    and n.estudiante_inscripcion_id=i.id
                                    left join estudiante_nota o on o.estudiante_asignatura_id=n.id
                    inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                    inner join turno_tipo q on h.turno_tipo_id=q.id
                    left join nota_tipo r on o.nota_tipo_id=r.id
                    inner join estadomatricula_tipo as emt on emt.id = i.estadomatricula_tipo_id
                    inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                    inner join orgcurricular_tipo as oct on oct.id = ie.orgcurricular_tipo_id
                    LEFT JOIN lugar_tipo as lt1 on lt1.id = j.lugar_prov_nac_tipo_id
                    LEFT JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    where emt.id in (4,5,55) and f.gestion_tipo_id = :gestion::INT and f.institucioneducativa_id = :sie::INT
                    and f.sucursal_tipo_id = :sucursal::INT and f.periodo_tipo_id = :periodo::INT
                    and a.codigo = 15 and b.codigo = 2 and d.codigo = 3
                    order by  paterno, materno, nombre
                ");
            } else {
                $query = $em->getConnection()->prepare("
                    select distinct
                      dt.id as dependencia_tipo_id,
                      dt.dependencia,
                      oct.id as orgcurricular_tipo_id,
                      oct.orgcurricula,
                      ie.le_juridicciongeografica_id,
                      ie.id as institucioneducativa_id,
                      ie.institucioneducativa,
                      f.gestion_tipo_id,
                      a.codigo as nivel_tipo_id,
                      a.facultad_area as nivel,
                      b.codigo as ciclo_tipo_id,
                      b.especialidad as ciclo,
                      d.codigo as grado_tipo_id,
                      d.acreditacion as grado,
                      p.id as paralelo_tipo_id,
                      p.paralelo,
                      q.id as turno_tipo_id,
                      q.turno,
                      f.periodo_tipo_id,
                      case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo,
                      j.id as estudiante_id,
                      j.codigo_rude,
                      j.carnet_identidad,
                      j.pasaporte,
                      j.paterno,
                      j.materno,
                      j.nombre,
                      j.genero_tipo_id,
                      case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,
                      j.fecha_nacimiento,
                      j.localidad_nac,
                      emt.id as estadomatricula_tipo_id,
                      emt.estadomatricula,
                      j.complemento,
                      i.id as estudiante_inscripcion_id,
                      CASE
                        WHEN a.codigo = 13 THEN
                        'Regular Humanística'
                        WHEN a.codigo = 15 THEN
                        'Alternativa Humanística'
                        WHEN a.codigo > 17 THEN
                        'Alternativa Técnica'
                      END AS subsistema,
                      j.lugar_prov_nac_tipo_id as lugar_nacimiento_id,
                      lt2.codigo as depto_nacimiento_id,
                      lt2.lugar as depto_nacimiento

                    /*f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,f.periodo_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                    ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                    ,q.turno,p.paralelo,j.codigo_rude,j.paterno,j.materno,j.nombre,case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                    ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac
                    ,l.modulo,r.nota_tipo,o.nota_cuantitativa,o.nota_cualitativa*/
                     from superior_facultad_area_tipo a
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                            inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                            inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                            inner join institucioneducativa as ie on ie.id = h.institucioneducativa_id
                                inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                inner join estudiante j on i.estudiante_id=j.id
                                left join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id
                                    left join superior_modulo_tipo l on l.id=k.superior_modulo_tipo_id
                                    left join institucioneducativa_curso_oferta m on m.superior_modulo_periodo_id=k.id
                                    and m.insitucioneducativa_curso_id=h.id
                                    left join estudiante_asignatura n on n.institucioneducativa_curso_oferta_id=m.id
                                    and n.estudiante_inscripcion_id=i.id
                                    left join estudiante_nota o on o.estudiante_asignatura_id=n.id
                    inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                    inner join turno_tipo q on h.turno_tipo_id=q.id
                    left join nota_tipo r on o.nota_tipo_id=r.id
                    inner join estadomatricula_tipo as emt on emt.id = i.estadomatricula_tipo_id
                    inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                    inner join orgcurricular_tipo as oct on oct.id = ie.orgcurricular_tipo_id
                    LEFT JOIN lugar_tipo as lt1 on lt1.id = j.lugar_prov_nac_tipo_id
                    LEFT JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    where emt.id in (4,5,55) and f.gestion_tipo_id = :gestion::INT and f.institucioneducativa_id = :sie::INT
                    and f.sucursal_tipo_id = :sucursal::INT and f.periodo_tipo_id = :periodo::INT
                    --and a.codigo = 5 and b.codigo = 2 and d.codigo = 2
                    and a.codigo = 15 and b.codigo = 2 and d.codigo = 3
                    order by  paterno, materno, nombre
                ");
            }
        }

        $query->bindValue(':sie', $sie);
        $query->bindValue(':gestion', $gestion);
        $query->bindValue(':sucursal', $sucursal);
        $query->bindValue(':periodo', $periodo);
        if ($identificador == 0 and $lista < 100){
            $query->bindValue(':flujo', $lista);
        }
        $query->execute();
        $entity = $query->fetchAll();
        return $entity;
    }

    /*
     * Reactiva el tramite devolviendolo a la bandeja que se desee
     */
    private function reactivaTramite($tramiteId,$procesoId,$usuarioId,$obs){
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
            $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstado);
            //$entityTramiteDetalle[0]->setObs($entityTramiteDetalle[0]->getObs().' - REACTIVADO');
            $entityTramiteDetalle[0]->setUsuarioDestinatarioId($usuarioId);
            $em->persist($entityTramiteDetalle[0]);
            $em->flush();

            /*
             * Se registra el proceso para llevar el tramite a la badeja de impresion
             */

            $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
            $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $entityTramiteDetalle[0]->getTramite()->getId()));
            $entityTramiteDetalleEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 1));
            $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('id' => $procesoId));

            $entityTramiteDetalleNew = new TramiteDetalle();
            $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
            $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
            $entityTramiteDetalleNew->setObs(strtoupper('Trámite reactivado por '.$obs.', '.date_format($fechaActual,"d/m/Y - H:i:s").''));
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

    /**
     * Despliega formulario de registro para una reactivacion de tramite
     * @return type
     */
    public function reactivarTramiteAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:Tramite:Legalizacion.html.twig', array(
                    'form' => $this->creaFormularioLegalizador('sie_diploma_tramite_reactivacion_save', '', $gestionActual, 17, 'Reactivar')->createView()
                    , 'titulo' => 'Reactivación de Trámite'
                    , 'subtitulo' => 'Diplomas de Bachiller'
                    , 'obs' => 1
        ));
    }

    /**
     * Reactiva y anula diploma de bachiller de la legalizacion de diploma
     * @return type
     */
    public function reactivarTramiteSaveAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($form) {
            $serie = $form['texto'];
            //$ges = $form['gestion'];
            $obs = $form['obs'];
            $identificador = $form['identificador'];
            /*
             * Extrae en codigo de departamento del usuario
             */
            $query = $em->getConnection()->prepare("
               select lugar_tipo_id from usuario_rol where usuario_id = ".$id_usuario."  and rol_tipo_id = ".$identificador." order by id asc limit 1
            ");
            $query->execute();
            $entityUsuario = $query->fetchAll();

            $departamentoUsuario = 0;
            if (count($entityUsuario) > 0){
                $departamentoUsuario = $entityUsuario[0]["lugar_tipo_id"];
            }

            /*
             * Extrae la gestion del numero de serie
             */
            $query = $em->getConnection()->prepare("
               select id, gestion_id, departamento_tipo_id from documento_serie where id like '".$serie."' limit 1
            ");
            $query->execute();
            $entitySerie = $query->fetchAll();
            if(count($entitySerie) > 0){
                $ges = $entitySerie[0]["gestion_id"];
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento con número de serie "'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
            }


            /*
             * Verifica si tiene documentos supletorios
             */
            $query = $em->getConnection()->prepare("
               select * from documento where tramite_id in (select tramite_id from documento where documento_serie_id like '".$serie."') and documento_tipo_id = 9 and documento_estado_id = 1
            ");
            $query->execute();
            $entityDocumentoSupletorio = $query->fetchAll();
            if(count($entityDocumentoSupletorio) > 0){
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento con número de serie "'. $serie .'" cuenta con Certificados Supletorios emitidos, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
            }


            if ($ges > 2014) {
                $query = $em->getConnection()->prepare("
                    select d.tramite_id as tramite_id, cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) as numero_serie, right(d.documento_serie_id,1) as tipo_serie
                    , pt.id as proceso_id, pt.proceso_tipo as proceso, mt.id as estadomatricula_id, mt.estadomatricula as estadomatricula
                    , case mt.id when 5 then true when 26 then true when 37 then true when 55 then true when 58 then true else false end as promovido
                    , case pt.id when 7 then true else false end as estadofintramite
                    , case d.documento_estado_id when 1 then true else false end as estadodocumento
                    , e.codigo_rude as rude
                    from documento as d
                    inner join documento_serie as ds on ds.id = d.documento_serie_id
                    inner join tramite as t on t.id = d.tramite_id
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                    left join (select * from tramite_detalle where id in (select max(id) from tramite_detalle where flujo_proceso_id in (select id from flujo_proceso where proceso_id = 7) group by tramite_id)) as td on td.tramite_id = t.id
                    left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                    left join proceso_tipo as pt on pt.id = fp.proceso_id
                    where d.documento_tipo_id = 1 and d.documento_serie_id in ('".str_pad(strtoupper($serie), 7, "0", STR_PAD_LEFT)."') and ds.gestion_id = ".$ges." and ds.departamento_tipo_id = ".$departamentoUsuario."
                ");
            } else {
                $query = $em->getConnection()->prepare("
                    select d.tramite_id as tramite_id, cast(left(d.documento_serie_id,(char_length(d.documento_serie_id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) as numero_serie, right(d.documento_serie_id,(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end)) as tipo_serie
                    , pt.id as proceso_id, pt.proceso_tipo as proceso, mt.id as estadomatricula_id, mt.estadomatricula as estadomatricula
                    , case mt.id when 5 then true when 26 then true when 37 then true when 55 then true when 58 then true else false end as promovido
                    , case pt.id when 7 then true else false end as estadofintramite
                    , case d.documento_estado_id when 1 then true else false end as estadodocumento
                    , e.codigo_rude as rude
                    from documento as d
                    inner join documento_serie as ds on ds.id = d.documento_serie_id
                    inner join tramite as t on t.id = d.tramite_id
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join estadomatricula_tipo as mt on mt.id = ei.estadomatricula_tipo_id
                    left join (select * from tramite_detalle where id in (select max(id) from tramite_detalle where flujo_proceso_id in (select id from flujo_proceso where proceso_id = 7) group by tramite_id)) as td on td.tramite_id = t.id
                    left join flujo_proceso as fp on fp.id = td.flujo_proceso_id
                    left join proceso_tipo as pt on pt.id = fp.proceso_id
                    where d.documento_tipo_id = 1 and d.documento_serie_id in ('".strtoupper($serie)."') and ds.gestion_id = ".$ges." and ds.departamento_tipo_id = ".$departamentoUsuario."
                ");
            }
            $query->execute();
            $entity = $query->fetchAll();
            if (count($entity) > 0){
                if (count($entity) < 2){
                    if (!$entity[0]["estadofintramite"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El estudiante con codigo rude "'.$entity[0]["rude"].'" y con número de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" no concluyo su trámite'));
                        return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                    } elseif (!$entity[0]["estadodocumento"]){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con numero de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" se encuentra anulado, no es posible realizar la anulación del tramite'));
                        return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                    } else {
                        $em->getConnection()->beginTransaction();
                        try {
                            //$idDocumento = $this->generaDocumento($entity[0]["tramite_id"], $id_usuario, 2, $entity[0]["numero_serie"], $entity[0]["tipo_serie"], $ges, $fechaActual);
                            $tramiteId = $entity[0]["tramite_id"];
                            $procesoId = 4; // Codigo del proceso para mostrar en la bandeja de impresion
                            //$obs = 'DIPLOMA ANULADO POR REACTIVACIÓN DE TRÁMITE PARA SU REIMPRESIÓN - '.date_format($fechaActual,"Y/m/d - H:i:s").'';
                            $error = $this->reactivaTramite($tramiteId,$procesoId,$id_usuario,$obs);
                            if ($error != ""){
                                $em->getConnection()->rollback();
                                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $error));
                                return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                            }
                            $error = $this->anulaDocumento($tramiteId,$obs);
                            if ($error != ""){
                                $em->getConnection()->rollback();
                                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $error));
                                return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                            }
                            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El documento con numero de serie "'.$entity[0]["numero_serie"].$entity[0]["tipo_serie"].'" fue reactivado'));
                            $em->getConnection()->commit();
                            return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                        } catch (Exception $ex) {
                            $em->getConnection()->rollback();
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
                            return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                        }
                    }
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, inconsistencia de datos con el documento "'. $serie .'" , intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el documento con número de serie "'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_reactivacion');
        }
    }
}
