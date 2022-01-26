<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class GradoTalentoTipo extends AbstractEnumType
{
	const TALENTO_EXTRAORDINARIO_GENERAL    = 'TALENTO EXTRAORDINARIO GENERAL';
	const TALENTO_EXTRAORDINARIO_ESPECIFICO    = 'TALENTO EXTRAORDINARIO ESPECIFICO';
	
	protected static $choices = [
			self::TALENTO_EXTRAORDINARIO_GENERAL    => 'Talento Extraordinario General',
			self::TALENTO_EXTRAORDINARIO_ESPECIFICO    => 'Talento Extraordinario Especifico'
			
	];	
	
}