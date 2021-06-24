<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaAccesoInternet;
use Sie\AppWebBundle\Entity\InstitucioneducativaAccesoInternetDatos;
use Sie\AppWebBundle\Entity\InstitucioneducativaAccesoTvDatos;

/**
 * InstitucioneducativaAccesoInternet Controller
 */
class InstitucioneducativaAccesoInternetController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:index.html.twig', array(
            'form' => $this->formSearch()->createView(),
        ));
    }

    private function formSearch() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('ie_acceso_internet_result'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function resultAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        $gestion = $request->getSession()->get('currentyear');

        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'esactivo' => true));

                if($iai) {
                    $this->get('session')->getFlashBag()->add('newError', 'La Institución Educativa ya realizó el reporte de información.');
                    return $this->redirect($this->generateUrl('ie_acceso_internet_index', array('iaiid' => $iai->getId())));
                }

                return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:result.html.twig', array(
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'form' => $this->formAccesoInternet($institucion->getId(), $gestion)->createView(),
                ));
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra registrada.');
            return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
        }
    }

    private function formAccesoInternet($sie, $gestion) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('ie_acceso_internet_save'))
            ->add('sie', 'hidden', array(
                'data' => $sie
            ))
            ->add('gestion', 'hidden', array(
                'data' => $gestion
            ))
            ->add('tieneInternet', 'choice', array(
                'required' => true,
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('tieneTv', 'choice', array(
                'required' => true,
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('tieneEmergenciaSanitaria', 'choice', array(
                'required' => true,
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('tieneBioseguridad', 'choice', array(
                'required' => true,
                'choices'=>array('1'=>'Si', '0'=>'No'),
                'expanded'=>true,
                'multiple'=>false
            ))
            ->add('proveedor', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => false,
                'class' => 'SieAppWebBundle:AccesoInternetProveedorTipo',
                'property' => 'proveedor',
                'empty_value' => 'Seleccionar...',
                'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width:100%')
            ))
            ->add('canaltv', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => false,
                'class' => 'SieAppWebBundle:AccesoCanaltvTipo',
                'property' => 'canaltv',
                'empty_value' => 'Seleccionar...',
                'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width:100%')
            ))
            ->add('adjuntoEmergenciaSanitaria', 'file', array(
                'attr' => array('title'=>"Adjuntar plan de emergencia sanitaria",'accept'=>"application/pdf,.doc,.docx",
                'class'=>'form-control')
            ))
            ->add('adjuntoBioseguridad', 'file', array(
                'attr' => array('title'=>"Adjuntar protocolo de bioseguridad",'accept'=>"application/pdf,.doc,.docx",
                'class'=>'form-control')
            ))
            ->add('guardar', 'submit', array('label' => 'Guardar'))
            ->getForm();
        return $form;
    }

    public function saveAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');        
        $sie = $form['sie'];
        $gestionid = $form['gestion'];
        $tieneInternet = $form['tieneInternet'];
        $tieneTv = $form['tieneTv'];
        $tieneEmergenciaSanitaria = $form['tieneEmergenciaSanitaria'];
        $tieneBioseguridad = $form['tieneBioseguridad'];

        if($tieneInternet == '1') {
            $internetArray = $form['proveedor'];
        } else {
            $internetArray = [];
        }
        
        if($tieneTv == '1') {
            $tvArray = $form['canaltv'];
        } else {
            $tvArray = [];
        }

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
        $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestionid);

        if($institucion) {
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['sie']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $internetDatos = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternetDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $tvDatos = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoTvDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));

                if($iai) {
                    if($iai->getEsactivo()) {
                        $this->get('session')->getFlashBag()->add('newError', 'La Institución Educativa ya realizó el reporte de información.');
                        return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
                    }
                    $em->remove($iai);
                    $em->flush();
                }

                foreach ($internetDatos as $key => $value) {
                    $em->remove($value);
                    $em->flush();
                }

                foreach ($tvDatos as $key => $value) {
                    $em->remove($value);
                    $em->flush();
                }
                
                $nuevoIAI = new InstitucioneducativaAccesoInternet();
                $nuevoIAI->setInstitucioneducativa($institucion);
                $nuevoIAI->setGestionTipo($gestion);
                $nuevoIAI->setTieneInternet($tieneInternet == '1' ? true : false);
                $nuevoIAI->setTieneTv($tieneTv == '1' ? true : false);
                $nuevoIAI->setTieneEmergenciaSanitaria($tieneEmergenciaSanitaria == '1' ? true : false);
                $nuevoIAI->setTieneBioseguridad($tieneBioseguridad == '1' ? true : false);
                $nuevoIAI->setEsactivo(false);
                $nuevoIAI->setFechaRegistro(new \DateTime('now'));

                if($tieneEmergenciaSanitaria == '1') {
                    $adjuntoEmergenciaSanitaria = $request->files->get('form')['adjuntoEmergenciaSanitaria'];
                    if(!empty($adjuntoEmergenciaSanitaria)){
                        $root_rue_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/cobertura/'.$sie;
                        
                        if (!file_exists($root_rue_path)) {
                            mkdir($root_rue_path, 0777);
                        }
                        $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/cobertura/'.$sie;
                        
                        if (!file_exists($destination_path)) {
                            mkdir($destination_path, 0777);
                        }
                        $archivo = 'pes_'.$form['sie'].date('YmdHis').'.'.$adjuntoEmergenciaSanitaria->getClientOriginalExtension();
                        $adjuntoEmergenciaSanitaria->move($destination_path, $archivo);
                        
                        $nuevoIAI->setPlanEmergenciaSanitaria($archivo);
                    }
                }

                if($tieneBioseguridad == '1') {
                    $adjuntoBioseguridad = $request->files->get('form')['adjuntoBioseguridad'];
                    if(!empty($adjuntoBioseguridad)){
                        $root_rue_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/cobertura/'.$sie;
                        
                        if (!file_exists($root_rue_path)) {
                            mkdir($root_rue_path, 0777);
                        }
                        $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/cobertura/'.$sie;
                        
                        if (!file_exists($destination_path)) {
                            mkdir($destination_path, 0777);
                        }
                        $archivo = 'pb_'.$form['sie'].date('YmdHis').'.'.$adjuntoBioseguridad->getClientOriginalExtension();
                        $adjuntoBioseguridad->move($destination_path, $archivo);
                        
                        $nuevoIAI->setProtocoloBioseguridad($archivo);
                    }
                }

                $em->persist($nuevoIAI);
                $em->flush();                

                foreach ($internetArray as $key => $value) {
                    $proveedor = $em->getRepository('SieAppWebBundle:AccesoInternetProveedorTipo')->findOneById($value);
                    $nuevoIAID = new InstitucioneducativaAccesoInternetDatos();
                    $nuevoIAID->setInstitucioneducativa($institucion);
                    $nuevoIAID->setGestionTipo($gestion);
                    $nuevoIAID->setAccesoInternetProveedorTipo($proveedor);
                    $nuevoIAID->setFechaRegistro(new \DateTime('now'));
                    $em->persist($nuevoIAID);
                    $em->flush();
                }

                foreach ($tvArray as $key => $value) {
                    $canaltv = $em->getRepository('SieAppWebBundle:AccesoCanaltvTipo')->findOneById($value);
                    $nuevoIATD = new InstitucioneducativaAccesoTvDatos();
                    $nuevoIATD->setInstitucioneducativa($institucion);
                    $nuevoIATD->setGestionTipo($gestion);
                    $nuevoIATD->setAccesoCanaltvTipo($canaltv);
                    $nuevoIATD->setFechaRegistro(new \DateTime('now'));
                    $em->persist($nuevoIATD);
                    $em->flush();
                }

                $iai_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $internetDatos_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternetDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
                $tvDatos_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoTvDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));

                $this->get('session')->getFlashBag()->add('newOk', 'Registro realizado satisfactoriamente.');

                return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:saved.html.twig', array(
                    'institucion' => $institucion,
                    'iai' => $iai_fin,
                    'internetDatos' => $internetDatos_fin,
                    'tvDatos' => $tvDatos_fin
                ));
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa.');
                return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
            }
        } else {
            $this->get('session')->getFlashBag()->add('noSearch', 'La unidad educativa no se encuentra registrada.');
            return $this->redirect($this->generateUrl('ie_acceso_internet_index'));
        }
    }

    public function impresionDDJJAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('ddjjIAI');
        $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneById($form['iai']);
        $iai->setEsactivo(true);
        $em->persist($iai);
        $em->flush();

        $this->get('session')->getFlashBag()->add('newOk', 'Registro realizado satisfactoriamente.');
        return $this->redirect($this->generateUrl('ie_acceso_internet_index'));        
    }

    public function seguimientoAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();
        $gestionactual = $this->session->get('currentyear');
        $bundle = $this->session->get('pathSystem');
        $roluser = $this->session->get('roluser');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        switch ($bundle) {
            case 'SieHerramientaBundle':
                $instipoid = 1;
                break;
    
            case 'SieEspecialBundle':
                $instipoid = 4;
                break;
            
            case 'SieHerramientaAlternativaBundle':
                $instipoid = 2;
                break;
            
            default:
                $instipoid = 0;
                break;
        }
        
        switch ($roluser) {
            case '7':
                $where = "ues.id_departamento = '".$lugar->getCodigo()."'";
                break;

            case '8':
                $where = '1 = 1';
                break;

            case '10':
                $where = "ues.cod_distrito = '".$lugar->getCodigo()."'";
                break;

            default:
                $where = '1 = 0';
                break;
        }

        $query = $em->getConnection()->prepare("
            select iai.id iaiId, ues.id_departamento, ues.desc_departamento, ues.cod_distrito, ues.distrito, ues.cod_ue_id, ues.desc_ue, case when iai.esactivo is true then true else false end as esactivo
            from
            (select a.cod_ue_id, a.desc_ue, a.cod_le_id, a.direccion, a.zona, a.cod_distrito, a.distrito,
            (case when a.tipo_area = 'R' then 'RURAL' when a.tipo_area = 'U' then 'URBANO' else '' end) as area,
            a.id_departamento, a.desc_departamento, a.id_provincia, a.desc_provincia, a.id_seccion, a.desc_seccion, a.id_canton, a.desc_canton, a.id_localidad, a.desc_localidad,
            a.cod_convenio_id, a.convenio,case when a.cod_dependencia_id <>3 then 'Publico' else 'Privado' end as dependencia_gen, a.cod_dependencia_id, a.dependencia, c.ini, c.pri, c.sec, c.epa, c.esa, c.eta, c.esp, c.perm,c.perm_tec,c.perm_otros, c.eja, a.fecha_last_update, a.estadoinstitucion_tipo_id, a.estadoinstitucion,a.institucioneducativa_tipo_id,a.cordx,a.cordy, a.nro_resolucion, a.fecha_resolucion
            from 
            (select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.orgcurricular_tipo_id,a.estadoinstitucion_tipo_id, h.estadoinstitucion, a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id,f.orgcurricula
            ,a.dependencia_tipo_id as cod_dependencia_id, e.dependencia,a.convenio_tipo_id as cod_convenio_id,g.convenio,d.cod_dep as id_departamento,d.des_dep as desc_departamento
            ,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
            ,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito,d.direccion,d.zona
            ,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, a.fecha_creacion,d.cordx,d.cordy, a.nro_resolucion, a.fecha_resolucion, a.institucioneducativa_tipo_id
            from institucioneducativa a 
            inner join 
            (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona,a.cordx,cordy
            from jurisdiccion_geografica a inner join 
            (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
            from (select id,codigo as cod_dep,lugar_tipo_id,lugar
            from lugar_tipo
            where lugar_nivel_id=1) a inner join (
            select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
            select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
            select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
            select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
            where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
            inner join 
            (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
            where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le  and institucioneducativa_acreditacion_tipo_id=1 and estadoinstitucion_tipo_id=10
            INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id
            INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id
            INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id
            INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id
            where a.institucioneducativa_acreditacion_tipo_id = 1) as a
            INNER JOIN (
            select institucioneducativa_id
            ,sum(case when nivel_tipo_id=11 then 1 else 0 end) as ini
            ,sum(case when nivel_tipo_id=12 then 1 else 0 end) as pri
            ,sum(case when nivel_tipo_id=13 then 1 else 0 end) as sec
            ,sum(case when nivel_tipo_id=6 then 1 else 0 end) as esp
            ,sum(case when nivel_tipo_id=8 then 1 else 0 end) as eja
            ,sum(case when nivel_tipo_id=201 then 1 else 0 end) as epa
            ,sum(case when nivel_tipo_id=202 then 1 else 0 end) as esa
            ,sum(case when nivel_tipo_id in (203,204,205,206) then 1 else 0 end) as eta
            ,sum(case when nivel_tipo_id in (207) then 1 else 0 end) as alfproy
            ,sum(case when nivel_tipo_id in (208) then 1 else 0 end) as alfcam
            ,sum(case when nivel_tipo_id in (211,212,213,214,215,216,217,218) then 1 else 0 end) as perm
            ,sum(case when nivel_tipo_id in (219,220,224) then 1 else 0 end) as perm_tec
            ,sum(case when nivel_tipo_id in (221,222,223) then 1 else 0 end) as perm_otros
            from institucioneducativa_nivel_autorizado
            group by institucioneducativa_id
            ) as c on a.cod_ue_id=c.institucioneducativa_id 
            inner join dependencia_tipo d on a.cod_dependencia_id=d.id
                inner join orgcurricular_tipo e on e.id=a.cod_org_curr_id
                    inner join convenio_tipo f on f.id=a.cod_convenio_id) ues
            left join institucioneducativa_acceso_internet iai on ues.cod_ue_id = iai.institucioneducativa_id and iai.gestion_tipo_id = ".$gestionactual."
            where ues.estadoinstitucion_tipo_id = 10 and ues.institucioneducativa_tipo_id = ".$instipoid." and ".$where."
            order by ues.id_departamento, ues.cod_distrito, cod_ue_id;
        ");
        
        $query->execute();        
        $seguimientoiai = $query->fetchAll();

        return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:seguimiento.html.twig', array(
            'seguimientoiai' => $seguimientoiai
        ));
    }

    public function restablecerAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();

        $iaiid = $request->get('iaiid');
        $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneById($iaiid);
        $iai->setEsactivo(false);
        $em->persist($iai);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('restablecerOk', 'Se han restablecido los registros satisfactoriamente: '.$iai->getInstitucioneducativa()->getId().' - '.$iai->getInstitucioneducativa());
        return $this->redirect($this->generateUrl('ie_acceso_internet_seguimiento'));
    }

    public function verDatosAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $em = $this->getDoctrine()->getManager();

        $iaiid = $request->get('iaiid');
        $iai = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternet')->findOneById($iaiid);
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($iai->getInstitucioneducativa()->getId());
        $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($iai->getGestionTipo()->getId());
        $internetDatos_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoInternetDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));
        $tvDatos_fin = $em->getRepository('SieAppWebBundle:InstitucioneducativaAccesoTvDatos')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion));

        return $this->render('SieHerramientaBundle:InstitucioneducativaAccesoInternet:datos.html.twig', array(
            'iai' => $iai,
            'internetDatos' => $internetDatos_fin,
            'tvDatos' => $tvDatos_fin,
            'institucion' => $institucion
        ));
    }
}