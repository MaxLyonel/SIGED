<?php

namespace Sie\TecnicaEstBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class EstadisticaController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        

        $estudiantes = $this->cantidadEstudiantes($gestionActual);
        $dataArrayEstudiantes = $this->convertirDatosJson($estudiantes);

        $institutos = $this->cantidadInstitutos($gestionActual);
        $dataArrayInstitutos = $this->convertirDatosJson($institutos);

        $personas = $this->cantidadPersonal($gestionActual);
        $dataArrayPersonas = $this->convertirDatosJson($personas);
        //$dataArray = $this->asignarTotalesDatosJson($dataArray);

        $dataArray = array_merge($dataArrayEstudiantes,$dataArrayInstitutos,$dataArrayPersonas);

        return $this->render('SieTecnicaEstBundle:Estadistica:index.html.twig', array(
            'titulo' => "Estadística",
            "data" => $dataArray,
        ));
    }

    private function convertirDatosJson($data){
        $dataArray = array();
        foreach ($data as $key => $row){
            if(isset($dataArray[$row['tipo_nombre']][0])){
                $dataArray[$row['tipo_nombre']][0]['cantidad'] = $dataArray[$row['tipo_nombre']][0]['cantidad'] + $row['cantidad']; 
            } else {
                $dataArray[$row['tipo_nombre']][0] = array('nombre'=>'TOTAL','cantidad'=>$row['cantidad']); 
            }
            $dataArray[$row['tipo_nombre']][] = array('nombre'=>$row['detalle'],'cantidad'=>(int)$row['cantidad']); 
        }
        return $dataArray;
    }


    private function cantidadEstudiantes($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            with tabla as (
                select emt.id as estadomatricula_id, emt.estadomatricula, gt.id as genero_id, gt.genero, sum(cantidad) as cantidad
                from est_tec_instituto_carrera_estudiante_estado as icee
                inner join est_tec_estadomatricula_tipo as emt on emt.id = est_tec_estadomatricula_tipo_id
                inner join genero_tipo as gt on gt.id = icee.genero_tipo_id
                where icee.gestion_tipo_id = :gestionId
                group by emt.id, emt.estadomatricula, gt.id, gt.genero
            )
              
            select 1 as tipo_id, 'inscripcion' as tipo_nombre, estadomatricula as detalle, sum(cantidad) as cantidad from tabla where estadomatricula_id in (1,2) group by estadomatricula_id, estadomatricula
            union all       
            select 2 as tipo_id, 'genero' as tipo_nombre, genero as detalle, sum(cantidad) as cantidad from tabla where estadomatricula_id in (1,2) group by genero_id, genero
            union all
            select 3 as tipo_id, 'matricula' as tipo_nombre, estadomatricula as detalle, sum(cantidad) as cantidad from tabla where estadomatricula_id not in (1,2) group by estadomatricula_id, estadomatricula
            order by 1,2,3
        "); 
        $query->bindValue(':gestionId', $gestion);
        $query->execute();
        $objEntidad = $query->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad;
        } else {
            return array();
        }
    }

    private function cantidadInstitutos($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            with tabla as (
                select categoriainstituto as nombre, count(categoriainstituto) as cantidad
                from est_tec_instituto as i
                inner join est_tec_categoria_tipo as ct on ct.id = i.est_tec_categoria_tipo_id
                group by ct.categoriainstituto
            )
              
            select 1 as tipo_id, 'categoria' as tipo_nombre, nombre as detalle, cantidad from tabla
            order by 1,2,3
        "); 
        $query->execute();
        $objEntidad = $query->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad;
        } else {
            return array();
        }
    }

    private function cantidadPersonal($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
        with tabla as (
            select case ct.es_docente when true then 'DOCENTE' else 'ADMINISTRATIVO' end as nombre, sum(cantidad) as cantidad
            from est_tec_instituto_sede_docente_adm as isda
            inner join est_tec_cargo_tipo as ct on ct.id = isda.est_tec_cargo_tipo_id
            where isda.gestion_tipo_id = :gestionId
            group by ct.es_docente
        )
          
        select 1 as tipo_id, 'cargo' as tipo_nombre, nombre as detalle, cantidad from tabla
        order by 1,2,3
        "); 
        $query->bindValue(':gestionId', $gestion);
        $query->execute();
        $objEntidad = $query->fetchAll(); 

        if (count($objEntidad)>0){
            return $objEntidad;
        } else {
            return array();
        }
    }

    public function chartDonut($titulo,$subTitulo,$nombreLabel,$contenedor,$name,$datos) {
        
        
            foreach ($entity['dato'] as $key => $dato) {
                $porcentaje = 0;
                if ($key == 0){
                    $subTotal = $dato['cantidad'];
                } else {
                    $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                    $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
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

}