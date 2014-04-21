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
use TYPO3\Flow\Annotations as Flow;

/**
 */
class RuntimeReflectionServiceTest extends \TYPO3\Flow\Tests\FunctionalTestCase {
	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	*/
	public function setUp() {
		parent::setUp();
		$this->reflectionService	= $this->objectManager->get('Famelo\Bean\Reflection\RuntimeReflectionService');
	}

	/**
	* @test
	*/
	public function addAnnotatedClassDuringRuntime() {
		$annotation = '\TYPO3\Flow\Annotations\Entity';
		$className = '\Foo\Bar\Domain\Model\Baz';
		$this->reflectionService->addClassNameForAnnotation($annotation, $className);
		$classNames = $this->reflectionService->getClassNamesByAnnotation($annotation);
		$this->assertTrue(in_array($className, $classNames));
	}

}
