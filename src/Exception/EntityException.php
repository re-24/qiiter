<?php

namespace re24\Qiiter\Exception;

class EntityException extends QiitaException
{
	public static function notContinProperty($entity,$property)
	{
		return new self(sprintf(
			'%s does not contain a property by the name of "%s"',
			$entity,
			$property
		));
	}
}