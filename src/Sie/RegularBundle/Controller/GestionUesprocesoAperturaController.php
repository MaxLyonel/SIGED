<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\UsuarioGeneracionRude;
use Sie\AppWebBundle\Entity\InstitucioneducativaNoacreditadosDdjj;

/**
 * GestionUesprocesoApertura controller.
 *
 */
class GestionUesprocesoAperturaController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    /**
     * index of gestion ues en proceso de acreditacion
     * send request values
     */
    public function indexAction(Request $request) {

        try {
          //validate user logged
          $id_usuario = $this->session->get('userId');
          if (!isset($id_usuario)) {
              return $this->redirect($this->generateUrl('login'));
          }
          //crete the db conexion
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();
          // get the send values
          $form = $request->get('form');
          $dataInfo = $form['dataInfo'];
          $arrDataInfo = json_decode($dataInfo, true);

          //get the courses per UE
          $query = $em->createQuery(
                  'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                  WHERE iec.institucioneducativa = :idInstitucion
                  AND iec.gestionTipo = :gestion
                  AND iec.nivelTipo IN (:niveles)
                  ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                  ->setParameter('idInstitucion', $arrDataInfo['sie'])
                  ->setParameter('gestion', $this->session->get('currentyear'))
                  ->setParameter('niveles',array(11,12,13));

          $cursos = $query->getResult();

          //dump($form);die;
          return $this->render('SieRegularBundle:GestionUesprocesoApertura:index.html.twig', array(
                                'cursos'   => $cursos,
                                'dataInfo' => $dataInfo,
                                'arrDataInfo'=>$arrDataInfo
          ));
          //return $this->render('SieRegularBundle:RegistroInstitucionEducativa:new.html.twig', array('form' => $formulario->createView()));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }


    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        try{
            $gestiones = array($gestion=>$gestion,$gestion-1=>$gestion-1);
            return $this->createFormBuilder()
                    ->setAction($this->generateUrl('adicionNotas'))
                    ->add('codigoRude','text',array('label'=>'Código Rude','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                    ->add('idInstitucion','text',array('label'=>'Sie','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                    ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                    ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
        }catch(Exception $ex){

        }
    }

    public function mainCursoAction(Request $request){

      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      //crete the db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send values
      $form = $request->get('dataInfo');
      $dataInfo = $request->get('dataInfo');
      $arrDataInfo = json_decode($dataInfo, true);

      //get the courses for UE

      $objInstitucioneducativaNoacreditadosDdjj = $em->getRepository('SieAppWebBundle:InstitucioneducativaNoacreditadosDdjj')->findOneBy(array(
        'institucioneducativa' => $arrDataInfo['sie'],
        'gestionTipoId'          => $this->session->get('currentyear')
      ));

      $cursos = $this->getAllCursos($arrDataInfo);
      return $this->render('SieRegularBundle:GestionUesprocesoApertura:maincurso.html.twig', array(
                            'cursos'        => $cursos,
                            'dataInfo'      => $dataInfo,
                            'swShowlinkDdjj' => ($objInstitucioneducativaNoacreditadosDdjj)?true:false
      ));
    }

    /**
    *get the all cursos
    **/
    private function getAllCursos($data){
      $em = $this->getDoctrine()->getManager();
      $qCursos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
      $query = $qCursos->createQueryBuilder('iec')
              ->select('iec.id as id, IDENTITY(iec.nivelTipo) as nivelTipo, IDENTITY(iec.gradoTipo) as gradoTipo,IDENTITY(iec.paraleloTipo) as paraleloTipo,
                      IDENTITY(iec.turnoTipo) as turnoTipo, iecddjj.esabierta as printddjj, tt.turno, nt.nivel, gt.grado, pt.paralelo '
                      )
              ->leftjoin('SieAppWebBundle:InstitucioneducativaNoacreditadosDdjj', 'iecddjj', 'WITH', 'iec.institucioneducativa = iecddjj.institucioneducativa')
              ->leftjoin('SieAppWebBundle:turnoTipo', 'tt', 'WITH', 'iec.turnoTipo = tt.id')
              ->leftjoin('SieAppWebBundle:nivelTipo', 'nt', 'WITH', 'iec.nivelTipo = nt.id')
              ->leftjoin('SieAppWebBundle:gradoTipo', 'gt', 'WITH', 'iec.gradoTipo = gt.id')
              ->leftjoin('SieAppWebBundle:paraleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
              ->where('iec.institucioneducativa = :idInstitucion')
              ->andwhere('iec.gestionTipo = :gestion')
              ->andwhere('iec.nivelTipo IN (:niveles)')
              ->setParameter('idInstitucion', $data['sie'])
              ->setParameter('gestion', $this->session->get('currentyear'))
              ->setParameter('niveles',array(11,12,13,999,401))
              ->orderBy('iec.turnoTipo')
              ->orderBy('iec.nivelTipo')
              ->orderBy('iec.gradoTipo')
              ->orderBy('iec.paraleloTipo')
              ->getQuery();
      $objresultInscription = $query->getResult();
      return $objresultInscription;
    }

    public function newcursoAction(Request $request){
      //dump($request);die;
      //GET SEND VALUES
      $dataInfo = $request->get('dataInfo');
      $arrDataInfo = json_decode($dataInfo, true);

      try{
          $em = $this->getDoctrine()->getManager();
          // $em->getConnection()->beginTransaction();
          $this->session = new Session();
          /*
           * opciones para los usuarios no nacionales
           */
          if($this->session->get('roluser') != 8 && $this->session->get('roluser') != 10){

              // Lista de turnos validos para la unidad educativa
              $query = $em->createQuery(
                      'SELECT DISTINCT tt.id,tt.turno
                      FROM SieAppWebBundle:InstitucioneducativaCurso iec
                      JOIN iec.institucioneducativa ie
                      JOIN iec.turnoTipo tt
                      WHERE ie.id = :id
                      AND iec.gestionTipo = :gestion
                      ORDER BY tt.id')
                      ->setParameter('id',$arrDataInfo['sie'])
                      ->setParameter('gestion',$this->session->get('idGestion'));
              $turnos = $query->getResult();
              $turnosArray = array();
              for($i=0;$i<count($turnos);$i++){
                  $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
              }
              /*
               * Listamos los niveles validos
               */
              $query = $em->createQuery(
                                      'SELECT n FROM SieAppWebBundle:NivelTipo n
                                      WHERE n.id IN (:id)'
                                      )->setParameter('id',array(12,13));
              $niveles_result = $query->getResult();
              $niveles = array();
              /*foreach ($niveles_result as $n){
                  $niveles[$n->getId()] = $n->getNivel();
              }*/
          }else{

              /*
               * Listamos los turnos validos
               */
              $query = $em->createQuery(
                                      'SELECT t FROM SieAppWebBundle:TurnoTipo t
                                      WHERE t.id IN (:id)'
                                      )->setParameter('id',array(1,2,4,8,9,10,11));
              $turnos_result = $query->getResult();
              $turnosArray = array();
              foreach ($turnos_result as $t){
                  $turnosArray[$t->getId()] = $t->getTurno();
              }

              /*
               * Listamos los niveles validos
               */
              $query = $em->createQuery(
                                      'SELECT n FROM SieAppWebBundle:NivelTipo n
                                      WHERE n.id IN (:id)'
                                      )->setParameter('id',array(12,13));
              $niveles_result = $query->getResult();
              $niveles = array();
              foreach ($niveles_result as $n){
                  $niveles[$n->getId()] = $n->getNivel();
              }

          }
          /*
           * Listamos los grados para nivel inicial
           */
          $query = $em->createQuery(
                                  'SELECT g FROM SieAppWebBundle:GradoTipo g
                                  WHERE g.id IN (:id)'
                                  )->setParameter('id',array(1,2));
          $grados_result = $query->getResult();
          $grados = array();
          foreach ($grados_result as $g){
              $grados[$g->getId()] = $g->getGrado();
          }

          /*
           * Listamos los paralelos validos
           */
          $query = $em->createQuery(
                                  'SELECT p FROM SieAppWebBundle:ParaleloTipo p
                                  WHERE p.id != :id'
                                  )->setParameter('id',0);
          $paralelos_result = $query->getResult();
          $paralelos = array();
          foreach ($paralelos_result as $p){
              $paralelos[$p->getId()] = $p->getParalelo();
          }

          
          if($arrDataInfo['institucionEducativaTipoId']==4){
            $arrNiveles = array('999'=>'Indirecta', '401'=>'Directa');
            $arrGrados = array('99'=>'Sin dato');
            
              $form = $this->createFormBuilder()
                        //->setAction($this->generateUrl('creacioncursos_create'))
                        ->add('idInstitucion','hidden',array('data'=>$arrDataInfo['sie']))
                        ->add('idGestion','hidden',array('data'=>$this->session->get('currentyear')))
                        ->add('dataInfo','hidden',array('data'=>$dataInfo))
                        ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'')))
                        ->add('nivel','choice',array('label'=>'Modalidad','choices'=>$arrNiveles,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'')))
                        ->add('grado','choice',array('label'=>'Grado','choices'=>$arrGrados,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('guardar','button',array('label'=>'Crear Curso','attr'=>array('class'=>'btn btn-primary', 'onclick'=>'saveCurso()')))
                        ->getForm();

          }else{

              if($this->session->get('roluser') != 8 && $this->session->get('roluser') != 10){
                $form = $this->createFormBuilder()
                        //->setAction($this->generateUrl('creacioncursos_create'))
                        ->add('idInstitucion','hidden',array('data'=>$arrDataInfo['sie']))
                        ->add('idGestion','hidden',array('data'=>$this->session->get('currentyear')))
                        ->add('dataInfo','hidden',array('data'=>$dataInfo))
                        ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarNiveles(this.value)')))
                        ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarGrados(this.value)')))
                        ->add('grado','choice',array('label'=>'Grado','choices'=>$grados,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('guardar','button',array('label'=>'Crear Curso','attr'=>array('class'=>'btn btn-primary', 'onclick'=>'saveCurso()')))
                        ->getForm();
            }else{
                $form = $this->createFormBuilder()
                        //->setAction($this->generateUrl('creacioncursos_create'))
                        ->add('idInstitucion','hidden',array('data'=>$arrDataInfo['sie']))
                        ->add('idGestion','hidden',array('data'=>$this->session->get('currentyear')))
                        ->add('dataInfo','hidden',array('data'=>$dataInfo))
                        ->add('turno','choice',array('label'=>'Turno','choices'=>$turnosArray,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('nivel','choice',array('label'=>'Nivel','choices'=>$niveles,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control','onchange'=>'listarGrados(this.value)')))
                        ->add('grado','choice',array('label'=>'Grado','choices'=>$grados,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                        ->add('guardar','button',array('label'=>'Crear Curso','attr'=>array('class'=>'btn btn-primary', 'onclick'=>'saveCurso()')))
                        ->getForm();
            }

          }

          
          $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrDataInfo['sie']);
          // $em->getConnection()->commit();
          return $this->render('SieRegularBundle:GestionUesprocesoApertura:newcurso.html.twig',array(
            'form'        => $form->createView(),
            'institucion' => $institucion,
            'gestion'     => $this->session->get('currentyear'),
            //'dataInfo'    => $dataInfo
          ));
      }catch(Exception $ex){
          // $em->getConnection()->rollback();
      }
      //return $this->render('SieRegularBundle:GestionUesprocesoApertura:newcurso.html.twig');
    }
    /**
    * save the new curso to the UE
    */
    public function saveCursoAction(Request $request){

      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      try{
          $form = $request->get('form');

          /*
           * Verificamos si existe el curso
           */
          $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(  'institucioneducativa'=>$form['idInstitucion'],
                                                                                                      'gestionTipo'=>$form['idGestion'],
                                                                                                      'turnoTipo'=>$form['turno'],
                                                                                                      'nivelTipo'=>$form['nivel'],
                                                                                                      'gradoTipo'=>$form['grado'],
                                                                                                      'paraleloTipo'=>$form['paralelo']));


          if($curso){
              
              $this->get('session')->getFlashBag()->add('newCursoError', 'No se pudo crear el curso, ya existe un curso con las mismas características.');
              //return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
          }else{
              // Actualizamos el increment de la tabla
              $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');");
              $query->execute();


              // Si no existe el curso
              $nuevo_curso = new InstitucioneducativaCurso();
              $nuevo_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['idGestion']));
              $nuevo_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']));
              $nuevo_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
              $nuevo_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
              switch($form['nivel']){
                  case 11:$ciclo=1;break;
                  case 12:
                          switch($form['grado']){
                              case 1:
                              case 2:
                              case 3: $ciclo = 1;break;
                              case 4:
                              case 5:
                              case 6: $ciclo = 2;break;
                          }
                          break;
                  case 13:
                          switch($form['grado']){
                              case 1:
                              case 2: $ciclo = 1;break;
                              case 3:
                              case 4: $ciclo = 2;break;
                              case 5:
                              case 6: $ciclo = 3;break;
                          }
                          break;
                  case 999:
                  $ciclo = 0;
                  break;
                  case 401:
                  $ciclo = 0;
                  $form['grado']=1;
                  break;
                  default:$ciclo = 0;break;       
              }

              $nuevo_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find($ciclo));
              $nuevo_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
              $nuevo_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
              $nuevo_curso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
              $nuevo_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
              $em->persist($nuevo_curso);
              $em->flush();

              $em->getConnection()->commit();
              $this->get('session')->getFlashBag()->add('newCursocreated', 'Curso creado correctamente');
              // $nuevo_curso->setMultigrado(0);
              // $nuevo_curso->setDesayunoEscolar(1);
              // $nuevo_curso->setModalidadEnsenanza(1);
              // $nuevo_curso->setIdiomaMasHabladoTipoId(48);
              // $nuevo_curso->setIdiomaRegHabladoTipoId(0);
              // $nuevo_curso->setIdiomaMenHabladoTipoId(0);
              // $nuevo_curso->setPriLenEnsenanzaTipoId(48);
              // $nuevo_curso->setSegLenEnsenanzaTipoId(0);
              // $nuevo_curso->setTerLenEnsenanzaTipoId(1);
              // $nuevo_curso->setFinDesEscolarTipoId(4);
              // switch ($form['nivel']) {
              //     case '11': $nroMaterias = 4;break;
              //     case '12': $nroMaterias = 9;break;
              //     case '13': $nroMaterias = 11;break;

              //     default:
              //         $nroMaterias = 4;
              //         break;
              // }
              // $nuevo_curso->setNroMaterias($nroMaterias);
              // $nuevo_curso->setConsolidado(1);
              // $nuevo_curso->setPeriodicidadTipoId(1111100);
              // $nuevo_curso->setNotaPeriodoTipo($em->getRepository('SieAppWebBundle:NotaPeriodoTipo')->find(4));

              
              //return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));

          }
        
      }catch(Exception $ex){
          $em->getConnection()->rollback();
      }

        //get sw on ddjj to the ue
          $objInstitucioneducativaNoacreditadosDdjj = $em->getRepository('SieAppWebBundle:InstitucioneducativaNoacreditadosDdjj')->findOneBy(array(
            'institucioneducativa'   => $form['idInstitucion'],
            'gestionTipoId'          => $form['idGestion']
          ));

          //get the data send
          $dataInfo = $form['dataInfo'];
          $arrDataInfo = json_decode($dataInfo, true);
          //get the courses for UE
          $cursos = $this->getAllCursos($arrDataInfo);
          return $this->render('SieRegularBundle:GestionUesprocesoApertura:maincurso.html.twig', array(
                                'cursos'         => $cursos,
                                'dataInfo'       => $form['dataInfo'],
                                'swShowlinkDdjj' => ($objInstitucioneducativaNoacreditadosDdjj)?true:false
                              ));

    }

    /**
    *
    **/
    public function removeCursoAction(Request $request){

        try{
          //GET SEND VALUES
          $dataInfo = $request->get('infoData');
          $arrDataInfo = json_decode($dataInfo, true);

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            /*
             * Verificamos si tiene estudiantes inscritos
             */
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$request->get('idCurso')));
            if($inscritos){
                $this->get('session')->getFlashBag()->add('deleteCursoError', 'No se puede eliminar el curso, porque tiene estudiantes inscritos');
                //return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            }else{
              /*
               * Eliminamos el curso
               */
              $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
              $em->remove($curso);
              $em->flush();
              //to do the transaction on DB
              $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newCursocreated', 'Curso eliminado correctamente');
            }
            //get sw on ddjj to the ue
            $objInstitucioneducativaNoacreditadosDdjj = $em->getRepository('SieAppWebBundle:InstitucioneducativaNoacreditadosDdjj')->findOneBy(array(
              'institucioneducativa'   => $arrDataInfo['sie'],
              'gestionTipoId'          => $this->session->get('currentyear')
            ));
            //$this->get('session')->getFlashBag()->add('deleteCursoOk', 'Se eliminó el curso correctamente');
            //return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
            //get the courses for UE
            $cursos = $this->getAllCursos($arrDataInfo);
            return $this->render('SieRegularBundle:GestionUesprocesoApertura:maincurso.html.twig', array(
                                  'cursos'   => $cursos,
                                  'dataInfo' => $request->get('infoData'),
                                  'swShowlinkDdjj' => ($objInstitucioneducativaNoacreditadosDdjj)?true:false
                                ));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteCursoError', $ex->getMessage());
            return $this->redirect($this->generateUrl('creacioncursos',array('op'=>'result')));
        }
    }

    /***
    * to do the student inscription
    **/
    public function inscribirStudentsAction(Request $request){


      return $this->render('SieRegularBundle:GestionUesprocesoApertura:lookforstudent.html.twig', array(
        'form'=>$this->findStudentForm($request->get('idCurso'),$request->get('dataInfo'))->createView()
      ));
    }


    /**
    * create the form to find the student by rude
    **/
    private function findStudentForm($idCurso,$data){
      //set the day array
      $arrDay = array();
      for($ind =1;$ind< 32 ; $ind++){
        $arrDay[$ind]=$ind;
      }
      //set the month array
      $arrMonth = array(
                   '1' =>'01',
                   '2' =>'02',
                   '3' =>'03',
                   '4' =>'04',
                   '5' =>'05',
                   '6' =>'06',
                   '7' =>'07',
                   '8' =>'08',
                   '9' =>'09',
                   '10'=>'10',
                   '11'=>'11',
                   '12'=>'12');
      //set the year array
      $arrYear = array();
      $year = date('Y');
      $cc = 0;
      while($cc < 50){
        $arrYear[$year]=$year;
        $year-=1;
        $cc++;
      }
      $form = $this->createFormBuilder()
              ->add('carnetIdentidad','text', array('label'=>'Carnet Identidad', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Carnet Identidad', 'pattern' => '[A-Za-z0-9\sñÑ]{3,10}', 'maxlength' => '10', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('complemento','text', array('label'=>'Complemento', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Complemento', 'pattern' => '[A-Za-z0-9\sñÑ]{3,10}', 'maxlength' => '2', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('ueprocedencia','text', array('label'=>'Unidad Educativa de Procedencia - SIE', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Unidad Educativa de Procedencia', 'pattern' => '[A-Za-z0-9\sñÑ]{3,10}', 'maxlength' => '10', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('paterno','text', array('label'=>'Paterno', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Paterno', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('materno','text', array('label'=>'Materno', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Materno', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('nombre','text', array('label'=>'Nombre', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Nombre', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              //->add('fechaNacimiento','text', array('label'=>'Fecha Nacimiento', 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => 'Fecha Nacimiento', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('day','choice', array('label'=>'Dia','choices'=>$arrDay, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('month','choice', array('label'=>'Dia','choices'=>$arrMonth, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('year','choice', array('label'=>'Dia','choices'=>$arrYear, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('idCurso', 'hidden', array('data'=> $idCurso))
              ->add('generoTipo', 'entity', array('label'=>'Género', 'attr'=>array('class'=>'form-control'),'mapped'=>false,
                'class'=>'SieAppWebBundle:GeneroTipo'
              ))

              ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                  'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                  'query_builder' => function (EntityRepository $e) {
              return $e->createQueryBuilder('pt')
                      ->orderBy('pt.id', 'ASC');
              }, 'property' => 'pais'
              ))

              ->add('departamento', 'choice', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),))
              ->add('provincia', 'choice', array('label' => 'Provincia', 'attr' => array('class' => 'form-control'),'mapped' => false ))
              ->add('localidad', 'text', array('data' => '', 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'placeholder'=>'LOCALIDAD')))



              ->add('find', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-blue', 'onclick'=>'findStudentNoAcreditado()')))
              ->getForm();
      return $form;
    }
    /**
    * to do the find student
    **/
    public function findStudentsAction(Request $request){

      //crete the connexion into the DB
      //get the info send
      $em = $this->getDoctrine()->getManager();
      $form =  $request->get('form');

      $dataUe = json_decode($form['data'],true);
      //set values to show the result
      $exist = true;

      //get the student and historial info by data info
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getDataStudentByCiOrdataStudentNoAcredit(
                                                                                                      $form['carnetIdentidad'],
                                                                                                      mb_strtoupper($form['nombre'], 'UTF-8') ,
                                                                                                      mb_strtoupper($form['paterno'], 'UTF-8'),
                                                                                                      mb_strtoupper($form['materno'], 'UTF-8'),
                                                                                                      $form['year'].'-'.$form['month'].'-'.$form['day'],
                                                                                                      $this->session->get('currentyear'), 
                                                                                                      $form['complemento']
                                                                                                    );
      //check if it has data
      $student = array();
      if($objStudent){
        //get the student datas
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($objStudent[0]['id']);
      } else{
        $exist = false;
        $message = 'Estudiante no registrado... Realize la inscripción por el boton';
        $this->get('session')->getFlashBag()->add('noresult', $message);
      }

      //dump($dataUe);
      //die;
        return $this->render($this->session->get('pathSystem').':GestionUesprocesoApertura:findstudents.html.twig', array(
            'objStudent'=>$objStudent,
            'student'=>$student,
            'form'=>$this->doInscriptionForm($form['data'], json_encode($form))->createView(),
            'dataUe'=>$form['data'],
            'exist'=>$exist
          ));

    }
    /**
    * form todo the inscription
    **/
    private function doInscriptionForm($data, $allData){
      $form = $this->createFormBuilder()
              ->add('data', 'hidden', array('data'=> $data))
              ->add('fullData', 'hidden', array('data'=>$allData))
              ->add('inscription', 'button', array('label'=> 'Inscribir Nuevo', 'attr'=>array('class'=>'btn btn-blue btn-xs','ata-placement'=>'top', 'onclick'=>'doInscription()')))
              ->getForm();
     return $form;
    }

    /**
    * methdo to save the new inscription and create the rude
    **/
    public function saveInscriptionNoAcreditAction(Request $request){

      //create the conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form= $request->get('form');
      $aInfoUeducativa = json_decode($form['data'],true);
      //convert values send
      $newStudent = json_decode($form['fullData'], true);
      // dump($newStudent);die;
      //dump($aInfoUeducativa);die;
      $arrInfoUe = $aInfoUeducativa;

      try {
        //crete a rudeal to the student
        $digits = 4;
        $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
        $rude = $aInfoUeducativa['sie'] . $this->session->get('currentyear') . $mat . $this->generarRude($aInfoUeducativa['sie'] . $this->session->get('ie_gestion') . $mat);


        //save the new student data

        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
        $query->execute();

        $student = new Estudiante();
        $student->setPaterno(mb_strtoupper($newStudent['paterno'], 'UTF-8'));
        $student->setMaterno(mb_strtoupper($newStudent['materno'], 'UTF-8'));
        $student->setNombre(mb_strtoupper($newStudent['nombre'], 'UTF-8'));
        $student->setCarnetIdentidad($newStudent['carnetIdentidad']);
        $student->setComplemento($newStudent['complemento']);

        $dategrepup = $newStudent['day'].'/'.$newStudent['month'].'/'.$newStudent['year'];
        $newTime = strtotime($dategrepup);
        $newFormatDay = date('Y-m-d', $newTime);

        $student->setFechaNacimiento(new \DateTime($newFormatDay));
        //$student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
        $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
        $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
        if($newStudent['pais']==1){
          $student->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['departamento']));
          $student->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['provincia']));
          $student->setLocalidadNac(mb_strtoupper($newStudent['localidad'], 'UTF-8'));        
        }
        
        $student->setCodigoRude($rude);
        $em->persist($student);
        $em->flush();

        $studentId = $student->getId();

        //to register the new rude and the user
        $UsuarioGeneracionRude = new UsuarioGeneracionRude();
        $UsuarioGeneracionRude->setUsuarioId($this->session->get('userId'));
        $UsuarioGeneracionRude->setFechaRegistro(new \DateTime('now'));
        $aDatosCreacion = array('sie' => $this->session->get('ie_id'), 'rude' => $rude);
        $UsuarioGeneracionRude->setDatosCreacion(serialize($aDatosCreacion));
        $em->persist($UsuarioGeneracionRude);
        $em->flush();

        //save the inscription
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
        $query->execute();
        $studentInscription = new EstudianteInscripcion();
        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($aInfoUeducativa['sie']));
        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
        // $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
        $studentInscription->setObservacion(1);
        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($newStudent['idCurso']));
        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
        $studentInscription->setCodUeProcedenciaId($newStudent['ueprocedencia']);
        $em->persist($studentInscription);
        $em->flush();
        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');
        die('krlos was here');

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }

    /**
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
    private function generarRude($cadena) {
        $codigoRude = "";
        $codigoVerificacion = "123456789A0";
        $peso = 2;
        $sum = 0;
        $int = 0;
        while ($int < strlen($cadena)) {
            if ($peso == 7)
                $peso = 2;
            $sum = $sum + ($peso * ord(substr($cadena, $int, 1)));
            $peso = $peso + 1;
            $int = $int + 1;
        }
        return substr($codigoVerificacion, 10 - ($sum % 11), 1);
    }

    /***
    *list of student per course
    */

    public function listStudentsAction(Request $request){
      //create the DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the send values
      $infoUe = $request->get('infoUe');
      $aInfoUeducativa = unserialize($infoUe);
      //crate the sw variable to do the action
      $exist = true;
      $objStudents = array();
      $dataUe=(unserialize($infoUe));
      //find the students on the id curso
      $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseNoAcreditados($request->get('idCurso'));
      if (!$objStudents)
          $exist = false;

      return $this->render($this->session->get('pathSystem') . ':GestionUesprocesoApertura:seeStudents.html.twig', array(
                  'objStudents' => $objStudents,
                  'exist' => $exist,
                  'idCurso' => $request->get('idCurso'),
                  'dataInfo'=>$request->get('dataInfo'),
                  'totalInscritos'=>count($objStudents)
      ));
    }

    /***
    *remove the student selected
    */

    public function removeInscriptionNoAcreditAction(Request $request){

      //create the DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $idStudent = $request->get('idStudent');

      try {
        //first remove the inscription
        $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
          'estudiante'=>$idStudent
        ));

        if($objInscription){
          //remove the inscription
          $em->remove($objInscription);
          $em->flush();
        }
        //second remove the student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
        if($objInscription){
          //remove the inscription
          $em->remove($objStudent);
          $em->flush();
        }
        //send confirm message
        $message = 'Estudiante eliminado...';
        $this->get('session')->getFlashBag()->add('removeNoAcredit', $message);
        //do the commit in DB
        $em->getConnection()->commit();
        //crate the sw variable to do the action
        $exist = true;
        //find the students on the id curso
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseNoAcreditados($request->get('idCurso'));
        if (!$objStudents)
            $exist = false;

        return $this->render($this->session->get('pathSystem') . ':GestionUesprocesoApertura:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'exist' => $exist,
                    'idCurso' => $request->get('idCurso'),
                    'dataInfo'=>$request->get('dataInfo'),
                    'totalInscritos'=>count($objStudents)
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }

    /**
    * print DDJJ U.E en proceso de regularizacipon
    **/
    public function printDdjjAction(Request $request){

      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      //crete the db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send values
      $form = $request->get('dataInfo');
      $dataInfo = $request->get('dataInfo');
      $arrDataInfo = json_decode($dataInfo, true);

      //get all cursos to the UE
      $objCursos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
        'institucioneducativa'=> $arrDataInfo['sie'],
        'gestionTipo'=> $this->session->get('currentyear')
      ));
      //varable sw to do the print DDJJ
      $sw = false;
      //check if it has courses
      if($objCursos){
        $arrIecId = array();
        foreach ($objCursos as $key => $value) {
          # code...set the iecId array
          $arrIecId[]=$value->getId();
        }
        //get all students
        $queryStudentsOnUnidadEducativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $queryStudentsOnUnidadEducativa->createQueryBuilder('ei')
                ->select('ei')
                ->where('ei.institucioneducativaCurso IN (:idcursos)')
                ->setParameter('idcursos', $arrIecId)
                ->getQuery();
        $objStudentsOnUnidadEducativa = $query->getResult();
        if($objStudentsOnUnidadEducativa){
          $sw=true;
        }
      }
      if($sw){
        try {
          $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_noacreditados_ddjj');");
          $query->execute();
          //save information on DB DDJJ
          $institucioneducativaNoacreditadosDdjjNew = new InstitucioneducativaNoacreditadosDdjj();
          $institucioneducativaNoacreditadosDdjjNew->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrDataInfo['sie']));
          $institucioneducativaNoacreditadosDdjjNew->setGestionTipoId($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
          $institucioneducativaNoacreditadosDdjjNew->setFecha(new \DateTime(date('Y-m-d')));
          $institucioneducativaNoacreditadosDdjjNew->setEsabierta('t');
          $em->persist($institucioneducativaNoacreditadosDdjjNew);
          $em->flush();
          $em->getConnection()->commit();
          //to do the print DDJJ
          $this->session->getFlashBag()->add('print_ddjj', 'Declaración Jurada impresa, ya no puede realizar cambios');
        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      }else{
        //no print
        $this->session->getFlashBag()->add('print_ddjj', 'No es posible generar la Declaración Jurada por que no tiene estudiantes inscritos...');
      }

      //get the courses for UE
      $objInstitucioneducativaNoacreditadosDdjj = $em->getRepository('SieAppWebBundle:InstitucioneducativaNoacreditadosDdjj')->findOneBy(array(
        'institucioneducativa' => $arrDataInfo['sie'],
        'gestionTipoId'          => $this->session->get('currentyear')
      ));

      $cursos = $this->getAllCursos($arrDataInfo);

      return $this->render('SieRegularBundle:GestionUesprocesoApertura:maincurso.html.twig', array(
                            'cursos'   => $cursos,
                            'dataInfo' => $dataInfo,
                            'swShowlinkDdjj' => ($objInstitucioneducativaNoacreditadosDdjj)?true:false
      ));
    }

}
