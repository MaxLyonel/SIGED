<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class OrigenDiscapacidadTipo extends AbstractEnumType
{
	const ANTES_DEL_NACIMIENTO    = 'ANTES DEL NACIMIENTO';
	const DURANTE_EL_PARTO = 'DURANTE EL PARTO';
	const DESPUES_DEL_NACIMIENTO = 'DESPUES DEL NACIMIENTO';
	protected static $choices = [
			self::ANTES_DEL_NACIMIENTO    => 'Antes del nacimiento',
			self::DURANTE_EL_PARTO => 'Durante el parto',
			self::DESPUES_DEL_NACIMIENTO => 'Despues del nacimiento'
	];	
	
}