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
use TYPO3\Flow\Reflection\ReflectionService;
use TYPO3\Flow\Utility\Files;

/**
 */
class Property {
	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string
	 */
	protected $className;

	public function __construct($identifier, $configuration, $className) {
		$this->identifier = $identifier;
		$this->configuration = $configuration;
		$this->className = $className;
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
		$comment = array(
			'/**',
			'     * @var ' . $this->getType(),
			'     */'
		);
		if (class_exists($this->className)) {
			$classReflection = new \ReflectionClass($this->className);
            if ($classReflection->hasProperty($this->identifier)) {
                $propertyReflection = $classReflection->getProperty($this->identifier);
                $comment = explode(chr(10), $propertyReflection->getDocComment());
                foreach ($comment as $line => $text) {
                    if (substr(ltrim($text, ' *'), 0, 4) == '@var') {
                        $comment[$line] = '     * @var ' . $this->getType();
                    }
                }
            }
		}

		if ($this->configuration['propertyType']['type'] !== 'relation') {
			return implode(chr(10), $comment);
		}

		$type = $this->configuration['propertyType']['relation'];
		$ormComment = '@ORM\\' . ucfirst($type);

		switch ($type) {
			case 'oneToOne':
			case 'oneToMany':
				$ormComment.= '(mappedBy="' . $this->getTargetProperty() . '")';
				break;

			case 'manyToOne':
			case 'manyToMany':
				$ormComment.= '(inversedBy="' . $this->getTargetProperty() . '")';
				break;
		}


		$ormCommentAdded = FALSE;
		foreach ($comment as $line => $text) {
			if (substr(ltrim($text, ' *'), 0, 4) == '@ORM') {
				$comment[$line] = '     * ' . $ormComment;
				$ormCommentAdded = TRUE;
				break;
			}
		}

		if ($ormCommentAdded === FALSE) {
			$newCommment = array();
			foreach ($comment as $line => $text) {
				$newCommment[] = $text;
				if (substr(ltrim($text, ' *'), 0, 4) == '@var') {
					$newCommment[] = '     * ' . $ormComment;
				}
			}
			$comment = $newCommment;
		}

		return implode(chr(10), $comment);
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