<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * Institucioneducativa Controller
 */
class AcaMultigradoController extends Controller {

    public $session;
    public $idInstitucion;
    public $arrLevel;
    public $arrLevelKey;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->arrLevel = array(
          11=>'Ini',
          12=>'Pri',
          13=>'Sec',
        );
        $this->arrLevelKey = array(
          'Ini'=>11,
          'Pri'=>12,
          'Sec'=>13,
        );

    }

    /**
     * Muestra el listado de Menús
     */
    public function indexAction (Request $request) {
        // dump($request);die;
        $id_usuario = $this->session->get('userId');
        $em = $this->getDoctrine()->getManager();
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // //get the data send
        // $form = $request->get('form');
        // dump($form);die;
        //get info
        $arrData['sie']     = $request->get('institucioneducativa');
        $arrData['gestion'] = $request->get('gestion'); 
        $arrData['idDetalle'] = '';
        // dump($arrData);die;
        ////////////////////////////////////////////////////////////////
        //get Multigrado per SIE and GESTION
        $objMultigradoArr = $this->getMultigradoPerUeAndGestion($arrData);

        //get number of student
        $numberStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInscriptionsPerUe($arrData['sie'], $arrData['gestion']);

        $arrNumberStudentes = array();
        foreach ($numberStudents as $key => $value) {
          $arrNumberStudentes[$value['nivel']][]=array('grado'=>$value['grado'],'paralelo'=>$value['paralelo'], 'students'=>$value['students']);
          // dump($value);
        }

        return $this->render($this->session->get('pathSystem').':AcaMultigrado:index.html.twig', array(
          'objMultigradoArr' => $objMultigradoArr,
          'jsonDataMultigrado' => json_encode($arrData),
          'arrNumberStudentes'=>$arrNumberStudentes,
          'arrData'=>$arrData

        ));

// die;

      //   ///////////////////////////////////////////////////////////////////////////////
      //
      //   $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getCourseByNivelAndGrado($arrData);
      //
      //   $arrMultigradoUe = array();
      //   //built the form
      //   foreach ($objInstitucionEducativa as $key => $value) {
      //     $arrMultigradoUe[$value->getTurnoTipo()->getId().'-'.$value->getParaleloTipo()->getId().'-'.$value->getMultigrado()] [$value->getNivelTipo()->getId().'-'.$value->getGradoTipo()->getId()] = $value->getMultigrado();
      //   }
      //   // dump($arrMultigradoUe);
      //
      //   $arrTwoMul = array();
      //   $indArr=0;
      //   $ind = 1;
      //   foreach ($arrMultigradoUe as $key => $value) {
      //     for ($i=0; $i < $indArr; $i++) {
      //       $arrTwoMul[$key][$i] = 0;
      //     }
      //     $j=  $i;
      //     foreach ($value as $key1 => $value1) {
      //       $arrTwoMul[$key][$j] = $key1;
      //       $j++;
      //     }
      //     $indArr = $indArr+ sizeof($value);
      //   }
      //   // dump($arrTwoMul);
      //
      //   return $this->render('SieRegularBundle:Multigrado:index.html.twig', array(
      //     'multigradoAll' => $arrMultigradoUe,
      //     'arrTwoMul' => $arrTwoMul
      //   ));
      //   // dump($objInstitucionEducativa);
      //   die;
      //   //get sie and gestion
      //   $gestion = '2016';
      //   $sie     = '10710016';//'10710016';
      //
      //
      //   $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
      //     'institucioneducativa' => $sie,
      //     'gestionTipo' => $gestion
      //   ));
      //   // dump($objInstitucionEducativa);
      //   $arrMultigrado = array();
      //   foreach ($objInstitucionEducativa as $key => $value) {
      //     $arrMultigrado[$value->getMultigrado()][] = array($value->getNivelTipo()->getId().'-'.$value->getGradoTipo()->getId() => $value->getMultigrado());
      //
      //     # code...
      //   }
      //   dump($arrMultigrado);die;
      // die;
    }

    private function getMultigradoPerUeAndGestion($data){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                  select tt.id as turnoId, tt.turno, pt.id as paraleloId, pt.paralelo,
                            w.i_11_1,
                            w.i_11_2,
                            w.p_12_1,
                            w.p_12_2,
                            w.p_12_3,
                            w.p_12_4,
                            w.p_12_5,
                            w.p_12_6,
                            w.s_13_1,
                            w.s_13_2,
                            w.s_13_3,
                            w.s_13_4,
                            w.s_13_5,
                            w.s_13_6
                            from (
                            select
                            turno_tipo_id,
                            paralelo_tipo_id,
                            'Aula ' || cast(multigrado as varchar) as multigrado
                            ,case SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '0' else 'checked' end as i_11_1
                            ,case SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '0' else 'checked' end as i_11_2
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_1
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_2
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 3 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_3
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 4 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_4
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 5 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_5
                            ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 6 then 1 else 0 end) when 0 then '0' else 'checked' end as p_12_6
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_1
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_2
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 3 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_3
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 4 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_4
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 5 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_5
                            ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 6 then 1 else 0 end) when 0 then '0' else 'checked' end as s_13_6
                            from (
                            select distinct iec.nivel_tipo_id, iec.grado_tipo_id, iec.multigrado, iec.paralelo_tipo_id, iec.turno_tipo_id from institucioneducativa_curso as iec
                            where iec.gestion_tipo_id = ".$data['gestion']." and iec.institucioneducativa_id = ".$data['sie']." and (multigrado != 0 and multigrado is not null)
                            order by iec.turno_tipo_id, iec.nivel_tipo_id, iec.grado_tipo_id, iec.paralelo_tipo_id, iec.multigrado
                            ) as v
                            group by
                            turno_tipo_id,
                            paralelo_tipo_id,
                            multigrado
                            ) as w
                            inner join turno_tipo as tt on (tt.id = w.turno_tipo_id)
                            inner join paralelo_tipo as pt on (pt.id = w.paralelo_tipo_id)
                            order by
                            turno_tipo_id,
                            paralelo_tipo_id,
                            multigrado


          ");
          //
      $query->execute();
      $ueMultigradoEntity = $query->fetchAll();
      return $ueMultigradoEntity;
      // dump($ueMultigradoEntity);

    }



    public function SaveAction(Request $request){
      //get the data send
      $form = $request->get('form');
      $arrMulti ='';
      // dump($form);die;
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      try {
        $jsonDataMultigrado = $form['jsonDataMultigrado'];
        $arrDataMultigrado = json_decode($jsonDataMultigrado,true);
        // dump($arrDataMultigrado);die;
        if(isset($form['multi'])){
          $arrMulti = $form['multi'];
        }else{

          //set multigrado on this sie and gestion to 0
          $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
            'institucioneducativa' => $arrDataMultigrado['sie'],
            'gestionTipo' => $arrDataMultigrado['gestion']
          ));
          foreach ($objInstitucionEducativa as $key => $value) {
            $value->setMultigrado(0);
            $em->persist($value);
            $em->flush();
          }
          $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($arrDataMultigrado['idDetalle']);
          $vproceso->setEsActivo('t');
          $em->persist($vproceso);
          $em->flush();

          $message = 'Datos guardados correctamente... ';
          $typeMessage = 'success';
          $em->getConnection()->commit();
          $this->addFlash($typeMessage, $message);
          return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));

        }

        //set multigrado on this sie and gestion to 0
        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
          'institucioneducativa' => $arrDataMultigrado['sie'],
          'gestionTipo' => $arrDataMultigrado['gestion']
        ));
        foreach ($objInstitucionEducativa as $key => $value) {
          $value->setMultigrado(0);
          $em->persist($value);
          $em->flush();
        }

        // dump($objInstitucionEducativa);
        //save the new multigrado on this sie and gestion
        reset($arrMulti);
        while ($val = current($arrMulti)) {
          // dump($val);
            list($infoCourse, $multi)= explode('-', $val);
            list($nivel,$grado,$paralelo,$turno) = explode('_',$infoCourse);

            $objInstitucionEducativaUpdate = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
              'institucioneducativa' => $arrDataMultigrado['sie'],
              'gestionTipo' => $arrDataMultigrado['gestion'],
              'nivelTipo' => $nivel,
              'gradoTipo' => $grado,
              'paraleloTipo'  => $paralelo,
              'turnoTipo'  => $turno
            ));


            if($objInstitucionEducativaUpdate->getMultigrado()>0){
              $swMulti = false;
              $em->getConnection()->rollback();
              $message = 'Error al guardar lo seleccionado';
              $typeMessage = 'warning';
              $this->addFlash($typeMessage, $message);
              return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
            }else{
              $objInstitucionEducativaUpdate->setMultigrado($multi+1);
              $em->persist($objInstitucionEducativaUpdate);
              $em->flush();
            }

            next($arrMulti);
        }
          $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($arrDataMultigrado['idDetalle']);
          $vproceso->setEsActivo('t');
          $em->persist($vproceso);
          $em->flush();
          $message = 'Datos guardados correctamente... ';
          $typeMessage = 'success';
          $em->getConnection()->commit();
          $this->addFlash($typeMessage, $message);
          return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }


    }

    public function reloadAction(Request $request){

      $form = $request->get('form');
      $jsonDataMultigrado = $form['jsonDataMultigrado'];
      $arrDataMultigrado = json_decode($jsonDataMultigrado,true);

      $arrMulti ='';

      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      try {

        //set multigrado on this sie and gestion to 0
        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
          'institucioneducativa' => $arrDataMultigrado['sie'],
          'gestionTipo' => $arrDataMultigrado['gestion']
        ));
        foreach ($objInstitucionEducativa as $key => $value) {
          $value->setMultigrado(1);
          $em->persist($value);
          $em->flush();
        }
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($arrDataMultigrado['idDetalle']);
        $vproceso->setEsActivo('f');
        $em->persist($vproceso);
        $em->flush();

        $message = 'Datos guardados correctamente... ';
        $typeMessage = 'success';
        $em->getConnection()->commit();
        $this->addFlash($typeMessage, $message);
        return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }




    }



    public function noMultigradoAction(Request $request){
      //create the db conexion
      $em=$this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get data send
      // $form = $request->get('form');
      // $jsonDataMultigrado = $form['jsonDataMultigrado'];
      $jsonDataMultigrado = $request->get('jsonDataMultigrado');
      $arrDataMultigrado = json_decode($jsonDataMultigrado,true);

      try {
        //set multigrado on this sie and gestion to 0
        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
          'institucioneducativa' => $arrDataMultigrado['sie'],
          'gestionTipo' => $arrDataMultigrado['gestion']
        ));
        foreach ($objInstitucionEducativa as $key => $value) {
          $value->setMultigrado(0);
          $em->persist($value);
          $em->flush();
        }

        $em->getConnection()->commit();
        $message = 'Datos guardados correctamente... ';
        $typeMessage = 'success';
        // $em->getConnection()->commit();
        $this->addFlash($typeMessage, $message);
        // return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
        $arrData['sie']     = $arrDataMultigrado['sie'];
        $arrData['gestion'] = $arrDataMultigrado['gestion'];
        $arrData['idDetalle'] = $arrDataMultigrado['idDetalle'];
        ////////////////////////////////////////////////////////////////
        //get Multigrado per SIE and GESTION
        $objMultigradoArr = $this->getMultigradoPerUeAndGestion($arrData);
        // dump($message);die;
        return $this->render($this->session->get('pathSystem').':AcaMultigrado:showNewMultigrado.html.twig', array(
          'objMultigradoArr' => $objMultigradoArr,
          'jsonDataMultigrado' => json_encode($arrData),
          'arrData'=>$arrData,
          'message'=>$message,
          'typeMessage'=> $typeMessage
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        $em->close();
        // echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        $message = 'Problemas al guardar... '.$ex->getMessage(). "\n";
        $typeMessage = 'warning';
        $this->addFlash($typeMessage, $message);
        return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
      }


      // dump($arrDataMultigrado);die;

    }

    //get the multigrado option to set
    public function yesMultigradoAction(Request $request){

// dump($request);die;
    // $form = $request->get('form');
    $jsonDataMultigrado = $request->get('jsonDataMultigrado');

    $arrDataMultigrado = json_decode($jsonDataMultigrado,true);
    // dump($arrDataMultigrado);die;
      $objMulti = $this->getYesMultigrado($arrDataMultigrado);
      //get the multis
      $arrMultigradoHead=array();
      foreach ($objMulti as $key => $value) {
        # code...
        if($value['nivel_tipo_id'].$value['grado_tipo_id']<=132)
          $arrMultigradoHead[$this->arrLevel[$value['nivel_tipo_id']].'-'.$value['grado_tipo_id'].''] =  $value['nivel_tipo_id'].'-'.$value['grado_tipo_id'] ;
      }

      return $this->render($this->session->get('pathSystem').':AcaMultigrado:yesMultigrado.html.twig', array(
        'mulitgradoHead' => (sizeof($arrMultigradoHead)>1)?$arrMultigradoHead:array(),
        'jsonDataMultigrado'=>$jsonDataMultigrado,
        'arrDataMultigrado'=>$arrDataMultigrado,

      ));
    }
    /**
     * get data about multgrado info per UE onliy allowed
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getYesMultigrado($data){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
              select count(nivel_tipo_id),nivel_tipo_id, grado_tipo_id
              from institucioneducativa_curso
              where gestion_tipo_id = ".$data['gestion']." and institucioneducativa_id = ".$data['sie']." and nivel_tipo_id <= 13
              group by nivel_tipo_id,grado_tipo_id
              having count(nivel_tipo_id)=1
              order by 2,3
          ");
          //
      $query->execute();
      $ueMultigradoEntity = $query->fetchAll();
      return $ueMultigradoEntity;
      // dump($ueMultigradoEntity);

    }

    /**
     * save new multigrado option
     * @param  Request $request [form info]
     * @return [type]           [description]
     */
    public function saveNewMultigradoAction(Request $request){
      //get the DB conexxion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //ge the send values
      $form = $request->get('form');
      try {
        // dump($form);die;
        // decode json data
        $jsonDataMultigrado = $form['jsonDataMultigrado'];
        $arrDataMultigrado = json_decode($jsonDataMultigrado,true);
        // dump($arrDataMultigrado);die;
        //set multigrado on this sie and gestion to 0
        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
          'institucioneducativa' => $arrDataMultigrado['sie'],
          'gestionTipo' => $arrDataMultigrado['gestion']
        ));
        foreach ($objInstitucionEducativa as $key => $value) {
          $value->setMultigrado(0);
          $em->persist($value);
          $em->flush();
        }

        $message = 'Datos guardados correctamente... ';
        $typeMessage = 'success';
        $flagError = false;
        if(isset($form['multi'])){
          //save the new seleccion about multi
          $arrMulti = $form['multi'];
          //save the new seleccion multi
          foreach ($arrMulti as $key => $value) {
            list($infoNewMulti,$multi) = explode('_', $key);
            list($nivel,$grado) = explode('-',$infoNewMulti);
            // dump($nivel .' '.$grado.' '.$multi);
            $objInstitucionEducativaUpdate = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
              'institucioneducativa' => $arrDataMultigrado['sie'],
              'gestionTipo' => $arrDataMultigrado['gestion'],
              'nivelTipo' => $this->arrLevelKey[$nivel],
              'gradoTipo' => $grado,
              // 'paraleloTipo'  => $paralelo,
              // 'turnoTipo'  => $turno
            ));

            if($objInstitucionEducativaUpdate->getMultigrado()>0){
              $swMulti = false;
              // $em->getConnection()->rollback();
              $message = 'Error al guardar lo seleccionado';
              $typeMessage = 'warning';
              $this->addFlash($typeMessage, $message);
              $flagError = true;
              // return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage, 'flagError'=>1));
            }else{
              $objInstitucionEducativaUpdate->setMultigrado($multi);
              $em->persist($objInstitucionEducativaUpdate);
              $em->flush();
            }
            // dump($infoNewMulti.'-'.$multi);

            # code...
          }

        }else{
          $message = 'Datos guardados correctamente... ';
          $typeMessage = 'success';
          //set multigrado on this sie and gestion to 0
          $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
            'institucioneducativa' => $arrDataMultigrado['sie'],
            'gestionTipo' => $arrDataMultigrado['gestion']
          ));
          foreach ($objInstitucionEducativa as $key => $value) {
            $value->setMultigrado(0);
            $em->persist($value);
            $em->flush();
          }

          // $em->getConnection()->commit();
          $this->addFlash($typeMessage, $message);
          return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));

        }
        // $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($arrDataMultigrado['idDetalle']);
        // $vproceso->setEsActivo('t');
        // $em->persist($vproceso);
        // $em->flush();
        if($flagError){
          $em->getConnection()->rollback();
        }else{
          $em->getConnection()->commit();
        }

        // $message = 'Datos guardados correctamente... ';
        // $typeMessage = 'success';
        $this->addFlash($typeMessage, $message);
        // return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));

        //get info
        $arrData['sie']     = $arrDataMultigrado['sie'];
        $arrData['gestion'] = $arrDataMultigrado['gestion'];
        $arrData['idDetalle'] = $arrDataMultigrado['idDetalle'];
        ////////////////////////////////////////////////////////////////
        //get Multigrado per SIE and GESTION
        $objMultigradoArr = $this->getMultigradoPerUeAndGestion($arrData);
        // dump($message);die;
        return $this->render('SieRegularBundle:Multigrado:showNewMultigrado.html.twig', array(
          'objMultigradoArr' => $objMultigradoArr,
          'jsonDataMultigrado' => json_encode($arrData),
          'arrData'=>$arrData,
          'message'=>$message,
          'typeMessage'=> $typeMessage
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        $em->close();
        // echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        $message = 'Problemas al guardar... '.$ex->getMessage(). "\n";
        $typeMessage = 'warning';
        $this->addFlash($typeMessage, $message);
        return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
      }

    }

    /*
    save the confirm multigrado info
    */
    public function confirmMultigradoAction(Request $request){
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
       //get the send values
       $form['jsonDataMultigrado'] = $request->get('jsonDataMultigrado');
       $jsonDataMultigrado = $form['jsonDataMultigrado'];
       $arrDataMultigrado = json_decode($jsonDataMultigrado,true);
      //  dump($arrDataMultigrado);die;
       try {
         $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($arrDataMultigrado['idDetalle']);
         $vproceso->setEsActivo('t');
         $em->persist($vproceso);
         $em->flush();
         $em->getConnection()->commit();

         return 'krlos';

       } catch (Exception $e) {
         $em->getConnection()->rollback();
         $em->close();
         // echo 'Excepción capturada: ', $ex->getMessage(), "\n";
         $message = 'Problemas al guardar... '.$ex->getMessage(). "\n";
         $typeMessage = 'warning';
         $this->addFlash($typeMessage, $message);
         return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
       }

      // return $this->redirectToRoute('ccalidad_list', array('id'=>2, 'gestion'=>2016));
    }




///////////////////////////////////////////////////////////////////////////











    /*
    get the history per student and gestion
    */
    private function getHistoryStudent($rude, $gestion, $institucioneducativa){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("select * from get_estudiante_historial_gestion_json('" . $rude . "','" . $gestion . "','" . $institucioneducativa . "');");
      $query->execute();
      $dataInscriptionJson = $query->fetchAll();
      $dataInscription=array();
      foreach ($dataInscriptionJson as $key => $inscription) {
         # code...
         $dataInscription [] = json_decode($inscription['get_estudiante_historial_gestion_json'],true);
       }
       return $dataInscription;
    }

    public function deleteInscriptionAction(Request $request){

      //create DB conexxion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();

      $valueResp['inscripcionidValido'] = $request->get('inscripcionidValido');
      $valueResp['inscripcionidInValido'] = $request->get('inscripcionidInValido');
      $valueResp['gestion'] = $request->get('gestion');
      $valueResp['matriculaTipo'] = $request->get('matriculaTipo');

      //values to aprobado
      $arrEstados = array(5,26,37,55,56,57,58,11);
      if(!in_array($valueResp['matriculaTipo'], $arrEstados)){
        try {
          //save data in log table
          $this->saveLogDataPerStudent($valueResp, __FUNCTION__);
          //delete the inscription
          $query = $em->getConnection()->prepare("select * from sp_sist_calidad_elim_inscripcion('" . $valueResp['inscripcionidValido'] . "','" . $valueResp['inscripcionidInValido'] . "');");
          $query->execute();
          // Try and commit the transaction
          $em->getConnection()->commit();
          $message = 'Se realizó la validación correctamente ';
          $typeMessage = 'success';
        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      }else{
        $message = 'No se puede proceder, el estudiante tiene estado promovido';
        $typeMessage = 'warning';
      }
      //submit the answer of process
      $this->addFlash($typeMessage, $message);
      return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
    }

    private function saveLogDataPerStudent($valueResp, $theFunction){
      //instanse function from defaultController
      $defaultController = new DefaultCont();
      $defaultController->setContainer($this->container);

      //ge the student info
      $arrValNew = $this->getDataLogSave($valueResp['inscripcionidValido']);
      $arrValOld = $this->getDataLogSave($valueResp['inscripcionidInValido']);

      $resp = $defaultController->setLogTransaccion(
          $valueResp['inscripcionidValido'],
          'EstudianteInscripcion',
          'U',
          json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
          $this->session->get('userId'),
          '',
          json_encode($arrValNew),
          json_encode($arrValOld),
          'SIGED',
          json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => $theFunction ))
      );
    }
    /*
    save info to save in log transaccion table
    */
    private function getDataLogSave($idInscription){
      $em = $this->getDoctrine()->getManager();
      $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscription);
      $arrResult['Id'] = $objInscription->getId();
      $arrResult['matriculaTipo'] = $objInscription->getEstadomatriculaTipo()->getId();
      $arrResult['institucioneducativaCurso'] = $objInscription->getInstitucioneducativaCurso()->getId();
      $arrResult['estadomatriculaTipo'] = $objInscription->getEstadomatriculaInicioTipo()->getId();
      return $arrResult;

    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}
