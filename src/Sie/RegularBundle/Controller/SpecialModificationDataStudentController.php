<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 


class SpecialModificationDataStudentController extends Controller{

    public $session;
    public $currentyear;
    public $userlogged;
     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }

    public function indexAction(){

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieRegularBundle:SpecialModificationDataStudent:index.html.twig', array(
                'form' => $this->craeteformsearch()->createView(),
            ));    
    }

    private function craeteformsearch() {
        $arrOptions = array('Concluyó el Bachillerato','Tiene registro en gestiones pasadas');
        $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('specialmodificationdata_student_lookfor_student'))
                ->add('codeRude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase','placeholder'=>'RUDE')))
                ->add('arrOption','choice',
                      array('label' => 'Opción',
                            'choices' => ($arrOptions),
                            'required' => true,
                            'empty_value' => 'Seleccionar Opción',
                            'attr' => array('class' => 'form-control col-lg-6 col-md-6 col-sm-6')))
                ->add('find', 'button', array('label' => 'Buscar','attr'=>array('class'=>'btn btn-primary','onclick'=>'lookforStudent()')))
                ->getForm();
        return $form;
    }

    public function lookforStudentAction(Request $request){
        //get the send data
        $response = new JsonResponse();
        $form['codeRude'] = $request->get('codigoRude');
        $form['arrOption'] = $request->get('arraOptionBuscar');

// dump($request);
// dump($form);
// die;
        // $form = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>trim($form['codeRude'])));
        
        if($objStudent){
            // do the validation
            switch($form['arrOption']){
                case '':
                // no values selected - option
                    $sw = false;
                    $message = 'Debe seleccionar un valor en el combo';
                    $typeMessage = 'warning';
                    $compleMessage = 'Alerta';
                break;
                case 0:
                // case general
                // check if the student is bachiller
                $objBachillerStudent = $this->getInscription($form,$form['arrOption']);
                if(!$objBachillerStudent){
                    $sw = false;
                    $message = 'El estudiante no es bachiller';
                    $typeMessage = 'warning';
                    $compleMessage = 'Alerta';

                    $response->setStatusCode(200);
                    $response->setData(array(
                        'status'=>'error',
                        'message'=>$message,
                        'swerror'=>$sw,
                    ));
                    return $response;  


                }else{
                    $message = 'error general';
                    $typeMessage = 'warning';
                    $compleMessage = 'good';
                    $sw = true;
                }
                break;
                case 1:
                // case homologation
                $objBachillerStudent = $this->getInscription($form,$form['arrOption']);
                if($objBachillerStudent[0]['gestion'] == $this->session->get('currentyear')){
                    $sw = false;
                    $message = 'El estudiante cuenta con inscripcion en la presente gestion';
                    $typeMessage = 'warning';
                    $compleMessage = 'Alerta';
                    $response->setStatusCode(200);
                    $response->setData(array(
                        'status'=>'error',
                        'message'=>$message,
                        'swerror'=>$sw,
                    ));
                    return $response;  

                }else{
                    $message = 'correct the case is by homologation';
                    $typeMessage = 'warning';
                    $compleMessage = 'good';
                    $sw = true;
                }
                break;
            }
        }else{
            $message = 'Estudiante no existe';
            $typeMessage = 'warning';
            $compleMessage = 'Error';
            $sw = true;

        }
        
        $this->addFlash('messageModStudent', $message);
        // get Genero to the student
        $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        $arrGenero = array();
        foreach ($objGenero as $value) {
            if($value->getId()<3){
                $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());                
            }
        }
        //get pais
        $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        $arrPais = array();
        foreach ($objPais as $value) {
            $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
        }
        // get departamento
        if ($objStudent->getPaisTipo()->getId() == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $objStudent->getPaisTipo()->getId());
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }
        $objDepto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $arrDepto = array();
        foreach ($objDepto as $depto) {
            $arrDepto[]=array('deptoId'=>$depto->getId(),'depto'=>$depto->getLugar());
        }

        // get provincias
        $objProv = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $objStudent->getLugarNacTipo()->getId()));

        $arrProvincia = array();
        foreach ($objProv as $prov) {
            $arrProvincia[] = array('provinciaId'=>$prov->getid(), 'provincia'=>$prov->getlugar());
        }
                
         $arrStudent =( array(
                    'estudianteId'=>$objStudent->getId(),
                    'carnetIdentidad'=>$objStudent->getCarnetIdentidad(),
                    'complemento'=>$objStudent->getComplemento(),
                    'genero'=>$objStudent->getGeneroTipo()->getGenero(),
                    'generoId'=>$objStudent->getGeneroTipo()->getId(),
                    'paterno'=>$objStudent->getPaterno(),
                    'materno'=>$objStudent->getMaterno(),
                    'nombre'=>$objStudent->getNombre(),
                    'fechaNacimiento'=>$objStudent->getFechaNacimiento()->format('d-m-Y'),
                    'pais'=>$objStudent->getPaisTipo()->getPais(),
                    'paisId'=>$objStudent->getPaisTipo()->getId(),
                    'lugarNacTipo'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getLugar(),
                    'lugarNacTipoId'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getId(),
                    'lugarProvNacTipo'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getLugar(),
                    'lugarProvNacTipoId'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getId(),

                    'generoTipo'=>($objStudent->getGeneroTipo()==NULL)?'':$objStudent->getGeneroTipo()->getGenero(),
                    'generoTipoId'=>($objStudent->getGeneroTipo()==NULL)?'':$objStudent->getGeneroTipo()->getId(),

                    'localidad'=>$objStudent->getLocalidadNac(),
                    'oficialia'=>$objStudent->getOficialia(),
                    'libro'=>$objStudent->getLibro(),
                    'partida'=>$objStudent->getPartida(),
                    'folio'=>$objStudent->getFolio(),

                    'resolucionAdm'=>'',
                    'fecharesolAdm'=>'',
                    'justificativo'=>'',
                    'archivoadjunto'=>'',


                ));
         $arrStudentModif = $arrStudent;
        // dump($objStudent);
        // dump($form);
        // die;
        $response->setStatusCode(200);
        $response->setData(array(
            'codigoRude'=>$objStudent->getCodigoRude(),
            'studentId'=>$objStudent->getId(),
            'student'=> $arrStudent,
            'studentModif'=> $arrStudentModif,
            'arrGenero' => $arrGenero,
            'arrPais' => $arrPais,
            'arrDepto' => $arrDepto,
            'arrProvincia' => $arrProvincia,
            'status'=>'error',
            'message'=>$message,
            'swerror'=>$sw,
        ));
       
        return $response;        

        // return $this->render('SieRegularBundle:SpecialModificationDataStudent:lookforStudent.html.twig', array(
        //         'compleMessage' => $compleMessage,
        //         'typeMessage'   => $typeMessage,
        //         'form'          => $this->studentForm($objStudent)->createView(),
        //         'sw'            => $sw,
        // ));
    }

    public function updateStudentAction(Request $request){

        $response = new JsonResponse();
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        //get send values
        $estudianteId = $request->get('estudianteId');
        $carnetIdentidad=$request->get('carnetIdentidad');
        $complemento=$request->get('complemento');
        $generoId=$request->get('generoId');
        $paterno=$request->get('paterno');
        $materno=$request->get('materno');
        $nombre=$request->get('nombre');
        $fechaNacimiento=$request->get('fechaNacimiento');
        $paisId=$request->get('paisId');
        $lugarNacTipoId=$request->get('lugarNacTipoId');
        $lugarProvNacTipoId=$request->get('lugarProvNacTipoId');
        $localidad=$request->get('localidad');
        $oficialia=$request->get('oficialia');
        $libro=$request->get('libro');
        $partida=$request->get('partida');
        $folio=$request->get('folio');

        $resolucionAdm = $request->get('resolucionAdm');
        $fecharesolAdm = $request->get('fecharesolAdm');
        $justificativo = $request->get('justificativo');
        
        //get the info data
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteId);
        // update student data
            
                //GET OLD DATA
                $oldDataStudent2 =json_encode( array(
                    'carnetIdentidad'=>$objStudent->getCarnetIdentidad(),
                    'complemento'=>$objStudent->getComplemento(),
                    'genero'=>$objStudent->getGeneroTipo()->getGenero(),
                    'paterno'=>$objStudent->getPaterno(),
                    'materno'=>$objStudent->getMaterno(),
                    'nombre'=>$objStudent->getNombre(),
                    'fechaNacimiento'=>$objStudent->getFechaNacimiento(),
                    'pais'=>$objStudent->getPaisTipo()->getPais(),
                    'paisId'=>$objStudent->getPaisTipo()->getId(),
                    'lugarNacTipo'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getLugar(),
                    'lugarNacTipoId'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getId(),
                    'lugarProvNacTipo'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getLugar(),
                    'lugarProvNacTipoId'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getId(),
                    'localidad'=>$objStudent->getLocalidadNac(),
                    'oficialia'=>$objStudent->getOficialia(),
                    'libro'=>$objStudent->getLibro(),
                    'partida'=>$objStudent->getPartida(),
                    'folio'=>$objStudent->getFolio(),
                ));

                $oldDataStudent = clone $objStudent;
                $oldDataStudent = json_encode((array)$oldDataStudent);
                
                $objStudent->setCarnetIdentidad($carnetIdentidad);
                $objStudent->setComplemento($complemento);
                $objStudent->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoId) );
                $objStudent->setPaterno(mb_strtoupper($paterno, 'utf8'));
                $objStudent->setMaterno(mb_strtoupper($materno, 'utf8'));
                $objStudent->setNombre(mb_strtoupper($nombre, 'utf8'));
                $objStudent->getFechaNacimiento(new \DateTime($fechaNacimiento));
                
                $objStudent->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId) );
                
                if(isset($lugarNacTipoId)){
                    $objStudent->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId) );
                }else{
                    $objStudent->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11) );
                }
                if(isset($lugarProvNacTipoId)){
                    $objStudent->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId) );
                }else{
                    $objStudent->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11) );
                }
                $objStudent->setLocalidadNac($localidad);
                $objStudent->setOficialia($oficialia);
                $objStudent->setLibro($libro);
                $objStudent->setPartida($partida);
                $objStudent->setFolio($folio);
                $em->flush();
                // save log data
                $objEstudianteHistorialModificacion = new EstudianteHistorialModificacion();
                $objEstudianteHistorialModificacion->setDatoAnterior($oldDataStudent2);
                $objEstudianteHistorialModificacion->setResolucion($resolucionAdm);
                $objEstudianteHistorialModificacion->setFechaResolucion(new \DateTime($fecharesolAdm));
                $objEstudianteHistorialModificacion->setJustificacion($justificativo);
                $objEstudianteHistorialModificacion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteId));
                $objEstudianteHistorialModificacion->setFechaRegistro(new \DateTime('now'));
                $objEstudianteHistorialModificacion->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                $em->persist($objEstudianteHistorialModificacion);
                $em->flush();


                 $response->setStatusCode(200);
                        $response->setData(array(
                            'good'=>'good',
                            'studentId'=>$objStudent->getId(),
                        ));
                       
                        return $response;

    }

     public function departamentosAction($pais) {
        $em = $this->getDoctrine()->getManager();
        if ($pais == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }


        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $departamento = array();
        foreach ($dep as $d) {
            $departamento[$d->getId()] = $d->getLugar();
        }

        $dto = $departamento;
        $response = new JsonResponse();
        return $response->setData(array('departamento' => $dto));
    }



    private function getInscription($data, $sw){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', trim($data['codeRude']));
        if(!$sw){
            $query = $query
            ->andwhere('iec.nivelTipo = :nivel')
            ->andwhere('iec.gradoTipo = :grado')
            ->andwhere('ei.estadomatriculaTipo IN (:mat)')
            ->setParameter('nivel', 13)
            ->setParameter('grado', 6) 
            ->setParameter('mat', array( 5,26,55,57,58)) ;
        }
        $query = $query            
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

        $objInfoInscription = $query->getResult();
       
        return  $objInfoInscription;
    }

    private function studentForm($student){
        $em = $this->getDoctrine()->getManager();
         //look for new values
         $entity = $em->getRepository('SieAppWebBundle:Estudiante');
         $query = $entity->createQueryBuilder('e')
                 ->select('e.carnetIdentidad', 'e.complemento', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'e.localidadNac', 'IDENTITY(lt.lugarTipo) as lugarTipoId', 'p.pais', 'd.departamento')
                 ->leftjoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'e.lugarNacTipo=lt.id')
                 ->leftjoin('SieAppWebBundle:DepartamentoTipo', 'd', 'WITH', 'lt.departamentoTipo=d.id')
                 ->leftjoin('SieAppWebBundle:PaisTipo', 'p', 'WITH', 'd.paisTipo=p.id')
                 ->where('e.codigoRude = :codigoRude')
                 ->setParameter('codigoRude', $student->getCodigoRude())
                 ->getQuery();
         $infoStudent = $query->getArrayResult();
         $this->lugarNac = $student->getLugarNacTipo();
         $this->pais = $student->getPaisTipo()->getId();
         $dataProvincia = ($student->getLugarProvNacTipo()) ? $student->getLugarProvNacTipo()->getId() : 1;

         $form = $this->createFormBuilder($student)
                 // ->setAction($this->generateUrl('sie_estudiantes_updatestudentD', array('id' => $student->getId())))
                 ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))
                 ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                 ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                 ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                 ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ-\'-. A-ZÑ-\'-.]{2,40}')))
                 ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                 ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                     'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                     'query_builder' => function (EntityRepository $e) {
                 return $e->createQueryBuilder('pt')
                         ->orderBy('pt.id', 'ASC');
             }, 'property' => 'pais',
                     'data' => $em->getReference("SieAppWebBundle:PaisTipo", $student->getPaisTipo()->getId())))
                 ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                     'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                     'query_builder' => function (EntityRepository $e) {
                 $consult = $e->createQueryBuilder('lt');
                 if ($this->pais == 1) {
                     $consult->where('lt.lugarNivel = :id')
                     ->setParameter('id', '1')
                     ->orderBy('lt.codigo', 'ASC');
                 } else {
                     $consult->where('lt.id = :id')
                     ->setParameter('id', '79355')
                     ->orderBy('lt.codigo', 'ASC');
                 }
                 return $consult;
             }, 'property' => 'lugar',
                     'data' => $em->getReference("SieAppWebBundle:LugarTipo", ($this->pais == 1) ? $student->getLugarNacTipo()->getId() : '79355' )
                 ))
                 ->add('provincia', 'entity', array('label' => 'Provincia', 'attr' => array('class' => 'form-control'),
                     'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                     'query_builder' => function (EntityRepository $e) {
                 return $e->createQueryBuilder('lt')
                         ->where('lt.lugarNivel = :id')
                         ->andwhere('lt.lugarTipo = :idDepto')
                         ->setParameter('id', '2')
                         ->setParameter('idDepto', $this->lugarNac)
                         ->orderBy('lt.codigo', 'ASC')
                 ;
             }, 'property' => 'lugar',
                     'data' => $em->getReference("SieAppWebBundle:LugarTipo", $dataProvincia)
                 ))
                 ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄËÏ-\']{2,30}')))
                 ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getCarnetIdentidad(), 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,12}', 'maxlength' => '12')))
                 ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                 //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                 // ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                 //     'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                 //     'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                 // ))
                 ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                 'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                 'query_builder' => function (EntityRepository $e) {
                     return $e->createQueryBuilder('gt')
                             ->where('gt.id IN (:arrGenero)')
                             ->setParameter('arrGenero', array(1,2))
                             // ->orderBy('lt.codigo', 'ASC')
                     ;
                 }, 'property' => 'genero',
                 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
             ))
                 ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-./_ ]{0,40}')))
                 ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/_() ]{0,20}')))
                 ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                 ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                 ->add('resoladm', 'text', array('required' => false, 'mapped' => false, 'label' => 'Resolucion Administrativa', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                 ->add('fecharesoladm', 'text', array('required' => false, 'mapped' => false, 'label' => 'Fecha Resolucion Administrativa', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                 ->add('obs', 'textarea', array('required' => false, 'mapped' => false, 'label' => 'Justivicativo', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                 ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateDataStudent()')))
                 ->getForm();
        
        return $form;

    }

    public function updateDataStudentAction(Request $request){
        
        
        try {
            // get the send values
            $form = $request->get('form');
            // create db Conexion
            $em = $this->getDoctrine()->getManager();

            //get the info data
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
            // update student data
            $resultSegip = $this->saveResultSegipService($form);
            if($resultSegip || $resultSegip == 2){
                //GET OLD DATA
                $oldDataStudent2 =json_encode( array(
                    'carnetIdentidad'=>$objStudent->getCarnetIdentidad(),
                    'complemento'=>$objStudent->getComplemento(),
                    'genero'=>$objStudent->getGeneroTipo()->getGenero(),
                    'paterno'=>$objStudent->getPaterno(),
                    'materno'=>$objStudent->getMaterno(),
                    'nombre'=>$objStudent->getNombre(),
                    'fecharesoladm'=>$objStudent->getFechaNacimiento(),
                    'pais'=>$objStudent->getPaisTipo()->getPais(),
                    'paisId'=>$objStudent->getPaisTipo()->getId(),
                    'lugarNacTipo'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getLugar(),
                    'lugarNacTipoId'=>($objStudent->getLugarNacTipo()==NULL)?'':$objStudent->getLugarNacTipo()->getId(),
                    'lugarProvNacTipo'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getLugar(),
                    'lugarProvNacTipoId'=>($objStudent->getLugarProvNacTipo()==NULL)?'':$objStudent->getLugarProvNacTipo()->getId(),
                    'localidad'=>$objStudent->getLocalidadNac(),
                    'oficialia'=>$objStudent->getOficialia(),
                    'libro'=>$objStudent->getLibro(),
                    'partida'=>$objStudent->getPartida(),
                    'folio'=>$objStudent->getFolio(),
                ));

                $oldDataStudent = clone $objStudent;
                $oldDataStudent = json_encode((array)$oldDataStudent);
                
                $objStudent->setCarnetIdentidad($form['ci']);
                $objStudent->setComplemento($form['complemento']);
                $objStudent->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']) );
                $objStudent->setPaterno(mb_strtoupper($form['paterno'], 'utf8'));
                $objStudent->setMaterno(mb_strtoupper($form['materno'], 'utf8'));
                $objStudent->setNombre(mb_strtoupper($form['nombre'], 'utf8'));
                $objStudent->getFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                
                $objStudent->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']) );
                
                if(isset($form['departamento'])){
                    $objStudent->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']) );
                }else{
                    $objStudent->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11) );
                }
                if(isset($form['provincia'])){
                    $objStudent->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia']) );
                }else{
                    $objStudent->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11) );
                }
                $objStudent->setLocalidadNac($form['localidad']);
                $objStudent->setOficialia($form['oficialia']);
                $objStudent->setLibro($form['libro']);
                $objStudent->setPartida($form['partida']);
                $objStudent->setFolio($form['folio']);
                $em->flush();
                // save log data
                $objEstudianteHistorialModificacion = new EstudianteHistorialModificacion();
                $objEstudianteHistorialModificacion->setDatoAnterior($oldDataStudent2);
                $objEstudianteHistorialModificacion->setResolucion($form['resoladm']);
                $objEstudianteHistorialModificacion->setFechaResolucion(new \DateTime($form['fecharesoladm']));
                $objEstudianteHistorialModificacion->setJustificacion($form['obs']);
                $objEstudianteHistorialModificacion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($objStudent->getId()));
                $objEstudianteHistorialModificacion->setFechaRegistro(new \DateTime('now'));
                $objEstudianteHistorialModificacion->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                $em->persist($objEstudianteHistorialModificacion);
                $em->flush();

                if($resultSegip == 1){
                    $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
                }else{
                    $updateMessage = 'Datos Modificados Correctamente';    
                }
                $typeMessage = 'success';
                $mainMessage = 'Guardado';

            }else{
                $updateMessage = 'Actualización no realizada, de acuerdo a la validación del SEGIP, el número de carnet y/o complemento no corresponde a los datos(paterno, materno, nombre y fecha de nacimiento) de la persona';
                $typeMessage = 'warning';
                $mainMessage = 'Error';
            }
            return $this->render('SieRegularBundle:SpecialModificationDataStudent:updateDataStudent.html.twig', array(
                'updateMessage' => $updateMessage,
                'typeMessage'   => $typeMessage,
                'mainMessage'   => $mainMessage
            ));
            
        } catch (Exception $e) {
            
        }
        
    }

    /**
    * check the student info with the segip service
    **/
    private function saveResultSegipService($form){
        //create db conexion
        $em = $this->getDoctrine()->getManager();

        // chec if the student has CI-COMPLEMENTO to do the validation
        $answerSegip=2;
        
        if($form['ci']){

            $arrParametros = array('complemento'=>$form['complemento'],
                'primer_apellido'=>$form['paterno'],
                'segundo_apellido'=>$form['materno'],
                'nombre'=>$form['nombre'],
                'fecha_nacimiento'=>$form['fechaNacimiento']);

            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');

        }
        
    
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if($answerSegip===true){
            $student->setSegipId(1);       
        }else{
            $student->setSegipId(0);       
        }
        $em->flush();
        return $answerSegip;
    }  

    public function valsegipAction(Request $request){
        $response = new JsonResponse();
        //get the send values
        $carnet = $request->get('carnet');
        $complemento = $request->get('complemento');
        $fechaNacimiento = $request->get('fechaNacimiento');
        $paterno = $request->get('paterno');
        $materno = $request->get('materno');
        $nombre = $request->get('nombre');

        $arrParametros = array(
                'complemento'=>$complemento,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento'=>$fechaNacimiento);

        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');        
        
        $response->setStatusCode(200);
        $response->setData(array(
            'answerSegip'=>$answerSegip,
        ));
       
        return $response;  
    }

    

}
