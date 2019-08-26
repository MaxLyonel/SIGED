<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Vista controller.
 *
 */
class ReporteController extends Controller {
    private $route_anterior;
    private $var_anterior;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function defaultAction() {
        $bimestre = 1;
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity,$bimestre);
        return $this->render('SieAppWebBundle:Reporte:consolidacion.html.twig', array('periodo'=>$bimestre,'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
    } 
    
    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Regular
     * Jurlan
     */
    public function departamentoRegularAction($bimestre) {
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity,$bimestre);
        return $this->render('SieAppWebBundle:Reporte:consolidacion.html.twig', array('periodo'=>$bimestre, 'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
    } 

    /**
     * Reporte Grafico y Detalle de la consolidacion por Distrito - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return typerepr
     */
    public function distritoRegularAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $depto = $request->get('name');
            $bimestre = $request->get('periodo');
            $entity = $this->consolidacionNivelDepartamental($id);            
            $datos = $this->chartBarConsolidacion($entity,$bimestre);            
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieAppWebBundle:Reporte:consolidacion.html.twig', array('periodo'=>$bimestre,'dato' => $datos, 'entity' => $entity, 'nombre' => $depto, 'nivel' => 'distritos', 'nivelnext' => 'institucional'));
    }

    /**
     * Reporte Grafico y Detalle de la consolidacion por Unidades Educativas - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function ueRegularAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $distrito = $request->get('name');
            $bimestre = $request->get('periodo');
            $entity = $this->consolidacionNivelDistrital($id);            
            $datos = $this->chartBarConsolidacion($entity,$bimestre);            
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieAppWebBundle:Reporte:consolidacion.html.twig', array('periodo'=>$bimestre,'dato' => $datos, 'entity' => $entity, 'nombre' => $distrito, 'nivel' => 'unidades educativas', 'nivelnext' => 'estudiantil'));
    }

    /**
     * Funcion que retorna el Reporte Grafico Chart Jquery de tipo Bar - Educacion Regular
     * Jurlan
     * @param Request $entity
     * @return repr
     */
    public function chartBarConsolidacion($entity,$bimestre) {
        $totalConsolidado = 0;
        $totalNoConsolidado = 0;
        if($bimestre == 1){
            $numeroLiteral = 'Primer';
        } elseif ($bimestre == 2) {
            $numeroLiteral = 'Segundo';
        } elseif ($bimestre == 3) {
            $numeroLiteral = 'Tercer';
        } elseif ($bimestre == 4) {
            $numeroLiteral = 'Cuarto';
        } elseif ($bimestre == 5) {
            $numeroLiteral = 'Quinto';
        } elseif ($bimestre == 6) {
            $numeroLiteral = 'Sexto';
        } elseif ($bimestre == 7) {
            $numeroLiteral = 'Septimo';
        } elseif ($bimestre == 8) {
            $numeroLiteral = 'Octavo';
        } elseif ($bimestre == 9) {
            $numeroLiteral = 'Noveno';
        } else {
            $numeroLiteral = '';
        }
            
        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos = "  
            var chartConsolidacion = new CanvasJS.Chart('chartContainer',
            {
                title:{
                    text: 'Consolidación - " . $numeroLiteral . " Bimestre', 
                    fontSize: 20,
                },
                animationEnabled: true,
                axisX:{
                  title: '',
                  titleFontSize: 20,
                },
                axisY:{
                  title: '',
                  titleFontSize: 20,
                  labelAutoFit: true,    
		  suffix: '%',
                  interval: 10,
                },
                data: [
                    {   
                        name: 'Consolidado',
                        showInLegend: true,
                        type: 'stackedColumn100', 
                        color: '#004B8D',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadConsolidado = $entity[$i]['cant_si_cons_'.$bimestre];
            $totalConsolidado = $totalConsolidado + $cantidadConsolidado;
            $nombre = $entity[$i]['nombre'];
            $datosTemp = $datosTemp . '{y: ' . ($cantidadConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '"},';
        }
        $datos = $datos . '{y: ' . ($totalConsolidado) . ', legendText:"Nacional", label: "Nacional", color: "#2F4F4F"},' . $datosTemp;
        $datos = $datos . "]
                    }, 
                    {        
                        name: 'No Consolidado',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#4192D9',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadNoConsolidado = $entity[$i]['cant_no_cons_'.$bimestre];
            $totalNoConsolidado = $totalNoConsolidado + $cantidadNoConsolidado;
            $nombre = $entity[$i]['nombre'];
            $datosTemp = $datosTemp . '{y: ' . ($cantidadNoConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '"},';
        }
        $datos = $datos . '{y: ' . ($totalNoConsolidado) . ', legendText:"General", label: "General", color: "#3CB371"},' . $datosTemp;
        $datos = $datos . "]
                    },              
                         
                ]
            });
            chartConsolidacion.render();";    
        return $datos;        
    }
    
    /**
     * Funcion que busca los archivos consolidados de la gestion actual y los bimestres por departamento
     * @return tabla
     */
    public function consolidacionNivelNacional() {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select 
                case depto when 'Chuquisaca' then 1 when 'La Paz' then 2 when 'Cochabamba' then 3 when 'Oruro' then 4 when 'Potosi' then 5 when 'Tarija' then 6 when 'Santa Cruz' then 7 when 'Beni' then 8 when 'Pando' then 9 end as codigo
                , depto as nombre
                , sum(case when consolidado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
                , sum(case when consolidado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
                , sum(case when consolidado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
                , sum(case when consolidado = 1 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
                , sum(case when consolidado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
                , sum(case when consolidado = 1 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
                , sum(case when consolidado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
                , sum(case when consolidado = 1 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
                from (
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.consolidado, 'bim1' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim1,0) as consolidado
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim1=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim2' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim2,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim2=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim3' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim3,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim3=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim4' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim4,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim4=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                ) as v
                group by depto order by depto
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }

    /**
     * Funcion que busca los archivos consolidados de la gestion actual y los bimestres por Distrito
     * @param type $depto - Departamento
     * @return tabla
     */
    public function consolidacionNivelDepartamental($depto) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select lt.lugar as nombre, v2.* from (
            select cod_dis as codigo
            , sum(case when consolidado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
            , sum(case when consolidado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
            , sum(case when consolidado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
            , sum(case when consolidado = 1 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
            , sum(case when consolidado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
            , sum(case when consolidado = 1 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
            , sum(case when consolidado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
            , sum(case when consolidado = 1 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
            from (
            select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.consolidado, 'bim1' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim1,0) as consolidado
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim1=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim2' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim2,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim2=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim3' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim3,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim3=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim4' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim4,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim4=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
            ) as v 
            where cod_depto = '" . $depto . "'
            group by cod_dis
            ) as v2 
            left join lugar_tipo as lt on lt.codigo = v2.codigo order by lugar
            ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }

    /**
     * Funcion que busca los archivos consolidados de la gestion actual y los bimestres por Unidades Educativas
     * @param type $distrito - Distrito
     * @return tabla
     */
    public function consolidacionNivelDistrital($distrito) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select ie.id as codigoue, ie.institucioneducativa as nombre, v2.* from (
            select cast(v.id as integer) as codigo
            , sum(case when consolidado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
            , sum(case when consolidado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
            , sum(case when consolidado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
            , sum(case when consolidado = 1 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
            , sum(case when consolidado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
            , sum(case when consolidado = 1 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
            , sum(case when consolidado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
            , sum(case when consolidado = 1 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
            from (
            select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.consolidado, 'bim1' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim1,0) as consolidado
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim1=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim2' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim2,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim2=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim3' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim3,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim3=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
                union all
                select *
                from (
                select substring(cast(b.cod_dis as varchar(4)),1,1) as cod_depto, case substring(cast(b.cod_dis as varchar(4)),1,1) when '1' then 'Chuquisaca' when '2' then 'La Paz' when '3' then 'Cochabamba' when '4' then 'Oruro' when '5' then 'Potosi'
                when '6' then 'Tarija' when '7' then 'Santa Cruz' when '8' then 'Beni' when '9' then 'Pando' end as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,d.bim as consolidado, 'bim4' as bimestre
                from jurisdiccion_geografica a 
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id 
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then b.unidad_educativa else a.unidad_educativa end,coalesce(b.bim4,0) as bim
                from (
                select * 
                from registro_consolidacion where gestion=2014) a
                left join (
                select * 
                from registro_consolidacion where gestion=2015
                and bim4=1
                ) b on a.unidad_educativa=b.unidad_educativa) d on c.id=d.unidad_educativa) a
            ) as v 
            where cod_dis = '" . $distrito . "'
            group by id
            ) as v2 
            left join institucioneducativa as ie on ie.id = v2.codigo order by institucioneducativa            
            ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }

    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Especial Fin dde Gestion 2014
     * Jurlan
     */
    public function departamentoEspecialFin2014Action() {
        $entity = array(
            1 => array('nombre' => 'CHUQUISACA', 'cantidad' => 8),
            2 => array('nombre' => 'LA PAZ', 'cantidad' => 16),
            3 => array('nombre' => 'COCHABAMBA', 'cantidad' => 11),
            4 => array('nombre' => 'ORURO', 'cantidad' => 9),
            5 => array('nombre' => 'POTOSI', 'cantidad' => 5),
            6 => array('nombre' => 'TARIJA', 'cantidad' => 9),
            7 => array('nombre' => 'SANTA CRUZ', 'cantidad' => 36),
            8 => array('nombre' => 'BENI', 'cantidad' => 9),
            9 => array('nombre' => 'PANDO', 'cantidad' => 3),
        );

        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos1 = "  
            var chart1 = new CanvasJS.Chart('chartContainer1',
            {
              title:{
                text: 'Unidades Educativas Consolidadas por Departamento - Fin de Gestión 2014',
                fontSize: 20,
              },
              exportFileName: 'Nacional',
              exportEnabled: true,
              animationEnabled: true,
              legend: {
                verticalAlign: 'bottom',
                horizontalAlign: 'center'
              },
              theme: 'theme1',
              data: [

                        {        
                          type: 'pie',  
                          showInLegend: true, 
                          indexLabelFontSize: 12,
			  indexLabelFontFamily: 'Monospace',       
			  indexLabelFontColor: 'darkgrey', 
			  indexLabelLineColor: 'darkgrey',        
			  indexLabelPlacement: 'outside',
                          toolTipContent: '<strong>#percent%</strong>',
                          indexLabel: '{label} {y}',
                          dataPoints: [ 
                            {y: 8, legendText: 'CHUQUISACA', label: 'CHUQUISACA'},
                            {y: 16, legendText: 'LA PAZ', label: 'LA PAZ'},
                            {y: 11, legendText: 'COCHABAMBA', label: 'COCHABAMBA'},
                            {y: 9, legendText: 'ORURO', label: 'ORURO'},
                            {y: 5, legendText: 'POTOSI', label: 'POTOSI'},
                            {y: 9, legendText: 'TARIJA', label: 'TARIJA'},
                            {y: 36, legendText: 'SANTA CRUZ', label: 'SANTA CRUZ'},
                            {y: 9, legendText: 'BENI', label: 'BENI'},
                            {y: 3, legendText: 'PANDO', label: 'PANDO'},
                          ]
                        }   
                    ]
                });
            chart1.render();";


        $datos2 = "  
            var chart2 = new CanvasJS.Chart('chartContainer2',
            {
              title:{
                text: 'Cantidad de Inscripciones por Genero - Fin de Gestión 2014' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Femenino', 
                            color: '#C9302C',
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 309, label: 'CHUQUISACA'},
                                {y: 631, label: 'LA PAZ'},
                                {y: 719, label: 'COCHABAMBA'},
                                {y: 293, label: 'ORURO'},
                                {y: 242, label: 'POTOSI'},
                                {y: 531, label: 'TARIJA'},
                                {y: 1386, label: 'SANTA CRUZ'},
                                {y: 297, label: 'BENI'},
                                {y: 99, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Masculino',
                            color: '#f0ad4e',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 403, label: 'CHUQUISACA'},
                                {y: 892, label: 'LA PAZ'},
                                {y: 1159, label: 'COCHABAMBA'},
                                {y: 369, label: 'ORURO'},
                                {y: 342, label: 'POTOSI'},
                                {y: 720, label: 'TARIJA'},
                                {y: 1862, label: 'SANTA CRUZ'},
                                {y: 390, label: 'BENI'},
                                {y: 134, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart2.render();";

        $datos3 = "  
            var chart3 = new CanvasJS.Chart('chartContainer3',
            {
              title:{
                text: 'Cantidad de Inscripciones por Tipo de Discapacidad - Fin de Gestión 2014' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Auditiva', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 55, label: 'CHUQUISACA'},
                                {y: 172, label: 'LA PAZ'},
                                {y: 159, label: 'COCHABAMBA'},
                                {y: 44, label: 'ORURO'},
                                {y: 51, label: 'POTOSI'},
                                {y: 531, label: 'TARIJA'},
                                {y: 379, label: 'SANTA CRUZ'},
                                {y: 165, label: 'BENI'},
                                {y: 21, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Visual',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 46, label: 'CHUQUISACA'},
                                {y: 97, label: 'LA PAZ'},
                                {y: 29, label: 'COCHABAMBA'},
                                {y: 28, label: 'ORURO'},
                                {y: 8, label: 'POTOSI'},
                                {y: 80, label: 'TARIJA'},
                                {y: 273, label: 'SANTA CRUZ'},
                                {y: 85, label: 'BENI'},
                                {y: 37, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Intelectual', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 392, label: 'CHUQUISACA'},
                                {y: 679, label: 'LA PAZ'},
                                {y: 757, label: 'COCHABAMBA'},
                                {y: 262, label: 'ORURO'},
                                {y: 327, label: 'POTOSI'},
                                {y: 472, label: 'TARIJA'},
                                {y: 1570, label: 'SANTA CRUZ'},
                                {y: 334, label: 'BENI'},
                                {y: 98, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Fisico Motora', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 26, label: 'CHUQUISACA'},
                                {y: 41, label: 'LA PAZ'},
                                {y: 27, label: 'COCHABAMBA'},
                                {y: 20, label: 'ORURO'},
                                {y: 55, label: 'POTOSI'},
                                {y: 112, label: 'TARIJA'},
                                {y: 177, label: 'SANTA CRUZ'},
                                {y: 7, label: 'BENI'},
                                {y: 0, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Multigrado', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 99, label: 'CHUQUISACA'},
                                {y: 367, label: 'LA PAZ'},
                                {y: 193, label: 'COCHABAMBA'},
                                {y: 82, label: 'ORURO'},
                                {y: 90, label: 'POTOSI'},
                                {y: 106, label: 'TARIJA'},
                                {y: 518, label: 'SANTA CRUZ'},
                                {y: 86, label: 'BENI'},
                                {y: 25, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Dificultad de Aprendizaje', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 94, label: 'CHUQUISACA'},
                                {y: 167, label: 'LA PAZ'},
                                {y: 713, label: 'COCHABAMBA'},
                                {y: 226, label: 'ORURO'},
                                {y: 53, label: 'POTOSI'},
                                {y: 375, label: 'TARIJA'},
                                {y: 367, label: 'SANTA CRUZ'},
                                {y: 10, label: 'BENI'},
                                {y: 52, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart3.render();";

        $datos4 = "  
            var chart4 = new CanvasJS.Chart('chartContainer4',
            {
              title:{
                text: 'Cantidad de Inscripciones por Origen de Discapacidad - Fin de Gestión 2014' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Nacimiento', 
                            color: '#C9302C',
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 560, label: 'CHUQUISACA'},
                                {y: 1259, label: 'LA PAZ'},
                                {y: 945, label: 'COCHABAMBA'},
                                {y: 382, label: 'ORURO'},
                                {y: 493, label: 'POTOSI'},
                                {y: 707, label: 'TARIJA'},
                                {y: 2394, label: 'SANTA CRUZ'},
                                {y: 590, label: 'BENI'},
                                {y: 163, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Adquirida',
                            color: '#f0ad4e',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 58, label: 'CHUQUISACA'},
                                {y: 57, label: 'LA PAZ'},
                                {y: 220, label: 'COCHABAMBA'},
                                {y: 54, label: 'ORURO'},
                                {y: 38, label: 'POTOSI'},
                                {y: 169, label: 'TARIJA'},
                                {y: 487, label: 'SANTA CRUZ'},
                                {y: 87, label: 'BENI'},
                                {y: 18, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'No Definida',
                            color: '#47A447',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 94, label: 'CHUQUISACA'},
                                {y: 167, label: 'LA PAZ'},
                                {y: 713, label: 'COCHABAMBA'},
                                {y: 226, label: 'ORURO'},
                                {y: 53, label: 'POTOSI'},
                                {y: 375, label: 'TARIJA'},
                                {y: 367, label: 'SANTA CRUZ'},
                                {y: 10, label: 'BENI'},
                                {y: 52, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart4.render();";

        $datos5 = "  
            var chart5 = new CanvasJS.Chart('chartContainer5',
            {
              title:{
                text: 'Cantidad de Inscripciones por Dependencia - Fin de Gestión 2014' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Fiscal o Estatal', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 102, label: 'CHUQUISACA'},
                                {y: 707, label: 'LA PAZ'},
                                {y: 1157, label: 'COCHABAMBA'},
                                {y: 442, label: 'ORURO'},
                                {y: 307, label: 'POTOSI'},
                                {y: 0, label: 'TARIJA'},
                                {y: 2179, label: 'SANTA CRUZ'},
                                {y: 521, label: 'BENI'},
                                {y: 233, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Convenio',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 610, label: 'CHUQUISACA'},
                                {y: 816, label: 'LA PAZ'},
                                {y: 721, label: 'COCHABAMBA'},
                                {y: 220, label: 'ORURO'},
                                {y: 277, label: 'POTOSI'},
                                {y: 1251, label: 'TARIJA'},
                                {y: 1069, label: 'SANTA CRUZ'},
                                {y: 166, label: 'BENI'},
                                {y: 0, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart5.render();";
        
        $datos6 = "  
            var chart6 = new CanvasJS.Chart('chartContainer6',
            {
              title:{
                text: 'Cantidad de Inscripciones por Área Geográfica - Fin de Gestión 2014' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Urbano', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 665, label: 'CHUQUISACA'},
                                {y: 1523, label: 'LA PAZ'},
                                {y: 1878, label: 'COCHABAMBA'},
                                {y: 662, label: 'ORURO'},
                                {y: 584, label: 'POTOSI'},
                                {y: 1251, label: 'TARIJA'},
                                {y: 2981, label: 'SANTA CRUZ'},
                                {y: 687, label: 'BENI'},
                                {y: 172, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Rural',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 47, label: 'CHUQUISACA'},
                                {y: 0, label: 'LA PAZ'},
                                {y: 0, label: 'COCHABAMBA'},
                                {y: 0, label: 'ORURO'},
                                {y: 0, label: 'POTOSI'},
                                {y: 0, label: 'TARIJA'},
                                {y: 267, label: 'SANTA CRUZ'},
                                {y: 0, label: 'BENI'},
                                {y: 61, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart6.render();";
        
        return $this->render('SieAppWebBundle:Reporte:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
    }

    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Especial Inicio de Gestion 2015
     * Jurlan
     */
    public function departamentoEspecialIni2015Action() {
        $entity = array(
            1 => array('nombre' => 'CHUQUISACA', 'cantidad' => 10),
            2 => array('nombre' => 'LA PAZ', 'cantidad' => 16),
            3 => array('nombre' => 'COCHABAMBA', 'cantidad' => 12),
            4 => array('nombre' => 'ORURO', 'cantidad' => 9),
            5 => array('nombre' => 'POTOSI', 'cantidad' => 6),
            6 => array('nombre' => 'TARIJA', 'cantidad' => 9),
            7 => array('nombre' => 'SANTA CRUZ', 'cantidad' => 42),
            8 => array('nombre' => 'BENI', 'cantidad' => 9),
            9 => array('nombre' => 'PANDO', 'cantidad' => 3),
        );

        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos1 = "  
            var chart1 = new CanvasJS.Chart('chartContainer1',
            {
              title:{
                text: 'Unidades Educativas Consolidadas por Departamento - Inicio de Gestión 2015',
                fontSize: 20,
              },
              exportFileName: 'Nacional',
              exportEnabled: true,
              animationEnabled: true,
              legend: {
                verticalAlign: 'bottom',
                horizontalAlign: 'center'
              },
              theme: 'theme1',
              data: [

                        {        
                          type: 'pie',  
                          showInLegend: true, 
                          indexLabelFontSize: 12,
			  indexLabelFontFamily: 'Monospace',       
			  indexLabelFontColor: 'darkgrey', 
			  indexLabelLineColor: 'darkgrey',        
			  indexLabelPlacement: 'outside',
                          toolTipContent: '<strong>#percent%</strong>',
                          indexLabel: '{label} {y}',
                          dataPoints: [ 
                            {y: 10, legendText: 'CHUQUISACA', label: 'CHUQUISACA'},
                            {y: 16, legendText: 'LA PAZ', label: 'LA PAZ'},
                            {y: 12, legendText: 'COCHABAMBA', label: 'COCHABAMBA'},
                            {y: 9, legendText: 'ORURO', label: 'ORURO'},
                            {y: 6, legendText: 'POTOSI', label: 'POTOSI'},
                            {y: 9, legendText: 'TARIJA', label: 'TARIJA'},
                            {y: 42, legendText: 'SANTA CRUZ', label: 'SANTA CRUZ'},
                            {y: 9, legendText: 'BENI', label: 'BENI'},
                            {y: 3, legendText: 'PANDO', label: 'PANDO'},
                          ]
                        }   
                    ]
                });
            chart1.render();";


        $datos2 = "  
            var chart2 = new CanvasJS.Chart('chartContainer2',
            {
              title:{
                text: 'Cantidad de Inscripciones por Genero - Inicio de Gestión 2015' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Femenino', 
                            color: '#C9302C',
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 424, label: 'CHUQUISACA'},
                                {y: 876, label: 'LA PAZ'},
                                {y: 1257, label: 'COCHABAMBA'},
                                {y: 368, label: 'ORURO'},
                                {y: 381, label: 'POTOSI'},
                                {y: 680, label: 'TARIJA'},
                                {y: 2172, label: 'SANTA CRUZ'},
                                {y: 431, label: 'BENI'},
                                {y: 137, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Masculino',
                            color: '#f0ad4e',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 345, label: 'CHUQUISACA'},
                                {y: 666, label: 'LA PAZ'},
                                {y: 781, label: 'COCHABAMBA'},
                                {y: 292, label: 'ORURO'},
                                {y: 275, label: 'POTOSI'},
                                {y: 505, label: 'TARIJA'},
                                {y: 1703, label: 'SANTA CRUZ'},
                                {y: 332, label: 'BENI'},
                                {y: 96, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart2.render();";

        $datos3 = "  
            var chart3 = new CanvasJS.Chart('chartContainer3',
            {
              title:{
                text: 'Cantidad de Inscripciones por Tipo de Discapacidad - Inicio de Gestión 2015' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Auditiva', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 78, label: 'CHUQUISACA'},
                                {y: 199, label: 'LA PAZ'},
                                {y: 153, label: 'COCHABAMBA'},
                                {y: 39, label: 'ORURO'},
                                {y: 62, label: 'POTOSI'},
                                {y: 102, label: 'TARIJA'},
                                {y: 468, label: 'SANTA CRUZ'},
                                {y: 175, label: 'BENI'},
                                {y: 21, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Visual',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 47, label: 'CHUQUISACA'},
                                {y: 112, label: 'LA PAZ'},
                                {y: 32, label: 'COCHABAMBA'},
                                {y: 32, label: 'ORURO'},
                                {y: 8, label: 'POTOSI'},
                                {y: 69, label: 'TARIJA'},
                                {y: 263, label: 'SANTA CRUZ'},
                                {y: 89, label: 'BENI'},
                                {y: 44, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Intelectual', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 416, label: 'CHUQUISACA'},
                                {y: 718, label: 'LA PAZ'},
                                {y: 823, label: 'COCHABAMBA'},
                                {y: 318, label: 'ORURO'},
                                {y: 355, label: 'POTOSI'},
                                {y: 489, label: 'TARIJA'},
                                {y: 1965, label: 'SANTA CRUZ'},
                                {y: 374, label: 'BENI'},
                                {y: 102, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Fisico Motora', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 39, label: 'CHUQUISACA'},
                                {y: 43, label: 'LA PAZ'},
                                {y: 35, label: 'COCHABAMBA'},
                                {y: 24, label: 'ORURO'},
                                {y: 75, label: 'POTOSI'},
                                {y: 57, label: 'TARIJA'},
                                {y: 216, label: 'SANTA CRUZ'},
                                {y: 12, label: 'BENI'},
                                {y: 0, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Multigrado', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 117, label: 'CHUQUISACA'},
                                {y: 337, label: 'LA PAZ'},
                                {y: 202, label: 'COCHABAMBA'},
                                {y: 82, label: 'ORURO'},
                                {y: 94, label: 'POTOSI'},
                                {y: 113, label: 'TARIJA'},
                                {y: 547, label: 'SANTA CRUZ'},
                                {y: 90, label: 'BENI'},
                                {y: 20, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Dificultad de Aprendizaje', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 72, label: 'CHUQUISACA'},
                                {y: 133, label: 'LA PAZ'},
                                {y: 811, label: 'COCHABAMBA'},
                                {y: 165, label: 'ORURO'},
                                {y: 62, label: 'POTOSI'},
                                {y: 355, label: 'TARIJA'},
                                {y: 416, label: 'SANTA CRUZ'},
                                {y: 23, label: 'BENI'},
                                {y: 46, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart3.render();";

        $datos4 = "  
            var chart4 = new CanvasJS.Chart('chartContainer4',
            {
              title:{
                text: 'Cantidad de Inscripciones por Origen de Discapacidad - Inicio de Gestión 2015' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Nacimiento', 
                            color: '#C9302C',
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 645, label: 'CHUQUISACA'},
                                {y: 1307, label: 'LA PAZ'},
                                {y: 1001, label: 'COCHABAMBA'},
                                {y: 445, label: 'ORURO'},
                                {y: 547, label: 'POTOSI'},
                                {y: 659, label: 'TARIJA'},
                                {y: 2832, label: 'SANTA CRUZ'},
                                {y: 647, label: 'BENI'},
                                {y: 168, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Adquirida',
                            color: '#f0ad4e',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 52, label: 'CHUQUISACA'},
                                {y: 102, label: 'LA PAZ'},
                                {y: 244, label: 'COCHABAMBA'},
                                {y: 50, label: 'ORURO'},
                                {y: 47, label: 'POTOSI'},
                                {y: 171, label: 'TARIJA'},
                                {y: 627, label: 'SANTA CRUZ'},
                                {y: 93, label: 'BENI'},
                                {y: 19, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'No Definida',
                            color: '#47A447',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 72, label: 'CHUQUISACA'},
                                {y: 133, label: 'LA PAZ'},
                                {y: 811, label: 'COCHABAMBA'},
                                {y: 165, label: 'ORURO'},
                                {y: 62, label: 'POTOSI'},
                                {y: 355, label: 'TARIJA'},
                                {y: 416, label: 'SANTA CRUZ'},
                                {y: 23, label: 'BENI'},
                                {y: 46, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart4.render();";

        $datos5 = "  
            var chart5 = new CanvasJS.Chart('chartContainer5',
            {
              title:{
                text: 'Cantidad de Inscripciones por Dependencia - Inicio de Gestión 2015' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Fiscal o Estatal', 
                            axisYType: 'secondary',
                            dataPoints: [                                
                                {y: 188, label: 'CHUQUISACA'},
                                {y: 724, label: 'LA PAZ'},
                                {y: 1251, label: 'COCHABAMBA'},
                                {y: 504, label: 'ORURO'},
                                {y: 381, label: 'POTOSI'},
                                {y: 0, label: 'TARIJA'},
                                {y: 2644, label: 'SANTA CRUZ'},
                                {y: 587, label: 'BENI'},
                                {y: 233, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Convenio',
                            axisYType: 'secondary',
                            dataPoints: [                           
                                {y: 581, label: 'CHUQUISACA'},
                                {y: 800, label: 'LA PAZ'},
                                {y: 805, label: 'COCHABAMBA'},
                                {y: 156, label: 'ORURO'},
                                {y: 275, label: 'POTOSI'},
                                {y: 1185, label: 'TARIJA'},
                                {y: 1231, label: 'SANTA CRUZ'},
                                {y: 176, label: 'BENI'},
                                {y: 0, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart5.render();";
        
        $datos6 = "  
            var chart6 = new CanvasJS.Chart('chartContainer6',
            {
              title:{
                text: 'Cantidad de Inscripciones por Área Geográfica - Inicio de Gestión 2015' ,                
                fontSize: 20,
              },
              exportFileName: 'Genero',
              exportEnabled: true,
              animationEnabled: true,	
                
              axisX:{
                interval: 1,
                gridThickness: 0,
                labelFontSize: 10,
                labelFontStyle: 'normal',
                labelFontWeight: 'normal',
                labelFontFamily: 'Lucida Sans Unicode'
              },
              axisY2:{
                interlacedColor: 'rgba(1,77,101,.2)',
                gridColor: 'rgba(1,77,101,.1)',
                labelFontSize: 15,
              },
              toolTip: {
			shared: true
                },
                legend:{
                        verticalAlign: 'top',
                        horizontalAlign: 'center',
                        fontSize: 20,
                },
                theme: 'theme1',
              data: [
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Urbano', 
                            axisYType: 'secondary',
                            color: '#C9302C',
                            dataPoints: [                                
                                {y: 716, label: 'CHUQUISACA'},
                                {y: 1542, label: 'LA PAZ'},
                                {y: 2056, label: 'COCHABAMBA'},
                                {y: 660, label: 'ORURO'},
                                {y: 656, label: 'POTOSI'},
                                {y: 1185, label: 'TARIJA'},
                                {y: 3604, label: 'SANTA CRUZ'},
                                {y: 763, label: 'BENI'},
                                {y: 178, label: 'PANDO'},
                            ]
                        },
                        {     
                            type: 'stackedBar',
                            showInLegend: true,
                            name: 'Rural',
                            axisYType: 'secondary',
                            color: '#f0ad4e',
                            dataPoints: [                           
                                {y: 53, label: 'CHUQUISACA'},
                                {y: 0, label: 'LA PAZ'},
                                {y: 0, label: 'COCHABAMBA'},
                                {y: 0, label: 'ORURO'},
                                {y: 0, label: 'POTOSI'},
                                {y: 0, label: 'TARIJA'},
                                {y: 271, label: 'SANTA CRUZ'},
                                {y: 0, label: 'BENI'},
                                {y: 55, label: 'PANDO'},
                            ]
                        },
                    ]
                });
            chart6.render();";
        
        return $this->render('SieAppWebBundle:Reporte:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
    }

    /**
     * Pagina Inicial - Información General - Nacional - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol(0,0,1);
        $subEntidades = $this->buscaSubEntidadRol(0,0);
        $entityEstadistica = $this->buscaEstadisticaAreaRol(0,0);
        //$entityEstadisticaUE = $this->buscaEstadisticaUERol(0,0);
        //$entityEstadisticaEE = $this->buscaEstadisticaEERol(0,0);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegular($gestionProcesada);

        $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
        $chartNivel = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio",$gestionProcesada,2,"chartContainerEfectivoNivel");
        $chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
        $chartGenero = $this->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Sexo",$gestionProcesada,3,"chartContainerEfectivoGenero");
        $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Dependencia",$gestionProcesada,5,"chartContainerEfectivoDependencia");

        return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
            'infoEntidad'=>$entidad,
            'infoSubEntidad'=>$subEntidades, 
            'infoEstadistica'=>$entityEstadistica, 
            //'infoEstadisticaUE'=>$entityEstadisticaUE,
            //'infoEstadisticaEE'=>$entityEstadisticaEE,
            'rol'=>0,
            'datoGraficoMatricula'=>$chartMatricula,
            'datoGraficoNivel'=>$chartNivel,
            'datoGraficoNivelGrado'=>$chartNivelGrado,
            'datoGraficoGenero'=>$chartGenero,
            'datoGraficoArea'=>$chartArea,
            'datoGraficoDependencia'=>$chartDependencia,
            'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
            'gestion'=>$gestionProcesada,
            'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
            'form' => $defaultController->createLoginForm()->createView()
        ));
    }

    /**
     * Pagina Inicial - Información General - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;
        $idUsuario = $this->session->get('userId');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $codigoArea = $request->get('codigo');
            $rolUsuario = $request->get('rol');
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario,1);
        $subEntidades = $this->buscaSubEntidadRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaEstadisticaAreaRol($codigoArea,$rolUsuario);
        //$entityEstadisticaUE = $this->buscaEstadisticaUERol($codigoArea,$rolUsuario);
        //$entityEstadisticaEE = $this->buscaEstadisticaEERol($codigoArea,$rolUsuario);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegular($gestionProcesada);
       
        $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
        $chartNivel = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio",$gestionProcesada,2,"chartContainerEfectivoNivel");
        $chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
        $chartGenero = $this->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Sexo",$gestionProcesada,3,"chartContainerEfectivoGenero");
        $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Dependencia",$gestionProcesada,5,"chartContainerEfectivoDependencia");
        
        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                //'infoEstadisticaUE'=>$entityEstadisticaUE,
                //'infoEstadisticaEE'=>$entityEstadisticaEE,
                'datoGraficoMatricula'=>$chartMatricula,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoNivelGrado'=>$chartNivelGrado,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'gestion'=>$gestionProcesada,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,
                    'infoEstadisticaUE'=>$entityEstadisticaUE,
                    'infoEstadisticaEE'=>$entityEstadisticaEE,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("Efectivos");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }  

    /**
     * Pagina Inicial - Información General - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularInstitucionEducativaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;
        $idUsuario = $this->session->get('userId');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $codigoArea = $request->get('codigo');
            $rolUsuario = $request->get('rol');
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario,1);
        $subEntidades = $this->buscaSubEntidadRegularInstitucionEducativaAreaRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaEstadisticaRegularInstitucionEducativaAreaRol($codigoArea,$rolUsuario);
        //$entityEstadisticaUE = $this->buscaEstadisticaUERol($codigoArea,$rolUsuario);
        //$entityEstadisticaEE = $this->buscaEstadisticaEERol($codigoArea,$rolUsuario);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegularInstitucionEducativa($gestionProcesada);
       
        $chartNivel = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Unidades Educativas según Nivel de Estudio",$gestionProcesada,11,"chartContainerInstitucionNivel");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Unidades Educativas según Dependencia",$gestionProcesada,11,"chartContainerInstitucionDependencia");

        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoDependencia'=>$chartDependencia,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("Efectivos");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }

    /**
     * Pagina Inicial - Información General - Institutos Tecnicos
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function informacionGeneralInstitutosTecnicosAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $idUsuario = $this->session->get('userId');
        $gestionProcesada = $gestionActual;

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $codigoArea = $request->get('codigo');
            $rolUsuario = $request->get('rol');
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario,9);
        //dump($entidad);die;
        $subEntidades = $this->buscaSubEntidadInstitutosTecnicosAreaRol($codigoArea,$rolUsuario);
        //dump($subEntidades);die;
        $entityEstadistica = $this->buscaEstadisticaInstitutosTecnicosAreaRol($codigoArea,$rolUsuario);
        //dump($entityEstadistica);die;
        $entityEstadisticaCarreras = $this->buscaEstadisticaCarrerasInstitutosTecnicosAreaRol($codigoArea,$rolUsuario,$entityEstadistica['cant_total']?$entityEstadistica['cant_total']:0);
        //dump($entityEstadisticaCarreras);die;
        if($rolUsuario <> 9){
            $chartTecnicosTecnologicos = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Institutos Técnicos y/o Tecnológicos",$gestionActual,12,"chartContainerTecnicoTecnologico");
            $chartArea = $this->chartPie($entityEstadistica,"Institutos Técnicos y/o Tecnológicos segun Área Geográfica",$gestionProcesada,1,"chartContainerArea");
            $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Institutos Técnicos y/o Tecnológicos según Dependencia",$gestionActual,12,"chartContainerDependencia");
            $chartSede = $this->chartPie($entityEstadistica,"Institutos Técnicos y/o Tecnológicos segun Sede",$gestionProcesada,2,"chartContainerSede");
            $chartCarreras = $this->chartColumnInformacionGeneral($entityEstadisticaCarreras,"Frecuencia de Carreras segun los Institutos Técnicos y/o Tecnológicos",$gestionActual,13,"chartContainerCarreras");
        }else{
            $chartTecnicosTecnologicos = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Carreras y/o Cursos",$gestionActual,13,"chartContainerTecnicoTecnologico");
            $chartArea = '';
            $chartDependencia = '';
            $chartSede = '';
            $chartCarreras = '';
        }

        $link = true;
        if ($rolUsuario == 9){
            $link = false;
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:institutosTecnicosTecnologicos.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoInstitutos'=>$chartTecnicosTecnologicos,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoSede'=>$chartSede,
                'datoGraficoCarreras'=>$chartCarreras,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:institutosTecnicosTecnologicos.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoInstitutos'=>$chartTecnicosTecnologicos,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoSede'=>$chartSede,
                    'datoGraficoCarreras'=>$chartCarreras,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:institutosTecnicosTecnologicos.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoInstitutos'=>$chartTecnicosTecnologicos,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoSede'=>$chartSede,
                    'datoGraficoCarreras'=>$chartCarreras,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("Efectivos");',
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }  


    /**
     * Reporte Gráfico - Información General - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularChartAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $sesion = $request->getSession();        
        $idUsuario = $this->session->get('userId');

        $tipoReporte = $request->get('tipo');
        $codigoArea = $request->get('codigo');
        $rolUsuario = $request->get('rol');

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario,1);
        $entityEstadistica = $this->buscaEstadisticaAreaRol($codigoArea,$rolUsuario);

        $titulo = "";
        switch ($tipoReporte) {
            case 1:
                $titulo = "Matrícula";
                break;
            case 2:
                $titulo = "Estudiantes Efectivos - Nivel de Estudio";
                break;
            case 3:
                $titulo = "Estudiantes Efectivos - Género";
                break;
            case 4:
                $titulo = "Estudiantes Efectivos - Área Geográfica";
                break;
            case 5:
                $titulo = "Estudiantes Efectivos - Dependencia";
                break;
            default:
                $titulo = "";
                break;
        }

        $entidadGrafico = $this->chartColumnInformacionGeneral($entityEstadistica,$titulo,$gestionProcesada,$tipoReporte);
        
        return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegularGraphic.html.twig', array('datoEntidad'=>$entidad,'datoGrafico'=>$entidadGrafico));                  
    }

    /**
     * Busca el nombre de la entidad en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEntidadRol($codigo,$rol,$ieTipo) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, 0 as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt 
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            "); 


        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            if($ieTipo==1){
                $tipo = "U.E.";
            }elseif($ieTipo == 9){
                $tipo = "I.T.";
            }elseif($ieTipo == 4){
                $tipo = "C.E.E.";
            }
            $queryEntidad = $em->getConnection()->prepare("
                select ie.id as id, '". $tipo .": '|| cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from institucioneducativa as ie
                left join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                where ie.id = ".$codigo."
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select ie.id, ie.id||' - '||ie.institucioneducativa as nombre from usuario_rol as ur
                    inner join usuario as u on u.id = ur.usuario_id
                    inner join persona as p on p.id = u.persona_id
                    inner join maestro_inscripcion as mi on mi.persona_id = p.id
                    inner join institucioneducativa as ie on ie.id = mi.institucioneducativa_id
                    where ur.usuario_id = ".$usuario." and ur.rol_tipo_id = ".$rol ." and mi.gestion_tipo_id = ".$gestionActual." and mi.es_vigente_administrativo = 1
                ");      
            */  
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'DISTRITO: '||lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt 
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 7 and lt.codigo not in ('1000','2000','3000','4000','5000','6000','7000','8000','9000')
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol."
                ");     
            */
        }  

        if($rol == 7) // Tecnico Departamental
        {   
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'Departamento: '||lt.lugar as nombre, ".$rol." as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt 
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 1
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol."
                ");    
            */ 
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt 
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_localidad = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            ");   
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol ."
                ");    
            */
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, departamento_codigo as codigo, departamento as nombre, 7 as rolUsuario
                , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as total_inscrito
                , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                from vm_estudiantes_estadistica_regular 
                where nivel_id in (11,12,13) 
                group by departamento_codigo, departamento
                order by departamento_codigo, departamento
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Unidad Educativa' as nombreArea, institucioneducativa_id as codigo, institucioneducativa_id::varchar||' - '||institucioneducativa  as nombre, 9 as rolUsuario
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as total_inscrito
                    , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) and distrito_codigo = '".$area."'
                    group by institucioneducativa_id, institucioneducativa
                    order by institucioneducativa_id, institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distrito Educativo' as nombreArea, distrito_codigo as codigo, distrito as nombre, 10 as rolUsuario
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as total_inscrito
                    , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) and departamento_codigo = '".$area."'
                    group by distrito_codigo, distrito
                    order by distrito_codigo, distrito
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Departamento' as nombreArea, departamento_codigo as codigo, departamento as nombre, 7 as rolUsuario
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as total_inscrito
                    , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) 
                    group by departamento_codigo, departamento
                    order by departamento_codigo, departamento
                ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return '';
        }
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadRegularInstitucionEducativaAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegularInstitucionEducativa();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, departamento_codigo as codigo, departamento as nombre, 7 as rolUsuario
                , coalesce(count(institucioneducativa_id),0) as cantidad
                from vm_instituciones_educativas 
                where orgcurricular_id = 1 and estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9) 
                group by departamento_codigo, departamento
                order by departamento_codigo, departamento
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Unidad Educativa' as nombreArea, institucioneducativa_id as codigo, institucioneducativa_id::varchar||' - '||institucioneducativa  as nombre, 9 as rolUsuario
                    , coalesce(count(institucioneducativa_id),0) as cantidad
                    from vm_instituciones_educativas 
                    where orgcurricular_id = 1 and estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9) and distrito_codigo = '".$area."'
                    group by institucioneducativa_id, institucioneducativa
                    order by institucioneducativa_id, institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distrito Educativo' as nombreArea, distrito_codigo as codigo, distrito as nombre, 10 as rolUsuario
                    , coalesce(count(institucioneducativa_id),0) as cantidad
                    from vm_instituciones_educativas 
                    where orgcurricular_id = 1 and estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)  and departamento_codigo = '".$area."'
                    group by distrito_codigo, distrito
                    order by distrito_codigo, distrito
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, departamento_codigo as codigo, departamento as nombre, 7 as rolUsuario
                , coalesce(count(institucioneducativa_id),0) as cantidad
                from vm_instituciones_educativas 
                where orgcurricular_id = 1 and estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9) 
                group by departamento_codigo, departamento
                order by departamento_codigo, departamento
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return '';
        }
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadInstitutosTecnicosAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $gestionActual;
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        if($rol == 8 or $rol == 0) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                
            SELECT 'Departamento' as nombreArea, lt4.codigo as codigo, lt4.lugar as nombre, 7 as rolUsuario, coalesce(count(inst.id),0) as cantidad
            FROM institucioneducativa AS inst
            INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
            WHERE inst.institucioneducativa_tipo_id IN (7,8,9) 
            AND inst.estadoinstitucion_tipo_id = 10
            AND inst.institucioneducativa_acreditacion_tipo_id = 2
            group by lt4.codigo, lt4.lugar
            order by lt4.codigo, lt4.lugar
            ");    
        }
        
        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            select 'Instituto Tecnico y/o Tecnologico' as nombreArea, inst.id as codigo, inst.id::varchar||' - '||inst.institucioneducativa  as nombre, 9 as rolUsuario , coalesce(count(inst.id),0) as cantidad
            FROM institucioneducativa AS inst
            INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
            WHERE inst.institucioneducativa_tipo_id IN (7,8,9) 
            AND inst.estadoinstitucion_tipo_id = 10
            AND inst.institucioneducativa_acreditacion_tipo_id = 2
            AND lt4.codigo = '". $area ."'
            group by inst.id, institucioneducativa
            order by inst.id, institucioneducativa");  
        } 
        
        if($rol == 9) // Director o Administrativo
        {   
            $queryEntidad = $em->getConnection()->prepare("
            select 'Instituto Tecnico y/o Tecnologico' as nombreArea, inst.id as codigo, inst.id::varchar||' - '||inst.institucioneducativa  as nombre, 9 as rolUsuario, 1 as cantidad
            FROM institucioneducativa AS inst
            WHERE inst.id = ". $area);
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
        }  

        

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0){
            return $objEntidad;
        } else {
            return '';
        }
    }

    /**
     * Busca el detalle de estudiantes en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 
                coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial
                , coalesce(sum(case when nivel_id = 11 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_1
                , coalesce(sum(case when nivel_id = 11 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_2
                , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria
                , coalesce(sum(case when nivel_id = 12 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_1
                , coalesce(sum(case when nivel_id = 12 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_2
                , coalesce(sum(case when nivel_id = 12 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_3
                , coalesce(sum(case when nivel_id = 12 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_4
                , coalesce(sum(case when nivel_id = 12 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_5
                , coalesce(sum(case when nivel_id = 12 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_6
                , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria
                , coalesce(sum(case when nivel_id = 13 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_1
                , coalesce(sum(case when nivel_id = 13 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_2
                , coalesce(sum(case when nivel_id = 13 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_3
                , coalesce(sum(case when nivel_id = 13 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_4
                , coalesce(sum(case when nivel_id = 13 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_5
                , coalesce(sum(case when nivel_id = 13 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_6
                , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_masculino
                , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_femenino
                , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_urbano
                , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_rural
                , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_publica
                , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_convenio
                , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_privada
                , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_efectivo
                , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                , coalesce(sum(cantidad),0) as total_inscrito
                from vm_estudiantes_estadistica_regular 
                where nivel_id in (11,12,13) 
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_1
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_2
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_1
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_2
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_3
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_4
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_5
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_6
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_1
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_2
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_3
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_4
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_5
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_6
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) and institucioneducativa_id = ".$area." 
                ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_1
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_2
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_1
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_2
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_3
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_4
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_5
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_6
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_1
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_2
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_3
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_4
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_5
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_6
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) and distrito_codigo = '".$area."' 
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_1
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_2
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_1
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_2
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_3
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_4
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_5
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_6
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_1
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_2
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_3
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_4
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_5
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_6
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13) and departamento_codigo = '".$area."' 
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_1
                    , coalesce(sum(case when nivel_id = 11 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_inicial_2
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_1
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_2
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_3
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_4
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_5
                    , coalesce(sum(case when nivel_id = 12 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_primaria_6
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_1
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_2
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_3
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 4 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_4
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 5 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_5
                    , coalesce(sum(case when nivel_id = 13 and grado_id = 6 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_secundaria_6
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,10,11,55) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from vm_estudiantes_estadistica_regular 
                    where nivel_id in (11,12,13)
                ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Busca el detalle de estudiantes en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaRegularInstitucionEducativaAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 
                sum(case dependencia_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
                sum(case dependencia_id when 2 then 1 else 0 end) as cant_convenio,
                sum(case dependencia_id when 3 then 1 else 0 end) as cant_privada,
                sum(case dependencia_id when 1 then 1 when 2 then 1 when 3 then 1 when 5 then 1 else 0 end) as cant_dependencia,
                sum(case niveles_id when '11' then 1 else 0 end) as cant_ini,
                sum(case niveles_id when '11,12' then 1 else 0 end) as cant_ini_pri,
                sum(case niveles_id when '11,13' then 1 else 0 end) as cant_ini_sec,
                sum(case niveles_id when '11,12,13' then 1 else 0 end) as cant_ini_pri_sec,
                sum(case niveles_id when '12' then 1 else 0 end) as cant_pri,
                sum(case niveles_id when '12,13' then 1 else 0 end) as cant_pri_sec,
                sum(case niveles_id when '13' then 1 else 0 end) as cant_sec,
                sum(case niveles_id when '11' then 1 when '12' then 1 when '13' then 1 when '11,12' then 1 when '11,13' then 1 when '11,12,13' then 1 when '12,13' then 1 else 0 end) as cant_nivel,
                count(*) as cant_total
                from vm_instituciones_educativas as vm
                where vm.orgcurricular_id = 1 and vm.estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 
                sum(case dependencia_id when 1 then 1 when 5 then 1  else 0 end) as cant_fiscal,
                sum(case dependencia_id when 2 then 1 else 0 end) as cant_convenio,
                sum(case dependencia_id when 3 then 1 else 0 end) as cant_privada,
                sum(case dependencia_id when 1 then 1 when 2 then 1 when 3 then 1 when 5 then 1 else 0 end) as cant_dependencia,
                sum(case niveles_id when '11' then 1 else 0 end) as cant_ini,
                sum(case niveles_id when '11,12' then 1 else 0 end) as cant_ini_pri,
                sum(case niveles_id when '11,13' then 1 else 0 end) as cant_ini_sec,
                sum(case niveles_id when '11,12,13' then 1 else 0 end) as cant_ini_pri_sec,
                sum(case niveles_id when '12' then 1 else 0 end) as cant_pri,
                sum(case niveles_id when '12,13' then 1 else 0 end) as cant_pri_sec,
                sum(case niveles_id when '13' then 1 else 0 end) as cant_sec,
                sum(case niveles_id when '11' then 1 when '12' then 1 when '13' then 1 when '11,12' then 1 when '11,13' then 1 when '11,12,13' then 1 when '12,13' then 1 else 0 end) as cant_nivel,
                count(*) as cant_total
                from vm_instituciones_educativas as vm
                where vm.orgcurricular_id = 1 and vm.estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)
                and vm.institucioneducativa_id = ".$area." 
            ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 
                sum(case dependencia_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
                sum(case dependencia_id when 2 then 1 else 0 end) as cant_convenio,
                sum(case dependencia_id when 3 then 1 else 0 end) as cant_privada,
                sum(case dependencia_id when 1 then 1 when 2 then 1 when 3 then 1 when 5 then 1 else 0 end) as cant_dependencia,
                sum(case niveles_id when '11' then 1 else 0 end) as cant_ini,
                sum(case niveles_id when '11,12' then 1 else 0 end) as cant_ini_pri,
                sum(case niveles_id when '11,13' then 1 else 0 end) as cant_ini_sec,
                sum(case niveles_id when '11,12,13' then 1 else 0 end) as cant_ini_pri_sec,
                sum(case niveles_id when '12' then 1 else 0 end) as cant_pri,
                sum(case niveles_id when '12,13' then 1 else 0 end) as cant_pri_sec,
                sum(case niveles_id when '13' then 1 else 0 end) as cant_sec,
                sum(case niveles_id when '11' then 1 when '12' then 1 when '13' then 1 when '11,12' then 1 when '11,13' then 1 when '11,12,13' then 1 when '12,13' then 1 else 0 end) as cant_nivel,
                count(*) as cant_total
                from vm_instituciones_educativas as vm
                where vm.orgcurricular_id = 1 and vm.estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)
                and distrito_codigo = '".$area."' 
            ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 
                sum(case dependencia_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
                sum(case dependencia_id when 2 then 1 else 0 end) as cant_convenio,
                sum(case dependencia_id when 3 then 1 else 0 end) as cant_privada,
                sum(case dependencia_id when 1 then 1 when 2 then 1 when 3 then 1 when 5 then 1 else 0 end) as cant_dependencia,
                sum(case niveles_id when '11' then 1 else 0 end) as cant_ini,
                sum(case niveles_id when '11,12' then 1 else 0 end) as cant_ini_pri,
                sum(case niveles_id when '11,13' then 1 else 0 end) as cant_ini_sec,
                sum(case niveles_id when '11,12,13' then 1 else 0 end) as cant_ini_pri_sec,
                sum(case niveles_id when '12' then 1 else 0 end) as cant_pri,
                sum(case niveles_id when '12,13' then 1 else 0 end) as cant_pri_sec,
                sum(case niveles_id when '13' then 1 else 0 end) as cant_sec,
                sum(case niveles_id when '11' then 1 when '12' then 1 when '13' then 1 when '11,12' then 1 when '11,13' then 1 when '11,12,13' then 1 when '12,13' then 1 else 0 end) as cant_nivel,
                count(*) as cant_total
                from vm_instituciones_educativas as vm
                where vm.orgcurricular_id = 1 and vm.estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)
                and departamento_codigo = '".$area."' 
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("   
                select 
                sum(case dependencia_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
                sum(case dependencia_id when 2 then 1 else 0 end) as cant_convenio,
                sum(case dependencia_id when 3 then 1 else 0 end) as cant_privada,
                sum(case dependencia_id when 1 then 1 when 2 then 1 when 3 then 1 when 5 then 1 else 0 end) as cant_dependencia,
                sum(case niveles_id when '11' then 1 else 0 end) as cant_ini,
                sum(case niveles_id when '11,12' then 1 else 0 end) as cant_ini_pri,
                sum(case niveles_id when '11,13' then 1 else 0 end) as cant_ini_sec,
                sum(case niveles_id when '11,12,13' then 1 else 0 end) as cant_ini_pri_sec,
                sum(case niveles_id when '12' then 1 else 0 end) as cant_pri,
                sum(case niveles_id when '12,13' then 1 else 0 end) as cant_pri_sec,
                sum(case niveles_id when '13' then 1 else 0 end) as cant_sec,
                sum(case niveles_id when '11' then 1 when '12' then 1 when '13' then 1 when '11,12' then 1 when '11,13' then 1 when '11,12,13' then 1 when '12,13' then 1 else 0 end) as cant_nivel,
                count(*) as cant_total
                from vm_instituciones_educativas as vm
                where vm.orgcurricular_id = 1 and vm.estadoinstitucion_id = 10 and institucioneducativa_id not in (1,2,3,4,5,6,7,8,9)
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Busca el detalle de institutos en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaInstitutosTecnicosAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $gestionActual;
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        if($rol == 8 or $rol == 0) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("   
            select 
            sum(case ie.dependencia_tipo_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
            sum(case ie.dependencia_tipo_id when 2 then 1 else 0 end) as cant_convenio,
            sum(case ie.dependencia_tipo_id when 3 then 1 else 0 end) as cant_privada,
            sum(case ie.dependencia_tipo_id when 1 then 1 when 2 then 1 when 3 then 1 else 0 end) as cant_dependencia,
            sum(case lt.area2001 when 'R' then 1 else 0 end) as cant_rural,
            sum(case lt.area2001 when 'U' then 1 else 0 end) as cant_urbana,
            sum(case ie.institucioneducativa_tipo_id when 7 then 1 else 0 end) as cant_tecnica,
            sum(case ie.institucioneducativa_tipo_id when 8 then 1 else 0 end) as cant_tecnologica,
            sum(case ie.institucioneducativa_tipo_id when 9 then 1 else 0 end) as cant_tt,
            sum(case ie.institucioneducativa_tipo_id when 7 then 1 when 8 then 1 when 9 then 1 else 0 end) as cant_institutos,count(*) as cant_total,
            sum(case when s.institucioneducativa_id=s.sede then 1 else 0 end) as cant_sede,
            sum(case when s.institucioneducativa_id<>s.sede then 1 else 0 end) as cant_subsede
            FROM ttec_institucioneducativa_sede s
            INNER JOIN institucioneducativa AS ie on s.institucioneducativa_id=ie.id
            INNER JOIN jurisdiccion_geografica AS jurg ON ie.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            WHERE ie.institucioneducativa_tipo_id IN (7,8,9) 
            AND ie.estadoinstitucion_tipo_id = 10
            AND ie.institucioneducativa_acreditacion_tipo_id = 2");    
        } 

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            select 
            sum(case ie.dependencia_tipo_id when 1 then 1 when 5 then 1 else 0 end) as cant_fiscal,
            sum(case ie.dependencia_tipo_id when 2 then 1 else 0 end) as cant_convenio,
            sum(case ie.dependencia_tipo_id when 3 then 1 else 0 end) as cant_privada,
            sum(case ie.dependencia_tipo_id when 1 then 1 when 2 then 1 when 3 then 1 else 0 end) as cant_dependencia,
            sum(case lt.area2001 when 'R' then 1 else 0 end) as cant_rural,
            sum(case lt.area2001 when 'U' then 1 else 0 end) as cant_urbana,
            sum(case ie.institucioneducativa_tipo_id when 7 then 1 else 0 end) as cant_tecnica,
            sum(case ie.institucioneducativa_tipo_id when 8 then 1 else 0 end) as cant_tecnologica,
            sum(case ie.institucioneducativa_tipo_id when 9 then 1 else 0 end) as cant_tt,
            sum(case ie.institucioneducativa_tipo_id when 7 then 1 when 8 then 1 when 9 then 1 else 0 end) as cant_institutos,count(*) as cant_total,
            sum(case when s.institucioneducativa_id=s.sede then 1 else 0 end) as cant_sede,
            sum(case when s.institucioneducativa_id<>s.sede then 1 else 0 end) as cant_subsede
            FROM ttec_institucioneducativa_sede s
            INNER JOIN institucioneducativa AS ie on s.institucioneducativa_id=ie.id
            INNER JOIN jurisdiccion_geografica AS jurg ON ie.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
            WHERE ie.institucioneducativa_tipo_id IN (7,8,9) 
            AND ie.estadoinstitucion_tipo_id = 10
            AND ie.institucioneducativa_acreditacion_tipo_id = 2
            AND lt4.codigo='". $area ."'");  
            } 

        if($rol == 9 ) // Por instituto
        {
            $queryEntidad = $em->getConnection()->prepare("
            SELECT
            sum(case area.id when 200 then 1 else 0 end) as cant_cursos,
            sum(case when area.id <> 200 then 1 else 0 end) as cant_carreras,
            count(*) as cant_total
            FROM ttec_institucioneducativa_carrera_autorizada AS autorizado
            INNER JOIN ttec_carrera_tipo AS carrera ON autorizado.ttec_carrera_tipo_id = carrera.id 
            INNER JOIN institucioneducativa AS instituto ON autorizado.institucioneducativa_id = instituto.id 
            INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id
            WHERE instituto.id = ". $area ."  AND autorizado.es_vigente is true"); 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            
        }  

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Busca el detalle de frecuencia de carreras de institutos en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaCarrerasInstitutosTecnicosAreaRol($area,$rol,$total) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $gestionActual;
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();
        $objEntidad = array();

        if($rol == 8 or $rol == 0) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("   
            select ct.nombre,count(*) as cantidad
            FROM ttec_institucioneducativa_sede s
            inner join institucioneducativa AS ie on s.institucioneducativa_id=ie.id
            INNER JOIN jurisdiccion_geografica AS jurg ON ie.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            inner join ttec_institucioneducativa_carrera_autorizada ieca on ie.id=ieca.institucioneducativa_id
            inner join ttec_carrera_tipo ct on ct.id=ieca.ttec_carrera_tipo_id
            WHERE ie.estadoinstitucion_tipo_id = 10
            AND ie.institucioneducativa_acreditacion_tipo_id = 2
            AND ct.ttec_area_formacion_tipo_id <> 200
            group by ct.id,ct.nombre
            order by count(*) desc
            limit 10");    
        } 

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            select ct.nombre,count(*) as cantidad
            FROM ttec_institucioneducativa_sede s
            inner join institucioneducativa AS ie on s.institucioneducativa_id=ie.id
            INNER JOIN jurisdiccion_geografica AS jurg ON ie.le_juridicciongeografica_id = jurg.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
            inner join ttec_institucioneducativa_carrera_autorizada ieca on ie.id=ieca.institucioneducativa_id
            inner join ttec_carrera_tipo ct on ct.id=ieca.ttec_carrera_tipo_id
            WHERE ie.estadoinstitucion_tipo_id = 10
            AND ie.institucioneducativa_acreditacion_tipo_id = 2
            AND ct.ttec_area_formacion_tipo_id <> 200
            AND lt4.codigo='". $area ."'
            group by ct.id,ct.nombre
            order by count(*) desc
            limit 10
            ");  
        } 
        if($rol != 9) // Tecnico Departamental
        {
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll(); 
        }
        if (count($objEntidad)>0){
            foreach($objEntidad as $key=>$value){
                $objEntidad[$key]['total'] = $total;
            }
         
        }

        return $objEntidad;
    }

    /**
     * Busca las unidades educativas en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaUERol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
            , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
            , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
            , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
            , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
            , coalesce(count(institucioneducativa_id),0) as cant_total 
            from (
            select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionProcesada."
            ) as v
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
                    , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
                    , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
                    , coalesce(count(institucioneducativa_id),0) as cant_total 
                    from (
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionProcesada." and institucioneducativa_id = ".$area."
                    ) as v
                ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
                    , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
                    , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
                    , coalesce(count(institucioneducativa_id),0) as cant_total 
                    from (
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionProcesada." and distrito_codigo = '".$area."'
                    ) as v
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
                    , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
                    , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
                    , coalesce(count(institucioneducativa_id),0) as cant_total 
                    from (
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionProcesada." and departamento_codigo = '".$area."'
                    ) as v
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
                , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
                , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
                , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
                , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
                , coalesce(count(institucioneducativa_id),0) as cant_total 
                from (
                select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionProcesada."
                ) as v
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Busca fecha en la cual se realizo la vista materializada
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaFechaVistaMaterializadaRegular($gestion) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select departamento_codigo AS lugar_codigo, upper(departamento) as lugar_nombre, cast(date_part('day',fecha_vista) as integer) as dia, cast(date_part('month',fecha_vista) as integer) as mes
            , case date_part('dow',fecha_vista) when 1 then 'lunes' when 2 then 'martes' when 3 then 'miercoles' when 4 then 'jueves' when 5 then 'viernes' when 6 then 'sabado' when 7 then 'domingo' else '' end as dia_literal
            , case date_part('month',fecha_vista) when 1 then 'enero' when 2 then 'febrero' when 3 then 'marzo' when 4 then 'abril' when 5 then 'mayo' when 6 then 'junio' when 7 then 'julio' when 8 then 'agosto' when 9 then 'septiembre' when 10 then 'octubre' when 11 then 'noviembre' when 12 then 'diciembre' else '' end as mes_literal
            , cast(date_part('year',fecha_vista) as integer) as gestion
            from vm_estudiantes_estadistica_regular limit 1
        "); 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0]['dia'].' de '.$objEntidad[0]['mes_literal'].' de '.$objEntidad[0]['gestion'];
        } else {
            return '';
        }
    }

    /**
     * Busca fecha en la cual se realizo la vista materializada
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaFechaVistaMaterializadaRegularInstitucionEducativa($gestion) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select departamento_codigo AS lugar_codigo, upper(departamento) as lugar_nombre, cast(date_part('day',fecha_vista) as integer) as dia, cast(date_part('month',fecha_vista) as integer) as mes
            , case date_part('dow',fecha_vista) when 1 then 'lunes' when 2 then 'martes' when 3 then 'miercoles' when 4 then 'jueves' when 5 then 'viernes' when 6 then 'sabado' when 7 then 'domingo' else '' end as dia_literal
            , case date_part('month',fecha_vista) when 1 then 'enero' when 2 then 'febrero' when 3 then 'marzo' when 4 then 'abril' when 5 then 'mayo' when 6 then 'junio' when 7 then 'julio' when 8 then 'agosto' when 9 then 'septiembre' when 10 then 'octubre' when 11 then 'noviembre' when 12 then 'diciembre' else '' end as mes_literal
            , cast(date_part('year',fecha_vista) as integer) as gestion
            from vm_instituciones_educativas limit 1
        "); 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0]['dia'].' de '.$objEntidad[0]['mes_literal'].' de '.$objEntidad[0]['gestion'];
        } else {
            return '';
        }
    }


    /**
     * Busca gestion a la cual pertenece la informacion de la vista materializada
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaGestionVistaMaterializadaRegular() {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select cast(gestion as integer) as gestion
            from vm_estudiantes_estadistica_regular limit 1
        "); 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0]['gestion'];
        } else {
            return 0;
        }
    }

    /**
     * Busca gestion a la cual pertenece la informacion de la vista materializada
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaGestionVistaMaterializadaRegularInstitucionEducativa() {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select date_part('year', fecha_vista) as gestion from vm_instituciones_educativas limit 1
        "); 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad[0]['gestion'];
        } else {
            return 0;
        }
    }


    /**
     * Busca las edificios educativos en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaEERol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$gestionActual = date_format($fechaActual,'Y');
        $gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano, coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_urbano, coalesce(count(edificioeducativo_id),0) as cant_total from (
            select distinct vm.area, ie.le_juridicciongeografica_id as edificioeducativo_id
            from vm_estudiantes_estadistica_regular as vm
            inner join institucioneducativa as ie on ie.id = vm.institucioneducativa_id
            where vm.gestion = ".$gestionActual."
            ) as v
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano, coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_urbano, coalesce(count(edificioeducativo_id),0) as cant_total from (
                    select distinct vm.area, ie.le_juridicciongeografica_id as edificioeducativo_id
                    from vm_estudiantes_estadistica_regular as vm
                    inner join institucioneducativa as ie on ie.id = vm.institucioneducativa_id
                    where vm.gestion = ".$gestionActual." and institucioneducativa_id = ".$area."
                    ) as v
                ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano, coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_urbano, coalesce(count(edificioeducativo_id),0) as cant_total from (
                    select distinct vm.area, ie.le_juridicciongeografica_id as edificioeducativo_id
                    from vm_estudiantes_estadistica_regular as vm
                    inner join institucioneducativa as ie on ie.id = vm.institucioneducativa_id
                    where vm.gestion = ".$gestionActual." and distrito_codigo = '".$area."'
                    ) as v
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano, coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_urbano, coalesce(count(edificioeducativo_id),0) as cant_total from (
                    select distinct vm.area, ie.le_juridicciongeografica_id as edificioeducativo_id
                    from vm_estudiantes_estadistica_regular as vm
                    inner join institucioneducativa as ie on ie.id = vm.institucioneducativa_id
                    where vm.gestion = ".$gestionActual." and departamento_codigo = '".$area."'
                    ) as v
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano, coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_urbano, coalesce(count(edificioeducativo_id),0) as cant_total from (
                select distinct vm.area, ie.le_juridicciongeografica_id as edificioeducativo_id
                from vm_estudiantes_estadistica_regular as vm
                inner join institucioneducativa as ie on ie.id = vm.institucioneducativa_id
                where vm.gestion = ".$gestionActual."
                ) as v
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

    /**
     * Funcion que retorna el Reporte Grafico Bar Jquery de tipo Map
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartColumnInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        $format = '{point.label:,.0f} ({point.y:.1f}%)';
        $tituloY = '';
        $valueY = '{value} %';
        switch ($tipoReporte) {
            case 1:
                //$datosTemp = "{name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Retiros', y: ".round(((100*$entity['cant_retiro'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro']."}, {name: 'Retiros Doble Promoción', y: ".round(((100*$entity['cant_retiro_doble_promocion'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_doble_promocion']."}, {name: 'Efectivos', y: ".round(((100*$entity['total_matricula'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['total_matricula']."},";
                $datosTemp = "{name: 'Total Inscritos', y: ".(($entity['total_inscrito']==0)?0:100).", label: '".number_format($entity['total_inscrito'], 0, ',', '.')."'}, {name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros por Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros por Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Efectivos', y: ".round(((100*$entity['cant_efectivo'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_efectivo']."},";
                $pointLabel = "Estudiantes";
                $name = 'Dependencia';
                break;
            case 2:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Inicial', y: ".round(((100*$entity['cant_inicial'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_inicial']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_primaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_primaria']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_secundaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_secundaria']."},";
                $pointLabel = "Estudiantes";
                $name = 'Dependencia';
                break;
            case 3:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Masculino', y: ".round(((100*$entity['cant_masculino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['cant_femenino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_femenino']."},";
                $pointLabel = "Estudiantes";
                $name = 'Dependencia';
                break;
            case 4:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Urbano', y: ".round(((100*$entity['cant_urbano'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_urbano']."}, {name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_rural']."},";
                $pointLabel = "Estudiantes";
                $name = 'Dependencia';
                break;
            case 5:
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                $pointLabel = "Estudiantes";
                $name = 'Dependencia';
                break;
            case 11:                
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_fiscal'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_fiscal']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_privada']."},";
                $pointLabel = "Unidades Educativas";
                $name = 'Dependencia';
                break;
            case 12:                
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_fiscal'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_fiscal']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_dependencia']==0) ? 1:$entity['cant_dependencia'])),1).", label: ".$entity['cant_privada']."},";
                $pointLabel = "Institutos Técnicos y/o Tecnológicos";
                $name = 'Dependencia';
                break;
            case 13:
                $datosTemp = "";
                foreach($entity as $dato){
                    $datosTemp = $datosTemp . "{name: '". $dato['nombre'] ."', y: " . $dato['cantidad'] . ", label: ".$dato['cantidad']."},";    
                }
                $pointLabel = "Institutos";
                $name = 'Carrera:';
                $format = '{point.label:,.0f}';
                $tituloY = 'Institutos Técnicos y/o Tecnológicos';
                $valueY = '{value}';
                break;
            case 14:
                $datosTemp = "";
                foreach($entity as $dato){
                    $datosTemp = $datosTemp . "{name: '". $dato['detalle'] ."', y: ".round(((100*$dato['cantidad'])/(($dato['total']==0) ? 1:$dato['total'])),1).", label: ".$dato['cantidad']."},";    
                }
                $pointLabel = "Centros";
                $name = 'Dependencia';
                break;
            case 15:
                $datosTemp = "";
                foreach($entity as $dato){
                    $datosTemp = $datosTemp . "{name: '". $dato['detalle'] ."', y: ".$dato['cantidad'].", label: ".$dato['cantidad']."},";    
                }
                $pointLabel = "Centros";
                $name = 'Área de Atención';
                $format = '{point.label:,.0f}';
                $valueY = '{value}';
                break;
            default:
                $datosTemp = "";
                break;
        }        

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    xAxis: {
                        type: 'category',
                    },
                    yAxis: {
                        title: {
                            text: '". $tituloY ."'
                        },
                        labels: {
                            format: '". $valueY ."'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '". $format ."'
                            }
                        }
                    },        
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel." </b> del total<br/>'
                    },

                    series: [{
                        name: '". $name ."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";        
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Pie Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartPieInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        switch ($tipoReporte) {
            case 1:
                $datosTemp = "{name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Retiros', y: ".round(((100*$entity['cant_retiro'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro']."}, {name: 'Retiros Doble Promoción', y: ".round(((100*$entity['cant_retiro_doble_promocion'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_doble_promocion']."}, {name: 'Efectivos', y: ".round(((100*$entity['cant_efectivo'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_efectivo']."},";
                break;
            case 2:
                $datosTemp = "{name: 'Inicial', y: ".round(((100*$entity['cant_inicial'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_inicial']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_primaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_primaria']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_secundaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_secundaria']."},";
                break;
            case 3:
                $datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['cant_masculino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['cant_femenino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_femenino']."},";
                break;
            case 4:
                $datosTemp = "{name: 'Urbano', y: ".round(((100*$entity['cant_urbano'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_urbano']."}, {name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_rural']."},";
                break;
            case 5:
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }        

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} Estudiantes</b> del total<br/>'
                    },
                    series: [{
                        name: 'Sexo',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }


    /**
     * Funcion que retorna el Reporte Grafico Donut 3d Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartDonut3dInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        $nameseries = 'Nivel de Estudio';
        switch ($tipoReporte) {
            case 1:
                $datosTemp = "{name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Retiros', y: ".round(((100*$entity['cant_retiro'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro']."}, {name: 'Retiros Doble Promoción', y: ".round(((100*$entity['cant_retiro_doble_promocion'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_doble_promocion']."}, {name: 'Efectivos', y: ".round(((100*$entity['cant_efectivo'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_efectivo']."},";
                $pointLabel = "Estudiantes";
                break;
            case 2:
                $datosTemp = "{name: 'Inicial', y: ".round(((100*$entity['cant_inicial'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_inicial']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_primaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_primaria']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_secundaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_secundaria']."},";
                $pointLabel = "Estudiantes";
                break;
            case 3:
                $datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['cant_masculino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['cant_femenino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_femenino']."},";
                $pointLabel = "Estudiantes";
                break;
            case 4:
                $datosTemp = "{name: 'Urbano', y: ".round(((100*$entity['cant_urbano'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_urbano']."}, {name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_rural']."},";
                $pointLabel = "Estudiantes";
                break;
            case 5:
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                $pointLabel = "Estudiantes";
                break;
            case 11:
                $datosTemp = "{name: 'Inicial', y: ".round(((100*$entity['cant_ini'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_ini']."}, {name: 'Inicial y Primaria', y: ".round(((100*$entity['cant_ini_pri'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_ini_pri']."}, {name: 'Inicial y Secundaria', y: ".round(((100*$entity['cant_ini_sec'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_ini_sec']."}, {name: 'Inicial, Primaria y Secundaria', y: ".round(((100*$entity['cant_ini_pri_sec'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_ini_pri_sec']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_pri'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_pri']."}, {name: 'Primaria y Secundaria', y: ".round(((100*$entity['cant_pri_sec'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_pri_sec']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_sec'])/(($entity['cant_nivel']==0) ? 1:$entity['cant_nivel'])),1).", label: ".$entity['cant_sec']."},";
                $pointLabel = "Unidades Educativas";
                break;
            case 12:
                $datosTemp = "{name: 'Técnicos', y: ".round(((100*$entity['cant_tecnica'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_tecnica']."}, {name: 'Tecnológicos', y: ".round(((100*$entity['cant_tecnologica'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_tecnologica']."}, {name: 'Técnico Tecnológicos', y: ".round(((100*$entity['cant_tt'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_tt']."},";
                $pointLabel = "Institutos";
                $nameseries = 'Técnicos y/o Tecnológicos';
                break;
            case 13:
                $datosTemp = "{name: 'Carreras', y: ".round(((100*$entity['cant_carreras'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_carreras']."}, {name: 'Cursos', y: ".round(((100*$entity['cant_cursos'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_cursos']."},";
                $pointLabel = "";
                $nameseries = 'Carreras y/o Cursos';
                break;
            case 14:
                $datosTemp = "";
                foreach($entity as $e){
                    $datosTemp = $datosTemp ."{name: '". $e['detalle'] ."', y: ".round(((100*$e['cantidad'])/(($e['total']==0) ? 1:$e['total'])),1).", label: ".$e['cantidad']."},";    
                }
                $pointLabel = "Centros";
                $nameseries = 'Ámbito de Educación';
                break;
            default:
                $datosTemp = "";
                $pointLabel = "";
                break;
        }      

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'pie',
                        options3d: {
                            enabled: true,
                            alpha: 45
                        }
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            innerSize: 100,
                            depth: 45,
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        shared: true,
                        useHTML: true,
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel."</b> del total<br/>'
                    },
                    series: [{
                        name: '". $nameseries ."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]


                });
            }
        ";   
        return $datos;
    }


    /**
     * Funcion que retorna el Reporte Grafico Donut Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartDonutInformacionGeneralNivelGrado($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {     
        $ini = $entity["cant_inicial"];
        $ini_1 = $entity["cant_inicial_1"];
        $ini_2 = $entity["cant_inicial_2"];
        $pri = $entity["cant_primaria"];
        $pri_1 = $entity["cant_primaria_1"];
        $pri_2 = $entity["cant_primaria_2"];
        $pri_3 = $entity["cant_primaria_3"];
        $pri_4 = $entity["cant_primaria_4"];
        $pri_5 = $entity["cant_primaria_5"];
        $pri_6 = $entity["cant_primaria_6"];
        $sec = $entity["cant_secundaria"];
        $sec_1 = $entity["cant_secundaria_1"];
        $sec_2 = $entity["cant_secundaria_2"];
        $sec_3 = $entity["cant_secundaria_3"];
        $sec_4 = $entity["cant_secundaria_4"];
        $sec_5 = $entity["cant_secundaria_5"];
        $sec_6 = $entity["cant_secundaria_6"];
        $total = $ini+$pri+$sec;

        $ini_p_1 = round(($ini_1*100)/($total), 1);   
        $ini_p_2 = round(($ini_2*100)/($total), 1);
        $pri_p_1 = round(($pri_1*100)/($total), 1);
        $pri_p_2 = round(($pri_2*100)/($total), 1);
        $pri_p_3 = round(($pri_3*100)/($total), 1);
        $pri_p_4 = round(($pri_4*100)/($total), 1);
        $pri_p_5 = round(($pri_5*100)/($total), 1);
        $pri_p_6 = round(($pri_6*100)/($total), 1);
        $sec_p_1 = round(($sec_1*100)/($total), 1);
        $sec_p_2 = round(($sec_2*100)/($total), 1);
        $sec_p_3 = round(($sec_3*100)/($total), 1);
        $sec_p_4 = round(($sec_4*100)/($total), 1);
        $sec_p_5 = round(($sec_5*100)/($total), 1);
        $sec_p_6 = round(($sec_6*100)/($total), 1);

        $ini_p = $ini_p_1+$ini_p_2;
        $pri_p = $pri_p_1+$pri_p_2+$pri_p_3+$pri_p_4+$pri_p_5+$pri_p_6;
        $sec_p = $sec_p_1+$sec_p_2+$sec_p_3+$sec_p_4+$sec_p_5+$sec_p_6;

        $datos = "  
            var colors = Highcharts.getOptions().colors,
            categories = ['Inicial', 'Primaria', 'Secundaria'],
            data1 = [{
                y: ".$ini.",
                color: colors[0],
                drilldown: {
                    name: 'Inicial en Familia Comunitaria',
                    categories: ['1° - ".$ini_1."', '2° - ".$ini_2."'],
                    data: [".$ini_p_1.", ".$ini_p_2."],
                    color: colors[0]
                }
            }, {
                y: ".$pri.",
                color: colors[1],
                drilldown: {
                    name: 'Primaria Comunitaria Vocacional',
                    categories: ['1° - ".$pri_1."', '2° - ".$pri_2."', '3° - ".$pri_3."', '4° - ".$pri_4."', '5° - ".$pri_5."', '6° - ".$pri_6."'],
                    data: [".$pri_p_1.", ".$pri_p_2.", ".$pri_p_3.", ".$pri_p_4.", ".$pri_p_5.", ".$pri_p_6."],
                    color: colors[1]
                }
            }, {
                y: ".$sec.",
                color: colors[2],
                drilldown: {
                    name: 'Secundaria Comunitaria Productiva',
                    categories: ['1° - ".$sec_1."', '2° - ".$sec_2."', '3° - ".$sec_3."', '4° - ".$sec_4."', '5° - ".$sec_5."', '6° - ".$sec_6."'],
                    data: [".$sec_p_1.", ".$sec_p_2.", ".$sec_p_3.", ".$sec_p_4.", ".$sec_p_5.", ".$sec_p_6."],
                    color: colors[2]
                }
            }],
            data = [{
                y: ".$ini.",
                color: colors[0],
                drilldown: {
                    name: 'Inicial en Familia Comunitaria',
                    categories: [".$ini_1.", ".$ini_2."],
                    data: [".$ini_p_1.", ".$ini_p_2."],
                    color: colors[0]
                }
            }, {
                y: ".$pri.",
                color: colors[1],
                drilldown: {
                    name: 'Primaria Comunitaria Vocacional',
                    categories: [".$pri_1.", ".$pri_2.", ".$pri_3.", ".$pri_4.", ".$pri_5.", ".$pri_6."],
                    data: [".$pri_p_1.", ".$pri_p_2.", ".$pri_p_3.", ".$pri_p_4.", ".$pri_p_5.", ".$pri_p_6."],
                    color: colors[1]
                }
            }, {
                y: ".$sec.",
                color: colors[2],
                drilldown: {
                    name: 'Secundaria Comunitaria Productiva',
                    categories: [".$sec_1.", ".$sec_2.", ".$sec_3.", ".$sec_4.", ".$sec_5.", ".$sec_6."],
                    data: [".$sec_p_1.", ".$sec_p_2.", ".$sec_p_3.", ".$sec_p_4.", ".$sec_p_5.", ".$sec_p_6."],
                    color: colors[2]
                }
            }],
            nivelData = [],
            gradoData = [],
            i,
            j,
            dataLen = data.length,
            drillDataLen,
            brightness;

            for (i = 0; i < dataLen; i += 1) {

                nivelData.push({
                    name: categories[i],
                    y: data[i].y,
                    label: data[i].y,
                    color: data[i].color
                });

                drillDataLen = data[i].drilldown.data.length;
                for (j = 0; j < drillDataLen; j += 1) {
                    brightness = 0.2 - (j / drillDataLen) / 5;
                    gradoData.push({
                        label: data[i].drilldown.categories[j],
                        y: data[i].drilldown.data[j],
                        name: j+1+'°',
                        color: Highcharts.Color(data[i].color).brighten(brightness).get()
                    });
                }
            }

            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({                    
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    plotOptions: {
                        pie: {
                            shadow: false,
                            center: ['50%', '50%']
                        }
                    },
                    tooltip: {
                        //valueSuffix: '%',
                        shared: true,
                        useHTML: true,
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} Estudiantes</b> del total<br/>'
                    },
                    series: [{
                        name: 'Nivel de Estudio',
                        data: nivelData,
                        size: '60%',
                        dataLabels: {
                            formatter: function () {
                                return this.y > 0 ? this.point.name : null;
                            },
                            color: '#ffffff',
                            distance: -30
                        }
                    }, {
                        name: 'Año de Escolaridad',
                        data: gradoData,
                        size: '80%',
                        innerSize: '60%',
                        dataLabels: {
                            formatter: function () {
                                return this.y > 0 ? '' + this.point.name + ' - ' + (this.point.label).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.') + ' (' + this.y + '%)' : null;
                            }
                        }
                    }]

                });
            }
        ";   
        return $datos;
    }



    /**
     * Funcion que retorna el Reporte Grafico Pyramid Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartPyramidInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        switch ($tipoReporte) {
            case 1:
                $datosTemp = "{name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Retiros', y: ".round(((100*$entity['cant_retiro'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro']."}, {name: 'Retiros Doble Promoción', y: ".round(((100*$entity['cant_retiro_doble_promocion'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_doble_promocion']."}, {name: 'Efectivos', y: ".round(((100*$entity['cant_efectivo'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_efectivo']."},";
                break;
            case 2:
                $datosTemp = "{name: 'Inicial', y: ".round(((100*$entity['cant_inicial'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_inicial']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_primaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_primaria']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_secundaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_secundaria']."},";
                break;
            case 3:
                $datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['cant_masculino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['cant_femenino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_femenino']."},";
                break;
            case 4:
                $datosTemp = "{name: 'Urbano', y: ".round(((100*$entity['cant_urbano'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_urbano']."}, {name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_rural']."},";
                break;
            case 5:
                $datosTemp = "{name: 'Fiscal o Estatal', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }      

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'pyramid',
                        marginRight: 100
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: { 
                            innerSize: 100, 
                            depth: 45, 
                            allowPointSelect: true, 
                            cursor: 'pointer', 
                            dataLabels: { 
                                enabled: true, 
                                format: '{point.name}: {point.percentage:.1f}% - {point.label}', 
                                style: { color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black' } 
                            } 
                        } 
                    },
                    series: [{
                        name: 'Estudiantes',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }


    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm.rptdesign&__format=pdf&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Distrital_v2_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Departamental_v2_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularPrintPdfTempAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'aMinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm_temp.rptdesign&__format=pdf&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Distrital_v2_rcm_temp.rptdesign&__format=pdf&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Departamental_v2_rcm_temp.rptdesign&__format=pdf&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm_temp.rptdesign&__format=pdf&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    /**
     * Imprime reportes estadisticos valoracion cuantitativa - en desarrollo segun el tipo de rol en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularValoracionCuantitativaEnDesarrolloPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=pdf&codges='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {        
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_institucional_v1.rptdesign&__format=pdf&codges='.$gestion.'&codsie='.$codigoArea));       
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_distrital_v1.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_departamental_v1.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=pdf&codges='.$gestion));
        } 
        

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }





    /**
     * Imprime reportes estadisticos valoracion cuantitativa - en desarrollo segun el tipo de rol en formato PDF
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularValoracionCuantitativaEnDesarrolloPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=xls&codges='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {        
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_institucional_v1.rptdesign&__format=xls&codges='.$gestion.'&codsie='.$codigoArea));       
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_distrital_v1.rptdesign&__format=xls&codges='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_departamental_v1.rptdesign&__format=xls&codges='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=xls&codges='.$gestion));
        } 
        

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato EXCEL
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm.rptdesign&__format=xls&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Distrital_v2_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Departamental_v2_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionEstadistica_Nacional_v2_rcm.rptdesign&__format=xls&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reporte listado de instituciones educativas en formato EXCEL
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularInstitucionEducativaListaPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_InstitucionesEducativas_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionInstitucionEducativa_v1_rcm.rptdesign&__format=xls'));  
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

/**
     * Imprime reporte listado de institutos tecnicos en formato EXCEL
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function informacionGeneralInstitutosTecnicosListaPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_InstitutosTecnicosTecnologicos_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionInstitutosTecnicos_v1_pvc.rptdesign&&__format=xls&codigo='.$codigoArea));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reporte listado de institutos tecnicos en formato PDF
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function informacionGeneralInstitutosTecnicosListaPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_InstitutosTecnicosTecnologicos_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionInstitutosTecnicos_v1_pvc.rptdesign&&__format=pdf&codigo='.$codigoArea));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    //    ================================================================================================================================
//    ALTERNATIVA
//    ================================================================================================================================
    public function alternativaAction(Request $request){
        echo 'prueba';
    }

    /**
     * build the login interface
     * @param Request $request
     * @return type
     */
    public function reporteLogoutAction(Request $request) {
        $sesion = $request->getSession();
        $sesion->clear();
        return $this->redirectToRoute('reporte_regular_index');
    }

    
    /**
     * Pagina de Subida de Archivos - Nacional - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function subidaArchivosRegularAction(Request $request) {    
  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');

            if ($form) {
                $codigoArea = $form['codigo'];
                $rolUsuario = $form['rol'];
                $gestion = $form['gestion'];
            }  else {   
                $codigoArea = $request->get('codigo');
                $rolUsuario = $request->get('rol');
                $gestion = $request->get('gestion');
            }
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
            $gestion = $gestionActual;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $em = $this->getDoctrine()->getManager();
        $entityGestionSelecionado = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion));


        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario,1);
        $subEntidades = $this->buscaSubEntidadRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaSubidaArchivosAreaRol($codigoArea,$rolUsuario,$gestion);

//        print_r($entidad);
 //       die();
        
        $chartSubidaArchivo = $this->chartColumnSubidaArchivos($entityEstadistica,"Unidades educativas por departamento y bimestre",$gestionActual,1,"chartContainer");

        if ( $rolUsuario == 10 or $rolUsuario == 11 ){
            return $this->render('SieAppWebBundle:Reporte:subidaArchivosRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoEstadistica'=>$entityEstadistica, 
                'chartSubidaArchivo'=>$chartSubidaArchivo, 
                'rol'=>$rolUsuario,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
                'gestion'=>$gestion,
                'fechaEstadisticaRegular'=>$fechaActual,
                'form' => $defaultController->createLoginForm()->createView(),
                'formGestion' => $this->creaFormularioGestion('reporte_regular_subida_archivos', $entityGestionSelecionado, $rolUsuario, $codigoArea)->createView()
            ));
        } else {
            return $this->render('SieAppWebBundle:Reporte:subidaArchivosRegular.html.twig', array(
            'infoEntidad'=>$entidad,
            'infoSubEntidad'=>$subEntidades, 
            'infoEstadistica'=>$entityEstadistica, 
            'chartSubidaArchivo'=>$chartSubidaArchivo, 
            'rol'=>$rolUsuario,
            'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
            'gestion'=>$gestion,
            'fechaEstadisticaRegular'=>$fechaActual,
            'form' => $defaultController->createLoginForm()->createView(),
            'formGestion' => $this->creaFormularioGestion('reporte_regular_subida_archivos', $entityGestionSelecionado, $rolUsuario, $codigoArea)->createView()
        ));
        }
    }

    private function creaFormularioGestion($routing, $entityGestion, $rol, $area) {

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('gestion', 'entity', array('data' => $entityGestion, 'attr' => array('class' => 'input-sm mb-15'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > :codGestion')
                                ->setParameter('codGestion', '2015')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('rol', 'hidden', array('attr' => array('value' => $rol)))
                ->add('codigo', 'hidden', array('attr' => array('value' => $area)))
                ->getForm();
        return $form;
    }

    private function getLinkEncript($datos){
        //$link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript('162210372807304672016087807304672016111120');
        //die($link);
        $codes = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';

        // Encriptamos los datos
        $result = "";
        $a = 0;
        $b = 0;
        for($i=0;$i<strlen($datos);$i++){
          //$x = strpos($codes, $datos[$i]) ;
          $x = ord($datos[$i]) ;
          $b = $b * 256 + $x;
          $a = $a + 8;

          while ( $a >= 6) {
              $a = $a - 6;
              $x = floor($b/(1 << $a));
              $b = $b % (1 << $a);
              $result = $result.''.substr($codes, $x,1);
          }
        }
        if($a > 0){
          $x = $b << (6 - $a);
          $result = $result.''.substr($codes, $x,1);
        }
        return $result;
    }

    /**
     * Busca el detalle de archivos subidos en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubidaArchivosAreaRol($area,$rol,$gestion) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        //$gestionActual = date_format($fechaActual,'Y');
        $gestionActual = $gestion;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id <= ".$gestionActual." order by id desc limit 2)
                select dt.id as lugar_codigo, dt.departamento as lugar_nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
                , sum(case when ufc.b0 >= 1 then 1 else 0 end) as si_b0
                , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as no_b0
                , sum(case when ufc.b1 >= 1 then 1 else 0 end) as si_b1
                , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as no_b1
                , sum(case when ufc.b2 >= 1 then 1 else 0 end) as si_b2
                , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as no_b2
                , sum(case when ufc.b3 >= 1 then 1 else 0 end) as si_b3
                , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as no_b3
                , sum(case when ufc.b4 >= 1 then 1 else 0 end) as si_b4
                , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as no_b4
                from institucioneducativa as ie
                --inner join (select distinct institucioneducativa_id from institucioneducativa_curso where gestion_tipo_id in (select gestion_int from gestion_actual) and nivel_tipo_id in (11,12,13) and grado_tipo_id != 0) as iec on iec.institucioneducativa_id = ie.id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join (select lugar_tipo.id,lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                inner join departamento_tipo as dt on dt.id = cast(left(dis.codigo,1) as integer)
                left join (
                    select ufc.cod_ue
                    , sum(case when ufc.bimestre = 0 or exists(select * from registro_consolidacion as rc0 where rc0.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc0.unidad_educativa = ufc.cod_ue) then 1 else 0 end) as b0
                    , sum(case when ufc.bimestre = 1 or exists(select * from registro_consolidacion as rc1 where rc1.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc1.unidad_educativa = ufc.cod_ue and rc1.bim1 >= 1) then 1 else 0 end) as b1
                    , sum(case when ufc.bimestre = 2 or exists(select * from registro_consolidacion as rc2 where rc2.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc2.unidad_educativa = ufc.cod_ue and rc2.bim2 >= 1) then 1 else 0 end) as b2
                    , sum(case when ufc.bimestre = 3 or exists(select * from registro_consolidacion as rc3 where rc3.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc3.unidad_educativa = ufc.cod_ue and rc3.bim3 >= 1) then 1 else 0 end) as b3
                    , sum(case when ufc.bimestre = 4 or exists(select * from registro_consolidacion as rc4 where rc4.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc4.unidad_educativa = ufc.cod_ue and rc4.bim4 >= 1) then 1 else 0 end) as b4 
                    from upload_file_control as ufc
                    where ufc.gestion in (select max(gestion_str) as gestion_str from gestion_actual) group by ufc.cod_ue
                    union all
                    select rc.unidad_educativa as cod_ue, 1 as b0, rc.bim1 as b1, rc.bim2 as b2, rc.bim3 as b3, rc.bim4 as b4 from registro_consolidacion as rc where rc.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and not exists (select * from upload_file_control as ufc1 where ufc1.cod_ue = rc.unidad_educativa)
                ) as ufc on ufc.cod_ue = ie.id --and estado_file = 't'
                where ie.orgcurricular_tipo_id = 1 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 
                and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)
                group by dt.id, dt.departamento--, dis.codigo, dis.lugar
                order by dt.id, dt.departamento
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("                    
                ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id <= ".$gestionActual." order by id desc limit 2)
                    select ie.id as lugar_codigo, ie.institucioneducativa as lugar_nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
                    , sum(case when ufc.b0 >= 1 then 1 else 0 end) as si_b0
                    , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as no_b0
                    , sum(case when ufc.b1 >= 1 then 1 else 0 end) as si_b1
                    , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as no_b1
                    , sum(case when ufc.b2 >= 1 then 1 else 0 end) as si_b2
                    , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as no_b2
                    , sum(case when ufc.b3 >= 1 then 1 else 0 end) as si_b3
                    , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as no_b3
                    , sum(case when ufc.b4 >= 1 then 1 else 0 end) as si_b4
                    , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as no_b4
                    from institucioneducativa as ie
                    --inner join (select distinct institucioneducativa_id from institucioneducativa_curso where gestion_tipo_id in (select gestion_int from gestion_actual) and nivel_tipo_id in (11,12,13) and grado_tipo_id != 0) as iec on iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join (select lugar_tipo.id,lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                    inner join departamento_tipo as dt on dt.id = cast(left(dis.codigo,1) as integer)
                    left join (
                        select ufc.cod_ue
                        , sum(case when ufc.bimestre = 0 or exists(select * from registro_consolidacion as rc0 where rc0.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc0.unidad_educativa = ufc.cod_ue) then 1 else 0 end) as b0
                        , sum(case when ufc.bimestre = 1 or exists(select * from registro_consolidacion as rc1 where rc1.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc1.unidad_educativa = ufc.cod_ue and rc1.bim1 >= 1) then 1 else 0 end) as b1
                        , sum(case when ufc.bimestre = 2 or exists(select * from registro_consolidacion as rc2 where rc2.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc2.unidad_educativa = ufc.cod_ue and rc2.bim2 >= 1) then 1 else 0 end) as b2
                        , sum(case when ufc.bimestre = 3 or exists(select * from registro_consolidacion as rc3 where rc3.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc3.unidad_educativa = ufc.cod_ue and rc3.bim3 >= 1) then 1 else 0 end) as b3
                        , sum(case when ufc.bimestre = 4 or exists(select * from registro_consolidacion as rc4 where rc4.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc4.unidad_educativa = ufc.cod_ue and rc4.bim4 >= 1) then 1 else 0 end) as b4 
                        from upload_file_control as ufc
                        where ufc.gestion in (select max(gestion_str) as gestion_str from gestion_actual) group by ufc.cod_ue
                        union all
                        select rc.unidad_educativa as cod_ue, 1 as b0, rc.bim1 as b1, rc.bim2 as b2, rc.bim3 as b3, rc.bim4 as b4 from registro_consolidacion as rc where rc.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and not exists (select * from upload_file_control as ufc1 where ufc1.cod_ue = rc.unidad_educativa)
                    ) as ufc on ufc.cod_ue = ie.id --and estado_file = 't'
                    where ie.orgcurricular_tipo_id = 1 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 and dis.codigo = '".$area."' 
                    and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)
                    group by ie.id, ie.institucioneducativa
                    order by ie.id, ie.institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id <= ".$gestionActual." order by id desc limit 2)
                    select dis.codigo as lugar_codigo, dis.lugar as lugar_nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
                    , sum(case when ufc.b0 >= 1 then 1 else 0 end) as si_b0
                    , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as no_b0
                    , sum(case when ufc.b1 >= 1 then 1 else 0 end) as si_b1
                    , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as no_b1
                    , sum(case when ufc.b2 >= 1 then 1 else 0 end) as si_b2
                    , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as no_b2
                    , sum(case when ufc.b3 >= 1 then 1 else 0 end) as si_b3
                    , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as no_b3
                    , sum(case when ufc.b4 >= 1 then 1 else 0 end) as si_b4
                    , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as no_b4
                    from institucioneducativa as ie
                    --inner join (select distinct institucioneducativa_id from institucioneducativa_curso where gestion_tipo_id in (select gestion_int from gestion_actual) and nivel_tipo_id in (11,12,13) and grado_tipo_id != 0) as iec on iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join (select lugar_tipo.id,lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                    inner join departamento_tipo as dt on dt.id = cast(left(dis.codigo,1) as integer)
                    left join (
                        select ufc.cod_ue
                        , sum(case when ufc.bimestre = 0 or exists(select * from registro_consolidacion as rc0 where rc0.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc0.unidad_educativa = ufc.cod_ue) then 1 else 0 end) as b0
                        , sum(case when ufc.bimestre = 1 or exists(select * from registro_consolidacion as rc1 where rc1.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc1.unidad_educativa = ufc.cod_ue and rc1.bim1 >= 1) then 1 else 0 end) as b1
                        , sum(case when ufc.bimestre = 2 or exists(select * from registro_consolidacion as rc2 where rc2.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc2.unidad_educativa = ufc.cod_ue and rc2.bim2 >= 1) then 1 else 0 end) as b2
                        , sum(case when ufc.bimestre = 3 or exists(select * from registro_consolidacion as rc3 where rc3.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc3.unidad_educativa = ufc.cod_ue and rc3.bim3 >= 1) then 1 else 0 end) as b3
                        , sum(case when ufc.bimestre = 4 or exists(select * from registro_consolidacion as rc4 where rc4.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc4.unidad_educativa = ufc.cod_ue and rc4.bim4 >= 1) then 1 else 0 end) as b4 
                        from upload_file_control as ufc
                        where ufc.gestion in (select max(gestion_str) as gestion_str from gestion_actual) group by ufc.cod_ue
                        union all
                        select rc.unidad_educativa as cod_ue, 1 as b0, rc.bim1 as b1, rc.bim2 as b2, rc.bim3 as b3, rc.bim4 as b4 from registro_consolidacion as rc where rc.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and not exists (select * from upload_file_control as ufc1 where ufc1.cod_ue = rc.unidad_educativa)
                    ) as ufc on ufc.cod_ue = ie.id --and estado_file = 't'
                    where ie.orgcurricular_tipo_id = 1 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 and dt.id = ".$area." 
                    and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)
                    group by dis.codigo, dis.lugar
                    order by dis.codigo, dis.lugar
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                    with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id <= ".$gestionActual." order by id desc limit 2)
                    select dt.id as lugar_codigo, dt.departamento as lugar_nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
                    , sum(case when ufc.b0 >= 1 then 1 else 0 end) as si_b0
                    , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as no_b0
                    , sum(case when ufc.b1 >= 1 then 1 else 0 end) as si_b1
                    , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as no_b1
                    , sum(case when ufc.b2 >= 1 then 1 else 0 end) as si_b2
                    , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as no_b2
                    , sum(case when ufc.b3 >= 1 then 1 else 0 end) as si_b3
                    , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as no_b3
                    , sum(case when ufc.b4 >= 1 then 1 else 0 end) as si_b4
                    , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as no_b4
                    from institucioneducativa as ie
                    --inner join (select distinct institucioneducativa_id from institucioneducativa_curso where gestion_tipo_id in (select gestion_int from gestion_actual) and nivel_tipo_id in (11,12,13) and grado_tipo_id != 0) as iec on iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join (select lugar_tipo.id,lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                    inner join departamento_tipo as dt on dt.id = cast(left(dis.codigo,1) as integer)
                    left join (
                        select ufc.cod_ue
                        , sum(case when ufc.bimestre = 0 or exists(select * from registro_consolidacion as rc0 where rc0.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc0.unidad_educativa = ufc.cod_ue) then 1 else 0 end) as b0
                        , sum(case when ufc.bimestre = 1 or exists(select * from registro_consolidacion as rc1 where rc1.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc1.unidad_educativa = ufc.cod_ue and rc1.bim1 >= 1) then 1 else 0 end) as b1
                        , sum(case when ufc.bimestre = 2 or exists(select * from registro_consolidacion as rc2 where rc2.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc2.unidad_educativa = ufc.cod_ue and rc2.bim2 >= 1) then 1 else 0 end) as b2
                        , sum(case when ufc.bimestre = 3 or exists(select * from registro_consolidacion as rc3 where rc3.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc3.unidad_educativa = ufc.cod_ue and rc3.bim3 >= 1) then 1 else 0 end) as b3
                        , sum(case when ufc.bimestre = 4 or exists(select * from registro_consolidacion as rc4 where rc4.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and rc4.unidad_educativa = ufc.cod_ue and rc4.bim4 >= 1) then 1 else 0 end) as b4 
                        from upload_file_control as ufc
                        where ufc.gestion in (select max(gestion_str) as gestion_str from gestion_actual) group by ufc.cod_ue
                        union all
                        select rc.unidad_educativa as cod_ue, 1 as b0, rc.bim1 as b1, rc.bim2 as b2, rc.bim3 as b3, rc.bim4 as b4 from registro_consolidacion as rc where rc.gestion in (select max(gestion_int) as gestion_int from gestion_actual) and not exists (select * from upload_file_control as ufc1 where ufc1.cod_ue = rc.unidad_educativa)
                    ) as ufc on ufc.cod_ue = ie.id --and estado_file = 't'
                    where ie.orgcurricular_tipo_id = 1 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 
                    and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)
                    group by dt.id, dt.departamento--, dis.codigo, dis.lugar
                    order by dt.id, dt.departamento
                ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad;
        } else {
            return '';
        }
    }


    /**
     * Funcion que retorna el Reporte Grafico Bar Jquery
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartColumnSubidaArchivos($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        $datosTemp = "";
        switch ($tipoReporte) {
            case 1:
                $si_b0 = "";
                $no_b0 = "";
                $si_b1 = "";
                $no_b1 = "";
                $si_b2 = "";
                $no_b2 = "";
                $si_b3 = "";
                $no_b3 = "";
                $si_b4 = "";
                $no_b4 = "";
                $nombres = "";
                for ($i = 0; $i < count($entity); $i++) {
                    if($nombres == ""){
                        $nombres = "'".$entity[$i]['lugar_nombre']."'";
                        $si_b0 = $entity[$i]['si_b0']; 
                        $no_b0 = $entity[$i]['no_b0'];
                        $si_b1 = $entity[$i]['si_b1'];
                        $no_b1 = $entity[$i]['no_b1'];
                        $si_b2 = $entity[$i]['si_b2'];
                        $no_b2 = $entity[$i]['no_b2'];
                        $si_b3 = $entity[$i]['si_b3'];
                        $no_b3 = $entity[$i]['no_b3'];
                        $si_b4 = $entity[$i]['si_b4'];
                        $no_b4 = $entity[$i]['no_b4'];
                    } else {                        
                        $nombres = $nombres.",'".$entity[$i]['lugar_nombre']."'";
                        $si_b0 = $si_b0.','.$entity[$i]['si_b0']; 
                        $no_b0 = $no_b0.','.$entity[$i]['no_b0'];
                        $si_b1 = $si_b1.','.$entity[$i]['si_b1'];
                        $no_b1 = $no_b1.','.$entity[$i]['no_b1'];
                        $si_b2 = $si_b2.','.$entity[$i]['si_b2'];
                        $no_b2 = $no_b2.','.$entity[$i]['no_b2'];
                        $si_b3 = $si_b3.','.$entity[$i]['si_b3'];
                        $no_b3 = $no_b3.','.$entity[$i]['no_b3'];
                        $si_b4 = $si_b4.','.$entity[$i]['si_b4'];
                        $no_b4 = $no_b4.','.$entity[$i]['no_b4'];
                    }
                }
                $datosTemp = "{name:'No subidos - Inicio',data:[".$no_b0."],stack:'B0',color:'#D6EAF8'},{name:'Subidos - Inicio',data:[".$si_b0."],stack:'B0',color:'#3498DB'},";
                $datosTemp = $datosTemp."{name:'No subidos - B1',data:[".$no_b1."],stack:'B1',color:'#D0ECE7'},{name:'Subidos - B1',data:[".$si_b1."],stack:'B1',color:'#16A085'},";
                $datosTemp = $datosTemp."{name:'No subidos - B2',data:[".$no_b2."],stack:'B2',color:'#D5F5E3'},{name:'Subidos - B2',data:[".$si_b2."],stack:'B2',color:'#2ECC71'},";
                $datosTemp = $datosTemp."{name:'No subidos - B3',data:[".$no_b3."],stack:'B3',color:'#FDEBD0'},{name:'Subidos - B3',data:[".$si_b3."],stack:'B3',color:'#F39C12'},";
                $datosTemp = $datosTemp."{name:'No subidos - B4',data:[".$no_b4."],stack:'B4',color:'#FADBD8'},{name:'Subidos - B4',data:[".$si_b4."],stack:'B4',color:'#E74C3C'},";
                break;
            default:
                $datosTemp = "";
                break;
        }        

        $datos = "   
            Highcharts.chart('".$contenedor."', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    xAxis: {
                        categories:[".$nombres."]
                    },
                    yAxis: {
                        allowDecimals: false,
                        min: 0,
                        title: {
                            text: 'Porcentaje de avance'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        column: {
                            stacking: 'percent'
                        },
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: false,
                                format: '{point.y:.1f}%'
                            }
                        }
                    },        
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;><b>Archivos</b></span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{series.name}</span>: <b>{point.y}</b><br/>', 
                        shared: true
                    },

                    series: [".$datosTemp."]
                });
        ";        
        return $datos;
    }

    /**
     * Pagina Directorio de Unidades Educativas
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function directorioInstitucionEducativaRegularAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $em = $this->getDoctrine()->getManager();
        $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => 0));
        $arrayDependencia = [1,2,5,3];

        //dump($this->creaFormularioBusquedaUnidadEducativa('reporte_regular_directorio_institucioneducativa_busqueda',80551245,$entityDepartamento,array('1'=>false,'2'=>false,'5'=>false,'3'=>false))->createView());
        //die();
        return $this->render('SieAppWebBundle:Reporte:directorioInstitucionEducativaRegular.html.twig', array(
            'infoEntidad'=>'',
            'form' => $defaultController->createLoginForm()->createView(),
            'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_regular_directorio_institucioneducativa_busqueda','',$entityDepartamento, $arrayDependencia)->createView()
        ));
    }

    private function creaFormularioBusquedaUnidadEducativa($routing, $ue, $entityDepartamento, $arrayDependencia) {
        $entityDependencia = ['1'=>'Fiscal o Estatal', '2'=>'Convenio', '5'=>'Comunitaria', '3'=>'Privada'];

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('ue', 'text', array('label' => 'Código SIE o Nombre de U.E.', 'required' => false, 'attr' => array('value' => $ue, 'class' => 'form-control no-border-left', 'placeholder' => 'Código SIE o Nombre de Unidad Educativa', 'style' => 'text-transform:uppercase')))
            ->add('departamento', 'entity', array('label' => 'Departamento.', 'empty_value' => 'Todos', 'data' => $entityDepartamento, 'required' => false, 'attr' => array('class' => 'form-control mb-20'), 'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('dt')
                            ->orderBy('dt.id', 'ASC')
                            ->where('dt.id != :codDepartamento')
                            ->setParameter('codDepartamento', 0);
                },
            ))
            //->add('departamento', 'entity', array('label' => 'Depto.', 'empty_value' => 'Todos', 'data' => $entityDepartamento, 'required' => false, 'attr' => array('class' => 'form-control input-sm mb-15'), 'class' => 'Sie\AppWebBundle\Entity\LugarTipo',
            //    'query_builder' => function(EntityRepository $er) {
            //        return $er->createQueryBuilder('lt')
            //                ->leftJoin('SieAppWebBundle:LugarNivelTipo', 'lnt', 'WITH', 'lnt.id = lt.lugarNivel')
            //                ->where('lnt.id = :codLugarNivelTipo')
            //                ->setParameter('codLugarNivelTipo', 1)
            //                ->orderBy('lt.id', 'ASC');
            //    },
            //))
            ->add('dependencia', 'choice', ['choices' => $entityDependencia, 'data' => $arrayDependencia, 'multiple' => true, 'expanded' => true])
            //->add('chkfiscal', 'checkbox', array('mapped' => false, 'required' => false, 'label' => 'Fiscal o Estatal', 'data' => $dependencia['1']))
            //->add('chkconvenio', 'checkbox', array('mapped' => false, 'required' => false, 'label' => 'Convenio', 'data' => $dependencia['2']))
            //->add('chkcomunitaria', 'checkbox', array('mapped' => false, 'required' => false, 'label' => 'Comunitaria', 'data' => $dependencia['5']))
            //->add('chkprivada', 'checkbox', array('mapped' => false, 'required' => false, 'label' => 'Privada', 'data' => $dependencia['3']))
            ->add('submit', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
        return $form;
    }

    /**
     * Pagina Unidades Educativas en funcion a criterios de busqueda
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function directorioInstitucionEducativaBusquedaRegularAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);


        $request = $this->getRequest();
        $this->route_anterior = $request->get('_route');
        $this->var_anterior = $request->query->all();


        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');

            if ($form) {
                $codigoDepartamento = $form['departamento'];
                if ($codigoDepartamento == "") {
                    $codigoDepartamento = 0;
                }
                $textUnidadEducativa = $form['ue'];
                $dependencia = isset($form['dependencia']) ? $form['dependencia'] : array();
                $dependenciaList = "";
                for($i = 0; $i < count($dependencia); $i++) {
                    if($dependenciaList==""){
                        $dependenciaList = $dependencia[$i];
                    } else {
                        $dependenciaList = $dependenciaList.",".$dependencia[$i];
                    }
                }
                if($dependenciaList==""){
                    $dependenciaList = "1,2,5,3";
                }

                $em = $this->getDoctrine()->getManager();
                $query = $em->getConnection()->prepare("
                    select ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, dis.lugar as distrito, jg.direccion as direccion
                    , (case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                    , pro.lugar as provincia, jg.zona
                    from institucioneducativa as ie
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                    inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                    inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                    inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                    left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
                    where ie.institucioneducativa_acreditacion_tipo_id = 1 
                    and (cast(ie.id as varchar) = replace('".$textUnidadEducativa."',' ','%') or ie.institucioneducativa like '%'||replace(UPPER('".$textUnidadEducativa."'),' ','%')||'%')
                    and (case ".$codigoDepartamento." when 0 then dep.codigo != '0' else dep.codigo = '".$codigoDepartamento."' end)
                    and det.id in (".$dependenciaList.")
                    order by orgcurricular, departamento, estadoinstitucion, seccion
                ");
                $query->execute();
                $entityInstitucionEducativa = $query->fetchAll();

                $infoBusqueda = serialize(array(
                    'unidadeducativa' => $textUnidadEducativa,
                    'departamento' => $codigoDepartamento,
                    'dependencia' => $dependencia
                ));
            }  else {   
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
                return $this->redirectToRoute('reporte_regular_directorio_institucioneducativa');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('reporte_regular_directorio_institucioneducativa');
        }

        $em = $this->getDoctrine()->getManager();
        $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $codigoDepartamento ));


        return $this->render('SieAppWebBundle:Reporte:directorioInstitucionEducativaRegular.html.twig', array(
            'infoEntidad' => '',
            'infoBusqueda' => $infoBusqueda,
            'entityBusqueda'=> $entityInstitucionEducativa,
            'form' => $defaultController->createLoginForm()->createView(),
            'dependencia' => $dependencia,
            'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_regular_directorio_institucioneducativa_busqueda',$textUnidadEducativa, $entityDepartamento, $dependencia)->createView()
        ));
    }


    /**
     * Pagina de Unidade Educativa seleccionada
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function directorioInstitucionEducativaDetalleRegularAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $em = $this->getDoctrine()->getManager();
               
        //return $this->redirect($this->generateUrl($this->route_anterior,$this->var_anterior));

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */            
            $infoBusqueda = $request->get('infoBusqueda');
            $sie = $request->get('sie');
            $ainfoBusqueda = unserialize($infoBusqueda);
            //get the values throght the infoUe
            $unidadEducativaText = $ainfoBusqueda['unidadeducativa'];
            $departamentoId = $ainfoBusqueda['departamento'];
            $dependenciaArrayId = $ainfoBusqueda['dependencia'];
            $dependenciaList = "";
            for($i = 0; $i < count($dependenciaArrayId); $i++) {
                if($dependenciaList==""){
                    $dependenciaList = $dependenciaArrayId[$i];
                } else {
                    $dependenciaList = $dependenciaList.",".$dependenciaArrayId[$i];
                }
            }

            $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoId ));

            $query = $em->getConnection()->prepare("
                select distinct ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, dis.lugar as distrito, jg.direccion as direccion
                , 'Educación '||(case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                , pro.lugar as provincia, jg.zona, c.director, jg.cordx, jg.cordy, loc.area, ien.nivel_autorizado
                , replace(replace(replace(replace(iet.turno, 'M', 'Mañana'), 'T', 'Tarde'), 'N', 'Noche'), '-', ', ') as turno
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
                inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
                left join (select institucioneducativa_id, carnet as ci_director,paterno||' '||materno||' '||nombre as director,cargo_tipo_id as item_director from maestro_inscripcion a 
                    inner join persona b on a.persona_id=b.id 
                    where a.gestion_tipo_id in (select max(gestion_tipo_id) as gestion_tipo_id from maestro_inscripcion where institucioneducativa_id= ".$sie." and cargo_tipo_id in (1,12) and es_vigente_administrativo = 'true') and a.institucioneducativa_id= ".$sie." and cargo_tipo_id in (1,12) and a.es_vigente_administrativo = 'true'
                ) c on ie.id = c.institucioneducativa_id
                left join (
                    select string_agg(distinct nt1.nivel,', ') as nivel_autorizado, institucioneducativa_id
                    from institucioneducativa_nivel_autorizado as iena1
                    left join nivel_tipo as nt1 on nt1.id = iena1.nivel_tipo_id
                    where iena1.institucioneducativa_id = ".$sie."  
                    group by iena1.institucioneducativa_id
                ) as ien on ien.institucioneducativa_id = ie.id
                left join (
                    select string_agg(cast(vv.turno_id as varchar), '-' order by vv.turno_id) as turno_id, string_agg(vv.turno,'-' order by vv.turno_id) as turno, vv.institucioneducativa_id from (
                        select v.turno, case v.turno when 'M' then 1 when 'T' then 2 when 'N' then 3 else 0 end as turno_id, v.institucioneducativa_id from (
                        select unnest(string_to_array(string_agg(distinct case tt1.abrv when 'MTN' then 'M-T-N' when '.' then '' when 'MN' then 'M-N' else tt1.abrv end,'-'),'-','')) as turno, iec1.institucioneducativa_id from institucioneducativa_curso as iec1 
                        inner join estudiante_inscripcion as ei1 on ei1.institucioneducativa_curso_id = iec1.id
                        inner join turno_tipo as tt1 on tt1.id = iec1.turno_tipo_id
                        where iec1.gestion_tipo_id in (select max(iec2.gestion_tipo_id) as gestion_tipo_id from institucioneducativa_curso as iec2 where iec2.institucioneducativa_id = iec1.institucioneducativa_id ) 
                        group by iec1.institucioneducativa_id
                        ) as v
                        group by institucioneducativa_id, turno
                        order by turno_id
                    ) as vv
                    group by institucioneducativa_id
                ) as iet on iet.institucioneducativa_id = ie.id 
                where ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.id = ".$sie." 
                and (case ".$departamentoId." when 0 then dep.codigo != '0' else dep.codigo = '".$departamentoId."' end)
                and det.id in (".$dependenciaList.")            
                order by orgcurricular, departamento, estadoinstitucion, seccion
            ");

            $query->execute();
            $entityInstitucionEducativa = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));            
        }

        return $this->render('SieAppWebBundle:Reporte:directorioInstitucionEducativaDetalleRegular.html.twig', array(
            'infoEntidad'=>'',
            'entityUnidadEducativa'=> $entityInstitucionEducativa,
            'form' => $defaultController->createLoginForm()->createView(),
            'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_regular_directorio_institucioneducativa_busqueda',$unidadEducativaText,$entityDepartamento, $dependenciaArrayId)->createView()
        ));
    }


    /**
     * get pdf
     * @param type $fase
     * return list of pruebas
     */
    public function informacionGeneralJuegosNacionalFasePdfAction($fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $arch = 'Juegos_'.$gestionActual.'_nacional_fase'.($fase-1).'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));


        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_JuegosPlurinacionales_Nacional_v1_rcm.rptdesign&__format=pdf&fase='.$fase.'&gestion='.$gestionActual));
        } else {
            $response->setContent("<html><head><title>Error</title></head><body></body></html>");
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function reporteOlimUEAction(Request $request) {
        $form = $request->get('reporteOlim');
        $sie = intval($form['sie']);
        $gestion = intval($form['gestion']);
        $tutor = 0;
        if(isset($form['tutor'])){
            $tutor = intval($form['tutor']);
            if($tutor == '' or $tutor == null){ $tutor = 0; }
        }
        $arch = 'Reporte_Olimpiada_UE'. $sie .'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_lst_Estudiantes_Participaciones_f1_v1.rptdesign&codue='. $sie .'&codges=' .$gestion. '&codtutor='.$tutor.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Pagina Inicial - Información General - Nacional - Educacion Especial
     * rcanaviri
     * @param Request $request
     * @return type
     */
    public function especialIndexAction(Request $request) {
        
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $gestionActual = date_format($fechaActual,'Y');
        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        
        $gestionProcesada = $gestionActual;

        $codigo = 0;
		$nivel = 0;	

		if ($request->isMethod('POST')) {
            $codigo = base64_decode($request->get('codigo'));
			$rol = $request->get('rol');
        } else {
            $codigo = 0;
			$rol = 0;	
		}

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigo,$rol,1);
        $subEntidades = $this->buscaSubEntidadRolEspecial($codigo,$rol);

        // devuelve un array con los diferentes tipos de reportes 1:sexo, 2:dependencia, 3:area de atencion, 4:modalidad  
        $entityEstadistica = $this->buscaEstadisticaEspecialAreaRol($codigo,$rol); 
       
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){             
                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
                } else {          
                    $subEntidades[$key]['total_general'] = 0;
                } 
            }
        } else {
            $subEntidades = null;
        }
        
        // para seleccionar ti

        if(count($entityEstadistica)>0){
            //$chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
            $chartDiscapacidad = $this->chartDonut3d($entityEstadistica[3],"Estudiantes matriculados según Área de Atención",$gestionProcesada,"Estudiantes","chartContainerDiscapacidad");
            //$chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
            $chartGenero = $this->chartPie($entityEstadistica[1],"Estudiantes matriculados según Sexo",$gestionProcesada,"Estudiantes","chartContainerGenero");
            //$chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
            $chartDependencia = $this->chartColumn($entityEstadistica[2],"Estudiantes matriculados según Dependencia",$gestionProcesada,"Estudiantes","chartContainerDependencia");
            $chartModalidad = $this->chartSemiPieDonut3d($entityEstadistica[4],"Estudiantes matriculados según Modalidad",$gestionProcesada,"Estudiantes","chartContainerModalidad");
        } else {
            $chartDiscapacidad = '';
            $chartGenero = '';
            $chartDependencia = '';
            $chartModalidad = '';
        }

        if($rol == 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("show");';
        } else {
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if(count($subEntidades)>0 and isset($subEntidades)){
            return $this->render('SieAppWebBundle:Reporte:matriculaEducativaEspecial.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'datoGraficoDiscapacidad'=>$chartDiscapacidad,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoModalidad'=>$chartModalidad,
                'datoGraficoDependencia'=>$chartDependencia,
                'mensaje'=>$mensaje,
                'gestion'=>$gestionActual,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));
        } else {
            return $this->render('SieAppWebBundle:Reporte:matriculaEducativaEspecial.html.twig', array(
                'infoEntidad'=>$entidad,
                'datoGraficoDiscapacidad'=>$chartDiscapacidad,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoModalidad'=>$chartModalidad,
                'datoGraficoDependencia'=>$chartDependencia,
                'mensaje'=>$mensaje,
                'gestion'=>$gestionActual,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));
        }
    }



    /**
     * Pagina Inicial - Instituciones Educativas - Nacional - Educacion Especial
     * rcanaviri-(edit-Pvargas)
     * @param Request $request
     * @return type
     */
    public function especialInstitucionEducativaIndexAction(Request $request) {        
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $gestionActual = date_format($fechaActual,'Y');
        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        
        $gestionProcesada = $gestionActual;

        $codigo = 0;
		$nivel = 0;	

		if ($request->isMethod('POST')) {
            $codigo = base64_decode($request->get('codigo'));
			$rol = $request->get('rol');
        } else {
            $codigo = 0;
			$rol = 0;	
		}
        //dump($codigo);die;
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigo,$rol,4);
        $subEntidades = $this->buscaSubEntidadRolEspecialInstitucionEducativa($codigo,$rol);
        $entityEstadistica = $this->buscaEstadisticaEspecialInstitucionEducativaAreaRol($codigo,$rol);

        //dump($subEntidades,$entityEstadistica);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            /* foreach ($subEntidades as $key => $dato) {
                if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){             
                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
                } else {          
                    $subEntidades[$key]['total_general'] = 0;
                } 
            } */
        } else {
            $subEntidades = null;
        }

        $link = true;
        if ($rol == 10){
            $link = false;
        }
        
        //$chartAmbito = $this->chartDonut3dInformacionGeneral($entityEstadistica[4],"Centros de Educación Especial segun Ámbito de Educación",$gestionActual,14,"chartContainerAmbito");
        $chartArea = $this->chartPie($entityEstadistica[2],"Centros de Educación Especial segun Área Geográfica",$gestionActual,3,"chartContainerArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica[1],"Centros de Educación Especial según Dependencia",$gestionActual,14,"chartContainerDependencia");
        $chartDiscapacidad = $this->chartColumnInformacionGeneral($entityEstadistica[3]," Centros de Educación Especial según Áreas de Atención",$gestionActual,15,"chartContainerDiscapacidad");
        
        if($rol == 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("show");';
        } else {
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }


        if(count($subEntidades)>0 and isset($subEntidades)){
            return $this->render('SieAppWebBundle:Reporte:institucionEducativaEspecial.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades,
                'infoEstadistica'=>$entityEstadistica,
                //'datoGraficoAmbito'=>$chartAmbito,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoDiscapacidad'=>$chartDiscapacidad,
                'mensaje'=>$mensaje,
                'gestion'=>$gestionActual,
                'fechaEstadistica'=>$fechaEstadistica,
                'link'=>$link,
                'form' => $defaultController->createLoginForm()->createView()
            ));
        } else {
            return $this->render('SieAppWebBundle:Reporte:institucionEducativaEspecial.html.twig', array(
                'infoEntidad'=>$entidad,
                //'datoGraficoAmbito'=>$chartAmbito,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoDiscapacidad'=>$chartDiscapacidad,
                'mensaje'=>$mensaje,
                'gestion'=>$gestionActual,
                'fechaEstadistica'=>$fechaEstadistica,
                'link'=>$link,
                'form' => $defaultController->createLoginForm()->createView()
            ));
        }
    }

    /**
    * * Busca el detalle de centros de educacion especial en funcion al tipo de rol - Educacion Especial
    * Pvargas
    * @param Request $request
    * @return type
    */
    public function buscaEstadisticaEspecialInstitucionEducativaAreaRol($codigo,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $gestionActual;

        $em = $this->getDoctrine()->getManager();

        if($rol == 8 or $rol == 20 or $rol == 0) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,eat.id as especial_area_tipo_id,eat.area_especial
                FROM institucioneducativa ie
                INNER JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                inner JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                inner join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id
                inner join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                inner join institucioneducativa_area_especial_autorizado ieae on ieae.institucioneducativa_id=ie.id
                inner join especial_area_tipo eat on eat.id=ieae.especial_area_tipo_id
                WHERE ie.institucioneducativa_tipo_id =4
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                )
               
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001
                
                UNION ALL
                select 3 as tipo_id, 'Atencion' as tipo_nombre,area_especial as detalle,count(id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                where especial_area_tipo_id<>99
                GROUP BY area_especial
                
                UNION ALL
                select 4 as tipo_id, 'Ambito' as tipo_nombre,a.area_especial as detalle,count(a.id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from
                (select id,array_to_string(array_agg( DISTINCT case WHEN especial_area_tipo_id in(1,2,3,4,5,10,99) then 'DISCAPACIDAD' else area_especial end) ,',') as area_especial
                    FROM tabla
                    GROUP BY id)a
                GROUP BY a.area_especial
                
                ORDER BY 1,2,3
               
            ");    
        } 

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,eat.id as especial_area_tipo_id,eat.area_especial
                FROM institucioneducativa ie
                INNER JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                inner JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                inner join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id
                inner join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                inner join institucioneducativa_area_especial_autorizado ieae on ieae.institucioneducativa_id=ie.id
                inner join especial_area_tipo eat on eat.id=ieae.especial_area_tipo_id
                WHERE ie.institucioneducativa_tipo_id =4
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                AND ddt.departamento_tipo_id = ". $codigo ."
                )
            
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001
                
                UNION ALL
                select 3 as tipo_id, 'Atencion' as tipo_nombre,area_especial as detalle,count(id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                where especial_area_tipo_id<>99
                GROUP BY area_especial
                
                UNION ALL
                select 4 as tipo_id, 'Ambito' as tipo_nombre,a.area_especial as detalle,count(a.id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from
                (select id,array_to_string(array_agg( DISTINCT case WHEN especial_area_tipo_id in(1,2,3,4,5,10,99) then 'DISCAPACIDAD' else area_especial end) ,',') as area_especial
                    FROM tabla
                    GROUP BY id)a
                GROUP BY a.area_especial
                
                ORDER BY 1,2,3
            ");  
        } 
        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,eat.id as especial_area_tipo_id,eat.area_especial
                FROM institucioneducativa ie
                INNER JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                inner JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                inner join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id
                inner join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                inner join institucioneducativa_area_especial_autorizado ieae on ieae.institucioneducativa_id=ie.id
                inner join especial_area_tipo eat on eat.id=ieae.especial_area_tipo_id
                WHERE ie.institucioneducativa_tipo_id =4
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                AND ddt.id = ". $codigo ."
                )

                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001
                
                UNION ALL
                select 3 as tipo_id, 'Atencion' as tipo_nombre,area_especial as detalle,count(id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                where especial_area_tipo_id<>99
                GROUP BY area_especial
                
                UNION ALL
                select 4 as tipo_id, 'Ambito' as tipo_nombre,a.area_especial as detalle,count(a.id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from
                (select id,array_to_string(array_agg( DISTINCT case WHEN especial_area_tipo_id in(1,2,3,4,5,10,99) then 'DISCAPACIDAD' else area_especial end) ,',') as area_especial
                    FROM tabla
                    GROUP BY id)a
                GROUP BY a.area_especial
                
                ORDER BY 1,2,3
            ");  
        }  

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }       

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        $aDato = array();
        
        foreach ($objEntidad as $key => $dato) {
            $aDato[$dato['tipo_id']][]= $dato;
        }
        return $aDato;
    }

    /**
     * Busca el detalle de estudiantes en funcion al tipo de rol - Educacion Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaEspecialAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                FROM estudiante AS e
                INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                WHERE
                iec.gestion_tipo_id IN (".$gestionActual.") AND
                ie.institucioneducativa_tipo_id = 4
                GROUP BY
                eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
            ) 
            
            select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join genero_tipo as gt on gt.id = t.genero_tipo_id
            group by gt.id, gt.genero
            
            union all
            
            select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
            group by dt.id, dt.dependencia
            
            union all
            
            select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
            group by eat.id, eat.area_especial
            
            union all
            
            select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.modalidad_id, t.modalidad
            
            order by tipo_id, id
             
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND ie.id = ".$area."
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("    
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND lt5.codigo = '".$area."'
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                    left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                    left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND lt4.codigo = '".$area."'
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND
                    ie.institucioneducativa_tipo_id = 4
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        $aDato = array();

        foreach ($objEntidad as $key => $dato) {
            $aDato[$dato['tipo_id']]['tipo'] = $dato['tipo_nombre'];
            if (isset($aDato[$dato['tipo_id']]['dato'][0]['cantidad'])){
                $cantidadParcial = $aDato[$dato['tipo_id']]['dato'][0]['cantidad'] + $dato['cantidad'];
            } else {
                $cantidadParcial = $dato['cantidad'];
            }    
            $aDato[$dato['tipo_id']]['dato'][0] = array('detalle'=>'Total', 'cantidad'=>$cantidadParcial);    
            $aDato[$dato['tipo_id']]['dato'][$dato['id']] = array('detalle'=>$dato['nombre'], 'cantidad'=>$dato['cantidad']);  
        }
        return $aDato;
    }

    /**
     * Funcion que retorna el Reporte Grafico Donut 3d Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartDonut3d($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {

        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }
        
        $pointLabel = $nombreLabel;

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'pie',
                        options3d: {
                            enabled: true,
                            alpha: 45
                        }
                    },
                    //colors: ['#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'],
                    colors: ['#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            innerSize: 100,
                            depth: 45,
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        shared: true,
                        useHTML: true,
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel."</b> del total<br/>'
                    },
                    series: [{
                        name: '".$entity['tipo']."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Pie Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartPie($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        if ($nombreLabel == 1){
            $nombreLabel = 'Institutos';
            $datosTemp = "{name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_rural']."}, {name: 'Urbana', y: ".round(((100*$entity['cant_urbana'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_urbana']."},";
            $name = 'Área Geográfica';
        }elseif($nombreLabel == 2){
            $nombreLabel = 'Institutos';
            $datosTemp = "{name: 'Sede', y: ".round(((100*$entity['cant_sede'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_sede']."}, {name: 'Subsede', y: ".round(((100*$entity['cant_subsede'])/(($entity['cant_total']==0) ? 1:$entity['cant_total'])),1).", label: ".$entity['cant_subsede']."},";
            $name = 'Sede/Subsede';
        }elseif($nombreLabel == 3){
            $nombreLabel = 'Centros';
            foreach($entity as $e){
                $datosTemp = $datosTemp . "{name: '". $e['detalle'] ."', y: ".round(((100*$e['cantidad'])/(($e['total']==0) ? 1:$e['total'])),1).", label: ".$e['cantidad']."},";
            }
            $name = 'Área Geográfica';
        }else{
            $name = 'Sexo';
            foreach ($entity['dato'] as $key => $dato) {
                $porcentaje = 0;
                if ($key == 0){
                    $subTotal = $dato['cantidad'];
                } else {
                    $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                    $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
                }
            }
        }
        
        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    colors: ['#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$nombreLabel."</b> del total<br/>'
                    },
                    series: [{
                        name: '". $name ."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Bar - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartColumn($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }   

        $pointLabel = $nombreLabel;

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'column'
                    },
                    colors: ['#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.label:,.0f} ({point.y:.1f}%)'
                            }
                        }
                    },        
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel." </b> del total<br/>'
                    },

                    series: [{
                        name: '".$entity['tipo']."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";        
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Semi Pie Donut 3d - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartSemiPieDonut3d($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }   

        $pointLabel = $nombreLabel;

        $datos = "   
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: 0,
                        plotShadow: false
                    },
                    colors: ['#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30'],
                    title: {
                        text: '".$titulo."',
                        align: 'center',
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel." </b> del total<br/>'
                    },
                    plotOptions: {
                        pie: {
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                },
                                format: '{point.label:,.0f} ({point.y:.1f}%)'
                            },
                            startAngle: -90,
                            endAngle: 90,
                            center: ['50%', '75%'],
                            allowPointSelect: true,
                            cursor: 'pointer',
                            showInLegend: true,
                        },
                    },
                    series: [{
                        type: 'pie',
                        name: '".$entity['tipo']."',
                        innerSize: '50%',
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";        
        return $datos;
    }


    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadRolEspecial($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            SELECT 'Departamento' as nombreArea, lt4.codigo as codigo, lt4.lugar  as nombre, 7 as rolUsuario 
            , count(*) as total_inscrito
            , coalesce(count(distinct institucioneducativa_id),0) as total_ues
            FROM estudiante AS e
            INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
            INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
            INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
            INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
            INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
            INNER JOIN  jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
            left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
            left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
            left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
            WHERE
            iec.gestion_tipo_id IN (".$gestionActual.") AND
            ie.institucioneducativa_tipo_id = 4
            GROUP BY lt4.id, lt4.codigo, lt4.lugar 
            ORDER BY lt4.id, lt4.codigo, lt4.lugar 
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    SELECT 'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id::varchar||' - '||ie.institucioneducativa as nombre, 9 as rolUsuario 
                    , count(*) as total_inscrito
                    , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    INNER JOIN  jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 and lt.codigo = '".$area."'
                    GROUP BY ie.id, ie.institucioneducativa
                    ORDER BY ie.id, ie.institucioneducativa 
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                SELECT 'Distrito Educativo' as nombreArea, lt5.codigo as codigo, lt5.lugar  as nombre, 10 as rolUsuario 
                , count(*) as total_inscrito
                , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                FROM estudiante AS e
                INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                INNER JOIN  jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                WHERE
                iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 and lt4.codigo = '".$area."'
                GROUP BY lt5.id, lt5.codigo, lt5.lugar 
                ORDER BY lt5.id, lt5.codigo, lt5.lugar 
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                SELECT 'Departamento' as nombreArea, lt4.codigo as codigo, lt4.lugar  as nombre, 7 as rolUsuario 
                , count(*) as total_inscrito
                , coalesce(count(distinct institucioneducativa_id),0) as total_ues
                FROM estudiante AS e
                INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                INNER JOIN  jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                WHERE
                iec.gestion_tipo_id IN (".$gestionActual.") AND
                ie.institucioneducativa_tipo_id = 4
                GROUP BY lt4.id, lt4.codigo, lt4.lugar 
                ORDER BY lt4.id, lt4.codigo, lt4.lugar 
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return null;
        }
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadRolEspecialInstitucionEducativa($codigo,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $gestionActual;
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, dep.codigo as codigo, dep.lugar as nombre, 7 as rolUsuario, coalesce(count(*),0) as cantidad
                FROM institucioneducativa ie
                JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id, lugar_tipo.area2001 AS area FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                WHERE ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 2 and ie.estadoinstitucion_tipo_id = 10 and ie.id not in (1,2,3,4,5,6,7,8,9) and ie.institucioneducativa_tipo_id = 4
                group by dep.codigo, dep.lugar
                order by dep.codigo, dep.lugar
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Centro de Educación Especial' as nombreArea, ie.id as codigo, ie.institucioneducativa as nombre, 9 as rolUsuario, coalesce(count(*),0) as cantidad
                    FROM institucioneducativa ie
                    JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id, lugar_tipo.area2001 AS area FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                    WHERE ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 2 and ie.estadoinstitucion_tipo_id = 10 and ie.id not in (1,2,3,4,5,6,7,8,9) and ie.institucioneducativa_tipo_id = 4 and dis.codigo = '".$codigo."'
                    group by ie.id, ie.institucioneducativa
                    order by ie.id, ie.institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distrito Educativo' as nombreArea, dis.codigo as codigo, dis.lugar as nombre, 10 as rolUsuario, coalesce(count(*),0) as cantidad
                    FROM institucioneducativa ie
                    JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id, lugar_tipo.area2001 AS area FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                    JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                    WHERE ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 2 and ie.estadoinstitucion_tipo_id = 10 and ie.id not in (1,2,3,4,5,6,7,8,9) and ie.institucioneducativa_tipo_id = 4 and dep.codigo = '".$codigo."'
                    group by dis.codigo, dis.lugar
                    order by dis.codigo, dis.lugar
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, dep.codigo as codigo, dep.lugar as nombre, 7 as rolUsuario, coalesce(count(*),0) as cantidad
                FROM institucioneducativa ie
                JOIN jurisdiccion_geografica jg ON jg.id = ie.le_juridicciongeografica_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id, lugar_tipo.area2001 AS area FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                JOIN (SELECT lugar_tipo.id, lugar_tipo.codigo, lugar_tipo.lugar, lugar_tipo.lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                WHERE ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.orgcurricular_tipo_id = 2 and ie.estadoinstitucion_tipo_id = 10 and ie.id not in (1,2,3,4,5,6,7,8,9) and ie.institucioneducativa_tipo_id = 4
                group by dep.codigo, dep.lugar
                order by dep.codigo, dep.lugar
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return '';
        }
    }

    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralEspecialPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato EXCEL - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralEspecialPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    /**
     * Imprime reportes estadisticos de centros de educacion especial segun el tipo de rol en formato PDF - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function especialInstitucionEducativaPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionCentros_Especial_v1_pvc.rptdesign&__format=pdf&codigo='.$codigoArea));
        /*  $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Nacional_v1_rcm.rptdesign&__format=pdf&codigo='.$codigoArea));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Distrital_v1_rcm.rptdesign&__format=pdf&codigo='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Departamental_v1_rcm.rptdesign&__format=pdf&codigo='.$codigoArea));
        }  

        if($rol == 8 or $rol == 20 or $rol  == 0) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionCentros_Especial_v1_pvc.rptdesign&__format=pdf&codigo='.$codigoArea));
        } */

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reportes estadisticos de centros de educacion especial segun el tipo de rol en formato XLS - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function especialInstitucionEducativaXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xlsx';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        //$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Nacional_v1_rcm.rptdesign&__format=xls&codigo='.$codigoArea));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionCentros_Especial_v1_pvc.rptdesign&__format=xlsx&codigo='.$codigoArea));

       /*  if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Distrital_v1_rcm.rptdesign&__format=xls&codigo='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Departamental_v1_rcm.rptdesign&__format=xls&codigo='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_institucionEducativa_Nacional_v1_rcm.rptdesign&__format=xls&codigo='.$codigoArea));
        }  */

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Página Directorio de Institutos Técnicos y Tecnológicos
     * AFiengo
     * @param Request $request
     * @return type
     */
    public function directorioIttsAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $em = $this->getDoctrine()->getManager();
        $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => 0));
        $arrayDependencia = [1,2,3];

        return $this->render('SieAppWebBundle:Reporte:directorioItts.html.twig', array(
            'infoEntidad'=>'',
            'form' => $defaultController->createLoginForm()->createView(),
            'formBusqueda' => $this->creaFormularioBusquedaInstituto('reporte_superior_directorio_itts_busqueda','',$entityDepartamento, $arrayDependencia)->createView()
        ));
    }

    private function creaFormularioBusquedaInstituto($routing, $ue, $entityDepartamento, $arrayDependencia) {
        $entityDependencia = ['1'=>'Fiscal', '2'=>'Convenio', '3'=>'Privada'];

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('itt', 'text', array('label' => 'Código RITT o Nombre del Instituto', 'required' => false, 'attr' => array('value' => $ue, 'class' => 'form-control no-border-left', 'placeholder' => 'Código RITT o Nombre del Instituto', 'style' => 'text-transform:uppercase')))
            ->add('departamento', 'entity', array('label' => 'Departamento.', 'empty_value' => 'Todos', 'data' => $entityDepartamento, 'required' => false, 'attr' => array('class' => 'form-control mb-20'), 'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('dt')
                            ->orderBy('dt.id', 'ASC')
                            ->where('dt.id != :codDepartamento')
                            ->setParameter('codDepartamento', 0);
                },
            ))
            ->add('dependencia', 'choice', ['choices' => $entityDependencia, 'data' => $arrayDependencia, 'multiple' => true, 'expanded' => true])
            ->add('submit', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
        return $form;
    }

    /**
     * Pagina Unidades Educativas en funcion a criterios de búsqueda
     * AFiengo
     * @param Request $request
     * @return type
     */
    public function directorioIttsBusquedaSuperiorAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $request = $this->getRequest();
        $this->route_anterior = $request->get('_route');
        $this->var_anterior = $request->query->all();

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $form = $request->get('form');
            if ($form) {
                $codigoDepartamento = $form['departamento'];
                if ($codigoDepartamento == "") {
                    $codigoDepartamento = 0;
                }
                $textUnidadEducativa = $form['itt'];
                $dependencia = isset($form['dependencia']) ? $form['dependencia'] : array();
                $dependenciaList = "";
                for($i = 0; $i < count($dependencia); $i++) {
                    if($dependenciaList==""){
                        $dependenciaList = $dependencia[$i];
                    } else {
                        $dependenciaList = $dependenciaList.",".$dependencia[$i];
                    }
                }
                if($dependenciaList==""){
                    $dependenciaList = "1,2,3";
                }

                $em = $this->getDoctrine()->getManager();
                $query = $em->getConnection()->prepare("
                    select ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, jg.direccion as direccion
                    , oct.orgcurricula as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                    , pro.lugar as provincia, jg.zona
                    from institucioneducativa as ie
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                    inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                    inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                    inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id                    
                    where ie.institucioneducativa_acreditacion_tipo_id = 2 
                    and (cast(ie.id as varchar) = replace('".$textUnidadEducativa."',' ','%') or ie.institucioneducativa like '%'||replace(UPPER('".$textUnidadEducativa."'),' ','%')||'%')
                    and (case ".$codigoDepartamento." when 0 then dep.codigo != '0' else dep.codigo = '".$codigoDepartamento."' end)
                    and det.id in (".$dependenciaList.")
                    order by orgcurricular, departamento, estadoinstitucion, seccion
                ");
                $query->execute();
                $entityInstitucionEducativa = $query->fetchAll();

                $infoBusqueda = serialize(array(
                    'itt' => $textUnidadEducativa,
                    'departamento' => $codigoDepartamento,
                    'dependencia' => $dependencia
                ));
            }  else {   
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
                return $this->redirectToRoute('reporte_superior_directorio_itts');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));
            return $this->redirectToRoute('reporte_superior_directorio_itts');
        }

        $em = $this->getDoctrine()->getManager();
        $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $codigoDepartamento ));

        return $this->render('SieAppWebBundle:Reporte:directorioIttsSuperior.html.twig', array(
            'infoEntidad' => '',
            'infoBusqueda' => $infoBusqueda,
            'entityBusqueda'=> $entityInstitucionEducativa,
            'form' => $defaultController->createLoginForm()->createView(),
            'dependencia' => $dependencia,
            'formBusqueda' => $this->creaFormularioBusquedaInstituto('reporte_superior_directorio_itts_busqueda',$textUnidadEducativa, $entityDepartamento, $dependencia)->createView()
        ));
    }

    /**
     * Pagina de instituto seleccionado
     * AFiengo
     * @param Request $request
     * @return type
     */
    public function directorioIttsDetalleSuperiorAction(Request $request) {  
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */            
            $infoBusqueda = $request->get('infoBusqueda');
            $ritt = $request->get('ritt');
            
            $ainfoBusqueda = unserialize($infoBusqueda);
            //get the values throght the infoUe
            $unidadEducativaText = $ainfoBusqueda['itt'];
            $departamentoId = $ainfoBusqueda['departamento'];
            $dependenciaArrayId = $ainfoBusqueda['dependencia'];
            $dependenciaList = "";
            for($i = 0; $i < count($dependenciaArrayId); $i++) {
                if($dependenciaList==""){
                    $dependenciaList = $dependenciaArrayId[$i];
                } else {
                    $dependenciaList = $dependenciaList.",".$dependenciaArrayId[$i];
                }
            }

            $entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoId ));
            
            $query = $em->getConnection()->prepare("
                select distinct ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, jg.direccion as direccion
                , 'Educación '||oct.orgcurricula as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                , pro.lugar as provincia, jg.zona, jg.cordx, jg.cordy, loc.area, ien.nivel_autorizado
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                left join (
                    select string_agg(distinct nt1.nivel,', ') as nivel_autorizado, institucioneducativa_id
                    from institucioneducativa_nivel_autorizado as iena1
                    left join nivel_tipo as nt1 on nt1.id = iena1.nivel_tipo_id
                    where iena1.institucioneducativa_id = ".$ritt."  
                    group by iena1.institucioneducativa_id
                ) as ien on ien.institucioneducativa_id = ie.id
                where ie.institucioneducativa_acreditacion_tipo_id = 2 and ie.id = ".$ritt." 
                and (case ".$departamentoId." when 0 then dep.codigo != '0' else dep.codigo = '".$departamentoId."' end)
                and det.id in (".$dependenciaList.")            
                order by orgcurricular, departamento, estadoinstitucion, seccion
            ");

            $query->execute();
            $entityInstitucionEducativa = $query->fetchAll();

            if(count($entityInstitucionEducativa) > 0){
                $carrerasCursos = $this->listadoOfertaAcademica($ritt);
            } else {
                $carrerasCursos = array();
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar información, intente nuevamente'));            
        }

        return $this->render('SieAppWebBundle:Reporte:directorioIttsDetalleSuperior.html.twig', array(
            'infoEntidad'=>'',
            'entityUnidadEducativa'=> $entityInstitucionEducativa,
            'carrerasCursos' => $carrerasCursos,
            'form' => $defaultController->createLoginForm()->createView(),
            'formBusqueda' => $this->creaFormularioBusquedaInstituto('reporte_superior_directorio_itts_busqueda',$unidadEducativaText,$entityDepartamento, $dependenciaArrayId)->createView()
        ));
    }

    /***
     * Obtiene un array con datos del listado de carreras y/o cursos autorizados
     */
    public function listadoOfertaAcademica($ritt){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT autorizado.id AS id, carrera.id AS idcarrera, carrera.nombre AS carr 
                    FROM ttec_institucioneducativa_carrera_autorizada AS autorizado
                    INNER JOIN ttec_carrera_tipo AS carrera ON autorizado.ttec_carrera_tipo_id = carrera.id 
                    INNER JOIN institucioneducativa AS instituto ON autorizado.institucioneducativa_id = instituto.id 
                    INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id
                    WHERE instituto.id = '".$ritt."' ORDER BY carr ASC";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $listado = $stmt->fetchAll();

        $list = array();                                  
        foreach($listado as $li){
            $query = $em->createQuery('SELECT a
                                         FROM SieAppWebBundle:TtecResolucionCarrera a 
                                        WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                     ORDER BY a.fecha DESC');                       
            $query->setParameter('idCaAutorizada', $li['id']);
            $query->setMaxResults(1);
            $resolucion = $query->getResult();   

            $list[] = array(
                'id' => $li['id'],
                'idcarrera' => $li['idcarrera'],
                'carrera' => $li['carr'],
                'idresolucion' => ($resolucion) ? $resolucion[0]->getId():"0",
                'resolucion' => ($resolucion) ? $resolucion[0]->getNumero():" ",
                'fecharesol' => ($resolucion) ? $resolucion[0]->getFecha():" ",
                'nivelformacion' => ($resolucion) ? $resolucion[0]->getNivelTipo()->getNivel():" ",
                'tiempoestudio' => ($resolucion) ? $resolucion[0]->getTiempoEstudio():" ",
                'regimen' =>  ($resolucion) ? $resolucion[0]->getTtecRegimenEstudioTipo()->getRegimenEstudio():" ",
                'cargahoraria' => ($resolucion) ? $resolucion[0]->getCargaHoraria():" ",
                'operacion' => ($resolucion) ? $resolucion[0]->getOperacion():" "
            );
        }                                    
        return $list;
    }  
}
