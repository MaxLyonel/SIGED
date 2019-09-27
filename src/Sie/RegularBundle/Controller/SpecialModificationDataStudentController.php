<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
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
        $arrOptions = array('General','Homologacion');
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
        $form = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codeRude']));
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
            }else{
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
            }else{
                $sw = true;
            }
            break;
        }
        $this->addFlash('messageModStudent', $message);
        
        
        // dump($objStudent);
        // dump($form);
        // die;

        return $this->render('SieRegularBundle:SpecialModificationDataStudent:lookforStudent.html.twig', array(
                'compleMessage' => $compleMessage,
                'typeMessage'   => $typeMessage,
                'form'          => $this->studentForm($objStudent)->createView(),
                'sw'            => $sw,
        ));
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
                ->setParameter('id', $data['codeRude']);
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
                 ->add('obs', 'textarea', array('required' => false, 'mapped' => false, 'label' => 'Observacion', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
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
                $objStudent->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']) );
                $objStudent->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia']) );
                $objStudent->setLocalidadNac($form['localidad']);
                $objStudent->setOficialia($form['oficialia']);
                $objStudent->setLibro($form['libro']);
                $objStudent->setPartida($form['partida']);
                $objStudent->setFolio($form['folio']);
                $em->flush();
                // save log data
                $objEstudianteHistorialModificacion = new EstudianteHistorialModificacion();
                $objEstudianteHistorialModificacion->setDatoAnterior($oldDataStudent);
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

}
