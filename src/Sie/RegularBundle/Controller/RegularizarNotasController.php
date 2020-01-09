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
 * RegularizarNotas controller.
 *
 */
class RegularizarNotasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(){
        try {
            if($this->session->get('roluser') != 8){
                die('Acceso denegado');
            }
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('regularizarNotas_search'))
                    ->add('rude', 'text', array('data'=>'','required' => true,'attr' => array('class' => 'form-control')))
                    ->add('buscar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-info')))
                    ->getForm();

            return $this->render('SieRegularBundle:RegularizarNotas:index.html.twig',array('form'=>$form->createView()));

        } catch (Exception $e) {
            return null;
        }
    }

    public function searchAction(Request $request){
        try {
            if($this->session->get('roluser') != 8){
                die('Acceso denegado');
            }
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $rude = $form['rude'];
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$rude));

            if(!$estudiante){
                $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('regularizarNotas_search'))
                    ->add('rude', 'text', array('data'=>$rude,'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('buscar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-info')))
                    ->getForm();

                return $this->render('SieRegularBundle:RegularizarNotas:index.html.twig',array('form'=>$form->createView()));
            }

            $ins = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $inscripciones = $ins->createQueryBuilder('ei')
                                ->select('ei')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->where('e.codigoRude = :rude')
                                ->orderBy('ei.fechaInscripcion','DESC')
                                ->setParameter('rude',$rude)
                                ->getQuery()
                                ->getResult();

            return $this->render('SieRegularBundle:RegularizarNotas:result.html.twig',array('estudiante'=>$estudiante,'inscripciones'=>$inscripciones));

        } catch (Exception $e) {
            return null;
        }
    }
    /**
     * Lista de estudiantes inscritos en la institucion educativa
     */
    public function showAction(Request $request) {
        try {
            // generar los titulos para los diferentes sistemas
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idInscripcion = $request->get('idInscripcion');

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($inscripcion->getEstudiante()->getId());

            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());

            // Obtenemos las calificaciones
            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getGradoTipo()->getId();

            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
            $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);

            /**
             * Verificamos el tipo de nota
             */
            $tipoNota = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado);
            
            if($tipoNota == 'Bimestre'){
                if($tipoUE){
                    if($tipoUE['id'] == 3 and $nivel == 13){
                        $plantilla = 'modular';
                        $operativo = 4;
                    }else{
                        $plantilla = 'regular';
                    }
                }else{
                    $plantilla = 'regular';
                    $tipoUE = array('id'=>5,'tipo'=>'Humanistica','nivel'=>0);
                }
            }else{
                $plantilla = 'trimestral';
            }

            // Obtenemos las notas en base al operativo consolidado (operativo-1)
            // Si el usuario no es tecnico entonces se le resta un valor del operativo

            if( !in_array($this->session->get('roluser'), array(7,8,10)) ){
                $operativo = $operativo - 1;
            }

            // VERIFICAMOS SI LAS NOTAS SON POSTBACHILLERATO
            if($inscripcion->getEstadomatriculaInicioTipo() != null and $inscripcion->getEstadomatriculaInicioTipo()->getId() == 29){
                $plantilla = 'postbachillerato';
                $notas = $this->get('notas')->postbachillerato($idInscripcion);
            }else{
                $notas = $this->get('notas')->regular($idInscripcion,$operativo);
            }

            $vista = 1; // EDITABLE DE ACUERDO AL OPERATIVO O FALTA DE CALIFICACIONES

            // SI EL ROL ES NACIONAL SE ABRE TODAS LAS CALIFICACIONES
            // DE TODOS LOS BIMESTRES O TRIMESTRES
            if ($this->session->get('roluser') == 8) {
                $vista = 2; // Opcion para editar todas las calificaciones
            }

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:RegularizarNotas:show.html.twig', array(
                        'gestion' => $curso->getGestionTipo()->getId(), 
                        'estudiante'=>$estudiante, 
                        'curso'=>$institucionCurso,
                        'notas'=>$notas,
                        'inscripcion'=>$inscripcion,
                        'vista'=>$vista,
                        'plantilla'=>$plantilla,
                        'tipoUE'=>$tipoUE
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
            echo 'Error';
        }
    }

    public function updateAction(Request $request){
        try{
            /*
            * Obtenemos los valores enviados desde el formulario
            */

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            
            $this->session = new Session();
            // Registro de notas
            $this->get('notas')->regularRegistro($request);
            // Actualizacion de estado de matricula
            $tipoUE = $request->get('tipoUE');
            if($tipoUE != 3){
                $this->get('notas')->actualizarEstadoMatricula($request->get('idInscripcion'));
            }
            // Cerramos la conexion
            $em->getConnection()->commit();
            /*
             * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
             */
            $this->get('session')->getFlashBag()->add('updateOk', 'Las calificaciones se regularizarón correctamente.');
            //return $this->redirect($this->generateUrl('regularizarNotas',array('op'=>'result')));
            //return $this->redirect($this->generateUrl('regularizarNotas_show',array('idInscripcion'=>$request->get('idInscripcion'))));
            $response = $this->forward('SieRegularBundle:RegularizarNotas:show',array(
                'idInscripcion'=>$request->get('idInscripcion')
            ));
            return $response;
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'No se pudo realizar la modificacion de calificaciones.');
            return $this->redirect($this->generateUrl('regularizarNotas',array('op'=>'result')));
        }
        
    }

    public function postbachilleratoUpdateAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idInscripcion = $request->get('idInscripcion');
            $idEstudianteNota = $request->get('idEstudianteNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $nota = $request->get('nota');

            for ($i=0; $i < count($idEstudianteNota); $i++) { 
                if($idEstudianteNota[$i] == 'nuevo'){
                    $this->get('notas')->registrarNota($idNotaTipo[$i], $idEstudianteAsignatura[$i],$nota[$i], '');
                }
            }
            
            $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
            // Cerramos la conexion
            $em->getConnection()->commit();
            /*
             * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
             */
            $this->get('session')->getFlashBag()->add('updateOk', 'Las calificaciones se regularizarón correctamente.');
            //return $this->redirect($this->generateUrl('regularizarNotas',array('op'=>'result')));
            //return $this->redirect($this->generateUrl('regularizarNotas_show',array('idInscripcion'=>$request->get('idInscripcion'))));
            $response = $this->forward('SieRegularBundle:RegularizarNotas:show',array(
                'idInscripcion'=>$request->get('idInscripcion')
            ));
            return $response;
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'No se pudo realizar la modificacion de calificaciones.');
            return $this->redirect($this->generateUrl('regularizarNotas',array('op'=>'result')));
        }
    }
}
