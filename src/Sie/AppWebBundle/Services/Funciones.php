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
}
