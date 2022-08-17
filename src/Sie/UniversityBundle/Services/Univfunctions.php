<?php
namespace Sie\UniversityBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;


/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for load all additional functionalities; if exist in a particular proccess
 * Date: 09-05-2022
 *
 *
 * Olimfunctions
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */
class Univfunctions {
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
  public function getAllStaff($arrData){

    $query =       "
        select aut.id, per.paterno, per.materno, per.nombre, per.carnet, per.complemento,carg.cargo, form.cargo as formacion, aut.gestion_tipo_id, aut.documentos_acad, aut.univ_sede_id
        from univ_sede as sede
        inner join univ_autoridad_universidad as aut on (sede.id = aut.univ_sede_id )
        inner join persona as per on (aut.persona_id = per.id)
        inner join univ_cargo_jerarquico_tipo as carg on (aut.univ_cargo_jerarquico_tipo_id = carg.id)
        inner join univ_formacion_tipo as form on (aut.univ_formacion_tipo_id = form.id)
        where aut.gestion_tipo_id =  ".$arrData['yearSelected']."  and aut.univ_sede_id = ".$arrData['sedeId']." order by per.paterno, per.materno";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrStaff = $query->fetchAll();
   
     return $arrStaff;
    // return 'krlos';
  }

  public function getAllOperative($arrData){

    $query =       "
        select * from univ_registro_consolidacion where univ_sede_id = ".$arrData['sedeId'] ." and activo is true order by 1";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrOpe = $query->fetchAll();
    if(sizeof($arrOpe)>0){
    }else{ 
      $arrOpe=array();
    }
   
     return $arrOpe;
    // return 'krlos';
  }


  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getPersonalStaff($arrData){
    
    $query =       "
        select * from univ_autoridad_universidad where gestion_tipo_id = ".$arrData['yearchoose'] ." and univ_sede_id = ".$arrData['sedeId'] ." and persona_id = ".$arrData['personId'];

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrOpe = $query->fetchAll();
    if(sizeof($arrOpe)>0){
      $existsStaff = true;
    }else{ 
      $existsStaff = false;
    }
   
     return $existsStaff;
    // return 'krlos';    

  }

  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getPosition(){
    
    $query = "select * from univ_cargo_jerarquico_tipo order by 1 ";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrPosition = $query->fetchAll();
    
     return $arrPosition;
    // return 'krlos';    

  }

  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getTraining(){
      
    $query = " select * from univ_formacion_tipo order by 1 ";

    $query = $this->em->getConnection()->prepare($query);
    
    $query->execute();
    $arrTraining = $query->fetchAll();
  
   
     return $arrTraining;
    // return 'krlos';    

  }

 
 
 

}

 ?>
