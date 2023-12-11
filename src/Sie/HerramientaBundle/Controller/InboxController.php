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
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;


use Doctrine\DBAL\Types\Type;
Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTimeType');

/**
 * EstudianteInscripcion controller.
 *
 */
class InboxController extends Controller {

    public $session;
    public $idInstitucion;
    public $arrUeModular;
    public $unidadEducativa;
    public $arrUeRegularizar;
    public $arrUeNocturnas;
    public $arrUeTecTeg;
    public $arrUeGeneral;
    public $operativoUe;
    public $reglasQA;
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
        $this->reglasQA = '2,3,6,8,10,12,13,15,16,20,24,25,26';
    }
    /**
       *fill the sie's modulars
       **/
       public function fillSieModular(){
        //  return array(
        //    '1'=>'82190066','82190032','82190082','82190056','82220075','82220110',
        //    '82220216','82220066','80390012','60390025','80440013','80440031',
        //    '80870039','80870078','80870069','80870043','70590010','80590021',
        //    '80590037','80590029','72460009','72460008','72460023','72460014',
        //    '81870039','81870028','81870041','81870037','81960030','61840025',
        //    '81870026','81870056','61710061','61710080','61710074','61710033',
        //    '81960054','82220075'
        //  );
       }

      /**
      *fill the sie's Regularizar
      **/
      public function fillSieRegularizarHumanistico(){
        // return array(
        //   '1' => '81970073', '2' => '81970111','3' => '81730080', '4'=>'40730366'
        //   );
      }
      /**
         *fill the sie's modulars
         **/
         public function fillSieTecnicos(){
          //  return array(
          //     '1'=> '80950056',
          //           '60900064',
          //           '81981463',
          //           '80540183',
          //           '80730784',
          //           '81410157',
          //           '60730045',
          //           '80980556',
          //           '40730531',
          //           '70710055',
          //           '81930066',
          //           '80900108',
          //           '81981478',
          //           '81730261',
          //           '80730789',
          //           //'82220182',
          //           '80980575',
          //           '50870063'
          //  );
         }

         /**
            *fill the sie's modulars
            **/
            public function fillSieNocutrnas(){
              // return array(
              //   '1'=> '81730055',
              //         '81730061',
              //         '81730160',
              //         '81730163',
              //         '81730167',
              //         '81730173',
              //         '81680075',
              //         '80980276'
              // );
            }


        /**
        *fill the sie's general
        **/
        public function fillSieGeneral(){
//          return array(
//            '1'=> '81230085',
//                  '70440024',
//                  '50460019',
//                  '60420065',
//                  '60480035',
//                  '70470019',
//                  '70470037',
//                  '40710002',
//                  '80590040',
//                  '50630055',
//                  '70590017',
//                  '80590002',
//                  '60660003',
//                  '80930002',
//                  '70870043',
//                  '80880022',
//                  '80870046',
//                  '70940035',
//                  '81210036',
//                  '51230003',
//                  '71180012',
//                  '81160034',
//                  '71200018',
//                  '81400059',
//                  '81330001',
//                  '81430134',
//                  '81390020',
//                  '71480033',
//                  '71370060',
//                  '81680050',
//                  '71700033',
//                  '81840007',
//                  '81840013',
//                  '71920002',
//                  '81880050',
//                  '81880003',
//                  '81920001',
//                  '61960012',
//                  '72180001',
//                  '82190131',
//                  '82200044',
//                  '82460008',
//                  '62470004',
//                  '62460007',
//                  '62480003',
//                   '70420056',
//                   '80660099',
//                   '80650024',
//                   '70960008',
//                   '60890094',
//                   '81200019',
//                   '81110025',
//                   '61450095',
//                   '81440010',
//                   '81720095',
//                   '71930002',
//                   '80630028'
//
//
// );
        }
    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //create the db connexion
        $em=$this->getDoctrine()->getManager();
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = $this->session->get('ie_id');
        //dump( $this->unidadEducativa);die;
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

        $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $this->session->get('ie_id'), 'gestion'=>$this->session->get('currentyear')-1));
        //get the current year
        $gestionOpeUnidadEducativa = (($operativoPerUe == 0))?$this->session->get('currentyear'):($operativoPerUe-1 == 3)?$this->session->get('currentyear'):$this->session->get('currentyear')-1;


        $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'institucioneducativaId' => $this->unidadEducativa,
          'gestionTipoId' => $gestionOpeUnidadEducativa

        ));
        //set the modular variable if exist
        // $this->session->set('ue_modular', (array_search("$this->unidadEducativa",$this->arrUeModular,true))?true:false);
        // $this->session->set('ue_regularizar', (array_search("$this->unidadEducativa",$this->arrUeRegularizar,true)!=false)?true:false);
        // $this->session->set('ue_noturna', (array_search("$this->unidadEducativa",$this->arrUeNocturnas,true)!=false)?true:false);
        //get type of UE
        $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$this->unidadEducativa,'gestion'=>$gestionOpeUnidadEducativa));
        if($objValidateUePlena){
          //switch to the kind of UE
          switch ($objValidateUePlena->getInstitucioneducativaHumanisticoTecnicoTipo()->getId()) {
            case 1:
            case 7:
              # plena
              $this->session->set('ue_plena', true);
              $this->session->set('acceso_total', true);
              $arrGradoTipoBth = false;
              if($objValidateUePlena->getGradoTipo()->getId()>=6){
                $arrGradoTipoBth = array(5,6);
              }else{
                $gradoTipoBth = $objValidateUePlena->getGradoTipo()->getId()==5?$arrGradoTipoBth = array(5):false;
              }
              $this->session->set('gradoTipoBth', $arrGradoTipoBth);
              break;
            case 2:
                # tec teg
                $this->session->set('ue_tecteg', true);
                $this->session->set('acceso_total', true);
                break;
            case 3:
                # tec teg
                $this->session->set('ue_modular', true);
                $this->session->set('acceso_total', true);
                break;
            case 4:
                # tec teg
                $this->session->set('ue_caldiff', true);
                $this->session->set('acceso_total', true);
                break;
            case 5:
                # tec teg
                $this->session->set('ue_humanistica_web', true);
                $this->session->set('acceso_total', true);
                break;
            default:
                $this->session->set('ue_humanistica', true);
                $this->session->set('acceso_total', false);
                break;
          }

        }else{
          // $this->session->set('ue_tecteg', (array_search("$this->unidadEducativa",$this->arrUeTecTeg,true)!=false)?true:false);
          //$this->session->set('ue_plena', ($objValidateUePlena)?true:false);
          // dump($this->session->get('ue_humanistica'));die;
          $this->session->set('ue_humanistica', true);
          $this->session->set('acceso_total', false);
        }
        //look for Humanistica UE
        $objRegularUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
          'id'=>$this->unidadEducativa,
          'institucioneducativaTipo'=>1
        ));

        
         
         //dump($objEspecial);die;
        // dump($this->unidadEducativa);die;

        // if($objRegularUe && ( ((int)$this->unidadEducativa <= 71980071 && (int)$this->unidadEducativa >=71980001 )
        // || ((int)$this->unidadEducativa <= 82230136 && (int)$this->unidadEducativa >=82230001 )
        // || ((int)$this->unidadEducativa <= 80730824
        //  && (int)$this->unidadEducativa >=80730002 )

        // ) or $this->unidadEducativa == 71700024 ){
            
        // }


        $ie_id=$this->session->get('ie_id');
        $_gestion=$this->session->get('currentyear');
        $this->session->set('esGuanawek',0);
        //change the year to current year after that ....
        $esGuanawek=$this->esGuanawek($ie_id,$gestion=2020);
        if($esGuanawek)
        {
          /*$operativoPerUe=1;
          $_gestion=2020;*/
          $this->session->set('esGuanawek',1);
        }
        //else{
          // $this->session->set('ue_general', (array_search("$this->unidadEducativa",$this->arrUeGeneral,true)!=false)?true:false);
          $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $this->session->get('ie_id'), 'gestion'=>$this->session->get('currentyear')-1));
          $_gestion=$this->session->get('currentyear');
          //$this->session->set('esGuanawek',0);
        //}

        $this->operativoUe = $operativoPerUe;
        //get the current year
        $arrSieInfo[0]['gestion']= bin2hex((($operativoPerUe == 0))?$this->session->get('currentyear'):($operativoPerUe-1 == 3)?$this->session->get('currentyear'):$this->session->get('currentyear')-1) ;
        $arrSieInfo[0]['id'] = bin2hex($this->session->get('ie_id')) ;
        //get the fuill ue info
        $arrFullUeInfo=array();
        $arrFullUeInfo =$arrSieInfo[0];
        //$arrFullUeInfo['ueplenainfo'] =$objValidateUePlena;

        //$objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));

        $objEspecial = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
          'id'=>$this->unidadEducativa,
          'institucioneducativaTipo'=>4
        ));
      //dump($objEspecial);die;

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

        $this->session->set('ue_sol_regularizar',false);
        //dump($this->session->get('pathSystem'));die;
        return $this->render($this->session->get('pathSystem') . ':Inbox:index.html.twig', array(
            'objValidateUePlena'=>($objValidateUePlena)?1:0,
            'arrSieInfo'=>$arrSieInfo[0],
            //'gestion'=>$this->session->get('currentyear'),
            'gestion'=>$_gestion,
            'form'=> $this->formUePlena(json_encode($arrFullUeInfo),$objTypeOfUE)->createView(),
            'formOperativoRude'=> $this->formOperativoRude(json_encode($arrFullUeInfo),$objTypeOfUE)->createView(),
            'consolidationform'=> $this->infoConsolidationForm(json_encode($arrFullUeInfo))->createView(),
            'entities' => $entities,

            'esGuanawek' => $esGuanawek,
            'objEspecial' => $objEspecial,
            'operativoBonoJP' => $this->get('operativoutils')->verificarEstadoOperativo($ie_id,$this->session->get('currentyear'),14),
        ));
    }

    /**
     * Funcion que determina si una Institucion Educativa, es tipo Guanawek
     *
     * @param institucioneducativa_id, gestion
     * @return true o flase
     * @author lnina
     **/
    private function esGuanawek($ie_id,$gestion)
    {
      $return=false;
      $tecnico_humanistico=4; //institucioneducativa_humanistico_tecnico_tipo_id 
      $departamentos=array();
      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();
      $query = '
      select * 
      from 
      institucioneducativa_humanistico_tecnico 
      where 
      institucioneducativa_humanistico_tecnico_tipo_id = ? 
      and gestion_tipo_id = ?
      and institucioneducativa_id = ?';

      $stmt = $db->prepare($query);
      $params = array($tecnico_humanistico,$gestion,$ie_id);
      $stmt->execute($params);
      $guanawek=$stmt->fetchAll();

      if($guanawek)
        $return=true;

      return $return;
    }

    // this is fot the RUDE Operativo
    private function formOperativoRude($data,$objTypeOfUE){
      return $this->createFormBuilder()
            ->add('data', 'hidden', array('data'=>$data))
            ->add('downOperativoRude','button',array('label'=>'Generar Archivo RUDE', 'attr'=>array('class'=>'btn btn-inverse btn-stroke text-center btn-block', 'onclick'=> 'downOperativoRudeup()') ))
            ->getForm();

    }
    /**
    *buill the ue plena form
    */
    private function formUePlena($data,$objTypeOfUE){

      $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
      /**ge the format to the UE**/
      switch (true) {
        // case $this->session->get('ue_modular'):
        //   # code...
        //   $label = 'Unidad Educativa Modular';
        //   $btnClass = 'btn btn-teal text-center btn-block';
        //   break;
        case $this->session->get('ue_regularizar'):
          # code...
          $label = 'U.E. Regularizar';
          $btnClass = 'btn btn-lilac text-center btn-block';
          break;
        // case $this->session->get('ue_noturna'):
        //   # code...
        //   $label = 'Unidad Educativa Nocturna';
        //   $btnClass = 'btn btn-warning text-center btn-block';
        //   break;
        // case $this->session->get('ue_tecteg'):
        //   # code...
        //   $label = 'Unidad Educativa Tec. Tegnológica';
        //   $btnClass = 'btn btn-success text-center btn-block';
        //   break;
        case $this->session->get('ue_general'):
            # code...
            $label = 'Registro del area TTG';
            $btnClass = 'btn btn-lilac text-center btn-block';
            break;
        // default:
        //   # code...
        //   $label = 'Unidad Educativa Plena';
        //   $btnClass = 'btn btn-primary text-center btn-block';
        //   break;
      }
      $btnForm  = ($this->operativoUe <= 5 )?'submit':'button';
      switch ($objTypeOfUEId) {
        case 1:
        case 7:
          # code...
          $label = 'U.E. Plena';
          $btnClass = 'btn btn-primary text-center btn-block';
          break;
        case 2:
          # code...
          $label = 'U.E. Tec. Tecnológica';
          $btnClass = 'btn btn-success text-center btn-block';
          break;
        case 3:
          # code...
          $label = 'U.E. Modular';
          $btnClass = 'btn btn-teal text-center btn-block';
          break;
        case 4:
          # code...
          $label = 'U.E. Cal. Diferenciado';
          $btnClass   ='btn btn-default text-center btn-block';
          break;
        case 5:
          # code...
          $label = 'U.E. Humanística';
          $btnClass ='btn btn-success text-center btn-block';
          break;
        case 6:
          # code...
          $label = 'U.E. Nocturna';
          $btnClass ='btn btn-teal text-center btn-block';
          break;
        default:
          # code...
          if( $this->session->get('ue_humanistica')){
            $label = 'U.E. Regular';
            $btnClass ='btn btn-lilac text-center btn-block';
          }else{
            $label = 'U.E.';
            $btnClass ='btn btn-default text-center disabled';
            $btnForm  = 'button';
          }

          break;
      }

      $btnClass = ($this->operativoUe <= 5)?$btnClass:'btn btn-default text-center disabled';

      //dump($btnClass);die;
      return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_inbox_open'))
            ->add('data', 'hidden', array('data'=>$data))
            ->add('goplena', $btnForm, array('label' => $label, 'attr' => array('class' => $btnClass)))
            ->getForm();
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
                ->andwhere('i.orgcurricularTipo = :curricularTipo')
                ->setParameter('persona', $persona)
                ->setParameter('gestion', $gestion)
                ->setParameter('curricularTipo', 1)
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

    private function getBaseInfoUE($data){

      $em = $this->getDoctrine()->getManager();

      $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));
      $arrSieInfoUe = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));        

      $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $this->session->get('ie_id'), 'gestion'=>$this->session->get('currentyear')-1));
      $_gestion=$this->session->get('currentyear');
      $this->operativoUe = $operativoPerUe;
      //get the current year
      $arrSieInfo[0]['gestion']= (($operativoPerUe == 0))?$this->session->get('currentyear'):($operativoPerUe-1 == 3)?$this->session->get('currentyear'):$this->session->get('currentyear')-1;
      $arrSieInfo[0]['id'] = $this->session->get('ie_id');
      //get the fuill ue info
      $arrFullUeInfo=array();
      $arrFullUeInfo =$arrSieInfo[0];

      return $arrFullUeInfo;      



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
        $dataPre = json_decode($form['data'], true);
        
        if(isset($dataPre['tipo']) && $dataPre['tipo'] == 'history'){
          $data['gestion'] = $dataPre['gestion'];
          $arrRol = array(10,8,7);
          $data['id'] =  ( in_array($this->session->get('roluser'),$arrRol) )? $dataPre['id']: $this->session->get('ie_id');
        }else{
          // start to get the data to open the UE info
          $data = $this->getBaseInfoUE(array());      
          // end to get the data to open the UE info
        }
        /*
        * verificamos si tiene tuicion
        */
        $tuicion = false;
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $data['id']);
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        if ($aTuicion[0]['get_ue_tuicion']) {
          $tuicion = true;
        }

        $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'institucioneducativaId' => $data['id'],
          'gestionTipoId' => $data['gestion']

        ));

        $repository = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $repository->createQueryBuilder('t')
            ->select('td')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'WITH', 'td.tramite = t.id')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'td.flujoProceso = fp.id')
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 't.institucioneducativa = ie.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('fp.id = :flujoP')
            ->andWhere('fp.proceso = :proceso')
            ->andWhere('t.gestionId = :gestion')
            ->setParameter('idInstitucion', $this->unidadEducativa)
            ->setParameter('flujoP', 13)
            ->setParameter('proceso', 11)
            ->setParameter('gestion', $data['gestion'])
            ->orderBy('t.gestionId')
            ->getQuery();
            
        $tramites = $query->getResult();

        if(sizeof($tramites)>0){
          $this->session->set('ue_sol_regularizar',true);
        } else {
          $this->session->set('ue_sol_regularizar',false);
        }

       if($objValidateUePlena){
         //switch to the kind of UE
         switch ($objValidateUePlena->getInstitucioneducativaHumanisticoTecnicoTipo()->getId()) {
           case 1:
           case 7:
             # plena
             $this->session->set('ue_plena', true);
             $this->session->set('acceso_total', true);
             break;
           case 2:
               # tec teg
             $this->session->set('ue_tecteg', true);
             $this->session->set('acceso_total', true);
               break;
           case 3:
               # tec teg
             $this->session->set('ue_modular', true);
             $this->session->set('acceso_total', true);
               break;
           case 4:
               # tec teg
             $this->session->set('ue_caldiff', true);
             $this->session->set('acceso_total', true);
               break;
           case 5:
               # tec teg
             $this->session->set('ue_humanistica_web', true);
             $this->session->set('acceso_total', true);
               break;
           default:
              $this->session->set('ue_humanistica', true);
             $this->session->set('acceso_total', false);
             break;
         }

       }else{
          $this->session->set('ue_humanistica', true);
          $this->session->set('acceso_total', false);
       }
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($data['id']);
        $dataInfo = array('id' => $data['id'], 'gestion' => $data['gestion'], 'ieducativa' => $ieducativa);

        //get and set the operativo
        $operativo = $this->get('funciones')->obtenerOperativo($data['id'],$data['gestion']);
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

      // $form['reglas'] = '1,2,3,10,12,13,16,27';
      $form['reglas'] = $this->reglasQA;
      $form['gestion'] = $data['gestion'];
      $form['sie'] = $data['id'];

      if ($data['gestion'] == $this->session->get('currentyear')) {
        $objObsQA = $this->getObservationQA($form);
      } else {
        $objObsQA = null;
      }
      //$this->session->set('donwloadLibreta', false);
      /*if($objObsQA){
        return $this->render($this->session->get('pathSystem') . ':Inbox:list_inconsistencia.html.twig', array(
          'objObsQA' => $objObsQA,
          'sie' =>  $form['sie'],
          'institucion' => $ieducativa->getInstitucioneducativa(),
          'gestion' => $form['gestion']
        ));
      } else {*/

        $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $ieducativa, 'gestion' => $data['gestion']));
        // dump($registroConsol); exit();
        if ($registroConsol) {
          if ($ieducativa) {
            if (($registroConsol->getBim1()==2) && ($registroConsol->getBim2()==2) && (($registroConsol->getBim3()==2) || ($registroConsol->getBim4()==2)) ) {
              $trimestre=3;
            }else{
              if (($registroConsol->getBim1()==2) && ($registroConsol->getBim2()==2) ) {
                $trimestre=2;
              }else{
                if ($registroConsol->getBim1()==2) {
                  $trimestre=1;
                }else{
                  $trimestre=0;
                }
              }
            }
          }else{ 
             $trimestre=0; 
            /*codigo sie no existe*/ 
          }
          # code...
        }else{ 
           $trimestre=0; 
          /*codigo sie no existe*/ 
        }

        //dump($this->session->get('pathSystem')); die; sieHerramientaBundle
        if(in_array($data['gestion'], array(2022,2021) )){
            $arrLabelToClose = array('0'=>'Inscriptions','1'=>'1er. Trim.','2'=>'2do. Trim.','3'=>'3er. Trim.','4'=>'3er. Trim.');
        }else{
            $arrLabelToClose = array('0'=>'Inscriptions','1'=>'1er. Trim.','2'=>'2do. Trim.','3'=>'3er. Trim.','4'=>'4to. Trim.');
        }
        
        $dataInfo['messageope']='Cerrar Operativo '. $arrLabelToClose[$this->get('funciones')->obtenerOperativo($ieducativa,$data['gestion'])];
        
        return $this->render($this->session->get('pathSystem') . ':Inbox:open.html.twig', array(
          'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
          'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
          'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Personal Docente',$data)->createView(),
          'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Estudiantes',$data)->createView(),
          'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo',$data)->createView(),
          'closeOperativoInscriptionform' => $this->CloseOperativoInscriptionForm('herramienta_inbox_close_operativo_inscription', 'Cerrar Operativo '. $arrLabelToClose[$this->get('funciones')->obtenerOperativo($ieducativa,$data['gestion'])],$data)->createView(),
          'closeOperativoform' => $this->CloseOperativoForm('herramienta_mallacurricular_index', 'Cerrar Operativo',$data)->createView(),
          'data'=>$dataInfo,
          'tuicion'=>$tuicion,
          'bimestre'=>$trimestre,
          'objObsQA' => $objObsQA,          
          'operativoSaludform' => $this->InfoStudentForm('herramienta_info_personalAdm_maestro_index', 'Operativo Salud',$data)->createView(),
          'closeOperativoRudeform' => $this->CloseOperativoRudeForm('herramienta_inbox_close_operativo_rude', 'Cerrar Operativo RUDE',$data)->createView(),
          'operativoBonoJPform' => $this->InfoStudentForm('operativo_bono_jp_cerrar', 'Cerrar Operativo Bono JP...',$data)->createView(),
          'operativoBonoJP' => $this->get('operativoutils')->verificarEstadoOperativo($form['sie'],$form['gestion'],14),
        ));
      // }
    }

    /**
     * create close operativo form
     * @return type obj form
     */
    private function InfoStudentForm($goToPath, $nextButton, $data) {
      
      // dump($this->session->get('ue_plena'));
      // die;
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = $data['id'];
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => bin2hex($data['gestion'])))
                        ->add('sie', 'hidden', array('data' => bin2hex($this->unidadEducativa)));
        if(
            ($this->session->get('ue_plena')  && $this->session->get('ue_humanistica')) ||
            ($this->session->get('ue_tecteg') && $this->session->get('ue_humanistica')) ||
            ($this->session->get('ue_modular') && $this->session->get('ue_humanistica')) ||
            ($this->session->get('ue_humanistica_web') && $this->session->get('ue_humanistica')) ||
            ($this->session->get('ue_sol_regularizar') && $this->session->get('ue_humanistica')) ||
            ($this->session->get('ue_plena')  ) ||
            ($this->session->get('ue_modular')  ) ||
            ($this->session->get('ue_caldiff')  ) ||
            ($this->session->get('ue_humanistica_web')  ) ||
            ($this->session->get('ue_sol_regularizar')  ) ||
            ($this->session->get('ue_tecteg') )
      ){
        $form =$form->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')))
        ;
        }else{
          if($nextButton == 'Estudiantes'){
            $form =$form->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')))
            ;
          }else{
            //$form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')))
            $form =$form->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')))
            ;
          }


        }
        $form = $form->add('accessuser', 'hidden', array('data' => false));
        $form = $form->getForm();
        return $form;
    }
    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function CloseOperativoForm($goToPath, $nextButton, $data) {
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = ((int)$this->session->get('ie_id'));
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $this->unidadEducativa))//81880091
        ;
        // if( $this->session->get('ue_humanistica')){
        if(
           ($this->session->get('ue_plena')  && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_tecteg') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_modular') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_caldiff') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_humanistica_web') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_sol_regularizar') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_plena')  ) ||
           ($this->session->get('ue_humanistica_web')  ) ||
           ($this->session->get('ue_modular')  ) ||
           ($this->session->get('ue_caldiff')  ) ||
           ($this->session->get('ue_sol_regularizar')  ) ||
           ($this->session->get('ue_tecteg') )
        ){
          $form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block', 'onclick'=>'closeOperativo()')));
        } else {
          $form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block', 'onclick'=>'closeOperativo()')));
        }
        $form = $form->getForm();
        return $form;
    }
/**
     * create form Student Info to send values
     * @return type obj form
     */
    private function CloseOperativoInscriptionForm($goToPath, $nextButton, $data) {
      //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
      $this->unidadEducativa = ((int)$this->session->get('ie_id'));
      $form =  $this->createFormBuilder()
                      ->setAction($this->generateUrl($goToPath))
                      ->add('gestion', 'hidden', array('data' => bin2hex($data['gestion']) ))
                      ->add('sie', 'hidden', array('data' => bin2hex($this->unidadEducativa)))//81880091
      ;
      $form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block', 'onclick'=>'closeOperativoInscription()')));
      $form = $form->getForm();
      return $form;
  }

    private function CloseOperativoRudeForm($goToPath, $nextButton, $data)
    {
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = ((int)$this->session->get('ie_id'));
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => bin2hex($data['gestion']) ))
                        ->add('sie', 'hidden', array('data' => bin2hex($this->unidadEducativa)))//81880091
        ;
        // if( $this->session->get('ue_humanistica')){
        if(
           ($this->session->get('ue_plena')  && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_tecteg') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_modular') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_caldiff') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_humanistica_web') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_sol_regularizar') && $this->session->get('ue_humanistica')) ||
           ($this->session->get('ue_plena')  ) ||
           ($this->session->get('ue_humanistica_web')  ) ||
           ($this->session->get('ue_modular')  ) ||
           ($this->session->get('ue_caldiff')  ) ||
           ($this->session->get('ue_sol_regularizar')  ) ||
           ($this->session->get('ue_tecteg') )
        ){
          /**/
          $form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block', 'onclick'=>'closeOperativoRude()')));
        } else {
          $form =$form->add('next', 'button', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block', 'onclick'=>'closeOperativoRude()')));
        }
        $form = $form->getForm();
        return $form;
    }

    public function openByGestionAction(Request $request, $number) {
      //get the db conexion
      $em = $this->getDoctrine()->getManager();

      $arrGestion = array('1'=>2012, '2'=>2013, '3'=>2014, '4'=>2015, '5'=>2016,'6'=>2008, '7'=>2009, '8'=>2010, '9'=>2011);
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

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
      switch ($form['gestion']) {
        case 2016:
        /*
        case 3:
        case 18:
        case 19:
        case 20:
        case 21:
        case 22:*/

          # code...
          return $this->render($this->session->get('pathSystem') . ':Inbox:open.html.twig', array(
                      'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
                      'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                      'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Maestros',$data)->createView(),
                      'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Estudiantes',$data)->createView(),
                      'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo', $data)->createView(),
                      'closeOperativoform' => $this->CloseOperativoForm('herramienta_mallacurricular_index', 'Cerrar Operativo',$data)->createView(),
                      'data' => $data
          ));
          break;

        default:
          # code...
          return $this->render($this->session->get('pathSystem') . ':InboxRequest:open.html.twig', array(
                        'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $data)->createView(),
                        'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                        'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Información Maestro',$data)->createView(),
                        //'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudiante_index', 'Información Estudiante',$data)->createView(),
                        'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudianterequest_index', 'Información Estudiante',$data)->createView(),
                        'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo', $data)->createView(),
                        'closeOperativoform' => $this->CloseOperativoForm('herramienta_inbox_close_operativo', 'Cerrar Operativo',$data)->createView(),
                        'data' => $data

            ));
          break;
      }

    }

    private function getObservationQA($data){
      // added to 2021 about qa
      $years = $data['gestion'].' ,'.$data['gestion'];

      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                                select vp.*
                                                from validacion_proceso vp
                                                where vp.institucion_educativa_id = '".$data['sie']."' and vp.gestion_tipo_id in (".$years.")
                                                and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
                                                and vp.es_activo = 'f'
                                            ");
          //
      $query->execute();
      $objobsQA = $query->fetchAll();

      return $objobsQA;
    }

    // public function closeOperativoAction (Request $request){
    //   //crete conexion DB
    //   $em = $this->getDoctrine()->getManager();
    //   $em->getConnection()->beginTransaction();
    //   //get the values
    //   $form = $request->get('form');
    //   //ini var to validate info
    //   $observation = false;
    //   $inconsistencia = false;
    //   $objObsQA = false;
    //   $valPersonalAdm = false;

    //   //get the current operativo
    //   $objOperativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($form['sie'], $form['gestion']);
    //   //update the close operativo to registro consolido table
    //   $objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
    //     'unidadEducativa' => $form['sie'],
    //     'gestion'         => $form['gestion']
    //   ));

    //   $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
    //   $periodo = 0;
    //   if($registroConsol){
    //       if($registroConsol->getBim1()      == '0' and $registroConsol->getBim2() == '0' and $registroConsol->getBim3() == '0' and $registroConsol->getBim4() == '0'){
    //           $periodo = 1;
    //       }
    //       else if($registroConsol->getBim1() >= '1' and $registroConsol->getBim2() == '0' and $registroConsol->getBim3() == '0' and $registroConsol->getBim4() == '0'){
    //           $periodo = 2;
    //       }
    //       else if($registroConsol->getBim1() >= '1' and $registroConsol->getBim2() >= '1' and $registroConsol->getBim3() == '0' and $registroConsol->getBim4() == '0'){
    //           $periodo = 3;
    //       }
    //       else if($registroConsol->getBim1() >= '1' and $registroConsol->getBim2() >= '1' and $registroConsol->getBim3() >= '1' and $registroConsol->getBim4() == '0'){
    //           $periodo = 4;
    //       }
    //       else if($registroConsol->getBim1() >= '1' and $registroConsol->getBim2() >= '1' and $registroConsol->getBim3() >= '1' and $registroConsol->getBim4() >= '1'){
    //           $periodo = 4;
    //       }
    //   }
    //   else{
    //       $rconsol = new RegistroConsolidacion();
    //       $rconsol->setTipo(1);
    //       $rconsol->setGestion($form['gestion']);
    //       $rconsol->setUnidadEducativa($form['sie']);
    //       $rconsol->setTabla('**');
    //       $rconsol->setIdentificador('**');
    //       $rconsol->setCodigo('**');
    //       $rconsol->setDescripcionError('Consolidado exitosamente!!');
    //       $rconsol->setFecha(new \DateTime("now"));
    //       $rconsol->setusuario('0');
    //       $rconsol->setConsulta('**');
    //       $rconsol->setBim1('0');
    //       $rconsol->setBim2('0');
    //       $rconsol->setBim3('0');
    //       $rconsol->setBim4('0');
    //       $rconsol->setPeriodoId(1);
    //       $rconsol->setSubCea(0);
    //       $rconsol->setBan(1);
    //       $rconsol->setEsonline('t');
    //       $rconsol->setInstitucioneducativaTipoId(1);
    //       $em->persist($rconsol);
    //       $em->flush();
    //       //$em->getConnection()->commit();
    //   }

    //   if($this->session->get('ue_caldiff') == false) {
    //     if ($this->session->get('ue_modular')) {
    //       $inconsistencia = null;
    //     } else {
    //       if($this->session->get('ie_id')=='80730460' && $form['gestion']==2018){
    //         $query = $em->getConnection()->prepare('select * from sp_validacion_regular_insamericano_web(:gestion, :sie, :periodo)');
    //         $query->bindValue(':gestion', $form['gestion']);
    //         $query->bindValue(':sie', $form['sie']);
    //         $query->bindValue(':periodo', $periodo);
    //         $query->execute();
    //         $inconsistencia = $query->fetchAll();
    //       }else{
    //         $query = $em->getConnection()->prepare('select * from sp_validacion_regular_web(:gestion, :sie, :periodo)');
    //         $query->bindValue(':gestion', $form['gestion']);
    //         $query->bindValue(':sie', $form['sie']);
    //         $query->bindValue(':periodo', $periodo);
    //         $query->execute();
    //         $inconsistencia = $query->fetchAll();
    //       }
    //     }
    //   }

    //   /***********************************\
    //   * *
    //   * Validacion personal Administrativo de las Unidades Educativas
    //   * send array => sie, gestion, reglas *
    //   * return type of UE *
    //   * *
    //   \************************************/
    //   $objOperativoValidacionPersonal = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findBy(array(
    //     'institucioneducativa' => $form['sie'],
    //     'gestionTipo' => $form['gestion'],
    //     'notaTipo' => $periodo
    //   ));
    //   $arrValidacionPersonal = array();
    //   if($objOperativoValidacionPersonal>0){
    //     foreach ($objOperativoValidacionPersonal as $key => $value) {
    //       # code...
    //       if($value->getRolTipo()->getId() == 2 || $value->getRolTipo()->getId() == 5)
    //         $arrValidacionPersonal[] = $value->getRolTipo()->getId();
    //     }
    //   }
    //   //validation docente Administrativo director
    //   if(sizeof($arrValidacionPersonal)<2){
    //     $observation = true;
    //     $valPersonalAdm = true;
    //   }

    //   /***********************************\
    //   * *
    //   * Validacion CONTROL DE CALIDAD
    //   * send array => sie, gestion, reglas *
    //   * return observations UE *
    //   * *
    //   \************************************/
    //   // $form['reglas'] = '1,2,3,8,10,12,13,16';
    //   $form['reglas'] = $this->reglasQA;
    //   $objObsQA = $this->getObservationQA($form);
    //   if( $inconsistencia || $objObsQA ){
    //     $observation = true;
    //   }

    //   if(!$observation) {
    //       $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
    //       $registroConsol->setFecha(new \DateTime("now"));

    //       switch ($periodo) {
    //           case 1: $registroConsol->setBim1('2'); break;
    //           case 2: $registroConsol->setBim2('2'); break;
    //           case 3: $registroConsol->setBim3('2'); break;
    //           case 4: $registroConsol->setBim4('2'); break;
    //       }

    //       $em->persist($registroConsol);
    //       $em->flush();
    //       $em->getConnection()->commit();
    //   }

    //   return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
    //     'inconsistencia' => $inconsistencia,
    //     'objObsQA' => $objObsQA,
    //     'validacionPersonal' => $valPersonalAdm,
    //     'observation' => $observation,
    //     'institucion' =>  $form['sie'],
    //     'gestion' => $form['gestion'],
    //     'periodo' => $periodo));
    // }

    public function closeOperativoAction (Request $request)
    {
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');
      //ini var to validate info
      $observation = false;
      $inconsistencia = false;
      $objObsQA = false;
      $valPersonalAdm = false;

      //Sanitizamos las variables de ingreso
      //$form['sie']= filter_var($form['sie'],FILTER_VALIDATE_INT);
      //$form['gestion']= filter_var($form['gestion'],FILTER_VALIDATE_INT);


      //get the current operativo //Variable no utilizada
      //$objOperativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($form['sie'], $form['gestion']);
      
      //update the close operativo to registro consolido table //Variable no utilizada
      /*$objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $form['sie'],
        'gestion'         => $form['gestion']
      ));*/

      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
      $periodo = 0;
      /*$periodo = 0;
      if($registroConsol)
      {
          if($registroConsol->getBim1() == '0')
          {
              $periodo = 1;
          }
      }*/
      /* to do the final close ope
      if($form['gestion']>2020){
        $queryClose = 'select * from sp_validacion_regular_web_2021_ini(:gestion, :sie, :periodo)';
      }else{
        $queryClose = 'select * from sp_validacion_regular_web_2020(:gestion, :sie, :periodo)';
      }


      $query = $em->getConnection()->prepare($queryClose);
      $query->bindValue(':gestion', $form['gestion']);
      $query->bindValue(':sie', $form['sie']);
      $query->bindValue(':periodo', $periodo);
      $query->execute();
      $inconsistencia = $query->fetchAll();
      */

      /***********************************\
      * *
      * Validacion CONTROL DE CALIDAD
      * send array => sie, gestion, reglas *
      * return observations UE *
      * *
      \************************************/
      // $form['reglas'] = '1,2,3,8,10,12,13,16';


      $operativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);
      $operativo = ($operativo < 1)?1:$operativo;



      $form['reglas'] = $this->reglasQA;
      $objObsQA = $this->getObservationQA($form);
      if( $inconsistencia || $objObsQA ){
        if($operativo >= 3){
          $observation = true;
        }
      }
      $valPersonalAdm = false;
      
      if($observation)
      {
        return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
          'inconsistencia' => $inconsistencia,
          'objObsQA' => $objObsQA,
          'validacionPersonal' => $valPersonalAdm,
          'observation' => $observation,
          'institucion' =>  $form['sie'],
          'gestion' => $form['gestion'],
          'periodo' => $periodo));
        
      }
      else
      {
        if($form['gestion']>2020)
        {
          $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
          
            if($registroConsol)
            {
                // se deshabilita las inconsistencias
                if ($operativo>=3) {
                  $operativo=3;
                }

                $valor_op=array('1'=>6,'2'=>7,'3'=>8);
                if($form['gestion']==2021) { 
                  $queryCheckCal = 'select * from sp_validacion_regular_web2021_fg(:gestion,:sie,:ope)';
                }
                if($form['gestion']==2022) { 
                  $queryCheckCal = 'select * from sp_validacion_regular_web2022_fg(:gestion,:sie,:ope)';
                }
                if($form['gestion']==2023) { 
                  $queryCheckCal = 'select * from sp_validacion_regular_web2023_fg(:gestion,:sie,:ope)';
                }
                $query = $em->getConnection()->prepare($queryCheckCal);
                $query->bindValue(':gestion', $form['gestion']);
                $query->bindValue(':sie', $form['sie']);
                $query->bindValue(':ope', $valor_op[$operativo]);
                $inconsistenciaCal = $query->fetchAll();
                
                $queryChange = "select * from sp_validacion_regular_web2021_fg('".$form['gestion']."','".$form['sie']."','".$valor_op[$operativo]."')";
                $query = $em->getConnection()->prepare($queryChange);
                $query->execute();
                $inconsistenciaCal = $query->fetchAll();                
                
                /*if($operativo >= 3)
                {
                  $queryCheckCal = 'select * from sp_validacion_regular_web_2021_med(:gestion, :sie, :ope)';
                  $query = $em->getConnection()->prepare($queryCheckCal);
                  $query->bindValue(':gestion', $form['gestion']);
                  $query->bindValue(':sie', $form['sie']);
                  $query->bindValue(':ope', 3);
                  $query->execute();
                  $inconsistenciaCal = $query->fetchAll();
                }
                else
                {
                  $inconsistenciaCal = array ();
                }*/
                
                if(sizeof($inconsistenciaCal)>0)
                {
                  return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
                  'observation' => true,
                  'inconsistencia' => $inconsistenciaCal,
                  'objObsQA' => false,
                  'validacionPersonal' => false,
                  'institucion' =>  $form['sie'],
                  'gestion' => $form['gestion'],
                  'periodo' => $periodo)); 
                }
                else
                {
                  if($operativo == 1 or $operativo == 2  or $operativo == 3)
                  {
                      $fieldOpe = 'setBim' .$operativo;
                      $registroConsol->$fieldOpe(2);
                      //$registroConsol->setBim1(2);
                      //$this->session->set('donwloadLibreta', true);
                    if($operativo > 3)
                        $this->session->set('donwloadLibreta', true);
                    else
                      $this->session->set('donwloadLibreta', false);
                  }
                }
            }
            else
            {
              $registroConsol = new RegistroConsolidacion();
              $registroConsol->setTipo(1);
              $registroConsol->setGestion($form['gestion']);
              $registroConsol->setUnidadEducativa($form['sie']);
              $registroConsol->setTabla('**');
              $registroConsol->setIdentificador('**');
              $registroConsol->setCodigo('**');
              $registroConsol->setDescripcionError('Consolidado exitosamente!!');
              $registroConsol->setFecha(new \DateTime("now"));
              $registroConsol->setusuario('0');
              $registroConsol->setConsulta('**');
              $registroConsol->setBim1('0');
              $registroConsol->setBim2('0');
              $registroConsol->setBim3('0');
              $registroConsol->setBim4('0');
              $registroConsol->setPeriodoId(1);
              $registroConsol->setSubCea(0);
              $registroConsol->setBan(1);
              $registroConsol->setEsonline('t');
              $registroConsol->setInstitucioneducativaTipoId(1);
            }
            $em->persist($registroConsol);
            $em->flush();
            $em->getConnection()->commit();
            return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
                          'observation' => false,
                          'institucion' =>  $form['sie'],
                          'gestion' => $form['gestion'],
                          'periodo' => $operativo));
        }
        else
        {
            $operativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);
            if($operativo<4)
            {
                if($this->session->get('esGuanawek')){
                  $registroConsol->setBim1('2');
                  $registroConsol->setBim2('2');
                  $registroConsol->setBim3('2');
                }
                else
                {
                  $fieldOpe = 'setBim' .$operativo;
                  $registroConsol->$fieldOpe(2);
                  
                }
            }
            /*switch ($periodo)
            {
                case 1: $registroConsol->setBim1('2'); break;//VOLVERLO DINAMICO
                
            }*/
            $registroConsol->setFecha(new \DateTime("now"));
            $em->persist($registroConsol);
            $em->flush();
            $em->getConnection()->commit();
            
              return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
              'observation' => false,
              'institucion' =>  $form['sie'],
              'gestion' => $form['gestion'],
              'periodo' => $periodo));

        }
      }


      // if($this->session->get('ue_caldiff') == false) {
      //   if ($this->session->get('ue_modular')) {
      //     $inconsistencia = null;
      //   } else {
      //     if($this->session->get('ie_id')=='80730460' && $form['gestion']==2018){
      //       $query = $em->getConnection()->prepare('select * from sp_validacion_regular_insamericano_web(:gestion, :sie, :periodo)');
      //       $query->bindValue(':gestion', $form['gestion']);
      //       $query->bindValue(':sie', $form['sie']);
      //       $query->bindValue(':periodo', $periodo);
      //       $query->execute();
      //       $inconsistencia = $query->fetchAll();
      //     }else{
      //       $query = $em->getConnection()->prepare('select * from sp_validacion_regular_web(:gestion, :sie, :periodo)');
      //       $query->bindValue(':gestion', $form['gestion']);
      //       $query->bindValue(':sie', $form['sie']);
      //       $query->bindValue(':periodo', $periodo);
      //       $query->execute();
      //       $inconsistencia = $query->fetchAll();
      //     }
      //   }
      // }

      /***********************************\
      * *
      * Validacion personal Administrativo de las Unidades Educativas
      * send array => sie, gestion, reglas *
      * return type of UE *
      * *
      \************************************/
      
      // $objOperativoValidacionPersonal = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findBy(array(
      //   'institucioneducativa' => $form['sie'],
      //   'gestionTipo' => $form['gestion'],
      //   'notaTipo' => $periodo
      // ));

      // $arrValidacionPersonal = array();
      // if($objOperativoValidacionPersonal>0){
      //   foreach ($objOperativoValidacionPersonal as $key => $value) {
      //     # code...
      //     if($value->getRolTipo()->getId() == 2 || $value->getRolTipo()->getId() == 5)
      //       $arrValidacionPersonal[] = $value->getRolTipo()->getId();
      //   }
      // }
      // //validation docente Administrativo director
      // if(sizeof($arrValidacionPersonal)<2){
      //   $observation = true;
      //   $valPersonalAdm = true;
      // }
    }



    public function closeOperativoRudeAction (Request $request)
    {
      
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');
      $form['sie'] = hex2bin($form['sie']) ;
      $form['gestion'] = hex2bin($form['gestion']) ; 
      //ini var to validate info
      $observation = false;
      $inconsistencia = false;
      $objObsQA = false;
      $valPersonalAdm = false;

      //Sanitizamos las variables de ingreso
      //$form['sie']= filter_var($form['sie'],FILTER_VALIDATE_INT);
      //$form['gestion']= filter_var($form['gestion'],FILTER_VALIDATE_INT);


      //get the current operativo //Variable no utilizada
      //$objOperativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($form['sie'], $form['gestion']);
      
      //update the close operativo to registro consolido table //Variable no utilizada
      /*$objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $form['sie'],
        'gestion'         => $form['gestion']
      ));*/

      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
      if($registroConsol){
          $periodo = 0;
          if($registroConsol)
          {
              if($registroConsol->getBim1() == '0')
              {
                  $periodo = 1;
              }
          }
          //dump($form);die; 
          // exute validation
          $query = $em->getConnection()->prepare('select * from sp_validacion_regular_RUDE(:gestion, :sie)');
          $query->bindValue(':gestion', $form['gestion']);
          $query->bindValue(':sie', $form['sie']);      
          $query->execute();
          $inconsistencia = $query->fetchAll();

          /***********************************\
          * *
          * Validacion CONTROL DE CALIDAD
          * send array => sie, gestion, reglas *
          * return observations UE *
          * *
          \************************************/
          // $form['reglas'] = '1,2,3,8,10,12,13,16';
          /*$form['reglas'] = $this->reglasQA;
          $objObsQA = $this->getObservationQA($form);*/
          if($inconsistencia){
            $observation = true;
          }
          
          $valPersonalAdm = false;

          if($observation){
            return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
              'inconsistencia' => $inconsistencia,
              'objObsQA' => $objObsQA,
              'validacionPersonal' => $valPersonalAdm,
              'observation' => $observation,
              'norow' => false,
              'institucion' =>  $form['sie'],
              'gestion' => $form['gestion'],
              'periodo' => $periodo));
          }else{
              $registroConsolRude = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion'], 'rude' => 1));

              if($registroConsolRude){
                return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
                'observation' => false,
                'norow' => false,
                'institucion' =>  $form['sie'],
                'gestion' => $form['gestion'],
                'periodo' => 0));
              }else{

                $registroConsol->setRude(1);            
                $em->persist($registroConsol);
                $em->flush();
                $em->getConnection()->commit();
                return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
                'observation' => false,
                'norow' => false,
                'institucion' =>  $form['sie'],
                'gestion' => $form['gestion'],
                'periodo' => 0));

              }
              
          }        

      }else{

         return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
                'observation' => true,
                'norow' => true,
                'institucion' =>  $form['sie'],
                'gestion' => $form['gestion'],
                'periodo' => 0));

      }
      


    }


    public function downOperativoRudeAction(Request $request){
      return $this->redirect($this->generateUrl('principal_web'));
      // create DB conexion
      $em = $this->getDoctrine()->getManager();
      
      //get values send
      $form = $request->get('form');

      //get the file to generate the new file
      $dir = '/archivos/descargas/';
      // conver json values to array
      $arrData = json_decode($form['data'],true);
      
      // $objOperativoLog = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
      //   'institucioneducativa' => $arrData['id'],
      //   'gestionTipoId' => $arrData['gestion'],
      //   'institucioneducativaOperativoLogTipo' => 5,
      // ));

      // if($objOperativoLog){

      //     $message = "No se puede proceder por que el archivo ya fue descargado";
      //       $this->addFlash('notidonwloadrude', $message);
      //       $sw = false;
      //       $dataDownload = array(
      //         'sw' => $sw
      //       );  

      // }else{

          $objOperativo = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
            'unidadEducativa' => $arrData['id'],
            'gestion' => $arrData['gestion'],
          ));
          // dump($objOperativo);
          // dump($arrData);
          
          $cabecera = 'R';
          //before to donwload file remove the old
          $fileRude = $arrData['id'] . '-' . date('Y-m-d') . '_' . 'RB';
          system('rm -fr ' . $dir .$fileRude.'.sie' );      
          system('rm -fr ' . $dir .$fileRude.'.igm' );

          if($objOperativo /*&&  $objOperativo->getBim1()*/){
            $sw = true;
            //to generate the file execute de function
            $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_rude_txt('" . $arrData['id'] . "','" . $arrData['gestion'] . "','" . $cabecera . "');");
            $query->execute();

            $newGenerateFile = $arrData['id'] . '-' . date('Y-m-d') . '_' . 'RB';
            

            //decode base64
            $outputdata = system('base64 '.$dir.''.$newGenerateFile. '.sie  >> ' . $dir . 'NR' . $newGenerateFile . '.sie');

            system('rm -fr ' . $dir . $newGenerateFile.'.sie');
            exec('mv ' . $dir . 'NR' .$newGenerateFile . '.sie ' . $dir . $newGenerateFile . '.sie ');

            //name the file
           exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir  . $newGenerateFile . '.sie');
           exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
           $dataDownload = array(
              'file' => $newGenerateFile . '.igm ',
              'datadownload' => $form['data'],
              'sw' => $sw
            );
          }else{
            $message = "Problemas al descargar el archio, el archivo no presetan Inicio de Gestión";
            $this->addFlash('notidonwloadrude', $message);
            $sw = false;
            $dataDownload = array(
              'sw' => $sw
            );
          }   
          
      // }   

      
     return $this->render($this->session->get('pathSystem') . ':Inbox:downOperativoRude.html.twig', $dataDownload );
    }

    public function downloadAction(Request $request, $file,$datadownload) {
      // dump($datadownload);die;
      $form = json_decode($datadownload,true);
      $form['operativoTipo']=5;
      
      // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,1);
      $objOperativoLog = $this->get('funciones')->saveOperativoLog($form);
        //get path of the file
        //$dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
        $dir = '/archivos/descargas/';
        //remove space on the post values
        $file = preg_replace('/\s+/', '', $file);
        $file = str_replace('%20', '', $file);
        // save the log operativo
        
        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }

    public function closeOperativoInscriptionAction (Request $request)
    { 
      //dump("cierre Inscr");die; 
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');
      $form['sie'] = hex2bin($form['sie']) ;
      $form['gestion'] = hex2bin($form['gestion']) ;      
      //dump($form);die;
      //ini var to validate info
      $observation = false;
      $inconsistencia = false;
      $objObsQA = false;
      $valPersonalAdm = false;
      $periodo = 0;

      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
      //get the operativo number
      $operativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);
      // dump($operativo);die;
      // check the operative to find the correct vars
      switch ($operativo) {
        case 1:
        case 2:
        
          $opeTrim = $operativo + 5;
          $dbFunction = 'sp_validacion_regular_web2023_mg';
          break;
        case 3: 
          $opeTrim = $operativo + 5;
          $dbFunction = 'sp_validacion_regular_web2023_fg';                 
          break;
        case 4: 
          $opeTrim = ($operativo-1) + 5;
          $dbFunction = 'sp_validacion_regular_web2023_fg';                 
          break;
        
        default:
          $opeTrim = 0;
          $dbFunction = 'sp_validacion_regular_inscripcion_ig_web';
          break;
      }

      // dump($dbFunction);die;
      
      // $operativo = ($operativo < 1)?1:$operativo;
      $query = $em->getConnection()->prepare('select * from '.$dbFunction.'(:gestion, :sie, :valor)');
      $query->bindValue(':gestion', $form['gestion']);
      $query->bindValue(':sie', $form['sie']);      
      $query->bindValue(':valor',$opeTrim);      
      $query->execute();
      $inconsistencia = $query->fetchAll();
     
      if($inconsistencia){
        $observation = true;
      }
     
      // if(($this->session->get('ue_modular')!==NULL) && $this->session->get('ue_modular')){$observation=false;}
           
      if($observation){ 
        $this->session->set('donwloadLibreta', false);              
        return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
          'inconsistencia' => $inconsistencia,
          'objObsQA' => $objObsQA,
          'validacionPersonal' => $valPersonalAdm,
          'observation' => $observation,
          'norow' => false,
          'institucion' =>  $form['sie'],
          'gestion' => $form['gestion'],
          'periodo' => $periodo));
      }else{
        if(!$registroConsol){
            $registroConsol = new RegistroConsolidacion();
            $registroConsol->setTipo(1);
            $registroConsol->setGestion($form['gestion']);
            $registroConsol->setUnidadEducativa($form['sie']);
            $registroConsol->setTabla('**');
            $registroConsol->setIdentificador('**');
            $registroConsol->setCodigo('**');
            $registroConsol->setDescripcionError('Consolidado exitosamente!!');
            $registroConsol->setFecha(new \DateTime("now"));
            $registroConsol->setusuario($this->session->get('userId'));
            $registroConsol->setConsulta('*');
            $registroConsol->setBim1('0');
            $registroConsol->setBim2('0');
            $registroConsol->setBim3('0');
            $registroConsol->setBim4('0');
            $registroConsol->setPeriodoId(1);
            $registroConsol->setSubCea(0);
            $registroConsol->setBan(1);
            $registroConsol->setEsonline('t');
            $registroConsol->setInstitucioneducativaTipoId(1);

          }else{
            if($operativo <= 3 ){
              $fieldOpe = 'setBim' .$operativo;
              $registroConsol->$fieldOpe(2);

              $data = array(
                  'operativoTipo' => 3,
                  'gestion' => $form['gestion'],
                  'id' => $form['sie'],
                  'operativo' => $operativo,
              );   
              $this->saveLog($data);

              
            }
          }

            $em->persist($registroConsol);
            $em->flush();
            $em->getConnection()->commit();

            // get the flag to show the donwload libreta option
            if($operativo+1 == $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']))
              $this->session->set('donwloadLibreta', true);
            else
              $this->session->set('donwloadLibreta', false);              

          return $this->render($this->session->get('pathSystem') . ':Tramite:list_inconsistencia.html.twig', array(
            'observation' => false,
            'norow' => false,
            'institucion' =>  $form['sie'],
            'gestion' => $form['gestion'],
            'periodo' => 0));
      }

    }

    private function saveLog($data){
      $em = $this->getDoctrine()->getManager();

        $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
        
        //save the log data
        $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find($data['operativoTipo']));
        $objDownloadFilenewOpe->setGestionTipoId($data['gestion']);
        $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
        $objDownloadFilenewOpe->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['id']));
        $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
        $objDownloadFilenewOpe->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($data['operativo']+5));
        $objDownloadFilenewOpe->setDescripcion('...');
        $objDownloadFilenewOpe->setEsexitoso('t');
        $objDownloadFilenewOpe->setEsonline('t');
        $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
        $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
        $dataClient = json_encode(array('userAgent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>$_SERVER['HTTP_HOST']));
        $objDownloadFilenewOpe->setClienteDescripcion($dataClient);
        $em->persist($objDownloadFilenewOpe);
        $em->flush();      

      return 1;
    }
     

}
