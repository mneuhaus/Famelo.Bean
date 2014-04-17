<?php
namespace Famelo\Bean\Variables;

/**
 */
class AskVariable extends AbstractVariable {
	public function interact() {
		$this->value = $this->ask('<q>' . $this->configuration['question'] . '</q>' . chr(10));
		$this->outputLine();
	}
}