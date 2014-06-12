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
class CrudTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicController() {
		$relationTargetClassName = '\Test\Package\Domain\Model\OneToMany\Target';

		$this->setAnswers(array(
			'test.package',	// Package

			'model/create',		// What to do
			'oneToMany/Target',	// modelName
			'',					// proceed to generate

			'model/create',	// What to do
			'basic',		// modelName

			'someString',	// propertyName
			'string',		// propertyType

			'someInteger',	// propertyName
			'integer',		// propertyType

			'someBoolean',	// propertyName
			'boolean',		// propertyType

			'someFloat',	// propertyName
			'float',		// propertyType

			'someDatetime',	// propertyName
			'datetime',		// propertyType

			'someOneToMany',			// propertyName
			'relation',					// propertyType
			'one to many',				// relationType
			$relationTargetClassName,	// targetclass
			'oneToManyTarget',			// mappedBy

			'',				// proceed to generate

			'test.package',
			'crud',
			'foo',
			'\Test\Package\Domain\Model\Basic',
			'someString',
			'exit'
		));
		$this->controller->plantCommand();

		$expectedControllerClassName = '\Test\Package\Controller\FooController';

		$this->assertClassExists($expectedControllerClassName);
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Index.html');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/New.html');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Edit.html');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Show.html');

		// $this->assertClassHasMethod($expectedControllerClassName, 'indexAction');
		// $this->assertClassHasMethod($expectedControllerClassName, 'fooBarAction');
		// $this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/FooBar.html');
		// $this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Index.html');

		$this->assertPolicyExists(
			$this->packagePath . '/Configuration/Policy.yaml',
			'Test_Package_FooController',
			'method(Test\Package\Controller\FooController->.*Action())'
		);
	}
}
