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

/**
 * Vista controller.
 *
 */
class ReporteBachillerDestacadoController extends Controller {

    public function defaultAction() {

        $bimestre = 1;
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity, 1);
        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacion.html.twig', array('periodo' => $bimestre, 'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
    }

    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Regular
     * Jurlan
     */
    public function departamentoRegularAction($bimestre) {
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity, 1);
        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacion.html.twig', array('periodo' => $bimestre, 'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
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

            $datos = $this->chartBarConsolidacion($entity, 2);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacion.html.twig', array('periodo' => $bimestre, 'dato' => $datos, 'entity' => $entity, 'nombre' => $depto, 'nivel' => 'distritos', 'nivelnext' => 'institucional'));
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
            $datos = $this->chartBarConsolidacion($entity, 3);
        } else {
            return $this->redirectToRoute('reporte_depto_regular');
        }
        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacion.html.twig', array('periodo' => $bimestre, 'dato' => $datos, 'entity' => $entity, 'nombre' => $distrito, 'nivel' => 'unidades educativas', 'nivelnext' => 'estudiantil'));
    }

    /**
     * Funcion que retorna el Reporte Grafico Chart Jquery de tipo Bar - Educacion Regular
     * Jurlan
     * @param Request $entity
     * @return repr
     */
    public function chartBarConsolidacion($entity, $nivel) {
        $totalConsolidado = 0;
        $totalNoConsolidado = 0;

        /**
         * variable que contiene el codigo jquery del reporte grafico
         */
        $datos = "  
            var chartConsolidacion = new CanvasJS.Chart('chartContainer',
            {
                title:{
                    text: 'Registro Cuenta Bancaria', 
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
                        name: 'Registrados',
                        showInLegend: true,
                        type: 'stackedColumn100', 
                        color: '#004B8D',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadConsolidado = $entity[$i]['consolidado'];
            $totalConsolidado = $totalConsolidado + $cantidadConsolidado;
            $nombre = $entity[$i]['nombre'];
            $datosTemp = $datosTemp . '{y: ' . ($cantidadConsolidado) . ', legendText:"' . ($nombre) . '", label: "' . ($nombre) . '"},';
        }
        $datos = $datos . '{y: ' . ($totalConsolidado) . ', legendText:"Nacional", label: "Nacional", color: "#2F4F4F"},' . $datosTemp;
        $datos = $datos . "]
                    }, 
                    {        
                        name: 'No Registrados',
                        showInLegend: true,
                        type: 'stackedColumn100',
                        color: '#4192D9',
                        dataPoints: [   ";
        $datosTemp = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidadNoConsolidado = $entity[$i]['noconsolidado'];
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
    public function consolidacionNivelNacional() {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare(" 
                SELECT t1.codigo as codigo, t1.departamento as nombre, sum(case when registro = 'SI' then 1 else 0 end) as consolidado, sum(case when registro = 'NO' then 1 else 0 end) as noconsolidado
FROM(
SELECT DISTINCT
'SI' AS Registro,departamento_tipo.id codigo,departamento_tipo.departamento,distrito_tipo.id,distrito_tipo.distrito,institucioneducativa.id,institucioneducativa.institucioneducativa
,maestro_cuentabancaria.carnet,maestro_cuentabancaria.paterno,maestro_cuentabancaria.materno,maestro_cuentabancaria.nombre,maestro_cuentabancaria.complemento,entidadfinanciera_tipo.entidadfinanciera,maestro_cuentabancaria.cuentabancaria
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND institucioneducativa_curso.gestion_tipo_id = 2015 AND institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'
UNION ALL

SELECT DISTINCT
'NO' AS Registro,departamento_tipo.id codigo,departamento_tipo.departamento,distrito_tipo.id,distrito_tipo.distrito,institucioneducativa.id,institucioneducativa.institucioneducativa,
'' carnet,'' AS paterno,'' AS materno,'' AS nombre,'' AS complemento,'' AS entidadfinanciera,'' AS cuentabancaria FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6  AND
institucioneducativa.id NOT IN 
(
SELECT DISTINCT institucioneducativa.id
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'
)
) as t1
GROUP BY t1.codigo, t1.departamento;
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
        $query = $em->getConnection()->prepare("SELECT t1.codigo as codigo, t1.distrito as nombre, sum(case when registro = 'SI' then 1 else 0 end) as consolidado, sum(case when registro = 'NO' then 1 else 0 end) as noconsolidado
FROM(
SELECT DISTINCT
'SI' AS Registro,departamento_tipo.id depto,departamento_tipo.departamento,distrito_tipo.id codigo,distrito_tipo.distrito,institucioneducativa.id,institucioneducativa.institucioneducativa
,maestro_cuentabancaria.carnet,maestro_cuentabancaria.paterno,maestro_cuentabancaria.materno,maestro_cuentabancaria.nombre,maestro_cuentabancaria.complemento,entidadfinanciera_tipo.entidadfinanciera,maestro_cuentabancaria.cuentabancaria
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND institucioneducativa_curso.gestion_tipo_id = 2015 AND institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'

UNION ALL

SELECT DISTINCT
'NO' AS Registro,departamento_tipo.id codigo,departamento_tipo.departamento,distrito_tipo.id,distrito_tipo.distrito,institucioneducativa.id,institucioneducativa.institucioneducativa,
'' carnet,'' AS paterno,'' AS materno,'' AS nombre,'' AS complemento,'' AS entidadfinanciera,'' AS cuentabancaria FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6  AND
institucioneducativa.id NOT IN 
(
SELECT DISTINCT institucioneducativa.id
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'
)
) as t1
WHERE t1.depto = ".$depto."
GROUP BY t1.codigo, t1.distrito;");
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
        $query = $em->getConnection()->prepare("SELECT t1.codigo as codigo, t1.institucioneducativa as nombre, sum(case when registro = 'SI' then 1 else 0 end) as consolidado, sum(case when registro = 'NO' then 1 else 0 end) as noconsolidado
FROM(
SELECT DISTINCT
'SI' AS Registro,departamento_tipo.id depto,departamento_tipo.departamento,distrito_tipo.id dist,distrito_tipo.distrito,institucioneducativa.id codigo,institucioneducativa.institucioneducativa
,maestro_cuentabancaria.carnet,maestro_cuentabancaria.paterno,maestro_cuentabancaria.materno,maestro_cuentabancaria.nombre,maestro_cuentabancaria.complemento,entidadfinanciera_tipo.entidadfinanciera,maestro_cuentabancaria.cuentabancaria
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND institucioneducativa_curso.gestion_tipo_id = 2015 AND institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'

UNION ALL

SELECT DISTINCT
'NO' AS Registro,departamento_tipo.id codigo,departamento_tipo.departamento,distrito_tipo.id,distrito_tipo.distrito,institucioneducativa.id,institucioneducativa.institucioneducativa,
'' carnet,'' AS paterno,'' AS materno,'' AS nombre,'' AS complemento,'' AS entidadfinanciera,'' AS cuentabancaria FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN jurisdiccion_geografica ON institucioneducativa.le_juridicciongeografica_id = jurisdiccion_geografica.id
INNER JOIN distrito_tipo ON distrito_tipo.id = jurisdiccion_geografica.distrito_tipo_id
INNER JOIN departamento_tipo ON distrito_tipo.departamento_tipo_id = departamento_tipo.id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6  AND
institucioneducativa.id NOT IN 
(
SELECT DISTINCT institucioneducativa.id
FROM
institucioneducativa
INNER JOIN institucioneducativa_curso ON institucioneducativa_curso.institucioneducativa_id = institucioneducativa.id
INNER JOIN maestro_cuentabancaria ON maestro_cuentabancaria.institucioneducativa_id = institucioneducativa.id
INNER JOIN entidadfinanciera_tipo ON entidadfinanciera_tipo.id = maestro_cuentabancaria.entidadfinanciera_tipo_id
WHERE
institucioneducativa_curso.nivel_tipo_id = 13 AND
institucioneducativa_curso.gestion_tipo_id = 2015 AND
institucioneducativa_curso.grado_tipo_id = 6 AND maestro_cuentabancaria.esoficial = 't'
)
) as t1
WHERE t1.dist = ".$distrito."
GROUP BY t1.codigo, t1.institucioneducativa;");
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

        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
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

        return $this->render('SieRegularBundle:ReporteBachillerDestacado:consolidacionEspecial.html.twig', array('dato1' => $datos1, 'dato2' => $datos2, 'dato3' => $datos3, 'dato4' => $datos4, 'dato5' => $datos5, 'dato6' => $datos6));
    }

}
