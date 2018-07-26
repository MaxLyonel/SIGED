<?php
namespace Sie\OlimpiadasBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;

/**
 * Author: krlos pacha <pckrlos@gmail.com>
 * Description:This is a class for load all additional functionalities; if exist in a particular proccess
 * Date: 18-02-2018
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
        where ot.institucioneducativa_id = ".$arrDataInscription['sie']." and ot.materia_tipo_id=".$arrDataInscription['olimMateria']." and ot.gestion_tipo_id = ".$arrDataInscription['gestion']
    );
    
    $query->execute();
    $objTutores = $query->fetchAll();
   
     return $objTutores;
    // return 'krlos';
  }

   public function getTutores2($arrDataInscription){
// dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
      
      "select p.id as personaid,ot.id as olimtutorid, oro.id as olimregistroolimpiadaid, ot.institucioneducativa_id, oro.gestion_tipo_id, p.nombre, p.paterno, p.materno, p.carnet, ot.telefono1, ot.telefono2, ot.correo_electronico 
        from olim_tutor ot
        left join olim_registro_olimpiada oro on ot.olim_registro_olimpiada_id = oro.id
        left join persona p on ot.persona_id = p.id
        where ot.institucioneducativa_id = ".$arrDataInscription['sie']." and ot.olim_registro_olimpiada_id=".$arrDataInscription['OlimRegistroOlimpiadaId']
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

  public function getAllowedAreasByOlim(){
    // ro.id as olimid, ro.nombre_olimpiada,
     $query = $this->em->getConnection()->prepare("
        select  mt.id as materiaid, mt.materia
        from olim_registro_olimpiada ro
        left join olim_materia_tipo  mt on (ro.id = mt.olim_registro_olimpiada_id)
        where ro.gestion_tipo_id = '".$this->session->get('currentyear')."'
        order by 2 
      ");
    $query->execute();
    $objArea = $query->fetchAll();
    $arrAreas = array();
    foreach ($objArea as $value) {
      $arrAreas[$value['materiaid']] = $value['materia'];
    }
    return $arrAreas;

  }

  /**
   * [getTutor description] by krlos
   * @param  [type] $arrDataInscription [description]
   * @return [type]                     [description]
   */
   public function getTutor($arrDataInscription){
  // dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
    
      "select ot.id, p.id as personadid, p.nombre, p.paterno,p.materno, ot.telefono1, ot.telefono2, ot.correo_electronico
        from olim_tutor ot
             left join persona p on ot.persona_id=p.id
        where ot.institucioneducativa_id = ".$arrDataInscription['sie']." and ot.materia_tipo_id=".$arrDataInscription['olimMateria']." and ot.id = ".$arrDataInscription['tutorid']." and ot.gestion_tipo_id = ".$arrDataInscription['gestion']
    );
    
    $query->execute();
    $objTutores = $query->fetch();
   
     return $objTutores;
    // return 'krlos';
   }
    public function getTutor2($arrDataInscription){
  // dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
    
      "select ot.id, p.id as personadid, p.nombre, p.paterno,p.materno, ot.telefono1, ot.telefono2, ot.correo_electronico
        from olim_tutor ot
             left join persona p on ot.persona_id=p.id
        where ot.id = ".$arrDataInscription['olimtutorid']." and ot.gestion_tipo_id = ".$arrDataInscription['gestiontipoid']
    );
    
    $query->execute();
    $objTutores = $query->fetch();
   
     return $objTutores;
    // return 'krlos';
   }

   /**
    * [getReglaByMateriaCategoryGestion description]
    * @param  [type] $arrDataInscription [description]
    * @return [type]                     [description]
    */
   public function getReglaByMateriaCategoryGestion($arrDataInscription){
    $arrDataInscription['gestion'] = $arrDataInscription['gestion']-1;
    $sql = "select * from olim_reglas_olimpiadas_tipo 
            where olim_materia_tipo_id = ".$arrDataInscription['olimMateria']."  and gestion_tipo_id = ".$arrDataInscription['gestion']
            ;
    if($arrDataInscription['category']!=''){
      $sql .= " and olim_categoria_tipo_id = ".$arrDataInscription['category'];
    }
   
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetch();
    // return 'krlos';
   }
   /**
    * [getGradoAllowed description]
    * @param  [type] $arrDataInscription [description]
    * @return [type]                     [description]
    */
   public function getGradoAllowed($arrDataInscription){

    $arrDataInscription['gestion'] = $arrDataInscription['gestion']-1;
    $sql = "select * from olim_reglas_olimpiadas_nivel_grado_tipo 
            where olim_reglas_olimpiadas_tipo_id = ".$arrDataInscription['idregla']." and  nivel_tipo_id = ".$arrDataInscription['idnivel']
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetchAll();

   }
   /**
    * [getStudentsToOlimpiadas description]
    * @param  [type] $institucionEducativaCursoId [description]
    * @return [type]                              [description]
    */
   public function getStudentsToOlimpiadas($institucionEducativaCursoId){

    $sql = "
            
            select ei.id as estinsid,e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento 
            from institucioneducativa ie 
            left join institucioneducativa_curso iec on ie.id = iec.institucioneducativa_id
            left join estudiante_inscripcion ei on ei.institucioneducativa_curso_id = iec.id
            left join estudiante e on ei.estudiante_id = e.id
            where (e.pais_tipo_id = 1 or e.es_doble_nacionalidad = 't') and iec.id = ".$institucionEducativaCursoId."
            order by e.paterno, e.materno, e.nombre ASC
        "
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetchAll();

   }

   public function getYearsOldsStudent($fecha_nacimiento, $fecha_control){

            $fecha_actual = $fecha_control;

            if (!strlen($fecha_actual)) {
                $fecha_actual = date('d-m-Y');
            }
// dump($fecha_actual);
            // separamos en partes las fechas
            $array_nacimiento = explode("-", str_replace('/', '-', $fecha_nacimiento));
            $array_actual = explode("-", $fecha_actual);

            $anos = $array_actual[2] - $array_nacimiento[2]; // calculamos años
            $meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses
            $dias = $array_actual[0] - $array_nacimiento[0]; // calculamos días
            //ajuste de posible negativo en $días
            if ($dias < 0) {
                --$meses;

                //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual
                switch ($array_actual[1]) {
                    case 1:
                        $dias_mes_anterior = 31;
                        break;
                    case 2:
                        $dias_mes_anterior = 31;
                        break;
                    case 3:
                        if (bisiesto($array_actual[2])) {
                            $dias_mes_anterior = 29;
                            break;
                        } else {
                            $dias_mes_anterior = 28;
                            break;
                        }
                    case 4:
                        $dias_mes_anterior = 31;
                        break;
                    case 5:
                        $dias_mes_anterior = 30;
                        break;
                    case 6:
                        $dias_mes_anterior = 31;
                        break;
                    case 7:
                        $dias_mes_anterior = 30;
                        break;
                    case 8:
                        $dias_mes_anterior = 31;
                        break;
                    case 9:
                        $dias_mes_anterior = 31;
                        break;
                    case 10:
                        $dias_mes_anterior = 30;
                        break;
                    case 11:
                        $dias_mes_anterior = 31;
                        break;
                    case 12:
                        $dias_mes_anterior = 30;
                        break;
                }

                $dias = $dias + $dias_mes_anterior;

                if ($dias < 0) {
                    --$meses;
                    if ($dias == -1) {
                        $dias = 30;
                    }
                    if ($dias == -2) {
                        $dias = 29;
                    }
                }
            }

            //ajuste de posible negativo en $meses
            if ($meses < 0) {
                --$anos;
                $meses = $meses + 12;
            }

            $tiempo[0] = $anos;
            $tiempo[1] = $meses;
            $tiempo[2] = $dias;

            return $tiempo;
   }

    function bisiesto($anio_actual) {
        $bisiesto = false;
        //probamos si el mes de febrero del año actual tiene 29 días
        if (checkdate(2, 29, $anio_actual)) {
            $bisiesto = true;
        }
        return $bisiesto;
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
 

  public function getStudentsInOlimpiadas($materiaId, $ruleId, $gestion, $studentInscriptionId){
// olim_reglas_olimpiadas_tipo_id = ".$ruleId." and 
    $sql = "
            
            select estudiante_inscripcion_id from olim_estudiante_inscripcion 
            where materia_tipo_id = ".$materiaId." and 
            gestion_tipo_id = ".$gestion."  and
            estudiante_inscripcion_id = ".$studentInscriptionId." 
            "
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetchAll();

   }

  public function getStudentsInGroup($groupId){

    $sql = "
            
            select e.codigo_rude,e.paterno, e.materno, e.nombre, e.carnet_identidad, e.complemento,oigp.id as olim_inscripcion_grupo_proyecto_id  ,oei.id as olim_estudiante_inscripcion_id, ei.id as estudiante_inscripcion_id, e.id as estudiante_id
            from olim_inscripcion_grupo_proyecto oigp
              left join olim_estudiante_inscripcion oei on oigp.olim_estudiante_inscripcion_id = oei.id
                left join estudiante_inscripcion ei on oei.estudiante_inscripcion_id = ei.id
                  left join estudiante e on ei.estudiante_id = e.id
            where oigp.olim_grupo_proyecto_id = ".$groupId
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetchAll();

   }


    public function getDirectorInfo($data){
// dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
      
      "
        SELECT m0_.id AS maestroId, p1_.id AS personaId, o2_.id AS datosId, p1_.carnet AS carnet3, p1_.paterno AS paterno, p1_.materno AS materno, p1_.nombre AS nombre, o2_.telefono1 AS telefono1, o2_.telefono2 AS telefono2, o2_.correo_electronico AS correo_electronico
        FROM maestro_inscripcion m0_ 
        INNER JOIN persona p1_ ON (m0_.persona_id = p1_.id) 
        left JOIN olim_director_inscripcion_datos o2_ ON (o2_.maestro_inscripcion_id = m0_.id) 
        WHERE m0_.gestion_tipo_id =  ".$data['gestion']." AND m0_.institucioneducativa_id =  ".$data['sie']." AND m0_.es_vigente_administrativo = 't' and m0_.cargo_tipo_id in (1,12)
      "
    );
    
    $query->execute();
    $objTutores = $query->fetch();
   
     return $objTutores;
    // return 'krlos';
  }

  public function getDataRule($data){
// dump($data);die;
/*    $sql = "
            select * from olim_reglas_olimpiadas_tipo  
            where id = ". $data['categoryId'] ." and olim_materia_tipo_id = ".$data['materiaId']
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetchAll();*/

      $query = $this->em->createQueryBuilder()
                ->select('orot')
                ->from('SieAppWebBundle:OlimReglasOlimpiadasTipo', 'orot')
                ->where('orot.id = :categoryId')
                ->andWhere('orot.olimMateriaTipo = :materiaId')
                ->setParameter('categoryId',$data['categoryId'])
                ->setParameter('materiaId',$data['materiaId'])
                ->getQuery()
                ;
                
                
      $objRuleInscription = $query->getResult();
      return $objRuleInscription[0];
  }



    public function lookForOlimStudentByRudeGestion($data){
// dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
      
      "
          select e.codigo_rude, e.carnet_identidad,e.complemento, e.paterno, e.materno, e.nombre,e.fecha_nacimiento ,ei.id as estinsid,ei.estadomatricula_tipo_id, ei.institucioneducativa_curso_id, 
          iec.nivel_tipo_id,iec.grado_tipo_id, iec.paralelo_tipo_id, iec.turno_tipo_id,
          nt.nivel,gt.grado, pt.paralelo, tt.turno, i.institucioneducativa, i.id as sie
          from estudiante e 
          left join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
          left join institucioneducativa_curso iec on ei.institucioneducativa_curso_id = iec.id
          left join institucioneducativa i on (iec.institucioneducativa_id = i.id)
          left join nivel_tipo nt on (iec.nivel_tipo_id = nt.id)
          left join grado_tipo gt on (iec.grado_tipo_id = gt.id)
          left join paralelo_tipo pt on (iec.paralelo_tipo_id = pt.id)
          left join turno_tipo tt on (iec.turno_tipo_id = tt.id)
          where e.codigo_rude = '".$data['codigoRude']."' and iec.gestion_tipo_id = ".$data['gestiontipoid']."
        
      "
    );
    
    $query->execute();
    $objInscriptionStudent = $query->fetch();
   
     return $objInscriptionStudent;
    // return 'krlos';
  }

   public function validateStudent($data){
// dump($arrDataInscription);die;
    $query = $this->em->getConnection()->prepare(
      
      "
         select * from estudiante e
          where (pais_tipo_id = 1 or es_doble_nacionalidad = 't') and carnet_identidad is not null and e.codigo_rude = '".$data['codigoRude']."'
      "
    );
    
    $query->execute();
    $objStudent = $query->fetch();
   
     return $objStudent;
    // return 'krlos';
  }


   public function getStudentsInscription($data){
    $query = $this->em->getConnection()->prepare(
      
      "
        select count(oei.id) as takeit
            from olim_estudiante_inscripcion oei
            inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
            inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
            inner join estudiante e on e.id = ei.estudiante_id
            inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
            inner join olim_tutor ot on ot.id = oei.olim_tutor_id
            inner join persona p on p.id = ot.persona_id
            where iec.institucioneducativa_id = ".$data['sie']." and
            oei.materia_tipo_id = ".$data['materiaId']." and
            iec.nivel_tipo_id = ".$data['nivelId']." and
            iec.grado_tipo_id = ".$data['gradoId']." and
            iec.paralelo_tipo_id ='".$data['paraleloId']."' and
            iec.turno_tipo_id = ".$data['turnoId']."
            

      "
    );
    
    $query->execute();
    $objStudent = $query->fetch();
   
     return $objStudent;
    // return 'krlos';
  }


  public function getGroupsNumber($data){
    $query = $this->em->getConnection()->prepare(
      // select  count(*) as groupnumber from olim_grupo_proyecto 
      //   where materia_tipo_id = ".$data['materiaId']." and
      //   olim_reglas_olimpiadas_tipo_id = ".$data['categoryId']."  and
      //   olim_tutor_id = ".$data['olimtutorid']."
      "

        select  count(*) as groupnumber 
        from olim_grupo_proyecto  ogp
        left join olim_tutor ot on (ogp.olim_tutor_id = ot.id)
        where 
        ogp.materia_tipo_id =  ".$data['materiaId']." and
        ogp.olim_reglas_olimpiadas_tipo_id = ".$data['categoryId']."  and
        ot.institucioneducativa_id = ".$data['institucioneducativaid']."
        
      "
    );
    
    $query->execute();
    $objGropNumber = $query->fetch();
   
     return $objGropNumber;
    // return 'krlos';
  }



  public function getStudentsInscriptionGroup($data){
    $query = $this->em->getConnection()->prepare(
      
      "
        select count(e.codigo_rude) as takeitgroup
            from olim_inscripcion_grupo_proyecto oigp
              left join olim_estudiante_inscripcion oei on oigp.olim_estudiante_inscripcion_id = oei.id
                left join estudiante_inscripcion ei on oei.estudiante_inscripcion_id = ei.id
                  left join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
                    left join estudiante e on ei.estudiante_id = e.id
            where iec.institucioneducativa_id = ".$data['sie']." and
            oei.materia_tipo_id = ".$data['materiaId']." and
            oei.olim_reglas_olimpiadas_tipo_id = ".$data['categoryId']."
            
         
      "
    );
    
    $query->execute();
    $objStudent = $query->fetch();
   
     return $objStudent;
    // return 'krlos';
  }



   public function getNumberStudentsInGroup($groupId){

    $sql = "
            select count(*) as takeitgroup
            from olim_inscripcion_grupo_proyecto oigp
            where oigp.olim_grupo_proyecto_id = ".$groupId
            ;
    
    $query = $this->em->getConnection()->prepare($sql);
    
    $query->execute();
    return $query->fetch();

   }




 

}

 ?>
