<?php
namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;

/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for load all additional connections; if exist in a particular proccess
 * Date: 18-10-2017
 *
 *
 * Seguimiento
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */
class Seguimiento {
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
  public function getDistritoByPersonaId($personaId){

    $query = $this->em->getConnection()->prepare(
      "
            select f.id,g.codigo
            from usuario d
            left join usuario_rol e on e.usuario_id = d.id
            left join rol_tipo f on e.rol_tipo_id = f.id
            inner join lugar_tipo g on e.lugar_tipo_id = g.id
            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id
            where
            d.persona_id = ".$personaId." and
            e.esactivo is true and
            f.id not in ('9','2')
      "
    );
    $query->execute();
    $arrRolesDistritoUser = $query->fetchAll();
    $swOption = true;
    reset($arrRolesDistritoUser);
    while($swOption && $val = current($arrRolesDistritoUser)){
      if($val['id']==10){
        $swOption = false;
        $codUserDistrito = $val['codigo'];
      }
      next($arrRolesDistritoUser);
    }
    if($swOption)
     return false;
    else
     return $codUserDistrito;
  }
  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getUesByCodDistrito($codDistrito){
    //create the query to find the ues by distrito
    $query = $this->em->getConnection()->prepare("
                                SELECT DISTINCT
                                lt4.codigo AS codigo_departamento,
                                lt4.lugar AS departamento,
                                dist.id AS codigo_distrito,
                                dist.distrito AS distrito,
                                inst.id AS codigo_sie,
                                inst.institucioneducativa AS centro_educativo,
                                lt.id
                                FROM
                                institucioneducativa AS inst
                                INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
                                INNER JOIN distrito_tipo AS dist ON dist.id = jurg.distrito_tipo_id
                                LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
                                LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                                LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                                LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                                LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                                WHERE
                                inst.id IN
                                (
                                SELECT DISTINCT institucioneducativa_id
                                from institucioneducativa_operativo_log
                                where institucioneducativa_operativo_log_tipo_id = 1
                                and gestion_tipo_id = 2017
                                )
                                and dist.id = ".$codDistrito."
                                ORDER BY
                                codigo_departamento,
                                codigo_distrito,
                                codigo_sie
    ");
    $query->execute();
    $objUeCloseRude = $query->fetchAll();
    return $objUeCloseRude;
  }
  /**
   * [getUesGisByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getUesGisByCodDistrito($codDistrito){
    // dump($codDistrito);die;
    //create the query to find the ues by distrito
    $query = $this->em->getConnection()->prepare("
                SELECT DISTINCT
            lt4.codigo AS codigo_departamento,
            lt4.lugar AS departamento,
            dist.id AS codigo_distrito,
            dist.distrito AS distrito,
            inst.id AS codigo_sie,
            inst.institucioneducativa AS centro_educativo,
            lt.id,
            inst.le_juridicciongeografica_id,
            valgeo.descripcion
            FROM
            institucioneducativa AS inst
            INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
            INNER JOIN distrito_tipo AS dist ON dist.id = jurg.distrito_tipo_id
            INNER JOIN validacion_geografica_tipo AS valgeo ON jurg.validacion_geografica_tipo_id = valgeo.id
            LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
            WHERE
            dist.id = ".$codDistrito."
            ORDER BY
            codigo_departamento,
            codigo_distrito,
            codigo_sie
    ");
    $query->execute();
    $objUeCloseRude = $query->fetchAll();
    return $objUeCloseRude;
  }

  /**
   * [getStudentTramite description]
   * @param  [type] $rude [rude]
   * @return [type]       [true, false]
   */
  public function getStudentTramite($rude,$validationType){

    //create the query to find the ues by distrito
    // $query = $this->em->getConnection()->prepare("
    //                                             select * from tramite a
    //                                             inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
    //                                             inner join estudiante c on b.estudiante_id = c.id
    //                                             where c.codigo_rude = '".$rude."'
    //                                             and a.esactivo is true
    // ");
    $query = $this->em->getConnection()->prepare("
                                              select * from tramite a
                                              inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
                                              inner join documento d on a.id = d.tramite_id
                                              inner join estudiante c on b.estudiante_id = c.id
                                              where c.codigo_rude = '".$rude."'
                                              and d.documento_estado_id = '".$validationType."'
                                              ");
    $query->execute();
    $objTramite = $query->fetchAll();
    if($objTramite){
      return true;
    } else {
      return false;
    }
  }

}

 ?>
