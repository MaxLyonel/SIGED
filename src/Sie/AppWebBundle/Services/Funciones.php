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
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Sie\AppWebBundle\Entity\SuperiorModuloTipo;

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


        if( in_array($this->session->get('roluser'), array(7,8,10)) ){
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
            case when rc.bim1 > 0 then 'SI' else 'NO' end AS bim1,
            case when rc.bim2 > 0 then 'SI' else 'NO' end AS bim2,
            case when rc.bim3 > 0 then 'SI' else 'NO' end AS bim3,
            case when rc.bim4 > 0 then 'SI' else 'NO' end AS bim4
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
           /* $curriculaStudent = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                'estudianteInscripcion'=>$data['eInsId']
            ));

            // check if the student has curricula
            if(!$curriculaStudent){

                  //look for the curricula-s ID in curso_oferta table
                  $queryInstCursoOferta = $this->em->createQueryBuilder()
                                          ->select('ieco')
                                          ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                                          ->where('ieco.insitucioneducativaCurso = :iecoId')
                                          ->andWhere('ieco.asignaturaTipo = 0')
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

            }*/
            
        // }

                $queryInstCursoOferta = $this->em->createQueryBuilder()
                                          ->select('ieco')
                                          ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                                          ->where('ieco.insitucioneducativaCurso = :iecoId')
                                          // ->andWhere('ieco.asignaturaTipo = 0')
                                          ->setParameter('iecoId', $data['iecId']);
                                          
                $objAreas = $queryInstCursoOferta->getQuery()->getResult();

                $curriculaStudent = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
                        'estudianteInscripcion'=>$data['eInsId']
                    ));
                // dump($curriculaStudent);
                foreach ($objAreas  as $area) {
                     // dump($area->getId());
                    $oldcurriculaStudent = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                        'institucioneducativaCursoOferta'=>$area->getId(),
                        'estudianteInscripcion' => $data['eInsId'],
                        'gestionTipo' => $data['gestion']
                    )); 
                    if(!$oldcurriculaStudent){
                        $studentAsignatura = new EstudianteAsignatura();
                        $studentAsignatura->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($data['gestion'] ));
                        $studentAsignatura->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($data['eInsId']));
                        $studentAsignatura->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($area->getId()));
                        $studentAsignatura->setEstudianteasignaturaEstado($this->em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find(4));
                        $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                        $this->em->persist($studentAsignatura);
                        $this->em->flush();
                        // dump($studentAsignatura);
                    }
                    
                    
                }               

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

    public function lookforModuleToCourse($iecId){

        $query = $this->em->createQueryBuilder()
                ->select('l.id as smpid, k.modulo, g.id as iecoid, k.codigo as codigo')
                ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.id = g.insitucioneducativaCurso')               
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'l', 'WITH', 'l.id = g.superiorModuloPeriodo')              
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'k', 'WITH', 'k.id = l.superiorModuloTipo')              
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $iecId)
                ->getQuery();
        $objModulesInCourse = $query->getResult();
        return $objModulesInCourse;
    }

    public function setModuleToCourse($moduloPeriodo,$iecId){

         $moduloPeriodo = $this->em->createQueryBuilder()
                    ->select('l.codigo as codigo, k.id as smpid')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                    ->where('h.id = :idCurso')
                    ->setParameter('idCurso', $iecId)
                    ->getQuery()
                    ->getResult();
        
        
        foreach ($moduloPeriodo as $value) {
           // dump($value['codigo']);
           $foundElement = true;
           $objModulesInCourse = $this->lookforModuleToCourse($iecId);
           reset($objModulesInCourse);
           while( $foundElement && $val = current($objModulesInCourse) ){
                if($value['codigo']!=408){
                    if($value['codigo']==$val['codigo']){
                        $foundElement=false;
                    }else{
                     $smpid = $value['smpid'];
                    }
                }else{
                    $foundElement=false;
                }
                
            next($objModulesInCourse);
           }
           if($foundElement){
             //get the curricula to the course 
                $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                $ieco = new InstitucioneducativaCursoOferta();
                $ieco->setAsignaturaTipo($this->em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
                $ieco->setInsitucioneducativaCurso($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                $ieco->setSuperiorModuloPeriodo($this->em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($smpid));
                $ieco->setHorasmes(0);
                $this->em->persist($ieco);
                $this->em->flush();
           }
        }
          return json_encode(array('id'=>400, 'status'=>'done'));

    }

    public function loadCurriculaCurso($infoUe){
        $aInfoUeducativa = unserialize($infoUe);
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //$iecId = '';
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        //dump($iecId);dump($nivel);dump($grado);die;
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        // $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();

        if($nivel == 15 || $nivel == 5){
            try {                
                $iePeriodo = $this->em->createQueryBuilder()
                    ->select('g')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->where('h.id = :idCurso')
                    ->setParameter('idCurso', $iecId)
                    ->getQuery()
                    ->getResult();
                
                $moduloPeriodo = $this->em->createQueryBuilder()
                    ->select('l')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                    ->where('h.id = :idCurso')
                    ->setParameter('idCurso', $iecId)
                    ->getQuery()
                    ->getResult();

                $sw = false;
                foreach ($moduloPeriodo as $key => $value) {
                    if ($value->getCodigo() == '415') {
                        $sw = true;
                    }
                }
                if(!$sw){
                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_tipo');")->execute();
                    $newSuperiorModuloTipo = new SuperiorModuloTipo();
                    $newSuperiorModuloTipo->setModulo('MÓDULO EMERGENTE');
                    $newSuperiorModuloTipo->setObs('');
                    $newSuperiorModuloTipo->setCodigo('415');
                    $newSuperiorModuloTipo->setSigla('MIE');
                    $newSuperiorModuloTipo->setOficial(1);
                    $newSuperiorModuloTipo->setSuperiorAreaSaberesTipo($this->em->getRepository('SieAppWebBundle:SuperiorAreaSaberesTipo')->find(1));
                    $this->em->persist($newSuperiorModuloTipo);
                    $this->em->flush();

                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($newSuperiorModuloTipo);
                    $smp->setInstitucioneducativaPeriodo($iePeriodo[0]);
                    $smp->setHorasModulo(0);
                    $this->em->persist($smp);
                    $this->em->flush();

                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                    $ieco = new InstitucioneducativaCursoOferta();
                    $ieco->setAsignaturaTipo($this->em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
                    $ieco->setInsitucioneducativaCurso($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                    $ieco->setSuperiorModuloPeriodo($smp);
                    $ieco->setHorasmes(0);
                    $this->em->persist($ieco);
                    $this->em->flush();
                }
                $moduleToCurse = $this->setModuleToCourse($moduloPeriodo,$iecId);
                if($moduloPeriodo) {                                     
                    $modulos = $this->em->createQueryBuilder()
                    ->select('l')
                    ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                    ->where('l.codigo IN (:codigos)')
                    ->andWhere('l.id NOT IN (:modulos)')
                    ->setParameter('codigos', array(401,402,403,404))
                    ->setparameter('modulos', $moduloPeriodo)
                    ->getQuery()
                    ->getResult();
                }else {
                    $modulos = $this->em->createQueryBuilder()
                    ->select('l')
                    ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                    ->where('l.codigo IN (:codigos)')
                    ->setParameter('codigos', array(401,402,403,404))
                    ->getQuery()
                    ->getResult();
                }                
                
                foreach ($modulos as $modulo) {
                    //die("abc");        
                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($modulo);
                    $smp->setInstitucioneducativaPeriodo($iePeriodo[0]);
                    $smp->setHorasModulo(0);
                    $this->em->persist($smp);
                    $this->em->flush();
                    // dump($smp->getId());

                    //get the curricula to the course 
                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                    $ieco = new InstitucioneducativaCursoOferta();
                    $ieco->setAsignaturaTipo($this->em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
                    $ieco->setInsitucioneducativaCurso($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                    $ieco->setSuperiorModuloPeriodo($smp);
                    $ieco->setHorasmes(0);
                    $this->em->persist($ieco);
                    $this->em->flush();
                }

                

                return json_encode(array('id'=>400, 'status'=>'done'));
                // $em->getConnection()->commit();
            } catch (Exception $ex) {
                // $em->getConnection()->rollback();
            }
        }
    }

    public function validatePrimaria($sie,$gestion,$infoUe){
       
        // get the send data
        $arrInfoUe = unserialize($infoUe);
        // check if the Centro is on the avalable list
        $centroAllowed = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'institucioneducativaId'=>$sie,
          'gestionTipoId'=>$gestion,
          'institucioneducativaHumanisticoTecnicoTipo'=>10,
  
        ));
        //var to set true or false if the ue is primaria
        $swValidationPrimaria = false;
        // check if the course is PRIMARIA
        if( $centroAllowed &&
            $gestion == 2018 &&
            $arrInfoUe['ueducativaInfoId']['sfatCodigo'] == 15 &&
            $arrInfoUe['ueducativaInfoId']['setId'] == 13 &&
            $arrInfoUe['ueducativaInfoId']['periodoId'] >= 2
          ){
            $swValidationPrimaria=true;
        }else{
            if( $centroAllowed &&
                $gestion >= 2019 &&
                $arrInfoUe['ueducativaInfoId']['sfatCodigo'] == 15 &&
                $arrInfoUe['ueducativaInfoId']['setId'] == 13 &&
                $arrInfoUe['ueducativaInfoId']['periodoId'] >= 2
            ){
                $swValidationPrimaria=true;
            }else{
                $swValidationPrimaria=false;
            }        
        }

        return $swValidationPrimaria;

    }

    public function validateModIntEmer($iecId){

        $moduloIntEmer = $this->em->createQueryBuilder()
                    ->select('l')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                    ->innerjoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','k.id = ieco.superiorModuloPeriodo')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                    ->where('h.id = :idCurso')
                    ->andwhere('l.codigo = :codigo')
                    ->setParameter('idCurso', $iecId)
                    ->setParameter('codigo', 415)
                    ->getQuery();
         
         $moduloIntEmer = $moduloIntEmer->getResult();
         
         $swSetNameModIntEmer = false;
         $arrModIntEme = array('status'=>false,'codMIE'=>'');
         if($moduloIntEmer){
            if($moduloIntEmer[0]->getModulo()=='MÓDULO EMERGENTE'){
                $arrModIntEme = array('status'=>true,'codMIE'=>$moduloIntEmer[0]->getId());
            }else{
                $arrModIntEme = array('status'=>false,'codMIE'=>'');
            }
         }
         
        return json_encode($arrModIntEme);
    }

      public function validatePrimariaCourse($iecId){
        //look data about the course in a query
        $query = $this->em->createQueryBuilder()
                    ->select('IDENTITY(iec.gestionTipo) as gestion,sfat.codigo as sfatCodigo, (seet.id) as setId, (ies.periodoTipoId) as periodoId, (ies.institucioneducativa) as sie')
                    ->from('SieAppWebBundle:InstitucioneducativaCurso', 'iec')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'siep', 'WITH', 'iec.superiorInstitucioneducativaPeriodo = siep.id')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'siea', 'WITH', 'siep.superiorInstitucioneducativaAcreditacion = siea.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'siea.institucioneducativaSucursal = ies.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'siea.acreditacionEspecialidad = sae.id')
                    ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'seet', 'WITH', 'sae.superiorEspecialidadTipo = seet.id')
                    ->innerJoin('SieAppWebBundle:SuperiorFacultadAreaTipo', 'sfat', 'WITH', 'seet.superiorFacultadAreaTipo = sfat.id')
                    ->where('iec.id = :iecId')
                    ->setParameter('iecId', $iecId)
                    ->getQuery();

        $objInfoCourse = $query->getResult();
        
        // get centroAllowed in the next query
         $centroAllowed = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
          'institucioneducativaId'=>$objInfoCourse[0]['sie'],
          'gestionTipoId'=>$objInfoCourse[0]['gestion'],
          'institucioneducativaHumanisticoTecnicoTipo'=>10,
        ));

        //set values to validate PRIMARIA course
        $swValidationPrimaria = false;
        
        // check if the course is PRIMARIA
        if( $centroAllowed &&
            $objInfoCourse[0]['gestion'] == 2018 &&
            $objInfoCourse[0]['sfatCodigo'] == 15 &&
            $objInfoCourse[0]['setId'] == 13 &&
            $objInfoCourse[0]['periodoId'] >= 2
          ){
            $swValidationPrimaria=true;
        }else{
            if( $centroAllowed &&
                $objInfoCourse[0]['gestion'] >= 2019 &&
                $objInfoCourse[0]['sfatCodigo'] == 15 &&
                $objInfoCourse[0]['setId'] == 13 &&
                $objInfoCourse[0]['periodoId'] >= 2
            ){
                $swValidationPrimaria=true;
            }else{
                $swValidationPrimaria=false;
            }        
        }
        // return the response
        return $swValidationPrimaria;

    }

    public function verificarMateriasPrimariaAlternativa($idCurso){
        $materias = $this->em->createQueryBuilder()
                    ->select('smt.id as asignaturaId, smt.modulo as asignatura, smt.codigo')
                    ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo','smp','WITH','ieco.superiorModuloPeriodo = smp.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo','smt','WITH','smp.superiorModuloTipo = smt.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAreaSaberesTipo','sast','with','smt.superiorAreaSaberesTipo = sast.id')
                    // ->groupBy('smt.id, smt.modulo, smt.codigo, ea.id, eae.id, ieco.id, sast.id')
                    ->where('iec.id = :idCurso')
                    ->setParameter('idCurso',$idCurso)
                    ->getQuery()
                    ->getResult();

        $nuevaModalidad = false;
        if(count($materias) == 5){
            $tieneEmergente = false;
            foreach ($materias as $m) {
                // dump($m['codigo']);
                if($m['codigo'] == 415){
                    $tieneEmergente = true;
                }
            }
            if($tieneEmergente){
                $nuevaModalidad = true;
            }
        }

        return $nuevaModalidad;
        }

        public function appValidationQuality($data){
            //    and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
        
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


     /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    public function getCurrentInscriptionStudentByRude($data) {

        $entity = $this->em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento', 'ei.id as estInsId')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('it = :idTipo')
                ->andWhere('iec.nivelTipo = :level')
                ->andWhere('iec.gradoTipo in (:levels)')
                ->setParameter('id', $data['codigoRude'])
                ->setParameter('idTipo',1)
                ->setParameter('levels',array(3,4,5,6))
                ->setParameter('level', 13)
                ->orderBy('iec.gestionTipo', 'ASC')
                ->addorderBy('ei.fechaInscripcion', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }


       /**
     * get the select the studdent inscription -
     * @param type $estInsId
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    public function getSelectStudentInscription($estInsId) {

        $entity = $this->em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento', 'ei.id as estInsId')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('ei.id = :id')
                ->setParameter('id', $estInsId)
                ->orderBy('iec.gestionTipo', 'ASC')
                ->addorderBy('ei.fechaInscripcion', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function getSpeciality(){
           $query = $this->em->createQueryBuilder()
                    ->select('esp')
                    ->from('SieAppWebBundle:EspecialTecnicaEspecialidadTipo', 'esp')
                    ->orderBy('esp.id', 'ASC')
                    ->getQuery();
         
            $arrSpeciality = $query->getResult();
            return $arrSpeciality;
    }

    public function verificarGradoCerrado($sie,$gestion){
        $repositorio = $this->em->getRepository('SieAppWebBundle:RegistroConsolidacion');
        $regConsol = $repositorio->createQueryBuilder('rc')
                ->where('rc.unidadEducativa = :ue')
                ->andWhere('rc.gestion = :gestion')
                ->setParameter('ue',$sie)
                ->setParameter('gestion',$gestion)
                ->getQuery()
                ->getResult();

        $response = false;
        // Verificamos si se realizo el cierre de sexto grado
        if($regConsol[0]->getCierreSextosec() != null){
            $response = true;
        }
        
        return $response;

    }


     /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    public function getInscriptionBthByRude($codigoRude) {

        $entity = $this->em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento', 'ei.id as estInsId')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('it = :idTipo')
                ->andWhere('iec.nivelTipo = :level')
                ->andWhere('iec.gradoTipo in (:levels)')
                ->setParameter('id', $codigoRude)
                ->setParameter('idTipo',1)
                ->setParameter('levels',array(6))
                ->setParameter('level', 13)
                ->orderBy('iec.gestionTipo', 'ASC')
                ->addorderBy('ei.fechaInscripcion', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }



    function tiempo_transcurrido($fecha_nacimiento, $fecha_control) {
        $fecha_actual = $fecha_control;

        if (!strlen($fecha_actual)) {
            $fecha_actual = date('d-m-Y');
        }

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

  /**
     * save the log information about sie file donwload
     * @param  [type] $form [description]
     * @return [type]       [description]
     */

    public function saveDataInstitucioneducativaOperativoLog($data){
        //conexion to DB
        
        try {
        
        $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();

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
        } catch (Exception $e) {  
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }    


}
