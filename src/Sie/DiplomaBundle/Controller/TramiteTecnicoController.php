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

class TramiteTecnicoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Despliega formulario para el registro del tramite y tipo de diploma tecnico
     * @return type
     */
    public function recepcionTramiteAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('sie_diploma_tramite_tecnico_recepcion_registro'))
                    ->add('rude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                    ->add('gestion', 'entity', array('data' => '', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('gt')
                                    ->where('gt.id > 2008')
                                    ->orderBy('gt.id', 'DESC');
                        },
                    ))
                    ->add('diploma','choice',  
                      array('label' => 'Modalidad',
                            'choices' => array( '3' => 'Agropecuario'
                                                ,'4' => 'Industrial'
                                                ,'5' => 'Comercial'                       
                                                ),
                            'data' => '', 'attr' => array('class' => 'form-control')))
                    ->add('serie', 'text', array('label' => 'Nro. Serie', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                    ->add('identificador', 'hidden', array('attr' => array('value' => 7)))
                    ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm()->createView();

        return $this->render('SieDiplomaBundle:TramiteTecnico:FormularioTramiteTecnicoDiploma.html.twig', array(
                    'form' => $form
                    , 'titulo' => 'Recepción'
                    , 'subtitulo' => 'Diplomas Técnicos'
        ));
    }

    
    
    /**
     * Registro de la legalizacion de diploma
     * @return type
     */
    public function recepcionTramiteRegistroAction(Request $request) {
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
            $rude = $form['rude'];
            $ges = $form['gestion'];
            $tipoDiploma = $form['diploma'];
            $serie = $form['serie'];
            $identificador = $form['identificador'];
            if($identificador == 7){
                $identificador = 16;
            }
            
            /*
             * Extrae en codigo de departamento del usuario
             */
            $query = $em->getConnection()->prepare("
               select lugar_tipo_id from usuario_rol where usuario_id = ".$id_usuario."  and rol_tipo_id = ".$identificador." limit 1
            ");
            $query->execute();
            $entityUsuario = $query->fetchAll();

            $departamentoUsuario = 0;
            if (count($entityUsuario) > 0){
                $departamentoUsuario = $entityUsuario[0]["lugar_tipo_id"];
            } 
            $em->getConnection()->beginTransaction();
            try {
                /*
                 * Extrae los datos de estudiante en funcion a su codigo rude y gestion de inscripcion
                 */
                $query = $em->getConnection()->prepare("
                    select e.id as estudianteId, ei.id as estudianteInscripcionId, iec.gestion_tipo_id from estudiante as e
                    inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    where e.codigo_rude = '".$rude."' and iec.gestion_tipo_id = ".$ges."
                    and case when iec.gestion_tipo_id >=2011 then (iec.nivel_tipo_id=13 and iec.grado_tipo_id=6) or (iec.nivel_tipo_id=15 and iec.grado_tipo_id=3) when iec.gestion_tipo_id <= 2010 then (iec.nivel_tipo_id=3 and iec.grado_tipo_id=4) or (iec.nivel_tipo_id=5 and iec.grado_tipo_id=2) else iec.nivel_tipo_id=13 and iec.grado_tipo_id=6 end 

                ");
                $query->execute();
                $entityEstudianteInscripcion = $query->fetchAll();

                if (count($entityEstudianteInscripcion) > 0){
                    $estudianteInscripcionId = $entityEstudianteInscripcion[0]["estudianteinscripcionid"];

                    /*
                     * Verifica si ya cuenta con un diploma de nivel tecnico
                     */
                    $query = $em->getConnection()->prepare("
                        select * from documento as d
                        inner join tramite as t on t.id = d.tramite_id
                        inner join estudiante_inscripcion as ei on ei.estudiante_id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        where e.codigo_rude = '".$rude."' and t.tramite_tipo = 2 and d.documento_tipo_id = ".$tipoDiploma." and d.documento_estado_id = 1
                    ");
                    $query->execute();
                    $entityEstudianteDiplomaTecnico = $query->fetchAll();
                    if (count($entityEstudianteDiplomaTecnico) == 0){ 
                        $entityTramite = new Tramite();
                        /*
                         * Verifica si el numero de serie esta disponible y asignado a su departamento
                         */
                        $query = $em->getConnection()->prepare("                            
                            select ds.id as id, ds.gestion_id as gestion_serie, cast(left(ds.id,(char_length(ds.id)-(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end))) as integer) as numero_serie, right(ds.id,(case ds.gestion_id when 2010 then 2 when 2013 then 2 else 1 end)) as tipo_serie from documento_serie as ds where ds.esanulado = 'false' and ds.departamento_tipo_id = ".$departamentoUsuario." and not exists (select * from documento as d where d.documento_serie_id = ds.id) and (case ds.gestion_id when 2011 then ds.id = '".$serie."' when 2012 then ds.id = '".$serie."' when 2013 then ds.id = '".$serie."' else ds.id = (lpad('".$serie."',7,'0')::varchar) end)
                        ");
                        $query->execute();
                        $entitySerie = $query->fetchAll();
                        if (count($entitySerie)<=0){
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, el numero de serie "'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                            return $this->redirectToRoute('sie_diploma_tramite_tecnico_recepcion');
                        }

                        $numeroSerie = $entitySerie[0]["numero_serie"];
                        $tipoSerie = $entitySerie[0]["tipo_serie"];
                        $gestionSerie = $entitySerie[0]["gestion_serie"];

                        /*REGISTRAR EL DOCUMENTO*/

                        /*
                         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
                         */
                        $entityTramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('id' => 2));
                        $entityFlujoTipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->findOneBy(array('id' => 2));
                        $entityEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));

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
                        $entityTramite->setGestionId($ges);
                        $em->persist($entityTramite);
                        $em->flush();

                        $entityTramite->setTramite($entityTramite->getId());
                        $em->persist($entityTramite);
                        $em->flush();

                        /*
                         * Extra el id del registro ingresado de la tabla tramite 
                         */
                        $tramiteId = $entityTramite->getId();

                        $error = $this->procesaTramite($tramiteId, $id_usuario, 'Adelante','');


                        $documentoId = $this->generaDocumento($tramiteId, $id_usuario, $tipoDiploma, $numeroSerie, $tipoSerie, $gestionSerie, $fechaActual);

                        //$error = $this->procesaTramite($tramiteId, $id_usuario, 'Adelante','');
                        $em->getConnection()->commit();
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Trámite registrado'));

                        return $this->redirectToRoute('sie_diploma_tramite_tecnico_recepcion');

                    } else {
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, ya cuenta con un diploma técnico"'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                        return $this->redirectToRoute('sie_diploma_tramite_tecnico_recepcion');
                    }                     
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, no cuenta con una inscripción válida para otorgar al estudiante un diploma"'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                    return $this->redirectToRoute('sie_diploma_tramite_tecnico_recepcion');
                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
                return $this->redirectToRoute('sie_diploma_tramite_tecnico_recepcion');
            }
            /*
            if (count($entityRegistroDiplomaTecnico) > 0){
                if (count($entity) < 2){
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
                                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_Departamento_v1.rptdesign&serie='.str_pad($serie, 7, "0", STR_PAD_LEFT).'&gestion='.$ges.'&&__format=pdf&'));
                            } else {
                                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_Estudiantelegalizacion_Departamento_v1.rptdesign&serie='.$serie.'&gestion='.$ges.'&&__format=pdf&'));
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
            */
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('sie_diploma_tramite_legalizacion');
        }   
    }    

    private function creaFormularioTramiteTecnico($routing, $value1, $value2, $value3, $identificador) {
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
            throw new Exception('Se ha producido un error muy grave.');
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

    /**
     * Despliega formulario de busqueda de los departamentos
     * @return type
     */
    public function impresionTramiteFormularioAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieDiplomaBundle:TramiteTecnico:ImpresionDiplomaTecnico.html.twig', array(
                    'form' => $this->creaFormularioImpresion('sie_diploma_tramite_tecnico_impresion_pdf', '', $gestionActual, '', 7)->createView()
                    , 'titulo' => 'Impresión'
                    , 'subtitulo' => 'Diplomas Técnicos'
        ));
    }

    private function creaFormularioImpresion($routing, $value1, $value2, $value3, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id < 2017')
                                ->andWhere('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function impresionDocumentoPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */
        $form = $request->get('form');
        if ($form) {
            $ue = $form['sie'];
            $gestion = $form['gestion'];
            $identificador = $form['identificador'];
        } else {
            $ue = $request->get('sie');
            $gestion = $request->get('gestion');
            $identificador = $request->get('identificador');
        }
        
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

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $id_usuario );
            $query->bindValue(':sie', $ue);
            $query->bindValue(':roluser', $identificador);
            $query->execute();
            $aTuicion = $query->fetchAll();

        
        /*if (!$aTuicion[0]['get_ue_tuicion']) {
            $this->session->getFlashBag()->set('danger',array('title' => 'Error','message' => 'No tiene tuición sobre la Unidad Educativa'));
            return $this->redirectToRoute('sie_diploma_tramite_tecnico_impresion_formulario');
        }*/  
            
        $arch = $ue.'_'.$gestion.'_DIPLOMA_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        switch ($gestion) {
            case 2009:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2010:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2011:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2012 :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;   
            case 2013 :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;          
            default :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
        }
                 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;   
    }
}