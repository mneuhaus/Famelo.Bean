<?php
namespace Famelo\Bean\Variables;

/**
 */
class StaticVariable extends AbstractVariable {
	public function interact() {
		$this->value = $this->configuration['variable'];
	}
}