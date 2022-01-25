<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class TalentoTipo extends AbstractEnumType
{
	const LOGICO_MATEMATICO_Y_TECNOLOGICO    = 'LOGICO, MATEMATICO Y ECNOLOGICO';
	const HUMANISTICO_SOCIAL    = 'HUMANISTICO SOCIAL';
	const ARTISTICO    = 'ARTISTICO';
	const DEPORTIVO    = 'DEPORTIVO';
	
	protected static $choices = [
			self::LOGICO_MATEMATICO_Y_TECNOLOGICO    => 'Logico, matematico y Tecnologico',
			self::HUMANISTICO_SOCIAL    => 'Humanistico Social',
			self::ARTISTICO    => 'Artistico',
			self::DEPORTIVO    => 'Deportivo'
			
	];	
	
}