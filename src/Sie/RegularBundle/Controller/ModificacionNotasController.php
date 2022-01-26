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
class ModificacionNotasController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            // generar los titulos para los diferentes sistemas
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');

            ////////////////////////////////////////////////////
            $rolUsuario = $this->session->get('roluser');
            if($rolUsuario != 8){
                echo "La pagina no existe o no tiene permisos para ingresar a esta pagina";die;
            }

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
                if(!$estudiante){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante con el código ingresado no existe!');
                    return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                // Verificamos si el estudiante tiene inscripcion en la unidad educativa y gestion seleccionada
                $inscripcion = $em->createQuery(
                    'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                    LEFT JOIN ei.estudiante e
                    LEFT JOIN ei.institucioneducativaCurso iec
                    LEFT JOIN  iec.gestionTipo gt
                    WHERE e.codigoRude = :rude
                    AND iec.institucioneducativa = :idInstitucion
                    AND gt.id = :gestion')
                    ->setParameter('rude',$form['codigoRude'])
                    ->setParameter('idInstitucion',$form['idInstitucion'])
                    ->setParameter('gestion',$form['gestion'])
                    ->getResult();
                if(!$inscripcion){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con inscripcion en la unidad educativa y la gestion seleccionada');
                    return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                $this->session->set('idEstudiante', $estudiante->getId());
                $this->session->set('idGestion',$form['gestion']);
                $this->session->set('idInstitucion',$form['idInstitucion']);
                $this->session->set('idInscripcion',$inscripcion[0]->getId());
                $idEstudiante = $estudiante->getId();
                $gestion = $form['gestion'];
                $idInstitucion = $form['idInstitucion'];
                $idInscripcion = $inscripcion[0]->getId();
            } else {
                /*
                 * Verificamos si se tiene que mostrar el formulario de busqueda
                 */
                if($op == 'search'){
                    return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * Verificar si existe la session de la persona y gestion
                 */
                if($this->session->get('idEstudiante')){
                    $idEstudiante = $this->session->get('idEstudiante');
                    $gestion = $this->session->get('idGestion');
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
                    $idInstitucion = $this->session->get('idInstitucion');
                    $idInscripcion = $this->session->get('idInscripcion');

                }else{
                    return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);

            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());

            // Obtenemos las calificaciones
            $operativo = $this->get('funciones')->obtenerOperativo($inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId(),$inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId());
            $notas = $this->get('notas')->regular($idInscripcion,$operativo);


            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:ModificacionNotas:index.html.twig', array(
                        'gestion' => $gestion, 
                        'estudiante'=>$estudiante, 
                        'curso'=>$institucionCurso,
                        'notas'=>$notas,
                        'inscripcion'=>$inscripcion
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
            echo 'Error';
        }
    }
    public function editAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            $idEstudiante = $this->session->get('idEstudiante');
            $gestion = $this->session->get('idGestion');
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
            $idInstitucion = $this->session->get('idInstitucion');

            /*
             * Listamos las asignaturas del estudiante
             */
            $query = $em->createQuery(
                    'SELECT ea FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ea.institucioneducativaCursoOferta ieco
                    JOIN ieco.asignaturaTipo at
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    WHERE e.id = :idEstudiante
                    AND iec.gestionTipo = :gestion
                    AND iec.institucioneducativa = :idInstitucion
                    AND ei.estadomatriculaTipo IN (:estadoMatricula)
                    ORDER BY at.id')
                    ->setParameter('idEstudiante', $idEstudiante)
                    ->setParameter('estadoMatricula', array(4,5,10,11))
                    ->setParameter('gestion', $gestion)
                    ->setParameter('idInstitucion',$idInstitucion);

            $asignaturas = $query->getResult();
            
            if(!$asignaturas){
                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, debe realizar la adicion de areas y calificaciones.');
                return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            
            //VAlidamos que la gestion sea del 2014 para arriba
            if($gestion < 2014){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden modificar notas de la gestión 2014 o superior!');
                return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            // Obtenemos el curso en el que esta inscrito el estudiante
            // Obtenemos la cantidad de notas y el tipo de nota (Bimestre, Trimestre, etc)
            // 
            $curso = $em->createQueryBuilder()     
                    ->select('iec.id,npt.notaPeriodoTipo, npt.periodomes')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                    ->leftJoin('SieAppWebBundle:NotaPeriodoTipo','npt','WITH','iec.notaPeriodoTipo = npt.id')
                    ->where('e.id = :idEstudiante')
                    ->andWhere('iec.institucioneducativa = :idInstitucion')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->setParameter('idEstudiante',$idEstudiante)
                    ->setParameter('idInstitucion',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->orderBy('e.paterno,e.materno,e.nombre')
                    ->getQuery()
                    ->getResult();            

            // Obtenemos la cantidad de notas que tiene el curso y el tipo de nota
            $tipo_nota = $curso[0]['notaPeriodoTipo'];
            $cantidad_notas = $curso[0]['periodomes'];
            /**
             * Obtenemos el id de inscripcion del estudiante
             */
            foreach ($asignaturas as $a){
                $idInscripcionEstudiante = $a->getEstudianteInscripcion()->getId();
            }
            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y obtenemos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcionEstudiante);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();
            /*
            / Buscamos las notas por asignatura
            */
            $notasArray = array();
            $cont = 0;

            foreach ($asignaturas as $a){
                $cont_notas = 0;
                $notasArray[$cont] = array('idEstudianteAsignatura'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId(),'asignatura'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura(),'codigo'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId());
                for($i=1;$i<=$cantidad_notas;$i++){
                    $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>$i));
                    if($notas){
                        if($idNivel == 11){
                            $notasArray[$cont]['notas'][] = array('id'=>$cont."-".$i,'idEstudianteNota'=>$notas->getId(),'bimestre'=>$i." ".$tipo_nota,'nota'=>$notas->getNotaCualitativa());
                        }else{
                            $notasArray[$cont]['notas'][] = array('id'=>$cont."-".$i,'idEstudianteNota'=>$notas->getId(),'bimestre'=>$i." ".$tipo_nota,'nota'=>$notas->getNotaCuantitativa());
                        }
                        $cont_notas++;
                    }else{
                        $notasArray[$cont]['notas'][] = array('id'=>$cont."-".$i,'idEstudianteNota'=>'ninguno','bimestre'=>$i." ".$tipo_nota,'nota'=>'X');
                        $cont_notas++;
                    }
                }
                if($cont_notas == $cantidad_notas and $idNivel != 11){
                    $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                    if($notaPromedio){
                        $notasArray[$cont]['notas'][] = array('id'=>$cont."-5",'idEstudianteNota'=>$notaPromedio->getId(),'bimestre'=>'Promedio','nota'=>$notaPromedio->getNotaCuantitativa());
                    }else{
                        $notasArray[$cont]['notas'][] = array('id'=>$cont."-5",'idEstudianteNota'=>'ninguno','bimestre'=>'Promedio','nota'=>'X');
                    }
                }
                $cont++;
            }
            /**
             * Verificamos si existen notas por lo menos del primer bimestre o trimestre
             */

            if($cont_notas==0){
              $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no tiene notas registradas en la gestión seleccionada.');
              return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            /*
             * Verificamos si el estudiante tiene notas registradas
             */
            if(count($notasArray[$cont-1]['notas'])<0){
                $this->get('session')->getFlashBag()->add('noSearch', 'No hay notas registradas para la gestion seleccionada');
                return $this->render('SieRegularBundle:ModificacionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
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
            return $this->render('SieRegularBundle:ModificacionNotas:edit.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso
            ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            echo 'Exception: '.$ex->getMessage();
        }
    }
    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        //$gestiones = array($gestion=>$gestion,$gestion-1=>$gestion-1,$gestion-2=>$gestion-2,$gestion-3=>$gestion-3);
        for ($i=$gestion; $i>=$gestion-10 ; $i--) { 
            $gestiones[$i] = $i;
        }

        return $this->createFormBuilder()
                ->setAction($this->generateUrl('modificacionNotas'))
                ->add('codigoRude','text',array('data'=>'','label'=>'Código Rude','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                ->add('idInstitucion','text',array('data'=>'80400014','label'=>'Sie','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'data'=>2015,'attr'=>array('class'=>'form-control')))
                ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                ->getForm();
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
            $this->get('notas')->actualizarEstadoMatricula($request->get('idInscripcion'));
            
            // Cerramos la conexion
            $em->getConnection()->commit();
            /*
             * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
             */
            $this->get('session')->getFlashBag()->add('updateOk', 'Las calificaciones se modificaron correctamente.');
            return $this->redirect($this->generateUrl('modificacionNotas',array('op'=>'result')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'No se pudo realizar la modificacion de calificaciones.');
            return $this->redirect($this->generateUrl('modificacionNotas',array('op'=>'result')));
        }
        
    }
}
