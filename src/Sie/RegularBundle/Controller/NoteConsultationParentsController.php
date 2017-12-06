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

class NoteConsultationParentsController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * note consultation parents Index
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
        return $this->render($this->session->get('pathSystem') . ':NoteConsultationParents:index.html.twig', array(
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
        //check if the data exist
        if ($objStudent) {
            //look for inscription data
            $oInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getNotePerStudent($objStudent->getId());

            //check if exists data
            if (!$oInscription) {
                $message = 'Estdiante no cuenta con Historial';
                $this->addFlash('warningrein', $message);
                $exist = false;
                $oInscription = array();
            }
        } else {
            $message = 'Código RUDE no existe';
            $this->addFlash('warningrein', $message);
            $exist = false;
        }

        return $this->render($this->session->get('pathSystem') . ':NoteConsultationParents:result.html.twig', array(
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
        //get the info about inscription
        $aData = array(
            'sie' => $sie, 'gestion' => $gestion,
            'unidadEducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie)->getInstitucioneducativa(),
            'nivel' => $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel)->getNivel(),
            'grado' => $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado)->getGrado(),
            'paralelo' => $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo)->getParalelo(),
            'turno' => $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno)->getTurno()
        );

        //get the nota data
        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudent($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);
        //get the correct cualitaiva note

        if ($nivel == 11 || $nivel == 1)
            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentCualitativaIni($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);
        else
            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentCualitativa($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);

        //init the data values
        $aNota = array();
        $aBim = array();
        $aNotaCualitativa = array();

        //build the nota
        foreach ($objNota as $nota) {
            //get the correct label to the libreta
            $expr = '/(?<=\s|^)[a-z]/i';
            preg_match_all($expr, $nota['notaTipoLiteral'], $matches);
            $labelLib = implode('.', $matches[0]);
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = ($nivel == 11) ? $nota['notaCualitativa'] : $nota['notaCuantitativa'] : '';
            //($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
            if ($labelLib)
                $aBim[$nota['notaTipo']] = $labelLib;
        }
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idstudent);
        $tablesize = ($nivel == 11) ? '12' : '7';
        $aBim = ($aBim) ? $aBim : array();
        return $this->render('SieRegularBundle:NoteConsultationParents:nota.html.twig', array(
                    'notastudent' => $aNota,
                    'bimestres' => $aBim,
                    'objNotaCualitativa' => ($objNotaCualitativa) ? $objNotaCualitativa : array(),
                    'level' => $nivel,
                    'tablesize' => $tablesize,
                    'datastudent' => $student,
                    'dataInfo' => $aData
        ));
    }

    public function notaNewAction($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie, $estadomatriculaTipo) {

        $em = $this->getDoctrine()->getManager();
        //get the info about inscription
        $aData = array(
            'sie' => $sie, 'gestion' => $gestion,'estadomatriculaTipo'=>$estadomatriculaTipo,'inscripcionid'=>$inscripcionid,
            'unidadEducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie)->getInstitucioneducativa(),
            'nivel' => $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel)->getNivel(),
            'grado' => $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado)->getGrado(),
            'paralelo' => $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo)->getParalelo(),
            'turno' => $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno)->getTurno()
        );

        //get the nota data
        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);
        //get the correct cualitaiva note

        if ($nivel == 11 || $nivel == 1)
            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentCualitativaIniNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);
        else
            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentCualitativaNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);

        //init the data values
        $aNota = array();
        $aBim = array();
        $aNotaCualitativa = array();

        //build the nota
        foreach ($objNota as $nota) {
            //get the correct label to the libreta
            $expr = '/(?<=\s|^)[a-z]/i';
            preg_match_all($expr, $nota['notaTipoLiteral'], $matches);
            $labelLib = implode('.', $matches[0]);
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = ($nivel == 11) ? $nota['notaCualitativa'] : $nota['notaCuantitativa'] : '';
            //($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
            if ($labelLib)
                $aBim[$nota['notaTipo']] = $labelLib;
        }
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idstudent);
        $tablesize = ($nivel == 11) ? '12' : '7';
        $aBim = ($aBim) ? $aBim : array();
        return $this->render('SieRegularBundle:NoteConsultationParents:nota.html.twig', array(
                    'notastudent' => $aNota,
                    'bimestres' => $aBim,
                    'objNotaCualitativa' => ($objNotaCualitativa) ? $objNotaCualitativa : array(),
                    'level' => $nivel,
                    'tablesize' => $tablesize,
                    'datastudent' => $student,
                    'dataInfo' => $aData,
                    'setNotasForm'   => $this->setNotasForm('krlos')->createView(),
        ));
    }

    /*
    // this is the next step to do the setting up NOTAS to student
    */
    private function setNotasForm($data){
      return  $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizarNotas_show'))
                // ->add('idInscripcion','text',array('data'=>$data))
                ->add('setNotas','submit',array('label'=>'Registrar Notas','attr'=>array('class'=>'btn btn-red', 'role'=>'button')))
              ->getForm();
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
    public function removeAction($idstudent, $nivel, $grado, $paralelo, $turno, $sie, $gestion, $eiid) {


        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                'estudianteInscripcion' => $eiid,
                'gestionTipo' => $gestion
            ));

            //step 1 delete nota
            foreach ($objEstAsig as $asig) {

                $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array(
                    'estudianteAsignatura' => $asig->getId()
                ));
                if ($objNota)
                    $em->remove($objNota);
                //$em->flush();
            }

            //step 2 delete asignatura
            array_walk($objEstAsig, array($this, 'deleteEntity'), $em);
            //step 3 delete nota cualitativa
            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array(
                'estudianteInscripcion' => $eiid
            ));
            array_walk($objNotaCualitativa, array($this, 'deleteEntity'), $em);
            //step 4 delete socio economico data
            $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findOneBy(array(
                'estudianteInscripcion' => $eiid
            ));
            if ($objSocioEco)
                $em->remove($objSocioEco);

            //step 5 remove teh inscription
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
            $em->remove($objStudentInscription);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $message = "datos Eliminados";
            $this->addFlash('successremoveins', $message);
            return $this->redirectToRoute('remove_inscription_sie_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    protected function deleteEntity($entity, $key, $em) {
        if ($entity)
            $em->remove($entity);
    }

}
