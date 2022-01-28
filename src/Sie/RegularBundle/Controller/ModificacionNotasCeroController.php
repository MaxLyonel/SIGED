<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class ModificacionNotasCeroController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            if(!$this->session->get('userId')){
                return $this->redirect($this->generateUrl('login'));
            }

            $tipoSistema = $request->getSession()->get('sysname');

            ////////////////////////////////////////////////////
            $rolUsuario = $this->session->get('roluser');

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
                if(!$estudiante){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante con el código ingresado no existe!');
                    return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                // Verificamos si el estudiante tiene inscripcion en la unidad educativa y gestion seleccionada
                $inscripcion = $em->createQuery(
                    'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                    LEFT JOIN ei.estudiante e
                    LEFT JOIN ei.institucioneducativaCurso iec
                    LEFT JOIN iec.gestionTipo gt
                    LEFT JOIN ei.estadomatriculaTipo emt
                    WHERE e.codigoRude = :rude
                    AND iec.institucioneducativa = :idInstitucion
                    AND gt.id = :idGestion
                    AND emt.id IN (:idMatricula)')
                    ->setParameter('rude',$form['codigoRude'])
                    ->setParameter('idInstitucion',$form['idInstitucion'])
                    ->setParameter('idGestion',$form['gestion'])
                    ->setParameter('idMatricula',array(4))
                    ->getResult();
                if(!$inscripcion){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con inscripcion en la unidad educativa y la gestion seleccionada');
                    return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                $this->session->set('idEstudiante', $estudiante->getId());
                $this->session->set('idGestion',$form['gestion']);
                $this->session->set('idInstitucion',$form['idInstitucion']);
                $idEstudiante = $estudiante->getId();
                $gestion = $form['gestion'];
                $idInstitucion = $form['idInstitucion'];

                /*
                 * verificamos si tiene tuicion
                 */
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['idInstitucion']);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $idInstitucion = $form['idInstitucion'];
                    $gestion = $form['gestion'];
                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

            } else {
                /*
                 * Verificamos si se tiene que mostrar el formulario de busqueda
                 */
                if($op == 'search'){
                    return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * Verificar si existe la session de la persona y gestion
                 */
                if($this->session->get('idEstudiante')){
                    $idEstudiante = $this->session->get('idEstudiante');
                    $gestion = $this->session->get('idGestion');
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
                    $idInstitucion = $this->session->get('idInstitucion');
                }else{
                    return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }
           
            // Obtenemos el curso en el que esta inscrito el estudiante
            // Obtenemos la cantidad de notas y el tipo de nota (Bimestre, Trimestre, etc)
            // 
            $curso = $em->createQueryBuilder()     
                    ->select('iec.id,gt.id as gradoId,npt.notaPeriodoTipo, npt.periodomes')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                    ->leftJoin('SieAppWebBundle:NotaPeriodoTipo','npt','WITH','iec.notaPeriodoTipo = npt.id')
                    ->leftJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                    ->where('e.id = :idEstudiante')
                    ->andWhere('iec.institucioneducativa = :idInstitucion')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->setParameter('idEstudiante',$idEstudiante)
                    ->setParameter('idInstitucion',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->orderBy('e.paterno,e.materno,e.nombre')
                    ->getQuery()
                    ->getResult();   
            /**
             * Obtenemos la cantidad de notas
             */
            switch($gestion){
                case 2012:  $tipo_nota = 'Trimestre';
                            $cantidad_notas = 3;
                            $valor_inicial = 6;
                            $valor_final = 8;
                            break;
                case 2013:  switch($curso[0]['gradoId']){
                                case 1: $tipo_nota = 'Bimestre';
                                        $cantidad_notas = 4;
                                        $valor_inicial = 1;
                                        $valor_final = 4;
                                        break;
                                default:
                                        $tipo_nota = 'Trimestre';
                                        $cantidad_notas = 3;
                                        $valor_inicial = 6;
                                        $valor_final = 8;
                                        break;
                                        
                            }
                            break;
                case 2014:
                case 2015:
                case 2016:  $tipo_nota = 'Bimestre';
                            $cantidad_notas = 4;
                            $valor_inicial = 1;
                            $valor_final = 4;
                            break;

            }

            /*
             * Listamos las asignaturas del estudiante
             */
            $query = $em->createQuery(
                    'SELECT DISTINCT at.id, at.asignatura, ei.id as idInscripcion
                    FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    JOIN ea.institucioneducativaCursoOferta ieco
                    JOIN ieco.asignaturaTipo at
                    WHERE e.id = :idEstudiante
                    AND iec.gestionTipo = :gestion
                    AND iec.institucioneducativa = :idInstitucion
                    AND ei.estadomatriculaTipo IN (:estadoMatricula)
                    ORDER BY at.id')
                    ->setParameter('idEstudiante', $idEstudiante)
                    ->setParameter('estadoMatricula', array(4))
                    ->setParameter('gestion', $gestion)
                    ->setParameter('idInstitucion',$idInstitucion);

            $asignaturas = $query->getResult();
            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $em->createQuery(
                                        'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, nt.notaTipo as notaTipo
                                        FROM SieAppWebBundle:EstudianteNota en
                                        JOIN en.notaTipo nt
                                        JOIN en.estudianteAsignatura ea
                                        JOIN ea.estudianteInscripcion ei
                                        JOIN ea.institucioneducativaCursoOferta ieco
                                        JOIN ieco.asignaturaTipo at
                                        WHERE ei.id = :idInscripcion
                                        AND at.id = :idAsignatura
                                        AND nt.id NOT IN (:promedios)
                                        ORDER BY at.id, nt.id')
                                        ->setParameter('idInscripcion',$a['idInscripcion'])
                                        ->setParameter('idAsignatura',$a['id'])
                                        ->setParameter('promedios',array(5,9,11))
                                        ->getResult();
                if($asignaturasNotas){

                    $cont2 = 0;
                    foreach ($asignaturasNotas as $an) {
                        $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa']
                                                        );
                        $cont2++;
                    }
                }else{
                    $cont2 = 0 ;
                    for($i=$valor_inicial;$i<=$valor_final;$i++){
                        $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>($cont2+1)." ".$tipo_nota,
                                                            'nota'=>''
                                                        );
                        $cont2++;
                    }
                    if($gestion < 2013 or ($gestion == 2013 and $curso[0]['gradoId'] == 1) ){
                        // completar
                    }
                }
                $cont++;
            }
            
            if(!$notasArray){
                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, debe realizar la adicion de areas y calificaciones.');
                return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            
            //VAlidamos que la gestion sea del 2014 para arriba
            if($gestion < 2013){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden modificar notas de la gestión 2015!');
                return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            
            foreach ($asignaturas as $a){
                $idInscripcionEstudiante = $a['idInscripcion'];
            }

            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y obtenemos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcionEstudiante);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();

            $this->session->set('idInscripcion',$idInscripcionEstudiante);
            
            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            $titulos = $notasArray[0]['notas'];

            for($i=0;$i<count($titulos);$i++){
                $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
            }

            $this->session->set('idNivel',$idNivel);
            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:ModificacionNotasCero:index.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso,
                        'idInscripcion'=>$idInscripcionEstudiante
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
            echo 'Error';
        }
    }

        /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        $gestiones = array();
        for ($i=$gestion; $i >= ($gestion-1) ; $i--) { 
            $gestiones[$i] = $i;
        }
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('modificacionNotasCero'))
                ->add('codigoRude','text',array('label'=>'Código Rude','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                ->add('idInstitucion','text',array('label'=>'Sie','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
    }


    /**
     * Verificacion de notas
     */
    public function verificarAction($idInscripcion){
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($idInscripcion);
        //dump($arrayNotas);die;
        return $this->render('SieRegularBundle:ModificacionNotasCero:notas.html.twig', array(
         'asignaturas'=>$arrayNotas['notas'],'cualitativas'=>$arrayNotas['cualitativas'],'operativo'=>$arrayNotas['operativo'],
         'nivel'=>$inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId(),'idInscripcion'=>$inscripcion->getId()
        ));
    }

    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            if(!$this->session->get('userId')){
                return $this->redirect($this->generateUrl('login'));
            }
            $idEstudiante = $this->session->get('idEstudiante');
            $gestion = $this->session->get('idGestion');
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
            $idInstitucion = $this->session->get('idInstitucion');

            // Obtenemos el curso en el que esta inscrito el estudiante
            // Obtenemos la cantidad de notas y el tipo de nota (Bimestre, Trimestre, etc)
            // 
            $curso = $em->createQueryBuilder()     
                    ->select('iec.id,gt.id as gradoId,npt.notaPeriodoTipo, npt.periodomes')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                    ->leftJoin('SieAppWebBundle:NotaPeriodoTipo','npt','WITH','iec.notaPeriodoTipo = npt.id')
                    ->leftJoin('SieAppWebBundle:GradoTipo','gt','WITH','iec.gradoTipo = gt.id')
                    ->where('e.id = :idEstudiante')
                    ->andWhere('iec.institucioneducativa = :idInstitucion')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->setParameter('idEstudiante',$idEstudiante)
                    ->setParameter('idInstitucion',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->orderBy('e.paterno,e.materno,e.nombre')
                    ->getQuery()
                    ->getResult();   
            /**
             * Obtenemos la cantidad de notas
             */
            switch($gestion){
                case 2012:  $tipo_nota = 'Trimestre';
                            $cantidad_notas = 3;
                            $valor_inicial = 6;
                            $valor_final = 8;
                            break;
                case 2013:  switch($curso[0]['gradoId']){
                                case 1: $tipo_nota = 'Bimestre';
                                        $cantidad_notas = 4;
                                        $valor_inicial = 1;
                                        $valor_final = 4;
                                        break;
                                default:
                                        $tipo_nota = 'Trimestre';
                                        $cantidad_notas = 3;
                                        $valor_inicial = 6;
                                        $valor_final = 8;
                                        break;
                                        
                            }
                            break;
                case 2014:
                case 2015:
                case 2016:  $tipo_nota = 'Bimestre';
                            $cantidad_notas = 4;
                            $valor_inicial = 1;
                            $valor_final = 4;
                            break;

            }

            /*
             * Listamos las asignaturas del estudiante
             */
            $query = $em->createQuery(
                    'SELECT DISTINCT at.id, at.asignatura, ei.id as idInscripcion
                    FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    JOIN ea.institucioneducativaCursoOferta ieco
                    JOIN ieco.asignaturaTipo at
                    WHERE e.id = :idEstudiante
                    AND iec.gestionTipo = :gestion
                    AND iec.institucioneducativa = :idInstitucion
                    AND ei.estadomatriculaTipo IN (:estadoMatricula)
                    ORDER BY at.id')
                    ->setParameter('idEstudiante', $idEstudiante)
                    ->setParameter('estadoMatricula', array(4,5,10,11,55,9))
                    ->setParameter('gestion', $gestion)
                    ->setParameter('idInstitucion',$idInstitucion);

            $asignaturas = $query->getResult();
            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $em->createQuery(
                                        'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, nt.notaTipo as notaTipo
                                        FROM SieAppWebBundle:EstudianteNota en
                                        JOIN en.notaTipo nt
                                        JOIN en.estudianteAsignatura ea
                                        JOIN ea.estudianteInscripcion ei
                                        JOIN ea.institucioneducativaCursoOferta ieco
                                        JOIN ieco.asignaturaTipo at
                                        WHERE ei.id = :idInscripcion
                                        AND at.id = :idAsignatura
                                        AND nt.id NOT IN (:promedios)
                                        ORDER BY at.id, nt.id')
                                        ->setParameter('idInscripcion',$a['idInscripcion'])
                                        ->setParameter('idAsignatura',$a['id'])
                                        ->setParameter('promedios',array(5,9,11))
                                        ->getResult();
                if($asignaturasNotas){
                    
                    $cont2 = 0;
                    foreach ($asignaturasNotas as $an) {
                        $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$cont2,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'nota'=>$an['notaCuantitativa']
                                                        );
                        $cont2++;
                    }
                }else{
                    $cont2 = 0 ;
                    for($i=$valor_inicial;$i<=$valor_final;$i++){
                        $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'ninguno',
                                                            'bimestre'=>($cont2+1)." ".$tipo_nota,
                                                            'nota'=>''
                                                        );
                        $cont2++;
                    }
                    if($gestion < 2013 or ($gestion == 2013 and $curso[0]['gradoId'] == 1) ){
                        // completar
                    }
                }
                $cont++;
            }
            
            if(!$notasArray){
                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, debe realizar la adicion de areas y calificaciones.');
                return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            
            //VAlidamos que la gestion sea del 2014 para arriba
            if($gestion < 2013){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden modificar notas de la gestión 2015!');
                return $this->render('SieRegularBundle:ModificacionNotasCero:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            
            foreach ($asignaturas as $a){
                $idInscripcionEstudiante = $a['idInscripcion'];
            }

            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y obtenemos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcionEstudiante);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();
            
            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            $titulos = $notasArray[0]['notas'];

            for($i=0;$i<count($titulos);$i++){
                $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
            }

            $this->session->set('idNivel',$idNivel);
            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:ModificacionNotasCero:edit.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso                    
            ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
        }
    }


    public function updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            if(!$this->session->get('userId')){
                return $this->redirect($this->generateUrl('login'));
            }
            /*
            * Obtenemos los valores enviados desde el formulario
            */
            $idEstudianteNota = $request->get('idEstudianteNota');
            $nota = $request->get('nota');
            $nivel = $this->session->get('idNivel');
            //dump($idEstudianteNota);
            //dump($nota);die;
            /*
             * Recorremos el array de notas para verificar si los datos son numeros
             * y si comprenden entre los 1 a 100 SI EL NIVEL ES PRIMARIA O SECUNDARIA
             */
            if($nivel != 11){
                for($i=0;$i<count($nota);$i++){
                    if(!is_numeric($nota[$i]) or $nota[$i]<0 or $nota[$i]>100){
                      $this->get('session')->getFlashBag()->add('updateError', 'Las calificaciones ingresadas no son válidas, intentelo nuevamente.');
                      return $this->redirect($this->generateUrl('modificacionNotasCero',array('op'=>'result')));
                    }
                }
            }
            /**
             * Validacion de notas, promedios
             */
            /*
            for($i=0;$i<count($idEstudianteNota);$i++){
                if($idEstudianteNota[$i] == 'ninguno'){
                    $this->get('session')->getFlashBag()->add('updateError', 'No se pudo realizar la modificacion de calificaciones, los promedios no estan registrados.');
                    return $this->redirect($this->generateUrl('modificacionNotasCero',array('op'=>'result')));
                }
            }
            */
           
            for($i=0;$i<count($idEstudianteNota);$i++){
                if($idEstudianteNota[$i] != 'ninguno'){
                    if ($nota[$i] == "") {
                        $nuevaNota = 0;
                    }else{
                        $nuevaNota = $nota[$i];
                    }
                    $modificacion_nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                    if($nivel == 11){
                        // notas para inicial
                        $modificacion_nota->setNotaCualitativa(preg_replace("/[\n|\r|\n\r]/i"," ",$nuevaNota));
                    }else{
                        //notas para primaria y secundaria
                        $modificacion_nota->setNotaCuantitativa($nuevaNota);
                    }
                    $modificacion_nota->setFechaModificacion(new \DateTime('now'));
                    $modificacion_nota->setUsuarioId($this->session->get('userId'));
                    $em->flush();
                }                
            }

            // Actualizamos el promedio si corresponde
            $idInscripcion = $this->session->get('idInscripcion');
            /*
             * Listamos las asignaturas del estudiante
             */
            $query = $em->createQuery(
                    'SELECT DISTINCT at.id, at.asignatura, ei.id as idInscripcion
                    FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    JOIN ea.institucioneducativaCursoOferta ieco
                    JOIN ieco.asignaturaTipo at
                    WHERE ei.id = :idInscripcion
                    ORDER BY at.id')
                    ->setParameter('idInscripcion',$idInscripcion);

            $asignaturas = $query->getResult();
            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;

            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('idEstudianteAsignatura'=>$a['id'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $em->createQuery(
                                        'SELECT at.id, at.asignatura, en.id as idNota, en.notaCuantitativa, nt.id as notaTipo, ea.id as idEstudianteAsignatura
                                        FROM SieAppWebBundle:EstudianteNota en
                                        JOIN en.notaTipo nt
                                        JOIN en.estudianteAsignatura ea
                                        JOIN ea.estudianteInscripcion ei
                                        JOIN ea.institucioneducativaCursoOferta ieco
                                        JOIN ieco.asignaturaTipo at
                                        WHERE ei.id = :idInscripcion
                                        AND at.id = :idAsignatura
                                        AND nt.id IN (:promedios)
                                        ORDER BY at.id, nt.id')
                                        ->setParameter('idInscripcion',$a['idInscripcion'])
                                        ->setParameter('idAsignatura',$a['id'])
                                        ->setParameter('promedios',array(1,2,3,4,5))
                                        ->getResult();
                
                $cont = 0;
                if($asignaturasNotas){
                    $sumaNotas = 0;
                    $existePromedio = 'no';
                    foreach ($asignaturasNotas as $an) {
                        if($an['notaTipo'] == 5){
                            $existePromedio = 'si';
                            $promedio = round($sumaNotas/4);
                            $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($an['idNota']);
                            $notaPromedio->setNotaCuantitativa($promedio);
                            $notaPromedio->setFechaModificacion(new \DateTime('now'));
                            $em->flush();
                        }else{
                            $sumaNotas = $sumaNotas + $an['notaCuantitativa'];
                            $cont++;
                            $idEstudianteAsignatura = $an['idEstudianteAsignatura'];
                        }
                    }
                }
                // Si no existe el promedio registramos el promedio
                if($existePromedio == 'no' and $cont == 4){
                    $promedio = round($sumaNotas/4);
                    $nuevoPromedio = new EstudianteNota();
                    $nuevoPromedio->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                    $nuevoPromedio->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura));
                    $nuevoPromedio->setNotaCuantitativa($promedio);
                    $nuevoPromedio->setNotaCualitativa('');
                    $nuevoPromedio->setRecomendacion('');
                    $nuevoPromedio->setUsuarioId($this->session->get('userId'));
                    $nuevoPromedio->setFechaRegistro(new \DateTime('now'));
                    $nuevoPromedio->setFechaModificacion(new \DateTime('now'));
                    $nuevoPromedio->setObs('');
                    $em->persist($nuevoPromedio);
                    $em->flush();
                }
                $cont++;
            }

            /**
             * Ejecutamos la funcion de base de datos 
             */
            $query = $em->getConnection()->prepare("select * from sp_calcula_estadomatricula_inscripcion(".$idInscripcion.")");
            $query->execute();

            // Cerramos la conexion
            $em->getConnection()->commit();
            /*
             * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
             */
            $this->get('session')->getFlashBag()->add('updateOk', 'Las calificaciones se modificaron correctamente.');
            return $this->redirect($this->generateUrl('modificacionNotasCero',array('op'=>'result')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'No se pudo realizar la modificacion de calificaciones.');
            return $this->redirect($this->generateUrl('modificacionNotasCero',array('op'=>'result')));
        }
        
    }
}
