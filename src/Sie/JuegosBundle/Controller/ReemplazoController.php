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

use Sie\JuegosBundle\Controller\EstudianteInscripcionJuegosController as estudianteInscripcionJuegosController;
use Sie\JuegosBundle\Controller\ReglaController as reglaController;
use Sie\JuegosBundle\Controller\ClasificacionController as clasificacionController;
use Sie\JuegosBundle\Controller\RegisterPersonStudentController as registerPersonStudentController;

use Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;

class ReemplazoController extends Controller {

    public $session;

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

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':Reemplazo:index.html.twig', array(
            'titulo' => 'Reemplazo',
            'subtitulo' => 'deportistas',
            'form' => $this->creaFormularioBusqueda('sie_juegos_reemplazo_guarda','',null,null,null,null)->createView(),
        ));
    }

    private function creaFormularioBusqueda($routing, $rude1, $prueba, $fase, $rude2) {
        $form = $this->createFormBuilder()
                ->add('rude1', 'text', array('label' => 'Rude (Lesionado)', 'attr' => array('value' => $rude1, 'placeholder' => 'Ingresar rude del lesionado', 'class' => 'form-control', 'pattern' => '[0-9a-z\sñÑ]{6,8}', 'maxlength' => '20', 'autocomplete' => 'on', 'onBlur' => 'listar_prueba(this.value)', 'style' => 'text-transform:uppercase')))
                ->add('prueba', 'choice', array('label' => 'Prueba', 'empty_value' => 'Seleccione prueba', 'choices' => $prueba, 'data' => $prueba, 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_fase(this.value)', 'required' => true)))
                ->add('fase', 'choice', array('label' => 'Fase', 'empty_value' => 'Seleccione fase', 'choices' => $fase, 'data' => $fase, 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'habilitar_rude(this.value)', 'required' => true)))
                ->add('rude2', 'text', array('label' => 'Rude (Lesionado)', 'attr' => array('value' => $rude2, 'placeholder' => 'Ingresar rude del reemplazante', 'class' => 'form-control', 'disabled' => 'disabled', 'pattern' => '[0-9a-z\sñÑ]{6,8}', 'maxlength' => '20', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary col-md-12', 'onclick'=>'confirma()')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista las pruebas con el cual cuenta el estudiante
    // PARAMETROS: por POST  rude
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function busquedaPruebaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');
        $response = new JsonResponse();
        $msg_incorrecto = '';
        $estudianteInscripcionId = 0;
        $historial = array();
       
        try {
            $pruebas = $this->getPruebaRudeGestion($_POST['rude'],$gestionActual);
            if (count($pruebas)>0){
                $msg_incorrecto = '';
                $estudianteInscripcionId = $pruebas[0]['estudiante_inscripcion_id'];
            } else {
                $msg_incorrecto = 'el estudiante no cuenta con pruebas de conjunto o equipo validas para reemplazar';
            }
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListInscriptionStudentPerGestion($estudianteInscripcionId, $gestionActual);
            if(count($object)>0){
                foreach ($object as $registro) {
                    $deportista = $registro['nombre']." ".$registro['paterno']." ".$registro['materno'];                    
                    $historial[$deportista][] = array("equipo"=>$registro['equipoNombre'],"disciplina"=>$registro['disciplina'],"prueba"=>$registro['prueba'],"genero"=>$registro['genero'],"posicion"=>$registro['posicion'],"fase"=>($registro['fase'])-1);
                }
            }
            return $response->setData(array(
                'pruebas' => $pruebas, 'msg_incorrecto' => $msg_incorrecto, 'historial' => $historial,
            ));

        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $response->setData(array('msg_incorrecto'=>'inconsistencia, intente nuevamente'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las pruebas con las que cuenta el estudiante
    // PARAMETROS: rude, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getPruebaRudeGestion($rude, $gestionId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct pt.id, pt.prueba, ei.id as estudiante_inscripcion_id 
            from jdp_estudiante_inscripcion_juegos as eij
            inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join jdp_prueba_tipo as pt on pt.id = eij.prueba_tipo_id
            inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            where eij.gestion_tipo_id = ".$gestionId." and e.codigo_rude = '".$rude."' and eij.esactivo = true and (dt.disciplina_participacion_tipo_id = 1 or pt.id in (89,90,200,201,174,175))
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista la ultima fase con el cual cuenta el estudiante en la prueba seleccionada
    // PARAMETROS: por POST  rude, pruebaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function busquedaFaseAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');
        $response = new JsonResponse();
        $msg_incorrecto = '';
       
        try {
            $fases = $this->getFaseRudePruebaGestion($_POST['rude'], $_POST['prueba'], $gestionActual);
            if (count($fases)>0){
                $msg_incorrecto = '';
            } else {
                $msg_incorrecto = 'el equipo del estudiante no clasifico de fase previa';
            }
            return $response->setData(array(
                'fases' => $fases, 'msg_incorrecto' => $msg_incorrecto,
            ));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $response->setData(array('msg_incorrecto'=>'inconsistencia, intente nuevamente'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista la ultima fase con el cual cuenta el estudiante en la prueba seleccionada
    // PARAMETROS: rude, gestionId, pruebaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getFaseRudePruebaGestion($rude, $prueba, $gestionId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct ft.id, ft.fase
            from jdp_equipo_estudiante_inscripcion_juegos as eeij 
            inner join jdp_estudiante_inscripcion_juegos as eij on eij.id = eeij.estudiante_inscripcion_juegos_id
            inner join jdp_fase_tipo as ft on ft.id = eij.fase_tipo_id
            where eij.fase_tipo_id != 1 and eij.gestion_tipo_id = ".$gestionId." and eij.prueba_tipo_id = ".$prueba." and eij.esactivo = true and eeij.equipo_id in (
                select distinct eeij1.equipo_id
                from jdp_estudiante_inscripcion_juegos as eij1
                inner join estudiante_inscripcion as ei1 on ei1.id = eij1.estudiante_inscripcion_id
                inner join estudiante as e1 on e1.id = ei1.estudiante_id
                inner join jdp_equipo_estudiante_inscripcion_juegos as eeij1 on eeij1.estudiante_inscripcion_juegos_id = eij1.id
                where eij1.gestion_tipo_id = ".$gestionId." and e1.codigo_rude = '".$rude."' and eij1.prueba_tipo_id = ".$prueba." and eij1.esactivo = true
            )
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla la posicion el cual cuenta el equipo del estudiante se encuentra
    // PARAMETROS: equipoId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getPosicionGestionEquipoPruebaFase($equipoId, $prueba, $gestionId, $faseId) {        
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct ft.id, ft.fase, eij.posicion
            from jdp_equipo_estudiante_inscripcion_juegos as eeij 
            inner join jdp_estudiante_inscripcion_juegos as eij on eij.id = eeij.estudiante_inscripcion_juegos_id
            inner join jdp_fase_tipo as ft on ft.id = eij.fase_tipo_id
            where eij.fase_tipo_id = ".$faseId." and eij.gestion_tipo_id = ".$gestionId." and eij.prueba_tipo_id = ".$prueba." and eeij.equipo_id = " . $equipoId . " and eij.esactivo = true 
        ");        
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla el ID del area geográfica al cual perstenece el equipo del estudiante 
    // PARAMETROS: equipoId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getLugarFaseInscripcion($estudianteInscripcionJuegosId, $faseId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct case ".$faseId." when 1 then ie.id when 2 then lt.id when 3 then jg.circunscripcion_tipo_id when 4 then lt5.id else 0 end as lugar_id
            from jdp_estudiante_inscripcion_juegos as eij
            inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
            inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
            inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
            inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
            inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
            where eij.id = ".$estudianteInscripcionJuegosId." 
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que busca la ultima inscripcion del estudiante en la prueba y fase seleccionada
    // PARAMETROS: rude, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getInscripcionJuegosRudeGestionPruebaFase($rude, $pruebaId, $gestionId, $faseId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select eij.id, coalesce(eeij.equipo_id, 0) as equipo_id, ei.id as estudiante_inscripcion_id
            from jdp_estudiante_inscripcion_juegos as eij
            inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join jdp_prueba_tipo as pt on pt.id = eij.prueba_tipo_id
            inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            left join jdp_equipo_estudiante_inscripcion_juegos as eeij on eeij.estudiante_inscripcion_juegos_id = eij.id
            where e.codigo_rude = '".$rude."' and eij.fase_tipo_id = ".$faseId." and eij.gestion_tipo_id = ".$gestionId." and eij.prueba_tipo_id = ".$pruebaId." and eij.esactivo = true 
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Action que guarda y valida el reemplazo de estudiantes deportistas 
    // PARAMETROS: por POST  $rude1, faseId, pruebaId, $rude2
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function guardaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_usuario_lugar = $this->session->get('roluserlugarid');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "Session concluida, ingrese nuevamente al sistema",
            ));
        }

        $msg_correcto = "";
        $msg_incorrecto = "";
        $response = new JsonResponse();

        $form = $request->get('form');
        if(!$form){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "Inconsistecia en el envio del formulario, intentelo nuevamente",
            ));
        }

        $estudianteInscripcionJuegosId = 0;
        $equipoId = 0;
        $registroNuevo = false;
        $rude1 = $form['rude1'];
        $pruebaId = $form['prueba'];
        $faseId = $form['fase'];
        $rude2 = $form['rude2'];
        $gestionId = $gestionActual;
        $historial = array();

        $inscripcionJuegosRude = $this->getInscripcionJuegosRudeGestionPruebaFase($rude1, $pruebaId, $gestionId, $faseId);
        if(count($inscripcionJuegosRude)>0){
            $estudianteInscripcionJuegosId = $inscripcionJuegosRude[0]['id'];
            $equipoId = $inscripcionJuegosRude[0]['equipo_id'];
        } else {
            $inscripcionJuegosRude = $this->getInscripcionJuegosRudeGestionPruebaFase($rude1, $pruebaId, $gestionId, ($faseId-1));
            if(count($inscripcionJuegosRude)>0){
                $registroNuevo = true;
                $estudianteInscripcionJuegosId = $inscripcionJuegosRude[0]['id'];
                $equipoId = $inscripcionJuegosRude[0]['equipo_id'];
            } else {
                return $response->setData(array(
                    'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "No se encontro la inscripcion del estudiante lesionado, intentelo nuevamente",
                ));
            }
        }

        $estudianteInscripcionJuegosEntity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id' => $estudianteInscripcionJuegosId));                        
                                                
        $estudianteInscripcionId = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getId();
        $institucionEducativaId = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivelId = $estudianteInscripcionJuegosEntity->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                
        $estudianteInscripcionRude = $this->getEstudianteInscripcionRude($rude2, $gestionId, $institucionEducativaId);
        if(count($estudianteInscripcionRude)==0){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El estudiante con código rude ".$rude2." y u.e. ".$institucionEducativaId." no existe, intentelo nuevamente",
            ));
        }
        $estudianteInscripcionIdRude2 =  $estudianteInscripcionRude[0]['id'];

        $object = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListInscriptionStudentPerGestion($estudianteInscripcionIdRude2, $gestionActual);
        if(count($object)>0){
            foreach ($object as $registro) {
                $deportista = $registro['nombre']." ".$registro['paterno']." ".$registro['materno'];                    
                $historial[$deportista][] = array("equipo"=>$registro['equipoNombre'],"disciplina"=>$registro['disciplina'],"prueba"=>$registro['prueba'],"genero"=>$registro['genero'],"posicion"=>$registro['posicion'],"fase"=>($registro['fase'])-1);
            }
        }

        $clasificacionController = new clasificacionController();
        $clasificacionController->setContainer($this->container);

        $inscripcionActivo = $clasificacionController->verificaInscripcionActivoEstudianteGestionPruebaFase($estudianteInscripcionId,$gestionId,$pruebaId,$faseId);
        if(!$inscripcionActivo[0]){
            $inscripcionActivo = $clasificacionController->verificaInscripcionActivoEstudianteGestionPruebaFase($estudianteInscripcionId,$gestionId,$pruebaId,($faseId-1));
            if(!$inscripcionActivo[0]){
                return $response->setData(array(
                    'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "No se encontro la inscripcion activa del estudiante lesionado en juegos, ya se reemplazo, intentelo nuevamente", 'historial' => $historial,
                ));
            }
        }
        // $entidadUsuario = $clasificacionController->buscaEntidadFase($faseId, $id_usuario);
        // dump($estudianteInscripcionJuegosEntity->getInstitucioneducativaCurso);die;
        // $entidadUsuarioId =  $entidadUsuario[0]['id'];

        $lugarInscripcionFase = $this->getLugarFaseInscripcion($estudianteInscripcionJuegosEntity->getId(), $faseId);
        if(count($lugarInscripcionFase) == 0){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El equipo del estudiante lesionado no cuenta con area geografica, intentelo nuevamente", 'historial' => $historial,
            ));
        } else {
            if($lugarInscripcionFase[0]['lugar_id'] == 0){
                return $response->setData(array(
                    'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El equipo del estudiante lesionado no cuenta con area geografica, intentelo nuevamente", 'historial' => $historial,
                ));
            }
        }
        $entidadUsuarioId =  $lugarInscripcionFase[0]['lugar_id'];

        $estadoEstudianteInscripcion = $clasificacionController->verificaEstadoInscripcionEstudiante($estudianteInscripcionId);

        $reglaController = new reglaController();
        $reglaController->setContainer($this->container);

        $registerPersonStudentController = new registerPersonStudentController();
        $registerPersonStudentController->setContainer($this->container);
       
        $posicionEquipo = $this->getPosicionGestionEquipoPruebaFase($equipoId, $pruebaId, $gestionId, $faseId);
        if(count($posicionEquipo)==0){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El equipo del estudiante lesionado no se encuentra clasificado en la fase selecionada, intentelo nuevamente", 'historial' => $historial,
            ));
        }
        $posicion = $posicionEquipo[0]['posicion'];
       
        $validaGeneroNivelRude = $this->getValidaGeneroNivelRude($estudianteInscripcionId, $estudianteInscripcionIdRude2);
        if(count($validaGeneroNivelRude)==0){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "el estudiante ".$rude2." no cuenta con las caracteristicas de ".$rude1.", intentelo nuevamente", 'historial' => $historial,
            ));
        }

        if(!$validaGeneroNivelRude[0]['val_genero']){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El estudiante ".$rude2." no cuenta con el mismo género del estudiante ".$rude1.", intentelo nuevamente", 'historial' => $historial,
            ));
        }

        if(!$validaGeneroNivelRude[0]['val_nivel']){
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El estudiante ".$rude2." no cuenta con el mismo nivel de educacion del estudiante ".$rude1.", intentelo nuevamente", 'historial' => $historial,
            ));
        }

        $estadoEstudianteInscripcionRude2 = $clasificacionController->verificaEstadoInscripcionEstudiante($estudianteInscripcionIdRude2);
       
        //dump($estudianteInscripcionIdRude2);dump($gestionId);dump($pruebaId);dump($faseId);dump($equipoId);dump($entidadUsuarioId);dump($posicion);dump($estudianteInscripcionId);    
        if($estadoEstudianteInscripcion and $estadoEstudianteInscripcionRude2){
            $msg = $reglaController->valEstudianteReemplazoJuegos($estudianteInscripcionIdRude2, $gestionId, $pruebaId, $faseId, $equipoId, $entidadUsuarioId, $posicion, $estudianteInscripcionId);
            //dump($msg);die;
            if($msg[0]){
                $em->getConnection()->beginTransaction();
                try {
                    if ($msg[1] == "Update"){
                        $estudianteInscripcionReemplazoEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionIdRude2));                        
                        $estudianteInscripcionJuegosEntity->setEstudianteInscripcion($estudianteInscripcionReemplazoEntity);
                        $estudianteInscripcionJuegosEntity->setObs('REEMPLAZO DEL ESTUDIANTE '.$rude1.' POR '.$rude2.'');
                        $estudianteInscripcionJuegosEntity->setFechaModificacion($fechaActual);
                        $em->persist($estudianteInscripcionJuegosEntity);

                        $inscripcionJuegosRude1Anterior = $this->getInscripcionJuegosRudeGestionPruebaFase($rude1, $pruebaId, $gestionId, ($faseId-1));
                        if(count($inscripcionJuegosRude1Anterior) > 0){
                            $estudianteInscripcionJuegosIdAnterior = $inscripcionJuegosRude1Anterior[0]['id'];
                        } else {
                            return $response->setData(array(
                                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El estudiante ".$rude1." no cuenta con inscripcion en la fase ".($faseId-1).", intentelo nuevamente", 'historial' => $historial,
                            ));
                        }

                        $estudianteInscripcionJuegosEntityAnterior = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id' => $estudianteInscripcionJuegosIdAnterior));                        
                        $estudianteInscripcionJuegosEntityAnterior->setEsactivo(false);
                        $estudianteInscripcionJuegosEntityAnterior->setFechaModificacion($fechaActual);
                        $em->persist($estudianteInscripcionJuegosEntityAnterior);

                        $msg[1] = 'REEMPLAZO DEL ESTUDIANTE '.$rude1.' POR '.$rude2.'';

                        $em->flush();
                    } else {
                        $entrenadorEntity = $registerPersonStudentController->getEquipoCouch($estudianteInscripcionJuegosId,$faseId);
                        $entrenadorSave = false;
                        if(count($entrenadorEntity) > 0){
                            $entrenadorSave = true;
                            $personaId = $entrenadorEntity["personaId"];
                        } else {
                            $entrenadorEntity = $registerPersonStudentController->getEquipoCouch($estudianteInscripcionJuegosId,($faseId-1));
                            if(count($entrenadorEntity) > 0){
                                $entrenadorSave = true;
                                $personaId = $entrenadorEntity["personaId"];
                            } else {
                                $entrenadorSave = false;
                            }
                        }

                        $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
                        $gestionEntity = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
                        $faseEntity = $em->getRepository('SieAppWebBundle:JdpFaseTipo')->findOneBy(array('id' => $faseId));
                        $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionIdRude2));
                        $estudianteNombre = $estudianteInscripcionEntity->getEstudiante()->getNombre();
                        $estudiantePaterno = $estudianteInscripcionEntity->getEstudiante()->getPaterno();
                        $estudianteMaterno = $estudianteInscripcionEntity->getEstudiante()->getMaterno();
                        $estudianteNombreApellido = $estudianteNombre . ' ' . $estudiantePaterno . ' ' . $estudianteMaterno;
                        $estudianteInscripcionJuegos = new JdpEstudianteInscripcionJuegos();
                        $estudianteInscripcionJuegos->setEstudianteInscripcion($estudianteInscripcionEntity);
                        $estudianteInscripcionJuegos->setPruebaTipo($pruebaEntity);
                        $estudianteInscripcionJuegos->setGestionTipo($gestionEntity);
                        $estudianteInscripcionJuegos->setFaseTipo($faseEntity);
                        $estudianteInscripcionJuegos->setPosicion($posicion);
                        $estudianteInscripcionJuegos->setFechaRegistro($fechaActual);
                        $estudianteInscripcionJuegos->setFechaModificacion($fechaActual);
                        $estudianteInscripcionJuegos->setUsuarioId($id_usuario);
                        $em->persist($estudianteInscripcionJuegos);

                        if($equipoId > 0){
                            $equipoEstudianteInscripcionJuegos = new JdpEquipoEstudianteInscripcionJuegos();
                            $equipoEstudianteInscripcionJuegos->setEstudianteInscripcionJuegos($estudianteInscripcionJuegos);
                            $equipoEstudianteInscripcionJuegos->setEquipoId($equipoId);
                            $equipoEstudianteInscripcionJuegos->setEquipoNombre('Equipo'.$equipoId);
                            $em->persist($equipoEstudianteInscripcionJuegos);
                        }

                        if($entrenadorSave){
                            if($comisionId == 0){
                                if($nivel== 12){
                                    $comisionId = 139;
                                } else {
                                    $comisionId = 140;
                                }
                            }
                            $personaInscripcionJuegos = new JdpPersonaInscripcionJuegos();
                            $personaInscripcionJuegos->setEstudianteInscripcionJuegos($estudianteInscripcionJuegos);
                            $personaInscripcionJuegos->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personaId));
                            $personaInscripcionJuegos->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($comisionId));
                            $em->persist($personaInscripcionJuegos);
                        }

                        $estudianteInscripcionJuegosEntity->setEsactivo(false);
                        $em->persist($estudianteInscripcionJuegosEntity);

                        $em->flush();
                        $estudianteInscripcionJuegosId = $estudianteInscripcionJuegos->getId();
                        
                        //array_push($ainscritos,array('id'=>($estudianteInscripcionJuegosId), 'nombre'=>$estudianteNombreApellido, 'posicion'=> $posicion));
                    }
                    $em->getConnection()->commit();

                    $object = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListInscriptionStudentPerGestion($estudianteInscripcionIdRude2, $gestionActual);
                    if(count($object)>0){
                        foreach ($object as $registro) {
                            $deportista = $registro['nombre']." ".$registro['paterno']." ".$registro['materno'];                    
                            $historial[$deportista][] = array("equipo"=>$registro['equipoNombre'],"disciplina"=>$registro['disciplina'],"prueba"=>$registro['prueba'],"genero"=>$registro['genero'],"posicion"=>$registro['posicion'],"fase"=>($registro['fase'])-1);
                        }
                    }

                    return $response->setData(array(
                        'msg_correcto' => $msg[1], 'msg_incorrecto' => $msg_incorrecto, 'historial' => $historial,
                    ));
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    return $response->setData(array(
                        'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "Error al registrar, intente nuevamente",  'historial' => $historial,
                    ));
                }
            } else {
                return $response->setData(array(
                    'msg_correcto' => $msg_correcto, 'msg_incorrecto' => $msg[1],  'historial' => $historial,
                ));
            }
        } else {
            return $response->setData(array(
                'msg_correcto' => $msg_correcto, 'msg_incorrecto' => "El estudiante ya no se encuentra con el estado efectivo, favor de verificar la información actual del estudiante", 'historial' => $historial,
            ));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla la inscripcion del estudiante
    // PARAMETROS: rude, gestionId, institucionEducativaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getEstudianteInscripcionRude($rude, $gestionId, $institucionEducativaId) {        
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select ei.id, iec.nivel_tipo_id, e.genero_tipo_id
            from estudiante as e
            inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            where e.codigo_rude = '".$rude."' and iec.gestion_tipo_id = ".$gestionId." and iec.institucioneducativa_id = ".$institucionEducativaId." and ei.estadomatricula_tipo_id in (4)
        ");        
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida si ambos estudiantes son compatibles
    // PARAMETROS: rude, gestionId, institucionEducativaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getValidaGeneroNivelRude($estudianteInscripcionIdRude1, $estudianteInscripcionIdRude2) {        
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select case when e1.genero_tipo_id = e2.genero_tipo_id then true else false end as val_genero
            , case when e1.nivel_tipo_id = e2.nivel_tipo_id then true else false end as val_nivel
            from (
            select 1 as id, e.genero_tipo_id, iec.nivel_tipo_id
            from estudiante as e
            inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            where ei.id = ".$estudianteInscripcionIdRude1.") as e1
            inner join (
            select 1 as id, e.genero_tipo_id, iec.nivel_tipo_id
            from estudiante as e
            inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            where ei.id = ".$estudianteInscripcionIdRude2.") as e2 on e1.id = e2.id
        ");        
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }


    

}
