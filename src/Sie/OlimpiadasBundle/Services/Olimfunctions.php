<?php
namespace Sie\OlimpiadasBundle\Services;

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
 * Olimfunctions
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */
class Olimfunctions {
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
  public function getTutores($arrDataInscription){
// dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
      // "   
      //     select p.id as personadid, p.nombre, p.paterno,p.materno, ot.telefono1, ot.telefono2, ot.correo_electronico, omt.materia
      //     from olim_tutor ot 
      //       left join olim_inscripcion_tutor oit on ot.id = oit.olim_tutor_id
      //         left join olim_estudiante_inscripcion oei on oit.olim_estudiante_inscripcion_id = oei.id
      //           left join estudiante_inscripcion ei on oei.estudiante_inscripcion_id = ei.id
      //             left join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id
      //               left join olim_materia_tipo omt on oei.materia_tipo_id = omt.id
      //                 left join persona p on ot.persona_id=p.id
      //     where oei.materia_tipo_id = ".$arrDataInscription['materia']." and ic.institucioneducativa_id = ".$arrDataInscription['sie']." and ic.gestion_tipo_id= ".$arrDataInscription['gestion']."
      //     group by p.id,p.nombre, p.paterno,p.materno, ot.telefono1, ot.telefono2, ot.correo_electronico, omt.materia
      // "
      "select ot.id, p.id as personadid, p.nombre, p.paterno,p.materno, ot.telefono1, ot.telefono2, ot.correo_electronico
        from olim_tutor ot
             left join persona p on ot.persona_id=p.id
        where ot.institucioneducativa_id = ".$arrDataInscription['sie']." and ot.materia_tipo_id=".$arrDataInscription['materia'].""
    );
    
    $query->execute();
    $objTutores = $query->fetchAll();
   
     return $objTutores;
    // return 'krlos';
  }

  /**
   * [lookForTutores description]
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  public function lookForTutores($data){
    //look for the tutores in persona table
    $query = $this->em->getConnection()->prepare("select id, nombre, paterno, materno, carnet from persona 
    where carnet = '".$data['carnet']."' ");
    $query->execute();
    return $query->fetchAll();
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
 

 

}

 ?>
