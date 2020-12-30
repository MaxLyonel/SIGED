<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InstitucionalizacionDgesttla;

class InstitucionalizacionDgesttlaController extends Controller
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
    public function __construct() {
        $this->session = new Session();
    }

    /***
     * Formulario de registro
     */
    public function indexAction(Request $request) {
        $form = $this->createRegistroNuevoForm();
        $data = array(
            'form' => $form->createView(),
        );

        return $this->render('SieProcesosBundle:InstitucionalizacionDgesttla:index.html.twig', $data);        
    }

    public function createRegistroNuevoForm()
    {
        $form = $this->createFormBuilder();
        
        $form=$form
            ->setAction($this->generateUrl('institucionalizacion_new'))
            ->add('carnet','text',array('label'=>'Carnet de Identidad:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '15', 'placeholder' => 'INGRESAR EL NRO DE C.I.')))
            ->add('complemento','text',array('label'=>'Complemento:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '2', 'placeholder' => '(OPCIONAL)')))
            ->add('paterno','text',array('label'=>'Apellido Paterno:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL APELLIDO PATERNO')))
            ->add('materno','text',array('label'=>'Apellido Materno:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL APELLIDO MATERNO')))
            ->add('nombre','text',array('label'=>'Nombre(s):','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '180', 'placeholder' => 'INGRESAR EL/LOS NOMBRE/S')))
            ->add('apellidoEsposo','text',array('label'=>'Apellido de casada:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => '(OPCIONAL)')))
            ->add('nacionalidad','text',array('label'=>'Nacionalidad:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR LA NACIONALIDAD')))
            ->add('genero', 'choice', array('label' => 'Género:', 'required' => true, 'choices' => [1 => 'Masculino', 2 => 'Femenino', 3 => 'Otro']))
            ->add('fechaNacimiento', 'date', array('label' => 'Fecha de nacimiento:', 'widget' => 'single_text','format' => 'dd-mm-yyyy', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('direccion', 'text', array('label' => 'Dirección actual:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR LA DIRECCIÓN ACTUAL')))
            ->add('telefono', 'text', array('label' => 'Telefono fijo:','required'=>false,'attr' => array('class' => 'form-control', 'maxlength' => '10', 'placeholder' => 'INGRESAR EL TELÉFONO FIJO')))
            ->add('celular', 'text', array('label' => 'Telefono celular:','required'=>true,'attr' => array('class' => 'form-control', 'maxlength' => '10', 'placeholder' => 'INGRESAR EL TELÉFONO CELULAR')))
            ->add('correoElectronico', 'text', array('label' => 'Correo Electrónico:','required'=>true,'attr' => array('class' => 'form-control', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL CORREO ELECTRÓNICO')))
            ->add('licenciatura', 'text', array('label' => 'Licenciatura en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU LICENCIATURA (SI CORRESPONDE)')))
            ->add('tecnicoSuperior', 'text', array('label' => 'Técnico Superior en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU TÉCNICO SUPERIOR (SI CORRESPONDE)')))
            ->add('diplomado', 'text', array('label' => 'Diplomado en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU DIPLOMADO (SI CORRESPONDE)')))
            ->add('especialidad', 'text', array('label' => 'Especialidad en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU ESPECIALIDAD (SI CORRESPONDE)')))
            ->add('maestria', 'text', array('label' => 'Maestría en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU MAESTRÍA (SI CORRESPONDE)')))
            ->add('doctorado', 'text', array('label' => 'Doctorado en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU DOCTORADO (SI CORRESPONDE)')))
            ->add('departamento', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento',
                'mapped' => false,
                'required' => true,
                'property' => 'departamento',
                'label' => 'Departamento:',
                'empty_value' => 'Seleccionar...'
            ))
            ->add('instituto', 'choice', array('label' => 'Instituto', 'required' => true, 'choices' => [0 => 'Seleccionar...']))
            ->add('cargo', 'choice', array('label' => 'Cargo', 'required' => true, 'choices' => [0 => 'Seleccionar...']))
            ->add('nroDeposito','text',array('label'=>'Número de depósito bancario:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL NÚMERO DE DEPÓSITO BANCARIO')))
            ->add('guardar','submit',array('label'=>'Enviar formulario'))
            ->getForm();

        return $form;
    }


    public function registroNuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $carnet = $form['carnet'];
        $complemento = $form['complemento'];
        $paterno = $form['paterno'];
        $materno = $form['materno'];
        $nombre = $form['nombre'];
        $apellidoEsposo = $form['apellidoEsposo'];
        $genero = $form['genero'];
        $fechaNacimiento = $form['fechaNacimiento'];
        $nacionalidad = $form['nacionalidad'];
        $direccion = $form['direccion'];
        $telefono = $form['telefono'] ? intval($form['telefono']) : 0;
        $celular = $form['celular'];
        $correoElectronico = $form['correoElectronico'];
        $licenciatura = $form['licenciatura'];
        $tecnicoSuperior = $form['tecnicoSuperior'];
        $especialidad = $form['especialidad'];
        $diplomado = $form['diplomado'];
        $maestria = $form['maestria'];
        $doctorado = $form['doctorado'];
        $departamento = $form['departamento'];
        $instituto = $form['instituto'];
        $cargo = $form['cargo'];
        $nroDeposito = $form['nroDeposito'];

        $persona = array(
            'complemento'=>$complemento,
            'primer_apellido'=>$paterno,
            'segundo_apellido'=>$materno,
            'nombre'=>$nombre,
            'fecha_nacimiento'=>$fechaNacimiento
        );

        $registro = $em->getRepository('SieAppWebBundle:InstitucionalizacionDgesttla')->findBy(array('carnet' => $carnet, 'complemento' => $complemento, 'esOficial' => true));
        
        if(count($registro)>=2) {
            $this->get('session')->getFlashBag()->add('error', 'Se encontraron 2 registros previos para la persona. No se realizó el registro.');
            return $this->redirectToRoute('institucionalizacion_index');
        } else {
            $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnet, $persona, 'prod', 'academico');
        
            if($resultado) {
                $em->getConnection()->beginTransaction();
                try{
                    $institucionalizacion = new InstitucionalizacionDgesttla();
                    $institucionalizacion->setFechaRegistro(new \DateTime('now'));
                    $institucionalizacion->setCarnet(mb_strtoupper($carnet, 'UTF-8'));
                    $institucionalizacion->setComplemento(mb_strtoupper($complemento, 'UTF-8'));
                    $institucionalizacion->setPaterno(mb_strtoupper($paterno, 'UTF-8'));
                    $institucionalizacion->setMaterno(mb_strtoupper($materno, 'UTF-8'));
                    $institucionalizacion->setNombre(mb_strtoupper($nombre, 'UTF-8'));
                    $institucionalizacion->setApellidoEsposo(mb_strtoupper($apellidoEsposo, 'UTF-8'));
                    $institucionalizacion->setGenero($genero);
                    $institucionalizacion->setFechaNacimiento(new \DateTime($fechaNacimiento));
                    $institucionalizacion->setNacionalidad(mb_strtoupper($nacionalidad, 'UTF-8'));
                    $institucionalizacion->setDireccion(mb_strtoupper($direccion, 'UTF-8'));
                    $institucionalizacion->setTelefono($telefono);
                    $institucionalizacion->setCelular($celular);
                    $institucionalizacion->setCorreoElectronico($correoElectronico);
                    $institucionalizacion->setLicenciatura(mb_strtoupper($licenciatura, 'UTF-8'));
                    $institucionalizacion->setTecnicoSuperior(mb_strtoupper($tecnicoSuperior, 'UTF-8'));
                    $institucionalizacion->setEspecialidad(mb_strtoupper($especialidad, 'UTF-8'));
                    $institucionalizacion->setDiplomado(mb_strtoupper($diplomado, 'UTF-8'));
                    $institucionalizacion->setMaestria(mb_strtoupper($maestria, 'UTF-8'));
                    $institucionalizacion->setDoctorado(mb_strtoupper($doctorado, 'UTF-8'));
                    $institucionalizacion->setDepartamento($departamento);
                    $institucionalizacion->setInstituto($instituto);
                    $institucionalizacion->setCargo($cargo);
                    $institucionalizacion->setNroDeposito(mb_strtoupper($nroDeposito, 'UTF-8'));
                    $institucionalizacion->setEsOficial(true);
                    
                    $em->persist($institucionalizacion);
                    $em->flush();
                    $em->getConnection()->commit();   

                    $this->get('session')->getFlashBag()->add('success', 'Los datos fueron registrados correctamente.');
                    return $this->redirectToRoute('institucionalizacion_index');
                    
                }catch (\Exception $ex) {
                    $em->getConnection()->rollback();
                    $this->get('session')->getFlashBag()->add('error', 'No se realizó el registro.'.$ex);
                    return $this->redirectToRoute('institucionalizacion_index');
                }
            } else {
                $this->get('session')->getFlashBag()->add('noSegip', 'Los datos no fueron validados con SEGIP. No se realizó el registro.');
                return $this->redirectToRoute('institucionalizacion_index');
            }
        }
    }

    public function institutosAction($idDepartamento){
        $em = $this->getDoctrine()->getManager();

        $departamento = $em->getRepository('SieAppWebBundle:InstitucionalizacionDepartamento')->findOneById($idDepartamento);
        $institutos = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstituto')->findBy(array('departamento' => $departamento));

        $institutosArray = array();
        foreach($institutos as $itt) {
            $institutosArray[$itt->getId()] = $itt->getInstituto();
        }

        $response = new JsonResponse();
        return $response->setData(array('institutos' => $institutosArray));
    }

    public function cargosAction($idInstituto){
        $em = $this->getDoctrine()->getManager();

        $instituto = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstituto')->findOneById($idInstituto);

        $entity = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstitutoCargo');
        $query = $entity->createQueryBuilder('a')
            ->select('b')
            ->innerjoin('SieAppWebBundle:InstitucionalizacionCargo', 'b', 'WITH', 'a.cargo=b.id')
            ->where('a.instituto = :instituto')
            ->setParameter('instituto', $instituto)
            ->getQuery();

        $cargos = $query->getResult();

        $cargosArray = array();
        foreach($cargos as $cargo) {
            $cargosArray[$cargo->getId()] = $cargo->getCargo();
        }

        $response = new JsonResponse();
        return $response->setData(array('cargos' => $cargosArray));
    }

    public function indexPrintAction(Request $request) {
        return $this->render('SieProcesosBundle:InstitucionalizacionDgesttla:index_print.html.twig');
    }

    public function printInscAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $formulario = $request->get("formulario");

        $persona = $em->getRepository('SieAppWebBundle:InstitucionalizacionDgesttla')->findOneByCarnet($formulario['carnet']);
        
        if($persona) {
            $arch = 'DGESTTLA_INSTITUCIONALIZACION_' . $persona->getCarnet() . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dgesttla_insc_Institucionalizacion_v1_afv.rptdesign&__format=pdf&&carnet='.$persona->getCarnet().'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } else {
            $this->get('session')->getFlashBag()->add('error', 'No se encontró el registro para el Número de Carnet de Identidad: '.$formulario['carnet'].'. No es posible generar el formulario de inscripción.');
            return $this->redirectToRoute('institucionalizacion_index_print');
        }
    }

    // public function recepcionDistritoGuardarAction(Request $request)
    // {
    //     //variable de control para el cargado de adjunto
    //     $error_upload = false;

    //     $form = $request->get('form');
    //     $file = $request->files->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($form,$file);die;
    //     $gestion =$em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
    //     $ruta = '/../web/uploads/archivos/flujos/'.$form['idrue'].'/rue/'.$gestion.'/';
    //     $datos=array();
    //     $datos['observacion']=$form['observacion'];
    //     $datos['varevaluacion1']=$form['varevaluacion1'];
    //     $datos['requisitos']=$form['requisitos'];
    //     if($form['varevaluacion1'] == 'SI'){
    //         $datos['informedistrito']=$form['informedistrito'];
    //         $datos['fechainformedistrito']=$form['fechainformedistrito'];
            
    //         $adjunto = $this->upload($file['adjuntoinforme'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos['adjuntoinforme']=$adjunto;
            
    //         if(isset($file['actaconformidad'])){
    //             $adjunto = $this->upload($file['actaconformidad'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos['actaconformidad']=$adjunto;

    //             $adjunto = $this->upload($file['bidistrital'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos['bidistrital']=$adjunto;
    //         }
    //     }else{
    //         $datos['varevaluacion2']=$form['varevaluacion2'];
    //         if($form['varevaluacion2'] == 'SI'){
    //             $datos['informedistrito']=$form['informedistrito'];
    //             $datos['fechainformedistrito']=$form['fechainformedistrito'];

    //             $adjunto = $this->upload($file['adjuntoinforme'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos['adjuntoinforme']=$adjunto;
    //         }
    //         $varevaluacion2 = $form['varevaluacion2'];
    //     }
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');   
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion1 = $form['varevaluacion1'];
    //     $lugar = $this->obtienelugar($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion1,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     if($mensaje['dato']== true){
    //         if($varevaluacion1=="NO"){
    //             $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'NO'));
    //             $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
    //             $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
    //             if($mensaje['dato'] == true){
    //                 $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion2,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //                 if($mensaje['dato'] == false){
    //                     //eliminar tramite recibido
    //                     $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
    //                     //eliminar tramite enviado
    //                     $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //                 }
    //             }else{
    //                 //eliminar tramite enviado
    //                 $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //             }
    //         }
    //     }
    //     $request->getSession()
    //         ->getFlashBag()
    //         ->add($mensaje['tipo'], $mensaje['msg']);

    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // /***
    //  * Formulario Distrito apertura/reapertura
    //  */
    // public function recepcionDistritoAperturaAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id'); 
    //     $tipo = $request->get('tipo'); 
    //     //dump($id,$tipo);die;
    //     //validation if the user is logged
    //     $em = $this->getDoctrine()->getManager();
    //     if($tipo =='idtramite'){
    //         $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //         $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //         $tipotramite = $tramite->getTramiteTipo()->getId();
    //         $tareasDatos = $this->obtieneDatos($tramite);
    //         $flujotipo = $tramite->getFlujoTipo()->getId();
    //         $tarea = $tramiteDetalle->getFlujoProceso();
    //         $idrue = $tramite->getInstitucioneducativa()?$tramite->getInstitucioneducativa()->getId():null;
    //         foreach($tareasDatos[0]['datos']['tramites'] as $t){
    //             if($t['id']==54){
    //                 $mapa = true;
    //             }else{
    //                 $mapa = false;
    //             }
    //         }
    //     }else{
    //         $tramite = null;
    //         $tareasDatos =null;
    //         $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo'=>$id,'orden'=>1));
    //         $flujotipo = $id;
    //         $tarea = $flujoproceso;
    //         $idrue = null;
    //         $mapa = false;
    //     }
    //     //dump($tarea);die;
    //     $distritoAperturaForm = $this->createDistritoAperturaForm($flujotipo,$tarea,$tramite,$idrue);
    //     return $this->render('SieProcesosBundle:TramiteRue:recepcionDistritoApertura.html.twig', array(
    //         'form' => $distritoAperturaForm->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tarea,
    //         'mapa'=>$mapa,
    //     ));

    // }

    // public function createDistritoAperturaForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     //dump($wfdatos);die;
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($tramites,$requisitos);die;
    //     $requisitos = array(
    //         'Requisitos Legales'=>'Requisitos Legales',
    //         'Requisitos de Infraestructura'=>'Requisitos de Infraestructura',
    //         'Requisitos Administrativos'=>'Requisitos Administrativos'
    //     );
    //     if($tramite){
    //         $this->trArray = array($tramite->getTramiteTipo()->getId());
    //     }else{
    //         $this->trArray = array(44,54);
    //     }
    //     $form = $this->createFormBuilder()
    //         ->setAction($this->generateUrl('tramite_rue_recepcion_distrito_apertura_guardar'))
    //         ->add('flujoproceso', 'hidden', array('data' =>$tarea->getId() ))
    //         ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //         ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
    //         ->add('codigo', 'text', array('label'=>'Código de Solicitud:','required'=>false,'attr'=>array('class'=>'form-control validar','data-placeholder'=>"")))
    //         ->add('buscar', 'button', array('attr'=>array('class'=>'btn btn-primary','onclick'=>'buscarSolicitud()')))
    //         ->add('tramite_tipo','entity',array('label'=>'Tipo de Trámite:','required'=>false,'multiple' => false,'expanded' => false,'attr'=>array('class'=>'form-control','data-placeholder'=>"Seleccionar tipo de trámite"),'class'=>'SieAppWebBundle:TramiteTipo',
    //             'query_builder'=>function(EntityRepository $tr){
    //             return $tr->createQueryBuilder('tr')
    //                 ->where('tr.obs = :rue')
    //                 ->andWhere('tr.id in (:tipo)')
    //                 ->setParameter('rue','RUE')
    //                 ->setParameter('tipo',$this->trArray)
    //                 ->orderBy('tr.tramiteTipo','ASC');},
    //             'property'=>'tramiteTipo','empty_value' => 'Seleccione tipo de trámite'))
    //         ->add('idrue', 'hidden', array('data' =>$idrue))
    //         ->add('requisitos','choice',array('label'=>'Requisitos:','required'=>true, 'multiple' => true,'expanded' => true,'choices'=>$requisitos))
    //         ->add('observacion','textarea',array('label'=>'Observación:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
    //         ->add('varevaluacion1','choice',array('label'=>'¿Observar y devolver?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //         ->add('informedistrito','text',array('label'=>'CITE del Informe Técnico:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase','placeholder'=>'')))
    //         ->add('fechainformedistrito', 'text', array('label'=>'Fecha del Informe Técnico:','required'=>true,'attr' => array('class' => 'form-control date','placeholder'=>'','autocomplete'=>'off')))
    //         ->add('adjuntoinforme', 'file', array('label' => 'Adjuntar Informe Técnico (Máximo permitido 3M):','required'=>true, 'attr' => array('title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
    //         ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //         ->getForm();
    //     return $form;
    // }
    
    // public function recepcionDistritoAperturaGuardarAction(Request $request)
    // {
    //     //variable de control para el cargado de adjunto
    //     $error_upload = false;
    //     $form = $request->get('form');
    //     $file = $request->files->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     $gestionActual = $this->session->get('currentyear');
    //     //dump($form,$file);die;
    //     $datos=array();
    //     $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo'=>$form['codigo']));
    //     $datosSolicitud = json_decode($solicitudTramite->getDatos(),true);
    //     //dump($form);die;
    //     $gestion = $form['tramite']==''?$gestionActual:$em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
    //     $datos['observacion']=$form['observacion'];
    //     $datos['varevaluacion1']=$form['varevaluacion1'];
    //     $datos['requisitos']=$form['requisitos'];
    //     if($form['varevaluacion1'] == 'SI'){
    //         $datos['informedistrito']=$form['informedistrito'];
    //         $datos['fechainformedistrito']=$form['fechainformedistrito'];
    //         if($form['tramite_tipo'] == 54){
    //             if($form['tramite'] != ''){
    //                 $ruta = '/../web/uploads/archivos/flujos/rue/apertura/'.$gestion.'/'.$form['tramite'].'/';
                
    //                 $adjunto = $this->upload($file['adjuntoinforme'],$ruta);
    //                 if($adjunto == ''){
    //                     $error_upload = true;
    //                 }
    //                 $datos['adjuntoinforme']=$adjunto;
    //             }
    //         }else{
    //             $ruta = '/../web/uploads/archivos/flujos/'. $form['idrue'] .'/rue/'.$gestion.'/';
    //             $adjunto = $this->upload($file['adjuntoinforme'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos['adjuntoinforme']=$adjunto;
    //         }                        
    //     }

    //     $datos = array_merge($datosSolicitud,$datos);
    //     //dump($datos);die;
    //     if($form['tramite_tipo'] == 54){
    //         $lugar = array('idlugarlocalidad'=>$datos['Apertura de Unidad Educativa']['idlocalidad2001'],'idlugardistrito'=>$this->session->get('roluserlugarid'));
    //     }else{
    //         $lugar = array('idlugarlocalidad'=>$datos['jurisdiccion_geografica']['localidad2001_id'],'idlugardistrito'=>$this->session->get('roluserlugarid'));
    //     } 
        
    //     $datos = json_encode($datos);        

    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');   
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $tipotramite= $form['tramite_tipo'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion1 = $form['varevaluacion1'];
    //     //$lugar = $this->obtienelugar($idtramite);
    //     if ($form['tramite']==''){
    //         $mensaje = $this->get('wftramite')->guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion1,'',$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //         $tipo = 1;
    //         if($form['tramite_tipo'] == 54){
    //             $ruta = '/../web/uploads/archivos/flujos/rue/apertura/'.$gestion.'/'.$mensaje['idtramite'].'/';
    //             $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->find($mensaje['iddatos']);
    //             $datos = json_decode($wfdatos->getDatos(),true); 
                
    //             $adjunto = $this->upload($file['adjuntoinforme'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos['adjuntoinforme']=$adjunto;
                
    //             $wfdatos->setDatos(json_encode($datos));
    //             $em->flush();
    //             $origen = '/../web/uploads/archivos/flujos/rue/solicitud/'.$gestion.'/'.$form['codigo'];
    //             $destino = '/../web/uploads/archivos/flujos/rue/apertura/'.$gestion.'/'.$mensaje['idtramite'];
    //             $this->copiarArchivos($origen, $destino);
    //         } 
    //         $solicitudTramite->setEstado(true);
    //         $em->flush();            
    //     }else{
    //         $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion1,$form['tramite'],$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //         $tipo = 2;
    //     }

    //     $request->getSession()
    //         ->getFlashBag()
    //         ->add($mensaje['tipo'], $mensaje['msg']);
    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>$tipo));
    // }

    // public function copiarArchivos($file_origen,$file_destino){
    //     $from = $this->get('kernel')->getRootDir() . $file_origen;
    //     $to = $this->get('kernel')->getRootDir() . $file_destino;
    //     //Abro el directorio que voy a leer
    //     $dir = opendir($from);
    //     //Recorro el directorio para leer los archivos que tiene
    //     while(($file = readdir($dir)) !== false){
    //         //Leo todos los archivos excepto . y ..
    //         if(strpos($file, '.') !== 0){
    //             //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
    //             copy($from.'/'.$file, $to.'/'.$file);
    //         }
    //     }
    //     return true;
    // }
    // /***
    //  * Formulario Departamento
    //  */
    // public function recepcionDepartamentoAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //     $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //     /**
    //      * obtiene datos de los anteriores formularios
    //      */
    //     $tareasDatos = $this->obtieneDatos($tramite);
    //     //dump($tareasDatos);die;
    //     foreach($tareasDatos[0]['datos']['tramites'] as $t){
    //         if($t['id']==54){
    //             $mapa = true;
    //         }else{
    //             $mapa = false;
    //         }
    //     }
    //     $flujotipo = $tramite->getFlujoTipo()->getId();
    //     $tarea = $tramiteDetalle->getFlujoProceso()->getId();
    //     $tipotramite = $tramite->getTramiteTipo()->getId();
    //     $idrue = $tramite->getInstitucioneducativa()?$tramite->getInstitucioneducativa()->getId():'';
    //     $departamentoForm = $this->createDepartamentoForm($flujotipo,$tarea,$tramite,$idrue); 
    //     return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamento.html.twig', array(
    //         'form' => $departamentoForm->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo(),
    //         'mapa' => $mapa
    //     ));

    // }

    // public function createDepartamentoForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $form = $this->createFormBuilder()
    //     ->setAction($this->generateUrl('tramite_rue_recepcion_departamento_guardar'))
    //     ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
    //     ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //     ->add('idrue', 'hidden', array('data' =>$idrue ))
    //     ->add('tramite', 'hidden', array('data' =>$tramite->getId()))
    //     ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'empty_value'=>false,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //     ->add('informesubdireccion','text',array('label'=>'CITE del Informe Subdirección Dirección:','required'=>false,'attr' => array('class' => 'form-control inf','style' => 'text-transform:uppercase')))
    //     ->add('fechainformesubdireccion', 'text', array('label'=>'Fecha de Informe Subdirección:','required'=>false,'attr' => array('class' => 'form-control date inf','autocomplete'=>'off')))
    //     ->add('adjuntoinformesubdireccion', 'file', array('label' => 'Adjuntar Informe Subdireccioón (Máximo permitido 3M):','required'=>false, 'attr' => array('class'=>'form-control-file inf','title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
    //     ->add('informejuridica','text',array('label'=>'CITE de Informe Legal:','required'=>false,'attr' => array('class' => 'form-control inf','style' => 'text-transform:uppercase')))
    //     ->add('fechainformejuridica', 'text', array('label'=>'Fecha de Informe Legal:','required'=>false,'attr' => array('class' => 'form-control date inf','autocomplete'=>'off')))
    //     ->add('adjuntoinformejuridica', 'file', array('label' => 'Adjuntar Informe Legal (Máximo permitido 3M):','required'=>false, 'attr' => array('class'=>'form-control-file inf','title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
    //     ->add('resolucion','text',array('label'=>'Nro. de Resolución Administrativa:','required'=>false,'attr' => array('class' => 'form-control resol','style' => 'text-transform:uppercase')))
    //     ->add('fecharesolucion', 'text', array('label'=>'Fecha de Resolución Administrativa:','required'=>false,'attr' => array('class' => 'form-control date resol')))
    //     ->add('adjuntoresolucion', 'file', array('label' => 'Adjuntar Resolución Administrativa (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Resolución",'accept'=>"application/pdf,.img,.jpg")))
    //     ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control inf','style' => 'text-transform:uppercase')))
    //     ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //     ->getForm();
    //     return $form;
    // }

    // public function recepcionDepartamentoGuardarAction(Request $request)
    // {
    //     //variable de control para el cargado de adjunto
    //     $error_upload = false;
    //     $form = $request->get('form');
    //     $files = $request->files->get('form');
    //     $em = $this->getDoctrine()->getManager();
        
    //     $gestion = $em->getRepository('SieAppWebBundle:Tramite')->find($form['tramite'])->getGestionId();
    //     if($form['idrue']==''){
    //         $ruta = '/../web/uploads/archivos/flujos/rue/apertura/'.$gestion.'/'.$form['tramite'].'/';
    //     }else{
    //         $ruta = '/../web/uploads/archivos/flujos/'.$form['idrue'].'/rue/'.$gestion.'/';
    //     }
    //     $datos=array();
    //     $datos['observacion']=$form['observacion'];
    //     $datos['varevaluacion']=$form['varevaluacion'];
    //     $datos['informesubdireccion']=$form['informesubdireccion'];
    //     $datos['fechainformesubdireccion']=$form['fechainformesubdireccion'];
    //     if($form['informesubdireccion']){
            
    //         $adjunto = $this->upload($files['adjuntoinformesubdireccion'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos['adjuntoinformesubdireccion']=$adjunto;
    //     }
    //     if($form['varevaluacion'] == 'SI'){
    //         $datos['informejuridica']=$form['informejuridica'];
    //         $datos['fechainformejuridica']=$form['fechainformejuridica'];

    //         $adjunto = $this->upload($files['adjuntoinformejuridica'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos['adjuntoinformejuridica']=$adjunto;
    //         $datos['resolucion']=$form['resolucion'];
    //         $datos['fecharesolucion']=$form['fecharesolucion'];

    //         $adjunto = $this->upload($files['adjuntoresolucion'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos['adjuntoresolucion']=$adjunto;
    //     }
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion = $form['varevaluacion'];
    //     $lugar = $this->obtienelugar($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     $request->getSession()
    //             ->getFlashBag()
    //             ->add($mensaje['tipo'], $mensaje['msg']);
    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // /***
    //  * Formulario Minedu
    //  */
    // public function recepcionMineduAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //     $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //     /**
    //      * obtiene datos de los anteriores formularios
    //      */
    //     $tareasDatos = $this->obtieneDatos($tramite);
    //     //dump($tareasDatos);die;
    //     foreach($tareasDatos[0]['datos']['tramites'] as $t){
    //         if($t['id']==54){
    //             $mapa = true;
    //         }else{
    //             $mapa = false;
    //         }
    //     }
    //     $flujotipo = $tramite->getFlujoTipo()->getId();
    //     $tarea = $tramiteDetalle->getFlujoProceso()->getId();
    //     $tipotramite = $tramite->getTramiteTipo()->getId();
    //     $idrue = $tramite->getInstitucioneducativa()?$tramite->getInstitucioneducativa()->getId():'';
    //     $mineduForm = $this->createMineduForm($flujotipo,$tarea,$tramite,$idrue);
    //     return $this->render('SieProcesosBundle:TramiteRue:recepcionMinedu.html.twig', array(
    //         'form' => $mineduForm->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo(),
    //         'mapa' => $mapa,
    //     ));

    // }

    // public function createMineduForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $form = $this->createFormBuilder()
    //     ->setAction($this->generateUrl('tramite_rue_recepcion_minedu_guardar'))
    //     ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
    //     ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //     ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
    //     ->add('idrue', 'hidden', array('data' =>$idrue ))
    //     ->add('tramitetipo', 'hidden', array('data' =>5 ))
    //     ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //     ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
    //     ->add('guardar','submit',array('label'=>'Registrar Modificación'))
    //     ->getForm();
    //     return $form;
    // }

    // public function recepcionMineduGuardarAction(Request $request)
    // {
    //     $form = $request->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($form);die;
    //     $datos=array();
    //     $datos['observacion']=mb_strtoupper($form['observacion'],'utf-8');;
    //     $datos['varevaluacion']=$form['varevaluacion'];
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = mb_strtoupper($form['observacion'],'utf-8');
    //     $varevaluacion = $form['varevaluacion'];
    //     $lugar = $this->obtienelugar($idtramite);
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     if($mensaje['dato'] == true){
    //         if($varevaluacion=="SI"){
    //             $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
    //             $varevaluacion2 = "";
    //             $observacion2 = mb_strtoupper($form['observacion'],'utf-8');;
    //             $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
    //             $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);

    //             if($mensaje['dato'] == true){
    //                 $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion2,$varevaluacion2,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //                 if($mensaje['dato'] == true){
    //                     /**
    //                      * Registrar en el RUE
    //                      */
    //                     $em->getConnection()->beginTransaction();
    //                     try{
    //                         $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
    //                         $tareasDatos = $this->obtieneDatos($tramite);
    //                         if($tramite->getInstitucioneducativa()){
    //                             $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
    //                             $iddistrito = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'];
    //                         }                            
    //                         $tipo = '';
    //                         //dump($tareasDatos);die;
    //                         foreach($tareasDatos[0]['datos']['tramites'] as $t){
    //                             $vAnterior = array();
    //                             $vNuevo = array();
    //                             if($t['id'] == 34){#ampliacion de nivel incluir resolucion
    //                                 foreach($tareasDatos[0]['datos']['institucioneducativaNivel'] as $n){
    //                                     $arr[] = $n['id'];
    //                                 }
    //                                 $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelampliar'];
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $em->flush();
    //                                 //adiciona niveles nuevos
    //                                 foreach($nuevoNivel as $n){
    //                                     if(!in_array($n['id'],$arr)){
    //                                         $nivel = new InstitucioneducativaNivelAutorizado();
    //                                         $nivel->setFechaRegistro(new \DateTime('now'));
    //                                         $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
    //                                         $nivel->setInstitucioneducativa($institucioneducativa);
    //                                         $em->persist($nivel);
    //                                     }
    //                                 }
    //                                 $em->flush();
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($tareasDatos[0]['datos']['institucioneducativaNivel']),json_encode($tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelampliar']),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 35){#reduccion de nivel incluir resolucion
    //                                 $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir'];
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $em->flush();
    //                                 //elimina los niveles
    //                                 $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $institucioneducativa->getId()));
    //                                 if($nivelesElim){
    //                                     foreach ($nivelesElim as $nivel) {
    //                                         $em->remove($nivel);
    //                                     }
    //                                     $em->flush();
    //                                 }
    //                                 //adiciona niveles nuevos
    //                                 foreach($nuevoNivel as $n){
    //                                     //dump($n);die;
    //                                     $nivel = new InstitucioneducativaNivelAutorizado();
    //                                     $nivel->setFechaRegistro(new \DateTime('now'));
    //                                     $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
    //                                     $nivel->setInstitucioneducativa($institucioneducativa);
    //                                     $em->persist($nivel);
    //                                 }
    //                                 $em->flush();
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($tareasDatos[0]['datos']['institucioneducativaNivel']),json_encode($tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir']),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 36){#cambio de dependencia incluir resolucion
    //                                 $dependenciaTipo = $em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id']);
    //                                 $vAnterior['dependencia']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['dependencia_tipo_id'];
    //                                 $vAnterior['dependencia']['dependencia'] = $tareasDatos[0]['datos']['institucioneducativa']['dependencia'];
    //                                 $vNuevo['dependencia'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia'];
    //                                 $institucioneducativa->setDependenciaTipo($dependenciaTipo);
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 if($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id'] == 2){ //convenio
    //                                     $convenio = $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['conveniotipo']['id']);
    //                                     $institucioneducativa->setConvenioTipo($convenio);
    //                                     $institucioneducativa->setAreaMunicipio($tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_dependencia']);
    //                                     $vNuevo['conveniotipo'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['conveniotipo'];
    //                                     $vAnterior['area_municipio'] = $tareasDatos[0]['datos']['institucioneducativa']['area_municipio'];
    //                                     $vNuevo['area_municipio'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_dependencia1'];
    //                                 }else{ //fiscal
    //                                     $institucioneducativa->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById(0));
    //                                     $vAnterior['conveniotipo']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['convenio_tipo_id'];
    //                                     $vAnterior['conveniotipo']['convenio'] = $tareasDatos[0]['datos']['institucioneducativa']['convenio'];
    //                                 }
    //                                 $em->flush();

    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 37){#cambio de nombre incluir resolucion
    //                                 $institucioneducativa->setInstitucioneducativa(mb_strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_nombre'], 'utf-8'));
    //                                 $institucioneducativa->setDesUeAntes(mb_strtoupper($tareasDatos[0]['datos']['institucioneducativa']['institucioneducativa'], 'utf-8'));
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $em->flush();

    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],mb_strtoupper($tareasDatos[0]['datos']['institucioneducativa']['institucioneducativa'], 'utf-8'),mb_strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_nombre'], 'utf-8'),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 38){#cambio de jurisdiccion administrativa incluir resolucion
    //                                 $iddistrito = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito']['id'];
    //                                 $lugarIdDistrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $iddistrito))->getId();
    //                                 $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($iddistrito);
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $institucioneducativa->setAreaMunicipio($tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_jur']);
    //                                 $jurisdicciongeografica = $institucioneducativa->getLeJuridiccionGeografica();
    //                                 $jurisdicciongeografica->setLugarTipoIdDistrito($lugarIdDistrito);
    //                                 $jurisdicciongeografica->setDistritoTipo($distritoTipo);
    //                                 $jurisdicciongeografica->setFechaModificacion(new \DateTime('now'));
    //                                 $em->flush();
    //                                 $vAnterior['area_municipio'] = $tareasDatos[0]['datos']['institucioneducativa']['area_municipio'];
    //                                 $vNuevo['area_municipio'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_jur'];
    //                                 $vAnterior['distrito']['id'] = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'];
    //                                 $vAnterior['distrito']['distrito'] = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito'];
    //                                 $vNuevo['nuevo_distrito'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito'];
                                    
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 39){#Fusion incluir resolucion
    //                                 $tipo = 'fusion';
    //                                 $iefusion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($tareasDatos[0]['datos'][$t['tramite_tipo']]['siefusion']['id']);

    //                                 $vAnterior['siefusion'] = array('sie1'=>$institucioneducativa->getId(),'sie2'=>$iefusion->getId());
    //                                 $vNuevo = array('sieresultante'=>$institucioneducativa->getId()); 
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));                                   
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 40){#Desglose incluir resolucion
    //                                 $tipo = 'desglose';
    //                             }elseif($t['id'] == 41){#cambio de infraestructura incluir resolucion
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $institucioneducativa->setAreaMunicipio($tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_infra']);
    //                                 if(isset($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'])){
    //                                     $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion']));
    //                                 }else{
    //                                     $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($this->obtieneCodigoLe($tareasDatos[0]['datos'][$t['tramite_tipo']],$tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'],$usuario)));
    //                                 }
    //                                 $em->flush();
    //                                 $vAnterior['area_municipio'] = $tareasDatos[0]['datos']['institucioneducativa']['area_municipio'];
    //                                 $vNuevo['area_municipio'] = $tareasDatos[0]['datos'][$t['tramite_tipo']]['i_area_infra'];
    //                                 $vAnterior = array('jurisdiccion_geografica_id'=>$tareasDatos[0]['datos']['jurisdiccion_geografica']['id']);
    //                                 $vNuevo = array('jurisdiccion_geografica_id'=>$institucioneducativa->getLeJuridicciongeografica()->getId());
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 42 or $t['id'] == 43){#cierre temporal
    //                                 $estado = $em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(19);
    //                                 if($tipo == 'fusion' ){
    //                                     $iefusion->setEstadoinstitucionTipo($estado);
    //                                     $iefusion->setObsRue($observacion);
    //                                     $iefusion->setFechaModificacion(new \DateTime('now'));
    //                                     $iefusion->setFechaCierre((new \DateTime('now'))->format('Y-m-d'));
    //                                     $vAnterior['estado']['id'] = $iefusion->getEstadoinstitucionTipo()->getId();
    //                                     $vAnterior['estado']['estado'] = $iefusion->getEstadoinstitucionTipo()->getEstadoinstitucion();
    //                                 }else{
    //                                     $institucioneducativa->setEstadoinstitucionTipo($estado);
    //                                     $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                     $institucioneducativa->setFechaCierre((new \DateTime('now'))->format('Y-m-d'));
    //                                     $institucioneducativa->setObsRue($observacion);
    //                                     $vAnterior['estado']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion_tipo_id'];
    //                                     $vAnterior['estado']['estado'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion'];                                        
    //                                 }
    //                                 $em->flush();
                                    
    //                                 $vNuevo['estado']['id'] = $estado->getId();
    //                                 $vNuevo['estado']['estado'] = $estado->getEstadoinstitucion();
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 44){#reapertura incluir resolucion
    //                                 $estado = $em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(10);
    //                                 $institucioneducativa->setEstadoinstitucionTipo($estado);
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $vAnterior['estado']['id'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion_tipo_id'];
    //                                 $vAnterior['estado']['estado'] = $tareasDatos[0]['datos']['institucioneducativa']['estadoinstitucion'];                                        
    //                                 $em->flush();
                                    
    //                                 $vNuevo['estado']['id'] = $estado->getId();
    //                                 $vNuevo['estado']['estado'] = $estado->getEstadoinstitucion();
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[1]['datos']['resolucion'],$tareasDatos[1]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 46){#regularizacion rue incluir resolucion , ya incluido
    //                                 $vAnterior['nro_resolucion'] = $institucioneducativa->getNroResolucion();
    //                                 $vAnterior['fecha_resolucion'] = $institucioneducativa->getFechaResolucion()?$institucioneducativa->getFechaResolucion()->format('d-m-Y'):'';
    //                                 $institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
    //                                 $institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
    //                                 $institucioneducativa->setFechaModificacion(new \DateTime('now'));
    //                                 $institucioneducativa->setObsRue($observacion);
    //                                 $em->flush();
                                    
    //                                 $vNuevo['nro_resolucion'] = $tareasDatos[2]['datos']['resolucion'];
    //                                 $vNuevo['fecha_resolucion'] = $tareasDatos[2]['datos']['fecharesolucion'];
    //                                 $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,$t['id'],$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                             }elseif($t['id'] == 54){ //incluir resolucion, revisar Apertura
    //                                 //$nuevaInstitucioneducativa = $this->registrarInstitucioneducativa($tareasDatos[0][$t['tramite_tipo']]);
    //                                 $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
    //                                 //dump($datosSolicitud);die;
    //                                 if(isset($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'])){
    //                                     $codLe = $tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'];
    //                                 }else{
    //                                     $codLe = $this->obtieneCodigoLe($tareasDatos[0]['datos'][$t['tramite_tipo']],$tareasDatos[0]['datos'][$t['tramite_tipo']]['iddistrito'],$usuario);                                        
    //                                 }
    //                                 $query = $em->getConnection()->prepare('SELECT get_genera_codigo_ue(:codle)');
    //                                 $query->bindValue(':codle', $codLe);
    //                                 $query->execute();
    //                                 $codigoue = $query->fetchAll();
    //                                 //dump($codigoue);die;
    //                                 $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
    //                                 $ieducativatipo = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(1);
    //                                 $entity = new Institucioneducativa();
    //                                 $entity->setId($codigoue[0]["get_genera_codigo_ue"]);
    //                                 $entity->setInstitucioneducativa(mb_strtoupper($datosSolicitud['institucioneducativa'], 'utf-8'));
    //                                 $entity->setFechaResolucion(new \DateTime($tipo=='desglose'?$tareasDatos[2]['datos']['fecharesolucion']:$tareasDatos[1]['datos']['fecharesolucion']));
    //                                 $entity->setFechaCreacion(new \DateTime('now'));
    //                                 $entity->setFechaFundacion(new \DateTime($datosSolicitud['fechafundacion']));
    //                                 $entity->setNroResolucion(mb_strtoupper($tipo=='desglose'?$tareasDatos[2]['datos']['resolucion']:$tareasDatos[1]['datos']['resolucion'], 'utf-8'));
    //                                 $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($datosSolicitud['dependencia']['id']));
    //                                 if($datosSolicitud['dependencia']['id'] == 2){
    //                                     $convenio = $datosSolicitud['conveniotipo']['id'];
    //                                     $areaMunicipio = $datosSolicitud['i_area_apertura'];
    //                                 }elseif($datosSolicitud['dependencia']['id'] == 3){
    //                                     $convenio = $datosSolicitud['constitucion']['id'];
    //                                     $areaMunicipio = null;
    //                                 }else{
    //                                     $convenio = 0;
    //                                     $areaMunicipio = $datosSolicitud['i_area_apertura'];
    //                                 }
    //                                 $entity->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($convenio));
    //                                 $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(10));
    //                                 $entity->setInstitucioneducativaTipo($ieducativatipo);
    //                                 $entity->setObsRue($observacion);
    //                                 $entity->setAreaMunicipio($areaMunicipio);
    //                                 $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codLe));    
    //                                 $entity->setOrgcurricularTipo($ieducativatipo->getOrgcurricularTipo());
    //                                 $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(1));
    //                                 //dump($entity);die;
    //                                 $em->persist($entity);
    //                                 $em->flush();
                                    
    //                                 foreach($datosSolicitud['niveltipo'] as $n){
    //                                     $nivel = new InstitucioneducativaNivelAutorizado();
    //                                     $nivel->setFechaRegistro(new \DateTime('now'));
    //                                     $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
    //                                     $nivel->setInstitucioneducativa($entity);
    //                                     $em->persist($nivel);
    //                                 }
    //                                 $em->flush();
                                    
    //                                 //actualizamos el tramite con la unidad educativa creada
    //                                 $tramite->setInstitucioneducativa($entity);
    //                                 $em->flush();
    //                                 // Try and commit the transaction
    //                                 $mensaje['msg'] = "El trámite Nro. " . $tramite->getId() . " de APERTURA DE UNIDAD EDUCATIVA para la institución educativa ". $entity->getInstitucioneducativa() ." fue registrada correctamente con el código RUE: ".$entity->getId().", y el código de Local educativo: ".$entity->getLeJuridicciongeografica()->getId();
    //                                 if($tipo == 'desglose'){
    //                                     $vAnterior['siedesglose'] = $institucioneducativa->getId();
    //                                     $vAnterior['sienuevo'] = $entity->getId();
    //                                     $historial = $this->registraHistorialTramite($institucioneducativa,$tramite,54,$tareasDatos[2]['datos']['resolucion'],$tareasDatos[2]['datos']['fecharesolucion'],json_encode($vAnterior),json_encode($vNuevo),$form['observacion'],$usuario);
    //                                 }
    //                             }elseif($t['id'] == 55){//Actualizacion de Resolucion
    //                                 //$nuevaInstitucioneducativa = $this->registrarInstitucioneducativa($tareasDatos[0][$t['tramite_tipo']]);
    //                                 $datosSolicitud = $tareasDatos[0]['datos'][$t['tramite_tipo']];
    //                                 $codigoue = $tareasDatos[0]['datos']['institucioneducativa']['id'];
    //                                 $datosRequisito = $tareasDatos[2]['datos'];
    //                                 //dump($codigoue);die;
    //                                 $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($codigoue);
    //                                 $entity->setFechaResolucion(new \DateTime($datosRequisito['fecharesolucion']));
    //                                 $entity->setNroResolucion(mb_strtoupper($datosRequisito['resolucion']));
    //                                 $em->persist($entity);
    //                                 $em->flush();                                   
                                    
    //                                 // Try and commit the transaction
    //                                 $mensaje['msg'] = "El trámite Nro. " . $tramite->getId() . " de APERTURA DE UNIDAD EDUCATIVA para la institución educativa ". $entity->getInstitucioneducativa() ." fue registrada correctamente con el código RUE: ".$entity->getId().", y el código de Local educativo: ".$entity->getLeJuridicciongeografica()->getId();
    //                             }
    //                         }
    //                         $em->getConnection()->commit();
    //                     }catch (\Exception $ex) {
    //                         $mensaje['msg'] ='¡Ocurrio un error al registrar los datos, vuelva a intentar!</br> '.$ex->getMessage();
    //                         $mensaje['tipo'] = 'error';
    //                         $em->getConnection()->rollback();
    //                         //dump($em);
    //                         $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //                         $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
    //                         $c = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //                         //dump($b.'-b',$a.'-a',$c.'-c');die;
    //                         //dump($ex->getMessage().'---bd');
    //                     }
    //                 }else{
    //                     $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
    //                     $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //                 }
    //             }else{
    //                 $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
    //             }
    //         }
    //     }
        
    //     $request->getSession()
    //             ->getFlashBag()
    //             ->add($mensaje['tipo'], $mensaje['msg']);

    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // public function registraHistorialTramite($institucioneducativa,$tramite,$tramiTipoId,$nroResolucion,$fechaResolucion,$valorAnterior,$valorNuevo,$obs,$usuario){
        
    //     $em = $this->getDoctrine()->getManager();
    //     $historial = new InstitucioneducativaHistorialTramite();
    //     $historial->setInstitucioneducativa($institucioneducativa);
    //     $historial->setTramite($tramite);
    //     $historial->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find($tramiTipoId));
    //     $historial->setNroResolucion(mb_strtoupper($nroResolucion, 'utf-8'));
    //     $historial->setFechaResolucion(new \DateTime($fechaResolucion));
    //     $historial->setValorAnterior($valorAnterior);
    //     $historial->setValorNuevo($valorNuevo);
    //     $historial->setObservacion(mb_strtoupper($obs, 'utf-8'));
    //     $historial->setFechaRegistro(new \DateTime('now'));
    //     $historial->setUsuarioRegistro($em->getRepository('SieAppWebBundle:Usuario')->find($usuario));
    //     $em->persist($historial);
    //     $em->flush();

    //     return $historial;
    // }

    // /***
    //  * Formulario envio certificados
    //  */
    // public function enviaCertificadoMineduAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //     $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //     /**
    //      * obtiene datos de los anteriores formularios
    //      */
    //     $tareasDatos = $this->obtieneDatos($tramite);
    //     $flujotipo = $tramite->getFlujoTipo()->getId();
    //     $tarea = $tramiteDetalle->getFlujoProceso()->getId();
    //     $tipotramite = $tramite->getTramiteTipo()->getId();
    //     $enviaCertificadoMinedu = $this->createEnviaCertificadoMineduForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
    //     return $this->render('SieProcesosBundle:TramiteRue:enviaCertificadoMinedu.html.twig', array(
    //         'form' => $enviaCertificadoMinedu->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
    //     ));

    // }

    // public function createEnviaCertificadoMineduForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $form = $this->createFormBuilder()
    //     ->setAction($this->generateUrl('tramite_rue_envia_certificado_minedu_guardar'))
    //     ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
    //     ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //     ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
    //     ->add('idrue', 'hidden', array('data' =>$idrue ))
    //     ->add('tramitetipo', 'hidden', array('data' =>5 ))
    //     //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //     ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
    //     ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //     ->getForm();
    //     return $form;
    // }

    // public function enviaCertificadoMineduGuardarAction(Request $request)
    // {
        
    //     $form = $request->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($form);die;
    //     $datos=array();
    //     $datos['observacion']=$form['observacion'];
    //     //$datos['varevaluacion']=$form['varevaluacion'];
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion = "";
    //     $lugar = $this->obtienelugar($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     if($mensaje['dato']== true){
    //         $request->getSession()
    //             ->getFlashBag()
    //             ->add('exito', $mensaje['msg']);
    //     }else{
    //         $request->getSession()
    //             ->getFlashBag()
    //             ->add('error', $mensaje['msg']);
    //     }
    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // /***
    //  * Formulario recepcion y envio certificados departamento
    //  */
    // public function enviaCertificadoDepartamentoAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //     $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //     /**
    //      * obtiene datos de los anteriores formularios
    //      */
    //     $tareasDatos = $this->obtieneDatos($tramite);
    //     $flujotipo = $tramite->getFlujoTipo()->getId();
    //     $tarea = $tramiteDetalle->getFlujoProceso()->getId();
    //     $tipotramite = $tramite->getTramiteTipo()->getId();
    //     $enviaCertificadoDepartamentoForm = $this->createEnviaCertificadoDepartamentoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
    //     return $this->render('SieProcesosBundle:TramiteRue:enviaCertificadoDepartamento.html.twig', array(
    //         'form' => $enviaCertificadoDepartamentoForm->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
    //     ));

    // }

    // public function createEnviaCertificadoDepartamentoForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $form = $this->createFormBuilder()
    //     ->setAction($this->generateUrl('tramite_rue_entrega_certificado_distrito_guardar'))
    //     ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
    //     ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //     ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
    //     ->add('idrue', 'hidden', array('data' =>$idrue ))
    //     ->add('tramitetipo', 'hidden', array('data' =>5 ))
    //     //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //     ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
    //     ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //     ->getForm();
    //     return $form;
    // }

    // public function enviaCertificadoDepartamentoGuardarAction(Request $request)
    // {
        
    //     $form = $request->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($form);die;
    //     $datos=array();
    //     $datos['observacion']=$form['observacion'];
    //     //$datos['varevaluacion']=$form['varevaluacion'];
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion = "";
    //     $lugar = $this->obtienelugar($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     if($mensaje['dato']== true){
    //         $request->getSession()
    //             ->getFlashBag()
    //             ->add('exito', $mensaje['msg']);
    //     }else{
    //         $request->getSession()
    //             ->getFlashBag()
    //             ->add('error', $mensaje['msg']);
    //     }
    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // /***
    //  * Formulario recepcion y entraga certificados Distrito
    //  */
    // public function entregaCertificadoDistritoAction(Request $request)
    // {
        
    //     $this->session = $request->getSession();
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     //validation if the user is logged
    //     if (!isset($usuario)) {
    //         return $this->redirect($this->generateUrl('login'));
    //     }
    //     $id = $request->get('id');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
    //     $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //     /**
    //      * obtiene datos de los anteriores formularios
    //      */
    //     $tareasDatos = $this->obtieneDatos($tramite);
    //     $flujotipo = $tramite->getFlujoTipo()->getId();
    //     $tarea = $tramiteDetalle->getFlujoProceso()->getId();
    //     $tipotramite = $tramite->getTramiteTipo()->getId();
    //     $entregaCertificadoDistritoForm = $this->createEntregaCertificadoDistritoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
    //     return $this->render('SieProcesosBundle:TramiteRue:entregaCertificadoDistrito.html.twig', array(
    //         'form' => $entregaCertificadoDistritoForm->createView(),
    //         'tramite'=>$tramite,
    //         'datos'=>$tareasDatos,
    //         'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
    //     ));

    // }

    // public function createEntregaCertificadoDistritoForm($flujotipo,$tarea,$tramite,$idrue)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $form = $this->createFormBuilder()
    //     ->setAction($this->generateUrl('tramite_rue_entrega_certificado_distrito_guardar'))
    //     ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
    //     ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
    //     ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
    //     ->add('idrue', 'hidden', array('data' =>$idrue ))
    //     ->add('tramitetipo', 'hidden', array('data' =>5 ))
    //     //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
    //     ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
    //     ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //     ->getForm();
    //     return $form;
    // }

    // public function entregaCertificadoDistritoGuardarGuardarAction(Request $request)
    // {
        
    //     $form = $request->get('form');
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($form);die;
    //     $datos=array();
    //     $datos['observacion']=$form['observacion'];
    //     //$datos['varevaluacion']=$form['varevaluacion'];
    //     $datos = json_encode($datos);
    //     //dump($datos);die;
    //     $usuario = $this->session->get('userId');
    //     $rol = $this->session->get('roluser');
    //     $flujotipo = $form['flujotipo'];
    //     $tarea = $form['flujoproceso'];
    //     $idtramite = $form['tramite'];
    //     $tabla = 'institucioneducativa';
    //     $id_tabla = $form['idrue'];
    //     $observacion = $form['observacion'];
    //     $varevaluacion = "";
    //     $lugar = $this->obtienelugar($idtramite);
    //     $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
    //     $request->getSession()
    //             ->getFlashBag()
    //             ->add($mensaje['tipo'], $mensaje['msg']);
    //     return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    // }

    // public function obtienelugar($idtramite)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
    //     $lugar = array();
    //     if ($tramite->getInstitucioneducativa()){
    //         $lugar['idlugarlocalidad'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
    //         $lugar['idlugardistrito'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            
    //     }else{
    //         $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
    //             ->select('wfd')
    //             ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
    //             ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
    //             ->where('td.tramite='.$tramite->getId())
    //             ->andWhere('wfd.esValido=true')
    //             ->andWhere('fp.orden=1')
    //             ->getQuery()
    //             ->getResult();
    //         $lugar['idlugarlocalidad'] = $wfdatos[0]->getLugarTipoLocalidadId();
    //         $lugar['idlugardistrito'] = $wfdatos[0]->getLugarTipoDistritoId();
    //     }
    //     return $lugar;
    // }

    // public function obtieneCodigoLe($le,$iddistrito,$id_usuario){
    //     try {
    //         //dump($le);die;
    //         $em = $this->getDoctrine()->getManager();
            
    // 		$sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idmunicipio2001']);
    // 		$secCod = $sec->getCodigo();
    // 		$proCod = $sec->getLugarTipo()->getCodigo();
    //         $depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();
            
    // 		$dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($iddistrito);
    // 		$distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $dis->getId()));
    //         $query = $em->getConnection()->prepare('SELECT get_genera_codigo_le(:dep,:pro,:sec)');
    //         $query->bindValue(':dep', $depCod);
    //         $query->bindValue(':pro', $proCod);
    //         $query->bindValue(':sec', $secCod);
    //         $query->execute();
    //         $codigolocal = $query->fetchAll();
    //         // Registramos el local
    // 		$entity = new JurisdiccionGeografica();
    //         $entity->setId($codigolocal[0]["get_genera_codigo_le"]);
    //         $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idlocalidad2001']));
    //         $entity->setLugarTipoIdLocalidad2012($le['idcomunidad2012']);
    //         $entity->setLugarTipoIdDistrito($distrito->getId());
    //         $entity->setDistritoTipo($dis);
    //         $entity->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneById(0));
    //         $entity->setZona(mb_strtoupper($le['zona'], 'utf-8'));
    //         $entity->setDireccion(mb_strtoupper($le['direccion'], 'utf-8'));
    //         $entity->setCordx($le['latitud']);
    //         $entity->setCordy($le['longitud']);
    //         $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(1));
    //         $entity->setUsuarioId($id_usuario);
    //         $entity->setFechaRegistro(new \DateTime('now'));
    //         $em->persist($entity);
    // 		$em->flush();
    //         return $entity->getId();
    // 	} catch (Exception $ex) {
    // 		return false;
    // 	}
    // }
    
    // public function buscarRueAction(Request $request)
    // {
    //     //dump($request);die;
        
    //     $idlugarusuario = $this->session->get('roluserlugarid');
    //     //dump($idlugarusuario);die;
    //     $iddistrito=$request->get('iddistrito');
    //     $idsiefusion=$request->get('idsiefusion');
    //     $em = $this->getDoctrine()->getManager();
    //     $query = $em->createQuery('SELECT ie.id,ie.institucioneducativa,nt.id as nivel_tipo_id,nt.nivel
    //             FROM SieAppWebBundle:Institucioneducativa ie
    //             JOIN SieAppWebBundle:JurisdiccionGeografica le WITH ie.leJuridicciongeografica = le.id
    //             JOIN SieAppWebBundle:InstitucioneducativaNivelAutorizado iena WITH iena.institucioneducativa = ie.id
    //             JOIN SieAppWebBundle:NivelTipo nt WITH nt.id = iena.nivelTipo
    //             WHERE ie.id = :id
    //             AND ie.estadoinstitucionTipo = 10
    //             AND ie.institucioneducativaAcreditacionTipo = 1
    //             AND ie.institucioneducativaTipo = 1
    //             AND ie.dependenciaTipo in (1,2)
    //             AND le.lugarTipoIdDistrito = :lugar_id')
    //             ->setParameter('id', $idsiefusion)
    //             ->setParameter('lugar_id', $iddistrito);
    //     $institucioneducativa = $query->getResult();
    //     //dump($institucioneducativa);die;
    //     $response = new JsonResponse();
    //     if($institucioneducativa){
    //         $iefusion = array('idsiefusion'=>$idsiefusion,'institucioneducativa'=>$idsiefusion.'-'.$institucioneducativa[0]['institucioneducativa']);
    //         $response->setData(array('ie'=>$iefusion));
    //     }else{
    //         $response->setData(array('msg'=>'El código SIE es incorrecto.'));
    //     }

    //     return $response;
    // }

    // public function buscarRueComparteAction(Request $request)
    // {
    //     //dump($request);die;
        
    //     $idlugarusuario = $this->session->get('roluserlugarid');
    //     $sie=$request->get('sie');
    //     $iddistrito = $request->get('iddistrito');
    //     $le = $request->get('le');
    //     //dump($sie,$iddistrito,$le);die;
    //     $em = $this->getDoctrine()->getManager();
        
    //     $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
    //             ->select('ie.id,ie.institucioneducativa,ot.orgcurricula,d.dependencia,dt.distrito','le.id as codigole ')
    //             ->innerJoin('SieAppWebBundle:DependenciaTipo', 'd', 'with', 'd.id = ie.dependenciaTipo')
    //             ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'le', 'with', 'le.id = ie.leJuridicciongeografica')
    //             ->innerJoin('SieAppWebBundle:DistritoTipo', 'dt', 'with', 'dt.id = le.distritoTipo')
    //             ->innerJoin('SieAppWebBundle:OrgcurricularTipo', 'ot', 'with', 'ot.id = ie.orgcurricularTipo')
    //             ->where("ie.id='".$sie."'")
    //             ->andWhere("ie.estadoinstitucionTipo=10")
    //             ->andWhere("ie.institucioneducativaAcreditacionTipo=1");

    //     if($iddistrito){
    //         $institucioneducativa = $institucioneducativa
    //             ->andWhere("le.distritoTipo=".$iddistrito)
    //             ->andWhere("le.id<>".$le);
    //     }

    //     $institucioneducativa = $institucioneducativa
    //         ->getQuery()
    //         ->getResult();

    //     //dump($institucioneducativa);die;
    //     $response = new JsonResponse();
    //     if($institucioneducativa){
    //         $ie = array('id'=>$sie,
    //                     'institucioneducativa'=>$institucioneducativa[0]['institucioneducativa'],
    //                     'subsistema'=>$institucioneducativa[0]['orgcurricula'],
    //                     'dependencia'=>$institucioneducativa[0]['dependencia'],
    //                     'codigole'=>$institucioneducativa[0]['codigole']
    //                 );
    //         //dump($ie);die;
    //         $response->setData($ie);
    //     }else{
    //         $response->setData(array('msg'=>'El código SIE es incorrecto.'));
    //     }

    //     return $response;
    // }

    // public function validarNombreDistritoAction(Request $request)
    // {
    //     //dump($request);die;
        
    //     $idlugarusuario = $this->session->get('roluserlugarid');
    //     //dump($idlugarusuario);die;
    //     $sie=$request->get('sie');
    //     $nuevo_nombre=$request->get('nuevo_nombre');
    //     $iddistrito=$request->get('iddistrito');
    //     $em = $this->getDoctrine()->getManager();
    //     $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
    //         ->select('ie.id,ie.institucioneducativa,dt.distrito')
    //         ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'le', 'with', 'le.id = ie.leJuridicciongeografica')
    //         ->innerJoin('SieAppWebBundle:DistritoTipo', 'dt', 'with', 'dt.id = le.distritoTipo')
    //         ->where("ie.institucioneducativa='".$nuevo_nombre."'")
    //         ->andWhere("le.distritoTipo=".$iddistrito)
    //         ->andWhere("ie.estadoinstitucionTipo=10")
    //         ->andWhere("ie.institucioneducativaAcreditacionTipo=1")
    //         ->getQuery()
    //         ->getResult();
    //     //dump($institucioneducativa);die;
    //     $response = new JsonResponse();
    //     if($institucioneducativa){
    //         $ie = array('id'=>$institucioneducativa[0]['id'],
    //                     'institucioneducativa'=>$institucioneducativa[0]['institucioneducativa'],
    //                     'distrito'=>$institucioneducativa[0]['distrito'],
    //                     'msg'=>'El nuevo nombre de la Unidad Educativa: <strong>'. $institucioneducativa[0]['institucioneducativa'] .'</strong> ya se encuentra registrada con el <strong>Código RUE: '. $institucioneducativa[0]['id'] .'</strong> en el mismo Distrito Educativo: <strong>'. $institucioneducativa[0]['distrito'] .'</strong>.</br>Por lo que debe elegir otro nombre.');
    //         //dump($ie);die;
    //         $response->setData($ie);
    //     }else{
    //         $response->setData(array('msg'=>'ok'));
    //     }

    //     return $response;
    // }

    // public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol,$id_ie)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     /**   
    //      * id del lugar usuario
    //     */
    //     $query = $em->getConnection()->prepare('select lugar_tipo_id from usuario_rol where usuario_id='. $usuario .' and rol_tipo_id=' . $rol);
    //     $query->execute();
    //     $lugarTipo = $query->fetchAll();

    //     /**
    //      * tareas devueltas por condicion
    //      */
    //     $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
    //             ->select('fp.id,wf.condicion')
    //             ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
    //             ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
    //             ->getQuery()
    //             ->getResult();
        
    //     //dump($wftareac);die;
    //     /**
    //      * tareas devueltas
    //      */
    //     $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
    //             ->select('fp.id')
    //             ->where('fp.tareaSigId =' . $tarea_actual)
    //             ->getQuery()
    //             ->getResult();
    //     /**
    //      * tarea anterior
    //      */
    //     $tarea = 'td.flujo_proceso_id='. $tarea_ant;

    //     if($wftareac and $fp){
    //         $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."') or td.flujo_proceso_id=". $fp[0]['id']. ")";
    //     }elseif ($wftareac){
    //         $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."'))";
    //     }elseif ($fp){
    //         $tarea = "(" . $tarea . " or td.flujo_proceso_id=". $fp[0]['id']. ")";
    //     }
    //     /**
    //      * si tiene condicion la tarea anterior
    //      */
    //     $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
    //     $query1->execute();
    //     $evaluacion = $query1->fetchAll();
        
    //     if($rol == 9){ //DIRECTOR
    //         $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ie.id=". $id_ie);
    //     }elseif ($rol == 10) { //DISTRITO
    //         if ($evaluacion)
    //         {
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] .")) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
    //         }else{
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] ."))");
    //         }
    //     }elseif ($rol == 7) { //DEPARTAMENTO
    //         if ($evaluacion)
    //         {
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join distrito_tipo dt on le.distrito_tipo_id=dt.id
    //             left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] ."))) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
    //         }else{
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join distrito_tipo dt on le.distrito_tipo_id=dt.id
    //             left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] .")))");
    //         }
    //     }elseif ($rol == 8) { //NACIONAL
    //         if ($evaluacion)
    //         {
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
    //         }else{
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             left join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea);
    //         }
    //     }
    //     $query->execute();
    //     $tramites = $query->fetchAll();
    //     //dump($tramites);die;
    //     $data['tramites'] = $tramites;
    //     return $data;
    // }

    // public function obtieneDatos($tramite)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
    //             ->select('wfd')
    //             ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
    //             ->where('td.tramite='.$tramite->getId())
    //             ->andWhere("wfd.esValido=true")
    //             ->orderBy("td.flujoProceso")
    //             ->getQuery()
    //             ->getResult();
    //     $tareasDatos = array();
    //     foreach($wfdatos as $wfd)
    //     {
    //         $datos = json_decode($wfd->getdatos(),true);
    //         $tareasDatos[] = array('flujoProceso'=>$wfd->getTramiteDetalle()->getFlujoProceso(),'datos'=>$datos);
    //     }
    //     //dump($tareasDatos);die;
    //     return $tareasDatos;
    // }

    // public function buscaredificioAction(Request $request)
    // {
    //     $idLe = $request->get('idLe');
    //     $iddistrito = $request->get('iddistrito');

    //     $em = $this->getDoctrine()->getManager();
    //     //dump($edificio);die;
    //     $lugarArray = array();
    //     $response = new JsonResponse();
    //     if($iddistrito){
    //         $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneBy(array('id'=>$idLe,'distritoTipo'=>$iddistrito));
            
    //     }else{
    //         $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($idLe);
    //         /* $departamento2012 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 8, 'paisTipoId' =>1));
    //         $departamento2001 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' =>1));

    //         foreach($departamento2012 as $d){
    //             $dep[$d->getid()] = $d->getlugar();
    //         }
    //         $lugarArray['c2012']['dep']['lista']=$dep;
    //         foreach($departamento2001 as $d){
    //             $dep1[$d->getid()] = $d->getlugar();
    //         }
    //         $lugarArray['c2001']['dep']['lista']=$dep1; */
            
    //     }      
        
    //     if ($edificio){
    //         $lugarArray['zona'] = $edificio->getZona();
    //         $lugarArray['direccion'] = $edificio->getDireccion();
    //         $lugarArray['distrito']['id'] = $edificio->getDistritoTipo()->getId();
    //         $lugarArray['c2001']['loc']['id'] = $edificio->getLugarTipoLocalidad()->getId();
    //         $lugarArray['c2001']['can']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId();
    //         $lugarArray['c2001']['mun']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
    //         $lugarArray['c2001']['prov']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
    //         $lugarArray['c2001']['dep']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            
    //         //$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idDepartamento);
    //         $query = $em->createQuery(
    //             'SELECT dt
    //             FROM SieAppWebBundle:DistritoTipo dt
    //             WHERE dt.id NOT IN (:ids)
    //             AND dt.departamentoTipo = :dpto
    //             ORDER BY dt.id')
    //             ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
    //             ->setParameter('dpto', $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getCodigo());
    //         $distrito = $query->getResult();
    //         foreach($distrito as $d){
    //             $lugarArray['distrito']['lista'][$d->getId()] = $d->getDistrito();
    //         }

    //         $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
    // 	    foreach($provincia as $p){
    //             $lugarArray['c2001']['prov']['lista'][$p->getid()] = $p->getlugar();
    //         }
            
    //         $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
    //     	foreach($municipio as $m){
    //             $lugarArray['c2001']['mun']['lista'][$m->getid()] = $m->getlugar();
    //         }
            
    //         $canton = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>4, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId()));
    // 	    foreach($canton as $c){
    //             $lugarArray['c2001']['can']['lista'][$c->getid()] = $c->getlugar();
    //         }
            
    //         $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>5, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId()));
    // 	    foreach($localidad as $l){
    //             $lugarArray['c2001']['loc']['lista'][$l->getid()] = $l->getlugar();
    //         }

    //         if($edificio->getLugarTipoIdLocalidad2012()){
            
    //             $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$edificio->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));            
    //             $lugarArray['c2012']['comu']['id'] = $comunidad->getId();
    //             $lugarArray['c2012']['mun']['id'] = $comunidad->getLugarTipo()->getId();
    //             $lugarArray['c2012']['prov']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getId();
    //             $lugarArray['c2012']['dep']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
                
    //             $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
    //             foreach($provincia as $p){
    //                 $lugarArray['c2012']['prov']['lista'][$p->getid()] = $p->getlugar();
    //             }
                
    //             $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getId()));
    //             foreach($municipio as $m){
    //                 $lugarArray['c2012']['mun']['lista'][$m->getid()] = $m->getlugar();
    //             }
                
    //             $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>11, 'lugarTipo' => $comunidad->getLugarTipo()->getId()));
    //             foreach($comunidad as $c){
    //                 $lugarArray['c2012']['comu']['lista'][$c->getid()] = $c->getlugar();
    //             }
    //         }
    //         return $response->setData(array(
    //             'lugar' => $lugarArray,
    //         ));
    //     }else{
    //         //dump($dep);die;
    //         $mensaje = "¡Código de Edificio Educativo incorrecto!";
    //         //dump($mensaje);die;
    //         return $response->setData(array(
    //             'msg'=>$mensaje,
    //             'lugar' => $lugarArray,
    //         ));
    //     }
        
    // }

    // public function provinciasAction($idDepartamento,$censo){
    //     //dump($idDepartamento);die;
    //     $em = $this->getDoctrine()->getManager();
    //     if($censo == 2001){
    //         $nivel = 2;
    //     }else{
    //         $nivel = 9;
    //     }
        
    //     $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idDepartamento));
    // 	$provincia = array();
    // 	foreach($prov as $p){
    //         $provincia[$p->getid()] = $p->getlugar();
    //         /* if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
    //             $provincia[$p->getid()] = $p->getlugar();
    //         } */
    //     }
        
    //     /* *
    //      * distitos
    //      */
    //     $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idDepartamento);
    //     $query = $em->createQuery(
    //         'SELECT dt
    //            FROM SieAppWebBundle:DistritoTipo dt
    //           WHERE dt.id NOT IN (:ids)
    //             AND dt.departamentoTipo = :dpto
    //        ORDER BY dt.id')
    //         ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
    //         ->setParameter('dpto', (int)$dep->getcodigo());
    //         $distrito = $query->getResult();
    //         $distritoArray = array();
    //         foreach($distrito as $c){
    //             $distritoArray[$c->getId()] = $c->getDistrito();
    //         }

    // 	$response = new JsonResponse();
    // 	return $response->setData(array('provincia' => $provincia, 'distrito' => $distritoArray));
    // }

    // public function municipiosAction($idProvincia,$censo){
    // 	$em = $this->getDoctrine()->getManager();
    //     if($censo == 2001){
    //         $nivel = 3;
    //     }else{
    //         $nivel = 10;
    //     }
    //     $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idProvincia));
    // 	$municipio = array();
    // 	foreach($mun as $m){
    //         $municipio[$m->getid()] = $m->getlugar();
    //         /* if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    // 		    $municipio[$m->getid()] = $m->getlugar();
    //         } */
    // 	}
    // 	$response = new JsonResponse();
    // 	return $response->setData(array('municipio' => $municipio));
    // }

    // public function comunidadAction($idMunicipio,$censo){
    //     //dump($idMunicipio,$censo,'entra');die;
    //     $em = $this->getDoctrine()->getManager();
    //     if($censo == 2012){
    //         $nivel = 11;
    //     }
    // 	$com = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
    // 	$canton = array();
    // 	foreach($com as $c){
    //         $comunidad[$c->getid()] = $c->getlugar();
    //         /* if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    // 		    $comunidad[$c->getid()] = $c->getlugar();
    //         } */
    // 	}
    // 	$response = new JsonResponse();
    // 	return $response->setData(array('comunidad' => $comunidad));
    // }

    // public function cantonesAction($idMunicipio,$censo){
    //     //dump($idMunicipio,$censo);die;
    //     $em = $this->getDoctrine()->getManager();
    //     if($censo == 2001){
    //         $nivel = 4;
    //     }
    // 	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
    // 	$canton = array();
    // 	foreach($can as $c){
    //         $canton[$c->getid()] = $c->getlugar();
    //         /* if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    // 		    $canton[$c->getid()] = $c->getlugar();
    //         } */
    // 	}
    // 	$response = new JsonResponse();
    // 	return $response->setData(array('canton' => $canton));
    // }

    // public function tramiteTareaRitt($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     //dump($lugarTipo);die;
    //     $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$usuario,'rolTipo'=>$rol));            
    //     $idlugarusuario = $usuariorol[0]->getLugarTipo()->getCodigo();
    //     //dump($usuariorol);die;
    //     //dump((int)$idlugarusuario);die;
    //      /**tareas devuelta por condicion**/
    //     $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
    //             ->select('fp.id,wf.condicion')
    //             ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
    //             ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
    //             ->getQuery()
    //             ->getResult();
    //     /**tarea devuelta**/
    //     $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
    //             ->select('fp.id')
    //             ->where('fp.tareaSigId =' . $tarea_actual)
    //             ->getQuery()
    //             ->getResult();
    //     /**tarea anterior**/
    //     $tarea = 'td.flujo_proceso_id='. $tarea_ant;
    //     if($wftareac and $fp){
    //         $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."') or td.flujo_proceso_id=". $fp[0]['id']. ")";
    //     }elseif ($wftareac){
    //         $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."'))";
    //     }elseif ($fp){
    //         $tarea = "(" . $tarea . " or td.flujo_proceso_id=". $fp[0]['id']. ")";
    //     }
    //     //dump($wftareac);die;
    //     /**si la tarea anterior tiene evaluacion **/
    //     $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
    //     $query1->execute();
    //     $evaluacion = $query1->fetchAll();
    //     if($rol == 7){ // departamental
    //         if ($evaluacion)
    //         {
                
    //             $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
    //             from
    //             (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
    //             from ttec_institucioneducativa_sede se
    //             join institucioneducativa ie on se.institucioneducativa_id=ie.id
    //             join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join tramite t on ie.id=t.institucioneducativa_id
    //             where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='". (int)$idlugarusuario ."' and lugar_nivel_id=1))))))ie
    //             left join
    //             (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t
    //             join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . "))t on ie.institucioneducativa_id=t.institucioneducativa_id");
    //         }else{
    //             $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
    //             from
    //             (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
    //             from ttec_institucioneducativa_sede se
    //             join institucioneducativa ie on se.institucioneducativa_id=ie.id
    //             join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join tramite t on ie.id=t.institucioneducativa_id
    //             where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='". (int)$idlugarusuario ."' and lugar_nivel_id=1))))))ie
    //             left join
    //             (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t
    //             join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea .")t on ie.institucioneducativa_id=t.institucioneducativa_id");
    //         }
    //     }elseif($rol == 8){ 
    //         if ($evaluacion)
    //         {
                
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
    //             left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
    //             left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
    //             left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
    //             left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
    //         }else{
    //             $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
    //             from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
    //             join institucioneducativa ie on t.institucioneducativa_id=ie.id
    //             join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
    //             left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
    //             left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
    //             left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
    //             left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
    //             left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
    //             join tramite_tipo tt on t.tramite_tipo=tt.id
    //             join usuario u on td.usuario_remitente_id=u.id
    //             join persona p on p.id=u.persona_id
    //             where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea);
    //         }
    //     }
    //     $query->execute();
    //     $tramites = $query->fetchAll();
    //     //dump($tramites);die;
    //     $data['tramites'] = $tramites;
    //     return $data;
    // }
    
    // public function guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id)
    // {

    //     //dump($datos);die;
    //     $tramiteDetalle = new TramiteDetalle();
    //     $em = $this->getDoctrine()->getManager();
    //     $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
    //     $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        
    //     $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
    //     $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(1);
        
    //     //insert tramite
    //     if($flujoproceso->getOrden() == 1 and $idtramite == ""){
            
    //         $tramite = new Tramite();
    //         $wfSolicitudTramite = new WfSolicitudTramite();
    //         $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
    //         $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
    //         $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
    //         //dump($tramitetipo);die;
    //         $tramite->setFlujoTipo($flujotipo);
    //         $tramite->setTramiteTipo($tramitetipo);
    //         $tramite->setFechaTramite(new \DateTime(date('Y-m-d')));
    //         $tramite->setFechaRegistro(new \DateTime(date('Y-m-d')));
    //         $tramite->setEsactivo(true);
    //         $tramite->setGestionId((new \DateTime())->format('Y'));
            
    //         switch ($tabla) {
    //             case 'institucioneducativa':
    //                 if ($id_tabla){
    //                     $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
    //                     $tramite->setInstitucioneducativa($institucioneducativa);
    //                 }
    //                 break;
    //             case 'estudiante_inscripcion':
    //                 $estudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id_tabla);
    //                 $tramite->setestudianteInscripcion($estudiante);
    //                 break;
    //             case 'apoderado_inscripcion':
    //                 $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($id_tabla);
    //                 $tramite->setApoderadoInscripcion($apoderado);
    //                 break;
    //             case 'maestro_inscripcion':
    //                 $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id_tabla);
    //                 $tramite->setMaestroInscripcion($maestro);
    //                 break;
    //         }
    //         $em->persist($tramite);
    //         $em->flush();
    //         $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();
    //         //dump($tramite);die;
    //         if ($datos){
    //             //datos propios de la solicitud
    //             $wfSolicitudTramite->setTramite($tramite);
    //             $wfSolicitudTramite->setDatos($datos);
    //             $wfSolicitudTramite->setEsValido(true);
    //             $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
    //             $wfSolicitudTramite->setLugarTipoId($lugarTipo_id);
    //             $em->persist($wfSolicitudTramite);
    //             $em->flush();
    //         }
    //     }else{
    //         /*$query = $em->getConnection()->prepare('select * from tramite_detalle where flujo_proceso_id='. $flujoproceso->getTareaAntId());
    //         $query->execute();
    //         $tramiteD = $query->fetchAll();*/
    //         //dump($idtramite);die;
    //         //Modificacion de datos propios de la solicitud
    //         $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);

    //     }
    //     //insert tramite_detalle
    //     //dump($tramiteD);die;
    //     $tramiteDetalle->setObs($observacion);
    //     $tramiteDetalle->setTramite($tramite);
    //     $tramiteDetalle->setTramiteEstado($tramiteestado);
    //     $tramiteDetalle->setFlujoProceso($flujoproceso);
    //     $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
    //     $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
    //     $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
    //     $tramiteDetalle->setUsuarioRemitente($usuario);
    //     /** */
    //     if ($idtramite!="")
    //     {
    //         $td_anterior = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
    //         $tramiteDetalle->setTramiteDetalle($td_anterior);
    //     }
    //     //dump($flujoproceso);die;
    //     if ($flujoproceso->getEsEvaluacion() == true) 
    //     {
    //         $tramiteDetalle->setValorEvaluacion($varevaluacion);
    //     }
    //     if($flujoproceso->getWfAsignacionTareaTipo()->getId() == 3) //asignacion por seleccion
    //     {
    //            if($idtramite != "")
    //            {
    //                 $query = $em->getConnection()->prepare('select * from tramite_detalle where id='. (int)$tramite->getTramite().' and tramite_id='.$idtramite);
    //                 $query->execute();
    //                 $td = $query->fetchAll();
    //                 $tramiteD = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['id']);
    //                 $tramiteD->setUsuarioDestinatario($usuario);
    //                 //$em->persist($tramiteD);
    //                 $em->flush();
    //            }
    //     }else{ //si es directa o randomica
    //         //dump($uDestinatario);die;
    //         $uDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->find($uDestinatario);
    //         //dump($uDestinatario);die;
    //         $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
    //     }
    //     $em->persist($tramiteDetalle);
    //     $em->flush();
    //     if ($flujoproceso->getTareaSigId() == null)
    //     {
    //         $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
    //     }
    //     $tramite->setTramite($tramiteDetalle->getId());
    //     //$em->persist($tramite);
    //     $em->flush();
    //     //dump((new \DateTime())->format('Y'));die;
    //     //guardar datos del propios del tramite
    //     $mensaje = 'El trámite se guardo correctamente';
    //     return $mensaje;
    // }
    
    // public function buscarSolicitudAction(Request $request)
    // {
    //     //dump($request);die;        
    //     $idlugarusuario = $this->session->get('roluserlugarid');
    //     $codigo = $request->get('codigo');
    //     $em = $this->getDoctrine()->getManager();
    //     $tramite_tipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($request->get('tramite_tipo'));        
    //     $tramite = $request->get('tramite');      
        
    //     $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo'=>$codigo,'estado'=>false));  
    //     //dump(json_decode($solicitudTramite->getDatos(),true),$tramite_tipo);die;
    //     $datos = "";
    //     if($solicitudTramite){
    //         $datos = json_decode($solicitudTramite->getDatos(),true);
    //         //dump($datos['tramites'][0]['id'],$tramite_tipo->getId());die;
    //         if($datos['tramites'][0]['id'] == $tramite_tipo->getId()){
    //             $data = array(
    //                 'datos'=>$datos,
    //                 'tramite_tipo'=>$tramite_tipo,
    //                 'codigo' =>$codigo,
    //                 'gestion'=>$solicitudTramite->getFechaRegistro()->format('Y'),
    //             );    
    //         }else{
    //             $data = array(
    //                 'datos'=>null,
    //                 'tramite_tipo'=>$tramite_tipo,
    //                 'codigo' =>$codigo,
    //             );
    //         }            
    //     }else{
    //         $data = array(
    //             'datos'=>null,
    //             'tramite_tipo'=>$tramite_tipo,
    //             'codigo' =>$codigo,
    //         );

    //         if(isset($tramite)){
    //             $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->findBy(array('tramite'=>$tramite), array('id' => 'DESC'));
    //             $solicitudTramite = $em->getRepository('SieAppWebBundle:SolicitudTramite')->findOneBy(array('codigo'=>$codigo,'estado'=>true));
    //             if(count($tramiteDetalle) > 0 && count($solicitudTramite)){
    //                 if($tramiteDetalle[0]->getFlujoProceso()->getId() == 51){
    //                     $datos = json_decode($solicitudTramite->getDatos(),true);
    //                     //dump($datos['tramites'][0]['id'],$tramite_tipo->getId());die;
    //                     if($datos['tramites'][0]['id'] == $tramite_tipo->getId()){
    //                         $data = array(
    //                             'datos'=>$datos,
    //                             'tramite_tipo'=>$tramite_tipo,
    //                             'codigo' =>$codigo,
    //                             'gestion'=>$solicitudTramite->getFechaRegistro()->format('Y'),
    //                         );    
    //                     }     
    //                 }
    //             }
    //         }    
    //     }
    //    // dump();die;        
    //     return $this->render('SieProcesosBundle:TramiteRue:solicitudAperturaReapertura.html.twig', $data);

    // }

    

    // public function obtieneDatosApertura($tramitetipo,$form,$files,$codigo){
    //     $em = $this->getDoctrine()->getManager();
    //     $gestion = date('Y');
    //     $ruta = '/../web/uploads/archivos/flujos/rue/solicitud/'.$gestion.'/'.$codigo.'/';
    //     $datos = array();
    //     $datos[$tramitetipo]['institucioneducativa']=trim(mb_strtoupper($form['institucioneducativa'],'utf-8'));
    //     $datos[$tramitetipo]['fechafundacion']=$form['fechafundacion'];
    //     $datos[$tramitetipo]['telefono']=$form['telefono'];
    //     $datos[$tramitetipo]['director']=trim(mb_strtoupper($form['director'],'utf-8'));
    //     if($form['siecomparte']){
    //         $datos[$tramitetipo]['siecomparte']=$form['siecomparte'];
    //     }
    //     if($form['lejurisdiccion']){
    //         $datos[$tramitetipo]['lejurisdiccion']=$form['lejurisdiccion'];
    //     }
    //     $datos[$tramitetipo]['zona']=trim(mb_strtoupper($form['zona'],'utf-8'));
    //     $datos[$tramitetipo]['direccion']=trim(mb_strtoupper($form['direccion'],'utf-8'));
    //     $datos[$tramitetipo]['iddistrito']=$form['distrito'];
    //     $datos[$tramitetipo]['distrito']=$em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['distrito'])->getDistrito();
    //     $datos[$tramitetipo]['iddepartamento2001']=$form['departamento2001'];
    //     $datos[$tramitetipo]['departamento2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2001'])->getLugar();
    //     $datos[$tramitetipo]['idprovincia2001']=$form['provincia2001'];
    //     $datos[$tramitetipo]['provincia2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2001'])->getLugar();
    //     $datos[$tramitetipo]['idmunicipio2001']=$form['municipio2001'];
    //     $datos[$tramitetipo]['municipio2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2001'])->getLugar();
    //     $datos[$tramitetipo]['idcanton2001']=$form['canton2001'];
    //     $datos[$tramitetipo]['canton2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton2001'])->getLugar();
    //     $datos[$tramitetipo]['idlocalidad2001']=$form['localidad2001'];
    //     $datos[$tramitetipo]['localidad2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad2001'])->getLugar();
    //     $datos[$tramitetipo]['iddepartamento2012']=$form['departamento2012'];
    //     $datos[$tramitetipo]['departamento2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2012'])->getLugar();
    //     $datos[$tramitetipo]['idprovincia2012']=$form['provincia2012'];
    //     $datos[$tramitetipo]['provincia2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2012'])->getLugar();
    //     $datos[$tramitetipo]['idmunicipio2012']=$form['municipio2012'];
    //     $datos[$tramitetipo]['municipio2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2012'])->getLugar();
    //     $datos[$tramitetipo]['idcomunidad2012']=$form['comunidad2012'];
    //     $datos[$tramitetipo]['comunidad2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad2012'])->getLugar();                 
    //     $datos[$tramitetipo]['latitud']=$form['latitud'];
    //     $datos[$tramitetipo]['longitud']=$form['longitud'];
    //     $datos[$tramitetipo]['dependencia']=array('id'=>$form['dependencia'],'dependencia'=>$em->getRepository('SieAppWebBundle:DependenciaTipo')->find($form['dependencia'])->getDependencia());
    //     if($form['dependencia'] == 2){
    //         $datos[$tramitetipo]['conveniotipo']=array('id'=>$form['conveniotipo'],'convenio'=>$em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo'])->getConvenio());
    //     }
    //     if($form['dependencia'] == 3){
    //         //$datos[$tramitetipo]['constitucion']=array('id'=>$form['constitucion'],'constitucion'=>$this->obtieneConstitucion($form['constitucion']));
    //         $datos[$tramitetipo]['constitucion']=array('id'=>$form['constitucion'],'constitucion'=>$em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['constitucion'])->getConvenio());
    //     }
    //     //dump($datos);die;
    //     $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
    //         ->select('nt.id,nt.nivel')
    //         ->where('nt.id in (:id)')
    //         ->orderBy('nt.id')
    //         ->setParameter('id',$form['niveltipo'])
    //         ->getQuery()
    //         ->getResult();
    //     $datos[$tramitetipo]['niveltipo']=$nivel;
    //     if(in_array(11,$form['niveltipo'])){
    //         $datos[$tramitetipo]['cantidad_11_1']=$form['cantidad_11_1'];
    //         $datos[$tramitetipo]['cantidad_11_2']=$form['cantidad_11_2'];
    //     }
    //     if(in_array(12,$form['niveltipo'])){
    //         $datos[$tramitetipo]['cantidad_12_1']=$form['cantidad_12_1'];
    //         $datos[$tramitetipo]['cantidad_12_2']=$form['cantidad_12_2'];
    //         $datos[$tramitetipo]['cantidad_12_3']=$form['cantidad_12_3'];
    //         $datos[$tramitetipo]['cantidad_12_4']=$form['cantidad_12_4'];
    //         $datos[$tramitetipo]['cantidad_12_5']=$form['cantidad_12_5'];
    //         $datos[$tramitetipo]['cantidad_12_6']=$form['cantidad_12_6'];
    //     }
    //     if(in_array(13,$form['niveltipo'])){
    //         $datos[$tramitetipo]['cantidad_13_1']=$form['cantidad_13_1'];
    //         $datos[$tramitetipo]['cantidad_13_2']=$form['cantidad_13_2'];
    //         $datos[$tramitetipo]['cantidad_13_3']=$form['cantidad_13_3'];
    //         $datos[$tramitetipo]['cantidad_13_4']=$form['cantidad_13_4'];
    //         $datos[$tramitetipo]['cantidad_13_5']=$form['cantidad_13_5'];
    //         $datos[$tramitetipo]['cantidad_13_6']=$form['cantidad_13_6'];
    //     }
    //     $datos[$tramitetipo]['cantidad_adm']=$form['cantidad_adm'];
    //     $datos[$tramitetipo]['cantidad_maestro']=$form['cantidad_maestro'];

    //     $adjunto = $this->upload($files['i_solicitud_apertura'],$ruta);
    //     if($adjunto == ''){
    //         $error_upload = true;
    //     }
    //     $datos[$tramitetipo]['i_solicitud_apertura']=$adjunto;

    //     if($form['dependencia'] == 1){
            
    //         $adjunto = $this->upload($files['i_actafundacion_apertura'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos[$tramitetipo]['i_actafundacion_apertura']=$adjunto;

    //         $datos[$tramitetipo]['i_folio_apertura']=$form['i_folio_apertura'];
    //         $datos[$tramitetipo]['i_certificacion_apertura']=$form['i_certificacion_apertura'];
    //         $datos[$tramitetipo]['i_area_apertura']=$form['i_area_apertura'];
    //         $datos[$tramitetipo]['i_compromiso_apertura']=$form['i_compromiso_apertura'];
    //     }
    //     if($form['dependencia'] == 2){
    //         $datos[$tramitetipo]['i_representante_apertura']=$form['i_representante_apertura'];

    //         $adjunto = $this->upload($files['i_actafundacion_apertura'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos[$tramitetipo]['i_actafundacion_apertura']=$adjunto;

    //         $datos[$tramitetipo]['i_folio_apertura']=$form['i_folio_apertura'];
    //         $datos[$tramitetipo]['i_convenio_apertura']=$form['i_convenio_apertura'];
    //         $datos[$tramitetipo]['i_convenioadministracion_apertura']=isset($form['i_convenioadministracion_apertura'])?$form['i_convenioadministracion_apertura']:0;
    //         $datos[$tramitetipo]['i_certificacion_apertura']=$form['i_certificacion_apertura'];
    //         $datos[$tramitetipo]['i_area_apertura']=$form['i_area_apertura'];
    //         $datos[$tramitetipo]['i_constitucion_apertura']=$form['i_constitucion_apertura'];
    //         $datos[$tramitetipo]['i_registro_culto_apertura']=isset($form['i_registro_culto_apertura'])?$form['i_registro_culto_apertura']:0;
    //         $datos[$tramitetipo]['i_org_nogubernamental_apertura']=isset($form['i_org_nogubernamental_apertura'])?$form['i_org_nogubernamental_apertura']:0;
    //         $datos[$tramitetipo]['i_form_fundaempresa_apertura']=isset($form['i_form_fundaempresa_apertura'])?$form['i_form_fundaempresa_apertura']:0;
    //         if(isset($form['i_form_fundaempresa_apertura'])){
    //             $datos[$tramitetipo]['nro_fundaempresa_apertura']=$form['nro_fundaempresa_apertura'];
    //             $datos[$tramitetipo]['fecha_fundaempresa_apertura']=$form['fecha_fundaempresa_apertura'];
    //         }                        
    //         $datos[$tramitetipo]['i_fotocopia_nit_apertura']=isset($form['i_fotocopia_nit_apertura'])?$form['i_fotocopia_nit_apertura']:0;
    //         if(isset($form['i_fotocopia_nit_apertura'])){
    //             $datos[$tramitetipo]['nit_apertura']=$form['nit_apertura'];
    //             $datos[$tramitetipo]['i_balance_apertura']=$form['i_balance_apertura'];
    //         }                        
    //         $datos[$tramitetipo]['i_testimonioconvenio']=$form['i_testimonioconvenio'];
    //     }
    //     if($form['dependencia'] == 3){                        
    //         if($form['constitucion']!=48){
    //             $datos[$tramitetipo]['i_personeria_apertura']=$form['i_personeria_apertura'];
    //         }
    //         if($form['constitucion']==48){
    //             $adjunto = $this->upload($files['i_afcoop_apertura'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos[$tramitetipo]['i_afcoop_apertura']=$adjunto;
    //         }                        
    //         $datos[$tramitetipo]['i_fotocopia_nit_apertura']=$form['i_fotocopia_nit_apertura'];
    //         $datos[$tramitetipo]['nit_apertura']=$form['nit_apertura'];
    //         $datos[$tramitetipo]['i_balance_apertura']=isset($form['i_balance_apertura'])?$form['i_balance_apertura']:0;
    //         $datos[$tramitetipo]['i_representante_apertura']=$form['i_representante_apertura'];
    //         $datos[$tramitetipo]['i_copia_ci_apertura']=$form['i_copia_ci_apertura'];
    //         $datos[$tramitetipo]['ci_apertura']=$form['ci_apertura'];

    //         $adjunto = $this->upload($files['i_funcionamiento_apertura'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos[$tramitetipo]['i_funcionamiento_apertura']=$adjunto;

    //         $datos[$tramitetipo]['i_estatutos_apertura']=$form['i_estatutos_apertura'];
    //         if(($form['constitucion']==45 or $form['constitucion']==49) and isset($files['i_certificacionculto_apertura'])){

    //             $adjunto = $this->upload($files['i_certificacionculto_apertura'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos[$tramitetipo]['i_certificacionculto_apertura']=$adjunto;
    //         }
    //         if($form['constitucion']==46 or $form['constitucion']==47 or $form['constitucion']==49){
    //             $datos[$tramitetipo]['i_form_fundaempresa_apertura']=$form['i_form_fundaempresa_apertura'];
    //             $datos[$tramitetipo]['nro_fundaempresa_apertura']=$form['nro_fundaempresa_apertura'];
    //             $datos[$tramitetipo]['fecha_fundaempresa_apertura']=$form['fecha_fundaempresa_apertura'];
    //         }

    //         $adjunto = $this->upload($files['i_empleadores_apertura'],$ruta);
    //         if($adjunto == ''){
    //             $error_upload = true;
    //         }
    //         $datos[$tramitetipo]['i_empleadores_apertura']=$adjunto;

    //         if($form['constitucion']==49){
    //             $datos[$tramitetipo]['i_convenio_apertura']=$form['i_convenio_apertura'];
    //         }                        
    //         $datos[$tramitetipo]['ii_alquiler_apertura']=$form['ii_alquiler_apertura'];
    //         if($form['ii_alquiler_apertura']=='SI'){

    //             $adjunto = $this->upload($files['ii_contrato_apertura'],$ruta);
    //             if($adjunto == ''){
    //                 $error_upload = true;
    //             }
    //             $datos[$tramitetipo]['ii_contrato_apertura']=$adjunto;
    //         }else{
    //             $datos[$tramitetipo]['ii_folio_apertura']=$form['ii_folio_apertura'];
    //         }
    //         $datos[$tramitetipo]['iii_reglamento_apertura']=$form['iii_reglamento_apertura'];
    //         $datos[$tramitetipo]['iii_convivencia_apertura']=$form['iii_convivencia_apertura'];
    //         $datos[$tramitetipo]['iii_manual_apertura']=$form['iii_manual_apertura'];
    //         $datos[$tramitetipo]['iii_kardex_apertura']=$form['iii_kardex_apertura'];
    //         $datos[$tramitetipo]['iii_sippase_apertura']=$form['iii_sippase_apertura'];
    //         $datos[$tramitetipo]['iii_contratos_apertura']=$form['iii_contratos_apertura'];
    //     }
    //     $datos[$tramitetipo]['ii_inventario_apertura']=$form['ii_inventario_apertura'];
    //     $datos[$tramitetipo]['ii_planos_apertura']=$form['ii_planos_apertura'];
    //     $datos[$tramitetipo]['iii_poa_apertura']=$form['iii_poa_apertura'];
    //     return $datos;
    // }

    // public function reaperturaAction(Request $request){

    //     $form = $this->createFormBuilder()
    //         ->setAction($this->generateUrl('tramite_rue_reapertura_guardar'))
    //         ->add('sie', 'text', array('label'=>'Código SIE:','required'=>true,'attr'=>array('class'=>'form-control validar','data-placeholder'=>"")))
    //         ->add('buscar', 'button', array('attr'=>array('class'=>'btn btn-primary','onclick'=>'buscarSieReapertura()')))
    //         ->getForm();

    //     return $this->render('SieProcesosBundle:TramiteRue:reapertura.html.twig', array(
    //         'form' => $form->createView(),
    //     ));    
    // }

    // public function buscarSieReaperturaAction(Request $request)
    // {
    //     $sie = $request->get('sie');
    //     $em = $this->getDoctrine()->getManager();

    //     $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
    //         ->select('ie')
    //         ->where('ie.id = :id')
    //         ->andWhere('ie.estadoinstitucionTipo = 19')
    //         ->andWhere('ie.institucioneducativaTipo = 1')
    //         ->andWhere("ie.nroResolucion <>'' ")
    //         ->andWhere("ie.obsRue not like '%definitiv%'")
    //         ->andWhere("ie.obsRue not like '%DEFINITIV%'")
    //         ->setParameter('id',$sie)
    //         ->getQuery()
    //         ->getResult();
    //     //dump($institucioneducativa);die;
    //     if($institucioneducativa){
    //         $lugar_tipo2012 = $institucioneducativa[0]->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()?$em->getRepository('SieAppWebBundle:LugarTipo')->find($institucioneducativa[0]->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()):null;
    //         $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$sie));
    //         $tramite_tipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find(44);
    //         $form = $this->createFormBuilder()
    //             ->add('observacion','textarea',array('label'=>'Justificación:','required'=>true,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
    //             ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
    //             ->getForm();
    //         $data = array(
    //             'form'=>$form->createView(),
    //             'institucioneducativa'=>$institucioneducativa[0],
    //             'ieNivel'=>$institucioneducativaNivel,
    //             'lugarTipo2012'=>$lugar_tipo2012,
    //             'tramite_tipo'=>$tramite_tipo,
    //         );
            
    //         return $this->render('SieProcesosBundle:TramiteRue:datosReapertura.html.twig', $data);
    //     }else{
    //         $response = new JsonResponse();
    //         $data = array(
    //             'msg'=>'Código SIE incorrecto.'
    //         );
    //         $response->setData($data);

    //         return $response;

    //     }      
    // }

    // public function reaperturaGuardarAction(Request $request){
    //     //variable de control para el cargado de adjunto
    //     $error_upload = false;
    //     $this->session = $request->getSession();
    //     $form = $request->get('form');
    //     $files = $request->files->get('form');
    //     //dump($form,$files);die;
    //     $em = $this->getDoctrine()->getManager();
    //     /**
    //      * datos propios de la solicitud del formulario rue
    //      */
    //     $query = $em->getConnection()->prepare('SELECT ie.id,ie.institucioneducativa,ie.area_municipio,ie.fecha_fundacion,ie.le_juridicciongeografica_id,ie.estadoinstitucion_tipo_id,et.estadoinstitucion,ie.dependencia_tipo_id,dt.dependencia,ie.convenio_tipo_id,ct.convenio
    //             FROM institucioneducativa ie
    //             join estadoinstitucion_tipo et on ie.estadoinstitucion_tipo_id=et.id
    //             join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
    //             join convenio_tipo ct on ct.id=ie.convenio_tipo_id
    //             and ie.id='. $form['sie']);
    //             $query->execute();
    //     $institucioneducativa = $query->fetchAll();

    //     $query = $em->getConnection()->prepare('SELECT nt.id,nt.nivel
    //             FROM institucioneducativa_nivel_autorizado ien
    //             join nivel_tipo nt on ien.nivel_tipo_id = nt.id
    //             WHERE ien.institucioneducativa_id='. $form['sie']);
    //             $query->execute();
    //     $ieNivelAutorizado = $query->fetchAll();
    //     $query = $em->getConnection()->prepare('SELECT le.id,le.zona,le.direccion,le.distrito_tipo_id,dt.distrito,
    //             lt.id as localidad2001_id,lt.lugar as localidad2001,lt1.id as canton2001_id,lt1.lugar as canton2001,lt2.id as municipio2001_id,lt2.lugar as municipio2001,lt3.id as provincia2001_id,lt3.lugar as provincia2001,lt4.id as departamento2001_id,lt4.lugar as departamento2001,lt.area2001,
    //             lt5.id as comunidad2012_id,lt5.lugar as comunidad2012,lt6.id as municipio2012_id,lt6.lugar as municipio2012,lt7.id as provincia2012_id,lt7.lugar as provincia2012,lt8.id as departamento2012_id,lt8.lugar as departamento2012
    //             FROM jurisdiccion_geografica le
    //             join distrito_tipo dt on dt.id=le.distrito_tipo_id
    //             join lugar_tipo lt on lt.id=le.lugar_tipo_id_localidad
    //             join lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
    //             join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
    //             join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
    //             join lugar_tipo lt4 on lt4.id=lt3.lugar_tipo_id
    //             left join lugar_tipo lt5 on lt5.id=le.lugar_tipo_id_localidad2012
    //             left join lugar_tipo lt6 on lt6.id=lt5.lugar_tipo_id
    //             left join lugar_tipo lt7 on lt7.id=lt6.lugar_tipo_id
    //             left join lugar_tipo lt8 on lt8.id=lt7.lugar_tipo_id
    //             WHERE le.id='. $institucioneducativa[0]['le_juridicciongeografica_id']);
    //             $query->execute();
    //     $le = $query->fetchAll();
        
    //     $datos = array();
    //     $datos['institucioneducativa']=$institucioneducativa[0];
    //     $datos['jurisdiccion_geografica']=$le[0];
    //     $datos['institucioneducativaNivel']=$ieNivelAutorizado;
    //     $tramites = $em->getRepository('SieAppWebBundle:TramiteTipo')->createQueryBuilder('tt')
    //         ->select('tt.id,tt.tramiteTipo as tramite_tipo')
    //         ->where('tt.id in (:id)')
    //         ->setParameter('id',44)
    //         ->getQuery()
    //         ->getResult();
    //     //dump($tramites);die;  
    //     $datos['tramites'] = $tramites;
    //     $datos['justificacion'] = trim(mb_strtoupper($form['observacion'], 'utf-8'));
    //     //dump($datos);die;
    //     $gestion = $this->session->get('currentyear');
    //     $ruta = '/../web/uploads/archivos/flujos/'.$form['sie'].'/rue/'.$gestion.'/';

    //     $adjunto = $this->upload($files['i_solicitud_reapertura'],$ruta);
    //     if($adjunto == ''){
    //         $error_upload = true;
    //     }
    //     $datos[$tramites[0]['tramite_tipo']]['i_solicitud_reapertura']=$adjunto;

    //     $datos[$tramites[0]['tramite_tipo']]['estadoinstitucion_id']=10;
    //     $datos[$tramites[0]['tramite_tipo']]['estadoinstitucion']='Abierta';
    //     //dump($datos);die;

    //     $em->getConnection()->beginTransaction();
    //     try{
    //         $solicitudTramite = new Solicitudtramite();
    //         //$solicitudTramite->setDatos($datos);
    //         $solicitudTramite->setFechaRegistro(new \DateTime('now'));
    //         $solicitudTramite->setEstado(false);
    //         $solicitudTramite->setDatos(json_encode($datos));
    //         $em->persist($solicitudTramite);
    //         $em->flush();
    //         $codigo = date('Ymd') . str_pad($solicitudTramite->getId(), 4, "0", STR_PAD_LEFT);
    //         $solicitudTramite->setCodigo($codigo);
    //         $em->flush();
    //         //dump($solicitudTramite);die;
    //         //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
    //         $idsolicitud = $solicitudTramite->getId();
    //         $em->getConnection()->commit();

    //         $query1 = $em->getConnection()->prepare("select 
    //             'CODIGO_SOLICITUD:_'||cast(codigo as varchar)||'__'||
    //             'CODIGO_RUE:_'||cast(wf.datos::json->'institucioneducativa'->>'id' as varchar)||'__'||
    //             'EDIFICIO_EDUCATIVO:_'||cast(wf.datos::json->'institucioneducativa'->>'le_juridicciongeografica_id' as varchar) as qr
    //             from solicitud_tramite wf
    //             where id=". $idsolicitud);
        
    //         $query1->execute();
    //         $qr = $query1->fetchAll();
    //         //dump($qr);die;
    //         $lk = $qr[0]['qr'];
    //         $file = 'rue_iniciosolicitudReapertura_v2_pvc.rptdesign';    
    //         $arch = 'FORMULARIO_'.$idsolicitud.'_' . date('YmdHis') . '.pdf';
    //         $response = new Response();
    //         $response->headers->set('Content-type', 'application/pdf');
    //         $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    //         $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idsolicitud='.$idsolicitud.'&lk='. $lk .'&&__format=pdf&'));
    //         //dump($this->container->getParameter('urlreportweb') . $file . '&idsolicitud='.$idsolicitud.'&lk='. $lk .'&&__format=pdf&');die;
    //         $response->setStatusCode(200);
    //         $response->headers->set('Content-Transfer-Encoding', 'binary');
    //         $response->headers->set('Pragma', 'no-cache');
    //         $response->headers->set('Expires', '0');
    //         return $response;
    //     }catch (\Exception $ex) {
    //         $em->getConnection()->rollback();            
    //         $request->getSession()
    //             ->getFlashBag()
    //             ->add('error', 'error '. $ex);
    //         return $this->redirectToRoute('tramite_rue_reapertura');
    //     }

    // }
}