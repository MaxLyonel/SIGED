<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class DeteccionTipo extends AbstractEnumType
{
	const NINGUNO    = 'NINGUNO';
	const UNIDAD_EDUCATIVA    = 'UNIDAD EDUCATIVA';
	const OLIMPIADAS_CIENTIFICAS    = 'OLIMPIADAS CIENTIFICAS';
	const JUEGOS_DEPORTIVOS_PLURINACIONALES    = 'JUEGOS DEPORTIVOS PLURINACIONALES';
	const ENCUENTRO_ARTISTICO    = 'ENCUENTRO ARTISTICO';
	
	protected static $choices = [
			self::NINGUNO    => 'Ninguno',
			self::UNIDAD_EDUCATIVA    => 'Unidad Educativa',
			self::OLIMPIADAS_CIENTIFICAS    => 'Olimpiadas Cientificas',
			self::JUEGOS_DEPORTIVOS_PLURINACIONALES    => 'Juegos Deportivos Plurinacionales',
			self::ENCUENTRO_ARTISTICO    => 'Encuentro Artisitico'
	];	
	
}