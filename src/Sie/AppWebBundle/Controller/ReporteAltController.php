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

/**
 * Vista controller.
 *
 */
class ReporteController extends Controller {

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

        $entidad = $this->buscaEntidadRol(0,0);
        $subEntidades = $this->buscaSubEntidadRol(0,0);
        $entityEstadistica = $this->buscaEstadisticaAreaRol(0,0);
        $entityEstadisticaUE = $this->buscaEstadisticaUERol(0,0);
        $entityEstadisticaEE = $this->buscaEstadisticaEERol(0,0);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegular($gestionActual);

        $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionActual,1,"chartContainerMatricula");
        $chartNivel = $this->chartDonutInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Nivel de Estudio",$gestionActual,2,"chartContainerEfectivoNivel");
        $chartGenero = $this->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Género",$gestionActual,3,"chartContainerEfectivoGenero");
        $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Área Geográfica",$gestionActual,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Dependencia",$gestionActual,5,"chartContainerEfectivoDependencia");

        return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegular.html.twig', array(
            'infoEntidad'=>$entidad,
            'infoSubEntidad'=>$subEntidades, 
            'infoEstadistica'=>$entityEstadistica, 
            'infoEstadisticaUE'=>$entityEstadisticaUE,
            'infoEstadisticaEE'=>$entityEstadisticaEE,
            'rol'=>0,
            'datoGraficoMatricula'=>$chartMatricula,
            'datoGraficoNivel'=>$chartNivel,
            'datoGraficoGenero'=>$chartGenero,
            'datoGraficoArea'=>$chartArea,
            'datoGraficoDependencia'=>$chartDependencia,
            'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
            'gestion'=>$gestionActual,
            'fechaEstadisticaRegular'=>$fechaEstadisticaRegular
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
        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaEstadisticaAreaRol($codigoArea,$rolUsuario);
        $entityEstadisticaUE = $this->buscaEstadisticaUERol($codigoArea,$rolUsuario);
        $entityEstadisticaEE = $this->buscaEstadisticaEERol($codigoArea,$rolUsuario);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegular($gestionActual);
       
        $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionActual,1,"chartContainerMatricula");
        $chartNivel = $this->chartDonutInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Nivel de Estudio",$gestionActual,2,"chartContainerEfectivoNivel");
        $chartGenero = $this->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Género",$gestionActual,3,"chartContainerEfectivoGenero");
        $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Área Geográfica",$gestionActual,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Efectivos por Dependencia",$gestionActual,5,"chartContainerEfectivoDependencia");
        
        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'infoEstadisticaUE'=>$entityEstadisticaUE,
                'infoEstadisticaEE'=>$entityEstadisticaEE,
                'datoGraficoMatricula'=>$chartMatricula,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'gestion'=>$gestionActual,
                'fechaEstadisticaRegular'=>$fechaEstadisticaRegular
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegular.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionActual,
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegular.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,
                    'infoEstadisticaUE'=>$entityEstadisticaUE,
                    'infoEstadisticaEE'=>$entityEstadisticaEE,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionActual,
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular
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

        $sesion = $request->getSession();        
        $idUsuario = $this->session->get('userId');

        $tipoReporte = $request->get('tipo');
        $codigoArea = $request->get('codigo');
        $rolUsuario = $request->get('rol');

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario);
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

        $entidadGrafico = $this->chartColumnInformacionGeneral($entityEstadistica,$titulo,$gestionActual,$tipoReporte);
        
        return $this->render('SieAppWebBundle:Reporte:informacionGeneralRegularGraphic.html.twig', array('datoEntidad'=>$entidad,'datoGrafico'=>$entidadGrafico));                  
    }

    /**
     * Busca el nombre de la entidad en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEntidadRol($codigo,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, 0 as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt 
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                select ie.id as id, 'U.E.: '|| cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from institucioneducativa as ie
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

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 'Departamentos' as nombreArea, cast(lt.codigo as integer) as codigo, lt.lugar as nombre, 7 as rolUsuario from lugar_tipo as lt where lt.lugar_tipo_id = 1 and lugar_nivel_id = 1 order by cast(lt.codigo as integer)
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Unidades Educativas' as nombreArea, ie.id as codigo, cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, 9 as rolUsuario  from institucioneducativa as ie 
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_distrito
                    where cast(lt1.codigo as integer) = ".$area." order by codigo
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distritos Educativos' as nombreArea, cast(lt.codigo as integer) as codigo, lt.lugar as nombre, 10 as rolUsuario from lugar_tipo as lt where cast(left(lt.codigo,1) as integer) = ".$area." and lugar_nivel_id = 7 and lt.codigo not in ('1000','2000','3000','4000','5000','6000','7000','8000','9000') order by nombre
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Departamentos' as nombreArea, cast(lt.codigo as integer) as codigo, lt.lugar as nombre, 7 as rolUsuario from lugar_tipo as lt where lt.lugar_tipo_id = 1 and lugar_nivel_id = 1 order by cast(lt.codigo as integer)
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

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select 
                coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_inicial
                , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_primaria
                , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_secundaria
                , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_masculino
                , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_femenino
                , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_urbano
                , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_rural
                , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_publica
                , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_convenio
                , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_privada
                , coalesce(sum(case when estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_efectivo
                , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                , coalesce(sum(cantidad),0) as total_inscrito
                from (
                select nivel_id, genero_id, genero, estadomatricula_id, estadomatricula, area, dependencia_id, dependencia
                , sum(cantidad) as cantidad 
                from vm_estudiantes_estadistica_regular 
                where gestion = ".$gestionActual." and nivel_id in (11,12,13) group by nivel_id, area, dependencia_id, dependencia, genero_id, genero, estadomatricula_id, estadomatricula
                ) as v 
            "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from (
                    select nivel_id, genero_id, genero, estadomatricula_id, estadomatricula, area, dependencia_id, dependencia
                    , sum(cantidad) as cantidad 
                    from vm_estudiantes_estadistica_regular 
                    where gestion = ".$gestionActual." and nivel_id in (11,12,13) and institucioneducativa_id = ".$area." group by nivel_id, area, dependencia_id, dependencia, genero_id, genero, estadomatricula_id, estadomatricula
                    ) as v 
                ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from (
                    select nivel_id, genero_id, genero, estadomatricula_id, estadomatricula, area, dependencia_id, dependencia
                    , sum(cantidad) as cantidad 
                    from vm_estudiantes_estadistica_regular 
                    where gestion = ".$gestionActual." and nivel_id in (11,12,13) and distrito_codigo = '".$area."' group by nivel_id, area, dependencia_id, dependencia, genero_id, genero, estadomatricula_id, estadomatricula
                    ) as v 
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from (
                    select nivel_id, genero_id, genero, estadomatricula_id, estadomatricula, area, dependencia_id, dependencia
                    , sum(cantidad) as cantidad 
                    from vm_estudiantes_estadistica_regular 
                    where gestion = ".$gestionActual." and nivel_id in (11,12,13) and departamento_codigo = '".$area."' group by nivel_id, area, dependencia_id, dependencia, genero_id, genero, estadomatricula_id, estadomatricula
                    ) as v 
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 
                    coalesce(sum(case when nivel_id = 11 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_inicial
                    , coalesce(sum(case when nivel_id = 12 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_primaria
                    , coalesce(sum(case when nivel_id = 13 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_secundaria
                    , coalesce(sum(case when genero_id = 1 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_masculino
                    , coalesce(sum(case when genero_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_femenino
                    , coalesce(sum(case when area = 'U' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_urbano
                    , coalesce(sum(case when area = 'R' and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_rural
                    , coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_publica
                    , coalesce(sum(case when dependencia_id = 2 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_convenio
                    , coalesce(sum(case when dependencia_id = 3 and estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_privada
                    , coalesce(sum(case when estadomatricula_id in (4,5,11,55,56,57,58,45,26,27,0) then cantidad else 0 end),0) as cant_efectivo
                    , coalesce(sum(case when estadomatricula_id in (6) then cantidad else 0 end),0) as cant_no_incorporado
                    , coalesce(sum(case when estadomatricula_id in (10,3,99) then cantidad else 0 end),0) as cant_retiro_abandono
                    , coalesce(sum(case when estadomatricula_id in (9) then cantidad else 0 end),0) as cant_retiro_traslado
                    , coalesce(sum(cantidad),0) as total_inscrito
                    from (
                    select nivel_id, genero_id, genero, estadomatricula_id, estadomatricula, area, dependencia_id, dependencia
                    , sum(cantidad) as cantidad 
                    from vm_estudiantes_estadistica_regular 
                    where gestion = ".$gestionActual." and nivel_id in (11,12,13) group by nivel_id, area, dependencia_id, dependencia, genero_id, genero, estadomatricula_id, estadomatricula
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

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select coalesce(sum(case when (dependencia_id = 1 or dependencia_id = 5) then 1 else 0 end),0) as cant_publica
            , coalesce(sum(case when dependencia_id = 2 then 1 else 0 end),0) as cant_convenio
            , coalesce(sum(case when dependencia_id = 3 then 1 else 0 end),0) as cant_privada
            , coalesce(sum(case area when 'U' then 1 else 0 end),0) as cant_urbano
            , coalesce(sum(case area when 'R' then 1 else 0 end),0) as cant_rural
            , coalesce(count(institucioneducativa_id),0) as cant_total 
            from (
            select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionActual."
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
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionActual." and institucioneducativa_id = ".$area."
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
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionActual." and distrito_codigo = '".$area."'
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
                    select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionActual." and departamento_codigo = '".$area."'
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
                select distinct area, dependencia_id, institucioneducativa_id from vm_estudiantes_estadistica_regular where gestion = ".$gestionActual."
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

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select departamento_codigo AS lugar_codigo, upper(departamento) as lugar_nombre, cast(date_part('day',fecha_vista) as integer) as dia, cast(date_part('month',fecha_vista) as integer) as mes
            , case date_part('dow',fecha_vista) when 1 then 'lunes' when 2 then 'martes' when 3 then 'miercoles' when 4 then 'jueves' when 5 then 'viernes' when 6 then 'sabado' when 7 then 'domingo' else '' end as dia_literal
            , case date_part('month',fecha_vista) when 1 then 'enero' when 2 then 'febrero' when 3 then 'marzo' when 4 then 'abril' when 5 then 'mayo' when 6 then 'junio' when 7 then 'julio' when 8 then 'agosto' when 9 then 'septiembre' when 10 then 'octubre' when 11 then 'noviembre' when 12 then 'diciembre' else '' end as mes_literal
            , cast(date_part('year',fecha_vista) as integer) as gestion
            from vm_estudiantes_estadistica_regular
            where gestion = ".$gestion." limit 1
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
        $gestionActual = date_format($fechaActual,'Y');

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
        switch ($tipoReporte) {
            case 1:
                //$datosTemp = "{name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Retiros', y: ".round(((100*$entity['cant_retiro'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro']."}, {name: 'Retiros Doble Promoción', y: ".round(((100*$entity['cant_retiro_doble_promocion'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_doble_promocion']."}, {name: 'Efectivos', y: ".round(((100*$entity['total_matricula'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['total_matricula']."},";
                $datosTemp = "{name: 'Total Inscritos', y: ".(($entity['total_inscrito']==0)?0:100).", label: ".$entity['total_inscrito']."}, {name: 'No Incorporados', y: ".round(((100*$entity['cant_no_incorporado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_no_incorporado']."}, {name: 'Retiros por Abandono', y: ".round(((100*$entity['cant_retiro_abandono'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_abandono']."}, {name: 'Retiros por Traslado', y: ".round(((100*$entity['cant_retiro_traslado'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_retiro_traslado']."}, {name: 'Efectivos', y: ".round(((100*$entity['cant_efectivo'])/(($entity['total_inscrito']==0) ? 1:$entity['total_inscrito'])),1).", label: ".$entity['cant_efectivo']."},";
                break;
            case 2:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Inicial', y: ".round(((100*$entity['cant_inicial'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_inicial']."}, {name: 'Primaria', y: ".round(((100*$entity['cant_primaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_primaria']."}, {name: 'Secundaria', y: ".round(((100*$entity['cant_secundaria'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_secundaria']."},";
                break;
            case 3:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Masculino', y: ".round(((100*$entity['cant_masculino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['cant_femenino'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_femenino']."},";
                break;
            case 4:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Urbano', y: ".round(((100*$entity['cant_urbano'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_urbano']."}, {name: 'Rural', y: ".round(((100*$entity['cant_rural'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_rural']."},";
                break;
            case 5:
                $datosTemp = "{name: 'Total Efectivos', y: ".(($entity['cant_efectivo']==0)?0:100).", label: ".$entity['cant_efectivo']."}, {name: 'Pública', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }        

        $datos = "   
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
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
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: 'Total porcentaje por matricula de estudiantes'
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
                                format: '{point.y:.1f}%'
                            }
                        }
                    },        
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label} Estudiantes</b> del total<br/>'
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
                $datosTemp = "{name: 'Pública', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }        

        $datos = "   
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
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
                                format: '<b>{point.name}</b>: {point.percentage:.1f}% - {point.label}',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label} Estudiantes</b> del total<br/>'
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
     * Funcion que retorna el Reporte Grafico Donut Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartDonutInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
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
                $datosTemp = "{name: 'Pública', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }      

        $datos = "   
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
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
                                format: '<b>{point.name}</b>: {point.percentage:.1f}% - {point.label}',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
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
                $datosTemp = "{name: 'Pública', y: ".round(((100*$entity['cant_publica'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_publica']."}, {name: 'Convenio', y: ".round(((100*$entity['cant_convenio'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['cant_privada'])/(($entity['cant_efectivo']==0) ? 1:$entity['cant_efectivo'])),1).", label: ".$entity['cant_privada']."},";
                break;
            default:
                $datosTemp = "";
                break;
        }      

        $datos = "   
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
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
        $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
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
        $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=pdf&codges='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_distrital_v1.rptdesign&__format=pdf&codges='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_departamental_v1.rptdesign&__format=pdf&codges='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_rangoValoracion_enDesarrollo_porMateriaBismestre_nacional_v1.rptdesign&__format=pdf&codges='.$gestion));
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
        $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));
        } 

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
}