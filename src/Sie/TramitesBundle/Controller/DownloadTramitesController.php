<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityRepository;

class DownloadTramitesController extends Controller {

    /**
     * construct function
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * build the report download pdf
     * @param Request $request
     * @return object form login
     */

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Método que generara un archivo PDF, para la declaracion jurada que sera emitida para cada estudiante.
    // PARAMETROS: codigo rude, gestion, código sie, especialidad y el nivel
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************       
    public function ddjjoneAction(Request $request, $rude, $gestion, $sie, $esp, $nivel) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $rude . '_' . $gestion . '_' . $sie . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_dj_estudiante_individual_v1_pmm.rptdesign&rude=' . $rude . '&gestion=' . $gestion . '&sie=' . $sie . '&esp='.$esp.'&nivel='.$nivel.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get DDJJJ per UE
     * @param Request $request
     * @param type $gestion
     * @param type $sie
     * @return Response
     */
    public function ddjjgroupAction(Request $request, $sie, $gestion, $especialidad, $nivel) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $sie . '_' . $gestion . '_' . $especialidad . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_ddjj_estudiantes_general_v1_pmm.rptdesign&sie=' . $sie . '&gestion=' . $gestion . '&nivel=' . $nivel . '&esp=' . $especialidad . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Método que generara un archivo PDF, para la impresion del listado de tecnico medio  que sera emitida 
    // para cada estudiante.
    // PARAMETROS: gestion, departamento.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************  
    public function imprimirListaTMAction(Request $request) {
        $gestion = $request->get('gestion');
        $depto = $request->get('departamento');
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'tecnico_medio_' . $gestion . '_'.$depto.'.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_lst_estudiante_tecnicomedio_v1_pmm.rptdesign&gestion='.$gestion.'&departamento='.$depto.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Método que generara un archivo xls, para la impresion del listado de tecnico medio  que sera emitida 
    // para cada estudiante.
    // PARAMETROS: gestion, departamento.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************  
    public function imprimirListaExcelTMAction(Request $request) {
        $gestion = $request->get('gestion');
        $depto = $request->get('departamento');

        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'tecnico_medio_' . $gestion . '.xls'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_lst_estudiante_tecnicomedio_v1_pmm.rptdesign&gestion='.$gestion.'&departamento='.$depto.'&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permitira redireccionar a la vista "buscarGestionTecnicoMedio.html.twig", enviando 
    // la variable form que es creada por la funcion "creaFormularioGestion", como tambien el envio del titulo
    // y el subtitulo.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    
    public function listadoTMAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:buscarGestionTecnicoMedio.html.twig', array(
                    'form' => $this->creaFormularioGestion('sie_tramite_buscar_gestion_tm')->createView()
                    , 'titulo' => 'Impresión Listado'
                    , 'subtitulo' => 'Técnico Medio'
        ));
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permitira crear el formulario para la vista de la declaracion jurada.
    // PARAMETROS: routing = ruta del formulario.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************  
    public function creaFormularioGestion($routing) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('gt')
                        ->where('gt.id > 2015')
                        ->orderBy('gt.id', 'DESC');
            },
                ))
                ->add('depto_tm', 'entity', array('empty_value' => 'Seleccione Departamento', 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo', 'property' => 'departamento',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('depto')
                        ->where('depto.id > 0')
                        ->orderBy('depto.id', 'ASC');
            },
                ))
                ->add('buscar_gestion', 'submit', array('label' => ' Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que proporcionara todos los estudiantes de tecnico medio, y sea enviado a la vista 
    // "ListaEstudiantesTecnicoMedio.html.twig".
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function listaEstudiantesTecnicoMedioAction(Request $request) {

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $sie = 0;
        $ges = 0;
        $identificador = 0;
        $lista = '';
        $save = $this->session->get('save');
        $em = $this->getDoctrine()->getManager();
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        if (!isset($save)) {
            return $this->redirectToRoute('sie_tramites_homepage');
        }
        $form = $request->get('form');
        $gestion = $form['gestiones'];
        $depto = $form['depto_tm'];
        $departamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $depto));

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                    select distinct  ta.estudiante, initcap(e.paterno) paterno, initcap(e.materno) materno, initcap(e.nombre) nombre,
                    e.codigo_rude, e.carnet_identidad,  e.fecha_nacimiento fecha_nacimiento, 
                    CASE
                            WHEN lt4.lugar = 'NINGUNO' THEN '' ELSE lt4.lugar
                    END depto_nacimiento ,
                    t.tramite_tipo,
                    ta.tramite_id tramite, nivel as GRADO,  se.especialidad especialidad, 
                    initcap(ie.institucioneducativa) centro, ta.institucioneducativa institucioneducativa, lt2.lugar,  lt1.lugar distrito
                    from tramite_alternativa ta 
                    INNER JOIN tramite t ON t.id = ta.tramite_id
                    inner join tramite_detalle td ON td.tramite_id = ta.tramite_id
                    inner join estudiante e on ta.estudiante  = e.id 
                    INNER JOIN institucioneducativa ie ON ie.id = ta.institucioneducativa
                    inner join superior_especialidad_tipo se ON se.id = ta.esp
                    inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo lt1 on lt1.id = jg.lugar_tipo_id_distrito
                    inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo lt3 on lt3.id = e.lugar_prov_nac_tipo_id
                    inner join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                    WHERE 
                    ta.nivel in  (3)
                    AND t.tramite_tipo in (8)
                    AND td.flujo_proceso_id = 18
                    AND td.tramite_estado_id = 1
                    AND t.gestion_id = :gestion
                    and lt2.lugar = :depto
                    order by lugar, distrito, centro, especialidad, paterno, materno, nombre");
        $query->bindValue(':gestion', $gestion);
        $query->bindValue(':depto', trim($departamento));
        $query->execute();
        $entity = $query->fetchAll();
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesTecnicoMedio.html.twig', array(
                    'estudiantes' => $entity,
                    'form' => $this->creaFormularioGestion('sie_tramite_buscar_gestion_tm')->createView(),
                    'gestion' => $gestion,
                    'departamento' => $departamento
        ));
    }



    /**
     * build the DDJJ - student report download pdf
     * @param Request $request
     * @return object form login
     */
    
    public function ddjjStudentWebAction(Request $request) {

        //get the data send to the report
        $rude = $request->get('idstudent');
        $gestion = $request->get('gestion');
        $sie = $request->get('sie');
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_Estudiante_gral_v1.rptdesign&rude=' . $rude . '&gestion=' . $gestion . '&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

}
