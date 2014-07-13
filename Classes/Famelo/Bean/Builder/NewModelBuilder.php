<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Util\Inflector;
use Famelo\Bean\Builder\Dto\Property;
use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Bean\PhpParser\Reflection\ReflectionClass;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\Template;
use PhpParser\printer\Standard;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ClassSchema;
use TYPO3\Flow\Utility\Files;

/**
 */
class NewModelBuilder extends PhpBuilder {

	/**
	 * @var string
	 */
	protected $classStatement;

	public function save($className, $variables = array()) {
		$fileName = $this->getFilename($className, $variables);
		$statements = $this->loadFile($fileName, $variables);
		$this->classStatement = $this->getClass($statements);

		foreach ($variables['properties'] as $propertyName => $property) {
			if (isset($property['propertyName'])) {
				$property['propertyName'] = lcfirst($property['propertyName']);
			}
			$property = new Property($propertyName, $property);

			if ($property->shouldBeRemoved()) {
				$this->removePropertyAndMethods($property);
				continue;
			}

			if ($this->hasProperty($property->getIdentifier())) {
				$this->removePropertyAndMethods($property);
			}

			$this->setProperty($this->classStatement, $property);
			$this->generateMappedBy($className, $property);
		}

		$code = $this->printCode($statements);

		file_put_contents($fileName, $code);
		$this->addFlashMessage('"' . str_replace(FLOW_PATH_ROOT, '', $fileName) . '"', 'saved file: ');
	}

	public function loadFile($fileName, $variables) {
		$properties = $variables['properties'];
		unset($variables['properties']);

		$parser = new Parser(new Lexer);

		if (file_exists($fileName)) {
			$content = file_get_contents($fileName);
		} else {
			$this->view->setTemplatePathAndFilename($this->configuration['template']);
			$this->view->assignMultiple($variables);
			$content = $this->view->render();
		}
		return $parser->parse($content);
	}

	public function getFilename($className, $variables) {
		$target = $this->configuration['target'];
		$fileName = $this->generateFileName($target, $variables);

		if (empty($className)) {
			return $fileName;
		}

		$oldFileName = $this->reflectionService->getFilenameForClassName($className);
		if ($fileName !== $oldFileName && !empty($oldFileName)) {
			$this->renameFile($oldFileName, $fileName, $variables);
		}

		return $fileName;
	}

	public function renameFile($oldFileName, $newFileName, $variables) {
		$this->view->setTemplatePathAndFilename($this->configuration['template']);
		$this->view->assignMultiple($variables);
		$parser = new Parser(new Lexer);
		$oldStatements = $parser->parse(file_get_contents($oldFileName));
		$newStatements = $parser->parse($this->view->render());

		$oldStatements[0]->name = $newStatements[0]->name;
		$oldClass = $this->getClass($oldStatements);
		$newClass = $this->getClass($newStatements);
		$oldClass->name = $newClass->name;
		$this->saveFile($newFileName, $oldStatements);
		unlink($oldFileName);
	}

	public function saveFile($fileName, $statements) {
		$code = $this->printCode($statements);
		if (!is_dir(dirname($fileName))) {
			Files::createDirectoryRecursively(dirname($fileName));
		}
		file_put_contents($fileName, $code);
	}

	public function removePropertyAndMethods($property) {
		if ($this->hasProperty($property->getIdentifier(), $this->classStatement)) {
			$this->removeProperty($property->getIdentifier(), $this->classStatement);
		}

		$methods = array(
			'get', 'set', 'add', 'remove'
		);
		foreach ($methods as $method) {
			$existingMethodName = $method . ucfirst($property->getIdentifier());
			if ($this->hasMethod($existingMethodName, $this->classStatement)) {
				$this->removeMethod($existingMethodName, $this->classStatement);
			}
		}
	}

	public function setProperty($classStatement, $property) {
		$methods = array('get', 'set', 'add', 'remove');
		$partial = 'Properties/Basic';

		$propertyNode = $this->getPartial($partial, array(
			'name' 	=> $property->getName(),
			'type' 	=> $property->getType(),
			'docComment' => $property->getDocComment()
		));
		$classStatement->stmts = array_merge($classStatement->stmts, $propertyNode);

		foreach ($methods as $method) {
			$methodName = $method . ucfirst($property->getName());

			if ($method == 'add' || $method == 'remove') {
				if (stristr($property->getType(), '<') !== FALSE) {
					$method = $method . 'Collection';
				} else if(substr($property->getType(), 0, 5) === 'array') {
					$method = $method . 'Array';
				} else {
					continue;
				}

				$methodNode = $this->getPartial('Methods/' . ucfirst($method), array(
					'name' 	=> $property->getName(),
					'singular' => $property->getSingularName(),
					'type' 	=> $property->getElementType()
				));
			} else {
				$methodNode = $this->getPartial('Methods/' . ucfirst($method), array(
					'name' 	=> $property->getName(),
					'type' 	=> $property->getType()
				));
			}

			$classStatement->stmts = array_merge($classStatement->stmts, $methodNode);
		}
	}

	public function generateMappedBy($className, $property) {
		if (!$property->isRelation()) {
			return;
		}

		$targetProperty = $property->getTargetProperty();
		$targetClassName = $property->getElementType();

		$reflection = new ReflectionClass($targetClassName);
		if ($reflection->hasProperty($targetProperty) === FALSE) {
			$this->addPropertiesToClass(
				$reflection->getFileName(),
				array($property->generateRelationReverse($className))
			);
		}
	}

	public function addPropertiesToClass($fileName, $properties) {
		$template = file_get_contents($fileName);
		$template = new Template($this->parser, $template);
		$statements = $template->getStmts(array());
		$classStatement = $this->getClass($statements);

		foreach ($properties as $propertyName => $property) {
			$property = new Property($propertyName, $property);
			$this->setProperty($classStatement, $property);
		}

		$code = $this->printCode($statements);
		file_put_contents($fileName, $code);
		$this->addFlashMessage('"' . str_replace(FLOW_PATH_ROOT, '', $fileName) . '"', 'saved file: ');
	}
}