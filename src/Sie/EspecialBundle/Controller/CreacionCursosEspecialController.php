<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;
use Sie\AppWebBundle\Entity\EspecialModalidadTipo;
use Sie\AppWebBundle\Entity\EspecialMomentoTipo;


/**
 * EstudianteInscripcion controller.
 *
 */
class CreacionCursosEspecialController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            //dump($tipoSistema);die;
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Creación de Cursos');
                                $this->session->set('layout','layoutRegular.html.twig');break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignación de Docentes');
                                    $this->session->set('layout','layoutAlternativa.html.twig');break;
                case 'ALTERNATIVA ESPECIAL': $this->session->set('tituloTipo', 'Creación de Oferta Educativa');
                                    $this->session->set('layout','layoutEspecialSie.html.twig');break;
                default:    $this->session->set('tituloTipo', 'Paralelos');
                            $this->session->set('layout','layoutRegular.html.twig');break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                    /*
                     * verificamos si existe la unidad educativa
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
                    if (!$institucioneducativa) {
                        $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                        return $this->render('SieEspecialBundle:CreacionCursosEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                    /*
                     * verificamos si tiene tuicion
                     */
                    $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                    $query->bindValue(':user_id', $this->session->get('userId'));
                    $query->bindValue(':sie', $form['sie']);
                    $query->bindValue(':rolId', $this->session->get('roluser'));
                    $query->execute();
                    $aTuicion = $query->fetchAll();
                    if ($aTuicion[0]['get_ue_tuicion']){
                        $institucion = $form['sie'];
                        $gestion = $form['gestion'];
                    }else{
                        $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                        return $this->render('SieEspecialBundle:CreacionCursosEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                //not work ObtenerUnidadEducativaAction function,  check if the user is director ... krlos was here :)
                if (1) { // si no es institucion educativa 5 o administrativo 9
                //if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieEspecialBundle:CreacionCursosEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieEspecialBundle:CreacionCursosEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {
                        $funcion = new \Sie\EspecialBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = $request->getSession()->get('currentyear');
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Lista de cursos institucioneducativa
           /* $query = $em->createQuery(
                    'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('niveles',array(11,12,13));
                */

            $query = $em->createQuery(
                    'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                    JOIN iec.institucioneducativaCurso ie
                    WHERE ie.institucioneducativa = :idInstitucion
                    AND ie.gestionTipo = :gestion
                    ORDER BY ie.turnoTipo,iec.especialAreaTipo, ie.nivelTipo, ie.gradoTipo, ie.paraleloTipo')
                                ->setParameter('idInstitucion', $institucion)
                                ->setParameter('gestion', $gestion);
               
            $cursos = $query->getResult();
           //dump($cursos);die;
            /*
             * obtenemos los datos de la unidad educativa
             */
            //$est = $niveles;
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
           // $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$institucion,'gestionTipo'=>$gestion,'cargoTipo'=>0));

            $em->getConnection()->commit();

           /* return $this->render('SieEspecialBundle:CreacionCursos:index.html.twig', array(
                        'cursos' => $cursos, 'institucion' => $institucion, 'gestion' => $gestion,'maestros'=>$maestros*/
            return $this->render('SieEspecialBundle:CreacionCursosEspecial:index.html.twig', array(
                    'cursos' => $cursos, 'institucion' => $institucion, 'gestion' => $gestion
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido');
            return $this->render('SieEspecialBundle:CreacionCursosEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('creacioncursos_especial'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 9)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }


    /**
     * Creacion del formulario de nuevo curso
     */
    public function newAction(Request $request){
        try{
            //dump($request);die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $gestion = $request->get('idGestion');
            /*
             * Listamos los turnos validos
             */
            $query = $em->createQuery(
                                    'SELECT t FROM SieAppWebBundle:TurnoTipo t
                                    WHERE t.id IN (:id)'
                                    )->setParameter('id',array(1,2,4,8,9,10,11));
            $turnos_result = $query->getResult();
            $turnos = array();
            foreach ($turnos_result as $t){
                $turnos[$t->getId()] = $t->getTurno();
            }

            /*
             * Listamos los momento aplicado a visual
             */
            $query = $em->createQuery(
                'SELECT t FROM SieAppWebBundle:EspecialMomentoTipo t
                WHERE t.id IN (:id)'
                )->setParameter('id',array(1,2,3,4));
                $momentos_result = $query->getResult();
                $momentos = array();
                foreach ($momentos_result as $t){
                $momentos[$t->getId()] = $t->getMomento();
                }
            
/*
             * Listamos las areas autorizadas segun el RUE
             */
              /*  $query = $em->createQuery(
                    'SELECT a FROM SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado a
                                    WHERE a.institucioneducativa = (:id) ORDER BY a.especialAreaTipo'
                     )->setParameter('id', $institucion->getId());
                    $areas_result = $query->getResult();
                    
                    $areas = array();
                    foreach ($areas_result as $a){
                        //TODO consultar si se quita multiple a pesar que esta acreditada

                        $areas[$a->getEspecialAreaTipo()->getId()] = $a->getEspecialAreaTipo()->getAreaEspecial();
                    }*/
                    

            /*
             * Listamos las areas validas
             */
            $query = $em->createQuery(
                    'SELECT a FROM SieAppWebBundle:EspecialAreaTipo a
                                    WHERE a.id IN (:id) ORDER BY a.areaEspecial'
                     )->setParameter('id',array(1,2,3,4,6,7,10,12));
                    // )->setParameter('id',array(1,2,3,4,5,6,7,8,9,100));
                    $areas_result = $query->getResult();
                    $areas = array();
                    foreach ($areas_result as $a){
                        $areas[$a->getId()] = $a->getAreaEspecial();
                    }

            /*
             * Listamos los niveles validos
             */
//             $query = $em->createQuery(
//                                     'SELECT n FROM SieAppWebBundle:NivelTipo n
//                                     WHERE n.id != :id'
//                                     )->setParameter('id',0);
//             $niveles_result = $query->getResult();
//             $niveles = array();
//             foreach ($niveles_result as $n){
//                 $niveles[$n->getId()] = $n->getNivel();
//             }

            /*
             * Listamos los grados para nivel inicial
             */
//             $query = $em->createQuery(
//                                     'SELECT g FROM SieAppWebBundle:GradoTipo g
//                                     WHERE g.id IN (:id)'
//                                     )->setParameter('id',array(1,2,3,4,5,6,99));
//             $grados_result = $query->getResult();
//             $grados = array();

//             foreach ($grados_result as $g){
//                 $grados[$g->getId()] = $g->getGrado();
//             }
            /*
             * Listamos los paralelos validos
             */
            $query = $em->createQuery(
                                    'SELECT p FROM SieAppWebBundle:ParaleloTipo p
                                    WHERE p.id != :id'
                                    )->setParameter('id',0);
            $paralelos_result = $query->getResult();
            $paralelos = array();
            foreach ($paralelos_result as $p){
                $paralelos[$p->getId()] = $p->getParalelo();
            }

            /*
             * Listamos los programas validos
             */
//             $query = $em->createQuery(
//                  'SELECT pr FROM SieAppWebBundle:ProgramaTipo pr
//                                     WHERE pr.id != :id'
//                  )->setParameter('id',100);
//                  $programas_result = $query->getResult();
//                  $programas = array();
//                  foreach ($programas_result as $pr){
//                      $programas[$pr->getId()] = $pr->getPrograma();
//                  }
                    /*
                     * Listamos los servicios validos
                     */
//             $query = $em->createQuery(
//                          'SELECT s FROM SieAppWebBundle:ServicioTipo s
//                                     WHERE s.id != :id'
//                          )->setParameter('id',100);
//                          $servicios_result = $query->getResult();
//                          $servicios = array();
//                          foreach ($servicios_result as $s){
//                              $servicios[$s->getId()] = $s->getServicio();
//                          }

                            /*
                             * Listamos los servicios tecnicos validos
                             */
                            //!= :id   ->setParameter('id',100)
            $query = $em->createQuery(
                                    'SELECT at FROM SieAppWebBundle:EspecialTecnicaEspecialidadTipo at
                                    WHERE at.id not in (0,99,32,100) ORDER BY at.especialidad'
                                    );
                                    $tecnicas_result = $query->getResult();
                                    $tecnicas = array();
                                    foreach ($tecnicas_result as $at){
                                        $tecnicas[$at->getId()] = $at->getEspecialidad();
                                    }
            $tecnica = 99;
            /*
             * Listamos los niveles tecnicos validos
             */
            $query = $em->createQuery('SELECT nt FROM SieAppWebBundle:EspecialNivelTecnicoTipo nt ORDER BY nt.nivelTecnico');
                    $nivelesTecnico = $query->getResult();
                    $nivelesTecnicoArray = array();
                    foreach ($nivelesTecnico as $t){
                        $nivelesTecnicoArray[$t->getId()] = $t->getNivelTecnico();
                    }
            $nivelTecnico = 99;
            $momento = 99;

           // dump ($tecnicas);die;

            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('creacioncursos_especial_create'))
                    ->add('idInstitucion','hidden',array('data'=>$request->get('idInstitucion')))
                    ->add('idGestion','hidden',array('data'=>$request->get('idGestion')))
                    ->add('turno','choice',array('label'=>'Turno','choices'=>$turnos,'attr'=>array('class'=>'form-control')))
                    ->add('area', 'choice',array('label'=>'Subárea','choices'=>$areas , 'empty_value' => 'Seleccionar', 'required' => true, 'attr' => array('class' => 'form-control')))
                    ->add('modalidad','choice',array('label'=>'Modalidad','empty_value' => 'Seleccionar','required' => true, 'attr' => array('class' => 'form-control'),'attr'=>array('class'=>'form-control')))
                    ->add('nivel','choice',array('label'=>'Oferta','empty_value'=>'Seleccionar','attr'=>array('class'=>'form-control')))
                    ->add('grado','choice',array('label'=>'Grado','empty_value'=>'Seleccionar','attr'=>array('class'=>'form-control')))
                    ->add('programa','choice',array('label'=>'Programa','empty_value'=>'Seleccionar','attr'=>array('class'=>'form-control')))
                    ->add('servicio','choice',array('label'=>'Servicio','empty_value'=>'Seleccionar','attr'=>array('class'=>'form-control')))
                    ->add('tecnica','choice',array('label'=>'Técnica','choices'=>$tecnicas,'data'=> $tecnica,'attr'=>array('class'=>'form-control')))
                    ->add('momento','choice',array('label'=>'Momento','choices'=>$momentos,'data'=> $momento,'attr'=>array('class'=>'form-control')))
                    ->add('nivelTecnico','choice',array('label'=>'Nivel de Formación Técnica','choices'=>$nivelesTecnicoArray,'data'=> $nivelTecnico,'attr'=>array('class'=>'form-control')))
                    ->add('nivelTecnicoId','hidden',array('data'=> $nivelTecnico))
                    ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'attr'=>array('class'=>'form-control')))
                    ->add('fisicoMotor','choice',array('label'=>'Fisico-Motora','choices'=>array('Físico-Motora/Auditiva'=>'Físico-Motora/Auditiva','Físico-Motora/Visual'=>'Físico-Motora/Visual','Otro'=>'Otro'),'multiple' => false,'expanded' => true,'attr'=>array('class'=>'form-control')))
                    ->add('multiple','choice',array('label'=>'Multiple','choices'=>array('Auditiva/Multiple'=>'Auditiva/Multiple','Físico-Motora/Multiple'=>'Físico-Motora/Multiple','Intelectual/Múltiple'=>'Intelectual/Múltiple', 'Visual/Auditiva'=>'Visual/Auditiva','Visual/Intelectual'=>'Visual/Intelectual','Intelectual/Auditiva'=>'Intelectual/Auditiva' ),'multiple' => false,'expanded' => true,'attr'=>array('class'=>'form-control')))
                    ->add('educacionCasa', CheckboxType::class, array('label'=>'Educación Sociocomunitaria en Casa','required' => false))
                    ->add('guardar','submit',array('label'=>'Crear Oferta','attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
            $em->getConnection()->commit();

            return $this->render('SieEspecialBundle:CreacionCursosEspecial:new.html.twig',array(
                'form'=>$form->createView(),
                'institucion' => $institucion,
                'gestion' => $gestion
            ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
//         ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'attr'=>array('class'=>'form-control')))

//         ->add('programa','choice',array('label'=>'Programa','choices'=>$programas,'attr'=>array('class'=>'form-control')))
//         ->add('servicio','choice',array('label'=>'Servicio','choices'=>$servicios,'attr'=>array('class'=>'form-control')))
//         ->add('tecnica','choice',array('label'=>'Tecnica','choices'=>$tecnicas,'attr'=>array('class'=>'form-control')))

    }

    public function createAction(Request $request){
        try{
           
            $form = $request->get('form');
            //dump($form);die;
            $nivelTecnico = 99;
            if(isset($form['nivelTecnico'])){
                $nivelTecnico = $form['nivelTecnico']?$form['nivelTecnico']:99;
            }
            $momento = 99;
            if(isset($form['momento']) && $form['programa']==26){
                $momento = $form['momento']?$form['momento']:99;
            }

            $especialidad = 99;
            if(isset($form['nivel']) && $form['nivel']==405){ // si es tecnica
                $especialidad = $form['tecnica']?$form['tecnica']:99;
            }
            //dump($nivelTecnico);die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_especial');")->execute();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();

            $em->getConnection()->beginTransaction();

            /*
             * Verificamos si existe el curso
             */

            $query = $em->createQuery(
                    'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                    JOIN iec.institucioneducativaCurso ie
                    WHERE ie.institucioneducativa = :idInstitucion
                    AND ie.gestionTipo = :gestion
                    AND ie.turnoTipo = :turno
                    AND ie.nivelTipo = :nivel
                    AND ie.gradoTipo = :grado
                    AND ie.paraleloTipo = :paralelo
                    AND iec.especialAreaTipo =:area
                    AND iec.especialProgramaTipo =:programa
                    AND iec.especialServicioTipo =:servicio
                    AND iec.especialNivelTecnicoTipo =:niveltecnico
                    AND iec.especialTecnicaEspecialidadTipo =:tecnica
                    AND iec.especialMomentoTipo =:momento
                    ORDER BY ie.turnoTipo, ie.nivelTipo, ie.gradoTipo, ie.paraleloTipo')
                                ->setParameter('idInstitucion', $form['idInstitucion'])
                                ->setParameter('gestion', $form['idGestion'])
                                ->setParameter('turno', $form['turno'])
                                ->setParameter('nivel', $form['nivel'])
                                ->setParameter('grado', $form['grado'])
                                ->setParameter('paralelo', $form['paralelo'])
                                ->setParameter('area', $form['area'])
                                ->setParameter('programa', $form['programa'])
                                ->setParameter('servicio', $form['servicio'])
                                ->setParameter('niveltecnico', $nivelTecnico)
                                ->setParameter('tecnica', $especialidad)
                                ->setParameter('momento', $form['momento']);
                                $curso = $query->getResult(); 


//             $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array(  'institucioneducativaCurso.institucioneducativa'=>$form['idInstitucion'],
//                     'institucioneducativaCurso.gestionTipo'=>$form['idGestion'],
//                     'institucioneducativaCurso.turnoTipo'=>$form['turno'],
//                  'especialAreaTipo'=>$form['area'],
//                     'institucioneducativaCurso.nivelTipo'=>$form['nivel'],
//                     'institucioneducativaCurso.gradoTipo'=>$form['grado'],
//                     'institucioneducativaCurso.paraleloTipo'=>$form['paralelo'],
//                     'especialProgramaTipo'=>$form['programa'],
//                     'especialServicioTipo'=>$form['servicio'],
//                  'especialNivelTecnicoTipo'=>$form['nivelTecnico'],
//                  'especialTecnicaEspecialidadTipo'=>$form['tecnica']));
            //dump($form);die;
           // dump($curso);die;
            if($curso){
                $this->get('session')->getFlashBag()->add('newCursoError', 'No se pudo crear el curso, ya existe un curso con las mismas características.');
                return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
            }else{
                /**
                 * Asignacion de lugar para educacion en casa
                 * LUGAR = EDUCACION SOCIOCOMUNITARIA EN CASA 
                 */

                $lugar = "";
                //dump($form['educacionCasa']);die;
                if ((isset($form['educacionCasa']) and ($form['educacionCasa'] == 1 or $form['educacionCasa']==true))){
                    if(isset($form['multiple'])){
                        $lugar="EDUCACION SOCIOCOMUNITARIA EN CASA" . '-' . $form['multiple'];
                    }elseif(isset($form['fisicoMotor']) and $form['fisicoMotor'] != 'Otro'){
                        $lugar="EDUCACION SOCIOCOMUNITARIA EN CASA" . '-' . $form['fisicoMotor'];
                    }elseif(!isset($form['fisicoMotor']) and !isset($form['educacionCasa'])){
                         $lugar="";   
                    }else {
                        $lugar="EDUCACION SOCIOCOMUNITARIA EN CASA";
                    }                    
                }
                if (isset($form['multiple']) && $form['area'] == 5 && !isset($form['educacionCasa'])){
                        $lugar=$form['multiple'];
                }
                // Si no existe el curso
                // curso generico SIE
                $nuevo_curso_sie = new InstitucioneducativaCurso();
                $nuevo_curso_sie->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['idGestion']));
                $nuevo_curso_sie->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
                $nuevo_curso_sie->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                $nuevo_curso_sie->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
                $nuevo_curso_sie->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
                $nuevo_curso_sie->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
                $nuevo_curso_sie->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
                $nuevo_curso_sie->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
                $nuevo_curso_sie->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $nuevo_curso_sie->setLugar(mb_strtoupper($lugar,'utf-8'));
                $em->persist($nuevo_curso_sie);
                $em->flush();
            
                $nuevo_curso = new InstitucioneducativaCursoEspecial();
                $nuevo_curso->setInstitucioneducativaCurso($nuevo_curso_sie);
                $nuevo_curso->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($form['area']));
                $nuevo_curso->setEspecialProgramaTipo($em->getRepository('SieAppWebBundle:EspecialProgramaTipo')->find($form['programa']));
                $nuevo_curso->setEspecialServicioTipo($em->getRepository('SieAppWebBundle:EspecialServicioTipo')->find($form['servicio']));
                $nuevo_curso->setEspecialTecnicaEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialTecnicaEspecialidadTipo')->find($especialidad));
                $nuevo_curso->setEspecialNivelTecnicoTipo($em->getRepository('SieAppWebBundle:EspecialNivelTecnicoTipo')->find($nivelTecnico));
                $nuevo_curso->setEspecialModalidadTipo($em->getRepository('SieAppWebBundle:EspecialModalidadTipo')->find($form['modalidad']));
                $nuevo_curso->setEspecialMomentoTipo($em->getRepository('SieAppWebBundle:EspecialMomentoTipo')->find($momento));
                $em->persist($nuevo_curso);
                $em->flush();
            
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newCursoOk', 'La oferta fué creada correctamente');
                return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
            }
            
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }

    }

    public function deleteAction(Request $request){
        try{

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($request->get('idCurso'));
            $cursosie = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($curso->getInstitucioneducativaCurso()->getId());

            // remove modalidad on course
            $objModalidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findBy(array('institucioneducativaCurso'=>$cursosie->getId()));
            if($objModalidad); {
                foreach ($objModalidad as $keyMod => $valueMod) {
                    $em->remove($valueMod);
                    $em->flush();
                }
            }            
            // end remove modalidad on course
            /*
             * Verificamos si tiene estudiantes inscritos
             */
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findBy(array('institucioneducativaCursoEspecial'=>$request->get('idCurso')));
            if($inscritos){
                $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque tiene estudiantes inscritos');
                return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
            }

            /*
             * Verificamos si no tiene registros en curso oferta
             */
            $curso_oferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$cursosie->getId()));
            if($curso_oferta){
                foreach ($curso_oferta as $key => $value) {
                    $curso_oferta_maestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$value->getId()));
                    if($curso_oferta_maestro); {
                        foreach ($curso_oferta_maestro as $keyM => $valueM) {
                            $em->remove($valueM);
                            $em->flush();
                        }
                    }
                    $estudiante_asignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$value->getId()));
                    if($estudiante_asignatura); {
                        foreach ($estudiante_asignatura as $keyEA => $valueEA) {
                            $em->remove($valueEA);
                            $em->flush();
                        }
                    }
                    $em->remove($value);
                    $em->flush();
                }
                //$this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque cuenta con asignaturas');
                //return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
            }

            $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $cursosie));
            if($objInscription){
                foreach($objInscription as $key => $value){
                    $objInscriptionObservacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findby(array('estudianteInscripcion'=>$value));
                    if($objInscriptionObservacion){
                      foreach($objInscriptionObservacion as $valueO) {
                        $em->remove($valueO);
                        $em->flush();
                      }
                    }
                    $objInscriptionSpecial = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneBy(array('estudianteInscripcion' => $value));
                    if($objInscriptionSpecial){
                      $em->remove($objInscriptionSpecial);
                      $em->flush();
                    }
                    $em->remove($value);
                    $em->flush();
                }
            }

            /*
             * Eliminamos el curso
             */
            $em->remove($curso);
            $em->flush();

            /*
             * Eliminamos el curso SIE
             */
            $em->remove($cursosie);
            $em->flush();


            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteCursoOk', 'Se eliminó la oferta correctamente');
            return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteCursoError', $ex->getMessage());
            return $this->redirect($this->generateUrl('creacioncursos_especial',array('op'=>'result')));
        }
    }


    public function listarNivelesAction($area,$modalidad) {
        
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();

        if ($area == "1" ) { //AUDITIVA
            if($modalidad == 1){
                $nivelesArray = array(403,404,405,410,411);
                if ($this->session->get('idGestion') > 2023) {
                    $nivelesArray =  array(403,404,405,411);
                }
            }else{
                $nivelesArray = array(410);
            }
        }
        elseif ($area == "2" ) { //VISUAL
            if($modalidad == 1){
                $nivelesArray = array(411); 
            }else{
                $nivelesArray = array(410);
            }
        }
        elseif ($area == "3" or $area == "5" or $area == "12" ) { //intelectual - multiple - autista
            
            if($modalidad == 1){ //directa
                if ($this->session->get('idGestion') < 2020) {
                    $nivelesArray = array(401,402,405,410);
                }
                else{
                    $nivelesArray = array(400,402,405,410,408,411);
                }
                if ($this->session->get('idGestion') >= 2023) { //nuevo plan
                    $nivelesArray = array(409,401,405,412,410,411);
                }
                if ($this->session->get('idGestion') > 2023 AND  $area == "12" ) { //nuevo plan
                    $nivelesArray = array(409,401,412,411);
                }
                if ($this->session->get('idGestion') > 2023 AND $area == "3") { //nuevo plan
                    $nivelesArray = array(409,401,405,412,411);
                }
                
            }else{
                $nivelesArray = array(410);
            }
        }
        elseif ($area == "4" ) {   //educacion en casa alternativa-regular fisico-motora
            if ($this->session->get('idGestion') < 2020) {
                $nivelesArray = array(99);
            }else{
                
                if($modalidad == 1){
                    $nivelesArray = array(410,411);
                }else{
                    $nivelesArray = array(410);
                }
                
            }
        }
        elseif ($area == "6" ) {    //DIFICULTADES EN EL APRENDIZAJE
            if($modalidad == 1){
                $nivelesArray = array(411);
            }else{
                $nivelesArray = array(410);
            }
        }
        /*
        elseif ($area == "8" ) {
                $query = $em->createQuery(
                        'SELECT n.id, n.nivel FROM SieAppWebBundle:NivelTipo n
                                    WHERE n.id IN (:id)'    
                        )->setParameter('id',array(406,410,411));

        }
        elseif ($area == "9" ) {
            $query = $em->createQuery(
                    'SELECT n.id, n.nivel FROM SieAppWebBundle:NivelTipo n
                                    WHERE n.id IN (:id)'
                    )->setParameter('id',array(410));

        }
        elseif ($area == "100" ) {
            $query = $em->createQuery(
                    'SELECT n.id, n.nivel FROM SieAppWebBundle:NivelTipo n
                                    WHERE n.id IN (:id)'
                    )->setParameter('id',array(999));

        }*/
        elseif ($area == "7") {    //TALENTO EXTRAORDINARIO
            if($modalidad == 1){
                $nivelesArray = array(411);
            }else{
                $nivelesArray = array(410);
            }
        }
       
        elseif ($area == "10") {    //MENTAL-PSIQUICA
            if($modalidad == 1){
                $nivelesArray = array(411);
            }
        }
        $query = $em->createQuery(
                'SELECT n.id, n.nivel FROM SieAppWebBundle:NivelTipo n
                            WHERE n.id IN (:id)'
                )->setParameter('id',$nivelesArray);
        
        $niveles = $query->getResult();
        
        $nivelesArray = array();
        for($i=0;$i<count($niveles);$i++){
            $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        }
        $response = new JsonResponse();
        return $response->setData(array('niveles' => $nivelesArray));
    }

    public function listarGradosAction($nivel, $area) { //dump($nivel, $area);die;

        $this->session = new Session();

        $em = $this->getDoctrine()->getManager();
        if ($nivel == "401" ) {
            $grados = array(1,2);
        }
        elseif ($nivel == "400") {
            $grados = array(1,2);
        }
        elseif ($nivel == "408") {
            $grados = array(1,2,3);
        }
        elseif ($nivel == "412") {
            $grados = array(1,2,3,4,5,6);
        }
        elseif ($nivel == "402") {
            if ($this->session->get('idGestion') < 2021) {
                $grados = array(1,2,3,4,5,6);
            }else{
                $grados = array(4,5,6);
            }
        }elseif ($nivel == "406" ) {
            $grados = array(1,2,3);
        }
        elseif ($nivel == "403" ) {
            $grados = array(1,2);
        }
        elseif ($nivel == "404" ) { //EDUC PRIMARIA
            $grados = array(1,2,3,4,5,6);
        }
        elseif ($nivel == "405" ) { //TECNICA
            $grados = array(1,2,3,4,5,6);
            if($area==1 or $area==2 or $area==4){
                $grados = array(1,2);
            }
        }
        elseif ($nivel == "407"  ) {
            $grados = array(41,42,43);
        } 
        elseif ($nivel == "413" || $nivel == "414"  || $nivel == "415" ) {
            $grados = array(44,45,46);
        }         
        else  {
            $grados = array(99);
        }

        
        $query = $em->createQuery(
            'SELECT g.id, g.grado FROM SieAppWebBundle:GradoTipo g
                            WHERE g.id IN (:id)'
            )->setParameter('id',$grados);

        $grados = $query->getResult();
        $gradosArray = array();
        for($i=0;$i<count($grados);$i++){
            $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
        }
        $response = new JsonResponse();
        return $response->setData(array('grados' => $gradosArray));
    }

    public function listarNivelesTecnicosAction($nivel, $area) { 

        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $niveltecnico = '';
        if ($nivel == "404" or $nivel == "405" ) { //TECNICA
            $niveltecnico = array(1,2,3,4,5,6);
            if($area==1){
                $niveltecnico = array(1,2,3,4);
            }
        }
        $nivelestecnicosArray = array();
        if($niveltecnico){
            $query = $em->createQuery(
                'SELECT g.id, g.nivelTecnico FROM SieAppWebBundle:EspecialNivelTecnicoTipo g
                                WHERE g.id IN (:id)'
                )->setParameter('id',$niveltecnico);

            $nivelestecnicos = $query->getResult();
            
            for($i=0;$i<count($nivelestecnicos);$i++){
                $nivelestecnicosArray[$nivelestecnicos[$i]['id']] = $nivelestecnicos[$i]['nivelTecnico'];
            }
        }
            //dump($nivelestecnicosArray);die;
        $response = new JsonResponse();
        return $response->setData(array('nivelestecnicos' => $nivelestecnicosArray));
    }

    public function listarModalidadesAction($area) { 

        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $ids = array(1,2);
        if($area==2 && $this->session->get('idGestion')>=2023) //visual
            $ids = array(1,2,3);
        
        if( $this->session->get('idGestion')>2023) // para todas las areas 
            $ids = array(1,2,3);

        if($area==10) //mental-psiquica
            $ids = array(1);

        $modalidadesArray = array();
            $query = $em->createQuery(
                'SELECT m.id, m.modalidad FROM SieAppWebBundle:EspecialModalidadTipo m
                                WHERE m.id IN (:id)'
                )->setParameter('id',$ids);

            $modalidades = $query->getResult();
            
            for($i=0;$i<count($modalidades);$i++){
                $modalidadesArray[$modalidades[$i]['id']] = $modalidades[$i]['modalidad'];
            }
            //dump( $modalidadesArray);die;
        $response = new JsonResponse();
        return $response->setData(array('modalidades' => $modalidadesArray));
    }


    public function listarServiciosAction($area,$nivel,$grado,$modalidad) {
       // dump($area);dump($nivel);dump($grado);dump($modalidad);die;
        $em = $this->getDoctrine()->getManager();
        $this->session = new Session();
        
        if ($area == "6" and $nivel == "410" and  $grado == "99" ) {
            if($modalidad == 1){
                $servicios = array(1,2,3,4,5);
            }else{
                $servicios = array(20);
            }//array(6,7,20)
            if($modalidad == 3 AND $area == "6" ){
                $servicios = array(1,2,3,4,5);
            }
        }
        elseif ($area == "7" and $nivel == "410" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $servicios = array(8,9,10,11,12,13,14,15,16,17,18,19);
            } else {
                if($modalidad == 1){
                    $servicios = array(10,11,12,18,22); //--14,15
                    if ($this->session->get('idGestion') > 2020) {
                        $servicios = array(1,2,3,4,5,25,26,27,28);
                    }
                } else {
                    //$servicios = array(20);//8,9
                    $servicios = array(8,9,30,31);//8,9
                }
                if($modalidad == 3){
                    $servicios = array(1,2,25,4,29);
                }

            }//array(8,9,10,11,12,14,15)
        }
        elseif (($area == "1" or $area == "3" or $area == "4" or $area == "5" or $area == "8" or $area == "9"  or $area == "12")  and $nivel == "410" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $servicios =array(); // array(1,2,3,4,5);
            }else{
                if($modalidad == 1 and ($area == "1" or $area == "3" or $area == "5" or $area == "4" or $area == "12")){
                    $servicios = array(1,2,3,4,5,25,26,27,28,29);
                    
                    if($area == 1 and $this->session->get('idGestion') > 2022){
                        $servicios = array(1,2,3,4,5,25,26,27,28,29,40);
                    }

                }else{
                    $servicios = array(20);
                }

                if($modalidad == 3  AND $area == "1"){
                    $servicios = array(1,2,3,4,25,27,40);
                }
                if($modalidad == 3 AND ($area == "3" OR $area == "12" ) ){
                    $servicios = array(5,1,2,3,4,25,27,26,28);
                }
                if($modalidad == 3 AND $area == "4" ){
                    $servicios = array(3, 27,2,25,1,4,5);
                }
               
               
            }
            //array(1,2,3,4,5,20)
        }
        elseif ($area == "2" and $nivel == "410" and  $grado == "99" ) {
            if($modalidad == 1){
                $servicios = array(1,2,3,4,5,28);
            }else{
                $servicios = array(21);
                if($this->session->get('idGestion') > 2023){
                    $servicios = array(20);
                }
            }//array(1,2,3,4,5,21)
            if($modalidad == 3){
                $servicios = array(1,2,3,4,5,28,35,36,37,38);
            }
            
        }
        elseif (($area == "9")  and $nivel == "410" and  $grado == "99" ) {
            $servicios = array(20);
        }
        else {
            $servicios = array(99);
        }
        //dump($servicios);die;
        $query = $em->createQuery(
            'SELECT s.id, s.servicio FROM SieAppWebBundle:EspecialServicioTipo s
                            WHERE s.id in (:id)'
            )->setParameter('id',$servicios);

        $servicios = $query->getResult();
        $serviciosArray = array();
        for($i=0;$i<count($servicios);$i++){
            $serviciosArray[$servicios[$i]['id']] = $servicios[$i]['servicio'];
        }
        $response = new JsonResponse();
        return $response->setData(array('servicios' => $serviciosArray));
    }

    public function listarProgramasAction($area,$nivel,$grado,$modalidad) {
       // dump($area);
       // dump($nivel);die;
        $em = $this->getDoctrine()->getManager();
        $this->session = new Session();
        if ( $area == "1" and $nivel == "411" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $programas = array(13);
            } else {
                
                $programas = array(19, 20, 21, 22);
            }
            if ($this->session->get('idGestion') == 2023) {
                $programas = array(39,19,22,41,42,43,44,45,46);
            }
            if ($this->session->get('idGestion') > 2023) {
                $programas = array(39,19,22,41,42,43,44,45);
            }
            
        }
        elseif ($area == "2" and $nivel == "411" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $programas = array(7,8,9,10,11,12,14,15,16);
            }else{
                if($modalidad == 1){ //DIRECTA
                    if ($this->session->get('idGestion') < 2021) {
                        $programas = array(7,8,9,11,12,14,15,16);
                    }
                    if ($this->session->get('idGestion') == 2021) {
                        $programas = array(7,8,9,12,15,25,26,27);   //--- se agrego nuevos programas cambio denom en BD 24=25
                    }
                    if ($this->session->get('idGestion') > 2021) {
                        $programas = array(7,8,12,25,26,29);   //--- se agrego nuevos programas cambio denom en BD 24=25
                    }
                    if ($this->session->get('idGestion') >=2023) {
                        $programas = array(7,8,25,26,27,47,48);   //
                    }
                }else{ //INDIRECTA
                    $programas = array(10);
                }
            }//array(7,8,9,10,11,12,14,15,16)
        }
        elseif (($area == "3" or $area == "5") and ($nivel == "411" or $nivel == "409") and  $grado == "99" ) { //Programas
            $programas = array(99);

            if ($this->session->get('idGestion') > 2020) {
                $programas = array(28,30);
            }
            if ($this->session->get('idGestion') >=2023 && $nivel=="411") {
                $programas = array(37,38);   //--- 
            }
            if ($this->session->get('idGestion') >=2023 && $nivel=="409") {
                $programas = array(28);   //--- 
            }
        }
        elseif (( $area == "12") and ($nivel == "411" or $nivel == "409" ) and  $grado == "99" ) { //Programas
                $programas = array(99);
    
                if ($this->session->get('idGestion') ==2023 && $nivel=="411") {
                    $programas = array(38);   //--- 
                }
                if ($this->session->get('idGestion') >2023 && $nivel=="411") {
                    $programas = array(37,38);   //--- 
                }
                if ($this->session->get('idGestion') >=2023 && $nivel=="409") {
                    $programas = array(28);   //--- 
                }
            
        }elseif ($area == "6" and $nivel == "411" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $programas = array(1,2,3,4,5,6);
            }else{
                $programas = array(5,6,17);
            }
        }elseif ($area == "4" and $nivel == "411" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $programas = array(99);
            }else{
                $programas = array(18);
            }
            if ($this->session->get('idGestion') >= 2023) {
                $programas = array(28);
            }
        }elseif ($area == "7" and $nivel == "411" and  $grado == "99" ) {
            if ($this->session->get('idGestion') < 2020) {
                $programas = array(99);
            } else {
                if($modalidad == 1){
                    //$programas = array(23, 24);
                    $programas = array(31,32,33,34);
                    
                }else{
                    $programas = array(99);
                }
            }
        }elseif ($area == "10" and $nivel == "411" and  $grado == "99" ) { // MENTAL PSIQUICA + PROGRAMAS
           
                if($modalidad == 1){
                    $programas = array(50,51,52,53,54,55,56,57,58,59);   
                }
            
        }else{
            $programas = array(99);
        }

        $query = $em->createQuery(
            'SELECT p.id, p.programa FROM SieAppWebBundle:EspecialProgramaTipo p
                    WHERE p.id IN (:id)'
            )->setParameter('id',$programas);

        $lista = $query->getResult();
        $programasArray = array();
        for($i=0;$i<count($lista);$i++){
            $programasArray[$lista[$i]['id']] = $lista[$i]['programa'];
        }
        $response = new JsonResponse();
        return $response->setData(array('programas' => $programasArray));
    }





//     public function listarNivelesAction($area){
//      $em = $this->getDoctrine()->getManager();
//      $query = $em->createQuery(
//              'SELECT DISTINCT n.id, n.nivel FROM SieAppWebBundle:NivelTipo n
//                                     WHERE n.id IN (:id)'
//              )->setParameter('id',array(410,411));

//                  $niveles = $query->getResult();
//                  $nivelesArray = array();
//                  for($i=0;$i<count($niveles);$i++){
//                      $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
//                  }
//                  //print_r($nivelesArray);die;
//                  $response = new JsonResponse();
//                  return $response->setData(array('niveles' => $nivelesArray));
//     }


}
