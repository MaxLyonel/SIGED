<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos;

class InscripcionController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_usuario = $this->session->get('userName');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $entidadId = $id_usuario;
        //$entidadId = 60900047;
        $faseId = 1;

        $em = $this->getDoctrine()->getManager();

        $objEntidad = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $entidadId));

        $query = $em->getConnection()->prepare("
                    select nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                    where ie.id = ".$entidadId." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$faseId."
                    group by pt.disciplina_tipo_id
                    ) as v
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        $query->execute();
        $entityDisciplinas = $query->fetchAll();

        if(count($objEntidad)==0){
            return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                        'infoDisciplinas' => $entityDisciplinas,
            ));
        }

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoDisciplinas' => $entityDisciplinas,
        ));
    }

    public function fpBusquedaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':Inscripcion:index.html.twig', array(
                    'gestion' => $gestion,
                    'formBusqueda' => $this->creaFormularioBuscador('sie_juegos_inscripcion_fp_index','')->createView()
        ));
    }

    private function creaFormularioBuscador($routing, $sie) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sieBusqueda', 'text', array('label' => 'SIE', 'attr' => array('value' => $sie, 'placeholder' => 'Ingresar código SIE', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                //->add('submit', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function fpIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');
            if ($form) {
                if (isset($form['sieBusqueda'])){
                    $sie = $form['sieBusqueda'];
                } else {
                    $sie = $form['sie'];
                }
            }  else {
                $sie = $request->get('sieBusqueda');
                if ($sie == ''){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_juegos_inscripcion_fp_busqueda'));
                }
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar información, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_fp_busqueda'));
        }


        $em = $this->getDoctrine()->getManager();

        /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $sie);
        $query->bindValue(':rolId', 8);
        $query->execute();
        $aTuicion = $query->fetchAll();

        if (!$aTuicion[0]['get_ue_tuicion']){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la unidad educativa'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_fp_busqueda'));
        }

        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));

        if (!$objUeducativa) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe el Código SIE, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_fp_busqueda'));
        }

        if ($objUeducativa->getInstitucioneducativaAcreditacionTipo()->getId() == 0) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Unidad Educativa legalmente no acreditada, regularizar la observación e intentar nuevamente'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_fp_busqueda'));
        }

        $exist = true;

        $query = $em->getConnection()->prepare("
                  select distinct nt.id as nivelid, nt.nivel as nivel, gt.id as gradoid, gt.grado as grado, get.id as generoid, get.genero as genero from institucioneducativa_curso as iec
                    inner join nivel_tipo as nt on nt.id = iec.nivel_tipo_id
                    inner join grado_tipo as gt on gt.id = iec.grado_tipo_id
                    inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join genero_tipo as get on get.id = e.genero_tipo_id
                    where iec.institucioneducativa_id = " . $sie . " and gestion_tipo_id = " . $gestion . " and nt.id in (12,13) /*and (case when (EXISTS (select esactivo_primaria from fase_tipo where id = 1 and esactivo_primaria = 't' and esactivo_secundaria = 't')) then iec.nivel_tipo_id in (12,13) when (EXISTS (select esactivo_primaria from fase_tipo where id = 1 and esactivo_primaria = 't')) then iec.nivel_tipo_id in (12) when (EXISTS (select esactivo_primaria from fase_tipo where id = 1 and esactivo_secundaria = 't')) then iec.nivel_tipo_id in (13) else iec.nivel_tipo_id in (0) end)   */
                    order by nt.id, gt.id, get.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();

        //if ($entityNiveles) {

            $aInfoUnidadEductiva = array();
            foreach ($entityNiveles as $uEducativa) {
                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'genero' => $uEducativa['genero']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelid'], 'gradoId' => $uEducativa['gradoid'], 'generoId' => $uEducativa['generoid']),
                    'requestUser' => array('sie' => $sie, 'gestion' => $gestion)
                ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['genero']] = array('infoUe' => $sinfoUeducativa);
            }
        //} else {
        //    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de la Unidad Educativa ó Código SIE no existe '));
        //    return $this->redirect($this->generateUrl('sie_juegos_inscripcion_index'));
        //}

        $objEstudiantesRegistrados = $this->buscaInscritosInstitucionEducativa($sie,1);

        return $this->render($this->session->get('pathSystem') . ':Inscripcion:index.html.twig', array(
                    'infoUnidadEducativa' => $objUeducativa,
                    'infoNiveles' => $aInfoUnidadEductiva,
                    'infoDeportistas' => $objEstudiantesRegistrados,
                    'sie' => $sie,
                    'gestion' => $gestion,
                    'exist' => $exist,
                    'formBusqueda' => $this->creaFormularioBuscador('sie_juegos_inscripcion_fp_index',$sie)->createView(),
        ));
    }

    /**
     * get request
     * @param type $request
     * return list of students
     */
    public function listaEstudiantesAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $gradoId = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $generoId = $aInfoUeducativa['ueducativaInfoId']['generoId'];
        $nivel = $aInfoUeducativa['ueducativaInfo']['nivel'];
        $grado = $aInfoUeducativa['ueducativaInfo']['grado'];
        $genero = $aInfoUeducativa['ueducativaInfo']['genero'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerNivelGradoGenero($sie, $gestion, $nivelId, $gradoId, $generoId);

        $exist = true;
        $aData = array();
        //check if the data exist
        if ($objStudents) {
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivelId, 'grado' => $gradoId, 'genero' => $generoId));
            $exist = true;
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }


        return $this->render($this->session->get('pathSystem') . ':Inscripcion:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'grado' => $grado,
                    'genero' => $genero,
                    'infoUe' => $infoUe,
                    'exist' => $exist,
                    'form' => $this->creaFormularioRegistro('sie_juegos_inscripcion_lista_estudiantes_registro', $sie, $gestion, $nivelId, $gradoId, $generoId, 0)->createView(),
        ));
    }

    /**
     * get request
     * @param type $request
     * return registro de estudiantes incritos en fase previa
     */
    public function listaEstudiantesRegistroAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $fase = 1;

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            if ($form) {
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $nivel = $form['nivel'];
                $grado = $form['grado'];
                $genero = $form['genero'];
                $disciplina = $form['disciplina'];
                $prueba = $form['prueba'];
                $deportistas = $request->get('deportistas');
                $listaDeportistas = "";

                $query = $em->getConnection()->prepare("select * from fase_tipo where id = 1");
                $query->execute();
                $faseTipoEntity = $query->fetchAll();

                if(count($faseTipoEntity)<1){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se cuenta habilitado la inscripcion de deportistas para la Fase Previa"));
                    return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
                }

                if(!$faseTipoEntity[0]['esactivo_primaria']){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron"));
                    return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
                }
                if(!$faseTipoEntity[0]['esactivo_secundaria']){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron"));
                    return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
                }

                if ($deportistas != ''){
                    foreach($deportistas as $deportista){
                        if ($listaDeportistas == ""){
                            $listaDeportistas = $deportista;
                        } else {
                            $listaDeportistas = $listaDeportistas.",".$deportista."";
                        }
                    }
                } else {
                    $listaDeportistas = "Sin deportistas";
                }
                try {
                    $pruebaEntity = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $prueba));
                    $gestionEntity = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionActual));
                    $faseEntity = $em->getRepository('SieAppWebBundle:FaseTipo')->findOneBy(array('id' => $fase));
                    $msgEstudiantesRegistrados = "";
                    $msgEstudiantesObservados = "";
                    foreach($deportistas as $deportista){
                        $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $deportista));
                        $estudianteInscripcionEstadoInicio = $estudianteInscripcionEntity->getEstadomatriculaInicioTipo()->getId();
                        $estudianteInscripcionEstado = $estudianteInscripcionEntity->getEstadomatriculaTipo()->getId();
                        $estudianteInscripcionNombre = $estudianteInscripcionEntity->getEstudiante()->getNombre().' '.$estudianteInscripcionEntity->getEstudiante()->getPaterno().' '.$estudianteInscripcionEntity->getEstudiante()->getMaterno();

                        //if(!$faseTipoEntity[0]['esactivo_primaria'] and $estudianteInscripcionEstadoInicio != 15 and $nivel == 12){
                        //    $msg = array('0'=>false, '1'=>$estudianteInscripcionNombre.' (Inscripciones abiertas solo para traslados)');
                        //} else {
                        $msg = $this->validaInscripcionJuegos($deportista,$gestion,$prueba,$fase,$nivel);
                        //}

                        if($msg[0]){
                            $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $deportista));
                            $estudianteInscripcionJuegos = new EstudianteInscripcionJuegos();
                            $estudianteInscripcionJuegos->setEstudianteInscripcion($estudianteInscripcionEntity );
                            $estudianteInscripcionJuegos->setPruebaTipo($pruebaEntity);
                            $estudianteInscripcionJuegos->setGestionTipo($gestionEntity);
                            $estudianteInscripcionJuegos->setFaseTipo($faseEntity);
                            $estudianteInscripcionJuegos->setFechaRegistro($fechaActual);
                            $estudianteInscripcionJuegos->setFechaModificacion($fechaActual);
                            $estudianteInscripcionJuegos->setUsuarioId($id_usuario);
                            $em->persist($estudianteInscripcionJuegos);
                            $em->flush();
                            $estudianteInscripcionJuegosId = $estudianteInscripcionJuegos->getId();
                            if ($msgEstudiantesRegistrados == ""){
                                $msgEstudiantesRegistrados = $msg[1];
                            } else {
                                $msgEstudiantesRegistrados = $msgEstudiantesRegistrados.' - '.$msg[1];
                            }
                        } else {
                            if ($msgEstudiantesObservados == ""){
                                $msgEstudiantesObservados = $msg[1];
                            } else {
                                $msgEstudiantesObservados = $msgEstudiantesObservados.' - '.$msg[1];
                            }
                        }
                    }
                    if ($msgEstudiantesRegistrados != ""){
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante (".$msgEstudiantesRegistrados.") "));
                    }
                    if ($msgEstudiantesObservados != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "Estudiante (".$msgEstudiantesObservados.") "));
                    }

                    $em->getConnection()->commit();
                    return $this->redirectToRoute('sie_juegos_inscripcion_fp_index', array('sieBusqueda'=>$sie), 307);
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                    return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
                }
            } else {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
        } else {
            $msg = "Datos no validos, intente nuevamente";
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
        }
        return $this->render($this->session->get('pathSystem') . ':Inscripcion:seeStudents.html.twig');
    }


    /**
     * get Form
     * @param type $routing
     * @param type $value1
     * @param type $value2
     * @param type $value3
     * @param type $value4
     * @param type $value5
     * @param type $identificador
     * return form
     */
    private function creaFormularioRegistro($routing, $value1, $value2, $value3, $value4, $value5, $identificador) {
        $this->nivelId = $value3;
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'hidden', array('attr' => array('value' => $value1)))
                ->add('gestion', 'hidden', array('attr' => array('value' => $value2)))
                ->add('nivel', 'hidden', array('attr' => array('value' => $value3)))
                ->add('grado', 'hidden', array('attr' => array('value' => $value4)))
                ->add('genero', 'hidden', array('attr' => array('value' => $value5)))
                ->add('disciplina', 'entity', array('data' => '', 'attr' => array('class' => 'form-control mb-15', 'required' => 'required'), 'class' => 'Sie\AppWebBundle\Entity\DisciplinaTipo',
                    'required'    => true,
                    'placeholder' => 'Seleccionar',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('dt')
                        ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = dt.nivelTipo')
                        ->where('nt.id = :codNivel')
                        ->andWhere("dt.estado = 't'")
                        ->setParameter('codNivel', $this->nivelId)
                        ->orderBy('dt.id', 'ASC');
            },
                ))
                ->add('prueba', 'entity', array('data' => '', 'attr' => array('class' => 'form-control mb-15', 'required' => 'required'), 'class' => 'Sie\AppWebBundle\Entity\PruebaTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('pt')
                        ->where('pt.id = 0')
                        ->andWhere("pt.esactivo = 't'")
                        ->orderBy('pt.prueba', 'ASC');
            },
                ))
                ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-')))
                ->getForm();
        return $form;
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaPruebasAction($ue, $disciplina, $genero) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        //get grado
        $apruebas = array();
        $ainscritos = array();
        $entity = $em->getRepository('SieAppWebBundle:PruebaTipo');
        $query = $entity->createQueryBuilder('pt')
                ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'gt', 'WITH', 'gt.id = pt.generoTipo')
                ->where('dt.id = :disciplinaId')
                ->andWhere('gt.id = :generoId')
                ->andWhere("pt.esactivo = 't'")
                ->setParameter('disciplinaId', $disciplina)
                ->setParameter('generoId', $genero)
                ->distinct()
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aPruebas = $query->getResult();
        foreach ($aPruebas as $prueba) {
            $apruebas[$prueba->getId()] = $prueba->getPrueba();
        }

        if (count($apruebas) > 0){
            $aprueba01 = array_keys($apruebas)[0];
        } else {
            $aprueba01 = 0;
        }

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->setParameter('ueId', $ue)
                ->setParameter('pruebaId', $aprueba01)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('e.paterno, e.materno, e.nombre')
                ->getQuery();
        $aInscritos = $query->getResult();
        foreach ($aInscritos as $inscrito) {
            $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre();
        }

        $response = new JsonResponse();
        return $response->setData(array('apruebas' => $apruebas,'ainscritos' => $ainscritos));
    }

    /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosPruebaAction($prueba,$ue) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->setParameter('ueId', $ue)
                ->setParameter('pruebaId', $prueba)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aInscritos = $query->getResult();
        if(count($aInscritos)>0){
            foreach ($aInscritos as $inscrito) {
                $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
            }
        } else {
            $ainscritos = array();
        }
        $response = new JsonResponse();
        return $response->setData(array('ainscritos' => $ainscritos));
    }

    /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosInstitucionEducativa($ue,$fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select('eij.id as eInsId, pat.paralelo, pt.prueba as prueba, dt.disciplina as disciplina, e.paterno, e.materno, e.nombre, e.fechaNacimiento, e.codigoRude, e.carnetIdentidad, e.complemento')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:ParaleloTipo','pat','WITH','pat.id = iec.paraleloTipo')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:FaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ft.id = :faseId')
                ->andWhere('ie.id = :ueId')
                ->setParameter('ueId', $ue)
                ->setParameter('faseId', $fase)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('e.paterno, e.materno, e.nombre, dt.disciplina, pt.prueba')
                ->getQuery();
        $aInscritos = $query->getResult();
        //if(count($aInscritos)>0){
        //    foreach ($aInscritos as $inscrito) {
        //        $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
        //    }
        //} else {
        //    $ainscritos = array();
        //}
        //$response = new JsonResponse();
        //return $response->setData(array('ainscritos' => $ainscritos));
        return $aInscritos;
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function validaInscripcionJuegos($inscripcionEstudiante,$gestion,$prueba,$fase,$nivel) {
        $em = $this->getDoctrine()->getManager();
        if ($nivel == 12){
            $query = $em->getConnection()->prepare("select * from (
                select count(eij.id) as cant, eij.estudiante_inscripcion_id, COUNT(distinct disciplina) as cantdisc
                , sum(case when dt.id = 14 or dt.id = 15 or dt.id = 16 or dt.id = 17 then 1 else 0 end)  as cant_dis_conj
                , sum(case when dt.id <> 14 and dt.id <> 15 and dt.id <> 16 and dt.id <> 17 then 1 else 0 end)  as cant_dis_indi
                , sum(case when dt.id = 18 and (pt.id = 99 or pt.id = 100 or pt.id = 101 or pt.id = 102) then 1 else 0 end)  as cant_pru_nat
                , string_agg(distinct dt.disciplina,', ') as disciplinas
                , string_agg(distinct pt.prueba,', ') as pruebas
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                where eij.estudiante_inscripcion_id = ".$inscripcionEstudiante." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase."
                group by eij.estudiante_inscripcion_id
                order by cant desc
                ) as v");
        } else {
            $query = $em->getConnection()->prepare("select * from (
                select count(eij.id) as cant, eij.estudiante_inscripcion_id, COUNT(distinct disciplina) as cantdisc
                , sum(case when dt.id = 3 or dt.id = 4 or dt.id = 5 or dt.id = 7 then 1 else 0 end)  as cant_dis_conj
                , sum(case when dt.id <> 3 and dt.id <> 4 and dt.id <> 5 and dt.id <> 7 then 1 else 0 end)  as cant_dis_indi
                , sum(case when dt.id = 2 and (pt.id = 27 or pt.id = 28) then 1 else 0 end)  as cant_pru_conj_atle
                , sum(case when dt.id = 2 and (pt.id <> 27 and pt.id <> 28) then 1 else 0 end)  as cant_pru_indi_atle
                , sum(case when dt.id = 8 and (pt.id = 63 or pt.id = 64 or pt.id = 75 or pt.id = 76) then 1 else 0 end)  as cant_pru_conj_nat
                , sum(case when dt.id = 8 and (pt.id <> 63 and pt.id <> 64 and pt.id <> 75 and pt.id <> 76) then 1 else 0 end)  as cant_pru_indi_nat
                , sum(case when dt.id = 6 then 1 else 0 end)  as cant_pru_cic
                , sum(case when dt.id = 9 then 1 else 0 end)  as cant_pru_raq
                , sum(case when dt.id = 11 then 1 else 0 end)  as cant_pru_ten
                , string_agg(distinct dt.disciplina,', ') as disciplinas
                , string_agg(distinct pt.prueba,', ') as pruebas
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                where eij.estudiante_inscripcion_id = ".$inscripcionEstudiante." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase."
                group by eij.estudiante_inscripcion_id
                order by cant desc
                ) as v ");
        }
        $query->execute();
        $inscripcionEstudianteEntity = $query->fetchAll();

        $tipoDisciplinaPrueba = $this->verificaTipoDisciplinaPrueba($prueba);
        $inscripcionEstudianteGestionPruebaFase = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,$prueba,$fase);
        $inscripcionEstudianteGestionDisciplinaFase = $this->verificaInscripcionEstudianteGestionDisciplinaFase($inscripcionEstudiante,$gestion,$prueba,$fase);
        $estudiante = $this->verificaInscripcionEstudiante($inscripcionEstudiante);
        $cantidadIncritosGestionPruebaFase = $this->validaCantidadGestionPruebaFaseUe($gestion,$prueba,$fase,$estudiante["sie"]);

        /*
        **************** INCLUIR LA VERIFICACION DEL CUPO ************************
        */

        $edadValida = true;

        if($nivel == 13){
            if($estudiante["gestion_nacimiento"] < 1998 or $estudiante["gestion_nacimiento"] > 2005){
                $edadValida = false;
            }
        }
        if($nivel == 12) {
            if($estudiante["gestion_nacimiento"] < 2005 or $estudiante["gestion_nacimiento"] > 2011){
                $edadValida = false;
            }
        }


        if ($edadValida){
            if($inscripcionEstudianteGestionPruebaFase[0]){
                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en: '.$tipoDisciplinaPrueba["prueba"].')');
            } else {
                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                if ($nivel == 12){
                    if ($tipoDisciplinaPrueba["idDisciplina"] == 19){ //gimnasia artistica
                        if ($prueba == 103 or $prueba == 104){ // nivel I
                            if ($estudiante["edad_gestion"] >= 6 and $estudiante["edad_gestion"]<=9){
                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                            } else {
                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 6 a 9 años)');
                            }
                        }
                        if ($prueba == 105 or $prueba == 106){ // nivel II
                            if ($estudiante["edad_gestion"] >= 10 and $estudiante["edad_gestion"]<=12){
                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                            } else {
                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 10 a 12 años)');
                            }
                        }
                    }
                } else {
                    if ($tipoDisciplinaPrueba["idDisciplina"] == 8){ //natacion
                        if ($prueba == 65 or $prueba == 66 or $prueba == 67 or $prueba == 68 or $prueba == 69 or $prueba == 70 or $prueba == 71 or $prueba == 72 or $prueba == 73 or $prueba == 74 or $prueba == 124 or $prueba == 125 or $prueba == 126 or $prueba == 127 or $prueba == 128 or $prueba == 129 or $prueba == 130 or $prueba == 131 or $prueba == 132 or $prueba == 133){ // promocional
                            if ($estudiante["gestion_nacimiento"] >= 2003 and $estudiante["gestion_nacimiento"]<=2005){
                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                            } else {
                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 12 a 14 años y 2003 a 2005 en el año de nacimiento)');
                            }
                        }
                        if ($prueba == 114 or $prueba == 115 or $prueba == 116 or $prueba == 117 or $prueba == 118 or $prueba == 119 or $prueba == 120 or $prueba == 121 or $prueba == 122 or $prueba == 123 or $prueba == 138 or $prueba == 139 or $prueba == 140 or $prueba == 141 or $prueba == 142 or $prueba == 143 or $prueba == 144 or $prueba == 145 or $prueba == 146 or $prueba == 147){ // avanzado
                            if ($estudiante["gestion_nacimiento"] >= 1998 and $estudiante["gestion_nacimiento"]<=2002){
                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                            } else {
                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 10 a 12 años y 1998 a 2002 en el año de nacimiento)');
                            }
                        }
                    }
                    if ($tipoDisciplinaPrueba["idDisciplina"] == 6){ //ciclismo
                        if (count($inscripcionEstudianteEntity) > 0){
                            if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                if ($prueba == 47 or $prueba == 48 or $prueba == 49 or $prueba == 50){ // rutera
                                    $inscripcion110 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,110,$fase);
                                    if ($inscripcion110[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                    }
                                    $inscripcion111 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,111,$fase);
                                    if ($inscripcion111[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                    }
                                    $inscripcion112 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,112,$fase);
                                    if ($inscripcion112[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                    }
                                    $inscripcion113 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,113,$fase);
                                    if ($inscripcion113[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                    }
                                }
                                if ($prueba == 110 or $prueba == 111 or $prueba == 112 or $prueba == 113){ // montañera
                                    $inscripcion47 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,47,$fase);
                                    if ($inscripcion47[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                    }
                                    $inscripcion48 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,48,$fase);
                                    if ($inscripcion48[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                    }
                                    $inscripcion49 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,49,$fase);
                                    if ($inscripcion49[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                    }
                                    $inscripcion50 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,50,$fase);
                                    if ($inscripcion50[0]){
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /*
            if ($cantidadIncritosGestionPruebaFase < $tipoDisciplinaPrueba["cupo"]){
                if($inscripcionEstudianteGestionPruebaFase[0]){
                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en: '.$tipoDisciplinaPrueba["prueba"].')');
                } else {
                    if(count($inscripcionEstudianteEntity) > 0){
                        if ($nivel == 12){
                            //**
                            // ** Validación de nivel primario
                            // **
                            if($tipoDisciplinaPrueba["tipoDisciplina"] == 'Individual'){
                                if($inscripcionEstudianteEntity[0]["cant_dis_indi"] > 0){
                                    switch ($tipoDisciplinaPrueba["idDisciplina"]) {
                                        case 18: //natacion
                                            if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                            } else {
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                            }
                                            break;
                                        default : //otros
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                            break;
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            } else {
                                if($inscripcionEstudianteEntity[0]["cant_dis_conj"] > 0){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina conjunto)');
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            }
                        } else {
                            //**
                            // ** Validación de nivel secundario
                            //*
                            if($tipoDisciplinaPrueba["tipoDisciplina"] == 'Individual'){
                                if($inscripcionEstudianteEntity[0]["cant_dis_indi"] > 0){
                                    if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                        switch ($tipoDisciplinaPrueba["idDisciplina"]) {
                                            case 2: //atletismo
                                                if($tipoDisciplinaPrueba["tipoPrueba"] == 'Individual'){
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_indi_atle"] >= 2){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas individuales)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                } else {
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_conj_atle"] > 0){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas conjunto)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                }
                                                break;
                                            case 6: //ciclismo
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 8: //natacion
                                                if($tipoDisciplinaPrueba["tipoPrueba"] == 'Individual'){
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_indi_nat"] >= 3){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas individuales)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                } else {
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_conj_nat"] > 0){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas de equipo)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                }
                                                break;
                                            case 9: //raquetbol
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 10: //raqueta fronton
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 11: //tenis de mesa
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            default : //otros
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                                break;
                                        }
                                    } else {
                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas individuales)');
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            } else {
                                if($inscripcionEstudianteEntity[0]["cant_dis_conj"] > 0){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina conjunto)');
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            }
                        }
                    } else {
                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                    }
                }
            } else {
                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' ('.$cantidadIncritosGestionPruebaFase.' estudiantes registrados, no es posible incluir mas)');
            }
            */
        } else {
            $msg = array('0'=>false, '1'=>$estudiante["nombre"]." (Edad y/o gestión del estudiante no válida para inscripción)");
        }
        return $msg;
    }


    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,$prueba,$fase){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
            ->select('eij.id as inscripcionId, pt.id as pruebaId, dt.id as disciplinaId, e.paterno, e.materno, e.nombre')
            ->leftJoin('SieAppWebBundle:PruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->leftJoin('SieAppWebBundle:DisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->setParameter('codInscripcion', $inscripcionEstudiante)
            ->setParameter('codPrueba', $prueba)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $verInsPru = $queryVerInsPru->getArrayResult();
        if (count($verInsPru) > 0){
            return array('0'=>true, '1'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"]);
        } else {
            return array('0'=>false, '1'=>'');
        }
    }

    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaInscripcionEstudianteGestionDisciplinaFase($inscripcionEstudiante,$gestion,$prueba,$fase){
        $em = $this->getDoctrine()->getManager();
        $objEntidad = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $prueba));
        $disciplina = $objEntidad->getDisciplinaTipo()->getId();
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
            ->select('eij.id as inscripcionId, pt.id as pruebaId, dt.id as disciplinaId, e.paterno, e.materno, e.nombre')
            ->leftJoin('SieAppWebBundle:PruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->leftJoin('SieAppWebBundle:DisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('pt.disciplinaTipo = :codDisciplina')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->setParameter('codInscripcion', $inscripcionEstudiante)
            ->setParameter('codDisciplina', $disciplina)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $verInsPru = $queryVerInsPru->getArrayResult();
        if (count($verInsPru) > 0){
            return array('0'=>true, '1'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"]);
        } else {
            return array('0'=>false, '1'=>'');
        }
    }

    /**
     * get estudiante
     * @param type $inscripcionEstudiante
     * return array validacion
     */
    public function verificaInscripcionEstudiante($inscripcionEstudiante){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select e.paterno, e.materno, e.nombre, e.carnet_identidad, e.complemento, ie.id as sie, ie.institucioneducativa, to_char(e.fecha_nacimiento, 'yyyy') as gestion_nacimiento, to_char(e.fecha_nacimiento, 'MM') as mes_nacimiento, to_char(e.fecha_nacimiento, 'dd') as dia_nacimiento, (cast(to_char(cast('2018-06-30' as date),'yyyyMMdd') as integer) - cast(to_char(e.fecha_nacimiento,'yyyyMMdd') as integer))/10000 as edad, (cast(to_char(now(),'yyyy') as integer) - cast(to_char(e.fecha_nacimiento,'yyyy') as integer)) as edad_gestion
            from estudiante_inscripcion as ei
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join estudiante as e on e.id = ei.estudiante_id
            where ei.id = ". $inscripcionEstudiante ."
        ");
        $query->execute();
        $verInsPru = $query->fetchAll();

        if (count($verInsPru) > 0){
            return array('gestion_nacimiento'=>$verInsPru[0]["gestion_nacimiento"], 'edad'=>$verInsPru[0]["edad"], 'edad_gestion'=>$verInsPru[0]["edad_gestion"],'sie'=>$verInsPru[0]["sie"],'ue'=>$verInsPru[0]["institucioneducativa"],'nombre'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"], 'ci'=>$verInsPru[0]["carnet_identidad"].(($verInsPru[0]["complemento"] == "") ? "-".$verInsPru[0]["complemento"] : ""));
        } else {
            return '';
        }
    }

    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaTipoDisciplinaPrueba($prueba){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:PruebaTipo');
        $queryVerDisPru = $repositoryVerInsPru->createQueryBuilder('pt')
            ->select('pt.id as prueba_id, pt.prueba, dt.id as disciplina_id, dt.disciplina, dt.cantidad, pt.clasificadorTipo as clasificador_tipo')
            ->leftJoin('SieAppWebBundle:DisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->where('pt.id = :codPrueba')
            ->setParameter('codPrueba', $prueba)
            ->getQuery();
        $verInsDisPru = $queryVerDisPru->getArrayResult();
        switch ($verInsDisPru[0]["disciplina_id"]) {
            case 3:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 4:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 5:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 7:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 14:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 15:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 16:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            case 17:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                break;
            default:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                break;
        }

        if($verInsDisPru[0]["clasificador_tipo"]=='G'){
            $tipoPrueba = "Conjunto";
        }
        $idPrueba = $verInsDisPru[0]["prueba_id"];
        $idDisciplina = $verInsDisPru[0]["disciplina_id"];
        $prueba = $verInsDisPru[0]["prueba"];
        $disciplina = $verInsDisPru[0]["disciplina"];
        $cupo = $verInsDisPru[0]["cantidad"];
        return array('tipoDisciplina'=>$tipoDisciplina,'tipoPrueba'=>$tipoPrueba,'idPrueba'=>$idPrueba,'prueba'=>$prueba,'idDisciplina'=>$idDisciplina,'disciplina'=>$disciplina,'cupo'=>$cupo);
    }

    public function listaEstudiantesInscritosUeDescargaPdfAction($usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa as nombre from institucioneducativa as ie where ie.id = ".$this->session->get('userName')."
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        if (count($objEntidad) > 0){
        } else {
            $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa as nombre from usuario_rol as ur
                inner join usuario as u on u.id = ur.usuario_id
                inner join persona as p on p.id = u.persona_id
                inner join maestro_inscripcion as mi on mi.persona_id = p.id
                inner join institucioneducativa as ie on ie.id = mi.institucioneducativa_id
                where ur.usuario_id = ".$usuario." and ur.rol_tipo_id = 9 and mi.gestion_tipo_id = ".$gestionActual."
            ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
        }

        $sie = $objEntidad[0]['id'];

        $arch = $sie.'_'.$gestionActual.'_JUEGOS_FPREVIA_UE_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_previa_v2_rcm.rptdesign&__format=pdf&codue='.$sie.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function listaInscritosUeDescargaPdfAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $sie = $request->get('sie');

        $arch = 'JUEGOS_FPREVIA_'.$sie.'_'.$gestionActual.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_previa_v2_rcm.rptdesign&__format=pdf&codue='.$sie.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function eliminaInscripcionAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        $respuesta = array();

        $borrar = true;
        $inscripcion = $request->get('inscripcion');
        $sie = $request->get('sie');

        $em->getConnection()->beginTransaction();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('id'=>$inscripcion));
            if ($entityDatos) {
                $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $fase = $entityDatos->getFaseTipo()->getId();
                $estudiante = $entityDatos->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getNombre();
                $estudianteInscripcion = $entityDatos->getEstudianteInscripcion()->getId();
                $pruebaId = $entityDatos->getPruebaTipo()->getId();

                $query = $em->getConnection()->prepare("select * from fase_tipo where id = 1");
                $query->execute();
                $faseTipoEntity = $query->fetchAll();

                if ($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                    $borrar = false;
                }

                if ($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                    $borrar = false;
                }

                if($borrar){
                    $entityDatosFaseSuperior = $this->verificaInscripcionEstudianteGestionDisciplinaFase($estudianteInscripcion,$gestionActual,$pruebaId,($fase+1));
                    if (!$entityDatosFaseSuperior[0]){
                        $em->remove($entityDatos);
                        $em->flush();
                        $em->getConnection()->commit();
                        $respuesta = array('0'=>true);
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante ".$estudiante." eliminado"));
                    } else {
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Estudiante ".$estudiante." no puede ser eliminado debido a que se encuentra clasificado en la fase ".$fase));
                    }
                } else {
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las listas estan cerradas, no puede eliminar estudiantes"));
                }
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index', array('sieBusqueda'=>$sie), 307);
            } else {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se puede eliminar la inscripcion del estudiante"));
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se puede eliminar la inscripcion del estudiante"));
            return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
        }
    }

    public function eliminaPruebaInscripcionAction($inscripcion) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();

        $borrar = true;

        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('id'=>base64_decode($inscripcion)));
            if ($entityDatos) {
                $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $fase = $entityDatos->getFaseTipo()->getId();
                $estudiante = $entityDatos->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getNombre();
                $estudianteInscripcion = $entityDatos->getEstudianteInscripcion()->getId();
                $pruebaId = $entityDatos->getPruebaTipo()->getId();

                $query = $em->getConnection()->prepare("select * from fase_tipo where id = 1");
                $query->execute();
                $faseTipoEntity = $query->fetchAll();

                if ($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                    $borrar = false;
                }

                if ($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                    $borrar = false;
                }

                if($borrar){
                    $entityDatosFaseSuperior = $this->verificaInscripcionEstudianteGestionDisciplinaFase($estudianteInscripcion,$gestionActual,$pruebaId,($fase+1));
                    if (!$entityDatosFaseSuperior[0]){
                        $em->remove($entityDatos);
                        $em->flush();
                        $em->getConnection()->commit();
                        $respuesta = array('0'=>true);
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante ".$estudiante." eliminado"));
                    } else {
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Estudiante ".$estudiante." no puede ser eliminado debido a que se encuentra clasificado en la fase ".$fase));
                    }
                } else {
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las listas estan cerradas, no puede eliminar estudiantes"));
                }
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $respuesta = array();
        }
        $response = new JsonResponse();
        return $response->setData(array('aregistro' => $respuesta));
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function validaCantidadGestionPruebaFaseUe($gestion,$prueba,$fase,$sie) {
        $em = $this->getDoctrine()->getManager();
        if ($fase == 1){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                where ie.id = ". $sie ." and pt.id = ". $prueba ." and eij.gestion_tipo_id = ". $gestion ." and eij.fase_tipo_id = ". $fase ."
            ");
        }
        if ($fase == 2){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                where eij.fase_tipo_id = 5
            ");
        }
        $query->execute();
        $inscripcionEstudianteEntity = $query->fetchAll();
        //print_r($inscripcionEstudianteEntity);
        if (count($inscripcionEstudianteEntity) > 0){
            return $inscripcionEstudianteEntity[0]["cantidad"];
        } else {
            return 0;
        }
    }
}
