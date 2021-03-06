<?php
namespace Famelo\Bean\Tests\Functional\Neos;

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
use Famelo\Bean\Tests\Functional\BaseTest;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
class PluginTest extends BaseTest {
	/**
	* @test
	*/
	public function createPlugin() {
		$this->interaction->expects($this->any())
						  ->method('ask')
						  ->will($this->onConsecutiveCalls(
								'test.package', // Package
								'neos/plugin',  // What to do
								'Foo Plugin',   // pluginLabel
								'foo',			// pluginName
								'foo',			// controllerName
								'index',		// actionName
								'',				// proceed to generate
								'exit'			// exit command
						  ));
		$this->controller->plantCommand();

		$expectedControllerClassName = '\Test\Package\Controller\FooController';
		$this->assertClassExists($expectedControllerClassName);
		$this->assertClassHasMethod($expectedControllerClassName, 'indexAction');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Foo/Index.html');

		$this->assertPolicyExists(
			$this->packagePath . '/Configuration/Policy.yaml',
			'Test_Package_FooPlugin',
			'method(Test\Package\Controller\FooController->.*Action())'
		);

		$this->assertNodeTypeExists(
			$this->packagePath . '/Configuration/NodeTypes.yaml',
			'Test.Package:Foo',
			'method(Test\Package\Controller\FooController->.*Action())'
		);

		$this->assertPrototypeExists(
			$this->packagePath . '/Resources/Private/TypoScript/Root.ts2',
			'Test.Package:Foo'
		);
	}
}
