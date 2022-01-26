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
use Symfony\Component\HttpFoundation\JsonResponse;

class StudentsInscriptionsController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * students Inscriptions Index
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
        return $this->render($this->session->get('pathSystem') . ':StudentsInscription:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 8; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('students_inscriptions_find'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase', 'onkeyup'=>'getYearOfUe(this.value)', 'onchange'=>'getYearOfUe(this.value)')))
                        ->add('gestion', 'choice', array('label' => 'Gestión',  'attr' => array('class' => 'form-control')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
    }

    /**
     * find the bachillers per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function findAction(Request $request) {

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            //get ghe info about UE
            $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
            //validate UE
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->add('noticesi', 'No existe Unidad Educativa');
                return $this->redirectToRoute('students_inscriptions_index');
            }
            //look for the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $institucionEducativa->getId());
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            //check if the user has the tuicion
            if (!$aTuicion[0]['get_ue_tuicion']) {
                $this->session->getFlashBag()->add('noticesi', 'No tiene tuición sobre la Unidad Educativa');
                return $this->redirectToRoute('students_inscriptions_index');
            }

            $numberStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInscriptionsPerUe($form['sie'], $form['gestion']);
            $message = "Resultado de la Busqueda...";
            $this->addFlash('successsi1', $message);
            return $this->render($this->session->get('pathSystem') . ':StudentsInscription:find.html.twig', array(
                        'numberStudents' => $numberStudents,
                        'unidadEducativa' => $institucionEducativa,
                        'gestionSelected' => $form['gestion']
            ));
        }
    }

    /**
     * fill the list of students with nivell, grado, paralelo, turno
     * @param Request $requerst
     * @param type $sie
     * @param type $gestion
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     */
    public function openhistoricAction(Request $requerst, $sie, $gestion, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        $aData = array(
            'sie' => $sie, 'gestion' => $gestion,
            'nivel' => $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel)->getNivel(),
            'grado' => $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado)->getGrado(),
            'paralelo' => $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo)->getParalelo(),
            'turno' => $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno)->getTurno()
        );
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
        return $this->render($this->session->get('pathSystem') . ':StudentsInscription:listStudents.html.twig', array(
                    'students' => $objStudents,
                    'dataInfo' => $aData
        ));
    }

    // get the year of UE by sie
    public function getYearOfUeAction(Request $request){
        // get the send values
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get all year of ue        
        $query = $em->getConnection()->prepare('
                SELECT DISTINCT gestion_tipo_id
                FROM institucioneducativa_curso
                WHERE institucioneducativa_id = '. $sie .'
                order by gestion_tipo_id DESC
                ');
        $query->execute();
        $arryearsOfUe = $query->fetchAll();
        
        $arryearsofue = array();
        foreach ($arryearsOfUe as $yearofue) {
            $arryearsofue[$yearofue['gestion_tipo_id']] = $yearofue['gestion_tipo_id'];
        }
        //dump($arryearsofue);die;
        $response = new JsonResponse();
        return $response->setData(array('arryearsofue' => $arryearsofue));

    }


}
