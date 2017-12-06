<?php

namespace Sie\AppWebBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
final class DiscapacidadEspecialTipo extends AbstractEnumType
{
	const NINGUNO    = 'NINGUNO';
	const AUDITIVA    = 'AUDITIVA';
	const VISUAL    = 'VISUAL';
	const INTELECTUAL    = 'INTELECTUAL';
	const SINDROME_DE_DOWN    = 'SINDROME DE DOWN';
	const AUTISMO    = 'AUTISMO';
	const FISICO_MOTORA    = 'FISICO-MOTORA';
	const MULTIPLE    = 'MULTIPLE';
	const SORDOCEGUERA    = 'SORDOCEGUERA';
	
	protected static $choices = [
			self::NINGUNO    => 'Ninguno',
			self::AUDITIVA    => 'Auditva',
			self::VISUAL    => 'Visual',
			self::INTELECTUAL    => 'Intelectual',
			self::SINDROME_DE_DOWN    => 'Sindrome de Down',
			self::AUTISMO    => 'Autismo',
			self::FISICO_MOTORA    => 'Fisico-Motora',
			self::MULTIPLE    => 'Multiple',
			self::SORDOCEGUERA    => 'Sordoceguera'
	];	
	
}