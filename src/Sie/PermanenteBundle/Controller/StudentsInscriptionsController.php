<?php

namespace Sie\PermanenteBundle\Controller;

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
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcional;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * StudentsInscriptions controller.
 *
 */
class StudentsInscriptionsController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request){
      
        //get the send values
        $infoUe = '';// $request->get('infoUe');
        // dump($infoUe);
        // dump($request);
        // die;
        $arrInfoUe = array();//unserialize($infoUe);
        $infoUe = json_encode($arrInfoUe);
        
        return $this->render('SiePermanenteBundle:StudentsInscriptions:newlookforstudent.html.twig', array(
          'infoUe'=>$infoUe,
          'iecId' =>$request->get('infoUe')
          // 'form'=>$this->findStudentForm($infoUe)->createView()
        ));
    }
     public function indexCLAction(Request $request){
        //get the send values
       // dump($request);die;
        $infoUe = $request->get('infoUe');
        $arrInfoUe = unserialize($infoUe);

        return $this->render('SiePermanenteBundle:CursosLargos:newlookforstudent.html.twig', array(
          'infoUe'=>$infoUe,
          'iecId' => $arrInfoUe['ueducativaInfo']['ueducativaInfoId']['iecid']
            // 'form'=>$this->findStudentForm($infoUe)->createView()
        ));
    }
    /**
    * create the form to find the student by rude
    **/
    private function findStudentForm($data){
      $form = $this->createFormBuilder()
              ->add('rudeal','text', array('label'=>'Rudeal', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Rudeal', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('find', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'findStudent()')))
              ->getForm();
      return $form;
    }
    /**
    *find student method
    **/
    public function findStudentAction(Request $request){
      //crete the connexion into the DB
      //get the info send
      $em = $this->getDoctrine()->getManager();
      $form =  $request->get('form');
      $dataUe = unserialize($form['data']);
    // dump($dataUe);die;
      //get the student info by rudeal
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rudeal']));
      //check if the student exist
      if($objStudent){
        //check if the student has an inscription on this year sesion->get('ie_gestion');
        $selectedInscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionStudentByYear($objStudent->getId(), $this->session->get('ie_gestion'),$dataUe['ueducativaInfo']['ueducativaInfoId']['iecid']);
        if(!$selectedInscriptionStudent){
          //check if the level and grado is correct to the student//the next step is do it
            $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());

            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'objStudent'=>$objStudent,
              'objStudentInscriptions'=>$objStudentInscriptions,
              'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
              'exist'=>true

            ));

        }else{
            //the student has an inscription on the same level
            $this->session->getFlashBag()->add('noinscription', 'Estudiante ya cuenta con inscripcion en el mismo nivel');
            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'exist'=>false
            ));
        }
      }else{
        //the student doesn't exist
        $this->session->getFlashBag()->add('noinscription', 'Estudiante no registrado');
        return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
          'exist'=>false
        ));
      }

    }//end function
    /**
    * form todo the inscription
    **/
    private function doInscriptionForm($data, $studentId){
      $form = $this->createFormBuilder()
             // ->add('caseespecial', 'checkbox', array('label'=>'Inscripción Menores:', 'attr'=>array('class'=>'form-control', 'checked'=>false, 'onclick'=>'showHideReq(this)' ) ))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('studentId', 'hidden', array('data'=> $studentId))
              ->add('inscriptionExcepacional', 'entity', array('label'=>'Motivo:','class' => 'SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo', 'property' => 'descripcion'))
              ->add('documento', 'text', array('label'=>'Info Complementaria:', 'attr'=>array('class'=>'form-control' ) ))
              ->add('inscription', 'button', array('label'=> 'Inscribir', 'attr'=>array('class'=>'btn btn-success btn-stroke','ata-placement'=>'top', 'onclick'=>'doInscription()')))
              ->getForm();
     return $form;
    }
    /**
    * methdo to save the new inscription
    **/
    public function saveInscriptionAction(Request $request){

      //create the conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form= $request->get('form');
      $aInfoUeducativa = unserialize($form['data']);


    //set the validate year
    $validateYear = false;
    //if not checked, so validate the studens olds year
    if(!isset($form['caseespecial'])){
      //get the curso info
      $objInstitucioneducativaCursoStudent=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']);

      //get the students year old

      $yearStudent = (date('Y') - date('Y',strtotime($form['dateStudent'])));

      //validate the humanisticos
      if($objInstitucioneducativaCursoStudent->getNivelTipo()->getId()==15){
        //validate to nivel=15 - ciclo=1 - grado=1
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
          if(!($yearStudent>=15)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=1 - grado=2
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
          if(!($yearStudent>=16)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=1
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
          if(!($yearStudent>=17)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=2
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=3
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==3){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }
      }//end first if - validate the humanisticos

    }
    //if the year is wrong go to show the alert
    if($validateYear){
      //reload the students list
      $exist = true;
      $objStudents = array();
      $this->session->getFlashBag()->add('noinscription', 'Estudiante no inscrito no cumple la edad...');
      $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecId']);
      $dataUe=(unserialize($form['data']));

      return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                  'objStudents' => $objStudents,
                  'form' => $this->createFormStudentInscription($form['data'])->createView(),
                  'exist' => $exist,
                  'infoUe' => $form['data'],
                  'dataUe'=> $dataUe['ueducativaInfo']
      ));
    }

      try {
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
        $query->execute();
        $studentInscription = new EstudianteInscripcion();
        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['studentId']));
        $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
        $studentInscription->setObservacion(1);
        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']));
        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
        $studentInscription->setCodUeProcedenciaId(0);
        $em->persist($studentInscription);
        $em->flush();

        //save the inscription data when the inscription is excepcional
        if(isset($form['caseespecial'])){
          $estudianteInscripcionAlternativaExcepcionalObjNew = new EstudianteInscripcionAlternativaExcepcional();
          $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcionAlternativaExcepcionalTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->find($form['inscriptionExcepacional']));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setFecha(new \DateTime('now'));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setDocumento($form['documento']);
          $em->persist($estudianteInscripcionAlternativaExcepcionalObjNew);
          $em->flush();
        }

        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

        //reload the students list
        $exist = true;
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $objStudents = array();
//
//        if ($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['nivelId'] != '15'){
//            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ueducativaInfo']['grado'];
//        }
//        else{
//            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ueducativaInfo']['grado'];
//        }

       // $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']);
          $query = $em->getConnection()->prepare('
               select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id

                
                where c.id =:idcurso 

               
        ');
          $query->bindValue(':idcurso', $idcurso);
          $query->execute();
          $objStudents= $query->fetchAll();
          $querya = $em->getConnection()->prepare('
                 select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
															left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.id=:idcurso
                
        ');
          $querya->bindValue(':nivel', 230);
          $querya->bindValue(':idcurso', $idcurso);
          $querya->execute();
          $cursoCorto= $querya->fetchAll();

          $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
          $estadomatriculaArray = array();
          foreach($estadomatricula as $value){
              if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==75))
              {
                  if($value->getId()==75)
                  {
                      $estadomatriculaArray[$value->getId()] ='CONCLUIDO';;
                  }
                  else
                  {
                      $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                  }
              }

          }


        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        $dataUe=(unserialize($form['data']));
        return $this->render('SiePermanenteBundle:InfoEstudianteRequest:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($form['data'])->createView(),
                    'exist' => $exist,
                   'objx' => $estadomatriculaArray,
                    'existins' => $existins,
                    'infoUe' => $form['data'],
                    'cursocorto'=>$cursoCorto,
//                  'etapaespecialidad' => $etapaespecialidad,
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'totalInscritos'=>count($objStudents)
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      }

    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'text', array('data' => $data))
                        ->getForm();
    }

    public function inscriptionbyCiAction(Request $request){
      // ini var to the function
      $em = $this->getDoctrine()->getManager();
      $response = new JsonResponse();
      // get the send values
      $ci = $request->get('ci');
      $complemento = $request->get('complemento');
      
      // here is to find the studnen by CI  on table estudiante/persona
      // first look for student on ESTUDIANTE table 
      // set array conditions
      $arrayCondition['carnetIdentidad'] = $ci;
      if($complemento){
        $arrayCondition['complemento'] = $complemento;
      }    
      // find the student by arrayCondition
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy($arrayCondition);
      // set result in the process to find
      $dataInscription = array();
      $dataInscriptionR = array();
      $dataInscriptionA = array();
      $dataInscriptionE = array();
      $dataInscriptionP = [];
      $arrGenero = array();
      $arrExpedido = array();
      $arrPais = array();
      $arrStudent = array();
      $arrNewStudent = array();
      $arrStudent = array();
      $arrDepto = array();
      $arrProvincia = array();

      $swstudent        = false;
      $swperson         = false;
      $swnewperson      = false;
      // check if the student exist
      if($objStudent){
        $swstudent = true;
        //get the student data 
        $arrStudent = array(
          'paterno'     =>$objStudent->getPaterno(),
          'materno'     =>$objStudent->getMaterno(),
          'nombres'     =>$objStudent->getNombre(),
          'fecNac'      =>$objStudent->getFechaNacimiento()->format('d-m-Y'),
          'carnet'      =>$objStudent->getCarnetIdentidad(),
          'genero'      =>$objStudent->getGeneroTipo()->getGenero(),
          'generoId'    =>$objStudent->getGeneroTipo()->getId(),
          'complemento' =>$objStudent->getComplemento(),
          'rude'        =>$objStudent->getCodigoRude(),
          'expedido'    =>$objStudent->getExpedido()->getSigla(),
          'expedidoId'  =>$objStudent->getExpedido()->getId(),
          'expedidoId2'  =>$objStudent->getExpedido()->getId(),
          'studentId'  =>$objStudent->getId(),
        );
        // get all cardex info
        // $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $objStudent->getCodigoRude() . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        // $query->execute();
        // $dataInscription = $query->fetchAll();
        // foreach ($dataInscription as $key => $inscription) {
        //     switch ($inscription['institucioneducativa_tipo_id_raep']) {
        //         case '1':
        //             $dataInscriptionR[$key] = $inscription;
        //             break;
        //         case '2':
        //             $dataInscriptionA[$key] = $inscription;
        //             break;
        //         case '4':
        //             $dataInscriptionE[$key] = $inscription;
        //             break;
        //         case '5':
        //         if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 14)$bloquep ='Segundo';
        //         if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 15)$bloquep = 'Tercero';
        //         if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 16)$bloquep = 'Quinto';
        //         if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 17)$bloquep = 'Sexto';
        //             $dataInscriptionP[] = array(
        //               'gestion'=> $inscription['gestion_tipo_id_raep'],
        //               'institucioneducativa'=> $inscription['institucioneducativa_raep'],
        //               'partp'=> ($inscription['parte_p']==1 ||$inscription['parte_p']==2)?'Antiguo':'Actual',
        //               'bloquep'=> $bloquep,
        //               'fini'=> $inscription['fech_ini_p'],
        //               'ffin'=> $inscription['fech_fin_p'],
        //               'curso'=> $inscription['institucioneducativa_curso_id_raep'],
        //               'matricula'=> $inscription['estadomatricula_p'],
        //             );
        //             break;
        //     }
        // }
      }else{
        // look into the PERSON table
        // set arrayCondition2
        $arrayCondition2['carnet'] = $ci;
        if($complemento){
          $arrayCondition2['complemento'] = $complemento;
        }
        $objStudent = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrayCondition2);
        // dump($objStudent);die;
        // check if the person exist on the person table
        if($objStudent){
          // the person exist
          $swperson = true;
          $arrStudent = array(
            'paterno'     =>$objStudent->getPaterno(),
            'materno'     =>$objStudent->getMaterno(),
            'nombres'     =>$objStudent->getNombre(),
            'fecNac'      =>$objStudent->getFechaNacimiento()->format('d-m-Y'),
            'carnet'      =>$objStudent->getCarnet(),
            'complemento' =>$objStudent->getComplemento(),
            'expedido'    =>$objStudent->getExpedido()->getSigla(),
            'expedidoId'  =>$objStudent->getExpedido()->getId(),
            'expedidoId2'  =>$objStudent->getExpedido()->getId(),
            'genero'      =>$objStudent->getGeneroTipo()->getGenero(),
            'generoId'    =>$objStudent->getGeneroTipo()->getId(),


          );
          // dump($arrStudent);die;
        }
      }
      // this is to the new person
      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
      foreach ($objExpedido as $value) {
        $arrExpedido[] = array('expedidoId' => $value->getId(),'expedido' => $value->getSigla());
      }
      // get genero data
      $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
      foreach ($objGenero as $value) {
          if($value->getId()<3){
              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());                
          }
      }
      //get pais data
      $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
      foreach ($objPais as $value) {
        $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
      }

      if(!$swperson  && !$swstudent){
        //set struct data person
        $arrNewStudent = array(
          'paisId'=>'',
          'lugarNacTipoId'=>'',
          'lugarProvNacTipoId'=>'',
          'localidad'=>'',
        );
        $swnewperson = true;
      }

      $arrResponse = array(
            'status'           => 200,
            'dataStudent'      => $arrStudent,
            'dataInscriptionR' => $dataInscriptionR,
            'dataInscriptionA' => $dataInscriptionA,
            'dataInscriptionE' => $dataInscriptionE,
            'dataInscriptionP' => $dataInscriptionP,
            'swstudent'        => $swstudent,
            'swperson'         => $swperson,
            'swnewperson'      => $swnewperson,
            'arrGenero'        => $arrGenero,
            'arrExpedido'      => $arrExpedido,
            'arrPais'          => $arrPais,
            'arrNewStudent'    => $arrNewStudent,
      );
      // dump($arrResponse);die;
      $response->setStatusCode(200);
      $response->setData($arrResponse);
       
      return $response;    
      
    }


    public function getDeptoAction(Request $request){
        $response = new JsonResponse();
        // get the send values
        $paisId = $request->get('paisId');
        // create db conexino
        $em = $this->getDoctrine()->getManager();
        // get departamento
        if ($paisId == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $paisId);
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }
        $objDepto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $arrDepto = array();
        foreach ($objDepto as $depto) {
            $arrDepto[]=array('deptoId'=>$depto->getId(),'depto'=>$depto->getLugar());
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'arrDepto' => $arrDepto,
        ));
       
        return $response;        


    }

    public function getProvinciaAction(Request $request){
    
      $em = $this->getDoctrine()->getManager();
      $response = new JsonResponse();
      $lugarNacTipoId = $request->get('lugarNacTipoId');

      // / get provincias
      $objProv = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $lugarNacTipoId));

      $arrProvincia = array();
      foreach ($objProv as $prov) {
          $arrProvincia[] = array('provinciaId'=>$prov->getid(), 'provincia'=>$prov->getlugar());
      }

        $response->setStatusCode(200);
      $response->setData(array(
          'arrProvincia' => $arrProvincia,
      ));
     
      return $response;

    }
    // to do the check and inscription about the student
    public function checkDataStudentAction(Request $request){

      //ini json var
      $response = new JsonResponse();
      // create db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send data
      $paisId = $request->get('paisId');
      $lugarNacTipoId = $request->get('lugarNacTipoId');
      $lugarProvNacTipoId = $request->get('lugarProvNacTipoId');
      $localidad = $request->get('localidad');
      $paterno = $request->get('paterno');
      $materno = $request->get('materno');
      $nombre = $request->get('nombres');
      $fecNac = $request->get('fecNac');
      $generoId = $request->get('generoId');
      $carnet = $request->get('carnet');
      $complemento = $request->get('complementoval');
      $iecId = $request->get('iecId');
      $expedidoId = $request->get('expedidoId');
      // set data to validate with segip function
      $arrParametros = array(
        'complemento'=>$complemento,
        'primer_apellido'=>$paterno,
        'segundo_apellido'=>$materno,
        'nombre'=>$nombre,
        'fecha_nacimiento'=>$fecNac
      );
      // get info segip
      $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
      // check if the data person is true
      if($answerSegip){
        // now get the Centro info 
        try {
         
          // create fec nac var 
          $newFecNac = str_replace('/', '-', $fecNac);
          $fecNac =  date('Y-m-d', strtotime($newFecNac));
          // get the students years old
          $yearStudent = (date('Y') - date('Y',strtotime($fecNac)));

          // check if the students has the required
          if($yearStudent>15){
            // create rude code to the student
            
            $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
            $query->bindValue(':sie', $this->session->get('ie_id'));            
            $query->bindValue(':gestion', $this->session->get('currentyear'));
            $query->execute();
            $codigorude = $query->fetchAll();
            $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
            
            // set the data person to the student table
            $estudiante = new Estudiante();
            // set the new student
            $estudiante->setCodigoRude($codigoRude);
            $estudiante->setCarnetIdentidad($carnet);
            $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
            $estudiante->setPaterno(mb_strtoupper($paterno, 'utf-8'));
            $estudiante->setMaterno(mb_strtoupper($materno, 'utf-8'));
            $estudiante->setNombre(mb_strtoupper($nombre, 'utf-8'));                        
            $estudiante->setFechaNacimiento(new \DateTime($fecNac));            
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoId));
            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));
            // check if the country is Bolivia
            if ($paisId === '1'){                    
                $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId));
                $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId));
                $estudiante->setLocalidadNac($localidad);
            }else{//no Bolivia
                $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                $estudiante->setLocalidadNac('');
            }
            $estudiante->setSegipId(1);
            $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
            $em->persist($estudiante);

            // set the inscription to the new student
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
            $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);

            $em->flush();

            // Try and commit the transaction
            $em->getConnection()->commit();

            $status = 'success';
            $code = 200;
            $message = "Estudiante registrado existosamente!!!";
            $swcreatestudent = true;

          }else{

            $status = 'error';
            $code = 400;
            $message = "Estudiante no cumple con la edad Requerida";
            $swcreatestudent = false;            
          }

        } catch (Exception $e) {
          
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
          
        }
      }else{

        $status = 'error';
        $code = 400;
        $message = "Datos introducidos no cumplen con la validacion SEGIP!!!";
        $swcreatestudent = false;

      }

      //send the response info
       $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swcreatestudent' => $swcreatestudent,
            
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
       
    }

    public function studentsInscriptionAction(Request $request){
      //ini json var
      $response = new JsonResponse();
      // get the send values 
      $iecId = $request->get('iecId');
      $studentId = $request->get('studentId');
      // create db conexion
      $em = $this->getDoctrine()->getManager();

      try {
        // check if the student has an inscription on this course
        $objCurrentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$studentId,'institucioneducativaCurso'=>$iecId));
        

          if(!$objCurrentInscription){
          // do inscription
          // set the inscription to the new student
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
            $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);

            $em->flush();

            $status = 'success';
            $code = 200;
            $message = "Estudiante registrado existosamente!!!";
            $swcreatestudent = true;   


          }else{

            $status = 'error';
            $code = 400;
            $message = "Estudiante ya cuenta con una inscripcion en el curso seleccionado";
            $swcreatestudent = false;   

          }


           $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swcreatestudent' => $swcreatestudent,
            
            );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
        
      } catch (Exception $e) {
        
      }

    }

    public function showListStudentAction(Request $request){
      $infoUe = $request->get('iecId');
      // $aInfoUeducativa = unserialize($infoUe);
      $idcurso = $request->get('iecId');
      // $dataUe=(unserialize($infoUe));
      $em = $this->getDoctrine()->getManager();
      $exist = true;
            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id

                
                where c.id =:idcurso      

               
        ');
            $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $objStudents= $query->fetchAll();
            $querya = $em->getConnection()->prepare('
                 select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
                                left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
                                  left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
                                      left join permanente_programa_tipo e on b.programa_tipo_id=e.id
                                            left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
                                            left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
                                              left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id 
                                                left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
                                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
                                                      left join persona k on j.persona_id =k.id
                                                        left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
                              left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.id=:idcurso
                
        ');
            $querya->bindValue(':nivel', 230);
            $querya->bindValue(':idcurso', $idcurso);
            $querya->execute();

            $cursoCorto= $querya->fetchAll();
            //  dump($cursoCorto);die;

            $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
            $estadomatriculaArray = array();
            foreach($estadomatricula as $value){
                if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==5))
                {
                    $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                }

            }

            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }

           //$data = $this->getAreas($infoUe);
            return $this->render('SiePermanenteBundle:InfoEstudianteRequest:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'exist' => $exist,
                'objx' => $estadomatriculaArray,
                'cursocorto'=>$cursoCorto,
                'existins' => $existins,
                  'infoUe' => $infoUe,
                  'dataUe' => array(),
                'totalInscritos'=>count($objStudents)
            ));
    }


    public function saveInscriptionCLAction(Request $request){
     //   dump($request);die;
        //create the conexion DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form= $request->get('form');
        $aInfoUeducativa = unserialize($form['data']);
        $dataUe= unserialize($form['data']);
        $infoUe = $form['data'];
        //set the validate year
        $validateYear = false;
     
        if(!isset($form['caseespecial'])){
      
            $objInstitucioneducativaCursoStudent=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']);

            //get the students year old

            $yearStudent = (date('Y') - date('Y',strtotime($form['dateStudent'])));

            //validate the humanisticos
            if($objInstitucioneducativaCursoStudent->getNivelTipo()->getId()==15){
                //validate to nivel=15 - ciclo=1 - grado=1
                if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
                    if(!($yearStudent>=15)){
                        $validateYear=true;
                    }
                }

                //validate to nivel=15 - ciclo=1 - grado=2
                if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
                    if(!($yearStudent>=16)){
                        $validateYear=true;
                    }
                }

                //validate to nivel=15 - ciclo=2 - grado=1
                if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
                    if(!($yearStudent>=17)){
                        $validateYear=true;
                    }
                }

                //validate to nivel=15 - ciclo=2 - grado=2
                if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
                    if(!($yearStudent>=18)){
                        $validateYear=true;
                    }
                }

                //validate to nivel=15 - ciclo=2 - grado=3
                if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==3){
                    if(!($yearStudent>=18)){
                        $validateYear=true;
                    }
                }
            }//end first if - validate the humanisticos

        }
        //if the year is wrong go to show the alert
        if($validateYear){
            //reload the students list
            $exist = true;
            $objStudents = array();
            $this->session->getFlashBag()->add('noinscription', 'Estudiante no inscrito no cumple la edad...');
            $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecId']);
            $dataUe=(unserialize($form['data']));

            return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'form' => $this->createFormStudentInscription($form['data'])->createView(),
                'exist' => $exist,
                'infoUe' => $infoUe,
                'dataUe'=> $dataUe['ueducativaInfo']
            ));
        }

        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['studentId']));
            $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();

            //save the inscription data when the inscription is excepcional
            if(isset($form['caseespecial'])){
                $estudianteInscripcionAlternativaExcepcionalObjNew = new EstudianteInscripcionAlternativaExcepcional();
                $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcionAlternativaExcepcionalTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->find($form['inscriptionExcepacional']));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setFecha(new \DateTime('now'));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setDocumento($form['documento']);
                $em->persist($estudianteInscripcionAlternativaExcepcionalObjNew);
                $em->flush();
            }

            //to do the submit data into DB
            //do the commit in DB
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

            //reload the students list
          //  $aInfoUeducativa = unserialize($infoUe);
         //   $dataUe=(unserialize($infoUe));
            //dump ($aInfoUeducativa);die;
            $exist = true;
            $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
            //  dump ($dataUe);die;
            $objStudents = array();

            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 
                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id
                where c.id =:idcurso      
        ');
            $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $objStudents= $query->fetchAll();
            $query = $em->getConnection()->prepare('    
                	
	        select a.id as iecid, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,tt.turno,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, b.lugar_tipo_departamento_id as deptoid,depto.lugar as departamento,  b.lugar_tipo_provincia_id as provid,prov.lugar as provincia,  b.lugar_tipo_municipio_id as munid,mun.lugar as municipio, b.lugar_detalle as comunidad,b.poblacion_detalle,
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as cursolargoid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
												sip.id as superid,
												sia.id as siaid,
												sae.id as saeid,
												sat.acreditacion,
												sespt.especialidad,
												sfat.facultad_area as areaprograma
												
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
																														inner join superior_institucioneducativa_periodo sip on a.superior_institucioneducativa_periodo_id = sip.id
																																inner join lugar_tipo depto on depto.id= b.lugar_tipo_departamento_id  
																																	inner join lugar_tipo prov on prov.id = b.lugar_tipo_provincia_id
																																		inner join lugar_tipo mun on mun.id = b.lugar_tipo_municipio_id
																																inner join turno_tipo tt on tt.id= a.turno_tipo_id
																																		inner join superior_periodo_tipo spt on spt.id  = sip.superior_periodo_tipo_id
																																			inner join superior_institucioneducativa_acreditacion sia on sia.id = sip.superior_institucioneducativa_acreditacion_id
																																				inner join institucioneducativa ie on ie.id =sia.institucioneducativa_id
																																					inner join superior_acreditacion_especialidad sae on sae.id = sia.acreditacion_especialidad_id
																																						inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
																																							inner join superior_especialidad_tipo sespt on sespt.id = sae.superior_especialidad_tipo_id
																																								inner join superior_facultad_area_tipo sfat on sfat.id = sespt.superior_facultad_area_tipo_id
                    where  a.nivel_tipo_id= 231 and a.id=:curso
	        ');
            $query->bindValue(':curso', $idcurso);
            $query->execute();
            $cursosLargos= $query->fetchAll();
            //  dump($cursosLargos);die;

            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }
            $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
            $estadomatriculaArray = array();

            $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		  (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            		 --per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
																		
											where iec.id=:idcurso
											order by iecom.id desc
        ');
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
            $form = $this->createFormBuilder()
                ->add('matricula', 'choice', array('required' => false, 'choices' => $estadomatriculaArray,  'attr' => array('class' => 'form-control')))
                ->getForm();


            return $this->render('SiePermanenteBundle:CursosLargos:seeInscritos.html.twig', array(
                'objStudents' => $objStudents,
                'objx' => $estadomatriculaArray,
                //'form' => $this->createFormStudentInscription($form['data'])->createView(),
                'form' => $form->createView(),
                'lstmod'=>$listamodcurso,
                'exist' => $exist,
                'cursolargo'=>$cursosLargos,
                'existins' => $existins,
                'infoUe' => $infoUe,
                'dataUe' => $dataUe,
                'totalInscritos'=>count($objStudents)

            ));
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }


}
