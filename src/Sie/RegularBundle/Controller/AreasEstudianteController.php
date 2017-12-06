<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class AreasEstudianteController extends Controller {

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
                case 'REGULAR': $this->session->set('tituloTipo', 'Áreas del Estudiante');
                                $this->session->set('layout','layoutRegular.html.twig');break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                                    $this->session->set('layout','layoutAlternativa.html.twig');break;
                default:    $this->session->set('tituloTipo', 'Adicion');
                            $this->session->set('layout','layoutRegular.html.twig');break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                    
                    /**
                     * Validamos que el modulo solo funcione con gestion 2015
                     * solo para el operativo, luego descomentar para que funcione para las demas gestiones
                     */
                    if($form['gestion'] > 2015 or 1 == 1){
                        $this->get('session')->getFlashBag()->add('noSearch', 'La gestión seleccionada no es válida.');
                        return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                    
                    /*
                     * verificamos si existe la unidad educativa
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                    if (!$institucioneducativa) {
                        $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                        return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                        return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                            return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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

            // Lista de turnos validos para la unidad educativa
            $query = $em->createQuery(
                    'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id',$institucion)
                    ->setParameter('gestion',$gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for($i=0;$i<count($turnos);$i++){
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            /**
             * Creamos el formulario de busqueda de turno nivel grado y paralelo
             */
            $form = $this->createFormBuilder()
                    ->add('idInstitucion', 'hidden', array('data'=>$institucion))
                    ->add('idGestion', 'hidden', array('data'=>$gestion))
                    ->add('turno', 'choice', array('required' => true,'empty_value'=>'Seleccionar...', 'choices' => $turnosArray,'attr'=>array('class'=>'form-control','onchange'=>'cargarNiveles()')))
                    ->add('nivel', 'choice', array('required' => true, 'empty_value'=>'Seleccionar...', 'attr'=>array('class'=>'form-control','onchange'=>'cargarGrados()')))
                    ->add('grado', 'choice', array('required' => true,'empty_value'=>'Seleccionar...', 'attr'=>array('class'=>'form-control','onchange'=>'cargarParalelos()')))
                    ->add('paralelo', 'choice', array('required' => true,'empty_value'=>'Seleccionar...', 'attr'=>array('class'=>'form-control')))
                    ->add('buscar', 'submit', array('label' => 'Buscar Curso','attr'=>array('class'=>'btn btn-info btn-block')))
                    ->getForm();
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            
            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$institucion,'gestionTipo'=>$gestion,'cargoTipo'=>0));
            
            $em->getConnection()->commit();
            


            return $this->render('SieRegularBundle:AreasEstudiante:index.html.twig', array(
                        'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion,'maestros'=>$maestros,'form'=>$form->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:AreasEstudiante:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }
    
    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('areas_estudiante'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
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
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('listagrados' => $list));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax para la seleccion de nivel grado y paralelo
     */
    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion,$gestion){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('turnos' => $turnosArray));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function cargarNivelesAction($idInstitucion,$gestion,$turno){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
            return $response->setData(array('niveles' => $nivelesArray));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    public function cargarGradosAction($idInstitucion,$gestion,$turno,$nivel){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('grados' => $gradosArray));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    public function cargarParalelosAction($idInstitucion,$gestion,$turno,$nivel,$grado){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('paralelos' => $paralelosArray));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /*
     * Lista de areas segun el nivel
     * ventana modal
     */
    
    public function lista_areas_nivelAction($idNivel,$idCurso,$idEstudianteInscripcion){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            /*
             * Si el nivel es secundaria hacemos otras consultas 13
             */
            $query = $em->createQuery(
                            'SELECT ieco
                            FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                            JOIN ieco.asignaturaTipo at
                            WHERE ieco.insitucioneducativaCurso = :idCurso
                            ORDER BY at.id ASC')
                        ->setParameter('idCurso',$idCurso);
            $areasCurso = $query->getResult();
            
            $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$idEstudianteInscripcion));
            $areasArray = array();
            for($i=0;$i<count($areasCurso);$i++){
                $check = '';
                $bloqueado = '';
                for($j=0;$j<count($areasEstudiante);$j++){
                    //if($areasCurso[$i]->getAsignaturaTipo()->getId() == $areasEstudiante[$j]->getAsignaturaTipo()->getId()){
                    if($areasCurso[$i]->getId() == $areasEstudiante[$j]->getInstitucioneducativaCursoOferta()->getId()){
                        $check = 'checked';
                        $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$areasEstudiante[$j]->getId()));
                        if($notas){
                            $bloqueado = 'disabled';
                        }
                    }
                }
                $areasArray[] = array('idCursoOferta'=>$areasCurso[$i]->getId(),'marcado'=>$check,'bloqueado'=>$bloqueado,'campo'=>$areasCurso[$i]->getAsignaturaTipo()->getAreaTipo()->getArea(),'codigo'=>$areasCurso[$i]->getAsignaturaTipo()->getId(),'asignatura'=>$areasCurso[$i]->getAsignaturaTipo()->getAsignatura());
            }
            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:AreasEstudiante:listaAreas.html.twig',array('areasNivel'=>$areasArray));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */
    public function lista_estudiantes_cursoAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $form = $request->get('form');

            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'institucioneducativa'=>$form['idInstitucion'],
                'gestionTipo'=>$form['idGestion'],
                'turnoTipo'=>$form['turno'],
                'nivelTipo'=>$form['nivel'],
                'gradoTipo'=>$form['grado'],
                'paraleloTipo'=>$form['paralelo']));
            if($curso){
                $idCurso = $curso->getId();
                $mensaje = '';
            }else{
                $mensaje = "No hay estudiantes";
                //return $mensaje;
            }

            $qb = $em->createQueryBuilder();

            // Obtenemos los estudiantes inscritos, y la cantidad de asginaturas de cada estudiante
            $estudiantesInscritos = $qb     
                    ->select('e.codigoRude, e.paterno,e.materno,e.nombre, ei.id, count(ea.institucioneducativaCursoOferta) as asignaturas')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                    ->leftJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ei.id = ea.estudianteInscripcion')
                    ->where('ei.institucioneducativaCurso = :idInstitucionCurso')
                    ->setParameter('idInstitucionCurso',$idCurso)
                    ->groupBy('e.codigoRude, e.paterno,e.materno,e.nombre, ei.id')
                    ->orderBy('e.paterno,e.materno,e.nombre')
                    ->getQuery()
                    ->getResult();

            //Obtenemos la cantidad de Areas del curso
            $query1 = $em->createQuery(
                'SELECT COUNT(ieco)
                FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                WHERE ieco.insitucioneducativaCurso = :idCurso'
                )->setParameter('idCurso',$idCurso);
            $numeroAreas = $query1->getSingleScalarResult();

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:AreasEstudiante:listaEstudiantesCurso.html.twig',array('inscritos'=>$estudiantesInscritos,'curso'=>$curso,'mensaje'=>$mensaje,'nroAreas'=>$numeroAreas));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    /**
     * Funcion para adicionar y elimiar areas
     * ///////////////////////////////////////////////////////////////////////////////////////////////
     */
    public function lista_areas_estudiante_adicionar_eliminarAction(Request $request){
        try{
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idEstudianteInscripcion = $request->get('idEstudianteInscripcion');
        $idCurso = $request->get('idInstitucionCurso');
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
        /*
         * Areas provenientes de la ventana modal
         */
        $cursosOfertas = $request->get('areas');

        /**
         * Listamos las areas que tenia anteriormente el estudiante
         * y luego eliminamos los que fueron desmarcados
         */
        $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$idEstudianteInscripcion));
        foreach ($estudianteAsignatura as $ea) {
            $existe = 'no';
            foreach ($cursosOfertas as $key => $co) {
                if($ea->getInstitucioneducativaCursoOferta()->getId() == $co){
                    $existe = 'si';
                }
            }
            // Si el curso existia pero fue desmarcado procedemos a eliminarlo
            if($existe == 'no'){
                // Verificamos si la asignatura del estuidante tiene notas
                $notasEstAsig = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$ea->getId()));
                if(!$notasEstAsig){
                    $estAsignaturaBorrar = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($ea->getId());
                    $em->remove($estAsignaturaBorrar);
                    $em->flush();
                    //echo "eliminar".$aa->getId()." -- ". $aa->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId() ."<br>";
                }
            }
        }

        /**
         * Actualizamos el id 
         */
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();

        /**
         * Agregamos las nuevas areas seleccionadas
         */
        foreach ($cursosOfertas as $key => $co) {
            $nuevo = 'si';
            foreach ($estudianteAsignatura as $ea) {
                if($co == $ea->getInstitucioneducativaCursoOferta()->getId()){
                    $nuevo = 'no';
                }
            }
            if($nuevo == 'si'){
                $this->session = new Session();
                // Registramos la nueva area para el estudiante
                $estAsignaturaNew = new EstudianteAsignatura();
                $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                $estAsignaturaNew->setFerchaLastUpdate(new \DateTime('now'));
                $estAsignaturaNew->setVersion(0);
                $estAsignaturaNew->setRevisionId(0);
                $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion));
                $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co));
                $em->persist($estAsignaturaNew);
                $em->flush();
            }
        }
        //Obtenemos la cantidad de Areas del estudiante
        $query1 = $em->createQuery(
            'SELECT COUNT(ea)
            FROM SieAppWebBundle:EstudianteAsignatura ea
            WHERE ea.estudianteInscripcion = :idInscripcion'
            )->setParameter('idInscripcion',$idEstudianteInscripcion);
        $numeroAreasEstudiante = $query1->getSingleScalarResult();

        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('mensaje'=>'ok','span'=>$idEstudianteInscripcion,'cantidadAsignaturas'=>$numeroAreasEstudiante));
    }catch(Exception $ex){
        $em->getConnection()->rollback();
    }
    }

    /**
     * Eliminar un area
     */
    public function deleteAction($idCursoOferta){
        try{
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());
        /**
         * Si existe el curso y el curso oferta entonces eliminamos el curso oferta
         */
        if($cursoOferta and $curso){
            $eliminar = 'si';
            // Verificamos si el curso oferta no tiene estudidantes asociados en la tabla estudiante asignatura
            /*$estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
            if($estudianteAsignatura){ 
                $mensaje='No se puede eliminar el área, porque tiene estudiantes asociados'; 
                $eliminar='no'; 
            }*/
            // VErificamos si tiene maestro asignado
            if($cursoOferta->getMaestroInscripcion() != null){ 
                $mensaje = 'No se puede eliminar el área si tiene un maestro asignado, primero debe quitar el maestro';
                $eliminar='no';
            }
            
            if($eliminar == 'si'){
                $mensaje = 'Se elimino el area';
                $em->remove($cursoOferta);
                $em->flush();
            }
        }
        
        $this->session = new Session();

        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$curso->getInstitucioneducativa()->getId(),'gestionTipo'=>$this->session->get('idGestion'),'cargoTipo'=>0));
        $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$curso->getId()));
        $em->getConnection()->commit();
        return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig',array('areasCurso'=>$areasCurso,'maestros'=>$maestros,'curso'=>$curso,'mensaje'=>$mensaje));
    }catch(Exception $ex){
        $em->getConnection()->rollback();
    }
    }

    /*
     * Asignar maestro al area
     */
    public function asignar_maestroAction($idCursoOferta,$idMaestro){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            //$cursoOferta = new InstitucioneducativaCursoOferta();
            if($idMaestro != 'ninguno'){
                $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestro);
                $cursoOferta->setMaestroInscripcion($maestro);
                $nombreMaestro = $maestro->getPersona()->getPaterno().' '.$maestro->getPersona()->getMaterno().' '.$maestro->getPersona()->getNombre();
            }else{
                $cursoOferta->setMaestroInscripcion(null);
                $nombreMaestro = '';
            }
            $em->flush();
            
            $curso = $cursoOferta->getAsignaturaTipo()->getAsignatura();
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('maestro'=>$nombreMaestro,'curso'=>$curso));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }
    /**
     * Funciones para el registro de notas
     */
    public function lista_areas_sin_notasAction($idEstudianteInscripcion, $idNivel){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT ea FROM SieAppWebBundle:EstudianteAsignatura ea
                JOIN ea.estudianteInscripcion ei
                WHERE ei.id = :idEstudianteInscripcion
                AND ei.estadomatriculaTipo IN (:estadoMatricula)
                ')
                ->setParameter('idEstudianteInscripcion', $idEstudianteInscripcion)
                ->setParameter('estadoMatricula', array(4,5,10));
        $asignaturas = $query->getResult();

        $cont=0;
        $notasArray = array();
        foreach ($asignaturas as $a) {
            $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>1));
            if(!$notas){
                $notasArray[$cont] = array('asignatura'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura(),'id'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId());
                if($idNivel == 11){
                    $notasArray[$cont]['notas'][] = array('notaTipo'=>1,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>'ninguno','bimestre'=>"1 Bimestre",'nota'=>'Ninguna','id'=>$cont."1");
                }else{
                    $notasArray[$cont]['notas'][] = array('notaTipo'=>1,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>'ninguno','bimestre'=>"1 Bimestre",'nota'=>0,'id'=>$cont."1");
                }
                $cont++;
            }
        }
        /*echo "<pre>";
        print_r($notasArray);
        echo "<pre>";
        */
        return $this->render('SieRegularBundle:AreasEstudiante:listaAreasCalificaciones.html.twig',array(
            'asignaturas'=>$notasArray,
            'idNivel'=> $idNivel
            ));

    }
    public function registrar_notasAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();

            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $notas = $request->get('nota');
            $nivel = $request->get('idNivel');
            for($i=0;$i<count($idEstudianteAsignatura);$i++) {
                $newNota = new EstudianteNota();
                if($nivel != 11){
                    $newNota->setNotaCuantitativa($notas[$i]);
                    $newNota->setNotaCualitativa('');
                }else{
                    $newNota->setNotaCuantitativa(0);
                    $newNota->setNotaCualitativa($notas[$i]);
                }
                $newNota->setFechaRegistro(new \DateTime('now'));
                $newNota->setFechaModificacion(new \DateTime('now'));
                $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
                $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                $newNota->setUsuarioId($this->session->get('userId'));
                $newNota->setRecomendacion('');
                $newNota->setObs('');
                $em->persist($newNota);
                $em->flush();
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('mensaje'=>'Correcto'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
}
