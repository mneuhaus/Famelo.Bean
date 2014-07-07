<?php
namespace Famelo\Bean\Tests\Functional\Builder;

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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
class ModelTest extends BaseTest {

// 	/**
// 	*/
// 	public function setUp() {
// 		parent::setUp();
// 		$existingEntityFilename = FLOW_PATH_PACKAGES . 'Application/Famelo.Bean/Tests/Functional/Fixtures/ExistingEntity.php';
// 		$this->reflectionService->addFilenameForClassName('\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity', $existingEntityFilename);
// 	}

// 	public function reset() {
// 		parent::setUp();
// 		$existingEntityFilename = FLOW_PATH_PACKAGES . 'Application/Famelo.Bean/Tests/Functional/Fixtures/ExistingEntity.php';
// 		file_put_contents($existingEntityFilename, '<?php
// namespace Famelo\Bean\Tests\Functional\Fixtures;

// use TYPO3\Flow\Annotations as Flow;
// use Doctrine\ORM\Mapping as ORM;

// /**
//  * @Flow\Entity
//  */
// class ExistingEntity {

// }');
// 	}

	/**
	* @test
	*/
	public function addPropertyToExistingEntity() {
		$this->reset();
		$builder = new \Famelo\Bean\Builder\ModelBuilder(array(
      		'builder' => '\Famelo\Bean\Builder\ModelBuilder',
      		'partialPath' => 'resource://Famelo.Bean/Private/Beans/Model/'
		));

		$expectedModelClassName = '\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity';
		$builder->update($expectedModelClassName, array(
			'package' => 'Famelo.Bean',
			'model' => $expectedModelClassName,
			'source' => $expectedModelClassName,
			'className' => 'ExistingEntity',
			'properties' => array(
				'__NEW897489273' => array(
					'propertyName' => 'someString',
					'propertyType' => array(
		            	'type' => 'string',
		            	'relation' => '0',
		            	'elementType' => '0'
					)
				)
			)
		));

		$this->assertClassHasProperty($expectedModelClassName, 'someString', 'string');
		$this->assertClassHasMethod($expectedModelClassName, 'getSomeString');
		$this->assertClassHasMethod($expectedModelClassName, 'setSomeString');
	}

	/**
	* @test
	*/
	public function renamePropertyInExistingEntity() {
		$this->reset();
		$builder = new \Famelo\Bean\Builder\ModelBuilder(array(
      		'builder' => '\Famelo\Bean\Builder\ModelBuilder',
      		'partialPath' => 'resource://Famelo.Bean/Private/Beans/Model/'
		));

		$expectedModelClassName = '\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity';
		$builder->update($expectedModelClassName, array(
			'package' => 'Famelo.Bean',
			'model' => $expectedModelClassName,
			'source' => $expectedModelClassName,
			'className' => 'ExistingEntity',
			'properties' => array(
				'existingProperty' => array(
					'propertyName' => 'newPropertyName',
					'propertyType' => array(
		            	'type' => 'string',
		            	'relation' => '0',
		            	'elementType' => '0'
					)
				)
			)
		));

		$this->assertClassHasProperty($expectedModelClassName, 'newPropertyName', 'string');
		$this->assertClassHasMethod($expectedModelClassName, 'getNewPropertyName');
		$this->assertClassHasMethod($expectedModelClassName, 'setNewPropertyName');

		$this->assertClassHasNotProperty($expectedModelClassName, 'existingProperty');
		$this->assertClassHasNotMethod($expectedModelClassName, 'getExistingProperty');
		$this->assertClassHasNotMethod($expectedModelClassName, 'setExistingProperty');
	}

	/**
	* @test
	*/
	public function changeExistingPropertyType() {
		$this->reset();
		$builder = new \Famelo\Bean\Builder\ModelBuilder(array(
      		'builder' => '\Famelo\Bean\Builder\ModelBuilder',
      		'partialPath' => 'resource://Famelo.Bean/Private/Beans/Model/'
		));

		$expectedModelClassName = '\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity';
		$builder->update($expectedModelClassName, array(
			'package' => 'Famelo.Bean',
			'model' => $expectedModelClassName,
			'source' => $expectedModelClassName,
			'className' => 'ExistingEntity',
			'properties' => array(
				'existingProperty' => array(
					'propertyName' => 'existingProperty',
					'propertyType' => array(
		            	'type' => 'boolean',
		            	'relation' => '0',
		            	'elementType' => '0'
					)
				)
			)
		));

		$this->assertClassHasProperty($expectedModelClassName, 'existingProperty', 'boolean');
		$this->assertClassHasMethod($expectedModelClassName, 'getExistingProperty');
		$this->assertClassHasMethod($expectedModelClassName, 'setExistingProperty');
	}
}
