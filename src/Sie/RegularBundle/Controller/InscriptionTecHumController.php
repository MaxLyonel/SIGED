<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;

class InscriptionTecHumController extends Controller {

    public $oparalelos;
    public $lugarNac;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecHum:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }

    private function craeteformsearch() {
        $em = $this->getDoctrine()->getManager();
        //$estudiante = new Estudiante();
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('remove_inscription_sie_index'))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '30', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('materno', 'text', array('label' => 'Materno', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '30', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '30', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('fnacimiento', 'text', array('mapped' => false, 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                        ->add('ci', 'text', array('label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '30', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                            'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                            'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('g')
                                ->where('g.id != :id')
                                ->setParameter('id', '3');
                    }, 'property' => 'genero'
                        ))
                        ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                            'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                            'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('pt')
                                ->orderBy('pt.id', 'ASC');
                    }, 'property' => 'pais',
                        ))
                        ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                            'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                            'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '90')
                                ->orderBy('lt.codigo', 'ASC');
                    }, 'property' => 'lugar',
                            'data' => $em->getReference("SieAppWebBundle:LugarTipo", 11)
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
                            'data' => $em->getReference("SieAppWebBundle:LugarTipo", '90')
                        ))
                        ->add('localidad', 'text', array('mapped' => false, 'required' => false, 'label' => 'Localidad', 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'pattern' => '[a-zñ A-ZÑ]{3,40}')))


                        //->add('rude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findStudent()')))
                        ->getForm();
    }

    /**
     * find the student and inscription data
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {


        //get the value to send
        $form = $request->get('form');

        //find the id of student
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsByPatMatNom($form);

        $exist = true;
        $oInscription = array();
        //check if the data exist
        if ($objStudent) {
            //look for inscription data
        } else {
            $message = 'No existe ocurrencias';
            $this->addFlash('warningrein', $message);
            $exist = false;
        }
        //render the result
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecHum:result.html.twig', array(
                    'students' => $objStudent,
                    'formnewinscription' => $this->createNewInscriptionForm($form)->createView(),
                    'formins' => $this->createInscriptionForm()->createView(),
                    'exist' => $exist
        ));
    }

    private function createNewInscriptionForm($data) {

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_techum_newins'))
                        ->add('newins', 'submit', array('label' => 'Nueva Inscripcion', 'attr' => array('class' => 'btn btn-success')))
                        ->add('infostudent', 'hidden', array('label' => 'student', 'data' => serialize($data)))
                        ->getForm()
        ;
    }

    private function createInscriptionForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_techum_oldins'))
                        ->add('ins', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    public function newinsAction(request $request) {

        $getData = $request->get('form');
        $data = unserialize($getData['infostudent']);
        $data['rude'] = '';

        $formInscription = $this->createFormInscription($request->get('form'), '', '2015-01-01');
        return $this->render($this->session->get('pathSystem') . ':InscriptionTecHum:new.html.twig', array(
                    'newstudent' => $data,
                    'formInscription' => $formInscription->createView()
        ));
    }

    /**
     * buil the inscription form 
     * @param type $aInscrip
     * @return type form
     */
    private function createFormInscription($data, $rude, $yearStudent) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_techum_savenew'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('rude', 'hidden', array('data' => $rude, 'required' => false))
                ->add('yearStudent', 'hidden', array('data' => $yearStudent, 'required' => false))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
    }

    /**
     * todo the registration of extranjero
     * @param Request $request
     * 
     */
    public function savenewAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');

            //if doesn't exist the rude we generate the rude
            if (!$form['rude']) {
                $digits = 4;
                $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
                $rude = $form['institucionEducativa'] . $this->session->get('currentyear') . $mat . $this->generarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
                $newStudent = unserialize($form['newdata']);
                $newStudent = unserialize($newStudent['infostudent']);


                //$newStudent = unserialize($newStudent['fullInfo']);

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
                $query->execute();

                $student = new Estudiante();
                $student->setPaterno(strtoupper($newStudent['paterno']));
                $student->setMaterno(strtoupper($newStudent['materno']));
                $student->setNombre(strtoupper($newStudent['nombre']));
                $student->setCarnetIdentidad($newStudent['ci']);
                //$student->setComplemento($newStudent['complemento']);
                $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
                $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
                $student->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['departamento']));
                $student->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['provincia']));
                $student->setLocalidadNac(strtoupper($newStudent['localidad']));
                $student->setFechaNacimiento(new \DateTime($newStudent['fnacimiento']));
                $student->setCodigoRude($rude);

                $em->persist($student);
                $em->flush();
                $studentId = $student->getId();
            } else {
                //if the student has a rude, get the id student
                $studentId = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude']))->getId();
            }


            //look for the course to the student
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $this->session->get('currentyear')
            ));

            if (!$objCurso) {
                $InstitucioneducativaCurso = new InstitucioneducativaCurso();
                $InstitucioneducativaCurso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                $InstitucioneducativaCurso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(3));
                $InstitucioneducativaCurso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
                $InstitucioneducativaCurso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
                $InstitucioneducativaCurso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find('2015'));
                $InstitucioneducativaCurso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
                $InstitucioneducativaCurso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $InstitucioneducativaCurso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
                $InstitucioneducativaCurso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));

                $em->persist($InstitucioneducativaCurso);
                $em->flush();
            }
            $iecId = ($objCurso) ? $objCurso->getId() : $InstitucioneducativaCurso->getId();

            //insert a new record with the new selected variables and put matriculaFinID like 4
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(59));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodinstechum', 'Datos registrados en el sistema...');
            return $this->redirect($this->generateUrl('inscription_techum_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
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

    public function existAction(Request $request) {
        try {

            //create the connection to DB
            $em = $this->getDoctrine()->getManager();
            //get values send by post
            $form = $request->get('form');

            $idStudent = $form['idstudent'];
            $codigoRude = $form['rude'];

            //look for inscription for this idStudent
            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $studentInscription->createQueryBuilder('ei')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :idStudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :mat')
                    ->setParameter('idStudent', $idStudent)
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('mat', '4')
                    ->getQuery();
            $objStudentInscription = $query->getResult();
            //validate if the student has an inscription
            if ($objStudentInscription) {
                $this->addFlash('warnininstechum', 'El Estudiante ya cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_techum_index');
            }
            //get info about the student
            $student = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $student->createQueryBuilder('e')
                    ->select('e.paterno as paterno, e.materno as materno , e.nombre as nombre, e.fechaNacimiento as fnacimiento, e.carnetIdentidad as ci, e.complemento as complemento, IDENTITY(e.generoTipo) as generoTipo, e.codigoRude as rude ')
                    ->where('e.id = :idStudent')
                    ->setParameter('idStudent', $idStudent)
                    ->getQuery();
            $data = $query->getResult();
            //get the bith daty of student
            foreach ($data[0]['fnacimiento'] as $odata) {
                $sbirthdate = $odata;
                break;
            }

            //get how old is the student
            $sbday = explode(' ', $sbirthdate);
            list ($year, $month, $day) = explode('-', $sbday[0]);
            //$tiempo = $this->tiempo_transcurrido($day . '-' . $month . '-' . $year, '30-6-2015');
            $yearStudent = 8;
            //$tiempo[0];
            $data[0]['yearStudent'] = $yearStudent;
            //build the form to the inscription
            $formInscription = $this->createFormInscription($request->get('form'), $data[0]['rude'], $yearStudent);
            return $this->render($this->session->get('pathSystem') . ':InscriptionTecHum:new.html.twig', array(
                        'newstudent' => $data[0],
                        'formInscription' => $formInscription->createView()
            ));
        } catch (Exception $ex) {
            
        }
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id) {
        $em = $this->getDoctrine()->getManager();
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
         */
        $aniveles = array();
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "Unidad Educativa no existe";
        $em = $this->getDoctrine()->getManager();
        //get the Niveles

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.nivelTipo != :nivel')
                //->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $id)
                ->setParameter('nivel', '13')
                //->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->getQuery();
        $aNiveles = $query->getResult();
        foreach ($aNiveles as $nivel) {
            $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
        }

        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

        $response = new JsonResponse();
        $aniveles = array('13' => 'Seccundaria');
        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $sie) {
        $em = $this->getDoctrine()->getManager();
        //get grado
        $grados = ($idnivel == 11) ? array(1, 2) : array(1);
        $agrados = array();
//        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
//        $query = $entity->createQueryBuilder('iec')
//                ->select('(iec.gradoTipo)')
//                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
//                ->where('iec.institucioneducativa = :sie')
//                ->andWhere('iec.nivelTipo = :idnivel')
//                ->andwhere('iec.gradoTipo IN (:grados)')
//                ->andwhere('iec.gestionTipo = :gestion')
//                ->setParameter('sie', $sie)
//                ->setParameter('idnivel', $idnivel)
//                ->setParameter('grados', $grados)
//                ->setParameter('gestion', $this->session->get('currentyear'))
//                ->distinct()
//                ->orderBy('iec.gradoTipo', 'ASC')
//                ->getQuery();
//        $aGrados = $query->getResult();
//        foreach ($aGrados as $grado) {
//            $agrados[$grado[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado();
//        }
        $agrados = array('6' => 'Sexto');
        $response = new JsonResponse();
        return $response->setData(array('agrados' => $agrados));
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function findparaleloAction($grado, $sie, $nivel) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aparalelos = array();
//        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
//        $query = $entity->createQueryBuilder('iec')
//                ->select('(iec.paraleloTipo)')
//                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
//                ->where('iec.institucioneducativa = :sie')
//                ->andWhere('iec.nivelTipo = :nivel')
//                ->andwhere('iec.gradoTipo = :grado')
//                ->andwhere('iec.gestionTipo = :gestion')
//                ->setParameter('sie', $sie)
//                ->setParameter('nivel', $nivel)
//                ->setParameter('grado', $grado)
//                ->setParameter('gestion', $this->session->get('currentyear'))
//                ->distinct()
//                ->orderBy('iec.paraleloTipo', 'ASC')
//                ->getQuery();
//        $aParalelos = $query->getResult();
//        foreach ($aParalelos as $paralelo) {
//            $aparalelos[$paralelo[1]] = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
//        }
        $aparalelos = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E', '6' => 'F', '7' => 'G', '8' => 'H', '9' => 'I', '10' => 'j');
        $response = new JsonResponse();
        return $response->setData(array('aparalelos' => $aparalelos));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturnoAction($paralelo, $sie, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();

//        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
//        $query = $entity->createQueryBuilder('iec')
//                ->select('(iec.turnoTipo)')
//                ->where('iec.institucioneducativa = :sie')
//                ->andWhere('iec.nivelTipo = :nivel')
//                ->andwhere('iec.gradoTipo = :grado')
//                ->andwhere('iec.paraleloTipo = :paralelo')
//                ->andWhere('iec.gestionTipo = :gestion')
//                ->setParameter('sie', $sie)
//                ->setParameter('nivel', $nivel)
//                ->setParameter('grado', $grado)
//                ->setParameter('paralelo', $paralelo)
//                ->setParameter('gestion', $this->session->get("currentyear"))
//                ->distinct()
//                ->orderBy('iec.turnoTipo', 'ASC')
//                ->getQuery();
//        $aTurnos = $query->getResult();
//        foreach ($aTurnos as $turno) {
//            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
//        }
        $aturnos = array('1' => 'MAÑANA',
            '2' => 'TARDE',
            '4' => 'NOCHE',
            '8' => 'MAÑANA_TARDE',
            '9' => 'TARDE_NOCHE',
            '10' => 'MAÑANA,TARDE Y NOCHE',
            '11' => 'MAÑANA NOCHE');

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

    /**
     * get the notas of student
     * @param type $idstudent
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @param type $gestion
     * @return list of nota the student
     */
    public function notaAction($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {

        $em = $this->getDoctrine()->getManager();
        //get the nota data
        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudent($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);

        $aNota = array();
        //build the nota
        foreach ($objNota as $nota) {
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = $nota['notaCuantitativa'] : '';
            ($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
        }
        return $this->render('SieRegularBundle:InscriptionTecHum:nota.html.twig', array(
                    'notastudent' => $aNota,
                    'bimestres' => $aBim
        ));
    }

    /**
     * Remove the inscription
     * @param type $idstudent
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @param type $sie
     * @param type $gestion
     * @param type $eiid
     * @return type delete records in student inscription
     */
    public function removeAction(Request $request) {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //get id inscription for the student
        //look for the record about inscription student to do the update
        $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['eiId']);
        $inscriptionStudent->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['matricula']));
        $inscriptionStudent->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(64));
        $em->persist($inscriptionStudent);
        $em->flush();
        //go the next window
        $message = 'Cambio realizado correctamente...';
        $this->addFlash('goodindexdoble', $message);
        return $this->redirectToRoute('inscription_doble_promocion_index');

        die;
    }

    public function inscriptionAction($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie, $matricula, $ciclo) {


        $posicionCurso = $this->getCourse($nivel, $ciclo, $grado, $matricula);
        //get paralelos
        $this->oparalelos = $this->getParalelosStudent($posicionCurso, $sie);

        //get current inscription
        return $this->render('SieRegularBundle:InscriptionTecHum:inscription.html.twig', array(
                    'form' => $this->inscriptionForm($idstudent, $sie, $posicionCurso)->createView()
        ));
    }

    /**
     * buil the Omitidos form 
     * @param type $aInscrip
     * @return type form
     */
    private function inscriptionForm($idStudent, $ue, $nextcurso) {

        $em = $this->getDoctrine()->getManager();
        list($nextnivel, $nextciclo, $nextgrado) = explode('-', $this->aCursos[$nextcurso]);
        $onivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nextnivel);
        $ogrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextgrado);
        $ociclo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextciclo);

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_talento_regNotas'))
                //->add('ue', 'text', array('data' => $ue, 'disabled' => true, 'label' => 'Unidad Educativa', 'attr' => array('required' => false, 'maxlength' => 8, 'class' => 'form-control')))
                //->add('ueid', 'hidden', array('data' => $ue))
                ->add('institucionEducativa', 'text', array('data' => $ue, 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'data' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ue)->getInstitucioneducativa(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'text', array('data' => $onivel->getNivel(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('grado', 'text', array('data' => $ogrado->getGrado(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('nivelId', 'hidden', array('data' => $onivel->getId()))
                ->add('gradoId', 'hidden', array('data' => $ogrado->getId()))
                ->add('cicloId', 'hidden', array('data' => $ociclo->getId()))
                ->add('idStudent', 'hidden', array('required' => false, 'mapped' => false, 'data' => $idStudent))
                //->add('lastue', 'hidden', array('mapped' => false, 'data' => $lastue))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                //->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                ->add('paralelo', 'entity', array('label' => 'Paralelo', 'empty_value' => 'seleccionar...', 'attr' => array('class' => 'form-control'),
                    'class' => 'SieAppWebBundle:ParaleloTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('p')
                        ->where('p.id in (:ue)')
                        ->setParameter('ue', $this->oparalelos)
                        ->distinct()
                        ->orderBy('p.paralelo', 'ASC')
                ;
            }, 'property' => 'paralelo'
                ))
                ->add('turno', 'choice', array('attr' => array('requirede' => true, 'class' => 'form-control')))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
    }

//    private function inscriptionForm() {
//        return $this->createFormBuilder()
//                        ->add('sie', 'text')
//                        ->add('ue', 'text')
//                        ->add('nivel', 'text')
//                        ->add('grado', 'text')
//                        ->add('paralelo', 'text')
//                        ->add('turno', 'text')
//                        ->add('save', 'submit')
//                        ->getForm();
//    }

    /**
     * build the cursos in a array
     * return array with the courses
     */
    private function fillCursos() {
        $this->aCursos = array(
            ('11-1-1'),
            ('11-1-2'),
            ('12-1-1'),
            ('12-1-2'),
            ('12-1-3'),
            ('12-2-4'),
            ('12-2-5'),
            ('12-2-6'),
            ('13-1-1'),
            ('13-1-2'),
            ('13-2-3'),
            ('13-2-4'),
            ('13-3-5'),
            ('13-3-6')
        );
        return($this->aCursos);
    }

    /**
     * obtiene el nivel, ciclo y grado
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return nivel, ciclo grado del estudiante
     */
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
//get the array of courses
        $cursos = $this->aCursos;
//this is a switch to find the courses
        $sw = 1;
//loof for the courses of student
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }

    private function getParalelosStudent($posCurso, $ue) {
        $em = $this->getDoctrine()->getManager();
        list($nivel, $ciclo, $grado) = explode('-', $this->aCursos[$posCurso]);

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :ue')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('ue', $ue)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        try {
            $objparalelos = $query->getResult();
            $aparalelos = array();
            if ($objparalelos) {
                foreach ($objparalelos as $paralelo) {
                    $aparalelos[$paralelo['paraleloTipo']] = $paralelo['paraleloTipo'];
                }
            }
            return $aparalelos;
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
