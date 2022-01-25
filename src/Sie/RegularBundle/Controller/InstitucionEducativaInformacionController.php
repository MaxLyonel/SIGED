<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

/**
 * InstitucionEducativaInformacion controller.
 *
 */
class InstitucionEducativaInformacionController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    /**
     * Formulario de Busqueda InstitucionEducativaInformacion.
     *
     */
    public function indexAction(Request $request) {        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieRegularBundle:InstitucionEducativaInformacion:index.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_ue_informacion_general_busqueda', '', $gestionActual, '', 0)->createView(),
                    'gestion' => $gestionActual->format('Y'),
        ));   
    }
    
    private function creaFormularioBuscador($routing, $value1, $value2, $value3, $identificador) {        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }   
    
    public function buscaUnidadEducativaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');     
        // generar los titulos para los diferentes sistemas

        $tipoSistema = $request->getSession()->get('sysname');

        ////////////////////////////////////////////////////
        $rolUsuario = $this->session->get('roluser');
        
        $sie = 0;
        $ges = 0;
        $identificador = 0;
                
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        if ($form) {
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $identificador = $form['identificador'];           

            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $id_usuario);
            $query->bindValue(':sie', $sie);
            $query->bindValue(':roluser', $rolUsuario);
            $query->execute();
            $aTuicion = $query->fetchAll();

            if (!$aTuicion[0]['get_ue_tuicion']) {
                $this->session->getFlashBag()->set('danger',array('title' => 'Error','message' => 'No tiene tuición sobre la Unidad Educativa'));
                return $this->redirectToRoute('sie_ue_informacion_general');
            }            
                        
            $query = $em->getConnection()->prepare("
                    select a.id as cod_ue_id,a.institucioneducativa as desc_ue,b.sucursal_tipo_id as sub_cea,b.gestion_tipo_id as gestion_id,2 as operativo_id,b.periodo_tipo_id as periodo_id
                    ,b.nombre_subcea,b.telefono1,b.telefono2,b.referencia_telefono2,b.fax,b.email,b.casilla,c.ci_director,c.director, case c.item_director when 1 then 'SI' else 'NO' end as item_director
                    ,b.cod_cerrada_id/*, b.turno_tipo_id as turno_id, iec.turno*/, iec.turno as turno_abrv,current_date as fecha_consolidacion,a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id
                    ,a.dependencia_tipo_id as cod_dependencia_id,a.convenio_tipo_id as cod_convenio_id,d.cod_dep as id_departamento,d.des_dep as desc_departamento
                    ,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
                    ,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,b.zona,b.direccion,d.cod_dis as cod_distrito,d.des_dis as distrito
                    ,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, case b.cod_cerrada_id when 10 then 'NO' else 'SI' end as institucioneducativa_cerrado
                    ,dt.dependencia, case d.desc_area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as desc_area
                    from (
                                    institucioneducativa a 
                                    inner join dependencia_tipo as dt on dt.id = a.dependencia_tipo_id
                                    inner join institucioneducativa_sucursal b on a.id=b.institucioneducativa_id 
                                    --inner join turno_tipo as tt on tt.id = b.turno_tipo_id
                                    inner join (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,desc_area
                                                                                    from jurisdiccion_geografica a 
                                                                                    inner join (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001, e.area2001 as desc_area
                                                                                                                                    from (select id,codigo as cod_dep,lugar_tipo_id,lugar	from lugar_tipo where lugar_nivel_id=1) a 
                                                                                                                                    inner join (select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                                                                                                                                    inner join (select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                                                                                                                                    inner join (select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id
                                                                                                                                    inner join (select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                                                                                                                                    ) b on a.lugar_tipo_id_localidad=b.id
                                                                                                                                    inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                                                                                    ) d on a.le_juridicciongeografica_id=d.cod_le
                                    ) 
                                    left join (select institucioneducativa_id, carnet as ci_director,paterno||' '||materno||' '||nombre as director,cargo_tipo_id as item_director from maestro_inscripcion a 
                                                                                    inner join persona b on a.persona_id=b.id 
                                                                                    where a.gestion_tipo_id=".$ges." and a.institucioneducativa_id=".$sie." and cargo_tipo_id in (1,12) limit 1
                                                                            ) c on a.id=c.institucioneducativa_id
                                    left join (
                                        select string_agg(turno,'-' order by turno_id) as turno, institucioneducativa_id from (
                                            select turno, case turno when 'M' then 1 when 'T' then 2 when 'N' then 3 else 0 end as turno_id, institucioneducativa_id from (
                                            select unnest(string_to_array(string_agg(distinct case tt1.abrv when 'MTN' then 'M-T-N' when 'MN' then 'M-N' else tt1.abrv end,'-'),'-','')) as turno, iec1.institucioneducativa_id from institucioneducativa_curso as iec1 
                                            inner join estudiante_inscripcion as ei1 on ei1.institucioneducativa_curso_id = iec1.id
                                            inner join turno_tipo as tt1 on tt1.id = iec1.turno_tipo_id
                                            where iec1.gestion_tipo_id = ".$ges." and iec1.institucioneducativa_id in (".$sie.")
                                            group by iec1.institucioneducativa_id
                                            ) as v
                                            group by institucioneducativa_id, turno
                                            order by turno_id
                                        ) as vv
                                        group by institucioneducativa_id
                                    ) as iec on iec.institucioneducativa_id = a.id
                    where a.id=".$sie." and b.gestion_tipo_id=".$ges.";
                ");
            
            $query->execute();
            $ueEntity = $query->fetchAll();
            
            $query = $em->getConnection()->prepare("
                    select 
                        SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 1 then 1 else 0 end) as ini1
                        ,SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 2 then 1 else 0 end) as ini2
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 1 then 1 else 0 end) as pri1
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 2 then 1 else 0 end) as pri2
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 3 then 1 else 0 end) as pri3
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 4 then 1 else 0 end) as pri4
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 5 then 1 else 0 end) as pri5
                        ,SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 6 then 1 else 0 end) as pri6
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 1 then 1 else 0 end) as sec1
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 2 then 1 else 0 end) as sec2
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 3 then 1 else 0 end) as sec3
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 4 then 1 else 0 end) as sec4
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 5 then 1 else 0 end) as sec5
                        ,SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 6 then 1 else 0 end) as sec6
                      from (
                      select distinct iec.nivel_tipo_id, iec.grado_tipo_id, iec.paralelo_tipo_id from institucioneducativa_curso as iec 
                      where iec.gestion_tipo_id = ".$ges." and iec.institucioneducativa_id = ".$sie."
                      ) as v
                ");
            $query->execute();
            $ueNivelGradoEntity = $query->fetchAll();
            
            $query = $em->getConnection()->prepare("
                    select 'Aula ' || cast(multigrado as varchar) as multigrado
                        ,case SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '' else 'X' end as ini1
                        ,case SUM(case when nivel_tipo_id = 11 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '' else 'X' end as ini2
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '' else 'X' end as pri1
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '' else 'X' end as pri2
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 3 then 1 else 0 end) when 0 then '' else 'X' end as pri3
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 4 then 1 else 0 end) when 0 then '' else 'X' end as pri4
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 5 then 1 else 0 end) when 0 then '' else 'X' end as pri5
                        ,case SUM(case when nivel_tipo_id = 12 and grado_tipo_id = 6 then 1 else 0 end) when 0 then '' else 'X' end as pri6
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 1 then 1 else 0 end) when 0 then '' else 'X' end as sec1
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 2 then 1 else 0 end) when 0 then '' else 'X' end as sec2
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 3 then 1 else 0 end) when 0 then '' else 'X' end as sec3
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 4 then 1 else 0 end) when 0 then '' else 'X' end as sec4
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 5 then 1 else 0 end) when 0 then '' else 'X' end as sec5
                        ,case SUM(case when nivel_tipo_id = 13 and grado_tipo_id = 6 then 1 else 0 end) when 0 then '' else 'X' end as sec6
                      from (
                      select distinct iec.nivel_tipo_id, iec.grado_tipo_id, iec.multigrado, iec.paralelo_tipo_id from institucioneducativa_curso as iec 
                      where iec.gestion_tipo_id = ".$ges." and iec.institucioneducativa_id = ".$sie." and (multigrado != 0 and multigrado is not null)
                      order by iec.nivel_tipo_id, iec.grado_tipo_id, iec.paralelo_tipo_id, iec.multigrado 
                      ) as v
                      group by multigrado 
                      order by multigrado
                ");
            $query->execute();
            $ueMultigradoEntity = $query->fetchAll();
            
            return $this->render('SieRegularBundle:InstitucionEducativaInformacion:index.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_ue_informacion_general_busqueda', $sie, $ges, '', $identificador)->createView(),
                    'gestion' => $ges,
                    'sie' => $sie,
                    'ue' => $ueEntity,
                    'ueNivelGrado' => $ueNivelGradoEntity,
                    'ueMultigrado' => $ueMultigradoEntity,
            ));                       
            
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_ue_informacion_general');
        }
    }
    
    public function impresionUECaratulaAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');
      
        $sie = $request->get('sie');
        $ges = $request->get('gestion');
        $arch = $sie.'caratula_'.$ges. '_'.date('YmdHis').'.pdf';
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reg_caratula_unidadeducativa_gral_v1_rcm.rptdesign&&cod_ue='.$sie.'&&gestion_id='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }
    
    public function impresionUEInformacionEstadisticaAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');
        
        $sie = $request->get('sie');
        $ges = $request->get('gestion');
        $arch = $sie.'infestaditica_'.$ges. '_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reg_informacionestadistica_unidadeducativa_gral_v1_rcm.rptdesign&&codigo_ue='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }
    
    public function impresionUEOfertaCurricularAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');
        
        $sie = $request->get('sie');
        $ges = $request->get('gestion');
        $arch = $sie.'ofertacurricular_'.$ges. '_'.date('YmdHis').'.pdf';
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reg_ofertacurricular_unidadeducativa_gral_v1_rcm.rptdesign&&codigo_ue='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }
    
    public function impresionUEDocenteAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');
        
        $sie = $request->get('sie');
        $ges = $request->get('gestion');
        $arch = $sie.'docente_'.$ges. '_'.date('YmdHis').'.pdf';
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reg_docente_unidadeducativa_gral_v1_rcm.rptdesign&&codigo_ue='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }
    
    public function impresionUEAdministrativoAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');
        
        $sie = $request->get('sie');
        $ges = $request->get('gestion');
        $arch = $sie.'administrativo_'.$ges. '_'.date('YmdHis').'.pdf';
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reg_administrativo_unidadeducativa_gral_v1_rcm.rptdesign&&codigo_ue='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }
}
