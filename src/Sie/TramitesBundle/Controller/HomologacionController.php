<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
/**
 * Vista controller.
 *
 */
class HomologacionController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que muestra la cantidad de certificados tecnicos impresos de educacion alternativa a nivel nacional
    // PARAMETROS: gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecIndexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        return $this->render('SieTramitesBundle:Homologacion:cerAltIndex.html.twig', array(
                    'form' => $this->creaFormularioCertTecHomologacion('tramite_homologacion_certificacion_guarda', '', $gestionActual)->createView()
                    , 'titulo' => 'Registro Gestiones Anteriores'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

    private function creaFormularioCertTecHomologacion($routing, $cea, $gestion) {
        $especialidad = array();
        $nivel = array();
        $especialidad = Array();
        $form = '';
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('rudeal', 'text', array('label' => 'RUDEAL', 'attr' => array('placeholder' => 'RUDEAL', 'required' => true, 'onblur' => 'verificarEstudiante(this.value)', 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('nombre_student', 'text', array('label' => 'nombre', 'attr' => array('placeholder' => 'Nombre Estudiante', 'disabled' => 'disabled', 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('sies', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'Código SIE', 'onkeyup' => 'llenar_ue(this.value)', 'required' => true, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('ue', 'text', array('label' => 'C.E.A.', 'attr' => array('placeholder' => 'C.E.A', 'required' => true, 'value' => $cea, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $gestion, 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('gt')
                        ->where('gt.id >= 1900 and gt.id <= 2013')
                        ->orderBy('gt.id', 'DESC');
            },
                ))
                ->add('grado', 'choice', array('empty_value' => 'Seleccione Grado', 'attr' => array('class' => 'form-control', 'required' => true),
                    'choices' => array('1' => 'Técnico Básico', '2' => 'Técnico Auxiliar'),
                ))
                ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $gestion, 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('gt')
                        ->where('gt.id >= 1900 and gt.id <= 2012')
                        ->orderBy('gt.id', 'DESC');
            },
                ))
                ->add('nivel', 'entity', array('empty_value' => 'Seleccione Area', 'attr' => array('class' => 'form-control', 'onchange' => 'llenarEspecialidad(this.value)', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo', 'property' => 'facultad_area',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('nv')
                        ->where('nv.id >= 16')
                        ->andwhere('nv.id <= 23')
                        ->orderBy('nv.id', 'ASC');
            },
                ))
                ->add('especialidad', 'choice', array('label' => 'especialidad', 'choices' => $especialidad, 'attr' => array('class' => 'form-control', 'onchange' => 'listarNivel(this.value)', 'required' => true)))
                ->add('certificado', 'text', array('label' => 'Nro. Certificado', 'attr' => array('placeholder' => 'Nro. Certificado', 'required' => true, 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('registrar', 'submit', array('label' => ' Registrar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();

        return $form;
    }


    public function certTecGuardaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $queryId = $em->getConnection()->prepare('SELECT MAX(id) id FROM homologacion');
        $queryId->execute();
        $id = $queryId->fetchAll();
        $ids = 0;

        if (sizeof($id) == 0) {
            $ids = 1;
        } else {
            $ids = $id[0]['id'] + 1;
        }
        /*
         * se obtiene los datos del estudiante de acuerdo al RUDEAL
         */
        $queryEntidad = $em->getConnection()->prepare(
                '
                        SELECT *
                        FROM estudiante a
                        WHERE a.codigo_rude = :rudeal
                    ');
        $queryEntidad->bindValue(':rudeal', $request->get('form')['rudeal']);
        $queryEntidad->execute();
        $estudiante_id = $queryEntidad->fetchAll();
        $estudiante_id = $estudiante_id[0]['id'];
        $nombre_ue = $request->get('nombre_ue');
        switch ($request->get('form')['grado']) {
            case '1':
                $cargaHoraria = 800;
                break;
            case '2':
                $cargaHoraria = 1200;
                Break;
        }
        $queryVerificar = $em->getConnection()->prepare('SELECT * FROM homologacion WHERE rudeal = :rudeal AND grado_id = :grado AND nro_certificado =:certificado');
        $queryVerificar->bindValue(':rudeal', $request->get('form')['rudeal']);
        $queryVerificar->bindValue(':grado', (int) $request->get('form')['grado']);
        $queryVerificar->bindValue(':certificado', $request->get('form')['certificado']);
        $queryVerificar->execute();
        $ver = $queryVerificar->fetchAll();
        if (sizeof($ver) == 0) {
            $query = $em->getConnection()->prepare('
            INSERT INTO homologacion(institucioneducativa_id, institucioneducativa, gestion_id, rudeal, nivel_id, ciclo_id, grado_id, carga_horaria, nro_certificado, estudiante_id, usuario_id, fecha_reg)
            VALUES(:sie, :ue, :gestion, :rudeal, :nivel_id, :ciclo, :grado, :carga_horaria, :certificado, :estudiante, :usuario, :fecha)
                ');



            $query->bindValue(':sie', (int) $request->get('form')['sies']);
            $query->bindValue(':ue', $nombre_ue);
            $query->bindValue(':gestion', (int) $request->get('form')['gestiones']);
            $query->bindValue(':rudeal', $request->get('form')['rudeal']);
            $query->bindValue(':grado', (int) $request->get('form')['grado']);
            $query->bindValue(':nivel_id', (int) $request->get('form')['nivel']);
            $query->bindValue(':ciclo', (int) $request->get('form')['especialidad']);
            $query->bindValue(':carga_horaria', (int) $cargaHoraria);
            $query->bindValue(':estudiante', (int) $estudiante_id);
            $query->bindValue(':certificado', $request->get('form')['certificado']);
            $query->bindValue(':usuario', $id_usuario);
            $query->bindValue(':fecha', date('Y-m-d'));
            $query->execute();
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('success', array('title' => 'Exito!!', 'message' => 'Se Registro Correctamente'));
            return $this->redirectToRoute('tramite_homologacion_certificacion_index');
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estos datos ya se encuentran Registrados'));
            return $this->redirectToRoute('tramite_homologacion_certificacion_index');
        }
    }

    public function certTecListaEspecialidadAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    'SELECT
                    a.id id, a.especialidad esp
                    FROM
                    superior_especialidad_tipo a
                    INNER JOIN superior_facultad_area_tipo b ON a.superior_facultad_area_tipo_id = b.id
                    INNER JOIN superior_acreditacion_especialidad c ON c.superior_especialidad_tipo_id = a.id
                    WHERE
                    b.id  in (16,17,18,19,20,21,22,23,38) and
                    b.id = :id and a.especialidad IS NOT NULL
                    GROUP BY
                    a.id, a.especialidad
                    ORDER BY
                    a.especialidad
                    ');
            $queryEntidad->bindValue(':id', $_POST['id']);
            $queryEntidad->execute();
            $especialidad = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('especialidades' => $especialidad));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function certTecListaCeaAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    '
                        SELECT *
                        FROM institucioneducativa a
                        WHERE a.id = :sie
                    ');
            $queryEntidad->bindValue(':sie', $_POST['sies']);
            $queryEntidad->execute();
            $sie = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('ue' => $sie));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function certTecListaVerificarParticipanteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare(                '
                        SELECT *
                        FROM estudiante a
                        WHERE a.codigo_rude = :rudeal
                    ');
        $queryEntidad->bindValue(':rudeal', $request->get('rudeal'));
        $queryEntidad->execute();
        $sie = $queryEntidad->fetchAll();
        $response = new JsonResponse();
        return $response->setData(array('students' => $sie));
    }

}
