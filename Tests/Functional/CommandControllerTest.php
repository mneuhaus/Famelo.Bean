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
class CommandControllerTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicController() {
		$this->setAnswers(array(
			'test.package',
			'commandController',
			'foo',
			'index',
			'fooBar',
			'',
			'exit'
		));
		$this->controller->plantCommand();

		$expectedControllerClassName = '\Test\Package\Command\FooCommandController';

		$this->assertClassExists($expectedControllerClassName);

		$this->assertClassHasMethod($expectedControllerClassName, 'indexCommand');
		$this->assertClassHasMethod($expectedControllerClassName, 'fooBarCommand');
	}

	/**
	* @test
	*/
	public function createControllerInSubdirectory() {
		$this->setAnswers(array(
			'test.package',
			'commandController',
			'bar/foo/guz',
			'index',
			'fooBar',
			'',
			'exit'
		));
		$this->controller->plantCommand();

		$expectedControllerClassName = '\Test\Package\Command\Bar\Foo\GuzCommandController';
		$this->assertClassExists($expectedControllerClassName);

		$this->assertClassHasMethod($expectedControllerClassName, 'indexCommand');
		$this->assertClassHasMethod($expectedControllerClassName, 'fooBarCommand');
	}
}
