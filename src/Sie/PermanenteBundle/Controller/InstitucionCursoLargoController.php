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
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\SocioeconomicoAlternativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcionIdioma;
use Sie\AppWebBundle\Entity\EstudianteDiscapacidad;

/**
 * MaestroGestion controller.
 *
 */
class InstitucionCursoLargoController extends Controller {
    public $session;
    public $idCarrera;
    public $idDepartamento;

    public function __construct(){
        $this->session = new Session();
    }
    /*
     * Lista de maestros en la unidad educativa
     */
    public function indexAction(Request $request,$op) {
        // generar los titulos para los diferentes sistemas
        $this->session = new Session();
        $tipoSistema = $request->getSession()->get('sysname');
        switch($tipoSistema){
            case 'REGULAR': $this->session->set('tituloTipo', 'Asignacion de Carreras');break;
            case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Asignacion de Carreras');break;
            case 'PERMANENTE': 
                $this->session->set('tituloTipo', 'Asignacion de Carreras');
                $this->session->set('institucion', 'Centro');break;
            default: $this->session->set('tituloTipo', 'asignacion');break;
        }
        
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
            $form = $request->get('form');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->render('SiePermanenteBundle:InstitucionCursoLargo:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                return $this->render('SiePermanenteBundle:InstitucionCursoLargo:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                       return $this->render('SiePermanenteBundle:InstitucionCursoLargo:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView())); 
                    }
                } else {
                    return $this->render('SiePermanenteBundle:InstitucionCursoLargo:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                    'SELECT iec, cet FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    INNER JOIN iec.carreraespecialidadTipo cet
                    INNER JOIN cet.carreraTipo ct
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND ct.escursocorto = :op')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('op',false);
        $carreras = $query->getResult();
        
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:index.html.twig', array(
                    'carreras' => $carreras, 'institucion' => $institucion, 'gestion' => $gestion
        ));
    }
    
    /*
     * formularios de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo'))
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
        // Programas asginados al centro
        $em = $this->getDoctrine()->getManager();

        $programas = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CarreraTipo ct
            WHERE ct.escursocorto = :op')
            ->setParameter('op',false)
            ->getResult();
        $programasArray = array();
        foreach ($programas as $p) {
            $programasArray[$p->getId()] = $p->getCarrera();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_create'))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                ->add('numero','text',array('label'=>'Número','required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'4','autocomplete'=>'off')))
                ->add('carrera', 'choice', array('label' => 'Programa', 'required' => true, 'choices' => $programasArray,'empty_value'=>'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'required' => true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('modalidad', 'entity', array('label' => 'Modalidad', 'required' => true, 'class' => 'SieAppWebBundle:ModalidadTipo', 'attr' => array('class' => 'form-control')))
                ->add('nivelAcreditacion', 'entity', array('label' => 'Nivel de Acreditación', 'required' => true, 'class' => 'SieAppWebBundle:NivelacreditacionTipo', 'attr' => array('class' => 'form-control')))
                ->add('departamento', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC');
                    }, 'property' => 'lugar','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('distrito', 'choice', array('label' => 'Provincia', 'required' =>true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('fechaInicio','text',array('label'=>'Fecha de Inicio','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('fechaFin','text',array('label'=>'Fecha de conclusión','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('lugar','text',array('label'=>'Lugar','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('duracion','text',array('label'=>'Duración en horas','attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,4}','maxlength'=>'4','autocomplete'=>'off')))
                ->add('reconocimiento','text',array('label'=>'Reconocimiento (horas)','attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,4}','maxlength'=>'4','autocomplete'=>'off','onkeyup'=>'validarReconocimiento(this.value)')))
                ->add('poblacion','entity',array('label'=>'Población','class'=>'SieAppWebBundle:PoblacionTipo','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('poblacionDetalle','text',array('label'=>'Poblacion Detalle','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:new.html.twig', array('form' => $form->createView()));
    }
    
    /*
     * registramos el nuevo maestro
     */
    public function createAction(Request $request) {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $institucion_curso = new InstitucioneducativaCurso();
        $institucion_curso->setCarreraespecialidadTipo($em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['especialidad']));
        $institucion_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(1));
        $institucion_curso->setDuracionhoras($form['duracion']);
        $institucion_curso->setReconocimientoHoras($form['reconocimiento']);
        $institucion_curso->setFechaFin(new \DateTime($form['fechaFin']));
        $institucion_curso->setFechaInicio(new \DateTime($form['fechaInicio']));
        $institucion_curso->setFecharegistroCuso(new \DateTime('now'));
        $institucion_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
        $institucion_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(10));
        $institucion_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
        $institucion_curso->setLugar($form['lugar']);
        $institucion_curso->setLugartipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio']));
        $institucion_curso->setModalidadTipo($em->getRepository('SieAppWebBundle:ModalidadTipo')->find($form['modalidad']));
        $institucion_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(18));
        $institucion_curso->setNivelacreditacionTipo($em->getRepository('SieAppWebBundle:NivelacreditacionTipo')->find($form['nivelAcreditacion']));
        
        $institucion_curso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find(1));
        $institucion_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
        
        $institucion_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
        $institucion_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find(1));
        $institucion_curso->setNumeroperiodo($form['numero']);
        $institucion_curso->setEsabierto(true);
        $institucion_curso->setPoblacionTipo($em->getRepository('SieAppWebBundle:PoblacionTipo')->find($form['poblacion']));
        $institucion_curso->setPoblacionDetalle($form['poblacionDetalle']);

        $em->persist($institucion_curso);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
        return $this->redirect($this->generateUrl('institucioncursolargo'));
    }
    
    public function listar_especialidadesAction($carrera) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT iece, cet FROM SieAppWebBundle:InstitucioneducativaCarreraespecialidad iece
                    JOIN iece.carreraespecialidadTipo cet
                    WHERE iece.institucioneducativa = :idInstitucion
                    AND cet.carreraTipo = :idCarrera')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('idCarrera', $carrera);
        $esp = $query->getResult();
        
        //$esp = $em->getRepository('SieAppWebBundle:InstitucionCarreraespecialidadTipo')->findBy(array('carreraTipo' => $carrera,''));
        $especialidad = array();
        foreach ($esp as $e) {
            $especialidad[$e->getCarreraespecialidadTipo()->getId()] = $e->getCarreraespecialidadTipo()->getEspecialidad();
        }
        $response = new JsonResponse();
        return $response->setData(array('especialidades' => $especialidad));
    }
    
    public function editAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));

        $programas = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CarreraTipo ct
            WHERE ct.escursocorto = :op')
            ->setParameter('op',false)
            ->getResult();
        $programasArray = array();
        foreach ($programas as $p) {
            $programasArray[$p->getId()] = $p->getCarrera();
        }
        
        $query = $em->createQuery(
                        'SELECT iece, cet FROM SieAppWebBundle:InstitucioneducativaCarreraespecialidad iece
                    JOIN iece.carreraespecialidadTipo cet
                    WHERE iece.institucioneducativa = :idInstitucion
                    AND cet.carreraTipo = :idCarrera')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('idCarrera', $curso->getCarreraespecialidadTipo()->getCarreraTipo()->getId());
        $esp = $query->getResult();
        
        //$esp = $em->getRepository('SieAppWebBundle:InstitucionCarreraespecialidadTipo')->findBy(array('carreraTipo' => $carrera,''));
        $especialidad = array();
        foreach ($esp as $e) {
            $especialidad[$e->getCarreraespecialidadTipo()->getId()] = $e->getCarreraespecialidadTipo()->getEspecialidad();
        }
        if(count($especialidad)==0){
            $especialidad[$curso->getCarreraespecialidadTipo()->getId()] = $curso->getCarreraespecialidadTipo()->getEspecialidad();
        }

        $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($curso->getLugartipo());
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugartipo()->getId());
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo()->getId());

        $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId()));
        $provinciasArray = array();
        foreach ($provincias as $p) {
            $provinciasArray[$p->getId()] = $p->getLugar();
        }

        $municipios = $em->createQuery(
            'SELECT lt FROM SieAppWebBundle:LugarTipo lt
            WHERE lt.lugarTipo = :prov')
            ->setParameter('prov',$provincia->getId())
            ->getResult();
        $munArray = array();
        foreach ($municipios as $m) {
            $munArray[$m->getId()] = $m->getLugar();
        }


        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_update'))
                ->add('idCurso', 'hidden', array('data' => $curso->getId()))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                ->add('numero','text',array('label'=>'Número','data'=>$curso->getNumeroperiodo(),'required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'4','autocomplete'=>'off')))
                ->add('carrera', 'choice', array('label' => 'Programa', 'required' => true, 'choices' => $programasArray,'data'=>$curso->getCarreraespecialidadTipo()->getCarreraTipo()->getId(),'empty_value'=>'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'required' => true,'choices'=>$especialidad,'data'=>$curso->getCarreraespecialidadTipo()->getId(),'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('modalidad', 'entity', array('label' => 'Modalidad', 'required' => true, 'class' => 'SieAppWebBundle:ModalidadTipo','data'=>$em->getReference('SieAppWebBundle:ModalidadTipo',$curso->getModalidadTipo()->getId()), 'attr' => array('class' => 'form-control')))
                ->add('nivelAcreditacion', 'entity', array('label' => 'Nivel de Acreditación', 'required' => true, 'class' => 'SieAppWebBundle:NivelacreditacionTipo','data'=>$em->getReference('SieAppWebBundle:NivelacreditacionTipo',$curso->getNivelacreditacionTipo()->getId()), 'attr' => array('class' => 'form-control')))
                ->add('departamento', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC');
                    }, 'property' => 'lugar','data'=>$em->getReference('SieAppWebBundle:LugarTipo',$departamento->getId()),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('distrito', 'choice', array('label' => 'Provincia','choices'=>$provinciasArray,'data'=>$provincia->getId(), 'required' =>true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','choices'=>$munArray,'data'=>$curso->getLugartipo()->getId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('fechaInicio','text',array('label'=>'Fecha de Inicio','data'=>$curso->getFechaInicio()->format('d-m-Y'),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('fechaFin','text',array('label'=>'Fecha de conclusión','data'=>$curso->getFechaFin()->format('d-m-Y'),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('lugar','text',array('label'=>'Lugar','data'=>$curso->getLugar(),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('duracion','text',array('label'=>'Duración en horas','data'=>$curso->getDuracionhoras(),'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,4}','maxlength'=>'4','autocomplete'=>'off')))
                ->add('reconocimiento','text',array('label'=>'Reconocimiento (horas)','data'=>$curso->getReconocimientoHoras(),'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,4}','maxlength'=>'4','autocomplete'=>'off','onkeyup'=>'validarReconocimiento(this.value)')))
                ->add('poblacion','entity',array('label'=>'Población','class'=>'SieAppWebBundle:PoblacionTipo','data'=>$em->getReference('SieAppWebBundle:PoblacionTipo',$curso->getPoblacionTipo()->getId()),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('poblacionDetalle','text',array('label'=>'Poblacion Detalle','data'=>$curso->getPoblacionDetalle(),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:edit.html.twig', array('form' => $form->createView()));
    }
    
    public function updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
            $institucion_curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']);
            $institucion_curso->setCarreraespecialidadTipo($em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['especialidad']));
            $institucion_curso->setDuracionhoras($form['duracion']);
            $institucion_curso->setReconocimientoHoras($form['reconocimiento']);
            $institucion_curso->setFechaFin(new \DateTime($form['fechaFin']));
            $institucion_curso->setFechaInicio(new \DateTime($form['fechaInicio']));
            $institucion_curso->setFecharegistroCuso(new \DateTime('now'));
            $institucion_curso->setLugar($form['lugar']);
            $institucion_curso->setLugartipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio']));
            $institucion_curso->setModalidadTipo($em->getRepository('SieAppWebBundle:ModalidadTipo')->find($form['modalidad']));
            $institucion_curso->setNivelacreditacionTipo($em->getRepository('SieAppWebBundle:NivelacreditacionTipo')->find($form['nivelAcreditacion']));
            $institucion_curso->setNumeroperiodo($form['numero']);
            $institucion_curso->setPoblacionTipo($em->getRepository('SieAppWebBundle:PoblacionTipo')->find($form['poblacion']));
            $institucion_curso->setPoblacionDetalle($form['poblacionDetalle']);

            $em->persist($institucion_curso);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron actualizados correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function deleteAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $institucion_curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));

            // Verificamos que no tenga modulos wn la tabla curso oferta
            $modulos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$institucion_curso->getId()));
            if(count($modulos)>0){
                $this->get('session')->getFlashBag()->add('deleteError', 'No se puede eliminar el registro, porque tiene módulos o especialidades');
                return $this->redirect($this->generateUrl('institucioncursolargo'));
            }
            
            $em->remove($institucion_curso);
            $em->flush();

            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'No se pudo eliminar el registro');
            return $this->redirect($this->generateUrl('institucioncursolargo'));
        }
    }

    public function closeAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            // Verificamos si el curso tiene partivcipantes inscritos
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$request->get('idCurso')));
            if(count($inscritos)<=0){
                $this->get('session')->getFlashBag()->add('closeError', 'No se pudo cerrar un curso si no tiene participantes inscritos');
                return $this->redirect($this->generateUrl('institucioncursolargo'));
            }

            $institucion_curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            $institucion_curso->setEsabierto(false);
            $em->flush();

            $this->get('session')->getFlashBag()->add('closeOk', 'El curso fue cerrado exitosamente');
            return $this->redirect($this->generateUrl('institucioncursolargo'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('closeError', 'No se pudo cerrar el curso');
            return $this->redirect($this->generateUrl('institucioncursolargo'));
        }
    }
    
    /*
     * Asignaturas
     */
    
    public function asignaturasAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$request->get('idCurso')));
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
        
        $this->session->set('idCurso', $request->get('idCurso'));
        
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:asignaturaIndex.html.twig', array(
                    'curso'=>$curso, 'asignaturas' => $cursoOferta, 'institucion' => $institucion, 'gestion' => $request->get('gestion')
        ));
    }
    
    public function asignaturas_sessionAction(){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$this->session->get('idCurso')));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($this->session->get('idInstitucion'));
        
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:asignaturaIndex.html.twig', array(
                    'curso'=>$curso, 'asignaturas' => $cursoOferta, 'institucion' => $institucion, 'gestion' => $this->session->get('idGestion')
        ));
    }
    
    public function asignaturas_newAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        /*$query = $em->createQuery(
                        'SELECT mi FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona p
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    ORDER BY p.paterno ASC')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'));
        $maestros = $query->getResult();*/
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestros = $query->getResult();
        $maestroArray = array();
        foreach($maestros as $m){
            $maestroArray[$m->getId()] = $m->getPersona()->getPaterno().' '.$m->getPersona()->getMaterno().' '.$m->getPersona()->getNombre(); 
        }
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('asignaturas_create'))
                ->add('idCurso', 'hidden', array('data' => $this->session->get('idCurso')))
                ->add('asignatura', 'text', array('label'=>'Módulo','attr'=>array('class'=>'form-control jnumbersletters','maxlength'=>'150','autocomplete'=>'off')))
                ->add('maestro','choice',array('label'=>'Facilitador','choices'=>$maestroArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))     
                ->add('horasMes','text',array('label'=>'Duración en horas','attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'3')))
                
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:asignatura_new.html.twig', array('form' => $form->createView()));
    }
    
    public function asignaturas_createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
            // Verificamos que no tenga mas de 10 modulos
            $modulos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$form['idCurso']));
            if(count($modulos)>=10){
                $this->get('session')->getFlashBag()->add('newError', 'No se puede registrar mas de 10 módulos');
                return $this->redirect($this->generateUrl('asignaturas_session'));
            }

            /*
             * registro de asignatura especialidad
             */
            $newAsignatura = new AsignaturaEspecialidadTipo();
            $newAsignatura->setAsignaturaEspecialidad($form['asignatura']);
            $newAsignatura->setEsactivo(1);
            $newAsignatura->setEsobligatorio(1);
            $newAsignatura->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));
            $em->persist($newAsignatura);
            $em->flush();
            
            /*
             * REgistro de institucion educativa curso oferta
             */
            $cursoOferta = new InstitucioneducativaCursoOferta();
            $cursoOferta->setAsignaturaEspecialidadTipo($em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find($newAsignatura->getId()));
            $cursoOferta->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));
            $cursoOferta->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($form['maestro']));
            $cursoOferta->setHorasmes($form['horasMes']);
            $em->persist($cursoOferta);
            $em->flush();
            
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('asignaturas_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function asignaturas_editAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $asignatura = $em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find($request->get('idAsignatura'));
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($request->get('idCursoOferta'));

        
        /*$query = $em->createQuery(
                        'SELECT mi FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona p
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    ORDER BY p.paterno ASC')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'));
        $maestros = $query->getResult();*/
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestros = $query->getResult();
        $maestroArray = array();
        foreach($maestros as $m){
            $maestroArray[$m->getId()] = $m->getPersona()->getPaterno().' '.$m->getPersona()->getMaterno().' '.$m->getPersona()->getNombre(); 
        }
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('asignaturas_update'))
                ->add('idAsignatura','hidden',array('data'=>$asignatura->getId()))
                ->add('idCursoOferta','hidden',array('data'=>$cursoOferta->getId()))
                ->add('asignatura','text',array('label'=>'Módulo','data'=>$asignatura->getAsignaturaEspecialidad(),'attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off','maxlength'=>'150')))     
                ->add('maestro','choice',array('label'=>'Facilitador','choices'=>$maestroArray,'data'=>$cursoOferta->getMaestroInscripcion()->getId(),'attr'=>array('class'=>'form-control')))     
                ->add('horasMes','text',array('label'=>'Duración en horas por mes','data'=>$cursoOferta->getHorasmes(),'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'3')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:asignatura_edit.html.twig', array('form' => $form->createView()));
    }
    
    public function asignaturas_updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
        
            $asignatura = $em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->findOneById($form['idAsignatura']);
            $asignatura->setAsignaturaEspecialidad($form['asignatura']);
            $em->flush();
            
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($form['idCursoOferta']);
            $cursoOferta->setAsignaturaEspecialidadTipo($em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find($form['idAsignatura']));
            $cursoOferta->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($form['maestro']));
            $cursoOferta->setHorasmes($form['horasMes']);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron actualizados correctamente');
            return $this->redirect($this->generateUrl('asignaturas_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    
    public function asignaturas_deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($request->get('idCursoOferta'));
            //VErificamos si ualgun estudiante ya cursa la asginatura o modulo
            $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
            if(count($estudianteAsignatura)>0){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('deleteError', 'No se puede eliminar el módulo, porque tiene estudiantes inscritos');
                return $this->redirect($this->generateUrl('asignaturas_session'));
            }

            $em->remove($cursoOferta);
            $em->flush();
            
            $asignatura = $em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find($request->get('idAsignatura'));
            $em->remove($asignatura);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('asignaturas_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Participantes
     */

    public function participante_indexAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
        $participantes = $em->createQuery(
            'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
            INNER JOIN ei.estudiante e
            WHERE ei.institucioneducativaCurso = :idCurso
            ORDER BY e.paterno, e.materno, e.nombre')
            ->setParameter('idCurso',$curso->getId())
            ->getResult();

        $this->session->set('idCurso', $curso->getId());
        
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_index.html.twig', array(
                    'curso'=>$curso, 'participantes' => $participantes, 'institucion' => $institucion, 'gestion' => $request->get('gestion')
        ));
    }    

    public function participante_sessionAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($this->session->get('idInstitucion'));
        $participantes = $em->createQuery(
            'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
            INNER JOIN ei.estudiante e
            WHERE ei.institucioneducativaCurso = :idCurso
            ORDER BY e.paterno, e.materno, e.nombre')
            ->setParameter('idCurso',$curso->getId())
            ->getResult();
        
        return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_index.html.twig', array(
                    'curso'=>$curso, 'participantes' => $participantes, 'institucion' => $institucion, 'gestion' => $request->get('gestion')
        ));
    }

    /**
     * PArticipantes con rude
     */

    public function participante_searchAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_new_rude'))
                ->add('tipo','hidden',array('data'=>'rude'))
                ->add('rude', 'text', array('label' => 'CÓDIGO RUDEAL', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{12,20}','maxlength'=>'20')))
                ->add('guardar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_search.html.twig', array('form' => $form->createView()));
        }catch(Exception $ex){

        }
    }

    public function participante_new_rudeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
            // Verificacmos si existe o no el estudiante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rude']));
            if(!$estudiante){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('searchError', 'El participante con el código ingresado no existe');
                return $this->redirect($this->generateUrl('institucioncursolargo_participante_search'));
            }

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));

            $pais = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',0)
                ->getResult();
            $paisArray = array(); 
            foreach ($pais as $p) {
                $paisArray[$p->getId()] = $p->getLugar();
            }

            $departamentos = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',1)
                ->getResult();
            $depArray = array();
            //dump($departamentos);die;
            foreach ($departamentos as $d) {
                $depArray[$d->getId()] = $d->getLugar();
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_create_rude'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion->getId()))
                ->add('idEstudiante','hidden',array('data'=>$estudiante->getId()))
                ->add('idCurso', 'hidden', array('data' => $curso->getId()))
                ->add('gestion', 'hidden', array('data' => $request->get('gestion')))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad','data'=>$estudiante->getCarnetIdentidad(),'required'=>false, 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('complemento','text',array('label'=>'Complemento','data'=>$estudiante->getComplemento(),'required'=>false,'attr'=>array('class'=>'form-control', 'readonly'=>true)))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno','data'=>$estudiante->getPaterno(),'required'=>false,'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('materno', 'text', array('label' => 'Apellido Materno','data'=>$estudiante->getMaterno(),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$estudiante->getNombre(),'required'=>false, 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$estudiante->getFechaNacimiento()->format('d-m-Y'),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('genero', 'text', array('label' => 'Género','data'=>$estudiante->getGeneroTipo()->getGenero(),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('correo', 'text', array('label' => 'Correo Electrónico','data'=>$estudiante->getCorreo(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))

                ->add('nacPais','choice',array('label'=>'Pais','choices'=>$paisArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacDepartamentos(this.value)')))
                ->add('nacDepartamento','choice',array('label'=>'Departamento','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacProvincias(this.value)')))
                ->add('nacProvincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nacLugar','text',array('label'=>'Ciudad, Pueblo o Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))


                ->add('departamento','choice',array('label'=>'Departamento','choices'=>$depArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'provincias(this.value)')))
                ->add('provincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'municipios(this.value)')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('localidad','text',array('label'=>'Localidad/Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))

                ->add('zona', 'text', array('label' => 'Zona/Barrio/Villa', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('calle', 'text', array('label' => 'Avenida/Calle', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('nro', 'text', array('label' => 'Numero Domicilio', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers jupper')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))

                ->add('discapacidad','entity',array('label'=>'Registro y tipos de necesidades educativas de acuerdo a discapacidad','class'=>'SieAppWebBundle:DiscapacidadTipo','multiple'=>true,'attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar la(s) discapacidad(es)')))

                ->add('idiomaMaterno','entity',array('label'=>'¿Cual es la lengua materna del participante?','class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control')))
                ->add('idiomaConoce','entity',array('label'=>'¿Que idioma o lenguas habla el participante?','multiple'=>true,'class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar Idioma(s)')))
                ->add('identifica','entity',array('label'=>'¿El participante pertenece o se identifica como?','class'=>'SieAppWebBundle:EtniaTipo','attr'=>array('class'=>'form-control')))

                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_new_rude.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'curso'=>$curso,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_create_rudeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try{
            $this->session = new Session();
            $form = $request->get('form');

            // Obtenemos los datos del estudiante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idEstudiante']);
            $idEstudiante = $estudiante->getId();
            // Verificamos que no tenga una inscripcion
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$idEstudiante,'institucioneducativaCurso'=>$form['idCurso']));
            if($estudianteInscripcion){
                echo "no se puede inscribir al mismo curso mas de una vez";
                die;
            }

            // actualizamos algunos datos del estudiante
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacDepartamento']));
            $estudiante->setIdiomaMaternoId($form['idiomaMaterno']);
            $estudiante->setFechaModificacion(new \DateTime('now'));
            $estudiante->setCorreo(mb_strtolower($form['correo'],'utf-8'));
            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['nacPais']));
            $estudiante->setLocalidadNac($form['nacLugar']);
            $estudiante->setCelular($form['celular']);
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacProvincia']));
            $em->flush();
            
            
            // Estudiante Inscripcion
            $newInscripcion = new EstudianteInscripcion();
            $newInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $newInscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante));
            $newInscripcion->setFechaInscripcion(new \DateTime('now'));
            $newInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));
            $em->persist($newInscripcion);
            $em->flush(); 
            $idInscripcion = $newInscripcion->getId();               
            

            //Direccion actual en la tabla socioeconomico_alternativa
            $socioeconomico_alternativa = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
            if(!$socioeconomico_alternativa){
                $newSocioeconomico = new SocioeconomicoAlternativa();
                $newSocioeconomico->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $newSocioeconomico->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                $newSocioeconomico->setDireccionZona(mb_strtoupper($form['zona'],'utf-8'));
                $newSocioeconomico->setDireccionCalle(mb_strtoupper($form['calle'],'utf-8'));
                $newSocioeconomico->setDireccionNro($form['nro']);
                $newSocioeconomico->setUsuarioId($this->session->get('userId'));
                $newSocioeconomico->setDepartamentoTipoId($form['departamento']);
                $newSocioeconomico->setProvinciaTipoId($form['provincia']);
                $newSocioeconomico->setSeccionmunicipioTipoId($form['municipio']);
                $newSocioeconomico->setLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
                $newSocioeconomico->setComunidad($form['localidad']);
                $newSocioeconomico->setIdentificacomo($form['identifica']);
                $em->persist($newSocioeconomico);
                $em->flush();
            }

            // Idiomas que habla esl estudiante
            $idiomasConoce = $form['idiomaConoce'];
            for($i=0;$i<count($idiomasConoce);$i++){
                $newIdioma = new EstudianteInscripcionIdioma();
                $newIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($idiomasConoce[$i]));
                $newIdioma->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $em->persist($newIdioma);
                $em->flush();   
            }
            $discapacidades = $form['discapacidad'];
            for($i=0;$i<count($discapacidades);$i++){
                $newDiscapacidad = new EstudianteDiscapacidad();
                $newDiscapacidad->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante));
                $newDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($discapacidades[$i]));
                $em->persist($newDiscapacidad);
                $em->flush();
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Error en el registro');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }
    }


    /**
     * Participantes por nombres y apellidos
     */
    public function participante_search_nameAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_search_result'))
                ->add('tipo','hidden',array('data'=>'name'))
                ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}','maxlength'=>'10','onkeyup'=>'verificarExistePersona(this.value)')))
                ->add('complemento','text',array('label'=>'Complemento','required'=>false,'attr'=>array('class'=>'form-control jonlynumbersletters jupper','maxlength'=>'2','autocomplete'=>'off')))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
                ->add('guardar', 'submit', array('label' => 'Buscar Participante', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_search_name.html.twig', array('form' => $form->createView()));
        }catch(Exception $ex){

        }
    }

    public function participante_search_resultAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
            $paterno = mb_strtoupper($form['paterno'],'utf-8');
            $materno = mb_strtoupper($form['materno'],'utf-8');
            $nombre = mb_strtoupper($form['nombre'],'utf-8');

            $estudiantes = $em->createQueryBuilder()
                ->select('e')
                ->from('SieAppWebBundle:Estudiante','e')
                ->where('e.paterno LIKE :paterno')
                ->andWhere('e.materno LIKE :materno')
                ->andWhere('e.nombre LIKE :nombre')
                ->setParameter('paterno',$paterno)
                ->setParameter('materno',$materno)
                ->setParameter('nombre',$nombre)
                ->getQuery()
                ->getResult();

            $em->getConnection()->commit();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_search_result.html.twig',array('estudiantes'=>$estudiantes,
                    'carnet'=>$form['carnet'],
                    'complemento'=>$form['complemento'],
                    'paterno'=>$form['paterno'],
                    'materno'=>$form['materno'],
                    'nombre'=>$form['nombre'],
                    'fechaNacimiento'=>$form['fechaNacimiento']
                    ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_new_nameAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            // Verificacmos si existe o no el estudiante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($request->get('idEstudiante'));
            if(!$estudiante){
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('institucioncursolargo_participante_search_name'));
            }

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));

            $pais = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',0)
                ->getResult();
            $paisArray = array(); 
            foreach ($pais as $p) {
                $paisArray[$p->getId()] = $p->getLugar();
            }

            $departamentos = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',1)
                ->getResult();
            $depArray = array();
            //dump($departamentos);die;
            foreach ($departamentos as $d) {
                $depArray[$d->getId()] = $d->getLugar();
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_create_rude'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion->getId()))
                ->add('idEstudiante','hidden',array('data'=>$estudiante->getId()))
                ->add('idCurso', 'hidden', array('data' => $curso->getId()))
                ->add('gestion', 'hidden', array('data' => $request->get('gestion')))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad','data'=>$estudiante->getCarnetIdentidad(),'required'=>false, 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('complemento','text',array('label'=>'Complemento','data'=>$estudiante->getComplemento(),'required'=>false,'attr'=>array('class'=>'form-control', 'readonly'=>true)))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno','data'=>$estudiante->getPaterno(),'required'=>false,'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('materno', 'text', array('label' => 'Apellido Materno','data'=>$estudiante->getMaterno(),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$estudiante->getNombre(),'required'=>false, 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$estudiante->getFechaNacimiento()->format('d-m-Y'),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('genero', 'text', array('label' => 'Género','data'=>$estudiante->getGeneroTipo()->getGenero(),'required'=>false, 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('correo', 'text', array('label' => 'Correo Electrónico','data'=>$estudiante->getCorreo(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))

                ->add('nacPais','choice',array('label'=>'Pais','choices'=>$paisArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacDepartamentos(this.value)')))
                ->add('nacDepartamento','choice',array('label'=>'Departamento','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacProvincias(this.value)')))
                ->add('nacProvincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nacLugar','text',array('label'=>'Ciudad, Pueblo o Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))


                ->add('departamento','choice',array('label'=>'Departamento','choices'=>$depArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'provincias(this.value)')))
                ->add('provincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'municipios(this.value)')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('localidad','text',array('label'=>'Localidad/Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))

                ->add('zona', 'text', array('label' => 'Zona/Barrio/Villa', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('calle', 'text', array('label' => 'Avenida/Calle', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('nro', 'text', array('label' => 'Numero Domicilio', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers jupper')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))

                ->add('discapacidad','entity',array('label'=>'Registro y tipos de necesidades educativas de acuerdo a discapacidad','class'=>'SieAppWebBundle:DiscapacidadTipo','multiple'=>true,'attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar la(s) discapacidad(es)')))

                ->add('idiomaMaterno','entity',array('label'=>'¿Cual es la lengua materna del participante?','class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control')))
                ->add('idiomaConoce','entity',array('label'=>'¿Que idioma o lenguas habla el participante?','multiple'=>true,'class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar Idioma(s)')))
                ->add('identifica','entity',array('label'=>'¿El participante pertenece o se identifica como?','class'=>'SieAppWebBundle:EtniaTipo','attr'=>array('class'=>'form-control')))

                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_new_rude.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'curso'=>$curso,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Participante nuevo
     */

    public function participante_newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));

            $pais = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',0)
                ->getResult();
            $paisArray = array(); 
            foreach ($pais as $p) {
                $paisArray[$p->getId()] = $p->getLugar();
            }

            $departamentos = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',1)
                ->getResult();
            $depArray = array();
            //dump($departamentos);die;
            foreach ($departamentos as $d) {
                $depArray[$d->getId()] = $d->getLugar();
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion->getId()))
                ->add('idCurso', 'hidden', array('data' => $curso->getId()))
                ->add('gestion', 'hidden', array('data' => $request->get('gestion')))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad','data'=>$request->get('carnet'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}','maxlength'=>'10','readonly'=>true)))
                ->add('complemento','text',array('label'=>'Complemento','data'=>$request->get('complemento'),'required'=>false,'attr'=>array('class'=>'form-control jonlynumbersletters jupper','maxlength'=>'2','autocomplete'=>'off','readonly'=>true)))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno','data'=>$request->get('paterno'), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45','readonly'=>true)))
                ->add('materno', 'text', array('label' => 'Apellido Materno','data'=>$request->get('materno'), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45','readonly'=>true)))
                ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$request->get('nombre'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45','readonly'=>true)))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$request->get('fechaNacimiento'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}','readonly'=>true)))
                ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control jupper')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))

                ->add('nacPais','choice',array('label'=>'Pais','choices'=>$paisArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacDepartamentos(this.value)')))
                ->add('nacDepartamento','choice',array('label'=>'Departamento','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacProvincias(this.value)')))
                ->add('nacProvincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nacLugar','text',array('label'=>'Ciudad, Pueblo o Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))


                ->add('departamento','choice',array('label'=>'Departamento','choices'=>$depArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'provincias(this.value)')))
                ->add('provincia','choice',array('label'=>'Provincia','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'municipios(this.value)')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('localidad','text',array('label'=>'Localidad/Comunidad','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))

                ->add('zona', 'text', array('label' => 'Zona/Barrio/Villa', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('calle', 'text', array('label' => 'Avenida/Calle', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('nro', 'text', array('label' => 'Numero Domicilio', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers jupper')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))

                ->add('discapacidad','entity',array('label'=>'Registro y tipos de necesidades educativas de acuerdo a discapacidad','class'=>'SieAppWebBundle:DiscapacidadTipo','multiple'=>true,'attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar la(s) discapacidad(es)')))

                ->add('idiomaMaterno','entity',array('label'=>'¿Cual es la lengua materna del participante?','class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control')))
                ->add('idiomaConoce','entity',array('label'=>'¿Que idioma o lenguas habla el participante?','multiple'=>true,'class'=>'SieAppWebBundle:IdiomaMaterno','attr'=>array('class'=>'form-control chosen-select','data-placeholder'=>'Seleccionar Idioma(s)')))
                ->add('identifica','entity',array('label'=>'¿El participante pertenece o se identifica como?','class'=>'SieAppWebBundle:EtniaTipo','attr'=>array('class'=>'form-control')))

                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_new.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'curso'=>$curso,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try{
            $this->session = new Session();
            $form = $request->get('form');

            // Verificamos si existe el participante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$form['carnetIdentidad']));
            if($estudiante){
                // Actualizamos sus datos
                $idEstudiante = $estudiante->getId();
            }else{
                // gENERAMOS UN CODIGO RUDE
                $query = $em->getConnection()->prepare('SELECT fuc_codigoestudiante(:sie::VARCHAR,:gestion::VARCHAR)');
                $query->bindValue(':sie', $this->session->get('idInstitucion'));
                $query->bindValue(':gestion', $this->session->get('idGestion'));
                $query->execute();
                $codigorude = $query->fetchAll();
                $codigo = $codigorude[0];
                $codigorude = $codigo['fuc_codigoestudiante'];


                $newEstudiante = new Estudiante();
                $newEstudiante->setCodigoRude($codigorude);
                $newEstudiante->setCarnetIdentidad($form['carnetIdentidad']);
                $newEstudiante->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                $newEstudiante->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                $newEstudiante->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                $newEstudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacDepartamento']));
                $newEstudiante->setIdiomaMaternoId($form['idiomaMaterno']);
                $newEstudiante->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                $newEstudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                $newEstudiante->setFechaModificacion(new \DateTime('now'));
                $newEstudiante->setCorreo(mb_strtolower($form['correo'],'utf-8'));
                $newEstudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['nacPais']));
                $newEstudiante->setLocalidadNac($form['nacLugar']);
                $newEstudiante->setCelular($form['celular']);
                $newEstudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacProvincia']));
                $em->persist($newEstudiante);
                $em->flush();
                $idEstudiante = $newEstudiante->getId();                
            }
            // Estudiante Inscripcion
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$idEstudiante,'institucioneducativaCurso'=>$form['idCurso']));
            if($estudianteInscripcion){
                $idInscripcion = $estudianteInscripcion->getId();
            }else{
                $newInscripcion = new EstudianteInscripcion();
                $newInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                $newInscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante));
                $newInscripcion->setFechaInscripcion(new \DateTime('now'));
                $newInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));
                $em->persist($newInscripcion);
                $em->flush(); 
                $idInscripcion = $newInscripcion->getId();               
            }

            //Direccion actual en la tabla socioeconomico_alternativa
            $socioeconomico_alternativa = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
            if(!$socioeconomico_alternativa){
                $newSocioeconomico = new SocioeconomicoAlternativa();
                $newSocioeconomico->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $newSocioeconomico->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                $newSocioeconomico->setDireccionZona(mb_strtoupper($form['zona'],'utf-8'));
                $newSocioeconomico->setDireccionCalle(mb_strtoupper($form['calle'],'utf-8'));
                $newSocioeconomico->setDireccionNro($form['nro']);
                $newSocioeconomico->setUsuarioId($this->session->get('userId'));
                $newSocioeconomico->setDepartamentoTipoId($form['departamento']);
                $newSocioeconomico->setProvinciaTipoId($form['provincia']);
                $newSocioeconomico->setSeccionmunicipioTipoId($form['municipio']);
                $newSocioeconomico->setLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
                $newSocioeconomico->setComunidad($form['localidad']);
                $newSocioeconomico->setIdentificacomo($form['identifica']);
                $em->persist($newSocioeconomico);
                $em->flush();
            }

            // Idiomas que habla esl estudiante
            $idiomasConoce = $form['idiomaConoce'];
            for($i=0;$i<count($idiomasConoce);$i++){
                $newIdioma = new EstudianteInscripcionIdioma();
                $newIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($idiomasConoce[$i]));
                $newIdioma->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $em->persist($newIdioma);
                $em->flush();   
            }
            $discapacidades = $form['discapacidad'];
            for($i=0;$i<count($discapacidades);$i++){
                $newDiscapacidad = new EstudianteDiscapacidad();
                $newDiscapacidad->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante));
                $newDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($discapacidades[$i]));
                $em->persist($newDiscapacidad);
                $em->flush();
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Error en el registro');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }
    }
    public function participante_editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idParticipante'));
            $participante = $em->getRepository('SieAppWebBundle:Estudiante')->find($inscripcion->getEstudiante()->getId());
            $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion'=>$inscripcion->getId()));
            $discapacidad = $em->getRepository('SieAppWebBundle:EstudianteDiscapacidad')->findBy(array('estudiante'=>$participante->getId()));
            $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionIdioma')->findby(array('estudianteInscripcion'=>$inscripcion->getId()));

            // Lugar de nacimiento
            $pais = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',0)
                ->getResult();
            $paisArray = array(); 
            foreach ($pais as $p) {
                $paisArray[$p->getId()] = $p->getLugar();
            }
            $nacDep = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar
                AND lt.lugarTipo = :pais')
                ->setParameter('lugar',1)
                ->setParameter('pais',$participante->getPaisTipo()->getId())
                ->getResult();
            $nacDepArray = array(); 
            foreach ($nacDep as $nd) {
                $nacDepArray[$nd->getId()] = $nd->getLugar();
            }
            $nacProv = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :dep')
                ->setParameter('dep',$participante->getLugarNacTipo()->getId())
                ->getResult();
            $nacProvArray = array(); 
            foreach ($nacProv as $np) {
                $nacProvArray[$np->getId()] = $np->getLugar();
            }
            //Direccion actual
            $departamentos = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :lugar')
                ->setParameter('lugar',1)
                ->getResult();
            $depArray = array();
            foreach ($departamentos as $d) {
                $depArray[$d->getId()] = $d->getLugar();
            }

            $provincias = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :dep')
                ->setParameter('dep',$socioeconomico->getDepartamentoTipoId())
                ->getResult();
            $provArray = array();
            foreach ($provincias as $p) {
                $provArray[$p->getId()] = $p->getLugar();
            }
            $municipios = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :prov')
                ->setParameter('prov',$socioeconomico->getProvinciaTipoId())
                ->getResult();
            $munArray = array();
            foreach ($municipios as $m) {
                $munArray[$m->getId()] = $m->getLugar();
            }
            $discapacidades = $em->createQuery(
                'SELECT dt FROM SieAppWebBundle:DiscapacidadTipo dt
                ORDER BY dt.id')
                ->getResult();
            $discArray = array();
            foreach ($discapacidades as $d) {
                $discArray[$d->getId()] = $d->getDiscapacidad();
            }
            $idiomasLista = $em->createQuery(
                'SELECT im FROM SieAppWebBundle:IdiomaMaterno im
                ORDER BY im.id')
                ->getResult();
            $idiomasArray = array();
            foreach ($idiomasLista as $il) {
                $idiomasArray[$il->getId()] = $il->getIdiomaMaterno();
            }
            // Generamos los arrays de los idiomas y discapadcidades del participante
            $discPartArray = array();
            foreach ($discapacidad as $d) {
                $discPartArray[] = $d->getDiscapacidadTipo()->getId();
            }
            $idiomaPartArray = array();
            foreach ($idiomas as $i) {
                $idiomaPartArray[] = $i->getIdiomaMaterno()->getId();
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursolargo_participante_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion->getId()))
                ->add('idCurso', 'hidden', array('data' => $curso->getId()))
                ->add('gestion', 'hidden', array('data' => $request->get('gestion')))
                ->add('idParticipante', 'hidden', array('data' => $participante->getId()))
                ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                ->add('idSocioeconomico', 'hidden', array('data' => $socioeconomico->getId()))
                ///->add('idDiscapacidad', 'hidden', array('data' => $discapacidad[0]->getId()))
                //->add('idIdiomas', 'hidden', array('data' => $idiomas[0]->getId()))

                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad','data'=>$participante->getCarnetIdentidad(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}','maxlength'=>'10','onkeyup'=>'verificarExistePersona(this.value)')))
                ->add('complemento','text',array('label'=>'Complemento','data'=>$participante->getComplemento(),'required'=>false,'attr'=>array('class'=>'form-control jonlynumbersletters jupper','maxlength'=>'2','autocomplete'=>'off')))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno','data'=>$participante->getPaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('materno', 'text', array('label' => 'Apellido Materno','data'=>$participante->getMaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$participante->getNombre(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}",'maxlength'=>'45')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$participante->getFechaNacimiento()->format('d-m-Y'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa','pattern'=>'(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
                ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo','data'=>$em->getReference('SieAppWebBundle:GeneroTipo',$participante->getGeneroTipo()->getId()), 'attr' => array('class' => 'form-control jupper')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico','data'=>$participante->getCorreo(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))

                ->add('nacPais','choice',array('label'=>'Pais','choices'=>$paisArray,'data'=>$participante->getPaisTipo()->getId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacDepartamentos(this.value)')))
                ->add('nacDepartamento','choice',array('label'=>'Departamento','choices'=>$nacDepArray,'data'=>$participante->getLugarNacTipo()->getId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'nacProvincias(this.value)')))
                ->add('nacProvincia','choice',array('label'=>'Provincia','choices'=>$nacProvArray,'data'=>$participante->getLugarProvNacTipo()->getId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nacLugar','text',array('label'=>'Ciudad, Pueblo o Comunidad','data'=>$participante->getLocalidadNac(),'attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))


                ->add('departamento','choice',array('label'=>'Departamento','choices'=>$depArray,'data'=>$socioeconomico->getDepartamentoTipoId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'provincias(this.value)')))
                ->add('provincia','choice',array('label'=>'Provincia','choices'=>$provArray,'data'=>$socioeconomico->getProvinciaTipoId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'municipios(this.value)')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','choices'=>$munArray,'data'=>$socioeconomico->getSeccionmunicipioTipoId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('localidad','text',array('label'=>'Localidad/Comunidad','data'=>'OTRO','attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))

                ->add('zona', 'text', array('label' => 'Zona/Barrio/Villa','data'=>$socioeconomico->getDireccionZona(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('calle', 'text', array('label' => 'Avenida/Calle','data'=>$socioeconomico->getDireccionCalle(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('nro', 'text', array('label' => 'Numero Domicilio','data'=>$socioeconomico->getDireccionNro(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers jupper')))
                ->add('celular', 'text', array('label' => 'Nro de Celular','data'=>$participante->getCelular() ,'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))

                ->add('discapacidad','choice',array('label'=>'Registro y tipos de necesidades educativas de acuerdo a discapacidad','choices'=>$discArray,'multiple'=>true,'data'=>$discPartArray,'attr'=>array('class'=>'form-control chosen-select')))

                ->add('idiomaMaterno','entity',array('label'=>'¿Cual es la lengua materna del participante?','class'=>'SieAppWebBundle:IdiomaMaterno','data'=>$em->getReference('SieAppWebBundle:IdiomaMaterno',$participante->getIdiomaMaternoId()),'attr'=>array('class'=>'form-control')))
                ->add('idiomaConoce','choice',array('label'=>'¿Que idioma o lenguas habla el participante?','choices'=>$idiomasArray,'multiple'=>true,'data'=>$idiomaPartArray,'attr'=>array('class'=>'form-control chosen-select')))
                ->add('identifica','entity',array('label'=>'¿El participante pertenece o se identifica como?','class'=>'SieAppWebBundle:EtniaTipo','data'=>$em->getReference('SieAppWebBundle:EtniaTipo',$socioeconomico->getIdentificacomo()),'attr'=>array('class'=>'form-control')))

                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_edit.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'curso'=>$curso,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_showAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idParticipante'));
            $participante = $em->getRepository('SieAppWebBundle:Estudiante')->find($inscripcion->getEstudiante()->getId());
            $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion'=>$inscripcion->getId()));
            $discapacidad = $em->getRepository('SieAppWebBundle:EstudianteDiscapacidad')->findBy(array('estudiante'=>$participante->getId()));
            $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionIdioma')->findby(array('estudianteInscripcion'=>$inscripcion->getId()));

            $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($socioeconomico->getDepartamentoTipoId());
            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($socioeconomico->getProvinciaTipoId());
            $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($socioeconomico->getSeccionmunicipioTipoId());

            $idiomaMaterno = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($participante->getIdiomaMaternoId());
            $identifica = $em->getRepository('SieAppWebBundle:EtniaTipo')->find($socioeconomico->getIdentificacomo());

            $form = $this->createFormBuilder()

                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad','data'=>$participante->getCarnetIdentidad(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('complemento','text',array('label'=>'Complemento','data'=>$participante->getComplemento(),'attr'=>array('class'=>'form-control','readonly'=>true)))                
                ->add('paterno', 'text', array('label' => 'Apellido Paterno','data'=>$participante->getPaterno(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('materno', 'text', array('label' => 'Apellido Materno','data'=>$participante->getMaterno(), 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$participante->getNombre(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$participante->getFechaNacimiento()->format('d-m-Y'), 'attr' => array('class' => 'form-control', 'readonly'=>true)))
                ->add('genero', 'text', array('label' => 'Género','data'=>$participante->getGeneroTipo()->getGenero(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('correo', 'text', array('label' => 'Correo Electrónico','data'=>$participante->getCorreo(),'attr' => array('class' => 'form-control','readonly'=>true)))

                ->add('nacPais','text',array('label'=>'Pais','data'=>$participante->getPaisTipo()->getPais(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('nacDepartamento','text',array('label'=>'Departamento','data'=>$participante->getLugarNacTipo()->getLugar(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('nacProvincia','text',array('label'=>'Provincia','data'=>$participante->getLugarProvNacTipo()->getLugar(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('nacLugar','text',array('label'=>'Ciudad, Pueblo o Comunidad','data'=>$participante->getLocalidadNac(),'attr'=>array('class'=>'form-control','readonly'=>true)))


                ->add('departamento','text',array('label'=>'Departamento','data'=>$departamento->getLugar(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('provincia','text',array('label'=>'Provincia','data'=>$provincia->getLugar(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('municipio','text',array('label'=>'Seccion/Municipio','data'=>$municipio->getLugar(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                ->add('localidad','text',array('label'=>'Localidad/Comunidad','data'=>$socioeconomico->getComunidad(),'attr'=>array('class'=>'form-control','readonly'=>true)))

                ->add('zona', 'text', array('label' => 'Zona/Barrio/Villa','data'=>$socioeconomico->getDireccionZona(),'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('calle', 'text', array('label' => 'Avenida/Calle','data'=>$socioeconomico->getDireccionCalle(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('nro', 'text', array('label' => 'Numero Domicilio','data'=>$socioeconomico->getDireccionNro(), 'attr' => array('class' => 'form-control','readonly'=>true)))
                ->add('celular', 'text', array('label' => 'Nro de Celular','data'=>$participante->getCelular() ,'attr' => array('class' => 'form-control', 'readonly'=>true)))

                //->add('discapacidad','choice',array('label'=>'Registro y tipos de necesidades educativas de acuerdo a discapacidad','choices'=>$discArray,'multiple'=>true,'data'=>$discPartArray,'attr'=>array('class'=>'form-control chosen-select')))

                ->add('idiomaMaterno','text',array('label'=>'¿Cual es la lengua materna del participante?','data'=>$idiomaMaterno->getIdiomaMaterno(),'attr'=>array('class'=>'form-control','readonly'=>true)))
                //->add('idiomaConoce','choice',array('label'=>'¿Que idioma o lenguas habla el participante?','choices'=>$idiomasArray,'multiple'=>true,'data'=>$idiomaPartArray,'attr'=>array('class'=>'form-control chosen-select')))
                ->add('identifica','text',array('label'=>'¿El participante pertenece o se identifica como?','data'=>$identifica->getEtnia(),'attr'=>array('class'=>'form-control','readonly'=>true)))

                ->getForm();
            return $this->render('SiePermanenteBundle:InstitucionCursoLargo:participante_show.html.twig', array(
                                'form' => $form->createView(),
                                'institucion'=>$institucion,
                                'curso'=>$curso,
                                'gestion'=>$request->get('gestion'),
                                'discapacidad'=>$discapacidad,
                                'idiomasConoce'=>$idiomas
                                ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $form = $request->get('form');
            // Verificamos si existe el participante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idParticipante']);
            $estudiante->setCarnetIdentidad($form['carnetIdentidad']);
            $estudiante->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
            $estudiante->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
            $estudiante->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacDepartamento']));
            $estudiante->setIdiomaMaternoId($form['idiomaMaterno']);
            $estudiante->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
            $estudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $estudiante->setFechaModificacion(new \DateTime('now'));
            $estudiante->setCorreo(mb_strtolower($form['correo'],'utf-8'));
            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['nacPais']));
            $estudiante->setLocalidadNac($form['nacLugar']);
            $estudiante->setCelular($form['celular']);
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['nacProvincia']));
            $em->flush();
            
            // Estudiante Inscripcion
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']);
            $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            
            $estudianteInscripcion->setFechaInscripcion(new \DateTime('now'));
            $estudianteInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));
            $em->flush(); 

            //Direccion actual en la tabla socioeconomico_alternativa
            $socioeconomico_alternativa = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($form['idSocioeconomico']);
            $socioeconomico_alternativa->setDireccionZona(mb_strtoupper($form['zona'],'utf-8'));
            $socioeconomico_alternativa->setDireccionCalle(mb_strtoupper($form['calle'],'utf-8'));
            $socioeconomico_alternativa->setDireccionNro($form['nro']);
            $socioeconomico_alternativa->setUsuarioId($this->session->get('userId'));
            $socioeconomico_alternativa->setDepartamentoTipoId($form['departamento']);
            $socioeconomico_alternativa->setProvinciaTipoId($form['provincia']);
            $socioeconomico_alternativa->setSeccionmunicipioTipoId($form['municipio']);
            $socioeconomico_alternativa->setLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
            $socioeconomico_alternativa->setComunidad($form['localidad']);
            $socioeconomico_alternativa->setIdentificacomo($form['identifica']);
            $em->flush();
            

            /**
             * Idiomas que conoce
             */
            // Eliminamos los idiomas anteriores
            $q = $em->createQuery(
                'DELETE FROM SieAppWebBundle:EstudianteInscripcionIdioma eii
                WHERE eii.estudianteInscripcion = :idEstIns')
                ->setParameter('idEstIns',$estudianteInscripcion->getId());
            $numDelete = $q->execute();
            // Registramos los nuevos idiomas
            $idiomasConoce = $form['idiomaConoce'];
            for($i=0;$i<count($idiomasConoce);$i++){
                $newIdioma = new EstudianteInscripcionIdioma();
                $newIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($idiomasConoce[$i]));
                $newIdioma->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcion->getId()));
                $em->persist($newIdioma);
                $em->flush();   
            }
            /**
             * Discapacidades
             */
            // Eliminamos las discapacidades
            $q = $em->createQuery(
                'DELETE FROM SieAppWebBundle:EstudianteDiscapacidad ed
                WHERE ed.estudiante = :idEst')
                ->setParameter('idEst',$estudiante->getId());
            $numDelete = $q->execute();
            // Registramos las nuevas discapacidades
            $discapacidades = $form['discapacidad'];
            for($i=0;$i<count($discapacidades);$i++){
                $newDiscapacidad = new EstudianteDiscapacidad();
                $newDiscapacidad->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
                $newDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($discapacidades[$i]));
                $em->persist($newDiscapacidad);
                $em->flush();
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron modificados correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    public function participante_deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idParticipante'));
            $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$estudianteInscripcion->getId()));
            if(count($estudianteAsignatura)>0){
                $this->get('session')->getFlashBag()->add('deleteError', 'No se puede eliminar el registro, el participante ya tiene notas');
                return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
            }
            // Obtenermos el id del estudiante
            $idEstudiante = $estudianteInscripcion->getEstudiante()->getId();
            // Eliminamos los idiomas del estudiante
            $q = $em->createQuery(
                'DELETE FROM SieAppWebBundle:EstudianteInscripcionIdioma eii
                WHERE eii.estudianteInscripcion = :idEstIns')
                ->setParameter('idEstIns',$estudianteInscripcion->getId());
            $numDelete = $q->execute();

            // Eliminamos los datos socioeconomicos
            $q1 = $em->createQuery(
                'DELETE FROM SieAppWebBundle:SocioeconomicoAlternativa sea
                WHERE sea.estudianteInscripcion = :idEstIns')
                ->setParameter('idEstIns',$estudianteInscripcion->getId());
            $num1Delete = $q1->execute();
            // Eliminamos la inscripcion
            
            $em->remove($estudianteInscripcion);
            $em->flush();

            // Eliminamos la discapacidad
            $q2 = $em->createQuery(
                'DELETE FROM SieAppWebBundle:EstudianteDiscapacidad ed
                WHERE ed.estudiante = :idEst')
                ->setParameter('idEst',$idEstudiante);
            $num2Delete = $q2->execute();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('institucioncursolargo_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax 
     */
    public function departamentoAction($pais){
        try{
            $em = $this->getDoctrine()->getManager();
            $departamento = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :pais
                AND lt.lugarNivel = 1')
                ->setParameter('pais',$pais)
                ->getResult();
            $departamentoArray = array();
            foreach ($departamento as $p) {
                $departamentoArray[$p->getId()] = $p->getLugar();
            }
            $response = new JsonResponse();
            return $response->setData(array('departamentos'=>$departamentoArray));
        } catch (Exception $ex) {
            return $ex;
        }
    }
    public function provinciaAction($departamento){
        try{
            $em = $this->getDoctrine()->getManager();
            $provincia = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :dep')
                ->setParameter('dep',$departamento)
                ->getResult();
            $provinciaArray = array();
            foreach ($provincia as $p) {
                $provinciaArray[$p->getId()] = $p->getLugar();
            }
            $response = new JsonResponse();
            return $response->setData(array('provincias'=>$provinciaArray));
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function municipioAction($provincia){
        try{
            //echo $provincia;die;
            $em = $this->getDoctrine()->getManager();
            $municipio = $em->createQuery(
                'SELECT lt FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarTipo = :prov')
                ->setParameter('prov',$provincia)
                ->getResult();
            $municipioArray = array();
            foreach ($municipio as $m) {
                $municipioArray[$m->getId()] = $m->getLugar();
            }
            $response = new JsonResponse();
            return $response->setData(array('municipios'=>$municipioArray));
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
