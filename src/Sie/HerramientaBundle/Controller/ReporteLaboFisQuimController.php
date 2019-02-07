<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Institucioneducativa controller.
 *
 */
class ReporteLaboFisQuimController extends Controller {

    public $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
    * Muestra la lista de Unidades Educativas del nivel secundario según RUE
    */
    public function indexAction(Request $request){
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("
            select
            a.desc_departamento as departamento
            ,a.distrito as distrito
            ,a.cod_ue_id as rue
            ,a.desc_ue as ue
            ,upper(a.dependencia)
            ,case when a.tipo_area = 'R' then 'RURAL' when a.tipo_area = 'U' then 'URBANA' else '' end as area
            ,case when w.institucioneducativa_id is null then 'NO' else 'SI' end as registro_labo
            ,w.id
            from
            (select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.orgcurricular_tipo_id,a.estadoinstitucion_tipo_id, h.estadoinstitucion, a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id,f.orgcurricula
            ,a.dependencia_tipo_id as cod_dependencia_id, e.dependencia,a.convenio_tipo_id as cod_convenio_id,g.convenio,d.cod_dep as id_departamento,d.des_dep as desc_departamento
            ,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
            ,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito,d.direccion,d.zona
            ,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, a.fecha_creacion
            from institucioneducativa a
            inner join
            (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona
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
            where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le
            INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id
            INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id
            INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id
            INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id
            where a.institucioneducativa_acreditacion_tipo_id = 1 and a.estadoinstitucion_tipo_id = 10 and a.dependencia_tipo_id in (1,2,3)) as a
            INNER JOIN (
            select institucioneducativa_id
            ,sum(case when nivel_tipo_id=13 then 1 else 0 end) as sec
            from institucioneducativa_nivel_autorizado
            where nivel_tipo_id = 13
            group by institucioneducativa_id
            ) as c on a.cod_ue_id=c.institucioneducativa_id
            inner join dependencia_tipo d on a.cod_dependencia_id=d.id
                inner join orgcurricular_tipo e on e.id=a.cod_org_curr_id
                    inner join convenio_tipo f on f.id=a.cod_convenio_id
                        left join equip_labo_fisi_quim w on a.cod_ue_id = w.institucioneducativa_id
                            left join equip_labo_fisi_quim_construida_tipo x on w.secciv_construida_tipo_id = x.id
            order by departamento,distrito,rue;
        ");
        $query->execute();
        $registro_labo = $query->fetchAll();

        return $this->render('SieHerramientaBundle:ReporteLaboFisQuim:index.html.twig', array(
            'registro_labo' => $registro_labo
        ));
    }

    public function fotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $query = $em->getConnection()->prepare("
            select f.foto, tf.tipo 
            from equip_labo_fisi_quim_fotos f
            inner join equip_labo_fisi_quim_tipo_foto tf on f.tipo_foto_id = tf.id
            where f.equip_labo_fisi_quim_id = $id ");
        $query->execute();
        $fotos = $query->fetchAll();

        return $this->render('SieHerramientaBundle:ReporteLaboFisQuim:fotos.html.twig', array(
            'fotos'=>$fotos
        ));
    }
}