<?php
namespace Famelo\Bean\PhpParser\Reflection;

use PhpParser\Lexer;
use PhpParser\Parser;
use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
class ReflectionClass extends \Famelo\Bean\PhpParser\Wrapper {
	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

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
	}

	public function initialize() {
		if ($this->fileName === NULL) {
			$this->fileName = $this->reflectionService->getFilenameForClassName($this->className);
			if ($this->fileName === NULL) {
				$this->fileName = FLOW_PATH_DATA . '/Temporary/Testing/Package/Classes/' . str_replace('\\', '/', $this->className) . '.php';
			}
			$parser = new Parser(new Lexer);
			$this->statements = $parser->parse(file_get_contents($this->fileName));
			$this->properties = $this->getProperties();
			$this->methods = $this->getMethods();
		}
	}

	public function hasProperty($propertyName) {
		$this->initialize();
		return isset($this->properties[$propertyName]);
	}

	public function getProperty($propertyName) {
		$this->initialize();
		return $this->properties[$propertyName];
	}

	public function hasMethod($methodName) {
		$this->initialize();
		return isset($this->methods[$methodName]);
	}

	public function getMethod($methodName) {
		$this->initialize();
		return $this->methods[$methodName];
	}

	public function getFileName() {
		return $this->fileName;
	}
}
