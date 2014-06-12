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

	/**
	 * @var \TYPO3\Flow\Package\PackageManager
	 */
	protected $packageManager;

	/**
	* @param \TYPO3\Flow\Package\PackageManagerInterface $packageManager
	* @return void
	*/
	public function injectPackageManager(\TYPO3\Flow\Package\PackageManagerInterface $packageManager) {
		$this->packageManager =  $packageManager;
	}

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

	/**
	 * @var array
	 */
	protected $fileNamesByClassName = array();

	public function getFilenameForClassName($className) {
		if (empty($this->fileNamesByClassName)) {
			foreach ($this->packageManager->getAvailablePackages() as $package) {
				foreach ($package->getClassFiles() as $fileClassName => $fileName) {
					$this->fileNamesByClassName[$fileClassName] = $package->getPackagePath() . '' . $fileName;
				}
			}
		}
		$className = ltrim($className, '\\');
		if (isset($this->fileNamesByClassName[$className])) {
			return $this->fileNamesByClassName[$className];
		}
	}

	public function addFilenameForClassName($className, $fileName) {
		$className = ltrim($className, '\\');
		$this->getFilenameForClassName($className);
		$this->fileNamesByClassName[$className] = $fileName;
	}

	/**
	 * @var array
	 */
	protected $runtimeClassSchemata = array();

	public function addClassSchema($classSchema) {
		$className = ltrim($classSchema->getClassName(), '\\');
		$this->runtimeClassSchemata[$className] = $classSchema;
	}

	/**
	 * Returns the class schema for the given class
	 *
	 * @param mixed $className The class name or an object
	 * @return \TYPO3\Flow\Reflection\ClassSchema
	 */
	public function getClassSchema($className) {
		$className = ltrim($className, '\\');
		if (isset($this->runtimeClassSchemata[$className])) {
			return $this->runtimeClassSchemata[$className];
		}

		return $this->reflectionService->getClassSchema($className);
	}
}
