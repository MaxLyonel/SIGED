<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\PersonaType;
use \Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Helper\findStudentController;
use Sie\AppWebBundle\Controller\DefaultController;

/**
 * lookforLibreta controller.
 *
 */
class LookforLibretaController extends Controller {

    /**
     * form to find the stutdent's users
     * @param Request $request
     * @return type
     */
    public function searchdatalibretaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        $agestion = array('2014' => '2014', '2015' => '2015');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('find_data_libreta'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
                ->add('gestion', 'choice', array("mapped" => false, 'choices' => $agestion, 'required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * find the libreta of the student
     * @param Request $request
     * @return object libreta, user, inst. educativa data
     */
    public function findlibretaAction(Request $request) {

        $session = new Session();
        $form = $request->get('form');


        $em = $this->getDoctrine()->getManager();
        //$sesion = $request->getSession();

        /* try to call a new class
          $infoStudent = new findStudentController();
          print_r($infoStudent->getDataStudent($form['codigoRude']));
         */

        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($form['codigoRude']);
        if ($entities) {

            $sesion = $request->getSession();
            // verificar la tuicion del apoderado sobre el estudiante
            $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneBy(array('personaApoderado' => $sesion->get('personaId'), 'personaEstudiante' => $entities->getId(), 'esactivo' => 't'));
            if (!$apoderado) {
                $session->getFlashBag()->add('noTuicion', 'No tiene tuicion sobre los datos del estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView()));
            }

            //get the student data
            $query = $em->getConnection()->prepare('SELECT get_estudiante_datos (:rude::VARCHAR)');
            $query->bindValue(':rude', $form['codigoRude']);
            $query->execute();
            $dataStudent = $query->fetchAll();
            //verificamos si hay datos del estudiante
            if (!$dataStudent) {
                $session->getFlashBag()->add('notice', 'No existe información personal del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView()));
            }
            $arraykeystudent = array('codigo_rude', 'carnet_identidad', 'paterno', 'materno', 'nombre', 'genero', 'lugar_nacimiento', 'oficialia', 'libro', 'partida', 'folio', 'grupo_sanguineo', 'fecha_nacimiento', 'estado_civil', 'pasaporte');
            //formateamos el resultado de la consulta datos estudiante
            $datastudent = $this->resetData($dataStudent, $arraykeystudent, 'get_estudiante_datos');

            //get the inscription data for this student
            $query = $em->getConnection()->prepare('SELECT get_estudiante_inscripcion (:estudiante_id::INT)');
            $query->bindValue(':estudiante_id', $entities->getId());
            $query->execute();
            $dataInscription = $query->fetchAll();
            //verificamos si hay datos del estudiante
            if (!$dataInscription) {
                $session->getFlashBag()->add('notice', 'No existe información de Inscripción del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView()));
            }
            $arraykeyinscription = array('estudiante_id', 'estudiante_inscripcion_id', 'grado_tipo_id', 'grado', 'paralelo_tipo_id', 'paralelo', 'gestion', 'ciclo_tipo_id', 'ciclo', 'dependencia_tipo_id', 'dependencia', 'orgcurricula', 'institucioneducativa_tipo_id', 'institucioneducativa_tipo', 'convenio_tipo_id', 'convenio', 'institucioneducativa_id', 'institucioneducativa', 'nivel_tipo_id', 'nivel', 'periodo_tipo_id', 'periodo', 'turno_tipo_id', 'turno', 'sucursal_tipo', 'sucursal', 'fecha_inscripcion', 'observacion');
            //formateamos el resultado de la consulta datos inscripcion
            $datainscription = $this->resetData($dataInscription, $arraykeyinscription, 'get_estudiante_inscripcion');

            //get the notas data for this student
            $query = $em->getConnection()->prepare('SELECT get_estudiante_libreta_nota_json(:rude::VARCHAR, :sie::INT, :gestion::INT, :nivel::INT, :grado::INT, :periodo::INT, :turno::INT, :sucursal::SMALLINT )');
            $query->bindValue(':rude', $form['codigoRude']);
            $query->bindValue(':sie', $datainscription['institucioneducativa_id']);
            $query->bindValue(':gestion', $form['gestion']);
            $query->bindValue(':nivel', $datainscription['nivel_tipo_id']);
            $query->bindValue(':grado', $datainscription['grado_tipo_id']);
            $query->bindValue(':periodo', $datainscription['periodo_tipo_id']);
            $query->bindValue(':turno', $datainscription['turno_tipo_id']);
            $query->bindValue(':sucursal', $datainscription['sucursal_tipo']);
            $query->execute();
            $notaStudent = $query->fetchAll();

            if (!$notaStudent) {
                $session->getFlashBag()->add('notice', 'No existe información de Inscripción del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView(),));
            }
            //formateamos el resultado de la consulta notas
            $notastudent = $this->resetDataArray($notaStudent, 'get_estudiante_libreta_nota_json');

            //obtenemos la nota cualitativa del estudiante
            $query = $em->getConnection()->prepare('SELECT get_estudiante_libreta_cualitativo_json(:rude::VARCHAR, :sie::INT, :gestion::INT, :nivel::INT, :grado::INT, :periodo::INT, :turno::INT, :sucursal::SMALLINT )');
            //get the notas data for this student
            $query->bindValue(':rude', $form['codigoRude']);
            $query->bindValue(':sie', $datainscription['institucioneducativa_id']);
            $query->bindValue(':gestion', $form['gestion']);
            $query->bindValue(':nivel', $datainscription['nivel_tipo_id']);
            $query->bindValue(':grado', $datainscription['grado_tipo_id']);
            $query->bindValue(':periodo', $datainscription['periodo_tipo_id']);
            $query->bindValue(':turno', $datainscription['turno_tipo_id']);
            $query->bindValue(':sucursal', $datainscription['sucursal_tipo']);
            $query->execute();
            $notaCualitativoStudent = $query->fetchAll();

            if (!$notaCualitativoStudent) {
                $session->getFlashBag()->add('notice', 'No existe información cualitativa del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView(),));
            }
            //formateamos el resultado de la consulta notas
            $notacualitativostudent = $this->getNotaCualitativa($notaCualitativoStudent, 'get_estudiante_libreta_cualitativo_json');

            return $this->render('SieAppWebBundle:Libreta:resultlibreta.html.twig', array('datastudent' => $datastudent, 'datainscription' => $datainscription, 'notastudent' => $notastudent, 'notacualitativostudent' => $notacualitativostudent));
        }
        $session->getFlashBag()->add('notice', 'El Rude es invalido o Estudiante no Existe');
        return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView(),));
    }

    /**
     * reseteamos la informacion obtenida de las consultas a la DB
     * @param type $dataStudent
     * @param type $akey
     * @param type $key
     * @return array reset info
     */
    private function resetData($dataStudent, $akey, $key) {
        foreach ($dataStudent as $datastudent) {
            $data = str_replace(array('(', ')', '"'), '', $datastudent[$key]);
            $element = explode(',', $data);
            $result = array_combine($akey, $element);
        }
        return $result;
    }

    /**
     * get the notas values into an array NOTAS
     * @param type $datas
     * @param type $key
     * @return array nota
     */
    private function resetDataArray($datas, $key) {
        $result = array();
        foreach ($datas as $k => $val) {
            $result[] = json_decode($val[$key], true);
        }
        $nota = array();
        foreach ($result as $key => $value) {
            $nota[$value['asignatura']][$value['nota_orden']] = $value['nota_cuantitativa'];
        }
        return $nota;
    }

    /**
     * get the nota cuantitativa
     * @param type $data
     * @param type $key
     * @return type
     */
    private function getNotaCualitativa($data, $key) {
        $result = array();
        foreach ($data as $k => $val) {
            $result[] = json_decode($val[$key], true);
        }
        return $result;
    }

}
