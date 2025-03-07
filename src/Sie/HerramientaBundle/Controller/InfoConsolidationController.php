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


use Sie\AppWebBundle\Entity\DepartamentoTipo;
use Sie\AppWebBundle\Entity\DistritoTipo;

use Doctrine\DBAL\Types\Type;
Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTimeType');

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
  public function indexAction(Request $request) { //? (--> 6)

    $aAccess = array(5, 2, 9); //? en el sidebar indica que solo los roles 7,8,9,10 pueden ingresar
    //? confuso
    if (in_array($this->session->get('roluser'), $aAccess)) {
        $institutionData1 = $this->getDataUe($this->session->get('ie_id')); //? obtiene el instituto educativo
        if ($institutionData1) {
          //create the db connexion
          $em=$this->getDoctrine()->getManager();
          //get the session's values
          $this->session = $request->getSession();
          $id_usuario = $this->session->get('userId');

          $this->unidadEducativa = $this->session->get('ie_id');

          //validation if the user is logged
          if (!isset($id_usuario)) {
              return $this->redirect($this->generateUrl('login'));
          }
          //get the sie and the name of sie
          $arrSieInfo = array();
          $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $this->unidadEducativa));
          //? Esta haciendo busquedas con listas predefinidas como variables
          $this->session->set('ue_modular', (array_search("$this->unidadEducativa",$this->arrUeModular,true))?true:false);
          $this->session->set('ue_regularizar', (array_search("$this->unidadEducativa",$this->arrUeRegularizar,true)!=false)?true:false);
          $this->session->set('ue_noturna', (array_search("$this->unidadEducativa",$this->arrUeNocturnas,true)!=false)?true:false);


          if($objValidateUePlena){
            //cambiar al tipo de ue
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

          $repository = $em->getRepository('SieAppWebBundle:Tramite');

          $registroConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion');
          $query = $registroConsolidation->createQueryBuilder('rc')
                  ->where('rc.unidadEducativa = :id')
                  ->setParameter('id', $this->session->get('ie_id'))
                  ->orderBy('rc.gestion', 'ASC')
                  ->getQuery();

          $objConsolidationInfo = $query->getResult(); //? Obtiene todos los registros consolidados de la unidad

          $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));

          $dataInfo = array('id' => $this->session->get('ie_id'), 'gestion' => $this->session->get('currentyear'), 'ieducativa' => $objInstitucionEducativa);

          // dump($this->session->get('pathSystem'));
          // dump($objValidateUePlena);
          // dump($arrSieInfo[0]);
          // dump($objConsolidationInfo);
          // dump($objInstitucionEducativa);
          // dump($dataInfo);
          // die;
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

  public function consolidacionSextoAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $gestionactual = $this->session->get('currentyear');
    $roluser = $this->session->get('roluser');
    $roluserlugarid = $this->session->get('roluserlugarid');
    $bundle = $this->session->get('pathSystem');


    $title = 'Reporte de consolidación de operativos 6to Secundaria - GESTIÓN :'.$gestionactual;
    $label = 'Cantidad de Unidades Educativas que reportaron información';
    $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
            
    $instipoid=1;
    
    $consulta = '';
    $params = []; // Array para almacenar los parámetros dinámicos

    switch ($roluser) {
        case 8: // Rol general, sin restricciones
            $consulta = '';  
            break;

        case 7: // Rol por departamento
            $lugardde = $em->getRepository('SieAppWebBundle:LugarTipo')->find($roluserlugarid);
            if ($lugardde) {
                $consulta = ' WHERE wies.departamento_codigo = :departamentoCodigo ';
                $params['departamentoCodigo'] = $lugardde->getCodigo();
            }
            break;

        case 10: // Rol por distrito
            $consulta = ' WHERE wies.distrito_id = :distritoId ';
            $params['distritoId'] = $roluserlugarid;
            break;

        default:
            $ues = null;
            break;
    }

    $queryEntidad = $em->getConnection()->prepare("
        SELECT DISTINCT 
            wies.departamento_codigo,
            wies.departamento,
            wies.distrito_id,
            wies.distrito_codigo,
            wies.distrito,
            wies.institucioneducativa_id,
            wies.institucioneducativa,
            wies.dependencia_id,
            wies.dependencia,
            wies.turno_id,
            wies.turno,
            wies.area,
            wies.niveles_id,
            wies.niveles_abrv,
            wies.estadoinstitucion_id,
            wies.est,
            CASE 
                WHEN iol.institucioneducativa_id IS NOT NULL THEN 'SI' 
                ELSE 'NO' 
            END AS cal,
            CASE 
                WHEN mc.persona_id IS NOT NULL THEN 'SI' 
                ELSE 'NO' 
            END AS director_ibd,
             CASE 
                WHEN eibd.institucioneducativa_id IS NOT NULL THEN 'SI' 
                ELSE 'NO' 
            END AS estudiante_ibd
        FROM vm_instituciones_educativas_sexto wies 
        LEFT JOIN institucioneducativa_operativo_log iol 
            ON iol.institucioneducativa_id = wies.institucioneducativa_id 
            AND iol.institucioneducativa_operativo_log_tipo_id = 10 
            AND iol.gestion_tipo_id = :gestionactual
        LEFT JOIN maestro_cuentabancaria mc 
            ON wies.institucioneducativa_id = mc.institucioneducativa_id 
            AND mc.gestion_tipo_id = :gestionactual 
            AND mc.esoficial = TRUE
        left join (
          select distinct  institucioneducativa_id 
          from estudiante_destacado_automatico_auditoria edaa 
          where gestion_tipo_id = :gestionactual
          and edaa.tipo_operacion = 'INSERCION'
          ) eibd on eibd.institucioneducativa_id = wies.institucioneducativa_id
        $consulta
        ORDER BY 1, 3, 5
    ");

    // Asignación de parámetros
    $queryEntidad->bindValue(':gestionactual', $gestionactual, \PDO::PARAM_INT);

    // Asignar los parámetros dinámicos de `$consulta`
    foreach ($params as $key => $value) {
        $queryEntidad->bindValue(":$key", $value, \PDO::PARAM_INT);
    }

    // Ejecutar la consulta
    $queryEntidad->execute();
    $objEntidad = $queryEntidad->fetchAll();
    
    return $this->render('SieHerramientaBundle:InfoConsolidation:index_sexto.html.twig', array(
        'consol' => $objEntidad,
        'gestion' => $gestionactual,
        'title' => $title,
        'label' => $label,
        'label_distrito' => $label_distrito
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





  public function reporteModalidadAtencionAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $objDepto =  $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
    $arrDepto = array();
    foreach ($objDepto as $value) {
      $arrDepto[] = array('id'=>$value->getId(),'codigo'=>$value->getCodigo(),'depto'=>$value->getDepartamento());
    }
    
    return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:reporte_modalidad_atencion.html.twig', array(
      'arrDepto' => $arrDepto,
    ));
  }


  public function reporteInicioActividadesAction(Request $request)
  {
    if($request->isMethod('POST'))
    {
      if($request->get('_departamento'))
        $this->session->set('_departamento',$request->get('_departamento'));
      if($request->get('_distrito'))
        $this->session->set('_distrito',$request->get('_distrito'));
    }
    else
    {
        $this->session->set('_departamento',-1);
        $this->session->set('_distrito',-1);
    }

    $departamento=-1;
    $distrito=-1;
    $userId=$this->session->get('userId');
    $userRol=$this->session->get('roluser');
    $datosUser=$this->getDatosUsuario($userId,$userRol);
    if($datosUser)
    {
      $departamentoDistrito=$datosUser['cod_dis'];
      list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);
      //$departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):$departamento;
      //$distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):$distrito;
      $departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):($this->session->get('_departamento') && $this->session->get('_departamento')!=-1)?$this->session->get('_departamento'):$departamento;;
      $distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):($this->session->get('_distrito') && $this->session->get('_distrito')!=-1)?$this->session->get('_distrito'):$distrito;
    }
    else
    {
      return $this->redirect($this->generateUrl('login'));
    }

    $gestion = ($request->get('_gestion'))?$request->get('_gestion'):date('Y');
    $mes = ($request->get('_mes'))?$request->get('_mes'):date('n');

    $todo=$this->getDatosInicioActividadesGeneral($departamento,$distrito,$gestion,$mes);
    $datos=$this->getDatosInicioActividadesPorDepartamento($departamento,$distrito,$gestion,$mes);
    $highcharts=$this->darFormatoHighcharts($todo);

    return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:reporte_inicio_actividades.html.twig', array(
      'etiqueta'=> 'Departamentos',
      'url'=>'herramienta_inforeporte_inicio_actividades_distrito',
      'url_form'=>'herramienta_inforeporte_inicio_actividades',
      'gestion'=> $gestion,
      'mes'=> $mes,
      'departamento'=> $departamento,
      'distrito'=> $distrito,
      'todo' => $todo,
      'datos' => $datos,
      'highcharts'=>$highcharts,
      'departamento_nom'=>$this->getNombreDepartamento($departamento),
      'distrito_nom'=>$this->getNombreDistrito($distrito),
    ));
  }

  public function reporteInicioActividadesDistritoAction(Request $request)
  {
    if($request->get('_departamento'))
      $this->session->set('_departamento',$request->get('_departamento'));
    if($request->get('_distrito'))
      $this->session->set('_distrito',$request->get('_distrito'));

    $departamento=-1;
    $distrito=-1;
    $userId=$this->session->get('userId');
    $userRol=$this->session->get('roluser');
    $datosUser=$this->getDatosUsuario($userId,$userRol);
    if($datosUser)
    {
      $departamentoDistrito=$datosUser['cod_dis'];
      list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);
      //$departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):$departamento;
      //$distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):$distrito;
      $departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):($this->session->get('_departamento') && $this->session->get('_departamento')!=-1)?$this->session->get('_departamento'):$departamento;;
      $distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):($this->session->get('_distrito') && $this->session->get('_distrito')!=-1)?$this->session->get('_distrito'):$distrito;
    }
    else
    {
      return $this->redirect($this->generateUrl('login'));
    }

    $gestion = ($request->get('_gestion'))?$request->get('_gestion'):date('Y');
    $mes = ($request->get('_mes'))?$request->get('_mes'):date('n');

    $todo=$this->getDatosInicioActividadesGeneral($departamento,$distrito,$gestion,$mes);
    $datos=$this->getDatosInicioActividadesPorDistrito($departamento,$distrito,$gestion,$mes);
    $highcharts=$this->darFormatoHighcharts($todo);

    return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:reporte_inicio_actividades.html.twig', array(
      'etiqueta'=> 'Distritos',
      'url'=>'herramienta_inforeporte_inicio_actividades_distrito_colegio',
      'url_form'=>'herramienta_inforeporte_inicio_actividades_distrito',
      'gestion'=> $gestion,
      'mes'=> $mes,
      'departamento'=> $departamento,
      'distrito'=> $distrito,
      'todo' => $todo,
      'datos' => $datos,
      'highcharts'=>$highcharts,
      'departamento_nom'=>$this->getNombreDepartamento($departamento),
      'distrito_nom'=>$this->getNombreDistrito($distrito),
    ));
  }

  public function reporteInicioActividadesColegioAction(Request $request)
  {
    if($request->get('_departamento'))
      $this->session->set('_departamento',$request->get('_departamento'));
    if($request->get('_distrito'))
      $this->session->set('_distrito',$request->get('_distrito'));

    $departamento=-1;
    $distrito=-1;
    $userId=$this->session->get('userId');
    $userRol=$this->session->get('roluser');
    $datosUser=$this->getDatosUsuario($userId,$userRol);
    if($datosUser)
    {
      $departamentoDistrito=$datosUser['cod_dis'];
      list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);
      //$departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):$departamento;
      //$distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):$distrito;
      $departamento = ($request->get('_departamento') && $request->get('_departamento')!=-1)?$request->get('_departamento'):($this->session->get('_departamento') && $this->session->get('_departamento')!=-1)?$this->session->get('_departamento'):$departamento;;
      $distrito = ($request->get('_distrito') && $request->get('_distrito')!=-1)?$request->get('_distrito'):($this->session->get('_distrito') && $this->session->get('_distrito')!=-1)?$this->session->get('_distrito'):$distrito;
    }
    else
    {
      return $this->redirect($this->generateUrl('login'));
    }

    $gestion = ($request->get('_gestion'))?$request->get('_gestion'):date('Y');
    $mes = ($request->get('_mes'))?$request->get('_mes'):date('n');

    $todo=$this->getDatosInicioActividadesGeneral($departamento,$distrito,$gestion,$mes);
    $datos=$this->getDatosInicioActividadesPorColegio($departamento,$distrito,$gestion,$mes);
    $highcharts=$this->darFormatoHighcharts($todo);

    return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:reporte_inicio_actividades.html.twig', array(
      'etiqueta'=> 'Unidad educativa',
      'url'=>'#',
      'url_form'=>'herramienta_inforeporte_inicio_actividades_distrito_colegio',
      'gestion'=> $gestion,
      'mes'=> $mes,
      'departamento'=> $departamento,
      'distrito'=> $distrito,
      'todo' => $todo,
      'datos' => $datos,
      'highcharts'=>$highcharts,
      'departamento_nom'=>$this->getNombreDepartamento($departamento),
      'distrito_nom'=>$this->getNombreDistrito($distrito),
    ));
  }


    //Obtine la SUMATORIA TOTAL de quienes iniciaron actividades y quienes no (A nivel bolivia) 
    private function getDatosInicioActividadesGeneral($departamento=-1,$distrito=-1,$gestion=-1,$mes=-1)
    {
        $general=array();

        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorGestion=($gestion==-1)?' <> ':' = ';
        $operadorMes=($mes==-1)?' <> ':' = ';

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query =" 
          select 
          --cod_dep,des_dep,cod_dis,des_dis,institucioneducativa_id,ue_nom,riesgo_unidadeducativa,dependencia,
          total,inicio, no_inicio,
          riesgo_sanitario,helada,granizada,sismo,riada,deslizamiento,sequia,incendio,inundacion,otros,sin_riesgo

          from 
          (
            select 
            --substring(cod_dis,1,1) as cod_dep,  case substring(cod_dis,1,1)   when '1' then 'CHUQUISACA'  when '2' then 'LA PAZ'  when '3' then 'COCHABAMBA'  when '4' then 'ORURO'   when '5' then 'POTOSI'  when '6' then 'TARIJA'  when '7' then 'SANTA CRUZ'  when '8' then 'BENI'  when '9' then 'PANDO'   end des_dep,
            --cod_dis,des_dis,institucioneducativa_id,institucioneducativa ue_nom,dependencia,string_agg(riesgo_unidadeducativa, ', ' ORDER BY riesgo_unidadeducativa) as riesgo_unidadeducativa,

                    sum(case when institucioneducativa_id is not null then 1 else 0 end)  as \"total\",
                    sum(case when riesgo_unidadeducativa is not null then 1 else 0 end)  as \"inicio\",
                    sum(case when riesgo_unidadeducativa is null then 1 else 0 end)  as \"no_inicio\",

                    SUM (CASE WHEN riesgo_unidadeducativa='Riesgo Sanitario'  THEN 1 ELSE 0 END ) AS \"riesgo_sanitario\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Helada'  THEN 1 ELSE 0 END ) AS \"helada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Granizada'  THEN 1 ELSE 0 END ) AS \"granizada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sismo'  THEN 1 ELSE 0 END ) AS \"sismo\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Riada'  THEN 1 ELSE 0 END ) AS \"riada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Deslizamiento'  THEN 1 ELSE 0 END ) AS \"deslizamiento\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sequia'  THEN 1 ELSE 0 END ) AS \"sequia\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Incendio'  THEN 1 ELSE 0 END ) AS \"incendio\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Inundación' THEN 1 ELSE 0 END ) AS \"inundacion\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Otros'  THEN 1 ELSE 0 END ) AS \"otros\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sin riesgo'  THEN 1 ELSE 0 END ) AS \"sin_riesgo\"

            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              ,(select riesgo_unidadeducativa from riesgo_unidadeducativa_tipo where id=f.riesgo_unidadeducativa_tipo_id) as riesgo_unidadeducativa
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
                      left join (select a.riesgo_unidadeducativa_tipo_id,b.institucioneducativa_id
                          from institucioneducativa_sucursal_riesgo_mes  a
                            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id=b.id
                        where b.gestion_tipo_id ".$operadorGestion." ? and a.mes ".$operadorMes." ?) f on c.id=f.institucioneducativa_id 
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis ".$operadorDistrito." ?
          and substring(cod_dis,1,1) ".$operadorDepartamento." ?
          --group by cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia
          ) a;";
        $stmt = $db->prepare($query);
        $params = array($gestion,$mes,$distrito,$departamento);
        $stmt->execute($params);
        $general=$stmt->fetchAll();

        return $general;
    }

    private function getDatosInicioActividadesPorDepartamento($departamento=-1,$distrito=-1,$gestion=-1,$mes=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorGestion=($gestion==-1)?' <> ':' = ';
        $operadorMes=($mes==-1)?' <> ':' = ';

        $departamentos=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->session->get('ie_id')));
        $query =" 
          select 
          cod_dep as departamento_id,
          des_dep as departamento_nom,

          total,inicio, no_inicio,
          riesgo_sanitario,helada,granizada,sismo,riada,deslizamiento,sequia,incendio,inundacion,otros,sin_riesgo

          from 
          (
            select 
            substring(cod_dis,1,1) as cod_dep,  case substring(cod_dis,1,1)   when '1' then 'CHUQUISACA'  when '2' then 'LA PAZ'  when '3' then 'COCHABAMBA'  when '4' then 'ORURO'   when '5' then 'POTOSI'  when '6' then 'TARIJA'  when '7' then 'SANTA CRUZ'  when '8' then 'BENI'  when '9' then 'PANDO'   end des_dep,

                    sum(case when institucioneducativa_id is not null then 1 else 0 end)  as \"total\",
                    sum(case when riesgo_unidadeducativa is not null then 1 else 0 end)  as \"inicio\",
                    sum(case when riesgo_unidadeducativa is null then 1 else 0 end)  as \"no_inicio\",

                    SUM (CASE WHEN riesgo_unidadeducativa='Riesgo Sanitario'  THEN 1 ELSE 0 END ) AS \"riesgo_sanitario\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Helada'  THEN 1 ELSE 0 END ) AS \"helada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Granizada'  THEN 1 ELSE 0 END ) AS \"granizada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sismo'  THEN 1 ELSE 0 END ) AS \"sismo\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Riada'  THEN 1 ELSE 0 END ) AS \"riada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Deslizamiento'  THEN 1 ELSE 0 END ) AS \"deslizamiento\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sequia'  THEN 1 ELSE 0 END ) AS \"sequia\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Incendio'  THEN 1 ELSE 0 END ) AS \"incendio\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Inundación' THEN 1 ELSE 0 END ) AS \"inundacion\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Otros'  THEN 1 ELSE 0 END ) AS \"otros\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sin riesgo'  THEN 1 ELSE 0 END ) AS \"sin_riesgo\"

            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              ,(select riesgo_unidadeducativa from riesgo_unidadeducativa_tipo where id=f.riesgo_unidadeducativa_tipo_id) as riesgo_unidadeducativa
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
                      left join (select a.riesgo_unidadeducativa_tipo_id,b.institucioneducativa_id
                          from institucioneducativa_sucursal_riesgo_mes  a
                            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id=b.id
                        where b.gestion_tipo_id ".$operadorGestion." ? and a.mes ".$operadorMes." ?) f on c.id=f.institucioneducativa_id 
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis ".$operadorDistrito." ?
          and substring(cod_dis,1,1) ".$operadorDepartamento." ?
          GROUP BY  cod_dep,des_dep
          ) a;
        ";
        $stmt = $db->prepare($query);
        $params = array($gestion,$mes,$distrito,$departamento);
        $stmt->execute($params);
        $departamentos=$stmt->fetchAll();

        return $departamentos;
    }

    private function getDatosInicioActividadesPorDistrito($departamento=-1,$distrito=-1,$gestion=-1,$mes=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorGestion=($gestion==-1)?' <> ':' = ';
        $operadorMes=($mes==-1)?' <> ':' = ';

        //$gestion=($gestion==-1)?date('Y'):$gestion;
        //$mes=($mes==-1)?date('m'):$mes;

        $departamentos=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->session->get('ie_id')));
        $query =" 
          select 
          cod_dep as departamento_id,
          des_dep as departamento_nom,
          cod_dis as distrito_id,
          des_dis as distrito_nom,

          total,inicio, no_inicio,
          riesgo_sanitario,helada,granizada,sismo,riada,deslizamiento,sequia,incendio,inundacion,otros,sin_riesgo

          from 
          (
            select 
            substring(cod_dis,1,1) as cod_dep,  case substring(cod_dis,1,1)   when '1' then 'CHUQUISACA'  when '2' then 'LA PAZ'  when '3' then 'COCHABAMBA'  when '4' then 'ORURO'   when '5' then 'POTOSI'  when '6' then 'TARIJA'  when '7' then 'SANTA CRUZ'  when '8' then 'BENI'  when '9' then 'PANDO'   end des_dep,
            cod_dis,des_dis,

                    sum(case when institucioneducativa_id is not null then 1 else 0 end)  as \"total\",
                    sum(case when riesgo_unidadeducativa is not null then 1 else 0 end)  as \"inicio\",
                    sum(case when riesgo_unidadeducativa is null then 1 else 0 end)  as \"no_inicio\",

                    SUM (CASE WHEN riesgo_unidadeducativa='Riesgo Sanitario'  THEN 1 ELSE 0 END ) AS \"riesgo_sanitario\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Helada'  THEN 1 ELSE 0 END ) AS \"helada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Granizada'  THEN 1 ELSE 0 END ) AS \"granizada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sismo'  THEN 1 ELSE 0 END ) AS \"sismo\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Riada'  THEN 1 ELSE 0 END ) AS \"riada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Deslizamiento'  THEN 1 ELSE 0 END ) AS \"deslizamiento\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sequia'  THEN 1 ELSE 0 END ) AS \"sequia\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Incendio'  THEN 1 ELSE 0 END ) AS \"incendio\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Inundación' THEN 1 ELSE 0 END ) AS \"inundacion\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Otros'  THEN 1 ELSE 0 END ) AS \"otros\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sin riesgo'  THEN 1 ELSE 0 END ) AS \"sin_riesgo\"

            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              ,(select riesgo_unidadeducativa from riesgo_unidadeducativa_tipo where id=f.riesgo_unidadeducativa_tipo_id) as riesgo_unidadeducativa
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
                      left join (select a.riesgo_unidadeducativa_tipo_id,b.institucioneducativa_id
                          from institucioneducativa_sucursal_riesgo_mes  a
                            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id=b.id
                        where b.gestion_tipo_id ".$operadorGestion." ? and a.mes ".$operadorMes." ?) f on c.id=f.institucioneducativa_id 
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis ".$operadorDistrito." ?
          and substring(cod_dis,1,1) ".$operadorDepartamento." ?
          group by cod_dep,des_dep,cod_dis,des_dis
          ) a;";
        $stmt = $db->prepare($query);
        $params = array($gestion,$mes,$distrito,$departamento);
        $stmt->execute($params);
        $distritos=$stmt->fetchAll();

        return $distritos;
    }

    private function getDatosInicioActividadesPorColegio($departamento=-1,$distrito=-1,$gestion=-1,$mes=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorGestion=($gestion==-1)?' <> ':' = ';
        $operadorMes=($mes==-1)?' <> ':' = ';

        $departamentos=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->session->get('ie_id')));

        $query =" 
          select 
          cod_dep as departamento_id,
          des_dep as departamento_nom,
          cod_dis as distrito_id,
          des_dis as distrito_nom,
          institucioneducativa_id ue_id,
          institucioneducativa as ue_nom,
          riesgo_unidadeducativa,
          dependencia,

          total,inicio, no_inicio,
          riesgo_sanitario,helada,granizada,sismo,riada,deslizamiento,sequia,incendio,inundacion,otros,sin_riesgo

          from 
          (
            select 
            substring(cod_dis,1,1) as cod_dep,  case substring(cod_dis,1,1)   when '1' then 'CHUQUISACA'  when '2' then 'LA PAZ'  when '3' then 'COCHABAMBA'  when '4' then 'ORURO'   when '5' then 'POTOSI'  when '6' then 'TARIJA'  when '7' then 'SANTA CRUZ'  when '8' then 'BENI'  when '9' then 'PANDO'   end des_dep,
            cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia,string_agg(riesgo_unidadeducativa, ', ' ORDER BY riesgo_unidadeducativa) as riesgo_unidadeducativa,

                    sum(case when institucioneducativa_id is not null then 1 else 0 end)  as \"total\",
                    sum(case when riesgo_unidadeducativa is not null then 1 else 0 end)  as \"inicio\",
                    sum(case when riesgo_unidadeducativa is null then 1 else 0 end)  as \"no_inicio\",

                    SUM (CASE WHEN riesgo_unidadeducativa='Riesgo Sanitario'  THEN 1 ELSE 0 END ) AS \"riesgo_sanitario\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Helada'  THEN 1 ELSE 0 END ) AS \"helada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Granizada'  THEN 1 ELSE 0 END ) AS \"granizada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sismo'  THEN 1 ELSE 0 END ) AS \"sismo\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Riada'  THEN 1 ELSE 0 END ) AS \"riada\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Deslizamiento'  THEN 1 ELSE 0 END ) AS \"deslizamiento\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sequia'  THEN 1 ELSE 0 END ) AS \"sequia\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Incendio'  THEN 1 ELSE 0 END ) AS \"incendio\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Inundación' THEN 1 ELSE 0 END ) AS \"inundacion\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Otros'  THEN 1 ELSE 0 END ) AS \"otros\",
                    SUM (CASE WHEN riesgo_unidadeducativa='Sin riesgo'  THEN 1 ELSE 0 END ) AS \"sin_riesgo\"

            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              ,(select riesgo_unidadeducativa from riesgo_unidadeducativa_tipo where id=f.riesgo_unidadeducativa_tipo_id) as riesgo_unidadeducativa
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
                      left join (select a.riesgo_unidadeducativa_tipo_id,b.institucioneducativa_id
                          from institucioneducativa_sucursal_riesgo_mes  a
                            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id=b.id
                        where b.gestion_tipo_id=? and a.mes=?) f on c.id=f.institucioneducativa_id 
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis=?
          and substring(cod_dis,1,1)=?
          group by cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia
          ) a;";

        $stmt = $db->prepare($query);
        $params = array($gestion,$mes,$distrito,$departamento);
        $stmt->execute($params);
        $colegios=$stmt->fetchAll();

        return $colegios;
    }

    private function darFormatoHighcharts($dato)
    {
      $datoInicioActividades=array();
      $datosRiesgo=array();
      foreach ($dato as $i)
      {
        $total = $i['total'];
        $inicio = $i['inicio'];
        $no_inicio = $i['no_inicio'];

        $helada = $i['helada'];
        $granizada = $i['granizada'];
        $sismo = $i['sismo'];
        $riada = $i['riada'];
        $deslizamiento = $i['deslizamiento'];
        $sequia = $i['sequia'];
        $incendio = $i['incendio'];
        $inundación = $i['inundacion'];
        $otros = $i['otros'];
        $riesgo_sanitario = $i['riesgo_sanitario'];
        $sin_riesgo = $i['sin_riesgo'];

        if($total>0)
        {
          $datoInicioActividades=array(
            array('name'=>'Si','y'=>round(($inicio*100)/($total), 1),       'label'=>$inicio ),
            array('name'=>'No','y'=>round(($no_inicio*100)/($total), 1),    'label'=>$no_inicio ),
          );
          $datosRiesgo=array(
            array('name'=>'Helada',             'y'=>round(($helada*100)/($total), 1),            'label'=>$helada ),
            array('name'=>'Granizada',          'y'=>round(($granizada*100)/($total), 1),         'label'=>$granizada ),
            array('name'=>'Sismo',              'y'=>round(($sismo*100)/($total), 1),             'label'=>$sismo ),
            array('name'=>'Riada',              'y'=>round(($riada*100)/($total), 1),             'label'=>$riada ),
            array('name'=>'Deslizamiento',      'y'=>round(($deslizamiento*100)/($total), 1),     'label'=>$deslizamiento ),
            array('name'=>'Sequia',             'y'=>round(($sequia*100)/($total), 1),            'label'=>$sequia ),
            array('name'=>'Incendio',           'y'=>round(($incendio*100)/($total), 1),          'label'=>$incendio ),
            array('name'=>'Inundación',         'y'=>round(($inundación*100)/($total), 1),        'label'=>$inundación ),
            array('name'=>'Otros',              'y'=>round(($otros*100)/($total), 1),             'label'=>$otros ),
            array('name'=>'Riesgo sanitario',   'y'=>round(($riesgo_sanitario*100)/($total), 1),  'label'=>$riesgo_sanitario ),
            array('name'=>'Sin riesgo',         'y'=>round(($sin_riesgo*100)/($total), 1),        'label'=>$sin_riesgo ),
            array('name'=>'No inicio',          'y'=>round(($no_inicio*100)/($total), 1),         'label'=>$no_inicio ),
          );
        }

      }
      return array(
        'inicio'=>json_encode($datoInicioActividades),
        'riesgos'=>json_encode($datosRiesgo),
      );
    }

    private function getDatosUsuario($userId,$userRol)
    {
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;

        $user=NULL;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select *
        from (
        select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,a.esactivo,a.id as user_id
        from usuario a 
          inner join usuario_rol b on a.id=b.usuario_id 
            inner join lugar_tipo c on b.lugar_tipo_id=c.id
        where codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
        union all
        select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
        from maestro_inscripcion a
          inner join institucioneducativa b on a.institucioneducativa_id=b.id
            inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
              inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                inner join usuario e on a.persona_id=e.persona_id
                  inner join usuario_rol f on e.id=f.usuario_id
        where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
        where user_id = ?
        and rol_tipo_id = ?
        ORDER BY cod_dis
        LIMIT 1
        ';
        $stmt = $db->prepare($query);
        $params = array($userId,$userRol);
        $stmt->execute($params);
        $user=$stmt->fetch();
        return $user;
    }

    private function getDepartamentoDistrito($numero)
    {
      $departamento=-1;
      $distrito=-1;
      if($numero==0)
      {
        $departamento=-1;
        $distrito=-1;
      }
      else
      {
        if($numero>0 && $numero<=9)
        {
          $departamento=$numero;
          $distrito=-1;
        }
        else
        {
          if($numero > 10 and strlen($numero)==4)
          {
            $departamento=substr($numero,0,1);
            $distrito=$numero;
          }
          else
          {
            $departamento=-1;
            $distrito=-1;
          }
        }
      }
      return array($departamento,$distrito);
    }

    private function getNombreDepartamento($id)
    {
      $objDepto=NULL;
      $em = $this->getDoctrine()->getManager();
      $objDepto =  $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $id));
      if($objDepto)
        $objDepto=strtoupper($objDepto->getDepartamento());
      else
        $objDepto=NULL;
      return $objDepto;
    }

    private function getNombreDistrito($id)
    {
      $objDistrito=NULL;
      $em = $this->getDoctrine()->getManager();
      $objDistrito =  $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneBy(array('id' => $id));
      if($objDistrito)
        $objDistrito=strtoupper($objDistrito->getDistrito());
      else
        $objDistrito=NULL;
      return $objDistrito;
    }

    private function getNombreColegio($id)
    {
      $objColegio=NULL;
      $em = $this->getDoctrine()->getManager();
      $objColegio =  $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $id));
      if($objColegio)
        $objColegio=strtoupper($objColegio->getInstitucioneducativa());
      else
        $objColegio=NULL;
      return $objColegio;
    }

      //Reporte estudiantes en situacio de vulnerabilidad
    function reporteEstudiantesSituacionVulnerabilidadAction(Request $request)
    {
      $departamento=-1;
      $distrito=-1;
      $userId=$this->session->get('userId');
      $userRol=$this->session->get('roluser');
      $datosUser=$this->getDatosUsuario($userId,$userRol);
      if($datosUser)
      {
        $departamentoDistrito=$datosUser['cod_dis'];
        list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);
      }
      else
      {
        return $this->redirect($this->generateUrl('login'));
      }
      $datos=$this->getUESituacionVulnerabilidad($departamento,$distrito);

      return $this->render($this->session->get('pathSystem') . ':InfoConsolidation:reporte_estudiantes_situacion_vulnerabilidad.html.twig', array(
        'departamento'=> $departamento,
        'distrito'=> $distrito,
        'departamento_nom'=>$this->getNombreDepartamento($departamento),
        'distrito_nom'=>$this->getNombreDistrito($distrito),
        'datos' => $datos,
      ));
    }

    private function getUESituacionVulnerabilidad($departamento=-1,$distrito=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';

        $departamentos=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select 
        j.id as departamento_id,
        j.departamento as departamento_nom,
        f.cod_dis as distrito_id,
        f.des_dis as distrito_nom,
        d.id as ue_id,
        d.institucioneducativa ue_nom,
        COALESCE(i.id_registro, -1) as registro
        
        from  institucioneducativa d 
        inner join jurisdiccion_geografica e on d.le_juridicciongeografica_id=e.id
        inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) f on e.lugar_tipo_id_distrito=f.id
        inner JOIN institucioneducativa_sucursal g on g.institucioneducativa_id=d.id
        
        RIGHT JOIN (
        select a.institucioneducativa_id, eq.id_registro from  institucioneducativa_frontera_vulnerabilidad a left JOIN (SELECT institucioneducativa_id as id_registro, count(institucioneducativa_id) nro_estudiantes from  estudiante_quipus GROUP BY institucioneducativa_id having count(institucioneducativa_id)>1) eq on a.institucioneducativa_id=eq.id_registro
        ) i on i.institucioneducativa_id =d.id
        
        INNER JOIN departamento_tipo j on j.id=CAST(substring(f.cod_dis,1,1) as INTEGER)
    
        WHERE j.id '.$operadorDepartamento.' ? 
        and f.cod_dis '.$operadorDistrito.' ?

        GROUP BY j.id,j.departamento,f.cod_dis,f.des_dis,d.id, i.id_registro
        ORDER BY j.id asc,j.departamento asc,f.cod_dis asc,d.institucioneducativa ASC
        ';

        $stmt = $db->prepare($query);
        $params = array($departamento,$distrito);
        $stmt->execute($params);
        $colegios=$stmt->fetchAll();

        return $colegios;
    }

    /**
     * dcastillo
     * borra registro de registro_consolidacion
     * 210222
     */

    public function enabledOpeAction(Request $request){

      
      $sie = $request->get('sie');
      $gestion = $request->get('gestion');      
      $em = $this->getDoctrine()->getManager();
      $objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $sie,
        'gestion'         => $gestion
      ));
      

      $em = $this->getDoctrine()->getManager();
      /*$query = $em->getConnection()->prepare("
          delete from registro_consolidacion where gestion = ".$gestion." and unidad_educativa = " . $sie
          );
      $query->execute();*/

      $query = $em->getConnection()->prepare("delete from registro_consolidacion where gestion = ".$gestion." and unidad_educativa = " . $sie);
      //$query = $em->getConnection()->prepare("delete from registro_consolidacion where gestion = 2025 and unidad_educativa = " . $sie);
        
      $res = $query->execute();
      //$valor= $query->fetchAll();   
      
      /*if($res){

        $response = new JsonResponse();
        //return $response->setData(array('data'=>$valor,'existe'=> 0));
        return $response->setData(array('data'=>1,'msg'=> 'Aperturado'));

      }
      $response = new JsonResponse();
      //return $response->setData(array('data'=>$valor,'existe'=> 0));
      return $response->setData(array('data'=>0,'msg'=> 'Error'));*/

      


      if($objRegistroConsolidado->getInstitucioneducativaTipoId()==4)   
      return $this->redirectToRoute('herramienta_especial_infoconsolidation_gestion_index');
      else
      return $this->redirectToRoute('herramienta_infoconsolidation_gestion_index');  



    }


    public function enabledOpeActionOLD(Request $request){
      // get the send values
      $sie = $request->get('sie');
      $gestion = $request->get('gestion');
      $em = $this->getDoctrine()->getManager();
      $objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $sie,
        'gestion'         => $gestion
      ));

      $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
      switch ($operativo) {
          case 1:
              $objRegistroConsolidado->setBim1(0);
              break;
          case 2:
              $objRegistroConsolidado->setBim2(0);
              break;
          case 3:
              $objRegistroConsolidado->setBim3(0);
              break;
          
          default:
              // code...
              break;
      }

 /*     $estado1 = 0;
      $estado2 = 0;
      $estado3 = 0;
      $estado4 = 0;
      if($operativo == 0 or $operativo == 1 or $operativo == 2)
      {
        $estado1 = 2;
        $estado2 = 0;
        $estado3 = 0;
        $estado4 = 0;
      }
      if($operativo == 3)
      {
        $estado1 = 2;
        $estado2 = 1;
        $estado3 = 0;
        $estado4 = 0;
      }

      $objRegistroConsolidado->setBim1($estado1);
      $objRegistroConsolidado->setBim2($estado2);
      $objRegistroConsolidado->setBim3($estado3);
      $objRegistroConsolidado->setBim4($estado4);
*/
      $em->persist($objRegistroConsolidado);
      $em->flush();

    if($objRegistroConsolidado->getInstitucioneducativaTipoId()==4)   
         return $this->redirectToRoute('herramienta_especial_infoconsolidation_gestion_index');
     else
         return $this->redirectToRoute('herramienta_infoconsolidation_gestion_index');  



    }
    public function habilitarOpeAction(Request $request){
      // get the send values

      $sie = $request->get('sie');
      $gestion = $request->get('gestion');
      $em = $this->getDoctrine()->getManager();
      $objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $sie,
        'gestion'         => $gestion
      ));

      if($objRegistroConsolidado->getInstitucioneducativaTipoId()==4) {
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
      
          switch ($operativo) {
            case 1:
                $objRegistroConsolidado->setBim1(0);
                break;
            case 2:
                $objRegistroConsolidado->setBim2(0);
                break;
            case 3:
                $objRegistroConsolidado->setBim3(0);
                break;
            
            default:
                // code...
                break;
          }
          
        $em->persist($objRegistroConsolidado);
        $em->flush();
      }
      if($objRegistroConsolidado->getInstitucioneducativaTipoId()==4)   
          return $this->redirectToRoute('herramienta_especial_infoconsolidation_gestion_index');
      else
          return $this->redirectToRoute('herramienta_infoconsolidation_gestion_index');  

    }

    public function observacionesMaestrosAction(Request $request) {

      $institucion = $request->getSession()->get('ie_id');
      $gestion = $request->getSession()->get('idGestion');

      dump('data2');
      dump($institucion);
      dump($gestion);
      die;

      $response = new JsonResponse();
      return $response->setData(array('data'=>1,'mensaje'=>''));

    }

    /**
     * dcastillo
     */
    public function consolidacionGestionInconsistenciasAction(Request $request) {

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
              $title = 'Reporte de inconsistencias del Operativo de Inscripcion';
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
              $title = 'Reporte de inconsistencias del Operativo de Inscripcion';
              $label = 'Cantidad de Unidades Educativas que reportaron información';
              $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
              break;
      }
      
      /* dcastillo nueva funcion */
      $consol = $this->get('sie_app_web.funciones')->reporteNoConsol($gestionactual, $roluser, $roluserlugarid, $instipoid);
      
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
      
      return $this->render($this->session->get('pathSystem') . ':InfoConsolidationInconsistencias:index_gestion.html.twig', array(
          'consol' => $consol,
          'gestiones' => $gestionesArray,
          'gestionactual' => $gestionactual,
          'title' => $title,
          'label' => $label,
          'label_distrito' => $label_distrito,
          'ues' => $ues
      ));
    }


    /**
     * dcastillo
     * mostrar modal con inconsistencias
     */
    public function getInconsistenciasAction(Request $request) {
      
    }

    




}
