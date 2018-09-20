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
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * FindSie controller.
 *
 */
class FindSieController extends Controller {

    public $session;
    public $idInstitucion;
    public $arrUeModular;
    public $unidadEducativa;
    public $arrUeRegularizar;
    public $arrUeNocturnas;
    public $arrUeTecTeg;
    public $arrUeGeneral;
    public $operativoUe;
    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->arrUeModular = $this->fillSieModular();
        $this->arrUeRegularizar = $this->fillSieRegularizarHumanistico();
        $this->arrUeNocturnas = $this->fillSieNocutrnas();
        $this->arrUeTecTeg = $this->fillSieTecnicos();
        $this->arrUeGeneral = $this->fillSieGeneral();
    }
    /**
       *fill the sie's modulars
       **/
       public function fillSieModular(){
       }

      /**
      *fill the sie's Regularizar
      **/
      public function fillSieRegularizarHumanistico(){

      }
      /**
         *fill the sie's modulars
         **/
         public function fillSieTecnicos(){

         }

         /**
            *fill the sie's modulars
            **/
            public function fillSieNocutrnas(){

            }


      /**
      *fill the sie's general
      **/
      public function fillSieGeneral(){

      }

    public function indexAction(Request $request){
      //dump($request);die;
      return $this->render($this->session->get('pathSystem') . ':FindSie:index.html.twig', array(
          'form'=> $this->formFindSie()->createView(),
      ));
    }
    private function formFindSie(){
    return $this->createFormBuilder()
          ->setAction($this->generateUrl('herramienta_findsie_opensie'))
          ->add('sie', 'text', array('data'=>'', 'attr'=>array('class'=>'form-control')))
          ->add('find', 'submit', array('label' =>'Buscar', 'attr' => array('class' => 'btn btn-info')))
          ->getForm();
  }
    /**
     * list of request
     *
     */
    public function openSieAction(Request $request) {

        //create the db connexion
        $em=$this->getDoctrine()->getManager();
        //get the send data
        $form = $request->get('form');
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = $form['sie'];

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the sie and the name of sie
        //$arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
        $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));
        $arrSieInfoUe = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
        //get the ue plena info
        //$objValidateUePlena=array();
        //if(!$arrSieInfo)
        $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'institucioneducativaId' => $this->unidadEducativa,
          'gestionTipoId' => $this->session->get('currentyear')

        ));
        //set the modular variable if exist
        // $this->session->set('ue_modular', (array_search("$this->unidadEducativa",$this->arrUeModular,true))?true:false);
        // $this->session->set('ue_regularizar', (array_search("$this->unidadEducativa",$this->arrUeRegularizar,true)!=false)?true:false);
        // $this->session->set('ue_noturna', (array_search("$this->unidadEducativa",$this->arrUeNocturnas,true)!=false)?true:false);
        //get type of UE
        $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$this->unidadEducativa,'gestion'=>$this->session->get('currentyear')));

        if($objValidateUePlena){
          //switch to the kind of UE
          switch ($objValidateUePlena->getInstitucioneducativaHumanisticoTecnicoTipo()->getId()) {
            case 1:
              # plena
              $this->session->set('ue_plena', true);
              break;
            case 2:
                # tec teg
              $this->session->set('ue_tecteg', true);
                break;
            case 3:
                # tec teg
              $this->session->set('ue_modular', true);
                break;
            case 4:
                # tec teg
              $this->session->set('ue_caldiff', true);
                break;
            case 5:
                # tec teg
              $this->session->set('ue_humanistica_web', true);
                break;
            default:
              # code...
              break;
          }

        }else{
          // $this->session->set('ue_tecteg', (array_search("$this->unidadEducativa",$this->arrUeTecTeg,true)!=false)?true:false);
          $this->session->set('ue_plena', ($objValidateUePlena)?true:false);
          // dump($this->session->get('ue_humanistica'));die;
        }
        //look for Humanistica UE
        $objRegularUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
          'id'=>$this->unidadEducativa,
          'institucioneducativaTipo'=>1
        ));
        // dump($this->unidadEducativa);die;

        if($objRegularUe && ( ((int)$this->unidadEducativa <= 71980071 && (int)$this->unidadEducativa >=71980001 )
        || ((int)$this->unidadEducativa <= 82230136 && (int)$this->unidadEducativa >=82230001 )
        || ((int)$this->unidadEducativa <= 80730824
         && (int)$this->unidadEducativa >=80730002 )

        ) ){
            $this->session->set('ue_humanistica', true);
        }

        // $this->session->set('ue_general', (array_search("$this->unidadEducativa",$this->arrUeGeneral,true)!=false)?true:false);
        $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $this->unidadEducativa, 'gestion'=>$this->session->get('currentyear')-1));
        
        if($this->session->get('roluser')==9 || $this->session->get('roluser')==8 || $this->session->get('roluser')==10)
          $operativoPerUe=$operativoPerUe-1;

        $this->operativoUe = $operativoPerUe;

        //get the current year
        $arrSieInfo[0]['gestion']= (($operativoPerUe == 0))?$this->session->get('currentyear'):($operativoPerUe == 4)?$this->session->get('currentyear'):$this->session->get('currentyear')-1;
        $arrSieInfo[0]['id'] = $this->unidadEducativa;
        //get the fuill ue info
        $arrFullUeInfo=array();
        $arrFullUeInfo =$arrSieInfo[0];
        //$arrFullUeInfo['ueplenainfo'] =$objValidateUePlena;

        //$objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));

        $repository = $em->getRepository('SieAppWebBundle:Tramite');

        $query = $repository->createQueryBuilder('t')
            ->select('td')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'WITH', 'td.tramite = t.id')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'td.flujoProceso = fp.id')
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 't.institucioneducativa = ie.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('fp.id = :flujoP')
            ->andWhere('fp.proceso = :proceso')
            ->setParameter('idInstitucion', $this->unidadEducativa)
            ->setParameter('flujoP', 13)
            ->setParameter('proceso', 11)
            ->orderBy('t.gestionId')
            ->getQuery();

        $entities = $query->getResult();
        if(sizeof($entities)>0){
          $this->session->set('ue_sol_regularizar',true);
        }

        return $this->render($this->session->get('pathSystem') . ':FindSie:openSie.html.twig', array(
            'objValidateUePlena'=>($objValidateUePlena)?1:0,
            'arrSieInfo'=>$arrSieInfo[0],
            'gestion'=>$this->session->get('currentyear'),
            'form'=> $this->formUePlena(json_encode($arrFullUeInfo),$objTypeOfUE)->createView(),
            'consolidationform'=> $this->infoConsolidationForm(json_encode($arrFullUeInfo))->createView(),
            'entities' => $entities,
            'sie'=>$form['sie']
        ));
    }
    /**
    *buill the ue consolidataion information form
    */
    private function infoConsolidationForm($data){
      /**ge the format to the UE**/
// dump($data);die;
      return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_infoconsolidation_index'))
            ->add('data', 'hidden', array('data'=>$data))
            ->add('goConsolidation', 'submit', array('label' => 'Información Consolidación', 'attr' => array('class' => 'btn btn-primary text-center btn-block')))
            ->getForm();
    }
    /**
    *buill the ue plena form
    */
    private function formUePlena($data){
      /**ge the format to the UE**/
      switch (true) {
        case $this->session->get('ue_modular'):
          # code...
          $label = 'Acceso Unidad Educativa Modular';
          $btnClass = 'btn btn-teal';
          break;
        case $this->session->get('ue_regularizar'):
          # code...
          $label = 'Acceso Unidad Educativa a Regularizar';
          $btnClass = 'btn btn-lilac';
          break;
        case $this->session->get('ue_noturna'):
          # code...
          $label = 'Acceso Unidad Educativa Nocturna';
          $btnClass = 'btn btn-warning';
          break;
        case $this->session->get('ue_tecteg'):
          # code...
          $label = 'Acceso Unidad Educativa Tec. Tegnológica';
          $btnClass = 'btn btn-success';
          break;
        default:
          # code...
          $label = 'Acceso Unidad Educativa Plena';
          $btnClass = 'btn btn-primary text-center btn-block';
          break;
      }
      return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_findsie_open'))
            ->add('data', 'hidden', array('data'=>$data))
            ->add('goplena', 'submit', array('label' => $label, 'attr' => array('class' => 'btn btn-facebook text-center btn-block')))
            ->getForm();
    }

    /**
     * get info about user
     * parameters: codigo user
     * @author krlos
     */
    private function getAllUserInfo($userName){

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Usuario');
        $query = $entity->createQueryBuilder('u')
                ->select('u.username as username, u.id as uId , mi.id as miId, IDENTITY(mi.gestionTipo) as gestion, IDENTITY(mi.institucioneducativa) as sie')
                ->leftJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'u.persona = mi.persona')
                ->where('u.username = :username')
                ->andwhere('mi.esVigenteAdministrativo = :vigente')
                ->setParameter('username', $userName)
                ->setParameter('vigente', 1)
                //->setMaxResults(1)
                ->orderBy('mi.gestionTipo')
                ->getQuery();

        //print_r($query);die;
        try {
          $dataUser = $query->getResult();
          $sie = '';
          foreach ($dataUser as $key => $value) {
            # code...
            //dump($value);
            $sie = $value['sie'];
          }
          return $sie;
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }


    /**
     * get info about user
     * parameters: codigo user
     * @author krlos
     */
    private function getUserInfo($persona, $gestion){

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Persona');
        $query = $entity->createQueryBuilder('p')
                ->select('p.carnet as id, p.paterno as paterno, p.nombre as nombre')
                ->where('p.id = :persona')
                ->setParameter('persona', $persona)
                ->setMaxResults(1)
                ->getQuery();

        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    /**
     * get info about UE
     * parameters: codigo user
     * @author krlos
     */
    private function getUserSie($persona, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
                ->select('i.id as id, i.institucioneducativa as datainfo')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'mi.institucioneducativa = i.id')
                ->where('mi.persona = :persona')
                ->andwhere('mi.gestionTipo = :gestion')
                ->setParameter('persona', $persona)
                ->setParameter('gestion', $gestion)
                ->setMaxResults(1)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request
     */
    public function openAction(Request $request) {
      //create conexion
      $em = $this->getDoctrine()->getManager();
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //get the values
        $form = $request->get('form');
        $data = json_decode($form['data'], true);

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($data['id']);
        $dataInfo = array('id' => $data['id'], 'gestion' => $data['gestion'], 'ieducativa' => $ieducativa);

        //get and set the operativo
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($data['id'],$data['gestion']);
        $this->session->set('lastOperativo', $operativo);

        //verificar - crear registro en InstitucioneducativaHumanisticoTecnico
        $humanistico_tecnico = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'gestionTipoId' => $data['gestion'],
          'institucioneducativaId' => $data['id']
        ));

        if(!$humanistico_tecnico) {
          // $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
          // $humanistico_tecnico = new InstitucioneducativaHumanisticoTecnico();
          // $humanistico_tecnico->setGestionTipoId($data['gestion']);
          // $humanistico_tecnico->setInstitucioneducativaId($ieducativa->getId());
          // $humanistico_tecnico->setInstitucioneducativa($ieducativa->getInstitucioneducativa());
          // $humanistico_tecnico->setEsimpreso('f');
          // $humanistico_tecnico->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById(6));
          // $humanistico_tecnico->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->findOneById(5));
          // $em->persist($humanistico_tecnico);
          // $em->flush();
        }

        return $this->render($this->session->get('pathSystem') . ':Inbox:open.html.twig', array(
                    'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
                    'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                    'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Maestros',$data)->createView(),
                    'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Estudiantes',$data)->createView(),
                    'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo',$data)->createView(),
                    'closeOperativoform' => $this->CloseOperativoForm('herramienta_mallacurricular_index', 'Cerrar Operativo',$data)->createView(),
                    'data'=>$dataInfo
        ));
    }

    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function InfoStudentForm($goToPath, $nextButton, $data) {
        $this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $data['id']))//81880091
                        ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft')))
                        ->getForm()
        ;
    }

    public function openByGestionAction(Request $request, $number) {
      //get the db conexion
      $em = $this->getDoctrine()->getManager();

      $arrGestion = array('1'=>2012, '2'=>2013, '3'=>2014, '4'=>2015, '5'=>2016,'6'=>2008, '7'=>2009, '8'=>2010, '9'=>2011);
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //dump($this->session);die;
        //get the values
        $form = $request->get('form');

        //get the sie and the name of sie
        $arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
        //get the ue plena info
        if($arrSieInfo)
          $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $arrSieInfo[0]['id']));
        else
          $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));

        $this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        //dump($arrSieInfo);die;
        //$data = unserialize($form['data']);
        $data = array('id'=>$this->unidadEducativa, 'gestion'=>$arrGestion[$number]);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InboxRequest:open.html.twig', array(
                    'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
                    'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                    'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Información Maestro',$data)->createView(),
                    //'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Información Estudiante',$data)->createView(),
                    'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudianterequest_index', 'Información Estudiante',$data)->createView(),
                    'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo', $data)->createView()

        ));
    }

    public function openByGestionTramiteAction(Request $request) {
      //get the db conexion
      $em = $this->getDoctrine()->getManager();

      //get session data
      $this->session = $request->getSession();
      $id_usuario = $this->session->get('userId');

      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      //get the values
      $form = $request->get('form');
      //dump($form);die;
      switch ($form['tipo']) {
        case 3:
          $this->session->set('ue_regularizar', true);
          break;
        case 18:
          # code...
          $this->session->set('ue_general',true);
          break;
        case 19:
          # code...
          $this->session->set('ue_tecteg',true);
          break;
        case 20:
          # code...
          $this->session->set('ue_modular',true);
          break;
        case 21:
          # code...
          $this->session->set('ue_noturna',true);
          break;
        case 22:
          # code...
          $this->session->set('ue_plena',true);
          break;
        default:
          # code...
          break;
      }
      //create data for open gestion
      $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idInstitucion']);

      $data = array('id' => $form['idInstitucion'], 'gestion' => $form['gestion'], 'ieducativa' => $ieducativa);
      $dataInfo = array('id' => $form['idInstitucion'], 'gestion' => $form['gestion'], 'ieducativa' => $ieducativa);
      //get and set the operativo
      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($data['id'],$data['gestion']);
      $this->session->set('lastOperativo', $operativo);

      return $this->render($this->session->get('pathSystem') . ':Inbox:open.html.twig', array(
                  'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
                  'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                  'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Maestros',$data)->createView(),
                  'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Estudiantes',$data)->createView(),
                  'mallaCurricularform' => $this->InfoStudentForm('herramienta_mallacurricular_index', 'Malla Curricular',$data)->createView(),
                  'closeOperativoform' => $this->CloseOperativoForm('herramienta_mallacurricular_index', 'Cerrar Operativo',$data)->createView(),
                  'data'=>$dataInfo
      ));

    }

    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function CloseOperativoForm($goToPath, $nextButton, $data) {
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = ((int)$this->session->get('ie_id'));
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $this->unidadEducativa))//81880091
                        ->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft', 'onclick'=>'closeOperativo()')))
                        ->getForm()
        ;
    }

}
