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

    /**
     * SERVICIO DE VALIDACION DE RUTA POR ROL DE USUARIO
     * PARA QUE EL USUARIO NO PUEDA INGRESAR POR LA URL A UNA RUTA NO AUTORIZADA
     * @param  [type] $ruta [description]
     * @return [type]       [description]
     * By JHONNY
     */
    public function validarRuta($ruta){

        $sistemaId = $this->session->get('sistemaid');
        $rolId = $this->session->get('roluser');
        $datosRuta = $this->em->createQueryBuilder()
                ->select('msr')
                ->from('SieAppWebBundle:SistemaRol','sr')
                ->innerJoin('SieAppWebBundle:MenuSistemaRol','msr','with','msr.sistemaRol = sr.id')
                ->innerJoin('SieAppWebBundle:MenuSistema','ms','with','msr.menuSistema = ms.id')
                ->innerJoin('SieAppWebBundle:MenuTipo','mt','with','ms.menuTipo = mt.id')
                ->where('sr.rolTipo = :rol')
                ->andWhere('sr.sistemaTipo = :sistema')
                ->andWhere('mt.ruta = :ruta')
                ->setParameter('rol',$rolId)
                ->setParameter('sistema',$sistemaId)
                ->setParameter('ruta',$ruta)
                ->getQuery()
                ->getResult();

        $permitido = false;

        if (count($datosRuta) > 0) {
            if ($datosRuta[0]->getEsactivo()) {
                $permitido = true;
            }
        }

        return $permitido;
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
        if($gestion < 2020 ){
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

        }else{
            $operativo = 4;
            if(!$objRegistroConsolidado){
                // Si no existe es operativo inicio de gestion
                $operativo = 0;
            }else{
                //dump($objRegistroConsolidado);die;
                if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0){
                    $operativo = 1; // Primer Bimestre
                }
                if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0){
                    $operativo = 2; // segundo Bimestre
                }
                if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] == 0){
                    $operativo = 3; // tercero Bimestre
                }
                if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1){
                    $operativo = 4; // cuarto Bimestre
                }
                if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1){
                    $operativo = 1; // Fin de gestion o cerrado
                }
            }  
        }



        if( in_array($this->session->get('roluser'), array(7,8,10)) ){
            $operativo = $operativo - 1;
        }

        return $operativo;

    }
    public function obtenerOperativoTrimestre2020($sie,$gestion){
        $objRegistroConsolidado = $this->em->createQueryBuilder()
                ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
                ->where('rc.unidadEducativa = :ue')
                ->andWhere('rc.gestion = :gestion')
                ->setParameter('ue',$sie)
                ->setParameter('gestion',$gestion)
                ->getQuery()
                ->getResult();

        $operativo = 4;
        if(!$objRegistroConsolidado){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($objRegistroConsolidado);die;
            if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] == 0 and $objRegistroConsolidado[0]['bim3'] == 0){
                $operativo = 2; // segundo Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] == 0){
                $operativo = 3; // tercero Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] >= 1 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1){
                $operativo = 4; // cuarto Bimestre
            }
            if($objRegistroConsolidado[0]['bim1'] == 0 and $objRegistroConsolidado[0]['bim2'] >= 1 and $objRegistroConsolidado[0]['bim3'] >= 1){
                $operativo = 1; // Fin de gestion o cerrado
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
            if($objRegistroConsolidado[0]['bim1'] == 3){
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
        }

        return $operativo;

    }

    public function setLogTransaccion($key,$tabla,$tipoTransaccion,$observacion,$valorNuevo,$valorAnt,$sistema,$archivo){
        //try {
            // $serializer = SerializerBuilder::create()->build();
            // $valorAnt = $serializer->serialize($valorAnt, 'json');
            // $serializer2 = SerializerBuilder::create()->build();
            // $valorNuevo = $serializer2->serialize($valorNuevo, 'json');
            $valorNuevo = json_encode($valorNuevo);
            $valorAnt = json_encode($valorAnt);

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
                $tipo = array(
                    'id'=>$t->getInstitucioneducativaHumanisticoTecnicoTipo()->getId(),
                    'tipo'=>$t->getInstitucioneducativaHumanisticoTecnicoTipo()->getInstitucioneducativaHumanisticoTecnicoTipo(),
                    'grado'=>$t->getGradoTipo()->getId(),
                    'academico'=>true
                );
            }
        }else{
            $tipo = array(
                'id'=>5,
                'tipo'=>'Humanistica',
                'grado'=>0,
                'academico'=>false
            );
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

            case '20':
                $where = '1 = 1';
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
            case when rc.bim4 > 0 then 'SI' else 'NO' end AS bim4,
            case when rc.rude = 1 then 'SI' else 'NO' end AS rude,
            rc.gestion
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
            rc.institucioneducativa_tipo_id = ".$instipoid." AND inst.estadoinstitucion_tipo_id = 10
            ORDER BY
            codigo_departamento,
            codigo_distrito,
            codigo_sie;
        ");
        
        $query->execute();
        $registro_consolidacion = $query->fetchAll();
        return $registro_consolidacion;
    }

    /**
     * dcastillo
     * los que NO han cerrado operativo en la gestion actual
     */
    public function reporteNoConsol($gestionid, $roluser, $roluserlugarid, $instipoid){
        
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

        /*$query = $this->em->getConnection()->prepare("
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
            case when rc.bim4 > 0 then 'SI' else 'NO' end AS bim4,
            rc.gestion
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
            rc.institucioneducativa_tipo_id = ".$instipoid." AND inst.estadoinstitucion_tipo_id = 10
            ORDER BY
            codigo_departamento,
            codigo_distrito,
            codigo_sie;
        ");*/

        $query = $this->em->getConnection()->prepare("
        SELECT
            * 
        FROM
            (
            SELECT
                inst.ID,
                lt4.codigo AS codigo_departamento,
                lt4.lugar AS departamento,
                dt.ID codigo_distrito,
                dt.distrito,
                inst.ID codigo_sie,
                inst.institucioneducativa,
                'NO' AS bim1,
                'NO' AS bim2,
                'NO' AS bim3,
                'NO' AS bim4 --rc.gestion
                
            FROM
                institucioneducativa inst
                INNER JOIN jurisdiccion_geografica jg ON jg.ID = inst.le_juridicciongeografica_id
                LEFT JOIN lugar_tipo lt ON lt.ID = jg.lugar_tipo_id_localidad
                LEFT JOIN lugar_tipo lt1 ON lt1.ID = lt.lugar_tipo_id
                LEFT JOIN lugar_tipo lt2 ON lt2.ID = lt1.lugar_tipo_id
                LEFT JOIN lugar_tipo lt3 ON lt3.ID = lt2.lugar_tipo_id
                LEFT JOIN lugar_tipo lt4 ON lt4.ID = lt3.lugar_tipo_id
                INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.ID 
            WHERE ".$where."  AND inst.estadoinstitucion_tipo_id =  10 and inst.institucioneducativa_acreditacion_tipo_id = 1 and inst.institucioneducativa_tipo_id = 1 
            ORDER BY
                codigo_departamento,
                codigo_distrito,
                codigo_sie 
            ) AS datos 
        WHERE
            datos.codigo_sie NOT IN ( SELECT unidad_educativa FROM registro_consolidacion WHERE gestion = ".$gestionid." AND institucioneducativa_tipo_id = ".$instipoid." )
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
            if( 
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
            if( //$centroAllowed &&
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
                ->andWhere('em.id in (:mat)')
                ->setParameter('id', $data['codigoRude'])
                ->setParameter('idTipo',1)
                ->setParameter('levels',array(3,4,5,6))
                ->setParameter('level', 13)
                ->setParameter('mat', array(4,5,55,11))
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

    public function getSpeciality($data){
    //dump($data);die;
           $query = $this->em->createQueryBuilder()
                    ->select('esp')
                    ->from('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico', 'ieeth')
                    ->innerjoin('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo', 'esp', 'WITH', 'ieeth.especialidadTecnicoHumanisticoTipo = esp.id')
                    ->where('ieeth.institucioneducativa = :currentSie')
                    ->andwhere('ieeth.gestionTipo = :currentGestion')
                    ->setParameter('currentSie',$data['currentSie'])
                    ->setParameter('currentGestion',$data['currentGestion'])
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
                ->setParameter('gestion',$gestion - 1)
                ->getQuery()
                ->getResult();

        $response = false;
        // Verificamos si se realizo el cierre de sexto grado
        // if($regConsol[0]->getCierreSextosec() != null){
        //     $response = true;
        // }
        
        return $response;

    }

    public function getConsolidationInitioOpe($sie,$gestion){

        $repositorio = $this->em->getRepository('SieAppWebBundle:RegistroConsolidacion');
        $regConsol = $repositorio->createQueryBuilder('rc')
                ->where('rc.unidadEducativa = :ue')
                ->andWhere('rc.gestion = :gestion')
                ->setParameter('ue',$sie)
                ->setParameter('gestion',$gestion)
                ->getQuery()
                ->getResult();

        if(!sizeof($regConsol)>0 ){
            return false;
        }else{
            return true;
        }

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

  /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    public function getInscriptionBthByGestion($data) {

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
                ->andWhere('em.id in(:estmat)')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $data['codigoRude'])
                ->setParameter('idTipo',1)
                ->setParameter('estmat',array(4,5,55,11))
                ->setParameter('gestion',$data['gestion'])
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

    public function getTheCurrentYear($dob, $fechaLimit){

        $today = $fechaLimit;
        $dob_a = explode("-", $dob);
        $today_a = explode("-", $today);
        $dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
        $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
        $years = $today_y - $dob_y;
        $months = $today_m - $dob_m;
        if ($today_m.$today_d < $dob_m.$dob_d) 
        {
            $years--;
            $months = 12 + $today_m - $dob_m;
        }

        if ($today_d < $dob_d) 
        {
            $months--;
        }

        $firstMonths=array(1,3,5,7,8,10,12);
        $secondMonths=array(4,6,9,11);
        $thirdMonths=array(2);

        if($today_m - $dob_m == 1) 
        {
            if(in_array($dob_m, $firstMonths)) 
            {
                array_push($firstMonths, 0);
            }
            elseif(in_array($dob_m, $secondMonths)) 
            {
                array_push($secondMonths, 0);
            }elseif(in_array($dob_m, $thirdMonths)) 
            {
                array_push($thirdMonths, 0);
            }
        }
        $arrAge = array('age'=>$years, 'months'=>$months);
        return $arrAge;
        // $dias = explode("-", $fechanacimiento, 3);
        // $dias = mktime(0,0,0,$dias[1],$dias[0],$dias[2]);
        // $edad = (int)((time()-$dias)/31556926 );
        // return $edad;        
        // list($dia,$mes,$anno) = explode("-",$fechanacimiento);
        // list($diaLimit,$mesLimit,$annoLimit) = explode("-",$fechaLimit);


        // $ano_diferencia = $annoLimit - $anno;
        // $mes_diferencia = $mesLimit - $mes;
        // $dia_diferencia = $diaLimit - $dia;
        
        // if ($dia_diferencia < 0 && $mes_diferencia <= 0){
        //     $ano_diferencia--;
        // }

        // return $ano_diferencia;
    }    



    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturno($form) {

        $arrInstitucioneducativa = json_decode($form['jsonInstitucioneducativa'], true);
        
        $aturnos = array();

        $entity = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                // ->andWhere('iec.nivelTipo = :nivel')
                // ->andwhere('iec.gradoTipo = :grado')
                // ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $arrInstitucioneducativa['id'])
                // ->setParameter('nivel', $nivel)
                // ->setParameter('grado', $grado)
                // ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $form['gestion'])
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $this->em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }
        return $aturnos;
        // $response = new JsonResponse();
        // return $response->setData(array('aturnos' => $aturnos));
    }


    public function getlevel($data){

        $entity = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.turnoTipo = :idturno')
                // ->andwhere('iec.nivelTipo != :nivel')
                ->setParameter('sie', $data['sie'])
                ->setParameter('gestion', $data['gestion'])
                ->setParameter('idturno', $data['idturno'])
                // ->setParameter('nivel', '13')
                ->distinct()
                ->orderBy('iec.nivelTipo', 'ASC')
                ->getQuery();
        $aNiveles = $query->getResult();
        $arrNiveles = array();
        foreach ($aNiveles as $nivel) {
            $arrNiveles[$nivel[1]] = $this->em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
        }
        return $arrNiveles;
    }

    public function getgrado($data) {
        //get grado
        $agrados = array();
        $entity = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.turnoTipo = :idturno')
                ->setParameter('sie', $data['sie'])
                ->setParameter('idnivel', $data['idnivel'])
                ->setParameter('gestion', $data['gestion'])
                ->setParameter('idturno', $data['idturno'])
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();
        foreach ($aGrados as $grado) {
            $agrados[$grado[1]] = $this->em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado();
        }

      return $agrados;
    }


    public function getparalelo($data){
        $aparalelos = array();
        $entity = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $data['sie'])
                ->setParameter('nivel', $data['idnivel'])
                ->setParameter('grado', $data['idgrado'])
                ->setParameter('turno', $data['idturno'])
                ->setParameter('gestion', $data['gestion'])
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = $this->em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
        }

        return $aparalelos;
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
        if(isset($data['consolidacionValor'])){
            $objDownloadFilenewOpe->setDescripcion($data['consolidacionValor']);
        }

        $this->em->persist($objDownloadFilenewOpe);
        $this->em->flush();

        return $objDownloadFilenewOpe;
        } catch (Exception $e) {  
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }    

    /**
     * Servicio para validar si un estudiante tiene diploma de bachiller
     * @param  [integer] $idInscripcion     [Id de inscripcion del estudiante]
     * @return [boolean]                    [Retorna true si el estudainte tiene diploma, false si no tiene]
     */
    public function validarTieneDiploma($idInscripcion){
        try {
            $diploma = $this->em->createQueryBuilder()
                                ->select('d')
                                ->from('SieAppWebBundle:Tramite','t')
                                ->innerJoin('SieAppWebBundle:Documento','d','with','d.tramite = t.id')
                                ->where('t.estudianteInscripcion = :idInscripcion')
                                ->andWhere('d.documentoEstado = 1')
                                ->setParameter('idInscripcion', $idInscripcion)
                                ->getQuery()
                                ->getResult();

            $tieneDocumento = false;
            // Verificamos si tiene diploma o algun documento
            if($diploma){
                $tieneDocumento = true;
            }

            return $tieneDocumento;

        } catch (Exception $e) {
            
        }
    }

    /**
     * Servicio para validar si el estudiante tiene un documento emitido
     * @param  [type] $codigoRude       [Rude del estudiante]
     * @return [object]                 [listado de documentos del estudiante]
     */
    public function validarDocumentoEstudiante($codigoRude){
        $query = $this->em->getConnection()->prepare("
                    select *
                    from documento as d
                    inner join tramite as t on t.id = d.tramite_id
                    inner join estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    where e.codigo_rude = '". $codigoRude ."' and d.documento_estado_id = 1 and d.documento_tipo_id in (1,9,8) /*and d.documento_tipo_id = 1*/
                    ");

        $query->execute();
        $documentos = $query->fetchAll();

        return $documentos;
    }
    /**
     * Servicio para cupos en cursos de educacion especial
     * Accesos: Vistas y controladores
     * Autor: Patricia
     */
    public function getCantidadEstudiantes($infoUe,$estudiantes){
        
        $infoUe = unserialize($infoUe);
        $cantidad = 0;
        $area = $infoUe['ueducativaInfoId']['areaEspecialId'];
        $programa = $infoUe['ueducativaInfoId']['programaId'];
        $iecLugar = $infoUe['ueducativaInfo']['iecLugar'];
        switch($infoUe['ueducativaInfoId']['areaEspecialId']){
            case 2:
                foreach($estudiantes as $est ){
                    if($est['estadomatriculaId'] != 6 and $est['estadomatriculaId'] != 78){
                        $cantidad = $cantidad + 1;
                    }
                }
               // Se ha quitado el cupo de inscripcion
               // if(($programa == 14 and $cantidad >= 4) or ($programa == 8 and $cantidad >= 6) or (in_array($programa, array(9,15,10,16)) and $cantidad >= 7) or ($programa == 11  and $cantidad >= 3) or ($programa == 12  and $cantidad >= 8)){ 
                if(($programa == 14 and $cantidad >= 444) or ($programa == 8 and $cantidad >= 966) or (in_array($programa, array(9,15,10,16)) and $cantidad >= 777) or ($programa == 11  and $cantidad >= 333) or ($programa == 12  and $cantidad >= 888)){
                    $data['cupo'] = "NO";
                    $data['msg'] = "El curso para este programa no puede tener más de <strong>". $cantidad. " estudiantes activos.</strong>";
                }else{
                    $data['cupo'] = "SI";
                    $data['msg'] = "El curso cuenta con cupos";
                }
                break;
            case 3:
            case 4:
            case 5:
                foreach($estudiantes as $est ){
                    if($est['estadomatriculaId'] != 6 and $est['estadomatriculaId'] != 10 and $est['estadomatriculaId'] != 78){
                        $cantidad = $cantidad + 1;
                    }
                }
                if($cantidad >= 222 and (preg_match("/EDUCACION SOCIOCOMUNITARIA EN CASA/i", $iecLugar) or preg_match("/EDUCACIÓN SOCIOCOMUNITARIA EN CASA/i", $iecLugar) and $infoUe['ueducativaInfoId']['areaEspecialId']==4)){
                    $data['cupo'] = "NO";
                    $data['msg'] = "El curso o grupo no puede tener más de <strong>". $cantidad. " estudiantes activos.</strong>";
                }else{
                    $data['cupo'] = "SI";
                    $data['msg'] = "El curso cuenta con cupos";
                }
                break;
            default:
                $data['cupo'] = "SI";
                $data['msg'] = "El curso cuenta con cupos";
        }
        return $data;
    }    


    /**
     * Servicio para eliminar especialidades bth
     * @param  [integer] $codsie     [Id de inscripcion del estudiante]
     * @param  [integer] $gestion    [id de la gestion]
     * @return [boolean]             [Retorna true si elimmino las especialidades y false si no tiene especialidades]
     */
    public function eliminarEspecialidad($codsie,$gestion){
       
        $ieht = $this->em->createQueryBuilder()
        ->delete('esp')
        ->from('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico','esp')
        ->where('esp.institucioneducativa = '. $codsie)
        ->andWhere('esp.gestionTipo='. $gestion)
        ->getQuery()
        ->getResult();
        
    }

    /**
     * Service to check the users tuicion
     * @param  [array] $codrude    [codigoRude, userId, gestion]
     */
    public function getInscriptionToValidateTuicion($form, $gestion, $calidad){
        return true;
        //look for the current inscription on 4.5.11 matricula id
        $entity = $this->em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('iec')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('e.codigoRude = :id')
                ->andWhere('ei.estadomatriculaTipo IN (:mat)')
                ->andWhere('iec.gestionTipo = :gestion');
        if($calidad) {
            $query = $query->andWhere('iec.nivelTipo <> 13')
                ->andWhere('iec.gradoTipo <> 6');
        }
        $query = $query->setParameter('id', $form['codigoRude'])
                ->setParameter('mat', array(4, 5, 11, 28, 61, 62, 63))
                ->setParameter('gestion', $gestion)
                ->orderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        
        $objCurrentInscripcion = $query->getResult();
        //dump($objCurrentInscripcion);die;
        $swtucion = false;
        if($objCurrentInscripcion){
            while (($objectUe = current($objCurrentInscripcion)) !== FALSE && !$swtucion) {
                // check the tución info
                $currentSie = $objectUe->getid();
                $query = $this->em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $currentSie);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetch(); 
                if($aTuicion['get_ue_tuicion']){
                    $swtucion = $aTuicion['get_ue_tuicion'];
                }
                next($objCurrentInscripcion);
            }
            return ($swtucion);
        }else{
            return false;
        }
                
    }    

    /**
     * Service to check the users tuicion in inscription process
     * @param  [array] $codrude    [codigoRude, gestion]
     */
    public function getInscriptionToValidateTuicionUe($rue, $gestion){
        //$this->session->get('roluser')
        //look for the current inscription on 4.5.11 matricula id
        $entity = $this->em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('iec')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('e.codigoRude = :id')
                ->andWhere('ei.estadomatriculaTipo IN (:mat)')
                ->andWhere('iec.gestionTipo = :gestion');
        $query = $query->setParameter('id', $rue)
                ->setParameter('mat', array(4))
                ->setParameter('gestion', $gestion)
                ->orderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        
        $objCurrentInscripcion = $query->getResult();
        $swtucion = false;
        if($objCurrentInscripcion){
            while (($objectUe = current($objCurrentInscripcion)) !== FALSE && !$swtucion) {
                // check the tución info
                $currentSie = $objectUe->getInstitucioneducativa()->getId();
              
                $query = $this->em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $currentSie);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetch(); 
                if($aTuicion['get_ue_tuicion']){
                    $swtucion = $aTuicion['get_ue_tuicion'];
                }
                next($objCurrentInscripcion);
            }
            return ($swtucion);
        }else{
            return false;
        }
                
    } 
    /**
     * [existeInscripcionSimilarAprobado description]
     * @param  integer    $idInscripcion [inscripcion del estudiante para verificar si existe otra inscripcion similar con los diferentes estados]
     * @return boolean    response [true=si existe otra inscripcion similar, false=no existe inscripcion similar]
     */
    public function existeInscripcionSimilarAprobado($idInscripcion){
        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $estudiante = $inscripcion->getEstudiante()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        /* ESTADOS

        4 EFECTIVO
        5 PROMOVIDO
        24 APROBADO HOMOLOGACION
        26 PROMOVIDO POST-BACHILLERATO
        37 PROMOVIDO
        45 APROBADO HOMOLOGACION
        46 EFECTIVO
        55 PROMOVIDO BACHILLER DE EXCELENCIA
        56 PROMOVIDO POR NIVELACION
        57 PROMOVIDO POR REZAGO ESCOLAR
        58 PROMOVIDO TALENTO EXTRAORDINARIO
        */

        $otraInscripcion = $this->em->createQueryBuilder()
                        ->select('ei')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->where('ei.estudiante = :estudiante')
                        ->andWhere('ei.id != :idInscripcion')
                        ->andWhere('ei.estadomatriculaTipo IN (:estados)')
                        ->andWhere('iec.nivelTipo = :nivel')
                        ->andWhere('iec.gradoTipo = :grado')
                        ->setParameter('estudiante', $estudiante)
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->setParameter('estados', array(4,5,24,26,37,45,46,55,56,57,58)) // Estados que deveria validar
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();

        $response = false;
        if (count($otraInscripcion) > 0) {
            $response = true;
        }

        return $response;
    }

    public function getEstudianteBachillerHumanisticoAlternativa($institucioneducativa_id, $gestion_id, $genero_id){
        $query = $this->em->getConnection()->prepare("
            select
            distinct on (nt.id, nt.nivel, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            dt.id as dependencia_tipo_id,
            dt.dependencia,
            oct.id as orgcurricular_tipo_id,
            oct.orgcurricula,
            ie.le_juridicciongeografica_id,
            ie.id as institucioneducativa_id,
            ie.institucioneducativa,
            ies.gestion_tipo_id,
            nt.id as nivel_tipo_id,
            nt.nivel,
            ct.id as ciclo_tipo_id,
            ct.ciclo,
            sat.codigo as grado_tipo_id,  
            sat.acreditacion as grado,  
            pt.id as paralelo_tipo_id,
            pt.paralelo,
            tt.id as turno_tipo_id,
            tt.turno,
            pet.id as periodo_tipo_id,
            pet.periodo,
            e.id as estudiante_id,
            e.codigo_rude,
            e.carnet_identidad as carnet,  
            e.complemento,
            cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad,
            e.pasaporte,
            e.paterno,
            e.materno,
            e.nombre,
            e.segip_id,
            gt.id as genero_tipo_id,
            gt.genero,
            to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento,  
            e.localidad_nac,
            emt.id as estadomatricula_tipo_id,
            emt.estadomatricula,
            ei.id as estudiante_inscripcion_id,  
            case pat.id when 1 then lt2.lugar when 0 then '' else pat.pais end as lugar_nacimiento,
            CASE
            WHEN sfat.codigo = 13 THEN
            'Regular Humanística'
            WHEN sfat.codigo = 15 THEN
            'Alternativa Humanística'
            WHEN sfat.codigo > 17 THEN
            'Alternativa Técnica'
            END AS subsistema,
            e.lugar_prov_nac_tipo_id as lugar_nacimiento_id,
            lt2.codigo as depto_nacimiento_id,
            lt2.lugar as depto_nacimiento,
            t.id as tramite_id, d.id as documento_id, d.documento_serie_id as documento_serie_id, e.segip_id
            , eid.documento_numero as documento_diplomatico
            , ei.estadomatricula_inicio_tipo_id as estadomatricula_inicio_tipo_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            inner join estadomatricula_tipo as emt on emt.id = ei.estadomatricula_tipo_id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pat on pat.id = e.pais_tipo_id
            inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
            inner join orgcurricular_tipo as oct on oct.id = ie.orgcurricular_tipo_id
            inner join nivel_tipo as nt on nt.id = sfat.codigo
            inner join ciclo_tipo as ct on ct.id = iec.ciclo_tipo_id
            inner join paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
            inner join turno_tipo as tt on tt.id = iec.turno_tipo_id
            inner join periodo_tipo as pet on pet.id = ies.periodo_tipo_id
            inner join genero_tipo as gt on gt.id = e.genero_tipo_id
            left join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (1) and t.esactivo = 't'
            left join documento as d on d.tramite_id = t.id and documento_tipo_id in (1,9) and d.documento_estado_id = 1
            left join estudiante_inscripcion_diplomatico as eid on eid.estudiante_inscripcion_id = ei.id
            where ie.id = ".$institucioneducativa_id."
            and ies.gestion_tipo_id = ".$gestion_id."::DOUBLE PRECISION
            and gt.id = ".$genero_id."        
            and sfat.codigo in (15) and sat.codigo in (3) and sest.codigo in (2)
            and ei.estadomatricula_tipo_id in (4,5,55)
            order by nt.id, nt.nivel, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc        
        ");

        $query->execute();
        $bachilleres = $query->fetchAll();

        return $bachilleres;
    }

    /**
     * Funcion para verificar si se cerro el operativo de registro de calificaciones
     * para sexto de secundaria
     * @param  integer $sie     [description]
     * @param  integer $gestion [description]
     * @return [boolean]        si se cerro el operativo true , false si no se cerro el operativo
     */
    public function verificarSextoSecundariaCerrado($sie, $gestion){
        $registro = $this->em->createQueryBuilder()
                            ->select('ieol')
                            ->from('SieAppWebBundle:InstitucioneducativaOperativoLog','ieol')
                            ->where('ieol.institucioneducativa = :sie')
                            ->andWhere('ieol.gestionTipoId = :gestion')
                            ->andWhere('ieol.institucioneducativaOperativoLogTipo = 10')
                            ->setParameter('sie', $sie)
                            ->setParameter('gestion', $gestion)
                            ->getQuery()
                            ->getResult();

        $sextoCerrado = false;
        if ($registro) {
            $sextoCerrado = true;
        }

        return $sextoCerrado;
    }

    public function verificarApEspecializadosCerrado($sie, $gestion, $periodo){
        $registro = $this->em->createQueryBuilder()
                            ->select('ieol')
                            ->from('SieAppWebBundle:InstitucioneducativaOperativoLog','ieol')
                            ->where('ieol.institucioneducativa = :sie')
                            ->andWhere('ieol.gestionTipoId = :gestion')
                            ->andWhere('ieol.periodoTipo = :periodo')
                            ->andWhere('ieol.institucioneducativaOperativoLogTipo = 16')
                            ->setParameter('sie', $sie)
                            ->setParameter('gestion', $gestion)
                            ->setParameter('periodo', $periodo)
                            ->getQuery()
                            ->getResult();

        $sextoCerrado = false;
        if ($registro) {
            $sextoCerrado = true;
        }

        return $sextoCerrado;
    }

    public function lookforRudesbyDataStudent($data){

        
        $query = $this->em->createQueryBuilder('e')
                ->select('e')
                ->from('SieAppWebBundle:Estudiante','e')
                ->where('e.paterno like :paterno')
                ->andWhere('upper(e.materno) like :materno')
                ->andWhere('upper(e.nombre) like :nombre')
                ->setParameter('paterno', '%' . mb_strtoupper($data['paterno'], 'utf8') . '%')
                ->setParameter('materno', '%' . mb_strtoupper($data['materno'], 'utf8') . '%')
                ->setParameter('nombre', '%' . mb_strtoupper($data['nombre'], 'utf8') . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();

        return $entities;
        
    }

    public function searchByExcaustiveDataStudent($data){

        $oficialia = $data['oficialia'];
        if( empty($oficialia) || $oficialia == 'UNDEFINED' ){
            $oficialia = '';
        }

        $libro = $data['libro'];
        if( empty($libro) || $libro == 'UNDEFINED' ){
            $libro = '';
        }

        $partida = $data['partida'];
        if( empty($partida) || $partida == 'UNDEFINED' ){
            $partida = '';
        }

        $folio = $data['folio'];
        if( empty($folio) || $folio == 'UNDEFINED' ){
            $folio = '';
        }
        // dump($oficialia);
        // dump($libro);
        // dump($partida);
        //  dump(new \DateTime($data['fechaNacimiento']));
        //  die;
        $query = $this->em->createQueryBuilder('e')
                ->select('e')
                ->from('SieAppWebBundle:Estudiante','e')
                ->where('e.paterno like :paterno')
                ->andWhere('upper(e.materno) like :materno')
                ->andWhere('upper(e.nombre) like :nombre')
                ->andWhere('e.fechaNacimiento = :fechaNacimiento')
                ->andWhere('e.oficialia = :oficialia')
                ->andWhere('e.libro = :libro')
                ->andWhere('e.partida = :partida')
                ->andWhere('e.folio = :folio')
                ->setParameter('paterno', '%' . mb_strtoupper($data['paterno'], 'utf8') . '%')
                ->setParameter('materno', '%' . mb_strtoupper($data['materno'], 'utf8') . '%')
                ->setParameter('nombre', '%' . mb_strtoupper($data['nombre'], 'utf8') . '%')
                ->setParameter('fechaNacimiento', new \DateTime($data['fechaNacimiento']))
                ->setParameter('oficialia', mb_strtoupper($oficialia, 'utf8'))
                ->setParameter('libro', mb_strtoupper($libro, 'utf8'))
                ->setParameter('partida', mb_strtoupper($partida, 'utf8'))
                ->setParameter('folio', mb_strtoupper($folio, 'utf8'))
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();

        return $entities;

    }

    public function getAllInscriptionRegular($codigoRude){
        
        $query = $this->em->createQueryBuilder('e')
                ->select('ei.id as studenInscriptionId,n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
                ->from('SieAppWebBundle:Estudiante','e')
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
                ->setParameter('id', $codigoRude)
                ->setParameter('idTipo',1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        $entities = $query->getResult();

        return $entities;                
    }


    public function getCurrentInscriptionRegular($codigoRude, $gestion){
        
        $query = $this->em->createQueryBuilder('e')
                ->select('ei.id as studenInscriptionId,n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
                ->from('SieAppWebBundle:Estudiante','e')
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
                ->andWhere('it.id = :idTipo')
                ->andWhere('iec.gestionTipo = :gestionTipo')
                ->setParameter('id', $codigoRude)
                ->setParameter('idTipo',1)
                ->setParameter('gestionTipo',$gestion)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        $entities = $query->getResult();

        return $entities;                
    }    

    public function getCurrentInscriptionByRudeAndGestionAndMatricula($data){
        
        $query = $this->em->createQueryBuilder('e')
                ->select('ei.id as studenInscriptionId', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
                ->from('SieAppWebBundle:Estudiante','e')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('iec.gestionTipo = :gestion')                
                ->andWhere('ei.estadomatriculaTipo = :matricula')                
                ->andWhere('it = :idTipo')
                ->setParameter('id', $data['codigoRude'])
                ->setParameter('gestion', $data['gestion'])
                ->setParameter('matricula', $data['matriculaId'])
                ->setParameter('idTipo',1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        $entities = $query->getResult();

        return $entities;                
    } 

    public function getuserInscriptions($userId){
        $query = $this->em->getConnection()->prepare("
            select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,a.esactivo,a.id as user_id
            from usuario a 
                inner join usuario_rol b on a.id=b.usuario_id 
                inner join lugar_tipo c on b.lugar_tipo_id=c.id
            where substring(c.codigo,1,1) in ('7') and codigo not in ('04') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo='t'and a.id = ".$userId);

        $query->execute();
        $userInscriptions = $query->fetchAll();

        return $userInscriptions;
    }    

    public function getuserAccessToCalifications($userId,$valor){
        $valor =implode(',', $valor);
         // dump($valor); exit();
        /*$queryAccess = "
          select *
          from (
          select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
          from maestro_inscripcion a
            inner join institucioneducativa b on a.institucioneducativa_id=b.id
              inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
                inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                  inner join usuario e on a.persona_id=e.persona_id
                    inner join usuario_rol f on e.id=f.usuario_id
          where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and substring(d.codigo,1,1) in ('7','8','9','1','3','6','0') and e.esactivo='t') a
            where user_id=".$userId.";
        ";*/      
        $queryAccess = "
          select *
          from (
          select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
          from maestro_inscripcion a
            inner join institucioneducativa b on a.institucioneducativa_id=b.id
              inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
                inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                  inner join usuario e on a.persona_id=e.persona_id
                    inner join usuario_rol f on e.id=f.usuario_id
          where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and substring(d.codigo,1,1)::integer in (".$valor.") and e.esactivo='t') a
            where user_id='".$userId."';
           
        ";   

        $query = $this->em->getConnection()->prepare($queryAccess);

        $query->execute();
        $userInscriptions = $query->fetchAll();

        return $userInscriptions;
    }  

    public function getUEspreInscription($idDepto, $gestion){
        
        $queryUes = "
         select distinct(g.id),  g.institucioneducativa
            from institucioneducativa g
                inner join jurisdiccion_geografica h on g.le_juridicciongeografica_id=h.id
                     inner join distrito_tipo i on h.distrito_tipo_id = i.id
                        inner join departamento_tipo dt on (i.departamento_tipo_id = dt.id)
                            inner join preins_institucioneducativa_curso_cupo pin on (g.id = pin.institucioneducativa_id )
                            where dt.id = '".$idDepto."' and gestion_tipo_id = '".$gestion."';           
        ";   

        $query = $this->em->getConnection()->prepare($queryUes);

        $query->execute();
        $uesPreins = $query->fetchAll();

        return $uesPreins;
    }         
    public function chooseUE($idDepto, $sie, $gestion){
        
        $queryUes = "
         select *
            from institucioneducativa g
                inner join jurisdiccion_geografica h on g.le_juridicciongeografica_id=h.id
                     inner join distrito_tipo i on h.distrito_tipo_id = i.id
                        inner join departamento_tipo dt on (i.departamento_tipo_id = dt.id)
                            inner join preins_institucioneducativa_curso_cupo pin on (g.id = pin.institucioneducativa_id )
                                inner join dependencia_tipo dpt on (g.dependencia_tipo_id = dpt.id)
                                    inner join institucioneducativa_tipo int on (g.institucioneducativa_tipo_id = int.id)
                                        inner join estadoinstitucion_tipo eitt on (g.estadoinstitucion_tipo_id = eitt.id)
                            where dt.id = '".$idDepto."' and  g.id = '".$sie."' and pin.gestion_tipo_id = '".$gestion." '           
        limit 1";   

        $query = $this->em->getConnection()->prepare($queryUes);

        $query->execute();
        $uesPreins = $query->fetchAll();

        return $uesPreins;
    }      
    
    public function getAllJustify(){
        $allJustify = $this->em->getRepository('SieAppWebBundle:PreinsJustificativoTipo')->findAll();

        return $allJustify;
    }

    public function getLevelUE($sie, $gestion){

        // $objLevels = $this->em->createQueryBuilder()
        //     ->select('IDENTITY(iec.nivelTipo)')
        //     ->from('SieAppWebBundle:InstitucioneducativaCurso', 'iec')
        //     ->where('iec.institucioneducativa = :sie')
        //     ->andwhere('iec.gestionTipo = :gestion')
        //     ->setParameter('sie', $sie)
        //     ->setParameter('gestion', $gestion )
        //     ->orderBy('iec.nivelTipo', 'ASC')
        //     ->distinct()
        //     ->getQuery()
        //     ->getResult();  
        $queryUes = "
         select distinct(pin.nivel_tipo_id)
            from preins_institucioneducativa_curso_cupo pin
                where  pin.institucioneducativa_id = '".$sie."' and pin.gestion_tipo_id = '".$gestion." ' 
                order by pin.nivel_tipo_id
                ";   

        $query = $this->em->getConnection()->prepare($queryUes);

        $query->execute();
        $uesPreins = $query->fetchAll();        
        
        return($uesPreins);



    }

    public function getcurrentInscriptinoValidation($idInscripcion){
        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $estudiante = $inscripcion->getEstudiante()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        
        $response = false;
        $dataRemove = array();

        $currentInscription = $this->em->createQueryBuilder()
                        ->select('ei,iec')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->where('ei.estudiante = :estudiante')
                        ->andWhere('ei.estadomatriculaTipo IN (:estados)')
                        ->andWhere('iec.nivelTipo = :nivel')
                        ->andWhere('iec.gradoTipo = :grado')
                        ->setParameter('estudiante', $estudiante)                        
                        ->setParameter('estados', array(4,5,24,26,37,45,46,55,56,57,58)) // Estados que deveria validar
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
        $response = false;

        if (count($currentInscription) > 0) {
        // get data course
            $dataRemove['inscripcion']['id']    = $currentInscription[0]->getId();
            $dataRemove['inscripcion']['studentid']    = $currentInscription[0]->getEstudiante()->getId();
            $dataRemove['inscripcion']['nivel'] = $nivel;
            $dataRemove['inscripcion']['grado'] = $grado;
            $dataRemove['inscripcion']['gestion'] = $currentInscription[1]->getGestionTipo()->getId();;
            $asignaturas = $this->em->createQueryBuilder()
                    ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                    ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                    ->groupBy('asit.id, asit.asignatura, ea.id')
                    ->orderBy('asit.id','ASC')
                    ->where('ei.id = :idInscripcion')
                    ->setParameter('idInscripcion',$currentInscription[0]->getId())
                    ->getQuery()
                    ->getResult();   
                 // dumP($asignaturas);
            $dataRemove['inscripcion']['asignaturas'] = $asignaturas;

            foreach ($asignaturas as $a) {
                // $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $this->em->createQueryBuilder()
                                    ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                    ->from('SieAppWebBundle:EstudianteNota','en')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                    ->orderBy('nt.id','ASC')
                                    ->where('ea.id = :estAsigId')
                                    ->setParameter('estAsigId',$a['estAsigId'])
                                    ->getQuery()
                                    ->getResult();                    
                $dataRemove['inscripcion']['asignaturasNotas'][] = $asignaturasNotas;

            }          

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$currentInscription[0]->getId()),array('notaTipo'=>'ASC'));
            $dataRemove['inscripcion']['cualitativas'] = $cualitativas;

            $response = true;
        }

        return $dataRemove;
    }     

}
