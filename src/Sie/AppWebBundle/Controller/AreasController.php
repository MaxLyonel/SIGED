<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;

/**
 * EstudianteInscripcion controller.
 *
 */
class AreasController extends Controller {

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
                case 'REGULAR': $this->session->set('tituloTipo', 'Adición de Áreas / Asignacion de Maestros');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                    break;
                default: $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Maestros');
                    break;
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
                        return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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

//                    if ($aTuicion[0]['get_ue_tuicion']){
                        $institucion = $form['institucioneducativa'];
                        $gestion = $form['gestion'];
//                    }else{
//                        $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
//                        return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
//                    }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {
                        $funcion = new FuncionesController();
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
                    ORDER BY iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion);

            $cursos = $query->getResult();
            
            /*
             * Guardamos la consulta en un array ordenado
             */
            
            $cursosArray = array();
            foreach ($cursos as $cur) {
                $cursosArray[] = array(
                    'nivel' => $cur->getNivelTipo()->getNivel(),
                    'grado' => $cur->getGradoTipo()->getGrado(),
                    'paralelo' => $cur->getParaleloTipo()->getParalelo(),
                    'turno' => $cur->getTurnoTipo()->getTurno(),
                    'idInstitucion'=>$cur->getInstitucioneducativa()->getId(),
                    'idGestion'=>$cur->getGestionTipo()->getId(),
                    'idInstitucionCurso'=>$cur->getId(),
                    'idNivel' => $cur->getNivelTipo()->getId(),
                );
            }
            
            /*
             * Anadimos las areas del curso al array
             */

            for ($i = 0; $i < count($cursosArray); $i++) {
                $areas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$cursosArray[$i]['idInstitucionCurso']));
                if(count($areas)>0){
                    for($j=0;$j<count($areas);$j++){
                        $cursosArray[$i]['area'][] = array('campo'=>'ff','codigo'=>$areas[$j]->getAsignaturaTipo()->getId(),'asignatura' => $areas[$j]->getAsignaturaTipo()->getAsignatura(), 'maestro' => ($areas[$j]->getMaestroInscripcion() != '')?$areas[$j]->getMaestroInscripcion()->getPersona():'','idInstitucionCursoOferta'=>$areas[$j]->getId(),'idMaestroInscripcion'=>($areas[$j]->getMaestroInscripcion() != '')?$areas[$j]->getMaestroInscripcion()->getId():'');
                    }
                }else{
                    $cursosArray[$i]['area'] = null;//array('campo'=>'','codigo'=>'','asignatura'=>'adfdasf','maestro'=>'dafdsaf');
                }
            }
            
            /*
             * Creamos un nuevo array separado por niveles
             */
            
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
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$institucion,'gestionTipo'=>$gestion,'rolTipo'=>2));
            
            
            return $this->render('SieAppWebBundle:Areas:index.html.twig', array(
                        'inscritos' => $est, 'institucion' => $institucion, 'gestion' => $gestion,'maestros'=>$maestros
            ));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieAppWebBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }
    
    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('areas'))
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
                                    WHERE at.asignaturaNivelTipo = :idNivel
                                    AND at.id != 1045
                                    AND at.id != 1039
                                    ORDER BY at.id ASC';break;
                case 3: 
                case 4: $consulta = 'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivelTipo = :idNivel
                                    AND at.id != 1039
                                    ORDER BY at.id ASC';break;
                case 5: 
                case 6: $consulta = 'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivelTipo = :idNivel
                                    AND at.id != 1038
                                    ORDER BY at.id ASC';break;
            }
            $query = $em->createQuery($consulta)->setParameter('idNivel',$idNivel);
            $areasNivel = $query->getResult();
        }else{
            $query = $em->createQuery(
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.asignaturaNivelTipo = :idNivel
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
        
        return $this->render('SieAppWebBundle:Areas:listaAreas.html.twig',array('areasNivel'=>$areasArray));
    }
    
    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */
    public function lista_areas_cursoAction(Request $request){
        //echo $request->get('idCurso');die;
        $em = $this->getDoctrine()->getManager();
        //echo $request->get('divResultado');
        //echo $request->get('idInstitucionCurso');
        $idCurso = $request->get('idInstitucionCurso');
        
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
                /*    
                $eliminarArea = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areasCurso[$j]->getId());
                $em->remove($eliminarArea);
                $em->flush();*/
            }
            if($existe == 'no'){
                $newArea = new InstitucioneducativaCursoOferta();
                $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                $em->persist($newArea);
                $em->flush();
            }
        }
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa'=>$request->getSession()->get('idInstitucion'),'gestionTipo'=>$request->getSession()->get('idGestion'),'rolTipo'=>2));
        $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$idCurso));
        return $this->render('SieAppWebBundle:Areas:listaAreasCurso.html.twig',array('areasCurso'=>$areasCurso,'maestros'=>$maestros));
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
}
