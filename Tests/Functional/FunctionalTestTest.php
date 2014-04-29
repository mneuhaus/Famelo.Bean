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
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
class FunctionalTestTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicUnitTest() {
		$this->setAnswers(array(
			'test.package',
			'FunctionalTestCase',
			'foo',
			'exit'
		));
		$this->controller->plantCommand();

		$expectedClassName = '\Test\Package\Tests\Functional\FooTest';
		$expectedFileName = 'Tests/Functional/FooTest.php';
		$this->assertClassExists(
			$expectedClassName,
			$expectedFileName
		);
		$this->assertClassHasMethod($expectedClassName, 'someTest', $expectedFileName);
	}

	/**
	* @test
	*/
	public function createBasicUnitTestInSubdirectory() {
		$this->setAnswers(array(
			'test.package',
			'FunctionalTestCase',
			'foo/bar/guz',
			'exit'
		));
		$this->controller->plantCommand();

		$expectedClassName = '\Test\Package\Tests\Functional\Foo\Bar\GuzTest';
		$expectedFileName = 'Tests/Functional/Foo/Bar/GuzTest.php';
		$this->assertClassExists(
			$expectedClassName,
			$expectedFileName
		);
		$this->assertClassHasMethod($expectedClassName, 'someTest', $expectedFileName);
	}
}
