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

    /*if(!$objInfoCentro){
      try {
        $objNewRegistroConsolidation = new RegistroConsolidacion();

        $objNewRegistroConsolidation->setTipo(1);
        $objNewRegistroConsolidation->setGestion($data['gestion']);
        $objNewRegistroConsolidation->setUnidadEducativa($data['idInstitucion']);
        $objNewRegistroConsolidation->setPeriodoId(1);
        $objNewRegistroConsolidation->setBim1(0);
        $objNewRegistroConsolidation->setBim2(0);
        $objNewRegistroConsolidation->setBim3(0);
        $objNewRegistroConsolidation->setBim4(0);
        $objNewRegistroConsolidation->setDescripcionError('Consolidado exitosamente (web)!!');
        $objNewRegistroConsolidation->setFecha(new \DateTime('now'));
        $objNewRegistroConsolidation->setUsuario(0);
        $objNewRegistroConsolidation->setSubCea(0);
        $objNewRegistroConsolidation->setBan(1);
        $objNewRegistroConsolidation->setEsonline(1);
        $objNewRegistroConsolidation->setInstitucioneducativaTipoId($data['institucioneducativaTipoId']);
        $em->persist($objNewRegistroConsolidation);
        $em->flush();
        $em->getConnection()->commit();

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }*/

    //dump($data);die;
    $dataInfo = array('id' => $data['idInstitucion'], 'gestion' => $data['gestion'], 'institucioneducativa' => $data['institucioneducativa']);
    return $this->render($this->session->get('pathSystem') . ':InfoEspecial:open.html.twig', array(
                'centroform' => $this->InfoStudentForm('herramienta_especial_info_centro_index', 'Centro Educativo', $data)->createView(),
                'personalAdmform' => $this->InfoStudentForm('herramienta_especial_info_personal_adm_index', 'Personal Administrativo',$data)->createView(),
                'infoMaestroform' => $this->InfoStudentForm('herramienta_especial_info_maestro_index', 'Maestros',$data)->createView(),
                'infotStudentform' => $this->InfoStudentForm('info_students_index', 'Estudiantes',$data)->createView(),
                'cursosform' => $this->InfoStudentForm('creacioncursos_especial', 'Oferta',$data)->createView(),
                'areasform' => $this->InfoStudentForm('area_especial_search', 'Areas/Maestros',$data)->createView(),
                'closeOperativoform' => $this->CloseOperativoForm('info_especial_close_operativo', 'Cerrar Operativo',$data)->createView(),
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

  public function closeOperativoAction (Request $request){
      //crete conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the values
      $form = $request->get('form');

      //get the current operativo
      $objOperativo = $this->get('funciones')->obtenerOperativo($form['sie'],$form['gestion']);

      //update the close operativo to registro consolido table
      $objRegistroConsolidado = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
        'unidadEducativa' => $form['sie'],
        'gestion'         => $form['gestion']
      ));

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

      $query = $em->getConnection()->prepare('select * from sp_validacion_especial_web(:igestion_id, :icod_ue, :ibimestre)');
      $query->bindValue(':igestion_id', $form['gestion']);
      $query->bindValue(':icod_ue', $form['sie']);
      $query->bindValue(':ibimestre', $periodo);
      $query->execute();
      $inconsistencia = $query->fetchAll();

      if(!$inconsistencia) {
          $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $form['sie'], 'gestion' => $form['gestion']));
          $registroConsol->setFecha(new \DateTime("now"));

          switch ($periodo) {
              case 1: $registroConsol->setBim1('2'); break;
              case 2: $registroConsol->setBim2('2'); break;
              case 3: $registroConsol->setBim3('2'); break;
              case 4: $registroConsol->setBim4('2'); break;
          }

          $em->persist($registroConsol);
          $em->flush();
          $em->getConnection()->commit();
      }

      return $this->render($this->session->get('pathSystem') . ':InfoEspecial:list_inconsistencia.html.twig', array('inconsistencia' => $inconsistencia, 'institucion' =>  $form['sie'], 'gestion' => $form['gestion'], 'periodo' => $periodo));
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Segundo Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Tercer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Cuarto Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }
}
