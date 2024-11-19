<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\Consolidacion;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;

/**
 * Institucioneducativa Controller
 */
class InstitucioneducativaController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function indexAction(Request $request) {
        //get the session's values
        // dump($request->get('gestion'));die;

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

        $em = $this->getDoctrine()->getManager();

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');

        $query = $repository->createQueryBuilder('inss')
                ->select('max(inss.gestionTipo)')
                ->where('inss.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion)
                ->getQuery();

        $inss = $query->getResult();
        
        
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('ie')
                ->select('ie, ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo in (:gestion)')
                ->andWhere('ies.id = :sucursal')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('sucursal', $sucursal)
                ->getQuery();

        $infoUe = $query->getResult();

        //dump($gestion);dump($infoUe);die;

        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');

        $query = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.id = inss.institucioneducativa')
                ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                ->where('inst.id = :idInstitucion')
                ->andWhere('inss.gestionTipo in (:gestion)')
                ->andWhere('inss.id = :sucursal')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('sucursal', $sucursal)
                ->getQuery();

        $ubicacionUe = $query->getResult();

        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
                        'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('niveles', array(11, 12, 13));

        $cursos = $query->getResult();

        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
                ->select('per.carnet, per.paterno, per.materno, per.nombre')
                ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                ->where('mins.institucioneducativa = :idInstitucion')
                ->andWhere('mins.gestionTipo = :gestion')
                ->andWhere('mins.periodoTipo = :periodo')
                ->andWhere('mins.cargoTipo in (:cargo)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargo', array('1','12'))
                ->setParameter('periodo', $periodo)
                ->addOrderBy('mins.cargoTipo')
                ->setMaxResults(1)
                ->getQuery();

        $director = $query->getOneOrNullResult();

        $gestionSuc = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestionSuc));
        
        //dump($infoUe);die;
        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:index.html.twig', array(
                    'ieducativa' => $infoUe,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'cursos' => $cursos,
                    'maestros' => $maestros,
                    'ubicacion' => $ubicacionUe,
                    'director' => $director,
                    'form' => $this->editSucursalForm($sucursal)->createView(),
        ));
    }

    private function editSucursalForm($sucursal) {

        $institucion = $this->session->get('ie_id');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT tt FROM SieAppWebBundle:TurnoTipo tt
                        ORDER BY tt.turno');

        $turnos = $query->getResult();
        $turnosArray = array();
        foreach ($turnos as $t) {
            // CHANGED
            // dump($t->getGestionTipo());
            if( $t->getId() !== 0 ){
                $turnosArray[$t->getId()] = $t->getTurno();
            }
        }
        // die;
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_ceducativa_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion))
                ->add('gestionSuc', 'hidden', array('data' => $sucursal->getGestionTipo()))
                ->add('idSucursal', 'hidden', array('data' => $sucursal->getId()))
                ->add('idPeriodo', 'hidden', array('data' => $sucursal->getPeriodoTipoId()))
                ->add('telefono1', 'text', array('required' => false, 'label' => 'Número de teléfono del CEA', 'data' => $sucursal->getTelefono1(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}', 'maxlength' => '10')))
                ->add('telefono2', 'text', array('required' => true, 'label' => 'Número de celular de la Directora o Director', 'data' => $sucursal->getTelefono2(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}', 'maxlength' => '10')))
                ->add('email', 'text', array('required' => true, 'label' => 'Correo electrónico del CEA o de la Directora o Director', 'data' => $sucursal->getEmail(), 'attr' => array('class' => 'form-control')))
                ->add('casilla', 'text', array('required' => false, 'label' => 'Casilla postal del CEA', 'data' => $sucursal->getCasilla(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}', 'maxlength' => '10')))
                ->add('turno', 'choice', array('label' => 'Turno', 'required' => true, 'choices' => $turnosArray, 'data' => $sucursal->getTurnoTipo() ? $sucursal->getTurnoTipo()->getId() : 0, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar cambios', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            //Actualizacion en la tabla maestro inscripcion
            $iesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($form['idSucursal']);

            $iesucursal->setTelefono1($form['telefono1']);
            $iesucursal->setTelefono2($form['telefono2']);
            $iesucursal->setEmail(mb_strtolower($form['email'], 'utf-8'));
            $iesucursal->setCasilla($form['casilla']);
            $iesucursal->setReferenciaTelefono2(mb_strtoupper('DIRECTOR/A', 'utf-8'));
            $iesucursal->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));
            $em->persist($iesucursal);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron modificados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_ceducativa_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('herramienta_ceducativa_index'));
        }
    }
    
    public function gessubsemAction() {

        $iesubsea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getAllSucursalTipo($this->session->get('ie_id'));
        //$iesubsea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getAllSucursalTipo1($this->session->get('ie_id'),[2017,2018],'0',[2,3]);
        //dump($iesubsea);die;
        //return $this->render($this->session->get('pathSystem') . ':Centroeducativo:selgessubsem.html.twig', array('iesubsea' => $iesubsea));
        
        return $this->render($this->session->get('pathSystem') . ':Centroeducativo:selgessubsem.html.twig', array(
            'iesubsea' => $iesubsea,
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la actualizacion del edificio educativo del sub cea
    // PARAMETROS: routing, jurisdiccionGeograficaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormRegistroJuridiccionGeografica($routing, $jurisdiccionGeograficaId) {
        $em = $this->getDoctrine()->getManager();
        $entidadJurisdiccionGeografica = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneBy(array('id' => $jurisdiccionGeograficaId));
        
        $entidadDepartamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1), array('id' => 'asc'));
        //dump($entidadDepartamento);die;
        $entidadDep = array();
        foreach ($entidadDepartamento as $dato)
        {
           $entidadDep[$dato->getCodigo()] = $dato->getLugar();
        }
        $departamentoId = null;
        $entidadProvincia = array();
        $provinciaId = null;
        $entidadMunicipio = array();
        $municipioId = null;
        $entidadCanton = array();
        $cantonId = null;
        $entidadLocalidad = array();
        $localidadId = null;
        $entidadDistrito = array();
        $distritoId = null;
        $direccion = "";
        $zona = "";        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('departamento', 'choice', array('label' => 'Departamento', 'empty_value' => 'Seleccione Departamento', 'choices' => $entidadDep, 'data' => $departamentoId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaProvincia(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'empty_value' => 'Seleccione Provincia', 'choices' => $entidadProvincia, 'data' => $provinciaId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaMunicipio(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'empty_value' => 'Seleccione Municipio', 'choices' => $entidadMunicipio, 'data' => $municipioId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaCanton(this.value)')))
                ->add('canton', 'choice', array('label' => 'Canton', 'empty_value' => 'Seleccione Canton', 'choices' => $entidadCanton, 'data' => $cantonId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaLocalidad(this.value)')))
                ->add('localidad', 'choice', array('label' => 'Localidad', 'empty_value' => 'Seleccione Localidad', 'choices' => $entidadLocalidad, 'data' => $localidadId, 'attr' => array('class' => 'form-control', 'required' => true)))
                ->add('distrito', 'choice', array('label' => 'Distrito Educativo', 'empty_value' => 'Seleccione Distrito', 'choices' => $entidadDistrito, 'data' => $distritoId, 'attr' => array('class' => 'form-control', 'required' => true)))
                ->add('subcea', 'text', array('label' => 'Nombre Sub Centro', 'attr' => array('value' => $zona, 'class' => 'form-control', 'placeholder' => 'Nombre del Sub Centro', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('direccion', 'text', array('label' => 'Direccion', 'attr' => array('value' => $direccion, 'class' => 'form-control', 'placeholder' => 'Dirección', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('zona', 'text', array('label' => 'Zona', 'attr' => array('value' => $zona, 'class' => 'form-control', 'placeholder' => 'Zona', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('search', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funsion que lista verifica si el id de jurisdiccion geografica del sub centro es igual al del c.e.a. principal
    // PARAMETROS: por POST  institucionEducativaSucursalId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function verIgualdadJurisdiccionSucursal($institucionEducativaSucursalId){
        //dump($institucionEducativaSucursalId);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select ies.le_juridicciongeografica_id as sucursal_jurisdiccion_id, jg.id as cea_jurisdiccion_id from institucioneducativa_sucursal as ies
            inner join institucioneducativa as ie on ie.id = ies.institucioneducativa_id
            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            where ies.id = ".$institucionEducativaSucursalId."
        ");      
        $query->execute();
        $entity = $query->fetchAll();
        if(count($entity)>0){
            if ($entity[0]['sucursal_jurisdiccion_id'] == $entity[0]['cea_jurisdiccion_id']) {
                $msg = array('0'=>true, '1'=>'El registro de jurisdicción geográfica no fue registrado hasta la fecha');
            } else {
                $msg = array('0'=>false, '1'=>'El registro de jurisdicción geográfica ya fue registrada anteriormente');
            }
        } else {
            $msg = array('0'=>false, '1'=>'No existe el registro solicitado');
        }
        return $msg;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que guarda el nombre del sub centro y jurisdiccion geografica segun censo 2001
    // PARAMETROS: por POST  localidadId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function saveJurisdiccionSubCeaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $session = $request->getSession();
        $id_usuario = $session->get('userId');
        //dump($session->get('ie_suc_id'));die;
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $idiesuc = $session->get('ie_suc_id');
            $form = $request->get('form');
            // dump($form);die;
            if ($form) {
                $departamentoId = $form['departamento'];
                $provinciaId = $form['provincia'];
                $municipioId = $form['municipio'];
                $cantonId = $form['canton'];
                $localidadId = $form['localidad'];
                $distritoId = $form['distrito'];
                $subcea = $form['subcea'];
                $direccion = $form['direccion'];
                $zona = $form['zona'];
            } else {
                $em->getConnection()->rollback();
                return $this->redirectToRoute('herramienta_ceducativa_seleccionar_cea');
            }
            $entityInstitucionEducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('id' => $idiesuc));
            $entityLocalidadLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $localidadId));
            $entityDistritoLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $distritoId));
            $entityDistritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneBy(array('id' => $entityDistritoLugarTipo->getCodigo()));
            $distritoCodigo = $entityDistritoLugarTipo->getCodigo();
            $entityValidacionGeograficaTipo = $em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneBy(array('id' => 0));
            $entityJuridiccionAcreditacionTipo = $em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->findOneBy(array('id' => 4));
            

            $idjurgeocentral = $entityInstitucionEducativaSucursal->getLeJuridicciongeografica()->getId();
            $entityJurisdiccionGeograficaCentral = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneBy(array('id' => $idjurgeocentral));
            $institucioneducativaId = $entityInstitucionEducativaSucursal->getInstitucioneducativa()->getId();
            $sucursalId = $entityInstitucionEducativaSucursal->getSucursalTipo()->getId();
            //dump($idjurgeocentral);die;
            $nuevoId = str_pad($sucursalId,2,"0",STR_PAD_LEFT);

            $query = $em->getConnection()->prepare("
                select cast(coalesce(max(cast(substring(cast(id as varchar) from (length(cast(id as varchar))-2) for 3) as integer)),0) + 1 as varchar) as id
                from jurisdiccion_geografica 
                where juridiccion_acreditacion_tipo_id = 4
            ");      
            $query->execute();
            $entityId = $query->fetchAll();
            $nuevoId = $distritoCodigo.str_pad($entityId[0]['id'],3,"0",STR_PAD_LEFT);

            $entityJurisdiccionGeografica  = new JurisdiccionGeografica(); 
            //dump($nuevoId);die;
            $entityJurisdiccionGeografica->setId($nuevoId); 
            $entityJurisdiccionGeografica->setLugarTipoLocalidad($entityLocalidadLugarTipo);           
            $entityJurisdiccionGeografica->setLugarTipoIdDistrito($distritoId);
            $entityJurisdiccionGeografica->setObs('SUCURSAL SUB C.E.A.');
            $entityJurisdiccionGeografica->setDistritoTipo($entityDistritoTipo);
            $entityJurisdiccionGeografica->setDireccion(mb_strtoupper($direccion, 'UTF-8'));
            $entityJurisdiccionGeografica->setZona(mb_strtoupper($zona, 'UTF-8'));
            $entityJurisdiccionGeografica->setJuridiccionAcreditacionTipo($entityJuridiccionAcreditacionTipo);
            $entityJurisdiccionGeografica->setValidacionGeograficaTipo($entityValidacionGeograficaTipo);
            $entityJurisdiccionGeografica->setFechaRegistro($fechaActual);
            $entityJurisdiccionGeografica->setUsuarioId($id_usuario);
            $em->persist($entityJurisdiccionGeografica);

            
            $entityInstitucionEducativaSucursal->setNombreSubcea(mb_strtoupper($subcea, 'UTF-8'));
            $entityInstitucionEducativaSucursal->setLeJuridicciongeografica($entityJurisdiccionGeografica);
            $em->persist($entityInstitucionEducativaSucursal);

            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('successMsg', 'Se guardo el registro correctamente.');
            return $this->redirectToRoute('herramienta_ceducativa_seleccionar_cea');
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('errorMsg', 'Registro no guardado, intente nuevamente.');
            return $this->redirectToRoute('herramienta_ceducativa_seleccionar_cea');
        }
    }
   

    public function gessubsemopenAction(Request $request, $teid, $gestion, $subcea, $semestre, $idiesuc) {

       //dcastillo

       

       // ver si cerro operativo       

        $sesion = $request->getSession();
        $sesion->set('ie_gestion', $gestion);
        $sesion->set('ie_subcea', $subcea);
        $sesion->set('ie_per_cod', $semestre);
        $sesion->set('ie_suc_id', $idiesuc);
        $estadoOperativo = false;
        $rutaObservaciones = "";

        //
        /*$em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select count(*) as cierre from registro_consolidacion_alt_2024 where unidad_educativa = " . $this->session->get('ie_id'));
        $query->execute();
        $cierre2024 = $query->fetchAll();

        $especializadoscierre = false;
        if ($cierre2024[0]['cierre'] > 0){
            $especializadoscierre = true;
        }*/
      
        //80660237

        //$especializadoscierre = false;
        $especializadoscierre = $this->get('funciones')->verificarApEspecializadosCerrado($this->session->get('ie_id'),$gestion,$this->session->get('ie_per_cod'));
        //$especializadoscierre = $this->get('funciones')->verificarApEspecializadosCerrado('20680003',$gestion,$this->session->get('ie_per_cod'));

        
        if ($subcea < 0){
            return $this->redirectToRoute('herramienta_ceducativa_seleccionar_cea');
        }
        //test

        $em = $this->getDoctrine()->getManager();

        // $entidadInstitucionEducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('id' => $idiesuc));
        // $jurisdiccionGeograficaId = $entidadInstitucionEducativaSucursal->getLeJuridicciongeografica()->getId();

        $valIgualdadJurisdiccionSucursal = $this->verIgualdadJurisdiccionSucursal($idiesuc);
        //dump($valIgualdadJurisdiccionSucursal);die;

        if ($subcea > 0 and $valIgualdadJurisdiccionSucursal[0]){ 
            //dump($gestion);dump($subcea);dump($semestre);dump($idiesuc);die;            
            return $this->render($this->session->get('pathSystem') . ':Principal:registrosubcea.html.twig', array(
                'form' => $this->creaFormRegistroJuridiccionGeografica('herramienta_ceducativa_registro_jurisdiccion_subcea_save',0)->createView(),
            ));
        } 
        
        switch ($semestre) {
            case 1:
                $sesion->set('ie_per_nom', 'anual');
                break;
            case 2:
                $sesion->set('ie_per_nom', 'Primer Semestre');
                break;
            case 3:
                $sesion->set('ie_per_nom', 'Segundo Semestre');
                break;
        }
        // dump($teid);die;
        $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($idiesuc);
        
        vuelve:

        //dump($teid);die;
        switch ($teid) {
            case 0://MODO EDICION 
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '!Modo edición!');
                break;            
            case 6: //REGULARIZACION NOTAS O INSCRIPCIONES
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '!En operativo de regularización!');
                break;
            case 7: //REGULARIZACION INSCRIPCIONES
                $sesion->set('ie_per_estado', '1');
                $sesion->set('ie_operativo', '!En operativo de regularización inscripciones!');
                break;    
            case 10: //INSCRIPCIONES - INICIO DE SEMESTRE
                /**
                 * Verificamos si el operativo esta dentro de plazo
                 */
                if($ies->getGestionTipo()->getId() >= 2019){
                    if($ies->getPeriodoTipoId() == 2){
                        $operativo = 1;
                    }else{
                        $operativo = 3;
                    }
                    $operativoControl = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                                        ->select('oc')
                                        ->where('oc.operativoTipo =' . $operativo)
                                        ->andWhere('oc.distritoTipo = ' . $ies->getInstitucioneducativa()->getLejuridicciongeografica()->getDistritoTipo()->getId())
                                        ->andWhere('oc.gestionTipo = ' . $ies->getGestionTipo()->getId())
                                        ->getQuery()
                                        ->getResult();
                    foreach($operativoControl as $o){
                        $datos = json_decode($o->getObs(),true);
                        foreach ($datos as $d){
                            if($ies->getInstitucioneducativa()->getId() == json_decode($d,true)['ie'] and $ies->getSucursalTipo()->getId() == json_decode($d,true)['suc']){
                                $sesion->set('ie_per_estado', '1');
                                if(strtotime(date('d-m-Y')) > strtotime($o->getFechaFin()->format('d-m-Y'))){
                                    $obs = $this->verificarOperativo($request);
                                    //dump($obs);die;
                                    if($obs == 1){
                                        $sesion->set('ie_per_estado', '0');
                                        $sesion->set('ie_operativo', '!Operativo inscripciones, fuera de plazo. Venció el '. $o->getFechaFin()->format('d-m-Y') . ', contactese con su tecnico SIE.!');
                                        $estadoOperativo = true;
                                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativoinicio";
                                    }else{
                                        $teid = 99;
                                        $estadoOperativo = false;
                                        $rutaObservaciones = "!TOME NOTA: El operativo inscripciones fué cerrado automaticamente, pues su plazo para el cierre venció el ". $o->getFechaFin()->format('d-m-Y')." y no presentó observaciones.";
                                        goto vuelve;
                                    }
                                    
                                }else{
                                    $sesion->set('ie_per_estado', '1');
                                    $sesion->set('ie_operativo', '¡En operativo inscripciones!');                
                                }
                            }
                        }
                    }
                }elseif($ies->getGestionTipo()->getId() == 2018){
                    $sesion->set('ie_per_estado', '1');
                    $obs = $this->verificarOperativo($request);
                    if($obs == 1){
                        $sesion->set('ie_per_estado', '0');
                        $sesion->set('ie_operativo', '!Operativo inscripciones, fuera de plazo. Contactese con su tecnico SIE.!');
                        $estadoOperativo = true;
                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativoinicio";
                    }else{
                        $teid = 99;
                        $estadoOperativo = false;
                        $rutaObservaciones = "!TOME NOTA: El operativo inscripciones fué cerrado automaticamente, pues encuentra fuera de plazo y no presentó observaciones.";
                        goto vuelve;
                    }
                }else{
                    $sesion->set('ie_per_estado', '1');
                    $sesion->set('ie_operativo', '¡En operativo inscripciones!');
                }
                break;
            case 11: //NOTAS - FIN DE SEMESTRE
                /**
                 * Verificamos si el operativo esta dentro de plazo
                 */
                if($ies->getGestionTipo()->getId() >= 2019){
                    if($ies->getPeriodoTipoId() == 2){
                        $operativo = 2;
                    }else{
                        $operativo = 4;
                    }
                    $operativoControl = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                                        ->select('oc')
                                        ->where('oc.operativoTipo =' . $operativo)
                                        ->andWhere('oc.distritoTipo = ' . $ies->getInstitucioneducativa()->getLejuridicciongeografica()->getDistritoTipo()->getId())
                                        ->andWhere('oc.gestionTipo = ' . $ies->getGestionTipo()->getId())
                                        ->getQuery()
                                        ->getResult();
                    foreach($operativoControl as $o){
                        $datos = json_decode($o->getObs(),true);
                        foreach ($datos as $d){
                            if($ies->getId() == json_decode($d,true)['ies']){
                                $sesion->set('ie_per_estado', '2');
                                if(strtotime(date('d-m-Y')) > strtotime($o->getFechaFin()->format('d-m-Y'))){
                                    $obs = $this->verificarOperativo($request);
                                    dump($obs);die;
                                    if($obs == 1){
                                        $sesion->set('ie_per_estado', '0');
                                        $sesion->set('ie_operativo', '!Operativo fin de semestre (notas), fuera de plazo. Vencio el '. $o->getFechaFin()->format('d-m-Y') . ', contactese con su tecnico SIE.!');
                                        $estadoOperativo = true;
                                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativo";
                                    }else{
                                        $teid = 99;
                                        $estadoOperativo = false;
                                        $rutaObservaciones = "!TOME NOTA: El operativo fin de semestre (notas) fué cerrado automaticamente, pues su plazo para el cierre venció el ". $o->getFechaFin()->format('d-m-Y')." y no presentó observaciones.";
                                        goto vuelve;
                                    }
                                }else{
                                    $sesion->set('ie_per_estado', '2');
                                    $sesion->set('ie_operativo', '¡En operativo notas!');
                                }
                            }
                        }
                    }
                }elseif($ies->getGestionTipo()->getId() == 2018){
                    $sesion->set('ie_per_estado', '2');
                    $obs = $this->verificarOperativo($request);
                    if($obs == 1){
                        $sesion->set('ie_per_estado', '0');
                        $sesion->set('ie_operativo', '!Operativo fin de semestre (notas), fuera de plazo. Contactese con su tecnico SIE.!');
                        $estadoOperativo = true;
                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativoinicio";
                    }else{
                        $teid = 99;
                        $estadoOperativo = false;
                        $rutaObservaciones = "!TOME NOTA: El operativo fin de semestre (notas) fué cerrado automaticamente, pues se encuentra fuera de plazo y no presentó observaciones.";
                        goto vuelve;
                    }
                }else{
                    $sesion->set('ie_per_estado', '2');
                    $sesion->set('ie_operativo', '¡En operativo notas!');
                }
                break;
            case 12: //INSCRIPCIONES Y NOTAS - INICIO DE SISTEMA UNA SOLA VEZ EN LA VIDA -MODO EDICION
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡En operativo de inicio de sistema academico (inscripciones y notas)!');
                break;  
            case 13: //INSCRIPCIONES Y NOTAS - POR MIGRACION DE SISTEMA UNA SOLA VEZ EN LA VIDA -MODO EDICION
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡Operativo unico para regularización de información, gestiones pasadas!');
                $em->getConnection()->beginTransaction();
                $db = $em->getConnection();
                try {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();
                    $iest = new InstitucioneducativaSucursalTramite();
                    $iest->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($idiesuc));            
                    $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('0'));
                    $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('5'));
                    $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));

                    //EXTRAER COD DISTRITO
                    $query = "SELECT get_ie_distrito_id(".$this->session->get('ie_id').");";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $podis = $stmt->fetchAll();
                    foreach ($podis as $p){
                        $lugarestipoid = $p["get_ie_distrito_id"];           
                    }
                    $lugarids = explode(",", $lugarestipoid);
        //                $dep_id = substr($lugarids[1],0,strlen($lugarids[1])-1);
                    $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
                    $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                    $iest->setDistritoCod($dis_cod->getCodigo());
                    $iest->setFechainicio(new \DateTime('now'));
                    $iest->setUsuarioIdInicio($this->session->get('userId'));
                    $em->persist($iest);
                    $em->flush();
                    $em->getConnection()->commit();
                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    return $this->redirectToRoute('principal_web');
                }
                //return $this->redirectToRoute('principal_web');
                break;
            case 99: //SOLO LECTURA
                $sesion->set('ie_per_estado', '2'); //dcastillo
                $sesion->set('ie_operativo', '!En modo vista!');
                break;
            case 100: //MAESTRO DE UNIDAD EDUCATIVA ALTER
                /**
                 * Verificamos si el operativo esta dentro de plazo
                 */
               
                if($ies->getGestionTipo()->getId() >= 2019){
                    if($ies->getPeriodoTipoId() == 2){
                        $operativo = 2;
                    }else{
                        $operativo = 4;
                    }
                    $operativoControl = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                                        ->select('oc')
                                        ->where('oc.operativoTipo =' . $operativo)
                                        ->andWhere('oc.distritoTipo = ' . $ies->getInstitucioneducativa()->getLejuridicciongeografica()->getDistritoTipo()->getId())
                                        ->andWhere('oc.gestionTipo = ' . $ies->getGestionTipo()->getId())
                                        ->getQuery()
                                        ->getResult();
                    foreach($operativoControl as $o){
                        $datos = json_decode($o->getObs(),true);
                        foreach ($datos as $d){
                            if($ies->getId() == json_decode($d,true)['ies']){
                                $sesion->set('ie_per_estado', '3');
                                if(strtotime(date('d-m-Y')) > strtotime($o->getFechaFin()->format('d-m-Y'))){
                                    $obs = $this->verificarOperativo($request);
                                    if($obs = 1){
                                        $sesion->set('ie_per_estado', '0');
                                        $sesion->set('ie_operativo', '!Operativo fin de semestre (notas), fuera de plazo. Venció el '. $o->getFechaFin()->format('d-m-Y') . ', contactese con su tecnico SIE.!');
                                        $estadoOperativo = true;
                                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativo";
                                    }else{
                                        $teid = 99;
                                        $estadoOperativo = false;
                                        $rutaObservaciones = "!TOME NOTA: El operativo fin de semestre (notas) fué cerrado automaticamente, pues su plazo para el cierre venció el ". $o->getFechaFin()->format('d-m-Y')." y no presentó observaciones.";
                                        goto vuelve;
                                    }
                                }else{
                                    $sesion->set('ie_per_estado', '3');
                                    $sesion->set('ie_operativo', '¡En operativo fin de semestre (notas)!');                
                                }
                            }
                        }
                    }
                }elseif($ies->getGestionTipo()->getId() == 2018){
                    $sesion->set('ie_per_estado', '3');
                    $obs = $this->verificarOperativo($request);
                    if($obs == 1){
                        $sesion->set('ie_per_estado', '0');
                        $sesion->set('ie_operativo', '!Operativo fin de semestre (notas), fuera de plazo. Contactese con su tecnico SIE.!');
                        $estadoOperativo = true;
                        $rutaObservaciones = "herramienta_alter_reporte_observacionesoperativoinicio";
                    }else{
                        $teid = 99;
                        $estadoOperativo = false;
                        $rutaObservaciones = "!TOME NOTA: El operativo fin de semestre (notas) fué cerrado automaticamente, pues se encuentra fuera de plazo y no presentó observaciones.";
                        goto vuelve;
                    }
                }else{
                    $sesion->set('ie_per_estado', '3');
                    $sesion->set('ie_operativo', '¡En operativo fin de semestre (notas)!');                
                }
                return $this->redirectToRoute('herramienta_alter_notas_maestro_index');
                break;
            default:
                $sesion->set('ie_per_estado', '0');
        }
        //dump($estadoOperativo,$rutaObservaciones);die;
        // to resolve the operatvio CEPEAD only to 2 weeks 
        /*if($this->session->get('ie_id')==80730796){
            $sesion->set('ie_per_estado', '3');
            $sesion->set('ie_operativo', '!En operativo de regularización!');
        }*/

        return $this->render($this->session->get('pathSystem') . ':Principal:menuprincipal.html.twig',array('estadoOperativo'=>$estadoOperativo,'rutaObservaciones'=>$rutaObservaciones,'gestion'=>$request->get('gestion'), 'especializadoscierre' => $especializadoscierre ));
    }

    public function verificarOperativo($request) {
        
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        $gestion = 2019;
        $msg=null;
        
        try {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();
            $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($sesion->get('ie_suc_id'));            
            $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findByInstitucioneducativaSucursal($ies);
            
            if ($iest){
                //dump($sesion->get('ie_per_estado'));die;
                if ($sesion->get('ie_per_estado') == '1'){//INICIO INSCRIPCIONES
                    //MIGRANDO DATOS DE SOCIO ECONOMICOS DEL ANTERIOR PERIODO AL ACTUAL PERIODO
                    $gestant = $this->session->get('ie_gestion');
                    $perant = $this->session->get('ie_per_cod');
                    if ($this->session->get('ie_per_cod') == '3'){
                        //$gestant = $this->session->get('ie_gestion');
                        $perant = 2;
                    }else{
                         if ($this->session->get('ie_per_cod') == '2'){
                            $gestant = intval($this->session->get('ie_gestion'))-1;
                            //$perant = 2;
                        }
                    }

                    // $query = "select count(a.estudiante_inscripcion_id) 
                    //             from estudiante_inscripcion_socioeconomico_alternativa a 
                    //             inner join estudiante_inscripcion b on b.id = a.estudiante_inscripcion_id
                    //             inner join institucioneducativa_curso c on c.id = b.institucioneducativa_curso_id
                    //             inner join institucioneducativa d on d.id = c.institucioneducativa_id
                    //             inner join institucioneducativa_sucursal e on e.institucioneducativa_id = d.id
                    //             where c.institucioneducativa_id = '".$this->session->get('ie_id')."'
                    //             and e.gestion_tipo_id = 2017 and e.periodo_tipo_id = 3";                    
                    // $obs= $db->prepare($query);
                    // $params = array();
                    // $obs->execute($params);
                    // $socioeco = $obs->fetchAll();
                    // //dump($socioeco);die;
                    // if (!$socioeco){
                    //     $query = "select * from sp_genera_migracion_socioeconomicos_alter('".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$gestant."','".$perant."','".$this->session->get('ie_gestion')."','".$this->session->get('ie_per_cod')."','');";
                    //     $obs= $db->prepare($query);
                    //     $params = array();
                    //     $obs->execute($params);
                    // }
                    //MIGRANDO DATOS DE SOCIO ECONOMICOS DEL ANTERIOR PERIODO AL ACTUAL PERIODO

                    $query = "select * from sp_validacion_alternativa_ig_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    //dump($query);die;
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        $msg = 1; 
                    }else{
                        $msg = 0;
                        if ($iest[0]->getTramiteEstado()->getId() == '11'){//Aceptación de apertura Inicio de Semestre
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('12'));//¡Inicio de Semestre - Cerrado!                           
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                if($ies->getPeriodoTipoId()==2){
                                    $operativo = 1;
                                }else{
                                    $operativo = 3;
                                } 
                                $reg = $this->registroConsolidacion($ies,$operativo,'registro');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '7'){//Autorizado para regularización(Inicio de Semestre)
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('9'));//Inicio de Semestre Regularizado - Cerrado                             
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                            /**
                             * Registro de la regularizacion en consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                    }
                    //dump($msg);die;
                }
                if ($sesion->get('ie_per_estado') == '2'){//FIN NOTAS
                    $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($ies->getInstitucioneducativa()->getId() == 80730796 and $iest[0]->getTramiteEstado()->getId() == '13'){
                        $observaciones = "";
                    }
                    if ($observaciones){
                        $msg = 1;
                    }else{
                        $msg = 0;
                        if ($iest[0]->getTramiteEstado()->getId() == '6'){//¡En regularización notas!
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('8'));//Ver regularización notas terminada                          
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la regularizacion en consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '13'){//¡En notas!
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('14'));//Ver notas terminadas
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                if($ies->getPeriodoTipoId()==2){
                                    $operativo = 2;
                                }else{
                                    $operativo = 4;
                                } 
                                $reg = $this->registroConsolidacion($ies,$operativo,'registro');
                            }
                        } 
                    }
                }
                if ($sesion->get('ie_per_estado') == '3'){//OPERATIVO DE MODO REGULARIZACION     
                    $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        $msg = 1;
                    }
                    else{
                        $msg = 0;                
                        if ($iest[0]->getTramiteEstado()->getId() == '6'){//Autorizado para regularización(Fin de Semestre)                           
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('8'));//Fin de Semestre Regularizado - Cerrado                            
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            //Registro de la regularizacion en consolidacion a partir de la gestion 2019
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '5'){//Autorizado para regularización gestión pasada                          
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('10'));//Fin de regularizacion gestión pasada
                            //$iestvar->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('25'));                                
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                        }
                    }
                }
            }else{//EN CASO QUE LA SUCURSAL PERIODO NO TENG ASIGNADO UN PERIODO TRAMITE               
                $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        $msg = 1;
                    }
                    else{        
                        $msg=0;          
                        $iest = new InstitucioneducativaSucursalTramite();
                        $iest->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($sesion->get('ie_suc_id')));            
                        $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('0'));
                        $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('14'));//¡Fin de Semestre - Cerrado!
                        $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));

                        //EXTRAER COD DISTRITO
                        $query = "SELECT get_ie_distrito_id(".$this->session->get('ie_id').");";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $podis = $stmt->fetchAll();
                        foreach ($podis as $p){
                            $lugarestipoid = $p["get_ie_distrito_id"];           
                        }
                        $lugarids = explode(",", $lugarestipoid);
                        $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
                        $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                        $iest->setDistritoCod($dis_cod->getCodigo());
                        $iest->setFechainicio(new \DateTime('now'));
                        $iest->setUsuarioIdInicio($this->session->get('userId'));
                        $em->persist($iest);
                        $em->flush(); 
                    }
                    
            }            
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();

        }
        return $msg;
    }

    public function menuprincipalopenAction() {
        return $this->render($this->session->get('pathSystem') . ':Principal:menuprincipal.html.twig');
    }

//    public function generarinicioAction($gestion, $subcea, $semestre) {
//        $em = $this->getDoctrine()->getManager();
//        if ($semestre == '2') {
//            $semestre = '3';
//        }
//        if ($semestre == '3') {
//            $gestion = intval($gestion) + 1;
//            $semestre = '2';
//        }
//        return $this->redirectToRoute('principal_web');
//    }
    
public function paneloperativosAction(Request $request) {//EX LISTA DE CEAS CERRADOS
    
    $id_usuario = $this->session->get('userId');
    if (!isset($id_usuario)) {
        return $this->redirect($this->generateUrl('login'));
    }
    $form = $this->ceaspendientesForm($this->session->get('roluser'),'operativo');
    $form_exp = $this->exportaroperativoForm();
    return $this->render($this->session->get('pathSystem') . ':Principal:listaceacerradonew.html.twig', array(
        'form' => $form->createView(),
        'rol' => $this->session->get('roluser'),
        'id_usuario' =>$id_usuario,
        'form_exp' => $form_exp->createView(),
        ));
}

public function paneloperativoslistaAction(Request $request) //EX LISTA DE CEAS CERRADOS
{   
    $usuario_lugar = $this->session->get('roluserlugarid');
    $rol = $request->get('rol');
    $id_usuario = $request->get('id_usuario');
    $em = $this->getDoctrine()->getManager();
    if(!$request->get('gestion'))
    {
        $gestion = 'select id from gestion_tipo';
    }else{
        $gestion = $request->get('gestion');
    }
    
    if ($rol == 8 )
    {
        if(!$request->get('departamento'))
        {
            $departamento = 'select id from departamento_tipo';
        }else{
            $departamento = $request->get('departamento');
        }

        /* $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo_id , te.tramite_estado, CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
            JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
            JOIN tramite_estado te ON te.id=iest.tramite_estado_id
            JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
            JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
            JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
            JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
            WHERE ies.gestion_tipo_id IN (". $gestion .")
            AND ie.estadoinstitucion_tipo_id=10
            AND ie.institucioneducativa_acreditacion_tipo_id=1
            AND ie.institucioneducativa_tipo_id=2
            AND CAST (lt1.codigo as INT) IN (" . $departamento.")"); */
            $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo , te.tramite_estado,
                        CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id,ies.periodo_tipo_id,iest.tramite_tipo_id,iest.periodo_estado_id,o.fecha_inicio,o.fecha_fin,case WHEN te.id in (12,9,8,14,10) THEN 'OPERATIVO CERRADO'WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 'OPERATIVO FUERA DE PLAZO' WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 'OPERATIVO EN PROCESO' WHEN ies.gestion_tipo_id < 2018  THEN 'GESTION PASADA CERRADA' END as operativo_estado,CASE WHEN te.id in (12,9,8,14,10) THEN 0 WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 1 WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 0 WHEN ies.gestion_tipo_id < 2018  THEN 0 END as operativo_clave
                        FROM institucioneducativa ie
                        JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
                        JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
                        JOIN tramite_estado te ON te.id=iest.tramite_estado_id
                        JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
                        JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
                        JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
                        JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
                        LEFT JOIN (SELECT json_array_elements_text(obs::json)::json->>'ie' as ie_id,json_array_elements_text(obs::json)::json->>'suc' as suc,oc.gestion_tipo_id,oc.fecha_inicio,oc.fecha_fin,oc.operativo_tipo_id
                        FROM operativo_control oc)o on ie.id=o.ie_id::INTEGER and ies.sucursal_tipo_id=o.suc::INTEGER and ies.gestion_tipo_id=o.gestion_tipo_id and ((ies.periodo_tipo_id=2 and o.operativo_tipo_id=1 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id)or (ies.periodo_tipo_id=2 and o.operativo_tipo_id=2 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=3 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=4 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id))
                        WHERE ies.gestion_tipo_id IN (". $gestion .")
                        AND ie.estadoinstitucion_tipo_id=10
                        AND ie.institucioneducativa_acreditacion_tipo_id=1
                        AND ie.institucioneducativa_tipo_id=2
                        AND CAST (lt1.codigo as INT) IN (" . $departamento.")");
    }elseif($rol == 7){
        /* $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo_id , te.tramite_estado, CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
            JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
            JOIN tramite_estado te ON te.id=iest.tramite_estado_id
            JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
            JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
            JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
            JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
            WHERE ies.gestion_tipo_id IN (". $gestion .")
            AND ie.estadoinstitucion_tipo_id=10
            AND ie.institucioneducativa_acreditacion_tipo_id=1
            AND ie.institucioneducativa_tipo_id=2
            AND lt1.id=" . $usuario_lugar); */
            $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo , te.tramite_estado,
                        CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id,ies.periodo_tipo_id,iest.tramite_tipo_id,iest.periodo_estado_id,o.fecha_inicio,o.fecha_fin,case WHEN te.id in (12,9,8,14,10) THEN 'OPERATIVO CERRADO'WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 'OPERATIVO FUERA DE PLAZO' WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 'OPERATIVO EN PROCESO' WHEN ies.gestion_tipo_id < 2018  THEN 'GESTION PASADA CERRADA' END as operativo_estado,CASE WHEN te.id in (12,9,8,14,10) THEN 0 WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 1 WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 0 WHEN ies.gestion_tipo_id < 2018  THEN 0 END as operativo_clave
                        FROM institucioneducativa ie
                        JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
                        JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
                        JOIN tramite_estado te ON te.id=iest.tramite_estado_id
                        JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
                        JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
                        JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
                        JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
                        LEFT JOIN (SELECT json_array_elements_text(obs::json)::json->>'ie' as ie_id,json_array_elements_text(obs::json)::json->>'suc' as suc,oc.gestion_tipo_id,oc.fecha_inicio,oc.fecha_fin,oc.operativo_tipo_id
                        FROM operativo_control oc)o on ie.id=o.ie_id::INTEGER and ies.sucursal_tipo_id=o.suc::INTEGER and ies.gestion_tipo_id=o.gestion_tipo_id and ((ies.periodo_tipo_id=2 and o.operativo_tipo_id=1 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id)or (ies.periodo_tipo_id=2 and o.operativo_tipo_id=2 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=3 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=4 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id))
                        WHERE ies.gestion_tipo_id IN (". $gestion .")
                        AND ie.estadoinstitucion_tipo_id=10
                        AND ie.institucioneducativa_acreditacion_tipo_id=1
                        AND ie.institucioneducativa_tipo_id=2
                        AND lt1.id=" . $usuario_lugar);

    }elseif($rol == 10){
        /* $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo_id , te.tramite_estado, CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
            JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
            JOIN tramite_estado te ON te.id=iest.tramite_estado_id
            JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
            JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
            JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
            JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
            WHERE ies.gestion_tipo_id IN (". $gestion .")
            AND ie.estadoinstitucion_tipo_id=10
            AND ie.institucioneducativa_acreditacion_tipo_id=1
            AND ie.institucioneducativa_tipo_id=2
            AND lt.id=" . $usuario_lugar); */
        $query = $em->getConnection()->prepare("SELECT lt1.lugar as departamento, lt.codigo, ie.id, ie.institucioneducativa, ies.sucursal_tipo_id, ies.gestion_tipo_id, CASE WHEN ies.periodo_tipo_id=2 THEN 'PRIMERO' WHEN ies.periodo_tipo_id=3 THEN 'SEGUNDO' WHEN ies.periodo_tipo_id=1 THEN 'ANUAL' END AS periodo_tipo , te.tramite_estado,
                    CASE WHEN ies.gestion_tipo_id >2017 THEN tt.tramite_tipo ELSE 'OPERATIVO CERRADO' END AS tramite_tipo,te.id AS te_id,ies.periodo_tipo_id,iest.tramite_tipo_id,iest.periodo_estado_id,o.fecha_inicio,o.fecha_fin,case WHEN te.id in (12,9,8,14,10) THEN 'OPERATIVO CERRADO'WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 'OPERATIVO FUERA DE PLAZO' WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 'OPERATIVO EN PROCESO' WHEN ies.gestion_tipo_id < 2018  THEN 'GESTION PASADA CERRADA' END as operativo_estado,CASE WHEN te.id in (12,9,8,14,10) THEN 0 WHEN (ies.gestion_tipo_id > 2018 and CURRENT_DATE > o.fecha_fin) or ies.gestion_tipo_id=2018 THEN 1 WHEN ies.gestion_tipo_id > 2018 and CURRENT_DATE <= o.fecha_fin THEN 0 WHEN ies.gestion_tipo_id < 2018  THEN 0 END as operativo_clave
                    FROM institucioneducativa ie
                    JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
                    JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
                    JOIN tramite_estado te ON te.id=iest.tramite_estado_id
                    JOIN tramite_tipo tt ON iest.tramite_tipo_id=tt.id
                    JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
                    JOIN lugar_tipo lt ON le.lugar_tipo_id_distrito=lt.id
                    JOIN lugar_tipo lt1 ON lt.lugar_tipo_id=lt1.id
                    LEFT JOIN (SELECT json_array_elements_text(obs::json)::json->>'ie' as ie_id,json_array_elements_text(obs::json)::json->>'suc' as suc,oc.gestion_tipo_id,oc.fecha_inicio,oc.fecha_fin,oc.operativo_tipo_id
                    FROM operativo_control oc)o on ie.id=o.ie_id::INTEGER and ies.sucursal_tipo_id=o.suc::INTEGER and ies.gestion_tipo_id=o.gestion_tipo_id and ((ies.periodo_tipo_id=2 and o.operativo_tipo_id=1 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id)or (ies.periodo_tipo_id=2 and o.operativo_tipo_id=2 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=3 and iest.periodo_estado_id=1 and ies.gestion_tipo_id=o.gestion_tipo_id) or (ies.periodo_tipo_id=3 and o.operativo_tipo_id=4 and iest.periodo_estado_id=2 and ies.gestion_tipo_id=o.gestion_tipo_id))
                    WHERE ies.gestion_tipo_id IN (". $gestion .")
                    AND ie.estadoinstitucion_tipo_id=10
                    AND ie.institucioneducativa_acreditacion_tipo_id=1
                    AND ie.institucioneducativa_tipo_id=2
                    AND lt.id=" . $usuario_lugar);

    }
    
    $query->execute();
    $entity = $query->fetchAll();

    
    //$em = $this->getDoctrine()->getEntityManager();
    //$db = $em->getConnection();  
    

    /* if ($rol == '8' ){//NACIONAL
//            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));            
//            $coddis = $usuariorol[0]->getLugarTipo()->getCodigo();
//            dump($usuariorol);
//            die;
        $query = "
                    select
                    k.id as deptoid, k.lugar, a.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                    a.sucursal_tipo_id as nombre_subcea, a.gestion_tipo_id,  
                            CASE
                                WHEN a.periodo_tipo_id = 1 THEN
                                                'ANUAL'
                                            WHEN a.periodo_tipo_id = 2 THEN
                                                'PRIMERO'
                                            WHEN a.periodo_tipo_id = 3 THEN
                                                'SEGUNDO'
                                        END AS semestre,
                    a.periodo_tipo_id,
                    a.iestid as iestid,
                    a.tramite_estado,
                    a.teid as teid,
                    a.ttid as ttid,
                    a.tramite_tipo,
                    a.obs1                             
                    from
                    jurisdiccion_geografica jg 
                                    inner join (
                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                                    from lugar_tipo
                                            where lugar_nivel_id=7 
                                    ) lt 	
                                    on jg.lugar_tipo_id_distrito = lt.id
            
                                    inner join lugar_tipo k on k.id = lt.lugar_tipo_id
            
                                    inner join (
                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                            from institucioneducativa a
                                    ) ie		
                                    on jg.id = ie.le_juridicciongeografica_id
            
            inner join 
            
            (select  a.id as iestid, x.id as teid,x.obs as obs1, a.*, b.*, x.*,x1.*,x1.id as ttid
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            inner join tramite_estado x on a.tramite_estado_id = x.id
            inner join tramite_tipo x1 on a.tramite_tipo_id = x1.id
            --where b.institucioneducativa_id = '81230227'
            ) a on a.institucioneducativa_id = ie.id
            inner join (
            select  b.institucioneducativa_id, b.sucursal_tipo_id, max(cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer)) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where
            a.tramite_estado_id not in (10)
            --b.institucioneducativa_id = '81230227'
            group by b.institucioneducativa_id, b.sucursal_tipo_id
            union
            
            select b.institucioneducativa_id, b.sucursal_tipo_id, cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where 
            --b.institucioneducativa_id = '81230227' and
            a.tramite_estado_id in (5,6,7)
            
            
            ) b on a.institucioneducativa_id=b.institucioneducativa_id and a.sucursal_tipo_id=b.sucursal_tipo_id and 
            cast(cast(a.gestion_tipo_id as character varying) || cast(a.periodo_tipo_id as character varying) as integer)=b.gestionperiodo
            where a.gestion_tipo_id in (". $gestion .") and cast(k.codigo as int) in (". $departamento .")
            order by k.id, a.distrito_cod, ie.id, nombre_subcea, gestion_tipo_id, periodo_tipo_id";
    }
    
    if ($rol == '7' ){//DEPARTAMENTO            
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));            
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
//            dump($idlugarusuario);
//            die;
        $query = "
                    select
                    k.id as deptoid, k.lugar, a.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                    a.sucursal_tipo_id as nombre_subcea, a.gestion_tipo_id,  
                            CASE
                                WHEN a.periodo_tipo_id = 1 THEN
                                                'ANUAL'
                                            WHEN a.periodo_tipo_id = 2 THEN
                                                'PRIMERO'
                                            WHEN a.periodo_tipo_id = 3 THEN
                                                'SEGUNDO'
                                        END AS semestre,
                    a.periodo_tipo_id,
                    a.iestid as iestid,
                    a.tramite_estado,
                    a.teid as teid,
                    a.ttid as ttid,
                    a.tramite_tipo,
                    a.obs1                             
                    from
                    jurisdiccion_geografica jg 
                                    inner join (
                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                                    from lugar_tipo
                                            where lugar_nivel_id=7 
                                    ) lt 	
                                    on jg.lugar_tipo_id_distrito = lt.id
            
                                    inner join lugar_tipo k on k.id = lt.lugar_tipo_id
            
                                    inner join (
                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                            from institucioneducativa a
                                    ) ie		
                                    on jg.id = ie.le_juridicciongeografica_id
            
            inner join 
            
            (select  a.id as iestid, x.id as teid,x.obs as obs1, a.*, b.*, x.*,x1.*,x1.id as ttid
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            inner join tramite_estado x on a.tramite_estado_id = x.id
            inner join tramite_tipo x1 on a.tramite_tipo_id = x1.id
            --where b.institucioneducativa_id = '80480255'
            ) a on a.institucioneducativa_id = ie.id
            inner join (
            select  b.institucioneducativa_id, b.sucursal_tipo_id, max(cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer)) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where 
            a.tramite_estado_id not in (10)
            --and b.institucioneducativa_id = '80480255'
            group by b.institucioneducativa_id, b.sucursal_tipo_id
            
            
            union
            
            select b.institucioneducativa_id, b.sucursal_tipo_id, cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where 
            --b.institucioneducativa_id = '80480255' and
            a.tramite_estado_id in (5,6,7)
            
            
            ) b on a.institucioneducativa_id=b.institucioneducativa_id and a.sucursal_tipo_id=b.sucursal_tipo_id and 
            cast(cast(a.gestion_tipo_id as character varying) || cast(a.periodo_tipo_id as character varying) as integer)=b.gestionperiodo
            
            where k.id = '".$idlugarusuario."' and a.gestion_tipo_id in (". $gestion .")
            
            order by 
            k.id, a.distrito_cod,
            ie.id, nombre_subcea, gestion_tipo_id, periodo_tipo_id";
    }
    
    if ($rol == '10' ){//DISTRITO            
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));            
        $coddis = $usuariorol[0]->getLugarTipo()->getCodigo();
//            dump($coddis);
//            die;
        $query = "
                    select
                    k.id as deptoid, k.lugar, a.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                    a.sucursal_tipo_id as nombre_subcea, a.gestion_tipo_id,  
                            CASE
                                WHEN a.periodo_tipo_id = 1 THEN
                                                'ANUAL'
                                            WHEN a.periodo_tipo_id = 2 THEN
                                                'PRIMERO'
                                            WHEN a.periodo_tipo_id = 3 THEN
                                                'SEGUNDO'
                                        END AS semestre,
                    a.periodo_tipo_id,
                    a.iestid as iestid,
                    a.tramite_estado,
                    a.teid as teid,
                    a.ttid as ttid,
                    a.tramite_tipo,
                    a.obs1
                    from
                    jurisdiccion_geografica jg 
                                    inner join (
                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                                    from lugar_tipo
                                            where lugar_nivel_id=7 
                                    ) lt 	
                                    on jg.lugar_tipo_id_distrito = lt.id
            
                                    inner join lugar_tipo k on k.id = lt.lugar_tipo_id
            
                                    inner join (
                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                            from institucioneducativa a
                                    ) ie		
                                    on jg.id = ie.le_juridicciongeografica_id
            
            inner join 
            
            (select  a.id as iestid, x.id as teid,x.obs as obs1, a.*, b.*, x.*,x1.*,x1.id as ttid
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            inner join tramite_estado x on a.tramite_estado_id = x.id 
            inner join tramite_tipo x1 on a.tramite_tipo_id = x1.id 
            --where b.institucioneducativa_id = '80480255'
            ) a on a.institucioneducativa_id = ie.id
            inner join (
            select  b.institucioneducativa_id, b.sucursal_tipo_id, max(cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer)) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where 
            a.tramite_estado_id not in (10)
            --and b.institucioneducativa_id = '80480255'
            group by b.institucioneducativa_id, b.sucursal_tipo_id
            
            
            union
            
            select b.institucioneducativa_id, b.sucursal_tipo_id, cast(cast(b.gestion_tipo_id as character varying) || cast(b.periodo_tipo_id as character varying) as integer) as gestionperiodo
            from institucioneducativa_sucursal_tramite  a
            inner join institucioneducativa_sucursal b on a.institucioneducativa_sucursal_id = b.id
            where 
            --b.institucioneducativa_id = '80480255' and
            a.tramite_estado_id in (5,6,7)
            
            
            ) b on a.institucioneducativa_id=b.institucioneducativa_id and a.sucursal_tipo_id=b.sucursal_tipo_id and 
            cast(cast(a.gestion_tipo_id as character varying) || cast(a.periodo_tipo_id as character varying) as integer)=b.gestionperiodo
            
            where a.distrito_cod = '".$coddis."' and a.gestion_tipo_id in (". $gestion .")
            
            order by 
            k.id, a.distrito_cod,
            ie.id, nombre_subcea, gestion_tipo_id, periodo_tipo_id";
    }
    
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll(); */
//        dump($po);
//        die;
    //dump($entity);die;
    return $this->render($this->session->get('pathSystem') . ':Principal:tablaceaoperativopendiente.html.twig', array(
            'entities' => $entity,
        ));
    }

    /**
     * Lista de observaciones de operativos fuera de plazo
     */
    public function paneloperativosobservacionesAction(Request $request) {
    
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        //dump($request);die;
        if($request->get('periodo_estado') == 1){
            $query = $em->getConnection()->prepare("select institucioneducativa,observacion from sp_validacion_alternativa_ig_web('". $request->get('gestion') ."','". $request->get('ie') ."','". $request->get('suc') ."','". $request->get('periodo') ."')");    
        }else{
            $query = $em->getConnection()->prepare("select institucioneducativa,observacion from sp_validacion_alternativa_web('". $request->get('gestion') ."','". $request->get('ie') ."','". $request->get('suc') ."','". $request->get('periodo') ."')");    
        }
        $query->execute();
        $observacion = $query->fetchAll();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$request->get('ie'),'sucursalTipo'=>$request->get('suc'),'gestionTipo'=>$request->get('gestion')));
        
        
        return $this->render($this->session->get('pathSystem') . ':Principal:observacionesOperativo.html.twig', array(
            'entity' => $entity,
            'observacion' => $observacion,
            'periodo_estado' => $request->get('periodo_estado'),
            ));
    }



    
    public function gessubsemtramitesolicitudcambiarestadoAction($iestid) {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();
            $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->find($iestid);            
            
            $tramiteestado = $iest->getTramiteEstado();
            $estado = $tramiteestado->getId();
//            dump($estado);
//            die;            
            if ($estado == '14'){                                                           
                $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('6'));//EN REGULARIZACIÓN FIN DE SEMESTRE                
                $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('24')); 
            }    
            if ($estado == '12'){
                $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('7'));//EN REGULARIZACIÓN INICIO DE SEMESTRE
                $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('23'));                                
            }
            $iest->setFechaModificacion(new \DateTime('now'));
            $iest->setUsuarioIdModificacion($this->session->get('userId'));            
            $em->persist($iest);
            $em->flush();
            
            $em->getConnection()->commit();
            return $this->redirect($this->generateUrl('principal_web'));            
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $this->redirect($this->generateUrl('principal_web'));            
        }
    }


    public function cerraroperativoespecializadosAction(Request $request) { //dcastillo

        /*
        sp_validacion_alternativa_curso_web(
            IN igestion_id character varying,
            IN icod_ue character varying,
            IN isub_cea character varying,
            IN iperiodo_id character varying,
            IN inivel_id character varying DEFAULT '15',
            IN iciclo_id character varying DEFAULT '2',
            IN igrado_id character varying DEFAULT '3',
            IN iusuario_id character varying DEFAULT '1')
        */


        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();      
        
        $db = $em->getConnection();

        $query = "select * from sp_validacion_alternativa_curso_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
        //$query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','81981438','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";

        //dump($query); die;
        
        $obs= $db->prepare($query);
        $params = array();
        $obs->execute($params);

        //dump($obs); die;

        $observaciones = $obs->fetchAll();
        //esto devuelve las obsrvaciones
        //$observaciones = [];
        if ($observaciones){
            return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativo'));  
        }

        return $this->redirect($this->generateUrl('principal_web')); 

        /*
        else{   
           // dump('sin error'); die; 

            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();

            $gestion = $this->session->get('ie_gestion'); //2024;
            $unidad_educativa = $this->session->get('ie_id'); //81730232;
            $usuario = $this->session->get('userId'); //100;
            $sub_cea = $this->session->get('ie_subcea'); //123;
            $sucursal_id = $this->session->get('ie_suc_id'); //123;
            $tipo_operativo = 1; //1: cierre especializados


            $query = '
                INSERT INTO public.registro_consolidacion_alt_2024 ( gestion, unidad_educativa, usuario,sub_cea, tipo_operativo, sucursal_id ) 
                VALUES 
                (?,?,?,?,?,?);

            ';
            $stmt = $db->prepare($query);
            $params = array($gestion,$unidad_educativa, $usuario, $sub_cea, $tipo_operativo, $sucursal_id );
            $stmt->execute($params);
            $requisitos=$stmt->fetch();

            // grabamos en opertaivo log oficial

            $institucioneducativa_operativo_log_tipo_id = 16;
            $gestion_tipo_id= $this->session->get('ie_gestion');
            $periodo_tipo_id= 3;
            $institucioneducativa_id=$this->session->get('ie_id'); 
            $institucioneducativa_sucursal=$this->session->get('ie_subcea');
            $nota_tipo_id= 54;
            $descripcion= 'Especializados';
            $esexitoso= true;
            $esonline=true;
            $usuario=$this->session->get('userId');
            //$fecha_registro=
            $cliente_descripcion='';
            $obs = 'Alternativa';

            $query = '
                INSERT INTO institucioneducativa_operativo_log (institucioneducativa_operativo_log_tipo_id, gestion_tipo_id, periodo_tipo_id, institucioneducativa_id, institucioneducativa_sucursal, nota_tipo_id, descripcion, esexitoso, esonline, usuario, cliente_descripcion, obs)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);

            ';
            
            $stmt = $db->prepare($query);
            $params = array($institucioneducativa_operativo_log_tipo_id, $gestion_tipo_id,$periodo_tipo_id,$institucioneducativa_id, $institucioneducativa_sucursal,$nota_tipo_id, $descripcion, $esexitoso, $esonline, $usuario,$cliente_descripcion,$obs );
            $stmt->execute($params);
            $requisitos=$stmt->fetch();



           return $this->redirect($this->generateUrl('principal_web')); 
        }*/

    }

    public function cerraroperativoAction(Request $request) { //dcastillo
        
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        $gestion = 2019;
      
        //dump($request);die; no manda nada por request todo esta en la sesion
        //dump($sesion->get('ie_per_estado'));die;

        
        try {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();
            $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($sesion->get('ie_suc_id'));            
            $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findByInstitucioneducativaSucursal($ies);
            if ($iest){
                if ($sesion->get('ie_per_estado') == '1'){//INICIO INSCRIPCIONES
                    //MIGRANDO DATOS DE SOCIO ECONOMICOS DEL ANTERIOR PERIODO AL ACTUAL PERIODO

                    dump('1'); die;
                    $gestant = $this->session->get('ie_gestion');
                    $perant = $this->session->get('ie_per_cod');
                    if ($this->session->get('ie_per_cod') == '3'){
                        //$gestant = $this->session->get('ie_gestion');
                        $perant = 2;
                    }else{
                         if ($this->session->get('ie_per_cod') == '2'){
                            $gestant = intval($this->session->get('ie_gestion'))-1;
                            //$perant = 2;
                        }
                    }

                    // $query = "select count(a.estudiante_inscripcion_id) 
                    //             from estudiante_inscripcion_socioeconomico_alternativa a 
                    //             inner join estudiante_inscripcion b on b.id = a.estudiante_inscripcion_id
                    //             inner join institucioneducativa_curso c on c.id = b.institucioneducativa_curso_id
                    //             inner join institucioneducativa d on d.id = c.institucioneducativa_id
                    //             inner join institucioneducativa_sucursal e on e.institucioneducativa_id = d.id
                    //             where c.institucioneducativa_id = '".$this->session->get('ie_id')."'
                    //             and e.gestion_tipo_id = 2017 and e.periodo_tipo_id = 3";                    
                    // $obs= $db->prepare($query);
                    // $params = array();
                    // $obs->execute($params);
                    // $socioeco = $obs->fetchAll();
                    // //dump($socioeco);die;
                    // if (!$socioeco){
                    //     $query = "select * from sp_genera_migracion_socioeconomicos_alter('".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$gestant."','".$perant."','".$this->session->get('ie_gestion')."','".$this->session->get('ie_per_cod')."','');";
                    //     $obs= $db->prepare($query);
                    //     $params = array();
                    //     $obs->execute($params);
                    // }
                    //MIGRANDO DATOS DE SOCIO ECONOMICOS DEL ANTERIOR PERIODO AL ACTUAL PERIODO

                    $query = "select * from sp_validacion_alternativa_ig_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    //dump($query);die;
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                            return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativoinicio'));
                    }
                    else{    
                      
                        if ($iest[0]->getTramiteEstado()->getId() == '11'){//Aceptación de apertura Inicio de Semestre
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('12'));//¡Inicio de Semestre - Cerrado!                           
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                if($ies->getPeriodoTipoId()==2){
                                    $operativo = 1;
                                }else{
                                    $operativo = 3;
                                } 
                                $reg = $this->registroConsolidacion($ies,$operativo,'registro');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '7'){//Autorizado para regularización(Inicio de Semestre)
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('9'));//Inicio de Semestre Regularizado - Cerrado                             
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                            /**
                             * Registro de la regularizacion en consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                    }
                }
                if ($sesion->get('ie_per_estado') == '2'){//FIN NOTAS
                    //dump('2'); die;
                    //dump("select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');");
                     //die;
                    $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";

                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($ies->getInstitucioneducativa()->getId() == 80730796 and $iest[0]->getTramiteEstado()->getId() == '13'){
                        $observaciones = "";
                    }

                    $observaciones = "";

                    if ($observaciones){
                        return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativo'));                    }
                    else{

                       // dump($iest[0]->getTramiteEstado()->getId()); die;

                        if ($iest[0]->getTramiteEstado()->getId() == '6'){//¡En regularización notas!
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('8'));//Ver regularización notas terminada                          
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la regularizacion en consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '13'){//¡En notas! 13
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('14'));//Ver notas terminadas
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            /**
                             * Registro de la consolidacion a partir de la gestion 2019
                             */
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                if($ies->getPeriodoTipoId()==2){
                                    $operativo = 2;
                                }else{
                                    $operativo = 4;
                                } 
                                $reg = $this->registroConsolidacion($ies,$operativo,'registro');
                            }
                        } 
                    }
                }
                if ($sesion->get('ie_per_estado') == '3'){//OPERATIVO DE MODO REGULARIZACION     
                   // dump('3'); die;
                    $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativo'));
                    }
                    else{                        
                        if ($iest[0]->getTramiteEstado()->getId() == '6'){//Autorizado para regularización(Fin de Semestre)                           
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('8'));//Fin de Semestre Regularizado - Cerrado                            
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush();
                            //Registro de la regularizacion en consolidacion a partir de la gestion 2019
                            if($ies->getGestionTipo()->getId() >= $gestion){
                                $reg = $this->registroConsolidacion($ies,'','regularizar');
                            }
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '5'){//Autorizado para regularización gestión pasada                          
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('10'));//Fin de regularizacion gestión pasada
//                            $iestvar->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('25'));                                
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                        }
                    }                        
                }
            }else{//EN CASO QUE LA SUCURSAL PERIODO NO TENG ASIGNADO UN PERIODO TRAMITE               
                $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativo'));
                    }
                    else{                     
                        $iest = new InstitucioneducativaSucursalTramite();
                        $iest->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($sesion->get('ie_suc_id')));            
                        $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('0'));
                        $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('14'));//¡Fin de Semestre - Cerrado!
                        $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));

                        //EXTRAER COD DISTRITO
                        $query = "SELECT get_ie_distrito_id(".$this->session->get('ie_id').");";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $podis = $stmt->fetchAll();
                        foreach ($podis as $p){
                            $lugarestipoid = $p["get_ie_distrito_id"];           
                        }
                        $lugarids = explode(",", $lugarestipoid);
                        $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
                        $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                        $iest->setDistritoCod($dis_cod->getCodigo());
                        $iest->setFechainicio(new \DateTime('now'));
                        $iest->setUsuarioIdInicio($this->session->get('userId'));
                        $em->persist($iest);
                        $em->flush(); 
                    }
            }            
            $em->getConnection()->commit();

            return $this->redirect($this->generateUrl('herramienta_alter_reporte_operativo_exitoso_cerrado'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * Registro de la consolidacion/cierre del operativo
     */
    function registroConsolidacion($ies,$operativo,$tipo)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        try {              
            if($tipo == 'registro'){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('consolidacion');")->execute(); 
                $consolidacion = new Consolidacion();
                $consolidacion->setInstitucioneducativa($ies->getInstitucioneducativa());
                $consolidacion->setInstitucioneducativaSucursal($ies);
                $consolidacion->setInstitucioneducativaTipo($ies->getInstitucioneducativa()->getInstitucioneducativaTipo());
                $consolidacion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find(date('Y')));
                $consolidacion->setOperativoTipo($em->getRepository('SieAppWebBundle:OperativoTipo')->find($operativo));
                $consolidacion->setSistemaTipo($em->getRepository('SieAppWebBundle:SistemaTipo')->find(2));
                $consolidacion->setFechaRegistro(new \DateTime('now'));
                $consolidacion->setUsuarioCreacion($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                $consolidacion->setEsonline(true);
                $em->persist($consolidacion);
                $em->flush(); 
            }else{
                $consolidacion = $em->getRepository('SieAppWebBundle:Consolidacion')->findBy(array('institucioneducativaSucursal'=>$ies));
                if($consolidacion){
                    $consolidacion[0]->setFechaModificacion(new \DateTime('now'));
                    $consolidacion[0]->setUsuarioModificacion($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                    $em->flush(); 
                }
            }
            $em->getConnection()->commit();
            return true;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return false;
        }
    }   
    
    public function tramitecontinuaroperativoAction(Request $request, $iestid) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        
        try {              
            $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->find($iestid);
            $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($iest->getInstitucioneducativaSucursal()->getId());
            if ($ies){                
                $tramiteestado = $iest->getTramiteEstado()->getId();
                //dump($tramiteestado);die;
                if (($tramiteestado == '12') or ($tramiteestado == '9')){//12 Inicio de Semestre - Cerrado, 9 Inicio de Semestre Regularizado - Cerrado
                    $ie = $ies->getInstitucioneducativa()->getId();
                    //$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
                    //$iest = new InstitucioneducativaSucursalTramite();
                    //$iest->setInstitucioneducativaSucursal($ies);            
                    $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('2'));//fin de periodo
                    $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('13'));//Aceptación de apertura Fin de Semestre
                    $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));//Fin de semestre

                    //EXTRAER COD DISTRITO
                    $query = "SELECT get_ie_distrito_id(".$ie.");";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $podis = $stmt->fetchAll();
                    foreach ($podis as $p){
                        $lugarestipoid = $p["get_ie_distrito_id"];           
                    }

                    $lugarids = explode(",", $lugarestipoid);
                    //dump($lugarids);die;
                    $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));
                    //dump($dis_id);die;
                    $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                    $iest->setDistritoCod($dis_cod->getCodigo());
                    $iest->setFechainicio(new \DateTime('now'));
                    $iest->setUsuarioIdInicio($this->session->get('userId'));
                    $em->persist($iest);
                    $em->flush();
                    
                }
                if (($tramiteestado == '14') or ($tramiteestado == '8')){//14 Fin de Semestre - Cerrado, 8 Fin de Semestre Regularizado - Cerrado          
                    $periodo = $ies->getPeriodoTipoId();
                    $gestion = $ies->getGestionTipo()->getId();
                    $sucursal = $ies->getSucursalTipo()->getId();
                    $ie = $ies->getInstitucioneducativa()->getId();
                    //dump($periodo); die;
                    if ($periodo == '2'){
                        $query = $em->getConnection()->prepare('SELECT sp_genera_inicio_sgte_gestion_alternativa(:sie, :gestion, :periodo, :subcea)');
                        $query->bindValue(':sie', $ie);
                        $query->bindValue(':gestion', $gestion);
                        $query->bindValue(':periodo', '3');
                        $query->bindValue(':subcea', $sucursal);
                        $query->execute();
                        $iesid = $query->fetchAll();            
                        if ($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"] != '0'){
                            $iesidnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"]);
                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
                            $iest = new InstitucioneducativaSucursalTramite();
                            $iest->setInstitucioneducativaSucursal($iesidnew);            
                            $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('1'));//inicio de periodo
                            $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('11'));//Aceptación de apertura Inicio de Semestre
                            $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('5'));//Inicio de semestre

                            //EXTRAER COD DISTRITO
                            $query = "SELECT get_ie_distrito_id(".$ie.");";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $podis = $stmt->fetchAll();
                            foreach ($podis as $p){
                                $lugarestipoid = $p["get_ie_distrito_id"];           
                            }
                            //dump($lugarestipoid);die;
                            $lugarids = explode(",", $lugarestipoid);
                            $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
                            $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                            $iest->setDistritoCod($dis_cod->getCodigo());
                            $iest->setFechainicio(new \DateTime('now'));
                            $iest->setUsuarioIdInicio($this->session->get('userId'));
                            $em->persist($iest);
                            $em->flush();
                            }
                        else{
                            $em->getConnection()->rollback();
                            return $this->redirectToRoute('principal_web');
                        }
                    }
                    if ($periodo == '3'){
                        $query = $em->getConnection()->prepare('SELECT sp_genera_inicio_sgte_gestion_alternativa(:sie, :gestion, :periodo, :subcea)');
                        $query->bindValue(':sie', $ie);
                        $query->bindValue(':gestion', intval($gestion)+1); //PARA UNA GESTION ADELANTE
                        $query->bindValue(':periodo', '2');
                        $query->bindValue(':subcea', $sucursal);
                        $query->execute();
                        $iesid = $query->fetchAll();            
                        if ($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"] != '0'){
                            $iesidnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"]);
                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
                            $iest = new InstitucioneducativaSucursalTramite();
                            $iest->setInstitucioneducativaSucursal($iesidnew);            
                            $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('1'));
                            $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('11'));//Aceptación de apertura Inicio de Semestre
                            $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('5'));

                            //EXTRAER COD DISTRITO
                            $query = "SELECT get_ie_distrito_id(".$ie.");";
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $podis = $stmt->fetchAll();
                            foreach ($podis as $p){
                                $lugarestipoid = $p["get_ie_distrito_id"];           
                            }
                            //dump($lugarestipoid);die;
                            $lugarids = explode(",", $lugarestipoid);
                            $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
                            $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
                            $iest->setDistritoCod($dis_cod->getCodigo());
                            $iest->setFechainicio(new \DateTime('now'));
                            $iest->setUsuarioIdInicio($this->session->get('userId'));
                            $em->persist($iest);
                            $em->flush();
                            }
                        else{
                            $em->getConnection()->rollback();
                            return $this->redirectToRoute('principal_web');
                        }
                    }
                }
                
                $em->getConnection()->commit();                
                //$em->getConnection()->rollback();                
                return $this->redirectToRoute('principal_web');
            }    
                  
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        return $this->redirectToRoute('principal_web');
    }    
    
    public function listadisceasAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        //$usuariodatos = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($this->session->get('userId'));
//        print_r($usuariodatos[0]['discod']);
//        die;        
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$this->session->get('userId'),'rolTipo'=>$this->session->get('roluser')));

        
        $db = $em->getConnection();            
        $query = "
            select            
	    d.id as cod_ue,		
            d.institucioneducativa
            from jurisdiccion_geografica a 
                    inner join (
                            select id,codigo as cod_dis,lugar as des_dis 
                                    from lugar_tipo
                            where lugar_nivel_id=7 
                    ) b 	
                    on a.lugar_tipo_id_distrito = b.id
                    inner join (
                            select a.id, a.institucioneducativa, a.le_juridicciongeografica_id
                            from institucioneducativa a
                            where a.orgcurricular_tipo_id = 2                                     
                    ) d		
                    on a.id=d.le_juridicciongeografica_id
            where 
	      (b.cod_dis = '".$usuariorol[0]->getLugarTipo()->getCodigo()."')
           order by d.id ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //dump($po);die;
        return $this->render($this->session->get('pathSystem') . ':Principal:distritolistaceas.html.twig', array(
                'entities' => $po,
        /*return $this->render($this->session->get('pathSystem') . ':Principal:listaceasdistrito.html.twig', array(
                'entities' => $po,*/
            ));
    }
    
    public function abrirceaAction(Request $request, $ie_id, $ie_nombre) {
        $sesion = $request->getSession();
        $sesion->set('ie_id',$ie_id);
        $sesion->set('ie_nombre', $ie_nombre);
        $sesion->set('ie_per_estado', '3');
        $sesion->set('ie_operativo', '¡En modo edición!');
        //return $this->redirect($this->generateUrl('principal_web'));
        return $this->redirect($this->generateUrl('herramienta_ceducativa_seleccionar_cea'));
    }
    
    public function seleccionarceaAction() {
        $historialForm = $this->historialceasForm($this->session->get('roluser'),$this->session->get('ie_id'));
        //dump($historialForm);die;
        return $this->render($this->session->get('pathSystem') . ':Principal:seleccionarcea.html.twig',array(
            'form'=>$historialForm->createView(),
        ));

    }
    
    public function buscarceaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        /*
         * verificamos si tiene tuicion
         */    
        $usuario_id = $this->session->get('userId');        
        $usuario_rol = $this->session->get('roluser');
        
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $usuario_id);
        $query->bindValue(':sie', $form['codsie']);
        $query->bindValue(':rolId', $usuario_rol);
        $query->execute();
        $aTuicion = $query->fetchAll();        
        //dump($usuario_id.' '.$idInstitucion.' '.$usuario_rol);
        //die;        
        if ($aTuicion[0]['get_ue_tuicion']) {
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codsie']);
            if ($ie){
                $sesion = $request->getSession();
                $sesion->set('ie_id',$form['codsie']);
                $sesion->set('ie_nombre', $ie->getInstitucioneducativa());
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡En modo edición!');
                // dump($form['semestre']);die;
                $sesion->set('u_semestre', $form['semestre']);
                //return $this->redirect($this->generateUrl('principal_web'));
                /***
                 * Cargar funcion para inicio de semestre
                 */
                $gestionActual = date('Y');
                $mesActual = date('f');
                if ($gestionActual >= 2019){                   
                    $distrito_cod = $ie->getLeJuridicciongeografica()->getDistritoTipo()->getId();
                    $operativoControl = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                            ->select('oc')
                            ->where('oc.operativoTipo in(1,3)')
                            ->andWhere("oc.distritoTipo = " .$distrito_cod)
                            ->andWhere("oc.gestionTipo = " .$gestionActual)
                            ->getQuery()
                            ->getResult();
                    //dump($operativoControl);die;
                    $em->getConnection()->beginTransaction();
                    $db = $em->getConnection();
                    try { 
                        foreach($operativoControl as $o){
                            if($o->getOperativoTipo()->getId()==1){
                                $periodo = 2;
                            }else{
                                $periodo = 3;
                            }
                            $datos = json_decode($o->getObs(),true);
                            foreach($datos as $d){
                                $id_ie = json_decode($d,true)['ie'];
                                $sucursal = json_decode($d,true)['suc'];
                                if($ie->getId() == $id_ie){
                                    $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array('institucioneducativa'=>$ie->getId(),'sucursalTipo'=>$sucursal,'gestionTipo'=>$gestionActual,'periodoTipoId'=>$periodo));
                                    if(!$ies){
                                        $query = $em->getConnection()->prepare('SELECT sp_genera_inicio_sgte_gestion_alternativa(:sie, :gestion, :periodo, :subcea)');
                                        $query->bindValue(':sie', $id_ie);
                                        $query->bindValue(':gestion', $gestionActual);
                                        $query->bindValue(':periodo', $periodo);
                                        $query->bindValue(':subcea', $sucursal);
                                        $query->execute();
                                        $iesid = $query->fetchAll();      
                                        //dump($iesid) ; die;     
                                        if ($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"] != '0'){
                                            $iesidnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"]);
                                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
                                            $iest = new InstitucioneducativaSucursalTramite();
                                            $iest->setInstitucioneducativaSucursal($iesidnew);            
                                            $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('1'));
                                            $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('11'));//Aceptación de apertura Inicio de Semestre
                                            $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('5'));
                                            $iest->setDistritoCod($distrito_cod);
                                            $iest->setFechainicio(new \DateTime('now'));
                                            $iest->setUsuarioIdInicio($this->session->get('userId'));
                                            $em->persist($iest);
                                            $em->flush();
                                        }else{
                                            $em->getConnection()->rollback();
                                        }
                                    }
                                }
                            }
                        }
                        $em->getConnection()->commit(); 
                    }catch (Exception $ex) {
                        $em->getConnection()->rollback();
                    }
                }

                /***adicionado***/
                /**historial segun opciones de seleccion******/
                $ie_id = $form['codsie'];
                $gestion = $form['gestion'];
                $semestre = $form['semestre'];
                $subcea = $form['subcea'];
                if($gestion==""){
                    $gestionesArray = array();
                    $ges = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getGestionCea($ie_id);
                    foreach ($ges as $i=>$ges) {
                        $gestionesArray[$i] = $ges['gestion'];
                    }
                    $gestion = $gestionesArray;
                }
                if($subcea==""){
                    if($form['gestion']=""){
                        $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($ie_id,'');
                    }else{
                        $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($ie_id,$form['gestion']);
                    }
                    //dump($subcea);die;
                    $subceasArray = array();
    	            foreach ($subcea as $i=>$sc) {
                        $subceasArray[$i] = $sc['sucursal'];
                    }
                    $subcea = $subceasArray;
                    //dump($subcea);die;
                }
                if($semestre==""){
                    $sem = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaSemestre($ie_id);
                    //dump($sem);die;
                    $semestreArray = array();
                    //$provincia[-1] = '-Todos-';
    	            foreach ($sem as $i=>$s) {
                        //dump($sc);die;
                        $semestreArray[$i] = $s['periodo'];
                    }
                    $semestre = $semestreArray;
                }
                $iesubsea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getAllSucursalTipo1($ie_id,$gestion,$subcea,$semestre);
                // dump($iesubsea[0]['teid']);die;
                $this->session->set('tramiteEstadoId', $iesubsea[0]['teid']);

                //dump($iesubsea); die;
                return $this->render($this->session->get('pathSystem') . ':Centroeducativo:tablahistorial.html.twig', array(
                    'iesubsea' => $iesubsea,
                ));
                ///*****///////
                //return $this->redirect($this->generateUrl('sie_alt_ges_sub_sem'));
            }
            else{           
                //$this->session->getFlashBag()->add('notfound', 'El código de institución educativa no se encuentra.');
                //return $this->redirect($this->generateUrl('herramienta_ceducativa_seleccionar_cea'));
                $response = new JsonResponse();
                return $response->setData(array('msg' => 'El código de institución educativa no se encuentra.'));
                
            }    
        }
        else{
            //$this->session->getFlashBag()->add('notfound', 'No tiene tuición sobre el Centro de Educación Alternativa.');
            //return $this->redirect($this->generateUrl('herramienta_ceducativa_seleccionar_cea'));
            $response = new JsonResponse();
            return $response->setData(array('msg' => 'No tiene tuición sobre el Centro de Educación Alternativa.'));
        }
    }

    public function crearPeriodoAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->where('g.id < 2016 AND g.id > 2008')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }

        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $repository = $em->getRepository('SieAppWebBundle:SucursalTipo');
        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id')
            ->getQuery();
        $sucursales = $query->getResult();
        $sucursalesArray = array();
        foreach ($sucursales as $s) {
            $sucursalesArray[$s->getId()] = $s->getId();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramientalt_ceducativa_crear_periodo_cea'))
                ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
                ->add('gestion', 'choice', array('label' => 'Gestión', 'required' => true, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
                ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
                ->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => true, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
                ->add('crear', 'submit', array('label' => 'Crear Periodo', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $this->render($this->session->get('pathSystem') . ':Principal:crearperiodo.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function crearPeriodoCeaAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        $idInstitucion = $form['idInstitucion'];
        $gestion = $form['gestion'];
        $periodo = $form['periodo'];
        $subcea = $form['subcea'];

        /*dump($idInstitucion);
        dump($gestion);
        dump($periodo);
        dump($subcea);die;*/

        $usuario_lugar = $this->session->get('roluserlugarid');
        $usuario_rol = $this->session->get('roluser');
        $usuario_id = $this->session->get('userId');
        $persona_id = $this->session->get('personaId');

        /*
        * verificamos si existe la Institución Educativa
        */
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $idInstitucion, 'institucioneducativaTipo' => 2));
        if (!$institucioneducativa) {
            $this->get('session')->getFlashBag()->add('errorMsg', 'El código SIE ingresado no es válido.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
        }

        /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $usuario_id);
        $query->bindValue(':sie', $idInstitucion);
        $query->bindValue(':rolId', $usuario_rol);
        $query->execute();
        $aTuicion = $query->fetchAll();
        
//        dump($usuario_id.' '.$idInstitucion.' '.$usuario_rol);
//        die;

        if ($aTuicion[0]['get_ue_tuicion']) {

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('ie')
                ->select('ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
//                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'siea', 'WITH', 'siea.institucioneducativaSucursal = ies.id')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.institucioneducativa = ie.id')
//                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ie.id = :idInstitucion')
//                ->andWhere('iec.gestionTipo = :gestionc')
                ->andWhere('ies.gestionTipo = :gestions')
                ->andWhere('ies.periodoTipoId = :periodo')
                ->andWhere('ies.sucursalTipo = :sucursal')
                ->setParameter('idInstitucion', $idInstitucion)
//                ->setParameter('gestionc', $gestion)
                ->setParameter('gestions', $gestion)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $subcea)
                ->setMaxResults(1)
                ->getQuery();

            $inscripciones = $query->getResult();
//            dump($inscripciones);
//            die;
            if($inscripciones) {
                $this->get('session')->getFlashBag()->add('errorMsg', 'El CEA ya cuenta con el Periodo seleccionado habilitado.');
                return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
            }
            else {
                $query = $em->getConnection()->prepare('SELECT sp_genera_inicio_sgte_gestion_alternativa(:sie, :gestion, :periodo, :subcea)');
                $query->bindValue(':sie', $idInstitucion);
                $query->bindValue(':gestion', $gestion);
                $query->bindValue(':periodo', $periodo);
                $query->bindValue(':subcea', $subcea);
                $query->execute();
                $iesid = $query->fetchAll();            
                if (($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"] != '0') and ($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"] != '')){
//                    $iesidnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($iesid[0]["sp_genera_inicio_sgte_gestion_alternativa"]);
//                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
//                    $iest = new InstitucioneducativaSucursalTramite();
//                    $iest->setInstitucioneducativaSucursal($iesidnew);            
//                    $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('1'));//inicio de periodo
//                    $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('11'));//Aceptación de apertura Inicio de Semestre
//                    $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('5'));//Inicio de semestre
//                    $query = "SELECT get_ie_distrito_id(".$idInstitucion.");";
//                    $stmt = $em->getConnection()->prepare($query);
//                    $params = array();
//                    $stmt->execute($params);
//                    $podis = $stmt->fetchAll();
//                    foreach ($podis as $p){
//                        $lugarestipoid = $p["get_ie_distrito_id"];           
//                    }
//                    $lugarids = explode(",", $lugarestipoid);
//                    $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
//                    $dis_cod = $em->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id);
//                    $iest->setDistritoCod($dis_cod->getCodigo());
//                    $iest->setFechainicio(new \DateTime('now'));
//                    $iest->setUsuarioIdInicio($this->session->get('userId'));
//                    $em->persist($iest);
//                    $em->flush();

                    $this->get('session')->getFlashBag()->add('successMsg', 'Se ha habilitado el periodo seleccionado.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
                }else{
                    $this->get('session')->getFlashBag()->add('errorMsg', 'Ha ocurrido un problema en la generación del periodo.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));                    
                }
            }            
        } else {
            $this->get('session')->getFlashBag()->add('errorMsg', 'No tiene tuición sobre el Centro de Educación Alternativa.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
        }
    }

    public function crearSucursalAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->where('g.id < 2016 AND g.id > 2008')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }

        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $repository = $em->getRepository('SieAppWebBundle:SucursalTipo');
        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id')
            ->getQuery();
        $sucursales = $query->getResult();
        $sucursalesArray = array();
        foreach ($sucursales as $s) {
            $sucursalesArray[$s->getId()] = $s->getId();
        }

        $entidadDepartamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1), array('id' => 'asc'));
        //dump($entidadDepartamento);die;
        $entidadDep = array();
        foreach ($entidadDepartamento as $dato)
        {
           $entidadDep[$dato->getCodigo()] = $dato->getLugar();
        }
        $departamentoId = null;
        $entidadProvincia = array();
        $provinciaId = null;
        $entidadMunicipio = array();
        $municipioId = null;
        $entidadCanton = array();
        $cantonId = null;
        $entidadLocalidad = array();
        $localidadId = null;
        $entidadDistrito = array();
        $distritoId = null;
        $direccion = "";
        $zona = "";  

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramientalt_ceducativa_crear_sucursal_cea'))
                ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
                //->add('gestion', 'choice', array('label' => 'Gestión', 'required' => true, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
                ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
                //->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => true, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
                ->add('departamento', 'choice', array('label' => 'Departamento', 'empty_value' => 'Seleccione Departamento', 'choices' => $entidadDep, 'data' => $departamentoId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaProvincia(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'empty_value' => 'Seleccione Provincia', 'choices' => $entidadProvincia, 'data' => $provinciaId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaMunicipio(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'empty_value' => 'Seleccione Municipio', 'choices' => $entidadMunicipio, 'data' => $municipioId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaCanton(this.value)')))
                ->add('canton', 'choice', array('label' => 'Canton', 'empty_value' => 'Seleccione Canton', 'choices' => $entidadCanton, 'data' => $cantonId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listaLocalidad(this.value)')))
                ->add('localidad', 'choice', array('label' => 'Localidad', 'empty_value' => 'Seleccione Localidad', 'choices' => $entidadLocalidad, 'data' => $localidadId, 'attr' => array('class' => 'form-control', 'required' => true)))
                ->add('distrito', 'choice', array('label' => 'Distrito Educativo', 'empty_value' => 'Seleccione Distrito', 'choices' => $entidadDistrito, 'data' => $distritoId, 'attr' => array('class' => 'form-control', 'required' => true)))
                ->add('subcea', 'text', array('label' => 'Nombre SubCentro', 'attr' => array('value' => $zona, 'class' => 'form-control', 'placeholder' => 'Nombre del Sub Centro', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('direccion', 'text', array('label' => 'Direccion', 'attr' => array('value' => $direccion, 'class' => 'form-control', 'placeholder' => 'Dirección', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('zona', 'text', array('label' => 'Zona', 'attr' => array('value' => $zona, 'class' => 'form-control', 'placeholder' => 'Zona', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('crear', 'submit', array('label' => 'Crear Sucursal', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();


        return $this->render($this->session->get('pathSystem') . ':Principal:crearsucursal.html.twig', array(
                'form' => $form->createView()
            ));
    }

    public function crearSucursalCeaAction(Request $request){

        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $session = $request->getSession();
        $usuario_id = $session->get('userId');

        //validation if the user is logged
        if (!isset($usuario_id)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        if ($form) {
            $idInstitucion = $form['idInstitucion'];
            // $gestion = $form['gestion'];
            $gestion = $fechaActual->format('Y');
            $periodo = $form['periodo'];
            $nombre = strtoupper($form['subcea']);
            // $subcea = $form['subcea'];
            $departamentoId = $form['departamento'];
            $provinciaId = $form['provincia'];
            $municipioId = $form['municipio'];
            $cantonId = $form['canton'];
            $localidadId = $form['localidad'];
            $distritoId = $form['distrito'];
            $direccion = strtoupper($form['direccion']);
            $zona = strtoupper($form['zona']);
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('errorMsg', 'Ha ocurrido un problema al enviar el formulario, intente nuevamente.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));  
        }
        
        $subcea = 0;

        $usuario_lugar = $this->session->get('roluserlugarid');
        $usuario_rol = $this->session->get('roluser');
        $persona_id = $this->session->get('personaId');

        /*
        * verificamos si existe la Institución Educativa
        */
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $idInstitucion, 'institucioneducativaTipo' => 2));
        if (!$institucioneducativa) {
            $this->get('session')->getFlashBag()->add('errorMsg', 'El código SIE ingresado no es válido.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
        }

        /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $usuario_id);
        $query->bindValue(':sie', $idInstitucion);
        $query->bindValue(':rolId', $usuario_rol);
        $query->execute();
        $aTuicion = $query->fetchAll();

        if ($aTuicion[0]['get_ue_tuicion']) {          

            $queryEntidad = $em->getConnection()->prepare("
                select max(sucursal_tipo_id) as sucursal_tipo_id from institucioneducativa_sucursal where institucioneducativa_id = ".$idInstitucion." 
            ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            if (count($objEntidad)<1) {
                $this->get('session')->getFlashBag()->add('errorMsg', 'El código SIE ingresado no es válido.');
                return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
            } else {
                $subcea = ((int)$objEntidad[0]['sucursal_tipo_id'])+1;
            }       

            $queryEntidad = $em->getConnection()->prepare("
                select sucursal_tipo_id from institucioneducativa_sucursal where institucioneducativa_id = ".$idInstitucion." and nombre_subcea like trim('".$nombre."')
            ");

            $queryEntidad->execute();
            $objEntidadValidaNombre = $queryEntidad->fetchAll();
            if (count($objEntidadValidaNombre)>0) {
                $this->get('session')->getFlashBag()->add('errorMsg', 'El nombre del SUB CEA ya se encuentra registrado con el numero '.$subcea.'.');
                return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
            }

            $entityInstitucionEducativaSucursalCentral = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $idInstitucion, 'gestionTipo' => $gestion, 'sucursalTipo' => 0, 'periodoTipoId' => $periodo));
            if(!$entityInstitucionEducativaSucursalCentral) {
                $this->get('session')->getFlashBag()->add('errorMsg', 'El CEA '.$idInstitucion.' no cuenta con el SUB CEA 0 habilitado, debe aperturar el CEA CENTRAL en la gestion y periodo seleccionado antes de abrir otro SUB CEA.');
                return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
            }

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
            $query = $repository->createQueryBuilder('ie')
                ->select('ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo = :gestions')
                ->andWhere('ies.periodoTipoId = :periodo')
                ->andWhere('ies.sucursalTipo = :sucursal')
                ->setParameter('idInstitucion', $idInstitucion)
                ->setParameter('gestions', $gestion)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $subcea)
                ->setMaxResults(1)
                ->getQuery();
            $inscripciones = $query->getResult();

            if($inscripciones) {
                $this->get('session')->getFlashBag()->add('errorMsg', 'El CEA ya cuenta con el SUB CEA '.$subcea.' habilitada.');
                return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
            }
            else {                                
                $em->getConnection()->beginTransaction();
                try {                    
                    // $entityInstitucionEducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('id' => $idiesuc));
                    $entityLocalidadLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $localidadId));
                    $entityDistritoLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $distritoId));
                    $entityDistritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneBy(array('id' => $entityDistritoLugarTipo->getCodigo()));
                    $distritoCodigo = $entityDistritoLugarTipo->getCodigo();
                    $entityValidacionGeograficaTipo = $em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneBy(array('id' => 0));
                    $entityJuridiccionAcreditacionTipo = $em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->findOneBy(array('id' => 4));
                    
                    // $idjurgeocentral = $entityInstitucionEducativaSucursal->getLeJuridicciongeografica()->getId();
                    // $entityJurisdiccionGeograficaCentral = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneBy(array('id' => $idjurgeocentral));
                    // $institucioneducativaId = $entityInstitucionEducativaSucursal->getInstitucioneducativa()->getId();
                    // $sucursalId = $entityInstitucionEducativaSucursal->getSucursalTipo()->getId();
                    // dump($idjurgeocentral);die;
                    // $nuevoId = str_pad($sucursalId,2,"0",STR_PAD_LEFT);

                    $query = $em->getConnection()->prepare("
                        select cast(coalesce(max(cast(substring(cast(id as varchar) from (length(cast(id as varchar))-2) for 3) as integer)),0) + 1 as varchar) as id
                        from jurisdiccion_geografica 
                        where juridiccion_acreditacion_tipo_id = 4
                    ");      
                    $query->execute();
                    $entityId = $query->fetchAll();
                    $nuevoId = $distritoCodigo.str_pad($entityId[0]['id'],3,"0",STR_PAD_LEFT);

                    $entityJurisdiccionGeografica  = new JurisdiccionGeografica(); 
                    $entityJurisdiccionGeografica->setId($nuevoId); 
                    $entityJurisdiccionGeografica->setLugarTipoLocalidad($entityLocalidadLugarTipo);           
                    $entityJurisdiccionGeografica->setLugarTipoIdDistrito($distritoId);
                    $entityJurisdiccionGeografica->setObs('NUEVO SUCURSAL SUB C.E.A.');
                    $entityJurisdiccionGeografica->setDistritoTipo($entityDistritoTipo);
                    $entityJurisdiccionGeografica->setDireccion(mb_strtoupper($direccion, 'UTF-8'));
                    $entityJurisdiccionGeografica->setZona(mb_strtoupper($zona, 'UTF-8'));
                    $entityJurisdiccionGeografica->setJuridiccionAcreditacionTipo($entityJuridiccionAcreditacionTipo);
                    $entityJurisdiccionGeografica->setValidacionGeograficaTipo($entityValidacionGeograficaTipo);
                    $entityJurisdiccionGeografica->setFechaRegistro($fechaActual);
                    $entityJurisdiccionGeografica->setUsuarioId($usuario_id);
                    $em->persist($entityJurisdiccionGeografica);
                   
                    $entityGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion));
                    $entityInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $idInstitucion));
                    $entitySucursalTipo = $em->getRepository('SieAppWebBundle:SucursalTipo')->findOneBy(array('id' => $subcea));

                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');")->execute();
                    $entityInstitucionEducativaSucursal = new InstitucioneducativaSucursal();
                    $entityInstitucionEducativaSucursal->setNombreSubcea($nombre, 'UTF-8');
                    $entityInstitucionEducativaSucursal->setCodCerradaId(10);
                    $entityInstitucionEducativaSucursal->setPeriodoTipoId($periodo);
                    $entityInstitucionEducativaSucursal->setGestionTipo($entityGestionTipo);
                    $entityInstitucionEducativaSucursal->setInstitucioneducativa($entityInstitucioneducativa);
                    $entityInstitucionEducativaSucursal->setLeJuridicciongeografica($entityJurisdiccionGeografica);
                    $entityInstitucionEducativaSucursal->setSucursalTipo($entitySucursalTipo);
                    $entityInstitucionEducativaSucursal->setDireccion($direccion);
                    $entityInstitucionEducativaSucursal->setZona($zona);
                    $entityInstitucionEducativaSucursal->setEsabierta(true);
                    
                    $entityInstitucionEducativaSucursal->setLeJuridicciongeografica($entityJurisdiccionGeografica);
                    $em->persist($entityInstitucionEducativaSucursal);

                    $em->flush();
                    $em->getConnection()->commit();
                    $this->get('session')->getFlashBag()->add('successMsg', 'Se habilito el SUB CEA '.$subcea.' - '.$nombre.' correctamente.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));       
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $em->getConnection()->rollback();
                    $this->get('session')->getFlashBag()->add('errorMsg', 'Ha ocurrido un problema en la generación del SUB CEA.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));          
                }
            }            
        } else {
            $this->get('session')->getFlashBag()->add('errorMsg', 'No tiene tuición sobre el Centro de Educación Alternativa.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_sucursal'));
        }
    }
    
    public function ceaspendientesAction(Request $request) {        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $sesion = $request->getSession();
        $form = $this->ceaspendientesForm($sesion->get('roluser'),'ceaspendientes');
        $form_pendientes = $this->exportarpendientesForm();
//        dump($po); die;
        return $this->render($this->session->get('pathSystem') . ':Principal:listaceapendientesnew.html.twig', array(
                //'entities' => $po,
                'rol' => $sesion->get('roluser'),
                'id_usuario' =>$id_usuario,
                'form' => $form->createView(),
                'form_exp' => $form_pendientes->createView(),
            ));
    }
    public function listaceaspendientesAction(Request $request) {        

        //dump($request);die;
        $rol = $request->get('rol');
        if(!$request->get('gestion'))
        {
            $gestion = 'select id from gestion_tipo where id>=2017';
        }else{
            $gestion = $request->get('gestion');
        }
        $id_usuario = $request->get('id_usuario');
        if ($rol == 8 )
        {
            if(!$request->get('departamento'))
            {
                $departamento = '1,2,3,4,5,6,7,8,9';
            }else{
                $departamento = $request->get('departamento');
            }
                

        }
        //dump($rol);die;
        $em = $this->getDoctrine()->getManager();        
        $db = $em->getConnection();

        
        if ($rol == '8' ) {//NACIONAL
             $query = "
                        select * from(
                            select
                            cast(k.codigo as int) as dep_codigo,
                            k.id as deptoid,
                                k.lugar,        
                                b.distrito_cod,  
                                d.id as ieue,	
                                d.institucioneducativa,                    
                                z.gestion_tipo_id as gestion_tipo_id,
                                case when z.periodo_tipo_id = '2' then 'Primer Semestre' else 'Segundo Semestre' end as semestre,                    
                                z.periodo_tipo_id as periodo,
                                z.estadoId,
                                z.tramite_estado obs                    
                        from jurisdiccion_geografica a 
                                    inner join (
                                        select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                            from lugar_tipo
                                        where lugar_nivel_id=7 
                                    ) b 	
                                    on a.lugar_tipo_id_distrito = b.id
                                    inner join (
                                        select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                        from institucioneducativa a                                                        
                                        where a.orgcurricular_tipo_id = 2
                                        and a.institucioneducativa_tipo_id = 2
                                        and a.estadoinstitucion_tipo_id = 10
                                        and a.institucioneducativa_acreditacion_tipo_id = 1
                                    ) d on a.id=d.le_juridicciongeografica_id
                                    inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    left join (
                            select ie.id as id_ie, ff.obs as tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id as estadoId
                            from jurisdiccion_geografica jg 
                            inner join (
                                select id, codigo as cod_dis, lugar_tipo_id, lugar
                                from lugar_tipo
                                where lugar_nivel_id=7 
                            ) lt 	
                            on jg.lugar_tipo_id_distrito = lt.id
                            inner join (
                                select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                from institucioneducativa a
                            ) ie		
                            on jg.id = ie.le_juridicciongeografica_id
                            inner join institucioneducativa_sucursal z on ie.id = z.institucioneducativa_id
                            inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                            inner join tramite_estado ff on ff.id = w.tramite_estado_id
                            inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                            where z.gestion_tipo_id in (". $gestion .") 			    
                            and w.tramite_estado_id not in (8,9,10,12,14)
                            and z.periodo_tipo_id in (2,3)
                            group by k.lugar, ie.id, ie.institucioneducativa, ff.tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id
                            order by k.lugar, ie.id
                                    ) z on z.id_ie = d.id 					    
                        where z.gestion_tipo_id is not null                
                        order by lugar, ieue
                        ) abc where dep_codigo in (". $departamento .")";
        }
        //dump($query);die;

        if ($rol == '7' ) {//DEPARTAMENTAL
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));
            //dump($usuariorol);die;
            $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
            //dump()
            
            $query = "
                                    select * from(
                                        select
                                            k.id as deptoid,
                                            k.lugar,        
                                            b.distrito_cod,  
                                            d.id as ieue,	
                                            d.institucioneducativa,                    
                                            z.gestion_tipo_id as gestion_tipo_id,
                                            case when z.periodo_tipo_id = '2' then 'Primer Semestre' else 'Segundo Semestre' end as semestre,                    
                                            z.periodo_tipo_id as periodo,
                                            z.estadoId,
                                            z.tramite_estado obs                    
                                        from jurisdiccion_geografica a 
                                                inner join (
                                                    select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                        from lugar_tipo
                                                    where lugar_nivel_id=7 
                                                ) b 	
                                                on a.lugar_tipo_id_distrito = b.id
                                                inner join (
                                                    select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                    from institucioneducativa a                                                        
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and a.estadoinstitucion_tipo_id = 10
                                                    and a.institucioneducativa_acreditacion_tipo_id = 1
                                                ) d on a.id=d.le_juridicciongeografica_id
                                                inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                                left join (
                                        select ie.id as id_ie, ff.obs as tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id as estadoId
                                        from jurisdiccion_geografica jg 
                                        inner join (
                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                            from lugar_tipo
                                            where lugar_nivel_id=7 
                                        ) lt 	
                                        on jg.lugar_tipo_id_distrito = lt.id
                                        inner join (
                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                            from institucioneducativa a
                                        ) ie		
                                        on jg.id = ie.le_juridicciongeografica_id
                                        inner join institucioneducativa_sucursal z on ie.id = z.institucioneducativa_id
                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                        inner join tramite_estado ff on ff.id = w.tramite_estado_id
                                        inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                                        where z.gestion_tipo_id in (". $gestion .") 			    
                                        and w.tramite_estado_id not in (8,9,10,12,14)
                                        and z.periodo_tipo_id in (2,3)
                                        group by k.lugar, ie.id, ie.institucioneducativa, ff.tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id
                                        order by k.lugar, ie.id
                                                ) z on z.id_ie = d.id 					    
                                    where z.gestion_tipo_id is not null                
                                    order by lugar, ieue
                                    ) abc                                    
                                    where abc.deptoid = '".$idlugarusuario."'";
        } 
        
        if ($rol == '10' ) {//DISTRITAL
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));     

            $query = "
                                    select * from(
                                        select
                                            k.id as deptoid,
                                            k.lugar,        
                                            b.distrito_cod,  
                                            d.id as ieue,	
                                            d.institucioneducativa,                    
                                            z.gestion_tipo_id as gestion_tipo_id,
                                            case when z.periodo_tipo_id = '2' then 'Primer Semestre' else 'Segundo Semestre' end as semestre,                    
                                            z.periodo_tipo_id as periodo,
                                            z.estadoId,
                                            z.tramite_estado obs                    
                                        from jurisdiccion_geografica a 
                                                inner join (
                                                    select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                        from lugar_tipo
                                                    where lugar_nivel_id=7 
                                                ) b 	
                                                on a.lugar_tipo_id_distrito = b.id
                                                inner join (
                                                    select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                    from institucioneducativa a                                                        
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and a.estadoinstitucion_tipo_id = 10
                                                    and a.institucioneducativa_acreditacion_tipo_id = 1
                                                ) d on a.id=d.le_juridicciongeografica_id
                                                inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                                left join (
                                        select ie.id as id_ie, ff.obs as tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id as estadoId
                                        from jurisdiccion_geografica jg 
                                        inner join (
                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                            from lugar_tipo
                                            where lugar_nivel_id=7 
                                        ) lt 	
                                        on jg.lugar_tipo_id_distrito = lt.id
                                        inner join (
                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                            from institucioneducativa a
                                        ) ie		
                                        on jg.id = ie.le_juridicciongeografica_id
                                        inner join institucioneducativa_sucursal z on ie.id = z.institucioneducativa_id
                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                        inner join tramite_estado ff on ff.id = w.tramite_estado_id
                                        inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                                        where z.gestion_tipo_id in (". $gestion .") 			    
                                        and w.tramite_estado_id not in (8,9,10,12,14)
                                        and z.periodo_tipo_id in (2,3)
                                        group by k.lugar, ie.id, ie.institucioneducativa, ff.tramite_estado, z.gestion_tipo_id, z.periodo_tipo_id, ff.id
                                        order by k.lugar, ie.id
                                                ) z on z.id_ie = d.id 					    
                                    where z.gestion_tipo_id is not null                
                                    order by lugar, ieue
                                    ) abc                                    
                                    where abc.distrito_cod = '".$usuariorol[0]->getLugarTipo()->getCodigo()."'";
        }
    
        //dump($usuariorol[0]->getLugarTipo()->getCodigo());die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
//        dump($po); die;
        return $this->render($this->session->get('pathSystem') . ':Principal:tablaceapendientes.html.twig', array(
                'entities' => $po,
            ));
    }
    public function exportarpendientesForm()

    {   //dump($tipo);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('sie_alt_lista_ceaspendientes'))
            ->add('tipo','hidden')
            ->add('rol','hidden')
            ->add('gestion1','hidden')
            ->add('departamento1','hidden')
            ->add('id_usuario','hidden')
            ->add('pdf1', 'submit', array('label'=> 'Exportar PDF', 'attr'=>array('class'=>'btn btn-sm btn-danger')))
            ->add('xlsx1', 'submit', array('label'=> 'Exportar Excel', 'attr'=>array('class'=>'btn btn-sm btn-success')))
            ->getForm();

        return $form;
    }
    public function exportaroperativoForm()

    {   //dump($tipo);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('sie_alt_lista_operativos'))
            ->add('tipo','hidden')
            ->add('rol','hidden')
            ->add('gestion1','hidden')
            ->add('departamento1','hidden')
            ->add('id_usuario','hidden')
            ->add('pdf1', 'submit', array('label'=> 'Exportar PDF', 'attr'=>array('class'=>'btn btn-sm btn-danger')))
            ->add('xlsx1', 'submit', array('label'=> 'Exportar Excel', 'attr'=>array('class'=>'btn btn-sm btn-success')))
            ->getForm();

        return $form;
    }

    public function ceaspendientesForm($rol,$tipo)
    {   
        $form = $this->createFormBuilder();
            //->setAction($this->generateUrl('herramientalt_ceducativa_estadistiscas_cierre'))
            if ($tipo =='operativo')
                $form=$form
                ->add('gestion','entity',array('label'=>'Seleccione Gestión:','required'=>false,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                    return $g->createQueryBuilder('g')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
            else{//ceas pendientes
                $form=$form
                ->add('gestion','entity',array('label'=>'Seleccione Gestión','required'=>false,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                    return $g->createQueryBuilder('g')->where('g.id >= 2017')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
            }
            if($rol==8)
            {
                $form = $form
                ->add('departamento','entity',array('label'=>'Departamento','required'=>false,'class'=>'SieAppWebBundle:DepartamentoTipo','query_builder'=>function(EntityRepository $dp){
                    return $dp->createQueryBuilder('dp')->where('dp.id >= 1')->orderBy('dp.departamento','ASC');},'property'=>'departamento','empty_value' => 'Todos','attr'=>array('class'=>'form-control')));
            }
            $form =$form    
            /*->add('distrito','entity',array('label'=>'Distrito','required'=>true,'class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $ds){
                return $ds->createQueryBuilder('ds')->orderBy('ds.distrito','ASC');},'property'=>'distrito','empty_value' => 'Todos'))*/
            ->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-success form-control', 'onclick'=>'buscarceaspendientes()')))
            ->getForm();

        return $form;
    }
    ///////////////////----------------------------------------->
    ///////////////////----------------------------------------->
    ///////////////////----------------------------------------->
    ///////////////////----------------------------------------->
    public function historialceasForm($rol,$ie_id)
    {   
        $form = $this->createFormBuilder();
        if($rol==9 or $rol==10 or $rol==2){
            $gestion = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getGestionCea($ie_id);
            $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($ie_id,'');
            //dump($gestion);die;
            $gestionesArray = array();
            $subceasArray = array();
            //$provincia[-1] = '-Todos-';
    	    foreach ($gestion as $g) {
                //dump($g);die;
                $gestionesArray[$g['gestion']] = $g['gestion'];
            }
            foreach ($subcea as $sc) {
                //dump($sc);die;
                $subceasArray[$sc['sucursal']] = $sc['sucursal'];
            }
            $form=$form
                ->add('codsie','text',array('label'=>'Cod. SIE:','data'=>$ie_id,'read_only'=>true,'attr'=>array('class'=>'form-control')))
                ->add('gestion','choice',array('label'=>'Gestión:','required'=>true,'data'=>(new \DateTime())->format('Y'),'choices'=>$gestionesArray,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
                ->add('subcea','choice',array('label'=>'Sucursal:','required'=>true,'choices'=>$subceasArray,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
        }else{
            $form=$form
                ->add('codsie','text',array('label'=>'Cod. SIE:', 'attr'=>array('maxlength' => '8','class'=>'form-control')))
                ->add('gestion','choice',array('label'=>'Gestión:','required'=>true,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
                ->add('subcea','choice',array('label'=>'Sucursal:','required'=>true,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
        }
        //->setAction($this->generateUrl('herramientalt_ceducativa_estadistiscas_cierre'))
        $form=$form
            ->add('semestre','entity',array('label'=>'Semestre:','required'=>false,'class'=>'SieAppWebBundle:PeriodoTipo','query_builder'=>function(EntityRepository $p){
                return $p->createQueryBuilder('p')->where('p.id=2 or p.id=3');},'property'=>'periodo','empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
            ->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-success', 'onclick'=>'buscarhistorial()')))
            ->getForm();
        return $form;
    }


    
    //************* ESTADISTICAS DE CIERRE 
    //************* ESTADISTICAS DE CIERRE 
    //************* ESTADISTICAS DE CIERRE 
    //************* ESTADISTICAS DE CIERRE 
    public function ceasstdcierreAction(Request $request) {
        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {

            return $this->redirect($this->generateUrl('login'));
        }
        //$gestionparam = '2018';
        //dump($request->get('form'));die;
        if ($request->get('form')) {
            $form = $request->get('form');
            $gestion = $form['gestion'];            
            $periodo = $form['semestre'];
            $operativo = $form['operativo'];
            $em = $this->getDoctrine()->getManager();
            $semestre = $em->getRepository('SieAppWebBundle:PeriodoTipo')->find($periodo);
            if($gestion<(new \DateTime())->format('Y'))
            {
                if($operativo == 9) {
                    $estadostramite = '5,9,12';
                    $titulo = 'Gestiones Pasadas (Inscripciones) - '. $semestre->getPeriodo() . ' ' . $gestion; 
                } else {                    
                    $estadostramite = '5,8,14';
                    $titulo = 'Gestiones Pasadas (Notas) - '. $semestre->getPeriodo() . ' ' . $gestion; 
                }   
            }else{
                if($operativo == 9) {
                    $estadostramite = '9,12';
                    $titulo = $gestion. ' ' . $semestre->getPeriodo() . '-Inscripciones';    
                }else{
                    $estadostramite = '8,14';
                    $titulo = $gestion. ' ' . $semestre->getPeriodo() . '-Notas';    
                }
            }
            //INSCRIPCIONES 7,11
            //NOTAS 6,13
            /*if ($form['val'] === '1'){
                    //INSCRIPCIONES 1ER BIM
                    $periodotecho = '3';
                    $gestion = $gestionparam;
                    $periodo = '2';                    
                    $estadostramite = '9,12';
                    $titulo = $form['titulo'];
            }else{
                if ($form['val'] === '2'){
                    //NOTAS 1ER BIM
                    $periodotecho = '3';
                    $gestion = $gestionparam;
                    $periodo = '2';
                    $estadostramite = '8,14';
                    $titulo = $form['titulo'];
                }else{
                    if ($form['val'] === '3'){
                        //INSCRIPCIONES 2DO BIM
                        $periodotecho = '3';
                        $gestion = $gestionparam;
                        $periodo = '3';                        
                        $estadostramite = '9,12';
                        $titulo = $form['titulo'];
                    }else{
                        if ($form['val'] === '4'){
                            //NOTAS 2BIM
                            $periodotecho = '3';
                            $gestion = $gestionparam;
                            $periodo = '3';                            
                            $estadostramite = '8,14';
                            $titulo = $form['titulo'];
                        }else{
                            if ($form['val'] === '5'){
                                //NOTAS 2BIM
                                $periodotecho = '3';
                                $gestion = '2017,2016,2015,2014,2013,2012,2011,2010,2009,2008,2007,2006';
                                $periodo = '2,3';                            
                                $estadostramite = '5';
                                $titulo = $form['titulo'];
                            }
                        }       
                    }
                }
            } */           
        } else {
            $periodotecho = '3';
            $gestion = (new \DateTime())->format('Y');
            //dump($gestion);die;
            $periodo = '2';                    
            $estadostramite = '9,12';
            $titulo = 'Primer Semestre '.$gestion.'- Inscripciones';
        }        

        //$sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $queryAnterior = " select lugar, canttecho, (canttecho - cantconcluido) as cantfaltante from (  
                    select lugar, canttecho, COALESCE(cantconcluido,'0') as cantconcluido
                        from (
                                select dd.lugar, count(*) as canttecho
                                 from (
                                   select
                                    k.lugar,          
                                    d.id as ieue,	
                                    d.institucioneducativa
                                    from jurisdiccion_geografica a 
                                            inner join (
                                                    select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                            from lugar_tipo
                                                    where lugar_nivel_id=7 
                                            ) b 	
                                            on a.lugar_tipo_id_distrito = b.id
                                            inner join (

                                                    select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                    from institucioneducativa a                                                        
                                                    where 
                                                        a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and a.estadoinstitucion_tipo_id = 10
                                                    and a.institucioneducativa_acreditacion_tipo_id = 1
                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id
                                            inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    group by k.lugar, d.id, d.institucioneducativa
                                    order by k.lugar, d.id
                                    ) dd
                                    group by dd.lugar ) a left join (
                                    select dd.lugar as lugarcount, count(*) as cantconcluido
                                    from
                                        (select k.lugar, ie.id, ie.institucioneducativa		
                                                from jurisdiccion_geografica jg 
                                                inner join (
                                                        select id, codigo as cod_dis, lugar_tipo_id, lugar
                                                                from lugar_tipo
                                                        where lugar_nivel_id=7 
                                                ) lt 	
                                                on jg.lugar_tipo_id_distrito = lt.id

                                                inner join (
                                                        select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                                        from institucioneducativa a
                                                ) ie		
                                                on jg.id = ie.le_juridicciongeografica_id
                                                inner join institucioneducativa_sucursal z on ie.id = z.institucioneducativa_id
                                                inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                                        where z.gestion_tipo_id in (".$gestion.")
                                        and z.periodo_tipo_id in (".$periodo.")
                                        and w.tramite_estado_id in (".$estadostramite.") 
                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id) dd
                                      group by dd.lugar ) b on a.lugar=b.lugarcount ) abc ";
            
        $query = "     
            select lugar, canttecho, (canttecho - COALESCE(cantconcluido,'0')) as cantfaltante
            from (
                select dd.departamento_id AS lugar_id, dd.departamento as lugar, count(*) as canttecho
                from (
                    select lt14.id as departamento_id, lt14.lugar as departamento
                    , lt15.id as distrito_id, lt15.lugar as distrito
                    , d.id as ieue, d.institucioneducativa
                    from jurisdiccion_geografica a 
                    inner join (
                        select a.id, a.institucioneducativa, a.le_juridicciongeografica_id              
                        from institucioneducativa a                                                        
                        where a.orgcurricular_tipo_id = 2 and a.institucioneducativa_tipo_id = 2
                        and a.estadoinstitucion_tipo_id = 10 and a.institucioneducativa_acreditacion_tipo_id = 1
                    ) d on a.id=d.le_juridicciongeografica_id
                    inner join lugar_tipo as lt10 on lt10.id = a.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                    inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                    inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                    inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                    inner join lugar_tipo as lt15 on lt15.id = a.lugar_tipo_id_distrito
                    group by lt14.id, lt14.lugar, lt15.id, lt15.lugar, d.id, d.institucioneducativa
                ) dd
                group by dd.departamento_id, dd.departamento
            ) a 
            left join (
                select departamento_id as lugar_id, sum(case when subconcluido = subtotal then 1 else 0 end) as cantconcluido  from (
                select ie.id, lt15.id as distrito_id, lt14.id as departamento_id
                , sum(case iest.tramite_estado_id when 8 then 1 when 9 then 1 when 12 then 1 when 14 then 1 when 10 then 1 else 0 end) as subconcluido
                , sum(case iest.tramite_estado_id when 6 then 1 when 7 then 1 when 11 then 1 when 13 then 1 when 5 then 1 else 0 end) as subabierto
                , count(ies.sucursal_tipo_id) as subtotal
                from institucioneducativa_sucursal as ies
                inner join institucioneducativa_sucursal_tramite as iest on iest.institucioneducativa_sucursal_id = ies.id
                inner join tramite_estado as te on te.id = iest.tramite_estado_id
                inner join institucioneducativa as ie on ie.id = ies.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ies.le_juridicciongeografica_id
                left join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                left join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                left join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                left join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                left join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                where ies.gestion_tipo_id = ".$gestion." and ies.periodo_tipo_id = ".$periodo." and iest.tramite_estado_id in (".$estadostramite.")-- and lt14.codigo = '8' 
                group by ie.id, lt15.id, lt14.id
                ) as operativo
                group by departamento_id
            ) b on a.lugar_id=b.lugar_id 
            order by a.lugar
        ";        
        $porcentajes = $db->prepare($query);
        $params = array();
        $porcentajes->execute($params);
        $po = $porcentajes->fetchAll();
        
        $querytotAnterior = "
            select tottecho, (tottecho - COALESCE(cantconcluido,'0')) as totfaltante from(
                
            select sum(canttecho) as tottecho, sum(cantconcluido) as cantconcluido
                    from(
                         select *
                            from ( select dd.lugar, count(*) as canttecho
                                     from (
                                       select
                                        k.lugar,          
                                        d.id as ieue,	
                                        d.institucioneducativa
                                        from jurisdiccion_geografica a 
                                                inner join (
                                                        select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                                from lugar_tipo
                                                        where lugar_nivel_id=7 
                                                ) b 	
                                                on a.lugar_tipo_id_distrito = b.id
                                                inner join (
                                                        select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                    from institucioneducativa a                                                        
                                                    where 
                                                            a.orgcurricular_tipo_id = 2
                                                        and a.institucioneducativa_tipo_id = 2
                                                        and a.estadoinstitucion_tipo_id = 10
                                                        and a.institucioneducativa_acreditacion_tipo_id = 1
                                                ) d		
                                                on a.id=d.le_juridicciongeografica_id
                                                inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                        group by k.lugar, d.id, d.institucioneducativa
                                        order by k.lugar, d.id                                    
                                        ) dd
                                        group by dd.lugar
                                        ) a left join (
                                        select dd.lugar, count(*) as cantconcluido
                                        from
                                            (select k.lugar, ie.id, ie.institucioneducativa		
                                                    from jurisdiccion_geografica jg 
                                                    inner join (
                                                            select id, codigo as cod_dis, lugar_tipo_id, lugar
                                                                    from lugar_tipo
                                                            where lugar_nivel_id=7 
                                                    ) lt 	
                                                    on jg.lugar_tipo_id_distrito = lt.id

                                                    inner join (
                                                            select a.id, a.le_juridicciongeografica_id, a.institucioneducativa
                                                            from institucioneducativa a
                                                    ) ie		
                                                    on jg.id = ie.le_juridicciongeografica_id
                                                    inner join institucioneducativa_sucursal z on ie.id = z.institucioneducativa_id
                                                    inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                                            where z.gestion_tipo_id in (".$gestion.")
                                            and z.periodo_tipo_id in (".$periodo.")
                                            and w.tramite_estado_id in (".$estadostramite.")  
                                            group by k.lugar, ie.id, ie.institucioneducativa
                                            order by k.lugar, ie.id) dd
                                          group by dd.lugar ) b on a.lugar=b.lugar
                               ) ff) abcds";        
        $querytot = "   
            select tottecho, (tottecho - COALESCE(totconcluido,0)) as totfaltante from(  
                select sum(canttecho) as tottecho, sum(COALESCE(cantconcluido,0)) as totconcluido
                from (
                    select dd.departamento_id AS lugar_id, dd.departamento as lugar, count(*) as canttecho
                    from (
                        select lt14.id as departamento_id, lt14.lugar as departamento
                        , lt15.id as distrito_id, lt15.lugar as distrito
                        , d.id as ieue, d.institucioneducativa
                        from jurisdiccion_geografica a 
                        inner join (
                            select a.id, a.institucioneducativa, a.le_juridicciongeografica_id              
                            from institucioneducativa a                                                        
                            where a.orgcurricular_tipo_id = 2 and a.institucioneducativa_tipo_id = 2
                            and a.estadoinstitucion_tipo_id = 10 and a.institucioneducativa_acreditacion_tipo_id = 1
                        ) d on a.id=d.le_juridicciongeografica_id
                        inner join lugar_tipo as lt10 on lt10.id = a.lugar_tipo_id_localidad
                        inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                        inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                        inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                        inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                        inner join lugar_tipo as lt15 on lt15.id = a.lugar_tipo_id_distrito
                        group by lt14.id, lt14.lugar, lt15.id, lt15.lugar, d.id, d.institucioneducativa
                    ) dd
                    group by dd.departamento_id, dd.departamento
                ) a 
                left join (
                    select departamento_id as lugar_id, sum(case when subconcluido = subtotal then 1 else 0 end) as cantconcluido  from (
                    select ie.id, lt15.id as distrito_id, lt14.id as departamento_id
                    , sum(case iest.tramite_estado_id when 8 then 1 when 9 then 1 when 12 then 1 when 14 then 1 when 10 then 1 else 0 end) as subconcluido
                    , sum(case iest.tramite_estado_id when 6 then 1 when 7 then 1 when 11 then 1 when 13 then 1 when 5 then 1 else 0 end) as subabierto
                    , count(ies.sucursal_tipo_id) as subtotal
                    from institucioneducativa_sucursal as ies
                    inner join institucioneducativa_sucursal_tramite as iest on iest.institucioneducativa_sucursal_id = ies.id
                    inner join tramite_estado as te on te.id = iest.tramite_estado_id
                    inner join institucioneducativa as ie on ie.id = ies.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ies.le_juridicciongeografica_id
                    left join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                    left join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                    left join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                    left join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                    left join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                    left join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                    where ies.gestion_tipo_id = ".$gestion." and ies.periodo_tipo_id = ".$periodo." and iest.tramite_estado_id in (".$estadostramite.")-- and lt14.codigo = '8' 
                    group by ie.id, lt15.id, lt14.id
                    ) as operativo
                    group by departamento_id
                ) b on a.lugar_id=b.lugar_id 
            ) as abc
        ";
        $totales = $db->prepare($querytot);
        $params = array();
        $totales->execute($params);
        $potot = $totales->fetchAll();
                
        //dump($potot); die;
        
        return $this->render($this->session->get('pathSystem') . ':Default:estadisticasoperativo.html.twig', array(
                'entities' => $po,
                'entitiestot' => $potot,
                'titulo' => $titulo,
                'form' => $this->estadisticaForm()->createView(),
            ));
    }
    public function estadisticaForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramientalt_ceducativa_estadistiscas_cierre'))
            ->add('gestion','entity',array('label'=>'Gestión','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
               return $g->createQueryBuilder('g')->where('g.id >= 2006')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => 'Seleccione gestión'))
            ->add('semestre','entity',array('label'=>'Semestre','required'=>true,'class'=>'SieAppWebBundle:PeriodoTipo','query_builder'=>function(EntityRepository $p){
              return $p->createQueryBuilder('p')->where('p.id in (2,3)');},'property'=>'periodo','empty_value' => 'Seleccione semestre'))
            ->add('operativo','choice',array('label'=>'Operativo','required'=>true,'choices'=>array('9' => 'Inscripciones','8' => 'Notas'),'empty_value' => 'Seleccione operativo'))
            ->add('buscar', 'submit', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-primary')))
            ->getForm();

        return $form;
    }
    public function submenuDiversaAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC')
            ->where(' g.id > 2017')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }
//dump($gestionesArray);die;
        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $repository = $em->getRepository('SieAppWebBundle:SucursalTipo');
        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id')
            ->getQuery();
        $sucursales = $query->getResult();
        $sucursalesArray = array();
        foreach ($sucursales as $s) {
            $sucursalesArray[$s->getId()] = $s->getId();
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('sie_alt_reportes_diversa'))
            //       ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
            ->add('gestion', 'choice', array('label' => 'Gestión', 'required' => true, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
            ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
            //   ->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => true, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
            ->add('crear', 'submit', array('label' => 'Generar Reportes', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:diversa.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function reporteDiversaAction(Request $request){

        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $gestion = $form['gestion'];
        $periodo = $form['periodo'];
        $roltipo = ' ';
        $semestre = ' ';

        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
       // dump($idlugarusuario);die;


       // dump($request);die;

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $idrol= $this->session->get('roluser');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $query = $em->getConnection()->prepare('
        select codigo from lugar_tipo where id = :idlugar 
');
        $query->bindValue(':idlugar', $idlugarusuario);

        $query->execute();
       $lugar =$query->fetch();

      if(($idrol==8)||($idrol==20))
      {
          $roltipo = 'Nacional';
      }elseif($idrol==7)
      {
          //departamental
          $roltipo = 'Departamental';
      }elseif($idrol==10)
      {
          //distrital
          $roltipo = 'Distrital';
      }elseif($idrol==9)
      {
          //distrital
          $roltipo = 'Centro';
      }

     //  dump($lugar);die;

//        $query = $em->getConnection()->prepare('
////        select * from periodo_tipo where id = :idperiodo
//');
//        $query->bindValue(':idperiodo', $periodo);
//
//        $query->execute();
//        $semestre =$query->fetch();
        //get and set the variables

        if($periodo==2)
        {
            $semestre = 'Primer Semestre';
        }elseif($periodo==3)
        {
            //departamental
            $semestre = 'Segundo Semestre';
        }

        $arrDataReport = array(
            'roluser' => $this->session->get('roluser'),
            'rol' =>  $roltipo,
            'semestre' =>  $semestre,
            'userId' => $this->session->get('userId'),
            'sie' => $this->session->get('ie_id'),
            'gestion' => $gestion,
            'subcea' => $this->session->get('ie_subcea'),
            'periodo' => $periodo,
            'lugarid'=> $lugar['codigo']
        );
        //dump($arrDataReport);die;

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:reporteDiversa.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
        ));



//        $em = $this->getDoctrine()->getManager();
//        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
//        dump($objUeducativa);die;
    }


    public function manualesAction(Request $request){

        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
        // dump($idlugarusuario);die;


        // dump($request);die;

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get and set the variables

        $arrDataReport = array(
            'roluser' => $this->session->get('roluser'),
            'userId' => $this->session->get('userId'),
            'sie' => $this->session->get('ie_id'),
            'gestion' => $this->session->get('ie_gestion'),
            'subcea' => $this->session->get('ie_subcea'),
            'periodo' => $this->session->get('ie_per_cod'),
            'lugarid'=> $idlugarusuario
        );

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:manuales.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
        ));



//        $em = $this->getDoctrine()->getManager();
//        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
//        dump($objUeducativa);die;
    }

    public function listarprovinciasAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 2)
                ->setParameter('lt1', $dpto + 1);
            $provincias = $query->getResult();

            $provinciasArray = array();
            foreach ($provincias as $c) {
                $provinciasArray[$c->getId()] = $c->getLugar();
            }

            $query = $em->getConnection()->prepare("
                select id, codigo, lugar from lugar_tipo where lugar_nivel_id = 7 and cast(substring(codigo from 1 for 1) as integer) = ".$dpto
            );
            $query->execute();
            $distritos = $query->fetchAll();

            $distritosArray = array();
            foreach ($distritos as $c) {
                $distritosArray[$c['id']] = $c['lugar'];
            }            

            $response = new JsonResponse();
            return $response->setData(array('listaprovincias' => $provinciasArray, 'listadistritos' => $distritosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function listarmunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 3)
                ->setParameter('lt1', $prov);
            $municipios = $query->getResult();

            $municipiosArray = array();
            foreach ($municipios as $c) {
                $municipiosArray[$c->getId()] = $c->getLugar();
            }     

            $response = new JsonResponse();
            return $response->setData(array('listamunicipios' => $municipiosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarcantonesAction($muni) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 4)
                ->setParameter('lt1', $muni);
            $cantones = $query->getResult();

            $cantonesArray = array();
            foreach ($cantones as $c) {
                $cantonesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listacantones' => $cantonesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarlocalidadesAction($cantn) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 5)
                ->setParameter('lt1', $cantn);
            $localidades = $query->getResult();

            $localidadesArray = array();
            foreach ($localidades as $c) {
                $localidadesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listalocalidades' => $localidadesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listardistritosAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT dt
                    FROM SieAppWebBundle:DistritoTipo dt
                    WHERE dt.id NOT IN (:ids)
                    AND dt.departamentoTipo = :dpto
                    ORDER BY dt.id')
                ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
                ->setParameter('dpto', $dpto);
            $distritos = $query->getResult();

            $distritosArray = array();
            foreach ($distritos as $c) {
                $distritosArray[$c->getId()] = $c->getDistrito();
            }

            $response = new JsonResponse();
            return $response->setData(array('listadistritos' => $distritosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }
    public function ceducativaListaGestionAction(Request $request) {
        //dump($request);die;
        $id_cea = $request->get('id_cea');
        $gestion = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getGestionCea($id_cea);
        $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($id_cea,'');
        //dump($subcea);die;
        $gestionesArray = array();
        $subceasArray = array();
        //$provincia[-1] = '-Todos-';
        usort($gestion, function($a, $b) {
            return intval($b['gestion']) - intval($a['gestion']);
        });

    	foreach ($gestion as $g) {
                //dump($g);die;
                $gestionesArray[$g['gestion']] = $g['gestion'];
        }
        foreach ($subcea as $sc) {
            //dump($sc);die;
                $subceasArray[$sc['sucursal']] = $sc['sucursal'];
        }
        //dump($gestionesArray);die;
    	$response = new JsonResponse();
    	return $response->setData(array('gestion' => $gestionesArray,'subcea'=>$subceasArray));
    }

    public function ceducativaListaSubceaAction(Request $request) {
        //dump($request);die;
        $id_cea = $request->get('id_cea');
        $gestion = $request->get('gestion');
        $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($id_cea,$gestion);
        //dump($subcea);die;
        $subceasArray = array();
        //$provincia[-1] = '-Todos-';
    	foreach ($subcea as $sc) {
            //dump($sc);die;
                $subceasArray[$sc['sucursal']] = $sc['sucursal'];
        }
        //dump($subceasArray);die;
    	$response = new JsonResponse();
    	return $response->setData(array('subcea' => $subceasArray));
    }
    public function submenuAlterPrimariaAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC')
            ->where(' g.id > 2017')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }
//dump($gestionesArray);die;
        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('sie_alt_reportes_alterprimaria'))
            //       ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
            ->add('gestion', 'choice', array('label' => 'Gestión', 'required' => true, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
            ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
            //   ->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => true, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
            ->add('crear', 'submit', array('label' => 'Generar Reportes', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:alterPrimaria.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function reporteAlterPrimariaAction(Request $request){

        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $gestion = $form['gestion'];
        $periodo = $form['periodo'];
        $roltipo = ' ';
        $semestre = ' ';

        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
        // dump($idlugarusuario);die;


        // dump($request);die;

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $idrol= $this->session->get('roluser');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $query = $em->getConnection()->prepare('
        select codigo from lugar_tipo where id = :idlugar 
');
        $query->bindValue(':idlugar', $idlugarusuario);

        $query->execute();
        $lugar =$query->fetch();

        if(($idrol==8)||($idrol==20))
        {
            $roltipo = 'Nacional';
        }elseif($idrol==7)
        {
            //departamental
            $roltipo = 'Departamental';
        }elseif($idrol==10)
        {
            //distrital
            $roltipo = 'Distrital';
        }elseif($idrol==9)
        {
            //distrital
            $roltipo = 'Centro';
        }elseif($idrol==20)
        {
            //distrital
            $roltipo = 'Invitado Nacional';
        }

        //  dump($lugar);die;

//        $query = $em->getConnection()->prepare('
////        select * from periodo_tipo where id = :idperiodo
//');
//        $query->bindValue(':idperiodo', $periodo);
//
//        $query->execute();
//        $semestre =$query->fetch();
        //get and set the variables

        if($periodo==2)
        {
            $semestre = 'Primer Semestre';
        }elseif($periodo==3)
        {
            //departamental
            $semestre = 'Segundo Semestre';
        }

        $arrDataReport = array(
            'roluser' => $this->session->get('roluser'),
            'rol' =>  $roltipo,
            'semestre' =>  $semestre,
            'userId' => $this->session->get('userId'),
            'sie' => $this->session->get('ie_id'),
            'gestion' => $gestion,
            //'subcea' => $this->session->get('ie_subcea'),
            'periodo' => $periodo,
            'lugarid'=> $lugar['codigo']
        );
        //dump($arrDataReport);die;

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:reporteAlterPrimaria.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
        ));



//        $em = $this->getDoctrine()->getManager();
//        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
//        dump($objUeducativa);die;
    }
}
