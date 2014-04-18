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
class ModelTest extends BaseTest {
	/**
	* @test
	*/
	public function createBasicModel() {
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'basic',
			'properties' => array(
				array(
					'propertyName' => 'someString',
					'propertyType' => 'string'
				),
				array(
					'propertyName' => 'someInteger',
					'propertyType' => 'integer'
				),
				array(
					'propertyName' => 'someBoolean',
					'propertyType' => 'boolean'
				),
				array(
					'propertyName' => 'someFloat',
					'propertyType' => 'float'
				),
				array(
					'propertyName' => 'someDatetime',
					'propertyType' => '\DateTime'
				)
			)
		));
		$expectedModelClassName = '\Test\Package\Domain\Model\Basic';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\BasicRepository';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);

		$this->assertClassHasProperty($expectedModelClassName, 'someString', 'string');
		$this->assertClassHasProperty($expectedModelClassName, 'someInteger', 'integer');
		$this->assertClassHasProperty($expectedModelClassName, 'someBoolean', 'boolean');
		$this->assertClassHasProperty($expectedModelClassName, 'someFloat', 'float');
		$this->assertClassHasProperty($expectedModelClassName, 'someDatetime', '\DateTime');
		$this->assertClassHasMethod($expectedModelClassName, 'getSomeString');
		$this->assertClassHasMethod($expectedModelClassName, 'setSomeString');
	}
}
