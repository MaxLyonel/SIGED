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

class InscriptionDoblePromocionController extends Controller {

    public $oparalelos;

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
        return $this->render($this->session->get('pathSystem') . ':InscriptionDoblePromocion:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }

    private function craeteformsearch() {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('remove_inscription_sie_index'))
                        ->add('rude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findInscription()')))
                        ->getForm();
    }

    /**
     * find the student and inscription data
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {
        //get the value to send
        $rude = $request->get('rude');
        //find the id of student
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
            'codigoRude' => $rude
        ));
        $exist = true;
        $oInscription = array();
        //check if the data exist
        if ($objStudent) {
            //look for inscription data
            $oInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscriptionEfectivoDoblePrmocion($objStudent->getId(), $this->session->get('currentyear') - 1);
//            echo "<pre>";
//            print_r($oInscription);
//            echo "</pre>";
//            die;
            //check if exists data
            if (!$oInscription || count($oInscription) < 2) {
                $message = 'Estdiante no cuenta con Historial de Doble Promoción';
                $this->addFlash('warningrein', $message);
                $exist = false;
                $oInscription = array();
            }
        } else {
            $message = 'Código RUDE no existe';
            $this->addFlash('warningrein', $message);
            $exist = false;
        }
        //render the result
        return $this->render($this->session->get('pathSystem') . ':InscriptionDoblePromocion:result.html.twig', array(
                    'dataInscription' => $oInscription,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist
        ));
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
        return $this->render('SieRegularBundle:InscriptionDoblePromocion:nota.html.twig', array(
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
        return $this->render('SieRegularBundle:InscriptionDoblePromocion:inscription.html.twig', array(
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
