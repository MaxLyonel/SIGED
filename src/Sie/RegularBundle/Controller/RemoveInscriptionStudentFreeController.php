<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

/**
 * Estudiante controller.
 *
 */
class RemoveInscriptionStudentFreeController extends Controller {

    private $session;
    private $operativo;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * index action build the form to search
     *
     */
    public function indexAction(Request $request) {
        # SERVICIO QUE CONTROLA EL ACCESO A LAS OPCIONES POR URL
        # VALIDA SI EL ROL DEL USUARIO TIENE PERMISO SOBRE LA URL
        if(!$this->get('funciones')->validarRuta($request->attributes->get('_route'))){
            return $this->redirect($this->generateUrl('principal_web'));
        }

//die('krlos');
        $em = $this->getDoctrine()->getManager();
        $this->session->set('removeinscription', false);
        
        // return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        if($this->session->get('roluser') == 8){
            for ($i = 1; $i <= 16; $i++) {
                $aGestion[$currentYear] = $currentYear;
                $currentYear--;
            }
        }else{
            for ($i = 1; $i <= 1; $i++) {
                $aGestion[$currentYear] = $currentYear;
                $currentYear--;
            }
        }
 
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('remove_inscription_student_free_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 19, 'pattern' => '[A-Z0-9]{13,19}', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {


        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

         /**
         * add validation QA
         * @var [type]
         */
        $objObservation = $this->get('seguimiento')->getStudentObservationQA($form);
        // dump($objObservation);die;
        if(false){
        // if($objObservation){
  
            $message = "Estudiante observado - rude " . $form['codigoRude'] . " :";
            $this->addFlash('notiremovest', $message);
            
            $observaionMessage = 'Estudiante presenta inconsistencia, se sugiere corregirlos por las opciones de calidad...';
            $this->addFlash('studentObservation', $observaionMessage);
            return $this->redirectToRoute('remove_inscription_student_free_index');
        }

        if ($this->session->get('removeinscription')) {
            $form['codigoRude'] = $this->session->get('removeCodigoRude');
            $form['gestion'] = $this->session->get('removeGestion');
        }

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //check if the student exists
        if ($objStudent) {
            // $objInscriptionCurrent = $em->getRepository('SieAppWebBundle:Estudiante')->findCurrentStudentInscriptionWithOutEstado($objStudent->getId(), $form['gestion']);

            //validate to student with nivel 11
            /*if($objInscriptionCurrent[0]['nivelId'] == 11){
              $message = 'No permitido Estudiante con inscripción en '.$objInscriptionCurrent[0]['nivel'];
              $this->addFlash('notiremovest', $message);
              return $this->redirectToRoute('remove_inscription_student_free_index');
            }*/
            //dump($objInscriptionCurrent);die;
            //get the inscription info about student getnumberInscription
            //$objNumberInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getnumberInscription($objStudent->getId(), $this->session->get('currentyear'));
            
            

            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $form['gestion']);

            $closeopesextosecc = false;
            if ($objInscription[0]['nivelId']==13 and $objInscription[0]['gradoId']==6) {
              $closeopesextosecc = $this->get('funciones')->verificarSextoSecundariaCerrado($objInscription[0]['sie'], $objInscription[0]['gestion']);
            }

            $operativo = $this->get('funciones')->obtenerOperativo($objInscription[0]['sie'], $objInscription[0]['gestion']);
            
            if ($closeopesextosecc or $operativo > 3){
              $message = 'La Unidad Educativa donde se encuentra inscrito el estudiante, con código RUDE: '. $form['codigoRude'] .', ha cerrado su operativo de fin de gestión, por lo que no es posible modificar el estado de matrícula';
              $this->addFlash('notiremovest', $message);
              return $this->redirectToRoute('remove_inscription_student_free_index');  
            }
            
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('remove_inscription_student_free_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('remove_inscription_student_free_index');
        }
    }
    /**
    * show the view main to do the change
    **/
    public function mainCambioEstadoAction(Request $request){

      //crete the DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the send dataInfo
      $dataInfo = $request->get('dataInfo');
      $arrDataInfo = json_decode($dataInfo,true);
      $operativo = $this->get('funciones')->obtenerOperativo($arrDataInfo['sie'],$arrDataInfo['gestion']);
      if($operativo >= 3){
        return new JsonResponse([
          'status' => 400, // or any other appropriate success code
          'view' => '',
        ]);
      }     

      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($arrDataInfo['estId']);
      //get the student's inscription
      $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
      //get institucioneducativaCurso info
      $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
      $arrAllowAccessOption = array(7,8);
      if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        //get flag to do the operation
        $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$objInsctitucionEducativaCurso->getInstitucioneducativa()->getId(), 'gestion'=>$arrDataInfo['gestion']));
        //validate if the user download the sie file
        if($swAccess){
          $message = 'No se puede realizar la transacción debido a que ya descargo el archivo SIE, esta operación debe realizarlo con el Tec. de Departamento';
          $this->addFlash('idNoInscription', $message);
          return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:menssageInscription.html.twig', array());
        }

      }
      return new JsonResponse([
          'status' => 200, // or any other appropriate success code
          'view' => $this->renderView($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:maincambioestado.html.twig', array(
                        'form' => $this->mainChangeStadoForm($dataInfo,$this->session->get('roluser'))->createView(),
                        'datastudent' => $objStudent,
                        'dataInscription' => $objEstudiantInscripcion
          )),
        ]);
      // return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:maincambioestado.html.twig', array(
      //             'form' => $this->mainChangeStadoForm($dataInfo,$this->session->get('roluser'))->createView(),
      //             'datastudent' => $objStudent,
      //             'dataInscription' => $objEstudiantInscripcion
      //           ));

    }
    /**
    * create the for with the new estados
    **/
    private function mainChangeStadoForm($dataInfo, $rolUser){
      $datai = json_decode($dataInfo,true);
      $em = $this->getDoctrine()->getManager();
      // $em->getConnection()->beginTransaction();
      // $arrEstados = array('4'=>'Efectivo', '10'=>'Abandono');
      // $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($datai['estInsId']);
      $estudianteNotas = $em->createQueryBuilder()
                                ->select('en.id as idNota')
                                ->from('SieAppWebBundle:EstudianteNota','en')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                ->orderBy('en.id','ASC')
                                ->where('ea.estudianteInscripcion = :estInsId')
                                ->setParameter('estInsId',$datai['estInsId'])
                                ->getQuery()
                                ->getResult();
      $rolesAllowed = array(7,10);
      if(in_array($rolUser,$rolesAllowed)){
          if (count($estudianteNotas) > 0){
            $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO');
          } else {
            $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO','6'=>'NO INCORPORADO');
          }
      }else{
        // $arrEstados = array( '10'=>'RETIRO ABANDONO',/*'6'=>'NO INCORPORADO','9'=>'RETIRADO TRASLADO'*/);
        if($rolUser == 8)
          // $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO','6'=>'NO INCORPORADO','9'=>'RETIRADO TRASLADO','5'=>'PROMOVIDO');
          $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO','6'=>'NO INCORPORADO','5'=>'PROMOVIDO','11'=>'REPROBADO');
        else
          $arrEstados = array();
      }

      return $this->createFormBuilder()
        ->add('estadonew', 'choice', array('label'=>'Estado', 'choices'=>$arrEstados, 'attr'=>array('class'=>'form-control')))
        ->add('verificar', 'button', array('label'=>'Verificar', 'attr'=>array('class'=>'btn btn-info', 'onclick'=>"verificarCambio('$dataInfo')")))
        ->getForm();
    }
    /**
     * [validateNotas description]
     * @param  [type] $notas [description]
     * @param  [type] $state [description]
     * @return [type]        [description]
     */
    private function validateNotas($notas, $state){
      // set the parameters
      $notasComplete = array();
      $swChangeStatus = false;
        foreach ($notas['cuantitativas'] as $key => $value) {
          if(isset($value['nota'])){
            $notasComplete[]=$value['nota'];
          }
        }
        // dump($notasComplete);die;
        if($state==6 || $state==10){
          if(sizeof($notasComplete)>1){
            $swChangeStatus=true;
          }
        }
        //return the validate info
        return $swChangeStatus;
    }
    /**
     * [verificarCambioAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function verificarCambioAction(Request $request){
      //cretae the db conexion
      $em = $this->getDoctrine()->getManager();

      //get the send values
      $dataInfo = $request->get('dataInfo');
      $estadoNew = $request->get('estadonew');
      $arrDataInfo = json_decode($dataInfo,true);
      // dump($arrDataInfo);      die;
      
      //get the operativo information
      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($arrDataInfo)-1;
      $this->operativo = $this->get('funciones')->obtenerOperativo($arrDataInfo['sie'],$arrDataInfo['gestion']);
      //get the info student
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $arrDataInfo['rude']));
      //dump($arrDataInfo);die;
      //answer the verification
      $swverification = true;
      $message = '';
      if($arrDataInfo['estadoMatriculaId']==$estadoNew){
        $swverification = false;
        $message = 'No realizado, esta intentando cambiar al mismo estado... ';
        $this->addFlash('changestate', $message);
      }
      
    if($this->session->get('roluser')==9 ){ // para directores UE
      // add validation about tuicion of user
      $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
      $query->bindValue(':user_id', $this->session->get('userId'));
      $query->bindValue(':sie', $arrDataInfo['sie']);
      $query->bindValue(':roluser', $this->session->get('roluser'));
      $query->execute();
      $aTuicion = $query->fetchAll();
            //check if the user has the tuicion
      if (!$aTuicion[0]['get_ue_tuicion'] &&  $message === '') {
        $swverification = false;
        $message = "Usuario no tiene tuición para realizar la operación";
        $this->addFlash('changestate', $message);
      }
    }
      //ge notas to do the changeMatricula to Retiro Abandono
      $swChangeStatus = false;
      
      /*validation abuot the NOTAS*/
      if($this->operativo > 0 ){
        $notas = $this->get('notas')->regular($arrDataInfo['estInsId'],$this->operativo);
        $swChangeStatus = $this->validateNotas($notas, $estadoNew);

        if($swChangeStatus && $estadoNew==6 && $message === ''){
          $swverification = false;
          $message = 'No realizado, Estudidante cuenta con calificaciones. ';
          $this->addFlash('changestate', $message);
        }

        if($swChangeStatus && $estadoNew==10 &&  $message === ''){
          $swverification = false;
          $message = 'No realizado, Estudidante no cuenta con calificaciones. ';
          $this->addFlash('changestate', $message);
        }
      }

      //get the students inscriptions
      $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivosChange($objStudent->getId(), $arrDataInfo['gestion']);

     
      $db = $em->getConnection(); 

      $query =" select distinct e.codigo_rude, ic.institucioneducativa_id, ei.estadomatricula_tipo_id
                from estudiante e 
                inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id 
                where ic.gestion_tipo_id = " . $arrDataInfo['gestion'] ."
                and ic.nivel_tipo_id in (11,12,13)
                and ei.estadomatricula_tipo_id in (4,10,5,55,11)
                and ei.id <>  " . $arrDataInfo['estInsId'] ." 
                and e.id =  " . $arrDataInfo['estId'] ." ; ";

            $stmt = $db->prepare($query);       
            $stmt->execute(); 
            $result = $stmt->fetchAll();    
        // dump ( $result);
        // die;
    if(count($result) > 0 &&  $message === ''){
        $swverification = false;
        $message = 'No es posible realizar el cambio de estado, ya que el estudiante presenta otro estado de matrícula válido. Por favor, revise su historial!!!';
        $this->addFlash('changestate', $message);
    }

    if($operativo >= 3 &&  $message === ''){
        $swverification = false;
        $message = 'No se puede realizar el cambio de estado, la Unidad Educativa consolidao su información!!!';
        $this->addFlash('changestate', $message);
    }
// dump($arrDataInfo);die;
    return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:verificarcambio.html.twig', array(
              'swverification' => $swverification,
              'operativo'=>$operativo,
              'dataInfo'  => $dataInfo ,
              'estadoNew' => $estadoNew
              ));
      
    }

    /*
    // this is the next step to do the setting up NOTAS to student
    */
    private function setNotasForm($data){
      return  $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizarNotas_show'))
                // ->add('idInscripcion','text',array('data'=>$data))
                ->add('setNotas','submit',array('label'=>'Registrar Notas','attr'=>array('class'=>'btn btn-success')))
              ->getForm();
    }

    /**
    * Function saveCambioEstadoAction
    *
    * @author krlos Pacha C. <pckrlos@gmail.com>
    * @access public
    * @param object request
    * @return string
    */
    public function saveCambioEstadoAction(Request $request) {


        //get the send values
        $form = $request->get('form');
        $arrDataInfo = json_decode($form['dataInfo'],true);
// dump($form['estadoNew']);die;
        //create the DB conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $id_usuario = $this->session->get('userId');

        try {
          $justificacion =  $form['justificacionCE']==null?'':$form['justificacionCE'];
          //find the estudent's inscription to do the change
          $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
          $oldInscriptionStudent = clone $inscriptionStudent;
          $inscriptionStudent->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadoNew']));
          $inscriptionStudent->setObservacion($justificacion.' - usuario: '.$id_usuario);//$form['justificacionCE']
          $em->persist($inscriptionStudent);
          $em->flush();
          
          $em->getConnection()->commit();
          // added set log info data
          $this->get('funciones')->setLogTransaccion(
                               $inscriptionStudent->getId(),
                                'estudiante_inscripcion',
                                'U',
                                '',
                                $inscriptionStudent,
                                $oldInscriptionStudent,
                                'SIGED',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
          );     
         
          $message = "Proceso realizado exitosamente.";
          $this->addFlash('okchange', $message);
          $response = new JsonResponse();
          //check the tipo of matricula
          if($form['estadoNew']==4444444){ //se aplico en proceso de inscripción no aplica notas
            return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:setNotasChangeStatus.html.twig', array(
                      'setNotasForm'   => $this->setNotasForm($arrDataInfo['estInsId'])->createView(),
                      'idInscripcion'  => $arrDataInfo['estInsId'],
                      'newStatus'      => $form['estadoNew']
                      ));
          }else{
            return $response->setData(array('reloadIt'=>true, 'mssg'=>'Cambio de estado realizado exitosamente'));
            // die('Cambio de estado realizado exitosamente !');
          }

          //return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));

            //print_r($inscriptionStudent);
            //$this->session->set('removeinscription', true);
            //$this->session->set('removeCodigoRude', $form['codigoRude']);
            //$this->session->set('removeGestion', $form['gestion']);
//            comentado por error en sp_corrije_inscripcion_estudinate_observaciones linea 116
            /*$query = $em->getConnection()->prepare('SELECT * from sp_corrije_inscripcion_estudiante_observaciones(:rude::VARCHAR, :inscripcionid ::VARCHAR)');
            $query->bindValue(':rude', $form['codigoRude']);
            $query->bindValue(':inscripcionid', $inscriptionStudent->getId());
            $query->execute();*/




            /*
             *   Registramos las notas cuantitativas
            */
            // $idEstudianteNota = $request->get('idEstudianteNota');
            // $idNotaTipo = $request->get('idNotaTipo');
            // $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            // $nota = $request->get('nota');
            //
            // //dump($idEstudianteNota);
            // //dump($idNotaTipo);
            // //dump($idEstudianteAsignatura);
            // //dump($nota);die;
            //
            // if(count($idEstudianteNota)>0){
            //     for ($i=0; $i < count($idEstudianteNota); $i++) {
            //             if($idEstudianteNota[$i] == "nuevo"){
            //                 // Verificamos si existe la nota
            //                 $existsNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$idEstudianteAsignatura[$i],'notaTipo'=>$idNotaTipo[$i]));
            //                 if(!$existsNota){
            //                     // Actualizamos el idseq de la tabla estudiante nota
            //                     $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
            //                     // Registramos la nueva nota
            //                     $newNota = new EstudianteNota();
            //                     $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
            //                     $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
            //                     if($arrDataInfo['nivelId'] == 11){
            //                         $newNota->setNotaCuantitativa(0);
            //                         $newNota->setNotaCualitativa(mb_strtoupper($nota[$i],'utf-8'));
            //                     }else{
            //                         if(is_numeric($nota[$i])){
            //                             $notaValidada = $nota[$i];
            //                         }else{
            //                             $notaValidada = 0;
            //                         }
            //                         $newNota->setNotaCuantitativa($notaValidada);
            //                         $newNota->setNotaCualitativa('');
            //                     }
            //                     $newNota->setRecomendacion('');
            //                     $newNota->setUsuarioId($this->session->get('userId'));
            //                     $newNota->setFechaRegistro(new \DateTime('now'));
            //                     $newNota->setFechaModificacion(new \DateTime('now'));
            //                     $newNota->setObs('');
            //                     $em->persist($newNota);
            //                     $em->flush();
            //                 }
            //             }else{
            //                 $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
            //                 if($updateNota){
            //                     if($arrDataInfo['nivelId'] == 11){
            //                         $updateNota->setNotaCualitativa(mb_strtoupper($nota[$i],'utf-8'));
            //                     }else{
            //                         $updateNota->setNotaCuantitativa($nota[$i]);
            //                     }
            //                     $updateNota->setUsuarioId($this->session->get('userId'));
            //                     $updateNota->setFechaModificacion(new \DateTime('now'));
            //                     $em->flush();
            //                 }
            //             }
            //     }
            // }
            //
            //
            // /*
            // * Registramos las notas cualitativas
            // */
            // $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
            // $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
            // $notaCualitativa = $request->get('notaCualitativa');
            // // Registro de notas cualitativas de incial y secundaria
            // $idInscripcion = $arrDataInfo['estInsId'];
            //
            // for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
            //     if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
            //         // Verificamos si existe la nota
            //         $existsNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion,'notaTipo'=>$idNotaTipoCualitativa[$j]));
            //         if(!$existsNotaCualitativa){
            //             $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
            //
            //             $newCualitativa = new EstudianteNotaCualitativa();
            //             $newCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
            //             $newCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
            //             $newCualitativa->setNotaCuantitativa(0);
            //             $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
            //             $newCualitativa->setRecomendacion('');
            //             $newCualitativa->setUsuarioId($this->session->get('userId'));
            //             $newCualitativa->setFechaRegistro(new \DateTime('now'));
            //             $newCualitativa->setFechaModificacion(new \DateTime('now'));
            //             $newCualitativa->setObs('');
            //             $em->persist($newCualitativa);
            //             $em->flush();
            //         }
            //     }else{
            //         $updateCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
            //         if($updateCualitativa){
            //             $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
            //             $updateCualitativa->setUsuarioId($this->session->get('userId'));
            //             $updateCualitativa->setFechaModificacion(new \DateTime('now'));
            //             $em->flush();
            //         }
            //     }
            // }


        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! se ha detectado inconsistencia de datos.";
            $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
            return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));
        }
    }




////////////////////////////////////////////////////////////////////////77777we need to check this code down
    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultparamAction($rude, $gestion) {
        $em = $this->getDoctrine()->getManager();

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //check if the student exists
        if ($objStudent) {
            //get the inscription info about student getnumberInscription
            //$objNumberInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getnumberInscription($objStudent->getId(), $this->session->get('currentyear'));
            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $gestion);
            //check it the current inscription exists
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudentFree:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('remove_inscription_student_free_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('remove_inscription_student_free_index');
        }
    }


    /**
     * create form Student
     * @param type $data
     * @return the student form
     */
    private function createFormStudent($data) {
        $formStudent = $this->createFormBuilder()
                ->setAction($this->generateUrl('change_state_student_modify'))
                ->add('idStudent', 'hidden', array('data' => $data['idStudent']))
                ->add('iecId', 'hidden', array('data' => $data['institucioneducativaCurso']))
                ->add('save', 'submit', array('label' => 'Retirar Esudiante', 'attr' => array('class' => 'btn btn-default btn-block')));

        return $formStudent->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function modifyAction(Request $request) {
        try {
            //create conexion on DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');
            //look for the student
            $studentInsc = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'institucioneducativaCurso' => $form['iecId'],
                'estudiante' => $form['idStudent']
            ));
            //update the student's estado matricula
            $studentInsc->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
            $em->persist($studentInsc);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $message = 'Operación realizada correctamente';
            $this->addFlash('goodstate', $message);
            return $this->redirectToRoute('change_state_student_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

}
