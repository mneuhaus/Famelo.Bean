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
class ModelBuilder extends PhpBuilder {

	public function plant($variables = array()) {
		$source = $this->configuration['template'];
		$target = $this->configuration['target'];

		$properties = $variables['properties'];
		unset($variables['properties']);

		$target = $this->generateFileName($target, $variables);

		$template = file_get_contents($source);
		$template = new Template($this->parser, $template);
		$node = $template->getStmts($variables);

		foreach ($properties as $property) {
			$this->addProperty($node, $property);
		}

		$code = $this->printCode($node);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}
		if (!file_exists($target)) {
			file_put_contents($target, $code);

			return array('<info>Created: ' . $target . '</info>');
		}
	}

	public function addProperty($stmts, $property) {
		$stmt = $this->getClass($stmts);
		$properties = $this->getClassProperties($stmt);
		$classMethods = $this->getClassMethods($stmt);

		$methods = array(
			'get', 'set', 'add', 'remove'
		);

		$propertyName = $property['propertyName'];
		$propertyType = $property['propertyType'];
		$docComment = '';
		$propertySubType = isset($property['propertySubtype']) ? $property['propertySubtype'] : NULL;

		$partial = 'Properties/Basic';
		$propertyNode = $this->getPartial($partial, array(
			'name' 	=> $propertyName,
			'type' 	=> $propertyType,
			'docComment' => $docComment
		));
		$stmt->stmts = array_merge($stmt->stmts, $propertyNode);

		foreach ($methods as $method) {
			$methodName = $method . ucfirst($propertyName);

			if (isset($classMethods[$methodName])) {
				continue;
			}

			if ($method == 'add' || $method == 'remove') {
				if (stristr($propertyType, '<') !== FALSE) {
					$method = $method . 'Collection';
				} else if(substr($propertyType, 0, 5) === 'array') {
					$method = $method . 'Array';
				} else {
					continue;
				}

				$singularPropertName = Inflector::singularize($propertyName);

				$methodNode = $this->getPartial('Methods/' . ucfirst($method), array(
					'name' 	=> $propertyName,
					'singular' => $singularPropertName,
					'type' 	=> $propertySubType
				));
				$stmt->stmts = array_merge($stmt->stmts, $methodNode);
			} else {
				$methodNode = $this->getPartial('Methods/' . ucfirst($method), array(
					'name' 	=> $propertyName,
					'type' 	=> $propertyType
				));
				$stmt->stmts = array_merge($stmt->stmts, $methodNode);
			}
		}
	}
}