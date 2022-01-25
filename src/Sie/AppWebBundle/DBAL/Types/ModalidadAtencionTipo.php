<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class ModalidadAtencionTipo extends AbstractEnumType
{
	const DIRECTA    = 'DIRECTA';
	const INDIRECTA = 'INDIRECTA';
	
	protected static $choices = [
			self::DIRECTA    => 'Directa',
			self::INDIRECTA => 'Indirecta'
	];	
	
}