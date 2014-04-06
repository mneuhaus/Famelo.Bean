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
class YamlBuilder extends FluidBuilder {
	public function append($source, $target, $variables = array()) {
		// var_dump($source, $tar)
	}
}