<?php
namespace Famelo\Bean\Variables;

/**
 */
class EntityVariable extends AbstractVariable {
	public function interact() {
		$this->value = '\\' . ltrim($this->chooseClassNameAnnotatedWith(
			'<q>' . $this->configuration['question'] . '</q>' . chr(10),
			'\TYPO3\Flow\Annotations\Entity'
		), '\\');
		$this->interaction->outputLine();
	}
}