<?php
namespace Famelo\Bean\Variables;

use TYPO3\Flow\Annotations as Flow;

/**
 */
class RepeaterVariable extends AbstractVariable {
	/**
	 * @var string
	 */
	protected $partial = 'Repeater';

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
			$variable->injectInteraction($this->interaction);
			$variable->interact();
			$variables[$variableName] = $variable->getValue();

			$value = $variable->getValue();
			if ($isFirst === TRUE) {
				if (empty($value)) {
					return FALSE;
				}
				$isFirst = FALSE;
			}
		}
		return $variables;
	}

	public function getRows() {
		$rows = array();
		$values = is_array($this->value) ? $this->value : array();
		foreach ($values as $value) {
			$rows[] = array(
				'formName' => $this->getFormName() . '[' . $value['propertyName'] . ']',
				'variables' => $this->getVariables($value, $this->getPropertyPath() . '.' . $value['propertyName'] . '.')
			);
		}
		return $rows;
	}

	public function getVariables($value, $prefix) {
		$variables = array();
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			$variableType = isset($variable['type']) ? $variable['type'] : 'ask';
			$variableImplementation = $this->getVariableImplementation($variableType);
			$variable = new $variableImplementation($variable, array());
			$variable->setName($variableName);
			$variable->setPrefix($prefix);
			if (isset($value[$variableName])){
				$variable->setValue($value[$variableName]);
			}
			$variables[$variableName] = $variable;
		}
		return $variables;
	}

	public function getTemplate() {
		return array('variables' => $this->getVariables(array(), $this->getPropertyPath() . '.--template--.'));
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
		$this->interaction->table($values, $headers);
	}
}
