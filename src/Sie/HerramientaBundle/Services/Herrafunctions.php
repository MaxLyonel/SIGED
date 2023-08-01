<?php
namespace Sie\HerramientaBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;


/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for load all additional functionalities; if exist in a particular proccess
 * Date: 28-07-2023
 *
 *
 * Herra functions
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */

class Herrafunctions {
  protected $em;
  protected $router;
  protected $session;
  /**
   * [__construct description]
   * @param EntityManager $entityManager [description]
   * @param Router        $router        [description]
   */
  public function __construct(EntityManager $entityManager, Router $router){
    $this->em = $entityManager;
    $this->router = $router;
    $this->session = new Session();
  }
  /**
   * [getDistritoByPersonaId description]
   * @param  [type] $personaId [description]
   * @return [type]            [description]
   */
  public function getAllAdm($inpData){

$query = "
        select * 
        from (
        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
        ,a.observacion
        from public.empareja_sie_planilla a 
          inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
        where institucioneducativa_id='".$inpData['sie']."' and gestion_tipo_id=".$inpData['gestion']." and mes_tipo_id=".$inpData['idMounth']." and a.maestro_inscripcion_id_sie is not null and a.planilla_pago_comparativo_id_sie is not null and nuevo_maestro_inscripcion_id is null
        and a.cargo_tipo_id<>0
        union 
        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
        ,a.observacion
        from public.empareja_sie_planilla a 
          inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
        where institucioneducativa_id='".$inpData['sie']."' and gestion_tipo_id=".$inpData['gestion']." and mes_tipo_id=".$inpData['idMounth']." and nuevo_maestro_inscripcion_id is null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is not null 
        and b.cod_func<>2
        union 
        select a.id,b.ci||case when trim(coalesce(b.complemento,''))='' then '' else '-'||b.complemento end as ci
        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(coalesce(b.nombre,'')) as apellidos_nombre,
        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
        ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
        ,a.observacion
        from empareja_sie_planilla  a
          inner join nuevo_maestro_inscripcion b on a.nuevo_maestro_inscripcion_id=b.id
        where institucioneducativa_id='".$inpData['sie']."' and a.gestion_tipo_id=".$inpData['gestion']." and a.mes_tipo_id=".$inpData['idMounth']." 
        and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
        and a.cargo_tipo_id<>0
        ) a
        order by cargo_sie,apellidos_nombre;
";
$query = "
select * 
from (
select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
(select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
,a.observacion
from public.empareja_sie_planilla a 
  inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
where institucioneducativa_id='".$inpData['sie']."' and gestion_tipo_id=".$inpData['gestion']." and mes_tipo_id=".$inpData['idMounth']."  and a.maestro_inscripcion_id_sie is not null and a.planilla_pago_comparativo_id_sie is not null and nuevo_maestro_inscripcion_id is null
and a.cargo_tipo_id<>0
union 
select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
(select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
,a.observacion
from public.empareja_sie_planilla a 
  inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
where institucioneducativa_id='".$inpData['sie']."' and gestion_tipo_id=".$inpData['gestion']." and mes_tipo_id=".$inpData['idMounth']."  and nuevo_maestro_inscripcion_id is null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is not null 
and b.cod_func<>2
union 
select a.id,b.ci||case when trim(coalesce(b.complemento,''))='' then '' else '-'||b.complemento end as ci
,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(coalesce(b.nombre,'')) as apellidos_nombre,
(select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,(select solucion_tipo from solucion_comparacion_planilla_tipo where id=a.solucion_comparacion_planilla_tipo_id) as solucion_comparacion_planilla_tipo_id
,a.observacion
from empareja_sie_planilla  a
  inner join nuevo_maestro_inscripcion b on a.nuevo_maestro_inscripcion_id=b.id
where a.institucioneducativa_id='".$inpData['sie']."' and a.gestion_tipo_id=".$inpData['gestion']." and a.mes_tipo_id=".$inpData['idMounth']."  
and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
and a.cargo_tipo_id<>0
) a
order by cargo_sie,apellidos_nombre;
";
    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrData = $query->fetchAll();
   
     return $arrData;
    // return 'krlos';
  }

  public function getCargo(){

    $query =       "
        select * from Cargo_Tipo where id in (1,2,3,4,5,12) order by 1
        ";
    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrData = $query->fetchAll();
    if(sizeof($arrData)>0){
    }else{ 
      $arrOpe=array();
    }
   
     return $arrData;
    // return 'krlos';
  }

  public function getFinanciamiento(){

    $query =       "
        select * from financiamiento_tipo ft order by 1
        ";
    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrData = $query->fetchAll();
    if(sizeof($arrData)>0){
    }else{ 
      $arrData=array();
    }
   
     return $arrData;
    // return 'krlos';
  }


  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function closeOperative($inpData){
    
    $query =       "
        select * from public.sp_validacion_cierre_operativo_comparacion_sie_planilla('".$inpData['gestion']."','".$inpData['idMounth']."','".$inpData['sie']."');
        ";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrOpe = $query->fetchAll();
    if(sizeof($arrOpe)>0){
    }else{ 
      $arrOpe = array();
    }
   
     return $arrOpe;
    // return 'krlos';
  }


  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getAllowMonth($year, $months){
    
    $query = "
        select * 
        from registro_consolidacion_operativo_planilla
        where gestion='".$year."' and institucioneducativa_id=99999999 order by mes
        ";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrData = $query->fetchAll();
    if(sizeof($arrData)>0){
      
      $arrAllowMonths = array();
      foreach ($arrData as $value) {
        $ind = $value['mes']-1;
        
        if($value['mes'] == $months[$ind]['id']){
          $arrAllowMonths[] = $months[$ind];
        }
       
      }
    }else{ 
      $arrAllowMonths = array();
    }
   
     return $arrAllowMonths;
    // return 'krlos';
  }

 
 

}

 ?>
