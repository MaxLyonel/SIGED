<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Documento;
use Doctrine\ORM\EntityRepository;

class TramiteDeclaracionJuradaController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * declaracion jurada Index 
     * @param Request $request
     * @return type
     */

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Método que muestra la vista "find.html.twig" enviando la variable form creada por la funcion 
    // "craeteformsearchsie".
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':TramiteDeclaracionJurada:find.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Método que crea los campos del formulario.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************  
    private function craeteformsearchsie() {
        $especialidad = array();
        $nivel = array();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('tramite_declaracion_jurada_find'))
                ->add('gestiones', 'entity', array('empty_value' => 'Gestión', 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'limpiar_dato_sie()'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('gt')
                        ->where('gt.id > 2014')
                        ->orderBy('gt.id', 'DESC');
            },
                ))
                ->add('sies', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'C.E.A', 'required' => true, 'class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('especialidad', 'choice', array('label' => 'especialidad', 'choices' => $especialidad, 'attr' => array('class' => 'form-control', 'onchange' => 'listarNivel(this.value)', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'nivel', 'choices' => $nivel, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'buscarBoton(this.value,1)')))
                ->add('search', 'submit', array('label' => ' Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que redireccionara a la vista "Estudiantes.html.twig" enviando todo los datos del estudiante
    // el nombre de la unidad educativa, gestion, la especialidad, el nivel y la variable form.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************  
    public function ListarEstudiantesAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        /*
         * Se obtiene el nombre de la especialidad con el codigo obtenido mediante la variable request
         */
        $query = $em->getConnection()->prepare("
                Select DISTINCT(especialidad), especialidad_id 
                from vm_estudiantes_tecnica_alternativa 
                WHERE especialidad_id = :esp::INT 
                GROUP BY especialidad, especialidad_id
                ");
        $query->bindValue(':esp', $form['especialidad']);
        $query->execute();
        $entityEspe = $query->fetchAll();
        /*
         * Se obtiene el nombre del centro de educacion alternativa con el codigo obtenido mediante la variable request
         */
        $instEdu = $em->getConnection()->prepare(
                'select * from institucioneducativa where id = :sie'
        );
        $instEdu->bindValue(':sie', $form['sies']);
        $instEdu->execute();
        $institucionEducativa = $instEdu->fetchAll();
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->add('noticeddjj', 'No existe Unidad Educativa');
                return $this->redirectToRoute('tramite_declaracion_jurada_index');
            }
            /*
             * se obtiene los datos de los estudiantes filtrados por codigo sie, gestion, especialidad y nivel
             */
        $estudiantes = $this->getBachilleresPerSie($form['sies'], $form['gestiones'], $form['especialidad'], $form['nivel']);
        $especialidad = $entityEspe[0]['especialidad'];
        $esp_id = $entityEspe[0]['especialidad_id'];
        $ie = $institucionEducativa[0];
        $gestion = $form['gestiones'];
        $nivel= $form['nivel'];
        return $this->render('SieTramitesBundle:TramiteDeclaracionJurada:Estudiantes.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView(),
                    'bachilleres' => $estudiantes,
                    'unidadEducativa' => $ie,
                    'gestionSelected' => $gestion,
                    'esp' => $especialidad,
                    'esp_id' => $esp_id,
                    'nivel'=>$nivel
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que seleccionara todos los estudiantes 
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************      

    private function getBachilleresPerSie($sie, $gestion, $especialidad, $nivel) {
        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare('
                SELECT row_number() over() as item, a.paterno, a.materno, a.nombre, a.codigo_rude, a.fecha_nacimiento, a.depto_nacimiento, ie.id as insteduId, a.estudiante_id as studentId, 
                a.gestion_tipo_id as gestionTipo, a.especialidad, ie.institucioneducativa, a.especialidad_id, a.grado_id
                FROM vm_estudiantes_tecnica_alternativa a
                inner join institucioneducativa ie on ie.id = a.institucioneducativa_id
                WHERE 
                institucioneducativa_id=  :sie and
                grado_id= :nivel AND especialidad_id= :esp and gestion_tipo_id = :gestion
                GROUP BY a.paterno, a.materno, a.nombre, a.codigo_rude, a.fecha_nacimiento, a.depto_nacimiento, ie.id, a.estudiante_id, 
                a.gestion_tipo_id, a.especialidad, ie.institucioneducativa, a.especialidad_id, a.grado_id
                ORDER BY paterno, materno, nombre
                ');
        $queryEntidad->bindValue(':sie',$sie);
        $queryEntidad->bindValue(':gestion',$gestion);
        $queryEntidad->bindValue(':esp',$especialidad);
        $queryEntidad->bindValue(':nivel',$nivel);
        try {
            $queryEntidad->execute();
            $nivel_select = $queryEntidad->fetchAll();
            return $nivel_select;
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
