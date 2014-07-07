<?php
namespace Famelo\Bean\Variables;

/**
 */
class AskVariable extends AbstractVariable {
	/**
	 * @var string
	 */
	protected $partial = 'Textfield';

	public function interact() {
		$this->value = $this->interaction->ask('<q>' . $this->configuration['question'] . '</q>' . chr(10));
		$this->interaction->outputLine();
	}
}