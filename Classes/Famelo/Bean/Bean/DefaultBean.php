<?php

namespace Famelo\Bean\Bean;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Common\Command\AbstractInteractiveCommandController;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\Package;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class DefaultBean extends AbstractBean {
	/**
	 * @var array
	 * @Flow\Inject(setting="Variables")
	 */
	protected $variableImplementations;

	/**
	 * @var boolean
	 */
	protected $silent = FALSE;

	public function setSilent($silent) {
		$this->silent = $silent;
	}

    public function plant() {
        $this->fetchVariables();
        $this->build($this->variables);
    }

    public function build($variables) {
        foreach ($this->configuration['files'] as $file) {
			$builderClassName = '\Famelo\Bean\Builder\FluidBuilder';
			if (isset($file['builder'])) {
				$builderClassName = $file['builder'];
			}
			$builder = new $builderClassName($file);
			$builder->injectInteraction($this->interaction);
			if (isset($file['mode'])) {
				$changes = call_user_func(array($builder, $file['mode']), $variables);
			} else {
            	$changes = $builder->plant($variables);
			}
			if ($this->silent === FALSE && is_array($changes)) {
				foreach ($changes as $change) {
					$this->interaction->outputLine($change);
				}
			}
        }
    }

	public function fetchVariables() {
    	$this->initialize();
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			$variableType = isset($variable['type']) ? $variable['type'] : 'ask';
			$variableImplementation = $this->getVariableImplementation($variableType);
			$variable = new $variableImplementation($variable, $this->variables);
			$variable->injectInteraction($this->interaction);
			$variable->interact();
			$this->variables[$variableName] = $variable->getValue();
		}
	}

	public function getVariableImplementation($variableType) {
		if (isset($this->variableImplementations[$variableType])) {
			return $this->variableImplementations[$variableType];
		}
		return $variableType;
	}
}