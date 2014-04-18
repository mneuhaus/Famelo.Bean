<?php
namespace Famelo\Bean\Tests\Functional;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Famelo\Bean\Bean\DefaultBean;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
abstract class BaseTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	*/
	public function setUp() {
		parent::setUp();
		$this->configurationManager	= $this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->reflectionService	= $this->objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
		$this->packagePath = FLOW_PATH_DATA . '/Temporary/Testing/Package';
		$this->reset();
	}

	/**
	 */
	public function reset() {
		$this->modelName = NULL;
		$this->packageName = NULL;
		$this->properties = NULL;
		if (is_dir($this->packagePath)) {
			Files::emptyDirectoryRecursively($this->packagePath);
		}
	}

	public function getBaseVariables($packageName) {
		$variables = array(
			'packageKey' => $packageName,
			'namespace' => str_replace('.', '\\', $packageName),
			'packagePath' => $this->packagePath,
			'classesPath' => $this->packagePath . '/Classes/' . str_replace('.', '/', $packageName),
			'resourcesPath' => $this->packagePath . '/Resources/',
			'configurationPath' => $this->packagePath . '/Configuration/',
			'documentationPath' => $this->packagePath . '/Documentation/'
		);
		return $variables;
	}

	public function assertClassExists($className) {
		$classPath = '/Classes/' . str_replace('\\', '/', $className) . '.php';
		$this->assertFileExists($this->packagePath . $classPath);

		require_once($this->packagePath . $classPath);
		$this->assertTrue(class_exists($className), '"' . $className . '" does not exists');
	}

	public function assertClassHasProperty($className, $propertyName, $propertyType) {
		$reflection = new \ReflectionClass($className);
		$this->assertTrue($reflection->hasProperty($propertyName),
			'"' . $className . '::$' . $propertyName . '" not found'
		);

		$property = $reflection->getProperty($propertyName);
		$this->assertTrue(stristr($property->getDocComment(), '@var ' . $propertyType) !== FALSE,
			'"' . $className . '::$' . $propertyName . '" not of type "' . $propertyType . '"'
		);
	}

	public function assertClassHasMethod($className, $methodName) {
		$reflection = new \ReflectionClass($className);
		$this->assertTrue($reflection->hasMethod($methodName),
			'"' . $className . '::$' . $methodName . '()" not found'
		);
	}

	public function assertPolicyExists($policyFile, $policyName, $policyDefinition) {
		$this->assertFileExists($policyFile);
		$policy = Yaml::parse(file_get_contents($policyFile));

		$this->assertTrue(isset($policy['resources']['methods'][$policyName]), 'Policy "' . $policyName . '" does not exist');

		$this->assertEquals($policy['resources']['methods'][$policyName], $policyDefinition);
	}

	public function assertNodeTypeExists($nodeTypesFile, $nodeTypeName) {
		$this->assertFileExists($nodeTypesFile);
		$nodeTypes = Yaml::parse(file_get_contents($nodeTypesFile));

		$this->assertTrue(isset($nodeTypes[$nodeTypeName]),
			'NodeType "' . $nodeTypeName . '" does not exist');
	}

	public function assertPrototypeExists($typoScriptFile, $prototypeName) {
		$this->assertFileExists($typoScriptFile);
		$typoScript = file_get_contents($typoScriptFile);

		$this->assertTrue(stristr($typoScript, 'prototype('. $prototypeName . ')') !== FALSE,
			'Prototype "' . $prototypeName . '" does not exist');
	}
}
