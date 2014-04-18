<?php
namespace Famelo\Bean\Fluid;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface;
use TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface;

/**
 * Template parser building up an object syntax tree
 *
 */
class TemplateParser extends \TYPO3\Fluid\Core\Parser\TemplateParser {

	/**
	 * adds a namespace
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return void
	 */
	public function addNamespace($name, $namespace) {
		$this->namespaces[$name] = $namespace;
	}

	/**
	 * Resets the parser to its default values.
	 *
	 * @return void
	 */
	protected function reset() {
		$this->namespaces = array(
			'f' => 'TYPO3\Fluid\ViewHelpers',
			'k' => 'TYPO3\Kickstart\ViewHelpers',
			'b' => 'Famelo\Bean\ViewHelpers'
		);
	}
}
