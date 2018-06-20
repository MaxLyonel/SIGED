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
            dist.id = ".$codDistrito." and inst.institucioneducativa_acreditacion_tipo_id = 1
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
  public function getStudentTramite($rude,$rolUser){

    //create the query about the roluser
    switch ($rolUser) {
      case 8:
          $query = $this->em->getConnection()->prepare("
                                                    select * from tramite a
                                                    inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
                                                    inner join documento d on a.id = d.tramite_id
                                                    inner join estudiante c on b.estudiante_id = c.id
                                                    where c.codigo_rude = '".$rude."'
                                                    and d.documento_tipo_id = '2'
                                                    and d.documento_estado_id = '1'
                                                    ");
        # code...
        break;
      case 10:
          $query = $this->em->getConnection()->prepare("
                                                      select * from tramite a
                                                      inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
                                                      inner join estudiante c on b.estudiante_id = c.id
                                                      where c.codigo_rude = '".$rude."'
                                                      and a.esactivo is true
          ");
        break;

      default:
        return false;
        break;
    }

    $query->execute();
    $objTramite = $query->fetchAll();
    if($objTramite){
      return true;
    } else {
      return false;
    }
  }

  public function getStudentObservationQA($data){

    $query = $this->em->getConnection()->prepare(
      "
           
          SELECT
          c.entidad,
          b.descripcion,
          a.obs,
          a.institucion_educativa_id
          FROM validacion_regla_entidad_tipo AS c
          INNER JOIN validacion_regla_tipo AS b ON b.validacion_regla_entidad_tipo_id = c.id
          INNER JOIN validacion_proceso AS a ON a.validacion_regla_tipo_id = b.id
          WHERE
          a.es_activo ='f' AND
          a.llave = '".$data['codigoRude']."' AND
          a.gestion_tipo_id = ".$data['gestion']
    );
    $query->execute();
    $objStudentObservation = $query->fetchAll();
    return $objStudentObservation;
    // dump($objStudentObservation);die;
  }


  public function getAllObservationQA($data){
      
      //    and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
      // $em = $this->getDoctrine()->getManager();
      $query = $this->em->getConnection()->prepare("
                                                select vp.*
                                                from validacion_proceso vp
                                                where vp.institucion_educativa_id = '".$data['sie']."' and vp.gestion_tipo_id = '".$data['gestion']."'
                                                and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
                                                and vp.es_activo = 'f'
                                            ");
          //
      $query->execute();
      $objobsQA = $query->fetchAll();


      return $objobsQA;
    }

    public function getSubSistemaInstitucionEducativa($institucionEducativaId){
      
      //    and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
      // $em = $this->getDoctrine()->getManager();
      $query = $this->em->getConnection()->prepare("
                                select ie.id as codigo, ie.institucioneducativa as institucioneducativa, oct.id as orgcurricular_id, oct.orgcurricula
                                , (case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as subsistema
                                from institucioneducativa as ie
                                inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                                left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
                                where ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.id = ".$institucionEducativaId." and ie.estadoinstitucion_tipo_id = 10
  
                                            ");
          //
      $query->execute();
      $objIE = $query->fetch();


      return $objIE;
    }
    public function getNotasHistorialRegular($participanteId){
      // dump($participanteId);die;
      //    and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
      // $em = $this->getDoctrine()->getManager();
      $query = $this->em->getConnection()->prepare("
              SELECT
              estudiante.codigo_rude,
              cast(estudiante.carnet_identidad as varchar)||(case when estudiante.complemento is null then '' when estudiante.complemento = '' then '' else '-'||estudiante.complemento end) as carnet_identidad,
              estudiante.paterno,
              estudiante.materno,
              estudiante.nombre,
              institucioneducativa_curso.institucioneducativa_id,
              institucioneducativa.institucioneducativa,
              institucioneducativa_curso.grado_tipo_id,
              grado_tipo.grado,
              paralelo_tipo.paralelo,
              institucioneducativa_curso.gestion_tipo_id,
              estudiante_inscripcion.estadomatricula_tipo_id,
              institucioneducativa_curso_oferta.asignatura_tipo_id,
              -- asignatura_tipo.asignatura,
              (case WHEN institucioneducativa_curso_oferta.asignatura_tipo_id = 1039 then upper(asignatura_tipo.asignatura ||' '||especialidad_tecnico_humanistico_tipo.especialidad) else asignatura_tipo.asignatura end) as asignatura,
              asignatura_tipo.area_tipo_id,
              UPPER(area_tipo.area) as area,
              turno_tipo.turno,
              Sum(case when estudiante_nota.nota_tipo_id = 1 then estudiante_nota.nota_cuantitativa end) AS b1,
              Sum(case when estudiante_nota.nota_tipo_id = 2 then estudiante_nota.nota_cuantitativa end) AS b2,
              Sum(case when estudiante_nota.nota_tipo_id = 3 then estudiante_nota.nota_cuantitativa end) AS b3,
              Sum(case when estudiante_nota.nota_tipo_id = 4 then estudiante_nota.nota_cuantitativa end) AS b4,
              Sum(case when estudiante_nota.nota_tipo_id = 5 then estudiante_nota.nota_cuantitativa end) AS b5,
              Sum(case when estudiante_nota.nota_tipo_id = 6 then estudiante_nota.nota_cuantitativa end) AS t1,
              Sum(case when estudiante_nota.nota_tipo_id = 7 then estudiante_nota.nota_cuantitativa end) AS t2,
              Sum(case when estudiante_nota.nota_tipo_id = 8 then estudiante_nota.nota_cuantitativa end) AS t3,
              Sum(case when estudiante_nota.nota_tipo_id = 9 then estudiante_nota.nota_cuantitativa end) AS t4,
              Sum(case when estudiante_nota.nota_tipo_id = 10 then estudiante_nota.nota_cuantitativa end) AS t5,
              Sum(case when estudiante_nota.nota_tipo_id = 11 then estudiante_nota.nota_cuantitativa end) AS t6
              FROM
              estudiante
              INNER JOIN  estudiante_inscripcion ON  estudiante_inscripcion.estudiante_id =  estudiante.id
              INNER JOIN  institucioneducativa_curso ON  estudiante_inscripcion.institucioneducativa_curso_id =  institucioneducativa_curso.id
              INNER JOIN  estudiante_asignatura ON  estudiante_asignatura.estudiante_inscripcion_id =  estudiante_inscripcion.id
              INNER JOIN  estudiante_nota ON  estudiante_nota.estudiante_asignatura_id =  estudiante_asignatura.id
              INNER JOIN  institucioneducativa_curso_oferta ON  institucioneducativa_curso_oferta.insitucioneducativa_curso_id =  institucioneducativa_curso.id AND  estudiante_asignatura.institucioneducativa_curso_oferta_id =  institucioneducativa_curso_oferta.id
              INNER JOIN  asignatura_tipo ON  institucioneducativa_curso_oferta.asignatura_tipo_id =  asignatura_tipo.id
              INNER JOIN  area_tipo ON  asignatura_tipo.area_tipo_id =  area_tipo.id
              INNER JOIN  grado_tipo ON  institucioneducativa_curso.grado_tipo_id =  grado_tipo.id
              INNER JOIN  paralelo_tipo ON  institucioneducativa_curso.paralelo_tipo_id =  paralelo_tipo.id
              INNER JOIN  turno_tipo ON turno_tipo.id = institucioneducativa_curso.turno_tipo_id
              INNER JOIN  institucioneducativa ON  institucioneducativa_curso.institucioneducativa_id =  institucioneducativa.id
              LEFT JOIN  estudiante_inscripcion_humnistico_tecnico ON estudiante_inscripcion_humnistico_tecnico.estudiante_inscripcion_id = estudiante_inscripcion.id
              LEFT JOIN  especialidad_tecnico_humanistico_tipo ON estudiante_inscripcion_humnistico_tecnico.especialidad_tecnico_humanistico_tipo_id = especialidad_tecnico_humanistico_tipo.id
              WHERE
              estudiante_inscripcion.id = ".$participanteId." 
              -- and estudiante_inscripcion.estadomatricula_tipo_id in (4,5,55) --and institucioneducativa_curso.nivel_tipo_id in (3,13)
              --AND case when institucioneducativa_curso.gestion_tipo_id > 2010 then institucioneducativa_curso.ciclo_tipo_id in (2,3) else true end
              -- AND case when institucioneducativa_curso.gestion_tipo_id > 2013 or (institucioneducativa_curso.gestion_tipo_id > 2013 and grado_tipo.id = 1) then grado_tipo.id in (3,4,5,6) else grado_tipo.id in (1,2,3,4) end
              -- AND institucioneducativa_curso.nivel_tipo_id = (case when (2017 <= 2010) then 3 else 13 end)
              -- AND (case when (2017 <= 2010) then (institucioneducativa_curso.grado_tipo_id in (1,2,3,4)) else (institucioneducativa_curso.grado_tipo_id in (3,4,5,6)) end)
              GROUP BY
              estudiante.codigo_rude,
              estudiante.carnet_identidad,
              estudiante.complemento,
              estudiante.paterno,
              estudiante.materno,
              estudiante.nombre,
              institucioneducativa_curso.institucioneducativa_id,
              institucioneducativa.institucioneducativa,
              institucioneducativa_curso.grado_tipo_id,
              grado_tipo.grado,
              paralelo_tipo.paralelo,
              institucioneducativa_curso.gestion_tipo_id,
              estudiante_inscripcion.estadomatricula_tipo_id,
              institucioneducativa_curso_oferta.asignatura_tipo_id,
              turno_tipo.turno,
              asignatura_tipo.area_tipo_id,
              area_tipo.area,
              asignatura_tipo.asignatura,
              especialidad_tecnico_humanistico_tipo.especialidad
              ORDER BY
              institucioneducativa_curso.grado_tipo_id desc,asignatura_tipo.area_tipo_id, institucioneducativa_curso_oferta.asignatura_tipo_id
              
                                            ");

          //
      $query->execute();
      $entityInscripcion = $query->fetchAll();

dump($entityInscripcion);die;
      $gradoId = 0;
      $i = 0;
      $j = 0;
      $listaHistorial=array();
      foreach ($entityInscripcion as $registro)
      {
        if ($gradoId != $registro['grado_tipo_id']) {
          $gradoId = $registro['grado_tipo_id'];
          $i = 0;
        }
        if(($registro['gestion_tipo_id'] > 2013) or ($registro['gestion_tipo_id'] == 2013 and $registro['grado_tipo_id'] == 1)){
          $listaHistorial[$registro['grado_tipo_id']][$i] = array(
                                                                    'codigo_rude'=>$registro['codigo_rude'],
                                                                    'paterno'=>$registro['paterno'],
                                                                    'materno'=>$registro['materno'],
                                                                    'nombre'=>$registro['nombre'],
                                                                    'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                    'institucioneducativa'=>$registro['institucioneducativa'],
                                                                    'turno'=>$registro['turno'],
                                                                    'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                        'grado'=>$registro['grado'],
                                                                    'paralelo'=>$registro['paralelo'],
                                                                    'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                    'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                    'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                    'asignatura'=>$registro['asignatura'],
                                                                    'area_tipo_id'=>$registro['area_tipo_id'],
                                                                    'area'=>$registro['area'],
                                                                    'calendarioId'=>1,
                                                                    'calendario'=>'Bimestral',
                                                                    'n1'=>$registro['b1'],
                                                                    'n2'=>$registro['b2'],
                                                                    'n3'=>$registro['b3'],
                                                                    'n4'=>$registro['b4'],
                                                                    'n5'=>$registro['b5'],
                                                                    'n6'=>null
                                                                  );
        } else {
          $listaHistorial[$registro['grado_tipo_id']][$i] = array(
                                                                    'codigo_rude'=>$registro['codigo_rude'],
                                                                    'paterno'=>$registro['paterno'],
                                                                    'materno'=>$registro['materno'],
                                                                    'nombre'=>$registro['nombre'],
                                                                    'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                    'institucioneducativa'=>$registro['institucioneducativa'],
                                                                    'turno'=>$registro['turno'],
                                                                    'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                    'grado'=>$registro['grado'],
                                                                    'paralelo'=>$registro['paralelo'],
                                                                    'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                    'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                    'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                    'asignatura'=>$registro['asignatura'],
                                                                    'area_tipo_id'=>$registro['area_tipo_id'],
                                                                    'area'=>$registro['area'],
                                                                    'calendarioId'=>2,
                                                                    'calendario'=>'Trimestral',
                                                                    'n1'=>$registro['t1'],
                                                                    'n2'=>$registro['t2'],
                                                                    'n3'=>$registro['t3'],
                                                                    'n4'=>$registro['t4'],
                                                                    'n5'=>$registro['t5'],
                                                                    'n6'=>$registro['t6']
                                                                  );
        }

        $i++;
        // $listaHistorial[$i] = $entidadEspecialidadTipo['grado_tipo_id'];
      }
      //dump($listaHistorial);
      //die;
      return $listaHistorial;
    }

    public function getYearsOldsStudentByFecha($fecha_nacimiento, $fecha_control){

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



public function getReportCalificationsByStudentInscription($studentInscription){

  $query = $this->em->getConnection()->prepare("

  select 
  case when b1 > 0 then '1' else '' end b1,
  case when b2 > 0 then '1' else '' end b2,
  case when b3 > 0 then '1' else '' end b3,
  case when b4 > 0 then '1' else '' end b4,
  case when t1 > 0 then '1' else '' end t1,
  case when t2 > 0 then '1' else '' end t2,
  case when t3 > 0 then '1' else '' end t3
  from (
  SELECT
  public.estudiante_inscripcion.id,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 1 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 1 then 1 else 0 end) b1,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 2 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 2 then 1 else 0 end) b2,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 3 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 3 then 1 else 0 end) b3,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 4 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 4 then 1 else 0 end) b4,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 6 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 6 then 1 else 0 end) t1,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 7 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 7 then 1 else 0 end) t2,
  sum( case when public.institucioneducativa_curso.nivel_tipo_id in (2,3,12,13) and nota_tipo_id = 8 then public.estudiante_nota.nota_cuantitativa
  when public.institucioneducativa_curso.nivel_tipo_id in (1,11) and public.estudiante_nota.nota_cualitativa <> '' and nota_tipo_id = 8 then 1 else 0 end) t3
  FROM
  public.estudiante_inscripcion
  INNER JOIN public.estudiante_asignatura ON public.estudiante_asignatura.estudiante_inscripcion_id = public.estudiante_inscripcion.id
  INNER JOIN public.estudiante_nota ON public.estudiante_nota.estudiante_asignatura_id = public.estudiante_asignatura.id
  INNER JOIN public.institucioneducativa_curso ON public.estudiante_inscripcion.institucioneducativa_curso_id = public.institucioneducativa_curso.id
  WHERE
  public.estudiante_inscripcion.id = ".$studentInscription."
  GROUP BY
  public.estudiante_inscripcion.id) a
  
    ");
  $query->execute();
  $objReportCalifications = $query->fetch();
  // dump($objReportCalifications);die;

  return $objReportCalifications;

}


}
 ?>
