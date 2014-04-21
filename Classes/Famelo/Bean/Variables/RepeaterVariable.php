<?php
namespace Famelo\Bean\Variables;

use TYPO3\Flow\Annotations as Flow;

/**
 */
class RepeaterVariable extends AbstractVariable {
	/**
	 * @var array
	 * @Flow\Inject(setting="Variables")
	 */
	protected $variableImplementations;

	public function interact() {
		$this->value = array();
		while (($variables = $this->fetchVariables()) !== FALSE) {
			$this->value[] = $variables;
		    $this->previewFields($this->value);
		}
	}

	public function fetchVariables() {
		$variables = array();
		$isFirst = TRUE;
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			$variableType = isset($variable['type']) ? $variable['type'] : 'ask';
			$variableImplementation = $this->getVariableImplementation($variableType);
			$variable = new $variableImplementation($variable, $this->previousVariables);
			$variable->interact();
			$variables[$variableName] = $variable->getValue();

			if ($isFirst === TRUE) {
				if (empty($variable->getValue())) {
					return FALSE;
				}
				$isFirst = FALSE;
			}
		}
		return $variables;
	}

	public function getVariableImplementation($variableType) {
		if (isset($this->variableImplementations[$variableType])) {
			return $this->variableImplementations[$variableType];
		}
		return $variableType;
	}

	public function previewFields($variables) {
		$headers = array();
		$values = array();
		foreach (current($variables) as $key => $value) {
			if (is_array($value)) {
				continue;
			}
			$headers[] = ucfirst($key);
		}
		foreach ($variables as $row) {
			$newRow = array();
			foreach ($row as $key => $value) {
				if (is_array($value)) {
					continue;
				}
				$newRow[] = $value;
			}
			$values[] = $newRow;
		}
		$this->table($values, $headers);
	}
}