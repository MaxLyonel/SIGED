<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class CreacionCursosSolicitudController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $this->session->set('tituloTipo', 'Solicitud para la creación de nuevos cursos/paralelos');
            $this->session->set('layout', 'layoutRegular.html.twig');
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                // dump($institucioneducativa);die;
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
               
                if ($institucioneducativa->getEstadoinstitucionTipo()->getId()==19) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'No puede crear cursos, la Unidad Educativa se encuentra cerrada');
                    return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                if (!in_array($institucioneducativa->getDependenciaTipo()->getId(),array(1,2))) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'No puede crear cursos, la Unidad Educativa no es pública');
                    return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];
                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            } else {
                return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));             
            }
            
            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // // Lista de turnos validos para la unidad educativa
            // $query = $em->createQuery(
            //         'SELECT DISTINCT tt.id,tt.turno
            //         FROM SieAppWebBundle:InstitucioneducativaCurso iec
            //         JOIN iec.institucioneducativa ie
            //         JOIN iec.turnoTipo tt
            //         WHERE ie.id = :id
            //         AND iec.gestionTipo = :gestion
            //         ORDER BY tt.id')
            //         ->setParameter('id', $institucion)
            //         ->setParameter('gestion', $gestion);
            // $turnos = $query->getResult();
            // $turnosArray = array();
            // for ($i = 0; $i < count($turnos); $i++) {
            //     $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            // }

            
            // Lista de turnos validos para la unidad educativa
            // $query = $em->createQuery(
            //     'SELECT DISTINCT tt.id,tt.turno
            //         FROM SieAppWebBundle:InstitucioneducativaCurso iec
            //         JOIN iec.institucioneducativa ie
            //         JOIN iec.turnoTipo tt
            //         WHERE ie.id = :id
            //         AND iec.gestionTipo = :gestion
            //         ORDER BY tt.id'
            // )
            // ->setParameter('id', $institucion)
            //     ->setParameter('gestion', $gestion );
            // $turnos = $query->getResult();
            // $turnosArray = array();
            // for ($i = 0; $i < count($turnos); $i++) {
            //     $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            // }    
            
            // /**
            //  * dcastillo 2202: 
            //  * si no hay turnos, ver gestion anterior, si no hay habilitar todos
            //  */

            //  if(sizeof($turnosArray) == 0){

            //     // vemos si hay la gestion anterior
            //     $query = $em->createQuery(
            //         'SELECT DISTINCT tt.id,tt.turno
            //             FROM SieAppWebBundle:InstitucioneducativaCurso iec
            //             JOIN iec.institucioneducativa ie
            //             JOIN iec.turnoTipo tt
            //             WHERE ie.id = :id
            //             AND iec.gestionTipo = :gestion
            //             ORDER BY tt.id'
            //     )
            //     ->setParameter('id', $institucion)
            //         ->setParameter('gestion', $gestion - 1);
            //     $turnos = $query->getResult();
            //     $turnosArray = array();
            //     for ($i = 0; $i < count($turnos); $i++) {
            //         $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            //     }    

            //     if(sizeof($turnosArray) == 0){
            //         // no hay en la gestion anterior, entonces se muestran todos

            //         $RAW_QUERY = 'SELECT * FROM turno_tipo where id not in (0,10,11);';            
            //         $statement = $em->getConnection()->prepare($RAW_QUERY);
            //         $statement->execute();
            //         $result = $statement->fetchAll();                  
            //         $turnos = $result;
            //         $turnosArray = array();
            //         for ($i = 0; $i < count($turnos); $i++) {
            //             $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            //         }

            //     }

            //  }

             /**
             * Creamos el formulario de busqueda de turno nivel grado y paralelo
             */
            
            // dump($turnosArray);die;
            //2023 esto para mostrar todos, para el rol de departamento siged
            //TODO: ver otros casos y en que circunstancias se habilitan los demas turnos
            $RAW_QUERY = 'SELECT * FROM turno_tipo where id  in (1,2,4,8);';            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();                  
            $turnos = $result;
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }

            $form = $this->createFormBuilder()
            ->add('idInstitucion', 'hidden', array('data' => $institucion))
            ->add('idGestion', 'hidden', array('data' => $gestion))
            ->add('turno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
            ->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
            ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
            ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
            // ->add('cantidad', 'text', array('required' => true, 'attr' => array('class' => 'form-control','pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '2', 'autocomplete' => 'off')))
            ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
            ->getForm();

            //dump($turnosArray); die; 

             //dcastillo: 2102 - habilitar todos los paralelos si es privada, solo A si es fiscal
            //$RAW_QUERY = 'SELECT dependencia_tipo_id FROM institucioneducativa where  CAST (id AS INTEGER) = ' .$request->getSession()->get('idInstitucion');            
            $RAW_QUERY = 'SELECT dependencia_tipo_id FROM institucioneducativa where  CAST (id AS INTEGER) = ' .$institucion;                   
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();
            $dependencia = $result;
            //dump($dependencia[0]['dependencia_tipo_id']); die;
            $dependencia_tipo_id = $dependencia[0]['dependencia_tipo_id'];

            //sacamos solo los PERMITIDOS segun RUE para esa UE
            $sw_nivel_primario = false;
            $sw_nivel_inicial = false;
            //para las modulares
            $sw_nivel_secundario = false;

            $RAW_QUERY = "SELECT
                nivel_tipo.id, 
                nivel_tipo.nivel, 
                nivel_tipo.vigente, 
                institucioneducativa_nivel_autorizado.institucioneducativa_id
            FROM
                institucioneducativa_nivel_autorizado
                INNER JOIN
                nivel_tipo
                ON 
                    institucioneducativa_nivel_autorizado.nivel_tipo_id = nivel_tipo.id
            where institucioneducativa_id = '" . $institucion."' and vigente = true";

            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();
            $niveles = $result;

            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];

                //para el caso modulares
                if($niveles[$i]['id'] == 13){
                    //esta UE tiene habilitada Primaria Comunitaria Vocacional
                    $sw_nivel_secundario = true;
                }
                
                //para el caso multigrado
                if($niveles[$i]['id'] == 12){
                    //esta UE tiene habilitada Primaria Comunitaria Vocacional
                    $sw_nivel_primario = true;
                }
                if($niveles[$i]['id'] == 11){
                    //esta UE tiene habilitada Inicial en Familia Comunitaria
                    $sw_nivel_inicial = true;
                }

            }


            /**
             * dcastillo 03052022
             * si la ue no tiene habilitado secundaria, preguntamos si es modular, si es se aumenta al array
             */

            $es_modular = false;
            $paralelosArray = array();           
            //no tiene  habilitado secundaria, vemos si es modular
            $sql = "select count(*) as es_modular
            from institucioneducativa_humanistico_tecnico iht 
            where iht.institucioneducativa_humanistico_tecnico_tipo_id = 3
            and gestion_tipo_id = ".$gestion."
            and institucioneducativa_id = '" . $institucion . "'";
            
            $statement = $em->getConnection()->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll();
            
            $es_modular_result = $result;
            $es_modular_result[0]['es_modular'];
            if($es_modular_result[0]['es_modular'] != 0){
                $es_modular = true;
            }

             /* dcastillo 2302: si tiene nivel primario, se adiciona nivel inicial y solo paralelo A
             * solo si es publico caso mULTIGRADOS
             */

             $sw_habilita_multigrado= false;
             //vemos si la ue tiene al menos un multigrado en primaria gestion 2022 en la tabla 
             //institucioneducativa_curso, campo multigrado en nivel_tipo_id = primaria
             $RAW_QUERY = '
             SELECT count(*) as existe_multigrado FROM institucioneducativa_curso 
             where institucioneducativa_id = ' .$institucion . ' and gestion_tipo_id = ' . $gestion . ' and nivel_tipo_id = 12' ;                   
             
             $statement = $em->getConnection()->prepare($RAW_QUERY);
             $statement->execute();
             $result = $statement->fetchAll();
             $es_multigrado_result = $result;
             $es_multigrado = false;
             if($es_multigrado_result[0]['existe_multigrado'] != 0){
                $es_multigrado = true;
             }

            $RAW_QUERY = 'SELECT * FROM grado_tipo where id in (1,2,3,4,5,6);';            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();                            
            $grados = $result;
            $gradosArray = array();
            for ($i = 0; $i < count($grados); $i++) {
                $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
            }
           
            //TODOS

            //dump(sizeof($paralelosArray)); die;
            // if(sizeof($paralelosArray) == 0)
            // {
                      
            // if( $sw_habilita_multigrado == false){
                // como estaba incialmente antes de multigrado
               
                // if($dependencia_tipo_id != 3 ){

                //     if($this->session->get('roluser') == 9 )
                //     {
                //         //es director?
                //         $RAW_QUERY = 'SELECT * FROM paralelo_tipo where  CAST (id AS INTEGER) = 1;';
                //         $statement = $em->getConnection()->prepare($RAW_QUERY);
                //         $statement->execute();
                //         $result = $statement->fetchAll();
                //         $paralelos = $result;
                //         $paralelosArray = array();
                //         for ($i = 0; $i < count($paralelos); $i++) {
                //             $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
                //         }

                //     }else{
                //         // es distrital o departamental o nacional
                //         if($this->session->get('roluser') == 7 or $this->session->get('roluser') == 11 or $this->session->get('roluser') == 8 ){

                //             $RAW_QUERY = 'SELECT * FROM paralelo_tipo where  CAST (id AS INTEGER) <= 26;';
                //             $statement = $em->getConnection()->prepare($RAW_QUERY);
                //             $statement->execute();
                //             $result = $statement->fetchAll();
                //             $paralelos = $result;
                //             $paralelosArray = array();
                //             for ($i = 0; $i < count($paralelos); $i++) {
                //                 $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
                //             }

                //         }
                //     }

                // }else{

                    $RAW_QUERY = 'SELECT * FROM paralelo_tipo where  CAST (id AS INTEGER) <= 26;';
                    $statement = $em->getConnection()->prepare($RAW_QUERY);
                    $statement->execute();
                    $result = $statement->fetchAll();
                    $paralelos = $result;
                    $paralelosArray = array();
                    for ($i = 0; $i < count($paralelos); $i++) {
                        $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
                    }

                // }
            // }else{
               
            //     if($this->session->get('roluser') == 7 or $this->session->get('roluser') == 8 or $this->session->get('roluser') == 10)
            //     {
                    
            //         if($es_modular == true){
            //             $RAW_QUERY = 'SELECT * FROM paralelo_tipo where  CAST (id AS INTEGER) = 1;';
            //             $statement = $em->getConnection()->prepare($RAW_QUERY);
            //             $statement->execute();
            //             $result = $statement->fetchAll();
            //             $paralelos = $result;
            //             $paralelosArray = array();
            //             for ($i = 0; $i < count($paralelos); $i++) {
            //                 $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
            //             }
            //         }else{

            //             $RAW_QUERY = 'SELECT * FROM paralelo_tipo where  CAST (id AS INTEGER) <= 26;';
            //             $statement = $em->getConnection()->prepare($RAW_QUERY);
            //             $statement->execute();
            //             $result = $statement->fetchAll();
            //             $paralelos = $result;
            //             $paralelosArray = array();
            //             for ($i = 0; $i < count($paralelos); $i++) {
            //                 $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
            //             }
            //         }

            //     }

            // }

            // }
            

            $formNuevo = $this->createFormBuilder()
            ->add('idInstitucion', 'hidden', array('data' => $institucion))
            ->add('idGestion', 'hidden', array('data' => $gestion))
            ->add('nuevoTurno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', )))            
            ->add('nuevoNivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $nivelesArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados2()')))            
            //->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
            //->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
            ->add('nuevoGrado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $gradosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))            
            ->add('nuevoParalelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $paralelosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'validateForm()'))) 
            // ->add('nuevoAncho', 'number', array('label' => 'Ancho', 'required' => true,'rounding_mode' => 0, 'precision' => 2, 'attr' => array('autocomplete' => 'off','class' => 'form-control', 'min'=> 0, 'max' => 100)))//'pattern' => '[.0-9]{5,10}', 'maxlength' => '6')))            
            // ->add('nuevoLargo', 'number', array('label' => 'Largo', 'required' => true,'rounding_mode' => 0, 'precision' => 2, 'attr' => array('autocomplete' => 'off','class' => 'form-control', 'min'=> 0, 'max' => 100))) //'pattern' => '[.0-9]{5,10}', 'maxlength' => '6')))            
            //->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
            ->add('estudiantes', 'number', array('label' => 'Proyeccion Est.', 'required' => true,'rounding_mode' => 0, 'precision' => 2, 'attr' => array('autocomplete' => 'off','class' => 'form-control', 'min'=> 0, 'max' => 99))) //'pattern' => '[.0-9]{5,10}', 'maxlength' => '6')))            
            ->add('crear', 'submit', array('label' => 'Solicitar Crear Curso', 'attr' => array('class' => 'btn btn-success btn-block')))
            ->getForm();
            
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            $tipoUE = $this->get('funciones')->getTipoUE($institucion,$gestion);
            $em->getConnection()->commit();

           


            //dcastillo aqui llama a esa pantalla donde se van a crear los cursos
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:index.html.twig', array(
                        'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion, 'tipoUE'=>$tipoUE , 'form' => $form->createView(), 'formNuevo' => $formNuevo->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array();
        $gestiones[$gestionactual] = $gestionactual;
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('creacion_cursos_solicitud'))
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
        try {
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
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax para la seleccion de nivel grado y paralelo
     */

    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion, $gestion) {
        try {
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('turnos' => $turnosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */

    public function cargarNivelesAction($idInstitucion, $gestion, $turno) {
        try {
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno);
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('niveles' => $nivelesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarGradosAction($idInstitucion, $gestion, $turno, $nivel) {
        try {
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel);
            $grados = $query->getResult();
            $gradosArray = array();
            for ($i = 0; $i < count($grados); $i++) {
                $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('grados' => $gradosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarParalelosAction($idInstitucion, $gestion, $turno, $nivel, $grado) {
        try {
            
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel)
                    ->setParameter('grado', $grado);
            $paralelos = $query->getResult();
            $paralelosArray = array();
            for ($i = 0; $i < count($paralelos); $i++) {
                $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('paralelos' => $paralelosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Registrar datos de dimensiones curso
     */

    public function cursos_infraAction(Request $request) {
        $icid = $request->get('icid');
        $ancho = $request->get('ancho');
        $largo = $request->get('largo');
        $em = $this->getDoctrine()->getManager();
        for ($i = 0; $i < count($icid); $i++) {
            $query = $em->getConnection()->prepare('INSERT INTO public.tramite_crea_curso_infra (institucioneducativa_curso_id, ancho, largo, fecha_registro)
            VALUES(:icid, :ancho, :largo, now())');
            $query->bindValue(':icid', $icid[$i]);
            $query->bindValue(':ancho', $ancho[$i]);
            $query->bindValue(':largo', $largo[$i]);
            $query->execute();
        }
        // $response = new JsonResponse();
        return $this->redirectToRoute('creacion_cursos_solicitud');
        // return $response->setData(array('exito'=>1,'mensaje'=>''));
    }

    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */

    public function lista_cursosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $curYear = date('Y');
        $form = $request->get('form');
        
        $sql = "select ic.id icid, ic.institucioneducativa_id, tt.turno, nt.nivel, gt.grado, pt.paralelo--, COALESCE(tcci.ancho,'0') ancho, COALESCE( tcci.largo,'0') largo 
                from institucioneducativa_curso ic 
                inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                --left join tramite_crea_curso_infra tcci on tcci.institucioneducativa_curso_id = ic.id
                where ic.gestion_tipo_id = ".$curYear."
                and ic.institucioneducativa_id = ".$form['idInstitucion']."
                and ic.nivel_tipo_id = " . $form['nuevoNivel']."
                and ic.grado_tipo_id = " . $form['nuevoGrado']."
                order by ic.institucioneducativa_id, tt.turno, nt.nivel, gt.grado, pt.paralelo";
                        
        $em = $this->getDoctrine()->getManager();      
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();

        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $form['idInstitucion'],
            'gestionTipo' => $form['idGestion'],
            'turnoTipo' => $form['nuevoTurno'],
            'nivelTipo' => $form['nuevoNivel'],
            'gradoTipo' => $form['nuevoGrado'],
            // 'paraleloTipo' => $form['nuevoParalelo'],  
                     
        ));
        

        if ($curso) {
            $idCurso = $curso->getId();
            $mensaje = '';
        } else {
            $mensaje = "No hay asignaturas";
            //return $mensaje;
        }

        // $areasCurso = $this->get('areas')->getAreas($idCurso);
        $operativo = $this->get('funciones')->obtenerOperativo($form['idInstitucion'],$form['idGestion']);

        
        // $existenOfertas = sizeof($areasCurso['cursoOferta']);
        
        return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaAreasCurso.html.twig', array(
            'areas' => $result,
            // 'curso' => $curso,
            'mensaje' => $mensaje,
            // 'operativo'=>$operativo,
            // 'institucioneducativa' => $form['idInstitucion'],
            // 'gestionTipo' => $form['idGestion'],
            // 'turnoTipo' => $form['nuevoTurno'],
            // 'nivelTipo' => $form['nuevoNivel'],
            // 'gradoTipo' => $form['nuevoGrado'],
            // 'paraleloTipo' => $form['nuevoParalelo'],            
        ));
    }

    public function lista_solicitudes_cursosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $curYear = date('Y');
        $form = $request->get('form');
        
        $sql = "select tcc.id as tccid, tcc.institucioneducativa_id, tt.turno, nt.nivel, gt.grado, pt.paralelo,tcc.estudiantes, tcc.fecha_creacion, tcc.fecha_solicitud, tcc.aprobado
        from tramite_crea_curso tcc  
        inner join turno_tipo tt on tcc.turno_tipo_id = tt.id
        inner join nivel_tipo nt on tcc.nivel_tipo_id = nt.id
        inner join grado_tipo gt on tcc.grado_tipo_id = gt.id
        inner join paralelo_tipo pt on tcc.paralelo_tipo_id = pt.id
        where tcc.gestion_tipo_id = ".$curYear."
        and tcc.institucioneducativa_id = ".$form['idInstitucion']."
        order by tcc.fecha_creacion desc";
                        
        $em = $this->getDoctrine()->getManager();      
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
       
        // dump($result); die;
        if (count($result)>0) {
            $mensaje = '';
        } else {
            $mensaje = "No hay solicitudes";
        }

        // $areasCurso = $this->get('areas')->getAreas($idCurso);
        // $operativo = $this->get('funciones')->obtenerOperativo($form['idInstitucion'],$form['idGestion']);

        
        // $existenOfertas = sizeof($areasCurso['cursoOferta']);
        
        return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaSolicitudCurso.html.twig', array(
            'areas' => $result,
            'mensaje' => $mensaje,
            'institucioneducativa' => $form['idInstitucion'],
            'gestionTipo' => $form['idGestion'],
            'turnoTipo' => $form['nuevoTurno'],
            'nivelTipo' => $form['nuevoNivel'],
            'gradoTipo' => $form['nuevoGrado'],
            'paraleloTipo' => $form['nuevoParalelo'],            
        ));
    }


    /*
     * Lista de areas segun el nivel
     * ventana modal
     */

    public function lista_areas_nivelAction($idNivel, $idCurso, $institucioneducativa,$gestionTipo,$turnoTipo,$nivelTipo,$gradoTipo,$paraleloTipo) {  


            // validamos que el nivel sea autorizado
            $sql = "select count(*) from
                (
                SELECT
                    nivel_tipo.id, 
                    nivel_tipo.nivel, 
                    nivel_tipo.vigente, 
                    institucioneducativa_nivel_autorizado.institucioneducativa_id
                FROM
                    institucioneducativa_nivel_autorizado
                    INNER JOIN
                    nivel_tipo
                    ON 
                        institucioneducativa_nivel_autorizado.nivel_tipo_id = nivel_tipo.id
                where institucioneducativa_id = '".$institucioneducativa."' and vigente = true
                )as tmp where id = " . $nivelTipo;

            $em = $this->getDoctrine()->getManager();      
            $statement = $em->getConnection()->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll();
            //dump($result[0]['count']); die;
            $nivelautorizado = $result[0]['count'];

                    $em = $this->getDoctrine()->getManager();      
                    $query = $em->getConnection()->prepare("select * FROM sp_crea_curso_oferta('$gestionTipo', '$institucioneducativa', '$turnoTipo', '$nivelTipo', '$gradoTipo','$paraleloTipo') ");
                    $query->execute();
                    $valor= $query->fetchAll();
                    $res= $valor[0]['sp_crea_curso_oferta'];
                    /* fin modificacion */

                    $res= $valor[0]['sp_crea_curso_oferta'];
                    $response = new JsonResponse();
                    return $response->setData(array('exito'=>$res,'mensaje'=>''));
          


           
    }

    /**
     * dcastillo
     * esto se deja por el momento
     */
    public function lista_areas_nivelActionOLD($idNivel, $idCurso, $institucioneducativa,$gestionTipo,$turnoTipo,$nivelTipo,$gradoTipo,$paraleloTipo) {        
        try {
            
            $em = $this->getDoctrine()->getManager();      
            $query = $em->getConnection()->prepare("select * FROM sp_crea_curso_oferta('$gestionTipo', '$institucioneducativa', '$turnoTipo', '$nivelTipo', '$gradoTipo','$paraleloTipo') ");
            $query->execute();
            $valor= $query->fetchAll();
            $res= $valor[0]['sp_crea_curso_oferta'];
            //var_dump($res);die;
            /* fin modificacion */
            
            //dump('fin'); die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            
            $areas = $this->get('areas')->getAreas($idCurso);
            $idsAsignaturas = $areas['asignaturas'];

            $asignaturas = $em->createQuery(
                    'SELECT at
                    FROM SieAppWebBundle:AsignaturaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
            )->setParameter('ids',$idsAsignaturas)
            ->getResult();
            
            $areasCurso = $areas['cursoOferta'];
                
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($idCurso);
            
            $tmpAsignaturaHistorico = $em->getRepository('SieAppWebBundle:TmpAsignaturaHistorico')->findBy(array(
                'gestionTipoId' => $curso->getGestionTipo()->getId(),
                'nivelTipoId' => $curso->getNivelTipo()->getId(),
                'gradoTipoId' => $curso->getGradoTipo()->getId()
            ));

            $areasNivel = [];

            foreach ($asignaturas as $key => $asignatura) {
                $existe_asignatura = false;
                foreach ($tmpAsignaturaHistorico as $key => $tmp) {
                    if($asignatura->getId() == $tmp->getAsignaturaTipo()->getId()) {
                        $existe_asignatura = true;
                    }
                }
                if($existe_asignatura) {
                    array_push($areasNivel,$asignatura);
                }
            }

            $areasArray = array();
            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areasNivel[$i]->getId() == $areasCurso[$j]['idAsignatura']) {
                        $check = 'checked';
                        $bloqueado = 'disabled';
                    }
                }
                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') {
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
                }
            }

            $maestros = $em->createQueryBuilder()
                ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet')
                ->from('SieAppWebBundle:MaestroInscripcion','mi')
                ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('gt.id = :gestion')
                ->andWhere('mi.esVigenteAdministrativo = :esvigente')
                ->andWhere('ct.id = :cargo')
                ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                ->setParameter('gestion',$this->session->get('idGestion'))
                ->setParameter('esvigente','t')
                ->setParameter('cargo',0)
                ->orderBy('p.paterno','asc')
                ->addOrderBy('p.materno','asc')
                ->addOrderBy('p.nombre','asc')
                ->getQuery()
                ->getResult();

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
            //return $this->render('SieRegularBundle:CreacionCursosSolicitud:show.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /**
     * Fcunrion para eadicionar y elimiar areas
     */
    public function eliminar_solicitudAction($solicitudnum) {
        try {
            
            $sql = "
                SELECT *
                FROM tramite_crea_curso                    
                where id = ".$solicitudnum."; ";
            $em = $this->getDoctrine()->getManager();      
            $statement = $em->getConnection()->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll();
            $response = new JsonResponse();
            if ($result[0]['aprobado']==null and $result[0]['fecha_aprobacion']==null){
                $query = $em->getConnection()->prepare("delete FROM  tramite_crea_curso where id = '$solicitudnum' ");
                $query->execute();
                return $response->setData(array('exito'=>1,'mensaje'=>'Se elimino correctamente'));
            } else{
                return $response->setData(array('exito'=>0,'mensaje'=>'El curso solicitado no se puede eliminar'));
            }

        } catch (Exception $ex) {
             $em->getConnection()->rollback();
             $response = new JsonResponse();
             return $response->setData(array('exito'=>0,'mensaje'=>'El curso solicitado no se puede eliminar'));
        }
    }

    public function nuevaAction($idCurso,$idAsignatura){
        try {
            $em = $this->getDoctrine()->getManager();
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);

            $nuevoRegistro = $this->get('areas')->nuevo($idCurso,$idAsignatura);

            $areasCurso = $this->get('areas')->getAreas($idCurso);

            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaAreasCurso.html.twig', array(
                        'areas' => $areasCurso,
                        'curso' => $curso,
                        'mensaje' => '',
                        'operativo'=>$operativo 
            ));

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => ''));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Eliminar un area
     */
    public function deleteAction($idCursoOferta) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());

            $eliminar = $this->get('areas')->delete($idCursoOferta);

            $idCurso = $curso->getId();

            $areasCurso = $this->get('areas')->getAreas($idCurso);
            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:CreacionCursosSolicitud:listaAreasCurso.html.twig', array(
                        'areas' => $areasCurso,
                        'curso' => $curso,
                        'mensaje' => '',
                        'operativo'=>$operativo
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Asignar maestro al area
     */

    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');

        $maestros = $this->get('maestroAsignacion')->listar($ieco);

        return $this->render('SieRegularBundle:CreacionCursosSolicitud:maestros.html.twig',array('maestros'=>$maestros));
    }

    public function maestrosAsignarAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idco = $this->get('maestroAsignacion')->asignar($request);
        $idco = $idco->getContent();
        $idco = json_decode($idco,true);
        $idco = $idco['ieco'];

        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$idco,'mensaje'=>''));
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
        }
        return $lit;
    }

    /*
    dcastillo crear nuevos cursos
    */
    public function crear_areas_cursoAction(Request $request) {
        
        $form = $request->get('form');
        

        $turno_id = $form['nuevoTurno'];
        $nivel_id = $form['nuevoNivel'];
        $grado_id = $form['nuevoGrado'];
        $paralelo_id = $form['nuevoParalelo'];
        $estudiantes = $form['estudiantes'];
        // $ancho_id = $form['nuevoAncho'];
        // $largo_id = $form['nuevoLargo'];
        
        $institucion_id = $form['idInstitucion'];
        $gestion_id = $form['idGestion'];
        
       
        $em = $this->getDoctrine()->getManager();     
        $response = new JsonResponse(); 
        $msg = "";

        // vemos si tiene director
        $sql = "
            select count(*) as existe_director
            from institucioneducativa_sucursal is2 
            where is2.institucioneducativa_id = " .$institucion_id . "
            and is2.gestion_tipo_id = " . $gestion_id ;
        
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        if($result[0]['existe_director'] == 0){
            $msg = "La UE no tiene director asignado en la gestion !";
            return $response->setData(array('exito'=>0,'mensaje'=>$msg));
        }
        
        // vemos si ya existe el curso
        $sql = "
            select count(*) as existe_curso
            from institucioneducativa_curso
            where institucioneducativa_id = " . $institucion_id . "
            and gestion_tipo_id = " . $gestion_id . "
            and turno_tipo_id = " . $turno_id . "
            and grado_tipo_id = " . $grado_id . "
            and nivel_tipo_id = " . $nivel_id . "
            and paralelo_tipo_id = '" . $paralelo_id . "'";

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        if ($result[0]['existe_curso'] != 0) {
            $msg = "EL CURSO YA EXISTE !";
            return $response->setData(array('exito' => 0, 'mensaje' => $msg));
        }

        // $sql = " select count(*) as noexiste_infra
        // from (
        // select ic.id, ic.institucioneducativa_id, tt.turno, nt.nivel, gt.grado, pt.paralelo, tcci.ancho, tcci.largo 
        // from institucioneducativa_curso ic 
        //     inner join turno_tipo tt on ic.turno_tipo_id = tt.id
        //     inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
        //     inner join grado_tipo gt on ic.grado_tipo_id = gt.id
        //     inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
        //     left join tramite_crea_curso_infra tcci on tcci.institucioneducativa_curso_id = ic.id
        //     where ic.gestion_tipo_id = " . $gestion_id . "
        //     and ic.institucioneducativa_id = " . $institucion_id . "
        //     and ic.nivel_tipo_id = " . $nivel_id . "
        //     and ic.grado_tipo_id = " . $grado_id . "
        //     order by ic.institucioneducativa_id, tt.turno, nt.nivel, gt.grado, pt.paralelo
        // ) a
        // where  a.ancho is null or a.largo is null";

        // $statement = $em->getConnection()->prepare($sql);
        // $statement->execute();
        // $result = $statement->fetchAll();
        // if ($result[0]['noexiste_infra'] != 0) {
        //     $msg = "DEBE PREVIAMENTE LLENAR DATOS DE INFRAESTRUCTURA!";
        //     return $response->setData(array('exito' => 0, 'mensaje' => $msg));
        // }

        $sql = " select count(*) as sol_existe
        from (
            select tcc.institucioneducativa_id, tcc.turno_tipo_id, tcc.nivel_tipo_id, tcc.grado_tipo_id, tcc.paralelo_tipo_id
            from tramite_crea_curso tcc  
            where tcc.gestion_tipo_id = " . $gestion_id . "
            and tcc.institucioneducativa_id = " . $institucion_id . "
            and tcc.nivel_tipo_id = " . $nivel_id . "
            and tcc.grado_tipo_id = " . $grado_id . "
            and tcc.turno_tipo_id = " . $turno_id . "
            and tcc.paralelo_tipo_id = '" . $paralelo_id . "'
            and tcc.aprobado is null ) a ";

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        if ($result[0]['sol_existe'] != 0) {
            $msg = "EXISTE UNA SOLICITUD PENDIENTE!";
            return $response->setData(array('exito' => 0, 'mensaje' => $msg));
        }

        $usuario_id = $this->session->get('userId');
        $query = $em->getConnection()->prepare("select * FROM sp_crea_nueva_solicitud_curso('$gestion_id', '$institucion_id', '$turno_id', '$nivel_id', '$grado_id','$paralelo_id','0','0','$estudiantes','$usuario_id') ");
      
        $query->execute();
        $valor= $query->fetchAll();
        $res= $valor[0]['sp_crea_nueva_solicitud_curso'];   
         
        return $response->setData(array('exito'=>$res,'mensaje'=>$msg));
       
    }   

}
