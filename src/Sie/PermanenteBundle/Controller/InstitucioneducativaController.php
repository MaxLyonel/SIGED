<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;


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
                        inss.direccion')
                ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
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
            $turnosArray[$t->getId()] = $t->getTurno();
        }
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_ceducativa_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $institucion))
                ->add('gestionSuc', 'hidden', array('data' => $sucursal->getGestionTipo()))
                ->add('idSucursal', 'hidden', array('data' => $sucursal->getId()))
                ->add('idPeriodo', 'hidden', array('data' => $sucursal->getPeriodoTipoId()))
                ->add('telefono1', 'text', array('required' => false, 'label' => 'Número de teléfono del CEA', 'data' => $sucursal->getTelefono1(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('telefono2', 'text', array('required' => true, 'label' => 'Número de celular de la Directora o Director', 'data' => $sucursal->getTelefono2(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('email', 'text', array('required' => true, 'label' => 'Correo electrónico del CEA o de la Directora o Director', 'data' => $sucursal->getEmail(), 'attr' => array('class' => 'form-control')))
                ->add('casilla', 'text', array('required' => false, 'label' => 'Casilla postal del CEA', 'data' => $sucursal->getCasilla(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('turno', 'choice', array('label' => 'Turno', 'required' => true, 'choices' => $turnosArray, 'data' => $sucursal->getTurnoTipo() ? $sucursal->getTurnoTipo()->getId() : 0, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
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
            $iesucursal->setEmail(mb_strtoupper($form['email'], 'utf-8'));
            $iesucursal->setCasilla($form['casilla']);
            $iesucursal->setReferenciaTelefono2(mb_strtoupper('DIRECTOR/A', 'utf-8'));
            $iesucursal->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));
            $em->persist($iesucursal);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('herramienta_ceducativa_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('herramienta_ceducativa_index'));
        }
    }
    
    public function gessubsemAction() {
        $iesubsea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getAllSucursalTipo($this->session->get('ie_id'));
        //dump($iesubsea);die;

        $reversed = array_reverse($iesubsea);

        return $this->render($this->session->get('pathSystem') . ':Principal:operativos.html.twig', array('iesubsea' => $reversed));
    }

    public function gessubsemopenAction(Request $request, $teid, $gestion, $subcea, $semestre, $idiesuc) {
        $sesion = $request->getSession();
        $sesion->set('ie_gestion', $gestion);
        $sesion->set('ie_subcea', $subcea);
        $sesion->set('ie_per_cod', $semestre);
        $sesion->set('ie_suc_id', $idiesuc);
        
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
        
        switch ($teid) {
            case 0://MODO EDICION 
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '!Modo edición!');
                break;            
            case 6: //REGULARIZACION NOTAS O INSCRIPCIONES
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '!En operativo de regularización!');
                break;                       
            case 10: //INSCRIPCIONES - INICIO DE SEMESTRE
                $sesion->set('ie_per_estado', '1');
                $sesion->set('ie_operativo', '¡En operativo inscripciones!');
                break;
            case 11: //NOTAS - FIN DE SEMESTRE
                $sesion->set('ie_per_estado', '2');
                $sesion->set('ie_operativo', '¡En operativo notas!');
                break;
            case 12: //INSCRIPCIONES Y NOTAS - INICIO DE SISTEMA UNA SOLA VEZ EN LA VIDA -MODO EDICION
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡En operativo de inicio de sistema academico (inscripciones y notas)!');
                break;  
            case 13: //INSCRIPCIONES Y NOTAS - POR MIGRACION DE SISTEMA UNA SOLA VEZ EN LA VIDA -MODO EDICION
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡Operativo unico para regularización de información, gestiones pasadas!');
                
                $em = $this->getDoctrine()->getManager();
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
                $sesion->set('ie_per_estado', '0');
                break;
            case 100: //MAESTRO DE UNIDAD EDUCATIVA ALTER
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡En operativo fin de semestre (notas)!');                
                return $this->redirectToRoute('herramienta_alter_notas_maestro_index');
                break;
            default:
                $sesion->set('ie_per_estado', '0');
        }
        
        return $this->render($this->session->get('pathSystem') . ':Principal:menuprincipal.html.twig');
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
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();  
        
        if ($sesion->get('roluser') == '8' ){//NACIONAL
//            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));            
//            $coddis = $usuariorol[0]->getLugarTipo()->getCodigo();
//            dump($usuariorol);
//            die;
            $query = "
                select  w.id as iestid, k.id as deptoid, k.lugar, w.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                     z.sucursal_tipo_id as nombre_subcea, z.gestion_tipo_id, 
                              CASE
                                 WHEN z.periodo_tipo_id = 2 THEN
                                    'PRIMERO'
                                 WHEN z.periodo_tipo_id = 3 THEN
                                    'SEGUNDO'
                              END AS semestre,          
                     z.periodo_tipo_id,
                     p.paterno, p.materno, p.nombre,
                     x.tramite_estado, w.tramite_estado_id as teid, w.fechainicio as fechacierre, x.obs 
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
                        inner join (

                        select id, usuario_id_inicio, institucioneducativa_sucursal_id, distrito_cod, tramite_estado_id, fechainicio
                        from institucioneducativa_sucursal_tramite iest

			inner join 
				(
				select max(b.id) as maxid, sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id, max(periodo_tipo_id)
				from institucioneducativa_sucursal a
				inner join institucioneducativa_sucursal_tramite b on b.institucioneducativa_sucursal_id = a.id
				
				group by sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id
				) q on q.maxid = iest.id

                        ) w

                        on w.institucioneducativa_sucursal_id = z.id
                        
                        inner join usuario d on w.usuario_id_inicio = d.id
                        inner join persona p on d.persona_id = p.id
                        inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                        inner join tramite_estado x on w.tramite_estado_id = x.id                          
                        
                where w.tramite_estado_id <> 5 
                order by lugar, w.distrito_cod, ie.id, nombre_subcea, paterno, materno, nombre";
        }
        
        if ($sesion->get('roluser') == '7' ){//DEPARTAMENTO            
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));            
            $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
//            dump($idlugarusuario);die;
//            die;
            $query = "
                select  w.id as iestid, k.id as deptoid, k.lugar, w.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                     z.sucursal_tipo_id as nombre_subcea, z.gestion_tipo_id, 
                              CASE
                                 WHEN z.periodo_tipo_id = 2 THEN
                                    'PRIMERO'
                                 WHEN z.periodo_tipo_id = 3 THEN
                                    'SEGUNDO'
                              END AS semestre,          
                     z.periodo_tipo_id,
                     p.paterno, p.materno, p.nombre,
                     x.tramite_estado, w.tramite_estado_id as teid, w.fechainicio as fechacierre, x.obs 
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
                        inner join (

                        select id, usuario_id_inicio, institucioneducativa_sucursal_id, distrito_cod, tramite_estado_id, fechainicio
                        from institucioneducativa_sucursal_tramite iest

			inner join 
				(
				select max(b.id) as maxid, sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id, max(periodo_tipo_id)
				from institucioneducativa_sucursal a
				inner join institucioneducativa_sucursal_tramite b on b.institucioneducativa_sucursal_id = a.id
				
				group by sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id
				) q on q.maxid = iest.id

                        ) w
                        
                        on w.institucioneducativa_sucursal_id = z.id
                        
                        inner join usuario d on w.usuario_id_inicio = d.id
                        inner join persona p on d.persona_id = p.id
                        inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                        inner join tramite_estado x on w.tramite_estado_id = x.id

                where k.id = '".$idlugarusuario."'
                    and w.tramite_estado_id <> 5 
                order by lugar, w.distrito_cod, ie.id, nombre_subcea, paterno, materno, nombre";
        }
        
        if ($sesion->get('roluser') == '10' ){//DISTRITO            
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));            
            $coddis = $usuariorol[0]->getLugarTipo()->getCodigo();
//            dump($coddis);
//            die;
            $query = "
                select  w.id as iestid, k.id as deptoid, k.lugar, w.distrito_cod, ie.id as ieid, ie.institucioneducativa,
                     z.sucursal_tipo_id as nombre_subcea, z.gestion_tipo_id, 
                              CASE
                                 WHEN z.periodo_tipo_id = 2 THEN
                                    'PRIMERO'
                                 WHEN z.periodo_tipo_id = 3 THEN
                                    'SEGUNDO'
                              END AS semestre,          
                     z.periodo_tipo_id,
                     p.paterno, p.materno, p.nombre,
                     x.tramite_estado, w.tramite_estado_id as teid, w.fechainicio as fechacierre, x.obs 
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
                            inner join (

                            select id, usuario_id_inicio, institucioneducativa_sucursal_id, distrito_cod, tramite_estado_id, fechainicio
                            from institucioneducativa_sucursal_tramite iest

                            inner join 
                                    (
                                    select max(b.id) as maxid, sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id, max(periodo_tipo_id)
                                    from institucioneducativa_sucursal a
                                    inner join institucioneducativa_sucursal_tramite b on b.institucioneducativa_sucursal_id = a.id

                                    group by sucursal_tipo_id, institucioneducativa_id, gestion_tipo_id
                                    ) q on q.maxid = iest.id

                            ) w
                             on w.institucioneducativa_sucursal_id = z.id
                            inner join usuario d on w.usuario_id_inicio = d.id
                            inner join persona p on d.persona_id = p.id
                            inner join lugar_tipo k on k.id = lt.lugar_tipo_id
                            inner join tramite_estado x on w.tramite_estado_id = x.id
                    where w.distrito_cod = '".$coddis."'
                        and w.tramite_estado_id <> 5 
                    order by lugar, w.distrito_cod, ie.id, nombre_subcea, paterno, materno, nombre";
        }
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
//        dump($po);
//        die;
        return $this->render($this->session->get('pathSystem') . ':Principal:listaceacerrado.html.twig', array(
                'entities' => $po,
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

    public function cerraroperativoAction(Request $request) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $db = $em->getConnection();
        try {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();
            $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($sesion->get('ie_suc_id'));            
            $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findByInstitucioneducativaSucursal($ies);
            if ($iest){                
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

                    $query = "select count(a.estudiante_inscripcion_id) 
                                from estudiante_inscripcion_socioeconomico_alternativa a 
                                inner join estudiante_inscripcion b on b.id = a.estudiante_inscripcion_id
                                inner join institucioneducativa_curso c on c.id = b.institucioneducativa_curso_id
                                inner join institucioneducativa d on d.id = c.institucioneducativa_id
                                inner join institucioneducativa_sucursal e on e.institucioneducativa_id = d.id
                                where c.institucioneducativa_id = '".$this->session->get('ie_id')."'
                                and e.gestion_tipo_id = 2017 and e.periodo_tipo_id = 3";                    
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $socioeco = $obs->fetchAll();
                    //dump($socioeco);die;
                    if (!$socioeco){
                        $query = "select * from sp_genera_migracion_socioeconomicos_alter('".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$gestant."','".$perant."','".$this->session->get('ie_gestion')."','".$this->session->get('ie_per_cod')."','');";
                        $obs= $db->prepare($query);
                        $params = array();
                        $obs->execute($params);
                    }

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
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '7'){//Autorizado para regularización(Inicio de Semestre)
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('9'));//Inicio de Semestre Regularizado - Cerrado                             
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                        }                      
                    }
                }
                if ($sesion->get('ie_per_estado') == '2'){//FIN NOTAS
                    //die('df');
                    $query = "select * from sp_validacion_alternativa_web('".$this->session->get('ie_gestion')."','".$this->session->get('ie_id')."','".$this->session->get('ie_subcea')."','".$this->session->get('ie_per_cod')."');";
                    $obs= $db->prepare($query);
                    $params = array();
                    $obs->execute($params);
                    $observaciones = $obs->fetchAll();
                    if ($observaciones){
                        return $this->redirect($this->generateUrl('herramienta_alter_reporte_observacionesoperativo'));                    }
                    else{
                        //die('f');
//                        $em->getConnection()->rollback();
//                        return $this->redirectToRoute('principal_web');
//                        print_r($iest[0]->getTramiteEstado()->getId());
//                        die;
                        if ($iest[0]->getTramiteEstado()->getId() == '6'){//¡En regularización notas!
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('8'));//Ver regularización notas terminada                          
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                        }
                        if ($iest[0]->getTramiteEstado()->getId() == '13'){//¡En notas!
                            $iestvar = $iest[0];
                            $iestvar->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('14'));//Ver notas terminadas
                            $iestvar->setFechaModificacion(new \DateTime('now'));
                            $iestvar->setUsuarioIdModificacion($this->session->get('userId'));
                            $em->persist($iestvar);
                            $em->flush(); 
                        } 
                    }
                }
                if ($sesion->get('ie_per_estado') == '3'){//OPERATIVO DE INICIO DE SISTEMA O MODO REGULARIZACION     
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
            }
            else{//EN CASO QUE LA SUCURSAL PERIODO NO TENG ASIGNADO UN PERIODO TRAMITE               
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
            
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        return $this->redirectToRoute('principal_web');
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
                    $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));                
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
        
        return $this->render($this->session->get('pathSystem') . ':Principal:distritolistaceas.html.twig', array(
                'entities' => $po,
            ));
    }
    
    public function abrirceaAction(Request $request, $ie_id, $ie_nombre) {
        $sesion = $request->getSession();
        $sesion->set('ie_id',$ie_id);
        $sesion->set('ie_nombre', $ie_nombre);
        $sesion->set('ie_per_estado', '3');
        $sesion->set('ie_operativo', '¡En modo edición!');
        return $this->redirect($this->generateUrl('principal_web'));
    }
    
    public function seleccionarceaAction() {
        return $this->render($this->session->get('pathSystem') . ':Principal:seleccionarcea.html.twig');
    }
    
    public function buscarceaAction(Request $request) {        
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $iesubsea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getAllSucursalTipo($this->session->get('ie_id'));
        //dump($iesubsea);die;



        /*
         * verificamos si tiene tuicion
         */        
        $usuario_id = $this->session->get('userId');        
        $usuario_rol = $this->session->get('roluser');
        
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $usuario_id);
        $query->bindValue(':sie', $form['Inputcodsie']);
        $query->bindValue(':rolId', $usuario_rol);
        $query->execute();
        $aTuicion = $query->fetchAll();        
//        dump($usuario_id.' '.$idInstitucion.' '.$usuario_rol);
//        die;
        if ($aTuicion[0]['get_ue_tuicion']) {
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['Inputcodsie']);
            if ($ie){
                $sesion = $request->getSession();
                $sesion->set('ie_id',$form['Inputcodsie']);
                $sesion->set('ie_nombre', $ie->getInstitucioneducativa());
                $sesion->set('ie_per_estado', '3');
                $sesion->set('ie_operativo', '¡En modo edición!');
                $reversed = array_reverse($iesubsea);
         //       return $this->render($this->session->get('pathSystem') . ':Principal:operativos.html.twig', array('iesubsea' => $reversed));

                  return $this->redirect($this->generateUrl('sie_per_ges_sub_sem'));
            }
            else{           
                $this->session->getFlashBag()->add('notfound', 'El código de institución educativo no se encuentra.');
                return $this->redirect($this->generateUrl('herramienta_per_ceducativa_seleccionar_cea'));
            }    
        }
        else{
           $this->session->getFlashBag()->add('notfound', '¡Error! No tiene tuición sobre la unidad educativa.');
           return $this->redirect($this->generateUrl('herramienta_per_ceducativa_seleccionar_cea'));
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
            $this->get('session')->getFlashBag()->add('errorMsg', '¡Error! El código SIE ingresado no es válido.');
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
                $this->get('session')->getFlashBag()->add('errorMsg', '¡Error! El CEA ya cuenta con el Perido seleccionado habilitado.');
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

                    $this->get('session')->getFlashBag()->add('successMsg', '¡Bien! Se ha habilitado el Periodo Seleccionado.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
                }else{
                    $this->get('session')->getFlashBag()->add('errorMsg', '¡Error! Ha ocurrido un problema en la generación del periodo.');
                    return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));                    
                }
            }            
        } else {
            $this->get('session')->getFlashBag()->add('errorMsg', '¡Error! No tiene tuición sobre la unidad educativa.');
            return $this->redirect($this->generateUrl('herramientalt_ceducativa_crear_periodo'));
        }
    }
    
    public function ceaspendientesAction(Request $request) {        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
         if ($sesion->get('roluser') == '8' ) {//NACIONAL
             $query = "                                 select *
                                 from (                                 
                                   select
                                    k.id as deptoid,
				    k.lugar,
                                    b.distrito_cod,            
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
                                                        inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and z.gestion_tipo_id = '2016'
                                                    and z.periodo_tipo_id = '3'
                                                    and w.tramite_estado_id in (8,14) 

                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id
                                            inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    group by k.id, k.lugar, b.distrito_cod, d.id, d.institucioneducativa
                                    order by k.lugar, d.id                                    
                                    ) dd
				    where dd.ieue not in
                                        (
                                        select ie.id	
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
                                        where z.gestion_tipo_id = '2017'
                                        and z.periodo_tipo_id = 2      
                                        and w.tramite_estado_id in (8,14) 
                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id
                                        ) ";             
         }
        
        if ($sesion->get('roluser') == '10' ) {//DISTRITAL
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));     
            $query = "select *
                                 from (                                 
                                   select
                                    k.id as deptoid,
				    k.lugar,
                                    b.distrito_cod,            
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
                                                        inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and z.gestion_tipo_id = '2016'
                                                    and z.periodo_tipo_id = '3'
                                                    and w.tramite_estado_id in (8,14) 

                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id
                                            inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    group by k.id, k.lugar, b.distrito_cod, d.id, d.institucioneducativa
                                    order by k.lugar, d.id                                    
                                    ) dd
				    where dd.ieue not in
                                        (
                                        select ie.id	
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
                                        where z.gestion_tipo_id = '2017'
                                        and z.periodo_tipo_id = 2      
                                        and w.tramite_estado_id in (8,14) 
                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id
                                        ) 
                                        and dd.distrito_cod = '".$usuariorol[0]->getLugarTipo()->getCodigo()."'";            
            
        }
        
        if ($sesion->get('roluser') == '7' ) {//DEPARTAMENTAL
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
            $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
            
            $query = "select *
                                 from (                                 
                                   select
                                    k.id as deptoid,
				    k.lugar,
                                    b.distrito_cod,            
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
                                                        inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and z.gestion_tipo_id = '2016'
                                                    and z.periodo_tipo_id = '3'
                                                    and w.tramite_estado_id in (8,14) 

                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id
                                            inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    where b.lugar_tipo_id = '".$idlugarusuario."'
                                    group by k.id, k.lugar, b.distrito_cod, d.id, d.institucioneducativa
                                    order by k.lugar, d.id                                    
                                    ) dd
				    where dd.ieue not in
                                        (
                                        select ie.id	
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
                                        where z.gestion_tipo_id = '2017'
                                        and z.periodo_tipo_id = 2      
                                        and w.tramite_estado_id in (8,14) 
                                        and k.id = '".$idlugarusuario."' 
                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id
                                        )";            
        }        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
//        dump($po);
//        die;
        return $this->render($this->session->get('pathSystem') . ':Principal:listaceapendientes.html.twig', array(
                'entities' => $po,
            ));
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
        
        if ($request->get('form')) {
            $form = $request->get('form');
            
            if ($form['val'] === '1'){
                    $gestiontecho = '2016';
                    $periodotecho = '3';

                    $gestion = '2017';
                    $periodo = '2';
                    //NOTAS 8,14
                    //INSCRIPCIONES 9,12 
                    $estadostramite = '9,12,8,14';
                    $titulo = $form['titulo'];
            }else{
                if ($form['val'] === '2'){
                    $gestiontecho = '2016';
                    $periodotecho = '3';

                    $gestion = '2017';
                    $periodo = '2';
                    //NOTAS 8,14
                    //INSCRIPCIONES 9,12 
                    $estadostramite = '8,14';
                    $titulo = $form['titulo'];
                }else{
                    if ($form['val'] === '3'){
                        $gestiontecho = '2016';
                        $periodotecho = '3';

                        $gestion = '2017';
                        $periodo = '3';
                        //NOTAS 8,14
                        //INSCRIPCIONES 9,12 
                        $estadostramite = '9,12,8,14';
                        $titulo = $form['titulo'];
                    }else{
                        if ($form['val'] === '4'){
                            $gestiontecho = '2016';
                            $periodotecho = '3';

                            $gestion = '2017';
                            $periodo = '3';
                            //NOTAS 8,14
                            //INSCRIPCIONES 9,12 
                            $estadostramite = '8,14';
                            $titulo = $form['titulo'];
                        }   
                    }
                }
            }            
        }else{
            $gestiontecho = '2016';
            $periodotecho = '3';

            $gestion = '2017';
            $periodo = '2';
            //NOTAS 8,14
            //INSCRIPCIONES 9,12 
            $estadostramite = '9,12,8,14';
            $titulo = '1er Semestre 2017-Notas';
        }        

        //$sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "   select *
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
                                                        inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and z.gestion_tipo_id = '".$gestiontecho."'
                                                    and z.periodo_tipo_id = ".$periodotecho."
                                                    and w.tramite_estado_id in (8,14) 

                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id
                                            inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                    group by k.lugar, d.id, d.institucioneducativa
                                    order by k.lugar, d.id
                                    ) dd
                                    group by dd.lugar ) a left join (

                                    select dd.lugar, count(*) as cantconclu
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
                                        where z.gestion_tipo_id = '".$gestion."'
                                        and z.periodo_tipo_id = ".$periodo."      
                                        and w.tramite_estado_id in (".$estadostramite.") 
                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id) dd
                                      group by dd.lugar ) b on a.lugar=b.lugar";        
        $porcentajes = $db->prepare($query);
        $params = array();
        $porcentajes->execute($params);
        $po = $porcentajes->fetchAll();
        
        $querytot = "select sum(canttecho) as tottecho, sum(cantconclu) as totconclu
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
                                                        inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                        inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                    where a.orgcurricular_tipo_id = 2
                                                    and a.institucioneducativa_tipo_id = 2
                                                    and z.gestion_tipo_id = '".$gestiontecho."'
                                                    and z.periodo_tipo_id = ".$periodotecho."
                                                    and w.tramite_estado_id in (8,14) 

                                                ) d		
                                                on a.id=d.le_juridicciongeografica_id
                                                inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                        group by k.lugar, d.id, d.institucioneducativa
                                        order by k.lugar, d.id                                    
                                        ) dd
                                        group by dd.lugar
                                        ) a left join (
                                        select dd.lugar, count(*) as cantconclu
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
                                            where z.gestion_tipo_id = '".$gestion."'
                                            and z.periodo_tipo_id = ".$periodo."
                                            and w.tramite_estado_id in (".$estadostramite.")  
                                            group by k.lugar, ie.id, ie.institucioneducativa
                                            order by k.lugar, ie.id) dd
                                          group by dd.lugar ) b on a.lugar=b.lugar
                               ) ff";        
        $totales = $db->prepare($querytot);
        $params = array();
        $totales->execute($params);
        $potot = $totales->fetchAll();
                
//        dump($potot);
//        die;
        
        return $this->render($this->session->get('pathSystem') . ':Default:estadisticasoperativo.html.twig', array(
                'entities' => $po,
                'entitiestot' => $potot,
                'titulo' => $titulo,
            ));
//        return $this->render($this->session->get('pathSystem') . ':Default:estadisticasoperativo.html.twig');
    }

    public function reporteDiversaAction(Request $request){

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

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:reporteDiversa.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
        ));



//        $em = $this->getDoctrine()->getManager();
//        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
//        dump($objUeducativa);die;
    }

    public function operativosAction (Request $request)
    {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $arrDataReport = array(
            'roluser' => $this->session->get('roluser'),
            'userId' => $this->session->get('userId'),
            'sie' => $this->session->get('ie_id'),
            'gestion' => $this->session->get('ie_gestion'),
            'subcea' => $this->session->get('ie_subcea'),
            'periodo' => $this->session->get('ie_per_cod'),
            'lugarid'=> $idlugarusuario
        );

        return $this->render($this->session->get('pathSystem') . ':Principal:operativos.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
        ));

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

            $response = new JsonResponse();
            return $response->setData(array('listaprovincias' => $provinciasArray));
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


}
