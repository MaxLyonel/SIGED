<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\acreditacionEspecialidad;

/**
 * Malla Curricular controller.
 *
 */
class IeducativaEquipamientoLaboratorioController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
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
        $sie = "";

        return $this->render($this->session->get('pathSystem') . ':IeducativaEquipamientoLaboratorio:index.html.twig', array(
            'form' => $this->creaFormularioBusqueda('herramienta_ieducativa_equipamiento_laboratorio_detalle',$sie)->createView()
        ));
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    public function detalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = (int)$fechaActual->format('Y');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');
        // dump($form['sie']);die;
        $sie = 0;

        if(!$form){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $sie = $form['sie'];

        $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,$gestionActual);
        if (!$entityIntitucionEducativaGestion){
            $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,($gestionActual-1));
            $gestionActual = $gestionActual-1;
        } 

        $entityInstitucionEducativaSecundariaGrados = $this->getInstitucionEducativaSecundariaGrados($sie,$gestionActual);

        $entityInstitucionEducativaEdificio = $this->getInstitucionEducativaEdificio($sie,$gestionActual);
        //dump($entityInstitucionEducativaEdificio);die;
        return $this->render($this->session->get('pathSystem') . ':IeducativaEquipamientoLaboratorio:equipamientoLaboratorio.html.twig', array(
            'form' => $this->creaFormularioBusqueda('herramienta_ieducativa_equipamiento_laboratorio_detalle',$sie)->createView()
            , 'entity' => $entityIntitucionEducativaGestion[0]
            , 'entityGrado' => $entityInstitucionEducativaSecundariaGrados
            , 'entityEdificio' => $entityInstitucionEducativaEdificio
            ,'formLaboratorio' => $this->creaFormularioLaboratorio()->createView()
        ));
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaGestion($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie." and gestion_tipo_id = ".$gestion."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaSecundariaGrados($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select institucioneducativa_id, gestion_tipo_id
            , sum(case grado_tipo_id when 1 then cantidad else 0 end) as primero
            , sum(case grado_tipo_id when 2 then cantidad else 0 end) as segundo
            , sum(case grado_tipo_id when 3 then cantidad else 0 end) as tercero
            , sum(case grado_tipo_id when 4 then cantidad else 0 end) as cuarto
            , sum(case grado_tipo_id when 5 then cantidad else 0 end) as quinto
            , sum(case grado_tipo_id when 6 then cantidad else 0 end) as sexto
            , sum(cantidad) as matricula
            from (
            SELECT iec1.grado_tipo_id AS grado_tipo_id, iec1.institucioneducativa_id, iec1.gestion_tipo_id, count(ei1.id) as cantidad
            FROM (institucioneducativa_curso iec1
            JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
            WHERE (-- (iec1.gestion_tipo_id IN ( date_part('year', current_date) )) AND 
            (iec1.nivel_tipo_id = ANY (ARRAY[13, 3])) and iec1.institucioneducativa_id = ".$sie." and iec1.gestion_tipo_id = ".$gestion." and ei1.estadomatricula_tipo_id in (4,5,10,11,55))
            GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id, grado_tipo_id 
            ) as v
            GROUP BY institucioneducativa_id, gestion_tipo_id        
        ");
        $query->execute();
        $objeto = $query->fetchAll();

        if($objeto){
            $objeto = $objeto[0];
        }
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaEdificio($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select turno_id, replace(replace(replace(replace(turno, 'M'::text, 'Mañana'::text), 'T'::text, 'Tarde'::text), 'N'::text, 'Noche'::text), '-'::text, ', '::text) AS turno,
            institucioneducativa_id, institucioneducativa, gestion_tipo_id from (
                SELECT string_agg(((vv.turno_id)::character varying)::text, '-'::text ORDER BY vv.turno_id) AS turno_id,
                string_agg(vv.turno, '-'::text ORDER BY vv.turno_id) AS turno,
                ie.id as institucioneducativa_id, ie.institucioneducativa, vv.gestion_tipo_id
                FROM ( 
                    SELECT v.turno, CASE v.turno WHEN 'M'::text THEN 1 WHEN 'T'::text THEN 2 WHEN 'N'::text THEN 3 ELSE 0 END AS turno_id, v.institucioneducativa_id, v.gestion_tipo_id
                    FROM ( 
                        SELECT unnest(string_to_array(string_agg(DISTINCT (
                            CASE tt1.abrv
                                WHEN 'MTN'::text THEN 'M-T-N'::character varying
                                WHEN '.'::text THEN ''::character varying
                                WHEN 'MN'::text THEN 'M-N'::character varying
                                ELSE tt1.abrv
                            END)::text, '-'::text), '-'::text, ''::text)) AS turno,
                        iec1.institucioneducativa_id, iec1.gestion_tipo_id
                        FROM ((institucioneducativa_curso iec1
                        JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
                        JOIN turno_tipo tt1 ON ((tt1.id = iec1.turno_tipo_id)))
                        WHERE ( (iec1.gestion_tipo_id = ".$gestion.") AND (iec1.institucioneducativa_id IN ( 
                                select id from institucioneducativa 
                                where le_juridicciongeografica_id in (select le_juridicciongeografica_id from institucioneducativa where id = ".$sie.")
                                and institucioneducativa_acreditacion_tipo_id = 1 and estadoinstitucion_tipo_id = 10 )) AND 
                        (iec1.nivel_tipo_id = ANY (ARRAY[11, 12, 13, 1, 2, 3])))
                        GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id) v
                    GROUP BY v.institucioneducativa_id, v.gestion_tipo_id, v.turno
                    ORDER BY
                        CASE v.turno
                                WHEN 'M'::text THEN 1
                                WHEN 'T'::text THEN 2
                                WHEN 'N'::text THEN 3
                                ELSE 0
                        END
                ) vv
                JOIN institucioneducativa as ie on ie.id = vv.institucioneducativa_id
                where vv.institucioneducativa_id not in (".$sie.")
                GROUP BY ie.id, ie.institucioneducativa, vv.gestion_tipo_id
            ) as vvv
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    private function creaFormularioBusqueda($routing, $sie) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'Código S.I.E.', 'attr' => array('value' => $sie, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Código de institución educativa', 'style' => 'text-transform:uppercase')))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    private function creaFormularioLaboratorio()
    { 
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_registro'))
            ->add('constructor',
                'choice',  
                array('label' => '',
                    'choices' => array( 
                        '1' => 'UPRE - PROGRAMA BOLIVIA CAMBIA, EVO CUMPLE'
                        ,'2' => 'MUNICIPIO'
                        ,'3' => 'ONG'
                        ,'4' => 'OTRO'
                    ),
            ))
            ->add('cantidadAmbiente', 'number', array('label' => 'Cantidad de ambientes', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('cantidadPiletaLavadero', 'number', array('label' => 'Cantidad de de piletas y lavaderos', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('cantidadTomaCorriente', 'number', array('label' => 'Cantidad de toma corrientes', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('gestionEquipado', 'number', array('label' => 'Año en la que fue equipado', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '4', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Año', 'style' => 'text-transform:uppercase')))
            ->add('institucionEquipo', 'text', array('label' => 'Institución que equipo', 'attr' => array('value' => '', 'class' => 'form-control', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Institución', 'style' => 'text-transform:uppercase')))
            ->add('cantidadEquipo', 'number', array('label' => 'Cantidad de equipos', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '5', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('nombreAlcalde', 'text', array('label' => 'Nombre del alcalde', 'attr' => array('value' => '', 'class' => 'form-control',  'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Nombre', 'style' => 'text-transform:uppercase')))
            ->add('telefonoAlcalde', 'number', array('label' => 'Telefono del alcalde', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9]', 'maxlength' => '8', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('foto61', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true, 'data_class' => null)) 
            ->add('foto62', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true, 'data_class' => null)) 
            ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
            ->getForm();
        return $form;        
    }

    private function craeteformsearch() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('herramienta_inscription_find_inscription'))
                        ->add('rude', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '20', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => array('2016' => '2016', '2015' => '2015'), 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }



    public function findInscriptionAction(Request $request) {
        //get the data send
        $form = $request->get('form');
        //do the conexion
        $em = $this->getDoctrine()->getManager();
        //validate if the student has been registered on nivel=13 and (grado = 6 or grado = 5) 
        $objInscriptionTec = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionStudentTecnica($form['rude'], $form['gestion']);
        if (!$objInscriptionTec) {
            $message = 'Estudiante cuenta con  inscripción en Unidad Educativa Técnica';
            $this->addFlash('warinscriptiontec', $message);
            return $this->redirectToRoute('herramienta_inscription_tecnica_index');
        }

        return $this->render($this->session->get('pathSystem') . ':InscriptionTec:findInscription.html.twig', array(
                    'form' => $this->createInscriptionForm()->createView()
        ));

        dump($objInscriptionTec);
        die;
    }

    /**
     * create form to do the inscription 
     * @return type form inscription
     */
    private function createInscriptionForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('herramienta_inscription_save_inscription'))
                        ->add('tecnicaProd', 'text')
                        ->add('especialidad', 'text')
                        ->add('nivel', 'text')
                        ->add('paralelo', 'text')
                        ->add('turno', 'text')
                        ->add('modalidadEnsenanza')
                        ->getForm();
    }

}
