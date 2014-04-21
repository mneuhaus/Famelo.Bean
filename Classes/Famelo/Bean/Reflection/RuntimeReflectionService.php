<?php
namespace Famelo\Bean\Reflection;

use TYPO3\Flow\Annotations as Flow;

/**
 *
 * @Flow\Scope("singleton")
 */
class RuntimeReflectionService {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	public function __call($name, $arguments) {
		return call_user_method_array($name, $this->reflectionService, $arguments);
	}

	/**
	 * @var array
	 */
	protected $classNamesByAnnotation = array();

	public function getClassNamesByAnnotation($annotation) {
		if (isset($this->classNamesByAnnotation[$annotation]) == FALSE) {
			$this->classNamesByAnnotation[$annotation] = $this->reflectionService->getClassNamesByAnnotation($annotation);
		}
		return $this->classNamesByAnnotation[$annotation];
	}

	public function addClassNameForAnnotation($annotation, $className) {
		$this->getClassNamesByAnnotation($annotation);
		$this->classNamesByAnnotation[$annotation][] = $className;
	}
}
