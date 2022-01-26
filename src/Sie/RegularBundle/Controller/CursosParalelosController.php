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
class CursosParalelosController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Paralelos');
                                $this->session->set('layout','layoutRegular.html.twig');break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                                    $this->session->set('layout','layoutAlternativa.html.twig');break;
                default:    $this->session->set('tituloTipo', 'Paralelos');
                            $this->session->set('layout','layoutRegular.html.twig');break;
            }

            ////////////////////////////////////////////////////
            $em = $this->getDoctrine()->getManager();
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                
                    /*
                     * verificamos si existe la unidad educativa
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                    if (!$institucioneducativa) {
                        $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                        return $this->render('SieRegularBundle:CursosParalelos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                        return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                            return $this->render('SieRegularBundle:CursosParalelos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieRegularBundle:CursosParalelos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
             * Guardamos la consulta en un array ordenado
             */
            $cursosArray = array();
            foreach ($cursos as $cur) {
                $cursosArray[] = array(
                    'turno' => $cur->getTurnoTipo()->getTurno(),
                    'nivel' => $cur->getNivelTipo()->getNivel(),
                    'grado' => $cur->getGradoTipo()->getGrado(),
                    'paralelo' => $cur->getParaleloTipo()->getParalelo(),
                    'idInstitucion'=>$cur->getInstitucioneducativa()->getId(),
                    'idGestion'=>$cur->getGestionTipo()->getId(),
                    'idInstitucionCurso'=>$cur->getId(),
                    'idNivel' => $cur->getNivelTipo()->getId(),
                    'idTurno' => $cur->getTurnoTipo()->getId(),
                    'idAsesor' => ($cur->getMaestroInscripcionAsesor() == null)?0:$cur->getMaestroInscripcionAsesor()->getId()
                );
            }
            
            /*
             * Anadimos las areas del curso al array
             */

            for ($i = 0; $i < count($cursosArray); $i++) {
                $areas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$cursosArray[$i]['idInstitucionCurso']));
                if(count($areas)>0){
                    for($j=0;$j<count($areas);$j++){                        
                        $cursosArray[$i]['area'][] = array('campo'=>$areas[$j]->getAsignaturaTipo()->getAreaTipo()->getArea(),'codigo'=>$areas[$j]->getAsignaturaTipo()->getId(),'asignatura' => $areas[$j]->getAsignaturaTipo()->getAsignatura(), 'maestro' => ($areas[$j]->getMaestroInscripcion() != '')?$areas[$j]->getMaestroInscripcion()->getPersona():'','idInstitucionCursoOferta'=>$areas[$j]->getId(),'idMaestroInscripcion'=>($areas[$j]->getMaestroInscripcion() != '')?$areas[$j]->getMaestroInscripcion()->getId():'');
                    }
                }else{
                    $cursosArray[$i]['area'] = null;//array('campo'=>'','codigo'=>'','asignatura'=>'adfdasf','maestro'=>'dafdsaf');
                }
            }
            
            $est = array();
            $est = $cursosArray;
            $niveles = array();
            if(count($est)>0){ 
            $n = $est[0]['nivel'];
            
            $c = 0;
            $niveles[0] = array($n,array('0'=>$est[0]));
            
            for ($j = 1; $j < count($est); $j++) {
                if ($est[$j]['nivel'] == $n) {
                    $niveles[$c][1][] = $est[$j];
                }else{
                    $c++;
                    $n = $est[$j]['nivel'];
                    $niveles[$c] = array($n,array('0'=>$est[$j]));
                }
            }
            }
            
            /*
             * obtenemos los datos de la unidad educativa
             */
            $est = $niveles;
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            
            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$institucion,'gestionTipo'=>$gestion,'cargoTipo'=>0));
            
            
            return $this->render('SieRegularBundle:CursosParalelos:index.html.twig', array(
                        'inscritos' => $est, 'institucion' => $institucion, 'gestion' => $gestion,'maestros'=>$maestros
            ));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:CursosParalelos:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }
    
    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('cursosparalelos'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }
    
    /*
     * Lista de areas segun el nivel
     * ventana modal
     */
    
    public function lista_areas_nivelAction($idNivel,$idCurso){
        $em = $this->getDoctrine()->getManager();
        /*
         * Si el nivel es secundaria hacemos otras consultas 13
         */
        if($idNivel == 13){
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $grado = $institucionCurso->getGradoTipo()->getId();
            switch($grado){
                case 1: 
                case 2: $consulta = 'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id != 1045
                                    AND at.id != 1039
                                    ORDER BY at.id ASC';break;
                case 3: 
                case 4: $consulta = 'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id != 1039
                                    ORDER BY at.id ASC';break;
                case 5: 
                case 6: $consulta = 'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id != 1038
                                    ORDER BY at.id ASC';break;
            }
            $query = $em->createQuery($consulta)->setParameter('idNivel',$idNivel);
            $areasNivel = $query->getResult();
        }else{
            $query = $em->createQuery(
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.asignaturaNivel = :idNivel
                            ORDER BY at.id ASC')
                        ->setParameter('idNivel',$idNivel);
            $areasNivel = $query->getResult();
        }
        $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$idCurso));
        $areasArray = array();
        for($i=0;$i<count($areasNivel);$i++){
            $check = '';
            for($j=0;$j<count($areasCurso);$j++){
                if($areasNivel[$i]->getId() == $areasCurso[$j]->getAsignaturaTipo()->getId()){
                    $check = 'checked';
                }
            }
            $areasArray[] = array('marcado'=>$check,'campo'=>$areasNivel[$i]->getAreaTipo()->getArea(),'codigo'=>$areasNivel[$i]->getId(),'asignatura'=>$areasNivel[$i]->getAsignatura());
        }
        
        return $this->render('SieRegularBundle:CursosParalelos:listaAreas.html.twig',array('areasNivel'=>$areasArray));
    }
    
    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */
    public function lista_areas_cursoAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        //echo $request->get('divResultado')."<br>";
        //echo $request->get('idInstitucionCurso');
        $idCurso = $request->get('idInstitucionCurso');
        //die;
        /*
         * Areas a registrar nuevos
         */
        $areas = $request->get('areas');
        /*
         * Areas registradas anteriormente
         */

        $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$idCurso));
        
        for($i=0;$i<count($areas);$i++){
            $existe = 'no';
            for($j=0;$j<count($areasCurso);$j++){                
                if($areas[$i] == $areasCurso[$j]->getAsignaturaTipo()->getId()){
                    $existe = 'si';
                }
            }
            if($existe == 'no'){
                //echo $areas[$i]." - ".$request->get('idInstitucionCurso')."<br>";
                $newArea = new InstitucioneducativaCursoOferta();
                $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                $em->persist($newArea);
                $em->flush();
            }
            //echo $existe."<br>";
        }
        //die;
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$request->getSession()->get('idInstitucion'),'gestionTipo'=>$request->getSession()->get('idGestion'),'rolTipo'=>2));
        $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$idCurso));
        return $this->render('SieRegularBundle:CursosParalelos:listaAreasCurso.html.twig',array('areasCurso'=>$areasCurso,'maestros'=>$maestros));
    }
    
    /*
     * Asignar maestro al area
     */
    public function asignar_maestroAction($idCursoOferta,$idMaestro){
        try{
            $em = $this->getDoctrine()->getManager();
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
            
            $response = new JsonResponse();
            return $response->setData(array('maestro'=>$nombreMaestro,'curso'=>$curso));
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /*
     * Asignar asesor al curso
     */
    public function asignar_asesorAction($idCurso,$idMaestro){
        try{
            $em = $this->getDoctrine()->getManager();
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            //$cursoOferta = new InstitucioneducativaCursoOferta();
            if($idMaestro != 'ninguno'){
                $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestro);
                $curso->setMaestroInscripcionAsesor($maestro);
                $nombreMaestro = $maestro->getPersona()->getPaterno().' '.$maestro->getPersona()->getMaterno().' '.$maestro->getPersona()->getNombre();
            }else{
                $curso->setMaestroInscripcionAsesor(null);
                $nombreMaestro = '';
            }
            $em->flush();
            
            $curso = $curso->getGradoTipo()->getGrado()." ".$curso->getParaleloTipo()->getParalelo();
            
            $response = new JsonResponse();
            return $response->setData(array('maestro'=>$nombreMaestro,'curso'=>$curso));
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
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
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('cursosparalelos_create'))
                ->add('idInstitucion','hidden',array('data'=>$request->get('idInstitucion')))
                ->add('idGestion','hidden',array('data'=>$request->get('idGestion')))
                ->add('turno','choice',array('label'=>'Turno','choices'=>$turnos,'attr'=>array('class'=>'form-control')))
                ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'attr'=>array('class'=>'form-control')))
                ->add('grado','choice',array('label'=>'Grado','choices'=>$grados,'attr'=>array('class'=>'form-control')))
                ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'attr'=>array('class'=>'form-control')))
                ->add('guardar','submit',array('label'=>'Crear Paralelo','attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
        return $this->render('SieRegularBundle:CursosParalelos:new.html.twig',array('form'=>$form->createView()));
    }
    
    public function createAction(Request $request){
        $form = $request->get('form');
        //print_r($form);die;
        $em = $this->getDoctrine()->getManager();
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
            $this->get('session')->getFlashBag()->add('newCursoError', 'Error, el curso ya existe.');
            return $this->redirect($this->generateUrl('cursosparalelos'));
        }else{ 
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
            $em->persist($nuevo_curso);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('newCursoOk', 'Curso creado correctamente');
            return $this->redirect($this->generateUrl('cursosparalelos',array('op'=>'result')));
        }
    }
    
    public function deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
        /*
         * Verificamos si tiene estudiantes inscritos
         */
        $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$request->get('idCurso')));
        if($inscritos){
            $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque tiene estudiantes inscritos');
            return $this->redirect($this->generateUrl('cursosparalelos'));
        }
        /*
         * Verificamos si no tiene registros en curso oferta
         */
        $curso_oferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$request->get('idCurso')));
        if($curso_oferta){
            $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque tiene asignaturas asignadas');
            return $this->redirect($this->generateUrl('cursosparalelos'));
        }
        /*
         * Eliminamos el curso
         */
        $em->remove($curso);
        $em->flush();
        $this->get('session')->getFlashBag()->add('deleteCursoOk', 'Se eliminó el curso correctamente');
        return $this->redirect($this->generateUrl('cursosparalelos',array('op'=>'result')));
    }

    public function listargradosAction($nivel) {
        $em = $this->getDoctrine()->getManager();
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
        return $response->setData(array('listagrados' => $list));
    }
}
