<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * ApoderadoEstudiante controller.
 *
 */
class ApoderadoEstudianteController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {


        // get DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the session's values
      $this->session = $request->getSession();
      $id_usuario = $this->session->get('userId');
      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      // return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array());
      // get the value to send
      // $form = $request->get('form');
      $form['sie']     = ($this->session->get('ie_id'))?$this->session->get('ie_id'):0;
      $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
      if($odataUedu){
        $arrResult = $this->mainInfo($form['sie']);

        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:index.html.twig', array(
                    'aInfoUnidadEductiva' => $arrResult['aInfoUnidadEductiva'],
                    'sie' => $arrResult['sie'],
                    'gestion' => $arrResult['gestion'],
                    'objUe' => $arrResult['objUe'],
                    'exist' => $arrResult['exist'],
                    'odataUedu' => $arrResult['odataUedu']
        ));
      }else{
        //is not UE user
        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:find.html.twig');
      }


    }

    public function findAction(Request $request){
      //get send values
      $form = $request->get('form');

      $em = $this->getDoctrine()->getManager();
      //get the session's values
      $this->session = $request->getSession();
      $id_usuario = $this->session->get('userId');
      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      // return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array());
      // get the value to send
      // $form = $request->get('form');
      // dump($this->session->get('ie_id'));die;
      $form['sie']     = $form['codigoSie'];
      $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
      if($odataUedu){
        $arrResult = $this->mainInfo($form['codigoSie']);

        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:index.html.twig', array(
                    'aInfoUnidadEductiva' => $arrResult['aInfoUnidadEductiva'],
                    'sie' => $arrResult['sie'],
                    'gestion' => $arrResult['gestion'],
                    'objUe' => $arrResult['objUe'],
                    'exist' => $arrResult['exist'],
                    'odataUedu' => $arrResult['odataUedu']
        ));
      }else{
        //is not UE user
        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:find.html.twig');
      }

    }

    private function mainInfo($sie){

      $form['sie']     = $sie;
      $form['gestion'] = $this->session->get('currentyear')-1;

        $em = $this->getDoctrine()->getManager();
        //find the levels from UE
        //levels gestion -1
        //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoUeducativaBySieGestion($form['sie'], $form['gestion']);

        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId']),
                    'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'])
                ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
            }
        } else {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        //$objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);die('krlossdfdfdfs');
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

        return array(
                    'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                    'sie' => $form['sie'],
                    'gestion' => $form['gestion'],
                    'objUe' => $objUe,
                    'exist' => $exist,
                    'odataUedu' => $odataUedu
                  );


    }



    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
    private function getCurrentInscriptionMinus($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo', 'IDENTITY(iec.nivelTipo) as nivelId, IDENTITY(iec.cicloTipo) as cicloId, IDENTITY(iec.gradoTipo) as gradoId' )
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '5')
                ->getQuery();

        $studentInscription = $query->getResult();
        return $studentInscription;
    }


    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'hidden', array('data' => $data))
                        ->getForm();
    }

    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
    private function gettheInscriptionStudent($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '4')
                ->getQuery();

        $studentInscription = $query->getResult();
        return $studentInscription;
    }

    public function seeApoderadosAction(Request $request) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');

        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

        //get next level data
        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $sie,
            'nivelTipo' => $nivel,
            'cicloTipo' => $ciclo,
            'gradoTipo' => $grado,
            'paraleloTipo' => $paralelo,
            'turnoTipo' => $turno,
            'gestionTipo' => $gestion
        ));

        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
            //get students list
            $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        //get apoderados
        $objApoderados = $em->getRepository('SieAppWebBundle:Estudiante')->getApoderadoPerCourse($aInfoUeducativa['ueducativaInfoId']['iecId']);
        //dump($objApoderados);die;

        //$odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);

        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:seeApoderados.html.twig', array(
                    'apoderados' => $objApoderados,
                    'objStudents' => $objStudents,
                    'sie' => $sie,
                    'turno' => $turno,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'paralelo' => $paralelo,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'infoUe' => $infoUe,
                    'exist' => $exist,
                    'itemsUe'=>$itemsUe,
                    'ciclo'=>$ciclo


        ));
    }


    /**
     * get the children
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return children
     */
    public function seeChildrenAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();

      //get the data send about apoderado and curso
        $jsnInfoApoderado = $request->get('jsnInfoApoderado');
        $arrInfoApoderado = json_decode($jsnInfoApoderado,true);
        //$infoStudent = $request->get('infoStudent');
        //get data about persona
        $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($arrInfoApoderado['persId']);
        //dump($objPersona);die;
        //get children
        $objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->findChildrenByCourse($arrInfoApoderado['persId'], $this->session->get('currentyear')-1);
        //dump($objChildren);die;
        return $this->render('SieHerramientaBundle:ApoderadoEstudiante:seechildren.html.twig', array(
          'form'             => $this->findStudentForm($jsnInfoApoderado)->createView(),
          'persona'          => $objPersona,
          'children'         => $objChildren,
          'jsnInfoApoderado' => $jsnInfoApoderado
        ));

    }
    /**
    * create the form to find the student by rude
    **/
    private function findStudentForm($data){
      $form = $this->createFormBuilder()
              //->add('rudeal','text', array('label'=>'Rude', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Rude', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('find', 'button', array('label'=> 'Confirmar Revisión', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'validateChildren()')))
              ->getForm();
      return $form;
    }

    public function validateChildrenAction(Request $request){
      //get the data send
      $form = $request->get('form');
      $jsnInfoApoderado = $form['data'];
      $arrInfoApoderado=json_decode( $form['data'],true);

      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the data send about apoderado and curso
        //$arrInfoApoderado = json_decode($jsnInfoApoderado,true);
        //$infoStudent = $request->get('infoStudent');
        //get data about persona
        $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($arrInfoApoderado['persId']);
        //get children
        $objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->findChildrenByCourse($arrInfoApoderado['persId'], $this->session->get('currentyear'));
        try {
          //updte validation apoderado inscription with value 2
          foreach ($objChildren as $key => $value) {
            # code todo the update...
            $objApoderadoInscriptionUpdate = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($value['apoId']);
            $objApoderadoInscriptionUpdate->setEsValidado(2);
            $em->persist($objApoderadoInscriptionUpdate);
            $em->flush();
          }
          $em->getConnection()->commit();
        } catch (Exception $e) {
          echo 'error '.$e;
        }
        $message = 'Validación realizada con exito...';
        $this->addFlash('successValidate', $message);
        //dump($objChildren);die;
        $em->getConnection()->close();
        return $this->render('SieHerramientaBundle:ApoderadoEstudiante:seechildren.html.twig', array(
          'form'             => $this->findStudentForm($jsnInfoApoderado)->createView(),
          'persona'          => $objPersona,
          'children'         => $objChildren,
          'jsnInfoApoderado' => $jsnInfoApoderado
        ));

    }





    public function addChildrenAction(Request $request){
      //get data send
      $jsnInfoApoderado = $request->get('jsnInfoApoderado');
      //dump($request->get('jsnInfoApoderado'));die;
      return $this->render('SieHerramientaBundle:ApoderadoEstudiante:addchildren.html.twig', array(
        'form'=>$this->addChildrenForm($jsnInfoApoderado)->createView()
      ));
    }

    private function addChildrenForm($jsnInfoApoderado){
      return $this->createFormBuilder()
            ->add('jsnInfoApoderado', 'hidden', array('data'=>$jsnInfoApoderado))
            ->add('rude', 'text', array('label'=>'Código Rude', 'attr'=>array('class'=>'form-control')))
            ->add('findchild', 'button', array('label'=>'Buscar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'findChild()')))
            ->getForm();
    }

    public function findChildrenAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      //get data send
      $dataSend = $request->get('form');
      //dump($dataSend['jsnInfoApoderado']);die;
      //set values to render on twig
      $objStuden = array();
      $objApoderadoInfo = array();
      $objApodero = array();
      $objChild = array();
      //look for the student by rude
      $objStuden = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
        'codigoRude'=>$dataSend['rude']
      ));
      //check if the student is valid
      $swExist = true;
      $swApoderado = true;
      $message = '';
      if($objStuden){
        // Student exist
        //check if the student has inscription
        $objChild = $em->getRepository('SieAppWebBundle:Estudiante')->findIdChildrenInscription($dataSend['rude'],$this->session->get('currentyear'));
        //dump($objChild);die;
        //check if the studen has inscription on current year
        if($objChild){
          //look if the student has an apoderado
          $objApoderadoInfo = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
            'estudianteInscripcion' => $objChild[0]['studentInscId']
          ));
          //check if the sudent has apoderado
          if($objApoderadoInfo){
            //student has apoderado, get the apoderado data
            $objApodero = $em->getRepository('SieAppWebBundle:Persona')->find($objApoderadoInfo->getPersona());
            //dump($objApodero->getPaterno());die;
          }else{
            //no apoderado inscription
            $swApoderado = false;
            //$messageApoderado = 'Estudiante Sin Apoderado';
          }
          //dump($objApoderadoInfo->getPersona());
        }else{
          //no inscription on current year
          $swExist = false;
          $message = 'Estudiante no presenta inscripción para la gestión '. $this->session->get('currentyear');
        }

      }else{
        //no student
        $swExist = false;
          $message = 'Código Rude no Existe';
      }
      $this->addFlash('errorApoderado', $message);
      //$this->addFlash('withoutApoderado', $messageApoderado);
      if($objChild){
        $dataSend['inscription']=$objChild[0]['studentInscId'];
      }


      return $this->render('SieHerramientaBundle:ApoderadoEstudiante:findchildren.html.twig',array(
        'estudiante' => $objStuden,
        'apoderado'  => $objApodero,
        'swExist'  => $swExist,
        'swApoderado' => $swApoderado,
        'formAddchild' => $this->findChildForm($dataSend)->createView()


      ));
    }

    private function findChildForm($data){
      //dump($data);die;
      $jsnInfoApoderado = $data['jsnInfoApoderado'];
      return $this->createFormBuilder()
              ->add('dataAddChild', 'hidden', array('data'=>serialize($data)))
              ->add('addchild', 'button', array('label'=>'adicionar Estudiante', 'attr'=>array('class'=>'btn btn-inverse', 'onclick'=>"addchilApoderado('$jsnInfoApoderado')")))
              ->getForm();
    }

    /**
     * get the children and save relation
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return children
     */
    public function seeSaveChildrenAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get data send
      $infoSend = ($request->get('form'));

      $dataSend = unserialize($infoSend['dataAddChild']);
//dump($dataSend['inscription']);die;
      //get the data send about apoderado and curso
        $jsnInfoApoderado = $dataSend['jsnInfoApoderado'];
        $arrInfoApoderado = json_decode($jsnInfoApoderado,true);

        try {
          //save apoderado inscription
          $objApoderadoInscription = new ApoderadoInscripcion();
          $objApoderadoInscription->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find(1));
          $objApoderadoInscription->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($arrInfoApoderado['persId']));
          $objApoderadoInscription->setEstudianteInscripcionId($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dataSend['inscription'])->getId());
          $objApoderadoInscription->setEsValidado(2);
          $em->persist($objApoderadoInscription);
          $em->flush();
          $em->getConnection()->commit();

        } catch (Exception $e) {
          $em->getConnection()->rollback();
          return $ex;
        }

        //dump($arrInfoApoderado);die;
        //$infoStudent = $request->get('infoStudent');
        //get data about persona
        $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($arrInfoApoderado['persId']);
        //dump($objPersona);die;
        //get children
        $objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->findChildrenByCourse($arrInfoApoderado['persId'], $this->session->get('currentyear'));
        //dump($objChildren);die;
        return $this->render('SieHerramientaBundle:ApoderadoEstudiante:seechildren.html.twig', array(
          'form'             => $this->findStudentForm($jsnInfoApoderado)->createView(),
          'persona'          => $objPersona,
          'children'         => $objChildren,
          'jsnInfoApoderado' => $jsnInfoApoderado
        ));

    }


    /**
     * remove the relation beetwen
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return children
     */
    public function removeChildrenAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get data send
      $idApoIns = $request->get('idApoIns');
//dump($idApoIns);die;
      //get the data send about apoderado and curso
        $jsnInfoApoderado = $request->get('jsnInfoApoderado');
        $arrInfoApoderado = json_decode($jsnInfoApoderado,true);

        try {
          //remove the relation apoderado inscription
          $objApoderadoInscription = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoIns);
          $em->remove($objApoderadoInscription);
          $em->flush();
          $em->getConnection()->commit();

        } catch (Exception $e) {
          $em->getConnection()->rollback();
          return $ex;
        }

        //dump($arrInfoApoderado);die;
        //$infoStudent = $request->get('infoStudent');
        //get data about persona
        $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($arrInfoApoderado['persId']);
        //dump($objPersona);die;
        //get children
        $objChildren = $em->getRepository('SieAppWebBundle:Estudiante')->findChildrenByCourse($arrInfoApoderado['persId'], $this->session->get('currentyear'));
        //dump($objChildren);die;
        return $this->render('SieHerramientaBundle:ApoderadoEstudiante:seechildren.html.twig', array(
          'form'             => $this->findStudentForm($jsnInfoApoderado)->createView(),
          'persona'          => $objPersona,
          'children'         => $objChildren,
          'jsnInfoApoderado' => $jsnInfoApoderado
        ));

    }






    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * obtiene el nivel, ciclo y grado
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return nivel, ciclo grado del estudiante
     */
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
        //get the array of courses
        $cursos = $this->aCursos;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }

    /**
     * build the cursos in a array
     * @param type $limitA
     * @param type $limitB
     * @param type $limitC
     * return array with the courses
     */
    private function fillCursos() {
        $this->aCursos = array(
            ('11-1-1'),
            ('11-1-2'),
            ('12-1-1'),
            ('12-1-2'),
            ('12-1-3'),
            ('12-2-4'),
            ('12-2-5'),
            ('12-2-6'),
            ('13-1-1'),
            ('13-1-2'),
            ('13-2-3'),
            ('13-2-4'),
            ('13-3-5'),
            ('13-3-6')
        );
        return($this->aCursos);
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:open.html.twig', array());
    }

    /**
     * get the turnos UE
     * @param Request $request
     * @return array with turnos UE
     */
    public function getTurnosAction(Request $request) {
        //get the values
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $nivelname = $request->get('nivelname');
        $typeInscription = $request->get('typeInscription');

        $em = $this->getDoctrine()->getManager();
        //get turnos
        $objturnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnosBySieAndGestion($sie, $nivel, $gestion);

        $exist = true;
        //check if the data exist
        if (!$objturnos) {
            $message = 'Código SIE no existe';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }
        return $this->render($this->session->get('pathSystem') . ':ApoderadoEstudiante:turnos.html.twig', array(
                    'objturnos' => $objturnos,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'nivelname' => $nivelname,
                    'typeInscription' => $typeInscription,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist
        ));
    }

}
