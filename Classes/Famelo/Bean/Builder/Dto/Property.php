<?php
namespace Famelo\Bean\Builder\Dto;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Util\Inflector;
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
class Property {
	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $identifier;

	public function __construct($identifier, $configuration) {
		$this->identifier = $identifier;
		$this->configuration = $configuration;
	}

	public function shouldBeRemoved() {
		return isset($this->configuration['removed']) && $this->configuration['removed'] == 1;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function getName() {
		if (isset($this->configuration['propertyName'])) {
			return $this->configuration['propertyName'];
		}
	}

	public function getSingularName() {
		return Inflector::singularize($this->getName());
	}

	public function getPluralName() {
		return Inflector::pluralize($this->getName());
	}

	public function getElementType() {
		return '\\' . ltrim($this->configuration['propertyType']['elementType'], '\\');
	}

	public function isRelation() {
		return $this->configuration['propertyType']['type'] == 'relation';
	}

	public function getRelationType() {
		return $this->configuration['propertyType']['relation'];
	}

	public function getType() {
		if ($this->configuration['propertyType']['type'] == 'relation') {
			switch ($this->configuration['propertyType']['relation']) {
				case 'oneToOne':
				case 'manyToOne':
					return $this->getElementType();

				case 'oneToMany':
				case 'manyToMany':
					return '\Doctrine\Common\Collections\Collection<' . $this->getElementType() . '>';
			}
		}
		return $this->configuration['propertyType']['type'];
	}

	public function getTargetProperty() {
		return $this->configuration['propertyType']['targetProperty'];
	}

	public function getDocComment() {
		if ($this->configuration['propertyType']['type'] !== 'relation') {
			return '';
		}

		$type = $this->configuration['propertyType']['relation'];
		$docComment = '@ORM\\' . ucfirst($type);

		switch ($type) {
			case 'oneToOne':
			case 'oneToMany':
				$docComment.= '(mappedBy="' . $this->getTargetProperty() . '")';
				break;

			case 'manyToOne':
			case 'manyToMany':
				$docComment.= '(inversedBy="' . $this->getTargetProperty() . '")';
				break;
		}

		return $docComment;
	}

	public function generateRelationReverse($className) {
		$reverseRelation = array(
			'oneToOne' => 'oneToOne',
			'manyToOne' => 'oneToMany',
			'oneToMany' => 'manyToOne',
			'manyToMany' => 'manyToMany',

		);
		return array(
			'propertyName' => $this->getTargetProperty(),
			'propertyType' => array(
				'type' => 'relation',
				'relation' => $reverseRelation[$this->getRelationType()],
				'elementType' => $className,
				'targetProperty' => $this->getName()
			)
		);
	}
}