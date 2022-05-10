<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SedeController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        //$response = new JsonResponse();
        $estado = true;
        $msg = '';

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
            
        $em = $this->getDoctrine()->getManager();
        //$entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $id_usuario, 'univSedeTipo' => 1));
        //dump($entityUnivSede);die;

        $form = $request->get('form');
        $sedeId = base64_decode($form['sede']);

        $titulo = "Sedes";
        $subtitulo = "";
        
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:UnivSede');
        $query = $repository->createQueryBuilder('s')
                ->select('s.id, s.sede, coalesce(ss.id,0) as sucursal')
                ->leftJoin('SieAppWebBundle:UnivSedeSucursal', 'ss', 'WITH', 'ss.univSede = s.id')
                ->where('s.usuario = :usuarioId')
                ->setParameter('usuarioId', $id_usuario)
                ->orderBy('s.id', 'ASC')
                ->getQuery();
        $entityUnivSede =  $query->getResult();

        return $this->render('SieUniversityBundle:Sede:index.html.twig', array(
            'central' => $entityUnivSedeCentral,
            'sedes' => $entityUnivSede,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo
        ));
    }


    public function tuisionSede($sede, $usuarioId)
    {    
        $em = $this->getDoctrine()->getManager();
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $usuarioId, 'id' => $sede));
        if (count($entityUnivSede)>0){
            return true;
        } else {
            return false;
        }
    }

    public function getFormSedeSucursal($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity){
        $form = $this->createFormBuilder()
        ->add('ofertaMaestro', 'hidden', array('label' => 'Info', 'attr' => array('value' => base64_encode(json_encode($val)))))
        ->add('item', 'text', array('label' => 'Item', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $item, 'style' => 'text-transform:uppercase', 'placeholder' => 'Item' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación (ej.: 01-01-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación (ej.: 01-01-2020)' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaFin', 'text', array('label' => 'Fecha final de asignación (ej.: 31-12-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => '----Fecha fin de asignación (ej.: 31-12-2020)' , 'maxlength' => 10, 'required' => true)))
        ->add('financiamiento', 'entity', array('data' => $financiamientoTipoEntity, 'label' => 'Financiamiento', 'empty_value' => 'Seleccione Financiamiento', 'class' => 'Sie\AppWebBundle\Entity\FinanciamientoTipo',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ft')
                        ->orderBy('ft.id', 'ASC');
            },
        ))
        ->getForm()->createView();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que visualiza el formulario para guardar o modificar la asignacion del maestro
    // PARAMETROS: datos
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function updateAction(Request $request) {
        $sede = $request->get('info');
        $info = base64_encode(json_encode(array('sedeId'=>$sede)));

        dump($sede,$info);die;
        $info = json_decode(base64_decode($request->get('info')), true);
        $val = json_decode(base64_decode($request->get('val')), true);
        $sistema = $request->get('sistema');
        
        $maestroInscripcionId = 0;
        
        //aqui llegan los maestros ya asignados para ese area
        $listaMaestros = $this->getMaestrosInstitucionEducativaCursoOferta($val['institucioneducativa_curso_oferta_id'], $val['nota_tipo_id']);
        
        //dump($listaMaestros);die;
        // dump($listaMaestros);dump($val['institucioneducativa_curso_oferta_id'], $val['nota_tipo_id']);die;
        $detalleCursoOferta = $this->getInstitucionEducativaCursoOferta($val['institucioneducativa_curso_oferta_id']);
        if(count($detalleCursoOferta )>0){
            $detalleCursoOferta = $detalleCursoOferta[0];
        }
        $institucionEducativaId = $info["sie"];
        $gestionId = $info["gestion"];
        
        $em = $this->getDoctrine()->getManager();
        $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($val['nota_tipo_id']);

        $maestroInscripcionId = 0;
        $item = "";
        $fechaInicio = "";
        $fechaFin = "";
        $financiamientoTipoEntity = array();
        $maestroInscripcioEntity = array();
        $formEnable = true;

        if(isset($info["vp_id"])){
            $validacionProcesoId = $info["vp_id"];  
            $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
            if(count($validacionProcesoEntity)>0){
                if($validacionProcesoEntity->getValidacionReglaTipo()->getId() == 57){
                    $llave = explode("|",$validacionProcesoEntity->getLlave());
                    if (count($llave) == 2){
                        $maestroInscripcionId = $llave[0];
                    } 
                } else {
                    $maestroInscripcionId = $validacionProcesoEntity->getLlave();
                }
            }
            $maestroInscripcioEntity = $this->getMaestroInscripcion($maestroInscripcionId);
            if(count($maestroInscripcioEntity)>0){
                $maestroInscripcioEntity = $maestroInscripcioEntity[0];
                $maestroInscripcioEntity['institucioneducativaCursoOfertaId'] = $val['institucioneducativa_curso_oferta_id'];
            }
            $val['maestro_inscripcion_id'] = $maestroInscripcionId;

            $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $val['institucioneducativa_curso_oferta_id'], 'notaTipo' => $val['nota_tipo_id']));
            //dump($institucioneducativaCursoOfertaMaestroEntity);die;
            if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
                $item = $institucioneducativaCursoOfertaMaestroEntity->getItem();
                $fechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-Y');
                $fechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-Y');
                $financiamientoTipoEntity = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo();
            } 
            if(count($institucioneducativaCursoOfertaMaestroEntity)>0){
                $formEnable = false;
            } 
        }
     
        $notaTipo = $notaTipoEntity->getNotaTipo();
        
        if (isset($info["vp_id"])){
            $form = $this->getFormOfertaMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity);
        } else {
            $maestroInscripcionLista = $this->listaMaestroInscripcion($institucionEducativaId,$gestionId);
            $arrayMaestroInscripcionLista = array();
            $arrayMaestroInscripcionLista[base64_encode(0)] = 'SIN ASIGNACIÓN DOCENTE';
            foreach ($maestroInscripcionLista as $data) {
                if($data['complemento'] != ""){
                    $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad']."-".$data['complemento'].")";
                } else {
                    $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad'].")";
                }
            }
            $form = $this->getFormRegistroMaestro($val, $item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $arrayMaestroInscripcionLista, 0);
        }
        $arrayRangoFecha = array('inicio'=>"01-01-".$gestionId,'final'=>"31-12-".$gestionId);
        $arrayFormulario = array('titulo' => "Registro / Modificación de maestro", 'detalleCursoOferta' => $detalleCursoOferta, 'listaMaestros' => $listaMaestros, 'notaTipo'=>$notaTipo, 'casoMaestro'=>$maestroInscripcioEntity, 'formNuevo'=>$form, 'formEnable'=>$formEnable, 'rangoFecha'=>$arrayRangoFecha);
                
        if (isset($info["vp_id"])){
            return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaFormulario.html.twig', $arrayFormulario);
        } else {     
            if($sistema == 2) {
                //viene del academico
                return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMaestroFormulario.html.twig', $arrayFormulario);
            } else{
                //viene del siged
                return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMaestroFormularioSiged.html.twig', $arrayFormulario);
            }     
        }
    }

}
