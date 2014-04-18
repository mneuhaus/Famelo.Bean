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
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'viewHelperName' => 'foo'
		));
		$expectedClassName = '\Test\Package\ViewHelpers\FooViewHelper';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['viewhelper']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

		$this->assertClassExists($expectedClassName);
		$this->assertClassHasMethod($expectedClassName, 'render');
	}

	/**
	* @test
	*/
	public function createViewHelperInSubdirectory() {
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'viewHelperName' => 'bar/foo/guz'
		));
		$expectedClassName = '\Test\Package\ViewHelpers\Bar\Foo\GuzViewHelper';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['viewhelper']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

		$this->assertClassExists($expectedClassName);
		$this->assertClassHasMethod($expectedClassName, 'render');
	}
}
