<?php
namespace Famelo\Bean\Variables;

use Famelo\Bean\Traits\InteractionTrait;
use TYPO3\Flow\Annotations as Flow;

/**
 */
abstract class AbstractVariable implements VariableInterface {
	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \Famelo\Bean\Service\InteractionService
	 */
	protected $interaction;

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

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $partial = 'Textfield';

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $prefix;

	public function injectInteraction($interaction) {
		$this->interaction = $interaction;
	}

	public function __construct($configuration, $previousVariables) {
		$this->configuration = $configuration;
		$this->previousVariables = $previousVariables;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function interact() {

	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}

	public function getName() {
		return $this->name;
	}

	public function getFormName() {
		if (stristr($this->getPropertyPath(), '.')) {
			$parts = explode('.', $this->getPropertyPath());
			$first = array_shift($parts);
			return $first . '[' . implode('][', $parts) . ']';
		}
		return $this->name;
	}

	public function getPropertyPath() {
		if (empty($this->prefix)) {
			return $this->name;
		}
		return $this->prefix . $this->name;
	}

	public function getPartial() {
		return $this->partial;
	}

	public function getLabel() {
		if (isset($this->configuration['label'])) {
			return $this->configuration['label'];
		}
	}

	public function setSource($source) {
		$this->source = $source;
	}

	public function chooseClassNameAnnotatedWith($question, $annotation) {
		$choices = array();
		$classNames = $this->reflectionService->getClassNamesByAnnotation($annotation);
		foreach ($classNames as $key => $className) {
			$choices[] = $className;
		}
		$index = FALSE;
		while ($index === FALSE) {
			$choice = ltrim($this->interaction->ask($question . chr(10),
				NULL,
				$choices,
				TRUE
			), '\\');
			$index = array_search($choice, $choices);
			if ($index === FALSE) {
				$this->interaction->outputLine('class not found: ' . $choice);
			}
		}
		return $classNames[$index];
	}
}