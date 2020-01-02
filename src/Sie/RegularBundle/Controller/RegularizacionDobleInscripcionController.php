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
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

/**
 * EstudianteInscripcion controller.
 *
 */
class RegularizacionDobleInscripcionController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }
    public function indexAction(Request $request){

      // if(!isset($this->session->get('userId'))){
      //   return $this->redirect($this->generateUrl('regularizacionDobleInscripcion'));
      // }
      return $this->render($this->session->get('pathSystem') . ':RegularizacionDobleInscripcion:index1.html.twig', array(
                  'form' => $this->formSearch()->createView(),
      ));
    }
    private function formSearch(){
      $aGestion = array();
      $currentYear = date('Y')-1;
      for ($i = 1; $i <= 1; $i++) {
          $aGestion[$currentYear] = $currentYear;
          $currentYear--;
      }

      return $this->createFormBuilder()
            ->setAction($this->generateUrl('regularizacionDobleInscripcion_result'))
            ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
            ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
            ->add('buscar', 'submit', array('label' => 'Buscar'))
            ->getForm();

    }
    public function resultAction(Request $request){
        try {
          $em = $this->getDoctrine()->getManager();
          //get the data form
          $form = $request->get('form');

            $em = $this->getDoctrine()->getManager();
            $rude = $form['codigoRude'];
            $gestion = $form['gestion'];
            // validate rude on validicionProceso
            $objStudent = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array(
              'llave'=>$rude,
              'gestionTipo'=>$gestion,
              'validacionReglaTipo' => 6,
              'esActivo'=>false
            ));

            if(!$objStudent){
              $message = 'Estudiante sin observación o código RUDE no existe...';
              $this->addFlash('error', $message);
              return $this->redirect($this->generateUrl('regularizacionDobleInscripcion'));

            }

            $inscripciones = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $ins = $inscripciones->createQueryBuilder('ei')
                                ->select('ei.id, ie.id as sie, gt.id as gestion, e.codigoRude, e.nombre, e.paterno, e.materno, ie.institucioneducativa, nt.id as nivelId, nt.nivel, grt.grado, pt.paralelo, emt.id as estadomatriculaId, emt.estadomatricula')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                                ->innerJoin('SieAppWebBundle:GradoTipo','grt','with','iec.gradoTipo = grt.id')
                                ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                                ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
                                ->where('e.codigoRude = :rude')
                                ->andWhere('gt.id = :gestion')
                                ->andWhere('ie.institucioneducativaTipo = 1')
                                ->orderBy('ei.fechaInscripcion','ASC')
                                ->setParameter('rude',$rude)
                                ->setParameter('gestion',$gestion)
                                ->getQuery()
                                ->getResult();

            // $arrayInscripciones = array();
            $arrayInscripciones1 = array();
            foreach ($ins as $i) {
                // $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($i['id']);
                // $arrayInscripciones[] = $arrayNotas;
                $operativo = $this->get('funciones')->obtenerOperativo($i['sie'], $i['gestion']);

                $inscripcionActual = $this->get('notas')->regular($i['id'], $operativo);


                $inscripcionActual['estudiante'] = $i['nombre'].' '.$i['paterno'].' '.$i['materno'];
                $inscripcionActual['codigoRude'] = $i['codigoRude'];
                $inscripcionActual['sie'] = $i['sie'];
                $inscripcionActual['institucioneducativa'] = $i['institucioneducativa'];
                $inscripcionActual['nivelname'] = $i['nivel'];
                $inscripcionActual['nivelId'] = $i['nivelId'];
                $inscripcionActual['gradoname'] = $i['grado'];
                $inscripcionActual['paraleloname'] = $i['paralelo'];
                $inscripcionActual['estadomatriculaId'] = $i['estadomatriculaId'];
                $inscripcionActual['estadomatriculaname'] = $i['estadomatricula'];
                $inscripcionActual['cantidadTotal'] = count($inscripcionActual['cuantitativas'])*$inscripcionActual['operativo'];

                // VERIFICAMOS SI LA INSCRIPCION TIENE CALIFICACIONES
                if ($inscripcionActual['operativo'] >= 1 and $inscripcionActual['cantidadRegistrados'] > 0 and $inscripcionActual['cantidadRegistrados'] < $inscripcionActual['cantidadTotal']) {
                  $estadosdisp = [9]; // RETIRO TRASLADO
                }else{
                  $estadosdisp = [6]; // NO INCORPORADO
                }

                $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>$estadosdisp));
                foreach ($estados as $e) {
                  $inscripcionActual['estadosCambiar'][] = array('id'=>$e->getId(), 'estadomatricula'=>$e->getEstadomatricula());
                }

                $arrayInscripciones1[] = $inscripcionActual;
            }

            // dump($arrayInscripciones);
            // dump($arrayInscripciones1);
            // die;

            // $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(6,9)));

            return $this->render('SieRegularBundle:RegularizacionDobleInscripcion:result.html.twig',array(
              'arrayInscripciones'=>$arrayInscripciones1,
              // 'estados'=>$estados,
              'gestion'=>$gestion
            ));

        } catch (Exception $ex) {

        }
    }

    public function guardarAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            // $defaultController = new DefaultCont();
            // $defaultController->setContainer($this->container);

            // $idEstudianteNota = $request->get('idEstudianteNota');
            // $nota = $request->get('nota');
            $nivel = $request->get('arrNivel');
            $rude = $request->get('rude');
            $idInscripcion = $request->get('arrIdInscripcion');
            // for($i=0; $i<count($idInscripcion); $i++){
            //     if(isset($idEstudianteNota[$i])){
            //         $op = $idEstudianteNota[$i];
            //         for($j=0;$j<count($op);$j++){
            //             if($idEstudianteNota[$i][$j] != 'nuevo'){
            //                 $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i][$j]);

            //                 if($nivel[$i] == 11){
            //                   if($updateNota->getNotaCualitativa() != $nota[$i][$j]){

            //                       // Antes
            //                       $arrayRegistro = null;

            //                       $arrayRegistro['id'] = $updateNota->getId();
            //                       $arrayRegistro['nota_tipo_id'] = $updateNota->getNotaTipo()->getId();
            //                       $arrayRegistro['estudiante_asignatura_id'] = $updateNota->getEstudianteAsignatura()->getId();
            //                       $arrayRegistro['nota_cuantitativa'] = $updateNota->getNotaCuantitativa();
            //                       $arrayRegistro['nota_cualitativa'] = $updateNota->getNotaCualitativa();
            //                       $arrayRegistro['recomendacion'] = $updateNota->getRecomendacion();
            //                       $arrayRegistro['usuario_id'] = $updateNota->getUsuarioId();
            //                       $arrayRegistro['fecha_registro'] = $updateNota->getFechaRegistro();
            //                       $arrayRegistro['fecha_modificacion'] = $updateNota->getFechaModificacion();
            //                       $arrayRegistro['obs'] = $updateNota->getObs();

            //                       $antes = json_encode($arrayRegistro);

            //                       // despues
            //                       $arrayRegistro = null;

            //                       $updateNota->setNotaCualitativa(mb_strtoupper($nota[$i][$j],'utf-8'));

            //                       $arrayRegistro['id'] = $updateNota->getId();
            //                       $arrayRegistro['nota_tipo_id'] = $updateNota->getNotaTipo()->getId();
            //                       $arrayRegistro['estudiante_asignatura_id'] = $updateNota->getEstudianteAsignatura()->getId();
            //                       $arrayRegistro['nota_cuantitativa'] = $updateNota->getNotaCuantitativa();
            //                       $arrayRegistro['nota_cualitativa'] = $updateNota->getNotaCualitativa();
            //                       $arrayRegistro['recomendacion'] = $updateNota->getRecomendacion();
            //                       $arrayRegistro['usuario_id'] = $updateNota->getUsuarioId();
            //                       $arrayRegistro['fecha_registro'] = $updateNota->getFechaRegistro();
            //                       $arrayRegistro['fecha_modificacion'] = $updateNota->getFechaModificacion();
            //                       $arrayRegistro['obs'] = $updateNota->getObs();

            //                       $despues = json_encode($arrayRegistro);

            //                       // registro del log
            //                       $resp = $defaultController->setLogTransaccion(
            //                           $updateNota->getId(),
            //                           'estudiante_nota',
            //                           'U',
            //                           json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
            //                           $this->session->get('userId'),
            //                           '',
            //                           $despues,
            //                           $antes,
            //                           'SIGED',
            //                           json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            //                       );
            //                   }
            //                 }else{
            //                   if($updateNota->getNotaCuantitativa() != $nota[$i][$j]){
            //                       // Antes
            //                       $arrayRegistro = null;

            //                       $arrayRegistro['id'] = $updateNota->getId();
            //                       $arrayRegistro['nota_tipo_id'] = $updateNota->getNotaTipo()->getId();
            //                       $arrayRegistro['estudiante_asignatura_id'] = $updateNota->getEstudianteAsignatura()->getId();
            //                       $arrayRegistro['nota_cuantitativa'] = $updateNota->getNotaCuantitativa();
            //                       $arrayRegistro['nota_cualitativa'] = $updateNota->getNotaCualitativa();
            //                       $arrayRegistro['recomendacion'] = $updateNota->getRecomendacion();
            //                       $arrayRegistro['usuario_id'] = $updateNota->getUsuarioId();
            //                       $arrayRegistro['fecha_registro'] = $updateNota->getFechaRegistro();
            //                       $arrayRegistro['fecha_modificacion'] = $updateNota->getFechaModificacion();
            //                       $arrayRegistro['obs'] = $updateNota->getObs();

            //                       $antes = json_encode($arrayRegistro);

            //                       // despues
            //                       $arrayRegistro = null;

            //                       $updateNota->setNotaCuantitativa($nota[$i][$j]);

            //                       $arrayRegistro['id'] = $updateNota->getId();
            //                       $arrayRegistro['nota_tipo_id'] = $updateNota->getNotaTipo()->getId();
            //                       $arrayRegistro['estudiante_asignatura_id'] = $updateNota->getEstudianteAsignatura()->getId();
            //                       $arrayRegistro['nota_cuantitativa'] = $updateNota->getNotaCuantitativa();
            //                       $arrayRegistro['nota_cualitativa'] = $updateNota->getNotaCualitativa();
            //                       $arrayRegistro['recomendacion'] = $updateNota->getRecomendacion();
            //                       $arrayRegistro['usuario_id'] = $updateNota->getUsuarioId();
            //                       $arrayRegistro['fecha_registro'] = $updateNota->getFechaRegistro();
            //                       $arrayRegistro['fecha_modificacion'] = $updateNota->getFechaModificacion();
            //                       $arrayRegistro['obs'] = $updateNota->getObs();

            //                       $despues = json_encode($arrayRegistro);

            //                       // registro del log
            //                       $resp = $defaultController->setLogTransaccion(
            //                           $updateNota->getId(),
            //                           'estudiante_nota',
            //                           'U',
            //                           json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
            //                           $this->session->get('userId'),
            //                           '',
            //                           $despues,
            //                           $antes,
            //                           'SIGED',
            //                           json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            //                       );
            //                   }
            //                 }
            //                 $em->flush();
            //             }

            //         }
            //     }
            // }

            //set new ESTADOS
            $response = $this->validateEstadoStudent($request);

            $em->getConnection()->commit();
            //verifcatiokn
            
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion[0]);

            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_estados(:option::VARCHAR,:rude::VARCHAR,:gestion::VARCHAR)');
            $query->bindValue(':option', 2);
            $query->bindValue(':rude', $rude);
            // $query->bindValue(':gestion', 2016);
            $query->bindValue(':gestion', $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId());
            $query->execute();
            $em->getConnection()->commit();

            $objValidationProcess = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave'=>$rude, 'validacionReglaTipo'=>6));

            if($response == 'done'){
              $message = 'Este caso no corresponde.';
              $this->addFlash('warning', $message);
              return new JsonResponse(array('mensaje'=> $message, 'typeMessage'=>'warning'));
            }else{
              if($objValidationProcess->getEsActivo()){
                $message = 'Se realizó la validación correctamente para el código RUDE: ' . $rude;
                $this->addFlash('success', $message);
                return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>'success'));
              }else{
                $message = 'Aún existe inconsistencia de datos para el código RUDE: ' . $rude;
                $this->addFlash('warning', $message);
                return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>'warning'));
              }
            }

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(array('mensaje'=>'Error al registrar','typeMessage'=>'error'));
        }
    }


      private function validateEstadoStudent($request){
      // Create DB conexxion
      // dump($request);die;
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the current states
      $arrEstudianteEstado = $request->get('estadoMatriculaActual');
      $arrIdInscripcion = $request->get('arrIdInscripcion');
      // $arridEstudianteAsignatura = $request->get('idEstudianteAsignatura');
      $arrEstadoMatriculaNuevo = $request->get('estadoMatriculaNuevo');
      $error = 'done';
      $error1 = '';

      // dump($arrEstudianteEstado);
      // dump($arrEstadoMatriculaNuevo);
      // die;
      try {

        //validate the students stado
        $arrEstados = array(5,26,37,55,56,57,58,11);
        foreach ($arrEstudianteEstado as $key => $estado) {
          //dump($estado);
          if(!in_array($estado, $arrEstados)){
            // get ids to change the estado
            $idInscripcion = isset($arrIdInscripcion[$key])?$arrIdInscripcion[$key]:'';
            // $dataEstudianteAsignatura = isset($arridEstudianteAsignatura[$key])?$arridEstudianteAsignatura[$key]:'';
            $idEstadoMatriculaNuevo = isset($arrEstadoMatriculaNuevo[$key])?$arrEstadoMatriculaNuevo[$key]:'';
            //save the new estado
            $this->changeStudentState($idInscripcion, $idEstadoMatriculaNuevo);
            $error = true;

          }else{
            $error1='este caso no corresponde';
          }
          # code...
        }
        // $em->getConnection()->commit();
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
      if($error == 'done'){
        return $error1;
      }else{
        return $error;
      }

      // dump($request);
    die;
    }

    private function changeStudentState($id, $newMatricula){
      $defaultController = new DefaultCont();
      $defaultController->setContainer($this->container);

      $em = $this->getDoctrine()->getManager();
      $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id);
      //set the old value
      $arrValOld = array(
        'id'=>$id,
        'EstadomatriculaTipo'=>$objStudentInscription->getEstadomatriculaTipo()->getId()
      );
      $objStudentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($newMatricula));
      $em->persist($objStudentInscription);
      $em->flush();

      $objValNew = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id);
      // set the new value
      $arrValNew = array(
        'id'=>$id,
        'EstadomatriculaTipo'=>$objValNew->getEstadomatriculaTipo()->getId()
      );
      $resp = $defaultController->setLogTransaccion(
          $id,
          'EstudianteInscripcion',
          'U',
          json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
          $this->session->get('userId'),
          '',
          json_encode($arrValNew),
          json_encode($arrValOld),
          'SIGED',
          json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
      );
      return true;
    }





}
