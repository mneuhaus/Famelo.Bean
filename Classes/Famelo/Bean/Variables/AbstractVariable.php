<?php
namespace Famelo\Bean\Variables;

use Famelo\Bean\Traits\InteractionTrait;
use TYPO3\Flow\Annotations as Flow;

/**
 */
abstract class AbstractVariable implements VariableInterface {
	use InteractionTrait;

	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var array
	 */
	protected $previousVariables;

	/**
	 * @var mixed
	 */
	protected $value;

	public function __construct($configuration, $previousVariables) {
		$this->configuration = $configuration;
		$this->previousVariables = $previousVariables;
	}

	public function getValue() {
		return $this->value;
	}

	public function interact() {

	}

	public function chooseClassNameAnnotatedWith($question, $annotation) {
		$choices = array();
		$classNames = $this->reflectionService->getClassNamesByAnnotation($annotation);
		foreach ($classNames as $key => $className) {
			$choices[] = $className;
		}
		$choice = $this->ask($question . chr(10),
			NULL,
			$choices,
			TRUE
		);
		return $classNames[array_search($choice, $choices)];
	}
}