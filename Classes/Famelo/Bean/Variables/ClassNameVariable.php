<?php
namespace Famelo\Bean\Variables;

/**
 */
class ClassNameVariable extends AskVariable {
	public function getDefaultValue() {
		#preg_match('/Domain\\\\Model\\\\(.+)/', $className, $match);
		return $this->source;
	}
}