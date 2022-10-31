<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class GradoParentescoTipo extends AbstractEnumType
{
	const PRIMER_GRADO    = '1ER GRADO';
	const SEGUNDO_GRADO = '2DO GRADO';
	protected static $choices = [
			self::PRIMER_GRADO => '1er grado',
			self::SEGUNDO_GRADO => '2do grado'
	];	
	
}