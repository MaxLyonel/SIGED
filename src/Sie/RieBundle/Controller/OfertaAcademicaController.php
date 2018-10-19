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
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada;
use Sie\AppWebBundle\Entity\TtecResolucionCarrera;
use Sie\AppWebBundle\Entity\TtecResolucionTipo;
use Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo;
use Sie\AppWebBundle\Entity\TtecCarreraTipo;
use Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo;
use Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo;

use Sie\AppWebBundle\Form\InstitucioneducativaType;

/**
 * Institucioneducativa controller.
 *
 */
class OfertaAcademicaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct(){
          //init the session values
          $this->session = new Session();
      }

    /**
     * Muestra formulario de Busqueda de la institución educativa
     */
    public function indexAction(Request $request){
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:prueba.html.twig');
    }

    /**
     * Muestra el listado de institutos con información de oferta académica
     */
    public function listinstitutoAction(Request $request){
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
                                    ->setParameter('idTipo', array(7, 8, 9))
                                    ->setParameter('idEstado', 10)
                                    ->setParameter('estadoSede', TRUE);        
        $entities = $query->getResult(); 

        return $this->render('SieRieBundle:OfertaAcademica:listinstituto.html.twig', array('entities' => $entities));
    }

    /**
     * Muestra el listado de oferta académica
     */
     public function listAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $listado = $this->listadoOfertaAcademica($request->get('idRie'));
        return $this->render('SieRieBundle:OfertaAcademica:list.html.twig', array('institucion' => $institucion, 'listado' => $listado));
     }   

    /**
     * Muestra formulario de adición de oferta educativa
     */
    public function newAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $areasArray = $this->obtieneInstitucionAreaFormArray($request->get('idRie'));
        $nivelesArray = $this->obtieneInstitucionNivelesFormArray($request->get('idRie'));
        $regimenEstudioArray = $this->obtieneRegimenEstudio();
        $tipoOperacionArray = $this->obtieneTipoTramite();

        if($areasArray && $nivelesArray) //Verificando si tiene areas y niveles autorizados
        {
            //Listado de Carreras Autorizadas
            $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('oac_create'))
            ->add('idRie', 'hidden', array('data' => $request->get('idRie')))
            ->add('ttecAreaFormacion', 'choice', array('label' => 'Area de Formación', 'required' => true,'choices'=>$areasArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('ttecCarreraTipo', 'choice', array('label' => 'Carrera', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('tiempoEstudio', 'choice', array('label' => 'Tiempo de Estudio', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('cargaHoraria', 'text', array('label' => 'Carga Horaria (Sólo números)', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '4') ))
            ->add('nivelTipo', 'choice', array('label' => 'Nivel de Formación', 'required' => true,'choices'=>$nivelesArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper'))) 
            ->add('regimenEstudio', 'choice', array('label' => 'Regimen de Estudio', 'required' => true,'choices'=>$regimenEstudioArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
            ->add('resolucion', 'text', array('label' => 'Resolución','required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY','class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
            ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución','required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
            ->add('operacion', 'choice', array('label' => 'Operación Trámite', 'required' => true,'choices'=>$tipoOperacionArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))            
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            
            return $this->render('SieRieBundle:OfertaAcademica:new.html.twig', array('form' => $form->getForm()->createView(), 'institucion'=>$institucion));
        }
        else
        {
    		$this->get('session')->getFlashBag()->add('mensaje', 'No tiene asignado NIVELES y/o AREAS DE FORMACION, consulte con el ADMINISTRADOR');
            return $this->redirect($this->generateUrl('oac_list', array('idRie' => $institucion->getId())));
        }
    }

    /**
     * Guarda datos del formulario de oferta educativa, datos de carrera
     */
     public function createAction(Request $request){
    	try{
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            //Validando los datos
            $query = $em->createQuery('SELECT ie
                                         FROM SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada ie
                                        WHERE ie.institucioneducativa = :idInstitucion
                                          AND ie.ttecCarreraTipo = :ieCarrera 
                                          AND ie.esVigente = :ieEsVigente')
                                        ->setParameter('idInstitucion', $form['idRie'])
                                        ->setParameter('ieCarrera', $form['ttecCarreraTipo'])
                                        ->setParameter('ieEsVigente', TRUE);
            $datoCarrera = $query->getResult();

            if($datoCarrera){
                $this->get('session')->getFlashBag()->add('mensaje', 'La Carrera ya se encuentra registrada...');
            } else {

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_carrera_autorizada');");
                $query->execute();
                //Guardando carreras autorizadas
                $entity = new TtecInstitucioneducativaCarreraAutorizada();
                $entity->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']));
                $entity->setTtecCarreraTipo($em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($form['ttecCarreraTipo']));
                $entity->setEsEnviado(FALSE);
                $entity->setEsVigente(TRUE);
                $entity->setFechaRegistro(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();     

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_resolucion_carrera');");
                $query->execute();
                //Guardando la resolucion de la carrera
                $resolucion = new TtecResolucionCarrera();
                $resolucion->setNumero($form['resolucion']);
                $resolucion->setFecha(new \DateTime($form['fechaResolucion']));
                $resolucion->setTtecResolucionTipo($em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findOneById(1)); //Por defecto guardando tipo de Resolucion = R.M.
                $resolucion->setTtecInstitucioneducativaCarreraAutorizada($entity);
                $resolucion->setFechaRegistro(new \DateTime('now'));
                $resolucion->setTiempoEstudio($form['tiempoEstudio']);
                $resolucion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($form['nivelTipo']));
                $resolucion->setCargaHoraria($form['cargaHoraria']);
                $resolucion->setTtecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findOneById($form['regimenEstudio']));
                $resolucion->setOperacion($form['operacion']);
                $em->persist($resolucion);
                $em->flush();            
            }
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la carrera.');
        }

        return $this->redirect($this->generateUrl('oac_list', array('idRie' => $institucion->getId())));  
    }            

    /**
     * Muestra formulario para modificar la oferta académica
     */
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($request->get('idresolucion'));
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')
                                                ->findOneById($datResolucion->getTtecInstitucioneducativaCarreraAutorizada()->getId());
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($datAutorizado->getInstitucioneducativa()->getId());
        
        if(!$datAutorizado){
            throw $this->createNotFoundException('No se puede encontrar la Oferta Académica');
        }else{
            $areasArray = $this->obtieneInstitucionAreaFormArray($institucion->getId());
            $nivelesArray = $this->obtieneInstitucionNivelesFormArray($institucion->getId());
            $regimenEstudioArray = $this->obtieneRegimenEstudio();
            $tiempoEstArray = $this->obtieneTiempoEstudio($datResolucion->getNivelTipo()->getId(), $datResolucion->getTtecRegimenEstudioTipo()->getId());
            $tipoOperacionArray = $this->obtieneTipoTramite();

            $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('oac_update'))
            ->add('idRie', 'hidden', array('data' => $institucion->getId()))
            ->add('id', 'hidden', array('data' => $datAutorizado->getId()))
            ->add('idresolucion', 'hidden', array('data' => $request->get('idresolucion')))
            ->add('ttecAreaFormacion', 'text', array('label' => 'Area de Formación', 'data' => $datAutorizado->getTtecCarreraTipo()->getTtecAreaFormacionTipo()->getAreaFormacion(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
            ->add('ttecCarreraTipo', 'text', array('label' => 'Carrera', 'data' => $datAutorizado->getTtecCarreraTipo()->getNombre(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
            ->add('tiempoEstudio', 'choice', array('label' => 'Tiempo de Estudio', 'data'=> $datResolucion->getTiempoEstudio(), 'required' => true,'choices'=>$tiempoEstArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('cargaHoraria', 'text', array('label' => 'Carga Horaria (Sólo números)', 'data'=>$datResolucion->getCargaHoraria(), 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '4') ))
            ->add('nivelTipo', 'choice', array('label' => 'Nivel de Formación', 'data'=>$datResolucion->getNivelTipo()->getId(), 'required' => true,'choices'=>$nivelesArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper'))) 
            ->add('regimenEstudio', 'choice', array('label' => 'Regimen de Estudio', 'data'=>$datResolucion->getTtecRegimenEstudioTipo()->getId(), 'required' => true,'choices'=>$regimenEstudioArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
            ->add('resolucion', 'text', array('label' => 'Resolución', 'data'=>$datResolucion->getNumero(), 'required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
            ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución', 'data' => $datResolucion->getFecha()->format('d-m-Y') ,'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
            ->add('operacion', 'choice', array('label' => 'Operación Trámite', 'data' => $datResolucion->getOperacion(), 'required' => true,'choices'=>$tipoOperacionArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))                        
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
            return $this->render('SieRieBundle:OfertaAcademica:edit.html.twig', array('form' => $form->getForm()->createView(), 'institucion'=> $institucion, 'datAutorizado' => $datAutorizado, 'datResolucion'=>$datResolucion));
        }        
    }

    /**
     * Guarda datos de las modificaciones 
     */
    public function updateAction(Request $request){
    	try{
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($form['id']);
            $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($form['idresolucion']);

            $datResolucion->setNumero($form['resolucion']);
            $datResolucion->setFecha(new \DateTime($form['fechaResolucion']));
            $datResolucion->setTiempoEstudio($form['tiempoEstudio']);
            $datResolucion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($form['nivelTipo']));
            $datResolucion->setCargaHoraria($form['cargaHoraria']);
            $datResolucion->setTtecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findOneById($form['regimenEstudio']));
            $datResolucion->setFechaModificacion(new \DateTime('now'));
            $datResolucion->setOperacion($form['operacion']);
            $em->persist($datResolucion);
            $em->flush();   
                        
            //Validando los datos
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al modificar los datos');
        }  
        
        return $this->redirect($this->generateUrl('oac_list', array('idRie' => $institucion->getId())));          
    }

    /**
     * Elimina el registro de carrera
     */
    public function deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($request->get('idAutorizado'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($datAutorizado->getInstitucioneducativa()->getId());

        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecResolucionCarrera a 
                                    WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada');                       
        $query->setParameter('idCaAutorizada', $datAutorizado->getId());
        $resoluciones = $query->getResult();           

        foreach($resoluciones as $resolucion){
            $em->remove($resolucion);
            $em->flush();
        }

        $em->remove($datAutorizado);
        $em->flush();        

        return $this->redirect($this->generateUrl('oac_list', array('idRie' => $institucion->getId())));
    }

    /**
     * Listado de resoluciones 
     */
    public function listresolucionesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')
                                    ->findOneById($request->get('idAutorizado'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($datAutorizado->getTtecCarreraTipo()->getId());

        $query = $em->createQuery('SELECT a
                                        FROM SieAppWebBundle:TtecResolucionCarrera a 
                                    WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                    ORDER BY a.fecha DESC');                       
        $query->setParameter('idCaAutorizada', $datAutorizado->getId());
        $resoluciones = $query->getResult(); 

        return $this->render('SieRieBundle:OfertaAcademica:listresoluciones.html.twig', array('institucion' => $institucion, 'resoluciones' => $resoluciones, 'carrera' => $carrera, 'datAutorizado' =>$datAutorizado));
    }

    /** 
     * Nuevo de Registro de Resolucion ministerial
     */ 
    public function newresolucionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($request->get('idAutorizado'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $nivelesArray = $this->obtieneInstitucionNivelesFormArray($request->get('idRie'));
        $regimenEstudioArray = $this->obtieneRegimenEstudio();
        $tipoOperacionArray = $this->obtieneTipoTramite();

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('oac_create_resolucion'))
        ->add('idRie', 'hidden', array('data' => $institucion->getId()))
        ->add('idAutorizado', 'hidden', array('data' => $datAutorizado->getId()))
        ->add('ttecAreaFormacion', 'text', array('label' => 'Area de Formación', 'data' => $datAutorizado->getTtecCarreraTipo()->getTtecAreaFormacionTipo()->getAreaFormacion(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
        ->add('ttecCarreraTipo', 'text', array('label' => 'Carrera', 'data' => $datAutorizado->getTtecCarreraTipo()->getNombre(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
        ->add('nivelTipo', 'choice', array('label' => 'Nivel de Formación', 'required' => true,'choices'=>$nivelesArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))         
        ->add('tiempoEstudio', 'choice', array('label' => 'Tiempo de Estudio', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('cargaHoraria', 'text', array('label' => 'Carga Horaria (Sólo números)', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '4') ))
        ->add('regimenEstudio', 'choice', array('label' => 'Regimen de Estudio', 'required' => true,'choices'=>$regimenEstudioArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
        ->add('resolucion', 'text', array('label' => 'Resolución','required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'maxlength' => '69')))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución','required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('operacion', 'choice', array('label' => 'Operación Trámite', 'required' => true,'choices'=>$tipoOperacionArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))            
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        return $this->render('SieRieBundle:OfertaAcademica:newresolucion.html.twig', array('form' => $form->getForm()->createView(), 'institucion'=> $institucion, 'idAutorizado' => $datAutorizado->getId()));
    }

    /** 
     * Guardar Nuevo de Registro de Resolucion ministerial
     */ 
    public function createresolucionAction(Request $request){    
        try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($form['idAutorizado']);

            //Registrando una resolucion de carrera
            $resolucion = new TtecResolucionCarrera();
            $resolucion->setNumero($form['resolucion']);
            $resolucion->setFecha(new \DateTime($form['fechaResolucion']));
            $resolucion->setTtecResolucionTipo($em->getRepository('SieAppWebBundle:TtecResolucionTipo')->findOneById(1)); //Por defecto guardando tipo de Resolucion = R.M.
            $resolucion->setTtecInstitucioneducativaCarreraAutorizada($datAutorizado);
            $resolucion->setFechaRegistro(new \DateTime('now'));
            $resolucion->setTiempoEstudio($form['tiempoEstudio']);
            $resolucion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($form['nivelTipo']));
            $resolucion->setCargaHoraria($form['cargaHoraria']);
            $resolucion->setTtecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findOneById($form['regimenEstudio']));
            $resolucion->setOperacion($form['operacion']);
            $em->persist($resolucion);
            $em->flush(); 

            //Caso: Operación = CIERRE => modificar vigencia = FALSE en AUTORIZACION
            if($form['operacion'] == 'CIERRE'){
                $datAutorizado->setEsVigente(FALSE);
                $datAutorizado->setFechaModificacion(new \DateTime('now'));
                $em->persist($datAutorizado);
                $em->flush();   
            }

            //Validando los datos
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar los datos');
        }  
        
        return $this->redirect($this->generateUrl('oac_list_resolucion', array('idRie' => $institucion->getId(), 'idAutorizado'=>$datAutorizado->getId())));    
    }

    /** 
     * Editar Resolucion ministerial
     */ 
    public function editresolucionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')
                                                ->findOneById($request->get('idAutorizado'));
        $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')
                                                ->findOneById($request->get('idResolucion'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));

        $regimenEstudioArray = $this->obtieneRegimenEstudio();
        $tipoOperacionArray = $this->obtieneTipoTramite();
        $tiempoEstArray = $this->obtieneTiempoEstudio($datResolucion->getNivelTipo()->getId(), $datResolucion->getTtecRegimenEstudioTipo()->getId());
        $nivelesArray = $this->obtieneInstitucionNivelesFormArray($request->get('idRie'));
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('oac_update_resolucion'))
        ->add('idRie', 'hidden', array('data' => $institucion->getId()))
        ->add('idAutorizado', 'hidden', array('data' => $datAutorizado->getId()))
        ->add('idResolucion', 'hidden', array('data' => $datResolucion->getId()))
        ->add('ttecAreaFormacion', 'text', array('label' => 'Area de Formación', 'data' => $datAutorizado->getTtecCarreraTipo()->getTtecAreaFormacionTipo()->getAreaFormacion(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))        
        ->add('ttecCarreraTipo', 'text', array('label' => 'Carrera', 'data' => $datAutorizado->getTtecCarreraTipo()->getNombre(), 'attr' => array('class' => 'form-control jupper', 'readonly'=>true)))
        ->add('nivelTipo', 'choice', array('label' => 'Nivel de Formación', 'data' => $datResolucion->getNivelTipo()->getId(), 'required' => true,'choices'=>$nivelesArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))                 
        ->add('tiempoEstudio', 'choice', array('label' => 'Tiempo de Estudio', 'data' => $datResolucion->getTiempoEstudio(), 'required' => true,'choices'=>$tiempoEstArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('cargaHoraria', 'text', array('label' => 'Carga Horaria (Sólo números)', 'data' => $datResolucion->getCargaHoraria(), 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '4') ))
        ->add('regimenEstudio', 'choice', array('label' => 'Regimen de Estudio', 'data' => $datResolucion->getTtecRegimenEstudioTipo()->getId(), 'required' => true,'choices'=>$regimenEstudioArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))             
        ->add('resolucion', 'text', array('label' => 'Resolución', 'data' => $datResolucion->getNumero(), 'required' => true, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'maxlength' => '69')))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución', 'data' => $datResolucion->getFecha()->format('d-m-Y'),'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('operacion', 'choice', array('label' => 'Operación Trámite', 'data' => $datResolucion->getOperacion(), 'required' => true,'choices'=>$tipoOperacionArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))            
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        return $this->render('SieRieBundle:OfertaAcademica:editresolucion.html.twig', array('form' => $form->getForm()->createView(), 'institucion'=> $institucion, 'idAutorizado' => $datAutorizado->getId()));
    }

    /** 
     * Guardar Modificacion de Registro de Resolucion ministerial
     */ 
    public function updateresolucionAction(Request $request){    
        try{
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($form['idAutorizado']);
            $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($form['idResolucion']);

            $datResolucion->setNumero($form['resolucion']);
            $datResolucion->setFecha(new \DateTime($form['fechaResolucion']));
            $datResolucion->setFechaModificacion(new \DateTime('now'));
            $datResolucion->setTiempoEstudio($form['tiempoEstudio']);
            $datResolucion->setCargaHoraria($form['cargaHoraria']);
            $datResolucion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($form['nivelTipo']));
            $datResolucion->setTtecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findOneById($form['regimenEstudio']));
            $datResolucion->setOperacion($form['operacion']);
            $em->persist($datResolucion);
            $em->flush(); 

            //Caso: Operación = CIERRE => modificar vigencia = FALSE en AUTORIZACION
            if($form['operacion'] == 'CIERRE'){
                $datAutorizado->setEsVigente(FALSE);
                $datAutorizado->setFechaModificacion(new \DateTime('now'));
                $em->persist($datAutorizado);
                $em->flush();   
            }
            
            //Validando los datos
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar los datos');
        }  
        
        return $this->redirect($this->generateUrl('oac_list_resolucion', array('idRie' => $institucion->getId(), 'idAutorizado'=>$datAutorizado->getId())));    
    }  

    /**
     * Elimina el registro de resolucion
     */
    public function deleteresolucionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $datResolucion = $em->getRepository('SieAppWebBundle:TtecResolucionCarrera')->findOneById($request->get('idResolucion'));
        $datAutorizado = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada')->findOneById($datResolucion->getTtecInstitucioneducativaCarreraAutorizada()->getId());
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($datAutorizado->getInstitucioneducativa()->getId());
        //eliminación autorización de curso
        $em->remove($datResolucion);
        $em->flush();   

        return $this->redirect($this->generateUrl('oac_list_resolucion', array('idRie' => $institucion->getId(), 'idAutorizado' => $datAutorizado->getId())));
    }

    /***
    * Obtiene Array de Niveles autorizados al instituto
    */    
    public function obtieneInstitucionNivelesFormArray($id){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT nivaut
                                     FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado nivaut
                                     JOIN nivaut.institucioneducativa instituto  
                                     JOIN nivaut.nivelTipo nivel  
                                    WHERE instituto.id = :idInstitucion
                                      AND nivel.id IN (:ieNivelTipo)')
                                       ->setParameter('idInstitucion', $id)
                                       ->setParameter('ieNivelTipo', array(500,501));
        $datos = $query->getResult();

        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[$dato->getNivelTipo()->getId()] = $dato->getNivelTipo()->getNivel();
        }
        return $nuevoArray; 
    }

    /***
     * Obtiene Array de Carreras, según Area de Formacion
     */  
     public function buscarcarreraAction($idArea) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery('SELECT a
                                    FROM SieAppWebBundle:TtecCarreraTipo a 
                                   WHERE a.ttecAreaFormacionTipo = :idArea
                                ORDER BY a.nombre ASC');                       
        $query->setParameter('idArea', $idArea);
        $datos = $query->getResult();         
 

    	$carreraArray = array();
    	foreach($datos as $dato) {
            $carreraArray[] = array('id' => $dato->getId(), 'nombre' => $dato->getNombre());
        }
        
    	$response = new JsonResponse();
        return $response->setData(array('carreras' => $carreraArray));
    }

    /***
     * Obtiene Array de Regimen de Estudios
     */
    public function obtieneRegimenEstudio(){
    $em = $this->getDoctrine()->getManager();
    $datosArray = array();
    $datos = $em->getRepository('SieAppWebBundle:TtecRegimenEstudioTipo')->findAll();
        foreach($datos as $dato){
            $datosArray[$dato->getId()] = $dato->getRegimenEstudio();
        }
        return $datosArray;
    }

    /***
     * Obtiene Array de Areas de Formacion de la Institucion
     */
     public function obtieneInstitucionAreaFormArray($idRie){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado a 
                                     JOIN a.institucioneducativa b
                                     JOIN a.ttecAreaFormacionTipo c
                                    WHERE b.id = :idRie
                                 ORDER BY c.areaFormacion ASC');                       
        $query->setParameter('idRie', $idRie);
        $datos = $query->getResult();         
        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[$dato->getTtecAreaFormacionTipo()->getId()] = $dato->getTtecAreaFormacionTipo()->getAreaFormacion();
        }

        return $nuevoArray; 
    }   

    /***
     * Obtiene array de denominaciones de una carrera determinada
     */
    public function obtieneDenominacion($idCarrera){
        $em = $this->getDoctrine()->getManager();
        $datos = $em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')
                                    ->findBy(array('ttecCarreraTipo' => $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findById($idCarrera)));
        $denominacionArray = array();
        if($datos){
            foreach($datos as $dato){
                $denominacionArray[$dato->getId()] = $dato->getDenominacion();  
            }
        }else{
            $denominacionArray[0] = 'Aun no definido'; 
        }
        return $denominacionArray;
    }

    /***
     * Obtiene un array con datos del listado de carreras autorizadas
     */
    public function listadoOfertaAcademica($idRie){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT autorizado.id AS id, carrera.id AS idcarrera, carrera.nombre AS carr 
                    FROM ttec_institucioneducativa_carrera_autorizada AS autorizado
                    INNER JOIN ttec_carrera_tipo AS carrera ON autorizado.ttec_carrera_tipo_id = carrera.id 
                    INNER JOIN institucioneducativa AS instituto ON autorizado.institucioneducativa_id = instituto.id 
                    INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id
                    WHERE instituto.id = '".$idRie."'  AND area.id <> 200 ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $listado = $stmt->fetchAll();

        $list = array();                                  
        foreach($listado as $li){
            $query = $em->createQuery('SELECT a
                                         FROM SieAppWebBundle:TtecResolucionCarrera a 
                                        WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                     ORDER BY a.fecha DESC');                       
            $query->setParameter('idCaAutorizada', $li['id']);
            $query->setMaxResults(1);
            $resolucion = $query->getResult();   

            $list[] = array(
                                'id' => $li['id'],
                                'idcarrera' => $li['idcarrera'],
                                'carrera' => $li['carr'],
                                'idresolucion' => ($resolucion) ? $resolucion[0]->getId():"0",
                                'resolucion' => ($resolucion) ? $resolucion[0]->getNumero():" ",
                                'fecharesol' => ($resolucion) ? $resolucion[0]->getFecha():" ",
                                'nivelformacion' => ($resolucion) ? $resolucion[0]->getNivelTipo()->getNivel():" ",
                                'tiempoestudio' => ($resolucion) ? $resolucion[0]->getTiempoEstudio():" ",
                                'regimen' =>  ($resolucion) ? $resolucion[0]->getTtecRegimenEstudioTipo()->getRegimenEstudio():" ",
                                'cargahoraria' => ($resolucion) ? $resolucion[0]->getCargaHoraria():" ",
                                'operacion' => ($resolucion) ? $resolucion[0]->getOperacion():" "
                            );
        }                                    
        return $list;
    }  

    /***
     * Obtiene tipos de trámites en el registro
     */
    public function obtieneTipoTramite(){
        $dato = array('ADECUACION' => 'ADECUACIÓN', 'APERTURA'=>'APERTURA', 'AMPLIACION' =>'AMPLIACIÓN', 'RATIFICACION' => 'RATIFICACIÓN');
        return $dato;
    }   

    /***
     * Obtener el array para el tiempo de estudio
     */
    public function obtieneTiempoEstudio($nivel, $regimen){
        if($nivel == 500 && $regimen == 1){
            return array('1' => '1', '2' => '2', '3' => '3');
        }
        if($nivel == 500 && $regimen == 2){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');
        }
        if($nivel == 500 && $regimen == 3){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');
        }

        if($nivel == 501 && $regimen == 1){
            return array('1' => '1', '2' => '2');
        }
        if($nivel == 501 && $regimen == 2){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4');
        }
        if($nivel == 501 && $regimen == 3){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');
        }
        
        if($nivel == 502 && $regimen == 1){
            return array('1' => '1', '2' => '2');
        }
        if($nivel == 502 && $regimen == 2){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4');
        }
        if($nivel == 502 && $regimen == 3){
            return array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');
        }
    }
}
