<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MaestroInscripcion {

	protected $em;
	protected $router;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
	}

	public function listar($sie, $gestion) {

        $queryCargos = $this->em->createQuery(
                'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2 AND ct.id = 0');
        $cargos = $queryCargos->getResult();

        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
        }

        $repository = $this->em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mi')
                ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, ft.formacion')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->setParameter('idInstitucion', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->distinct()
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $maestro = $query->getResult();

        $query = $repository->createQueryBuilder('mi')
            ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, ft.formacion')
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->leftJoin('SieAppWebBundle:MaestroInscripcionIdioma', 'maii', 'WITH', 'maii.maestroInscripcion = mi.id')
            ->where('mi.institucioneducativa = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo IN (:cargos)')
            ->andWhere('maii.id IS NULL')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->distinct()
            ->orderBy('p.paterno')
            ->addOrderBy('p.materno')
            ->addOrderBy('p.nombre')
            ->getQuery(); 
        
        $maestro_no_idioma = $query->getResult();

        $arrayNoIdioma = array();
        foreach ($maestro_no_idioma as $key => $value) {
            $arrayNoIdioma[$value['perId']] = $value['perId'];
        }

        $query = $repository->createQueryBuilder('mi')
            ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion')
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            ->where('mi.institucioneducativa = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo IN (:cargos)')
            ->andWhere('p.generoTipo = :genero')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->setParameter('genero', 3)
            ->distinct()
            ->orderBy('p.paterno')
            ->addOrderBy('p.materno')
            ->addOrderBy('p.nombre')
            ->getQuery(); 
        
        $maestro_no_genero = $query->getResult();

        $arrayNoGenero = array();
        foreach ($maestro_no_genero as $key => $value) {
            $arrayNoGenero[$value['perId']] = $value['perId'];
        }
		
		$response = ['maestro' => $maestro,
                    'maestro_no_idioma' => $arrayNoIdioma,
                    'maestro_no_genero' => $arrayNoGenero];

		return $response;
	}
}