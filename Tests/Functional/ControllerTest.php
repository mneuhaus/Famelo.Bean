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
class ControllerTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicController() {
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'controllerName' => 'foo',
			'actions' => array(
				array(
					'actionName' => 'index'
				),
				array(
					'actionName' => 'fooBar'
				)
			)
		));
		$expectedControllerClassName = '\Test\Package\Controller\FooController';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['controller']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

		$this->assertClassExists($expectedControllerClassName);

		$this->assertClassHasMethod($expectedControllerClassName, 'indexAction');
		$this->assertClassHasMethod($expectedControllerClassName, 'fooBarAction');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/FooBar.html');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Index.html');

		$this->assertPolicyExists(
			$this->packagePath . '/Configuration/Policy.yaml',
			'Test_Package_FooController',
			'method(Test\Package\Controller\FooController->.*Action())'
		);
	}

	/**
	* @test
	*/
	public function createControllerInSubdirectory() {
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'controllerName' => 'bar/foo/guz',
			'actions' => array(
				array(
					'actionName' => 'index'
				),
				array(
					'actionName' => 'fooBar'
				)
			)
		));
		$expectedControllerClassName = '\Test\Package\Controller\Bar\Foo\GuzController';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['controller']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

		$this->assertClassExists($expectedControllerClassName);

		$this->assertClassHasMethod($expectedControllerClassName, 'indexAction');
		$this->assertClassHasMethod($expectedControllerClassName, 'fooBarAction');

		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Bar/Foo/Guz/FooBar.html');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Bar/Foo/Guz/Index.html');

		$this->assertPolicyExists(
			$this->packagePath . '/Configuration/Policy.yaml',
			'Test_Package_Bar_Foo_GuzController',
			'method(Test\Package\Controller\Bar\Foo\GuzController->.*Action())'
		);
	}
}
