<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Controller\FuncionesController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\AsignaturaEspecialidadTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaCarreraespecialidad;

/**
 * MaestroGestion controller.
 *
 */
class InstitucioneducativaCursoTecnicaController extends Controller {
    public $session;
    public $idCarrera;
    public $idDepartamento;
    /*
     * Lista de maestros en la unidad educativa
     */
    public function indexAction(Request $request,$op) {
        // generar los titulos para los diferentes sistemas
        $this->session = new Session();
        $tipoSistema = $request->getSession()->get('sysname');
        switch($tipoSistema){
            case 'REGULAR': $this->session->set('tituloTipo', 'Asignacion de Carreras');break;
            case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Asignacion de Cursosss');break;
            case 'PERMANENTE': 
                $this->session->set('tituloTipo', 'Asignacion de Carreras');
                $this->session->set('institucion', 'Centro');break;
            default: $this->session->set('tituloTipo', 'asignacion');break;
        }



        // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
        if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
            echo "No tiene permisos para ver el contenido de esta pagina...";
            die;
        }

        
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
            $form = $request->get('form');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            /*
            * verificamos si tiene tuicion
            */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']){
                $institucion = $form['institucioneducativa'];
                $gestion = $form['gestion'];
            }else{
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
        } else {
            $nivelUsuario = $request->getSession()->get('roluser');
            if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativp 9
                // formulario de busqueda de institucion educativa
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                    if($op == 'search'){
                       return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView())); 
                    }
                } else {
                    return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            } else { // si es institucion educativa
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                }else{
                    $funcion = new FuncionesController();
                    $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'),$request->getSession()->get('currentyear')); //5484231);
                    $gestion = $request->getSession()->get('currentyear');
                }    
            }
        }



        // creamos variables de sesion de la institucion educativa y gestion
        $request->getSession()->set('idInstitucion', $institucion);
        $request->getSession()->set('idGestion', $gestion);

        /*
         * lista de maestros registrados en la unidad educativa
         */
        $query = $em->createQuery(
                        'SELECT iece FROM SieAppWebBundle:InstitucioneducativaCarreraespecialidad iece
                    JOIN iece.carreraespecialidadTipo cet
                    WHERE iece.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion);
        $carreras = $query->getResult();
        
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        
        return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:index.html.twig', array(
                    'carreras' => $carreras, 'institucion' => $institucion, 'gestion' => $gestion
        ));
    }
    
    /*
     * formularios de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioneducativaCursoTecnica'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off','maxlength'=>8,'class'=>'form-control jnumbers','pattern'=>'[0-9]{8}')))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }
    
    /*
     * Llamamos al formulario para asignacion de nueva carrera
     * a la institucion
     */
    public function newAction(Request $request) {

        $this->session = new Session();
        // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
        if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
            echo "No tiene permisos para ver el contenido de esta pagina...";
            die;
        }

        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioneducativaCursoTecnica_create'))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('carrera', 'entity', array('label' => 'Programa', 'required' => true, 'class' => 'SieAppWebBundle:CarreraTipo','empty_value'=>'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'choice', array('label' => 'Curso/Especialidad', 'required' => true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:new.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'gestion'=>$this->session->get('idGestion')));
    }
    
    /*
     * registramos la nueva carrera para la institucion
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try{
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            //Verificamos si el curso ya esta asignado
            $asignado = $em->getRepository('SieAppWebBundle:InstitucioneducativaCarreraespecialidad')->findBy(array('institucioneducativa'=>$form['idInstitucion'],'carreraespecialidadTipo'=>$form['especialidad']));
            if(count($asignado)>0){
                $this->get('session')->getFlashBag()->add('newError', 'El curso ya esta asignado');
                return $this->redirect($this->generateUrl('institucioneducativaCursoTecnica'));
            }

            $institucion_carreraEspecialidad = new InstitucioneducativaCarreraespecialidad();
            $institucion_carreraEspecialidad->setCarreraespecialidadTipo($em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['especialidad']));
            $institucion_carreraEspecialidad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
            $em->persist($institucion_carreraEspecialidad);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('institucioneducativaCursoTecnica'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function listar_especialidadesAction($carrera) {
        $em = $this->getDoctrine()->getManager();
        $esp = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->findBy(array('carreraTipo' => $carrera));
        $especialidad = array();
        foreach ($esp as $e) {
            $especialidad[$e->getId()] = $e->getEspecialidad();
        }
        $response = new JsonResponse();
        return $response->setData(array('especialidades' => $especialidad));
    }
    
    public function editAction(Request $request){

        $this->session = new Session();
        // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
        if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
            echo "No tiene permisos para ver el contenido de esta pagina...";
            die;
        }

        $em = $this->getDoctrine()->getManager();
        $curso_carrera = $em->getRepository('SieAppWebBundle:InstitucioneducativaCarreraespecialidad')->find($request->get('idCurso'));
        $this->session = new Session();
        $this->idCarrera = $curso_carrera->getCarreraespecialidadTipo()->getCarreraTipo()->getId();
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioneducativaCursoTecnica_update'))
                ->add('idCurso', 'hidden', array('data' => $request->get('idCurso')))
                ->add('idInstitucion', 'hidden', array('data' => $request->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $request->get('gestion')))
                ->add('carrera', 'entity', array('label' => 'Carrera', 'required' => true, 'class' => 'SieAppWebBundle:CarreraTipo','data'=>$em->getReference('SieAppWebBundle:CarreraTipo',$curso_carrera->getCarreraespecialidadTipo()->getCarreraTipo()->getId()),'empty_value'=>'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'entity', array('class' => 'SieAppWebBundle:CarreraespecialidadTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('cet')
                                ->where('cet.carreraTipo = :carrera')
                                ->setParameter('carrera',$this->idCarrera)
                                ->orderBy('cet.id', 'ASC')
                        ;
                    }, 'property' => 'especialidad','label'=>'Curso','data'=>$em->getReference('SieAppWebBundle:CarreraespecialidadTipo',$curso_carrera->getCarreraespecialidadTipo()->getId()),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucioneducativaCursoTecnica:edit.html.twig', array('form' => $form->createView()));
    }
    
    public function updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $institucion_carreraEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCarreraespecialidad')->find($form['idCurso']);
        $institucion_carreraEspecialidad->setCarreraespecialidadTipo($em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['especialidad']));
        $institucion_carreraEspecialidad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
        $em->persist($institucion_carreraEspecialidad);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron actualizados correctamente');
        return $this->redirect($this->generateUrl('institucioneducativaCursoTecnica'));
    }
    
    public function deleteAction(Request $request){
        try{
            $this->session = new Session();
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }
            
            $em = $this->getDoctrine()->getManager();
            $institucion_carreraEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCarreraespecialidad')->find($request->get('idCurso'));
            $em->remove($institucion_carreraEspecialidad);
            $em->flush();

            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('institucioneducativaCursoTecnica'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'No se pudo eliminar el registro');
            return $this->redirect($this->generateUrl('institucioneducativaCursoTecnica'));
        }
    }
}
