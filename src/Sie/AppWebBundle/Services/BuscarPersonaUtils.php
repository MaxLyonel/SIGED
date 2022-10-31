<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Entity\Persona;
//use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;


class BuscarPersonaUtils
{
	protected $em;
	protected $router;
	protected $session;

	public function __construct(EntityManager $entityManager, Router $router)
	{
		$this->em = $entityManager;
		$this->router = $router;
		$this->session = new Session();
	}

	public function buscarPersona($arrayDatosPersona,$conCI=true, $segipId=1)
	{
		$persona = null;
		$arrayCampos1 = array('carnet','paterno','materno','nombre','fechaNacimiento','complemento');
		$arrayCampos2 = array('carnet','paterno','materno','nombre','fechaNacimiento');
		$arrayCampos6 = array('carnet','paterno','materno','nombre');

		$arrayCampos3 = array('paterno','materno','nombre','fechaNacimiento');
		$arrayCampos4 = array('paterno','materno','nombre');

		$arrayCampos5 = array('carnet','fechaNacimiento','complemento'); // campos minimos para buscar persona

		if($conCI==false)
		{
			unset($arrayCampos1['carnet']);
			unset($arrayCampos2['carnet']);
			unset($arrayCampos6['carnet']);
			//unset($arrayCampos5['carnet']);
		}
		$arrayCampos = [$arrayCampos1,$arrayCampos2,$arrayCampos3,$arrayCampos4,$arrayCampos5,$arrayCampos6];

		$persona = null;

		foreach ($arrayCampos as $a)
		{
			$personaDatos = $this->generarCamposValoresPersona($arrayDatosPersona,$a);
			$personaDatos['segipId'] = $segipId;
			if($personaDatos != null)
			{
				$persona = $this->em->getRepository('SieAppWebBundle:Persona')->findOneBy($personaDatos);

				if($persona != null)
					break;
			}
		}
		return $persona;
	}

	public function generarCamposValoresPersona($arrayDatosPersona,$campos)
	{
		$personaDatos = null;
		$array_keys = array_keys($arrayDatosPersona);
		if(count($campos)>=3)
		{
			foreach ($campos as $value)
			{
				if(in_array($value,$array_keys))
				{
					if($value == 'fechaNacimiento')
						$personaDatos[$value] = new \DateTime($arrayDatosPersona[$value]);
					else
						$personaDatos[$value] = trim(strtoupper($arrayDatosPersona[$value]));
				}
			}
		}
		return $personaDatos;
	}

	public function buscarPersonav2($arrayDatosPersona,$conCI=true, $segipId=1)
	{
		$personas = null;
		$arrayCampos1 = array('carnet','complemento','fechaNacimiento'); // campos minimos para buscar persona
		$arrayCampos2 = array('carnet','complemento','fechaNacimiento','paterno');
		$arrayCampos3 = array('carnet','complemento','fechaNacimiento','paterno','materno');
		$arrayCampos4 = array('carnet','complemento','fechaNacimiento','paterno','materno','nombre');

		if($conCI==false)
		{
			unset($arrayCampos1['carnet']);
			unset($arrayCampos2['carnet']);
			unset($arrayCampos3['carnet']);
			unset($arrayCampos4['carnet']);
		}
		$arrayCampos = [$arrayCampos1,$arrayCampos2,$arrayCampos3,$arrayCampos4];

		$persona = null;

		foreach ($arrayCampos as $a)
		{
			$personaDatos = $this->generarCamposValoresPersona($arrayDatosPersona,$a);
			if($personaDatos != null)
			{
				$personaDatos['segipId'] = $segipId;
				$personas = $this->em->getRepository('SieAppWebBundle:Persona')->findBy($personaDatos);
				if(count($personas) == 1)
					break;
			}
		}
		if($personas != null && count($personas)==1)
			$personas = $personas[0];

		return $personas;
	}

}
