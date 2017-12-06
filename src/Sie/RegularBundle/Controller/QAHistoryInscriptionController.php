<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for to fixed an inscirption
 * Date: 12-10-2017
 *
 *
 * class.QAHistoryInscriptionController.php
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */

class QAHistoryInscriptionController extends Controller{

    public $session;
      public function __construct() {
          $this->session = new Session();
      }

    /**
     * [indexAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){
      //create conexion
      $em = $this->getDoctrine()->getManager();
      //get valuse send
      $form = $request->get('form');
      //get data student
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
      $form['idStudent'] = $objStudent->getId();
      // get the student's history
      $inscriptions = $this->getCurrentInscriptionsStudent($form['codigoRude']);

      // this is to render the view page
        return $this->render('SieRegularBundle:QAHistoryInscription:index.html.twig', array(
                'form'=>$this->createFormInscriptionQA($form)->createView(),
                'datastudent'=> $objStudent,
                'currentInscription' => $inscriptions,

        ));
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
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
                ->andWhere('it = :idTipo')
                ->setParameter('id', $id)
                ->setParameter('idTipo',1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    /**
     * [createFormNewExtranjeros description]
     * @param  integer $idStudent [description]
     * @param  integer $sw        [description]
     * @param  array   $data      [description]
     * @return [type]             [description]
     */
    private function createFormInscriptionQA($data) {

        $em = $this->getDoctrine()->getManager();

        $onivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($data['nivel']);
        $ogrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data['grado']);
        // $ociclo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextciclo);
        $currentyear = $this->session->get('currentyear');
        for ($i=0; $i < 9 ; $i++) {
          $arrGestiones[$currentyear] = $currentyear;
          $currentyear--;
        }

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('qahistoryInscription_do_inscription'))
                ->add('gestion', 'choice', array('label' => 'Gestion','choices'=>$arrGestiones , 'empty_value' => 'seleccionar...','attr' => array('class' => 'form-control', 'required' => true)))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'data' => '', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'text', array('data' => $onivel->getNivel(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('grado', 'text', array('data' => $ogrado->getGrado(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('nivelId', 'hidden', array('data' => $onivel->getId()))
                ->add('gradoId', 'hidden', array('data' => $ogrado->getId()))
                //  ->add('cicloId', 'hidden', array('data' => $ociclo->getId()))
                ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'seleccionar...','attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'seleccionar...', 'attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => ''))
                ->add('nroInconsistencias', 'hidden', array('data' => $data['nroInconsistencias']))
                ->add('idStudent', 'hidden', array('data' => $data['idStudent']))
                ->add('codigoRude', 'hidden', array('data' => $data['codigoRude']))
                ->add('idProceso', 'hidden', array('data' => $data['idProceso']))
                ->add('optionInscription','hidden', array('data'=>''))
                ->add('save', 'submit', array('label' => 'Verificar y Registrar', 'attr'=>array()))
                ->getForm();
    }
    /**
     * [findIEAction description]
     * @param  [type] $id    [description]
     * @param  [type] $nivel [description]
     * @param  [type] $grado [description]
     * @return [type]        [description]
     */
    public function findIEAction($id, $nivel, $grado, $gestion) {

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        $paralelo = array();
        $turno = array();
        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if ($ue) {
            if ($aTuicion[0]['get_ue_tuicion']) {

                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
                $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : 'No existe Unidad Educativa';

                $objOmitido = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                $query = $objOmitido->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                        ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $gestion)
                        ->distinct()
                        ->orderBy('iec.paraleloTipo', 'ASC')
                        ->getQuery();
                $infoOmitido = $query->getResult();

                foreach ($infoOmitido as $info) {
                    $paralelo[$info['paraleloTipo']] = $info['paralelo'];
                }
            } else {
                $nombreIE = 'Usuario No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = 'No existe Unidad Educativa';
        }
        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }

    /**
     * [findturnoAction description]
     * @param  [type] $paralelo [description]
     * @param  [type] $sie      [description]
     * @param  [type] $nivel    [description]
     * @param  [type] $grado    [description]
     * @return [type]           [description]
     */
    public function findturnoAction($paralelo, $sie, $nivel, $grado, $gestion) {
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
                ->setParameter('gestion', $gestion)
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



    /**
     * [doInscriptionAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function doInscriptionAction(Request $request){

        // return $this->render('SieRegularBundle:QAHistoryInscription:doInscription.html.twig', array(
        //         // ...
        //     ));    }
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');
        // dump($form);die;
        // $aDataStudent = unserialize($form['newdata']);
        // $aDataOption = json_decode($aDataStudent['dataOption'],true);

        try {

          //check if the user can do the inscription
          //validate allow access
            // $arrAllowAccessOption = array(7,8);
            // if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
            //   $defaultController = new DefaultCont();
            //   $defaultController->setContainer($this->container);
            //
            //   $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$form['gestion']));
            //   //validate if the user download the sie file
            //   if($swAccess){
            //     $message = 'No se puede realizar la inscripción debido a que ya descargo el archivo SIE';
            //     $this->addFlash('notiext', $message);
            //     return $this->redirect($this->generateUrl('inscriptionSpecialNew_index'));
            //   }
            // }

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivelId'],
                'gradoTipo' => $form['gradoId'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestion']
            ));
            // dump($objCurso->getId());die;
            //check if the student has inscription on this year, level, etc
            //condition var
            $arrConditionVar = array('estudiante'=>$form['idStudent'], 'institucioneducativaCurso'=>$objCurso->getId());

            $objStudentInscriptionSelected = $this->getCurrentInscriptionsByGestion($form['codigoRude'],$form['gestion']);

            if($objStudentInscriptionSelected){
              $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
              $form['idStudent'] = $objStudent->getId();
              $form['nivel'] = $form['nivelId'];
              $form['grado'] = $form['gradoId'];
              // get the student's history
              $inscriptions = $this->getCurrentInscriptionsStudent($form['codigoRude']);
              // set the message doesnt do inscription
              $message = 'Estudiante ya cuenta con Inscripcion seleccionada...';
              $this->addFlash('noInscriptionQA', $message);
              // this is to render the view page
                return $this->render('SieRegularBundle:QAHistoryInscription:index.html.twig', array(
                        'form'=>$this->createFormInscriptionQA($form)->createView(),
                        'datastudent'=> $objStudent,
                        'currentInscription' => $inscriptions,

                ));
            }


            // dump($objCurso);die;
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(74));
            $em->persist($studentInscription);
            // $em->flush();
            //add the areas to the student
            // $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(),$form['gestion']);

            // Try and commit the transaction
            // $em->getConnection()->commit();

            //if the number inscription is 1 we save ir
            if($form['nroInconsistencias']==1){

              $objValidacionProceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($form['idProceso']);
              $objValidacionProceso->setEsActivo('t');
              $em->persist($objValidacionProceso);
              // $em->flush();
              $message = 'Datos guardados correctamente... ';
              $typeMessage = 'yesInscriptionQA';
              $em->getConnection()->commit();
              $this->addFlash($typeMessage, $message);
              // return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
            }

            $message = 'Datos Registrados Correctamente';
            $this->addFlash('goodext', $message);
            //return $this->redirect($this->generateUrl('inscription_extranjeros_index'));

            return $this->forward('SieRegularBundle:RegularizarNotas:show',array(
              'idInscripcion'=>$studentInscription->getId()
              ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      // return $this->render('SieRegularBundle:InscriptionSpecialNew:doInscription.html.twig', array(
      //         // ...
      //     ));
      }


      /**
       * [getCurrentInscriptionsByGestion description]
       * @param  [type] $rude    [description]
       * @param  [type] $gestion [description]
       * @return [type]          [description]
       */
      private function getCurrentInscriptionsByGestion($rude, $gestion) {
      //$session = new Session();
          $swInscription = false;
          $em = $this->getDoctrine()->getManager();

          $entity = $em->getRepository('SieAppWebBundle:Estudiante');
          $query = $entity->createQueryBuilder('e')
                  ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                  ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                  ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                  ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                  ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                  ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                  ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                  ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                  ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                  ->where('e.codigoRude = :id')
                  ->andwhere('iec.gestionTipo = :gestion')
                  // ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                  ->setParameter('id', $rude)
                  ->setParameter('gestion', $gestion)
                  // ->setParameter('mat', array( 3,4,5,6 ))
                  ->orderBy('iec.gestionTipo', 'DESC')
                  ->getQuery();

              $objInfoInscription = $query->getResult();
              if(sizeof($objInfoInscription)>=1)
                return $objInfoInscription;
              else
                return false;

        }

        /**
         * to add the areas to the student
         * @param type $studentInscrId
         * @param type $newCursoId
         * @return boolean
         */
        private function addAreasToStudent($studentInscrId, $newCursoId, $gestion) {
            $em = $this->getDoctrine()->getManager();
            $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                'estudianteInscripcion' => $studentInscrId,
                'gestionTipo' => $gestion
            ));
            //if doesnt have areas we'll fill these
            if (!$areasEstudiante) {
                $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                    'insitucioneducativaCurso' => $newCursoId
                ));
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                $query->execute();
                foreach ($objAreas as $areas) {
                    //print_r($areas->getAsignaturaTipo()->getId());
                    $studentAsignatura = new EstudianteAsignatura();
                    $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                    $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                    $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                    $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                    $em->persist($studentAsignatura);
                    $em->flush();
                    //echo "<hr>";
                }
            }
            return true;
        }




    public function fillNotasAction()
    {
        return $this->render('SieRegularBundle:QAHistoryInscription:fillNotas.html.twig', array(
                // ...
            ));    }

}
