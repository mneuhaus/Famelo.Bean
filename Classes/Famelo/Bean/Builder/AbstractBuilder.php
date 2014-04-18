<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Bean\Traits\InteractionTrait;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Template;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class AbstractBuilder {
	use InteractionTrait;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var \Famelo\Bean\Fluid\KickstartView
	 * @Flow\Inject
	 */
	protected $view;

	public function __construct($configuration) {
		$this->configuration = $configuration;
	}

	public function generateFileName($target, $variables) {
		$this->view->assignMultiple($variables);
		return $this->view->renderString($target);
	}
}