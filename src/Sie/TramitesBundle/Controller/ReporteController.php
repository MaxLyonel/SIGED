<?php

namespace Sie\TramitesBundle\Controller;

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
class ReporteController extends Controller {
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que muestra la cantidad de certificados tecnicos impresos de educacion alternativa a nivel nacional
    // PARAMETROS: gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certificadoTecnicoAlternativaNacional($gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            with tabla as (
                select lt4.id as id, lt4.codigo as codigo, lt4.lugar as lugar
                , sum(case d.documento_tipo_id when 6 then 1 else 0 end) as cantidad_basico
                , sum(case d.documento_tipo_id when 7 then 1 else 0 end) as cantidad_auxiliar
                , sum(case d.documento_tipo_id when 8 then 1 else 0 end) as cantidad_medio
                , count(*) as cantidad_total
                from documento as d 
                inner join documento_serie as ds on ds.id = d.documento_serie_id
                inner join tramite as t on t.id = d.tramite_id
                inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                where d.documento_tipo_id in (6,7,8) and ds.gestion_id = ".$gestion."
                group by lt4.id, lt4.codigo, lt4.lugar
            )

            select id, codigo, lugar, cantidad_basico, cantidad_auxiliar, cantidad_medio, cantidad_total
            , round(cantidad_basico::numeric * 100/cantidad_total::numeric,1) as porcentaje_basico 
            , round(cantidad_auxiliar::numeric * 100/cantidad_total::numeric,1) as porcentaje_auxiliar 
            , round(cantidad_medio::numeric * 100/cantidad_total::numeric,1) as porcentaje_medio
            from tabla
            union all 
            select 1, '0', 'Bolivia', sum(cantidad_basico) as cantidad_basico, sum(cantidad_auxiliar) as cantidad_auxiliar, sum(cantidad_medio) as cantidad_medio, sum(cantidad_total) as cantidad_total
            , round( sum(cantidad_basico)::numeric * 100/sum(cantidad_total)::numeric,1) as porcentaje_basico 
            , round(sum(cantidad_auxiliar)::numeric * 100/sum(cantidad_total)::numeric,1) as porcentaje_auxiliar 
            , round(sum(cantidad_medio)::numeric * 100/sum(cantidad_total)::numeric,1) as porcentaje_medio
            from tabla
            order by id
        ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de un departamento, segun la gestion seleccionada por distrito
     * @return tabla
     */
    public function diplomaBachillerNivelDepartamental($depto, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select ltd.id as codigo, ltd.lugar as nombre,   ltd.obs as abreviatura,
                SUM(CASE nivel_tipo_id WHEN 3 THEN cantidad WHEN 13 THEN cantidad ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN cantidad WHEN 15 THEN cantidad ELSE 0 END) as cant2,
                SUM(cantidad) as total from (
                SELECT 
                    iec.nivel_tipo_id as nivel_tipo_id, 
                    lt5.id as distrito_tipo_id,
                    /*lt5.id as distrito_tipo_id, 
                    ie.id as institucioneducativa_id,*/
                    count(*) as cantidad
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and documento_tipo_id = 1 and iec.nivel_tipo_id in (3,5,13,15) and lt4.codigo = '".$depto."'
                group by lt5.id, /*lt5.id, ie.id, */iec.nivel_tipo_id
                ) as v 
                inner join lugar_tipo as ltd on ltd.id = v.distrito_tipo_id
                group by ltd.id, ltd.lugar,  ltd.obs
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de un distrito, segun la gestion seleccionada por ue
     * @return tabla
     */
    public function diplomaBachillerNivelDistrital($distrito, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select ie.id as codigo, ie.institucioneducativa as nombre,  ie.id as abreviatura, 
                SUM(CASE nivel_tipo_id WHEN 3 THEN cantidad WHEN 13 THEN cantidad ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN cantidad WHEN 15 THEN cantidad ELSE 0 END) as cant2,
                SUM(cantidad) as total from (
                SELECT 
                    iec.nivel_tipo_id as nivel_tipo_id, 
                    /*lt5.id as distrito_tipo_id,
                    lt5.id as distrito_tipo_id,*/
                    ie.id as institucioneducativa_id,
                    count(*) as cantidad
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and documento_tipo_id = 1 and iec.nivel_tipo_id in (3,5,13,15) and lt5.id = '".$distrito."'
                group by /*lt5.id, lt5.id, */ie.id, iec.nivel_tipo_id
                ) as v 
                INNER JOIN institucioneducativa as ie on ie.id = v.institucioneducativa_id
                group by ie.id, ie.institucioneducativa
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de una ue, segun la gestion seleccionada por estudiante
     * @return tabla
     */
    public function diplomaBachillerNivelInstitucional($ue, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select codigo_rude as codigo, estudiante as nombre, '' as abreviatura,
                SUM(CASE nivel_tipo_id WHEN 3 THEN 1 WHEN 13 THEN 1 ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN 1 WHEN 15 THEN 1 ELSE 0 END) as cant2,
                COUNT(*) as total from (
                SELECT 
                        d.documento_serie_id as serie,
                        e.codigo_rude as codigo_rude,
                        e.nombre || ' ' || e.paterno || ' ' || e.materno as estudiante,
                        iec.nivel_tipo_id as nivel_tipo_id, 
                        ie.id as ue_id,
                        ie.institucioneducativa as ue,
                        lt5.id as distrito_tipo_id, 
                        lt5.lugar as distrito,
                        lt4.codigo as departamento_tipo_id,
                        lt4.lugar as departamento,
                        lt4.obs as abreviatura,
                        iec.gestion_tipo_id
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and documento_tipo_id = 1 and iec.nivel_tipo_id in (3,5,13,15) and ie.id = ".$ue."
                ) as v
                group by codigo_rude, estudiante
                order by estudiante
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que halla las gestiones habilitadas en la base de datos
     * @return tabla
     */
    public function gestionesEntidad(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from gestion_tipo order by id desc");
        $query->execute();
        $entity = $query->fetchAll();
        return $entity;
    }
}