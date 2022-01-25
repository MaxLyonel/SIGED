<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Form\EstudianteAsignaturaType;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * EstudianteAsignatura controller.
 *
 */
class EstudianteAsignaturaController extends Controller {

    /**
     * form to find the stutdent's users
     *
     */
    public function indexAction(Request $request) {
        // data es un array con claves 'name', 'email', y 'message'

        return $this->render('SieAppWebBundle:EstudianteAsignatura:searchlibreta.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     * @param 
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('nota_main_find'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campor 1 obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * find the materias and notas by rude
     * @param Request $request
     * @return type
     */
    public function findmaterianotaAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($form['codigoRude']);
        if ($entities) {
            //get the student data
            $query = $em->getConnection()->prepare('SELECT get_estudiante_datos (:rude::VARCHAR)');
            $query->bindValue(':rude', $form['codigoRude']);
            $query->execute();
            $dataStudent = $query->fetchAll();

            $arraykeystudent = array('codigo_rude', 'carnet_identidad', 'paterno', 'materno', 'nombre', 'genero', 'lugar_nacimiento', 'oficialia', 'libro', 'partida', 'folio', 'grupo_sanguineo', 'fecha_nacimiento', 'estado_civil', 'pasaporte');
            $datastudent = $this->resetData($dataStudent, $arraykeystudent, 'get_estudiante_datos');

            //get inscription data
            $arraykeyinscription = array('estudiante_id', 'estudiante_inscripcion_id', 'grado_tipo_id', 'grado', 'paralelo_tipo_id', 'paralelo', 'gestion', 'ciclo_tipo_id', 'ciclo', 'dependencia_tipo_id', 'dependencia', 'orgcurricula', 'institucioneducativa_tipo_id', 'institucioneducativa_tipo', 'convenio_tipo_id', 'convenio', 'institucioneducativa_id', 'institucioneducativa', 'nivel_tipo_id', 'nivel', 'periodo_tipo_id', 'periodo', 'turno_tipo_id', 'turno', 'sucursal_tipo', 'sucursal', 'fecha_inscripcion', 'observacion');
            $query = $em->getConnection()->prepare('SELECT get_estudiante_inscripcion (:estudiante_id::INT)');
            $query->bindValue(':estudiante_id', $entities->getId());
            $query->execute();
            $dataInscription = $query->fetchAll();

            $datainscription = $this->resetData($dataInscription, $arraykeyinscription, 'get_estudiante_inscripcion');

            //get materias by rude
            $query = $em->getConnection()->prepare('SELECT get_estudiante_asignaturas(:gestion_id::INT, :estudiante_id::INT, :institucioneducativa_id::INT, :nivel_id::INT, :ciclo_id::INT, :grado_id::INT, :turno_id::INT, :periodo_id::INT)');
            $query->bindValue(':gestion_id', $datainscription['gestion']);
            $query->bindValue(':estudiante_id', $datainscription['estudiante_id']);
            $query->bindValue(':institucioneducativa_id', $datainscription['institucioneducativa_id']);
            $query->bindValue(':nivel_id', $datainscription['nivel_tipo_id']);
            $query->bindValue(':ciclo_id', $datainscription['ciclo_tipo_id']);
            $query->bindValue(':grado_id', $datainscription['grado_tipo_id']);
            $query->bindValue(':turno_id', $datainscription['turno_tipo_id']);
            $query->bindValue(':periodo_id', $datainscription['periodo_tipo_id']);
            $query->execute();
            $materias = $query->fetchAll();
            $amaterias = array();
//            echo "<pre>";
//            print_r($materias);
//            echo "<pre>";
//            die;
            //Array ( [get_estudiante_asignaturas] => (93912,4073000420077357,493135,1037,MATEM�TICA,"",t,t) ) 
            $keyMaterias = array('estudiante_id', 'id', 'id1', 'asignatura_id', 'asignatura', 'valor', 'turno', 'turno1');

            echo sizeof($materias);

            $amaterias = $this->resetData($materias, $keyMaterias, 'get_estudiante_asignaturas');

            die;

            //$datainscription = $this->resetData($dataInscription, $arraykeyinscription, 'get_estudiante_inscripcion');
            //return $this->render('SieAppWebBundle:Libreta:resultlibreta.html.twig', array('datastudent' => $datastudent, 'datainscription' => $datainscription));
            return $this->render('SieAppWebBundle:EstudianteAsignatura:materianota.html.twig', array('materias' => $materias, 'datastudent' => $datastudent, 'datainscription' => $datainscription));
        }

        return $this->render('SieAppWebBundle:EstudianteAsignatura:searchlibreta.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * formateamos la informacion obteneida de la consulta
     * @param type $dataStudent
     * @param type $akey
     * @param type $key
     * @return type
     */
    private function resetData($dataStudent, $akey, $key) {
        //echo "<br>";print_r($dataStudent);echo "<br>";
        foreach ($dataStudent as $datastudent) {
            $data = str_replace(array("'", '(', ')', '"'), '', $datastudent[$key]);
            $element = explode(',', $data);
            $result = array_combine($akey, $element);
        }
        //print_R($result);echo "<br><br><br><br><br>";
        return $result;
    }

}
