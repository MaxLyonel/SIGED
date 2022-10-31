<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * EstudianteInscripcion controller.
 *
 */
class NotasCualitativasController extends Controller {

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
                case 'REGULAR': $this->session->set('tituloTipo', 'Apreciaciones Cualitativas');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Apreciaciones Cualitativas');
                    break;
                default: $this->session->set('tituloTipo', '');
                    break;
            }
            /**
             * Identificamos el rol del usuario
             * 2- maestro, 9 - administrativo
             * 10 - Distrito, 7 - Departamento, 8-Nacional
             */
            $rolUsuario = $this->session->get('roluser');
            
            
            ////////////////////////////////////////////////////

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /*
                 * Preguntamos si los datos provienen del formulario de busqueda de docentes
                 * o de unidad educativa por nivel grado y paralelo
                 */
                switch ($form['tipoForm']) {
                    case 'maestro':
                        # code...
                        break;
                    default:
                        /**
                         * Verificamos la tuicion sobre la unidad educativa
                         * sie el usuario es distinto a administrativo unidad educativa (9)
                         */
                        if($this->session->get('roluser') != 9){
                            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                            $query->bindValue(':user_id', $this->session->get('userId'));
                            $query->bindValue(':sie', $form['idInstitucion']);
                            $query->bindValue(':rolId', $this->session->get('roluser'));
                            $query->execute();
                            $aTuicion = $query->fetchAll();
                            if ($aTuicion[0]['get_ue_tuicion'] == false){
                                $this->get('session')->getFlashBag()->add('noSearch', 'No tiene tuicion sobre la Unidad Educativa');
                                return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchUE($request->getSession()->get('currentyear'))->createView()));
                            }
                        }
                        /**
                         * Si los datos provienen del formulario de vusqueda de unidad educativa
                         */
                        $gestion = $this->session->get('currentyear');
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
                                return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchDatosUE($form['idInstitucion'],$request->getSession()->get('currentyear'))->createView()));
                            }else{
                                $this->get('session')->getFlashBag()->add('noSearch', 'Los datos introducidos no son correctos, intentelo nuevamente.');
                                return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchUE($request->getSession()->get('currentyear'))->createView()));
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
                        break;
                }
            }else{
                /**
                 * Trabajamos con la gestion actual
                 */
                $gestion = $request->getSession()->get('currentyear');
                switch ($rolUsuario) {
                    case 2: // Maestro
                        $idPersona = $this->session->get('personaId');
                        $tipoUsuario = 'maestro';
                        break;

                    case 9: // Administrativo
                        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find(80730356);
                        if($op == 'search' or $op != 'result'){
                            return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchDatosUE($institucion,$gestion)->createView(),'DatosUE'=>'DatosUE'));
                        }
                        if($this->session->get('notasInstitucion')){
                            $idInstitucion = $this->session->get('notasInstitucion');
                            $gestion = $this->session->get('notasGestion');
                            $turno = $this->session->get('notasTurno');
                            $nivel = $this->session->get('notasNivel');
                            $grado = $this->session->get('notasGrado');
                            $paralelo = $this->session->get('notasParalelo');

                        }else{
                            return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchDatosUE($institucion,$gestion)->createView(),'UE'=>'UE'));
                        }
                        $tipoUsuario = 'UnidadEducativa';
                        break;

                    case 7: // Departamento
                    case 8: // Nacional
                    case 10: // Distrito
                        if($op == 'search' or $op != 'result'){
                            return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchUE($gestion)->createView(),'UE'=>'UE'));
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
                            return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearchUE($gestion)->createView(),'UE'=>'UE'));
                        }
                        break;
                    default:
                        die();
                        break;
                }
            }
            /*
             * Listamos las asignaturas que da la persona(docente) si el usuario tiene rol de maestro (2)
             */
            if($tipoUsuario == 'maestro'){
                $persona = $em->getRepository('SieAppWebBundle:Persona')->find($idPersona);
                $query = $em->createQuery(
                        'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                        JOIN iec.maestroInscripcionAsesor mi
                        WHERE mi.persona = :idPersona
                        AND iec.gestionTipo = :gestion
                        ORDER BY iec.id')
                        ->setParameter('idPersona', $idPersona)
                        ->setParameter('gestion', $gestion);

                $areas = $query->getResult();

                return $this->render('SieHerramientaBundle:NotasCualitativas:index.html.twig', array(
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
                    if($c->getMaestroInscripcionAsesor() == null){
                        $idPersonaAsesor = 0;
                        $asesor = "No asignado";
                    }else{
                        $asesor = $c->getMaestroInscripcionAsesor()->getPersona()->getPaterno();
                        $idPersonaAsesor = $c->getMaestroInscripcionAsesor()->getPersona()->getId();
                    }
                    $curArray[] = array('id'=>$c->getId(),
                                        'sie'=>$c->getInstitucioneducativa()->getId(),
                                        'nombre'=>$c->getInstitucioneducativa()->getInstitucioneducativa(),
                                        'turno'=>$c->getTurnoTipo()->getTurno(),
                                        'nivel'=>$c->getNivelTipo()->getNivel(),
                                        'grado'=>$c->getGradoTipo()->getGrado(),
                                        'paralelo'=>$c->getParaleloTipo()->getParalelo(),
                                        'nivelId'=>$c->getNivelTipo()->getId(),
                                        'asesor'=>$asesor,
                                        'idPersonaAsesor'=>$idPersonaAsesor
                                        );
                }
                //print_r($curArray);die;
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idInstitucion);

                return $this->render('SieHerramientaBundle:NotasCualitativas:index_ue.html.twig', array(
                            'cursos' => $curArray, 'gestion' => $gestion, 'institucion'=>$institucion
                ));
            }
            
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieHerramientaBundle:NotasCualitativas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
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
                ->setAction($this->generateUrl('notascualitativas'))
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
    /*
     * Formulario de busqueda para unidad educativa
     */
    public function formSearchUE($gestion){        
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('notascualitativas'))
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
     * Listado de estudiantes inscritos 
     */
    public function newAction(Request $request) {
        $this->session = new Session();
        try {
            $em = $this->getDoctrine()->getManager();
            /**
             * Obtenemos el rol del usuario
             */
            $rol = $this->session->get('roluser');
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
            /**
             * Validacion de cursos
             */
            switch ($rol) {
                case 2:
                    /*
                     * Si no hay un maestro asignado para el curso redireccionamos al index
                     * por que el curso no corresponde al maestro logueado
                     */
                    if($institucionCurso->getMaestroInscripcionAsesor() == null){
                        $this->get('session')->getFlashBag()->add('noValido', 'El curso seleccionado no corresponde al maestro');
                        return $this->redirect($this->generateUrl('notascualitativas',array('op'=>'result')));
                    }
                    /**
                     * Verificacmos si el curso seleccionado tiene como asesosr el maestro logueado
                     * si no es asi lo redireccionamos al index
                     */
                    $id_persona = $institucionCurso->getMaestroInscripcionAsesor()->getPersona()->getId();
                    if($id_persona != $this->session->get('personaId')){
                        $this->get('session')->getFlashBag()->add('noValido', 'El curso seleccionado no corresponde al maestro');
                        return $this->redirect($this->generateUrl('notascualitativas',array('op'=>'result')));
                    }
                    break;
                case 9:
                    /**
                     * Verificacmos si el curso corresponde a la unidad educativa
                     */
                    if($institucionCurso != $this->session->get('notasInstitucion')){
                        $this->get('session')->getFlashBag()->add('noValido', 'El curso seleccionado no corresponde a la institucion educativa');
                        return $this->redirect($this->generateUrl('notascualitativas',array('op'=>'result')));
                    }
                    break;
                default:
                    # code...
                    break;
            }

            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));

            $query = $em->createQuery(
                'SELECT ei
                FROM SieAppWebBundle:EstudianteInscripcion ei
                JOIN ei.estudiante e
                JOIN ei.institucioneducativaCurso iec
                WHERE ei.institucioneducativaCurso = :idCurso
                ORDER BY e.paterno, e.materno, e.nombre ASC')
            ->setParameter('idCurso', $request->get('idCurso'));
                
            $estudiantes_inscritos = $query->getResult();
            $estudiantes = array();
            foreach($estudiantes_inscritos as $ei){
                $estudiante_nota = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$ei->getId(),'notaTipo'=>$request->get('bimestre')));
                if($estudiante_nota){
                    $idEstudianteNota = $estudiante_nota->getId();
                    $nota = $estudiante_nota->getNotaCualitativa();                  
                }else{
                    $idEstudianteNota = 'nuevo';
                    $nota = "";
                }
                $estudiantes[] = array(
                    'idEstudianteInscripcion'=>$ei->getId(),
                    'paterno'=>$ei->getEstudiante()->getPaterno(),
                    'materno'=>$ei->getEstudiante()->getMaterno(),
                    'nombre'=>$ei->getEstudiante()->getNombre(),
                    'estadoMatricula'=>$ei->getEstadomatriculaTipo()->getEstadomatricula(),
                    'estadoMatriculaId'=>$ei->getEstadomatriculaTipo()->getId(),
                    'idEstudianteNota'=>$idEstudianteNota,
                    'nota'=>$nota);
            }
            if($request->get('idPersonaMaestro') != 0){
                $maestro = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('idPersonaMaestro'));
            }else{
                $maestro = array('paterno'=>'No asignado','materno'=>'','nombre'=>'');
            }
            
            return $this->render('SieHerramientaBundle:NotasCualitativas:new.html.twig', array(
                        'curso' => $institucionCurso,
                        'maestro'=>$maestro,
                        'estudiantes' => $estudiantes,
                        'bimestre' => $request->get('bimestre')));
        } catch (Exception $ex) {
            
        }
    }

    public function createAction(Request $request) {
        $this->session = new Session();
        /*
         * Obtenemos los valores enviados desde el formulario
         */
        $em = $this->getDoctrine()->getManager();
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idEstudianteInscripcion  = $request->get('idEstudianteInscripcion');
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
                if ($nota[$i] != "") {
                    $registro_nota = new EstudianteNotaCualitativa();
                    $registro_nota->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion[$i]));
                    $registro_nota->setFechaRegistro(new \DateTime('now'));
                    $registro_nota->setNotaCualitativa(mb_strtoupper(preg_replace("/[\n|\r|\n\r]/i"," ",$nota[$i]),'utf-8'));
                    $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($bimestre));
                    $registro_nota->setUsuarioId($this->session->get('userId'));
                    $em->persist($registro_nota);
                    $em->flush();
                }
            }else{
                /*
                 * Modificacion de notas
                 */
                $modificacion_nota = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNota[$i]);
                $modificacion_nota->setNotaCualitativa(mb_strtoupper(preg_replace("/[\n|\r|\n\r]/i"," ",$nota[$i]),'utf-8'));
                $modificacion_nota->setFechaModificacion(new \DateTime('now'));
                $modificacion_nota->setUsuarioId($this->session->get('userId'));
                $em->flush();
            }
        }
        /*
         * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
         */
        $this->get('session')->getFlashBag()->add('updateOk', 'Se registraron las calificaciones correctamente.');
        return $this->redirect($this->generateUrl('notascualitativas',array('op'=>'result')));
    }
}
