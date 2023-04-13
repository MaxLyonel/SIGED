<?php

namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\TtecAreaFormacionTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaAreaFormacionAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaSede;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistoricoDetalle;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaRatificacion;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Sie\RieBundle\Models\Document;

/**
 * Institucioneducativa controller.
 *
 */
class DatoHistoricoController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    /**
     * Index
     */
    public function indexAction(Request $request) {

    }

    /**
     * Muestra listado de institutos técnicos/tecnológicos
     */    
    public function listittAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');        
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT se
                                     FROM SieAppWebBundle:TtecInstitucioneducativaSede se
                                     JOIN se.institucioneducativa ie 
                                    WHERE ie.institucioneducativaTipo in (:idTipo)
                                      AND ie.estadoinstitucionTipo in (:idEstado)
                                      AND se.estado = :estadoSede
                                ORDER BY ie.id ')
                                    ->setParameter('idTipo', array(7, 8, 9,11,12,13))
                                    ->setParameter('idEstado', 10)
                                    ->setParameter('estadoSede', TRUE);        
        $entities = $query->getResult(); 
        //dump($entities);die;

        return $this->render('SieRieBundle:DatoHistorico:listitt.html.twig', array('entities' => $entities));
    }

    /**
     * Muestra listado de historicos del instituto
     */    
    public function listAntesAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $id_lugar = $sesion->get('roluserlugarid');
        $em = $this->getDoctrine()->getManager();

        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($id_lugar);
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager(); 
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $esAcreditado = $this->get('dgfunctions')->esAcreditadoRitt($request->get('idRie'));
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecInstitucioneducativaHistorico a 
                                     JOIN a.institucioneducativa b
                                    WHERE b.id = :idRie
                                 ORDER BY a.fechaResolucion DESC');                       
        $query->setParameter('idRie', $request->get('idRie'));
        $historicos = $query->getResult();         
        return $this->render('SieRieBundle:DatoHistorico:list.html.twig', array('entity' => $entity,'esAcreditado'=>$esAcreditado, 'historicos' => $historicos, 'lugarUsuario' => intval($lugar->getCodigo())));        
    }
    public function listAction(Request $request){
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $id_lugar = $sesion->get('roluserlugarid');
        $em = $this->getDoctrine()->getManager();

        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($id_lugar);
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $esAcreditado = $this->get('dgfunctions')->esAcreditadoRitt($request->get('idRie'));
        //, (select count(d.id) from SieAppWebBundle:TtecInstitucioneducativaHistoricoDetalle d where d.institucioneducativaHistorico=a.id ) as total
        $query = $em->createQuery('SELECT a , (select count(d.id) from SieAppWebBundle:TtecInstitucioneducativaHistoricoDetalle d where d.institucioneducativaHistorico=a.id ) as total
                                     FROM SieAppWebBundle:TtecInstitucioneducativaHistorico a 
                                     JOIN a.institucioneducativa b
                                    WHERE b.id = :idRie
                                 ORDER BY a.fechaResolucion DESC');                       
        $query->setParameter('idRie', $request->get('idRie'));
        $historicos = $query->getResult();         
        //dump($historicos); die;
        $documentosArray   = $this->obtieneTipoDocumentoArray(); 

        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('historico_create'))
                    ->add('idRie', 'hidden', array('data' => $entity->getId()))
                    ->add('documentos', 'choice', array('label' => 'Tipo de Documento', 'disabled' => false,'choices' => $documentosArray, 'attr' => array('class' => 'form-control')))
                    ->add('fechaResolucion', 'text', array('label' => 'Fecha de Resolución', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('nroResolucion', 'text', array('label' => 'Número de Resolución', 'required' => true, 'attr' => array('placeholder'=>'0000/YYYY', 'class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => '30', 'style' => 'text-transform:uppercase')))
                    ->add('descripcion', 'textarea', array('label' => 'Descripción', 'required' => true, 'attr' => array('class' => 'form-control', 'rows' => '2', 'cols' => '50')))                    
                    ->add('datoAdicional', 'text', array('label' => 'Dato Adicional(opcional)', 'required' => false ,'attr' => array('class' => 'form-control','maxlength' => '180')))
                    ->add('archivo', 'file', array('label' => 'Archivo PDF Adjunto (opcional Max 3MB)', 'required' => false, "attr" => array('accept' => 'application/pdf', 'multiple' => false)))
                    ->add('guardar', 'submit', array('label' => 'Guardar datos', 'attr' => array('class' => 'btn btn-primary')));

        return $this->render('SieRieBundle:DatoHistorico:list.html.twig', array('form' => $form->getForm()->createView(), 'entity' => $entity,'esAcreditado'=>$esAcreditado, 'historicos' => $historicos, 'lugarUsuario' => intval($lugar->getCodigo())));        
    }

    /**
     * Formulario de Nuevo Registro de Historial
     */ 
     public function newAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager(); 
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('historico_create'))
                    ->add('idRie', 'hidden', array('data' => $entity->getId()))
                    ->add('fechaResolucion', 'text', array('label' => 'Fecha de Resolución', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('nroResolucion', 'text', array('label' => 'Número de Resolución', 'required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => '30', 'style' => 'text-transform:uppercase')))
                    ->add('descripcion', 'textarea', array('label' => 'Descripción', 'required' => true, 'attr' => array('class' => 'form-control', 'rows' => '4', 'cols' => '50')))                    
                    ->add('datoAdicional', 'text', array('label' => 'Dato Adicional(opcional)', 'required' => false ,'attr' => array('class' => 'form-control','maxlength' => '180')))
                    ->add('archivo', 'file', array('label' => 'Archivo PDF Adjunto (opcional Max 3MB)', 'required' => false, "attr" => array('accept' => 'application/pdf', 'multiple' => false)))
                    ->add('vigencia', CheckboxType::class, array('label'=>'Es ratificación de vigencia','required' => false))
                    ->add('fechaInicio', 'text', array('label' => 'Fecha de Inicio', 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('fechaFin', 'text', array('label' => 'Fecha de Finalización', 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('guardar', 'submit', array('label' => 'Guardar datos', 'attr' => array('class' => 'btn btn-primary')));
        return $this->render('SieRieBundle:DatoHistorico:new.html.twig', array('entity' => $entity,  'form' => $form->getForm()->createView()));
    }

    /**
     * Guardar datos de historiales
     */
    public function createAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        $form = $request->get('form');
       
        $maximo = 3.5 * (1024 * 1024);
        $size = $_FILES['form']['size']['archivo'];
        //dump($_FILES,$size,$maximo);die;
        if($size > $maximo){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, el archivo adjunto pesa más de lo peritido, que son 3.5 megas.');
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));
        }

        $vigencia = 0;
        if(isset($form['vigencia'])){
            $vigencia = 1;
        }
        if($vigencia==1 && ($form['fechaInicio']=='' or $form['fechaFin']=='' )){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, debe ingresar las fechas de vigencia de la insitución.');
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));
        }
        try {
            
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $tipo_res = $form['documentos'];
            $nro_resolucion = mb_strtoupper($form['nroResolucion'], 'utf-8');

            // $nombre_pdf = $this->subirArchivo($request->files->get('form')['archivo']);
            $nombre_pdf = $this->upFileToServer($_FILES);
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_historico');");
            $query->execute();
            $historico = new TtecInstitucioneducativaHistorico();
            $historico->setInstitucioneducativa($entity);
            $historico->setNroResolucion($nro_resolucion); 
            $historico->setFechaResolucion(new \DateTime($form['fechaResolucion']));
            $historico->setDescripcion($form['descripcion']);
            $historico->setDatoAdicional(($form['datoAdicional'])?$form['datoAdicional']:NULL);
            $historico->setArchivo($nombre_pdf);
            $historico->setFechaRegistro(new \DateTime('now'));
            $em->persist($historico);
            $em->flush();
           /* $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
            if($vigencia==1 && $form['fechaInicio']!='' && $form['fechaFin']!=''){
                $ratificacion = new TtecInstitucioneducativaRatificacion();
                $ratificacion->setTtecInstitucioneducativaHistorico($em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($historico->getId()));
                $ratificacion->setFechaInicio(new \DateTime($form['fechaInicio']));
                $ratificacion->setFechaFin(new \DateTime($form['fechaFin']));
                $ratificacion->setUsuarioId($id_usuario);
                $ratificacion->setFechaRegistro(new \DateTime('now'));
                $ratificacion->setFechaModificacion(new \DateTime('now'));
                $em->persist($ratificacion);
                $em->flush();
            }*/

            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));

        }catch (Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el dato histórico');
            return $this->redirect($this->generateUrl('historico_new', array('idRie'=>$form['idRie'])));
        }
    }

    /**
     * Formulario de Editar Historial
     */ 
     public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager(); 
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $historial = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($request->get('id'));
        $ratificacion = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaRatificacion')->findOneBy(array('ttecInstitucioneducativaHistorico' => $historial));
        $vigencia = '';
        $fechaInicio = '';
        $fechaFin = '';
        if($ratificacion){
            $vigencia = 'checked';
            $fechaInicio = $ratificacion->getFechaInicio()->format('d-m-Y');
            $fechaFin = $ratificacion->getFechaFin()->format('d-m-Y');
        }
        //dump($ratificacion);die;
        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('historico_update'))
                    ->add('idRie', 'hidden', array('data' => $entity->getId()))
                    ->add('id', 'hidden', array('data' => $request->get('id')))
                    ->add('nroResolucion', 'text', array('label' => 'Número de Resolución', 'data' => $historial->getNroResolucion(), 'required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => '30', 'style' => 'text-transform:uppercase')))
                    ->add('fechaResolucion', 'text', array('label' => 'Fecha de Resolución', 'data' => $historial->getFechaResolucion()->format('d-m-Y'),  'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('descripcion', 'textarea', array('label' => 'Descripción', 'data' => $historial->getDescripcion(),  'required' => true, 'attr' => array('class' => 'form-control', 'rows' => '4', 'cols' => '50')))                    
                    ->add('datoAdicional', 'text', array('label' => 'Dato Adicional(opcional)' , 'data' => $historial->getDatoAdicional(), 'required' => false, 'attr' => array('class' => 'form-control','maxlength' => '180')))
                    ->add('archivo', 'file', array('label' => 'Archivo PDF Adjunto (opcional)', 'required' => false))
                    ->add('vigencia', CheckboxType::class, array('label'=>'Es ratificación de vigencia' , 'required' => false))
                    ->add('fechaInicio', 'text', array('label' => 'Fecha de Inicio', 'data' => $fechaInicio, 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('fechaFin', 'text', array('label' => 'Fecha de Finalización', 'data' => $fechaFin, 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        return $this->render('SieRieBundle:DatoHistorico:edit.html.twig', array('entity' => $entity, 'form' => $form->getForm()->createView(), 'adjunto' => $historial->getArchivo(), 'ratificacion' => $vigencia));
    }    

    /**
     * Guardar la modificación de datos historicos
     */
     public function updateAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        $form = $request->get('form');
        $maximo = 3.5 * (1024 * 1024);
        $size = $_FILES['form']['size']['archivo'];
        //dump($size,$maximo);die;
        if($size > $maximo){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, el archivo adjunto pesa más de lo peritido, que son 3.5 megas');
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));
        }
        $vigencia = 0;
        if(isset($form['vigencia'])){
            $vigencia = 1;
        }
        if($vigencia==1 && ($form['fechaInicio']=='' or $form['fechaFin']=='' )){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, debe ingresar las fechas de vigencia de la insitución.');
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));
        }
        

        try {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $historico = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($form['id']);

            $historico->setNroResolucion($form['nroResolucion']); 
            $historico->setFechaResolucion(new \DateTime($form['fechaResolucion']));
            $historico->setDescripcion($form['descripcion']);
            $historico->setDatoAdicional(($form['datoAdicional'])?$form['datoAdicional']:NULL);
            $historico->setFechaModificacion(new \DateTime('now'));
            //Validando el archivo
            if($request->files->get('form')['archivo']){
                //dump($this->get('kernel')->getRootDir());die;
                if($historico->getArchivo() != '' and is_readable($this->get('kernel')->getRootDir().'/../web/uploads/archivos/'.$historico->getArchivo())  ){
                    unlink($this->get('kernel')->getRootDir().'/../web/uploads/archivos/'.$historico->getArchivo());    
                }  
                // $nombre_pdf = $this->subirArchivo($request->files->get('form')['archivo']);
                $nombre_pdf = $this->upFileToServer($_FILES);
                $historico->setArchivo($nombre_pdf);
            }   
            $em->persist($historico);
            $em->flush();

            $ratificacion = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaRatificacion')->findOneBy(array('ttecInstitucioneducativaHistorico' => $historico));
            if(!$ratificacion && $vigencia==1){
                $ratificacion = new TtecInstitucioneducativaRatificacion();
                $ratificacion->setTtecInstitucioneducativaHistorico($em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($historico->getId()));
                $ratificacion->setFechaRegistro(new \DateTime('now'));
                $ratificacion->setUsuarioId($id_usuario);
                $ratificacion->setFechaInicio(new \DateTime($form['fechaInicio']));
                $ratificacion->setFechaFin(new \DateTime($form['fechaFin']));
                $em->persist($ratificacion);
            }
            if($ratificacion && $vigencia==1){
                $ratificacion->setFechaInicio(new \DateTime($form['fechaInicio']));
                $ratificacion->setFechaFin(new \DateTime($form['fechaFin']));
                $ratificacion->setFechaModificacion(new \DateTime('now'));
                $em->persist($ratificacion);
            }
            if($ratificacion && $vigencia==0){
                $em->remove($ratificacion);
            }
            $em->flush(); 
           
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$form['idRie'])));

        } catch (Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el dato histórico');
            return $this->redirect($this->generateUrl('historico_new', array('idRie'=>$form['idRie'])));
        }
    }


    /***
     * Eliminacion del registro histórico
     */
    public function deleteAction(Request $request){
        try{        
            $em = $this->getDoctrine()->getManager();
            $historico = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($request->get('idhistorico'));
            //if($historico->getArchivo() != '' and is_readable($this->get('kernel')->getRootDir().'/../web/uploads/archivos/'.$historico->getArchivo())){
            if($historico->getArchivo() != '' and is_readable($this->get('kernel')->getRootDir().'/../web/uploads/archivos/'.$historico->getArchivo())){
                // unlink('%kernel.root_dir%/../uploads/archivos/'.$historico->getArchivo());
                unlink($this->get('kernel')->getRootDir().'/../web/uploads/archivos/'.$historico->getArchivo());    
            }             
            $idRie = $historico->getInstitucioneducativa()->getId();
            $em->remove($historico);
            $em->flush(); 
            return $this->redirect($this->generateUrl('historico_list', array('idRie'=>$idRie)));   

        }catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al eliminar el dato histórico');
            return $this->redirect($this->generateUrl('historico_new', array('idRie'=>$idRie)));
        }                

    }

         /**
     * Formulario de Editar Historial
     */ 
    public function detalleAction(Request $request){
        
        $em = $this->getDoctrine()->getManager(); 
        
        $historico = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($request->get('idhistorico'));
        $detalleHistorico = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistoricoDetalle')->findBy(array('institucioneducativaHistorico' => $historico));
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($historico->getInstitucioneducativa()->getId());
        $areafArray        = $this->obtieneAreaFormacionArray($entity->getInstitucioneducativaTipo()->getId());
        $areformInstitucionArray = $this->obtieneInstitucionAreaFormArray($entity->getId());
        $tiposArray = $this->obtieneTipoFlujosResolucionArray();
        $dependenciasArray = $this->obtieneDependenciaArray();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('historico_detalle_create'))
        ->add('idhistorico', 'hidden', array('data' => $historico->getId()))
        ->add('tipos', 'choice', array('label' => 'Tipo de Solicitud', 'disabled' => false, 'choices'=>$tiposArray,  'multiple' => false, 'expanded' => false, 'attr' => array('class' => 'form-control')))
        ->add('fechaInicio', 'text', array('label' => 'Fecha de Inicio', 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('fechaFin', 'text', array('label' => 'Fecha de Finalización', 'required' => false, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('dependenciaTipo', 'choice', array('label' => 'Carácter Jurídico', 'required' => false, 'disabled' => false,'choices' => $dependenciasArray, 'attr' => array('class' => 'form-control')))
        ->add('leJuridicciongeograficaId', 'text', array('label' => 'Código LE','required' => false, 'attr' => array('listactplaceholder'=>'########', 'class' => 'form-control', 'pattern' => '[0-9]{8,17}', 'maxlength' => '8')))
        ->add('departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('canton', 'text', array('label' => 'Cantón', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('zona', 'text', array('label' => 'Zona', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('direccion', 'text', array('label' => 'Dirección', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('areaFormacionTipo', 'choice', array('label' => 'Area de Formación', 'choices'=>$areafArray,  'required' => false  , 'multiple' => true,'expanded' => true, 'data' => $areformInstitucionArray))
        ->add('guardar', 'submit', array('label' => 'Guardar datos', 'attr' => array('class' => 'btn btn-primary')));

        return $this->render('SieRieBundle:DatoHistorico:list_detalle.html.twig', array('entity' => $entity,'resolucion' => $historico,'detalle' => $detalleHistorico,  'form' => $form->getForm()->createView() ));
    }    

        /**
     * Guardar datos de historiales
     */
    public function createDetalleAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $historico = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($form['idhistorico']);
        
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($historico->getInstitucioneducativa()->getId());
        $tipo = $em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findOneById($form['tipos']);

        if($form['tipos']==2 && ($form['fechaInicio']=='' or $form['fechaFin']=='' )){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, debe ingresar las fechas de vigencia de la insitución.');
            return $this->redirect($this->generateUrl('historico_detalle', array('idhistorico'=>$form['idhistorico'])));
        }
        if($form['tipos']==6 && !$form['dependenciaTipo']){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, debe seleccionar el cambio de dependencia.');
            return $this->redirect($this->generateUrl('historico_detalle', array('idhistorico'=>$form['idhistorico'])));
        }
        if($form['tipos']==7 && !$form['leJuridicciongeograficaId']){
            $this->get('session')->getFlashBag()->add('mensaje', 'Error, debe ingresar el codigo de la nueva localizacion.');
            return $this->redirect($this->generateUrl('historico_detalle', array('idhistorico'=>$form['idhistorico'])));
        }


        try {
            
            $valor_anterior = '';
            $id_anterior = 0;
            $valor_nuevo = '';
            $id_nuevo = 0;

            if($form['tipos']==2){ //ratificacion de apertura de la
                $valor_anterior = $form['fechaInicio'];
                $valor_nuevo = $form['fechaFin'];
            }
            if($form['tipos']==6){ //cambio de caracter juridico
                $dependencia =  $em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']);
                $valor_anterior = $entity->getDependenciaTipo()->getDependencia();
                $id_anterior = $entity->getDependenciaTipo()->getId();
                $valor_nuevo = $dependencia->getDependencia();
                $id_nuevo = $dependencia->getId();
                
                $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById( $id_nuevo));
                $em->persist($entity);
                $em->flush();

            }
    
            if($form['tipos']==7){ //cambio de domicilio
                $valor_anterior = $entity->getLeJuridicciongeografica()->getId();
                $id_anterior = $entity->getLeJuridicciongeografica()->getId();
                $valor_nuevo = $form['leJuridicciongeograficaId'];
                $id_nuevo = $form['leJuridicciongeograficaId'];
               
                $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($id_nuevo));
                $em->persist($entity);
                $em->flush();
            }

            if($form['tipos']==3 || $form['tipos']==4 || $form['tipos']==5){ //cambio de domicilio
                $valor_anterior = 'ABIERTA';
                $id_anterior = 10;
                $valor_nuevo = $tipo->getNombre();
                $id_nuevo = $form['tipos'];

                $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(19));
                $em->persist($entity);
                $em->flush();
               

            }
             //elimina areas de formacion
             if($form['tipos']==8){ //areas de formacion
                $anterior = '';
                $x = 1;
                $areasFormElim = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
                if($areasFormElim){
                    
                    foreach($areasFormElim as $area){
                       // dump($area->getTtecAreaFormacionTipo()->getAreaFormacion());die;
                        //$anterior = $anterior.'_'.$area->getTtecAreaFormacionTipo()->getId();
                        $anterior = $anterior.' '.$x.'-'.$area->getTtecAreaFormacionTipo()->getAreaFormacion().' ';
                        $em->remove($area);
                        $x++;
                    }
                    $em->flush();
                    $valor_anterior =  $anterior;
                }
    
                $areas = (isset($form['areaFormacionTipo']))?$form['areaFormacionTipo']:array();
                $nuevo = '';
                for($i=0;$i<count($areas);$i++){
                    $darea = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($areas[$i]);
                    $areaf = new TtecInstitucioneducativaAreaFormacionAutorizado();
                    $areaf->setFechaRegistro(new \DateTime('now'));
                    $areaf->setInstitucioneducativa($entity);
                    $areaf->setTtecAreaFormacionTipo($darea); 
                    $ii =  $i+1;
                    $nuevo = $nuevo.' '.$ii.'-'.$darea->getAreaFormacion().' ';
                    $em->persist($areaf);
                }   
                $valor_nuevo =  $nuevo;
                $em->flush();
            }
            $historicod = new TtecInstitucioneducativaHistoricoDetalle();
            $historicod->setInstitucioneducativaHistorico($em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($form['idhistorico']));
            $historicod->setResolucionTipo($tipo);
            $historicod->setValorAnterior($valor_anterior);
            $historicod->setValorAnteriorId($id_anterior);
            $historicod->setValorNuevo($valor_nuevo);
            $historicod->setValorNuevoId($id_nuevo);
            $historicod->setFechaRegistro(new \DateTime('now'));
            $historicod->setFechaModificacion(new \DateTime('now'));
            $historicod->setUsuarioId($id_usuario);
            $em->persist($historicod);
            $em->flush();
            

            if($form['tipos']==2 && $form['fechaInicio']!='' && $form['fechaFin']!=''){
                $ratificacion = new TtecInstitucioneducativaRatificacion();
                $ratificacion->setTtecInstitucioneducativaHistorico($em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->findOneById($historico->getId()));
                $ratificacion->setFechaInicio(new \DateTime($form['fechaInicio']));
                $ratificacion->setFechaFin(new \DateTime($form['fechaFin']));
                $ratificacion->setUsuarioId($id_usuario);
                $ratificacion->setFechaRegistro(new \DateTime('now'));
                $ratificacion->setFechaModificacion(new \DateTime('now'));
                $em->persist($ratificacion);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('historico_detalle', array('idhistorico'=>$form['idhistorico'])));

        }catch (Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el dato histórico');
            return $this->redirect($this->generateUrl('historico_new', array('idRie'=>$entity->getId())));
        }
    }

    /***
     * Eliminacion del registro histórico
     */
    public function deleteDetalleAction(Request $request){
        try{        
            $em = $this->getDoctrine()->getManager();
            $historicoDetalle = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistoricoDetalle')->findOneById($request->get('id'));
            $idHistorico = $historicoDetalle->getInstitucioneducativaHistorico()->getId();
            $em->remove($historicoDetalle);
            $em->flush(); 

        }catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al eliminar el dato histórico');
        }                
        return $this->redirect($this->generateUrl('historico_detalle', array('idhistorico'=>$idHistorico)));
    }
    public function obtieneTipoFlujosResolucionArray(){
        $em = $this->getDoctrine()->getManager();
        $tipos = $em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findAll();
       
            $tiposArray = array();
            foreach($tipos as $tipo){
                $tiposArray[$tipo->getId()] = $tipo->getNombre();
            }        
            
        return $tiposArray;     
    }

    public function obtieneDependenciaArray(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                                'SELECT DISTINCT dt.id,dt.dependencia
                                    FROM SieAppWebBundle:DependenciaTipo dt
                                    WHERE dt.id in (:id)
                                    ORDER BY dt.id ASC'
                                )->setParameter('id', array(1,2,3));
                                $dependencias = $query->getResult();
                                $dependenciasArray = array();
                                for($i=0;$i<count($dependencias);$i++){
                                    $dependenciasArray[$dependencias[$i]['id']] = $dependencias[$i]['dependencia'];
                                }
        return $dependenciasArray;
    }
    public function upFileToServer($archivo){

        $dirfile = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/';
        //$dirfile = 'uploads/archivos/';
        move_uploaded_file($archivo['form']['tmp_name']['archivo'],$dirfile.$archivo['form']['name']['archivo']);

        return $archivo['form']['name']['archivo'];

    }

    /***
     * Copiando archivo al directorio uploads/archivos
     */ 
    public function subirArchivo($archivo){
        $nombre_pdf = NULL;
        if(($archivo instanceof UploadedFile) && ($archivo->getError() == '0')){
            $originalName = $archivo->getClientOriginalName();
            $name_array = explode('.', $originalName);
            $file_type = $name_array[sizeof($name_array) - 1];
            $valid_filetypes = array('pdf');
            if(in_array(strtolower($file_type), $valid_filetypes)){
                $nombre_pdf = $archivo->getFileName();
                $document = new Document();
                $document->setFile($archivo);
                $document->setSubDirectory('archivos');
                $document->processFile();
            }
        }
        return $nombre_pdf;
    }
    public function obtieneTipoDocumentoArray(){
        $nuevoArray = array();
        $nuevoArray["R.M."]='Resolución Ministerial';
        $nuevoArray["R.A."]='Resolución Administrativa';
        $nuevoArray["D.S."]='Decreto Supremo';
        return $nuevoArray;    
    }

    /***
     * Obtiene Arrays de Estado de la Institucion
     */ 
    public function obtieneTiposResolucionArray(){
        $em = $this->getDoctrine()->getManager();
        $tipos = $em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findBy(array('abreviatura' => 'I'));
       
            $tiposArray = array();
            foreach($tipos as $tipo){
                $tiposArray[$tipo->getId()] = $tipo->getNombre();
            }        
            
        return $tiposArray;     
    }

    
    public function localUe($id){
        $db = $em->getConnection();
        $query = "SELECT lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS municipio, lt1.lugar AS canton, lt.lugar AS localidad, jg.*
                    FROM jurisdiccion_geografica jg 
               LEFT JOIN lugar_tipo AS lt ON lt.id = jg.lugar_tipo_id_localidad
               LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                   WHERE jg.id = ".$id."
                    ";
        $stmt = $db->prepare($query);
        dump($stmt);die;
        $response = new JsonResponse();
    	return $response->setData(array(
    			'direccion' => $stmt,
    	));
    }
    public function obtieneAreaFormacionArray($id){
        
        $em = $this->getDoctrine()->getManager();
        switch($id){
            case '999': //instituto técnico Y técnologico
                $nuevoArray = array();
                //areas técnicas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(7)));
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
                //areas tecnológicas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(8)));
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
            break;
            case '11': //CENTROS de capacitación artistica
            case '12': //instituto de formación artistica
            case '13': //escuelas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(12)));
                $nuevoArray = array();
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
            break;
            default: //instituto técnico ó tecnológico
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findAll();
                $nuevoArray = array();
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
               
            break;
        }
        return $nuevoArray; 
    }
    public function obtieneInstitucionAreaFormArray($id){
        $em = $this->getDoctrine()->getManager();
        $datos = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findById($id)));
        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[] = $dato->getTtecAreaFormacionTipo()->getId();
        }
        return $nuevoArray; 
    }
}


