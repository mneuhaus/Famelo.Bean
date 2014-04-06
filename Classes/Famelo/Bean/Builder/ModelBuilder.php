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

	public function buildNew($source, $target, $variables = array()) {
		$target = $this->generateFileName($target, $variables);

		$template = file_get_contents($source);
		$template = new Template($this->parser, $template);
		$node = $template->getStmts($variables);

		$fields = array();
		while (($field = $this->createField()) !== FALSE) {
			$fields[] = $field;
			$this->previewFields($fields);
		}

		foreach ($fields as $field) {
			$this->addField($node, $field);
		}

		$code = $this->printCode($node);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}
		if (!file_exists($target)) {
			$this->outputLine('<info>Created: ' . $target . '</info>');
			// echo $content;
			file_put_contents($target, $code);
		}
	}

	public function addField($stmts, $field) {
		$stmt = $this->getClass($stmts);
		$properties = $this->getClassProperties($stmt);
		$classMethods = $this->getClassMethods($stmt);

		$methods = array(
			'get', 'set', 'add', 'remove'
		);

		$propertyName = $field['name'];
		$propertyType = $field['type'];
		$propertySubType = isset($field['subtype']) ? $field['subtype'] : NULL;

		$partial = 'Properties/Basic';
		$propertyNode = $this->getPartial($partial, array(
			'name' 	=> $propertyName,
			'type' 	=> $propertyType,
			'docComment' => $field['docComment']
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

	public function createField() {
		$name = $this->ask('<q>Property name (leave empty to skip):</q> ');
		if (empty($name)) {
			return FALSE;
		}
		$type = $this->chooseFieldType();

		$field = array(
			'name' => $name,
			'type' => $type,
			'docComment' => NULL
		);

		switch ($type) {
			case 'relation':
				$field = $this->createRelationField($field);
				break;
		}

		$this->outputLine();
		return $field;
	}

	public function createRelationField($field) {
		$relations = array('one to many', 'many to one', 'one to one', 'many to many');
		$type = $this->ask(
			'<q>What type of relation (' . implode(', ', $relations) . ':</q> ' . chr(10),
			NULL,
			$relations,
			TRUE
		);

		$className = $this->chooseClassNameAnnotatedWith(
			'<q>What is the target entity for this relation?</q>',
			'\TYPO3\Flow\Annotations\Entity'
		);

		$field['relationType'] = $type;
		$field['docComments'] = array();

		switch ($type) {
			case 'one to one':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$field['type'] = '\\' . $className;
				$field['docComment'] = '@ORM\OneToOne(mappedBy="' . $mappedBy . '")';
				break;

			case 'many to one':
				$mappedBy = $this->ask(
					'<q>inversed by:</q> ' . chr(10)
				);
				$field['type'] = '\\' . $className;
				$field['docComment'] = '@ORM\ManyToOne(inversedBy="' . $mappedBy . '")';

				break;

			case 'one to many':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$field['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$field['docComment'] = '@ORM\OneToMany(mappedBy="' . $mappedBy . '")';
				$field['subtype'] = '\\' . $className;
				break;

			case 'many to many':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$field['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$field['docComment'] = '@ORM\ManyToMany(inversedBy="' . $mappedBy . '")';
				$field['subtype'] = '\\' . $className;
				break;
		}
		return $field;
	}

	public function chooseFieldType() {
		$types = array(
			'string' => 'string',
			'integer' => 'integer',
			'float' => 'float',
			'boolean' => 'boolean',
			'datetime' => '\DateTime',
			'done' => 'done',
			'relation' => 'relation'
		);
		$choice = $this->ask(
			'<q>Property Type (' . implode(',', array_keys($types)) . '):</q> ' . chr(10),
			NULL,
			array_keys($types)
		);
		return $types[$choice];
	}

	public function previewFields($fields) {
		$rows = array();
		foreach ($fields as $field) {
			$rows[] = array(
				$field['name'],
				$field['type']
			);
		}
		$this->table($rows, array('Name', 'Type'));
	}
}