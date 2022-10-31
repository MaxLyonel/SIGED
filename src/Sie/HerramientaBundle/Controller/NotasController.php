<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class NotasController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            $em = $this->getDoctrine()->getManager();
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Calificaciones');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Calificaciones');
                    break;
                default: $this->session->set('tituloTipo', 'Calificaciones');
                    break;
            }

            /**
             * Identificamos el rol del usuario
             * 2- maestro, 9 - administrativo
             * 10 - Distrito, 7 - Departamento, 8-Nacional
             */
            $rolUsuario = $this->session->get('roluser');
            
            /**
             * Si se enviaron datos desde un formulario
             */
            if ($request->getMethod() == 'POST') {
                $form = $request->get('form');
                /*
                 * Preguntamos si los datos provienen del formulario de busqueda de docentes
                 * o de unidad educativa por nivel grado y paralelo
                 */
                if($form['tipoForm'] == 'maestro'){
                    /*
                     * verificamos si el docente existe
                     */
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet'=>$form['carnet']));
                    if($persona){
                        $idPersona = $persona->getId();
                        $gestion = $form['gestion'];
                        $this->session->set('notasPersona', $idPersona);
                        $this->session->set('notasGestion', $gestion);
                    }else{
                        $this->get('session')->getFlashBag()->add('noSearch', 'El numero de carnet ingresado no es válido');
                        return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                }else{
                    /**
                     * Si los datos provienen del formulario de vusqueda de unidad educativa
                     */
                    $gestion = $this->session->get('currentyear') -1;
                    $query = $em->createQuery(
                            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                            WHERE iec.institucioneducativa = :idInstitucion
                            AND iec.gestionTipo = :gestion
                            AND iec.turnoTipo = :turno
                            AND iec.nivelTipo = :nivel
                            AND iec.gradoTipo = :grado
                            AND iec.paraleloTipo = :paralelo')
                            ->setParameter('idInstitucion', $form['idInstitucion'])
                            ->setParameter('gestion', $gestion)
                            ->setParameter('turno', $form['turno'])
                            ->setParameter('nivel', $form['nivel'])
                            ->setParameter('grado', $form['grado'])
                            ->setParameter('paralelo', $form['paralelo']);

                    $cursos = $query->getResult();
                    /**
                     * Verificamos si existe el curso con los datos introducidos
                     * si los datos son incorrectos direccionamos nuevamente al formulario de busqueda
                     * segun si es unidad educativa (datosue) o es otro usuario (ue)
                     */
                    if(!$cursos){
                        if($form['tipoForm'] == 'datosue'){
                            $this->get('session')->getFlashBag()->add('noSearch', 'Los datos introducidos no son correctos.');
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchDatosUE($form['idInstitucion'],$request->getSession()->get('currentyear'))->createView()));
                        }else{
                            $this->get('session')->getFlashBag()->add('noSearch', 'Los datos introducidos no son correctos, intentelo nuevamente.');
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchUE($request->getSession()->get('currentyear'))->createView()));
                        }
                    }
                    /**
                     * CReacion de sesiones de unidad educativa
                     */
                    $this->session->set('notasInstitucion',$form['idInstitucion']);
                    $this->session->set('notasGestion',$gestion);
                    $this->session->set('notasTurno',$form['turno']);
                    $this->session->set('notasNivel',$form['nivel']);
                    $this->session->set('notasGrado',$form['grado']);
                    $this->session->set('notasParalelo',$form['paralelo']);

                    $idInstitucion = $form['idInstitucion'];
                    $gestion = $gestion;
                    $turno = $form['turno'];
                    $nivel = $form['nivel'];
                    $grado = $form['grado'];
                    $paralelo = $form['paralelo'];

                    $tipoUsuario = 'UnidadEducativa';
                }
            } else {
                /**
                 * Trabajamos con la gestion actual
                 */
                $gestion = $request->getSession()->get('currentyear');
                switch ($rolUsuario) {
                    case 2: // MAestro
                        $idPersona = $this->session->get('personaId');
                        $tipoUsuario = 'maestro';
                        break;

                    case 9: // Administrativo
                        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find(80730356);
                        if($op == 'search' or $op != 'result'){
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchDatosUE($institucion,$gestion)->createView(),'DatosUE'=>'DatosUE'));
                        }
                        if($this->session->get('notasInstitucion')){
                            $idInstitucion = $this->session->get('notasInstitucion');
                            $gestion = $this->session->get('notasGestion');
                            $turno = $this->session->get('notasTurno');
                            $nivel = $this->session->get('notasNivel');
                            $grado = $this->session->get('notasGrado');
                            $paralelo = $this->session->get('notasParalelo');

                        }else{
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchDatosUE($institucion,$gestion)->createView(),'UE'=>'UE'));
                        }
                        $tipoUsuario = 'UnidadEducativa';
                        break;

                    case 7: // Departamento
                    case 8: // Nacional
                    case 10: // Distrito
                        if($op == 'search' or $op != 'result'){
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchUE($gestion)->createView(),'UE'=>'UE'));
                        }
                        /**
                         * Verificamos si existen las variables de session necesarias
                         * para armar nuevamente la grilla de asignaturas
                         */
                        if($this->session->get('notasInstitucion')){
                            $idInstitucion = $this->session->get('notasInstitucion');
                            $gestion = $this->session->get('notasGestion');
                            $turno = $this->session->get('notasTurno');
                            $nivel = $this->session->get('notasNivel');
                            $grado = $this->session->get('notasGrado');
                            $paralelo = $this->session->get('notasParalelo');
                            $tipoUsuario = 'UnidadEducativa';
                        }else{
                            return $this->render('SieHerramientaBundle:Notas:search.html.twig', array('form' => $this->formSearchUE($gestion)->createView(),'UE'=>'UE'));
                        }
                        break;
                    default:
                        return new Response('Hello ');
                        break;
                }
            }
            /*
             * Listamos las asignaturas que da la persona(docente) si el usuario tiene rol de maestro (2)
             */
            if($tipoUsuario == 'maestro'){
                $persona = $em->getRepository('SieAppWebBundle:Persona')->find($idPersona);
                $query = $em->createQuery(
                        'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                        JOIN ieco.insitucioneducativaCurso iec
                        JOIN ieco.asignaturaTipo at
                        JOIN ieco.maestroInscripcion mi
                        WHERE mi.persona = :idPersona
                        AND mi.gestionTipo = :gestion
                        AND mi.rolTipo = 2
                        ORDER BY mi.id')
                        ->setParameter('idPersona', $idPersona)
                        ->setParameter('gestion', $gestion);

                $areas = $query->getResult();

                
                return $this->render('SieHerramientaBundle:Notas:index.html.twig', array(
                            'areas' => $areas, 'gestion' => $gestion, 'persona'=>$persona
                ));
            }else{
                $query = $em->createQuery(
                        'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                        WHERE iec.institucioneducativa = :idInstitucion
                        AND iec.gestionTipo = :gestion
                        AND iec.turnoTipo = :turno
                        AND iec.nivelTipo = :nivel
                        AND iec.gradoTipo = :grado
                        AND iec.paraleloTipo = :paralelo')
                        ->setParameter('idInstitucion', $idInstitucion)
                        ->setParameter('gestion', $gestion)
                        ->setParameter('turno', $turno)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('paralelo', $paralelo);

                $cursos = $query->getResult();
                $curArray = array();
                foreach ($cursos as $c){
                    $query1 = $em->createQuery(
                            'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                            JOIN ieco.insitucioneducativaCurso iec
                            JOIN ieco.asignaturaTipo at
                            WHERE iec.id = :id
                            ORDER BY at.id')
                            ->setParameter('id',$c->getId());
                    $areas = $query1->getResult();
                    $areasArray = array();
                    foreach($areas as $a){
                        // Verificamos si la signatura tiene un maestro asignado
                        if($a->getMaestroInscripcion() != null or $a->getMaestroInscripcion() != ""){
                            $idPersonaMaestro = $a->getMaestroInscripcion()->getPersona()->getId();
                            $maestro = $a->getMaestroInscripcion()->getPersona()->getPaterno().' '.$a->getMaestroInscripcion()->getPersona()->getMaterno().' '.$a->getMaestroInscripcion()->getPersona()->getNombre();
                        }else{
                            $idPersonaMaestro = 0;
                            $maestro = 'No asignado';
                        }
                        $areasArray[] = array('idAsignatura'=>$a->getAsignaturaTipo()->getId(),
                                                'asignatura'=>$a->getAsignaturaTipo()->getAsignatura(),
                                                'idCurso'=>$c->getId(),
                                                'idCursoOferta'=>$a->getId(),
                                                'idPersonaMaestro'=>$idPersonaMaestro,
                                                'maestro'=>$maestro
                                            );
                    }
                    
                    $curArray[] = array('id'=>$c->getId(),
                                        'sie'=>$c->getInstitucioneducativa()->getId(),
                                        'nombre'=>$c->getInstitucioneducativa()->getInstitucioneducativa(),
                                        'turno'=>$c->getTurnoTipo()->getTurno(),
                                        'nivel'=>$c->getNivelTipo()->getNivel(),
                                        'grado'=>$c->getGradoTipo()->getGrado(),
                                        'paralelo'=>$c->getParaleloTipo()->getParalelo(),
                                        'nivelId'=>$c->getNivelTipo()->getId(),
                                        'areas'=>$areasArray);
                }
                //print_r($curArray);die;
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idInstitucion);

                return $this->render('SieHerramientaBundle:Notas:index_ue.html.twig', array(
                            'areas' => $curArray, 'gestion' => $gestion
                ));
            }
            
        } catch (Exception $ex) {
            
        }
    }
    
    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        $gestiones = array($gestion=>$gestion,$gestion-1=>$gestion-1);
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('notas'))
                ->add('tipoForm','hidden',array('data'=>'maestro'))
                ->add('carnet','text',array('label'=>'Carnet Maestro','attr'=>array('class'=>'form-control','placeholder'=>'Número de Carnet','pattern'=>'[0-9]{3,10}','autocomplete'=>'off')))
                ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
    }
    
    /*
     * Formulario de busqueda de turno nivel grado y paralelo
     */
    public function formSearchDatosUE($institucion,$gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.turnoTipo tt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id',$institucion->getId())
                ->setParameter('gestion',$gestion);
        $turnos = $query->getResult();
        $turnosArray = array();
        for($i=0;$i<count($turnos);$i++){
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }
        
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('notas'))
                ->add('tipoForm','hidden',array('data'=>'datosue'))
                ->add('idInstitucion','hidden',array('data'=>$institucion->getId()))
                ->add('idGestion','hidden',array('data'=>$gestion))
                ->add('institucion','text',array('label'=>'Institución Educativa','data'=>$institucion->getId().' - '.$institucion->getInstitucioneducativa(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nivel','choice',array('label'=>'Nivel','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('grado','choice',array('label'=>'Grado','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('paralelo','choice',array('label'=>'Paralelo','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
    }
    
    /**
     * Formulario de busqueda de unidad educativa
     */
    /*
     * Formulario de busqueda de paralelo
     */
    public function formSearchUE($gestion){        
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('notas'))
                ->add('tipoForm','hidden',array('data'=>'ue'))
                ->add('idInstitucion','text',array('label'=>'Sie','required'=>true,'attr'=>array('class'=>'form-control jnumbers','onkeyup'=>'institucionEducativa(this.value);','maxlength'=>'8','autocomplete'=>'off')))
                ->add('idGestion','hidden',array('data'=>$gestion))
                ->add('institucion','text',array('label'=>'Institución Educativa','disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('turno','choice',array('label'=>'Turno','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('nivel','choice',array('label'=>'Nivel','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('grado','choice',array('label'=>'Grado','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('paralelo','choice',array('label'=>'Paralelo','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
    }

    /*
     * Lista de estudiantes para el registro de notas
     */

    public function newAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            /*
             * Obtenemos los datos del curso y del curso oferta(asignatura)
             */
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            $institucionCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($request->get('idCursoOferta'));
            /*
             * Lista de estudiantes
             */
                $query = $em->createQuery(
                    'SELECT ea
                    FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    WHERE ei.institucioneducativaCurso = :idCurso
                    AND ea.institucioneducativaCursoOferta = :idCursoOferta
                    ORDER BY e.paterno, e.materno, e.nombre ASC')
                ->setParameter('idCurso', $request->get('idCurso'))
                ->setParameter('idCursoOferta',$request->get('idCursoOferta'));
                
            $estudiantes_inscritos = $query->getResult();
            
            $maestro = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('idPersonaMaestro'));
            
            return $this->render('SieHerramientaBundle:Notas:new.html.twig', array(
                        'curso' => $institucionCurso,
                        'cursoOferta' => $institucionCursoOferta,
                        'maestro'=>$maestro,
                        'estudiantes' => $estudiantes_inscritos,
                        'bimestre' => $request->get('bimestre')));
        } catch (Exception $ex) {
            
        }
    }

    public function createAction(Request $request) {
        /*
         * Declaramos la variable de session para obtener el id de usuario
         */
        $this->session = new Session();
        /*
         * Obtenemos los arrays y variables del formulario
         */
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        $asignatura = $request->get('asignatura');
        $nota = $request->get('nota');
        $bimestre = $request->get('bimestre');
        $nivel = $request->get('nivel');
        $em = $this->getDoctrine()->getManager();
        
        /*
         * Recorremos el array para ir haciendo los inserts
         */
        
        for ($i = 0; $i < count($idEstudianteAsignatura); $i++) {
            if ($nota[$i] != "") {
                $registro_nota = new EstudianteNota();
                $registro_nota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                $registro_nota->setFechaRegistro(new \DateTime('now'));
                if($nivel == 11){
                    // notas para inicial
                    $registro_nota->setNotaCualitativa(preg_replace("/[\n|\r|\n\r]/i"," ",$nota[$i]));
                }else{
                    //notas para primaria y secundaria
                    $registro_nota->setNotaCuantitativa($nota[$i]);
                }
                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($bimestre));
                $registro_nota->setUsuarioId($this->session->get('userId'));
                $em->persist($registro_nota);
                $em->flush();
            }
        }
        /*
         * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
         */
        $this->get('session')->getFlashBag()->add('registroOk', 'Se registraron las calificaciones correctamente.');
        return $this->redirect($this->generateUrl('notas',array('op'=>'result')));
    }
    /**
     * Esta funcion inserta y modifica las calificaciones
     * actualmenteel modulo esta funcionando con esta funcion
     */

    public function editAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            /*
             * Obtenemos los datos del curso y curso oferta(asignatura)
             */
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            $institucionCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($request->get('idCursoOferta'));
            /*
             * Listado de estudiantes inscritos por asignatura
             */
            $query = $em->createQuery(
                'SELECT ea
                FROM SieAppWebBundle:EstudianteAsignatura ea
                JOIN ea.estudianteInscripcion ei
                JOIN ei.estudiante e
                WHERE ea.institucioneducativaCursoOferta = :idCursoOferta
                ORDER BY e.paterno, e.materno, e.nombre ASC')
            ->setParameter('idCursoOferta',$request->get('idCursoOferta'));
                
            $estudiantes_inscritos = $query->getResult();
            $estudiantes = array();
            /*
             * creacion de array con los estudiantes y sus respectivas notas
             */
            foreach($estudiantes_inscritos as $ei){
                /*
                 * Verificamos si el estudiante tiene notas
                 */
                $estudiante_nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$ei->getId(),'notaTipo'=>$request->get('bimestre')));
                if($estudiante_nota){
                    $idEstudianteNota = $estudiante_nota->getId();
                    if($institucionCurso->getNivelTipo()->getId() == 11){
                        $nota = $estudiante_nota->getNotaCualitativa();
                    }else{
                        $nota = $estudiante_nota->getNotaCuantitativa();
                    }                        
                }else{
                    $idEstudianteNota = 'nuevo';
                    $nota = "";
                }
                $estudiantes[] = array(
                    'idEstudianteAsignatura'=>$ei->getId(),
                    'paterno'=>$ei->getEstudianteInscripcion()->getEstudiante()->getPaterno(),
                    'materno'=>$ei->getEstudianteInscripcion()->getEstudiante()->getMaterno(),
                    'nombre'=>$ei->getEstudianteInscripcion()->getEstudiante()->getNombre(),
                    'estadoMatricula'=>$ei->getEstudianteInscripcion()->getEstadomatriculaTipo()->getEstadomatricula(),
                    'estadoMatriculaId'=>$ei->getEstudianteInscripcion()->getEstadomatriculaTipo()->getId(),
                    'idEstudianteNota'=>$idEstudianteNota,
                    'nota'=>$nota);
            }
            if($request->get('idPersonaMaestro') != 0){
                $maestro = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('idPersonaMaestro'));
            }else{
                $maestro = array('paterno'=>'No asignado','materno'=>'','nombre'=>'');
            }
            return $this->render('SieHerramientaBundle:Notas:edit.html.twig', array(
                        'curso' => $institucionCurso,
                        'cursoOferta' => $institucionCursoOferta,
                        'maestro'=>$maestro,
                        'estudiantes' => $estudiantes,
                        'bimestre' => $request->get('bimestre')));
        }catch (Exception $ex) {
            die;
        }
    }
    
    public function updateAction(Request $request){
        $this->session = new Session();
        /*
         * Obtenemos los valores enviados desde el formulario
         */
        $em = $this->getDoctrine()->getManager();
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idEstudianteAsignatura  = $request->get('idEstudianteAsignatura');
        $nota = $request->get('nota');
        $bimestre = $request->get('bimestre');
        $nivel = $request->get('nivel');

        /*
         * Recorremos el array de estudiantes
         */        
        for($i=0;$i<count($idEstudianteNota);$i++){
            /*
             * Preguntamos si la nota es un nuevo registro
             * registro de notas
             */
            if($idEstudianteNota[$i] == 'nuevo'){
                /*
                 * Pregunatamos si la nota esta vacia, si ese es el caso no se registra
                 */
                if ($nota[$i] != ""){
                    $registro_nota = new EstudianteNota();
                    $registro_nota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                    $registro_nota->setFechaRegistro(new \DateTime('now'));
                    /*
                     * Seteamos la nota cualitativa o cuantitativa segun el nivel
                     */
                    if($nivel == 11){
                        // notas para inicial
                        $registro_nota->setNotaCualitativa(mb_strtoupper(preg_replace("/[\n|\r|\n\r]/i"," ",$nota[$i]), "utf-8"));
                        $registro_nota->setNotaCuantitativa(0);
                    }else{
                        //notas para primaria y secundaria
                        $nota_n = $nota[$i];
                        if(!is_numeric($nota[$i]) or $nota[$i]<0 or $nota[$i]>100){
                            $nota_n = 0;
                        }
                        $registro_nota->setNotaCuantitativa($nota_n);
                        $registro_nota->setNotaCualitativa('');
                    }
                    $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($bimestre));
                    $registro_nota->setUsuarioId($this->session->get('userId'));
                    $registro_nota->setRecomendacion('');
                    $registro_nota->setObs('');
                    $em->persist($registro_nota);
                    $em->flush();
                }
            }else{
                /*
                 * Modificacion de notas
                 */
                if ($nota[$i] == "") {
                    $nuevaNota = 0;
                }else{
                    $nuevaNota = $nota[$i];
                }
                $modificacion_nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                if($nivel == 11){
                    // notas para inicial
                    $modificacion_nota->setNotaCualitativa(mb_strtoupper(preg_replace("/[\n|\r|\n\r]/i"," ",$nuevaNota),"utf-8"));
                }else{
                    //notas para primaria y secundaria
                    $nota_n = $nota[$i];
                    if(!is_numeric($nota[$i]) or $nota[$i]<0 or $nota[$i]>100){
                        $nota_n = 0;
                    }
                    $modificacion_nota->setNotaCuantitativa($nota_n);
                }
                $modificacion_nota->setFechaModificacion(new \DateTime('now'));
                $modificacion_nota->setUsuarioId($this->session->get('userId'));
                $em->flush();
            }
        }
        /*
         * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
         */
        $this->get('session')->getFlashBag()->add('updateOk', 'Se registraron las calificaciones correctamente.');
        return $this->redirect($this->generateUrl('notas',array('op'=>'result')));
    }
    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion,$gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.turnoTipo tt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id',$idInstitucion)
                ->setParameter('gestion',$gestion);
        $turnos = $query->getResult();
        $turnosArray = array();
        for($i=0;$i<count($turnos);$i++){
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }
        $response = new JsonResponse();
        return $response->setData(array('turnos' => $turnosArray));
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function cargarNivelesAction($idInstitucion,$gestion,$turno){
        $em = $this->getDoctrine()->getManager();
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
        //print_r($nivelesArray);die;
        $response = new JsonResponse();
        return $response->setData(array('niveles' => $nivelesArray));
    }
    public function cargarGradosAction($idInstitucion,$gestion,$turno,$nivel){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT DISTINCT gt.id,gt.grado
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.gradoTipo gt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                AND iec.turnoTipo = :turno
                AND iec.nivelTipo = :nivel
                ORDER BY gt.id')
                ->setParameter('id',$idInstitucion)
                ->setParameter('gestion',$gestion)
                ->setParameter('turno',$turno)
                ->setParameter('nivel',$nivel);
        $grados = $query->getResult();
        $gradosArray = array();
        for($i=0;$i<count($grados);$i++){
            $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
        }
        //print_r($nivelesArray);die;
        $response = new JsonResponse();
        return $response->setData(array('grados' => $gradosArray));
    }
    public function cargarParalelosAction($idInstitucion,$gestion,$turno,$nivel,$grado){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT DISTINCT pt.id,pt.paralelo
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.paraleloTipo pt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                AND iec.turnoTipo = :turno
                AND iec.nivelTipo = :nivel
                AND iec.gradoTipo = :grado
                ORDER BY pt.id')
                ->setParameter('id',$idInstitucion)
                ->setParameter('gestion',$gestion)
                ->setParameter('turno',$turno)
                ->setParameter('nivel',$nivel)
                ->setParameter('grado',$grado);
        $paralelos = $query->getResult();
        $paralelosArray = array();
        for($i=0;$i<count($paralelos);$i++){
            $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
        }
        //print_r($nivelesArray);die;
        $response = new JsonResponse();
        return $response->setData(array('paralelos' => $paralelosArray));
    }

    /**
     * Funcion para hacer la busqueda de instituicion educativa
     */
    

}
