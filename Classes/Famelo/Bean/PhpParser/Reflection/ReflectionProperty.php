<?php
namespace Famelo\Bean\PhpParser\Reflection;

/**
 *
 */
class ReflectionProperty {
	/**
	 * @var \PhpParser\Node\Stmt
	 */
	protected $statement;
	public function __construct($statement) {
		$this->statement = $statement;
	}

	public function getName() {
		$propertyProperty = $this->statement->props[0];
		return $propertyProperty->name;
	}

	public function getDocComment() {
		return $this->statement->getDocComment();
	}
}
