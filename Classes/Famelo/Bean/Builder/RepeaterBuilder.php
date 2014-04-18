<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\printer\Standard;
use PhpParser\Template;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class RepeaterBuilder extends AbstractBuilder {
	/**
	 * @var \TYPO3\Fluid\View\StandaloneView
	 * @Flow\Inject
	 */
	protected $view;

	public function plant($variables = array()) {
		$repetitions = $variables[$this->configuration['variable']];
		$changes = array();
		foreach ($repetitions as $repetition) {
			$repetition = array_merge($variables, $repetition);
			foreach ($this->configuration['files'] as $file) {
				$builderClassName = '\Famelo\Bean\Builder\FluidBuilder';
				if (isset($file['builder'])) {
					$builderClassName = $file['builder'];
				}
				$builder = new $builderClassName($file);
				if (isset($file['mode'])) {
					$changes = array_merge($changes, call_user_method($file['mode'], $builder, $repetition));
				} else {
	            	$changes = array_merge($changes, $builder->plant($repetition));
				}
	        }
		}
		return $changes;
	}
}