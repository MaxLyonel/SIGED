<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Sie\AppWebBundle\Entity\Estudiante;
use \Sie\AppWebBundle\Entity\UsuarioRol;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Sie\AppWebBundle\Entity\EstudianteInscripcion;
use \Sie\AppWebBundle\Entity\EstudianteAsignatura;
use \Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Services\Funciones;

/**
 * Usuario controller.
 *
 */
class TrasladoController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }


    /**
     * form to find the stutdent's users
     *
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $userName = $sesion->get('userName');

        
        // return $this->redirectToRoute('principal_web');
        $arrayUser = array(4880808,6724503,3846452,8335918,5609814,
          1903534,7601269,'76460741F',12408349,5598692,10847510,6979685,'76091541G',12816826,5629007,5586495,10841255,5623407,5626516,
         );

        // if (!in_array($userName, $arrayUser)) {
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render($this->session->get('pathSystem') . ':Traslado:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        $agestion = array('2016' => '2016');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('traslado_web_findResult'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio', 'attr' => array('maxlength' => 18)))
                //->add('gestion', 'choice', array("mapped" => false, 'choices' => $agestion, 'required' => true))
                ->add('gestion', 'hidden', array("mapped" => false, 'data' => '2016', 'required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Lists all Usuario entities.
     *
     */
    public function findResultAction(Request $request) {

        $form = $request->get('form');

        $session = new Session();
        //$codigoRude = ($form) ? $form['codigoRude'] : $request->get('codigoRude');
        if ($form) {
            $codigoRude = $form['codigoRude'];
            $request->getSession()->set('codigoRude', $codigoRude);
        } else {
            $codigoRude = $request->getSession()->get('codigoRude');
        }
        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($codigoRude);
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $codigoRude));
        //verificamos si existe el estudiante y obtenemos informaicno del estudiante;
        if ($entities) {

//            $inscriptionOfStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $entities->getId(),
//                'gestionTipo' => $this->session->get('currentyear'),
//                'estadomatriculaTipo' => '4'
//            ));
            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->select('IDENTITY(iec.nivelTipo) as nivelTipo,IDENTITY(iec.gradoTipo) as gradoTipo,IDENTITY(iec.paraleloTipo) as paraleloTipo, IDENTITY(ei.estadomatriculaTipo) as matriculaId,
                      IDENTITY(iec.turnoTipo) as turnoTipo, ei.id as studentInscrId, IDENTITY(iec.institucioneducativa) as institucioneducativa')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo IN (:matricula) ')
                    ->andWhere('it = :idTipo')
                    ->setParameter('id', $entities->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('matricula', array(4,101))
                    ->setParameter('idTipo', 1)
                    ->getQuery();
            $inscriptionOfStudent = $query->getResult();
//dump($inscriptionOfStudent);die;
            //validate the current inscription
            if (!$inscriptionOfStudent) {
                $session->getFlashBag()->add('noticemove', 'El estudiante no cuenta con inscripción Efectiva para la gestion actual');
                return $this->render($this->session->get('pathSystem') . ':Traslado:index.html.twig', array(
                            'form' => $this->createSearchForm()->createView(),
                ));
            }

            //dump(  $inscriptionOfStudent[0]->getNivelTipo()->getId());
            $bolNote = false;
            //get nota to the student on transfer way
            $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudent($entities->getId(),
                                                                                                    $inscriptionOfStudent[0]['nivelTipo'],
                                                                                                    $inscriptionOfStudent[0]['gradoTipo'],
                                                                                                    $inscriptionOfStudent[0]['paraleloTipo'],
                                                                                                    $inscriptionOfStudent[0]['turnoTipo'],                                                                                                    $this->session->get('currentyear'),
                                                                                                    $inscriptionOfStudent[0]['institucioneducativa']);

          if($inscriptionOfStudent[0]['nivelTipo']==11 || $inscriptionOfStudent[0]['nivelTipo']==1 || in_array($inscriptionOfStudent[0]['matriculaId'], array(101))){
              // reset($objNota);
              // while ($val = current($objNota)) {
              //   if($val['notaCualitativa']){
              //     $bolNote = true;
              //   }
              //     next($objNota);
              // }
              $bolNote = true;
            }else{
              //validate if the student has notas
              reset($objNota);
              while ($val = current($objNota)) {
                if($val['notaCuantitativa']>0){
                  $bolNote = true;
                }
                  next($objNota);
              }
            }//end if

            //check if the student has calification
            if(!$bolNote){
              $session->getFlashBag()->add('noticemove', 'El estudiante no cuenta con notas... favor revisar');
              return $this->render($this->session->get('pathSystem') . ':Traslado:index.html.twig', array(
                          'form' => $this->createSearchForm()->createView(),
              ));
            }


            //obtenemos curso actual para realizar el traslado
            $currentInscription = $this->getCurrentInscriptionsStudent($codigoRude);

            //get the las inscription of student the new way
            $objLastUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objLastUe->createQueryBuilder('ei')
                    ->select('IDENTITY(iec.institucioneducativa) as ieId')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->setParameter('id', $entities->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->orderBy('ei.fechaInscripcion', 'ASC')
                    ->getQuery();
            $lastUnidadEducativa = $query->getResult();

            //get the last UE
            $objUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objUe->createQueryBuilder('ei')
                    ->select('IDENTITY(iec.institucioneducativa) as institucioneducativa, IDENTITY(iec.nivelTipo) as nivelId,IDENTITY(iec.cicloTipo) as cicloId,
                    IDENTITY(iec.gradoTipo) as gradoId,IDENTITY(iec.paraleloTipo) as paraleloId, IDENTITY(iec.turnoTipo) as turnoId, ei.id as estInsId')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estudiante = :id')
                    ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                    ->andWhere('it = :idTipo')
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('id', $entities->getId())
                    ->setParameter('mat', array(4,101))
                    ->setParameter('idTipo',1)
                    ->getQuery();
            $objLastUe = $query->getResult();

            $lastue = ($objLastUe) ? $objLastUe[0]['institucioneducativa'] : 'krlos';
            $forminscription = $this->createFormInscription($currentInscription[0], $codigoRude, $entities->getId(), $lastue, json_encode($objLastUe[0]));

            //obtenemos informacion de inscription (tipo record) sobre el estudiante
            $dataInscriptions = $this->getInscriptionsStudent($entities->getId());
            return $this->render($this->session->get('pathSystem') . ':Traslado:findResult.html.twig', array(
                        'datastudent' => $entities, 'datainscriptions' => $dataInscriptions, 'formInscription' => $forminscription->createView()
            ));
        } else {
            $session->getFlashBag()->add('noticemove', 'El Rude es invalido o Estudiante no Existe');
            //return $this->render($this->session->get('pathSystem') . ':Traslado:searchuser.html.twig', array('form' => $this->createSearchForm()->createView()));
            return $this->render($this->session->get('pathSystem') . ':Traslado:index.html.twig', array(
                        'form' => $this->createSearchForm()->createView(),
            ));
        }
    }

    private function createFormInscription($currentInscription, $rude, $idStudent, $lastue, $infoUeducativaFrom) {
      $arrBimestre = array('1'=>'1ro Bim','2'=>'2do Bim','3'=>'3ro Bim','4'=>'4to Bim');
        return $this->createFormBuilder($currentInscription)
                        ->setAction($this->generateUrl('traslado_web_registreInscrip'))
                        ->add('institucionEducativa', 'text', array('mapped' => false, 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'pattern' => '[0-9]{7,8}')))
                        ->add('institucionEducativaName', 'text', array('mapped' => false, 'label' => 'Institucion Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'text', array('mapped' => false, 'data' => $currentInscription['nivel'], 'disabled' => true, 'required' => false, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'text', array('mapped' => false, 'data' => $currentInscription['grado'], 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivelId', 'hidden', array('mapped' => false, 'data' => $currentInscription['nivelId']))
                        ->add('gradoId', 'hidden', array('mapped' => false, 'data' => $currentInscription['gradoId']))
                        ->add('rude', 'hidden', array('mapped' => false, 'data' => $rude))
                        ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                        ->add('lastue', 'hidden', array('data' => $lastue))
                        ->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('infoUeducativaFrom', 'hidden', array('data'=>$infoUeducativaFrom))
                        //->add('bimestre', 'choice',array('label'=>'Bimestre Traslado','choices'=>$arrBimestre, 'attr'=>array('class'=>'form-control')))
                        ->add('save', 'button', array('label' => 'Verificar y Registrar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'verificarTraslado()')))
                        ->getForm();
    }
    /**
    * Function verificartraslado
    *
    * @author krlos Pacha C. <pckrlos@gmail.com>
    * @access public
    * @param obj request
    * @return string
    */
    public function verificartrasladoAction(Request $request){
      //create the DB conexion
      $em = $this->getDoctrine()->getManager();
      //$em->getConnection()->beginTransaction();
      //get the send values
      $form = $request->get('form');
      
      $newForm = json_encode($form);
      //dump($newForm);die;
      $arrInfoUeducativaFrom = json_decode($form['infoUeducativaFrom'],true);

      //validtation abuut if the ue close SEXTO
      if($form['nivelId'] == 13 && $form['gradoId']==6 && $this->get('funciones')->verificarSextoSecundariaCerrado($form['institucionEducativa'],$this->session->get('currentyear'))){
        $message = 'No se puede realizar la inscripción debido a que la Unidad Educativa seleccionada ya se cerro el operativo Sexto de Secundaria';
        $this->addFlash('estadoTraslado', $message);
        return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig');
      }              

      // validation if the ue is over 4 operativo
      $operativo = $this->get('funciones')->obtenerOperativo($form['institucionEducativa'],$this->session->get('currentyear'));
      if($operativo >= 4){
        $message = 'No se puede realizar el traslado debido a que para la Unidad Educativa seleccionada ya se consolidaron todos los operativos';
        $this->addFlash('estadoTraslado', $message);
        return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig');
      }      

      //VALIDATE OPERATIVO IN BOTH SIE's
      $operativoUETo   = $this->get('funciones')->obtenerOperativo($form['institucionEducativa'],$this->session->get('currentyear'));
      $operativoUEFrom = $this->get('funciones')->obtenerOperativo($arrInfoUeducativaFrom['institucioneducativa'],$this->session->get('currentyear'));

      //validate if the user download the sie file
      if($operativoUETo != $operativoUEFrom){

        $message = 'Traslado no realizado debido a que la siguiente Unidad Educativa no se encuentra consolidada: ';
        if($operativoUETo > $operativoUEFrom){
          $message .= $arrInfoUeducativaFrom['institucioneducativa'];
        } else {
          $message .= $form['institucionEducativa'];
        }
        // dump($operativoUETo);
        // dump($operativoUEFrom);die;

        $this->addFlash('estadoTraslado', $message);
        return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
        );
      }
      //validate allow access
      $arrAllowAccessOption = array(7,8);
      if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$this->session->get('currentyear')));
        //validate if the user download the sie file
        if($swAccess){
          $message = 'Traslado no realizado debido a que ya descargo el archivo SIE, favor realizar este proceso con su Tec. de Departamento';
          $this->addFlash('estadoTraslado', $message);
          return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
          );
        }
      }

      //select * from sp_genera_trapaso_estudiante('2016', '8073070220071504', '80730390', '13', '3', '6', '1', '1', '80730525', '13', '3', '6', '2', '1','0');
      // $query = $em->getConnection()->prepare('SELECT * from sp_genera_trapaso_estudiante_forzado(
      $query = $em->getConnection()->prepare('SELECT * from sp_genera_trapaso_estudiante(      
        :igestion::VARCHAR, :icodigorude::VARCHAR,
        :icodueO::VARCHAR,:inivelO::VARCHAR, :icicloO::VARCHAR, :igradoO::VARCHAR, :iparaleloO::VARCHAR,:iturnoO::VARCHAR,
        :icodueD::VARCHAR,:inivelD::VARCHAR, :icicloD::VARCHAR, :igradoD::VARCHAR, :iparaleloD::VARCHAR,:iturnoD::VARCHAR,
        :iverifica::VARCHAR
      )');
      $query->bindValue(':igestion',    $this->session->get('currentyear'));
      $query->bindValue(':icodigorude', $form['rude']);

      $query->bindValue(':icodueO',     $arrInfoUeducativaFrom['institucioneducativa']);
      $query->bindValue(':inivelO',     $arrInfoUeducativaFrom['nivelId']);
      $query->bindValue(':icicloO',     $arrInfoUeducativaFrom['cicloId']);
      $query->bindValue(':igradoO',     $arrInfoUeducativaFrom['gradoId']);
      $query->bindValue(':iparaleloO',  $arrInfoUeducativaFrom['paraleloId']);
      $query->bindValue(':iturnoO',     $arrInfoUeducativaFrom['turnoId']);

      $query->bindValue(':icodueD',     $form['institucionEducativa']);
      $query->bindValue(':inivelD',     $form['nivelId']);
      $query->bindValue(':icicloD',     $arrInfoUeducativaFrom['cicloId']);
      $query->bindValue(':igradoD',     $form['gradoId']);
      $query->bindValue(':iparaleloD',  $form['paralelo']);
      $query->bindValue(':iturnoD',     $form['turno']);
      $query->bindValue(':iverifica',    0);

      $query->execute();
      $swTraslado = $query->fetchAll();

      // dump($swTraslado); die;
//$em->getConnection()->commit();
      switch ($swTraslado[0]['sp_genera_trapaso_estudiante']) {
      // switch ($swTraslado[0]['sp_genera_trapaso_estudiante_forzado']) {
        case 0:
            $message = "Traslado no realizado, porque que se detecto inconsistencia de datos. Error:  DIFERENCIAN EN TRIMESTRES COMPLETADOS";
            $this->addFlash('estadoTraslado', $message);
            return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
            );
            break;
        case 5:
            $message = "Traslado no realizado, porque que se detecto inconsistencia de datos. Error:  ESTA EL ESTUDIANTE EN LA UE DESTINO ESTA CON MATERIAS Y TRIMESTRES, PERO EN OTRO GRADO ERROR DE CONSISTENCIA ";
            $this->addFlash('estadoTraslado', $message);
            return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
            );
            break;
        case 1:
        case 4:
        case 2:
            $message = "Traslado realizado.";
            $this->addFlash('estadoTraslado', $message);

            $this->get('funciones')->setLogTransaccion(
                                $arrInfoUeducativaFrom['estInsId'],
                                'estudiante_inscripcion',
                                'C',
                                'traslado',
                                $arrInfoUeducativaFrom['estInsId'],
                                '',
                                'siged',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );

            return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
            );
            break;
        // case 2:

        //     // Datos de la inscripcion actual
        //     $arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($arrInfoUeducativaFrom['estInsId']);

        //     $notas = $arrayNotas['notas'];
        //     //dump($notas);die;
        //     $cualitativas = $arrayNotas['cualitativas'];


        //     // Materias del nuevo curso
        //     $nuevoCurso = $em->createQueryBuilder()
        //                     ->select('ieco')
        //                     ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
        //                     ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
        //                     ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
        //                     ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
        //                     ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
        //                     ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
        //                     ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
        //                     ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
        //                     ->innerJoin('SieAppWebBundle:CicloTipo','ct','with','iec.cicloTipo = ct.id')
        //                     ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
        //                     ->where('ie.id = :sie')
        //                     ->andWhere('gt.id = :gestion')
        //                     ->andWhere('tt.id = :turno')
        //                     ->andWhere('nt.id = :nivel')
        //                     ->andWhere('grat.id = :grado')
        //                     ->andWhere('pt.id = :paralelo')
        //                     ->andWhere('ct.id = :ciclo')
        //                     ->orderBy('at.id','ASC')
        //                     ->setParameter('sie',$form['institucionEducativa'])
        //                     ->setParameter('gestion',$this->session->get('currentyear'))
        //                     ->setParameter('turno',$form['turno'])
        //                     ->setParameter('nivel',$form['nivelId'])
        //                     ->setParameter('grado',$form['gradoId'])
        //                     ->setParameter('paralelo',$form['paralelo'])
        //                     ->setParameter('ciclo',$arrInfoUeducativaFrom['cicloId'])
        //                     ->getQuery()
        //                     ->getResult();


        //     //dump($nuevoCurso);die;
        //     $nuevoArrayNotas = array();
        //     foreach ($nuevoCurso as $nc) {
        //         $existe = false;
        //         for($i=0;$i<count($notas);$i++){
        //             if($nc->getAsignaturaTipo()->getId() == $notas[$i]['idAsignatura']){
        //                 $existe = true;
        //                 $position = $i;
        //             }
        //         }
        //         if($existe == true){
        //             $nuevoArrayNotas[] = $notas[$position];
        //         }else{
        //             $cantidadNotas = count($notas[$position-1]['notas']);
        //             $notasAdd = array();
        //             for($j=1;$j<=$cantidadNotas;$j++){
        //                 $notasAdd[] = array(
        //                     'id'=>$i."-".$j,
        //                     'idEstudianteNota'=>'nuevo',
        //                     'nota'=>'',
        //                     'idNotaTipo'=>$j
        //                 );
        //             }

        //             //dump($cantidadNotas);die;
        //             $nuevoArrayNotas[] = array(
        //                 'idAsignatura'=>$nc->getAsignaturaTipo()->getId(),
        //                 'asignatura'=>$nc->getAsignaturaTipo()->getAsignatura(),
        //                 'idCursoOferta'=>$nc->getId(),
        //                 'notas'=>$notasAdd
        //             );
        //         }
        //     }
        //     //
        //     //dump($nuevoArrayNotas);
        //     //die;

        //     //dump($arrayNotas);die;
        //     $message = "Traslado no realizado, se identifico que el curso de destino tiene mas materias, debe registrar las notas de la(s) materia(s) faltantes para poder realizar el traslado.";
        //     $this->addFlash('estadoTraslado', $message);

        //     // Armamos el array con los datos de la nueva inscripcion


        //     return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig',array(
        //       'asignaturas'=>$arrayNotas['notas'],'cualitativas'=>$arrayNotas['cualitativas'],'operativo'=>$arrayNotas['operativo'],
        //       'newAsignaturas'=>$nuevoArrayNotas,
        //       'newForm'=>$newForm,

        //     ));
        //     break;
      }

      die;


    }


    public function saveTrasladoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $newForm = $request->get('newForm');
        $form = json_decode($newForm,true);
        //dump($form);die;

        $arrInfoUeducativaFrom = json_decode($form['infoUeducativaFrom'],true);

        //select * from sp_genera_trapaso_estudiante('2016', '8073070220071504', '80730390', '13', '3', '6', '1', '1', '80730525', '13', '3', '6', '2', '1','0');
        $query = $em->getConnection()->prepare('SELECT * from sp_genera_trapaso_estudiante(
          :igestion::VARCHAR, :icodigorude::VARCHAR,
          :icodueO::VARCHAR,:inivelO::VARCHAR, :icicloO::VARCHAR, :igradoO::VARCHAR, :iparaleloO::VARCHAR,:iturnoO::VARCHAR,
          :icodueD::VARCHAR,:inivelD::VARCHAR, :icicloD::VARCHAR, :igradoD::VARCHAR, :iparaleloD::VARCHAR,:iturnoD::VARCHAR,
          :iverifica::VARCHAR
        )');
        $query->bindValue(':igestion',    $this->session->get('currentyear'));
        $query->bindValue(':icodigorude', $form['rude']);

        $query->bindValue(':icodueO',     $arrInfoUeducativaFrom['institucioneducativa']);
        $query->bindValue(':inivelO',     $arrInfoUeducativaFrom['nivelId']);
        $query->bindValue(':icicloO',     $arrInfoUeducativaFrom['cicloId']);
        $query->bindValue(':igradoO',     $arrInfoUeducativaFrom['gradoId']);
        $query->bindValue(':iparaleloO',  $arrInfoUeducativaFrom['paraleloId']);
        $query->bindValue(':iturnoO',     $arrInfoUeducativaFrom['turnoId']);

        $query->bindValue(':icodueD',     $form['institucionEducativa']);
        $query->bindValue(':inivelD',     $form['nivelId']);
        $query->bindValue(':icicloD',     $arrInfoUeducativaFrom['cicloId']);
        $query->bindValue(':igradoD',     $form['gradoId']);
        $query->bindValue(':iparaleloD',  $form['paralelo']);
        $query->bindValue(':iturnoD',     $form['turno']);
        $query->bindValue(':iverifica',    1);

        $query->execute();
        $swTraslado = $query->fetchAll();

        // Obtener la nueva inscripcion
        $inscripcion = $em->createQueryBuilder()
                        ->select('ei')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
                        ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                        ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                        ->innerJoin('SieAppWebBundle:CicloTipo','ct','with','iec.cicloTipo = ct.id')
                        ->where('iec.institucioneducativa = :sie')
                        ->andWhere('iec.gestionTipo = :gestion')
                        ->andWhere('iec.turnoTipo= :turno')
                        ->andWhere('iec.nivelTipo = :nivel')
                        ->andWhere('iec.gradoTipo = :grado')
                        ->andWhere('iec.paraleloTipo = :paralelo')
                        ->andWhere('iec.cicloTipo = :ciclo')
                        ->andWhere('e.codigoRude = :rude')
                        ->setParameter('sie',$form['institucionEducativa'])
                        ->setParameter('gestion',$this->session->get('currentyear'))
                        ->setParameter('turno',$form['turno'])
                        ->setParameter('nivel',$form['nivelId'])
                        ->setParameter('grado',$form['gradoId'])
                        ->setParameter('paralelo',$form['paralelo'])
                        ->setParameter('ciclo',$arrInfoUeducativaFrom['cicloId'])
                        ->setParameter('rude',$form['rude'])
                        ->getQuery()
                        ->getResult();


        //dump($inscripcion);die;
        // Registramos las asignaturas que falten
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idNotaTipo = $request->get('idNotaTipo');
        $idCursoOferta = $request->get('idCursoOferta');
        $nota = $request->get('nota');

        // Obtenemos los cursos ofertas unicos
        $cursosOfertasParaRegistrar = $idCursoOferta;
        $cursosOfertasParaRegistrar = array_values(array_unique($cursosOfertasParaRegistrar));

        for($i=0;$i<count($cursosOfertasParaRegistrar);$i++){

            // Verificamos si la materia ya fue refgistrada
            // para evitar duplicados
            $existeMateria = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$inscripcion[0]->getId(),'institucioneducativaCursoOferta'=>$cursosOfertasParaRegistrar[$i]));

            //dump($existeMateria[0]->getId());die;

            if(!$existeMateria){

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                $newAsignatura = new EstudianteAsignatura();
                $newAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $newAsignatura->setFechaRegistro(new \DateTime('now'));
                $newAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcion[0]->getId()));
                $newAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($cursosOfertasParaRegistrar[$i]));
                $em->persist($newAsignatura);
                $em->flush();

                $idEstudianteAsignatura = $newAsignatura->getId();
            }else{
                $idEstudianteAsignatura = $existeMateria[0]->getId();
            }

            for ($j=0; $j < count($idEstudianteNota); $j++) {
                if($cursosOfertasParaRegistrar[$i] == $idCursoOferta[$j]){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                    $newNota = new EstudianteNota();
                    $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$j]));
                    $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura));
                    if($form['nivelId'] == 11){
                        $newNota->setNotaCuantitativa(0);
                        $newNota->setNotaCualitativa(mb_strtoupper($nota[$j],'utf-8'));
                    }else{
                        $newNota->setNotaCuantitativa($nota[$j]);
                        $newNota->setNotaCualitativa('');
                    }
                    $newNota->setRecomendacion('');
                    $newNota->setUsuarioId($this->session->get('userId'));
                    $newNota->setFechaRegistro(new \DateTime('now'));
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $newNota->setObs('');
                    $em->persist($newNota);
                    $em->flush();
                }
            }


        }

        $message = "Traslado realizado.";
        $this->addFlash('estadoTraslado', $message);
        return $this->render($this->session->get('pathSystem') . ':Traslado:confirmarTraslado.html.twig'
        );

    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getInscriptionsStudent($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'ei.fechaInscripcion', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id=ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa=i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo=em.id')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->orderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {
        //$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andWhere('it = :idTipo')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->setParameter('id', $id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('idTipo',1)
                ->setParameter('mat', array(4,101))
                ->orderBy('ei.fechaInscripcion', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id, $nivel, $grado, $lastue) {
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $paralelo = array();
        $turno = array();
        if ($institucion) {
            $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "No existe Unidad Educativa";
            //get the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                if ($lastue != $id) {

                    $objTraslado = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                    $query = $objTraslado->createQueryBuilder('iec')
                            ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                            ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                            ->where('iec.institucioneducativa = :id')
                            ->andwhere('iec.nivelTipo = :nivel')
                            ->andwhere('iec.gradoTipo = :grado')
                            ->andwhere('iec.gestionTipo = :gestion')
                            ->setParameter('id', $id)
                            ->setParameter('nivel', $nivel)
                            ->setParameter('grado', $grado)
                            ->setParameter('gestion', $this->session->get('currentyear'))
                            ->distinct()
                            ->orderBy('iec.paraleloTipo', 'ASC')
                            ->getQuery();
                    $infoTraslado = $query->getResult();

                    foreach ($infoTraslado as $info) {
                        $paralelo[$info['paraleloTipo']] = $info['paralelo'];
                        //$paralelo[$info->getParaleloTipo()->getId()] = $info->getParaleloTipo()->getParalelo();
                        //$turno[$info->getTurnoTipo()->getId()] = $info->getTurnoTipo()->getTurno();
                    }
                } else {
                    $nombreIE = 'No se puede realizar el traslado porque ya tiene una inscripcion en esa unidad educativa';
                }
            } else {
                $nombreIE = 'No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = "No existe Unidad Educativa";
        }


        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }


    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function registreInscripAction(Request $request) {
        //conexion to DB throws em

        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');
            //update the inscription with matriculaFinID like 9
            $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objInscription->createQueryBuilder('ei')
                    ->select('(ei.id) as inscriptionId, IDENTITY(iec.institucioneducativa) as institucioneducativa, IDENTITY(iec.paraleloTipo) as paraleloTipo, IDENTITY(iec.turnoTipo) as turnoTipo,IDENTITY(iec.gradoTipo) as gradoTipo, IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.nivelTipo) as nivelTipo')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :idstudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :etipo')
                    ->andWhere('it = :idTipo')
                    ->setParameter('idstudent', $form['idStudent'])
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('etipo', 4)
                    ->setParameter('idTipo', 1)
                    ->getQuery();
            $objresultInscription = $query->getResult();

            //get the data opertation TO
            $dataInfoOperationTo = array('sie'=>$form['institucionEducativa'], 'gestion'=>$this->session->get('currentyear'));

            //get operativo Ue to
            $operativoUeTo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($dataInfoOperationTo)-1;
            //get the data opertation TO
            $dataInfoOperationFrom = array('sie'=>$objresultInscription[0]['institucioneducativa'], 'gestion'=>$this->session->get('currentyear'));

            //get operativo UE from
            $operativoUeTo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($dataInfoOperationTo)-1;
            $operativoUeFrom = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($dataInfoOperationFrom)-1;

            $swOperativo = ($operativoUeFrom == $operativoUeFrom)?true:false;

            //check if the UE's have the same operativo
            if(!$swOperativo){
              $this->session->getFlashBag()->add('noticemove', 'Traslado No realizado, Unidades Educativas con diferentes Bimestre de calificaciones');
              $em->clear();
              return $this->redirect($this->generateUrl('traslado_web'));
            }

//            if ($objresultInscription[0]) {
//                $message = "No se puede realizar el traslado por que el estudiante no cuenta con inscripcion efectiva";
//                $this->addFlash('noticemove', $message);
//                return $this->redirectToRoute('traslado_web');
//            }
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objresultInscription[0]['inscriptionId']);
            $currentInscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(9));
            $em->persist($currentInscrip);
            $em->flush();
            //get the id of next course
            $newCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'institucioneducativa' => $form['institucionEducativa'],
                'nivelTipo' => $form['nivelId'],
                //'cicloTipo' => $form['cicloId'],
                'gradoTipo' => $form['gradoId'],
                'paraleloTipo' => $form['paralelo'],
                'gestionTipo' => $this->session->get('currentyear')
            ));
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($currentInscrip->getEstudiante());
            $studentInscription->setCodUeProcedenciaId(0);
            $studentInscription->setObservacion('');
            $studentInscription->setObservacionId(0);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($newCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(15));
            $em->persist($studentInscription);
            $em->flush();
            //get the institucioneducativa_curso_oferta id to to do the new insert on estudiante_asignatura
            //dump($studentInscription->getId());
            //dump($studentInscription->getInstitucioneducativaCurso()->getId());
            /*$objInstitucionEduCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$studentInscription->getInstitucioneducativaCurso()));

            //dump($objInstitucionEduCursoOferta);die;
            if($objInstitucionEduCursoOferta){
              foreach ($objInstitucionEduCursoOferta as $key => $value) {
                # code...
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                $query->execute();
                $objStudentAsignatura = new EstudianteAsignatura();
                $objStudentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $objStudentAsignatura->setFechaRegistro(new \DateTime(date('Y-m-d')));
                $objStudentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $objStudentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($value->getId()));
                $em->persist($objStudentAsignatura);
                $em->flush();

              }
            }*/

            //is  possible we need to restartar the id on estudiante_nota
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
            $query->execute();
            /*dump($form);
            dump($objresultInscription);
            die;*/
            //this is to move the calification to the new ue
            /*$query = $em->getConnection()->prepare('SELECT * from sp_traslado_estudiante(:igestion_tipo_id::INT, :iinstitucioneducativa_ant::INT, :iinstitucioneducativa_new::INT, :icodigorude::VARCHAR, :inivel_tipo_id::INT, :igrado_tipo::INT, :iturno_tipo_ant::INT, :iturno_tipo_new::INT, :iparalelo_tipo_new::VARCHAR, :iparalelo_tipo_ant::VARCHAR, :iidinscripcion_new::INT)');
            $query->bindValue(':igestion_tipo_id', $this->session->get('currentyear'));
            $query->bindValue(':iinstitucioneducativa_ant', $objresultInscription[0]['institucioneducativa']);
            $query->bindValue(':iinstitucioneducativa_new', $form['institucionEducativa']);
            $query->bindValue(':icodigorude', $form['rude']);
            $query->bindValue(':inivel_tipo_id', $form['nivelId']);
            $query->bindValue(':igrado_tipo', $form['gradoId']);
            $query->bindValue(':iturno_tipo_ant', $objresultInscription[0]['turnoTipo']);
            $query->bindValue(':iturno_tipo_new', $form['turno']);
            $query->bindValue(':iparalelo_tipo_new', $form['paralelo']);
            $query->bindValue(':iparalelo_tipo_ant', $objresultInscription[0]['paraleloTipo']);
            $query->bindValue(':iidinscripcion_new', $studentInscription->getId());
            $query->execute();

            igestion character varying,
            icodigorude character varying,
            inotatipo character varying,
            icodueO character varying,
            inivelO character varying,
            icicloO character varying,
            igradoO character varying,
            iparaleloO character varying,
            iturnoO character varying,
            icodueD character varying,
            inivelD character varying,
            icicloD character varying,
            igradoD character varying,
            iparaleloD character varying,
            iturnoD character varying

            */
            //execute the function to do the traslado
            for($i =1; $i <= $operativoUeFrom; $i++){
              # code...
              //new fucntion to todo the traslado
              $query = $em->getConnection()->prepare('SELECT * from sp_genera_cambio_paralelo(
                :igestion::VARCHAR, :icodigorude::VARCHAR, :inotatipo::VARCHAR,
                :icodueO::VARCHAR,:inivelO::VARCHAR, :icicloO::VARCHAR, :igradoO::VARCHAR, :iparaleloO::VARCHAR,:iturnoO::VARCHAR,
                :icodueD::VARCHAR,:inivelD::VARCHAR, :icicloD::VARCHAR, :igradoD::VARCHAR, :iparaleloD::VARCHAR,:iturnoD::VARCHAR
              )');
              $query->bindValue(':igestion',    $this->session->get('currentyear'));
              $query->bindValue(':icodigorude', $form['rude']);
              $query->bindValue(':inotatipo',   $i);
              $query->bindValue(':icodueO',     $objresultInscription[0]['institucioneducativa']);
              $query->bindValue(':inivelO',     $objresultInscription[0]['nivelTipo']);
              $query->bindValue(':icicloO',     $objresultInscription[0]['cicloTipo']);
              $query->bindValue(':igradoO',     $objresultInscription[0]['gradoTipo']);
              $query->bindValue(':iparaleloO',  $objresultInscription[0]['paraleloTipo']);
              $query->bindValue(':iturnoO',     $objresultInscription[0]['turnoTipo']);

              $query->bindValue(':icodueD',     $form['institucionEducativa']);
              $query->bindValue(':inivelD',     $form['nivelId']);
              $query->bindValue(':icicloD',     $objresultInscription[0]['cicloTipo']);
              $query->bindValue(':igradoD',     $form['gradoId']);
              $query->bindValue(':iparaleloD',  $form['paralelo']);
              $query->bindValue(':iturnoD',     $form['turno']);

              $query->execute();
            }



/*            $query = $em->getConnection()->prepare("select * sp_traslado_estudiante(
                                                                                    '".$this->session->get('currentyear')."','".$objresultInscription[0]['institucioneducativa']."',
                                                                                    '".$form['institucionEducativa']."',
                                                                                    '".$form['rude']."',
                                                                                    '".$form['nivelId']."',
                                                                                    '".$form['gradoId']."',
                                                                                    '".$objresultInscription[0]['turnoTipo']."',
                                                                                    '".$form['turno']."',
                                                                                    '".$form['paralelo']."',
                                                                                    '".$objresultInscription[0]['paraleloTipo']."',
                                                                                    '".$studentInscription->getId()."')"
*/



              //dump($objInstitucionEduCursoOferta);die;

            /*
            //get the asignatura info
            $objStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $objresultInscription[0]['inscriptionId']));
            //update the asignatura table with new inscription this is for the notas students
            reset($objStudentAsignatura);
            while ($val = current($objStudentAsignatura)) {
                $objUpdateStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($val->getId());
                $objUpdateStudentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $em->persist($objUpdateStudentAsignatura);
                $em->flush();
                next($objStudentAsignatura);
            }

            //get the nota cualitativa info
            $objStudentNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $objresultInscription[0]['inscriptionId']));
            //update the nota cualitativa table with new inscription this is for the notas students
            reset($objStudentNotaCualitativa);
            while ($val = current($objStudentNotaCualitativa)) {
                $objUpdateStudentNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($val->getId());
                $objUpdateStudentNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $em->persist($objUpdateStudentNotaCualitativa);
                $em->flush();
                next($objStudentNotaCualitativa);
            }
          */
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodmove', 'El traslado fue registrado sin problemas');
            $em->clear();
            return $this->redirect($this->generateUrl('traslado_web'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturnoAction($paralelo, $sie, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

}
