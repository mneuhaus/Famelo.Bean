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
class ViewHelperTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicViewHelper() {
		$this->interaction->expects($this->any())
						  ->method('ask')
						  ->will($this->onConsecutiveCalls(
								'test.package',
								'viewhelper',
								'foo',
								'exit'
						  ));
		$this->controller->plantCommand();

		$expectedClassName = '\Test\Package\ViewHelpers\FooViewHelper';
		$this->assertClassExists($expectedClassName);
		$this->assertClassHasMethod($expectedClassName, 'render');
	}

	/**
	* @test
	*/
	public function createViewHelperInSubdirectory() {
		$this->interaction->expects($this->any())
						  ->method('ask')
						  ->will($this->onConsecutiveCalls(
								'test.package',
								'viewhelper',
								'bar/foo/guz',
								'exit'
						  ));
		$this->controller->plantCommand();

		$expectedClassName = '\Test\Package\ViewHelpers\Bar\Foo\GuzViewHelper';
		$this->assertClassExists($expectedClassName);
		$this->assertClassHasMethod($expectedClassName, 'render');
	}
}
