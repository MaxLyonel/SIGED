<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class ViveConTipo extends AbstractEnumType
{
	const NINGUNO    = 'NINGUNO';
	const PADRE    = 'PADRE';
	const MADRE    = 'MADRE';
	const ABUELO    = 'ABUELO';
	const ABUELA    = 'ABUELA';
	const AMBOS_PADRES    = 'AMBOS PADRES';
	const TIOS    = 'TIOS';
	const OTROS    = 'OTROS';
	
	protected static $choices = [
			self::NINGUNO    => 'Ninguno',
			self::PADRE    => 'Padre',
			self::MADRE    => 'Madre',
			self::ABUELO    => 'Abuelo',
			self::ABUELA    => 'Abuela',
			self::AMBOS_PADRES    => 'Ambos padres',
			self::TIOS    => 'Tios',
			self::OTROS    => 'Otros'
	];	
	
}