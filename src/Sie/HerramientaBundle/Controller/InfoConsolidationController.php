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
 * InfoConsolidationController controller.
 *
 */
class InfoConsolidationController extends Controller {

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
       return array(
         '1'=>'82190066','82190032','82190082','82190056','82220075','82220110',
         '82220216','82220066','80390012','60390025','80440013','80440031',
         '80870039','80870078','80870069','80870043','70590010','80590021',
         '80590037','80590029','72460009','72460008','72460023','72460014',
         '81870039','81870028','81870041','81870037','81960030','61840025',
         '81870026','81870056','61710061','61710080','61710074','61710033',
         '81960054','82220075'
       );
     }

    /**
    *fill the sie's Regularizar
    **/
    public function fillSieRegularizarHumanistico(){
      return array(
        '1' => '81970073', '2' => '81970111','3' => '81730080', '4'=>'40730366'
        );
    }
    /**
       *fill the sie's modulars
       **/
       public function fillSieTecnicos(){
         return array(
            '1'=> '80950056',
                  '60900064',
                  '81981463',
                  '80540183',
                  '80730784',
                  '81410157',
                  '60730045',
                  '80980556',
                  '40730531',
                  '70710055',
                  '81930066',
                  '80900108',
                  '81981478',
                  '81730261',
                  '80730789',
                  //'82220182',
                  '80980575',
                  '50870063'
         );
       }

       /**
          *fill the sie's modulars
          **/
          public function fillSieNocutrnas(){
            return array(
              '1'=> '81730055',
                    '81730061',
                    '81730160',
                    '81730163',
                    '81730167',
                    '81730173',
                    '81680075',
                    '80980276'
            );
          }


      /**
      *fill the sie's general
      **/
      public function fillSieGeneral(){
       return array(
         '1'=> '81230085',
               '70440024',
               '50460019',
               '60420065',
               '60480035',
               '70470019',
               '70470037',
               '40710002',
               '80590040',
               '50630055',
               '70590017',
               '80590002',
               '60660003',
               '80930002',
               '70870043',
               '80880022',
               '80870046',
               '70940035',
               '81210036',
               '51230003',
               '71180012',
               '81160034',
               '71200018',
               '81400059',
               '81330001',
               '81430134',
               '81390020',
               '71480033',
               '71370060',
               '81680050',
               '71700033',
               '81840007',
               '81840013',
               '71920002',
               '81880050',
               '81880003',
               '81920001',
               '61960012',
               '72180001',
               '82190131',
               '82200044',
               '82460008',
               '62470004',
               '62460007',
               '62480003',
                '70420056',
                '80660099',
                '80650024',
                '70960008',
                '60890094',
                '81200019',
                '81110025',
                '61450095',
                '81440010',
                '81720095',
                '71930002',
                '80630028'


);
      }
  /**
   * list of request
   *
   */
  public function indexAction(Request $request) {

    $aAccess = array(5, 2, 9);
    if (in_array($this->session->get('roluser'), $aAccess)) {
        $institutionData1 = $this->getDataUe($this->session->get('ie_id'));
        //verificar si es IE
        if ($institutionData1) {

                //create the db connexion
                $em=$this->getDoctrine()->getManager();
                //get the session's values
                $this->session = $request->getSession();
                $id_usuario = $this->session->get('userId');

                //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
                $this->unidadEducativa = $this->session->get('ie_id');

                //validation if the user is logged
                if (!isset($id_usuario)) {
                    return $this->redirect($this->generateUrl('login'));
                }
                //get the sie and the name of sie
                //$arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
                $arrSieInfo = array();
                // $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));
                // $arrSieInfoUe = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
                //get the ue plena info
                //$objValidateUePlena=array();
                //if(!$arrSieInfo)
                $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $this->unidadEducativa));
                //set the modular variable if exist
                $this->session->set('ue_modular', (array_search("$this->unidadEducativa",$this->arrUeModular,true))?true:false);
                $this->session->set('ue_regularizar', (array_search("$this->unidadEducativa",$this->arrUeRegularizar,true)!=false)?true:false);
                $this->session->set('ue_noturna', (array_search("$this->unidadEducativa",$this->arrUeNocturnas,true)!=false)?true:false);

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
                    default:
                      # code...
                      break;
                  }

                }else{
                  $this->session->set('ue_tecteg', (array_search("$this->unidadEducativa",$this->arrUeTecTeg,true)!=false)?true:false);
                  $this->session->set('ue_plena', ($objValidateUePlena)?true:false);
                }


                $this->session->set('ue_general', (array_search("$this->unidadEducativa",$this->arrUeGeneral,true)!=false)?true:false);
                $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $this->session->get('ie_id'), 'gestion'=>$this->session->get('currentyear')-1));
                $this->operativoUe = $operativoPerUe;
                //get the current year
                $arrSieInfo[0]['gestion']= ($operativoPerUe-1 == 4)?$this->session->get('currentyear'):$this->session->get('currentyear')-1;
                $arrSieInfo[0]['id'] = $this->session->get('ie_id');
                //get the fuill ue info
                $arrFullUeInfo=array();
                $arrFullUeInfo =$arrSieInfo[0];
                //$arrFullUeInfo['ueplenainfo'] =$objValidateUePlena;

                //$objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));

                $repository = $em->getRepository('SieAppWebBundle:Tramite');




                // $objConsolidationInfo = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findBy(array(
                //    'unidadEducativa' => $this->session->get('ie_id')
                //  ));

                 $registroConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion');
                 $query = $registroConsolidation->createQueryBuilder('rc')
                         ->where('rc.unidadEducativa = :id')
                         ->setParameter('id', $this->session->get('ie_id'))
                         ->orderBy('rc.gestion', 'ASC')
                         ->getQuery();

                 $objConsolidationInfo = $query->getResult();


                 $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));

                 $dataInfo = array('id' => $this->session->get('ie_id'), 'gestion' => $this->session->get('currentyear'), 'ieducativa' => $objInstitucionEducativa);
                 //dump($objInstitucionEducativa);die;

                return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:index.html.twig', array(
                    'objValidateUePlena'=>($objValidateUePlena)?1:0,
                    'arrSieInfo'=>$arrSieInfo[0],
                    'gestion'=>$this->session->get('currentyear'),
                    'infoConsolidations'    => $objConsolidationInfo,
                    'institucionEducativa' => $objInstitucionEducativa,
                    'data'=>$dataInfo
                ));

        } else {
            return $this->render('SieHerramientaBundle:InfoConsolidation:find.html.twig');
        }
    } else {
        return $this->render('SieHerramientaBundle:InfoConsolidation:find.html.twig');
    }


  }

  public function consolidacionGestionAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $gestionactual = $this->session->get('currentyear');
    $roluser = $this->session->get('roluser');
    $roluserlugarid = $this->session->get('roluserlugarid');
    $bundle = $this->session->get('pathSystem');

    switch ($bundle) {
        case 'SieRegularBundle':
        case 'SieHerramientaBundle':
            $instipoid = 1;
            $mingestion = 2014;
            $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
            $label = 'Cantidad de Unidades Educativas que reportaron información';
            $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
            break;

        case 'SieEspecialBundle':
            $instipoid = 4;
            $mingestion = 2013;
            $title = 'Reporte de cierre de operativos por gestión y Centro de Educación Especial';
            $label = 'Cantidad de Centros que reportaron matrícula';
            $label_distrito = 'Cantidad de Centros que reportaron matrícula en el distrito';
            break;
        
        default:
            $instipoid = 1;
            $mingestion = 2014;
            $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
            $label = 'Cantidad de Unidades Educativas que reportaron información';
            $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
            break;
    }
    
    $consol = $this->get('sie_app_web.funciones')->reporteConsol($gestionactual, $roluser, $roluserlugarid, $instipoid);

    switch ($roluser) {
        case 8:
            $ues = $this->get('sie_app_web.funciones')->estadisticaConsolNal($gestionactual, $instipoid);
            break;

        case 7:
            $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDptal($gestionactual, $roluserlugarid, $instipoid);
            break;

        case 10:
            $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDtal($gestionactual, $roluserlugarid, $instipoid);
            break;

        default:
            $ues = null;
            break;
    }

    $gestiones = $em->getRepository('SieAppWebBundle:GestionTipo')->findBy(array(), array('id' => 'DESC'));

    $gestionesArray = array();
    
    foreach ($gestiones as $value) {
        if ($value->getId() >= $mingestion) {
            $gestionesArray[$value->getId()] = $value->getGestion();
        }
    }
    return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:index_gestion.html.twig', array(
        'consol' => $consol,
        'gestiones' => $gestionesArray,
        'gestionactual' => $gestionactual,
        'title' => $title,
        'label' => $label,
        'label_distrito' => $label_distrito,
        'ues' => $ues
    ));
  }

  private function getDataUe($id) {

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa');
      $query = $entity->createQueryBuilder('i')
              ->select('i.id', 'i.institucioneducativa', 'dt.dependencia', 'ct.convenio', 'it.descripcion')
              //->select('mi.id as maInsId', 'i.id as instId, i.institucioneducativa', 'p.id as personId, p.paterno, p.materno, p.nombre,p.carnet')
              ->leftJoin('SieAppWebBundle:DependenciaTipo', 'dt', 'WITH', 'i.dependenciaTipo=dt.id')
              ->leftJoin('SieAppWebBundle:ConvenioTipo', 'ct', 'WITH', 'i.convenioTipo=ct.id')
              ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo=it.id')
              ->where('i.id = :id')
              ->setParameter('id', $id)
              ->getQuery();
      try {
          return $query->getResult();
      } catch (\Doctrine\ORM\NoResultException $e) {
          return null;
      }
  }

  /**
   * list of request
   *
   */
  public function findAction(Request $request) {


    //get data send
    $form = $request->get('form');
    // dump($form);die;

    $institutionData1 = $this->getDataUe($form['codigoSie']);
    //verificar si es IE
    if ($institutionData1) {

            //create the db connexion
            $em=$this->getDoctrine()->getManager();
            //get the session's values
            $this->session = $request->getSession();
            $id_usuario = $this->session->get('userId');

            //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
            $this->unidadEducativa = $form['codigoSie'];

            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }
            //get the sie and the name of sie
            //$arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
            $arrSieInfo = array();
            // $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));
            // $arrSieInfoUe = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
            //get the ue plena info
            //$objValidateUePlena=array();
            //if(!$arrSieInfo)
            $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $this->unidadEducativa));
            //set the modular variable if exist
            $this->session->set('ue_modular', (array_search("$this->unidadEducativa",$this->arrUeModular,true))?true:false);
            $this->session->set('ue_regularizar', (array_search("$this->unidadEducativa",$this->arrUeRegularizar,true)!=false)?true:false);
            $this->session->set('ue_noturna', (array_search("$this->unidadEducativa",$this->arrUeNocturnas,true)!=false)?true:false);

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
                default:
                  # code...
                  break;
              }

            }else{
              $this->session->set('ue_tecteg', (array_search("$this->unidadEducativa",$this->arrUeTecTeg,true)!=false)?true:false);
              $this->session->set('ue_plena', ($objValidateUePlena)?true:false);
            }


            $this->session->set('ue_general', (array_search("$this->unidadEducativa",$this->arrUeGeneral,true)!=false)?true:false);
            $operativoPerUe = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=> $form['codigoSie'], 'gestion'=>$this->session->get('currentyear')-1));
            $this->operativoUe = $operativoPerUe;
            //get the current year
            $arrSieInfo[0]['gestion']= ($operativoPerUe-1 == 4)?$this->session->get('currentyear'):$this->session->get('currentyear')-1;
            $arrSieInfo[0]['id'] = $form['codigoSie'];
            //get the fuill ue info
            $arrFullUeInfo=array();
            $arrFullUeInfo =$arrSieInfo[0];
            //$arrFullUeInfo['ueplenainfo'] =$objValidateUePlena;

            //$objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));

            $repository = $em->getRepository('SieAppWebBundle:Tramite');


            // $objConsolidationInfo = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findBy(array(
            //    'unidadEducativa' => $form['codigoSie']
            //  ));

             $registroConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion');
             $query = $registroConsolidation->createQueryBuilder('rc')
                     ->where('rc.unidadEducativa = :id')
                     ->setParameter('id', $form['codigoSie'])
                     ->orderBy('rc.gestion', 'ASC')
                     ->getQuery();

             $objConsolidationInfo = $query->getResult();


// dump($objConsolidationInfo);
             $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codigoSie']);

             $dataInfo = array('id' => $form['codigoSie'], 'gestion' => $this->session->get('currentyear'), 'ieducativa' => $objInstitucionEducativa);
             //dump($objInstitucionEducativa);die;
// dump($dataInfo);die;
            return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:index.html.twig', array(
                'objValidateUePlena'=>($objValidateUePlena)?1:0,
                'arrSieInfo'=>$arrSieInfo[0],
                'gestion'=>$this->session->get('currentyear'),
                'infoConsolidations'    => $objConsolidationInfo,
                'institucionEducativa' => $objInstitucionEducativa,
                'data'=>$dataInfo
            ));

    } else {
        return $this->render('SieHerramientaBundle:InfoConsolidation:find.html.twig');
    }


  }

}
