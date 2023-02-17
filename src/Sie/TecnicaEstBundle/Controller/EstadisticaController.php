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

        //dump($request->query->get('gestion')); die;

        if ($request->query->get('gestion') == null) {
            $gestionActual = 2022; //$fechaActual->format('Y');
        }else{
            $gestionActual = $request->query->get('gestion');
        }

        
        
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


        $tbl_titulados_egresados_publicas = $this->tbl_titulados_egresados_publicas($gestionActual);
        $tbl_titulados_egresados_privadas = $this->tbl_titulados_egresados_privadas($gestionActual);
        
        $tbl_becas_publicas = $this->tbl_becas_publicas($gestionActual);
        $tbl_becas_privadas = $this->tbl_becas_privadas($gestionActual);

        $tbl_matriculas_publicas = $this->tbl_matriculas_publicas($gestionActual);
        $tbl_matriculas_privadas = $this->tbl_matriculas_privadas($gestionActual);

        $tbl_personal_publicas = $this->tbl_personal_publicas($gestionActual);
        $tbl_personal_privadas = $this->tbl_personal_privadas($gestionActual);
       
        return $this->render('SieTecnicaEstBundle:Estadistica:index.html.twig', array(
            'titulo' => "Estadística",
            "data" => $dataArray,
            "tbl_titulados_egresados_publicas" => $tbl_titulados_egresados_publicas,
            "tbl_titulados_egresados_privadas" => $tbl_titulados_egresados_privadas,
            "tbl_becas_publicas" => $tbl_becas_publicas,
            "tbl_becas_privadas" => $tbl_becas_privadas,
            "tbl_matriculas_publicas" => $tbl_matriculas_publicas,
            "tbl_matriculas_privadas" => $tbl_matriculas_privadas,
            "tbl_personal_publicas" => $tbl_personal_publicas,
            "tbl_personal_privadas" => $tbl_personal_privadas,
            "gestionActual" => $gestionActual

        ));
    }

    public function indexListadoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $id_usuario = $this->session->get('userId');     
        $sedes = array();

        // totales que cerraron
       
        $total_cerradas = 0;


        $sql = "
        select 
            *,
            case
                when operativo = 0 then 'CERRADO'
                else 'ABIERTO'
            end as estado
            from 
            (
            SELECT
                est_tec_instituto.id, 	
                est_tec_instituto.instituto, 	
                est_tec_sede.id as sede_id, 
                est_tec_sede.sede, 
                est_tec_naturalezajuridica_tipo.naturalezajuridica,
                (select count(*) from est_tec_registro_consolidacion
                        where est_tec_sede_id = est_tec_sede.id and activo = true
                        ) as operativo
            FROM
                est_tec_instituto
                INNER JOIN
                est_tec_sede
                ON 
                    est_tec_instituto.id = est_tec_sede.est_tec_instituto_id
                INNER JOIN
                est_tec_naturalezajuridica_tipo
                ON 	
                    est_tec_sede.est_tec_naturalezajuridica_tipo_id = est_tec_naturalezajuridica_tipo.id
                order by 2,4
                ) as data
        ";

        $stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$data = $stmt->fetchAll();

        $total_registros = sizeof($data);
        

        $total_cerradas = 0;
        for ($i=0; $i < sizeof($data); $i++) {            
            $data[$i]['id'] = bin2hex(serialize($data[$i]['sede_id']));              
            if($data[$i]['estado'] == 'CERRADO'){
                $total_cerradas++;
            }

        }

        return $this->render('SieTecnicaEstBundle:Estadistica:indexlistado.html.twig', array(       
            'titulo' => "Sedes",
            'data' => $data,
            'total_cerradas' =>  $total_cerradas,
            'total_registros' => $total_registros,
           

        ));


        return $this->render('SieTecnicaEstBundle:Estadistica:indexlistado.html.twig', array(
            'titulo' => "Estadística",
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

    private function tbl_titulados_egresados_publicas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            select  gestion_tipo_id, estado_final, sum(cantidadm) as total_masculino, sum(cantidadf) as total_femenino
            FROM
            (
            select gestion_tipo_id, estado_final, sum(cantidadm) as cantidadm, 0 as cantidadf
            from
            (
            SELECT
                est_tec_egresado_titulados_tipo.estado_final, 
                est_tec_instituto_carrera_estudiante_egresado_titulado.gestion_tipo_id, 	
                est_tec_instituto_carrera_estudiante_egresado_titulado.cantidad as cantidadm, 
                0 as cantidadf
            FROM
                est_tec_egresado_titulados_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_egresado_titulado
                ON 
                    est_tec_egresado_titulados_tipo.id = est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId and  genero_tipo_id = 1 and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
            ) as datos
            group by gestion_tipo_id, estado_final
            
            union all
            
            select gestion_tipo_id, estado_final, 0 as cantidadm,  sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_egresado_titulados_tipo.estado_final, 
                est_tec_instituto_carrera_estudiante_egresado_titulado.gestion_tipo_id, 	
                0 as cantidadm,
                est_tec_instituto_carrera_estudiante_egresado_titulado.cantidad as cantidadf
                
            FROM
                est_tec_egresado_titulados_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_egresado_titulado
                ON 
                    est_tec_egresado_titulados_tipo.id = est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId  and genero_tipo_id = 2 and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
            ) as datos
            group by gestion_tipo_id, estado_final
            ) as data2 GROUP BY gestion_tipo_id , estado_final
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

    private function tbl_titulados_egresados_privadas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            select  gestion_tipo_id, estado_final, sum(cantidadm) as total_masculino, sum(cantidadf) as total_femenino
            FROM
            (
            select gestion_tipo_id, estado_final, sum(cantidadm) as cantidadm, 0 as cantidadf
            from
            (
            SELECT
                est_tec_egresado_titulados_tipo.estado_final, 
                est_tec_instituto_carrera_estudiante_egresado_titulado.gestion_tipo_id, 	
                est_tec_instituto_carrera_estudiante_egresado_titulado.cantidad as cantidadm, 
                0 as cantidadf
            FROM
                est_tec_egresado_titulados_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_egresado_titulado
                ON 
                    est_tec_egresado_titulados_tipo.id = est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId and  genero_tipo_id = 1 and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
            ) as datos
            group by gestion_tipo_id, estado_final
            
            union all
            
            select gestion_tipo_id, estado_final, 0 as cantidadm,  sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_egresado_titulados_tipo.estado_final, 
                est_tec_instituto_carrera_estudiante_egresado_titulado.gestion_tipo_id, 	
                0 as cantidadm,
                est_tec_instituto_carrera_estudiante_egresado_titulado.cantidad as cantidadf
                
            FROM
                est_tec_egresado_titulados_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_egresado_titulado
                ON 
                    est_tec_egresado_titulados_tipo.id = est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId  and genero_tipo_id = 2 and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
            ) as datos
            group by gestion_tipo_id, estado_final
            ) as data2 GROUP BY gestion_tipo_id , estado_final
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

    private function tbl_becas_publicas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
        select gestion_tipo_id,nacionalidad_beca, sum(cantidadm) as total_becas_masculino, sum(cantidadf) as total_becas_femenino 
        from
        (
        SELECT
            est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca, 
            est_tec_instituto_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.genero_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.cantidad as cantidadm,
            0 as cantidadf
        FROM
            est_tec_matricula_nacionalidad_beca_tipo
            INNER JOIN
            est_tec_instituto_carrera_estudiante_nacionalidad
            ON 
                est_tec_matricula_nacionalidad_beca_tipo.id = est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id
            INNER JOIN
            est_tec_instituto_carrera
            ON 
                est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
                
            where genero_tipo_id = 1 and gestion_tipo_id = :gestionId
            and est_tec_instituto_carrera.est_tec_sede_id in 
            ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
            
            union all
            
            SELECT
            est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca, 
            est_tec_instituto_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.genero_tipo_id, 
            0 as cantidadm,
            est_tec_instituto_carrera_estudiante_nacionalidad.cantidad  as cantidadf
        FROM
            est_tec_matricula_nacionalidad_beca_tipo
            INNER JOIN
            est_tec_instituto_carrera_estudiante_nacionalidad
            ON 
                est_tec_matricula_nacionalidad_beca_tipo.id = est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id
            INNER JOIN
            est_tec_instituto_carrera
            ON 
                est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            where genero_tipo_id = 2 and gestion_tipo_id = :gestionId
            and est_tec_instituto_carrera.est_tec_sede_id in 
            ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
            
            ) as data
            group by gestion_tipo_id, nacionalidad_beca
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

    private function tbl_becas_privadas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
        select gestion_tipo_id,nacionalidad_beca, sum(cantidadm) as total_becas_masculino, sum(cantidadf) as total_becas_femenino 
        from
        (
        SELECT
            est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca, 
            est_tec_instituto_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.genero_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.cantidad as cantidadm,
            0 as cantidadf
        FROM
            est_tec_matricula_nacionalidad_beca_tipo
            INNER JOIN
            est_tec_instituto_carrera_estudiante_nacionalidad
            ON 
                est_tec_matricula_nacionalidad_beca_tipo.id = est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id
            INNER JOIN
            est_tec_instituto_carrera
            ON 
                est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
                
            where genero_tipo_id = 1 and gestion_tipo_id = :gestionId
            and est_tec_instituto_carrera.est_tec_sede_id in 
            ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
            
            union all
            
            SELECT
            est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca, 
            est_tec_instituto_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.genero_tipo_id, 
            0 as cantidadm,
            est_tec_instituto_carrera_estudiante_nacionalidad.cantidad  as cantidadf
        FROM
            est_tec_matricula_nacionalidad_beca_tipo
            INNER JOIN
            est_tec_instituto_carrera_estudiante_nacionalidad
            ON 
                est_tec_matricula_nacionalidad_beca_tipo.id = est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id
            INNER JOIN
            est_tec_instituto_carrera
            ON 
                est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            where genero_tipo_id = 2 and gestion_tipo_id = :gestionId
            and est_tec_instituto_carrera.est_tec_sede_id in 
            ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
            
            ) as data
            group by gestion_tipo_id, nacionalidad_beca
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

    private function tbl_matriculas_publicas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            select gestion_tipo_id, estadomatricula, sum(cantidadm) as totalmasculino, sum(cantidadf) as totalfemenino
            from
            (
            SELECT
                est_tec_estadomatricula_tipo.estadomatricula, 
                est_tec_instituto_carrera_estudiante_estado.gestion_tipo_id, 
                est_tec_instituto_carrera_estudiante_estado.cantidad AS cantidadm, 
                0 AS cantidadf
            FROM
                est_tec_estadomatricula_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_estado
                ON 
                    est_tec_estadomatricula_tipo.id = est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_estado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId AND
                genero_tipo_id = 1
                and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
                
            union ALL
            
            SELECT
                est_tec_estadomatricula_tipo.estadomatricula, 
                est_tec_instituto_carrera_estudiante_estado.gestion_tipo_id, 
                0 AS cantidadm, 
                est_tec_instituto_carrera_estudiante_estado.cantidad AS cantidadf
            FROM
                est_tec_estadomatricula_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_estado
                ON 
                    est_tec_estadomatricula_tipo.id = est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_estado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId AND
                genero_tipo_id = 2
                and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 1)
                ) as datos
                group by gestion_tipo_id, estadomatricula
            order by 2
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

    private function tbl_matriculas_privadas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("            
            select gestion_tipo_id, estadomatricula, sum(cantidadm) as totalmasculino, sum(cantidadf) as totalfemenino
            from
            (
            SELECT
                est_tec_estadomatricula_tipo.estadomatricula, 
                est_tec_instituto_carrera_estudiante_estado.gestion_tipo_id, 
                est_tec_instituto_carrera_estudiante_estado.cantidad AS cantidadm, 
                0 AS cantidadf
            FROM
                est_tec_estadomatricula_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_estado
                ON 
                    est_tec_estadomatricula_tipo.id = est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_estado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId AND
                genero_tipo_id = 1
                and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
                
            union ALL
            
            SELECT
                est_tec_estadomatricula_tipo.estadomatricula, 
                est_tec_instituto_carrera_estudiante_estado.gestion_tipo_id, 
                0 AS cantidadm, 
                est_tec_instituto_carrera_estudiante_estado.cantidad AS cantidadf
            FROM
                est_tec_estadomatricula_tipo
                INNER JOIN
                est_tec_instituto_carrera_estudiante_estado
                ON 
                    est_tec_estadomatricula_tipo.id = est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id
                INNER JOIN
                est_tec_instituto_carrera
                ON 
                    est_tec_instituto_carrera_estudiante_estado.est_tec_instituto_carrera_id = est_tec_instituto_carrera.id
            WHERE
                gestion_tipo_id = :gestionId AND
                genero_tipo_id = 2
                and est_tec_instituto_carrera.est_tec_sede_id in 
                ( select id from est_tec_sede where est_tec_naturalezajuridica_tipo_id = 2)
                ) as datos
                group by gestion_tipo_id, estadomatricula
            order by 2
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

    private function tbl_personal_publicas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare(" 
            select gestion_tipo_id,cargo, sum(cantidadm) as total_masculino, sum(cantidadf) as total_femenino
            from
            (
            select gestion_tipo_id,cargo, sum(cantidad) as cantidadm, sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_instituto_sede_docente_adm.id, 
                est_tec_instituto_sede_docente_adm.est_tec_sede_id, 
                est_tec_instituto_sede_docente_adm.gestion_tipo_id, 
                est_tec_instituto_sede_docente_adm.genero_tipo_id, 
                est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id, 
                est_tec_instituto_sede_docente_adm.cantidad, 
                0 as cantidadf,
                est_tec_cargo_tipo.cargo
            FROM
                est_tec_instituto_sede_docente_adm
                INNER JOIN
                est_tec_sede
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_sede_id = est_tec_sede.id AND
                    est_tec_sede.est_tec_naturalezajuridica_tipo_id = 1
                INNER JOIN
                est_tec_cargo_tipo
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id = est_tec_cargo_tipo.id
            WHERE
                est_tec_cargo_tipo_id IN (1,2,4,6,10,8)
                and gestion_tipo_id = :gestionId	
                ) as data  where genero_tipo_id = 1
                GROUP BY gestion_tipo_id,cargo
                
                union all
                
                select gestion_tipo_id,cargo, sum(cantidad) as cantidadm, sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_instituto_sede_docente_adm.id, 
                est_tec_instituto_sede_docente_adm.est_tec_sede_id, 
                est_tec_instituto_sede_docente_adm.gestion_tipo_id, 
                est_tec_instituto_sede_docente_adm.genero_tipo_id, 
                est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id, 
                est_tec_instituto_sede_docente_adm.cantidad as cantidadf, 
                0 as cantidad,
                est_tec_cargo_tipo.cargo
            FROM
                est_tec_instituto_sede_docente_adm
                INNER JOIN
                est_tec_sede
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_sede_id = est_tec_sede.id AND
                    est_tec_sede.est_tec_naturalezajuridica_tipo_id = 1
                INNER JOIN
                est_tec_cargo_tipo
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id = est_tec_cargo_tipo.id
            WHERE
                est_tec_cargo_tipo_id IN (1,2,4,6,10,8)
                and gestion_tipo_id = :gestionId	
                ) as data  where genero_tipo_id = 2
                GROUP BY gestion_tipo_id,cargo
                ) as data2
                GROUP BY gestion_tipo_id,cargo
                order by 2
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

    private function tbl_personal_privadas($gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare(" 
            select gestion_tipo_id,cargo, sum(cantidadm) as total_masculino, sum(cantidadf) as total_femenino
            from
            (
            select gestion_tipo_id,cargo, sum(cantidad) as cantidadm, sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_instituto_sede_docente_adm.id, 
                est_tec_instituto_sede_docente_adm.est_tec_sede_id, 
                est_tec_instituto_sede_docente_adm.gestion_tipo_id, 
                est_tec_instituto_sede_docente_adm.genero_tipo_id, 
                est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id, 
                est_tec_instituto_sede_docente_adm.cantidad, 
                0 as cantidadf,
                est_tec_cargo_tipo.cargo
            FROM
                est_tec_instituto_sede_docente_adm
                INNER JOIN
                est_tec_sede
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_sede_id = est_tec_sede.id AND
                    est_tec_sede.est_tec_naturalezajuridica_tipo_id = 2
                INNER JOIN
                est_tec_cargo_tipo
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id = est_tec_cargo_tipo.id
            WHERE
                est_tec_cargo_tipo_id IN (1,7,3,5,11,9)
                and gestion_tipo_id = :gestionId	
                ) as data  where genero_tipo_id = 1
                GROUP BY gestion_tipo_id,cargo
                
                union all
                
                select gestion_tipo_id,cargo, sum(cantidad) as cantidadm, sum(cantidadf) as cantidadf
            from
            (
            SELECT
                est_tec_instituto_sede_docente_adm.id, 
                est_tec_instituto_sede_docente_adm.est_tec_sede_id, 
                est_tec_instituto_sede_docente_adm.gestion_tipo_id, 
                est_tec_instituto_sede_docente_adm.genero_tipo_id, 
                est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id, 
                est_tec_instituto_sede_docente_adm.cantidad as cantidadf, 
                0 as cantidad,
                est_tec_cargo_tipo.cargo
            FROM
                est_tec_instituto_sede_docente_adm
                INNER JOIN
                est_tec_sede
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_sede_id = est_tec_sede.id AND
                    est_tec_sede.est_tec_naturalezajuridica_tipo_id = 2
                INNER JOIN
                est_tec_cargo_tipo
                ON 
                    est_tec_instituto_sede_docente_adm.est_tec_cargo_tipo_id = est_tec_cargo_tipo.id
            WHERE
                est_tec_cargo_tipo_id IN (1,7,3,5,11,9)
                and gestion_tipo_id = :gestionId	
                ) as data  where genero_tipo_id = 2
                GROUP BY gestion_tipo_id,cargo
                ) as data2
                GROUP BY gestion_tipo_id,cargo
                order by 2
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