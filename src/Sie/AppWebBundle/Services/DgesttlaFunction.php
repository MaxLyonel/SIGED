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
 * DgesttlaFunction
 *
 * Email bugs/suggestions to pckrlos@gmail.com
 */
class DgesttlaFunction {
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
  public function getInscriptionHistory($personaId){

    $query = $this->em->getConnection()->prepare(
      "
          select a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.nombre as nombre_carrera,d.denominacion,e.pensum,
          e.resolucion_administrativa,e.nro_resolucion
          ,g.periodo,f.codigo as codigo_materia,f.materia as asignatura,i.paralelo,j.turno,k.estadomatricula_tipo_inicio_id,k.estadomatricula_tipo_fin_id
          ,l.paterno,l.materno,l.nombre, l.carnet, g.id as periodoid, h.gestion_tipo_id as gestiontipo, m.estadomatricula
          from ttec_institucioneducativa_carrera_autorizada a
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
          		inner join institucioneducativa c on a.institucioneducativa_id=c.id
          			inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
          				inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
          					inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
          						inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
          							inner join ttec_paralelo_materia h on h.ttec_materia_tipo_id=f.id
          								inner join ttec_paralelo_tipo i on h.ttec_paralelo_tipo_id=i.id
          									inner join turno_tipo j on h.turno_tipo_id=j.id
          										inner join ttec_estudiante_inscripcion k on k.ttec_paralelo_materia_id=h.id
          											inner join estadomatricula_tipo m on k.estadomatricula_tipo_fin_id = m.id
          												inner join persona l on k.persona_id=l.id
          where l.id = ".$personaId."
          order by j.turno, g.periodo
      "
    );
    $query->execute();
    $objInfo = $query->fetchAll();

    return $objInfo;
  }
  /**
   * [getUesByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getLastInscription($personaId){
    //create the query to find the ues by distrito
    $query = $this->em->getConnection()->prepare("
        select a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.nombre as nombre_carrera,d.denominacion,e.pensum,
        e.resolucion_administrativa,e.nro_resolucion, d.id as denominacionId
        ,g.periodo,f.codigo as codigo_materia,f.materia as asignatura,i.paralelo,j.turno,k.estadomatricula_tipo_inicio_id,k.estadomatricula_tipo_fin_id
        ,l.paterno,l.materno,l.nombre, l.carnet, g.id as periodoid, h.gestion_tipo_id as gestiontipo, m.estadomatricula
        from ttec_institucioneducativa_carrera_autorizada a
        	inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
        		inner join institucioneducativa c on a.institucioneducativa_id=c.id
        			inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
        				inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
        					inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
        						inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
        							inner join ttec_paralelo_materia h on h.ttec_materia_tipo_id=f.id
        								inner join ttec_paralelo_tipo i on h.ttec_paralelo_tipo_id=i.id
        									inner join turno_tipo j on h.turno_tipo_id=j.id
        										inner join ttec_estudiante_inscripcion k on k.ttec_paralelo_materia_id=h.id
        											inner join estadomatricula_tipo m on k.estadomatricula_tipo_fin_id = m.id
        												inner join persona l on k.persona_id=l.id
          where l.id = ".$personaId." and h.gestion_tipo_id = 2017
          order by
          j.turno,
          g.periodo

    ");

    $query->execute();
    $objLastInscription = $query->fetchAll();
    return $objLastInscription;
  }
  /**
   * [getUesGisByCodDistrito description]
   * @param  [type] $codDistrito [description]
   * @return [type]              [description]
   */
  public function getCarrerasBySie($codSie){
    // dump($codDistrito);die;
    //create the query to find the ues by distrito
    $query = $this->em->getConnection()->prepare("
                      select ie.id as ieId, ieca.id as iecaId, ct.id as ctId, ct.nombre  from
                      institucioneducativa ie
                      inner join ttec_institucioneducativa_carrera_autorizada ieca  on ie.id=ieca.institucioneducativa_id
                      inner join ttec_carrera_tipo ct on ieca.ttec_carrera_tipo_id = ct.id
                      where ie.id = ".$codSie." and ct.ttec_estado_carrera_tipo_id = 1
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
  public function getDenominations($id){

    //create the query about the roluser
    $query = $this->em->getConnection()->prepare("
                                                select id, denominacion from ttec_denominacion_titulo_profesional_tipo  where ttec_carrera_tipo_id = ".$id."
                                              ");

    $query->execute();
    $objDenominations = $query->fetchAll();
    return $objDenominations;
  }

  /**
   * [getStudentTramite description]
   * @param  [type] $rude [rude]
   * @return [type]       [true, false]
   */
  public function getNextMaterias($form, $typeInsc){

    // if($typeInsc=='new'){
    //     $form['periodoid'] = 10;
    // }

    //create the query about the roluser
    $query = $this->em->getConnection()->prepare("
    select b.gestion_tipo_id,e.periodo,f.codigo,f.materia,d.turno,c.paralelo,b.id as ttec_paralelo_materia_id,a.id_obs,a.obs,a.es_registro_paralelo_materia
    from sp_validacion_superior_asignatura_corresponde_web2('".$form['personaId']."','".$form['denominacionId']."') a
        left join ttec_paralelo_materia b on a.ttec_materia_tipo_id=b.ttec_materia_tipo_id and a.ttec_paralelo_tipo_id=b.ttec_paralelo_tipo_id and a.turno_tipo_id=b.turno_tipo_id and a.ttec_periodo_tipo_id=b.ttec_periodo_tipo_id 
            left join ttec_paralelo_tipo c on a.ttec_paralelo_tipo_id=c.id
                left join turno_tipo d on a.turno_tipo_id=d.id
                    left join ttec_periodo_tipo e on a.ttec_periodo_tipo_id=e.id
                        left join ttec_materia_tipo f on b.ttec_materia_tipo_id=f.id
    where a.es_registro_paralelo_materia is true
    order by e.periodo
    ");

    $query->execute();
    $objNextMaterias = $query->fetchAll();
    
    return $objNextMaterias;
  }

  public function getTreeAsignaturas($form){

     //create the query about the roluser
    $query = $this->em->getConnection()->prepare("
                                              
                                           select a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.id as carreraid,b.nombre as nombre_carrera,d.denominacion,e.pensum,e.resolucion_administrativa,e.nro_resolucion
,g.id as periodoid,g.periodo,f.codigo as codigo_materia,f.id as materiaid,f.materia as asignatura,i.id as paraleloid,i.paralelo,j.id as turnoid,j.turno 
                                              from ttec_institucioneducativa_carrera_autorizada a
                                                inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                                                  inner join institucioneducativa c on a.institucioneducativa_id=c.id
                                                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                                                      inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                                                        inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                                                          inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
                                                            inner join ttec_paralelo_materia h on h.ttec_materia_tipo_id=f.id
                                                              inner join ttec_paralelo_tipo i on h.ttec_paralelo_tipo_id=i.id
                                                                inner join turno_tipo j on h.turno_tipo_id=j.id
                                            where a.institucioneducativa_id=".$form['sie']." and a.ttec_carrera_tipo_id = ".$form['tipoCarrera']."

                                              ");

    $query->execute();
    $objCarreras = $query->fetchAll();
    return $objCarreras;

  }

  public function getStudents($form){

    $query = $this->em->getConnection()->prepare(
      "              
        select a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.id as carreraid,b.nombre as nombre_carrera,d.denominacion,e.pensum,e.resolucion_administrativa,e.nro_resolucion
        ,g.id as periodoid,g.periodo,f.codigo as codigo_materia,f.id as materiaid,f.materia as asignatura,i.id as paraleloid,i.paralelo,j.id as turnoid,j.turno ,k.estadomatricula_tipo_inicio_id,k.estadomatricula_tipo_fin_id
        ,l.paterno,l.materno,l.nombre,l.carnet
        from ttec_institucioneducativa_carrera_autorizada a
          inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
            inner join institucioneducativa c on a.institucioneducativa_id=c.id
              inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                  inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                    inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
                      inner join ttec_paralelo_materia h on h.ttec_materia_tipo_id=f.id
                        inner join ttec_paralelo_tipo i on h.ttec_paralelo_tipo_id=i.id
                          inner join turno_tipo j on h.turno_tipo_id=j.id
                            inner join ttec_estudiante_inscripcion k on k.ttec_paralelo_materia_id=h.id
                              inner join persona l on k.persona_id=l.id
        where a.institucioneducativa_id=".$form['sie']." and a.ttec_carrera_tipo_id = ".$form['ttecCarreraTipoId']." and g.id = ".$form['periodoId']."  and f.id = ".$form['materiaId']." and i.id = ".$form['paraleloId']." and j.id = ".$form['turnoId']."
      "
    );

    $query->execute();
    $objStudentes = $query->fetchAll();
    return $objStudentes;

  }

}

 ?>
