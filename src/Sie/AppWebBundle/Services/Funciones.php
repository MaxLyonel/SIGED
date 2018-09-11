<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;

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

    public function reporteConsolEspecial($gestionid, $roluser, $roluserlugarid){

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
            rc.institucioneducativa_tipo_id = 4
            ORDER BY
            codigo_departamento,
            codigo_distrito,
            codigo_sie;
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }


    public function estadisticaConsolEspecialNal($gestionid){
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
            rc.institucioneducativa_tipo_id = 4
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

    public function estadisticaConsolEspecialDptal($gestionid, $roluserlugarid){

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
            rc.institucioneducativa_tipo_id = 4
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

    public function estadisticaConsolEspecialDtal($gestionid, $roluserlugarid){

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
            rc.institucioneducativa_tipo_id = 4;
        ");
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }
}
