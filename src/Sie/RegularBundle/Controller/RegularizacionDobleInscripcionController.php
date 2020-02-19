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

            $arrayEstados = [];
            $estadosFinales = [5,26,37,55,56,57,58,11,28];
            $estadosFinalSinNota = [56,57,58,26];
            $estadosFinalConNota = [5,55,37,11,28,5,55];
            $tieneEstadoFinal = false;
            $arrayInscripciones = array();
            $arrayMatriculaLista = array();

            foreach ($ins as $i) {

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
                $inscripcionActual['cantidadNotaFaltante'] = (count($inscripcionActual['cuantitativas'])*4)-$inscripcionActual['cantidadRegistrados'];

                if ((in_array($i['estadomatriculaId'], $estadosFinalConNota) and $inscripcionActual['cantidadNotaFaltante'] == 0) or (in_array($i['estadomatriculaId'], $estadosFinalSinNota))) {
                  $inscripcionActual['estadoFinal'] = true;
                } else {
                  $inscripcionActual['estadoFinal'] = false;
                }


                if ($inscripcionActual['cantidadRegistrados'] == 0){
                  $inscripcionActual['estadosCambiar'] = $this->getEstadoMatriculaDisponibleSinNota($inscripcionActual['gestion']);
                } else {
                  $inscripcionActual['estadosCambiar'] = $this->getEstadoMatriculaDisponibleConNota($inscripcionActual['gestion']);
                }

                //$arrayMatriculaLista = $arrayMatriculaLista + $inscripcionActual['estadosCambiar'];
                $arrayMatriculaLista = array_merge($arrayMatriculaLista, $inscripcionActual['estadosCambiar']);
                //dump($arrayMatriculaLista);
                $arrayInscripciones[] = $inscripcionActual;
                $arrayEstados[] = $i['estadomatriculaId'];

                // // VERIFICAMOS SI ALGUNA DE LAS INSCRIPCIONES YA CUENTA CON ESTADO FINAL
                // // if (in_array($i['estadomatriculaId'], $estadosFinales)) {
                // //     $tieneEstadoFinal = true;
                // // }
                // if ((in_array($i['estadomatriculaId'], $estadosFinalConNota) and $inscripcionActual['cantidadNotaFaltante'] == 0) or (in_array($i['estadomatriculaId'], $estadosFinalSinNota))) {
                //     $tieneEstadoFinal = true;
                // }
            }
            
            //$arrayInscripciones['estadoMatriculaLista'] = array_unique($arrayInscripciones['estadoMatriculaLista']);
            $estadosLista = $this->getEstadoMatriculaReglaLista(implode(',',array_column($arrayMatriculaLista,'id')));
            $estadosListaArray = array();
            foreach ($estadosLista as $est) {
              $estadosListaArray[$est['estadomatricula_tipo_id']][] = $est['alterno_estadomatricula_tipo_id'];
            }
            $estadosListaJson = 'var estadosListaArray = '.json_encode($estadosListaArray).';';
            //dump($arrayInscripciones);dump($arrayMatriculaLista);dump($estadosLista);dump($estadosListaArray);dump($estadosListaJson);;die;
            //dump($arrayInscripciones);dump($arrayMatriculaLista);dump($estados);die;
            
            // $cont = 0;
            // foreach ($arrayInscripciones as $ai) {

            //     // VERIFICAMOS SI EL ESTADO DE LA INSCRIPCION NO ES UN ESTADO FINAL
            //     // PARA CALCULAR LOS OTRSO POSIBLES ESTADOS A MODIFICAR
            //     if (!in_array($ai['estadomatriculaId'], $estadosFinales)) {

            //         // VERIFICAMOS SI LA INSCRIPCION TIENE CALIFICACIONES
            //         if ($ai['cantidadRegistrados'] > 0 and $ai['cantidadRegistrados'] < $ai['cantidadTotal']) {

            //             if ($tieneEstadoFinal) {
            //                 $estadosdisp = [9]; // RETIRO TRASLADO
            //             }else{
            //                 // VERIFICAMOS SI LA INSCRIPCION ACTUAL TIENE MAS CALIFICACIONES QUE LAS DEMAS
            //                 $mayor = false;
            //                 for ($i = 0; $i < count($arrayInscripciones); $i++) { 
            //                     if ( $ai['cantidadRegistrados'] > $arrayInscripciones[$i]['cantidadRegistrados']) {
            //                         $mayor = true;
            //                     }else{
            //                         if ($ai['idInscripcion'] != $arrayInscripciones[$i]['idInscripcion']) {
            //                             $mayor = false;
            //                             break;
            //                         }
            //                     }
            //                 }

            //                 // SI LA INSCRIPCION TIENE MAS CALIFICACIONES QUE LAS DEMAS
            //                 // ENTONCES PUEDE CAMBIAR AL ESTADO FINAL RETIRADO ABANDONO
            //                 if ($mayor) {
            //                     $estadosdisp = [10]; // RETIRO ABANDONO
            //                 }else{
            //                     $estadosdisp = [9]; // RETIRO TRASLADO
            //                 }
            //             }

            //         }else{
            //             // SI NO TIENE CALIFICACIONES SOLO PUEDE CAMBIAR A NO INCORPORADO
            //             $estadosdisp = [6]; // NO INCORPORADO
            //         }
            //     }else{
            //         $estadosdisp = [$ai['estadomatriculaId']]; //  AGREGAMOS EL MISMO ESTADO DE MATRICULA
            //     }
                

            //     $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>$estadosdisp));
            //     foreach ($estados as $e) {
            //         $arrayInscripciones[$cont]['estadosCambiar'][] = array('id'=>$e->getId(), 'estadomatricula'=>$e->getEstadomatricula());
            //     }
                
            //     $cont++;
            // }

            // dump($arrayInscripciones);die;

            // $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(6,9)));

            return $this->render('SieRegularBundle:RegularizacionDobleInscripcion:result.html.twig',array(
              'arrayInscripciones'=>$arrayInscripciones,
              'estados'=>$estadosListaJson,
              'gestion'=>$gestion,
            ));

        } catch (Exception $ex) {

        }
    }

    public function getEstadoMatriculaFinProcesoEducativo() {
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select * from estadomatricula_tipo where fin_proceso_educativo = true 
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      return $objEntidad;
    }

    public function getEstadoMatriculaDisponibleConNota($gestion) {
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select * from estadomatricula_tipo where nota_presentacion_tipo_id in (1,3) and fin_proceso_educativo = false and case ".$gestion." when date_part('year',current_date) then true else id not in (4) end
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      return $objEntidad;
    }

    public function getEstadoMatriculaDisponibleSinNota($gestion) {
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select * from estadomatricula_tipo where nota_presentacion_tipo_id in (2,3) and fin_proceso_educativo = false and case ".$gestion." when date_part('year',current_date) then true else id not in (4) end
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      return $objEntidad;
    }

    public function getEstadoMatriculaReglaLista($ids) {
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select estadomatricula_tipo_id, alterno_estadomatricula_tipo_id from estadomatricula_regla where esactivo = true and estadomatricula_tipo_id in (".$ids.") order by alterno_estadomatricula_tipo_id
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      return $objEntidad;
    }

    public function guardarAction(Request $request){               
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {

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
            // $response = $this->validateEstadoStudent($request);

            $response = $this->validaEstadoInscripcionEstudiante($request);
            if($response == false){
              return new JsonResponse(array('mensaje'=>'No puede existir la asignación de estados que intenta registrar para el estudiante '.$rude.', ','typeMessage'=>'error'));
            } 

            //dump($request);die;

            $arrEstudianteEstado = $request->get('estadoMatriculaActual');
            $arrIdInscripcion = $request->get('arrIdInscripcion');
            $arrEstadoMatriculaNuevo = $request->get('estadoMatriculaNuevo');
            $estadoMatriculaFinProcesoEducativo = $this->getEstadoMatriculaFinProcesoEducativo();
            $listaEstadoMatriculaFinProcesoEducativo = array_column($estadoMatriculaFinProcesoEducativo,'id');

            foreach ($arrEstudianteEstado as $key => $estado) {
              // get ids to change the estado
              //if(!in_array($estado, $listaEstadoMatriculaFinProcesoEducativo)){
                $idInscripcion = isset($arrIdInscripcion[$key])?$arrIdInscripcion[$key]:'';
                $idEstadoMatriculaNuevo = isset($arrEstadoMatriculaNuevo[$key])?$arrEstadoMatriculaNuevo[$key]:'';
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                if(count($inscripcion)>0){
                  $idEstadoMatriculaActual = $inscripcion->getEstadomatriculaTipo()->getId();
                } else {
                  $idEstadoMatriculaActual = 0;
                }
                if(!in_array($idEstadoMatriculaActual, $listaEstadoMatriculaFinProcesoEducativo)){
                  $this->changeStudentState($idInscripcion, $idEstadoMatriculaNuevo);
                  //dump($idEstadoMatriculaNuevo);
                } else {
                }
              //}
            }
            //dump($arrEstudianteEstado);dump($arrIdInscripcion);dump($arrEstadoMatriculaNuevo);dump($listaEstadoMatriculaFinProcesoEducativo);die;
            //$em->getConnection()->commit();
            //verifcatiokn
            
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrIdInscripcion[0]);

            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_estados(:option::VARCHAR,:rude::VARCHAR,:gestion::VARCHAR)');
            $query->bindValue(':option', 2);
            $query->bindValue(':rude', $rude);
            // $query->bindValue(':gestion', 2016);
            $query->bindValue(':gestion', $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId());
            $query->execute();
            $em->getConnection()->commit();

            $objValidationProcess = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave'=>$rude, 'validacionReglaTipo'=>6, 'gestionTipo'=> $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId()));
            
            if($response == false){
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

    private function validaEstadoInscripcionEstudiante($request){
      $estadoMatriculaNuevo = $request->get('estadoMatriculaNuevo');
        $codigoRude = $request->get('rude');
        $estadoMatriculaReglaLista = $this->getEstadoMatriculaReglaLista(implode(',',$estadoMatriculaNuevo));
        $estadoMatriculaReglaListaArray = array();
        foreach ($estadoMatriculaReglaLista as $est) {
          $estadoMatriculaReglaListaArray[$est['estadomatricula_tipo_id']][] = $est['alterno_estadomatricula_tipo_id'];
        }
        //dump($estadoMatriculaNuevo[0]);dump($estadoMatriculaReglaLista);dump($estadoMatriculaReglaListaArray);
        //dump($estadoMatriculaNuevo);dump($estadoMatriculaReglaLista);die;
        $c = true;
        $cc = false;
        //dump($estadoMatriculaNuevo);
        foreach ($estadoMatriculaNuevo as $estNueRec) {
          foreach ($estadoMatriculaNuevo as $estNue) {
            if($estNueRec != $estNue){
              //dump($estNueRec);
              if($c == true){
                $cc = false;
                //dump($estadoMatriculaReglaListaArray[$estNue]);
                foreach ($estadoMatriculaReglaListaArray[$estNue] as $est) {
                  if($estNueRec == $est){
                      $cc = true;
                  }
                  //dump($estNueRec."-".$est."-".$cc);
                }
                if($cc == false){
                  $c = false;
                }
              }
            }
          }
        }
        //dump($c);die;
        return $c;
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
