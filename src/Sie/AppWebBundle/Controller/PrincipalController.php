<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Entity\ValidacionProceso;
use \Sie\AppWebBundle\Form\UsuarioType;
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
        $faea = $emF->getRepository('SieAppWebBundle:Faea2014')->find($codue->getUsername());


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

        //$objObservactionSie = $em->getRepository('SieAppWebBundle:ValidacionProceso')->getObservationPerSie(array('sie'=> $sieaux, 'gestion'=>2016));

        if($this->sesion->get('pathSystem') == 'SieGisBundle'){
            return $this->redirectToRoute('sie_gis_homepage');
        }

        $gestionactual = $this->sesion->get('currentyear');
        $roluser = $this->sesion->get('roluser');
        $roluserlugarid = $this->sesion->get('roluserlugarid');
        $bundle = $this->sesion->get('pathSystem');

        switch ($bundle) {
            case 'SieRegularBundle':
            case 'SieHerramientaBundle':
                $instipoid = 1;
                $mingestion = 2014;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;

            case 'SieEspecialBundle':
                $instipoid = 4;
                $mingestion = 2013;
                $title = 'Reporte de cierre de operativos por gestión y Centro de Educación Especial';
                $label = 'Cantidad de Centros que reportaron matrícula';
                $label_distrito = 'Cantidad de Centros que reportaron matrícula en el distrito';
                break;
            
            default:
                $instipoid = 1;
                $mingestion = 2014;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;
        }
        
        $consol = $this->get('sie_app_web.funciones')->reporteConsol($gestionactual, $roluser, $roluserlugarid, $instipoid);

        switch ($roluser) {
            case 8:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolNal($gestionactual, $instipoid);
                break;

            case 7:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDptal($gestionactual, $roluserlugarid, $instipoid);
                break;

            case 10:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDtal($gestionactual, $roluserlugarid, $instipoid);
                break;

            default:
                $ues = null;
                break;
        }

        $gestiones = $em->getRepository('SieAppWebBundle:GestionTipo')->findBy(array(), array('id' => 'DESC'));

        $gestionesArray = array();
        
        foreach ($gestiones as $value) {
            if ($value->getId() >= $mingestion) {
                $gestionesArray[$value->getId()] = $value->getGestion();
            }
        }

        return $this->render($this->sesion->get('pathSystem') . ':Principal:index.html.twig', array(
          'userData' => $userData,
          'entities' => $entities,
          'faea' => $faea,
          'consol' => $consol,
          'ues' => $ues,
          'gestiones' => $gestionesArray,
          'gestionactual' => $gestionactual,
          'title' => $title,
          'label' => $label,
          'label_distrito' => $label_distrito,
          'notification' => $not,
          'entitiestot' => $nacional,
          'entitiesdpto' => $departamental,
          'form' => $this->searchForm()->createView(),
          'rie' => $this->obtieneDatosPrincipal(), //Datos para la pantalla principal de RIE
          //'objObservactionSie' => $objObservactionSie
        ));
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
        $query = 'SELECT
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
                    AND dept.id = '.$dependencia;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po=$stmt->fetchAll();
        return $po[0]['cantidad'];                  
    }

}
