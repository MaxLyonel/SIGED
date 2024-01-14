<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\VerificarPersonaSegipType;
use Sie\AppWebBundle\Form\PersonaDatosType;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoMaestroController extends Controller {

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
            
            if($form['accessuser']==1){
                $form['sie'] = ($form['sie']);
                $form['gestion'] =($form['gestion']);     
            }else{
                $form['sie'] = hex2bin($form['sie']);
                $form['gestion'] =hex2bin($form['gestion']);                
            }
            

            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('herramienta_info_maestro_tsie_index'));
                }
                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
            }

            $dependencia =  $institucioneducativa->getdependenciaTipo()->getId();
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
                $request->getSession()->set('idDependencia',$dependencia);
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

        if(!is_object($institucionregular)){
            $this->get('session')->getFlashBag()->add('noTuicion', 'La Unidad Educativa no corresponde al Subsistema de Educación Regular');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('herramienta_info_maestro_tsie_index'));
                }
                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
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
        $cargosArray = array();

        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
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

        $query = $repository->createQueryBuilder('mi')
                ->select('p.id perId, p.carnet, p.complemento, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion, ct.cargo')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'WITH', 'mi.cargoTipo = ct.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->distinct()
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $maestro = $query->getResult();

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
        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:index.html.twig', array(
                'maestro' => $maestro,
                'institucion' => $institucion,
                'gestion' => $gestion,
                'maestro_no_idioma' => $arrayNoIdioma,
                'maestro_no_genero' => $arrayNoGenero,
                'activar_acciones' => $activar_acciones,
                'habilitar_ddjj' => $habilitar_ddjj
        ));
    }

    public function findAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        $dependencia =  $institucion->getdependenciaTipo()->getId();
        if ($dependencia != 3) {
            $this->get('session')->getFlashBag()->add('eliminarError', 'Todos registro de maestros debe realizarse a traves de la UGPSEP para U.E. públicas');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        }

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:search.html.twig', array(
                    'form' => $this->searchForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion')
        ));
    }

    /*
     * Crea un formulario para buscar una persona por C.I.
     *
     */
    private function searchForm() {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_info_maestro_result'))
            ->add('buscar', 'submit', array('label' => 'Buscar coincidencias', 'attr' => array('class' => 'btn btn-md btn-facebook')))
            ->getForm();
        return $form;
    }

    public function resultAction(Request $request) {        
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $persona = $this->get('sie_app_web.persona')->buscarPersonaPorCarnetComplemento($form);

        $dataPer = array('carnet'=>$form['carnet'], 'complemento'=>($form['complemento']=='')?'':$form['complemento']);
        $personaBus = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($dataPer);        
        // dump($persona);die;
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
        $data = [
            'carnet' => $form['carnet'],
            'complemento' => $form['complemento'],
            'id' => '',

        ];
        if($personaBus){
            if( $personaBus->getSegipId() == 0){

                $data['id']= bin2hex($personaBus->getId());
                $data['paterno']=$personaBus->getPaterno();
                $data['materno']=$personaBus->getMaterno();
                $data['nombre']=$personaBus->getNombre();
                $data['fecha_nacimiento']=$personaBus->getFechaNacimiento()->format('d-m-Y');
                
                $persona = array();
            }else{                
                $persona['personaCarnet']=$personaBus->getCarnet();
                $persona['personaComplemento']=$personaBus->getComplemento();
                $persona['personaPaterno']=$personaBus->getPaterno();
                $persona['personaMaterno']=$personaBus->getMaterno();
            }

        }else{
                $data['id']= '';
                $data['paterno']='';
                $data['materno']='';
                $data['nombre']='';
                $data['fecha_nacimiento']='';
        }

        $formVerificarPersona = $this->createForm(new VerificarPersonaSegipType(), 
            null,
            array(
                'action' => $this->generateUrl('herramienta_info_maestro_segip_verificar_persona'), 
                'method' => 'POST'
            )
        );

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:result.html.twig', array(
            'data' => $data,
            'persona' => $persona,
            'institucion' => $institucion,
            'gestion' => $request->getSession()->get('idGestion'),
            'form_verificar' => $formVerificarPersona->createView(),
        ));
    }

    public function verificarPersonaAction(Request $request){
        
        $form = $request->get('sie_verificar_persona_segip');

        $tipo_persona = 1;
        //si llega desde el form, estopara que otros formualrio no tengan error
        // mientras son modificados
        if ($request->get('nacionalidad')) {
            //NA: nacional, EX: extranjero
            $nacionalidad = $request->get('nacionalidad'); //EX o NA
            $tipo_persona = ($nacionalidad == 'NA') ? 1 : 2;
        }

        $data = [
            'carnet' => $form['carnet'],
            'complemento' => $form['complemento'],
            'primer_apellido' => $form['primer_apellido'],
            'segundo_apellido' => $form['segundo_apellido'],
            'nombre' => $form['nombre'],
            'fecha_nacimiento' => $form['fecha_nacimiento'],
            'tipo_persona' => $tipo_persona,  //tiene que venir del form
        ];

        $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($form['carnet'], $data, $form['entorno'], 'academico');
        
        $persona = array();

        if($resultado) {
            $persona = [
                'carnet' => $form['carnet'],
                'complemento' => $form['complemento'] ? $form['complemento'] : '0',
                'primer_apellido' => $form['primer_apellido'],
                'segundo_apellido' => $form['segundo_apellido'],
                'nombre' => $form['nombre'],
                'fecha_nacimiento' => $form['fecha_nacimiento'],
                'idper' => $form['idper'],
            ];
        }

        $formPersonaDatos = $this->createForm(new PersonaDatosType(), 
            null,
            array(
                'action' => $this->generateUrl('herramienta_info_maestro_persona_datos'), 
                'method' => 'POST'
            )
        );

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:formulario_persona_new.html.twig',array(
            'form_datos' => $formPersonaDatos->createView(),
            'resultado'=>$resultado,
            'persona' => serialize($persona),
            'institucion' => $form['institucion'],
            'gestion' => $form['gestion'],
            //dcastillo 2402 pasar esto al segundo formulario...
            'tipo_persona' => $tipo_persona
        ));
    }

    public function registrarPersonaAction(Request $request)
    {
        //NO PERMITIR REGISTRO DE PERSONAS
        //return $this->redirect($this->generateUrl('login'));

        //aqui se adiciono un campo hidden en formulario_persona_new.html.twig
        //por que por alguna razon, se hace dos validacione segip
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('sie_persona_datos');
        $persona = unserialize($form['persona']);
        $persona_validada = null;
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucion']);
        $gestion = $form['gestion'];
        
        $fecha = str_replace('-','/',$persona['fecha_nacimiento']);
        $complemento = $persona['complemento'] == '0'? '':$persona['complemento'];

        $tipo_persona = 1;
        if ($form['tipo_persona']) {
            //NA: nacional, EX: extranjero            
            $tipo_persona = ($form['tipo_persona'] == "1") ? 1 : 2;
        }

        $arrayDatosPersona = array(
            //'carnet'=>$form['carnet'],
            'complemento'=>$complemento,
            'paterno'=>$persona['primer_apellido'],
            'materno'=>$persona['segundo_apellido'],
            'nombre'=>$persona['nombre'],
            'fecha_nacimiento' => $fecha,
            'tipo_persona' => $tipo_persona
        );
        //dump($arrayDatosPersona); die;

        $personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($persona['carnet'], $arrayDatosPersona, 'prod', 'academico');
        //dump($personaValida); die;

        if( $personaValida )
        {
            $arrayDatosPersona['carnet']=$persona['carnet'];
            unset($arrayDatosPersona['fecha_nacimiento']);
            $arrayDatosPersona['fechaNacimiento']=$persona['fecha_nacimiento'];
            $personaEncontrada = $this->get('buscarpersonautils')->buscarPersonav2($arrayDatosPersona,$conCI=true, $segipId=1);

            if($personaEncontrada == null)
            {
                if($persona['idper']==''){
                    $newPersona = new Persona();
                }else{
                    $newPersona = $em->getRepository('SieAppWebBundle:Persona')->find(hex2bin($persona['idper']));
                }

                $newPersona->setCarnet($persona['carnet']);
                $newPersona->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                $newPersona->setPaterno(mb_strtoupper($persona['primer_apellido'], 'utf-8'));
                $newPersona->setMaterno(mb_strtoupper($persona['segundo_apellido'], 'utf-8'));
                $newPersona->setNombre(mb_strtoupper($persona['nombre'], 'utf-8'));
                $newPersona->setFechaNacimiento(new \DateTime($persona['fecha_nacimiento']));
                $newPersona->setCelular($form['celular']);
                $newPersona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
                $newPersona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
                $newPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($form['departamentoTipo']));
                $newPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                $newPersona->setSegipId(1);
                $newPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById(0));
                $newPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
                $newPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneById(0));
                $newPersona->setRda('0');
                $newPersona->setEsvigente('t');
                $newPersona->setActivo('t');

                $newPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find(isset($persona['extranjero'])?2:1));

                $em->persist($newPersona);
                $em->flush();
                $persona_validada = $newPersona;
            }
            // else existe la persona no se registra
        }
        else
        {
            $persona_validada = null;
        }
        // else persona no valida
    /*
        //Verificar si la persona ya fue registrada
        $repository = $em->getRepository('SieAppWebBundle:Persona');
        if($persona['complemento'] == '0')
        {
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.segipId = :valor')
                ->setParameter('carnet', $persona['carnet'])
                ->setParameter('valor', 1)
                ->getQuery();
        }
        else
        {
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.complemento = :complemento AND p.segipId = :valor')
                ->setParameter('carnet', $persona['carnet'])
                ->setParameter('complemento', $persona['complemento'])
                ->setParameter('valor', 1)
                ->getQuery();
        }
        $personas = $query->getResult();

        if($personas)
        {
            $persona_validada = $personas[0];
        }
        else
        {
            //Buscar personas asociadas al nro de carnet y complemento con segip_id=0 y actualizar
            $repository = $em->getRepository('SieAppWebBundle:Persona');
            if($persona['complemento'] == '0')
            {
                $query = $repository->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.carnet = :carnet AND p.segipId = :valor')
                    ->setParameter('carnet', $persona['carnet'])
                    ->setParameter('valor', 0)
                    ->getQuery();
            }
            else
            {
                $query = $repository->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.carnet = :carnet AND p.complemento = :complemento AND p.segipId = :valor')
                    ->setParameter('carnet', $persona['carnet'])
                    ->setParameter('complemento', $persona['complemento'])
                    ->setParameter('valor', 0)
                    ->getQuery();
            }
            $personas = $query->getResult();

            //Buscar usuarios asociados con username = persona-complemento y actualizar
            $repository = $em->getRepository('SieAppWebBundle:Usuario');
            
            $query = $repository->createQueryBuilder('u')
                ->select('u')
                ->where('u.persona in (:personas)')
                ->setParameter('personas', $personas)
                ->getQuery();

            $usuarios = $query->getResult();

            //Actualizar CI, complemento y username
            foreach ($personas as $key => $value)
            {
                $value->setCarnet($value->getCarnet().'±');
                $em->persist($value);
                $em->flush();
            }

            foreach ($usuarios as $key => $value)
            {
                $value->setUsername($value->getUsername().'±');
                $em->persist($value);
                $em->flush();
            }

            if($persona['complemento'] == '0')
            {
                $persona['complemento'] = '';
            }
            $newPersona = new Persona();
            $newPersona->setCarnet($persona['carnet']);
            $newPersona->setComplemento(mb_strtoupper($persona['complemento'], 'utf-8'));
            $newPersona->setPaterno(mb_strtoupper($persona['primer_apellido'], 'utf-8'));
            $newPersona->setMaterno(mb_strtoupper($persona['segundo_apellido'], 'utf-8'));
            $newPersona->setNombre(mb_strtoupper($persona['nombre'], 'utf-8'));
            $newPersona->setFechaNacimiento(new \DateTime($persona['fecha_nacimiento']));
            $newPersona->setCelular($form['celular']);
            $newPersona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $newPersona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $newPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($form['departamentoTipo']));
            $newPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $newPersona->setSegipId(1);
            $newPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById(0));
            $newPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneById(0));
            $newPersona->setRda('0');
            $newPersona->setEsvigente('t');
            $newPersona->setActivo('t');

            $em->persist($newPersona);
            $em->flush();
            $persona_validada = $newPersona;
        }
    */
        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:result_newpersona.html.twig',array(
            'persona'=>$persona_validada,
            'institucion' => $institucion,
            'gestion' => $gestion
        ));
    }

    /*
     * Llamamos al formulario para nuevo maestro
     */

    public function newAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:new.html.twig', array(
                    'form' => $this->newForm($form['idInstitucion'], $form['gestion'], $form['idPersona'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * Llamar al formulario de edicion
     */

    public function editAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
        $cisend = $persona->getCarnet();
        $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('idMaestroInscripcion'));
        $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $request->get('idMaestroInscripcion')));
        $em->getConnection()->commit();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $maestroInscripcion, $idiomas)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona,
                    'cisend' => bin2hex($cisend),
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInstitucion, $gestion, $idPersona) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.id IN (0,70) ORDER BY ct.id');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)')
                ->setParameter('id', array(0, 5, 12))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id)')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_maestro_create'))
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
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $maestroInscripcion, $idiomas) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
         WHERE ct.id IN (0,70) ORDER BY ct.id');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)  ORDER BY ft.id')
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

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_maestro_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => false, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion', 'text', array('label' => 'Denominativo del título del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '45', 'pattern' => '[A-Za-z0-9\Ññ ]{0,45}')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('checked' => $maestroInscripcion->getNormalista())))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem() ? $maestroInscripcion->getItem() : 0, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ($maestroInscripcion->getEstudiaiomaMaterno()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('checked' => $maestroInscripcion->getLeeescribebraile())))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /*
     * registramos el nuevo maestro
     */

    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            // Registrar sucursal
            $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'gestionTipo' => $form['gestion']));

            if(!$sucursal) {
                $query = $em->getConnection()->prepare("select * from sp_genera_institucioneducativa_sucursal('".$form['institucionEducativa']."','0','".$form['gestion']."','1');")->execute();
            }
            // Verificar si la persona ya esta registrada
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);
            $personaId = $persona->getId();

            // verificamos si el maestro esta registrado ya en tabla maestro inscripcion
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'persona' => $personaId, 'gestionTipo' => $form['gestion'], 'cargoTipo' => '0'));
            if ($maestroInscripcion) { // Si  el maestro ya esta inscrito
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada');
                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
            }

            //Actualizamos datos de la persona
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();

            // Registro Maestro inscripcion
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
            $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']));
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setIdiomaMaternoId(null);
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));            
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'gestionTipo' => $form['gestion'])));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
            $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($personaId));
            $maestroinscripcion->setRdaPlanillasId(0);
            $maestroinscripcion->setRef(0);
            $maestroinscripcion->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2));
            $maestroinscripcion->setItem($form['item']);
            $maestroinscripcion->setFechaRegistro(new \DateTime('now'));
            $maestroinscripcion->setEsVigenteAdministrativo(1);
            $em->persist($maestroinscripcion);
            $em->flush();

            // Registro de idiomas que habla

            for ($i = 1; $i < 10; $i++) {
                $idioma = $request->get('idioma' . $i);
                $lee = $request->get('lee' . $i);
                $habla = $request->get('habla' . $i);
                $escribe = $request->get('escribe' . $i);

                if ($idioma) {
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion_idioma');")->execute();
                    $maestroIdioma = new MaestroInscripcionIdioma();
                    $maestroIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($idioma));
                    $maestroIdioma->setIdiomaconoceTipoLee($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($lee));
                    $maestroIdioma->setIdiomaconoceTipoHabla($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($habla));
                    $maestroIdioma->setIdiomaconoceTipoEscribe($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($escribe));
                    $maestroIdioma->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($maestroinscripcion->getId()));
                    $em->persist($maestroIdioma);
                    $em->flush();
                }
            }

            $llave = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave' => $persona->getCarnet(), 'validacionReglaTipo' => 9, 'solucionTipoId' => 1));

            if($llave) {
                $llave->setEsActivo(true);
                $em->persist($llave);
                $em->flush();
            }
            
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Listar los idiomas que habla y lee el maestro
     * en el formulario nuevo
     */

    public function listaridiomasleehablaescribeAction() {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $idioma = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
            $idiomas = array();
            foreach ($idioma as $i) {
                $idiomas[$i->getId()] = $i->getIdiomaMaterno();
            }
            $conoce = $em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findAll();
            $conocimiento = array();
            foreach ($conoce as $c) {
                $conocimiento[$c->getId()] = $c->getIdiomaConoce();
            }

            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array('idiomas' => $idiomas, 'conoce' => $conocimiento));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Listar los idiomas que habla lee el maestro
     * en el formulario edit
     */

    public function listaridiomasleehablaescribeeditAction($idMaestroInscripcion) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $idiomasM = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $idMaestroInscripcion));
            $idiomasMaestro = array();
            foreach ($idiomasM as $im) {
                $idiomasMaestro[$im->getId()] = array('idioma' => $im->getIdiomaMaterno()->getId(), 'lee' => $im->getIdiomaconoceTipoLee()->getId(), 'habla' => $im->getIdiomaconoceTipoHabla()->getId(), 'escribe' => $im->getIdiomaconoceTipoEscribe()->getId());
            }
            //die;
            $idioma = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
            $idiomas = array();
            foreach ($idioma as $i) {
                $idiomas[$i->getId()] = $i->getIdiomaMaterno();
            }
            $conoce = $em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findAll();
            $conocimiento = array();
            foreach ($conoce as $c) {
                $conocimiento[$c->getId()] = $c->getIdiomaConoce();
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('idiomasMaestro' => $idiomasMaestro, 'idiomas' => $idiomas, 'conoce' => $conocimiento));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * guardar datos de modificacion
     */

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            // update the data person if has QA observation
            $answerMod = $this->updatePerson($form);

            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();

            //Actualizacion en la tabla maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']);
            if (!empty($maestroinscripcion)){
                $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
                $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']));
                $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
                $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
                $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
                $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
                $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'gestionTipo' => $form['gestion'])));
                $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
                $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
                $maestroinscripcion->setItem($form['item']);
                $maestroinscripcion->setFechaModificacion(new \DateTime('now'));
                $em->persist($maestroinscripcion);
                $em->flush();
            }

            //Actualizacion de idiomas
            //1ro eliminacion de los anteriores registros
            $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $form['idMaestroInscripcion']));
            foreach ($idiomas as $idioma) {
                $maestroIdioma = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findOneById($idioma->getId());
                $em->remove($maestroIdioma);
                $em->flush();
            }
            // 2do registro nuevos idiomas
            for ($i = 1; $i < 10; $i++) {
                $idioma = $request->get('idioma' . $i);
                $lee = $request->get('lee' . $i);
                $habla = $request->get('habla' . $i);
                $escribe = $request->get('escribe' . $i);
                if ($idioma) {
                    $maestroIdioma = new MaestroInscripcionIdioma();
                    $maestroIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($idioma));
                    $maestroIdioma->setIdiomaconoceTipoLee($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($lee));
                    $maestroIdioma->setIdiomaconoceTipoHabla($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($habla));
                    $maestroIdioma->setIdiomaconoceTipoEscribe($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($escribe));
                    $maestroIdioma->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']));
                    $em->persist($maestroIdioma);
                    $em->flush();
                    echo $idioma;
                }
            }

            $llave = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findBy(array('llave' => $persona->getCarnet(), 'validacionReglaTipo' => 9, 'solucionTipoId' => 2));

            if($llave) {
                foreach ($llave as $key => $value) {
                    $value->setEsActivo(true);
                    $em->persist($value);
                    $em->flush();# code...
                }
            }

            $em->getConnection()->commit();
            $msg = '';
            if(!$answerMod){
                $msg = '. DATOS DE PERSONA NO MODIFICADOS, NO CUMPLE CON LA VALIDACION SEGIP';
            }
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente '.$msg);
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index', array('op' => 'result')));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index', array('op' => 'result')));
        }
    }
    private function updatePerson($form){
        $em = $this->getDoctrine()->getManager();

            $arrParametros = array(
                'complemento'=>mb_strtoupper($form['complemento'], 'utf-8'),
                'primer_apellido'=>mb_strtoupper($form['paterno'], 'utf-8'),
                'segundo_apellido'=>mb_strtoupper($form['materno'], 'utf-8'),
                'nombre'=>mb_strtoupper($form['nombre'], 'utf-8'),
                'fecha_nacimiento'=>$form['fechaNacimiento']
            );
            if($form['extranjero'] == 1){
                $arrParametros['extranjero'] = 'e';
            }      
            
            // get info segip
            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( hex2bin($form['carnet']),$arrParametros,'prod', 'academico');   
            $answer = 0;
            if($answerSegip){
                
                $newPersona = $em->getRepository('SieAppWebBundle:Persona')->find(($form['idPersona']));
                $oldPersona = clone $newPersona;
                $newPersona->setPaterno(mb_strtoupper($form['paterno'], 'utf-8'));
                $newPersona->setMaterno(mb_strtoupper($form['materno'], 'utf-8'));
                $newPersona->setNombre(mb_strtoupper($form['nombre'], 'utf-8'));
                $newPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));                
                $newPersona->setSegipId(1);                

                $newPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find(($form['extranjero']==1)?2:1));    
                $em->persist($newPersona);
                $em->flush();
                $answer = 1;

                $this->get('funciones')->setLogTransaccion(
                                       $form['idPersona'],
                                        'persona',
                                        'U',
                                        '',
                                        $newPersona,
                                        $oldPersona,
                                        'ACADEMICO',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                );  


            }
        return $answer; 


    }

    /*
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('idMaestroInscripcion'));
            /*dump($request->get('idMaestroInscripcion'));
            dump($maestroInscripcion);die();*/
            if ($maestroInscripcion) { // Sie existe el maestro inscrito
                // Verificamos si el maestro tine asignados algunas areas o asignaturas
                $institucionCursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));
                if ($institucionCursoOfertaMaestro) {
                    $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar, el maestro tiene areas asignadas.');
                    return $this->redirect($this->generateUrl('herramienta_info_maestro_index', array('op' => 'result')));
                }
                // dump($institucionCursoOfertaMaestro);die();
                //eliminar idiomas del maestro
                $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));

                if ($idiomas) {
                    foreach ($idiomas as $id) {
                        $em->remove($em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findOneById($id->getId()));
                        $em->flush();
                    }
                }

                // Eliminados la inscripcion de salud
                // $maestroInscripcionEstadosalud = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneBy(array('maestroInscripcion'=>$maestroInscripcion->getId()));
                /*$em->remove($maestroInscripcionEstadosalud);
                $em->flush();*/
                $maestroInscripcionEstadosalud = $em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findBy(array('maestroInscripcion'=>$maestroInscripcion->getId()));
                if($maestroInscripcionEstadosalud){
                    foreach ($maestroInscripcionEstadosalud as $id1) {
                        $em->remove($em->getRepository('SieAppWebBundle:MaestroInscripcionEstadosalud')->findOneById($id1->getId()));
                        $em->flush();
                    }
                }

                //eliminamos el registro de inscripcion del maestro
                $em->remove($maestroInscripcion);
                $em->flush();
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente');

                return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
            }
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($maestroInscripcion->getPersona()->getId());
            $llave = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave' => $persona->getCarnet(), 'validacionReglaTipo' => 9, 'solucionTipoId' => 0));

            if($llave) {
                $llave->setEsActivo(true);
                $em->persist($llave);
                $em->flush();
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /* funcion para verificar si el maestro ya existe
      con el carnet, metodo ajax */

    public function verificar_existe_personaAction($carnet) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneByCarnet($carnet);
            if ($persona) {
                $paterno = $persona->getPaterno();
                $materno = $persona->getMaterno();
                $nombre = $persona->getNombre();
                $fechaNacimiento = $persona->getFechaNacimiento()->format('d-m-Y');
                $genero = $persona->getGeneroTipo()->getId();
                $direccion = $persona->getDireccion();
                $celular = $persona->getCelular();
                $rda = $persona->getRda();
                $correo = $persona->getCorreo();
                /* Creamos el array para los generos */
                $generos = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
                $generosArray = array();
                foreach ($generos as $g) {
                    $generosArray[$g->getId()] = $g->getGenero();
                }
                /* Se envia los parametros en formato json */
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('encontrado' => 'si', 'paterno' => $paterno, 'materno' => $materno, 'nombre' => $nombre, 'fechaNacimiento' => $fechaNacimiento, 'genero' => $genero, 'direccion' => $direccion, 'celular' => $celular, 'rda' => $rda, 'correo' => $correo, 'generosArray' => $generosArray));
            } else {
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('encontrado' => 'no'));
            }
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    //Selección del Personal Administrativo
    public function esVigenteMaestroAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->session = new Session();

            // Verificamos si no ha caducado la session
            if (!$this->session->get('userId')) {
                return $this->redirect($this->generateUrl('login'));
            }

            $em = $this->getDoctrine()->getManager();
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->get('gestion'));
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id' => $request->get('idMaestroInscripcion'), 'gestionTipo' => $gestion, 'institucioneducativa' => $institucion));

            //$icom = $institucion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcion));

            //$icom->setEsVigenteMaestro(1 - $icom->getEsVigenteMaestro());
            $cargo = $em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($request->get('idCargo'));
            if(!empty($maestroInscripcion)){
                $maestroInscripcion->setCargotipo($cargo);
                $maestroInscripcion->setEsVigenteAdministrativo(1 - $maestroInscripcion->getEsVigenteAdministrativo());
                $em->persist($maestroInscripcion);
                $em->flush();
            }
            $em->getConnection()->commit();

            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function indexTecSieAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:index_tec_sie.html.twig', array(
                'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
        ));

    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {

        $gestiones = [];

        for($i=$gestionactual;$i>=2009;$i--){
            $gestiones[$i]=$i;
        }

        // $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_maestro_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('accessuser', 'hidden', array('data' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function maestroHabilitarDDJJAction(Request $request) {
        $form = $request->get('ddjjMaestroHabilitar');

        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie, $gestion);
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2);

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $sie, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));

        try{
            $em->remove($validacion_personal_aux);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarOk', 'Se activó satisfactoriamente el operativo.');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('eliminarError', 'Error en la habilitación del operativo');
            return $this->redirect($this->generateUrl('herramienta_info_maestro_index'));
        }
    }

}
