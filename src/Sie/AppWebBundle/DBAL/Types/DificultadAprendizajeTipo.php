<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class DificultadAprendizajeTipo extends AbstractEnumType
{
	const DIFICULTADES_GENERALES    = 'DIFICULTADES GENERALES';
	const DIFICULTADES_ESPECIFICAS = 'DIFICULTADES ESPECIFICAS';
	const SIGNOS_DE_RIESGO = 'SIGNOS DE RIESGO';
	
	protected static $choices = [
			self::DIFICULTADES_GENERALES    => 'Dificultades Generales',
			self::DIFICULTADES_ESPECIFICAS => 'Dificultades Especificas',
			self::SIGNOS_DE_RIESGO => 'Signos de Riesgo'
	];	
	
}