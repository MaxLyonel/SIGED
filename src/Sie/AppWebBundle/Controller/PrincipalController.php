<?php

namespace Sie\AppWebBundle\Controller;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Entity\InstitucioneducativaSucursalModalidadAtencion;
use \Sie\AppWebBundle\Entity\InstitucioneducativaSucursalRiesgoMes;
use \Sie\AppWebBundle\Entity\ValidacionProceso;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\NotaTipo;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;


class PrincipalController extends Controller {

    public $sesion;

    public function __construct() {
        $this->sesion = new Session();
    }

    //creamos pantalla de busqueda
    /**
     * visualizamos informacion sobre el usuario loggeado
     * @param Request $request
     * @return array data user
     */
    public function indexAction(Request $request) {

        
        $user = $this->container->get('security.context')->getToken()->getUser();
        //$this->sesion->set('userId',$user->getId());
        //$this->sesion->set('roluser',8);


        $id_usuario = $this->sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->sesion->get('roluser');

        $userData = $this->userData($id_usuario);

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Notificacion');
        $hoy = new \DateTime('now');

        $query = $repository->createQueryBuilder('n')
                ->select('u')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->where('u.rolTipo = :rol')
                ->andWhere('n.fechaCrea <= :fecha')
                ->andWhere('n.fechaPub >= :fecha')
                ->andWhere('n.estado = :estado')
                ->orderBy('n.fechaPub', 'DESC')
                ->orderBy('n.id', 'DESC')
                ->setParameter('fecha', $hoy->format('Y-m-d'))
                ->setParameter('rol', $rol_usuario)
                ->setParameter('estado', 't')
                ->getQuery();
        
        $entities = $query->getResult();

        //Consulta Mensajes nuevos recibidos

        $emM = $this->getDoctrine()->getManager();

        $repositoryM = $emM->getRepository('SieAppWebBundle:Mensaje');

        $queryM = $repositoryM->createQueryBuilder('m')
                ->select('m.id, m.asunto, m.fecha, m.adjunto1, m.adjunto2, mu.leido, p.paterno, p.materno, p.nombre')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.emisor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.receptor = :receptor')
                ->andWhere('mu.leido = :leido')
                ->orderBy('m.fecha', 'DESC')
                ->orderBy('m.id', 'DESC')
                ->setParameter('receptor', $id_usuario)
                ->setParameter('leido', 'f')
                ->getQuery();

        $queryT = $repositoryM->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.emisor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.receptor = :receptor')
                ->andWhere('mu.leido = :leido')
                ->setParameter('receptor', $id_usuario)
                ->setParameter('leido', 'f')
                ->getQuery();

        $total = $queryT->getSingleScalarResult();

        $mensajesNuevos = $queryM->getResult();

        $this->sesion->set('mensajesNuevos', $mensajesNuevos);
        $this->sesion->set('mensajesNuevosT', $total);

        $emUe = $this->getDoctrine()->getManager();
        $codue = $emUe->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);

        $emF = $this->getDoctrine()->getManager();


        //////TEMPORAL ALTERNATIVA HERRAMIENTA
        //////TEMPORAL ALTERNATIVA HERRAMIENTA
        //////TEMPORAL ALTERNATIVA HERRAMIENTA
        //////EXTRACCION DE NOTIFICACIONES PARA EL USUARIO
        /*$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "  select institucioneducativa_id, institucioneducativa, gestion_tipo_id, sucursal_tipo_id, periodo_tipo_id, g.tramite_estado
                    from institucioneducativa_sucursal a
                    inner join institucioneducativa c on a.institucioneducativa_id = c.id
                    inner join institucioneducativa_sucursal_tramite b on b.institucioneducativa_sucursal_id = a.id
                    inner join tramite_estado g on g.id = b.tramite_estado_id
                    where g.id in ('5','16','17')
                    order by gestion_tipo_id";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $not = $stmt->fetchAll();
        if (!$not) {
            $not = array();
        }

        $departamental = $this->ceasstdcierreDptoAction();
        $nacional = $this->ceasstdcierreNalAction();

        dump($departamental);dump($nacional);die;*/
        
        $em->getConnection()->beginTransaction();
        $em->getConnection()->commit();
            
            $query = $em->getConnection()->prepare('select * from control_instalador ci  where ci.activo = true order by 1 desc ');
            $query->execute();
            $instalador= $query->fetchAll();

        $departamental = array();
        $nacional = array();
        $not = array();
        //////TEMPORAL ALTERNATIVA HERRAMIENTA
        //////TEMPORAL ALTERNATIVA HERRAMIENTA
        //////TEMPORAL ALTERNATIVA HERRAMIENTA

        //get observation data
        /*$sieaux = $sesion->get('ie_id');
        if (isset($sieaux)) {
            $sieaux = -1;
        }*/
        //Lista de observados consolidacion inscripcion
        // if ($rol_usuario == 9){
        //     $query = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->createQueryBuilder('eio')
        //         ->where('eio.gestionTipo = :gestion')
        //         ->andWhere('eio.institucioneducativa = :ie')
        //         ->setParameter('gestion', $this->sesion->get('currentyear'))
        //         ->setParameter('ie', $this->sesion->get('ie_id'))
        //         ->getQuery();
            
        //     $observacion = $query->getResult();
        // }else{
        //     $observacion = array();
        // }
        

        //$objObservactionSie = $em->getRepository('SieAppWebBundle:ValidacionProceso')->getObservationPerSie(array('sie'=> $sieaux, 'gestion'=>2016));

        if($this->sesion->get('pathSystem') == 'SieGisBundle'){
            return $this->redirectToRoute('sie_gis_homepage');
        }

        //         $query = $em->getConnection()->prepare('select lt4.lugar as departamento,initcap(lt5.lugar) as distrito,initcap(lt3.lugar) as provincia,lt2.lugar as municipio,ie.id as sie,ie.institucioneducativa as ue
        //         from institucioneducativa_encuesta iee
        //         inner join institucioneducativa ie on ie.id = iee.institucioneducativa_id
        //         inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
        //         left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
        //         left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
        //         left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
        //         left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
        //         left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
        //         left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito');
        //     $query->execute();
        //     $dataEncuesta= $query->fetchAll();
        // if (count($dataEncuesta) > 0){
        //     $existe = true;
        // }
        // else {
        //     $existe = false;
        // }
         $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
            $query = $em->createQuery(
                'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 8)
                ->setParameter('lt1', $paisNac);
            $dptoNacE = $query->getResult();

            $dptoNacArray = array();
            foreach ($dptoNacE as $value) {
                if( $value->getId()== 11 || $value->getId()==79355  )
                {

                }else {
                    $dptoNacArray[$value->getId()] = $value->getLugar();
                }
            }
        //    dump($dptoNacArray);die;
        //
        $registroInicioDeClases = array();
        if($this->sesion->get('roluser') == 9){
            $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id')));
            //$registroInicioDeClases = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalModalidadAtencion')->findby(array('institucioneducativaSucursal'=>$objIesucursal->getId()));
            $registroInicioDeClases=$this->getInicioClasesInstitucioneducativaSucursal();
        }

        //Obtenemos los datos de la tabla riesgo_unidadeducativa_riesgo
        // $unidadEducativaTipo =  $em->getRepository('SieAppWebBundle:RiesgoUnidadeducativaTipo')->findAll();

        //Aqui obtenemos un historial de los ultimos 3 meses del incio de actividades
        $historialInicioActividadesData=array();
        if ($rol_usuario==9){
            $historialInicioActividadesData=$this->getHistorialInicioDeActividades();
        // dump($historialInicioActividadesData);die;
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
                ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                ->where('inst.id = :idInstitucion')
                ->andWhere('inss.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $this->sesion->get('ie_id'))
                ->setParameter('gestion', $this->sesion->get('currentyear'))
                ->getQuery();

            $ubicacionUe = $query->getResult();

            $ubicacion = (isset($ubicacionUe) && !empty($ubicacionUe) && isset($ubicacionUe[0])) ? $ubicacionUe[0] : '';
                
            $descargaspn = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findBy(array(
                    'institucioneducativa' => $this->sesion->get('ie_id'),
                    'gestionTipoId'  => $this->sesion->get('currentyear'),
                    'institucioneducativaOperativoLogTipo' => 15
            ));
            $descarga = count($descargaspn);
            $verSPN = false;
            $nivelAutorizado = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id'), 'nivelTipo'=>13));

            if (count($nivelAutorizado) > 0){
                $verSPN = true;
            }
            // dump( $ubicacion);
            // dump(count($nivelAutorizado)); die;

            // Obtener la fecha actual
            $fechaActual = new \DateTime();
            $codigoDepartamento = $ubicacion['codigo_departamento'];
            $fechaFormato = $fechaActual->format('d/m/Y');
            $fechaDescargaSPN = '';

            // Array de fechas habilitadas por código de departamento
            $fechasHabilitadas = [
                "7" => "23/07/2024", //Santa Cruz
                "8" => "23/07/2024", //Beni
                "2" => "24/07/2024", //La Paz
                "4" => "24/07/2024", //Oruro
                "3" => "25/07/2024", //Cochabamba
                "6" => "25/07/2024", //Tarija
                "1" => "26/07/2024", //Chuquisaca
                "5" => "26/07/2024", //Potosi                      
                "9" => "26/07/2024"  //Pando
            ];

            // Variable para controlar si la acción está habilitada
            $habilitado = false;
            // dump( $fechasHabilitadas[$codigoDepartamento]);die;

            if (array_key_exists($codigoDepartamento, $fechasHabilitadas)) {
                if ($fechaFormato == $fechasHabilitadas[$codigoDepartamento]) {
                    $habilitado = true;
                }
                if ($fechaFormato == '27/07/2024' || $fechaFormato == '28/07/2024') {
                    $habilitado = true;
                }
            }
            $fechaDescargaSPN = $fechasHabilitadas[$codigoDepartamento];


        } else {
            $verSPN = false;
            $habilitado = false;
            $descarga = 0;
            $fechaDescargaSPN = '';
        }
       
        // $verSPN = false;
        // dump($fechaDescargaSPN);die;

        return $this->render($this->sesion->get('pathSystem') . ':Principal:index.html.twig', array(
          'userData' => $userData,
          'entities' => $entities,
          'notification' => $not,
          'entitiestot' => $nacional,
          'entitiesdpto' => $departamental,
          'form' => $this->searchForm()->createView(),
          'form2' => $this->searchForm2()->createView(),
          'rie' => $this->obtieneDatosPrincipal(), //Datos para la pantalla principal de RIE
          'instalador'=>$instalador,
          'habilitadoSPN'=>$habilitado,
          'verSPN'=>$verSPN,
          'descargasSPN'=>$descarga,
          'fechaDescargaSPN' => $fechaDescargaSPN,
          //'objObservactionSie' => $objObservactionSie
          'formOperativoRude'=> $this->formOperativoRude(json_encode(array('id'=>$this->sesion->get('ie_id'),'gestion'=>$this->sesion->get('currentyear'))),array())->createView(),
        //   'observacion' => $observacion,
        //   'dataEncuesta' => $dataEncuesta,
        //   'existe' => $existe,
        //   'depto' => $dptoNacArray,
          'registroInicioDeClases'=>count($registroInicioDeClases),

        //   'unidadEducativaTipoData'=>$unidadEducativaTipo,

        //   'historialInicioActividadesData'=>$historialInicioActividadesData,
        ));
    }

     // this is fot the RUDE Operativo
    private function formOperativoRude($data,$objTypeOfUE){
        $arrData = json_decode($data,true);

       $form = $this->createFormBuilder()
            ->add('data', 'hidden', array('data'=>$data))
            ->add('downOperativoRude','button',array('label'=>'Generar Archivo RUDE', 'attr'=>array('class'=>'btn btn-inverse btn-warning btn-stroke text-center btn-block', 'onclick'=> 'downOperativoRudeup()') ))
            ->add('gestion', 'hidden', array('data' => $this->sesion->get('currentyear')))
            ;
        $sieValue = ($arrData['id']>0)?$arrData['id']:'';
        $sieType = ($arrData['id']>0)?true:false;
        $form = $form->add('sie', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control','placeholder'=>'Introduzca SIE', 'value'=>$sieValue, 'readonly'=>$sieType)));
        $form = $form->getForm();    
        return $form;

    }

    /**
     * Crea un formulario para buscar una persona por C.I.
     *
     */
    private function searchForm() {
        $em = $this->getDoctrine()->getManager();

        $departamentos = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
        $arrayDepartamentos = array();

        $cont = 0;
        foreach ($departamentos as $key => $value) {
            if($cont > 0){
                $arrayDepartamentos[$value->getId()] = $value;
            }
            $cont++;
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_estudiante_bjp_verifica_reporte_dpto'))
                ->add('dpto', 'choice', array('choices' => $arrayDepartamentos, 'label' => 'Departamento:', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('getReporte', 'submit', array('label' => 'Generar reporte', 'attr' => array('class' => 'form-control btn btn-md btn-primary')))
                ->getForm();
        return $form;
    }
     private function searchForm2() {
        $em = $this->getDoctrine()->getManager();

        $departamentos = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
        $arrayDepartamentos = array();

        $cont = 0;
        foreach ($departamentos as $key => $value) {
            if($cont > 0){
                $arrayDepartamentos[$value->getId()] = $value;
            }
            $cont++;
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_encuesta_internet'))
                ->add('dpto', 'choice', array('choices' => $arrayDepartamentos, 'label' => 'Departamento:', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('getReporte', 'submit', array('label' => 'Reporte Encuesta', 'attr' => array('class' => 'form-control btn btn-md btn-primary')))
                ->getForm();
        return $form;
    }


    /**
     * obtenemos informacion del usuario
     * @param type $user
     * @return object data user
     */
    private function userData($user) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Persona');
        $query = $entities->createQueryBuilder('p')
                ->select('p.id as personaId, p.carnet, p.paterno,p.materno,p.nombre,p.foto,u.id as userId ,u.username,ur.id as userRolId,ur.esactivo as userRolActivo, rt.id as rolTipoId, rt.rol ')
                ->Join('SieAppWebBundle:Usuario', 'u', 'WITH', 'u.persona = p.id')
                ->Join('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'ur.usuario = u.id')
                ->Join('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'rt.id = ur.rolTipo')
                ->where('u.persona = :user')
                ->setParameter('user', $user)
                ->getQuery();
        try {
            return $query->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /*public function ceasstdcierreDptoAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "select *
                    from (
                    select dd.lugar, count(*) as canttecho
                    from (
                        select k.lugar, d.id as ieue, d.institucioneducativa
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
                                                        inner join institucioneducativa_tipo z on z.id = a.institucioneducativa_tipo_id
                                                        inner join bonojuancito_institucioneducativa jp on jp.institucioneducativa_id = a.id
                                                    where z.id = 4
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
                                                inner join institucioneducativa_sucursal_especial_cierre w on w.institucioneducativa_sucursal_id = z.id
                                                inner join bonojuancito_institucioneducativa jp on jp.institucioneducativa_id = ie.id
                                                inner join lugar_tipo k on k.id = lt.lugar_tipo_id

                                        group by k.lugar, ie.id, ie.institucioneducativa
                                        order by k.lugar, ie.id) dd
                                      group by dd.lugar ) b on a.lugar=b.lugar";
        $porcentajes = $db->prepare($query);
        $params = array();
        $porcentajes->execute($params);
        $porcentajesDpto = $porcentajes->fetchAll();

        return $porcentajesDpto;
    }

    public function ceasstdcierreNalAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

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
                                                            inner join institucioneducativa_tipo z on z.id = a.institucioneducativa_tipo_id
                                                            inner join bonojuancito_institucioneducativa jp on jp.institucioneducativa_id = a.id
                                                    where z.id = 4
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
                                                    inner join institucioneducativa_sucursal_especial_cierre w on w.institucioneducativa_sucursal_id = z.id
                                                    inner join bonojuancito_institucioneducativa jp on jp.institucioneducativa_id = ie.id
                                                    inner join lugar_tipo k on k.id = lt.lugar_tipo_id

                                            group by k.lugar, ie.id, ie.institucioneducativa
                                            order by k.lugar, ie.id) dd
                                          group by dd.lugar ) b on a.lugar=b.lugar
                               ) ff";
        $totales = $db->prepare($querytot);
        $params = array();
        $totales->execute($params);
        $porcentajesNal = $totales->fetchAll();

        return $porcentajesNal;
    }*/

    /***
     * Obtiene datos para página principal de RIE
     */
     public function obtieneDatosPrincipal(){
        $nacional = array(); 
        $nal_fiscal  = 0; $nal_convenio= 0; $nal_privado = 0;

        $nacional['BNI']['fiscal']   = $this->nroinstitutosDepartamento(9, 1);
        $nacional['BNI']['convenio'] = $this->nroinstitutosDepartamento(9, 2);
        $nacional['BNI']['privado']  = $this->nroinstitutosDepartamento(9, 3);
        $nal_fiscal  = $nal_fiscal  + $nacional['BNI']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['BNI']['convenio'];
        $nal_privado = $nal_privado + $nacional['BNI']['privado'];

        $nacional['CHQ']['fiscal']   = $this->nroinstitutosDepartamento(2, 1);
        $nacional['CHQ']['convenio'] = $this->nroinstitutosDepartamento(2, 2);
        $nacional['CHQ']['privado']  = $this->nroinstitutosDepartamento(2, 3);        
        $nal_fiscal  = $nal_fiscal  + $nacional['CHQ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['CHQ']['convenio'];
        $nal_privado = $nal_privado + $nacional['CHQ']['privado'];        

        $nacional['CBA']['fiscal']   = $this->nroinstitutosDepartamento(4, 1);
        $nacional['CBA']['convenio'] = $this->nroinstitutosDepartamento(4, 2);
        $nacional['CBA']['privado']  = $this->nroinstitutosDepartamento(4, 3);   
        $nal_fiscal  = $nal_fiscal  + $nacional['CBA']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['CBA']['convenio'];
        $nal_privado = $nal_privado + $nacional['CBA']['privado'];           

        $nacional['LPZ']['fiscal']  = $this->nroinstitutosDepartamento(3, 1);
        $nacional['LPZ']['convenio'] = $this->nroinstitutosDepartamento(3, 2);
        $nacional['LPZ']['privado']  = $this->nroinstitutosDepartamento(3, 3); 
        $nal_fiscal  = $nal_fiscal  + $nacional['LPZ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['LPZ']['convenio'];
        $nal_privado = $nal_privado + $nacional['LPZ']['privado'];           
        
        $nacional['ORU']['fiscal']   = $this->nroinstitutosDepartamento(5, 1);
        $nacional['ORU']['convenio'] = $this->nroinstitutosDepartamento(5, 2);
        $nacional['ORU']['privado']  = $this->nroinstitutosDepartamento(5, 3);   
        $nal_fiscal  = $nal_fiscal  + $nacional['ORU']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['ORU']['convenio'];
        $nal_privado = $nal_privado + $nacional['ORU']['privado'];  

        $nacional['PND']['fiscal']   = $this->nroinstitutosDepartamento(10, 1);
        $nacional['PND']['convenio'] = $this->nroinstitutosDepartamento(10, 2);
        $nacional['PND']['privado']  = $this->nroinstitutosDepartamento(10, 3);  
        $nal_fiscal  = $nal_fiscal  + $nacional['PND']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['PND']['convenio'];
        $nal_privado = $nal_privado + $nacional['PND']['privado'];  
        
        $nacional['PSI']['fiscal']   = $this->nroinstitutosDepartamento(6, 1);
        $nacional['PSI']['convenio'] = $this->nroinstitutosDepartamento(6, 2);
        $nacional['PSI']['privado']  = $this->nroinstitutosDepartamento(6, 3);  
        $nal_fiscal  = $nal_fiscal  + $nacional['PSI']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['PSI']['convenio'];
        $nal_privado = $nal_privado + $nacional['PSI']['privado'];                   
        
        $nacional['SCZ']['fiscal']   = $this->nroinstitutosDepartamento(8, 1);
        $nacional['SCZ']['convenio'] = $this->nroinstitutosDepartamento(8, 2);
        $nacional['SCZ']['privado']  = $this->nroinstitutosDepartamento(8, 3);
        $nal_fiscal  = $nal_fiscal  + $nacional['SCZ']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['SCZ']['convenio'];
        $nal_privado = $nal_privado + $nacional['SCZ']['privado'];            

        $nacional['TJA']['fiscal']  = $this->nroinstitutosDepartamento(7, 1);
        $nacional['TJA']['convenio'] = $this->nroinstitutosDepartamento(7, 2);
        $nacional['TJA']['privado']  = $this->nroinstitutosDepartamento(7, 3); 
        $nal_fiscal  = $nal_fiscal  + $nacional['TJA']['fiscal'];
        $nal_convenio= $nal_convenio + $nacional['TJA']['convenio'];
        $nal_privado = $nal_privado + $nacional['TJA']['privado'];            

        $nacional['NAL']['fiscal']   = $nal_fiscal; 
        $nacional['NAL']['convenio'] = $nal_convenio;
        $nacional['NAL']['privado']  = $nal_privado;

        return $nacional;
    }

    /***
     * Obtiene datos de Cantidad de Institutos por Departamento
     */
    public function nroinstitutosDepartamento($departamento, $dependencia){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();    
        
        //por bloqueo de base de datos
        //12/12/2023
        
        // $query = 'SELECT
        //     cast(0 as int) AS cantidad';

        /*$query = 'SELECT
                    COUNT(inst.id) AS cantidad 
                    FROM
                    public.institucioneducativa AS inst
                    INNER JOIN public.institucioneducativa_tipo AS instipo ON inst.institucioneducativa_tipo_id = instipo.id
                    INNER JOIN public.estadoinstitucion_tipo AS estinst ON inst.estadoinstitucion_tipo_id = estinst.id
                    INNER JOIN public.jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
                    LEFT JOIN public.lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
                    LEFT JOIN public.lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                    LEFT JOIN public.lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                    INNER JOIN public.dependencia_tipo AS dept ON inst.dependencia_tipo_id = dept.id
                    WHERE lt4.id = '.$departamento.' AND inst.institucioneducativa_tipo_id IN (7,8,9) 
                    AND estinst.id IN (10) 
                    AND inst.institucioneducativa_acreditacion_tipo_id = 2
                    AND dept.id = '.$dependencia;*/
        // $stmt = $db->prepare($query);
        /*$params = array();
        $stmt->execute($params);*/
        // $po=$stmt->fetchAll();
        // return $po[0]['cantidad'];
        return 0;                  
    }



    public function actualizarTipoModalidadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $existe=true;
        //get the sucursal info
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array( 'institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));
        //guardado de datos del no incio de clases

        //se añadio esta parte de codigo para corregir el error de que UE no tenian regsitro en la tabla sucursal con la gestion 2021 (13/05/2021)
        if(!$sucursal) 
        {
            $query = $em->getConnection()->prepare("select * from sp_genera_institucioneducativa_sucursal('".$this->sesion->get('ie_id')."','0','".$this->sesion->get('currentyear')."','1');")->execute();
        }
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array( 'institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));

        try
        {
            if($objIesucursal)
            {
                //verificamos si ya registro este mes
                $institucioneducativaSucursalRiesgoMes=$em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalRiesgoMes')->findOneBy(
                    array('institucioneducativaSucursal'=>$objIesucursal->getId(),
                          'mes'=>date('m'),
                          'semana' =>date('W')
                    )
                );
                if($institucioneducativaSucursalRiesgoMes==null)
                {
                    $institucioneducativaSucursalRiesgoMes=new InstitucioneducativaSucursalRiesgoMes();
                    $existe=false;
                }

                $inicio_clases=filter_var($request->get('inicio_clases'),FILTER_SANITIZE_NUMBER_INT);
                $noInicioRazon=filter_var($request->get('no_inicio_razon'),FILTER_SANITIZE_NUMBER_INT);
                $noInicioRazonOtros=filter_var($request->get('no_inicio_razon_otros'),FILTER_SANITIZE_STRING);

                if(strlen($noInicioRazonOtros)>250)
                    $noInicioRazonOtros= substr($noInicioRazonOtros, 0, 249);

                if($inicio_clases==1)
                {
                    $riesgoUnidadeducativaTipo=$em->getRepository('SieAppWebBundle:RiesgoUnidadeducativaTipo')->find(0);//inicio clases
                    $noInicioRazonOtros='';
                }
               else
                {
                    $riesgoUnidadeducativaTipo=$em->getRepository('SieAppWebBundle:RiesgoUnidadeducativaTipo')->find($noInicioRazon); //no inicio clases
                }
                $institucioneducativaSucursalRiesgoMes->setMes(date('m'));
                //$institucioneducativaSucursalRiesgoMes->setFechaInicio();
                //$institucioneducativaSucursalRiesgoMes->setFechaFin();
                if($existe)
                    $institucioneducativaSucursalRiesgoMes->setFechaModificacion(new \DateTime());
                else
                    $institucioneducativaSucursalRiesgoMes->setFechaRegistro(new \DateTime());
                //$institucioneducativaSucursalRiesgoMes->setOtros();
                $institucioneducativaSucursalRiesgoMes->setObservacion($noInicioRazonOtros);
                $institucioneducativaSucursalRiesgoMes->setRiesgoUnidadeducativaTipo($riesgoUnidadeducativaTipo);
                $institucioneducativaSucursalRiesgoMes->setInstitucioneducativaSucursal($objIesucursal);

                $institucioneducativaSucursalRiesgoMes->setSemana(date('W'));

                $em->persist($institucioneducativaSucursalRiesgoMes);
                $em->flush();
            }
        }
        catch (Exception $e)
        {
            echo 'this the error'.$e;
        }
        return $this->getInicioClasesAction();
    }

    public function getTipoModalidadAction()
    {
        $em = $this->getDoctrine()->getManager();
        //$objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id')));
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));

        $objInstitucioneducativaSucursalModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalModalidadAtencion')->findby(array('institucioneducativaSucursal'=>$objIesucursal->getId()));
        $datos=$items=array();
        foreach ($objInstitucioneducativaSucursalModalidadAtencion as  $value) 
        {
            $datos[] =($value->getModalidadAtencionTipo()->getModalidadAtencion());
            $items[] =($value->getModalidadAtencionTipo()->getId());
        }

        $response = new Response(json_encode(array('datos' => $datos,'items' => $items)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function getInicioClasesAction()
    {
        $datos=NULL;
        $em = $this->getDoctrine()->getManager();
        //$objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id')));
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array( 'institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));

        $detallesInicio=$this->getInicioClasesInstitucioneducativaSucursal();
        $riesgo=NULL;
        if($detallesInicio && $objIesucursal)
        {
            $datos=array(
                'mes'=>$detallesInicio->getMes(),
                'observacion'=>$detallesInicio->getObservacion(),
                //'riesgoUnidadeducativaTipo'=>$detallesInicio->getRiesgoUnidadeducativaTipo(),
            );
            $riesgoTmp=$detallesInicio->getRiesgoUnidadeducativaTipo();
            if($riesgoTmp && $riesgoTmp->getId()>0)
            {
                $riesgo = $em->getRepository('SieAppWebBundle:RiesgoUnidadeducativaTipo')->find($detallesInicio->getRiesgoUnidadeducativaTipo());
                $riesgo=$riesgo->getRiesgoUnidadeducativa();
            }
        }
        $response = new Response(json_encode(array('detallesInicio' => ($datos),'riesgo'=>$riesgo)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    private function getInicioClasesInstitucioneducativaSucursal()
    {
        $detallesInicioDeClases=array();
        $em = $this->getDoctrine()->getManager();
        //$objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id')));
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));

        if($objIesucursal)
        {
            $detallesInicioDeClases = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalRiesgoMes')->findOneBy(
                array('institucioneducativaSucursal'=>$objIesucursal->getId(),'mes'=>date('m')
            ));
        }

        return $detallesInicioDeClases;
    }

    private function getHistorialInicioDeActividades($limit=3)
    {
        $historialInicioActividadesData=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //$objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id')));
        $objIesucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$this->sesion->get('ie_id'),'gestionTipo'=> $this->sesion->get('currentyear') ));
        $sucursalId=-1;
        if($objIesucursal)
        {
            $sucursalId=$objIesucursal->getId();
        }

        $query = '
select 
ie_srm.id,
ie_srm.institucioneducativa_sucursal_id,
ie_srm.mes,
CASE 
      WHEN ie_srm.mes=1  THEN \'Enero\'
      WHEN ie_srm.mes=2  THEN \'Febrero\'
      WHEN ie_srm.mes=3  THEN \'Marzo\'
      WHEN ie_srm.mes=4  THEN \'Abril\'
      WHEN ie_srm.mes=5  THEN \'Mayo\'
      WHEN ie_srm.mes=6  THEN \'Junio\'
      WHEN ie_srm.mes=7  THEN \'Julio\'
      WHEN ie_srm.mes=8  THEN \'Agosto\'
      WHEN ie_srm.mes=9  THEN \'Septiembre\'
      WHEN ie_srm.mes=10  THEN \'Octubre\'
      WHEN ie_srm.mes=11  THEN \'Noviembre\'
      WHEN ie_srm.mes=12  THEN \'Diciembre\'
END as nombre_mes,
coalesce(ie_srm.riesgo_unidadeducativa_tipo_id,-1) AS riesgo_unidadeducativa_tipo_id,
ie_srm.fecha_inicio,
ie_srm.fecha_fin,
ie_srm.fecha_registro,
ie_srm.fecha_modificacion,
ie_srm.otros,
ie_srm.observacion,
r_udt.id as riesgo_id,
r_udt.riesgo_unidadeducativa,
r_udt.obs
from
Institucioneducativa_Sucursal_Riesgo_mes ie_srm 
left JOIN riesgo_unidadeducativa_tipo r_udt on ie_srm.riesgo_unidadeducativa_tipo_id =r_udt.id
where institucioneducativa_sucursal_id =?
--ORDER BY mes DESC
ORDER BY mes DESC,semana ASC
LIMIT ?';
        $stmt = $db->prepare($query);
        $params = array($sucursalId,$limit);
        $stmt->execute($params);
        $historialInicioActividadesData=$stmt->fetchAll();

        return $historialInicioActividadesData;
    }


    public function mostrarResultadosReporteModalidadAtencionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $departamento=filter_var($request->get('departamento'),FILTER_SANITIZE_NUMBER_INT);
        $distrito=filter_var($request->get('distrito'),FILTER_SANITIZE_NUMBER_INT);
        $distritoTmp=($distrito==-1)?'':$distrito;
        $gestion=filter_var($request->get('gestion'),FILTER_SANITIZE_NUMBER_INT);
        $mes=filter_var($request->get('mes'),FILTER_SANITIZE_NUMBER_INT);

        switch ($this->sesion->get('pathSystem')) {
            case 'SieHerramientaBundle':
                # code...
                $typeSystem = 1;
                break;
            case 'SieHerramientaAlternativaBundle':
                # code...
                $typeSystem = 2;
                break;
            case 'SieEspecialBundle':
                # code...
                $typeSystem = 3;
                break;
            case 'SiePermanenteBundle':
                # code...
                $typeSystem = 4;
                break;
            
            default:
                # code...
                $typeSystem = 0;
                break;
        }

        $query = 'select * from sp_genera_reporte_modalidad_atencion_niv(?,?,?,?,?);';
        $plantillaReporte = 'reporte_modalidad_atencion.html.twig';
        if($gestion == 2021)
        {
            if($mes<=6)
            {
                $plantillaReporte = 'reporte_modalidad_atencion_mensual.html.twig';
                $query = 'select * from sp_genera_reporte_modalidad_atencion_mensual(?,?,?,?);';
            }
        }

        $stmt = $db->prepare($query);
        $params = array($gestion,$departamento,$distritoTmp,$mes,$typeSystem);
        $stmt->execute($params);
        $datosReporte=$stmt->fetchAll();

        //return $this->render($this->sesion->get('pathSystem') . ':Principal:reporte_modalidad_atencion.html.twig', array
        return $this->render($this->sesion->get('pathSystem') . ':Principal:'.$plantillaReporte, array
        (
        'datosReporte' => $datosReporte,
        'mes' =>$mes,
        'gestion'=>$gestion,
        'departamento'=>$departamento,
        'distrito'=>$distrito,
        ));
    }

    public function mostrarResultadosReporteModalidadAtencionExcelAction($gestionInput,$departamentoInput,$distritoInput,$mesInput)
    {
        date_default_timezone_set('America/La_Paz');
        $gestion        = $gestionInput;
        $departamento   = $departamentoInput;
        $distrito       = $distritoInput;
        $distritoTmp    = ($distrito==-1)?'':$distrito;
        $mes            = $mesInput;

        $meses=array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');

        $arch = 'Reporte-Modalidades-de-Atención-'.$meses[$mes-1].'-'.date('Y').'_'.date('YmdHis').'.xlsx';
        $response = new Response();
        $response->headers->set('Content-type', 'application/xlsx');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        //$excel = file_get_contents('http://127.0.0.1:63020/viewer/preview?__report=D%3A\workspaces\workspace_especial\Reporte-modalidades-atencion\Reporte-modalidades_v1.rptdesign&__format=xlsx'.'&gestion='.$gestion.'&departamento='.$departamento.'&distrito='.$distritoTmp.'&mes='.$mes);
        $excel = file_get_contents($this->container->getParameter('urlreportweb') . 'Reporte-modalidades_v1.rptdesign&__format=xlsx&gestion='.$gestion.'&departamento='.$departamento.'&distrito='.$distritoTmp.'&mes='.$mes);
        if($gestion == 2021)
        {
            if($mes<=6)
            {
                //$excel = file_get_contents('http://127.0.0.1:63020/viewer/preview?__report=D%3A\workspaces\workspace_especial\Reporte-modalidades-atencion\Reporte-modalidades-mensual_v1.rptdesign&__format=xlsx'.'&gestion='.$gestion.'&departamento='.$departamento.'&distrito='.$distritoTmp.'&mes='.$mes);
                $excel = file_get_contents($this->container->getParameter('urlreportweb') . 'Reporte-modalidades-mensual_v1.rptdesign&__format=xlsx&gestion='.$gestion.'&departamento='.$departamento.'&distrito='.$distritoTmp.'&mes='.$mes);
            }
        }
        $response->setContent($excel);
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function getDistritosAction(Request $request)
    {
        $departamento=filter_var($request->get('departamento'),FILTER_SANITIZE_NUMBER_INT);
        $distritos_array=array();
        $em = $this->getDoctrine()->getManager();
        $distritos = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$departamento));
        
        foreach ($distritos as $d)
        {
            $distritos_array[]=array('id' =>$d->getId(),'distrito'=>$d->getDistrito());
        }
        $response = new Response(json_encode($distritos_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function descargarSPNAction(Request $request)
    {
        // $departamentoId = $request->get('departamentoId');
        // return $this->redirect($this->generateUrl('login'));
        $archivo = '/siged/web/uploads/instaladores/Sistema_de_Toma_de_Prueba.zip';
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();  
        $institucion = $this->sesion->get('ie_id');
        $gestion = $this->sesion->get('currentyear');
        
        $descargaspn = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findBy(array(
              'institucioneducativa' =>$institucion,
              'gestionTipoId'  => $gestion,
              'institucioneducativaOperativoLogTipo' => 15
            ));
            if (empty($descargaspn) || count($descargaspn) < 3) {
                
                $em->getConnection()->beginTransaction();
                try {
                
                    $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
                    $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(15));
                    $institucioneducativaOperativoLog->setGestionTipoId($gestion);
                    $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                    $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion));
                    $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
                    $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                    $institucioneducativaOperativoLog->setDescripcion('Olimpiada SPN');
                    $institucioneducativaOperativoLog->setEsexitoso('t');
                    $institucioneducativaOperativoLog->setEsonline('t');
                    $institucioneducativaOperativoLog->setUsuario($this->sesion->get('userId'));
                    $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
                    $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
                    $em->persist($institucioneducativaOperativoLog);
                    $em->flush();
                    $em->getConnection()->commit();

                    $response = new JsonResponse();
                    $response->setData([
                        'mensaje' => 'descargado',
                        'archivo' => $archivo,
                        'conteo' => count($descargaspn)
                    ]);
   
    
    
    
                } catch (\Exception $e) {
                    $em->getConnection()->rollBack();
                    $response->setData(['mensaje' => $e
                        ,'conteo' => count($descargaspn)
                    ]);
                }
            } else {
                $response->setData(['mensaje' => 'error',
                    'conteo' => count($descargaspn)
                ]);
            }
        return $response;
    }
}
