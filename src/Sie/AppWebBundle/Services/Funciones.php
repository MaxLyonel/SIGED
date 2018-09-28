<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;

class Funciones {

	protected $em;
	protected $router;
    protected $session;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
        $this->session = new Session();
	}

	/*
     * verificamos si tiene tuicion
     */
	public function verificaTuicion($sie, $idUsuario, $rolUsuario) {

        $query = $this->em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $idUsuario);
        $query->bindValue(':sie', $sie);
        $query->bindValue(':rolId', $rolUsuario);
        $query->execute();
        $aTuicion = $query->fetchAll();

        $response = ['tuicion' => $aTuicion[0]['get_ue_tuicion']];

		return $response;

	}

	public function obtenerOperativo($sie,$gestion){
        $objRegistroConsolidado = $this->em->createQueryBuilder()
                ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
                ->where('rc.unidadEducativa = :ue')
                ->andWhere('rc.gestion = :gestion')
                ->setParameter('ue',$sie)
                ->setParameter('gestion',$gestion)
                ->getQuery()
                ->getResult();

        $operativo = 5;
        if(!$objRegistroConsolidado){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($objRegistroConsolidado);die;
            if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 2; // segundo Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 3; // tercero Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 4; // cuarto Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1 and $objRegistroConsolidado[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }

        if( in_array($this->session->get('roluser'), array(7,8,10)) or $gestion < $this->session->get('currentyear')){
            $operativo = $operativo - 1;
        }

        return $operativo;

    }
	public function obtenerOperativoDown($sie,$gestion){
        $objRegistroConsolidado = $this->em->createQueryBuilder()
                ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
                ->where('rc.unidadEducativa = :ue')
                ->andWhere('rc.gestion = :gestion')
                ->setParameter('ue',$sie)
                ->setParameter('gestion',$gestion)
                ->getQuery()
                ->getResult();

        $operativo = 5;
        if(!$objRegistroConsolidado){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($objRegistroConsolidado);die;
            if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 2; // segundo Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] == 0 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 3; // tercero Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1 and $objRegistroConsolidado[0]['bim4'] == 0){
                $operativo = 4; // cuarto Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1 and $objRegistroConsolidado[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }

        return $operativo;

    }

    public function setLogTransaccion($key,$tabla,$tipoTransaccion,$observacion,$valorNuevo,$valorAnt,$sistema,$archivo){
        //try {
            $serializer = SerializerBuilder::create()->build();
            $valorAnt = $serializer->serialize($valorAnt, 'json');
            $serializer2 = SerializerBuilder::create()->build();
            $valorNuevo = $serializer2->serialize($valorNuevo, 'json');

            $newLogTransaccion = new LogTransaccion();
            $newLogTransaccion->setKey($key);
            $newLogTransaccion->setTabla($tabla);
            $newLogTransaccion->setFecha(new \DateTime('now'));
            $newLogTransaccion->setTipoTransaccion($tipoTransaccion);
            $newLogTransaccion->setIp(json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])));
            $newLogTransaccion->setUsuario($this->em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            $newLogTransaccion->setObservacion($observacion);
            $newLogTransaccion->setValorNuevo($valorNuevo);
            $newLogTransaccion->setValorAnt($valorAnt);
            $newLogTransaccion->setSistema($sistema);
            $newLogTransaccion->setArchivo($archivo);
            $this->em->persist($newLogTransaccion);
            $this->em->flush();

            return $newLogTransaccion;

       /* } catch (Exception $e) {

        }*/
    }

    public function getTipoUE($sie,$gestion){
        $tiposUE = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findBy(array('institucioneducativaId'=>$sie,'gestionTipoId'=>$gestion));
        if($tiposUE){
            foreach ($tiposUE as $t) {
                $tipo = array('id'=>$t->getInstitucioneducativaHumanisticoTecnicoTipo()->getId(),'tipo'=>$t->getInstitucioneducativaHumanisticoTecnicoTipo()->getInstitucioneducativaHumanisticoTecnicoTipo(),'grado'=>$t->getGradoTipo()->getId());
            }
        }else{
            $tipo = array('id'=>5,'tipo'=>'Humanistica','grado'=>0);
        }

        return $tipo;
    }

    /**
     * Funcion para convertir un objeto en formato JSON
     * by Jhonny
     */
    public function json($data){
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
        $json = $serializer->serialize($data, 'json');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    public function reporteConsol($gestionid, $roluser, $roluserlugarid, $instipoid){

        $lugar = $this->em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);
        
        switch ($roluser) {
            case '7':
                $where = "lt4.codigo = '".$lugar->getCodigo()."'";
                break;

            case '8':
                $where = '1 = 1';
                break;

            case '10':
                $where = "dt.id = '".$lugar->getCodigo()."'";
                break;

            default:
                $where = '1 = 0';
                break;
        }

        $query = $this->em->getConnection()->prepare("
            SELECT
            lt4.codigo AS codigo_departamento,
            lt4.lugar AS departamento,
            dt.id codigo_distrito,
            dt.distrito,
            inst.id codigo_sie,
            inst.institucioneducativa,
            case when rc.bim1 = 0 then 'NO' else 'SI' end AS bim1,
            case when rc.bim2 = 0 then 'NO' else 'SI' end AS bim2,
            case when rc.bim3 = 0 then 'NO' else 'SI' end AS bim3,
            case when rc.bim4 = 0 then 'NO' else 'SI' end AS bim4
            FROM registro_consolidacion rc
            INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
            INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
            LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
            INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.id
            WHERE ".$where." AND rc.gestion = ".$gestionid." AND
            rc.institucioneducativa_tipo_id = ".$instipoid."
            ORDER BY
            codigo_departamento,
            codigo_distrito,
            codigo_sie;
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }


    public function estadisticaConsolNal($gestionid, $instipoid){
        $query = $this->em->getConnection()->prepare("
            SELECT
            lt4.codigo as codigo_departamento,
            lt4.lugar as departamento,
            count(*) as total,
            sum(case when rc.bim1 > 0 then 1 else 0 end) as si_bim1,
            (count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) as no_bim1,
            sum(case when rc.bim2 > 0 then 1 else 0 end) as si_bim2,
            (count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) as no_bim2,
            sum(case when rc.bim3 > 0 then 1 else 0 end) as si_bim3,
            (count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) as no_bim3,
            sum(case when rc.bim4 > 0 then 1 else 0 end) as si_bim4,
            (count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) as no_bim4,
            round((sum(case when rc.bim1 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim1,
            round(((count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim1,
            round((sum(case when rc.bim2 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim2,
            round(((count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim2,
            round((sum(case when rc.bim3 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim3,
            round(((count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim3,
            round((sum(case when rc.bim4 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim4,
            round(((count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim4
            FROM registro_consolidacion rc
            INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
            INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
            LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
            INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.id
            WHERE rc.gestion = ".$gestionid." AND
            rc.institucioneducativa_tipo_id = ".$instipoid."
            GROUP BY
            codigo_departamento,
            departamento
            ORDER BY
            codigo_departamento;
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }

    public function estadisticaConsolDptal($gestionid, $roluserlugarid, $instipoid){

        $lugar = $this->em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $query = $this->em->getConnection()->prepare("
            SELECT
            lt4.codigo as codigo_departamento,
            lt4.lugar as departamento,
            dt.id as codigo_distrito,
            dt.distrito as distrito,
            count(*) as total,
            sum(case when rc.bim1 > 0 then 1 else 0 end) as si_bim1,
            (count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) as no_bim1,
            sum(case when rc.bim2 > 0 then 1 else 0 end) as si_bim2,
            (count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) as no_bim2,
            sum(case when rc.bim3 > 0 then 1 else 0 end) as si_bim3,
            (count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) as no_bim3,
            sum(case when rc.bim4 > 0 then 1 else 0 end) as si_bim4,
            (count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) as no_bim4,
            round((sum(case when rc.bim1 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim1,
            round(((count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim1,
            round((sum(case when rc.bim2 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim2,
            round(((count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim2,
            round((sum(case when rc.bim3 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim3,
            round(((count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim3,
            round((sum(case when rc.bim4 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim4,
            round(((count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim4
            FROM registro_consolidacion rc
            INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
            INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
            LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
            INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.id
            WHERE lt4.codigo = '".$lugar->getCodigo()."' AND rc.gestion = ".$gestionid." AND
            rc.institucioneducativa_tipo_id = ".$instipoid."
            GROUP BY
            codigo_departamento,
            departamento,
            codigo_distrito,
            distrito
            ORDER BY
            codigo_departamento,
            codigo_distrito;
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }

    public function estadisticaConsolDtal($gestionid, $roluserlugarid, $instipoid){

        $lugar = $this->em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $query = $this->em->getConnection()->prepare("
            SELECT
            count(*) as total,
            sum(case when rc.bim1 > 0 then 1 else 0 end) as si_bim1,
            (count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) as no_bim1,
            sum(case when rc.bim2 > 0 then 1 else 0 end) as si_bim2,
            (count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) as no_bim2,
            sum(case when rc.bim3 > 0 then 1 else 0 end) as si_bim3,
            (count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) as no_bim3,
            sum(case when rc.bim4 > 0 then 1 else 0 end) as si_bim4,
            (count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) as no_bim4,
            round((sum(case when rc.bim1 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim1,
            round(((count(*)-sum(case when rc.bim1 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim1,
            round((sum(case when rc.bim2 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim2,
            round(((count(*)-sum(case when rc.bim2 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim2,
            round((sum(case when rc.bim3 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim3,
            round(((count(*)-sum(case when rc.bim3 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim3,
            round((sum(case when rc.bim4 > 0 then 1 else 0 end) * 100 / cast(count(*) as numeric)), 2) as porc_si_bim4,
            round(((count(*)-sum(case when rc.bim4 > 0 then 1 else 0 end)) * 100 / cast(count(*) as numeric)), 2) as porc_no_bim4
            FROM registro_consolidacion rc
            INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
            INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
            LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
            LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
            LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
            LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
            LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
            INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.id
            WHERE dt.id = '".$lugar->getCodigo()."' AND rc.gestion = ".$gestionid." AND
            rc.institucioneducativa_tipo_id = ".$instipoid.";
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }

    public function saveOperativoLog($data){

    // look for the new row
    $objDownloadFilenewOpe = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
        'gestionTipoId' => $data['gestion'],
        'institucioneducativa' => $data['id'], 
        'institucioneducativaOperativoLogTipo' => $data['operativoTipo'],
    ));
    
    //check if the row exists with sie, year & operativoLog
    if(!$objDownloadFilenewOpe){
        $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
    }
    //save the log data
      $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($this->em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find($data['operativoTipo']));
      $objDownloadFilenewOpe->setGestionTipoId($data['gestion']);
      $objDownloadFilenewOpe->setPeriodoTipo($this->em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
      $objDownloadFilenewOpe->setInstitucioneducativa($this->em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['id']));
      $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
      $objDownloadFilenewOpe->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
      $objDownloadFilenewOpe->setDescripcion('...');
      $objDownloadFilenewOpe->setEsexitoso('t');
      $objDownloadFilenewOpe->setEsonline('t');
      $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
      $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
      $dataClient = json_encode(array('userAgent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>$_SERVER['HTTP_HOST']));
      $objDownloadFilenewOpe->setClienteDescripcion($dataClient);
      $this->em->persist($objDownloadFilenewOpe);
      $this->em->flush();
        
        return $objDownloadFilenewOpe;


    }

    public function statisticsRudeFileNac($data){

            $query = $this->em->getConnection()->prepare("
                SELECT
                lt4.codigo as codigo_departamento,
                lt4.lugar as departamento,
                count(*) as total

                FROM institucioneducativa_operativo_log ieol
                INNER JOIN institucioneducativa inst ON ieol.institucioneducativa_id = inst.id
                INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
                LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
                LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
                LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
                
                WHERE 
                ieol.gestion_tipo_id = ".$data['gestion']." AND
                ieol.institucioneducativa_operativo_log_tipo_id = 5
                GROUP BY
                codigo_departamento,
                departamento

                ORDER BY
                codigo_departamento
            ");

        $query->execute();
        $objStatistics = $query->fetchAll();
        return $objStatistics;

    }

    public function getOfertaBySieGestionSem($infoUe){
        //convert the send data to array
        $aInfoUeducativa = unserialize($infoUe);
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //$iecId = '';
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];

        $nivelCurso = $aInfoUeducativa['ueducativaInfo']['ciclo'];
        $gradoParaleloCurso = $aInfoUeducativa['ueducativaInfo']['grado'] . " - " . $aInfoUeducativa['ueducativaInfo']['paralelo'];
        $cursoOferta = array();

         $cursoOferta = $this->em->createQueryBuilder()
                                    ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ieco.insitucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->where('iec.id = :idCurso')
                                    ->setParameter('idCurso',$iecId)
                                    ->orderBy('at.id','ASC')
                                    ->getQuery()
                                    ->getResult();
                                    // dump($cursoOferta);die;
        
        return array('cursoOferta' => $cursoOferta, 'infoUe' => $infoUe, 'operativo' => '', 'nivel' => $nivel, 'grado' => $grado, 'nivelCurso' => $nivelCurso, 'gradoParaleloCurso' => $gradoParaleloCurso);
    }

    public function loadCurricula($infoUe){
        //convert the send data to array
        $aInfoUeducativa = unserialize($infoUe);
        $instEduCursoId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //look for the curricula to the UE
        $objInstEduCursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$instEduCursoId));
        
        try {
            $this->em->getConnection()->beginTransaction();
            //define the id-s curricula
            $arrCurricula = array(2008,2009,2010,2011,2012);
// dump($instEduCursoId);die;
            if(!$objInstEduCursoOferta){
                //set ciclo_id with ID 12 like primaria 
                $objInstEduCurso = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($instEduCursoId);

                $objInstEduCurso->setCicloTipo($this->em->getRepository('SieAppWebBundle:CicloTipo')->find(12));
                $this->em->persist($objInstEduCurso);
                $this->em->flush();

                //set thecurricula with the parameter in arrCurricula var
                foreach ($arrCurricula as $value) {
                    $newObjInstEduCursoOferta = new InstitucioneducativaCursoOferta();
                    $newObjInstEduCursoOferta->setHorasmes(0);
                    $newObjInstEduCursoOferta->setAsignaturaTipo($this->em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($value));
                    $newObjInstEduCursoOferta->setInsitucioneducativaCurso($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($instEduCursoId));

                    $this->em->persist($newObjInstEduCursoOferta);
                                         
                }
                $this->em->flush();

            }
            $this->em->getConnection()->commit();
            $objInstEduCursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($instEduCursoId);
            return $objInstEduCursoOferta;
                
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error')); 
        }
        
    }


    public function setCurriculaStudent($data){
        
        // check if Exists CURSO
        // $objInstCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($data['iecId']);
        // if($objInstCurso){
            // get info about curricula to student
            $curriculaStudent = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                'estudianteInscripcion'=>$data['eInsId']
            ));
            // check if the student has curricula
            if(!$curriculaStudent){
                  //look for the curricula-s ID in curso_oferta table
                  $queryInstCursoOferta = $this->em->createQueryBuilder()
                                          ->select('ieco')
                                          ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                                          ->where('ieco.insitucioneducativaCurso = :iecoId')
                                          ->andWhere('ieco.asignaturaTipo != 0')
                                          ->setParameter('iecoId', $data['iecId']);
                                          
                  $objAreas = $queryInstCursoOferta->getQuery()->getResult();

                  foreach ($objAreas as $area) {
                    
                    $studentAsignatura = new EstudianteAsignatura();
                    $studentAsignatura->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear') ));
                    $studentAsignatura->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($data['eInsId']));
                    $studentAsignatura->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($area->getId()));
                    $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                    $this->em->persist($studentAsignatura);
                    $this->em->flush();


                  }

            }
            
        // }
             $curriculaStudent = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                'estudianteInscripcion'=>$data['eInsId']
            ));

        return $curriculaStudent;
    }

    public function getCurriculaStudent($data){

        $query = $this->em->createQueryBuilder()
                ->select('at')
                ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                ->where('ea.estudianteInscripcion = :eInsId')
                ->setParameter('eInsId', $data['eInsId'])
                ;
        $objAreasStudent = $query->getQuery()->getResult();
        return $objAreasStudent;
    }



}
