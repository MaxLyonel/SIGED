<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Vista controller.
 *
 */
class ReporteUpFileController extends Controller {

    public function defaultAction(Request $request) {


        $exist = false;
        $bimestre = '0';
        $datos = array();
        $entity = array();
        $gestion = '';
        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');
            $bimestre = $form['bimestre'];
            $gestion = $form['gestion'];
            $exist = true;
            $entity = $this->consolidacionNivelNacional($gestion, $bimestre);
            // dump($entity);
            // die;
            $datos = $this->chartBarConsolidacion($entity, $bimestre, 1);
        }

        return $this->render('SieRegularBundle:ReporteUpFile:consolidacion.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nivel' => 'departamentos',
                    'nivelnext' => 'distrital',
                    'exist' => $exist,
                    'gestion' => $gestion,
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 4; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('reporte_upfile_index'))
                        //->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                        ->add('bimestre', 'choice', array('label' => 'Bimestre', 'choices' => array('0' => 'IG', '1' => '1er Bim', '2' => '2do Bim', '3' => '3ro Bim', '4' => '4to Bim'), 'attr' => array('class' => 'form-control')))
                        //->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'findUpfileInfo();')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-default')))
                        ->getForm();
    }

    public function resultAction(Request $request) {

        //get the values
        $form = $request->get('form');

        $bimestre = 1; //$form['bimestre'];
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity, $bimestre, 1);


        return $this->render('SieRegularBundle:ReporteUpFile:result.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nivel' => 'departamentos',
                    'nivelnext' => 'distrital',
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Regular
     * Jurlan
     */
    public function departamentoRegularAction($bimestre) {
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity, $bimestre, 1);
        return $this->render('SieRegularBundle:ReporteUpFile:consolidacion.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nivel' => 'departamentos',
                    'nivelnext' => 'distrital',
                    'form' => $this->craeteformsearchsie()->createView()
        ));
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
            $gestion = $request->get('gestion');

            $entity = $this->consolidacionNivelDepartamental($id, $gestion);

            $datos = $this->chartBarConsolidacion($entity, $bimestre, 2);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteUpFile:consolidacion.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nombre' => $depto,
                    'nivel' => 'distritos',
                    'nivelnext' => 'institucional',
                    'gestion' => $gestion,
                    'exist' => true,
                    'form' => $this->craeteformsearchsie()->createView()
        ));
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
            $gestion = $request->get('gestion');
            $entity = $this->consolidacionNivelDistrital($id, $gestion);
            $datos = $this->chartBarConsolidacion($entity, $bimestre, 3);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteUpFile:consolidacion.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nombre' => $distrito,
                    'nivel' => 'unidades educativas',
                    'nivelnext' => 'estudiantil',
                    'exist' => true,
                    'gestion' => $gestion,
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    /**
     * Funcion que retorna el Reporte Grafico Chart Jquery de tipo Bar - Educacion Regular
     * Jurlan
     * @param Request $entity
     * @return repr
     */
    public function chartBarConsolidacion($entity, $bimestre, $nivel) {
        $totalConsolidado = 0;
        $totalNoConsolidado = 0;
        if ($bimestre == 1) {
            $numeroLiteral = 'Primer';
        } elseif ($bimestre == 2) {
            $numeroLiteral = 'Segundo y Tercer';
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
        $aBimestre = array('0' => 'Inicio de Gestión',
            '1' => '1er Bimestre',
            '2' => '2do Bimestre',
            '3' => '3er Bimestre',
            '4' => '4to Bimestre',
        );
        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos = "
            var chartConsolidacion = new CanvasJS.Chart('chartContainer',
            {
                title:{
                    text: 'Subida de Archivos - " . $aBimestre[$bimestre] . " ',
                    fontSize: 20,
                },
                animationEnabled: true,
                axisX:{
                  title: '',
                  titleFontSize: 20,
                  labelAngle: 60,
                },
                toolTip:{
                    content: '{name}: {y}'
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
                        name: 'Subidos',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#004B8D',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadConsolidado = $entity[$i]['cant_si_cons_' . $bimestre];
            $totalConsolidado = $totalConsolidado + $cantidadConsolidado;
            $nombre = $entity[$i]['nombre'];
            $datosTemp = $datosTemp . '{y: ' . ($cantidadConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '"},';
        }
        $datos = $datos . '{y: ' . ($totalConsolidado) . ', legendText:"Nacional", label: "Nacional", color: "#2F4F4F"},' . $datosTemp;
        $datos = $datos . "]
                    },
                    {
                        name: 'No Subidos',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#4192D9',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadNoConsolidado = $entity[$i]['cant_no_cons_' . $bimestre];
            $totalNoConsolidado = $totalNoConsolidado + $cantidadNoConsolidado;

            if ($nivel === 1) {
                $nombre = $entity[$i]['nombre'];
                $nombreCompleto = $entity[$i]['nombre'];
            } else {
                $nombre = $entity[$i]['codigo'];
                $nombreCompleto = $entity[$i]['nombre'];
            }
            $datosTemp = $datosTemp . '{y: ' . ($cantidadNoConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '", name: "' . ($nombreCompleto) . '"},';
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
    public function consolidacionNivelNacional($gestion, $bimestre) {
        $em = $this->getDoctrine()->getManager();
        /*
        $query = $em->getConnection()->prepare(" 
            select
			case depto when 'Chuquisaca' then 1 when 'La Paz' then 2 when 'Cochabamba' then 3 when 'Oruro' then 4 when 'Potosi' then 5 when 'Tarija' then 6 when 'Santa Cruz' then 7 when 'Beni' then 8 when 'Pando' then 9 end as codigo
			, depto as nombre
			, sum(case when cargado = 0 and bimestre = 'bim0' then 1 else 0 end)  as cant_no_cons_0
			, sum(case when cargado = 10 and bimestre= 'bim0' then 1 else 0 end)  as cant_si_cons_0
			, sum(case when cargado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
			, sum(case when cargado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
			, sum(case when cargado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
			, sum(case when cargado = 2 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
			, sum(case when cargado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
			, sum(case when cargado = 3 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
			, sum(case when cargado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
			, sum(case when cargado = 4 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
			from (
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim0' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.ini,0) as cargado
                from (
                select *
                from registro_consolidacion where  tipo=1 and gestion=(" . $gestion . ")- 1) a
                left join (select *, 10 as ini from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=0) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim1' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1 ) a
                left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=1) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim2' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1) a
                left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=2) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim3' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1) a
                left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=3) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim4' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1) a
                left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=4) c on a.unidad_educativa=c.cod_ue

                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
	) as v
	group by depto order by depto

                ");
                */
        $query = $em->getConnection()->prepare(" 
            with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id = ".$gestion." order by id desc limit 2)
                select dt.id as codigo, dt.departamento as nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
                , sum(case when ufc.b0 >= 1 then 1 else 0 end) as cant_si_cons_0
                , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_0
                , sum(case when ufc.b1 >= 1 then 1 else 0 end) as cant_si_cons_1
                , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_1
                , sum(case when ufc.b2 >= 1 then 1 else 0 end) as cant_si_cons_2
                , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as cant_no_cons_2
                , sum(case when ufc.b3 >= 1 then 1 else 0 end) as cant_si_cons_3
                , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as cant_no_cons_3
                , sum(case when ufc.b4 >= 1 then 1 else 0 end) as cant_si_cons_4
                , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as cant_no_cons_4
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
                where ie.orgcurricular_tipo_id = 1 AND ie.estadoinstitucion_tipo_id = 10 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 
                /*and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)*/
                group by dt.id, dt.departamento--, dis.codigo, dis.lugar
                order by dt.id, dt.departamento
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
    public function consolidacionNivelDepartamental($depto, $gestion) {
        $em = $this->getDoctrine()->getManager();
        /*$query = $em->getConnection()->prepare(" select lt.lugar as nombre, v2.* from (
            select cod_dis as codigo
                , sum(case when cargado = 0 and bimestre = 'bim0' then 1 else 0 end)  as cant_no_cons_0
                , sum(case when cargado = 10 and bimestre= 'bim0' then 1 else 0 end)  as cant_si_cons_0
		, sum(case when cargado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
		, sum(case when cargado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
		, sum(case when cargado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
		, sum(case when cargado = 2 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
		, sum(case when cargado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
		, sum(case when cargado = 3 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
		, sum(case when cargado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
		, sum(case when cargado = 4 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
            from (
            select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim0' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.ini,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1) a
                left join (select *, 10 as ini from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=0) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
            select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim1' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")-1 ) a
		left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=1) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim2' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")-1) a
		left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=2) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim3' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")-1) a
		left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=3) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim4' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")-1) a
		left join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=4) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
            ) as v
            where cod_depto = " . $depto . "
            group by cod_dis
            ) as v2
            left join lugar_tipo as lt on lt.codigo = v2.codigo order by lugar
            ");
        */
        

        $query = $em->getConnection()->prepare("
            with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id = ".$gestion." order by id desc limit 2)
            select dis.codigo as codigo, dis.lugar as nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
            , sum(case when ufc.b0 >= 1 then 1 else 0 end) as cant_si_cons_0
            , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_0
            , sum(case when ufc.b1 >= 1 then 1 else 0 end) as cant_si_cons_1
            , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_1
            , sum(case when ufc.b2 >= 1 then 1 else 0 end) as cant_si_cons_2
            , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as cant_no_cons_2
            , sum(case when ufc.b3 >= 1 then 1 else 0 end) as cant_si_cons_3
            , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as cant_no_cons_3
            , sum(case when ufc.b4 >= 1 then 1 else 0 end) as cant_si_cons_4
            , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as cant_no_cons_4
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
            where ie.orgcurricular_tipo_id = 1 AND ie.estadoinstitucion_tipo_id = 10 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 and dt.id = ".$depto." 
            /*and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)*/
            group by dis.codigo, dis.lugar
            order by dis.codigo, dis.lugar
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
    public function consolidacionNivelDistrital($distrito, $gestion) {
        $em = $this->getDoctrine()->getManager();
        /*
        $query = $em->getConnection()->prepare(" select ie.id as codigoue, ie.institucioneducativa as nombre, v2.* from (
            select cast(v.id as integer) as codigo
                , sum(case when cargado = 0 and bimestre = 'bim0' then 1 else 0 end)  as cant_no_cons_0
                , sum(case when cargado = 10 and bimestre= 'bim0' then 1 else 0 end)  as cant_si_cons_0
		, sum(case when cargado = 0 and bimestre = 'bim1' then 1 else 0 end)  as cant_no_cons_1
		, sum(case when cargado = 1 and bimestre = 'bim1' then 1 else 0 end)  as cant_si_cons_1
		, sum(case when cargado = 0 and bimestre = 'bim2' then 1 else 0 end)  as cant_no_cons_2
		, sum(case when cargado = 2 and bimestre = 'bim2' then 1 else 0 end)  as cant_si_cons_2
		, sum(case when cargado = 0 and bimestre = 'bim3' then 1 else 0 end)  as cant_no_cons_3
		, sum(case when cargado = 3 and bimestre = 'bim3' then 1 else 0 end)  as cant_si_cons_3
		, sum(case when cargado = 0 and bimestre = 'bim4' then 1 else 0 end)  as cant_no_cons_4
		, sum(case when cargado = 4 and bimestre = 'bim4' then 1 else 0 end)  as cant_si_cons_4
            from (
             select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim0' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.ini,0) as cargado
                from (
                select *
                from registro_consolidacion where tipo=1 and gestion=(" . $gestion . ")- 1) a
                right join (select *, 10 as ini from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=0) c on a.unidad_educativa=c.cod_ue
                ) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
            select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim1' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and  gestion=(" . $gestion . ")-1) a
		right join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=1) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim2' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and  gestion=(" . $gestion . ")-1) a
		right join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=2) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim3' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and  gestion=(" . $gestion . ")-1) a
		right join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=3) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
                union all
                select *
                from (
                select dep.id as cod_depto, dep.departamento as depto
                ,  b.cod_dis,b.des_dis,c.id,c.institucioneducativa,'bim4' as bimestre, cargado
                from jurisdiccion_geografica a
                inner join (select id,codigo as cod_dis,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) b on a.lugar_tipo_id_distrito=b.id
                inner join institucioneducativa c on a.id=c.le_juridicciongeografica_id
                inner join (select case when a.unidad_educativa is null then c.cod_ue else a.unidad_educativa end,coalesce(c.bimestre,0) as cargado
		from (
		select *
		from registro_consolidacion where tipo=1 and  gestion=(" . $gestion . ")-1) a
		right join (select * from upload_file_control where gestion=cast((" . $gestion . ") as varchar) and bimestre=4) c on a.unidad_educativa=c.cod_ue
		) d on c.id=d.unidad_educativa
		left join distrito_tipo as dt on dt.id = a.distrito_tipo_id
		left join departamento_tipo as dep on dep.id = dt.departamento_tipo_id
		) a
            ) as v
            where cod_dis = '" . $distrito . "'
            group by id
            ) as v2
            left join institucioneducativa as ie on ie.id = v2.codigo order by institucioneducativa
            ");
        */

        $query = $em->getConnection()->prepare("
            with gestion_actual as (select id as gestion_int, id::varchar as gestion_str from gestion_tipo where id = ".$gestion." order by id desc limit 2)
            select ie.id as codigo, ie.id as codigoue, ie.institucioneducativa as nombre --, dis.codigo as distrito_codigo, dis.lugar as distrito
            , sum(case when ufc.b0 >= 1 then 1 else 0 end) as cant_si_cons_0
            , sum(case when ufc.b0 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_0
            , sum(case when ufc.b1 >= 1 then 1 else 0 end) as cant_si_cons_1
            , sum(case when ufc.b1 = 0  or ufc.b1 is null then 1 else 0 end) as cant_no_cons_1
            , sum(case when ufc.b2 >= 1 then 1 else 0 end) as cant_si_cons_2
            , sum(case when ufc.b2 = 0 or ufc.b2 is null then 1 else 0 end) as cant_no_cons_2
            , sum(case when ufc.b3 >= 1 then 1 else 0 end) as cant_si_cons_3
            , sum(case when ufc.b3 = 0 or ufc.b3 is null then 1 else 0 end) as cant_no_cons_3
            , sum(case when ufc.b4 >= 1 then 1 else 0 end) as cant_si_cons_4
            , sum(case when ufc.b4 = 0 or ufc.b4 is null then 1 else 0 end) as cant_no_cons_4
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
            where ie.orgcurricular_tipo_id = 1 AND ie.estadoinstitucion_tipo_id = 10 AND (ie.id <> ALL (ARRAY[1, 2, 3, 4, 5, 6, 7, 8, 9])) AND ie.institucioneducativa_acreditacion_tipo_id = 1 and dis.codigo = '".$distrito."' 
            /*and exists (select iec.institucioneducativa_id from institucioneducativa_curso as iec where iec.gestion_tipo_id in (select gestion_int from gestion_actual) and iec.nivel_tipo_id in (11,12,13) and iec.grado_tipo_id != 0 and ie.id = iec.institucioneducativa_id)*/
            group by ie.id, ie.institucioneducativa
            order by ie.id, ie.institucioneducativa
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

        return $this->render('SieRegularBundle:ReporteUpFile:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
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

        return $this->render('SieRegularBundle:ReporteUpFile:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
    }

    public function valapoderadoAction(Request $request){
        $exist = false;
        $bimestre = '0';
        $datos = array();
        $entity = array();
        $gestion = '';
        //if ($request->getMethod() == 'POST') {
            //$form = $request->get('form');
            //$bimestre = $form['bimestre'];
            //$gestion = $form['gestion'];
            $bimestre=0;
            $gestion=2016;
            $exist = true;
            $entity = $this->consolidacionNivelNacionalValapoderado($gestion, $bimestre);
            // dump($entity);
            // die;
            $datos = $this->chartBarConsolidacionValapoderado($entity, $bimestre, 1);
        //}

        return $this->render('SieRegularBundle:ReporteUpFile:valapoderado.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nivel' => 'departamentos',
                    'nivelnext' => 'distrital',
                    'exist' => $exist,
                    'gestion' => $gestion,
        ));
    }

    public function chartBarConsolidacionValapoderado($entity, $bimestre, $nivel) {
        $totalConsolidado = 0;
        $totalNoConsolidado = 0;
        if ($bimestre == 1) {
            $numeroLiteral = 'Primer';
        } elseif ($bimestre == 2) {
            $numeroLiteral = 'Segundo y Tercer';
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
        $aBimestre = array('0' => 'Inicio de Gestión',
            '1' => '1er Bimestre',
            '2' => '2do Bimestre',
            '3' => '3er Bimestre',
            '4' => '4to Bimestre',
        );
        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos = "
            var chartConsolidacion = new CanvasJS.Chart('chartContainer',
            {
                title:{
                    text: 'Validación de Apoderados',
                    fontSize: 20,
                },
                animationEnabled: true,
                axisX:{
                  title: '',
                  titleFontSize: 20,
                  labelAngle: 60,
                },
                toolTip:{
                    content: '{name}: {y}'
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
                        name: 'Validados',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#004B8D',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadConsolidado = $entity[$i]['cantidadvalidado'];
            $totalConsolidado = $totalConsolidado + $cantidadConsolidado;
            $nombre = $entity[$i]['depto'];
            $datosTemp = $datosTemp . '{y: ' . ($cantidadConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '"},';
        }
        $datos = $datos . '{y: ' . ($totalConsolidado) . ', legendText:"Nacional", label: "Nacional", color: "#2F4F4F"},' . $datosTemp;
        $datos = $datos . "]
                    },
                    {
                        name: 'No Validados',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#4192D9',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadNoConsolidado = $entity[$i]['cantidadnovalidado'];
            $totalNoConsolidado = $totalNoConsolidado + $cantidadNoConsolidado;

            if ($nivel === 1) {
                $nombre = $entity[$i]['depto'];
                $nombreCompleto = $entity[$i]['depto'];
            } else {
                $nombre = $entity[$i]['cod_depto'];
                $nombreCompleto = $entity[$i]['depto'];
            }
            $datosTemp = $datosTemp . '{y: ' . ($cantidadNoConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '", name: "' . ($nombreCompleto) . '"},';
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
     * Reporte Grafico y Detalle de la consolidacion por Distrito - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return typerepr
     */
    public function distritoRegularValapoderadoAction(Request $request) {

        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $depto = $request->get('name');
            $bimestre = $request->get('periodo');
            $gestion = $request->get('gestion');

            $entity = $this->consolidacionNivelDepartamentalValapoderado($id, $gestion);

            $datos = $this->chartBarConsolidacionValapoderado($entity, $bimestre, 2);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteUpFile:valapoderado.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nombre' => $depto,
                    'nivel' => 'distritos',
                    'nivelnext' => 'institucional',
                    'gestion' => $gestion,
                    'exist' => true,
        ));
    }


    /**
     * Reporte Grafico y Detalle de la consolidacion por Unidades Educativas - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function ueRegularValapoderadoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');

            $distrito = $request->get('name');
            $bimestre = $request->get('periodo');
            $gestion = $request->get('gestion');
            $entity = $this->consolidacionNivelDistritalValapoderado($id, $gestion);
            $datos = $this->chartBarConsolidacionValapoderado($entity, $bimestre, 3);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteUpFile:valapoderado.html.twig', array(
                    'periodo' => $bimestre,
                    'dato' => $datos,
                    'entity' => $entity,
                    'nombre' => $distrito,
                    'nivel' => 'unidades educativas',
                    'nivelnext' => 'estudiantil',
                    'exist' => true,
                    'gestion' => $gestion,
        ));
    }

    /**
     * Funcion que busca los archivos consolidados de la gestion actual y los bimestres por departamento
     * @return tabla
     */
    public function consolidacionNivelNacionalValapoderado($gestion, $bimestre) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select cod_depto, depto
            , sum(case coalesce(validado,'f') when 't' then 1 else 0 end) as cantidadvalidado, sum(case coalesce(validado,'f') when 'f' then 1 else 0 end) as cantidadnovalidado
            from (
            select ie.cod_depto, ie.depto, ie.cod_dis, ie.des_dis, ie.cod_ue, ie.institucioneducativa, ei.id
            , (select distinct case when estudiante_inscripcion_id >0 then cast('t' as boolean) else cast('f' as boolean) end from apoderado_inscripcion as ap where  ei.id = ap.estudiante_inscripcion_id  and ap.es_validado = 't' limit 1) as validado
            from estudiante_inscripcion as ei
            inner join (select * from institucioneducativa_curso where gestion_tipo_id = 2016 and nivel_tipo_id in (11,12,13)) as iec on iec.id = ei.institucioneducativa_curso_id
            inner join (
                    select ie2.id as cod_ue, ie2.institucioneducativa as institucioneducativa, cod_dis, des_dis, dt.id as cod_depto, dt.departamento as depto
                    from (select * from institucioneducativa where orgcurricular_tipo_id = 1 and id not in (1, 2, 3, 4, 5, 6, 7, 8, 9)) as ie2
                    inner join jurisdiccion_geografica as jg on jg.id = ie2.le_juridicciongeografica_id
                    inner join (
                    select cod_depto, depto,  cod_prov, prov, cod_sec, sec, cod_can, can, cod_loc, loc, lt5.id as id
                    from (select id, cast(codigo as integer) as cod_depto, lugar as depto from lugar_tipo where lugar_nivel_id = 1) as lt1
                    inner join (select id, codigo as cod_prov, lugar as prov, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 2) as lt2 on lt1.id = lt2.lugar_tipo_id
                    inner join (select id, codigo as cod_sec, lugar as sec, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 3) as lt3 on lt2.id = lt3.lugar_tipo_id
                    inner join (select id, codigo as cod_can, lugar as can, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 4) as lt4 on lt3.id = lt4.lugar_tipo_id
                    inner join (select id, codigo as cod_loc, lugar as loc, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 5) as lt5 on lt4.id = lt5.lugar_tipo_id
                    ) as lt on lt.id = jg.lugar_tipo_id_localidad
                inner join (select id, codigo as cod_dis, lugar as des_dis from lugar_tipo where lugar_nivel_id = 7) as lt7 on lt7.id = jg.lugar_tipo_id_distrito
                    inner join departamento_tipo as dt on dt.id = lt.cod_depto
            ) as ie on ie.cod_ue = iec.institucioneducativa_id
            ) as v
            group by cod_depto, depto
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
    public function consolidacionNivelDepartamentalValapoderado($depto, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select cod_dis as cod_depto, des_dis as depto
            , sum(case coalesce(validado,'f') when 't' then 1 else 0 end) as cantidadvalidado, sum(case coalesce(validado,'f') when 'f' then 1 else 0 end) as cantidadnovalidado
            from (
            select ie.cod_depto, ie.depto, ie.cod_dis, ie.des_dis, ie.cod_ue, ie.institucioneducativa, ei.id
            , (select distinct case when estudiante_inscripcion_id >0 then cast('t' as boolean) else cast('f' as boolean) end from apoderado_inscripcion as ap where  ei.id = ap.estudiante_inscripcion_id  and ap.es_validado = 't' limit 1) as validado
            from estudiante_inscripcion as ei
            inner join (select * from institucioneducativa_curso where gestion_tipo_id = 2016 and nivel_tipo_id in (11,12,13)) as iec on iec.id = ei.institucioneducativa_curso_id
            inner join (
                    select ie2.id as cod_ue, ie2.institucioneducativa as institucioneducativa, cod_dis, des_dis, dt.id as cod_depto, dt.departamento as depto
                    from (select * from institucioneducativa where orgcurricular_tipo_id = 1 and id not in (1, 2, 3, 4, 5, 6, 7, 8, 9)) as ie2
                    inner join jurisdiccion_geografica as jg on jg.id = ie2.le_juridicciongeografica_id
                    inner join (
                    select cod_depto, depto,  cod_prov, prov, cod_sec, sec, cod_can, can, cod_loc, loc, lt5.id as id
                    from (select id, cast(codigo as integer) as cod_depto, lugar as depto from lugar_tipo where lugar_nivel_id = 1) as lt1
                    inner join (select id, codigo as cod_prov, lugar as prov, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 2) as lt2 on lt1.id = lt2.lugar_tipo_id
                    inner join (select id, codigo as cod_sec, lugar as sec, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 3) as lt3 on lt2.id = lt3.lugar_tipo_id
                    inner join (select id, codigo as cod_can, lugar as can, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 4) as lt4 on lt3.id = lt4.lugar_tipo_id
                    inner join (select id, codigo as cod_loc, lugar as loc, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 5) as lt5 on lt4.id = lt5.lugar_tipo_id
                    ) as lt on lt.id = jg.lugar_tipo_id_localidad
                inner join (select id, codigo as cod_dis, lugar as des_dis from lugar_tipo where lugar_nivel_id = 7) as lt7 on lt7.id = jg.lugar_tipo_id_distrito
                    inner join departamento_tipo as dt on dt.id = lt.cod_depto
                    where lt.cod_depto = ".$depto."
            ) as ie on ie.cod_ue = iec.institucioneducativa_id
            ) as v
            group by cod_dis, des_dis
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
    public function consolidacionNivelDistritalValapoderado($distrito, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select cod_ue as cod_depto, institucioneducativa as depto, cod_ue as codigoue
            , sum(case coalesce(validado,'f') when 't' then 1 else 0 end) as cantidadvalidado, sum(case coalesce(validado,'f') when 'f' then 1 else 0 end) as cantidadnovalidado
            from (
            select ie.cod_depto, ie.depto, ie.cod_dis, ie.des_dis, ie.cod_ue, ie.institucioneducativa, ei.id
            , (select distinct case when estudiante_inscripcion_id >0 then cast('t' as boolean) else cast('f' as boolean) end from apoderado_inscripcion as ap where  ei.id = ap.estudiante_inscripcion_id  and ap.es_validado = 't' limit 1) as validado
            from estudiante_inscripcion as ei
            inner join (select * from institucioneducativa_curso where gestion_tipo_id = 2016 and nivel_tipo_id in (11,12,13)) as iec on iec.id = ei.institucioneducativa_curso_id
            inner join (
                    select ie2.id as cod_ue, ie2.institucioneducativa as institucioneducativa, cod_dis, des_dis, dt.id as cod_depto, dt.departamento as depto
                    from (select * from institucioneducativa where orgcurricular_tipo_id = 1 and id not in (1, 2, 3, 4, 5, 6, 7, 8, 9)) as ie2
                    inner join jurisdiccion_geografica as jg on jg.id = ie2.le_juridicciongeografica_id
                    inner join (
                    select cod_depto, depto,  cod_prov, prov, cod_sec, sec, cod_can, can, cod_loc, loc, lt5.id as id
                    from (select id, cast(codigo as integer) as cod_depto, lugar as depto from lugar_tipo where lugar_nivel_id = 1) as lt1
                    inner join (select id, codigo as cod_prov, lugar as prov, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 2) as lt2 on lt1.id = lt2.lugar_tipo_id
                    inner join (select id, codigo as cod_sec, lugar as sec, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 3) as lt3 on lt2.id = lt3.lugar_tipo_id
                    inner join (select id, codigo as cod_can, lugar as can, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 4) as lt4 on lt3.id = lt4.lugar_tipo_id
                    inner join (select id, codigo as cod_loc, lugar as loc, lugar_tipo_id from lugar_tipo where lugar_nivel_id = 5) as lt5 on lt4.id = lt5.lugar_tipo_id
                    ) as lt on lt.id = jg.lugar_tipo_id_localidad
                inner join (select id, codigo as cod_dis, lugar as des_dis from lugar_tipo where lugar_nivel_id = 7) as lt7 on lt7.id = jg.lugar_tipo_id_distrito
                    inner join departamento_tipo as dt on dt.id = lt.cod_depto
                    where lt7.cod_dis = '".$distrito."'
            ) as ie on ie.cod_ue = iec.institucioneducativa_id
            ) as v
            group by cod_ue, institucioneducativa
            ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }


    function pdfValapoderadoAction($codigo_sie,$gestion){
        $arch = $codigo_sie.'_VALIDACION_APODERADO_' . date('Ymd') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_lst_ValidacionApoderado_v1_rcm.rptdesign&__format=pdf&&codigo_ue=' . $codigo_sie .'&&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
