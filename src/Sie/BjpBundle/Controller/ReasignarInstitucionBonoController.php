<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * EstudianteInscripcion controller.
 *
 */
class ReasignarInstitucionBonoController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request){
      //crear conexion base de datos
      $em = $this->getDoctrine()->getManager();
      // Verificacmos si existe la session de usuario
      if (!$this->session->get('userId')) {
          return $this->redirect($this->generateUrl('login'));
      }
      $consultaAsig = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion');
      $query = $consultaAsig->createQueryBuilder('bjpa')
              ->leftjoin('SieAppWebBundle:BonojuancitoUnidadmilitar', 'bjpum', 'WITH', 'bjpa.bonojuancitoUnidadmilitar = bjpum.id')
              ->where('bjpum.usuario = :id')
              ->setParameter('id', $this->session->get('userId'))
              ->getQuery();

      $resultAsig = $query->getResult();
      $sieArrayue = array();
      $contador = 0;
      foreach ($resultAsig as $key => $value)
      {
        $sieArrayue[$value->getId()] = $value->getInstitucioneducativa() + ' ' + $value->getCodSie() ;
        //$sieArrayue[$value->getCodSie()] = $value->getInstitucioneducativa() + ' ' + $value->getCodSie() ;
        $contador = $contador + 1;
        //echo '<script language="javascript">alert("'.$contador.'");</script>';
       //  $sieArrayue[$value->getCodSie()] =$value->getInstitucioneducativa().'  '.$value->getCodSie();
        //$contador = $contador + 1;
      }
      if ($contador == 0)
      {
            $consultaAsig1 = $em->getRepository('SieAppWebBundle:institucioneducativa');
            $query1 = $consultaAsig1->createQueryBuilder('instedu')
              ->getQuery();

                  $resultAsig1 = $query1->getResult();
                  $sieArray1 = array();
              //    $contador = 0;
                  foreach ($resultAsig1 as $key => $value)
                  {
                    $sieArray1[$value->getId()] = $value->getInstitucioneducativa();
                    $contador = $contador + 1;
                    //echo '<script language="javascript">alert("'.$contador.'");</script>';
                  }
                  
      }      
      else{//return $this->render('SieBjpBundle:ReasignarInstitucionBono:index.html.twig', array(
             //'form' => $this->seleccionUeForm($sieArray)->createView()));
      }
      
      //dump($sieArrayue);die;
    
     $consultaFuerza = $em->getRepository('SieAppWebBundle:BonojuancitoFuerzaTipo');
      $query = $consultaFuerza->createQueryBuilder('bjpf')
                              ->getQuery();
      $resultFuerza = $query->getResult();
      $sieArray = array();
      foreach ($resultFuerza as $key => $value) 
      {
        $sieArray1[$value->getId()] = $value->getFuerza();

      }
      
      return $this->render('SieBjpBundle:ReasignarInstitucionBono:index.html.twig', array(
             'form' => $this->seleccionUeForm($sieArrayue, $sieArray1)->createView()));

              //echo '<script language="javascript">alert("juas0");</script>';
       //   return $this->render('SieBjpBundle:PagosBono:index.html.twig', array('form' => $this->seleccionUeForm()->createView()));
    }

    public function reasignarueAction($id,$idunimil){
        //die($id.$idunimil);        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        try {
            $consultaAsig = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->find($id);        
            $consultaAsig->setBonojuancitoUnidadmilitar($em->getRepository('SieAppWebBundle:BonojuancitoUnidadmilitar')->find($idunimil));
            $em->persist($consultaAsig);
            $em->flush();
            
            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'¡Reasignacion concluida!'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Ha ocurrido un error'.$ex));
        }     
    }
    
    private function seleccionUeForm($sieArray,$sieArray1){
        return $this->createFormBuilder()
                    ->add('sie','text',array('label'=>'Código RUE/SIE','attr'=>array('class'=>'form-control')))
                    ->add('sies', 'choice', array('label' => 'Seleccione Unidad Educativa','required'=> false, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray, 'attr' => array('class' => 'form-control','onchange' => 'validateForm()', 'onchange'=>'informacionUeFindDropDown(this);')))
                    ->add('buscar', 'button', array('label' => 'Buscar Institución Educativa', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))
                
                    //->add('sie','text',array('label'=>'Código RUE/SIE','attr'=>array('class'=>'form-control')))
                    //->add('sies', 'choice', array('label' => 'Seleccione Unidad Educativa','required'=> false, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray, 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
                    //->add('buscar', 'button', array('label' => 'Buscar ', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'informacionUe();')))

                    ->add('reasignar', 'button', array('label' => 'Reasignar Institución Educativa', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'reasignarUe();')))
                    
                    ->add('fuerza', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sieArray1, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    ->add('gum', 'choice', array('label' => 'Gran Unidad Militar' , 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarUm()')))
                    ->add('um', 'choice', array('label' => 'Unidad Militar','required'=> true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                    
                    ->getForm();
    }

public function cargarNivelesAction($fuerza) 
   {
        try {
             $em = $this->getDoctrine()->getManager();
             $em->getConnection()->beginTransaction();
             // $query = $em->createQuery(
             //                    'SELECT  IDENTITY(bjp.gradoTipo) as gradoId,bjp.grado
             //            FROM SieAppWebBundle:BonojuancitoPaga bjp
             //            WHERE bjp.institucioneducativa = :id
             //            AND bjp.gestionTipo = :gestion
             //            AND bjp.nivelTipo = :nivel
             //            ORDER BY bjp.gradoTipo')
             //            ->setParameter('id', $idInstitucion)
             //            ->setParameter('gestion', $this->session->get('currentyear')-1)
             //            ->setParameter('nivel', $nivel);
             //    $grados = $query->getResult();
             /* $query = $em->createQuery(
                            'SELECT  fm.fuerza
                              FROM SieAppWebBundle:BonojuancitoGranUnidadmilitar bjp, SieAppWebBundle:BonojuancitoFuerzaTipo fm
                              WHERE bjp.bonojuancitoFuerzaTipo = :fuerza')

                    ->setParameter('fuerza', $fuerza);
                $gum = $query->getResult();*/
               
              $consultaGranUnidadMil = $em->getRepository('SieAppWebBundle:BonojuancitoGranUnidadmilitar');
              $query = $consultaGranUnidadMil->createQueryBuilder('bjpgum')
              ->leftjoin('SieAppWebBundle:BonojuancitoFuerzaTipo', 'bjpft', 'WITH', 'bjpgum.bonojuancitoFuerzaTipo = bjpft.id')
              ->where('bjpft.id = :id')
              ->setParameter('id', $fuerza)
              ->getQuery();
                            //dump($query->getSQL());die;
                            //       $resultGranUnidadMil = $query->getResult();
                            // dump($resultGranUnidadMil);die;

                            //                 $gumArray = array();
                            //   //              dump($gum);die;
                            //             for ($i = 0; $i < count($resultGranUnidadMil); $i++) {
                            //                 $gumArray[$resultGranUSnidadMil[$i]['id']] = $resultGranUnidadMil[$i]['fuerza'];
                            //             }
                            //             $em->getConnection()->commit();
            $resultFuerza = $query->getResult();
            $sieArray = array();
            foreach ($resultFuerza as $key => $value) 
            {
              $sieArray[$value->getId()] = $value->getGranUnidadmilitar();

            }
           // dump($sieArray);die;
            $response = new JsonResponse();
            return $response->setData(array('fuerza' => $sieArray));

        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }
    
    
    public function cargarUmAction($gum)
    {
      //dump($gum);die;
      try {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
         
        $consultaUnidadMil = $em->getRepository('SieAppWebBundle:BonojuancitoUnidadmilitar');
        $query = $consultaUnidadMil->createQueryBuilder('bjpum')
        ->leftjoin('SieAppWebBundle:BonojuancitoGranUnidadmilitar', 'bjpgum', 'WITH', 'bjpum.bonojuancitoGranUnidadmilitar= bjpgum.id')
        ->where('bjpgum.id = :id')
        ->setParameter('id', $gum)
        
        ->getQuery();
        $resultGum = $query->getResult();
        //dump($resultGum);die;
        $sieArray = array();
        foreach ($resultGum as $key => $value)
        {
          $sieArray[$value->getId()] = $value->getUnidadmilitar();
    
        }
         //dump($sieArray);die;
        $response = new JsonResponse();
        return $response->setData(array('gum' => $sieArray));
    
      } catch (Exception $ex) {
        //$em->getConnection()->rollback();
      }
    }



    public function datosUeForm()
    {
        return $this->createFormBuilder()
                    ->add('codRUE','text', array('label'=>'Código RUE','attr'=>array('class'=>'form-control')))
                    ->getForm();
    }

    public function buscarInfoUeAction(Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $datosUnidadEduAsignacion = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getInfoAsignacion($form);
        //dump($datosUnidadEduAsignacion);die;
        return $this->render('SieBjpBundle:ReasignarInstitucionBono:informacionUe.html.twig', array(
            'formUe' => $this->buscarUeForm($datosUnidadEduAsignacion)->createView(),
             'terminado' => $datosUnidadEduAsignacion[0]['esterminado']
            ));

                
        $sesion = $request->getSession();
        $sesion->set('bjpumid', $datosUnidadEduAsignacion[0]['bjpumid']);
        
        //dump($sesion->get('bjpumid'));die;
        //dump($datosUnidadEduAsignacion[0]['bjpumid']);die;
        
        return $this->render('SieBjpBundle:PagosBono:informacionUe.html.twig', array(
            'formUe' => $this->buscarUeForm($datosUnidadEduAsignacion)->createView(),
             'terminado' => $datosUnidadEduAsignacion[0]['esterminado']
            ));
    }


  public function buscarUeForm($datosUnidadEduAsignacion) {

    $datosCompleto = $datosUnidadEduAsignacion[0];

        return $this->createFormBuilder()
                    ->setAction($this->generateUrl('ReasignarInstitucionBono_webForm'))
                                      ->add('rue','text', array('label'=>' Código RUE :','data'=>$datosCompleto['rue'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('sie','hidden', array('label'=>' Código RUE2 :','data'=>$datosCompleto['rue'],'attr'=>array('class'=>'form-control')))
                                      ->add('estado','hidden', array('label'=>' Estado :','data'=>$datosCompleto['esterminado'],'attr'=>array('class'=>'form-control ', 'disabled'=> true)))
                                      ->add('institucioneducativa','text', array('label'=>'Nombre Institucion Educativa : ','data'=>$datosCompleto['institucioneducativa'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('departamento1','text', array('label'=>'Departamento : ','data'=>$datosCompleto['departamentoTipo'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('provincia','text', array('label'=>'Provincia : ','data'=>$datosCompleto['provincia'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('municipio','text', array('label'=>'Municipio : ','data'=>$datosCompleto['municipio'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('canton','text', array('label'=>'Cantón : ','data'=>$datosCompleto['canton'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('localidad1','text', array('label'=>'Localidad : ','data'=>$datosCompleto['localidad'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('unidadmilitar','text', array('label'=>'Unidad Militar : ','data'=>$datosCompleto['unidadmilitar'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                                      ->add('fuerzamilitar','text', array('label'=>'Fuerza Militar : ','data'=>$datosCompleto['fuerza'],'attr'=>array('class'=>'form-control', 'disabled'=> true)))
                    //->add('abrir', 'submit', array('label' => 'Finalizar el Operativo en la Institucion Educativa ', 'attr' => array('class' => 'btn btn-info btn')))

                    ->getForm();
    }


    public function webFormAction(Request $request){
      $em = $this->getDoctrine()->getManager();
      $formulario = $request->get('form');
      // Lista de turnos validos para la unidad educativa
      $query = $em->createQuery(
                      'SELECT DISTINCT  IDENTITY(bjp.nivelTipo) as nivelId,bjp.nivel
              FROM SieAppWebBundle:BonojuancitoPaga bjp
              WHERE bjp.institucioneducativa = :id
              AND bjp.gestionTipo = :gestion
              ORDER BY bjp.nivelTipo')
              ->setParameter('id', $formulario['sie'])
              ->setParameter('gestion', $this->session->get('currentyear')-1);
      $nivel = $query->getResult();

      $nivelArray = array();
      for ($i = 0; $i < count($nivel); $i++) {
          $nivelArray[$nivel[$i]['nivelId']] = $nivel[$i]['nivel'];
      }

      $form = $this->createFormBuilder()
              ->add('idInstitucion', 'hidden', array('data' =>  $formulario['sie']))
              ->add('idGestion', 'hidden', array('data' => $this->session->get('currentyear')))
              //->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $nivelArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
              // ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
              // ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))

              ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
              ->getForm();
// dump($turnosArray);die;
      // return $this->render('SieBjpBundle:PagosBono:webform.html.twig', array(
      //     ));
          return $this->render('SieBjpBundle:ReasignarInstitucionBono:webform.html.twig', array(
                      // 'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion,
                       'form' => $form->createView()
          ));
    }


        // public function cargarGradosAction($idInstitucion, $gestion, $nivel) {
        //     try {
        //         $em = $this->getDoctrine()->getManager();
        //         $em->getConnection()->beginTransaction();
        //         // $query = $em->createQuery(
        //         //                 'SELECT DISTINCT gt.id,gt.grado
        //         //         FROM SieAppWebBundle:InstitucioneducativaCurso iec
        //         //         JOIN iec.institucioneducativa ie
        //         //         JOIN iec.gradoTipo gt
        //         //         WHERE ie.id = :id
        //         //         AND iec.gestionTipo = :gestion
        //         //
        //         //         AND iec.nivelTipo = :nivel
        //         //         ORDER BY gt.id')
        //         //         ->setParameter('id', $idInstitucion)
        //         //         ->setParameter('gestion', $gestion)
        //         //
        //         //         ->setParameter('nivel', $nivel);
        //         // $grados = $query->getResult();
        //         $query = $em->createQuery(
        //                         'SELECT DISTINCT  IDENTITY(bjp.gradoTipo) as gradoId,bjp.grado
        //                 FROM SieAppWebBundle:BonojuancitoPaga bjp
        //                 WHERE bjp.institucioneducativa = :id
        //                 AND bjp.gestionTipo = :gestion
        //                 AND bjp.nivelTipo = :nivel
        //                 ORDER BY bjp.gradoTipo')
        //                 ->setParameter('id', $idInstitucion)
        //                 ->setParameter('gestion', $this->session->get('currentyear')-1)
        //                 ->setParameter('nivel', $nivel);
        //         $grados = $query->getResult();

        //         $gradosArray = array();
        //         for ($i = 0; $i < count($grados); $i++) {
        //             $gradosArray[$grados[$i]['gradoId']] = $grados[$i]['grado'];
        //         }
        //         $em->getConnection()->commit();
        //         $response = new JsonResponse();
        //         return $response->setData(array('grados' => $gradosArray));
        //     } catch (Exception $ex) {
        //         //$em->getConnection()->rollback();
        //     }
        // }


        // public function cargarParalelosAction($idInstitucion, $gestion, $nivel, $grado) {
        //     try {
        //         $em = $this->getDoctrine()->getManager();
        //         $em->getConnection()->beginTransaction();
        //         // $query = $em->createQuery(
        //         //                 'SELECT DISTINCT pt.id,pt.paralelo
        //         //         FROM SieAppWebBundle:InstitucioneducativaCurso iec
        //         //         JOIN iec.institucioneducativa ie
        //         //         JOIN iec.paraleloTipo pt
        //         //         WHERE ie.id = :id
        //         //         AND iec.gestionTipo = :gestion
        //         //         AND iec.turnoTipo = :turno
        //         //         AND iec.nivelTipo = :nivel
        //         //         AND iec.gradoTipo = :grado
        //         //         ORDER BY pt.id')
        //         //         ->setParameter('id', $idInstitucion)
        //         //         ->setParameter('gestion', $gestion)
        //         //         ->setParameter('turno', $turno)
        //         //         ->setParameter('nivel', $nivel)
        //         //         ->setParameter('grado', $grado);
        //         // $paralelos = $query->getResult();

        //         $query = $em->createQuery(
        //                         'SELECT DISTINCT  IDENTITY(bjp.paraleloTipo) as paraleloId,bjp.paralelo
        //                 FROM SieAppWebBundle:BonojuancitoPaga bjp
        //                 WHERE bjp.institucioneducativa = :id
        //                 AND bjp.gestionTipo = :gestion
        //                 AND bjp.nivelTipo = :nivel
        //                  AND bjp.gradoTipo = :grado
        //                 ORDER BY bjp.paraleloTipo')
        //                 ->setParameter('id', $idInstitucion)
        //                 ->setParameter('gestion', $this->session->get('currentyear')-1)
        //                 ->setParameter('nivel', $nivel)
        //                 ->setParameter('grado', $grado);
        //         $paralelos = $query->getResult();

        //         $paralelosArray = array();
        //         for ($i = 0; $i < count($paralelos); $i++) {
        //             $paralelosArray[$paralelos[$i]['paraleloId']] = $paralelos[$i]['paralelo'];
        //         }
        //         $em->getConnection()->commit();
        //         $response = new JsonResponse();
        //         return $response->setData(array('paralelos' => $paralelosArray));
        //     } catch (Exception $ex) {
        //         //$em->getConnection()->rollback();
        //     }
        // }

        // public function lista_areas_cursoAction(Request $request) {


        //         $em = $this->getDoctrine()->getManager();
        //         $em->getConnection()->beginTransaction();
        //         //echo $request->get('divResultado')."<br>";
        //         //echo $request->get('idInstitucionCurso');
        //         $form = $request->get('form');


        //         $estudianteHabilitados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($form, 'f','t');
        //         $estudiantePagados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($form, 't','t');
        //         $estudianteInHabilitados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($form, 'f','f');

        //           return $this->render('SieBjpBundle:ReasignarInstitucionBono:estudiantesbono.html.twig', array(
        //                       'estudianteHabilitados' => $estudianteHabilitados,
        //                       'estudiantePagados' => $estudiantePagados,
        //                       'estudianteInHabilitados' => $estudianteInHabilitados,
        //                       'formEstudiantes' => $this->formWebForm( json_encode($form))->createView()

        //           ));

        // }


        // private  function formWebForm($data){
        //   $nivelArray = array(
        //     '1'=>'En Tiempo',
        //     '2'=>'Rezagado',
        //     '3'=>'abandono',
        //     '4'=>'Traspaso',
        //     '5'=>'Otro motivo'

        //   );
        //   $form = $this->createFormBuilder()
        //           ->add('data', 'hidden', array('data' =>  $data))
        //           ->add('movida', 'choice', array('label' => ' ','required'=> false, 'empty_value' => 'Seleccionar...', 'choices' => $nivelArray, 'attr' => array('class' => 'form-control')))
        //           ->add('mover', 'button', array('label' => 'Mover', 'attr' => array('class' => 'btn btn-info btn-block', 'onclick'=>'moverHabilitados()')))
        //           ->getForm();
        //   return $form;
        // }

        // public function moverHabilitadosAction(Request $request){

        //   $form = $request->get('form');
        //   $em = $this->getDoctrine()->getManager();

        //   switch ($form['movida']) {
        //     case '1':
        //     case '2':
        //       $this->actualizarEStadoEStudiante($form,'pagados');
        //       break;
        //     case '3':
        //     case '4':
        //     case '5':
        //       $this->actualizarEStadoEStudiante($form,'inhabilitados');
        //       break;

        //     default:
        //       # code...
        //       break;
        //   }
        //   // dump($form);die;
        //   $data = json_decode($form['data'],true);
        //   $estudianteHabilitados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($data, 'f','t');
        //   $estudiantePagados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($data, 't','t');
        //   $estudianteInHabilitados = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getEstudiantesBono($data, 'f','f');

        //     return $this->render('SieBjpBundle:ReasignarInstitucionBono:estudiantesbono.html.twig', array(
        //                 'estudianteHabilitados' => $estudianteHabilitados,
        //                 'estudiantePagados' => $estudiantePagados,
        //                 'estudianteInHabilitados' => $estudianteInHabilitados,
        //                 'formEstudiantes' => $this->formWebForm( json_encode($data))->createView()

        //     ));
        // }

        // private function actualizarEStadoEStudiante($form,$estado){
        //   $em = $this->getDoctrine()->getManager();
        //   foreach ($form as $key => $value) {
        //     # code...
        //     $estudianteMover = substr($key,0,6);
        //     if($estudianteMover=='habmov'){
        //       //actualizar estado
        //       $estudianteModificar = $em->getRepository('SieAppWebBundle:BonojuancitoPaga')->find($value);

        //       switch ($estado) {
        //         case 'pagados':
        //           # code...
        //           $estudianteModificar->setEspagado('t');
        //           break;
        //         case 'inhabilitados':
        //           # code...
        //           $estudianteModificar->setEshabilitado('f');
        //           break;

        //         default:
        //           # code...
        //           break;
        //       }
        //       $em->persist($estudianteModificar);
        //       $em->flush();
        //     }


        //   }
        // }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function index1Action(Request $request, $op) {
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            // generar los titulos para los diferentes sistemas

            //$this->session = new Session();
            $this->session->set('layout', 'layoutBjp.html.twig');
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR':
                    if($this->session->get('roluser') == 9){
                        $this->session->set('tituloTipo','Asignación de Maestros');
                    }else{
                        $this->session->set('tituloTipo', 'Adición/Eliminación de Áreas');
                    }
                    $this->session->set('layout', 'layoutBjp.html.twig');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                    $this->session->set('layout', 'layoutBjp.html.twig');
                    break;
                default: $this->session->set('tituloTipo', 'ReasignarInstitucionBono');
                    $this->session->set('layout', 'layoutBjp.html.twig');
                    break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /**
                 * VErificamos si la gestion es 2015
                 */
                if ($form['gestion'] < 2008 or $form['gestion'] > 2017) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida.');
                    return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /**
                 * test de carga de nombre institucion educativa
                 */
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * verificamos si tiene tuicion
                 */
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['institucioneducativa']);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];

                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 1) { // si no es estudiante
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');

                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = date('Y') - 1;
                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = date('Y') - 1;
                        //echo $institucion;die;
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Lista de turnos validos para la unidad educativa
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $institucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }

            /**
             * Creamos el formulario de busqueda de turno nivel grado y paralelo
             */
            $form = $this->createFormBuilder()
                    ->add('idInstitucion', 'hidden', array('data' => $institucion))
                    ->add('idGestion', 'hidden', array('data' => $gestion))
                    ->add('turno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    ->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
                    ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
                    ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
                    ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
                    ->getForm();
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */


            $em->getConnection()->commit();



            return $this->render('SieBjpBundle:ReasignarInstitucionBono:index.html.twig', array(
                        'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion, 'form' => $form->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieBjpBundle:ReasignarInstitucionBono:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */


    private function formSearch($gestionactual) {
        $gestiones = array();
        for($i=$gestionactual;$i>=2008;$i--){
            $gestiones[$i] = $i;}

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('ReasignarInstitucionBono'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                //->add('gestion1', 'choice', array('required' => true, 'choices' => $gestiones))
                //->add('generoTipo1', 'entity', array('class' => 'SieAppWebBundle:Institucioneducativa', 'property' => 'id'))
                ->add('gestion1', 'entity', array('class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil'))
                /*->add('institucioneducativa1', 'entity', array('class' => 'SieAppWebBundle:Institucioneducativa',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('ie')
                                ->where('ie.id = :id')
                                ->setParameter('id', '80730460')
                                ->orderBy('ie.id', 'ASC')
                        ;
                    }, 'property' => 'id'))*/
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Funcion para cargar los grados segun el nivel, para el nuevo curso
     */
    public function listargradosAction($nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //$dep = $em->getRepository('SieAppWebBundle:GradoTipo')->findAll();
            if ($nivel == 11) {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2));
            } else {
                $query = $em->createQuery(
                                'SELECT gt
                                FROM SieAppWebBundle:GradoTipo gt
                                WHERE gt.id IN (:id)
                                ORDER BY gt.id ASC'
                        )->setParameter('id', array(1, 2, 3, 4, 5, 6));
            }
            $gra = $query->getResult();
            $lista = array();
            foreach ($gra as $gr) {
                $lista[$gr->getId()] = $gr->getGrado();
            }
            $list = $lista;
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('listagrados' => $list));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax para la seleccion de nivel grado y paralelo
     */

    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion, $gestion) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('turnos' => $turnosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */

    // public function cargarNivelesAction($idInstitucion, $gestion, $turno) {
    //     try {
    //         $em = $this->getDoctrine()->getManager();
    //         $em->getConnection()->beginTransaction();
    //         $query = $em->createQuery(
    //                         'SELECT DISTINCT nt.id,nt.nivel
    //                 FROM SieAppWebBundle:InstitucioneducativaCurso iec
    //                 JOIN iec.institucioneducativa ie
    //                 JOIN iec.nivelTipo nt
    //                 WHERE ie.id = :id
    //                 AND iec.gestionTipo = :gestion
    //                 AND iec.turnoTipo = :turno
    //                 ORDER BY nt.id')
    //                 ->setParameter('id', $idInstitucion)
    //                 ->setParameter('gestion', $gestion)
    //                 ->setParameter('turno', $turno);
    //         $niveles = $query->getResult();
    //         $nivelesArray = array();
    //         for ($i = 0; $i < count($niveles); $i++) {
    //             $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
    //         }
    //         $em->getConnection()->commit();
    //         $response = new JsonResponse();
    //         return $response->setData(array('niveles' => $nivelesArray));
    //     } catch (Exception $ex) {
    //         //$em->getConnection()->rollback();
    //     }
    // }



    /*
     * Lista de areas segun el nivel
     * ventana modal
     */
    /*

    public function lista_areas_nivelAction($idNivel, $idCurso) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();

            // Creamos los arrays de materias segun la gestion
            $matpri = array(203, 204, 205, 207, 250, 252, 253, 254, 257);
            $matsec = array(203, 204, 205, 207, 251, 252, 253, 254, 257, 258);

            $matiniant = array(101, 102, 103, 104, 105);
            $matpriant = array(201, 203, 204, 205, 206, 207, 209, 210);
            $matsecantter = array(301, 302, 304, 305, 309, 313, 316, 317, 318, 319);
            $matsecantcua = array(301, 302, 303, 304, 305, 309, 313, 316, 317, 318, 319);
            $matsecantquisex = array(301, 302, 303, 305, 307, 308, 310, 311, 312, 313, 316, 317, 318, 319);


            $matinia = array(1000, 1001, 1002, 1003);
            $matpria = array(1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019);

            $matseca = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1044);
            $matsecb = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1043, 1044);
            $matsecc = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1041, 1042, 1043, 1044);

            $matsecd = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044);
            $matsece = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045);
            $matsecf = array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045);

            $matsecg = array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045); // PAra gestiones 2016 en adelante grados 5 y 6

            // Para unidades educativas nocturnas
            $matnocta = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044); // para 1 y 2
            $matnoctb = array(1031,1032,1033,1037,1040,1043,1044,1045); // para 3,4,5 y 6



            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getGradoTipo()->getId();
            $turno = $curso->getTurnoTipo()->getId();

            // VAlidamos q la gestion sea mayor o igual a 2008
            if($gestion < 2008){
                echo "La gestion seleccionada no es válida !!!";
                die;
            }

            switch ($gestion) {
                case 2008:
                case 2009:
                case 2010:
                case 2011:
                case 2012:
                case 2013:
                    switch ($nivel) {
                        case 11:
                        case 1:
                            $idsAsignaturas = $matiniant;
                            break;
                        case 12:
                        case 2:
                            switch ($grado) {
                                case 1:
                                    $idsAsignaturas = $matpri;
                                    break;
                                case 2:
                                case 3:
                                case 4:
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matpriant;
                                    break;
                            }
                            break;
                        case 13:
                        case 3:
                            switch ($grado) {
                                case 1:
                                    $idsAsignaturas = $matsec;
                                    break;
                                case 2:
                                    $idsAsignaturas = $matpriant;
                                    break;
                                case 3:
                                    $idsAsignaturas = $matsecantter;
                                    break;
                                case 4:
                                    $idsAsignaturas = $matsecantcua;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecantquisex;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2014:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    $idsAsignaturas = $matseca;
                                    break;
                                case 3:
                                case 4:
                                    $idsAsignaturas = $matsecc;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecc;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2015:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    $idsAsignaturas = $matsecd;
                                    break;
                                case 3:
                                case 4:
                                    $idsAsignaturas = $matsece;
                                    break;
                                case 5:
                                case 6:
                                    $idsAsignaturas = $matsecf;
                                    break;
                            }
                            break;
                    }
                    break;
                case 2016:
                    switch ($nivel) {
                        case 11:
                            $idsAsignaturas = $matinia;
                            break;
                        case 12:
                            $idsAsignaturas = $matpria;
                            break;
                        case 13:
                            switch ($grado) {
                                case 1:
                                case 2:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnocta;
                                    }else{
                                        $idsAsignaturas = $matsecd;
                                    }
                                    break;
                                case 3:
                                case 4:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnoctb;
                                    }else{
                                        $idsAsignaturas = $matsece;
                                    }
                                    break;
                                case 5:
                                case 6:
                                    if($turno == 4){
                                        // Para unidades educativas nocturnas
                                        $idsAsignaturas = $matnoctb;
                                    }else{
                                        $idsAsignaturas = $matsecg;
                                    }
                                    break;
                            }
                            break;
                    }
                    break;
            }
            //dump($idsAsignaturas);die;
            $asignaturas = $em->createQuery(
                    'SELECT at
                    FROM SieAppWebBundle:AsignaturaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
            )->setParameter('ids',$idsAsignaturas)
            ->getResult();

            $areasNivel = $asignaturas;
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));

            $areasArray = array();
            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areasNivel[$i]->getId() == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $check = 'checked';
                        $bloqueado = 'disabled';
                    }
                }
                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') {
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
                }
            }
            $maestros = $em->createQueryBuilder()
                           ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet')
                           ->from('SieAppWebBundle:MaestroInscripcion','mi')
                           ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                           ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                           ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                           ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                           ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                           ->where('ie.id = :idInstitucion')
                           ->andWhere('gt.id = :gestion')
                           ->andWhere('rt.id = :rol')
                           ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                           ->setParameter('gestion',$this->session->get('idGestion'))
                           ->setParameter('rol',2)
                           ->orderBy('p.paterno','asc')
                           ->addOrderBy('p.materno','asc')
                           ->addOrderBy('p.nombre','asc')
                           ->getQuery()
                           ->getResult();

            $em->getConnection()->commit();
            return $this->render('SieBjpBundle:PagosBono:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
            //return $this->render('SieRegularBundle:Areas:show.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }
*/
    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */
    /*


*/
    /**
     * Fcunrion para eadicionar y elimiar areas
     */
    /*
    public function lista_areas_curso_adicionar_eliminarAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session;
            $idCurso = $request->get('idInstitucionCurso');
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            /*
             * Areas a registrar nuevos
             */
      /*      $areas = $request->get('areas');
            /*
             * Areas registradas anteriormente
             */
        /*    $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));
            /**
             * Aplicamos la funcion para actualizar el primary key
             */
        /*    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            for ($i = 0; $i < count($areas); $i++) {
                $existe = 'no';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areas[$i] == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $existe = 'si';
                    }
                }
                if($this->session->get('idGestion') == 2016 and $areas[$i] == 1039){
                    $existe = 'si';
                }
                if ($existe == 'no') {
                    //echo $areas[$i]." - ".$request->get('idInstitucionCurso')."<br>";
                    $newArea = new InstitucioneducativaCursoOferta();
                    $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                    $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                    $newArea->setHorasmes(0);
                    $em->persist($newArea);
                    $em->flush();

                    // Verificamos si registraron el maestro pra registrarlo en la tabla curso oferta maestro
                    if ($request->get($areas[$i])) {
                         $idMaestroInscripcion = $request->get($areas[$i]);
                         //dump($idMaestroInscripcion);
                         //dump($newArea->getId());
                         //die;
                         $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                         $nuevoCOM = new InstitucioneducativaCursoOfertaMaestro();
                         $nuevoCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                         $nuevoCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroInscripcion));
                         $nuevoCOM->setHorasMes(0);
                         $nuevoCOM->setFechaRegistro(new \DateTime('now'));
                         $nuevoCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                         $nuevoCOM->setEsVigenteMaestro('t');
                         $em->persist($nuevoCOM);
                         $em->flush();
                    }

                    // Listamos los estudinates inscritos
                    // para registrar el curso a los estudiantes
                    $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso, 'estadomatriculaTipo'=>array(4,5,11)));
                    foreach ($inscritos as $ins) {
                        // Verificamos si el estudiante ya tiene la asignatura
                        $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
                        if(!$estInscripcion){
                            //echo "agregando notas a ". $ins->getEstudiante()->getNombre() ." <br>";
                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                            $query->execute();
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                            $estAsignaturaNew->setFechaRegistro(new \DateTime('now'));
                            $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                            $query->execute();

                        }
                    }
                }
            }

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                                    INNER JOIN ieco.asignaturaTipo at
                                    WHERE ieco.insitucioneducativaCurso = :iecId
                                    ORDER BY at.id ASC')
                                    ->setParameter('iecId', $idCurso)->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                    ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $idCurso)
                    ->orderBy('at.id','ASC')
                    ->getQuery()
                    ->getResult();
            $array = array();
            foreach ($totalAreasCurso as $tac) {
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura']);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieBjpBundle:PagosBono:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => ''));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }
*/
    /**
     * Eliminar un area
     */
    /**public function deleteAction($idCursoOferta) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());
            /**
             * Si existe el curso y el curso oferta entonces eliminamos el curso oferta
            */
            /*
            if ($cursoOferta and $curso) {
                //Verificamos si el curso oferta no tiene estudidantes asociados en la tabla estudiante asignatura
                /* $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
                  if($estudianteAsignatura){
                  $mensaje='No se puede eliminar el área, porque tiene estudiantes asociados';
                  $eliminar='no';
                  } */


                // VErificamos si tiene maestro asignado
                /* if($cursoOferta->getMaestroInscripcion() != null){
                  $mensaje = 'No se puede eliminar el área si tiene un maestro asignado, primero debe quitar el maestro';
                  $eliminar='no';
                  } */
/*
                $cursosOfertasSimilares = $em->createQuery(
                                'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.insitucioneducativaCurso iec
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE iec.id = :idCurso
                    AND at.id = :idAsignatura')
                        ->setParameter('idCurso', $curso->getId())
                        ->setParameter('idAsignatura', $cursoOferta->getAsignaturaTipo()->getId())
                        ->getResult();


                foreach ($cursosOfertasSimilares as $co) {
                    $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $co->getId()));

                    foreach ($estudianteAsignatura as $ea) {
                        // Eliminamos las notas
                        $em->createQuery(
                                        'DELETE FROM SieAppWebBundle:EstudianteNota en
                            WHERE en.estudianteAsignatura = :idEstAsig')
                                ->setParameter('idEstAsig', $ea->getId())->execute();
                    }

                    // Eliminamos en la tabla estudiante asignatura
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:EstudianteAsignatura ea
                        WHERE ea.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Eliminamos los registros de maestros
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                        WHERE iecom.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Registramos en la tabla de control
                    // Eliminamos el curso oferta

                    $cursoOfertaEliminar = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());
                    $em->remove($cursoOfertaEliminar);


                }
                $em->flush();
                $mensaje = 'Se elimino el área del curso';
            } else {
                $mensaje = 'No se puede eliminar el área';
            }

            $this->session = new Session();

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $curso->getId())->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        //->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $curso->getId())
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();
                $array = array();
                //dump($totalAreasCurso);
                foreach ($totalAreasCurso as $tac) {
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        //'area' => $tac['area'],
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura']);
                }
                $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieBjpBundle:PagosBono:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => $mensaje));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }*/

    /*
     * Asignar maestro al area
     */
    /*

    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');

        $em = $this->getDoctrine()->getManager();
        $maestrosMateria = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$ieco));
        //dump('ss');die;
        // Obtener datos del curso
        $curso = $em->createQueryBuilder()
                    ->select('ie.id as sie, gt.id as gestion')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                    ->where('ieco.id = :idCursoOferta')
                    ->setParameter('idCursoOferta',$ieco)
                    ->getQuery()
                    ->getResult();

        $sie = $curso[0]['sie'];
        $gestion = $curso[0]['gestion'];

        $arrayMaestros = array();
        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
            // trimestrales
            $inicio = 6;
            $fin = 8;
        }else{
            // Bimestrales
            $inicio = 0;
            $operativo = $this->operativo($sie,$gestion);
            if($operativo == 5){
                $fin = 4; //4;
            }else{
                $fin = $operativo - 1;
            }

        }
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($maestrosMateria as $mm) {
                if($mm->getNotaTipo()->getId() == $i){
                    $arrayMaestros[] = array(
                                            'id'=>$mm->getId(),
                                            'idmi'=>$mm->getMaestroInscripcion()->getId(),
                                            'horas'=>$mm->getHorasMes(),
                                            'idNotaTipo'=>$mm->getNotaTipo()->getId(),
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
                    $existe = true;
                    break;
                }
            }
            if($existe == false){
                $arrayMaestros[] = array(
                                            'id'=>'nuevo',
                                            'idmi'=>'',
                                            'horas'=>'',
                                            'idNotaTipo'=>$i,
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
            }
        }

        $maestros = $em->createQueryBuilder()
                        ->select('mi')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','mi.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','mi.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                        ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                        ->where('ie.id = :sie')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('rt.id = 2')
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();

        $operativo = $this->operativo($sie,$gestion);

        //dump($arrayMaestros);die;

        return $this->render('SieBjpBundle:PagosBono:maestros.html.twig',array('maestrosCursoOferta'=>$arrayMaestros, 'maestros'=>$maestros,'ieco'=>$ieco,'operativo'=>$operativo));
    }
    */
    /*

    public function maestrosAsignarAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        $horas = $request->get('horas');

        if(count($ieco)>0){
            for($i=0;$i<count($ieco);$i++){
                if($horas[$i] == ''){
                    $horasNum = 0;
                }else{
                    $horasNum = $horas[$i];
                }
                if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                    $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                    $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                    $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $newCOM->setHorasMes($horasNum);
                    $newCOM->setFechaRegistro(new \DateTime('now'));
                    $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                    $newCOM->setEsVigenteMaestro('t');
                    $em->persist($newCOM);
                    $em->flush();
                }else{
                    if($idmi[$i] != ''){
                        $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                        $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                        $updateCOM->setHorasMes($horasNum);
                        $updateCOM->setFechaModificacion(new \DateTime('now'));
                        $updateCOM->setEsVigenteMaestro('t');
                        $em->flush();
                    }
                }
            }
            $mensaje = "Cambios guardados";
        }else{
            $mensaje = "No registrado";
        }


        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0],'mensaje'=>$mensaje));
    }*/
    /*

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
            //dump($registroOperativo);die;
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }*/
    /*
    public function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
        }
        return $lit;
    }*/

}
