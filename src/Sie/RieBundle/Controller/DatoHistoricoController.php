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
    public function listAction(Request $request){
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
            // $nombre_pdf = $this->subirArchivo($request->files->get('form')['archivo']);
            $nombre_pdf = $this->upFileToServer($_FILES);
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_historico');");
            $query->execute();
            $historico = new TtecInstitucioneducativaHistorico();
            $historico->setInstitucioneducativa($entity);
            $historico->setNroResolucion($form['nroResolucion']); 
            $historico->setFechaResolucion(new \DateTime($form['fechaResolucion']));
            $historico->setDescripcion($form['descripcion']);
            $historico->setDatoAdicional(($form['datoAdicional'])?$form['datoAdicional']:NULL);
            $historico->setArchivo($nombre_pdf);
            $historico->setFechaRegistro(new \DateTime('now'));
            $em->persist($historico);
            $em->flush();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
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
            }

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
}
