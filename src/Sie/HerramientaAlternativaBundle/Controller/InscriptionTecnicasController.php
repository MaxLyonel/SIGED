<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

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
use Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * InscriptionTecnicas controller.
 *
 */
class InscriptionTecnicasController extends Controller {

    public $session;
    public $idInstitucion;
    public $aCursosRegular;
    public $aCursosAlternativa;
    public $aCursosAlternativaTrue;
    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursosRegular = $this->fillCursosRegular();
        $this->aCursosAlternativa = $this->fillCursosAlternativa();
        $this->aCursosAlternativaTrue = $this->fillCursosAlternativaTrue();
    }

    /**
     * build the cursos in a array
     * @param type $limitA
     * return array with the courses
     */
    private function fillCursosRegular() {
        $this->aCursosRegular = array(
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
        return($this->aCursosRegular);
    }
    /**
     * build the cursos Alternativa in a array
     * @param type $limitA
     * return array with the courses
     */
    private function fillCursosAlternativa() {
        $this->aCursosAlternativa = array(
            ('11-1-1'),
            ('11-1-2'),
            ('15-1-1'),
            ('15-1-1'),
            ('15-1-1'),
            ('15-2-2'),
            ('15-2-2'),
            ('15-2-2'),
            ('15-1-1'),
            ('15-1-1'),
            ('15-2-2'),
            ('15-2-2'),
            ('15-3-3'),
            ('15-3-3')
        );
        return($this->aCursosAlternativa);
    }
    /**
     * build the cursos Alternativa in a array
     * @param type $limitA
     * return array with the courses
     */
    private function fillCursosAlternativaTrue() {
        $this->aCursosAlternativaTrue = array(
            ('15-1-1'),
            ('15-1-2'),
            ('15-2-1'),
            ('15-2-2'),
            ('15-2-3')
        );
        return($this->aCursosAlternativaTrue);
    }
    /**
     * create the view to set the student's rudeal
     *
     */
    public function indexAction(Request $request) {
        //create the db connexion
        $em=$this->getDoctrine()->getManager();
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //draw the form
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:index.html.twig', array(
                    'form' => $this->formSelectedInscription()->createView(),
        ));
    }
    /**
    *build the chosen incription humanistica
    **/
    private function formSelectedInscription(){
      //option to do the inscription
      $arrInscriptions = array('Excepcionales', 'Observados', 'Omitidos');
      $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('alternativa_inscriptionTecnicas_goinscription'))
            ->add('inscription', 'choice', array('label'=>'Inscripcion', 'choices'=>$arrInscriptions, 'attr'=>array('class'=>'form-control')))
            ->add('buscar', 'submit', array('label' => 'Continuar', 'attr'=>array('class'=>'btn btn-success')))
            ->getForm();
      return $form;
    }

    public function goInscriptionAction(Request $request){

      //create the db connexion
      $em=$this->getDoctrine()->getManager();
      //get the session's values
      $this->session = $request->getSession();
      $id_usuario = $this->session->get('userId');
      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      //get the values to send, the type of inscription
      $form = $request->get('form');
      $arrInscriptions = array('Excepcionales', 'Observados', 'Omitidos');
      //draw the form
      return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:inscription.html.twig', array(
                  'form' => $this->formSearchStudent($form)->createView(),
                  'typeInscription'=>$arrInscriptions[$form['inscription']]
      ));
    }
    /**
    * build the find student form
    */
    private function formSearchStudent($data){
      $form = $this->createFormBuilder()
              ->setAction($this->generateUrl('inscription_omitidos_result'))
              ->add('codigoRude', 'text', array('label' => 'Rudeal', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestión', 'choices' => array('2015' => '2015', '2014' => '2014'), 'attr' => array('class' => 'form-control')))
              ->add('buscar', 'button', array('label' => 'Buscar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'searchStudentInscriptions()')))
              ->add('inscription', 'hidden', array('label' => 'inscription', 'data'=>$data['inscription'] ))
              ->getForm();
      return $form;
    }

    /**
    *get the student's inscriptions
    */
    public function resultAction(Request $request){
      //create the connexion
      $em=$this->getDoctrine()->getManager();
      //get the send values
      $form = $request->get('form');

      //get the student info
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
      $exist = true;
      //check if the student exists
      if($objStudent){
        //get the student's inscriptions
        $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());
        //$objStudentInscriptionsHumanisticas = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudentHum($objStudent->getId());

        //che if the student has inscriptions
        if($objStudentInscriptions){
          //get the last course with next level status
          /*reset($objStudentInscriptions);
          $sw=true;
          while($sw && $val = current($objStudentInscriptions)){
            if ($val['estadoMatriculaId'] == '5' || $val['estadoMatriculaId'] == '37' || $val['estadoMatriculaId'] == '56' || $val['estadoMatriculaId'] == '57' || $val['estadoMatriculaId'] == '58') {
                $arrDataInscription = $val;
                $sw=false;
            }else{
              $arrDataInscription = $objStudentInscriptions[0];
            }
            next($objStudentInscriptions);
          }


          switch ($arrDataInscription['nivelId']) {
            case '12':
            case '13':
              $currentLevel = $this->getCurrentLevel($arrDataInscription);
              $currentLevelStudent = $this->aCursosAlternativa[$currentLevel];
              break;
            case '15':
              $currentLevel = $this->getAlternativaLevel($arrDataInscription);
              $currentLevelStudent = $this->aCursosAlternativaTrue[$currentLevel];
              break;
            case '15':
              $currentLevel = $this->getAlternativaLevel($arrDataInscription);
              $currentLevelStudent = $this->aCursosAlternativaTrue[$currentLevel];
            default:dump($arrDataInscription);
              # code...
              $currentLevelStudent='is other';
              break;
          }*/
          //dump($objStudentInscriptions);die;
          //dump($objStudent);
          //dump($form);
          //draw the result information
          $currentLevelStudent='';
          $form['idStudent']=$objStudent->getId();
          return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:result.html.twig', array(
                      'objStudent' => $objStudent,
                      'objStudentInscriptions' => $objStudentInscriptions,
                      'form'=>$this->createFormInscription($form, $objStudentInscriptions, $currentLevelStudent)->createView(),
                      'exist' => $exist,
          ));
        }else{
          $message = 'Estudiante no tiene historial de inscripcion';
          $this->addFlash('warinscription', $message);
          $exist = false;
          return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:result.html.twig', array(
                      'exist' => $exist,
          ));
        }
      }else{
        $message = 'Estudiante no existe';
        $this->addFlash('warinscription', $message);
        $exist = false;
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:result.html.twig', array(
                    'exist' => $exist,
        ));
      }

    }
    /**
    *build the form to do the inscription
    */
    private function createFormInscription($data, $objStudentInscriptions, $currentLevelStudent){

      $em = $this->getDoctrine()->getManager();

      /*$arrCurrentLevel = explode('-', $currentLevelStudent);
      list($nivelId, $cicloId, $gradoId)=$arrCurrentLevel;
      //$aInscrip = explode('-', $aInscrip);
      $nivel = $em->getRepository('SieAppWebBundle:NivelTIpo')->find($nivelId);
      $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoId);
      */

      return $form = $this->createFormBuilder()
          ->setAction($this->generateUrl('alternativa_inscriptionHumanisticas_saveExcepcionales'))
          ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
          ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
          ->add('subcea', 'choice', array('label'=>'Sub CEA','attr' => array('required' => true, 'class' => 'form-control')))
          ->add('semestre', 'choice', array('mapped' => false, 'label' => 'Semestre', 'choices' => array(''=>'Seleccionar','1' => '1', '2' => '2'), 'attr' => array('class' => 'form-control')))
          //->add('nivel', 'text', array('data' => '', 'disabled' => true, 'attr' => array('class' => 'form-control')))
          //->add('grado', 'text', array('data' => '', 'disabled' => true, 'attr' => array('class' => 'form-control')))
          ->add('nivel', 'choice', array('label'=>'Nivel','attr' => array('required' => true, 'class' => 'form-control')))
          ->add('grado', 'choice', array('label'=>'Grado','attr' => array('required' => true, 'class' => 'form-control')))
          ->add('nivelId', 'hidden', array('mapped' => false, 'data' => ''))
          ->add('cicloId', 'hidden', array('mapped' => false, 'data' => ''))
          ->add('gradoId', 'hidden', array('mapped' => false, 'data' => ''))
          //->add('lastue', 'hidden', array('mapped' => false, 'data' => $lastue))
          ->add('rude', 'hidden', array('mapped' => false, 'data' => $data['codigoRude']))
          ->add('gestion', 'hidden', array('mapped' => false, 'data' => $data['gestion']))
          ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $data['idStudent']))
          ->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
          ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
          ->add('save', 'submit', array('label' => 'Guardar', 'attr'=>array('required'=>true, 'class'=>'btn btn btn-success')))
          ->add('inscription', 'hidden', array('label' => 'inscription', 'data'=>$data['inscription'] ))
          ->getForm();
    }
    /**
    *get the current level if the inscription is from regualar
    */
    private function getCurrentLevel($data){
      //convert some values in DB
      if ($data['gradoId']==101)$data['gradoId']=1;
      if ($data['gradoId']==102)$data['gradoId']=2;
      if ($data['gradoId']==103)$data['gradoId']=3;
      //get the student's level
      $levelStudent = $data['nivelId'].'-'.$data['cicloId'].'-'.$data['gradoId'];
      //look for current level on the regular array
      $sw=true;
      reset($this->aCursosRegular);
      while($sw && $level = current($this->aCursosRegular)){
        //if the levele is igual get the key of regular array
        if($levelStudent == $level){
          $key = key($this->aCursosRegular);
          $sw=false;
        }
        next($this->aCursosRegular);
      }
      $key = (($data['estadoMatriculaId'] == '5' || $data['estadoMatriculaId'] == '37' || $data['estadoMatriculaId'] == '56' || $data['estadoMatriculaId'] == '57' || $data['estadoMatriculaId'] == '58'))?$key+1:$key;
      //return the key more one
      return ($key);
    }
    /**
    *get the current level if the inscription is from alternativa
    */
    private function getAlternativaLevel($data){
      //try with other equivalencia
      if ($data['gradoId']==101)$data['gradoId']=1;
      if ($data['gradoId']==102)$data['gradoId']=2;
      if ($data['gradoId']==103)$data['gradoId']=3;
      //get the student's level
      $levelStudent = $data['nivelId'].'-'.$data['cicloId'].'-'.$data['gradoId'];
      //look for current level on the regular array
      $sw=true;
      reset($this->aCursosRegular);
      while($sw && $level = current($this->aCursosAlternativaTrue)){
        //if the levele is igual get the key of regular array
        if($levelStudent == $level){
          $key = key($this->aCursosAlternativaTrue);
          $sw=false;
        }
        next($this->aCursosAlternativaTrue);
      }
      $key = (($data['estadoMatriculaId'] == '5' || $data['estadoMatriculaId'] == '37' || $data['estadoMatriculaId'] == '56' || $data['estadoMatriculaId'] == '57' || $data['estadoMatriculaId'] == '58'))?$key+1:$key;
      //return the key more one
      return ($key);
    }
    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function InfoStudentForm($goToPath, $nextButton, $data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $data['id']))//81880091
                        ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-link')))
                        ->getForm()
        ;
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
     public function findIEAction($id, $gestion) {

         $em = $this->getDoctrine()->getManager();
         //get the tuicion
         //select * from get_ue_tuicion(137746,82480002)
         $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
         $query->bindValue(':user_id', $this->session->get('userId'));
         $query->bindValue(':sie', $id);
         $query->bindValue(':roluser', $this->session->get('roluser'));
         $query->execute();
         $aTuicion = $query->fetchAll();
         $arrSubCea = array();
         $paralelo = array();
         $turno = array();
         $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
         if ($ue) {
             if (!$aTuicion[0]['get_ue_tuicion']) {
                     $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
                     $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : 'No existe Unidad Educativa';
                     //get the subceas
                     /*$objQuerySuc = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
                     $query = $objQuerySuc->createQueryBuilder('ies')
                             ->select('ies.id as subCeaId, IDENTITY(ies.sucursalTipo) as sucursalTipo')
                             ->where('ies.institucioneducativa = :id')
                             ->andwhere('ies.gestionTipo = :gestion')
                             ->setParameter('id', $id)
                             ->setParameter('gestion', $gestion)
                             ->distinct()
                             ->orderBy('ies.sucursalTipo', 'ASC')
                             ->getQuery();*/
                     $objQuerySuc = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                     $query = $objQuerySuc->createQueryBuilder('iec')
                             ->select('iec.id, IDENTITY(iec.sucursalTipo) as sucursalTipo')
                             ->where('iec.institucioneducativa = :id')
                             ->andwhere('iec.gestionTipo = :gestion')
                             ->setParameter('id', $id)
                             ->setParameter('gestion', $gestion)
                             ->distinct()
                             ->orderBy('iec.sucursalTipo', 'ASC')
                             ->getQuery();

                     $infoQuerySuc = $query->getResult();

                     foreach ($infoQuerySuc as $info) {
                         $arrSubCea[$info['sucursalTipo']] = $info['sucursalTipo'];
                     }



             } else {
                 $nombreIE = 'Usuario No tiene Tuición sobre la Unidad Educativa';
             }
         } else {
             $nombreIE = 'No existe Unidad Educativa';
         }
         $response = new JsonResponse();

         return $response->setData(array('nombre' => $nombreIE, 'arrsubcea'=>$arrSubCea));
     }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findCicloAction(Request $request, $sie, $subcea, $semestre, $gestion) {
//        dump("$sie, $gestion, $facultad");

        $em = $this->getDoctrine()->getManager();
        //get ciclos
        $arrCiclos= array();
        $arrDefCiclos = array('1'=>'Primaria', '2'=>'Seccundaria');

        $objQuerySuc = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                    $query = $objQuerySuc->createQueryBuilder('i5')
                            ->select(' n2.id AS id3, n2.nivel AS nivel7, c0.id AS id0, c0.ciclo AS ciclo1')
                            ->innerjoin('SieAppWebBundle:TurnoTipo', 't1', 'WITH',    'i5.turnoTipo = t1.id')
                            ->innerjoin('SieAppWebBundle:NivelTipo', 'n2', 'WITH',    'i5.nivelTipo = n2.id')
                            ->innerjoin('SieAppWebBundle:CicloTipo', 'c0', 'WITH',    'i5.cicloTipo = c0.id')
                            ->innerjoin('SieAppWebBundle:GradoTipo', 'g3', 'WITH',    'i5.gradoTipo = g3.id')
                            ->innerjoin('SieAppWebBundle:ParaleloTipo', 'p4', 'WITH', 'i5.paraleloTipo = p4.id')
                            ->where('i5.institucioneducativa = :id')
                            ->andwhere('i5.gestionTipo = :gestion')
                            ->andWhere('n2.id IN (:codigo)')
                            ->setParameter('id', $sie)
                            ->setParameter('gestion', $gestion)
                            ->setParameter('codigo', array('18','19','20','21','22','23','24','25'))
                            ->distinct()
                            ->getQuery();
                    $infoQuery = $query->getResult();


        foreach ($infoQuery as $info) {
            $arrInfo[$info['id0']] = $info['ciclo1'];
        }

        $response = new JsonResponse();
        return $response->setData(array('aciclos' => $arrInfo));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findGradoAction(Request $request, $sie, $subcea, $semestre, $gestion) {
//        dump("$sie, $gestion, $facultad");

        $em = $this->getDoctrine()->getManager();
        //get ciclos
        $arrCiclos= array();
        $arrDefCiclos = array('1'=>'Primaria', '2'=>'Seccundaria');

        $objQuerySuc = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                    $query = $objQuerySuc->createQueryBuilder('i5')
                            ->select(' g3.id, g3.grado AS grado8 ')
                            ->innerjoin('SieAppWebBundle:TurnoTipo', 't1', 'WITH',    'i5.turnoTipo = t1.id')
                            ->innerjoin('SieAppWebBundle:NivelTipo', 'n2', 'WITH',    'i5.nivelTipo = n2.id')
                            ->innerjoin('SieAppWebBundle:CicloTipo', 'c0', 'WITH',    'i5.cicloTipo = c0.id')
                            ->innerjoin('SieAppWebBundle:GradoTipo', 'g3', 'WITH',    'i5.gradoTipo = g3.id')
                            ->innerjoin('SieAppWebBundle:ParaleloTipo', 'p4', 'WITH', 'i5.paraleloTipo = p4.id')
                            ->where('i5.institucioneducativa = :id')
                            ->andwhere('i5.gestionTipo = :gestion')
                            ->andWhere('n2.id IN (:codigo)')
                            ->setParameter('id', $sie)
                            ->setParameter('gestion', $gestion)
                            ->setParameter('codigo', array('18','19','20','21','22','23','24','25'))
                            ->distinct()
                            ->getQuery();
                    $infoQuery = $query->getResult();

        foreach ($infoQuery as $info) {
            $arrInfo[$info['id']] = $info['grado8'];
        }

        $response = new JsonResponse();
        return $response->setData(array('agrados' => $arrInfo));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findModuloAction(Request $request, $sie, $gestion, $facultad, $especialidad) {
        //dump("$sie, $gestion, $facultad, $especialidad");die;

        $em = $this->getDoctrine()->getManager();
        //get ciclos
        $arrCiclos= array();
        $arrDefCiclos = array('1'=>'Primaria', '2'=>'Seccundaria');

        $objQuerySuc = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo');
        $query = $objQuerySuc->createQueryBuilder('a')
                ->select(' g.id, g.modulo')
                ->innerjoin('SieAppWebBundle:SuperiorFacultadAreaTipo', 'b', 'WITH', 'a.superiorFacultadAreaTipo = b.id')
                ->innerjoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'c', 'WITH', 'c.superiorEspecialidadTipo = a.id')
                ->innerjoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'd', 'WITH', 'd.acreditacionEspecialidad = c.id')

                ->innerjoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'e', 'WITH', 'e.superiorInstitucioneducativaAcreditacion = d.id')
                ->innerjoin('SieAppWebBundle:SuperiorModuloPeriodo', 'f', 'WITH', 'f.institucioneducativaPeriodo = e.id')
                ->innerjoin('SieAppWebBundle:SuperiorModuloTipo', 'g', 'WITH', 'f.superiorModuloTipo = g.id')

                ->where('d.institucioneducativa = :id')
                ->andwhere('d.gestionTipo = :gestion')
                ->andWhere('b.codigo IN (:codigo)')
                ->andwhere('b.id = :facultad')
                ->andWhere('a.id = :especialidad')
                ->setParameter('id', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('codigo', array('18','19','20','21','22','23','24','25'))
                ->setParameter('facultad', $facultad)
                ->setParameter('especialidad', $especialidad)
                ->distinct()
                ->orderBy('g.id', 'ASC')
                ->getQuery();

        $infoQuery = $query->getResult();

        foreach ($infoQuery as $info) {
            $arrInfo[$info['id']] = $info['modulo'];
        }

        $response = new JsonResponse();
        return $response->setData(array('amodulos' => $arrInfo));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findAreaSuperiorAction(Request $request, $sie, $gestion, $facultad, $especialidad, $modulo) {
        //dump("$sie, $gestion, $facultad, $especialidad, $modulo");die;

        $em = $this->getDoctrine()->getManager();
        //get ciclos
        $arrCiclos= array();
        $arrDefCiclos = array('1'=>'Primaria', '2'=>'Seccundaria');

        $objQuerySuc = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo');
        $query = $objQuerySuc->createQueryBuilder('a')
                ->select(' h.id,  h.areaSuperior')
                ->innerjoin('SieAppWebBundle:SuperiorFacultadAreaTipo', 'b', 'WITH', 'a.superiorFacultadAreaTipo = b.id')
                ->innerjoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'c', 'WITH', 'c.superiorEspecialidadTipo = a.id')
                ->innerjoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'd', 'WITH', 'd.acreditacionEspecialidad = c.id')

                ->innerjoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'e', 'WITH', 'e.superiorInstitucioneducativaAcreditacion = d.id')
                ->innerjoin('SieAppWebBundle:SuperiorModuloPeriodo', 'f', 'WITH', 'f.institucioneducativaPeriodo = e.id')
                ->innerjoin('SieAppWebBundle:SuperiorModuloTipo', 'g', 'WITH', 'f.superiorModuloTipo = g.id')
                ->innerjoin('SieAppWebBundle:SuperiorAreaSaberesTipo', 'h', 'WITH', 'g.superiorAreaSaberesTipo = h.id')

                ->where('d.institucioneducativa = :id')
                ->andwhere('d.gestionTipo = :gestion')
                ->andWhere('b.codigo IN (:codigo)')
                ->andwhere('b.id = :facultad')
                ->andWhere('a.id = :especialidad')
                ->andWhere('g.id = :modulo')
                ->setParameter('id', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('codigo', array('18','19','20','21','22','23','24','25'))
                ->setParameter('facultad', $facultad)
                ->setParameter('especialidad', $especialidad)
                ->setParameter('modulo', $modulo)
                ->distinct()
                ->orderBy('h.id', 'ASC')
                ->getQuery();

        $infoQuery = $query->getResult();

        foreach ($infoQuery as $info) {
            $arrInfo[$info['id']] = $info['areaSuperior'];
        }
        $response = new JsonResponse();
        return $response->setData(array('aareas' => $arrInfo));
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function saveTecnicosAction(Request $request){

      try {
          //create conexion on DB
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();
          //get the variblees
          $form = $request->get('form');
dump($form);die;
          //here the validation with kind of inscription
          //to do the validation througth the kind of inscription
          //0 => 'Excepcionales', 1=> 'Observados', 2=>'Omitidos';
          switch ($form['inscription']) {
            case '0':
              # code...
              break;
            case '1':
              # code...
              break;
            case '2':
              # code...
              break;
            default:
              # code...
              break;
          }

          //get the id of next course
          $newCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
              'institucioneducativa' => $form['institucionEducativa'],
              'nivelTipo' => '15',
              'cicloTipo' => $form['nivel'],
              'gradoTipo' => $form['grado'],
              'paraleloTipo' => $form ['paralelo'],
              'turnoTipo' => $form ['turno'],
              'gestionTipo' => $form['gestion']
          ));

//          dump($form);
//          dump($newCurso);
//          die;

          //get info old about inscription
          //$currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));

          //insert a new record with the new selected variables and put matriculaFinID like 5
          $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
          $query->execute();
          $studentInscription = new EstudianteInscripcion();
          $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
          $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
          $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
          $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
          $studentInscription->setCodUeProcedenciaId($form['institucionEducativa']);
          $studentInscription->setObservacion(1);
          $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
          $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
          $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($newCurso->getId()));
          //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
          $studentInscription->setCodUeProcedenciaId(0);
          $em->persist($studentInscription);
          $em->flush();

          //add the areas to the student
          //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $newCurso->getId());

          //$this->session->getFlashBag()->add('goodomi', 'Se realizo la inscripción del Estudiante satisfactoriamente ');
          //do the commit in DB
          $em->getConnection()->commit();
          $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito.');
          return $this->redirect($this->generateUrl('alternativa_inscriptionTecnicas_index'));
      } catch (Exception $ex) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId) {
      /*
      select *
      from superior_institucioneducativa_acreditacion sia
      left join superior_institucioneducativa_periodo sip on (sia.id = sip.institucioneducativa_acreditacion_id)
      left join superior_modulo_periodo smp on (sip.id = smp.institucioneducativa_periodo_id)
      left join superior_modulo_tipo smt on (smp.modulo_tipo_id = smt.id)
      left join superior_periodo_tipo spt on (sip.periodo_superior_tipo_id = spt.id)
      where sia.institucioneducativa_id='40730506'
      and sia.gestion_tipo_id='2015'
      and sia.institucioneducativa_sucursal_id='560444'
      and spt.id = 0
      */
        return true;
    }

    /*
    * option inscription without rude
    */
    public function withoutRudeExcepcionalAction(Request $request){
      return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:withoutrude.html.twig', array(
                  'form' => $this->formnorude()->createView(),
      ));
    }

    /*
    * form without rude
    */
    private function formnorude() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('alternativa_inscriptionExcepcionales_searchstudents'))
                        ->add('pais', 'entity', array('label' => 'País', 'class' => 'SieAppWebBundle:PaisTipo', 'attr' => array('class' => 'form-control')))
                        ->add('ueprocedencia', 'text', array('label' => 'Unidad Educativa Procedencia', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,85}')))
                        ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array('2015' => '2015', '2014' => '2014'), 'attr' => array('class' => 'form-control')))
                        ->add('ci', 'text', array('required' => false, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,8}', 'required' => false)))
                        ->add('complemento', 'text', array('required' => false, 'label' => 'Complemento', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9 a-z A-Z]{1,2}', 'maxlength' => '2', 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                        ->add('paterno', 'text', array('required' => false, 'label' => 'Paterno', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('materno', 'text', array('required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('nombre', 'text', array('attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('fnac', 'text', array('label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                        ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Buscar'))
                        ->getForm();
    }
    /*
    *search stundents by name, paterno, materno, etc
    */
    public function searchStudentsAction (Request $request){
      $em = $this->getDoctrine()->getManager();
      $form = $request->get('form');

      //encontramos las coincidencias
      $sameStudents = $this->getCoincidenciasStudent($form);

      return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:resultnorude.html.twig', array(
                  'newstudent' => $form,
                  'samestudents' => $sameStudents,
                  'formninguno' => $this->nobodyform($form)->createView()
      ));
    }
    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('alternativa_inscriptionExcepcionales_newexcepcional'))
                        ->add('paterno', 'hidden', array('data' => strtoupper($data['paterno']), 'required' => false))
                        ->add('materno', 'hidden', array('data' => strtoupper($data['materno']), 'required' => false))
                        ->add('nombre', 'hidden', array('data' => strtoupper($data['nombre'])))
                        ->add('fnac', 'hidden', array('data' => $data['fnac']))
                        ->add('ci', 'hidden', array('data' => $data['ci']))
                        ->add('complemento', 'hidden', array('data' => $data['complemento']))
                        ->add('pais', 'hidden', array('data' => $data['pais']))
                        ->add('ueprocedencia', 'hidden', array('data' => $data['ueprocedencia']))
                        ->add('genero', 'hidden', array('data' => $data['genero']))
                        ->add('gestion', 'text', array('data' => $data['gestion']))
                        ->add('ninguno', 'submit', array('label' => 'Nueva Inscripción', 'attr' => array('class' => 'btn btn-teal btn-stroke btn-inset btn-sm btn-block')))
                        ->getForm();
    }
    /**
     * get the same students with the data send
     * @param type $data
     * @return type
     * return obj sutudent list
     */
    private function getCoincidenciasStudent($data) {
        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
        $query = $repository->createQueryBuilder('e')
                ->where('e.paterno like :paterno')
                ->andWhere('e.materno like :materno')
                ->andWhere('e.nombre like :nombre')
                ->setParameter('paterno', strtoupper($data['paterno']) . '%')
                ->setParameter('materno', strtoupper($data['materno']) . '%')
                ->setParameter('nombre', strtoupper($data['nombre']) . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();


        try {
            return $query->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            return $exc;
        }
    }
    /*
    * get variables and build the form todo new excepcional inscription
    */
    public function newExcepcionalAction(Request $request) {
        $form = $request->get('form');

        $sw = 0;
        $data = array();
        $formExcepcional = $this->createFormNewExcepcional(0, $sw, $form);
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecnicas:newexcepcional.html.twig', array(
                    'datastudent' => $form,
                    'form' => $formExcepcional->createView()
        ));
    }
    /*
    *create form todo the new inscription excepcional
    */
    private function createFormNewExcepcional($idStudent, $sw, $data) {

        $em = $this->getDoctrine()->getManager();

        return $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_savenewextranjero'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('subcea', 'choice', array('label'=>'Sub CEA','attr' => array('required' => true, 'class' => 'form-control')))
                ->add('semestre', 'choice', array('mapped' => false, 'label' => 'Semestre', 'choices' => array(''=>'','2' => '1', '3' => '2'), 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
    }

}
