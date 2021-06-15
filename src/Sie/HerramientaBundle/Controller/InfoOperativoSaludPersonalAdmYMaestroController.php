<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\VerificarPersonaSegipType;
use Sie\AppWebBundle\Form\PersonaDatosType;

use Sie\AppWebBundle\Entity\MaestroInscripcionEstadosalud;
use Sie\AppWebBundle\Entity\EstadosaludTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaControlOperativoMenus;
/**
 * EstudianteInscripcion controller.
 *
 */
class InfoOperativoSaludPersonalAdmYMaestroController extends Controller {

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
     * list of request
     *
     */
    public function indexAction(Request $request) {

        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');

            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('herramienta_info_maestro_tsie_index'));
                }
                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
            }

            /*
             * verificamos si tiene tuicion
             */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucion = $form['sie'];
                $gestion = $form['gestion'];
                // creamos variables de sesion de la institucion educativa y gestion
                $request->getSession()->set('idInstitucion', $institucion);
                $request->getSession()->set('idGestion', $gestion);
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('herramienta_info_maestro_tsie_index'));
                }
                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
            }
        } else {
            $institucion = $request->getSession()->get('idInstitucion');
            $gestion = $request->getSession()->get('idGestion');
        }

        $institucionregular = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $institucion, 'institucioneducativaTipo' => 1));

        // if(!is_object($institucionregular)){
        //     $this->get('session')->getFlashBag()->add('noTuicion', 'La Unidad Educativa no corresponde al Subsistema de Educación Regular');
        //         if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
        //             return $this->redirect($this->generateUrl('herramienta_info_maestro_tsie_index'));
        //         }
        //         return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        // }

        /*
         * lista de ADMINISTRATIVOS registrados en la unidad educativa
         */
        //$cargos = $em->getRepository('SieAppWebBundle:CargoTipo')->findBy(array('rolTipo'=>2));
        $queryCargos = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
            WHERE ct.id NOT IN (:id) ORDER BY ct.id')
            ->setParameter('id', array(0,70));

        $cargos = $queryCargos->getResult();
        $cargosArrayAdministrativos = array();

        foreach ($cargos as $c) {
            $cargosArrayAdministrativos[$c->getId()] = $c->getId();
        }

        /*
         * lista de maestros registrados en la unidad educativa
         */
        //$cargos = $em->getRepository('SieAppWebBundle:CargoTipo')->findBy(array('rolTipo'=>2));
        $queryCargos = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
            WHERE ct.id IN (:id) ORDER BY ct.id')
            ->setParameter('id', array(0,70));

        $cargos = $queryCargos->getResult();
        $cargosArrayMaestros = array();

        foreach ($cargos as $c) {
            $cargosArrayMaestros[$c->getId()] = $c->getId();
        }


        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

            //        $query = $repository->createQueryBuilder('icom')
            //                ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, ft.formacion, icom.esVigenteMaestro')
            //                ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'icom.maestroInscripcion = mi.id')
            //                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            //                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            //                ->where('mi.institucioneducativa = :idInstitucion')
            //                ->andWhere('mi.gestionTipo = :gestion')
            //                ->andWhere('mi.cargoTipo IN (:cargos)')
            //                ->setParameter('idInstitucion', $institucion)
            //                ->setParameter('gestion', $gestion)
            //                ->setParameter('cargos', $cargosArray)
            //                ->distinct()
            //                ->orderBy('p.paterno')
            //                ->addOrderBy('p.materno')
            //                ->addOrderBy('p.nombre')
            //                ->getQuery();
        
        $cargosArray = array_merge($cargosArrayAdministrativos, $cargosArrayMaestros);

        $query = $repository->createQueryBuilder('mi')
                ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion,ct.cargo ,ct.id cargoId,est.estadosalud')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'WITH', 'mi.cargoTipo = ct.id')

                ->leftJoin('SieAppWebBundle:MaestroInscripcionEstadosalud', 'mies', 'WITH', 'mi.id = mies.maestroInscripcion')
                ->leftJoin('SieAppWebBundle:EstadoSaludTipo', 'est', 'WITH', 'mies.estadosaludTipo = est.id')

                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->distinct()
                ->orderBy('ct.id','asc')
                ->addOrderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();
        $maestro = $query->getResult();

/*************************************AQUI*****************************************/
        $maestro=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $c=implode(',',array_values($cargosArray));
        $query = '
            SELECT distinct p.id as "perId", p.carnet, p.paterno, p.materno, p.nombre, mi.id as "miId", mi.fecha_registro, mi.fecha_modificacion, mi.es_vigente_administrativo, ft.formacion,ct.cargo ,ct.id as "cargoId",
            (
                SELECT string_agg(concat_ws(\',\',mies0.estadosalud_tipo_id,est0.estadosalud,to_char(mies0.fecha, \'DD-MM-YYYY\'),to_char(mies0.fecha, \'DD-MM-YYYY\')),\'|\')
                from maestro_inscripcion_estadosalud mies0
                INNER JOIN estadosalud_tipo est0 on mies0.estadosalud_tipo_id=est0.id
                where maestro_inscripcion_id=mi.id
                or persona_id=p.id --///////////////////AQUI CAMBIAR AND POR OR PARA MNOSTRAR DE OTRAS GESTIONES Y UNIDADES EDUCATIVAS
            ) as "detalleEstadoSalud"

            FROM maestro_inscripcion mi
            inner join persona  p  on mi.persona_id = p.id
            inner join formacion_tipo  ft  on mi.formacion_tipo_id = ft.id
            inner join cargo_tipo  ct  on mi.cargo_tipo_id = ct.id
            where mi.institucioneducativa_id = ?
            and mi.gestion_tipo_id = ?
            and mi.cargo_tipo_id IN ('.$c.')
            ORDER BY ct.id
        ';
        $stmt = $db->prepare($query);
        $params = array($institucion,$gestion);
        $stmt->execute($params);
        $maestro=$stmt->fetchAll();
        
/*************************************AQUI*****************************************/

        $query = $repository->createQueryBuilder('mi')
            ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, ft.formacion')
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->leftJoin('SieAppWebBundle:MaestroInscripcionIdioma', 'maii', 'WITH', 'maii.maestroInscripcion = mi.id')
            ->where('mi.institucioneducativa = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo IN (:cargos)')
            ->andWhere('maii.id IS NULL')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->distinct()
            ->orderBy('p.paterno')
            ->addOrderBy('p.materno')
            ->addOrderBy('p.nombre')
            ->getQuery();

        $maestro_no_idioma = $query->getResult();

        $arrayNoIdioma = array();
        foreach ($maestro_no_idioma as $key => $value) {
            $arrayNoIdioma[$value['perId']] = $value['perId'];
        }

        $query = $repository->createQueryBuilder('mi')
            ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion')
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            ->where('mi.institucioneducativa = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo IN (:cargos)')
            ->andWhere('p.generoTipo = :genero')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->setParameter('genero', 3)
            ->distinct()
            ->orderBy('p.paterno')
            ->addOrderBy('p.materno')
            ->addOrderBy('p.nombre')
            ->getQuery();

        $maestro_no_genero = $query->getResult();

        $arrayNoGenero = array();
        foreach ($maestro_no_genero as $key => $value) {
            $arrayNoGenero[$value['perId']] = $value['perId'];
        }

        /*
         * obtenemos datos de la unidad educativa
         */
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2);
        $ueplena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativaId' => $institucion->getId(), 'institucioneducativaHumanisticoTecnicoTipo' => 1));

        if($request->getSession()->get('currentyear')<2020) {
            $consol_gest_pasada = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim4' => '1'));
            $consol_gest_pasada2 = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim4' => '2'));
            $consol_gest_pasada3 = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim4' => '3'));
        } else {
            $consol_gest_pasada = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim3' => '1'));
            $consol_gest_pasada2 = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim3' => '2'));
            $consol_gest_pasada3 = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion , 'unidadEducativa' => $institucion, 'bim3' => '3'));
        }
        
        if(!($consol_gest_pasada or $consol_gest_pasada2 or $consol_gest_pasada3)){
            $activar_acciones = true;
        }

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));
        
        $activar_acciones = true;
        if($validacion_personal_aux){
            $activar_acciones = false;
        }

        $descarga_archivo_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo, 'institucioneducativaOperativoLogTipo' => 1));
        
        $habilitar_ddjj = true;
        if($descarga_archivo_aux){
            $habilitar_ddjj = false;
        }

        $control_operativo_menus = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo));
        if(!$ueplena){
            if($control_operativo_menus) {
                if($control_operativo_menus->getEstadoMenu() == 1) {
                    return $this->redirect($this->generateUrl('sie_app_web_close_modules_index', array(
                        'sie' => $institucion->getId(),
                        'gestion' => $gestion,
                        'operativo' => $operativo
                    )));
                }
            }
        }

        return $this->render($this->session->get('pathSystem') . ':InfoOperativoSaludAdministrivoMaestro:index.html.twig', array(
                'maestro' => $maestro,
                'institucion' => $institucion,
                'gestion' => $gestion,
                'maestro_no_idioma' => $arrayNoIdioma,
                'maestro_no_genero' => $arrayNoGenero,
                'activar_acciones' => $activar_acciones,
                'habilitar_ddjj' => $habilitar_ddjj,

                'operativoSalud' => $this->verificarEstadoOperativoSaludo($institucion->getId(),$gestion)
        ));
    }


    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInstitucion, $gestion, $idPersona) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.id NOT IN (:id) ORDER BY ct.id')
            ->setParameter('id', array(0, 86, 993, 650, 992, 994, 120, 100, 1000, 651, 14));

        $cargos = $query->getResult();

        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 5, 12))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $fechaInicio = "01-01-".$gestion;
        $fechaFin = "31-12-".$gestion;

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_personal_adm_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $idPersona))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion', 'text', array('label' => 'Denominativo del título del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '45', 'pattern' => '[A-Za-z0-9\Ññ ]{0,45}')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista'))
                ->add('item', 'text', array('label' => 'Número de Item', 'data' => '0', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => $em->getReference('SieAppWebBundle:IdiomaMaterno', 97), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille'))
                ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación (ej.: 01-01-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación' , 'maxlength' => 10, 'required' => true)))
                ->add('fechaFin', 'text', array('label' => 'Fecha fin de asignación (ej.: 31-12-2020)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha fin de asignación' , 'maxlength' => 10, 'required' => true)))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /**
     * Crea un formulario para buscar una persona por C.I.
     *
     */
    private function searchForm() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_personal_adm_result'))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias', 'attr' => array('class' => 'btn btn-md btn-facebook')))
                ->getForm();
        return $form;
    }


    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_personal_adm_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }



    /**
     * Esta funcion edita el estado de saludo de una persona, en la tabla 'maestro_inscripcion_estadosalud'
     *
     * @return 
     * @author lnina
     **/
    public function editarEstadoSaludAction(Request $request)
    {
        $esAjax=$request->isXmlHttpRequest();
        $form=$request->request->all();

        $request_personalInscripcion = $request->get('personalId');
        $request_personalInscripcion = filter_var($request_personalInscripcion,FILTER_SANITIZE_NUMBER_INT);
        $request_personalInscripcion = is_numeric($request_personalInscripcion)?$request_personalInscripcion:-1;

        $request_gestion = $request->get('gestion');
        $request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
        $request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

        $request_persona = $request->get('persona');
        $request_persona = filter_var($request_persona,FILTER_SANITIZE_NUMBER_INT);
        $request_persona = is_numeric($request_persona)?$request_persona:-1;

        $request_cargo = $request->get('cargo');
        $request_cargo = filter_var($request_cargo,FILTER_SANITIZE_NUMBER_INT);
        $request_cargo = is_numeric($request_cargo)?$request_cargo:-1;


        if($esAjax && $request_personalInscripcion>0 && $request_persona>0 && $request_cargo>=0)
        {
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$request_persona));
            /*
            $repository = $em->getRepository('SieAppWebBundle:EstadosaludTipo');
            $query = $repository->createQueryBuilder('es')
                ->where('es.id <> :id')
                ->andWhere('es.esactivo = :esactivo')
                ->setParameter('id', 4)
                ->setParameter('esactivo', 't')
                ->getQuery();
            $estadosSalud = $query->getResult();
            */
            $estadosSalud = $em->getRepository('SieAppWebBundle:EstadosaludTipo')->findBy(array('esactivo' => 't'));

            if($estadosSalud==null)
            {
                $estadosSalud=array();
            }

            $detalleSalud=array();
            $query = '
                    SELECT mies0.id as "mi_id",*
                    from maestro_inscripcion_estadosalud mies0
                    INNER JOIN estadosalud_tipo est0 on mies0.estadosalud_tipo_id=est0.id
                    where 
                    maestro_inscripcion_id=? 
                    or persona_id=? 
            ';///////////////////AQUI CAMBIAR AND POR OR PARA MNOSTRAR DE OTRAS GESTIONES Y UNIDADES EDUCATIVAS
            $stmt = $db->prepare($query);
            $params = array($request_personalInscripcion,$request_persona);
            $stmt->execute($params);
            $detalleSalud=$stmt->fetchAll();

            $estadosaludTipoDeceso  = $em->getRepository('SieAppWebBundle:EstadosaludTipo')->findOneBy(array('id'=>3));
            $maestroInscripcion     = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id'=>$request_personalInscripcion));
            $persona                = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$request_persona));

            $maestroDeceso  = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findBy(array(
                        //'maestroInscripcion'=>$maestroInscripcion,
                        'persona'=>$persona,
                        'estadosaludTipo'=> $estadosaludTipoDeceso
            ));


            return $this->render($this->session->get('pathSystem').':InfoOperativoSaludAdministrivoMaestro:estadoSalud.html.twig',array(
                'estadosSalud'=>$estadosSalud,
                'inscripcion_id' => $request_personalInscripcion,
                'gestion' => $request_gestion,
                'persona_id'=>$request_persona,
                'cargoTipo_id'=>$request_cargo,
                'persona'=>$persona,
                'detalleSalud'=>$detalleSalud,
                'deceso' => $maestroDeceso
            ));
        }
        else
        {
            $data=null;
            $status = 404;
            $msj = 'La dirección solicitada no existe.';
            $response = new JsonResponse($data,$status);
            $response->headers->set('Content-Type', 'application/json');
            return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
        }
    }

    /**
     * Esta funcion actualiza el estado de salud de una persona, en la tabla 'maestro_inscripcion_estadosalud'
     * ESTA ES LA VERSIN ANTERIRO  POR SIA CASO LA DEJO ACA
     * @return void
     * @author lnina
     **
    public function actualizarEstadoSaludAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $esAjax=$request->isXmlHttpRequest();
        $form=$request->request->all();

        $request_inscription = $request->get('request_inscription');
        $request_inscription = filter_var($request_inscription,FILTER_SANITIZE_NUMBER_INT);
        $request_inscription = is_numeric($request_inscription)?$request_inscription:-1;

        $request_estadoSalud = $request->get('request_estado_salud');
        $request_estadoSalud = filter_var($request_estadoSalud,FILTER_SANITIZE_NUMBER_INT);
        $request_estadoSalud = is_numeric($request_estadoSalud)?$request_estadoSalud:-1;

        $request_gestion = $request->get('request_gestion');
        $request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
        $request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

        $request_gestion_2 = $request->get('request_gestion_2');
        $request_gestion_2 = filter_var($request_gestion_2,FILTER_SANITIZE_NUMBER_INT);
        $request_gestion_2 = is_numeric($request_gestion_2)?$request_gestion_2:-1;

        $request_persona = $request->get('request_persona');
        $request_persona = filter_var($request_persona,FILTER_SANITIZE_NUMBER_INT);
        $request_persona = is_numeric($request_persona)?$request_persona:-1;

        $request_cargo = $request->get('request_cargo');
        $request_cargo = filter_var($request_cargo,FILTER_SANITIZE_NUMBER_INT);
        $request_cargo = is_numeric($request_cargo)?$request_cargo:-1;

        $request_fecha_1 = $request->get('request_fecha_1');
        $request_fecha_1 = filter_var($request_fecha_1,FILTER_SANITIZE_STRING);

        $request_fecha_2 = $request->get('request_fecha_2');
        $request_fecha_2 = filter_var($request_fecha_2,FILTER_SANITIZE_STRING);

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        $existe = false;
        if($esAjax && $request_inscription >0 && $request_estadoSalud >=0 )
        {
            $em = $this->getDoctrine()->getManager();
            
            $estadosaludTipo    = $em->getRepository('SieAppWebBundle:EstadosaludTipo')->findOneBy(array('id'=>$request_estadoSalud));
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id'=>$request_inscription,'gestionTipo'=>$request_gestion));
            $persona            = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$request_persona));
            $cargo              = $em->getRepository('SieAppWebBundle:CargoTipo')->findOneBy(array('id'=>$request_cargo));

            if($estadosaludTipo && $maestroInscripcion && $persona && $cargo)
            {
                
                //$repository = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud');
                //$query = $repository->createQueryBuilder('mi')
                //    ->where('mi.maestroInscripcion = :maestroInscripcion')
                //    //->andWhere('mi.estadosaludTipo <> :estadosaludTipo')
                //    ->setParameter('maestroInscripcion', $maestroInscripcion->getId())
                //    //->setParameter('estadosaludTipo', 4)
                //    ->setMaxResults(1)
                //    ->getQuery();
                //$maestroInscripcionEstadosalud = $query->getOneOrNullResult();
                
               
                $maestroInscripcionEstadosalud = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneBy(array(
                    'maestroInscripcion'=>$maestroInscripcion,
                    'persona'=>$persona,
                    'cargoTipo' => $cargo,
                    'estadosaludTipo'=> $estadosaludTipo
                ));
                if($maestroInscripcionEstadosalud)
                    $existe=true;
                
                if($existe==false || in_array($request_estadoSalud,[1,2]))
                {
                    $maestroInscripcionEstadosalud=null;
                    $tmpmaestroInscripcionEstadosalud=$maestroInscripcionEstadosalud;
                    if($maestroInscripcionEstadosalud==null)//Si no existe lo creamos
                        $maestroInscripcionEstadosalud = new MaestroInscripcionEstadosalud();

                    $maestroInscripcionEstadosalud->setEstadosaludTipo($estadosaludTipo);
                    $maestroInscripcionEstadosalud->setMaestroInscripcion($maestroInscripcion);
                    //if($tmpmaestroInscripcionEstadosalud==null)
                        $maestroInscripcionEstadosalud->setFechaModificacion(new \DateTime('now'));

                    $maestroInscripcionEstadosalud->setCargoTipo($cargo);
                    $maestroInscripcionEstadosalud->setPersona($persona);
                    
                    if($request_estadoSalud==2)//este es el estado enfermo, por lo tanto tendra dos fechas
                    {
                        list($d1,$m1,$y1)=explode('-',$request_fecha_1);
                        $maestroInscripcionEstadosalud->setFecha(new \DateTime($y1.'-'.$m1.'-'.$d1));

                        list($d2,$m2,$y2)=explode('-',$request_fecha_2);
                        //$maestroInscripcionEstadosalud->setFecha2(new \DateTime($y2.'-'.$m2.'-'.$d2));
                    }
                    else
                    {
                        if(in_array($request_estadoSalud,[0,4]))
                        {
                            $maestroInscripcionEstadosalud->setFecha(new \DateTime('now'));
                        }
                        else
                        {
                            list($d1,$m1,$y1)=explode('-',$request_fecha_1);
                            $maestroInscripcionEstadosalud->setFecha(new \DateTime($y1.'-'.$m1.'-'.$d1));
                        }
                    }

                    $em->persist($maestroInscripcionEstadosalud);
                    $em->flush();
                }

                //$data=$this->getDetalleRegistroMaestroInscripcion($maestroInscripcion);
                $data='Ok';
                $status= 200;
                $msj='Los datos fueron actualizados correctamente';
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='Ocurrio un error, los datos enviados no son correctos, por favor vuelva a intentarlo';
            }
        }
        else
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }
    */


    public function actualizarEstadoSaludAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $esAjax=$request->isXmlHttpRequest();
        $form=$request->request->all();

        $request_inscription = $request->get('request_inscription');
        $request_inscription = filter_var($request_inscription,FILTER_SANITIZE_NUMBER_INT);
        $request_inscription = is_numeric($request_inscription)?$request_inscription:-1;

        $request_gestion = $request->get('request_gestion');
        $request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
        $request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

        $request_persona = $request->get('request_persona');
        $request_persona = filter_var($request_persona,FILTER_SANITIZE_NUMBER_INT);
        $request_persona = is_numeric($request_persona)?$request_persona:-1;

        $request_cargo = $request->get('request_cargo');
        $request_cargo = filter_var($request_cargo,FILTER_SANITIZE_NUMBER_INT);
        $request_cargo = is_numeric($request_cargo)?$request_cargo:-1;


        //  NO OLIDAR SANITIZAR LOS DATOS DE ESTADO SALUD
        //  request_fecha_1,request_fecha_2,request_estado_salud

        $request_estado_salud = $form['request_estado_salud'];
        $request_fecha_1 = $form['request_fecha_1'];
        $request_fecha_2 = $form['request_fecha_2'];

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        $existe = false;

        $arrayMensajes=array();
        $error=false;
        if($esAjax && $request_inscription >0 )
        {
            $em = $this->getDoctrine()->getManager();
            
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id'=>$request_inscription,'gestionTipo'=>$request_gestion));
            $persona            = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$request_persona));
            $cargo              = $em->getRepository('SieAppWebBundle:CargoTipo')->findOneBy(array('id'=>$request_cargo));

            if($maestroInscripcion && $persona && $cargo && $request_gestion>0)
            {
                foreach($request_estado_salud as $k=>$e)
                {
                    $error=false;
                    $request_estadoSalud=$k;
                    $estadosaludTipo                = $em->getRepository('SieAppWebBundle:EstadosaludTipo')->findOneBy(array('id'=>$request_estadoSalud));
                    $maestroInscripcionEstadosalud  = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneBy(array(
                        'maestroInscripcion'=>$maestroInscripcion,
                        'persona'=>$persona,
                        'cargoTipo' => $cargo,
                        'estadosaludTipo'=> $estadosaludTipo
                    ));

                    $maestroInscripcionEstadosaludVerificacion  = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findBy(array(
                        'maestroInscripcion'=>$maestroInscripcion,
                        'persona'=>$persona,
                    ));

                    if($maestroInscripcionEstadosalud)
                        $existe=true;

                    if($existe==false || in_array($request_estadoSalud,[1,2]))
                    {
                        $maestroInscripcionEstadosalud=null;
                        $tmpmaestroInscripcionEstadosalud=$maestroInscripcionEstadosalud;
                        if($maestroInscripcionEstadosalud==null)//Si no existe lo creamos
                            $maestroInscripcionEstadosalud = new MaestroInscripcionEstadosalud();

                        $maestroInscripcionEstadosalud->setEstadosaludTipo($estadosaludTipo);
                        $maestroInscripcionEstadosalud->setMaestroInscripcion($maestroInscripcion);
                        $maestroInscripcionEstadosalud->setFechaModificacion(new \DateTime('now'));

                        $maestroInscripcionEstadosalud->setCargoTipo($cargo);
                        $maestroInscripcionEstadosalud->setPersona($persona);
                        
                        if(in_array($request_estadoSalud,[1,2]))//este es el estado enfermo o recuperado, por lo tanto tendra dos fechas
                        {
                            $fecha1=$request_fecha_1[$k];
                            $fecha2=$request_fecha_2[$k];

                            $tmpFecha1=explode('-',$fecha1);
                            $tmpFecha2=explode('-',$fecha2);

                            if(count($tmpFecha1)==3 && count($tmpFecha2)==3)
                            {
                                list($d1,$m1,$y1)=$tmpFecha1;
                                $maestroInscripcionEstadosalud->setFecha(new \DateTime($y1.'-'.$m1.'-'.$d1));

                                list($d2,$m2,$y2)=$tmpFecha2;
                                $maestroInscripcionEstadosalud->setFecha2(new \DateTime($y2.'-'.$m2.'-'.$d2));
                            }
                            else
                            {
                                $error=true;
                                $arrayMensajes[]='El estado: '.$estadosaludTipo->getEstadosalud().' requiere que tanto el campoo DESDE y campo HASTA sean registrados.';
                            }
                        }
                        else
                        {
                            if(in_array($request_estadoSalud,[0,4]))
                            {
                                if(count($maestroInscripcionEstadosaludVerificacion)==0)
                                {
                                    $maestroInscripcionEstadosalud->setFecha(new \DateTime('now'));
                                }
                                else
                                {
                                    $error=true;
                                    $arrayMensajes[]='El estado: '.$estadosaludTipo->getEstadosalud().' no puede ser registrado, puesto que ya existen registros.';
                                }
                            }
                            else
                            {
                                $fecha1=$request_fecha_1[$k];

                                $tmpFecha1=explode('-',$fecha1);

                                if(count($tmpFecha1)==3)
                                {
                                    list($d1,$m1,$y1)=$tmpFecha1;
                                    $maestroInscripcionEstadosalud->setFecha(new \DateTime($y1.'-'.$m1.'-'.$d1));
                                }
                                else
                                {
                                    $error=true;
                                    $arrayMensajes[]='El estado: '.$estadosaludTipo->getEstadosalud().' requiere que tanto el campoo DESDE sea registrado.';
                                }

                            }
                        }

                        if($error==false)
                        {
                            $em->persist($maestroInscripcionEstadosalud);
                            $em->flush();
                        }
                    }
                    else
                    {
                        $arrayMensajes[]='El estado: '.$estadosaludTipo->getEstadosalud().' ya fue registrado';
                    }
                }

                if(count($arrayMensajes)==0)
                {
                    //$data=$this->getDetalleRegistroMaestroInscripcion($maestroInscripcion);
                    //
                    
                    $detalleSalud=array();
                    $query = '
                            SELECT mies0.id as "mi_id",*
                            from maestro_inscripcion_estadosalud mies0
                            INNER JOIN estadosalud_tipo est0 on mies0.estadosalud_tipo_id=est0.id
                            where 
                            maestro_inscripcion_id=? 
                            or persona_id=? 
                    ';///////////////////AQUI CAMBIAR AND POR OR PARA MNOSTRAR DE OTRAS GESTIONES Y UNIDADES EDUCATIVAS

                    $stmt = $db->prepare($query);
                    $params = array($request_inscription,$request_persona);
                    $stmt->execute($params);
                    $detalleSalud=$stmt->fetchAll();
                    $tablaDetalles= $this->renderView($this->session->get('pathSystem').':InfoOperativoSaludAdministrivoMaestro:tablaEstadoSalud.html.twig',array(
                        'detalleSalud'=>$detalleSalud,
                    ));

                    $data=$tablaDetalles;
                    $status= 200;
                    $msj='Los datos fueron actualizados correctamente';
                }
                else
                {
                    $data=json_encode($arrayMensajes);
                    $status= 404;
                    $msj='Algunos datos no fueron guardados';
                }
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='Ocurrio un error, los datos enviados no son correctos, por favor vuelva a intentarlo';
            }
        }
        else
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }

    public function eliminarEstadoSaludAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $esAjax=$request->isXmlHttpRequest();

        $request_id = $id;
        $request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
        $request_id = is_numeric($request_id)?$request_id:-1;

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        if($esAjax && $request_id >0)
        {
            $query ="delete from maestro_inscripcion_estadosalud where id=?";
            $stmt = $db->prepare($query);
            $params = array($request_id);
            $stmt->execute($params);
            $tmp=$stmt->fetchAll();

            $borrado = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneBy(array('id' => $request_id));

            if($borrado==null)
            {
                $data='ok';
                $status= 200;
                $msj='Los datos fueron eliminados correctamente';
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='Ocurrio un error, por favor vuelva a intentarlo';
            }
        }
        else
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }



    /**
     * Esta funcion cierra el operativo de estado de salud
     *
     * @return void
     * @author lnina
     **/
    public function cerrarOperativoEstadoSaludAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $esAjax=$request->isXmlHttpRequest();

        $request_sie = $request->get('request_sie');
        $request_sie = filter_var($request_sie,FILTER_SANITIZE_NUMBER_INT);
        $request_sie = is_numeric($request_sie)?$request_sie:-1;

        $request_gestion = $request->get('request_gestion');
        $request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
        $request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

        //$request_tipo = $request->get('request_tipo'); //con esto se determina a quien pertence 0 cierre operativo administrativo y 1 cierre opertivo maestros
        //$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_NUMBER_INT);
        //$request_tipo = is_numeric($request_tipo)?$request_tipo:-1;

        $request_tipo = 21;//operativo salud abierto

        //en la tabla Institucioneducativa_Control_Operativo_Menus el identificador de operativo salud abierto es:21
        //en la tabla Institucioneducativa_Control_Operativo_Menus el identificador de operativo salud CERRADO es:11

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        if($esAjax && $request_sie >0 && $request_gestion >0)
        {
            $em = $this->getDoctrine()->getManager();
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request_sie);
            if($institucioneducativa)
            {
                //$operativoSalud = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('institucioneducativa' => $institucioneducativa,'gestionTipoId'=>$request_gestion));
                $request_tipo_cerrado=11;
                $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus');
                $query = $repository->createQueryBuilder('me')
                        ->where('me.institucioneducativa = :institucioneducativa')
                        ->andWhere('me.gestionTipoId = :gestion')
                        ->andWhere('me.estadoMenu IN (:estado)')

                        ->setParameter('institucioneducativa', $institucioneducativa->getId())
                        ->setParameter('gestion', $request_gestion)
                        ->setParameter('estado', array_values(array(11,21)))
                        ->getQuery();
                $operativoSalud = $query->getOneOrNullResult();

                if($operativoSalud==null)//no existe, lo creamos el cierre del operatiovo
                {
                    $operativoSalud = new InstitucioneducativaControlOperativoMenus();
                    $operativoSalud->setGestionTipoId($request_gestion);
                    $operativoSalud->setEstadoMenu($request_tipo_cerrado);
                    $operativoSalud->setFecharegistro(new \DateTime('now'));
                    $operativoSalud->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->findOneById(0));
                    $operativoSalud->setInstitucioneducativa($institucioneducativa);

                    $em->persist($operativoSalud);
                    $em->flush();

                    $data='ok';
                    $status= 200;
                    $msj='Los datos fueron guardados correctamente';
                }
                else//si existe
                {
                    $operativoSalud->setEstadoMenu($request_tipo_cerrado);
                    $operativoSalud->setFecharegistro(new \DateTime('now'));
                    $em->persist($operativoSalud);
                    $em->flush();

                    $data='ok';
                    $status= 200;
                    $msj='Los datos fueron guardados correctamente';
                }
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='La institucion educativa no existe.';
            }
        }
        else
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }


    public function abrirOperativoEstadoSaludAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $esAjax=$request->isXmlHttpRequest();

        $request_id = $id;
        $request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
        $request_id = is_numeric($request_id)?$request_id:-1;

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        if($esAjax && $request_id >0)
        {
            $query ="delete from institucioneducativa_control_operativo_menus where id=?";
            $stmt = $db->prepare($query);
            $params = array($request_id);
            $stmt->execute($params);
            $tmp=$stmt->fetchAll();

            $borrado = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('id' => $request_id));

            if($borrado==null)
            {
                $data='ok';
                $status= 200;
                $msj='Los datos fueron guardados correctamente';
            }
            else
            {
                $data=null;
                $status= 404;
                $msj='Ocurrio un error, por favor vuelva a intentarlo';
            }
        }
        else
        {
            $data=null;
            $status= 404;
            $msj='Ocurrio un error, por favor vuelva a intentarlo';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }

    public function verificarEstadoOperativoSaludo($request_sie,$request_gestion)
    {
        //21 abierto
        //11 cerrado
        $em = $this->getDoctrine()->getManager();
        $estadoOperativoSalud=true;
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request_sie);
        if($institucioneducativa)
        {
            //$operativoSalud = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('institucioneducativa' => $institucioneducativa,'gestionTipoId'=>$request_gestion,'estadoMenu'='21'));
            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus');
            $query = $repository->createQueryBuilder('me')
                    //->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion,ct.cargo ,ct.id cargoId,est.estadosalud, mies.vacunado')
                    ->where('me.institucioneducativa = :institucioneducativa')
                    ->andWhere('me.gestionTipoId = :gestion')
                    ->andWhere('me.estadoMenu IN (:estado)')

                    ->setParameter('institucioneducativa', $institucioneducativa->getId())
                    ->setParameter('gestion', $request_gestion)
                    ->setParameter('estado', array_values(array(11,21)))
                    ->getQuery();
            $operativoSalud = $query->getOneOrNullResult();
            

            if($operativoSalud)
            {
                if($operativoSalud->getEstadoMenu()==11)
                {
                    $estadoOperativoSalud=false;
                }
                else
                {
                    $estadoOperativoSalud=true;
                }
            }
        }
        return $estadoOperativoSalud;
    }



    public function seguimientoOperativoObtenerEstadoSaludAction($request_sie,$request_inscription,$request_gestion)
    {
        $data='';
        $status=200;
        $msj='';
        $em = $this->getDoctrine()->getManager();

            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id'=>$request_inscription,'gestionTipo'=>$request_gestion));

            if($maestroInscripcion)
            {
                /*
                $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud');
                $query = $repository->createQueryBuilder('mi')
                    ->where('mi.maestroInscripcion = :maestroInscripcion')
                    //->andWhere('mi.estadosaludTipo <> :estadosaludTipo')
                    ->setParameter('maestroInscripcion', $maestroInscripcion->getId())
                    //->setParameter('estadosaludTipo', 4)
                    ->setMaxResults(1)
                    ->getQuery();
                $maestroInscripcionEstadosalud = $query->getOneOrNullResult();
                */
                $maestroInscripcionEstadosalud = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneBy(array('maestroInscripcion'=>$maestroInscripcion->getId()));

                /*
                if($maestroInscripcionEstadosalud)
                {
                    $data='';
                    $tmp=$maestroInscripcionEstadosalud->getEstadosaludTipo();
                    
                    if($tmp)
                        $data=$tmp->getEstadosalud();
                    $tmpVacunado=$maestroInscripcionEstadosalud->getVacunado();
                    if($tmpVacunado)
                        $data=$data.'<br>*Vacunado';
                    else
                        $data=$data.'<br>*No vacunado';
                }
                */
                $data=$this->getDetalleRegistroMaestroInscripcion($maestroInscripcion);
            }

        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
    }

    public function seguimientoOperativoEstadoSaludAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $departamento=-1;
      $distrito=-1;
      $userId=$this->session->get('userId');
      $userRol=$this->session->get('roluser');
      $datosUser=$this->getDatosUsuario($userId,$userRol);
      if($datosUser)
      {
        $departamentoDistrito=$datosUser['cod_dis'];
        list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);

        $arrayDepartamentos = array();
        $arrayDistritos = array();
        $arrayUE = array();

        $rol= $datosUser['rol_tipo_id'];
        
        if(in_array($rol,[8]))//nacional
        {
            $arrayDepartamentos = $this->getDepartamentos();
            $arrayDistritos = array();
            $arrayUE = array();
        }
        else if(in_array($rol,[7]))//departamental
        {
            $tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
            $arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

            $arrayDistritos = $this->getDistritos($departamento);
            $arrayUE = array();
        }
        else if(in_array($rol,[10]))//distrital
        {
            $tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
            $arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

            $tmpDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($distrito);
            $arrayDistritos[] = array('id' =>$tmpDistrito->getId(),'distrito'=>$tmpDistrito->getDistrito());

            $arrayUE=$this->getUnidadesEducativas($departamento,$distrito);
        }
        else//ningun rol permitido
        {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem').':InfoOperativoSaludAdministrivoMaestro:seguimiento_operativo_salud.html.twig',array(
            'rol' => $rol,
            'departamentos'=>$arrayDepartamentos,
            'distritos'=>$arrayDistritos,
            'ues'=>$arrayUE,
        ));
      }
      else
      {
        return $this->redirect($this->generateUrl('login'));
      }
    }

    public function seguimientoOperativoEstadoSaludDetallesAction(Request $request)
    {
        $form = $request->request->all();

        $departamento = $form['departamento'];
        $distrito = $form['distrito'];
        $gestion = $form['gestion'];

        $departamento = filter_var($departamento,FILTER_SANITIZE_NUMBER_INT);
        $distrito = filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);
        $gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);

        $departamento = is_numeric($departamento)?$departamento:-1;
        $distrito = is_numeric($distrito)?$distrito:-1;
        $gestion = is_numeric($gestion)?$gestion:-1;

        $datos=$this->getUnidadesEducativasDetalle($departamento,$distrito,$gestion);

        return $this->render($this->session->get('pathSystem').':InfoOperativoSaludAdministrivoMaestro:resultado_seguimiento_operativo_salud.html.twig',array(
            'datos'=>$datos
        ));
    }

    /**
     * Obtiene datos del usuario departamento distrito
     *
     * @return void
     * @author lnina
     **/
    private function getDatosUsuario($userId,$userRol)
    {
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;

        $user=NULL;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select *
        from (
        select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,a.esactivo,a.id as user_id
        from usuario a 
          inner join usuario_rol b on a.id=b.usuario_id 
            inner join lugar_tipo c on b.lugar_tipo_id=c.id
        where codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
        union all
        select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
        from maestro_inscripcion a
          inner join institucioneducativa b on a.institucioneducativa_id=b.id
            inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
              inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                inner join usuario e on a.persona_id=e.persona_id
                  inner join usuario_rol f on e.id=f.usuario_id
        where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
        where user_id = ?
        and rol_tipo_id = ?
        ORDER BY cod_dis
        LIMIT 1
        ';
        $stmt = $db->prepare($query);
        $params = array($userId,$userRol);
        $stmt->execute($params);
        $user=$stmt->fetch();
        return $user;
    }

    private function getDepartamentoDistrito($numero)
    {
      $departamento=-1;
      $distrito=-1;
      if($numero==0)
      {
        $departamento=-1;
        $distrito=-1;
      }
      else
      {
        if($numero>0 && $numero<=9)
        {
          $departamento=$numero;
          $distrito=-1;
        }
        else
        {
          if($numero > 10 and strlen($numero)==4)
          {
            $departamento=substr($numero,0,1);
            $distrito=$numero;
          }
          else
          {
            $departamento=-1;
            $distrito=-1;
          }
        }
      }
      return array($departamento,$distrito);
    }

    public function getDepartamentos()
    {
      $em = $this->getDoctrine()->getManager();
      $departamentos =  $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
      $departamentos_array = array();
      foreach ($departamentos as $value)
      {
        $departamentos_array[] = array('id'=>$value->getId(),'codigo'=>$value->getCodigo(),'depto'=>$value->getDepartamento());
      }
      return $departamentos_array;
    }

    public function getDistritos($departamento)
    {
        $em = $this->getDoctrine()->getManager();
        //$departamento=filter_var($request->get('departamento'),FILTER_SANITIZE_NUMBER_INT);
        $distritos_array=array();
        $em = $this->getDoctrine()->getManager();
        $distritos = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$departamento));
        
        foreach ($distritos as $d)
        {
            $distritos_array[]=array('id' =>$d->getId(),'distrito'=>$d->getDistrito());
        }
        //$response = new Response(json_encode($distritos_array));
        //$response->headers->set('Content-Type', 'application/json');
        //return $response;
        return $distritos_array;
    }

    private function getUnidadesEducativas($departamento=-1,$distrito=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';

        $ue=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $query ="
            select 
            institucioneducativa_id,institucioneducativa
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          group by cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia
        ";

        $stmt = $db->prepare($query);
        $params = array($distrito,$departamento);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if($tmp)
        {
            foreach ($tmp as $u)
            {
                $ue[]=array('id' =>$u['institucioneducativa_id'],'ue'=>$u['institucioneducativa']);
            }
        }
        return $ue;
    }

    private function getUnidadesEducativasDetalle($departamento,$distrito,$gestion)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';

        $ue=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $queryOld ="
            select 
            TARGET AS institucioneducativa_id,institucioneducativa,
            (
                        --select CASE WHEN count(mi.id) = count(estadosalud) THEN 1 WHEN count(mi.id) <> count(estadosalud) THEN 0 END administrativos--p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion, est.estadosalud
                        select count(est.estadosalud)||' / '||count(mi.id) administrativos
                        from maestro_inscripcion mi
                        inner Join persona p on mi.persona_id = p.id
                        inner Join formacion_tipo ft on mi.formacion_tipo_id = ft.id
                        left Join (SELECT * from maestro_inscripcion_estadosalud where estadosalud_tipo_id>0 and estadosalud_tipo_id <=5)  mies on mi.id =mies.maestro_inscripcion_id
                        left Join estadosalud_tipo est  on mies.estadosalud_tipo_id = est.id
                        where mi.institucioneducativa_id = TARGET
                        and mi.gestion_tipo_id = ?
                        and mi.cargo_tipo_id  not in (0,70)
            ) as administrativos,
            (
                        --select CASE WHEN count(mi.id) = count(estadosalud) THEN 1 WHEN count(mi.id) <> count(estadosalud) THEN 0 END maestros--p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion, est.estadosalud
                        select  count(est.estadosalud)||' / '||count(mi.id) maestros
                        from maestro_inscripcion mi
                        inner Join persona p on mi.persona_id = p.id
                        inner Join formacion_tipo ft on mi.formacion_tipo_id = ft.id
                        left Join (SELECT * from maestro_inscripcion_estadosalud where estadosalud_tipo_id>0 and estadosalud_tipo_id <=5)  mies on mi.id =mies.maestro_inscripcion_id
                        left Join estadosalud_tipo est  on mies.estadosalud_tipo_id = est.id
                        where mi.institucioneducativa_id = TARGET
                        and mi.gestion_tipo_id = ?
                        and mi.cargo_tipo_id  in (0,70)
            ) as maestros,
            (
                        select count(est.estadosalud)||' / '||count(distinct mi.id) as resultado
                        from maestro_inscripcion mi
                        inner Join persona p on mi.persona_id = p.id
                        inner Join formacion_tipo ft on mi.formacion_tipo_id = ft.id
                        left Join (SELECT * from maestro_inscripcion_estadosalud where estadosalud_tipo_id >=5 )  mies on mi.id =mies.maestro_inscripcion_id
                        left Join estadosalud_tipo est  on mies.estadosalud_tipo_id = est.id
                        where mi.institucioneducativa_id = TARGET
                        and mi.gestion_tipo_id = ?
                        and mi.cargo_tipo_id  not in (0,70)
            ) administrativos_vacunados,
            (
                        select count(est.estadosalud)||' / '||count(distinct mi.id) as resultado
                        from maestro_inscripcion mi
                        inner Join persona p on mi.persona_id = p.id
                        inner Join formacion_tipo ft on mi.formacion_tipo_id = ft.id
                        left Join (SELECT * from maestro_inscripcion_estadosalud where estadosalud_tipo_id >=5)  mies on mi.id =mies.maestro_inscripcion_id
                        left Join estadosalud_tipo est  on mies.estadosalud_tipo_id = est.id
                        where mi.institucioneducativa_id = TARGET
                        and mi.gestion_tipo_id = ?
                        and mi.cargo_tipo_id  in (0,70)
            ) maestros_vacunados
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as TARGET,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          group by cod_dis,des_dis,TARGET,institucioneducativa,dependencia
        ";

        $query ="
            select 
            TARGET AS institucioneducativa_id,institucioneducativa,(
            select id 
            from institucioneducativa_control_operativo_menus 
            where gestion_tipo_id= ? and institucioneducativa_id = TARGET and  estado_menu='11'
            ) as estado_reporte
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as TARGET,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          group by cod_dis,des_dis,TARGET,institucioneducativa,dependencia
        ";


        $stmt = $db->prepare($query);
        //$params = array($gestion,$gestion,$gestion,$gestion,$distrito,$departamento);
        $params = array($gestion,$distrito,$departamento);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if($tmp)
        {
            foreach ($tmp as $u)
            {
                $ue[]=array(
                    'id' =>$u['institucioneducativa_id'],
                    'ue'=>$u['institucioneducativa'],
                    'estado_reporte'=>$u['estado_reporte'],

                );
            }
        }
        return $ue;
    }

    private function getDetalleRegistroMaestroInscripcion($maestroInscripcion)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $detalle ='';
        $query = "
            select string_agg(est.estadosalud ||' ('|| to_char(mies.fecha, 'DD-MM-YYYY') ||') ',' - <br>') detalle
            from maestro_inscripcion_estadosalud mies
            inner join estadosalud_tipo est on mies.estadosalud_tipo_id=est.id
            where mies.maestro_inscripcion_id = ?";
        $stmt = $db->prepare($query);
        $params = array($maestroInscripcion->getId());
        $stmt->execute($params);
        $detalle=$stmt->fetchAll();
        if(count($detalle)>0)
            $detalle=$detalle[0]['detalle'];
        return $detalle;
    }


    public function exportarReporteDDJJOperativoSaludAction(Request $request,$codue,$gestion)
    {
        $codue  = filter_var($codue,FILTER_SANITIZE_NUMBER_INT);
        $gestion               = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);

        $codue  = empty($codue)?-1:$codue;
        $gestion               = empty($gestion)?-1:$gestion;

        $arch           = 'DECLARACIÓN_JURADA_REPORTE_ESTADO_DE_SALUD-'.date('Y').'_'.date('YmdHis').'.pdf';
        $response       = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_maestroayadministrativo_salud_v1.rptdesign&__format=pdf'.'&codue='.$codue.'&gestion='.$gestion));
        //$response->setContent(file_get_contents('http://127.0.0.1:62804/viewer/preview?__report=D%3A\workspaces\workspace_especial\Reporte-maestro-admistrativo\reg_lst_maestroayadministrativo_salud_v1.rptdesign&__format=pdf'.'&codue='.$codue.'&gestion='.$gestion));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /*
    public function getUnidadesEducativasSeleccionadasAction($distrito)
    {
        $distrito=filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);

        $departamento=substr($distrito,0,1);

        $ues_array=$this->getUnidadesEducativas($departamento,$distrito);
        $response = new Response(json_encode($ues_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }*/



}
