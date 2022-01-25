<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;

/**
 * EstudianteInscripcion controller.
 *
 */
class CreacionCursosController extends Controller {

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
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Creación de Cursos');
                                $this->session->set('layout','layoutRegular.html.twig');break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                                    $this->session->set('layout','layoutAlternativa.html.twig');break;
                default:    $this->session->set('tituloTipo', 'Paralelos');
                            $this->session->set('layout','layoutRegular.html.twig');break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                
                    /*
                     * verificamos si existe la unidad educativa
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                    if (!$institucioneducativa) {
                        $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                        return $this->render('SieRegularBundle:CreacionCursos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                        return $this->render('SieRegularBundle:CreacionCursos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieRegularBundle:CreacionCursos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieRegularBundle:CreacionCursos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = $request->getSession()->get('currentyear');
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Lista de cursos institucioneducativa
            $query = $em->createQuery(
                    'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('niveles',array(11,12,13));

            $cursos = $query->getResult();
            /*
             * obtenemos los datos de la unidad educativa
             */
            //$est = $niveles;
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            
            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$institucion,'gestionTipo'=>$gestion,'cargoTipo'=>0));
            
            $em->getConnection()->commit();
            
            return $this->render('SieRegularBundle:CreacionCursos:index.html.twig', array(
                        'cursos' => $cursos, 'institucion' => $institucion, 'gestion' => $gestion,'maestros'=>$maestros
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:CreacionCursos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }
    
    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1,$gestionactual - 2 => $gestionactual - 2);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('creacioncursos'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
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
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            /*
             * opciones para los usuarios no nacionales
             */
            if($this->session->get('roluser') != 8){
            
                // Lista de turnos validos para la unidad educativa
                $query = $em->createQuery(
                        'SELECT DISTINCT tt.id,tt.turno
                        FROM SieAppWebBundle:InstitucioneducativaCurso iec
                        JOIN iec.institucioneducativa ie
                        JOIN iec.turnoTipo tt
                        WHERE ie.id = :id
                        AND iec.gestionTipo = :gestion
                        ORDER BY tt.id')
                        ->setParameter('id',$this->session->get('idInstitucion'))
                        ->setParameter('gestion',$this->session->get('idGestion'));
                $turnos = $query->getResult();
                $turnosArray = array();
                for($i=0;$i<count($turnos);$i++){
                    $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
                }
                /*
                 * Listamos los niveles validos
                 */
                $query = $em->createQuery(
                                        'SELECT n FROM SieAppWebBundle:NivelTipo n
                                        WHERE n.id IN (:id)'
                                        )->setParameter('id',array(11,12,13));
                $niveles_result = $query->getResult();
                $niveles = array();
                /*foreach ($niveles_result as $n){
                    $niveles[$n->getId()] = $n->getNivel();
                }*/
            }else{
                /*
                 * Listamos los turnos validos
                 */
                $query = $em->createQuery(
                                        'SELECT t FROM SieAppWebBundle:TurnoTipo t
                                        WHERE t.id IN (:id)'
                                        )->setParameter('id',array(1,2,4,8,9,10,11));
                $turnos_result = $query->getResult();
                $turnosArray = array();
                foreach ($turnos_result as $t){
                    $turnosArray[$t->getId()] = $t->getTurno();
                }
                /*
                 * Listamos los niveles validos
                 */
                $query = $em->createQuery(
                                        'SELECT n FROM SieAppWebBundle:NivelTipo n
                                        WHERE n.id IN (:id)'
                                        )->setParameter('id',array(11,12,13));
                $niveles_result = $query->getResult();
                $niveles = array();
                foreach ($niveles_result as $n){
                    $niveles[$n->getId()] = $n->getNivel();
                }
            }
            /*
             * Listamos los grados para nivel inicial 
             */
            $query = $em->createQuery(
                                    'SELECT g FROM SieAppWebBundle:GradoTipo g
                                    WHERE g.id IN (:id)'
                                    )->setParameter('id',array(1,2));
            $grados_result = $query->getResult();
            $grados = array();
            foreach ($grados_result as $g){
                $grados[$g->getId()] = $g->getGrado();
            }
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
            if($this->session->get('roluser') != 8){
                $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('creacioncursos_create'))
                        ->add('idInstitucion','hidden',array('data'=>$request->get('idInstitucion')))
                        ->add('idGestion','hidden',array('data'=>$request->get('idGestion')))
                        ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarNiveles(this.value)')))
                        ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarGrados(this.value)')))      
                        ->add('grado','choice',array('label'=>'Grado','choices'=>$grados,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('guardar','submit',array('label'=>'Crear Curso','attr'=>array('class'=>'btn btn-primary')))
                        ->getForm();
            }else{
                $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('creacioncursos_create'))
                        ->add('idInstitucion','hidden',array('data'=>$request->get('idInstitucion')))
                        ->add('idGestion','hidden',array('data'=>$request->get('idGestion')))
                        ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarGrados(this.value)')))
                        ->add('grado','choice',array('label'=>'Grado','choices'=>$grados,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('guardar','submit',array('label'=>'Crear Curso','attr'=>array('class'=>'btn btn-primary')))
                        ->getForm();
            }
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:CreacionCursos:new.html.twig',array('form'=>$form->createView(),'institucion'=>$institucion,'gestion'=>$request->get('idGestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $form = $request->get('form');
            /*
             * Verificamos si existe el curso
             */
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(  'institucioneducativa'=>$form['idInstitucion'],
                                                                                                        'gestionTipo'=>$form['idGestion'],
                                                                                                        'turnoTipo'=>$form['turno'],
                                                                                                        'nivelTipo'=>$form['nivel'],
                                                                                                        'gradoTipo'=>$form['grado'],
                                                                                                        'paraleloTipo'=>$form['paralelo']));
            if($curso){ 
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('newCursoError', 'No se pudo crear el curso, ya existe un curso con las mismas características.');
                return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            }else{ 
                // Actualizamos el increment de la tabla
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');");
                $query->execute();

                // Si no existe el curso
                $nuevo_curso = new InstitucioneducativaCurso();
                $nuevo_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['idGestion']));
                $nuevo_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
                $nuevo_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $nuevo_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                switch($form['nivel']){
                    case 11: $ciclo=1;break;
                    case 12: 
                            switch($form['grado']){
                                case 1:
                                case 2: 
                                case 3: $ciclo = 1;break;
                                case 4:
                                case 5:
                                case 6: $ciclo = 2;break;
                            }
                            break;
                    case 13: 
                            switch($form['grado']){
                                case 1:
                                case 2: $ciclo = 1;break;
                                case 3: 
                                case 4: $ciclo = 2;break;
                                case 5:
                                case 6: $ciclo = 3;break;
                            }
                            break;
                }
                
                $nuevo_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find($ciclo));
                $nuevo_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
                $nuevo_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
                $nuevo_curso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
                $nuevo_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
                $nuevo_curso->setMultigrado(0);
                $nuevo_curso->setDesayunoEscolar(1);
                $nuevo_curso->setModalidadEnsenanza(1);
                $nuevo_curso->setIdiomaMasHabladoTipoId(48);
                $nuevo_curso->setIdiomaRegHabladoTipoId(0);
                $nuevo_curso->setIdiomaMenHabladoTipoId(0);
                $nuevo_curso->setPriLenEnsenanzaTipoId(48);
                $nuevo_curso->setSegLenEnsenanzaTipoId(0);
                $nuevo_curso->setTerLenEnsenanzaTipoId(1);
                $nuevo_curso->setFinDesEscolarTipoId(4);
                switch ($form['nivel']) {
                    case '11': $nroMaterias = 4;break;
                    case '12': $nroMaterias = 9;break;
                    case '13': $nroMaterias = 11;break;
                    
                    default:
                        $nroMaterias = 4;
                        break;
                }
                $nuevo_curso->setNroMaterias($nroMaterias);
                $nuevo_curso->setConsolidado(1);
                $nuevo_curso->setPeriodicidadTipoId(1111100);
                $nuevo_curso->setNotaPeriodoTipo($em->getRepository('SieAppWebBundle:NotaPeriodoTipo')->find(4));

                $em->persist($nuevo_curso);
                $em->flush();


                /**
                 * Obtenemos las asignaturas humanisticas en funcion al nivel
                 */
                switch($form['nivel']){
                    case 11:    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.asignaturaNivel = :idNivel
                                        AND at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('idNivel', $form['nivel'])
                                ->setParameter('ids',array(1000,1001,1002,1003))
                                ->getResult();
                                break;

                    case 12:    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.asignaturaNivel = :idNivel
                                        AND at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('idNivel', $form['nivel'])
                                ->setParameter('ids',array(1011,1012,1013,1014,1015,1016,1017,1018,1019))
                                ->getResult();
                                break;  
                    case 13:    switch ($form['grado']) {
                                    case 1:
                                    case 2:
                                        $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.asignaturaNivel = :idNivel
                                                AND at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('idNivel', $form['nivel'])
                                        ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044))
                                        ->getResult();
                                        break;
                                    case 3:
                                    case 4:
                                        $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.asignaturaNivel = :idNivel
                                                AND at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('idNivel', $form['nivel'])
                                        ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045))
                                        ->getResult();
                                        break;
                                    case 5:
                                    case 6:
                                        $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.asignaturaNivel = :idNivel
                                                AND at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('idNivel', $form['nivel'])
                                        ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045))
                                        ->getResult();
                                        break;

                                }
                                break;
                }
                /**
                 * Registramos las asignaturas para el curso en la tabla curso oferta
                 * y Actualizamos el increment de la tabla
                 */
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                
                foreach ($asignaturas as $a) {
                    $newCursoOferta = new InstitucioneducativaCursoOferta();
                    $newCursoOferta->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($a->getId()));    
                    //$newCursoOferta->setHorasmes();
                    //$newCursoOferta->setAsignaturaEspecialidadTipo($em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find(0));
                    $newCursoOferta->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($nuevo_curso->getId()));
                    $em->persist($newCursoOferta);
                    $em->flush();
                }

                
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newCursoOk', 'Curso creado correctamente');
                return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            }
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function deleteAction(Request $request){
        try{

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            /*
             * Verificamos si tiene estudiantes inscritos
             */
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$request->get('idCurso')));
            if($inscritos){
                $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque tiene estudiantes inscritos');
                return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            }
            /*
             * Verificamos si no tiene registros en curso oferta
             
            $curso_oferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$request->get('idCurso')));
            if($curso_oferta){
                $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque cuenta con asignaturas');
                return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            }*/
            
            /**
             * Eliminamos los maestros de curso oferta maestro
             */
            $curso_oferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$request->get('idCurso')));
            foreach ($curso_oferta as $co) {
                $em->createQuery('DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                              WHERE iecom.institucioneducativaCursoOferta = :idCursoOferta')
                              ->setParameter('idCursoOferta',$co->getId())->execute();
            }

            /**
             * Eliminamos los registros de curso oferta
             */
            $em->createQuery('DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                              WHERE ieco.insitucioneducativaCurso = :idCurso')
                              ->setParameter('idCurso',$curso->getId())->execute();
            /*
             * Eliminamos el curso
             */
            $em->remove($curso);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteCursoOk', 'Se eliminó el curso correctamente');
            return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteCursoError', $ex->getMessage());
            return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
        }
    }
    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarnivelesAction($turno){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            $idInstitucion = $this->session->get('idInstitucion');
            $gestion = $this->session->get('idGestion');
            $query = $em->createQuery(
                    'SELECT DISTINCT nt.id,nt.nivel
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.nivelTipo nt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    ORDER BY nt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno);
            $niveles = $query->getResult();
            $nivelesArray = array();
            for($i=0;$i<count($niveles);$i++){
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('listaniveles' => $nivelesArray));
        }catch(Exception $ex){
            //$em->getConnection()->rollback();
        }
    }
    /**
     * Funcion para cargar los grados segun el nivel, para el nuevo curso
     */
    public function listargradosAction($nivel) {
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //$dep = $em->getRepository('SieAppWebBundle:GradoTipo')->findAll();
            if ($nivel == 11) {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2));
            } else {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2, 3, 4, 5, 6));
            }
            $gra = $query->getResult();
            $lista = array();
            foreach ($gra as $gr) {
                $lista[$gr->getId()] = $gr->getGrado();
            }
            $list = $lista;
            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array('listagrados' => $list));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
}
