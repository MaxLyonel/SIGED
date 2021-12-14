<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Form\FlujoProcesoType;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Entity\WfUsuarioFlujoProceso;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaHistorialTramite;
use Sie\AppWebBundle\Entity\SolicitudTramite;
use Sie\AppWebBundle\Entity\SucursalTipo;


/**
 * FlujoTipo controller.
 *
 */
class TramiteCeaController extends Controller
{
    public $session;
    public $iddep;
    public $idprov;
    public $idmun;
    public $idcan;
    public $idloc;
    public $iddis;
    public $tramiteTipoArray;
    public $nivelArray;

    /**
     * the class constructor
     */
    public function __construct()
    {
        $this->session = new Session();
    }

    /***
     * Formulario inicio de solicitud Modoficacion
     */
    public function inicioSolicitudModificacionAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $idlugarusuario = $this->session->get('roluserlugarid');
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        $idrue = $this->session->get('ie_id');
        //dump($id,$tipo);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        if ($tipo == 'idtramite') {
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tipotramite = $tramite->getTramiteTipo()->getId();
            $tareasDatos = $this->obtieneDatos($tramite);
            $flujotipo = $tramite->getFlujoTipo()->getId();
            $tarea = $tramiteDetalle->getFlujoProceso();
        } else {
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('institucioneducativa' => $idrue, 'flujoTipo' => $id, 'fechaFin' => null, 'gestionId' => $this->session->get('currentyear')));
            if ($tramite) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', 'El Centro de Educación Alternativa ya cuenta con una SOLICITUD DE MODIFICACIÓN RUE en la gestión actual con el <strong>Nro.: ' . $tramite->getId() . '</strong></br>Para iniciar un nuevo trámite primero debe finalizar el actual.');
                return $this->redirectToRoute('wf_tramite_index');
            } else {
                $tramite = null;
                $tareasDatos = null;
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $id, 'orden' => 1));
                $flujotipo = $flujoproceso->getFlujoTipo()->getId();
                $tarea = $flujoproceso;
            }
        }

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idrue);
        $lugar_tipo2012 = $em->getRepository('SieAppWebBundle:LugarTipo')->find($institucioneducativa->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012());
        $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $idrue));
        $inicioForm = $this->createInicioModificacionForm($flujotipo, $tarea->getId(), $tramite, $idrue, $institucioneducativa);
        //dump($tareasDatos);die;
        //dump($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$idrue,'gestionTipo'=>$this->session->get('currentyear'))));die;

        $epja = array(201, 202, 203, 204, 205);
        $is_ejpa = 0;
        foreach ($institucioneducativaNivel as $n) {
            if (array_search($n->getNivelTipo()->getId(), $epja) !== false) {
                $is_ejpa = 1;
                break;
            }
        }

        return $this->render('SieProcesosBundle:TramiteCea:inicioSolicitudModificacion.html.twig', array(
            'form' => $inicioForm->createView(),
            'institucioneducativa' => $institucioneducativa,
            'lugarTipo2012' => $lugar_tipo2012,
            'sucursal' => $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $idrue, 'gestionTipo' => $this->session->get('currentyear'))),
            'ieNivel' => $institucioneducativaNivel,
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tarea,
            'isEjpa' => $is_ejpa
        ));
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
    }

    public function createInicioModificacionForm($flujotipo, $tarea, $tramite, $idrue, $institucioneducativa)
    {
        $em = $this->getDoctrine()->getManager();

        $this->tramiteTipoArray = array(59, 60, 72); //fRnk: añadir el 59 para que no traiga este trámite

        //dump($this->tramiteTipoArray);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_inicio_modificacion_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 60))
            //->add('area', 'choice', array('label' => 'ÁREA GEOGRÁFICA ESTABLECIDA POR EL MUNICIPIO:','required'=>true,'multiple' => false,'expanded' => true,'choices'=>array('U'=>'Urbano','R'=>'Rural')))
            ->add('tramites', 'entity', array(
                'label' => 'Tipo de Trámite:', 'required' => true, 'multiple' => false, 'expanded' => false, 'attr' => array('class' => 'form-control', 'data-placeholder' => "Seleccionar tipo de trámite"), 'class' => 'SieAppWebBundle:TramiteTipo',
                'query_builder' => function (EntityRepository $tr) {
                    return $tr->createQueryBuilder('tr')
                        ->where('tr.obs = :rue')
                        ->andWhere('tr.id not in (:tipo)')
                        ->setParameter('rue', 'RCEA')
                        ->setParameter('tipo', $this->tramiteTipoArray)
                        ->orderBy('tr.tramiteTipo', 'ASC');
                },
                'property' => 'tramiteTipo', 'empty_value' => 'Seleccione tipo de trámite'
            ))
            ->add('tr', 'hidden')
            ->add('observacion', 'textarea', array('label' => 'JUSTIFICACIÓN:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function buscarTareaAction(Request $request)
    {
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        $ie = $request->get('ie');

        $em = $this->getDoctrine()->getManager();
        $tramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id);
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        $this->iddep2001 = $ie->getLeJuridiccionGeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
        $lugar2012 = $ie->getLeJuridiccionGeografica()->getLugarTipoIdLocalidad2012() ? $em->getRepository('SieAppWebBundle:LugarTipo')->find($ie->getLeJuridiccionGeografica()->getLugarTipoIdLocalidad2012()) : '';
        //dump($tramiteTipo);die;
        $form = $this->createFormBuilder();
        switch ($id) {
            case 62: //ampliacion de nivel
                $ienivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                    ->select('nt.id,nt.nivel')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                    ->where('ien.institucioneducativa =' . $ie->getId())
                    ->getQuery()
                    ->getResult();
                $epja = array(201, 202, 203, 204, 205);
                $is_ejpa = false;
                foreach ($ienivel as $n) {
                    if (array_search($n['id'], $epja) !== false) {
                        $is_ejpa = true;
                        break;
                    }
                }
                //dump($ienivel);die;
                if ($is_ejpa) {
                    $this->nivelArray = $epja;
                    foreach ($ienivel as $n) {
                        if (in_array($n['id'], $this->nivelArray)) {
                            $this->nivelArray = array_diff($this->nivelArray, array($n['id']));
                        }
                    }
                    $form = $form
                        ->add('nivelampliar', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                            return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(201, 202, 203, 204, 205))->orderBy('nt.id', 'ASC');
                        }, 'property' => 'nivel'))
                        ->getForm();
                } else { //fRnk: para EDUPER falta aun ver los ids
                    $this->nivelArray = array(222, 223, 225, 226, 219, 220, 224);
                    foreach ($ienivel as $n) {
                        if (in_array($n['id'], $this->nivelArray)) {
                            $this->nivelArray = array_diff($this->nivelArray, array($n['id']));
                        }
                    }
                    $form = $form
                        ->add('nivelampliar', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                            return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(222, 223, 225, 226, 219, 220, 224))->orderBy('nt.id', 'ASC');
                        }, 'property' => 'nivel'))
                        ->getForm();
                }
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel' => $ienivel,
                    'tipo' => $tipo
                );
                break;
            case 71: //Reduccion de Nivel
                $ienivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                    ->select('nt.id,nt.nivel')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                    ->where('ien.institucioneducativa =' . $ie->getId())
                    ->getQuery()
                    ->getResult();
                $this->idcan = $ie->getId();
                $form = $form
                    ->add('nivelreducir', 'entity', array('label' => 'Reduccion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                        return $nt->createQueryBuilder('nt')
                            ->select('nt')
                            ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                            ->where('ien.institucioneducativa =' . $this->idcan)
                            ->orderBy('nt.id', 'ASC');
                    }, 'property' => 'nivel'))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel' => $ienivel,
                    'tipo' => $tipo
                );
                break;
            case 61: //Ampliación o cambio de especialidades técnicas
                $ienivel = array();
                $data = array(
                    'form' => null,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel' => $ienivel,
                    'tipo' => $tipo
                );
                break;
            case 67: //Cierre de Especialidades Técnicas
                $ienivel = array();
                $data = array(
                    'form' => null,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel' => $ienivel,
                    'tipo' => $tipo
                );
                break;
            case 64: //cambio de dependencia
                if ($ie->getDependenciaTipo()->getId() == 1) {
                    $form = $form
                        ->add('dependencia', 'entity', array('label' => 'Dependencia', 'required' => true, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:DependenciaTipo', 'query_builder' => function (EntityRepository $dt) {
                            return $dt->createQueryBuilder('dt')->where('dt.id = 2');
                        }, 'property' => 'dependencia'))
                        ->add('conveniotipo', 'entity', array('label' => 'Tipo de Convenio:', 'required' => true, 'multiple' => false, 'attr' => array('class' => 'form-control'), 'empty_value' => 'Seleccione convenio', 'class' => 'SieAppWebBundle:ConvenioTipo', 'query_builder' => function (EntityRepository $ct) {
                            return $ct->createQueryBuilder('ct')->where('ct.codDependenciaId =2')->orderBy('ct.convenio', 'ASC');
                        }, 'property' => 'convenio'))
                        ->getForm();
                } else {
                    $form = $form
                        ->add('dependencia', 'entity', array('label' => 'Dependencia', 'required' => true, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:DependenciaTipo', 'query_builder' => function (EntityRepository $dt) {
                            return $dt->createQueryBuilder('dt')->where('dt.id = 1');
                        }, 'property' => 'dependencia'))
                        ->getForm();
                }
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 70: //cambio de nombre
                $form = $form
                    ->add('nuevo_nombre', 'text', array('label' => 'Nuevo nombre del Centro de Educación Alternativa:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'oninput' => 'validarnombre(this.value,1)', 'onblur' => 'validarnombredistrito(this.value)')))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'nombre_actual' => $ie->getInstitucioneducativa()
                );
                break;
            case 66: //cambio de jurisdiccion administrativa
                $this->iddep = $ie->getLeJuridicciongeografica()->getDistritoTipo()->getDepartamentoTipo();
                $this->iddis = $ie->getLeJuridicciongeografica()->getDistritoTipo()->getId();
                $this->disArray = array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, $ie->getLeJuridicciongeografica()->getDistritoTipo()->getId());
                $form = $form
                    ->add('nuevo_distrito', 'entity', array('label' => 'Nuevo Distrito:', 'required' => true, 'multiple' => false, 'attr' => array('class' => 'form-control'), 'empty_value' => 'Seleccione nuevo distrito', 'class' => 'SieAppWebBundle:DistritoTipo', 'query_builder' => function (EntityRepository $dt) {
                        return $dt->createQueryBuilder('dt')->where('dt.departamentoTipo = :id')->andwhere('dt.id not in (:iddis)')->setParameter('id', $this->iddep)->setParameter('iddis', $this->disArray)->orderBy('dt.distrito', 'ASC');
                    }, 'property' => 'distrito'))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'distrito' => $ie->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito()
                );
                break;
            case 39: //Fusion
                //ampliacion y cierre definitivo
                break;
            case 40: //desglose
                break;
            case 65: //cambio de infraestructura
                $form = $form
                    ->add('lejurisdiccion', 'text', array('label' => 'Código Edificio Educativo:', 'required' => false, 'attr' => array('class' => 'form-control validar', 'maxlength' => 8)));
                if ($lugar2012) {
                    $this->iddep2012 = $lugar2012->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
                    $form = $form
                        ->add('departamento2012', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                            return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddep2012);
                        }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'));
                } else {
                    $form = $form
                        ->add('departamento2012', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                            return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->andWhere('lt.id<>79355')->orderBy('lt.id', 'ASC');
                        }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'));
                }
                $form = $form
                    ->add('provincia2012', 'choice', array('label' => 'Provincia:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('municipio2012', 'choice', array('label' => 'Municipio:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('comunidad2012', 'choice', array('label' => 'Comunidad:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('departamento2001', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                        return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddep2001);
                    }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'))
                    ->add('provincia2001', 'choice', array('label' => 'Provincia:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('municipio2001', 'choice', array('label' => 'Municipio:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('canton2001', 'choice', array('label' => 'Cantón:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('localidad2001', 'choice', array('label' => 'Localidad/Comunidad:', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('zona', 'text', array('label' => 'Zona:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                    ->add('direccion', 'text', array('label' => 'Dirección:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'lugarTipo2001' => $ie->getLeJuridicciongeografica()->getLugarTipoLocalidad(),
                    'lugarTipo2012' => $em->getRepository('SieAppWebBundle:LugarTipo')->find($ie->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()),
                    'zona' => $ie->getLeJuridicciongeografica()->getZona(),
                    'direccion' => $ie->getLeJuridicciongeografica()->getDireccion()
                );
                break;
            case 69: //cierre temporal
            case 68: //cierre definitivo
                if ($ie->getEstadoinstitucionTipo()->getId() == 10) {
                    $form = $form
                        ->add('estadoinstitucion', 'checkbox', array('label' => 'CERRADA', 'required' => true));
                    if ($tipo == 'fusion') {
                        $form = $form
                            ->add('siefusion', 'text', array('label' => 'Código SIE del Centro de Educación Alternativa a Cerrar definitivamente:', 'required' => true, 'attr' => array('class' => 'form-control validar', 'maxlength' => 8)));
                    }
                    $form = $form
                        ->getForm()
                        ->createView();
                } else {
                    $form = null;
                }
                $data = array(
                    'form' => $form,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'estadoinstitucion' => $ie->getEstadoinstitucionTipo(),
                    'tipo' => $tipo
                );
                break;
            case 72: //Reapertura
                if ($ie->getEstadoinstitucionTipo()->getId() == 19) {
                    $form = $form
                        ->add('estadoinstitucion', 'checkbox', array('label' => 'ABIERTA', 'required' => true))
                        ->getForm()
                        ->createView();
                } else {
                    $form = null;
                }
                $data = array(
                    'form' => $form,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'estadoinstitucion' => $ie->getEstadoinstitucionTipo()
                );
                break;
            case 45: //nuevo certifcado rue
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                );
                break;
            case 46: //Regularización RUE
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                );
                break;
            case 63: //Apertura de Subcentro
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'tipo' => $tipo
                );
                break;
            case 55: //actualizacion de resolucion administrativa
                $resolucion = array('fecha' => $ie->getFechaResolucion()->format('d/m/Y'), 'numero' => $ie->getNroResolucion());
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'tipo' => $tipo,
                    'resolucion' => $resolucion
                );
                break;
        }

        return $this->render('SieProcesosBundle:TramiteCea:modificaTramite.html.twig', $data);
    }

    public function buscarRequisitosAction(Request $request)
    {
        $id = $request->get('id');
        //dump($id);die;
        $em = $this->getDoctrine()->getManager();
        if ($id != 59 and $id != 46 and $id != 55) {
            $ie = $request->get('ie');
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        } else {
            $dependencia = $request->get('dependencia');
            $constitucion = $request->get('constitucion');
            // dump($constitucion,$dependencia);die;
        }
        $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id);
        $requisitos = array();
        $form = $this->createFormBuilder();
        switch ($id) {
            case 62: //ampliacion de nivel
                $form = $form
                    ->add('i_solicitud_ampliar', 'file', array('label' => 'Adjuntar Solicitud de Ampliación de niveles o tramos de formación (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_ampliar', 'checkbox', array('label' => 'Original de Compromiso del Gobierno Autónomo Municipal que autorice la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos.', 'required' => false))
                    ->add('i_certificado_ampliar', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_ampliar', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_testimonio_ampliar', 'checkbox', array('label' => 'Copia legalizada de Testimonio de Constitución de la Institución, solo en caso de convenio.', 'required' => false))
                    ->add('i_convenio_ampliar', 'checkbox', array('label' => 'Convenio Interinstitucional vigente entre el Ministerio de Educación e Institución y/u Organización, que estipule garantizar la infraestructura, mobiliario, equipamiento y subvención, solo en caso de convenio.', 'required' => false))
                    ->add('i_folio_ampliar', 'checkbox', array('label' => 'Copia legalizada de Folio Real actualizado emitido por Derechos Reales o Testimonio de Propiedad de la Matricula Computarizada.', 'required' => false))
                    ->add('ii_planos_ampliar', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                    ->add('ii_edificio_ampliar', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                    ->add('ii_geografica_ampliar', 'checkbox', array('label' => 'Original de Formulario de Actualización Geográfica.', 'required' => false))
                    ->add('ii_equipamiento_ampliar', 'checkbox', array('label' => 'Detalle de equipamiento.', 'required' => false))
                    ->add('iii_curri_proy_ampliar', 'checkbox', array('label' => 'Proyecto Educativo que justifique la ampliación detallando las mallas curriculares, contenidos de los niveles o tramos de formación y listas de participantes que solicitan la formación.', 'required' => false))
                    ->add('iii_curri_informe_ampliar', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación.', 'required' => false))
                    ->getForm();
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                    //'ieNivel'=>$ienivel
                );
                break;
            case 63: //apertura subcentro
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $form = $form
                    ->add('i_informe_director_cea_sc', 'checkbox', array('label' => 'Informe técnico de apertura: Emitido por la o el Director del Centro de Educación Alternativa, a la Dirección Distrital Educativa correspondiente del área geográfica de apertura del sub centro, detallando las áreas y niveles de atención.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0,
                );
                break;
            case 71: //Reduccion de Nivel
                $form = $form
                    ->add('i_solicitud_reducir', 'file', array('label' => 'Adjuntar Solicitud de Reducción de niveles o tramos de formación (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_reducir', 'checkbox', array('label' => 'Original de Compromiso del Gobierno Autónomo Municipal que autorice la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos.', 'required' => false))
                    ->add('i_certificado_reducir', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_reducir', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_testimonio_reducir', 'checkbox', array('label' => 'Copia legalizada de Testimonio de Constitución de la Institución, solo en caso de convenio.', 'required' => false))
                    ->add('i_convenio_reducir', 'checkbox', array('label' => 'Convenio Interinstitucional vigente entre el Ministerio de Educación e Institución y/u Organización, que estipule garantizar la infraestructura, mobiliario, equipamiento y subvención, solo en caso de convenio.', 'required' => false))
                    ->add('i_folio_reducir', 'checkbox', array('label' => 'Copia legalizada de Folio Real actualizado emitido por Derechos Reales o Testimonio de Propiedad de la Matricula Computarizada.', 'required' => false))
                    ->add('ii_planos_reducir', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                    ->add('ii_edificio_reducir', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                    ->add('ii_geografica_reducir', 'checkbox', array('label' => 'Original de Formulario de Actualización Geográfica.', 'required' => false))
                    ->add('ii_equipamiento_reducir', 'checkbox', array('label' => 'Detalle de equipamiento.', 'required' => false))
                    ->add('iii_curri_proy_reducir', 'checkbox', array('label' => 'Proyecto Educativo que justifique la reducción detallando las mallas curriculares, contenidos de los niveles o tramos de formación y listas de participantes que solicitan la formación.', 'required' => false))
                    ->add('iii_curri_informe_reducir', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación.', 'required' => false))
                    ->getForm();
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 61: //Ampliación o cambio de especialidades técnicas
                $form = $form
                    ->add('i_solicitud_acesp', 'file', array('label' => 'Adjuntar Solicitud de Ampliación o cambio de especialidades técnicas (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_acesp', 'checkbox', array('label' => 'Original de Compromiso del Gobierno Autónomo Municipal que autorice la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos.', 'required' => false))
                    ->add('i_certificado_acesp', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_acesp', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('ii_edificio_acesp', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                    ->add('ii_equipamiento_acesp', 'checkbox', array('label' => 'Detalle de equipamiento de las nuevas especialidades técnicas.', 'required' => false))
                    ->add('iii_curri_proy_acesp', 'checkbox', array('label' => 'Proyecto Educativo que justifique la ampliación o cambio de las especialidades técnicas, detallando las mallas curriculares, contenidos de los niveles y listas de participantes que solicitan la formación, considerando las necesidades, vocaciones y potencialidades del contexto educativo.', 'required' => false))
                    ->add('iii_curri_informe_acesp', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación.', 'required' => false))
                    ->getForm();
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 67: //Cierre de Especialidades Técnicas
                $form = $form
                    ->add('i_informe_director_cea_cesp', 'checkbox', array('label' => 'Informe técnico de la Dirección del Centro de Educación Alternativa o Dirección Distrital Educativa justificando el cierre de la especialidad técnica a la Dirección Departamental de Educación.', 'required' => false))
                    ->getForm();
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 64: //cambio de dependencia
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                if ($ie->getDependenciaTipo()->getId() == 1) { //de fiscal a convenio
                    $form = $form
                        ->add('i_solicitud_dependencia', 'file', array('label' => 'Adjuntar Solicitud de Cambio de Dependencia de Fiscal a Convenio (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                        ->add('i_certificadorue_dependencia', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                        ->add('i_acreditacion_dependencia', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                        ->add('i_testimonio_dependencia', 'checkbox', array('label' => 'Copia legalizada de Testimonio de Constitución de la Institución, solo en caso de convenio.', 'required' => false))
                        ->add('i_convenio_dependencia', 'checkbox', array('label' => 'Convenio Interinstitucional vigente entre el Ministerio de Educación e Institución y/u Organización, que estipule garantizar la infraestructura, mobiliario, equipamiento y subvención, solo en caso de convenio.', 'required' => false))
                        ->add('i_folio_dependencia', 'checkbox', array('label' => 'Copia legalizada de Folio Real actualizado emitido por Derechos Reales o Testimonio de Propiedad de la Matricula Computarizada.', 'required' => false))
                        ->add('ii_planos_dependencia', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                        ->add('ii_edificio_dependencia', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                        ->add('ii_geografica_dependencia', 'checkbox', array('label' => 'Original de Formulario de Actualización Geográfica.', 'required' => false))
                        ->add('iii_informe_director_cea_dependencia', 'checkbox', array('label' => 'Original de Informe Técnico del Director/a del Centro de Educación Alternativa.', 'required' => false))
                        ->add('iii_informe_distrital_dependencia', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación.', 'required' => false))
                        ->getForm();
                } else {
                    $form = $form
                        ->add('i_solicitud_dependencia', 'file', array('label' => 'Adjuntar Solicitud de Cambio de Dependencia de Convenio a Fiscal (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                        ->add('i_certificadorue_dependencia', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                        ->add('i_acreditacion_dependencia', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                        ->add('i_testimonio_dependencia', 'checkbox', array('label' => 'Copia legalizada de Testimonio de Constitución de la Institución, solo en caso de convenio.', 'required' => false))
                        ->add('i_convenio_dependencia', 'checkbox', array('label' => 'Convenio Interinstitucional vigente entre el Ministerio de Educación e Institución y/u Organización, que estipule garantizar la infraestructura, mobiliario, equipamiento y subvención, solo en caso de convenio.', 'required' => false))
                        ->add('i_doc_protocolizado_dependencia', 'checkbox', array('label' => 'Original de Documento protocolizado de sesión o donación de infraestructura, si corresponde.', 'required' => false))
                        ->add('ii_planos_dependencia', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                        ->add('iii_informe_director_cea_dependencia', 'checkbox', array('label' => 'Original de Informe Técnico del Director/a del Centro de Educación Alternativa.', 'required' => false))
                        ->add('iii_informe_distrital_dependencia', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación.', 'required' => false))
                        ->getForm();
                }
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                //dump($data);die;
                break;
            case 70: //cambio de nombre
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $form = $form
                    ->add('i_solicitud_cn', 'file', array('label' => 'Adjuntar Solicitud de Modificación de denominación (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_certdefuncion_cn', 'file', array('label' => 'Adjuntar Certificado de Defunción (en caso de llevar nombre de una persona fallecida meritoria), o reseña histórica (aprobada y/o emitida por la Comunidad) (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar certificado", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_certificadorue_cn', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_cn', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicio Educativo. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 66: //cambio de jurisdiccion administrativa
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $form = $form
                    ->add('i_solicitud_jur', 'file', array('label' => 'Adjuntar Solicitud de Cambio de jurisdicción administrativa (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_jur', 'checkbox', array('label' => 'Original de Documento del Gobierno Autónomo Municipal que autorice y comprometa la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos.', 'required' => false))
                    ->add('i_certificadorue_jur', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_jur', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicios Educativos. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_conformidad_jur', 'checkbox', array('label' => 'Acta de conformidad firmada por las/los Directores Distritales Educativos.', 'required' => false))
                    ->add('ii_planos_jur', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                    ->add('ii_edificio_jur', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                    ->add('ii_geografica_jur', 'checkbox', array('label' => 'Original de Formulario de Actualización Geográfica.', 'required' => false))
                    ->add('iii_informe_distrital_jur', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación, que determine la viabilidad del cambio de jurisdicción.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 39: //Fusion
                break;
            case 40: //desglose
                break;
            case 65: //cambio de infraestructura
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $form = $form
                    ->add('i_solicitud_infra', 'file', array('label' => 'Adjuntar Solicitud de Cambio de infraestructura (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_infra', 'checkbox', array('label' => 'Original de Documento del Gobierno Autónomo Municipal que autorice y comprometa la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos.', 'required' => false))
                    ->add('i_certificadorue_infra', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Registro de Unidades Educativas – RUE. En caso de extravío del RUE, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('i_acreditacion_infra', 'checkbox', array('label' => 'Original o copia legalizada de Certificado de Acreditación de Servicios Educativos. En caso de extravío, presentar informe circunstanciado con Visto Bueno de la Dirección Departamental de Educación.', 'required' => false))
                    ->add('ii_planos_infra', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                    ->add('ii_edificio_infra', 'checkbox', array('label' => 'Original de Formulario de Edificio Escolar.', 'required' => false))
                    ->add('ii_geografica_infra', 'checkbox', array('label' => 'Original de Formulario de Actualización Geográfica.', 'required' => false))
                    ->add('iii_informe_distrital_infra', 'checkbox', array('label' => 'Original de Informe Técnico del Director Distrital de Educación, que determine la viabilidad del cambio de infraestructura.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 69: //cierre temporal
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $form = $form
                    ->add('i_informe_director_cea_cierre_tmp', 'checkbox', array('label' => 'Informe técnico, emitido por la o el Director del CEA que justifique el cierre temporal del Centro de Educación Alternativa.', 'required' => false))
                    ->add('i_certificadorue_cierre_tmp', 'checkbox', array('label' => 'Certificados RUE original y de Servicios Educativos, en caso de extravío los informes correspondientes.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0,
                );
                break;
            case 68: //cierre definitivo
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $form = $form
                    ->add('i_certificadorue_cierre', 'checkbox', array('label' => 'Certificados RUE original y de Servicios Educativos, en caso de extravío los informes correspondientes.', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0,
                );
                break;
            case 72: //Reapertura
                $requisitos = array('legal' => true, 'infra' => false, 'curri' => false);
                $form = $form
                    ->add('i_solicitud_reapertura', 'file', array('label' => 'Adjuntar Solicitud de Reapertura (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 45: //nuevo certifcado rue
                $requisitos = array('legal' => true, 'infra' => false, 'admi' => false);
                $form = $form
                    ->add('i_solicitud_nuevorue', 'file', array('label' => 'Adjuntar Informe Técnico circunstanciado de extravío del Original del Certificado RUE (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo(),
                    'constitucion' => 0
                );
                break;
            case 55: //Actualizacion de resolucion administrativa
            case 46: //Regularización
            case 59: //apertura
                $requisitos = array('legal' => true, 'infra' => true, 'curri' => true);
                $labelSolicitud = '';
                if ($id == 46) {
                    $labelSolicitud = 'regularización';
                }
                if ($id == 59) {
                    $labelSolicitud = 'apertura';
                    $form = $form
                        ->add('iii_curri_antecedentes', 'checkbox', array('label' => 'Antecedentes.', 'required' => false))
                        ->add('iii_curri_objgeneral', 'checkbox', array('label' => 'Objetivo General.', 'required' => false))
                        ->add('iii_curri_objespecificos', 'checkbox', array('label' => 'Objetivos Específicos.', 'required' => false))
                        ->add('iii_curri_diagnostico', 'checkbox', array('label' => 'Diagnóstico de las necesidades, potencialidades de la comunidad y sostenibilidad del Centro.', 'required' => false))
                        ->add('iii_curri_jussolicitud', 'checkbox', array('label' => 'Justificación de la Solicitud.', 'required' => false))
                        ->add('iii_curri_jusinfraestructura', 'checkbox', array('label' => 'Justificación de la infraestructura, mobiliario, equipamiento e ítems.', 'required' => false))
                        ->add('iii_curri_planes', 'checkbox', array('label' => 'Planes, programas y mallas curriculares.', 'required' => false))
                        ->add('iii_curri_proyeccion', 'checkbox', array('label' => 'Proyección de la solicitud en base a las Políticas Educativas en vigencia.', 'required' => false))
                        ->add('iii_curri_conclusiones', 'checkbox', array('label' => 'Conclusiones.', 'required' => false))
                        ->add('iii_curri_proy', 'hidden', array('label' => 'Proyecto Educativo', 'required' => false))
                        ->add('iii_curri_informe_distrital', 'checkbox', array('label' => 'Informe Técnico del Director Distrital de Educación. (Original).', 'required' => false));
                }
                if ($id == 55) {
                    $labelSolicitud = 'actualización';
                }
                $form = $form
                    ->add('i_solicitud_apertura', 'file', array('label' => 'Adjuntar Solicitud de ' . $labelSolicitud . ' dirigida a la Dirección Distrital Educativa correspondiente (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar solicitud", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_compromiso_apertura', 'checkbox', array('label' => 'Compromiso del Gobierno Autónomo Municipal que autorice la dotación y mantenimiento de la infraestructura, mobiliario, equipamiento y atención de servicios básicos (original).', 'required' => false))
                    ->add('i_actafundacion_apertura', 'file', array('label' => 'Adjuntar Acta de Fundación del Centro (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar acta", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_certdefuncion', 'file', array('label' => 'Certificado de defunción (en caso de llevar nombre de una persona fallecida meritoria), o reseña histórica (aprobada y/o emitida por la Comunidad) (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar certificado", 'accept' => "application/pdf,.img,.jpg")))
                    ->add('i_folio_apertura', 'checkbox', array('label' => 'Copia legalizada del Folio Real actualizado emitido por Derechos Reales o Testimonio de Propiedad de la Matricula Computarizada, en caso de capitales de departamento, CEA’s del área, requieren la certificación de los predios especificando el área educativa total.', 'required' => false));
                if ($dependencia == 2) {
                    $form = $form
                        ->add('i_constitucion_apertura', 'checkbox', array('label' => 'Copia legalizada de Testimonio de Constitución de la Institución.', 'required' => false))
                        ->add('i_convenio_apertura', 'checkbox', array('label' => 'Convenio Interinstitucional vigente entre el Ministerio de Educación e Institución y/u Organización, que estipule garantizar la infraestructura, mobiliario, equipamiento y subvención.', 'required' => false));
                }
                $form = $form
                    ->add('ii_planos_apertura', 'checkbox', array('label' => 'Planos aprobados por el Gobierno Autónomo Municipal correspondiente.', 'required' => false))
                    ->add('ii_edificio_escolar', 'checkbox', array('label' => 'Formulario de Edificio Escolar (original).', 'required' => false))
                    ->add('ii_act_geografica', 'checkbox', array('label' => 'Formulario de Actualización Geográfica (original).', 'required' => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($dependencia),
                    'constitucion' => $constitucion
                );
                break;
        }
        return $this->render('SieProcesosBundle:TramiteCea:requisitoTramite.html.twig', $data);
    }

    /**
     * formulario apertura
     */
    public function aperturaAction(Request $request)
    {
        $id = $request->get('id');
        $desglose = $request->get('desglose'); //Todo: edit para que sea subcentro
        $ie = $request->get('ie');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createInicioNuevoForm($desglose, $ie);
        $data = array(
            'form' => $form->createView(),
            'id' => $id,
            'desglose' => $desglose,
            'tramitetipo' => $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id) //nos quedamos aqui
        );
        if ($desglose == 1) {
            return $this->render('SieProcesosBundle:TramiteCea:aperturaDesglose.html.twig', $data);
        } else {
            return $this->render('SieProcesosBundle:TramiteCea:apertura.html.twig', $data);
        }
    }

    /**
     * formulario apertura subcentro
     */
    public function aperturaSubcentroAction(Request $request)
    {
        $id = $request->get('id');
        $desglose = $request->get('desglose');
        $ie = $request->get('ie');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createInicioNuevoSubcentroForm($desglose, $ie);
        $data = array(
            'form' => $form->createView(),
            'id' => $id,
            'desglose' => $desglose,
            'tramitetipo' => $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id)
        );
        return $this->render('SieProcesosBundle:TramiteCea:aperturaSubcentro.html.twig', $data);
    }

    /**
     * formulario apertura
     */
    public function actualizacionAction(Request $request)
    {
        $id = $request->get('id');
        $desglose = 3;
        $ie = $request->get('ie');
        //dump($ie);die;
        $em = $this->getDoctrine()->getManager();
        $form = $this->createInicioNuevoForm($desglose, $ie);
        $data = array(
            'form' => $form->createView(),
            'id' => $id,
            'desglose' => $desglose,
            'tramitetipo' => $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id)
        );
        return $this->render('SieProcesosBundle:TramiteCea:actualizacion.html.twig', $data);
    }

    public function createInicioNuevoForm($desglose, $ie)
    {
        $em = $this->getDoctrine()->getManager();
        /* $constitucion = array(1=>'Asociaciones o Fundaciones sin fines de lucro - ONG',
          45  2=>'Instituciones Religiosas',
          46  3=>'Sociedad Anónima (S.A.) / Sociedad de Responsabilidad Limitada(S.r.l.)',
          47  4=>'Unipersonal',
          48  5=>'Cooperativa',
          49  6=>'De Convenio (suscrito entre estados)'); */

        $form = $this->createFormBuilder();
        $ieName = "";

        if ($desglose == 0) {
            $form = $form
                ->setAction($this->generateUrl('tramite_cea_apertura_guardar'))
                ->add('departamento2012', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                    return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->andWhere('lt.id<>79355')->orderBy('lt.id', 'ASC');
                }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'))
                ->add('departamento2001', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                    return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 1')->andWhere('lt.paisTipoId=1')->orderBy('lt.id', 'ASC');
                }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'))
                ->add('distrito', 'choice', array('label' => 'Distrito Educativo', 'required' => false, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Enviar formulario'));
        } else {

            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
            $ieName = $ie;
            $this->iddep2001 = $ie->getLeJuridiccionGeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $this->iddis = $ie->getLeJuridiccionGeografica()->getDistritoTipo()->getId();
            $lugar2012 = $ie->getLeJuridiccionGeografica()->getLugarTipoIdLocalidad2012() ? $em->getRepository('SieAppWebBundle:LugarTipo')->find($ie->getLeJuridiccionGeografica()->getLugarTipoIdLocalidad2012()) : '';

            $form = $form
                ->add('departamento2001', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                    return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddep2001);
                }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'))
                ->add('distrito', 'entity', array('label' => 'Distrito:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:DistritoTipo', 'query_builder' => function (EntityRepository $lt) {
                    return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddis);
                }, 'property' => 'distrito', 'empty_value' => false));
            if ($lugar2012) {
                $this->iddep2012 = $lugar2012->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
                $form = $form
                    ->add('departamento2012', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                        return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddep2012);
                    }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'));
            } else {
                $form = $form
                    ->add('departamento2012', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                        return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->andWhere('lt.id<>79355')->orderBy('lt.id', 'ASC');
                    }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'));
            }
        }

        $form = $form
            ->add('institucioneducativa', 'text', array('label' => 'Nombre del CEA:', 'required' => false, 'attr' => array('value' => $ieName, 'class' => 'form-control datocea', 'oninput' => 'validarnombre(this.value,0)', 'style' => 'text-transform:uppercase')))
            ->add('fechafundacion', 'text', array('label' => 'Fecha de Fundación:', 'required' => false, 'attr' => array('class' => 'form-control date datocea')))
            ->add('telefono', 'text', array('label' => 'Teléfono de Referencia:', 'required' => false, 'attr' => array('class' => 'form-control validar datocea')))
            ->add('director', 'text', array('label' => 'Datos del Director/Encargado:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
            ->add('lejurisdiccion', 'text', array('label' => 'Código Edificio Educativo:', 'required' => false, 'attr' => array('class' => 'form-control validar datocea', 'maxlength' => 8)))
            ->add('provincia2012', 'choice', array('label' => 'Provincia:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('municipio2012', 'choice', array('label' => 'Municipio:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('comunidad2012', 'choice', array('label' => 'Comunidad:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('provincia2001', 'choice', array('label' => 'Provincia:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('municipio2001', 'choice', array('label' => 'Municipio:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('canton2001', 'choice', array('label' => 'Cantón:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('localidad2001', 'choice', array('label' => 'Localidad:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('zona', 'text', array('label' => 'Zona:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
            ->add('direccion', 'text', array('label' => 'Dirección:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')));
        //->add('area', 'choice', array('label' => 'ÁREA GEOGRÁFICA ESTABLECIDA POR EL MUNICIPIO:','required'=>false,'multiple' => false,'expanded' => true,'choices'=>array('U'=>'Urbano','R'=>'Rural')));
        if ($desglose == 0) {
            $form = $form
                ->add('dependencia', 'entity', array('label' => 'Dependencia:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'empty_value' => 'Seleccione dependencia', 'class' => 'SieAppWebBundle:DependenciaTipo', 'query_builder' => function (EntityRepository $dt) {
                    return $dt->createQueryBuilder('dt')->where('dt.id in (1,2)')->orderBy('dt.id');
                }, 'property' => 'dependencia'));
        } else {
            $form = $form
                ->add('dependencia', 'entity', array('label' => 'Dependencia:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'empty_value' => 'Seleccione dependencia', 'class' => 'SieAppWebBundle:DependenciaTipo', 'query_builder' => function (EntityRepository $dt) {
                    return $dt->createQueryBuilder('dt')->where('dt.id in (1,2)')->orderBy('dt.id');
                }, 'property' => 'dependencia'));
        }
        $form = $form
            ->add('conveniotipo', 'entity', array('label' => 'Tipo de Convenio:', 'required' => false, 'multiple' => false, 'attr' => array('class' => 'form-control datocea'), 'empty_value' => 'Seleccione convenio', 'class' => 'SieAppWebBundle:ConvenioTipo', 'query_builder' => function (EntityRepository $ct) {
                return $ct->createQueryBuilder('ct')->where('ct.id <>99')->andWhere("ct.id <> 50")->orderBy('ct.convenio', 'ASC');
            }, 'property' => 'convenio'))
            ->add('constitucion', 'entity', array('label' => 'Constitución de la Unidad Educativa:', 'required' => false, 'multiple' => false, 'attr' => array('class' => 'form-control datocea'), 'empty_value' => 'Seleccione constitución', 'class' => 'SieAppWebBundle:ConvenioTipo', 'query_builder' => function (EntityRepository $c) {
                return $c->createQueryBuilder('c')->where('c.codDependenciaId =3')->andWhere("c.tipoConvenio ='Constitucion'")->orderBy('c.convenio', 'ASC');
            }, 'property' => 'convenio'))
            ->add('niveltipoh', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(201, 202))->orderBy('nt.id', 'ASC');
            }, 'property' => 'nivel'))
            ->add('niveltipot', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(203, 204, 205))->orderBy('nt.id', 'ASC');
            }, 'property' => 'nivel'))
            ->add('niveltipop', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(222, 223, 225, 226));
            }, 'property' => 'nivel'))
            ->add('niveltipopt', 'entity', array('label' => 'Ampliacion', 'required' => false, 'multiple' => true, 'expanded' => true, 'class' => 'SieAppWebBundle:NivelTipo', 'query_builder' => function (EntityRepository $nt) {
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id', array(219, 220, 224));
            }, 'property' => 'nivel'))
            ->add('siecomparte', 'text', array('label' => 'Comparte el edificio con (Señale solo 1):', 'required' => false, 'attr' => array('class' => 'form-control datocea validar', 'maxlength' => 8)))
            ->add('cantidad_11_1_1', 'text', array('label' => 'epa1', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel11_1')))
            ->add('cantidad_11_1_2', 'text', array('label' => 'epa2', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel11_1')))
            ->add('cantidad_11_2_1', 'text', array('label' => 'esa', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel11_2')))
            ->add('cantidad_11_2_2', 'text', array('label' => 'esa', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel11_2')))
            ->add('cantidad_11_2_3', 'text', array('label' => 'esa', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel11_2')))
            ->add('cantidad_12_1', 'text', array('label' => 'tb', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel12')))
            ->add('cantidad_12_2', 'text', array('label' => 'ta', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel12')))
            ->add('cantidad_12_3', 'text', array('label' => 'tm', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel12')))
            ->add('cantidad_13_1', 'text', array('label' => 'fos', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_2', 'text', array('label' => 'epro', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_3', 'text', array('label' => 'edac', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_4', 'text', array('label' => 'edap', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_1_1', 'text', array('label' => 'fos_tb', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_1_2', 'text', array('label' => 'fos_ta', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_1_3', 'text', array('label' => 'fos_tm', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_2_1', 'text', array('label' => 'epro_tb', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_2_2', 'text', array('label' => 'epro_ta', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_2_3', 'text', array('label' => 'epro_tm', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_3_1', 'text', array('label' => 'edac_tb', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_3_2', 'text', array('label' => 'edac_ta', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_3_3', 'text', array('label' => 'edac_tm', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_4_1', 'text', array('label' => 'edap_tb', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_4_2', 'text', array('label' => 'edap_ta', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('cantidad_13_4_3', 'text', array('label' => 'edap_tm', 'required' => false, 'disabled' => 'disabled', 'attr' => array('class' => 'form-control datocea validar nivel13')))
            ->add('i_area_apertura', 'choice', array('label' => 'Área geográfica establecida por el municipio:', 'required' => false, 'empty_value' => false, 'multiple' => false, 'expanded' => true, 'choices' => array('U' => 'URBANA', 'R' => 'RURAL')))
            ->add('cantidad_adm', 'text', array('label' => '7.1 Adminidtrativo', 'required' => false, 'attr' => array('class' => 'form-control datocea validar')))
            ->add('cantidad_maestro', 'text', array('label' => '7.2 Docente (Maestro)', 'required' => false, 'attr' => array('class' => 'form-control datocea validar')))
            ->add('latitud', 'text', array('label' => 'Latitud:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'pointer-events:none')))
            ->add('longitud', 'text', array('label' => 'Longitud:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'pointer-events:none')))
            ->getForm();
        return $form;
    }

    public function createInicioNuevoSubcentroForm($desglose, $ie)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder();
        $ieName = "";
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        $ieName = $ie;
        $this->iddep2001 = $ie->getLeJuridiccionGeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
        $this->iddis = $ie->getLeJuridiccionGeografica()->getDistritoTipo()->getId();

        $form = $form
            ->add('departamento', 'entity', array('label' => 'Departamento:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:LugarTipo', 'query_builder' => function (EntityRepository $lt) {
                return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddep2001);
            }, 'property' => 'lugar', 'empty_value' => 'Seleccione departamento'))
            ->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('comunidad', 'choice', array('label' => 'Comunidad:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('canton', 'choice', array('label' => 'Cantón:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('localidad', 'choice', array('label' => 'Localidad:', 'required' => false, 'attr' => array('class' => 'form-control datocea')))
            ->add('distrito', 'entity', array('label' => 'Distrito:', 'required' => false, 'attr' => array('class' => 'form-control datocea'), 'class' => 'SieAppWebBundle:DistritoTipo', 'query_builder' => function (EntityRepository $lt) {
                return $lt->createQueryBuilder('lt')->where('lt.id = :id')->setParameter('id', $this->iddis);
            }, 'property' => 'distrito', 'empty_value' => false));

        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $form = $form
            ->add('idinstitucion', 'text', array('label' => 'Código SIE del CEA:', 'required' => false, 'attr' => array('value' => $ie->getId(), 'readonly' => true, 'class' => 'form-control datocea')))
            ->add('institucioneducativa', 'text', array('label' => 'Nombre del CEA:', 'required' => false, 'attr' => array('value' => $ieName, 'class' => 'form-control datocea', 'oninput' => 'validarnombre(this.value,0)', 'style' => 'text-transform:uppercase')))
            ->add('subcea', 'text', array('label' => 'Nombre Sub Centro:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
            ->add('telefono', 'text', array('label' => 'Teléfono de Referencia:', 'required' => false, 'attr' => array('class' => 'form-control validar datocea')))
            ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
            ->add('zona', 'text', array('label' => 'Zona:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
            ->add('direccion', 'text', array('label' => 'Dirección:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
            ->add('latitud', 'text', array('label' => 'Latitud:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'pointer-events:none')))
            ->add('longitud', 'text', array('label' => 'Longitud:', 'required' => false, 'attr' => array('class' => 'form-control datocea', 'style' => 'pointer-events:none')))
            ->getForm();
        return $form;
    }

    public function upload($file, $ruta)
    {
        // check if the file exists
        /*if(!empty($file)){
            $new_name = date('YmdHis').rand(1,99).'.'.$file->getClientOriginalExtension();
            $directoriomove = $this->get('kernel')->getRootDir() . $ruta;
            if (!file_exists($directoriomove)) {
                mkdir($directoriomove, 0775, true);
            }
            $file->move($directoriomove, $new_name);

        }else{
            $new_name='default-2x.pdf';
        }
        return $new_name;*/
        try {
            $new_name = '';
            if (!empty($file)) {
                $new_name = date('YmdHis') . rand(1, 99) . '.' . $file->getClientOriginalExtension();
                $directoriomove = $this->get('kernel')->getRootDir() . $ruta;
                $file->move($directoriomove, $new_name);
                if (!file_exists($directoriomove . '/' . $new_name)) {
                    return '';
                }
            } else {
                $new_name = '';
            }
            return $new_name;
        } catch (\Doctrine\ORM\NoResultException $ex) {
            return '';
        }
    }

    public function inicioSolicitudModificacionGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        $form = $request->get('form');
        $files = $request->files->get('form');
        //dump($form,$files);die;
        $tramites = json_decode($form['tr'], true);
        //dump($tramites,$form);die;
        $em = $this->getDoctrine()->getManager();

        //variable de control para el cargado de adjunto
        $error_upload = false;

        $gestionActual = $this->session->get('currentyear'); //2019;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = trim(mb_strtoupper($form['observacion'], 'utf-8'));
        $tipotramite = $form['tramitetipo'];
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idrue']);
        $ie_lugardistrito = $ie->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
        $ie_lugarlocalidad = $ie->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();

        /**
         * datos propios de la solicitud del formulario rue
         */

        $query = $em->getConnection()->prepare('SELECT ie.id,ie.institucioneducativa,ie.area_municipio,ie.fecha_fundacion,ie.le_juridicciongeografica_id,ie.estadoinstitucion_tipo_id,et.estadoinstitucion,ie.dependencia_tipo_id,dt.dependencia,ie.convenio_tipo_id,ct.convenio,ies.telefono1, ie.fecha_resolucion, ie.nro_resolucion
                FROM institucioneducativa ie
                left join institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id and ies.gestion_tipo_id=' . $gestionActual . ' 
                join estadoinstitucion_tipo et on ie.estadoinstitucion_tipo_id=et.id
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                left join convenio_tipo ct on ct.id=ie.convenio_tipo_id
                where -- ies.gestion_tipo_id=' . $gestionActual . ' and 
                ie.id=' . $form['idrue']);
        $query->execute();
        $institucioneducativa = $query->fetchAll();

        //dump($form['tramites']);die;
        if ($form['tramites'] == 68 or $form['tramites'] == 69) {

            if (sizeof($institucioneducativa) == 0) {
                $query = $em->getConnection()->prepare('SELECT ie.id,ie.institucioneducativa,ie.area_municipio,ie.fecha_fundacion,ie.le_juridicciongeografica_id,ie.estadoinstitucion_tipo_id,et.estadoinstitucion,ie.dependencia_tipo_id,dt.dependencia,ie.convenio_tipo_id,ct.convenio,ies.telefono1, ie.fecha_resolucion, ie.nro_resolucion
                FROM institucioneducativa ie
                left join institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
                join estadoinstitucion_tipo et on ie.estadoinstitucion_tipo_id=et.id
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                left join convenio_tipo ct on ct.id=ie.convenio_tipo_id
                where  ie.id=' . $form['idrue'] .
                    'ORDER BY ies.gestion_tipo_id DESC');
                $query->execute();
                $institucioneducativa = $query->fetchAll();
            }
            //dump($institucioneducativa);die;
        }

        //dump($institucioneducativa);die;
        $query = $em->getConnection()->prepare('SELECT nt.id,nt.nivel
                FROM institucioneducativa_nivel_autorizado ien
                join nivel_tipo nt on ien.nivel_tipo_id = nt.id
                WHERE ien.institucioneducativa_id=' . $form['idrue']);
        $query->execute();
        $ieNivelAutorizado = $query->fetchAll();

        $query = $em->getConnection()->prepare('SELECT le.id,le.zona,le.direccion,le.distrito_tipo_id,dt.distrito,
                lt.id as localidad2001_id,lt.lugar as localidad2001,lt1.id as canton2001_id,lt1.lugar as canton2001,lt2.id as municipio2001_id,lt2.lugar as municipio2001,lt3.id as provincia2001_id,lt3.lugar as provincia2001,lt4.id as departamento2001_id,lt4.lugar as departamento2001,lt.area2001,
                lt5.id as comunidad2012_id,lt5.lugar as comunidad2012,lt6.id as municipio2012_id,lt6.lugar as municipio2012,lt7.id as provincia2012_id,lt7.lugar as provincia2012,lt8.id as departamento2012_id,lt8.lugar as departamento2012
                FROM jurisdiccion_geografica le
                join distrito_tipo dt on dt.id=le.distrito_tipo_id
                join lugar_tipo lt on lt.id=le.lugar_tipo_id_localidad
                join lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
                join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
                join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
                join lugar_tipo lt4 on lt4.id=lt3.lugar_tipo_id
                left join lugar_tipo lt5 on lt5.id=le.lugar_tipo_id_localidad2012
                left join lugar_tipo lt6 on lt6.id=lt5.lugar_tipo_id
                left join lugar_tipo lt7 on lt7.id=lt6.lugar_tipo_id
                left join lugar_tipo lt8 on lt8.id=lt7.lugar_tipo_id
                WHERE le.id=' . $institucioneducativa[0]['le_juridicciongeografica_id']);
        $query->execute();
        $le = $query->fetchAll();

        $id_tabla = $institucioneducativa[0]['id'];
        $datos = array();
        $datos['institucioneducativa'] = $institucioneducativa[0];
        $datos['jurisdiccion_geografica'] = $le[0];
        $datos['institucioneducativaNivel'] = $ieNivelAutorizado;
        $tramites = $em->getRepository('SieAppWebBundle:TramiteTipo')->createQueryBuilder('tt')
            ->select('tt.id,tt.tramiteTipo as tramite_tipo')
            ->where('tt.id in (:id)')
            ->setParameter('id', $tramites)
            ->getQuery()
            ->getResult();

        $datos['tramites'] = $tramites;
        //$datos['area'] = $form['area'];
        $datos['justificacion'] = trim(mb_strtoupper($form['observacion'], 'utf-8'));
        if ($form['tramite'] == '') {
            $gestion = $this->session->get('currentyear'); //$gestionActual;
        } else {
            $gestion = $em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
        }

        $ruta = '/../web/uploads/archivos/flujos/' . $form['idrue'] . '/rue/' . $gestion . '/';
        $adjunto = '';
        //dump($datos);exit();
        foreach ($tramites as $tramite) {
            $tramiteTipoId = $tramite['id'];
            switch ($tramiteTipoId) {
                case 62: //Ampliacion de Nivel
                    $nt = $form['nivelampliar'];
                    foreach ($ieNivelAutorizado as $n) {
                        $nt[] = $n['id'];
                    }
                    $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                        ->select('nt.id,nt.nivel')
                        ->where('nt.id in (:id)')
                        ->orderBy('nt.id')
                        ->setParameter('id', $nt)
                        ->getQuery()
                        ->getResult();
                    $datos[$tramite['tramite_tipo']]['nivelampliar'] = $nivel;
                    $adjunto = $this->upload($files['i_solicitud_ampliar'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_ampliar'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_compromiso_ampliar'] = $form['i_compromiso_ampliar'];
                    $datos[$tramite['tramite_tipo']]['i_certificado_ampliar'] = $form['i_certificado_ampliar'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_ampliar'] = $form['i_acreditacion_ampliar'];
                    if ($institucioneducativa[0]['dependencia_tipo_id'] == 2) {
                        $datos[$tramite['tramite_tipo']]['i_testimonio_ampliar'] = $form['i_testimonio_ampliar'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_ampliar'] = $form['i_convenio_ampliar'];
                    }
                    $datos[$tramite['tramite_tipo']]['i_folio_ampliar'] = $form['i_folio_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_ampliar'] = $form['ii_planos_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_edificio_ampliar'] = $form['ii_edificio_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_geografica_ampliar'] = $form['ii_geografica_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_equipamiento_ampliar'] = $form['ii_equipamiento_ampliar'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_proy_ampliar'] = $form['iii_curri_proy_ampliar'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_informe_ampliar'] = $form['iii_curri_informe_ampliar'];
                    break;
                case 63: //apertura subcentro
                    $datos[$tramite['tramite_tipo']]['subcea'] = trim(mb_strtoupper($form['subcea'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['telefono'] = $form['telefono'];
                    $datos[$tramite['tramite_tipo']]['idperiodo'] = $form['periodo'];
                    $datos[$tramite['tramite_tipo']]['periodo'] = $em->getRepository('SieAppWebBundle:PeriodoTipo')->find($form['periodo'])->getPeriodo();
                    $datos[$tramite['tramite_tipo']]['iddepartamento'] = $form['departamento'];
                    $datos[$tramite['tramite_tipo']]['departamento'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idprovincia'] = $form['provincia'];
                    $datos[$tramite['tramite_tipo']]['provincia'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idmunicipio'] = $form['municipio'];
                    $datos[$tramite['tramite_tipo']]['municipio'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idcanton'] = $form['canton'];
                    $datos[$tramite['tramite_tipo']]['canton'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idlocalidad'] = $form['localidad'];
                    $datos[$tramite['tramite_tipo']]['localidad'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['iddistrito'] = $le[0]['distrito_tipo_id'];
                    $datos[$tramite['tramite_tipo']]['distrito'] = $le[0]['distrito'];
                    $datos[$tramite['tramite_tipo']]['zona'] = trim(mb_strtoupper($form['zona'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['direccion'] = trim(mb_strtoupper($form['direccion'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['latitud'] = $form['latitud'];
                    $datos[$tramite['tramite_tipo']]['longitud'] = $form['longitud'];

                    $datos[$tramite['tramite_tipo']]['i_informe_director_cea_sc'] = $form['i_informe_director_cea_sc'];
                    break;
                case 71: //Reduccion de Nivel
                    $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                        ->select('nt.id,nt.nivel')
                        ->where('nt.id in (:id)')
                        ->setParameter('id', $form['nivelreducir'])
                        ->getQuery()
                        ->getResult();
                    $datos[$tramite['tramite_tipo']]['nivelreducir'] = $nivel;
                    $adjunto = $this->upload($files['i_solicitud_reducir'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_reducir'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_compromiso_reducir'] = $form['i_compromiso_reducir'];
                    $datos[$tramite['tramite_tipo']]['i_certificado_reducir'] = $form['i_certificado_reducir'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_reducir'] = $form['i_acreditacion_reducir'];
                    if ($institucioneducativa[0]['dependencia_tipo_id'] == 2) {
                        $datos[$tramite['tramite_tipo']]['i_testimonio_reducir'] = $form['i_testimonio_reducir'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_reducir'] = $form['i_convenio_reducir'];
                    }
                    $datos[$tramite['tramite_tipo']]['i_folio_reducir'] = $form['i_folio_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_reducir'] = $form['ii_planos_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_edificio_reducir'] = $form['ii_edificio_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_geografica_reducir'] = $form['ii_geografica_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_equipamiento_reducir'] = $form['ii_equipamiento_reducir'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_proy_reducir'] = $form['iii_curri_proy_reducir'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_informe_reducir'] = $form['iii_curri_informe_reducir'];
                    break;
                case 61: //Ampliación o cambio de especialidades técnicas
                    $adjunto = $this->upload($files['i_solicitud_acesp'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_acesp'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_compromiso_acesp'] = $form['i_compromiso_acesp'];
                    $datos[$tramite['tramite_tipo']]['i_certificado_acesp'] = $form['i_certificado_acesp'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_acesp'] = $form['i_acreditacion_acesp'];
                    $datos[$tramite['tramite_tipo']]['ii_edificio_acesp'] = $form['ii_edificio_acesp'];
                    $datos[$tramite['tramite_tipo']]['ii_equipamiento_acesp'] = $form['ii_equipamiento_acesp'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_proy_acesp'] = $form['iii_curri_proy_acesp'];
                    $datos[$tramite['tramite_tipo']]['iii_curri_informe_acesp'] = $form['iii_curri_informe_acesp'];
                    break;
                case 67: //Cierre de Especialidades Técnicas
                    $datos[$tramite['tramite_tipo']]['i_informe_director_cea_cesp'] = $form['i_informe_director_cea_cesp'];
                    break;
                case 64: //Cambio de Dependencia
                    $d = $em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneBy(array('id' => $form['dependencia']));
                    $datos[$tramite['tramite_tipo']]['dependencia'] = array('id' => $d->getId(), 'dependencia' => $d->getDependencia());
                    if ($form['dependencia'][0] == 2) { //de Fiscal a Convenio
                        $c = $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo']);
                        $datos[$tramite['tramite_tipo']]['conveniotipo'] = array('id' => $c->getId(), 'convenio' => $c->getConvenio());

                        $adjunto = $this->upload($files['i_solicitud_dependencia'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_solicitud_dependencia'] = $adjunto;
                        $datos[$tramite['tramite_tipo']]['i_certificadorue_dependencia'] = $form['i_certificadorue_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_acreditacion_dependencia'] = $form['i_acreditacion_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_testimonio_dependencia'] = $form['i_testimonio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_dependencia'] = $form['i_convenio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_folio_dependencia'] = $form['i_folio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['ii_planos_dependencia'] = $form['ii_planos_dependencia'];
                        $datos[$tramite['tramite_tipo']]['ii_edificio_dependencia'] = $form['ii_edificio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['ii_geografica_dependencia'] = $form['ii_geografica_dependencia'];
                        $datos[$tramite['tramite_tipo']]['iii_informe_director_cea_dependencia'] = $form['iii_informe_director_cea_dependencia'];
                        $datos[$tramite['tramite_tipo']]['iii_informe_distrital_dependencia'] = $form['iii_informe_distrital_dependencia'];
                    } else { //de Convenio a Fiscal
                        $adjunto = $this->upload($files['i_solicitud_dependencia'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_solicitud_dependencia'] = $adjunto;
                        $datos[$tramite['tramite_tipo']]['i_certificadorue_dependencia'] = $form['i_certificadorue_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_acreditacion_dependencia'] = $form['i_acreditacion_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_testimonio_dependencia'] = $form['i_testimonio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_dependencia'] = $form['i_convenio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_doc_protocolizado_dependencia'] = $form['i_doc_protocolizado_dependencia'];
                        $datos[$tramite['tramite_tipo']]['ii_planos_dependencia'] = $form['ii_planos_dependencia'];
                        $datos[$tramite['tramite_tipo']]['iii_informe_director_cea_dependencia'] = $form['iii_informe_director_cea_dependencia'];
                        $datos[$tramite['tramite_tipo']]['iii_informe_distrital_dependencia'] = $form['iii_informe_distrital_dependencia'];
                    }
                    break;
                case 70: //Cambio de Nombre
                    $datos[$tramite['tramite_tipo']]['nuevo_nombre'] = trim(mb_strtoupper($form['nuevo_nombre'], 'utf-8'));

                    $adjunto = $this->upload($files['i_solicitud_cn'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_cn'] = $adjunto;

                    $adjunto = $this->upload($files['i_certdefuncion_cn'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_certdefuncion_cn'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_cn'] = $form['i_certificadorue_cn'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_cn'] = $form['i_acreditacion_cn'];
                    break;
                case 66: //Cambio de Jurisdiccion
                    $d = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['nuevo_distrito']);
                    $datos[$tramite['tramite_tipo']]['nuevo_distrito'] = array('id' => $d->getId(), 'nuevo_distrito' => $d->getDistrito());

                    $adjunto = $this->upload($files['i_solicitud_jur'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_jur'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_compromiso_jur'] = $form['i_compromiso_jur'];
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_jur'] = $form['i_certificadorue_jur'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_jur'] = $form['i_acreditacion_jur'];
                    $datos[$tramite['tramite_tipo']]['i_conformidad_jur'] = $form['i_conformidad_jur'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_jur'] = $form['ii_planos_jur'];
                    $datos[$tramite['tramite_tipo']]['ii_edificio_jur'] = $form['ii_edificio_jur'];
                    $datos[$tramite['tramite_tipo']]['ii_geografica_jur'] = $form['ii_geografica_jur'];
                    $datos[$tramite['tramite_tipo']]['iii_informe_distrital_jur'] = $form['iii_informe_distrital_jur'];
                    break;
                case 39: //Fusion
                    $iefusion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['siefusion']);
                    $datos[$tramite['tramite_tipo']]['siefusion']['id'] = $iefusion->getId();
                    $datos[$tramite['tramite_tipo']]['siefusion']['institucioneducativa'] = $iefusion->getInstitucioneducativa();
                    //$datos[$tramite['tramite_tipo']]['siefusion'] = $form['siefusion'];
                    break;
                case 40: //Desglose
                    break;
                case 65: //Cambio de Infraestructura
                    if ($form['lejurisdiccion']) {
                        $datos[$tramite['tramite_tipo']]['lejurisdiccion'] = $form['lejurisdiccion'];
                    }
                    $datos[$tramite['tramite_tipo']]['zona'] = trim(mb_strtoupper($form['zona'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['direccion'] = trim(mb_strtoupper($form['direccion'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['iddepartamento2001'] = $form['departamento2001'];
                    $datos[$tramite['tramite_tipo']]['departamento2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idprovincia2001'] = $form['provincia2001'];
                    $datos[$tramite['tramite_tipo']]['provincia2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idmunicipio2001'] = $form['municipio2001'];
                    $datos[$tramite['tramite_tipo']]['municipio2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idcanton2001'] = $form['canton2001'];
                    $datos[$tramite['tramite_tipo']]['canton2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idlocalidad2001'] = $form['localidad2001'];
                    $datos[$tramite['tramite_tipo']]['localidad2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['iddepartamento2012'] = $form['departamento2012'];
                    $datos[$tramite['tramite_tipo']]['departamento2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idprovincia2012'] = $form['provincia2012'];
                    $datos[$tramite['tramite_tipo']]['provincia2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idmunicipio2012'] = $form['municipio2012'];
                    $datos[$tramite['tramite_tipo']]['municipio2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idcomunidad2012'] = $form['comunidad2012'];
                    $datos[$tramite['tramite_tipo']]['comunidad2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad2012'])->getLugar();

                    $adjunto = $this->upload($files['i_solicitud_infra'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_infra'] = $adjunto;
                    $datos[$tramite['tramite_tipo']]['i_compromiso_infra'] = $form['i_compromiso_infra'];
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_infra'] = $form['i_certificadorue_infra'];
                    $datos[$tramite['tramite_tipo']]['i_acreditacion_infra'] = $form['i_acreditacion_infra'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_infra'] = $form['ii_planos_infra'];
                    $datos[$tramite['tramite_tipo']]['ii_edificio_infra'] = $form['ii_edificio_infra'];
                    $datos[$tramite['tramite_tipo']]['ii_geografica_infra'] = $form['ii_geografica_infra'];
                    $datos[$tramite['tramite_tipo']]['iii_informe_distrital_infra'] = $form['iii_informe_distrital_infra'];
                    break;
                case 69: //Cierre Temporal
                    $datos[$tramite['tramite_tipo']]['estadoinstitucion'] = $form['estadoinstitucion'];

                    $datos[$tramite['tramite_tipo']]['i_informe_director_cea_cierre_tmp'] = $form['i_informe_director_cea_cierre_tmp'];
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_cierre_tmp'] = $form['i_certificadorue_cierre_tmp'];
                    break;
                case 68: //Cierre Definitivo
                    $datos[$tramite['tramite_tipo']]['estadoinstitucion'] = $form['estadoinstitucion'];

                    $datos[$tramite['tramite_tipo']]['i_certificadorue_cierre'] = $form['i_certificadorue_cierre'];
                    break;
                case 72: //Reapertura
                    break;
                case 45: //Nuevo Certificado RUE
                    $adjunto = $this->upload($files['i_solicitud_nuevorue'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_nuevorue'] = $adjunto;
                    break;
                case 46: //regularizacion rue

                    $adjunto = $this->upload($files['i_solicitud_apertura'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_apertura'] = $adjunto;

                    if ($ie->getDependenciaTipo()->getId() == 1) {
                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_compromiso_apertura'] = $form['i_compromiso_apertura'];
                    }
                    if ($ie->getDependenciaTipo()->getId() == 2) {
                        $datos[$tramite['tramite_tipo']]['i_representante_apertura'] = $form['i_representante_apertura'];

                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_apertura'] = $form['i_convenio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenioadministracion_apertura'] = isset($form['i_convenioadministracion_apertura']) ? $form['i_convenioadministracion_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_constitucion_apertura'] = $form['i_constitucion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_registro_culto_apertura'] = isset($form['i_registro_culto_apertura']) ? $form['i_registro_culto_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_org_nogubernamental_apertura'] = isset($form['i_org_nogubernamental_apertura']) ? $form['i_org_nogubernamental_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_apertura'] = isset($form['i_form_fundaempresa_apertura']) ? $form['i_form_fundaempresa_apertura'] : 0;
                        if (isset($form['i_form_fundaempresa_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa_apertura'] = $form['nro_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa_apertura'] = $form['fecha_fundaempresa_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit_apertura'] = isset($form['i_fotocopia_nit_apertura']) ? $form['i_fotocopia_nit_apertura'] : 0;
                        if (isset($form['i_fotocopia_nit_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nit_apertura'] = $form['nit_apertura'];
                            $datos[$tramite['tramite_tipo']]['i_balance_apertura'] = $form['i_balance_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_testimonioconvenio'] = $form['i_testimonioconvenio'];
                    }
                    $datos[$tramite['tramite_tipo']]['ii_inventario_apertura'] = $form['ii_inventario_apertura'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_apertura'] = $form['ii_planos_apertura'];
                    $datos[$tramite['tramite_tipo']]['iii_poa_apertura'] = $form['iii_poa_apertura'];
                    break;
                case 55: //actualizacion de r.a.
                    $convenioId = $datos['institucioneducativa']['convenio_tipo_id'];
                    $dependenciaId = $datos['institucioneducativa']['dependencia_tipo_id'];
                    $form['dependencia'] = $dependenciaId;
                    if ($dependenciaId == 2) {
                        $form['conveniotipo'] = $convenioId;
                    }
                    if ($dependenciaId == 3) {
                        $form['constitucion'] = $convenioId;
                    }

                    $datos[$tramite['tramite_tipo']]['dependencia'] = array('id' => $form['dependencia'], 'dependencia' => $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($form['dependencia'])->getDependencia());
                    if ($form['dependencia'] == 2) {
                        $datos[$tramite['tramite_tipo']]['conveniotipo'] = array('id' => $form['conveniotipo'], 'convenio' => $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo'])->getConvenio());
                    }
                    if ($form['dependencia'] == 3) {
                        $datos[$tramite['tramite_tipo']]['constitucion'] = array('id' => $form['constitucion'], 'constitucion' => $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['constitucion'])->getConvenio());
                    }

                    $adjunto = $this->upload($files['i_solicitud_apertura'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_apertura'] = $adjunto;

                    if ($form['dependencia'] == 1) {
                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_compromiso_apertura'] = $form['i_compromiso_apertura'];
                    }
                    if ($form['dependencia'] == 2) {
                        $datos[$tramite['tramite_tipo']]['i_representante_apertura'] = $form['i_representante_apertura'];

                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_apertura'] = $form['i_convenio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenioadministracion_apertura'] = isset($form['i_convenioadministracion_apertura']) ? $form['i_convenioadministracion_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_constitucion_apertura'] = $form['i_constitucion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_registro_culto_apertura'] = isset($form['i_registro_culto_apertura']) ? $form['i_registro_culto_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_org_nogubernamental_apertura'] = isset($form['i_org_nogubernamental_apertura']) ? $form['i_org_nogubernamental_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_apertura'] = isset($form['i_form_fundaempresa_apertura']) ? $form['i_form_fundaempresa_apertura'] : 0;
                        if (isset($form['i_form_fundaempresa_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa_apertura'] = $form['nro_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa_apertura'] = $form['fecha_fundaempresa_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit_apertura'] = isset($form['i_fotocopia_nit_apertura']) ? $form['i_fotocopia_nit_apertura'] : 0;
                        if (isset($form['i_fotocopia_nit_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nit_apertura'] = $form['nit_apertura'];
                            $datos[$tramite['tramite_tipo']]['i_balance_apertura'] = $form['i_balance_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_testimonioconvenio'] = $form['i_testimonioconvenio'];
                    }
                    if ($form['dependencia'] == 3) {
                        if ($form['constitucion'] != 48) {
                            $datos[$tramite['tramite_tipo']]['i_personeria_apertura'] = $form['i_personeria_apertura'];
                        }
                        if ($form['constitucion'] == 48) {

                            $adjunto = $this->upload($files['i_afcoop_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['i_afcoop_apertura'] = $adjunto;
                        }
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit_apertura'] = $form['i_fotocopia_nit_apertura'];
                        $datos[$tramite['tramite_tipo']]['nit_apertura'] = $form['nit_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_balance_apertura'] = isset($form['i_balance_apertura']) ? $form['i_balance_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_representante_apertura'] = $form['i_representante_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_copia_ci_apertura'] = $form['i_copia_ci_apertura'];
                        $datos[$tramite['tramite_tipo']]['ci_apertura'] = $form['ci_apertura'];

                        $adjunto = $this->upload($files['i_funcionamiento_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_funcionamiento_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_estatutos_apertura'] = $form['i_estatutos_apertura'];
                        if (($form['constitucion'] == 45 or $form['constitucion'] == 49) and isset($files['i_certificacionculto_apertura'])) {
                            $adjunto = $this->upload($files['i_certificacionculto_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['i_certificacionculto_apertura'] = $adjunto;
                        }
                        if ($form['constitucion'] == 46 or $form['constitucion'] == 47 or $form['constitucion'] == 49) {
                            $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_apertura'] = $form['i_form_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa_apertura'] = $form['nro_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa_apertura'] = $form['fecha_fundaempresa_apertura'];
                        }

                        $adjunto = $this->upload($files['i_empleadores_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_empleadores_apertura'] = $adjunto;

                        if ($form['constitucion'] == 49) {
                            $datos[$tramite['tramite_tipo']]['i_convenio_apertura'] = $form['i_convenio_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['ii_alquiler_apertura'] = $form['ii_alquiler_apertura'];
                        if ($form['ii_alquiler_apertura'] == 'SI') {
                            $adjunto = $this->upload($files['ii_contrato_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['ii_contrato_apertura'] = $adjunto;
                        } else {
                            $datos[$tramite['tramite_tipo']]['ii_folio_apertura'] = $form['ii_folio_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['iii_reglamento_apertura'] = $form['iii_reglamento_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_convivencia_apertura'] = $form['iii_convivencia_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_manual_apertura'] = $form['iii_manual_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_kardex_apertura'] = $form['iii_kardex_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_sippase_apertura'] = $form['iii_sippase_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_contratos_apertura'] = $form['iii_contratos_apertura'];
                    }
                    $datos[$tramite['tramite_tipo']]['ii_inventario_apertura'] = $form['ii_inventario_apertura'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_apertura'] = $form['ii_planos_apertura'];
                    $datos[$tramite['tramite_tipo']]['iii_poa_apertura'] = $form['iii_poa_apertura'];
                    break;
                case 54: //apertura
                    $datos[$tramite['tramite_tipo']]['institucioneducativa'] = trim(mb_strtoupper($form['institucioneducativa'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['fechafundacion'] = $form['fechafundacion'];
                    $datos[$tramite['tramite_tipo']]['telefono'] = $form['telefono'];
                    $datos[$tramite['tramite_tipo']]['director'] = trim(mb_strtoupper($form['director'], 'utf-8'));
                    if ($form['siecomparte']) {
                        $siecomparte = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find(trim($form['siecomparte']));
                        $datos[$tramite['tramite_tipo']]['siecomparte']['id'] = $siecomparte->getId();
                        $datos[$tramite['tramite_tipo']]['siecomparte']['nombre'] = $siecomparte->getInstitucioneducativa();
                        $datos[$tramite['tramite_tipo']]['siecomparte']['subsistema'] = $siecomparte->getOrgcurricularTipo()->getOrgcurricula();
                        $datos[$tramite['tramite_tipo']]['siecomparte']['dependencia'] = $siecomparte->getDependenciaTipo()->getDependencia();
                    }
                    if ($form['lejurisdiccion']) {
                        $datos[$tramite['tramite_tipo']]['lejurisdiccion'] = $form['lejurisdiccion'];
                    }
                    $datos[$tramite['tramite_tipo']]['zona'] = trim(mb_strtoupper($form['zona'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['direccion'] = trim(mb_strtoupper($form['direccion'], 'utf-8'));
                    $datos[$tramite['tramite_tipo']]['iddistrito'] = $le[0]['distrito_tipo_id'];
                    $datos[$tramite['tramite_tipo']]['distrito'] = $le[0]['distrito'];
                    $datos[$tramite['tramite_tipo']]['iddepartamento2001'] = $form['departamento2001'];
                    $datos[$tramite['tramite_tipo']]['departamento2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idprovincia2001'] = $form['provincia2001'];
                    $datos[$tramite['tramite_tipo']]['provincia2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idmunicipio2001'] = $form['municipio2001'];
                    $datos[$tramite['tramite_tipo']]['municipio2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idcanton2001'] = $form['canton2001'];
                    $datos[$tramite['tramite_tipo']]['canton2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idlocalidad2001'] = $form['localidad2001'];
                    $datos[$tramite['tramite_tipo']]['localidad2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad2001'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['iddepartamento2012'] = $form['departamento2012'];
                    $datos[$tramite['tramite_tipo']]['departamento2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idprovincia2012'] = $form['provincia2012'];
                    $datos[$tramite['tramite_tipo']]['provincia2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idmunicipio2012'] = $form['municipio2012'];
                    $datos[$tramite['tramite_tipo']]['municipio2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['idcomunidad2012'] = $form['comunidad2012'];
                    $datos[$tramite['tramite_tipo']]['comunidad2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad2012'])->getLugar();
                    $datos[$tramite['tramite_tipo']]['latitud'] = $form['latitud'];
                    $datos[$tramite['tramite_tipo']]['longitud'] = $form['longitud'];
                    $datos[$tramite['tramite_tipo']]['dependencia'] = array('id' => $form['dependencia'], 'dependencia' => $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($form['dependencia'])->getDependencia());
                    if ($form['dependencia'] == 2) {
                        $datos[$tramite['tramite_tipo']]['conveniotipo'] = array('id' => $form['conveniotipo'], 'convenio' => $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo'])->getConvenio());
                    }
                    if ($form['dependencia'] == 3) {
                        $datos[$tramite['tramite_tipo']]['constitucion'] = array('id' => $form['constitucion'], 'constitucion' => $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['constitucion'])->getConvenio());
                    }
                    $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                        ->select('nt.id,nt.nivel')
                        ->where('nt.id in (:id)')
                        ->orderBy('nt.id')
                        ->setParameter('id', $form['niveltipo'])
                        ->getQuery()
                        ->getResult();
                    $datos[$tramite['tramite_tipo']]['niveltipo'] = $nivel;
                    if (in_array(11, $form['niveltipo'])) {
                        $datos[$tramite['tramite_tipo']]['cantidad_11_1'] = $form['cantidad_11_1'];
                        $datos[$tramite['tramite_tipo']]['cantidad_11_2'] = $form['cantidad_11_2'];
                    }
                    if (in_array(12, $form['niveltipo'])) {
                        $datos[$tramite['tramite_tipo']]['cantidad_12_1'] = $form['cantidad_12_1'];
                        $datos[$tramite['tramite_tipo']]['cantidad_12_2'] = $form['cantidad_12_2'];
                        $datos[$tramite['tramite_tipo']]['cantidad_12_3'] = $form['cantidad_12_3'];
                        $datos[$tramite['tramite_tipo']]['cantidad_12_4'] = $form['cantidad_12_4'];
                        $datos[$tramite['tramite_tipo']]['cantidad_12_5'] = $form['cantidad_12_5'];
                        $datos[$tramite['tramite_tipo']]['cantidad_12_6'] = $form['cantidad_12_6'];
                    }
                    if (in_array(13, $form['niveltipo'])) {
                        $datos[$tramite['tramite_tipo']]['cantidad_13_1'] = $form['cantidad_13_1'];
                        $datos[$tramite['tramite_tipo']]['cantidad_13_2'] = $form['cantidad_13_2'];
                        $datos[$tramite['tramite_tipo']]['cantidad_13_3'] = $form['cantidad_13_3'];
                        $datos[$tramite['tramite_tipo']]['cantidad_13_4'] = $form['cantidad_13_4'];
                        $datos[$tramite['tramite_tipo']]['cantidad_13_5'] = $form['cantidad_13_5'];
                        $datos[$tramite['tramite_tipo']]['cantidad_13_6'] = $form['cantidad_13_6'];
                    }
                    $datos[$tramite['tramite_tipo']]['cantidad_adm'] = $form['cantidad_adm'];
                    $datos[$tramite['tramite_tipo']]['cantidad_maestro'] = $form['cantidad_maestro'];

                    $adjunto = $this->upload($files['i_solicitud_apertura'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos[$tramite['tramite_tipo']]['i_solicitud_apertura'] = $adjunto;
                    if ($form['dependencia'] == 1) {
                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_compromiso_apertura'] = $form['i_compromiso_apertura'];
                    }
                    if ($form['dependencia'] == 2) {
                        $datos[$tramite['tramite_tipo']]['i_representante_apertura'] = $form['i_representante_apertura'];

                        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_actafundacion_apertura'] = $adjunto;

                        $datos[$tramite['tramite_tipo']]['i_folio_apertura'] = $form['i_folio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenio_apertura'] = $form['i_convenio_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_convenioadministracion_apertura'] = isset($form['i_convenioadministracion_apertura']) ? $form['i_convenioadministracion_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_certificacion_apertura'] = $form['i_certificacion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_area_apertura'] = $form['i_area_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_constitucion_apertura'] = $form['i_constitucion_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_registro_culto_apertura'] = isset($form['i_registro_culto_apertura']) ? $form['i_registro_culto_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_org_nogubernamental_apertura'] = isset($form['i_org_nogubernamental_apertura']) ? $form['i_org_nogubernamental_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_apertura'] = isset($form['i_form_fundaempresa_apertura']) ? $form['i_form_fundaempresa_apertura'] : 0;
                        if (isset($form['i_form_fundaempresa_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa_apertura'] = $form['nro_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa_apertura'] = $form['fecha_fundaempresa_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit_apertura'] = isset($form['i_fotocopia_nit_apertura']) ? $form['i_fotocopia_nit_apertura'] : 0;
                        if (isset($form['i_fotocopia_nit_apertura'])) {
                            $datos[$tramite['tramite_tipo']]['nit_apertura'] = $form['nit_apertura'];
                            $datos[$tramite['tramite_tipo']]['i_balance_apertura'] = $form['i_balance_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_testimonioconvenio'] = $form['i_testimonioconvenio'];
                    }
                    if ($form['dependencia'] == 3) {
                        if ($form['constitucion'] != 48) {
                            $datos[$tramite['tramite_tipo']]['i_personeria_apertura'] = $form['i_personeria_apertura'];
                        }
                        if ($form['constitucion'] == 48) {
                            $adjunto = $this->upload($files['i_afcoop_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['i_afcoop_apertura'] = $adjunto;
                        }
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit_apertura'] = $form['i_fotocopia_nit_apertura'];
                        $datos[$tramite['tramite_tipo']]['nit_apertura'] = $form['nit_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_balance_apertura'] = isset($form['i_balance_apertura']) ? $form['i_balance_apertura'] : 0;
                        $datos[$tramite['tramite_tipo']]['i_representante_apertura'] = $form['i_representante_apertura'];
                        $datos[$tramite['tramite_tipo']]['i_copia_ci_apertura'] = $form['i_copia_ci_apertura'];
                        $datos[$tramite['tramite_tipo']]['ci_apertura'] = $form['ci_apertura'];

                        $adjunto = $this->upload($files['i_funcionamiento_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_funcionamiento_apertura'] = $adjunto;
                        $datos[$tramite['tramite_tipo']]['i_estatutos_apertura'] = $form['i_estatutos_apertura'];
                        if (($form['constitucion'] == 45 or $form['constitucion'] == 49) and isset($files['i_certificacionculto_apertura'])) {
                            $adjunto = $this->upload($files['i_certificacionculto_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['i_certificacionculto_apertura'] = $adjunto;
                        }
                        if ($form['constitucion'] == 46 or $form['constitucion'] == 47 or $form['constitucion'] == 49) {
                            $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_apertura'] = $form['i_form_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa_apertura'] = $form['nro_fundaempresa_apertura'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa_apertura'] = $form['fecha_fundaempresa_apertura'];
                        }
                        $adjunto = $this->upload($files['i_empleadores_apertura'], $ruta);
                        if ($adjunto == '') {
                            $error_upload = true;
                        }
                        $datos[$tramite['tramite_tipo']]['i_empleadores_apertura'] = $adjunto;
                        if ($form['constitucion'] == 49) {
                            $datos[$tramite['tramite_tipo']]['i_convenio_apertura'] = $form['i_convenio_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['ii_alquiler_apertura'] = $form['ii_alquiler_apertura'];
                        if ($form['ii_alquiler_apertura'] == 'SI') {
                            $adjunto = $this->upload($files['ii_contrato_apertura'], $ruta);
                            if ($adjunto == '') {
                                $error_upload = true;
                            }
                            $datos[$tramite['tramite_tipo']]['ii_contrato_apertura'] = $adjunto;
                        } else {
                            $datos[$tramite['tramite_tipo']]['ii_folio_apertura'] = $form['ii_folio_apertura'];
                        }
                        $datos[$tramite['tramite_tipo']]['iii_reglamento_apertura'] = $form['iii_reglamento_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_convivencia_apertura'] = $form['iii_convivencia_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_manual_apertura'] = $form['iii_manual_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_kardex_apertura'] = $form['iii_kardex_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_sippase_apertura'] = $form['iii_sippase_apertura'];
                        $datos[$tramite['tramite_tipo']]['iii_contratos_apertura'] = $form['iii_contratos_apertura'];
                    }
                    $datos[$tramite['tramite_tipo']]['ii_inventario_apertura'] = $form['ii_inventario_apertura'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_apertura'] = $form['ii_planos_apertura'];
                    $datos[$tramite['tramite_tipo']]['iii_poa_apertura'] = $form['iii_poa_apertura'];
                    break;
            }
        }
        $datos = json_encode($datos);

        if ($error_upload) {
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Error, Archivo adjunto no fue cargado correctamente.!';
            $mensaje['tipo'] = 'error';
        } else {
            if ($form['tramite'] == '') {
                $mensaje = $this->get('wftramite')->guardarTramiteNuevo($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $tipotramite, '', '', $datos, $ie_lugarlocalidad, $ie_lugardistrito);
                $tipo = 1;
            } else {
                $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, '', $form['tramite'], $datos, $ie_lugarlocalidad, $ie_lugardistrito);
                $tipo = 2;
            }
        }


        //dump($mensaje);die;

        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => $tipo));
    }

    /**
     * Reporte de inicio de solicitud
     */
    public function inicioSolicitudModificacionReporteAction(Request $request)
    {
        //dump($request);die;
        $idtramite = $request->get('idtramite');
        $id_td = $request->get('id_td');
        //dump($idtramite,$id_td);die;
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("select *
            from json_to_recordset((select wf.datos::json->>'tramites'
            from wf_solicitud_tramite wf
            join tramite_detalle td on wf.tramite_detalle_id=td.id
            join flujo_proceso fp on fp.id=td.flujo_proceso_id
            where td.tramite_id=" . $idtramite . "
            and fp.orden=1
            and wf.es_valido = true)::json)
            as x(id int, tramite_tipo text)
            where id=40");

        $query->execute();
        $tramiteTipo = $query->fetchAll();

        $query11 = $em->getConnection()->prepare("select * from tramite tr
        inner join institucioneducativa ie on ie.id=tr.institucioneducativa_id  
        inner join institucioneducativa_nivel_autorizado na on na.institucioneducativa_id=ie.id
        where na.nivel_tipo_id in (201,202,203,204,205)
        and tr.id=" . $idtramite);
        $query11->execute();
        $epja = $query11->fetchAll();

        //dump($epja);die;
        if ($epja) {
            $file = 'rcea_iniciosolicitudModificacionEpja_v1_far.rptdesign';
        } else {
            $file = 'rcea_iniciosolicitudModificacionEduper_v1_far.rptdesign';
        }
        //dump($file);die;
        $query1 = $em->getConnection()->prepare("select 
        'NRO._TRAMITE:_'||cast(td.tramite_id as varchar)||'__'||
        'CODIGO_RUE:_'||cast(wf.datos::json->'institucioneducativa'->>'id' as varchar)||'__'||
        'EDIFICIO_EDUCATIVO:_'||cast(wf.datos::json->'institucioneducativa'->>'le_juridicciongeografica_id' as varchar) AS qr
        from wf_solicitud_tramite wf
        join tramite_detalle td on wf.tramite_detalle_id=td.id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        where td.tramite_id=" . $idtramite . "
        and fp.orden=1
        and wf.es_valido = true");

        $query1->execute();
        $qr = $query1->fetchAll();
        //dump($qr);die;
        $lk = $qr[0]['qr'];
        $arch = 'FORMULARIO_' . $idtramite . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //dump($this->container->getParameter('urlreportweb') . $file . '&idtramite='.$idtramite.'&lk='. $lk .'&&__format=pdf&');die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idtramite=' . $idtramite . '&lk=' . $lk . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /***
     * Formulario Distrito modificacion
     */
    public function recepcionDistritoAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        //dump($tramite);die;
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        //dump($tareasDatos);die;
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $distritoForm = $this->createDistritoForm($flujotipo, $tarea, $tramite, $tramite->getInstitucioneducativa()->getId(), $tareasDatos);
        //dump($tramite);dump($tareasDatos);die;
        return $this->render('SieProcesosBundle:TramiteCea:recepcionDistrito.html.twig', array(
            'form' => $distritoForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));
    }

    public function createDistritoForm($flujotipo, $tarea, $tramite, $idrue, $wfdatos)
    {
        //dump($wfdatos);die;
        $em = $this->getDoctrine()->getManager();

        $tramites = $wfdatos[0]['datos']['tramites'];
        $requisitos = array();
        foreach ($tramites as $t) {
            switch ($t['id']) {
                case 62: //ok
                case 71: //ok
                case 61:
                case 65:
                    if (!isset($requisitos['Requisitos Legales'])) {
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    if (!isset($requisitos['Requisitos de Infraestructura'])) {
                        $requisitos['Requisitos de Infraestructura'] = 'Requisitos de Infraestructura';
                    }
                    if (!isset($requisitos['Requisitos de Técnico - Pedagógicos'])) {
                        $requisitos['Requisitos de Técnico - Pedagógicos'] = 'Requisitos de Técnico - Pedagógicos';
                    }
                    break;
                case 64: //ok
                    if (!isset($requisitos['Requisitos Legales'])) {
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    if (!isset($requisitos['Requisitos de Infraestructura'])) {
                        $requisitos['Requisitos de Infraestructura'] = 'Requisitos de Infraestructura';
                    }
                    if (!isset($requisitos['Requisitos de Técnico - Pedagógicos'])) {
                        $requisitos['Requisitos de Técnico - Pedagógicos'] = 'Requisitos de Técnico - Pedagógicos';
                    }
                    break;
                case 67:
                case 63:
                case 70:
                case 69:
                case 68:
                case 72:
                case 45:
                    if (!isset($requisitos['Requisitos Legales'])) {
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    break;
                case 46:
                case 55:
                case 66:
                    if (!isset($requisitos['Requisitos Legales'])) {
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    if (!isset($requisitos['Requisitos de Infraestructura'])) {
                        $requisitos['Requisitos de Infraestructura'] = 'Requisitos de Infraestructura';
                    }
                    if (!isset($requisitos['Requisitos Administrativos'])) {
                        $requisitos['Requisitos Administrativos'] = 'Requisitos de Técnico - Pedagógicos';
                    }
                    break;
            }
        }
        // dump($tramites,$requisitos);die;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_recepcion_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 5))
            ->add('requisitos', 'choice', array('label' => 'Requisitos:', 'required' => true, 'multiple' => true, 'expanded' => true, 'choices' => $requisitos))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('varevaluacion1', 'choice', array('label' => '¿Observar y devolver?', 'expanded' => true, 'multiple' => false, 'required' => true, 'choices' => array('SI' => 'SI', 'NO' => 'NO'), 'attr' => array('class' => 'form-control')))
            ->add('varevaluacion2', 'choice', array('label' => '¿Informe Procedente?', 'expanded' => true, 'multiple' => false, 'required' => true, 'choices' => array('SI' => 'SI', 'NO' => 'NO'), 'attr' => array('class' => 'form-control')))
            ->add('informedistrito', 'text', array('label' => 'CITE del Informe Técnico:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'placeholder' => '')))
            ->add('fechainformedistrito', 'text', array('label' => 'Fecha del Informe Técnico:', 'required' => false, 'attr' => array('class' => 'form-control date', 'placeholder' => '', 'autocomplete' => 'off')))
            ->add('adjuntoinforme', 'file', array('label' => 'Adjuntar Informe Técnico (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar Informe", 'accept' => "application/pdf,.img,.jpg")));
        $form = $form
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();

        return $form;
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {
        //variable de control para el cargado de adjunto
        $error_upload = false;

        $form = $request->get('form');
        $file = $request->files->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form,$file);die;
        $gestion = $em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
        $ruta = '/../web/uploads/archivos/flujos/' . $form['idrue'] . '/rue/' . $gestion . '/';
        $datos = array();
        $datos['observacion'] = $form['observacion'];
        $datos['varevaluacion1'] = $form['varevaluacion1'];
        $datos['requisitos'] = $form['requisitos'];
        if ($form['varevaluacion1'] == 'SI') {
            $datos['informedistrito'] = $form['informedistrito'];
            $datos['fechainformedistrito'] = $form['fechainformedistrito'];

            $adjunto = $this->upload($file['adjuntoinforme'], $ruta);
            if ($adjunto == '') {
                $error_upload = true;
            }
            $datos['adjuntoinforme'] = $adjunto;

            if (isset($file['actaconformidad'])) {
                $adjunto = $this->upload($file['actaconformidad'], $ruta);
                if ($adjunto == '') {
                    $error_upload = true;
                }
                $datos['actaconformidad'] = $adjunto;

                $adjunto = $this->upload($file['bidistrital'], $ruta);
                if ($adjunto == '') {
                    $error_upload = true;
                }
                $datos['bidistrital'] = $adjunto;
            }
        } else {
            $datos['varevaluacion2'] = $form['varevaluacion2'];
            if ($form['varevaluacion2'] == 'SI') {
                $datos['informedistrito'] = $form['informedistrito'];
                $datos['fechainformedistrito'] = $form['fechainformedistrito'];

                $adjunto = $this->upload($file['adjuntoinforme'], $ruta);
                if ($adjunto == '') {
                    $error_upload = true;
                }
                $datos['adjuntoinforme'] = $adjunto;
            }
            $varevaluacion2 = $form['varevaluacion2'];
        }
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion1 = $form['varevaluacion1'];
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion1, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        if ($mensaje['dato'] == true) {
            if ($varevaluacion1 == "NO") {
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso' => $tarea, 'condicion' => 'NO'));
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario, $tarea, $idtramite);
                if ($mensaje['dato'] == true) {
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion2, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
                    if ($mensaje['dato'] == false) {
                        //eliminar tramite recibido
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        //eliminar tramite enviado
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                    }
                } else {
                    //eliminar tramite enviado
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                }
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);

        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    /***
     * Formulario Distrito apertura/reapertura
     */
    public function recepcionDistritoAperturaAction(Request $request)
    {
        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        //dump($id,$tipo);die;
        //validation if the user is logged
        $em = $this->getDoctrine()->getManager();
        if ($tipo == 'idtramite') {
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tipotramite = $tramite->getTramiteTipo()->getId();
            $tareasDatos = $this->obtieneDatos($tramite);
            $flujotipo = $tramite->getFlujoTipo()->getId();
            $tarea = $tramiteDetalle->getFlujoProceso();
            $idrue = $tramite->getInstitucioneducativa() ? $tramite->getInstitucioneducativa()->getId() : null;
            foreach ($tareasDatos[0]['datos']['tramites'] as $t) {
                if ($t['id'] == 59) {
                    $mapa = true;
                } else {
                    $mapa = false;
                }
            }
        } else {
            $tramite = null;
            $tareasDatos = null;
            $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $id, 'orden' => 1));
            $flujotipo = $id;
            $tarea = $flujoproceso;
            $idrue = null;
            $mapa = false;
        }
        //dump($tarea);die;
        $distritoAperturaForm = $this->createDistritoAperturaForm($flujotipo, $tarea, $tramite, $idrue);
        return $this->render('SieProcesosBundle:TramiteCea:recepcionDistritoApertura.html.twig', array(
            'form' => $distritoAperturaForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tarea,
            'mapa' => $mapa,
        ));
    }

    public function createDistritoAperturaForm($flujotipo, $tarea, $tramite, $idrue)
    {
        //dump($wfdatos);die;
        $em = $this->getDoctrine()->getManager();
        //dump($tramites,$requisitos);die;
        $requisitos = array(
            'Requisitos Legales' => 'Requisitos Legales',
            'Requisitos de Infraestructura' => 'Requisitos de Infraestructura',
            'Requisitos Técnico - Pedagógicos' => 'Requisitos Técnico - Pedagógicos'
        );
        if ($tramite) {
            $this->trArray = array($tramite->getTramiteTipo()->getId());
        } else {
            $this->trArray = array(59, 72);
        }
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_recepcion_distrito_apertura_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea->getId()))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('codigo', 'text', array('label' => 'Código de Solicitud:', 'required' => false, 'attr' => array('class' => 'form-control validar', 'data-placeholder' => "")))
            ->add('buscar', 'button', array('attr' => array('class' => 'btn btn-primary', 'onclick' => 'buscarSolicitud()')))
            ->add('tramite_tipo', 'entity', array(
                'label' => 'Tipo de Trámite:', 'required' => false, 'multiple' => false, 'expanded' => false, 'attr' => array('class' => 'form-control', 'data-placeholder' => "Seleccionar tipo de trámite"), 'class' => 'SieAppWebBundle:TramiteTipo',
                'query_builder' => function (EntityRepository $tr) {
                    return $tr->createQueryBuilder('tr')
                        ->where('tr.obs =:rue')
                        ->andWhere('tr.id in (:tipo)')
                        ->setParameter('rue', 'RCEA')
                        ->setParameter('tipo', $this->trArray)
                        ->orderBy('tr.tramiteTipo', 'ASC');
                },
                'property' => 'tramiteTipo', 'empty_value' => 'Seleccione tipo de trámite'
            ))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('requisitos', 'choice', array('label' => 'Requisitos:', 'required' => true, 'multiple' => true, 'expanded' => true, 'choices' => $requisitos))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('varevaluacion1', 'choice', array('label' => '¿Observar y devolver?', 'expanded' => true, 'multiple' => false, 'required' => true, 'choices' => array('SI' => 'SI', 'NO' => 'NO'), 'attr' => array('class' => 'form-control')))
            ->add('informedistrito', 'text', array('label' => 'CITE del Informe Técnico:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'placeholder' => '')))
            ->add('fechainformedistrito', 'text', array('label' => 'Fecha del Informe Técnico:', 'required' => true, 'attr' => array('class' => 'form-control date', 'placeholder' => '', 'autocomplete' => 'off')))
            ->add('adjuntoinforme', 'file', array('label' => 'Adjuntar Informe Técnico (Máximo permitido 3M):', 'required' => true, 'attr' => array('title' => "Adjuntar Informe", 'accept' => "application/pdf,.img,.jpg")))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function recepcionDistritoAperturaGuardarAction(Request $request)
    {
        //variable de control para el cargado de adjunto
        $error_upload = false;
        $form = $request->get('form');
        $file = $request->files->get('form');
        $em = $this->getDoctrine()->getManager();
        $gestionActual = $this->session->get('currentyear');
        //dump($form,$file);die;
        $datos = array();
        $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo' => $form['codigo']));
        $datosSolicitud = json_decode($solicitudTramite->getDatos(), true);
        //dump($form);die;
        $gestion = $form['tramite'] == '' ? $gestionActual : $em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
        $datos['observacion'] = $form['observacion'];
        $datos['varevaluacion1'] = $form['varevaluacion1'];
        $datos['requisitos'] = $form['requisitos'];
        if ($form['varevaluacion1'] == 'SI') {
            $datos['informedistrito'] = $form['informedistrito'];
            $datos['fechainformedistrito'] = $form['fechainformedistrito'];
            if ($form['tramite_tipo'] == 59) {
                if ($form['tramite'] != '') {
                    $ruta = '/../web/uploads/archivos/flujos/rue/apertura/' . $gestion . '/' . $form['tramite'] . '/';

                    $adjunto = $this->upload($file['adjuntoinforme'], $ruta);
                    if ($adjunto == '') {
                        $error_upload = true;
                    }
                    $datos['adjuntoinforme'] = $adjunto;
                }
            } else {
                $ruta = '/../web/uploads/archivos/flujos/' . $form['idrue'] . '/rue/' . $gestion . '/';
                $adjunto = $this->upload($file['adjuntoinforme'], $ruta);
                if ($adjunto == '') {
                    $error_upload = true;
                }
                $datos['adjuntoinforme'] = $adjunto;
            }
        }

        $datos = array_merge($datosSolicitud, $datos);
        //dump($datos, $form['tramite_tipo']);die;
        if ($form['tramite_tipo'] == 59) {
            $lugar = array('idlugarlocalidad' => $datos['Apertura de Centro de Educacion Alternativa']['idlocalidad2001'], 'idlugardistrito' => $this->session->get('roluserlugarid'));
        } else {
            $lugar = array('idlugarlocalidad' => $datos['jurisdiccion_geografica']['localidad2001_id'], 'idlugardistrito' => $this->session->get('roluserlugarid'));
        }

        $datos = json_encode($datos);

        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        if ($form['tramite_tipo'] == 72) {
            if (isset($form['institucioneducativaid'])) {
                $id_tabla = $form['institucioneducativaid'];
            }
        }
        $tipotramite = $form['tramite_tipo'];
        $observacion = $form['observacion'];
        $varevaluacion1 = $form['varevaluacion1'];
        //$lugar = $this->obtienelugar($idtramite);
        if ($form['tramite'] == '') {
            //dump($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $tipotramite, $varevaluacion1, '', $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);exit;
            $mensaje = $this->get('wftramite')->guardarTramiteNuevo($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $tipotramite, $varevaluacion1, '', $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
            //dump($mensaje);exit;
            $tipo = 1;
            if ($form['tramite_tipo'] == 59) {
                $ruta = '/../web/uploads/archivos/flujos/rue/apertura/' . $gestion . '/' . $mensaje['idtramite'] . '/';
                $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->find($mensaje['iddatos']);
                $datos = json_decode($wfdatos->getDatos(), true);

                $adjunto = $this->upload($file['adjuntoinforme'], $ruta);
                if ($adjunto == '') {
                    $error_upload = true;
                }
                $datos['adjuntoinforme'] = $adjunto;

                $wfdatos->setDatos(json_encode($datos));
                $em->flush();
                $origen = '/../web/uploads/archivos/flujos/rue/solicitud/' . $gestion . '/' . $form['codigo'];
                $destino = '/../web/uploads/archivos/flujos/rue/apertura/' . $gestion . '/' . $mensaje['idtramite'];
                $this->copiarArchivos($origen, $destino);
            }
            $solicitudTramite->setEstado(true);
            $em->flush();
        } else {
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion1, $form['tramite'], $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
            $tipo = 2;
        }

        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => $tipo));
    }

    public function copiarArchivos($file_origen, $file_destino)
    {
        $from = $this->get('kernel')->getRootDir() . $file_origen;
        $to = $this->get('kernel')->getRootDir() . $file_destino;
        //Abro el directorio que voy a leer
        $dir = opendir($from);
        //Recorro el directorio para leer los archivos que tiene
        while (($file = readdir($dir)) !== false) {
            //Leo todos los archivos excepto . y ..
            if (strpos($file, '.') !== 0) {
                //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                copy($from . '/' . $file, $to . '/' . $file);
            }
        }
        return true;
    }

    /***
     * Formulario Departamento
     */
    public function recepcionDepartamentoAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        //dump($tramite, $tareasDatos);die;
        foreach ($tareasDatos[0]['datos']['tramites'] as $t) {
            if ($t['id'] == 59) {
                $mapa = true;
            } else {
                $mapa = false;
            }
        }
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $idrue = $tramite->getInstitucioneducativa() ? $tramite->getInstitucioneducativa()->getId() : '';
        $departamentoForm = $this->createDepartamentoForm($flujotipo, $tarea, $tramite, $idrue);
        //dump($tramite);exit();
        return $this->render('SieProcesosBundle:TramiteCea:recepcionDepartamento.html.twig', array(
            'form' => $departamentoForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo(),
            'mapa' => $mapa
        ));
    }

    public function createDepartamentoForm($flujotipo, $tarea, $tramite, $idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_recepcion_departamento_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramite', 'hidden', array('data' => $tramite->getId()))
            ->add('varevaluacion', 'choice', array('label' => '¿Procedente?', 'expanded' => true, 'multiple' => false, 'required' => true, 'empty_value' => false, 'choices' => array('SI' => 'SI', 'NO' => 'NO'), 'attr' => array('class' => 'form-control')))
            ->add('informesubdireccion', 'text', array('label' => 'CITE del Informe Subdirección Dirección:', 'required' => false, 'attr' => array('class' => 'form-control inf', 'style' => 'text-transform:uppercase')))
            ->add('fechainformesubdireccion', 'text', array('label' => 'Fecha de Informe Subdirección:', 'required' => false, 'attr' => array('class' => 'form-control date inf', 'autocomplete' => 'off')))
            ->add('adjuntoinformesubdireccion', 'file', array('label' => 'Adjuntar Informe Subdirección (Máximo permitido 3M):', 'required' => false, 'attr' => array('class' => 'form-control-file inf', 'title' => "Adjuntar Informe", 'accept' => "application/pdf,.img,.jpg")))
            ->add('informejuridica', 'text', array('label' => 'CITE de Informe Legal:', 'required' => false, 'attr' => array('class' => 'form-control inf', 'style' => 'text-transform:uppercase')))
            ->add('fechainformejuridica', 'text', array('label' => 'Fecha de Informe Legal:', 'required' => false, 'attr' => array('class' => 'form-control date inf', 'autocomplete' => 'off')))
            ->add('adjuntoinformejuridica', 'file', array('label' => 'Adjuntar Informe Legal (Máximo permitido 3M):', 'required' => false, 'attr' => array('class' => 'form-control-file inf', 'title' => "Adjuntar Informe", 'accept' => "application/pdf,.img,.jpg")))
            ->add('resolucion', 'text', array('label' => 'Nro. de Resolución Administrativa:', 'required' => false, 'attr' => array('class' => 'form-control resol', 'style' => 'text-transform:uppercase')))
            ->add('fecharesolucion', 'text', array('label' => 'Fecha de Resolución Administrativa:', 'required' => false, 'attr' => array('class' => 'form-control date resol')))
            ->add('adjuntoresolucion', 'file', array('label' => 'Adjuntar Resolución Administrativa (Máximo permitido 3M):', 'required' => false, 'attr' => array('title' => "Adjuntar Resolución", 'accept' => "application/pdf,.img,.jpg")))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control inf', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function recepcionDepartamentoGuardarAction(Request $request)
    {
        //variable de control para el cargado de adjunto
        $error_upload = false;
        $form = $request->get('form');
        $files = $request->files->get('form');
        $em = $this->getDoctrine()->getManager();

        $gestion = $em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
        if ($form['idrue'] == '') {
            $ruta = '/../web/uploads/archivos/flujos/rue/apertura/' . $gestion . '/' . $form['tramite'] . '/';
        } else {
            $ruta = '/../web/uploads/archivos/flujos/' . $form['idrue'] . '/rue/' . $gestion . '/';
        }
        $datos = array();
        $datos['observacion'] = $form['observacion'];
        $datos['varevaluacion'] = $form['varevaluacion'];
        $datos['informesubdireccion'] = $form['informesubdireccion'];
        $datos['fechainformesubdireccion'] = $form['fechainformesubdireccion'];
        if ($form['informesubdireccion']) {

            $adjunto = $this->upload($files['adjuntoinformesubdireccion'], $ruta);
            if ($adjunto == '') {
                $error_upload = true;
            }
            $datos['adjuntoinformesubdireccion'] = $adjunto;
        }
        if ($form['varevaluacion'] == 'SI') {
            $datos['informejuridica'] = $form['informejuridica'];
            $datos['fechainformejuridica'] = $form['fechainformejuridica'];

            $adjunto = $this->upload($files['adjuntoinformejuridica'], $ruta);
            if ($adjunto == '') {
                $error_upload = true;
            }
            $datos['adjuntoinformejuridica'] = $adjunto;
            $datos['resolucion'] = $form['resolucion'];
            $datos['fecharesolucion'] = $form['fecharesolucion'];

            $adjunto = $this->upload($files['adjuntoresolucion'], $ruta);
            if ($adjunto == '') {
                $error_upload = true;
            }
            $datos['adjuntoresolucion'] = $adjunto;
        }
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion = $form['varevaluacion'];
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    /***
     * Formulario Minedu
     */
    public function recepcionMineduAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        //dump($tareasDatos);die;
        foreach ($tareasDatos[0]['datos']['tramites'] as $t) {
            if ($t['id'] == 54) {
                $mapa = true;
            } else {
                $mapa = false;
            }
        }
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $idrue = $tramite->getInstitucioneducativa() ? $tramite->getInstitucioneducativa()->getId() : '';
        $mineduForm = $this->createMineduForm($flujotipo, $tarea, $tramite, $idrue);
        return $this->render('SieProcesosBundle:TramiteCea:recepcionMinedu.html.twig', array(
            'form' => $mineduForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo(),
            'mapa' => $mapa,
        ));
    }

    public function createMineduForm($flujotipo, $tarea, $tramite, $idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_recepcion_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 5))
            ->add('varevaluacion', 'choice', array('label' => '¿Procedente?', 'expanded' => true, 'multiple' => false, 'required' => true, 'choices' => array('SI' => 'SI', 'NO' => 'NO'), 'attr' => array('class' => 'form-control')))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Registrar Modificación'))
            ->getForm();
        return $form;
    }

    public function recepcionMineduGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos = array();
        $datos['observacion'] = mb_strtoupper($form['observacion'], 'utf-8');;
        $datos['varevaluacion'] = $form['varevaluacion'];
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = mb_strtoupper($form['observacion'], 'utf-8');
        $varevaluacion = $form['varevaluacion'];
        $lugar = $this->obtienelugar($idtramite);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        if ($mensaje['dato'] == true) {
            if ($varevaluacion == "SI") {
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso' => $tarea, 'condicion' => 'SI'));
                $varevaluacion2 = "";
                $observacion2 = mb_strtoupper($form['observacion'], 'utf-8');;
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario, $tarea, $idtramite);

                if ($mensaje['dato'] == true) {
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion2, $varevaluacion2, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
                    if ($mensaje['dato'] == true) {
                        /**
                         * Registrar en el RUE
                         */
                        $em->getConnection()->beginTransaction();
                        try {
                            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
                            $tareasDatos = $this->obtieneDatos($tramite);
                            if ($tramite->getInstitucioneducativa()) {
                                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                                $iddistrito = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'];
                            }
                            $tipo = '';
                            //dump($tareasDatos);die;
                            foreach ($tareasDatos[0]['datos']['tramites'] as $t) {
                                $vAnterior = array();
                                $vNuevo = array();
                                if ($t['id'] == 62) { #ampliacion de nivel incluir resolucion
                                    foreach ($tareasDatos[0]['datos']['institucioneducativaNivel'] as $n) {
                                        $arr[] = $n['id'];
                                    }
                                    $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelampliar'];
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $em->flush();
                                    //adiciona niveles nuevos
                                    foreach ($nuevoNivel as $n) {
                                        if (!in_array($n['id'], $arr)) {
                                            $nivel = new InstitucioneducativaNivelAutorizado();
                                            $nivel->setFechaRegistro(new \DateTime('now'));
                                            $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                            $nivel->setInstitucioneducativa($institucioneducativa);
                                            $em->persist($nivel);
                                        }
                                    }
                                    $em->flush();
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($tareasDatos[0]['datos']['institucioneducativaNivel']), json_encode($tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelampliar']), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 63) { #Apertura de sub centro
                                    $fechaActual = new \DateTime(date('Y-m-d'));
                                    $session = $request->getSession();
                                    $usuario_id = $session->get('userId');
                                    $em = $this->getDoctrine()->getManager();
                                    $form = $request->get('form');

                                    $idInstitucion = $id_tabla; //fRnk: traer el id de la institución
                                    $gestion = $fechaActual->format('Y');
                                    $periodo = $tareasDatos[0]['datos'][$t['tramite_tipo']]['idperiodo'];
                                    $nombre = strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['subcea']);
                                    $departamentoId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['iddepartamento'];
                                    $provinciaId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['idprovincia'];
                                    $municipioId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['idmunicipio'];
                                    $cantonId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['idcanton'];
                                    $localidadId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['idlocalidad'];
                                    $distritoId = $tareasDatos[0]['datos'][$t['tramite_tipo']]['iddistrito'];
                                    $direccion = strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['direccion']);
                                    $zona = strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['zona']);

                                    $subcea = 0;

                                    $usuario_lugar = $this->session->get('roluserlugarid');
                                    $usuario_rol = $this->session->get('roluser');
                                    $persona_id = $this->session->get('personaId');

                                    //verificamos si existe la Institución Educativa
                                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $idInstitucion, 'institucioneducativaTipo' => 2));
                                    if (!$institucioneducativa) {
                                        throw new \Exception('El código SIE ingresado no es válido.');
                                    }

                                    $queryEntidad = $em->getConnection()->prepare("select max(sucursal_tipo_id) as sucursal_tipo_id from institucioneducativa_sucursal where institucioneducativa_id = " . $idInstitucion);
                                    $queryEntidad->execute();
                                    $objEntidad = $queryEntidad->fetchAll();
                                    //dump($objEntidad);exit();
                                    if (count($objEntidad) < 1) {
                                        throw new \Exception('El código SIE ingresado no es válido.');
                                    } else {
                                        $subcea = ((int)$objEntidad[0]['sucursal_tipo_id']) + 1;
                                    }

                                    $queryEntidad = $em->getConnection()->prepare("select sucursal_tipo_id from institucioneducativa_sucursal where institucioneducativa_id = " . $idInstitucion . " and nombre_subcea like trim('" . $nombre . "')");

                                    $queryEntidad->execute();
                                    $objEntidadValidaNombre = $queryEntidad->fetchAll();
                                    if (count($objEntidadValidaNombre) > 0) {
                                        throw new \Exception('El nombre del SUB CEA ya se encuentra registrado con el numero ' . $subcea . '.');
                                    }
                                    /*
                                                                        $entityInstitucionEducativaSucursalCentral = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $idInstitucion, 'gestionTipo' => $gestion, 'sucursalTipo' => 0, 'periodoTipoId' => $periodo));
                                                                        if (!$entityInstitucionEducativaSucursalCentral) {
                                                                            throw new \Exception('El CEA ' . $idInstitucion . ' no cuenta con el SUB CEA 0 habilitado, debe aperturar el CEA CENTRAL en la gestion y periodo seleccionado antes de abrir otro SUB CEA.');
                                                                        }*/
                                    //NOS QUEDAMOS AQUI
                                    $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
                                    $query = $repository->createQueryBuilder('ie')
                                        ->select('ies')
                                        ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                                        ->where('ie.id = :idInstitucion')
                                        ->andWhere('ies.gestionTipo = :gestions')
                                        ->andWhere('ies.periodoTipoId = :periodo')
                                        ->andWhere('ies.sucursalTipo = :sucursal')
                                        ->setParameter('idInstitucion', $idInstitucion)
                                        ->setParameter('gestions', $gestion)
                                        ->setParameter('periodo', $periodo)
                                        ->setParameter('sucursal', $subcea)
                                        ->setMaxResults(1)
                                        ->getQuery();
                                    $inscripciones = $query->getResult();
                                    $vAnterior = array();
                                    $vNuevo = array();
                                    if ($inscripciones) {
                                        throw new \Exception('El CEA ya cuenta con el SUB CEA ' . $subcea . ' habilitada.');
                                    } else {
                                        //$em->getConnection()->beginTransaction();
                                        try {
                                            // $entityInstitucionEducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('id' => $idiesuc));
                                            $entityLocalidadLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $localidadId));
                                            $entityDistritoLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $distritoId));
                                            $entityDistritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneBy(array('id' => $entityDistritoLugarTipo->getCodigo()));
                                            $distritoCodigo = $entityDistritoLugarTipo->getCodigo();
                                            $entityValidacionGeograficaTipo = $em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneBy(array('id' => 0));
                                            $entityJuridiccionAcreditacionTipo = $em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->findOneBy(array('id' => 4));
                                            $query = $em->getConnection()->prepare("
                                                        select cast(coalesce(max(cast(substring(cast(id as varchar) from (length(cast(id as varchar))-2) for 3) as integer)),0) + 1 as varchar) as id
                                                        from jurisdiccion_geografica
                                                        where juridiccion_acreditacion_tipo_id = 4
                                                    ");
                                            $query->execute();
                                            $entityId = $query->fetchAll();
                                            $nuevoId = $distritoCodigo . str_pad($entityId[0]['id'], 3, "0", STR_PAD_LEFT);

                                            $entityJurisdiccionGeografica = new JurisdiccionGeografica();
                                            $entityJurisdiccionGeografica->setId($nuevoId);
                                            $entityJurisdiccionGeografica->setLugarTipoLocalidad($entityLocalidadLugarTipo);
                                            $entityJurisdiccionGeografica->setLugarTipoIdDistrito($distritoId);
                                            $entityJurisdiccionGeografica->setObs('NUEVO SUCURSAL SUB C.E.A.');
                                            $entityJurisdiccionGeografica->setDistritoTipo($entityDistritoTipo);
                                            $entityJurisdiccionGeografica->setDireccion(mb_strtoupper($direccion, 'UTF-8'));
                                            $entityJurisdiccionGeografica->setZona(mb_strtoupper($zona, 'UTF-8'));
                                            $entityJurisdiccionGeografica->setJuridiccionAcreditacionTipo($entityJuridiccionAcreditacionTipo);
                                            $entityJurisdiccionGeografica->setValidacionGeograficaTipo($entityValidacionGeograficaTipo);
                                            $entityJurisdiccionGeografica->setFechaRegistro($fechaActual);
                                            $entityJurisdiccionGeografica->setUsuarioId($usuario_id);
                                            $em->persist($entityJurisdiccionGeografica);

                                            $entityGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion));
                                            $entityInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $idInstitucion));
                                            $entitySucursalTipo = $em->getRepository('SieAppWebBundle:SucursalTipo')->findOneBy(array('id' => $subcea));
                                            if (!$entitySucursalTipo) { //fRnk esto corregir para que haga un insert nuevo, tambien agregar para que inserte las coordenadas
                                                // $stn = new SucursalTipo();
                                                //  $em->persist($stn);
                                                $entitySucursalTipo = $em->getRepository('SieAppWebBundle:SucursalTipo')->findOneBy(array('id' => 255));
                                            }

                                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');")->execute();
                                            $entityInstitucionEducativaSucursal = new InstitucioneducativaSucursal();
                                            $entityInstitucionEducativaSucursal->setNombreSubcea($nombre, 'UTF-8');
                                            $entityInstitucionEducativaSucursal->setCodCerradaId(10);
                                            $entityInstitucionEducativaSucursal->setPeriodoTipoId($periodo);
                                            $entityInstitucionEducativaSucursal->setGestionTipo($entityGestionTipo);
                                            $entityInstitucionEducativaSucursal->setInstitucioneducativa($entityInstitucioneducativa);
                                            $entityInstitucionEducativaSucursal->setLeJuridicciongeografica($entityJurisdiccionGeografica);
                                            $entityInstitucionEducativaSucursal->setSucursalTipo($entitySucursalTipo);
                                            $entityInstitucionEducativaSucursal->setDireccion($direccion);
                                            $entityInstitucionEducativaSucursal->setZona($zona);
                                            $entityInstitucionEducativaSucursal->setEsabierta(true);

                                            $entityInstitucionEducativaSucursal->setLeJuridicciongeografica($entityJurisdiccionGeografica);
                                            $em->persist($entityInstitucionEducativaSucursal);
                                            $em->flush();
                                            //$em->getConnection()->commit();
                                            //$this->get('session')->getFlashBag()->add('successMsg', 'Se habilito el SUB CEA ' . $subcea . ' - ' . $nombre . ' correctamente.');
                                            $vAnterior['ceanuevo'] = 0;
                                            $vNuevo['ceanuevo'] = $entityInstitucionEducativaSucursal->getId();
                                        } catch (\Doctrine\ORM\NoResultException $exc) {
                                            throw new \Exception('Ha ocurrido un problema en la generación del SUB CEA.');
                                        }
                                    }

                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 71) { #reduccion de nivel incluir resolucion
                                    $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir'];
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $em->flush();
                                    //elimina los niveles
                                    $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $institucioneducativa->getId()));
                                    if ($nivelesElim) {
                                        foreach ($nivelesElim as $nivel) {
                                            $em->remove($nivel);
                                        }
                                        $em->flush();
                                    }
                                    //reduce niveles
                                    foreach ($nuevoNivel as $n) {
                                        //dump($n);die;
                                        $nivel = new InstitucioneducativaNivelAutorizado();
                                        $nivel->setFechaRegistro(new \DateTime('now'));
                                        $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                        $nivel->setInstitucioneducativa($institucioneducativa);
                                        $em->persist($nivel);
                                    }
                                    $em->flush();
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($tareasDatos[0]['datos']['institucioneducativaNivel']), json_encode($tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir']), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 61) { #Ampliación o cambio de Especialidad
                                    /*$nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir'];
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $em->flush();
                                    //elimina los niveles
                                    $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $institucioneducativa->getId()));
                                    if ($nivelesElim) {
                                        foreach ($nivelesElim as $nivel) {
                                            $em->remove($nivel);
                                        }
                                        $em->flush();
                                    }
                                    //reduce niveles
                                    foreach ($nuevoNivel as $n) {
                                        //dump($n);die;
                                        $nivel = new InstitucioneducativaNivelAutorizado();
                                        $nivel->setFechaRegistro(new \DateTime('now'));
                                        $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                        $nivel->setInstitucioneducativa($institucioneducativa);
                                        $em->persist($nivel);
                                    }
                                    $em->flush();*/
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode(array()), json_encode(array()), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 67) { #Cierre de Especialidades Técnicas
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode(array()), json_encode(array()), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 64) { #cambio de dependencia incluir resolucion
                                    $dependenciaTipo = $em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id']);
                                    $vAnterior['dependencia']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['dependencia_tipo_id'];
                                    $vAnterior['dependencia']['dependencia'] = $tareasDatos[0]['datos']['institucioneducativa']['dependencia'];
                                    $vNuevo['dependencia'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia'];
                                    $institucioneducativa->setDependenciaTipo($dependenciaTipo);
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    if ($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id'] == 2) { //convenio
                                        $convenio = $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['conveniotipo']['id']);
                                        $institucioneducativa->setConvenioTipo($convenio);
                                        $vNuevo['conveniotipo'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['conveniotipo'];
                                    } else { //fiscal
                                        $institucioneducativa->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById(0));
                                        $vAnterior['conveniotipo']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['convenio_tipo_id'];
                                        $vAnterior['conveniotipo']['convenio'] = $tareasDatos[0]['datos']['institucioneducativa']['convenio'];
                                    }
                                    $em->flush();

                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 70) { #cambio de nombre incluir resolucion
                                    $institucioneducativa->setInstitucioneducativa(mb_strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_nombre'], 'utf-8'));
                                    $institucioneducativa->setDesUeAntes(mb_strtoupper($tareasDatos[0]['datos']['institucioneducativa']['institucioneducativa'], 'utf-8'));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $em->flush();

                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], mb_strtoupper($tareasDatos[0]['datos']['institucioneducativa']['institucioneducativa'], 'utf-8'), mb_strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_nombre'], 'utf-8'), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 66) { #cambio de jurisdiccion administrativa incluir resolucion
                                    $iddistrito = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito']['id'];
                                    $lugarIdDistrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $iddistrito))->getId();
                                    $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($iddistrito);
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $jurisdicciongeografica = $institucioneducativa->getLeJuridiccionGeografica();
                                    $jurisdicciongeografica->setLugarTipoIdDistrito($lugarIdDistrito);
                                    $jurisdicciongeografica->setDistritoTipo($distritoTipo);
                                    $jurisdicciongeografica->setFechaModificacion(new \DateTime('now'));
                                    $em->flush();
                                    $vAnterior['distrito']['id'] = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'];
                                    $vAnterior['distrito']['distrito'] = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito'];
                                    $vNuevo['nuevo_distrito'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito'];

                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 39) { #Fusion incluir resolucion
                                    $tipo = 'fusion';
                                    $iefusion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($tareasDatos[0]['datos'][$t['tramite_tipo']]['siefusion']['id']);

                                    $vAnterior['siefusion'] = array('sie1' => $institucioneducativa->getId(), 'sie2' => $iefusion->getId());
                                    $vNuevo = array('sieresultante' => $institucioneducativa->getId());
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 40) { #Desglose incluir resolucion
                                    $tipo = 'desglose';
                                } elseif ($t['id'] == 65) { #cambio de infraestructura
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    if (isset($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'])) {
                                        $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion']));
                                    } else {
                                        $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($this->obtieneCodigoLe($tareasDatos[0]['datos'][$t['tramite_tipo']], $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'], $usuario)));
                                    }
                                    $em->flush();
                                    $vAnterior = array('jurisdiccion_geografica_id' => $tareasDatos[0]['datos']['jurisdiccion_geografica']['id']);
                                    $vNuevo = array('jurisdiccion_geografica_id' => $institucioneducativa->getLeJuridicciongeografica()->getId());
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 68 or $t['id'] == 69) { #cierre temporal
                                    $estado = $em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(19);

                                    $institucioneducativa->setEstadoinstitucionTipo($estado);
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setFechaCierre((new \DateTime('now'))->format('Y-m-d'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $vAnterior['estado']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion_tipo_id'];
                                    $vAnterior['estado']['estado'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion'];
                                    $em->flush();

                                    $vNuevo['estado']['id'] = $estado->getId();
                                    $vNuevo['estado']['estado'] = $estado->getEstadoinstitucion();
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 72) { #reapertura incluir resolucion
                                    $estado = $em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(10);
                                    $institucioneducativa->setEstadoinstitucionTipo($estado);
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[1]['datos']['resolucion'], 'utf-8'));
                                    $vAnterior['estado']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion_tipo_id'];
                                    $vAnterior['estado']['estado'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion'];
                                    $em->flush();

                                    $vNuevo['estado']['id'] = $estado->getId();
                                    $vNuevo['estado']['estado'] = $estado->getEstadoinstitucion();
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[1]['datos']['resolucion'], $tareasDatos[1]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 46) { #regularizacion rue incluir resolucion , ya incluido
                                    $vAnterior['nro_resolucion'] = $institucioneducativa->getNroResolucion();
                                    $vAnterior['fecha_resolucion'] = $institucioneducativa->getFechaResolucion() ? $institucioneducativa->getFechaResolucion()->format('d-m-Y') : '';
                                    $institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $institucioneducativa->setObsRue($observacion);
                                    $em->flush();

                                    $vNuevo['nro_resolucion'] = $tareasDatos[2]['datos']['resolucion'];
                                    $vNuevo['fecha_resolucion'] = $tareasDatos[2]['datos']['fecharesolucion'];
                                    $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, $t['id'], $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                } elseif ($t['id'] == 59) {
                                    //$nuevaInstitucioneducativa = $this->registrarInstitucioneducativa($tareasDatos[0][$t['tramite_tipo']]);
                                    $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
                                    //dump($datosSolicitud);die;
                                    if (isset($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'])) {
                                        $codLe = $tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'];
                                    } else {
                                        $codLe = $this->obtieneCodigoLe($tareasDatos[0]['datos'][$t['tramite_tipo']], $tareasDatos[0]['datos'][$t['tramite_tipo']]['iddistrito'], $usuario);
                                    }
                                    $query = $em->getConnection()->prepare('SELECT get_genera_codigo_ue(:codle)');
                                    $query->bindValue(':codle', $codLe);
                                    $query->execute();
                                    $codigoue = $query->fetchAll();
                                    //dump($codigoue);die;
                                    $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
                                    $epja = array(201, 202, 203, 204, 205);
                                    $institucioneducativaTipo = 5; //permanente
                                    foreach ($datosSolicitud['niveltipo'] as $n) {
                                        if (array_search($n['id'], $epja) !== false) {
                                            $institucioneducativaTipo = 2; //alternativa
                                            break;
                                        }
                                    }
                                    $ieducativatipo = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($institucioneducativaTipo);
                                    $entity = new Institucioneducativa();
                                    $entity->setId($codigoue[0]["get_genera_codigo_ue"]);
                                    $entity->setInstitucioneducativa(mb_strtoupper($datosSolicitud['institucioneducativa'], 'utf-8'));
                                    $entity->setFechaResolucion(new \DateTime($tipo == 'desglose' ? $tareasDatos[2]['datos']['fecharesolucion'] : $tareasDatos[1]['datos']['fecharesolucion']));
                                    $entity->setFechaCreacion(new \DateTime('now'));
                                    $entity->setFechaFundacion(new \DateTime($datosSolicitud['fechafundacion']));
                                    $entity->setNroResolucion(mb_strtoupper($tipo == 'desglose' ? $tareasDatos[2]['datos']['resolucion'] : $tareasDatos[1]['datos']['resolucion'], 'utf-8'));
                                    $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($datosSolicitud['dependencia']['id']));
                                    if ($datosSolicitud['dependencia']['id'] == 2) {
                                        $convenio = $datosSolicitud['conveniotipo']['id'];
                                        $areaMunicipio = $datosSolicitud['i_area_apertura'];
                                    } elseif ($datosSolicitud['dependencia']['id'] == 3) {
                                        $convenio = $datosSolicitud['constitucion']['id'];
                                        $areaMunicipio = null;
                                    } else {
                                        $convenio = 0;
                                        $areaMunicipio = $datosSolicitud['i_area_apertura'];
                                    }
                                    $entity->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($convenio));
                                    $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(10));
                                    $entity->setInstitucioneducativaTipo($ieducativatipo);
                                    $entity->setObsRue($observacion);
                                    $entity->setAreaMunicipio($areaMunicipio);
                                    $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codLe));
                                    $entity->setOrgcurricularTipo($ieducativatipo->getOrgcurricularTipo());
                                    $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(1));
                                    //dump($entity);die;
                                    $em->persist($entity);
                                    $em->flush();

                                    foreach ($datosSolicitud['niveltipo'] as $n) {
                                        $nivel = new InstitucioneducativaNivelAutorizado();
                                        $nivel->setFechaRegistro(new \DateTime('now'));
                                        $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                        $nivel->setInstitucioneducativa($entity);
                                        $em->persist($nivel);
                                    }
                                    $em->flush();

                                    //actualizamos el tramite con la unidad educativa creada
                                    $tramite->setInstitucioneducativa($entity);
                                    $em->flush();
                                    // Try and commit the transaction
                                    $mensaje['msg'] = "El trámite Nro. " . $tramite->getId() . " de APERTURA DE UNIDAD EDUCATIVA para la institución educativa " . $entity->getInstitucioneducativa() . " fue registrada correctamente con el código RUE: " . $entity->getId() . ", y el código de Local educativo: " . $entity->getLeJuridicciongeografica()->getId();
                                    if ($tipo == 'desglose') {
                                        $vAnterior['siedesglose'] = $institucioneducativa->getId();
                                        $vAnterior['sienuevo'] = $entity->getId();
                                        $historial = $this->registraHistorialTramite($institucioneducativa, $tramite, 54, $tareasDatos[2]['datos']['resolucion'], $tareasDatos[2]['datos']['fecharesolucion'], json_encode($vAnterior), json_encode($vNuevo), $form['observacion'], $usuario);
                                    }
                                } elseif ($t['id'] == 55) { //Actualizacion de Resolucion
                                    //$nuevaInstitucioneducativa = $this->registrarInstitucioneducativa($tareasDatos[0][$t['tramite_tipo']]);
                                    $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
                                    $codigoue = $tareasDatos[0]['datos']['institucioneducativa']['id'];
                                    $datosRequisito = $tareasDatos[2]['datos'];
                                    //dump($codigoue);die;
                                    $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($codigoue);
                                    $entity->setFechaResolucion(new \DateTime($datosRequisito['fecharesolucion']));
                                    $entity->setNroResolucion(mb_strtoupper($datosRequisito['resolucion']));
                                    $em->persist($entity);
                                    $em->flush();

                                    // Try and commit the transaction
                                    $mensaje['msg'] = "El trámite Nro. " . $tramite->getId() . " de APERTURA DE UNIDAD EDUCATIVA para la institución educativa " . $entity->getInstitucioneducativa() . " fue registrada correctamente con el código RUE: " . $entity->getId() . ", y el código de Local educativo: " . $entity->getLeJuridicciongeografica()->getId();
                                }
                            }
                            $em->getConnection()->commit();
                        } catch (\Exception $ex) {
                            $mensaje['msg'] = '¡Ocurrio un error al registrar los datos, vuelva a intentar!</br> ' . $ex->getMessage();
                            $mensaje['tipo'] = 'error';
                            $em->getConnection()->rollback();
                            //dump($em);
                            $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                            $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                            $c = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                            //dump($b.'-b',$a.'-a',$c.'-c');die;
                            //dump($ex->getMessage().'---bd');
                        }
                    } else {
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                    }
                } else {
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite, $usuario);
                }
            }
        }

        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);

        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    public function registraHistorialTramite($institucioneducativa, $tramite, $tramiTipoId, $nroResolucion, $fechaResolucion, $valorAnterior, $valorNuevo, $obs, $usuario)
    {

        $em = $this->getDoctrine()->getManager();
        $historial = new InstitucioneducativaHistorialTramite();
        $historial->setInstitucioneducativa($institucioneducativa);
        $historial->setTramite($tramite);
        $historial->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find($tramiTipoId));
        $historial->setNroResolucion(mb_strtoupper($nroResolucion, 'utf-8'));
        $historial->setFechaResolucion(new \DateTime($fechaResolucion));
        $historial->setValorAnterior($valorAnterior);
        $historial->setValorNuevo($valorNuevo);
        $historial->setObservacion(mb_strtoupper($obs, 'utf-8'));
        $historial->setFechaRegistro(new \DateTime('now'));
        $historial->setUsuarioRegistro($em->getRepository('SieAppWebBundle:Usuario')->find($usuario));
        $em->persist($historial);
        $em->flush();

        return $historial;
    }

    /***
     * Formulario envio certificados
     */
    public function enviaCertificadoMineduAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $enviaCertificadoMinedu = $this->createEnviaCertificadoMineduForm($flujotipo, $tarea, $tramite, $tramite->getInstitucioneducativa()->getId());
        return $this->render('SieProcesosBundle:TramiteCea:enviaCertificadoMinedu.html.twig', array(
            'form' => $enviaCertificadoMinedu->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));
    }

    public function createEnviaCertificadoMineduForm($flujotipo, $tarea, $tramite, $idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_envia_certificado_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 5))
            //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function enviaCertificadoMineduGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos = array();
        $datos['observacion'] = $form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion = "";
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        if ($mensaje['dato'] == true) {
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje['msg']);
        } else {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    /***
     * Formulario recepcion y envio certificados departamento
     */
    public function enviaCertificadoDepartamentoAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $enviaCertificadoDepartamentoForm = $this->createEnviaCertificadoDepartamentoForm($flujotipo, $tarea, $tramite, $tramite->getInstitucioneducativa()->getId());
        return $this->render('SieProcesosBundle:TramiteCea:enviaCertificadoDepartamento.html.twig', array(
            'form' => $enviaCertificadoDepartamentoForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));
    }

    public function createEnviaCertificadoDepartamentoForm($flujotipo, $tarea, $tramite, $idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_entrega_certificado_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 5))
            //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function enviaCertificadoDepartamentoGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos = array();
        $datos['observacion'] = $form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion = "";
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        if ($mensaje['dato'] == true) {
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje['msg']);
        } else {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    /***
     * Formulario recepcion y entraga certificados Distrito
     */
    public function entregaCertificadoDistritoAction(Request $request)
    {

        $this->session = $request->getSession();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $entregaCertificadoDistritoForm = $this->createEntregaCertificadoDistritoForm($flujotipo, $tarea, $tramite, $tramite->getInstitucioneducativa()->getId());
        return $this->render('SieProcesosBundle:TramiteCea:entregaCertificadoDistrito.html.twig', array(
            'form' => $entregaCertificadoDistritoForm->createView(),
            'tramite' => $tramite,
            'datos' => $tareasDatos,
            'tarea' => $tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));
    }

    public function createEntregaCertificadoDistritoForm($flujotipo, $tarea, $tramite, $idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_entrega_certificado_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' => $tarea))
            ->add('flujotipo', 'hidden', array('data' => $flujotipo))
            ->add('tramite', 'hidden', array('data' => $tramite ? $tramite->getId() : $tramite))
            ->add('idrue', 'hidden', array('data' => $idrue))
            ->add('tramitetipo', 'hidden', array('data' => 5))
            //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion', 'textarea', array('label' => 'Observación:', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
            ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function entregaCertificadoDistritoGuardarGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos = array();
        $datos['observacion'] = $form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion = "";
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $varevaluacion, $idtramite, $datos, $lugar['idlugarlocalidad'], $lugar['idlugardistrito']);
        $request->getSession()
            ->getFlashBag()
            ->add($mensaje['tipo'], $mensaje['msg']);
        return $this->redirectToRoute('wf_tramite_index', array('tipo' => 2));
    }

    public function obtienelugar($idtramite)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $lugar = array();
        if ($tramite->getInstitucioneducativa()) {
            $lugar['idlugarlocalidad'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
            $lugar['idlugardistrito'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
        } else {
            $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                ->where('td.tramite=' . $tramite->getId())
                ->andWhere('wfd.esValido=true')
                ->andWhere('fp.orden=1')
                ->getQuery()
                ->getResult();
            $lugar['idlugarlocalidad'] = $wfdatos[0]->getLugarTipoLocalidadId();
            $lugar['idlugardistrito'] = $wfdatos[0]->getLugarTipoDistritoId();
        }
        return $lugar;
    }

    public function obtieneCodigoLe($le, $iddistrito, $id_usuario)
    {
        try {
            //dump($le);die;
            $em = $this->getDoctrine()->getManager();

            $sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idmunicipio2001']);
            $secCod = $sec->getCodigo();
            $proCod = $sec->getLugarTipo()->getCodigo();
            $depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();

            $dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($iddistrito);
            $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $dis->getId()));
            $query = $em->getConnection()->prepare('SELECT get_genera_codigo_le(:dep,:pro,:sec)');
            $query->bindValue(':dep', $depCod);
            $query->bindValue(':pro', $proCod);
            $query->bindValue(':sec', $secCod);
            $query->execute();
            $codigolocal = $query->fetchAll();
            // Registramos el local
            $entity = new JurisdiccionGeografica();
            $entity->setId($codigolocal[0]["get_genera_codigo_le"]);
            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idlocalidad2001']));
            $entity->setLugarTipoIdLocalidad2012($le['idcomunidad2012']);
            $entity->setLugarTipoIdDistrito($distrito->getId());
            $entity->setDistritoTipo($dis);
            $entity->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneById(0));
            $entity->setZona(mb_strtoupper($le['zona'], 'utf-8'));
            $entity->setDireccion(mb_strtoupper($le['direccion'], 'utf-8'));
            if (isset($le['latitud'])) {
                $entity->setCordx($le['latitud']);
                $entity->setCordy($le['longitud']);
            }
            $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(1));
            $entity->setUsuarioId($id_usuario);
            $entity->setFechaRegistro(new \DateTime('now'));
            $em->persist($entity);
            $em->flush();
            return $entity->getId();
        } catch (Exception $ex) {
            return false;
        }
    }

    public function buscarRueAction(Request $request)
    {
        //dump($request);die;

        $idlugarusuario = $this->session->get('roluserlugarid');
        //dump($idlugarusuario);die;
        $iddistrito = $request->get('iddistrito');
        $idsiefusion = $request->get('idsiefusion');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT ie.id,ie.institucioneducativa,nt.id as nivel_tipo_id,nt.nivel
                FROM SieAppWebBundle:Institucioneducativa ie
                JOIN SieAppWebBundle:JurisdiccionGeografica le WITH ie.leJuridicciongeografica = le.id
                JOIN SieAppWebBundle:InstitucioneducativaNivelAutorizado iena WITH iena.institucioneducativa = ie.id
                JOIN SieAppWebBundle:NivelTipo nt WITH nt.id = iena.nivelTipo
                WHERE ie.id = :id
                AND ie.estadoinstitucionTipo = 10
                AND ie.institucioneducativaAcreditacionTipo = 1
                AND ie.institucioneducativaTipo = 2
                AND ie.dependenciaTipo in (1,2)
                AND le.lugarTipoIdDistrito = :lugar_id')
            ->setParameter('id', $idsiefusion)
            ->setParameter('lugar_id', $iddistrito);
        $institucioneducativa = $query->getResult();
        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if ($institucioneducativa) {
            $iefusion = array('idsiefusion' => $idsiefusion, 'institucioneducativa' => $idsiefusion . '-' . $institucioneducativa[0]['institucioneducativa']);
            $response->setData(array('ie' => $iefusion));
        } else {
            $response->setData(array('msg' => 'El código SIE es incorrecto.'));
        }

        return $response;
    }

    public function buscarRueComparteAction(Request $request)
    {
        //dump($request);die;

        $idlugarusuario = $this->session->get('roluserlugarid');
        $sie = $request->get('sie');
        $iddistrito = $request->get('iddistrito');
        $le = $request->get('le');
        //dump($sie,$iddistrito,$le);die;
        $em = $this->getDoctrine()->getManager();

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
            ->select('ie.id,ie.institucioneducativa,ot.orgcurricula,d.dependencia,dt.distrito', 'le.id as codigole ')
            ->innerJoin('SieAppWebBundle:DependenciaTipo', 'd', 'with', 'd.id = ie.dependenciaTipo')
            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'le', 'with', 'le.id = ie.leJuridicciongeografica')
            ->innerJoin('SieAppWebBundle:DistritoTipo', 'dt', 'with', 'dt.id = le.distritoTipo')
            ->innerJoin('SieAppWebBundle:OrgcurricularTipo', 'ot', 'with', 'ot.id = ie.orgcurricularTipo')
            ->where("ie.id='" . $sie . "'")
            ->andWhere("ie.estadoinstitucionTipo=10")
            ->andWhere("ie.institucioneducativaAcreditacionTipo=1");

        if ($iddistrito) {
            $institucioneducativa = $institucioneducativa
                ->andWhere("le.distritoTipo=" . $iddistrito)
                ->andWhere("le.id<>" . $le);
        }

        $institucioneducativa = $institucioneducativa
            ->getQuery()
            ->getResult();

        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if ($institucioneducativa) {
            $ie = array(
                'id' => $sie,
                'institucioneducativa' => $institucioneducativa[0]['institucioneducativa'],
                'subsistema' => $institucioneducativa[0]['orgcurricula'],
                'dependencia' => $institucioneducativa[0]['dependencia'],
                'codigole' => $institucioneducativa[0]['codigole']
            );
            //dump($ie);die;
            $response->setData($ie);
        } else {
            $response->setData(array('msg' => 'El código SIE es incorrecto.'));
        }

        return $response;
    }

    public function validarNombreDistritoAction(Request $request)
    {
        //dump($request);die;

        $idlugarusuario = $this->session->get('roluserlugarid');
        //dump($idlugarusuario);die;
        $sie = $request->get('sie');
        $nuevo_nombre = $request->get('nuevo_nombre');
        $iddistrito = $request->get('iddistrito');
        $em = $this->getDoctrine()->getManager();
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
            ->select('ie.id,ie.institucioneducativa,dt.distrito')
            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'le', 'with', 'le.id = ie.leJuridicciongeografica')
            ->innerJoin('SieAppWebBundle:DistritoTipo', 'dt', 'with', 'dt.id = le.distritoTipo')
            ->where("ie.institucioneducativa='" . $nuevo_nombre . "'")
            ->andWhere("le.distritoTipo=" . $iddistrito)
            ->andWhere("ie.estadoinstitucionTipo=10")
            ->andWhere("ie.institucioneducativaAcreditacionTipo=1")
            ->getQuery()
            ->getResult();
        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if ($institucioneducativa) {
            $ie = array(
                'id' => $institucioneducativa[0]['id'],
                'institucioneducativa' => $institucioneducativa[0]['institucioneducativa'],
                'distrito' => $institucioneducativa[0]['distrito'],
                'msg' => 'El nuevo nombre del Centro de Educación Alternativa: <strong>' . $institucioneducativa[0]['institucioneducativa'] . '</strong> ya se encuentra registrada con el <strong>Código RUE: ' . $institucioneducativa[0]['id'] . '</strong> en el mismo Distrito Educativo: <strong>' . $institucioneducativa[0]['distrito'] . '</strong>.</br>Por lo que debe elegir otro nombre.'
            );
            //dump($ie);die;
            $response->setData($ie);
        } else {
            $response->setData(array('msg' => 'ok'));
        }

        return $response;
    }

    public function tramiteTarea($tarea_ant, $tarea_actual, $flujotipo, $usuario, $rol, $id_ie)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * id del lugar usuario
         */
        $query = $em->getConnection()->prepare('select lugar_tipo_id from usuario_rol where usuario_id=' . $usuario . ' and rol_tipo_id=' . $rol);
        $query->execute();
        $lugarTipo = $query->fetchAll();

        /**
         * tareas devueltas por condicion
         */
        $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
            ->select('fp.id,wf.condicion')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
            ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
            ->getQuery()
            ->getResult();

        //dump($wftareac);die;
        /**
         * tareas devueltas
         */
        $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
            ->select('fp.id')
            ->where('fp.tareaSigId =' . $tarea_actual)
            ->getQuery()
            ->getResult();
        /**
         * tarea anterior
         */
        $tarea = 'td.flujo_proceso_id=' . $tarea_ant;

        if ($wftareac and $fp) {
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=" . $wftareac[0]['id'] . " and td.valor_evaluacion='" . $wftareac[0]['condicion'] . "') or td.flujo_proceso_id=" . $fp[0]['id'] . ")";
        } elseif ($wftareac) {
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=" . $wftareac[0]['id'] . " and td.valor_evaluacion='" . $wftareac[0]['condicion'] . "'))";
        } elseif ($fp) {
            $tarea = "(" . $tarea . " or td.flujo_proceso_id=" . $fp[0]['id'] . ")";
        }
        /**
         * si tiene condicion la tarea anterior
         */
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();

        if ($rol == 9) { //DIRECTOR
            $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and ie.id=" . $id_ie);
        } elseif ($rol == 10) { //DISTRITO
            if ($evaluacion) {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=" . $lugarTipo[0]['lugar_tipo_id'] . ") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=" . $lugarTipo[0]['lugar_tipo_id'] . ")) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=" . $tarea_ant . " and condicion_tarea_siguiente=" . $tarea_actual . ")");
            } else {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=" . $lugarTipo[0]['lugar_tipo_id'] . ") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=" . $lugarTipo[0]['lugar_tipo_id'] . "))");
            }
        } elseif ($rol == 7) { //DEPARTAMENTO
            if ($evaluacion) {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join distrito_tipo dt on le.distrito_tipo_id=dt.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=" . $lugarTipo[0]['lugar_tipo_id'] . ") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=" . $lugarTipo[0]['lugar_tipo_id'] . "))) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=" . $tarea_ant . " and condicion_tarea_siguiente=" . $tarea_actual . ")");
            } else {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join distrito_tipo dt on le.distrito_tipo_id=dt.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=" . $lugarTipo[0]['lugar_tipo_id'] . ") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=" . $lugarTipo[0]['lugar_tipo_id'] . ")))");
            }
        } elseif ($rol == 8) { //NACIONAL
            if ($evaluacion) {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=" . $tarea_ant . " and condicion_tarea_siguiente=" . $tarea_actual . ")");
            } else {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea);
            }
        }
        $query->execute();
        $tramites = $query->fetchAll();
        //dump($tramites);die;
        $data['tramites'] = $tramites;
        return $data;
    }

    public function obtieneDatos($tramite)
    {
        $em = $this->getDoctrine()->getManager();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite=' . $tramite->getId())
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $tareasDatos = array();
        foreach ($wfdatos as $wfd) {
            $datos = json_decode($wfd->getdatos(), true);
            $tareasDatos[] = array('flujoProceso' => $wfd->getTramiteDetalle()->getFlujoProceso(), 'datos' => $datos);
        }
        //dump($tareasDatos);die;
        return $tareasDatos;
    }

    public function buscaredificioAction(Request $request)
    {
        $idLe = $request->get('idLe');
        $iddistrito = $request->get('iddistrito');

        $em = $this->getDoctrine()->getManager();
        //dump($edificio);die;
        $lugarArray = array();
        $response = new JsonResponse();
        if ($iddistrito) {
            $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneBy(array('id' => $idLe, 'distritoTipo' => $iddistrito));
        } else {
            $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($idLe);
            /* $departamento2012 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 8, 'paisTipoId' =>1));
            $departamento2001 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' =>1));

            foreach($departamento2012 as $d){
                $dep[$d->getid()] = $d->getlugar();
            }
            $lugarArray['c2012']['dep']['lista']=$dep;
            foreach($departamento2001 as $d){
                $dep1[$d->getid()] = $d->getlugar();
            }
            $lugarArray['c2001']['dep']['lista']=$dep1; */
        }

        if ($edificio) {
            $lugarArray['zona'] = $edificio->getZona();
            $lugarArray['direccion'] = $edificio->getDireccion();
            $lugarArray['distrito']['id'] = $edificio->getDistritoTipo()->getId();
            $lugarArray['c2001']['loc']['id'] = $edificio->getLugarTipoLocalidad()->getId();
            $lugarArray['c2001']['can']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId();
            $lugarArray['c2001']['mun']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
            $lugarArray['c2001']['prov']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $lugarArray['c2001']['dep']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();

            //$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idDepartamento);
            $query = $em->createQuery(
                'SELECT dt
                FROM SieAppWebBundle:DistritoTipo dt
                WHERE dt.id NOT IN (:ids)
                AND dt.departamentoTipo = :dpto
                ORDER BY dt.id'
            )
                ->setParameter('ids', array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000))
                ->setParameter('dpto', $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getCodigo());
            $distrito = $query->getResult();
            foreach ($distrito as $d) {
                $lugarArray['distrito']['lista'][$d->getId()] = $d->getDistrito();
            }

            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
            foreach ($provincia as $p) {
                $lugarArray['c2001']['prov']['lista'][$p->getid()] = $p->getlugar();
            }

            $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
            foreach ($municipio as $m) {
                $lugarArray['c2001']['mun']['lista'][$m->getid()] = $m->getlugar();
            }

            $canton = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId()));
            foreach ($canton as $c) {
                $lugarArray['c2001']['can']['lista'][$c->getid()] = $c->getlugar();
            }

            $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId()));
            foreach ($localidad as $l) {
                $lugarArray['c2001']['loc']['lista'][$l->getid()] = $l->getlugar();
            }

            if ($edificio->getLugarTipoIdLocalidad2012()) {

                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $edificio->getLugarTipoIdLocalidad2012(), 'lugarNivel' => 11));
                $lugarArray['c2012']['comu']['id'] = $comunidad->getId();
                $lugarArray['c2012']['mun']['id'] = $comunidad->getLugarTipo()->getId();
                $lugarArray['c2012']['prov']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getId();
                $lugarArray['c2012']['dep']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();

                $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
                foreach ($provincia as $p) {
                    $lugarArray['c2012']['prov']['lista'][$p->getid()] = $p->getlugar();
                }

                $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getId()));
                foreach ($municipio as $m) {
                    $lugarArray['c2012']['mun']['lista'][$m->getid()] = $m->getlugar();
                }

                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 11, 'lugarTipo' => $comunidad->getLugarTipo()->getId()));
                foreach ($comunidad as $c) {
                    $lugarArray['c2012']['comu']['lista'][$c->getid()] = $c->getlugar();
                }
            }
            return $response->setData(array(
                'lugar' => $lugarArray,
            ));
        } else {
            //dump($dep);die;
            $mensaje = "¡Código de Edificio Educativo incorrecto!";
            //dump($mensaje);die;
            return $response->setData(array(
                'msg' => $mensaje,
                'lugar' => $lugarArray,
            ));
        }
    }

    public function provinciasAction($idDepartamento, $censo)
    {
        //dump($idDepartamento);die;
        $em = $this->getDoctrine()->getManager();
        if ($censo == 2001) {
            $nivel = 2;
        } else {
            $nivel = 9;
        }

        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idDepartamento));
        $provincia = array();
        foreach ($prov as $p) {
            $provincia[$p->getid()] = $p->getlugar();
            /* if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                $provincia[$p->getid()] = $p->getlugar();
            } */
        }

        /* *
         * distitos
         */
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idDepartamento);
        $query = $em->createQuery(
            'SELECT dt
               FROM SieAppWebBundle:DistritoTipo dt
              WHERE dt.id NOT IN (:ids)
                AND dt.departamentoTipo = :dpto
           ORDER BY dt.id'
        )
            ->setParameter('ids', array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000))
            ->setParameter('dpto', (int)$dep->getcodigo());
        $distrito = $query->getResult();
        $distritoArray = array();
        foreach ($distrito as $c) {
            $distritoArray[$c->getId()] = $c->getDistrito();
        }

        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia, 'distrito' => $distritoArray));
    }

    public function municipiosAction($idProvincia, $censo)
    {
        $em = $this->getDoctrine()->getManager();
        if ($censo == 2001) {
            $nivel = 3;
        } else {
            $nivel = 10;
        }
        $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idProvincia));
        $municipio = array();
        foreach ($mun as $m) {
            $municipio[$m->getid()] = $m->getlugar();
            /* if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            } */
        }
        $response = new JsonResponse();
        return $response->setData(array('municipio' => $municipio));
    }

    public function comunidadAction($idMunicipio, $censo)
    {
        //dump($idMunicipio,$censo,'entra');die;
        $em = $this->getDoctrine()->getManager();
        if ($censo == 2012) {
            $nivel = 11;
        }
        $com = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
        $canton = array();
        foreach ($com as $c) {
            $comunidad[$c->getid()] = $c->getlugar();
            /* if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $comunidad[$c->getid()] = $c->getlugar();
            } */
        }
        $response = new JsonResponse();
        return $response->setData(array('comunidad' => $comunidad));
    }

    public function cantonesAction($idMunicipio, $censo)
    {
        //dump($idMunicipio,$censo);die;
        $em = $this->getDoctrine()->getManager();
        if ($censo == 2001) {
            $nivel = 4;
        }
        $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
        $canton = array();
        foreach ($can as $c) {
            $canton[$c->getid()] = $c->getlugar();
            /* if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            } */
        }
        $response = new JsonResponse();
        return $response->setData(array('canton' => $canton));
    }

    public function localidadesAction($idCanton, $censo)
    {
        $em = $this->getDoctrine()->getManager();
        if ($censo == 2001) {
            $nivel = 5;
        }
        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idCanton));
        $localidad = array();
        foreach ($loc as $l) {
            $localidad[$l->getid()] = $l->getlugar();
            /* if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $localidad[$l->getid()] = $l->getlugar();
            } */
        }
        $response = new JsonResponse();
        return $response->setData(array('localidad' => $localidad));
    }

    public function tramiteTareaRitt($tarea_ant, $tarea_actual, $flujotipo, $usuario, $rol)
    {
        $em = $this->getDoctrine()->getManager();
        //dump($lugarTipo);die;
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $usuario, 'rolTipo' => $rol));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getCodigo();
        //dump($usuariorol);die;
        //dump((int)$idlugarusuario);die;
        /**tareas devuelta por condicion**/
        $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
            ->select('fp.id,wf.condicion')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
            ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
            ->getQuery()
            ->getResult();
        /**tarea devuelta**/
        $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
            ->select('fp.id')
            ->where('fp.tareaSigId =' . $tarea_actual)
            ->getQuery()
            ->getResult();
        /**tarea anterior**/
        $tarea = 'td.flujo_proceso_id=' . $tarea_ant;
        if ($wftareac and $fp) {
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=" . $wftareac[0]['id'] . " and td.valor_evaluacion='" . $wftareac[0]['condicion'] . "') or td.flujo_proceso_id=" . $fp[0]['id'] . ")";
        } elseif ($wftareac) {
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=" . $wftareac[0]['id'] . " and td.valor_evaluacion='" . $wftareac[0]['condicion'] . "'))";
        } elseif ($fp) {
            $tarea = "(" . $tarea . " or td.flujo_proceso_id=" . $fp[0]['id'] . ")";
        }
        //dump($wftareac);die;
        /**si la tarea anterior tiene evaluacion **/
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();
        if ($rol == 7) { // departamental
            if ($evaluacion) {

                $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
                from
                (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
                from ttec_institucioneducativa_sede se
                join institucioneducativa ie on se.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join tramite t on ie.id=t.institucioneducativa_id
                where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='" . (int)$idlugarusuario . "' and lugar_nivel_id=1))))))ie
                left join
                (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t
                join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=" . $tarea_ant . " and condicion_tarea_siguiente=" . $tarea_actual . "))t on ie.institucioneducativa_id=t.institucioneducativa_id");
            } else {
                $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
                from
                (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
                from ttec_institucioneducativa_sede se
                join institucioneducativa ie on se.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join tramite t on ie.id=t.institucioneducativa_id
                where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='" . (int)$idlugarusuario . "' and lugar_nivel_id=1))))))ie
                left join
                (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t
                join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . ")t on ie.institucioneducativa_id=t.institucioneducativa_id");
            }
        } elseif ($rol == 8) {
            if ($evaluacion) {

                $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
                left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea . " and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=" . $tarea_ant . " and condicion_tarea_siguiente=" . $tarea_actual . ")");
            } else {
                $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = " . $tarea_ant . " then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
                left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=" . $flujotipo . " and t.fecha_fin is null and " . $tarea);
            }
        }
        $query->execute();
        $tramites = $query->fetchAll();
        //dump($tramites);die;
        $data['tramites'] = $tramites;
        return $data;
    }

    public function guardarTramiteDetalle($usuario, $uDestinatario, $rol, $flujotipo, $tarea, $tabla, $id_tabla, $observacion, $tipotramite, $varevaluacion, $idtramite, $datos, $lugarTipo_id)
    {

        //dump($datos);die;
        $tramiteDetalle = new TramiteDetalle();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);

        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(1);

        //insert tramite
        if ($flujoproceso->getOrden() == 1 and $idtramite == "") {

            $tramite = new Tramite();
            $wfSolicitudTramite = new WfSolicitudTramite();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
            $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
            $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
            //dump($tramitetipo);die;
            $tramite->setFlujoTipo($flujotipo);
            $tramite->setTramiteTipo($tramitetipo);
            $tramite->setFechaTramite(new \DateTime(date('Y-m-d')));
            $tramite->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramite->setEsactivo(true);
            $tramite->setGestionId((new \DateTime())->format('Y'));

            switch ($tabla) {
                case 'institucioneducativa':
                    if ($id_tabla) {
                        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                        $tramite->setInstitucioneducativa($institucioneducativa);
                    }
                    break;
                case 'estudiante_inscripcion':
                    $estudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id_tabla);
                    $tramite->setestudianteInscripcion($estudiante);
                    break;
                case 'apoderado_inscripcion':
                    $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($id_tabla);
                    $tramite->setApoderadoInscripcion($apoderado);
                    break;
                case 'maestro_inscripcion':
                    $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id_tabla);
                    $tramite->setMaestroInscripcion($maestro);
                    break;
            }
            $em->persist($tramite);
            $em->flush();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();
            //dump($tramite);die;
            if ($datos) {
                //datos propios de la solicitud
                $wfSolicitudTramite->setTramite($tramite);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoId($lugarTipo_id);
                $em->persist($wfSolicitudTramite);
                $em->flush();
            }
        } else {
            /*$query = $em->getConnection()->prepare('select * from tramite_detalle where flujo_proceso_id='. $flujoproceso->getTareaAntId());
            $query->execute();
            $tramiteD = $query->fetchAll();*/
            //dump($idtramite);die;
            //Modificacion de datos propios de la solicitud
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        }
        //insert tramite_detalle
        //dump($tramiteD);die;
        $tramiteDetalle->setObs($observacion);
        $tramiteDetalle->setTramite($tramite);
        $tramiteDetalle->setTramiteEstado($tramiteestado);
        $tramiteDetalle->setFlujoProceso($flujoproceso);
        $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setUsuarioRemitente($usuario);
        /** */
        if ($idtramite != "") {
            $td_anterior = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tramiteDetalle->setTramiteDetalle($td_anterior);
        }
        //dump($flujoproceso);die;
        if ($flujoproceso->getEsEvaluacion() == true) {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
        }
        if ($flujoproceso->getWfAsignacionTareaTipo()->getId() == 3) //asignacion por seleccion
        {
            if ($idtramite != "") {
                $query = $em->getConnection()->prepare('select * from tramite_detalle where id=' . (int)$tramite->getTramite() . ' and tramite_id=' . $idtramite);
                $query->execute();
                $td = $query->fetchAll();
                $tramiteD = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['id']);
                $tramiteD->setUsuarioDestinatario($usuario);
                //$em->persist($tramiteD);
                $em->flush();
            }
        } else { //si es directa o randomica
            //dump($uDestinatario);die;
            $uDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->find($uDestinatario);
            //dump($uDestinatario);die;
            $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
        }
        $em->persist($tramiteDetalle);
        $em->flush();
        if ($flujoproceso->getTareaSigId() == null) {
            $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
        }
        $tramite->setTramite($tramiteDetalle->getId());
        //$em->persist($tramite);
        $em->flush();
        //dump((new \DateTime())->format('Y'));die;
        //guardar datos del propios del tramite
        $mensaje = 'El trámite se guardo correctamente';
        return $mensaje;
    }

    public function buscarSolicitudAction(Request $request)
    {
        //dump($request);die;
        $idlugarusuario = $this->session->get('roluserlugarid');
        $codigo = $request->get('codigo');
        $em = $this->getDoctrine()->getManager();
        $tramite_tipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($request->get('tramite_tipo'));
        $tramite = $request->get('tramite');

        $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo' => $codigo, 'estado' => false));
        //dump(json_decode($solicitudTramite->getDatos(),true),$tramite_tipo);die;
        $datos = "";
        if ($solicitudTramite) {
            $datos = json_decode($solicitudTramite->getDatos(), true);
            //dump($datos['tramites'][0]['id'],$tramite_tipo->getId());die;
            if ($datos['tramites'][0]['id'] == $tramite_tipo->getId()) {
                $data = array(
                    'datos' => $datos,
                    'tramite_tipo' => $tramite_tipo,
                    'codigo' => $codigo,
                    'gestion' => $solicitudTramite->getFechaRegistro()->format('Y'),
                );
            } else {
                $data = array(
                    'datos' => null,
                    'tramite_tipo' => $tramite_tipo,
                    'codigo' => $codigo,
                );
            }
        } else {
            $data = array(
                'datos' => null,
                'tramite_tipo' => $tramite_tipo,
                'codigo' => $codigo,
            );

            if (isset($tramite)) {
                $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->findBy(array('tramite' => $tramite), array('id' => 'DESC'));
                $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo' => $codigo, 'estado' => true));
                if (count($tramiteDetalle) > 0 && count($solicitudTramite)) {
                    if ($tramiteDetalle[0]->getFlujoProceso()->getId() == 51) {
                        $datos = json_decode($solicitudTramite->getDatos(), true);
                        //dump($datos['tramites'][0]['id'],$tramite_tipo->getId());die;
                        if ($datos['tramites'][0]['id'] == $tramite_tipo->getId()) {
                            $data = array(
                                'datos' => $datos,
                                'tramite_tipo' => $tramite_tipo,
                                'codigo' => $codigo,
                                'gestion' => $solicitudTramite->getFechaRegistro()->format('Y'),
                            );
                        }
                    }
                }
            }
        }
        // dump();die;        
        return $this->render('SieProcesosBundle:TramiteCea:solicitudAperturaReapertura.html.twig', $data);
    }

    public function aperturaGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        $form = $request->get('form');
        $files = $request->files->get('form');

        //dump($form,$files);die;
        $tramites = array(59);
        //dump($tramites,$form);die;
        $em = $this->getDoctrine()->getManager();

        /**
         * datos propios de la solicitud del formulario rue
         */
        $tramites = $em->getRepository('SieAppWebBundle:TramiteTipo')->createQueryBuilder('tt')
            ->select('tt.id,tt.tramiteTipo as tramite_tipo')
            ->where('tt.id in (:id)')
            ->setParameter('id', $tramites)
            ->getQuery()
            ->getResult();
        $em->getConnection()->beginTransaction();
        try {
            $solicitudTramite = new Solicitudtramite();
            //$solicitudTramite->setDatos($datos);
            $solicitudTramite->setFechaRegistro(new \DateTime('now'));
            $solicitudTramite->setEstado(false);
            $em->persist($solicitudTramite);
            $em->flush();
            $codigo = date('Ymd') . str_pad($solicitudTramite->getId(), 4, "0", STR_PAD_LEFT);
            $datos = $this->obtieneDatosApertura($tramites[0]['tramite_tipo'], $form, $files, $codigo);
            $datos['tramites'] = $tramites;
            //$datos['area'] = $form['area'];
            $solicitudTramite->setDatos(json_encode($datos));
            $solicitudTramite->setCodigo($codigo);
            $em->flush();
            $em->getConnection()->commit();

            $idsolicitud = $solicitudTramite->getId();
            //dump($idsolicitud);
            $query1 = $em->getConnection()->prepare("select 
            'CODIGO_SOLICITUD:_'||cast(codigo as varchar)||'__'||
            'UNIDAD_EDUCATIVA:_'||replace(wf.datos::json->'Apertura de Centro de Educacion Alternativa'->>'institucioneducativa',' ','_')||'__'||
            'DISTRITO_EDUCATIVO:_'||replace(wf.datos::json->'Apertura de Centro de Educacion Alternativa'->>'distrito',' ','_')||'__'||
            'DEPENDENCIA:_'||cast(wf.datos::json->'Apertura de Centro de Educacion Alternativa'->'dependencia'->>'dependencia' as varchar) as qr
            from solicitud_tramite wf
            where id=" . $idsolicitud);

            $query1->execute();
            $qr = $query1->fetchAll();
            //dump($qr);die;
            $lk = $qr[0]['qr'];
            //dump($idtramite,$id_td);die;
            $file = 'rcea_iniciosolicitudAperturaEpja_v1_far.rptdesign';
            if (isset($form['niveltipop'])) {
                $file = 'rcea_iniciosolicitudAperturaEduper_v1_far.rptdesign';
            }
            $arch = 'FORMULARIO_' . $idsolicitud . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idsolicitud=' . $idsolicitud . '&lk=' . $lk . '&&__format=pdf&'));
            //dump($this->container->getParameter('urlreportweb') . $file . '&idsolicitud='.$idsolicitud.'&lk='. $lk .'&&__format=pdf&');die;
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $request->getSession()
                ->getFlashBag()
                ->add('error', 'error');
            return $this->redirectToRoute('tramite_cea_apertura', array('id' => 59, 'desglose' => 0));
        }
    }

    public function obtieneDatosApertura($tramitetipo, $form, $files, $codigo)
    {
        $em = $this->getDoctrine()->getManager();
        $gestion = date('Y');
        $ruta = '/../web/uploads/archivos/flujos/rue/solicitud/' . $gestion . '/' . $codigo . '/';
        $datos = array();
        $datos[$tramitetipo]['institucioneducativa'] = trim(mb_strtoupper($form['institucioneducativa'], 'utf-8'));
        $datos[$tramitetipo]['fechafundacion'] = $form['fechafundacion'];
        $datos[$tramitetipo]['telefono'] = $form['telefono'];
        $datos[$tramitetipo]['director'] = trim(mb_strtoupper($form['director'], 'utf-8'));
        if ($form['siecomparte']) {
            $datos[$tramitetipo]['siecomparte'] = $form['siecomparte'];
            $scobj = new \stdClass;
            $scobj->id = $form['siecomparte'];
            $scobj->nombre = $form['siecompartedatanombre'];
            $scobj->subsistema = $form['siecompartedatasubsistema'];
            $scobj->dependencia = $form['siecompartedatadependencia'];
            $datos[$tramitetipo]['siecompartedata'] = $scobj;
        }
        if ($form['lejurisdiccion']) {
            $datos[$tramitetipo]['lejurisdiccion'] = $form['lejurisdiccion'];
        }
        $datos[$tramitetipo]['zona'] = trim(mb_strtoupper($form['zona'], 'utf-8'));
        $datos[$tramitetipo]['direccion'] = trim(mb_strtoupper($form['direccion'], 'utf-8'));
        $datos[$tramitetipo]['iddistrito'] = $form['distrito'];
        $datos[$tramitetipo]['distrito'] = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['distrito'])->getDistrito();
        $datos[$tramitetipo]['iddepartamento2001'] = $form['departamento2001'];
        $datos[$tramitetipo]['departamento2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2001'])->getLugar();
        $datos[$tramitetipo]['idprovincia2001'] = $form['provincia2001'];
        $datos[$tramitetipo]['provincia2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2001'])->getLugar();
        $datos[$tramitetipo]['idmunicipio2001'] = $form['municipio2001'];
        $datos[$tramitetipo]['municipio2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2001'])->getLugar();
        $datos[$tramitetipo]['idcanton2001'] = $form['canton2001'];
        $datos[$tramitetipo]['canton2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton2001'])->getLugar();
        $datos[$tramitetipo]['idlocalidad2001'] = $form['localidad2001'];
        $datos[$tramitetipo]['localidad2001'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad2001'])->getLugar();
        $datos[$tramitetipo]['iddepartamento2012'] = $form['departamento2012'];
        $datos[$tramitetipo]['departamento2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2012'])->getLugar();
        $datos[$tramitetipo]['idprovincia2012'] = $form['provincia2012'];
        $datos[$tramitetipo]['provincia2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2012'])->getLugar();
        $datos[$tramitetipo]['idmunicipio2012'] = $form['municipio2012'];
        $datos[$tramitetipo]['municipio2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2012'])->getLugar();
        $datos[$tramitetipo]['idcomunidad2012'] = $form['comunidad2012'];
        $datos[$tramitetipo]['comunidad2012'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad2012'])->getLugar();
        $datos[$tramitetipo]['latitud'] = $form['latitud'];
        $datos[$tramitetipo]['longitud'] = $form['longitud'];
        $datos[$tramitetipo]['dependencia'] = array('id' => $form['dependencia'], 'dependencia' => $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($form['dependencia'])->getDependencia());
        if ($form['dependencia'] == 2) {
            $datos[$tramitetipo]['conveniotipo'] = array('id' => $form['conveniotipo'], 'convenio' => $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo'])->getConvenio());
        }
        // dump(isset($form['niveltipoh']), isset($form['niveltipot']),isset($form['niveltipop']));die;
        if (isset($form['niveltipoh']) && isset($form['niveltipot'])) {
            $arr_in = array_merge($form['niveltipoh'], $form['niveltipot']);
            $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                ->select('nt.id,nt.nivel')
                ->where('nt.id in (:id)')
                ->orderBy('nt.id')
                ->setParameter('id', $arr_in)
                ->getQuery()
                ->getResult();
            $datos[$tramitetipo]['niveltipo'] = $nivel;
        } else if (isset($form['niveltipop'])) {
            if (isset($form['niveltipopt'])) {
                $arr_in = array_merge($form['niveltipop'], $form['niveltipopt']);
            } else {
                $arr_in = $form['niveltipop'];
            }
            $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                ->select('nt.id,nt.nivel')
                ->where('nt.id in (:id)')
                ->orderBy('nt.id')
                ->setParameter('id', $arr_in)
                ->getQuery()
                ->getResult();
            $datos[$tramitetipo]['niveltipo'] = $nivel;
        } else if (isset($form['niveltipoh'])) {
            $arr_in = $form['niveltipoh'];
            $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                ->select('nt.id,nt.nivel')
                ->where('nt.id in (:id)')
                ->orderBy('nt.id')
                ->setParameter('id', $arr_in)
                ->getQuery()
                ->getResult();
            $datos[$tramitetipo]['niveltipo'] = $nivel;
        } else if (isset($form['niveltipot'])) {
            $arr_in = $form['niveltipot'];
            $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                ->select('nt.id,nt.nivel')
                ->where('nt.id in (:id)')
                ->orderBy('nt.id')
                ->setParameter('id', $arr_in)
                ->getQuery()
                ->getResult();
            $datos[$tramitetipo]['niveltipo'] = $nivel;
        }

        if (isset($form['niveltipoh'])) {
            if (in_array(201, $form['niveltipoh'])) {
                $datos[$tramitetipo]['cantidad_11_1_1'] = $form['cantidad_11_1_1'];
                $datos[$tramitetipo]['cantidad_11_1_2'] = $form['cantidad_11_1_2'];
            }
            if (in_array(202, $form['niveltipoh'])) {
                $datos[$tramitetipo]['cantidad_11_2_1'] = $form['cantidad_11_2_1'];
                $datos[$tramitetipo]['cantidad_11_2_2'] = $form['cantidad_11_2_2'];
                $datos[$tramitetipo]['cantidad_11_2_3'] = $form['cantidad_11_2_3'];
            }
        }
        if (isset($form['niveltipot'])) {
            if (in_array(203, $form['niveltipot'])) {
                $datos[$tramitetipo]['cantidad_12_1'] = $form['cantidad_12_1'];
            }
            if (in_array(204, $form['niveltipot'])) {
                $datos[$tramitetipo]['cantidad_12_2'] = $form['cantidad_12_2'];
            }
            if (in_array(205, $form['niveltipot'])) {
                $datos[$tramitetipo]['cantidad_12_3'] = $form['cantidad_12_3'];
            }
        }
        if (isset($form['niveltipop'])) {
            if (in_array(222, $form['niveltipop'])) {
                $datos[$tramitetipo]['cantidad_13_1'] = $form['cantidad_13_1'];
            }
            if (in_array(223, $form['niveltipop'])) {
                $datos[$tramitetipo]['cantidad_13_2'] = $form['cantidad_13_2'];
            }
            if (in_array(225, $form['niveltipop'])) {
                $datos[$tramitetipo]['cantidad_13_3'] = $form['cantidad_13_3'];
            }
            if (in_array(226, $form['niveltipop'])) {
                $datos[$tramitetipo]['cantidad_13_4'] = $form['cantidad_13_4'];
            }
        }
        if (isset($form['niveltipopt'])) {
            if (in_array(219, $form['niveltipopt'])) {
                $datos[$tramitetipo]['cantidad_13_1_1'] = $form['cantidad_13_1_1'];
                $datos[$tramitetipo]['cantidad_13_2_1'] = $form['cantidad_13_2_1'];
                $datos[$tramitetipo]['cantidad_13_3_1'] = $form['cantidad_13_3_1'];
                $datos[$tramitetipo]['cantidad_13_4_1'] = $form['cantidad_13_4_1'];
            }
            if (in_array(220, $form['niveltipopt'])) {
                $datos[$tramitetipo]['cantidad_13_1_2'] = $form['cantidad_13_1_2'];
                $datos[$tramitetipo]['cantidad_13_2_2'] = $form['cantidad_13_2_2'];
                $datos[$tramitetipo]['cantidad_13_3_2'] = $form['cantidad_13_3_2'];
                $datos[$tramitetipo]['cantidad_13_4_2'] = $form['cantidad_13_4_2'];
            }
            if (in_array(224, $form['niveltipopt'])) {
                $datos[$tramitetipo]['cantidad_13_1_3'] = $form['cantidad_13_1_3'];
                $datos[$tramitetipo]['cantidad_13_2_3'] = $form['cantidad_13_2_3'];
                $datos[$tramitetipo]['cantidad_13_3_3'] = $form['cantidad_13_3_3'];
                $datos[$tramitetipo]['cantidad_13_4_3'] = $form['cantidad_13_4_3'];
            }
        }
        $datos[$tramitetipo]['i_area_apertura'] = $form['i_area_apertura'];
        $datos[$tramitetipo]['cantidad_adm'] = $form['cantidad_adm'];
        $datos[$tramitetipo]['cantidad_maestro'] = $form['cantidad_maestro'];

        $adjunto = $this->upload($files['i_solicitud_apertura'], $ruta);
        if ($adjunto == '') {
            $error_upload = true;
        }
        $datos[$tramitetipo]['i_solicitud_apertura'] = $adjunto;
        $datos[$tramitetipo]['i_compromiso_apertura'] = $form['i_compromiso_apertura'];
        $adjunto = $this->upload($files['i_actafundacion_apertura'], $ruta);
        if ($adjunto == '') {
            $error_upload = true;
        }
        $datos[$tramitetipo]['i_actafundacion_apertura'] = $adjunto;
        $adjunto = $this->upload($files['i_certdefuncion'], $ruta);
        if ($adjunto == '') {
            $error_upload = true;
        }
        $datos[$tramitetipo]['i_certdefuncion'] = $adjunto;
        $datos[$tramitetipo]['i_folio_apertura'] = $form['i_folio_apertura'];
        if ($form['dependencia'] == 2) {
            $datos[$tramitetipo]['i_constitucion_apertura'] = $form['i_constitucion_apertura'];
            $datos[$tramitetipo]['i_convenio_apertura'] = $form['i_convenio_apertura'];
        }
        $datos[$tramitetipo]['ii_planos_apertura'] = $form['ii_planos_apertura'];
        $datos[$tramitetipo]['ii_edificio_escolar'] = $form['ii_edificio_escolar'];
        $datos[$tramitetipo]['ii_act_geografica'] = $form['ii_act_geografica'];

        $datos[$tramitetipo]['iii_curri_antecedentes'] = $form['iii_curri_antecedentes'];
        $datos[$tramitetipo]['iii_curri_objgeneral'] = $form['iii_curri_objgeneral'];
        $datos[$tramitetipo]['iii_curri_objespecificos'] = $form['iii_curri_objespecificos'];
        $datos[$tramitetipo]['iii_curri_diagnostico'] = $form['iii_curri_diagnostico'];
        $datos[$tramitetipo]['iii_curri_jussolicitud'] = $form['iii_curri_jussolicitud'];
        $datos[$tramitetipo]['iii_curri_jusinfraestructura'] = $form['iii_curri_jusinfraestructura'];
        $datos[$tramitetipo]['iii_curri_planes'] = $form['iii_curri_planes'];
        $datos[$tramitetipo]['iii_curri_proyeccion'] = $form['iii_curri_proyeccion'];
        $datos[$tramitetipo]['iii_curri_conclusiones'] = $form['iii_curri_conclusiones'];
        $datos[$tramitetipo]['iii_curri_proy'] = $form['iii_curri_proy'];
        $datos[$tramitetipo]['iii_curri_informe_distrital'] = $form['iii_curri_informe_distrital'];

        return $datos;
    }

    public function reaperturaAction(Request $request)
    {

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_cea_reapertura_guardar'))
            ->add('sie', 'text', array('label' => 'Código SIE:', 'required' => true, 'attr' => array('class' => 'form-control validar', 'data-placeholder' => "")))
            ->add('buscar', 'button', array('attr' => array('class' => 'btn btn-primary', 'onclick' => 'buscarSieReapertura()')))
            ->getForm();

        return $this->render('SieProcesosBundle:TramiteCea:reapertura.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function buscarSieReaperturaAction(Request $request)
    {
        $sie = $request->get('sie');
        $em = $this->getDoctrine()->getManager();

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
            ->select('ie')
            ->where('ie.id = :id')
            ->andWhere('ie.estadoinstitucionTipo = 19')
            ->andWhere('ie.institucioneducativaTipo = 2')
            ->andWhere("ie.nroResolucion <>'' ")
            ->andWhere("ie.obsRue not like '%definitiv%'")
            ->andWhere("ie.obsRue not like '%DEFINITIV%'")
            ->setParameter('id', $sie)
            ->getQuery()
            ->getResult();
        //dump($institucioneducativa);die;
        if ($institucioneducativa) {
            $lugar_tipo2012 = $institucioneducativa[0]->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012() ? $em->getRepository('SieAppWebBundle:LugarTipo')->find($institucioneducativa[0]->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()) : null;
            $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $sie));
            $tramite_tipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find(72);
            $form = $this->createFormBuilder()
                ->add('observacion', 'textarea', array('label' => 'Justificación:', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('guardar', 'submit', array('label' => 'Enviar Solicitud'))
                ->getForm();
            $data = array(
                'form' => $form->createView(),
                'institucioneducativa' => $institucioneducativa[0],
                'ieNivel' => $institucioneducativaNivel,
                'lugarTipo2012' => $lugar_tipo2012,
                'tramite_tipo' => $tramite_tipo,
            );

            return $this->render('SieProcesosBundle:TramiteCea:datosReapertura.html.twig', $data);
        } else {
            $response = new JsonResponse();
            $data = array(
                'msg' => 'Código SIE incorrecto.'
            );
            $response->setData($data);

            return $response;
        }
    }

    public function reaperturaGuardarAction(Request $request)
    {
        //variable de control para el cargado de adjunto
        $error_upload = false;
        $this->session = $request->getSession();
        $form = $request->get('form');
        $files = $request->files->get('form');
        //dump($form,$files);die;
        $em = $this->getDoctrine()->getManager();
        /**
         * datos propios de la solicitud del formulario rue
         */
        $query = $em->getConnection()->prepare('SELECT ie.id,ie.institucioneducativa,ie.area_municipio,ie.fecha_fundacion,ie.le_juridicciongeografica_id,ie.estadoinstitucion_tipo_id,et.estadoinstitucion,ie.dependencia_tipo_id,dt.dependencia,ie.convenio_tipo_id,ct.convenio
                FROM institucioneducativa ie
                join estadoinstitucion_tipo et on ie.estadoinstitucion_tipo_id=et.id
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                join convenio_tipo ct on ct.id=ie.convenio_tipo_id
                and ie.id=' . $form['sie']);
        $query->execute();
        $institucioneducativa = $query->fetchAll();

        $query = $em->getConnection()->prepare('SELECT nt.id,nt.nivel
                FROM institucioneducativa_nivel_autorizado ien
                join nivel_tipo nt on ien.nivel_tipo_id = nt.id
                WHERE ien.institucioneducativa_id=' . $form['sie']);
        $query->execute();
        $ieNivelAutorizado = $query->fetchAll();
        $query = $em->getConnection()->prepare('SELECT le.id,le.zona,le.direccion,le.distrito_tipo_id,dt.distrito,
                lt.id as localidad2001_id,lt.lugar as localidad2001,lt1.id as canton2001_id,lt1.lugar as canton2001,lt2.id as municipio2001_id,lt2.lugar as municipio2001,lt3.id as provincia2001_id,lt3.lugar as provincia2001,lt4.id as departamento2001_id,lt4.lugar as departamento2001,lt.area2001,
                lt5.id as comunidad2012_id,lt5.lugar as comunidad2012,lt6.id as municipio2012_id,lt6.lugar as municipio2012,lt7.id as provincia2012_id,lt7.lugar as provincia2012,lt8.id as departamento2012_id,lt8.lugar as departamento2012
                FROM jurisdiccion_geografica le
                join distrito_tipo dt on dt.id=le.distrito_tipo_id
                join lugar_tipo lt on lt.id=le.lugar_tipo_id_localidad
                join lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
                join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
                join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
                join lugar_tipo lt4 on lt4.id=lt3.lugar_tipo_id
                left join lugar_tipo lt5 on lt5.id=le.lugar_tipo_id_localidad2012
                left join lugar_tipo lt6 on lt6.id=lt5.lugar_tipo_id
                left join lugar_tipo lt7 on lt7.id=lt6.lugar_tipo_id
                left join lugar_tipo lt8 on lt8.id=lt7.lugar_tipo_id
                WHERE le.id=' . $institucioneducativa[0]['le_juridicciongeografica_id']);
        $query->execute();
        $le = $query->fetchAll();

        $datos = array();
        $datos['institucioneducativa'] = $institucioneducativa[0];
        $datos['jurisdiccion_geografica'] = $le[0];
        $datos['institucioneducativaNivel'] = $ieNivelAutorizado;
        $tramites = $em->getRepository('SieAppWebBundle:TramiteTipo')->createQueryBuilder('tt')
            ->select('tt.id,tt.tramiteTipo as tramite_tipo')
            ->where('tt.id in (:id)')
            ->setParameter('id', 72)
            ->getQuery()
            ->getResult();
        //dump($tramites);die;  
        $datos['tramites'] = $tramites;
        $datos['justificacion'] = trim(mb_strtoupper($form['observacion'], 'utf-8'));
        //dump($datos);die;
        $gestion = $this->session->get('currentyear');
        $ruta = '/../web/uploads/archivos/flujos/' . $form['sie'] . '/rue/' . $gestion . '/';

        $adjunto = $this->upload($files['i_solicitud_reapertura'], $ruta);
        if ($adjunto == '') {
            $error_upload = true;
        }
        $datos[$tramites[0]['tramite_tipo']]['i_solicitud_reapertura'] = $adjunto;

        $datos[$tramites[0]['tramite_tipo']]['estadoinstitucion_id'] = 10;
        $datos[$tramites[0]['tramite_tipo']]['estadoinstitucion'] = 'Abierta';
        //dump($datos);die;

        $em->getConnection()->beginTransaction();
        try {
            $solicitudTramite = new Solicitudtramite();
            //$solicitudTramite->setDatos($datos);
            $solicitudTramite->setFechaRegistro(new \DateTime('now'));
            $solicitudTramite->setEstado(false);
            $solicitudTramite->setDatos(json_encode($datos));
            $em->persist($solicitudTramite);
            $em->flush();
            $codigo = date('Ymd') . str_pad($solicitudTramite->getId(), 4, "0", STR_PAD_LEFT);
            $solicitudTramite->setCodigo($codigo);
            $em->flush();
            //dump($solicitudTramite);die;
            //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
            $idsolicitud = $solicitudTramite->getId();
            $em->getConnection()->commit();

            $query1 = $em->getConnection()->prepare("select 
                'CODIGO_SOLICITUD:_'||cast(codigo as varchar)||'__'||
                'CODIGO_RUE:_'||cast(wf.datos::json->'institucioneducativa'->>'id' as varchar)||'__'||
                'EDIFICIO_EDUCATIVO:_'||cast(wf.datos::json->'institucioneducativa'->>'le_juridicciongeografica_id' as varchar) as qr
                from solicitud_tramite wf
                where id=" . $idsolicitud);

            $query1->execute();
            $qr = $query1->fetchAll();
            //dump($qr);die;

            $query11 = $em->getConnection()->prepare("select * from institucioneducativa ie  
            inner join institucioneducativa_nivel_autorizado na on na.institucioneducativa_id=ie.id
            where na.nivel_tipo_id in (201,202,203,204,205)
            and ie.id=" . $form['sie']);
            $query11->execute();
            $epja = $query11->fetchAll();
            if ($epja) {
                $file = 'rcea_iniciosolicitudReaperturaEpja_v1_far.rptdesign';
            } else {
                $file = 'rcea_iniciosolicitudReaperturaEduper_v1_far.rptdesign';
            }

            $lk = $qr[0]['qr'];
            $arch = 'FORMULARIO_' . $idsolicitud . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idsolicitud=' . $idsolicitud . '&lk=' . $lk . '&&__format=pdf&'));
            //dump($this->container->getParameter('urlreportweb') . $file . '&idsolicitud='.$idsolicitud.'&lk='. $lk .'&&__format=pdf&');die;
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $request->getSession()
                ->getFlashBag()
                ->add('error', 'error ' . $ex);
            return $this->redirectToRoute('tramite_cea_reapertura');
        }
    }
}
