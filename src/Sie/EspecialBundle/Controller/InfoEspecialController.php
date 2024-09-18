<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use \Sie\AppWebBundle\Entity\Institucioneducativa;
use \Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;

class InfoEspecialController extends Controller{
  public $session;

  public function __construct(){
    $this->session = new Session();
  }
  public function indexAction(Request $request){ 
    // get the data conexion
    $em = $this->getDoctrine()->getManager();
    
    if ($this->session->get('roluser') == 7 or $this->session->get('roluser') == 8 or $this->session->get('roluser') == 9 or $this->session->get('roluser') == 10) {
      $request->getSession()->set('onlyview', false);
    } else {
      $request->getSession()->set('onlyview', true);
    }    

    if ($request->getMethod() == 'POST') {
        $form = $request->get('form');

        $institucion = $form['sie'];

        $objCentro = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $institucion, 'institucioneducativaTipo' => 4));

        if($objCentro){
            /*
             * verificamos si tiene tuicion
             */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $institucion);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucion = $form['sie'];
                // creamos variables de sesion de la institucion educativa y gestion
                $request->getSession()->set('ie_id', $institucion);
                if ($this->session->get('roluser') == 7 or $this->session->get('roluser') == 8 or $this->session->get('roluser') == 9 or $this->session->get('roluser') == 10) {
                  $request->getSession()->set('onlyview', false);
                } else {
                  $request->getSession()->set('onlyview', true);
                }
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                return $this->redirect($this->generateUrl('herramienta_especial_buscar_centro'));
            }
            
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido o no corresponde a un Centro de Educación Especial');
            return $this->redirect($this->generateUrl('herramienta_especial_buscar_centro'));
        }

        // creamos variables de sesion de la institucion educativa
    }

     //$this->session = $request->getSession();
     // dump($request);die;
     $id_usuario = $this->session->get('userId');
     //validation if the user is logged
     if (!isset($id_usuario)) {
        return $this->redirect($this->generateUrl('login'));
     }
     //get all info about centro
     $objAllInfoCentro = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoCentroSpecial($this->session->get('ie_id'));
     
    // set the centro info
     $arrInfoCentro = array();
     $key = 0;
     $swNewRegistroConsolidation = true;
     foreach ($objAllInfoCentro as $key => $value) {

      
       # code...
       $arrInfoCentro[$key]['gestion'] = $value->getGestion();
       $arrInfoCentro[$key]['sie']  = $value->getUnidadEducativa();
       $arrInfoCentro[$key]['institucioneducativaTipoId']  = $value->getInstitucioneducativaTipoId();
       if($this->session->get('currentyear')==$value->getGestion())
        $swNewRegistroConsolidation = false;

     }
     if($swNewRegistroConsolidation){
       $arrInfoCentro[$key+1]['gestion'] = $this->session->get('currentyear');
       $arrInfoCentro[$key+1]['sie']  = $this->session->get('ie_id');
       $arrInfoCentro[$key+1]['institucioneducativaTipoId']  = 4;
     }

     //get data centro
     $objCentro = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($this->session->get('ie_id'));
     //render the view
     return $this->render('SieEspecialBundle:InfoEspecial:index.html.twig', array(
               'arrInfoCentro' => $arrInfoCentro,
               'objCentro'    => $objCentro[0]
           ));
   }

  public function openAction(Request $request){
      
    //dump($request);die;
    //create the db conexion
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    //get the values send
    $data = $request->get('form');
    //dump($data);die;
    //set the new row on RegistroConsolidacion
    $objInfoCentro = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findBy(array(
      'unidadEducativa'=>$data['idInstitucion'],
      'gestion'=>$data['gestion'],
    ));

    //set Institucioneducativasucursal
    $objInfoSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array(
      'institucioneducativa'=>$data['idInstitucion'],
      'gestionTipo'=>$data['gestion'],
    ));

    if(!$objInfoSucursal){
        try {
          
            // Registramos la sucursal
            $query = $em->getConnection()->prepare("select * from sp_genera_institucioneducativa_sucursal('".$data['idInstitucion']."','0','".$data['gestion']."','1')");
            $query->execute();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

   $periodo = $this->operativo($data['idInstitucion'], $data['gestion']);
   
    /*
    if ($periodo != 0) {
      $request->getSession()->set('onlyview', true);
    } else {
      $request->getSession()->set('onlyview', false);
    }
*/
    $dataInfo = array('id' => $data['idInstitucion'], 'gestion' => $data['gestion'], 'institucioneducativa' => $data['institucioneducativa']);
    return $this->render($this->session->get('pathSystem') . ':InfoEspecial:open.html.twig', array(
                'centroform' => $this->InfoStudentForm('herramienta_especial_info_centro_index', 'Centro Educativo', $data)->createView(),
                'personalAdmform' => $this->InfoStudentForm('herramienta_especial_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                'infoMaestroform' => $this->InfoStudentForm('herramienta_especial_info_maestro_index', 'Maestros',$data)->createView(),
                'infotStudentform' => $this->InfoStudentForm('info_students_index', 'Estudiantes',$data)->createView(),
                'cursosform' => $this->InfoStudentForm('creacioncursos_especial', 'Cursos',$data)->createView(),
                'areasform' => $this->InfoStudentForm('area_especial_search', 'Areas/Maestros',$data)->createView(),
                'data'=>$dataInfo,
                'closeOperativoform' => $this->CloseOperativoForm('info_especial_close_operativo', 'Cerrar Operativo Inscripción',$data, $periodo)->createView(),
                'closeOperativoRudeesform' => $this->CloseOperativoRudeesForm('info_especial_close_operativo_rudees', 'Cerrar Operativo Rudees',$data, $periodo)->createView(),
                'closeOperativoNotasform' => $this->CloseOperativoNotasForm('info_especial_close_operativo_notas', 'Cerrar Operativo 2do Trimestre',$data, $periodo)->createView(),
               // 'operativoSaludform' => $this->InfoStudentForm('herramienta_info_personalAdm_maestro_index', 'Operativo Salud',$data)->createView(),

                'operativo'=>$periodo,
              //  'operativoBonoJPform' => $this->cerrarOperativoForm('operativo_bono_jp_cerrar', 'Cerrar Operativo Bono JP',$data)->createView(),
              //  'operativoBonoJP' => $this->get('operativoutils')->verificarEstadoOperativo($data['idInstitucion'],$data['gestion'],14),                

    ));

  }

  /**
   * create form Student Info to send values
   * @return type obj form
   */
  private function CloseOperativoForm($goToPath, $nextButton, $data, $operativo) {
    //dump($goToPath); die;
      //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
      $estado = false;
      if($operativo=="")
        $estado = true;

      $this->unidadEducativa = ((int)$this->session->get('ie_id'));
      return $this->createFormBuilder()
                      ->setAction($this->generateUrl($goToPath))
                      ->add('gestion', 'hidden', array('data' => $data['gestion']))
                      ->add('sie', 'hidden', array('data' => $this->unidadEducativa))//81880091
                      ->add('next', 'button', array('label' => "$nextButton",  'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft', 'onclick'=>'closeOperativo()')))
                      ->getForm()
      ;
  }
  private function CloseOperativoRudeesForm($goToPath, $nextButton, $data,  $operativo)
    { 
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $estado = false;
        if($operativo==0)
          $estado = true;
        $this->unidadEducativa = ((int)$this->session->get('ie_id'));
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $this->unidadEducativa))//81880091
                        ->add('next', 'button', array('label' => "$nextButton",  'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft', 'onclick'=>'closeOperativoRudees()')))
                        ->getForm()
        ;
        return $form;
    }
  private function CloseOperativoNotasForm($goToPath, $nextButton, $data,  $operativo)
    {
   
        $estado = true;
        //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
        $this->unidadEducativa = ((int)$this->session->get('ie_id'));
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => $data['gestion']))
                        ->add('sie', 'hidden', array('data' => $this->unidadEducativa))//81880091
                        ->add('next', 'button', array('label' => "$nextButton",  'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft', 'onclick'=>'closeOperativoNotas()')))
                        ->getForm()
      
        ;
        //dump($form);die;
        return $form;
    }

  /**
   * create open action form
   * @return type obj form
   */
  private function InfoStudentForm($goToPath, $nextButton, $data) {
      //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
      $this->unidadEducativa = $data['idInstitucion'];
      return $this->createFormBuilder()
                      ->setAction($this->generateUrl($goToPath))
                      ->add('gestion', 'hidden', array('data' => $data['gestion']))
                      ->add('sie', 'hidden', array('data' => $data['idInstitucion']))//81880091
                      ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'cbp-singlePage cbp-l-caption-buttonLeft')))
                      ->getForm()
      ;
  }

  private function cerrarOperativoForm($goToPath, $nextButton, $data)
  {
    //dump($goToPath);die;
      //$this->unidadEducativa = $this->getAllUserInfo($this->session->get('userName'));
      $onClick = '';
      $this->unidadEducativa = $data['idInstitucion'];
      switch ($nextButton)
      {
        case 'Cerrar Operativo Bono JP':
            $onClick='';
         break;
        default:
          $onClick = '';
        break;
      }
      return $this->createFormBuilder()
                      ->setAction($this->generateUrl($goToPath))
                      ->add('gestion', 'hidden', array('data' => bin2hex($data['gestion'])))
                      ->add('sie', 'hidden', array('data' => bin2hex($data['idInstitucion'])))
                      ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-facebook btn-md btn-block', 'onclick'=>$onClick )))
                      ->getForm()
      ;
  }  

  public function closeOperativoAntesAction (Request $request){
    //crete conexion DB
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    //get the values
    $form = $request->get('form');
    //dump($form);die;

    //get the current operativo
    $objOperativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);

    //update the close operativo to registro consolido table
   
    $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $form['sie'], 
        'gestion' => $form['gestion']
      ));

    $periodo = $this->operativo($form['sie'], $form['gestion']);

    if(!$registroConsol){
        $rconsol = new RegistroConsolidacion();
        $rconsol->setTipo(1);
        $rconsol->setGestion($form['gestion']);
        $rconsol->setUnidadEducativa($form['sie']);
        $rconsol->setTabla('**');
        $rconsol->setIdentificador('**');
        $rconsol->setCodigo('**');
        $rconsol->setDescripcionError('Consolidado exitosamente!!');
        $rconsol->setFecha(new \DateTime("now"));
        $rconsol->setusuario('0');
        $rconsol->setConsulta('**');
        $rconsol->setBim1('0');
        $rconsol->setBim2('0');
        $rconsol->setBim3('0');
        $rconsol->setBim4('0');
        $rconsol->setPeriodoId(1);
        $rconsol->setSubCea(0);
        $rconsol->setBan(1);
        $rconsol->setEsonline('t');
        $rconsol->setInstitucioneducativaTipoId(4);
        $em->persist($rconsol);
        $em->flush();
        $em->getConnection()->commit();
    }
    $inconsistencia = null;
    //$periodo = 3;
   // $registroConsol->setBim1('2');
   // $registroConsol->setBim2('2');
   // $registroConsol->setBim3('2');

   // $em->persist($registroConsol);
   // $em->flush();

    $query = $em->getConnection()->prepare('select * from sp_validacion_especial_web(:igestion_id, :icod_ue, :ibimestre)');
    $query->bindValue(':igestion_id', $form['gestion']);
    $query->bindValue(':icod_ue', $form['sie']);
    $query->bindValue(':ibimestre', $periodo);
    $query->execute();
    $inconsistencia = $query->fetchAll();
    
    if(!$inconsistencia and $periodo > 0) {
        $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
        $registroConsol->setFecha(new \DateTime("now"));
        
        switch ($periodo) {
            case 1: $registroConsol->setBim1('2'); break;
            case 2: $registroConsol->setBim2('2'); break;
            case 3: $registroConsol->setBim3('2'); break;
          //  case 4: $registroConsol->setBim4('2'); break;
        }
        
        //$em->persist($registroConsol);
        $em->flush();
        $em->getConnection()->commit();
    }
    $estado ='';
    //dump($form,$inconsistencia,$periodo);die;
    return $this->render($this->session->get('pathSystem') . ':InfoEspecial:list_inconsistencia.html.twig', array('inconsistencia' => $inconsistencia, 'institucion' =>  $form['sie'], 'gestion' => $form['gestion'], 'periodo' => $periodo, 'estado' => $estado));
  }
/** INSCRIPCION */
  public function closeOperativoAction (Request $request){ 

      
      //crete conexion 
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');

      //get the current operativo
      $objOperativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);

      //update the close operativo to registro consolido table
      //dump($form['gestion']); die; 
     
      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
          'unidadEducativa' => $form['sie'], 
          'gestion' => $form['gestion']
        ));

      //dump( $registroConsol); die;
      
      $periodo = 0; //para inscripcion

      $inconsistencia = null;
      $estado = '';
      if($registroConsol){
        $estado = 'CON_INSC';
      }
      //dump($estado); die;
      if($estado==''){
        $query = $em->getConnection()->prepare('select * from sp_validacion_especial_web(:igestion_id, :icod_ue, :ibimestre)');
        $query->bindValue(':igestion_id', $form['gestion']);
        $query->bindValue(':icod_ue', $form['sie']);
        $query->bindValue(':ibimestre', $periodo);
        $query->execute();
        $inconsistencia = $query->fetchAll();
  //dump($inconsistencia);die;
        if(!$registroConsol && !$inconsistencia){
            $rconsol = new RegistroConsolidacion();
            $rconsol->setTipo(1);
            $rconsol->setGestion($form['gestion']);
            $rconsol->setUnidadEducativa($form['sie']);
            $rconsol->setTabla('**');
            $rconsol->setIdentificador('**');
            $rconsol->setCodigo('**');
            $rconsol->setDescripcionError('Consolidado exitosamente!!');
            $rconsol->setFecha(new \DateTime("now"));
            $rconsol->setusuario('0');
            $rconsol->setConsulta('**');
            $rconsol->setBim1('0');
            $rconsol->setBim2('0');
            $rconsol->setBim3('0');
            $rconsol->setBim4('0');
            $rconsol->setPeriodoId(1);
            $rconsol->setSubCea(0);
            $rconsol->setBan(1);
            $rconsol->setEsonline('t');
            $rconsol->setInstitucioneducativaTipoId(4);
            $em->persist($rconsol);
            $em->flush();
            $em->getConnection()->commit();
        }
      }
   
      //$periodo = 3;
     // $registroConsol->setBim1('2');
     // $registroConsol->setBim2('2');
     // $registroConsol->setBim3('2');

     // $em->persist($registroConsol);
     // $em->flush();

    /*
      
      if(!$inconsistencia and $periodo > 0) {
          $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
          $registroConsol->setFecha(new \DateTime("now"));
          
          switch ($periodo) {
              case 1: $registroConsol->setBim1('2'); break;
              case 2: $registroConsol->setBim2('2'); break;
              case 3: $registroConsol->setBim3('2'); break;
            //  case 4: $registroConsol->setBim4('2'); break;
          }
          
          //$em->persist($registroConsol);
          $em->flush();
          $em->getConnection()->commit();
      }*/
      //dump($form,$inconsistencia,$periodo);die;
     
      //dump($estado); die;
      return $this->render($this->session->get('pathSystem') . ':InfoEspecial:list_inconsistencia.html.twig', array('inconsistencia' => $inconsistencia, 'institucion' =>  $form['sie'], 'gestion' => $form['gestion'], 'periodo' => $periodo, 'estado' => $estado));
    }

    public function closeOperativoRudeesAction (Request $request){ 
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');
     
     $cod_sie = (int)$this->session->get('ie_id');
     $gestion = $this->session->get('currentyear');
      //update the close operativo to registro consolido table
     /*
      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
          'unidadEducativa' => $form['sie'], 
          'gestion' => $form['gestion']
        ));*/
      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
          'unidadEducativa' => $cod_sie, 
          'gestion' => $gestion
        ));
      //$periodo = $this->operativo($form['sie'], $form['gestion']);
      $periodo = $this->operativo($cod_sie, $gestion);
      //dump($registroConsol);
      $estado = '';

      
      if(!$registroConsol || $registroConsol==null){ 
        $estado = 'SIN_INSC';
      }else{
       
        if($registroConsol->getRude()==1){
          $estado = 'CON_RUDE';
        }
      }
      $inconsistencia = null;
      //dump($form['gestion']);
      //dump($form['sie']);
     // die;
     //dump($estado);die;
      if($estado==''){
       
        $query = $em->getConnection()->prepare('select * from sp_validacion_especial_rude(:igestion_id, :icod_ue)');
        $query->bindValue(':igestion_id', $gestion);
        $query->bindValue(':icod_ue', $cod_sie);
        $query->execute();
       
        $inconsistencia = $query->fetchAll(); 
       
        if($registroConsol && !$inconsistencia){ 
            $registroConsol->setRude(1);
            $em->persist($registroConsol);
            $em->flush();
            $em->getConnection()->commit();
        }
      }
     // dump($estado);die;
      return $this->render($this->session->get('pathSystem') . ':InfoEspecial:list_inconsistencia.html.twig', array('inconsistencia' => $inconsistencia, 'institucion' =>  $form['sie'], 'gestion' => $form['gestion'], 'periodo' => 100, 'estado' => $estado));
    }

    public function closeOperativoNotasAction (Request $request){ 
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');
      //dump($form);die;
      $cod_sie = (int)$this->session->get('ie_id');
      $gestion = $this->session->get('currentyear');
      //$gestion = $form['gestion'];

      //get the current operativo
     // $objOperativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);

      //update the close operativo to registro consolido table
     
      $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
          'unidadEducativa' =>$cod_sie, 
          'gestion' => $gestion
        ));
     //dump($registroConsol);die;
     // $periodo = $this->operativo($form['sie'], $form['gestion']);
     $periodo = $this->operativo($cod_sie, $gestion);
     //dump($periodo);die;
      $estado = '';
      if(!$registroConsol){
        $estado = 'SIN_INSC';
      }
      /*else{ cuando haya RUDE se habilitara
        if($registroConsol->getRude()!=1){
          $estado = 'SIN_RUDE';
        }
        /*if($registroConsol->getBim1()==2){
          $estado = 'CON_BIM1';
        }
        if($registroConsol->getBim2()==2){
          $estado = 'CON_BIM2';
        }
        if($registroConsol->getBim3()==2){
          $estado = 'CON_BIM3';
        }*/
      //}
      $inconsistencia = null;
      //dump($estado);die;
      if($estado==''){
        //sp_validacion_regular_RUDE
        $query = $em->getConnection()->prepare('select * from sp_validacion_especial_web(:igestion_id, :icod_ue, :ibimestre)');
        $query->bindValue(':igestion_id', $gestion);
        $query->bindValue(':icod_ue', $cod_sie);
        $query->bindValue(':ibimestre', $periodo);
        $query->execute();
        $inconsistencia = $query->fetchAll();
        //dump($registroConsol);
       // dump($inconsistencia);die;
        if($registroConsol && !$inconsistencia){ // dump("aaaaaa");die;
              $registroConsol->setBim1('2');
            if($periodo==2)
              $registroConsol->setBim2('2');
            if($periodo==3)
              $registroConsol->setBim3('2');
            $em->persist($registroConsol);
            $em->flush();
            $em->getConnection()->commit();
        }
      }
      
      return $this->render($this->session->get('pathSystem') . ':InfoEspecial:list_inconsistencia.html.twig', array('inconsistencia' => $inconsistencia, 'institucion' =>  $cod_sie, 'gestion' => $gestion, 'periodo' => $periodo ,'estado' => $estado));
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3, rc.rude')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = '';
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['rude'] != 1){
                $operativo = 0; // Operativo RUDE
            }
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['rude'] == 1){
                $operativo = 1; // Primer Trimestre
            }
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0){
                $operativo = 1; // Primer Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 ){
                $operativo = 2; // Segundo Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 ){
                $operativo = 3; // Tercer Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 ){
              $operativo = 3; // Tercer Trimestre
          }
        }
        return $operativo;
    }
}
