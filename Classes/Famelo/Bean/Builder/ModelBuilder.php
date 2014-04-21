<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Util\Inflector;
use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\Template;
use PhpParser\printer\Standard;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class ModelBuilder extends PhpBuilder {

	public function plant($variables = array()) {
		$this->variables = $variables;
		$source = $this->configuration['template'];
		$target = $this->configuration['target'];

		$properties = $variables['properties'];
		unset($variables['properties']);

		$target = $this->generateFileName($target, $variables);

		$this->view->setTemplatePathAndFilename($source);
		$this->view->assignMultiple($variables);
		$parser = new Parser(new Lexer);
		$statements = $parser->parse($this->view->render());

		foreach ($properties as $property) {
			$this->addProperty($statements, $property);
			$this->generateMappedBy($this->getClassName($statements), $property);
		}

		$code = $this->printCode($statements);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}
		if (!file_exists($target)) {
			file_put_contents($target, $code);
			require_once($target);

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
		$propertySubType = isset($property['propertySubtype']) ? $property['propertySubtype'] : NULL;

		$partial = 'Properties/Basic';
		$propertyNode = $this->getPartial($partial, array(
			'name' 	=> $propertyName,
			'type' 	=> $propertyType,
			'docComment' => isset($property['relation']) ? $this->generateDocComment($property['relation']) : ''
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

	public function generateDocComment($relation) {
		$docComment = '@ORM\\' . $relation['type'];
		unset($relation['type']);
		if (count($relation) > 0) {
			$arguments = array();
			foreach ($relation as $key => $value) {
				$arguments[] = $key . '="' . $value . '"';
			}
			$docComment.= '(' . implode(', ', $arguments) . ')';
		}
		return $docComment;
	}

	public function generateMappedBy($className, $property) {
		if (!isset($property['relation'])) {
			return;
		}
		$relation = $property['relation'];

		switch ($relation['type']) {
			case 'OneToMany':
				$reflection = new \ReflectionClass($property['subtype']);
				if ($reflection->hasProperty($relation['mappedBy']) === FALSE) {
					$this->addPropertiesToClass($reflection->getFileName(), array(
						array(
							'propertyName' => $relation['mappedBy'],
							'propertyType' => $className,
							'relation' => array(
								'type' => 'ManyToOne',
								'inversedBy' => $property['propertyName']
							)
						)
					));
				}
				break;
			case 'ManyToOne':
				$reflection = new \ReflectionClass($property['propertyType']);
				if ($reflection->hasProperty($relation['inversedBy']) === FALSE) {
					$this->addPropertiesToClass($reflection->getFileName(), array(
						array(
							'propertyName' => $relation['inversedBy'],
							'propertyType' => '\Doctrine\Common\Collections\Collection<' . $className . '>',
							'subtype' => $className,
							'relation' => array(
								'type' => 'OneToMany',
								'mappedBy' => $property['propertyName']
							)
						)
					));
				}
				break;
			case 'OneToOne':
				$reflection = new \ReflectionClass($property['propertyType']);
				if ($reflection->hasProperty($relation['mappedBy']) === FALSE) {
					$this->addPropertiesToClass($reflection->getFileName(), array(
						array(
							'propertyName' => $relation['mappedBy'],
							'propertyType' => $className,
							'relation' => array(
								'type' => 'OneToOne',
								'mappedBy' => $property['propertyName']
							)
						)
					));
				}
				break;
			case 'ManyToMany':
				$reflection = new \ReflectionClass($property['subtype']);
				if ($reflection->hasProperty($relation['inversedBy']) === FALSE) {
					$this->addPropertiesToClass($reflection->getFileName(), array(
						array(
							'propertyName' => $relation['inversedBy'],
							'propertyType' => '\Doctrine\Common\Collections\Collection<' . $className . '>',
							'subtype' => $className,
							'relation' => array(
								'type' => 'ManyToMany',
								'inversedBy' => $property['propertyName']
							)
						)
					));
				}
				break;
		}
	}

	public function addPropertiesToClass($classPath, $properties) {
		$template = file_get_contents($classPath);
		$template = new Template($this->parser, $template);
		$node = $template->getStmts(array());

		foreach ($properties as $property) {
			$this->addProperty($node, $property);
		}

		$code = $this->printCode($node);
		file_put_contents($classPath, $code);
	}
}