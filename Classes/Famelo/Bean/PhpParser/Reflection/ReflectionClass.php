<?php
namespace Famelo\Bean\PhpParser\Reflection;

use PhpParser\Lexer;
use PhpParser\Parser;

/**
 *
 */
class ReflectionClass extends \Famelo\Bean\PhpParser\Wrapper {
	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var array
	 */
	protected $statements;

	/**
	 * @var array
	 */
	protected $properties;

	/**
	 * @var array
	 */
	protected $methods;

	public function __construct($className) {
		$reflection = new \ReflectionClass($className);
		$this->className = $className;
		$this->fileName = $reflection->getFileName();

		$parser = new Parser(new Lexer);
		$this->statements = $parser->parse(file_get_contents($reflection->getFileName()));
		$this->properties = $this->getProperties();
		$this->methods = $this->getMethods();
	}

	public function hasProperty($propertyName) {
		return isset($this->properties[$propertyName]);
	}

	public function getProperty($propertyName) {
		return $this->properties[$propertyName];
	}

	public function hasMethod($methodName) {
		return isset($this->methods[$methodName]);
	}

	public function getMethod($methodName) {
		return $this->methods[$methodName];
	}
}
