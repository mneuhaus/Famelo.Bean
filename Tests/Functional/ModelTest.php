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

	/**
	* @test
	*/
	public function createOneToManyRelation() {
		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'relationTarget',
			'properties' => array()
		));
		$relationTargetClassName = '\Test\Package\Domain\Model\RelationTarget';
		$bean->build($variables);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'oneToMany',
			'properties' => array(
				array(
					'propertyName' => 'local',
					'propertyType' => '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>',
					'subtype' => $relationTargetClassName,
					'relation' => array(
						'type' => 'OneToMany',
						'mappedBy' => 'foreign'
					)
				)
			)
		));
		$bean->build($variables);

		$expectedModelClassName = '\Test\Package\Domain\Model\OneToMany';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\OneToManyRepository';
		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'local', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getLocal');
		$this->assertClassHasMethod($expectedModelClassName, 'setLocal');
		$this->assertClassHasMethod($expectedModelClassName, 'addLocal');
		$this->assertClassHasMethod($expectedModelClassName, 'removeLocal');
		$this->assertClassHasDocComment($expectedModelClassName, 'local', '@ORM\OneToMany(mappedBy="foreign")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'foreign', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getForeign');
		$this->assertClassHasMethod($relationTargetClassName, 'setForeign');
		$this->assertClassHasDocComment($relationTargetClassName, 'foreign', '@ORM\ManyToOne(inversedBy="local")');
	}
}
