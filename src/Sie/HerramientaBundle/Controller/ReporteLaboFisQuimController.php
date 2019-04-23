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
    public function indexAction(Request $request) {
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
            ,cod_le_id
            ,upper(a.dependencia) as dependencia
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
            where a.institucioneducativa_acreditacion_tipo_id = 1 and a.estadoinstitucion_tipo_id = 10 and a.dependencia_tipo_id in (1,2)) as a
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

    public function fotosAction(Request $request) {
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

    public function reporteAction(Request $request) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'REPORTE_LAB_FIS_QUIM.xls'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_rep_labo_fis_quim_afv_v1.rptdesign&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    public function detalleAction(Request $request) {
        // dump($request);die;
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = (int)$fechaActual->format('Y');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $sie = $request->get('sie');

        do {
            $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,$gestionActual);
            if (count($entityIntitucionEducativaGestion)>0){
                $gestionActual = $gestionActual;
            } else {
                $gestionActual = $gestionActual-1;
            }            
        } while (count($entityIntitucionEducativaGestion)==0 and $gestionActual > 2009);

        // $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,$gestionActual);
        // if (!$entityIntitucionEducativaGestion){
        //     $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,($gestionActual-1));
        //     $gestionActual = $gestionActual-1;
        // } 

        if (!$entityIntitucionEducativaGestion) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' no cuenta con registros desde la gestión 2010 en adelante'));
            // return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $entityInstitucionEducativaSecundariaGrados = $this->getInstitucionEducativaSecundariaGrados($sie,$gestionActual);

        $entityInstitucionEducativaEdificio = $this->getInstitucionEducativaEdificio($sie,$gestionActual);

        $entityInstitucionEducativaEquipoLaboratorio = $this->getInstitucionEducativaEquipoLaboratorio($sie);
        //dump($entityInstitucionEducativaEquipoLaboratorio);die;
        if ($entityInstitucionEducativaEquipoLaboratorio) {
            $this->session->getFlashBag()->set('warning', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' ya registro su Fomulario para el Equipamiento de Laboratorio en fecha: '.$entityInstitucionEducativaEquipoLaboratorio[0]['fecha_modificacion']));

            return $this->render($this->session->get('pathSystem') . ':ReporteLaboFisQuim:equipamientoLaboratorioVista.html.twig', array(
                'entity' => $entityIntitucionEducativaGestion[0]
                , 'entityGrado' => $entityInstitucionEducativaSecundariaGrados
                , 'entityEdificio' => $entityInstitucionEducativaEdificio
                , 'entityEquipoLaboratorio' => $entityInstitucionEducativaEquipoLaboratorio[0]
                ,'formPdf' => $this->creaFormularioVistaPdf($sie,$gestionActual)->createView()
                // , 'eliminarregistro' => $this->eliminarRegistro($sie,$gestionActual)->createView()
            ));
        }
    }

    private function getInstitucionEducativaGestion($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie." and gestion_tipo_id = ".$gestion."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaSecundariaGrados($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select institucioneducativa_id, gestion_tipo_id
            , sum(case grado_tipo_id when 1 then cantidad else 0 end) as primero
            , sum(case grado_tipo_id when 2 then cantidad else 0 end) as segundo
            , sum(case grado_tipo_id when 3 then cantidad else 0 end) as tercero
            , sum(case grado_tipo_id when 4 then cantidad else 0 end) as cuarto
            , sum(case grado_tipo_id when 5 then cantidad else 0 end) as quinto
            , sum(case grado_tipo_id when 6 then cantidad else 0 end) as sexto
            , sum(cantidad) as matricula
            from (
            SELECT iec1.grado_tipo_id AS grado_tipo_id, iec1.institucioneducativa_id, iec1.gestion_tipo_id, count(ei1.id) as cantidad
            FROM (institucioneducativa_curso iec1
            JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
            WHERE (-- (iec1.gestion_tipo_id IN ( date_part('year', current_date) )) AND 
            (iec1.nivel_tipo_id = ANY (ARRAY[13, 3])) and iec1.institucioneducativa_id = ".$sie." and iec1.gestion_tipo_id = ".$gestion." and ei1.estadomatricula_tipo_id in (4,5,10,11,55))
            GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id, grado_tipo_id 
            ) as v
            GROUP BY institucioneducativa_id, gestion_tipo_id        
        ");
        $query->execute();
        $objeto = $query->fetchAll();

        if($objeto){
            $objeto = $objeto[0];
        }
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaEdificio($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select turno_id, replace(replace(replace(replace(turno, 'M'::text, 'Mañana'::text), 'T'::text, 'Tarde'::text), 'N'::text, 'Noche'::text), '-'::text, ', '::text) AS turno,
            institucioneducativa_id, institucioneducativa, gestion_tipo_id from (
                SELECT string_agg(((vv.turno_id)::character varying)::text, '-'::text ORDER BY vv.turno_id) AS turno_id,
                string_agg(vv.turno, '-'::text ORDER BY vv.turno_id) AS turno,
                ie.id as institucioneducativa_id, ie.institucioneducativa, vv.gestion_tipo_id
                FROM ( 
                    SELECT v.turno, CASE v.turno WHEN 'M'::text THEN 1 WHEN 'T'::text THEN 2 WHEN 'N'::text THEN 3 ELSE 0 END AS turno_id, v.institucioneducativa_id, v.gestion_tipo_id
                    FROM ( 
                        SELECT unnest(string_to_array(string_agg(DISTINCT (
                            CASE tt1.abrv
                                WHEN 'MTN'::text THEN 'M-T-N'::character varying
                                WHEN '.'::text THEN ''::character varying
                                WHEN 'MN'::text THEN 'M-N'::character varying
                                ELSE tt1.abrv
                            END)::text, '-'::text), '-'::text, ''::text)) AS turno,
                        iec1.institucioneducativa_id, iec1.gestion_tipo_id
                        FROM ((institucioneducativa_curso iec1
                        JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
                        JOIN turno_tipo tt1 ON ((tt1.id = iec1.turno_tipo_id)))
                        WHERE ( (iec1.gestion_tipo_id = ".$gestion.") AND (iec1.institucioneducativa_id IN ( 
                                select id from institucioneducativa 
                                where le_juridicciongeografica_id in (select le_juridicciongeografica_id from institucioneducativa where id = ".$sie.")
                                and institucioneducativa_acreditacion_tipo_id = 1 and estadoinstitucion_tipo_id = 10 )) AND 
                        (iec1.nivel_tipo_id = ANY (ARRAY[13, 3])))
                        GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id) v
                    GROUP BY v.institucioneducativa_id, v.gestion_tipo_id, v.turno
                    ORDER BY
                        CASE v.turno
                                WHEN 'M'::text THEN 1
                                WHEN 'T'::text THEN 2
                                WHEN 'N'::text THEN 3
                                ELSE 0
                        END
                ) vv
                JOIN institucioneducativa as ie on ie.id = vv.institucioneducativa_id
                where vv.institucioneducativa_id not in (".$sie.") and ie.orgcurricular_tipo_id = 1
                GROUP BY ie.id, ie.institucioneducativa, vv.gestion_tipo_id
            ) as vvv
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaEquipoLaboratorio($sie) {
        $adjuntoDir = '/uploads/equipamiento_laboratorio/';
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select elfq.*, elfqct.construccion, '".$adjuntoDir."'||elfqf1.foto as foto1, '".$adjuntoDir."'||elfqf2.foto as foto2 from equip_labo_fisi_quim as elfq 
            inner join equip_labo_fisi_quim_construida_tipo as elfqct on elfqct.id = elfq.secciv_construida_tipo_id
            left join equip_labo_fisi_quim_fotos as elfqf1 on elfqf1.equip_labo_fisi_quim_id = elfq.id and elfqf1.tipo_foto_id = 1
            left join equip_labo_fisi_quim_fotos as elfqf2 on elfqf2.equip_labo_fisi_quim_id = elfq.id and elfqf2.tipo_foto_id = 2
            where elfq.institucioneducativa_id = ".$sie."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    public function creaFormularioVistaPdf($sie, $ges)
    { 
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_vista_pdf'))  
            ->add('sie', 'hidden', array('attr' => array('value' => base64_encode($sie))))
            ->add('ges', 'hidden', array('attr' => array('value' => base64_encode($ges))))          
            ->add('download', 'submit', array('label' => 'Descargar Pdf', 'attr' => array('class' => 'btn btn-lilac')))
            ->getForm();
        return $form;        
    }
}