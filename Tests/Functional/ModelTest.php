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
		$this->setAnswers(array(
			'test.package',	// Package
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

			'',				// proceed to generate
			'exit'			// exit command
		));
		$this->controller->plantCommand();

		$expectedModelClassName = '\Test\Package\Domain\Model\Basic';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\BasicRepository';

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
	public function createModelInSubdirectory() {
		$this->setAnswers(array(
			'test.package',	// Package
			'model/create',	// What to do
			'foo/bar/baz',	// modelName

			'someString',	// propertyName
			'string',		// propertyType

			'',				// proceed to generate
			'exit'			// exit command
		));
		$this->controller->plantCommand();

		$expectedModelClassName = '\Test\Package\Domain\Model\Foo\Bar\Baz';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\Foo\Bar\BazRepository';

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);

		$this->assertClassHasProperty($expectedModelClassName, 'someString', 'string');
		$this->assertClassHasMethod($expectedModelClassName, 'getSomeString');
		$this->assertClassHasMethod($expectedModelClassName, 'setSomeString');
	}

	/**
	* @test
	*/
	public function createOneToManyRelation() {
		$relationTargetClassName = '\Test\Package\Domain\Model\OneToMany\Target';
		$expectedModelClassName = '\Test\Package\Domain\Model\OneToMany\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\OneToMany\SourceRepository';

		$this->setAnswers(array(
			'test.package',		// Package

			'model/create',		// What to do
			'oneToMany/Target',	// modelName
			'',					// proceed to generate

			'model/create',				// What to do
			'oneToMany/Source',			// modelName
			'sourceItems',				// propertyName
			'relation',					// propertyType
			'one to many',				// relationType
			$relationTargetClassName,	// targetclass
			'targetItem',				// mappedBy

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sourceItems', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'setSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'addSourceItem');
		$this->assertClassHasMethod($expectedModelClassName, 'removeSourceItem');
		$this->assertClassHasDocComment($expectedModelClassName, 'sourceItems', '@ORM\OneToMany(mappedBy="targetItem")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targetItem', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getTargetItem');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargetItem');
		$this->assertClassHasDocComment($relationTargetClassName, 'targetItem', '@ORM\ManyToOne(inversedBy="sourceItems")');
	}

	/**
	* @test
	*/
	public function createManyToOneRelation() {
		$relationTargetClassName = '\Test\Package\Domain\Model\ManyToOne\Target';
		$expectedModelClassName = '\Test\Package\Domain\Model\ManyToOne\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\ManyToOne\SourceRepository';
		$this->setAnswers(array(
			'test.package',		// Package

			'model/create',		// What to do
			'manyToOne/Target',	// modelName
			'',					// proceed to generate


			'model/create',				// What to do
			'manyToOne/Source',			// modelName
			'sourceItem',				// propertyName
			'relation',					// propertyType
			'many to one',				// relationType
			$relationTargetClassName,	// targetclass
			'targetItems',				// inversedBy

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sourceItem', $relationTargetClassName);
		$this->assertClassHasMethod($expectedModelClassName, 'getSourceItem');
		$this->assertClassHasMethod($expectedModelClassName, 'setSourceItem');
		$this->assertClassHasDocComment($expectedModelClassName, 'sourceItem', '@ORM\ManyToOne(inversedBy="targetItems")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targetItems', '\Doctrine\Common\Collections\Collection<' . $expectedModelClassName . '>');
		$this->assertClassHasMethod($relationTargetClassName, 'getTargetItems');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargetItems');
		$this->assertClassHasMethod($relationTargetClassName, 'addTargetItem');
		$this->assertClassHasMethod($relationTargetClassName, 'removeTargetItem');
		$this->assertClassHasDocComment($relationTargetClassName, 'targetItems', '@ORM\OneToMany(mappedBy="sourceItem")');
	}

	/**
	* @test
	*/
	public function createOneToOneRelation() {
		$relationTargetClassName = '\Test\Package\Domain\Model\OneToOne\Target';
		$expectedModelClassName = '\Test\Package\Domain\Model\OneToOne\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\OneToOne\SourceRepository';
		$this->setAnswers(array(
			'test.package',		// Package

			'model/create',		// What to do
			'oneToOne/Target',	// modelName
			'',					// proceed to generate


			'model/create',				// What to do
			'oneToOne/Source',			// modelName
			'sourceItem',				// propertyName
			'relation',					// propertyType
			'one to one',				// relationType
			$relationTargetClassName,	// targetclass
			'targetItem',				// inversedBy

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sourceItem', $relationTargetClassName);
		$this->assertClassHasMethod($expectedModelClassName, 'getSourceItem');
		$this->assertClassHasMethod($expectedModelClassName, 'setSourceItem');
		$this->assertClassHasDocComment($expectedModelClassName, 'sourceItem', '@ORM\OneToOne(mappedBy="targetItem")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targetItem', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getTargetItem');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargetItem');
		$this->assertClassHasDocComment($relationTargetClassName, 'targetItem', '@ORM\OneToOne(mappedBy="sourceItem")');
	}

	/**
	* @test
	*/
	public function createManyToManyRelation() {
		$relationTargetClassName = '\Test\Package\Domain\Model\ManyToMany\Target';
		$expectedModelClassName = '\Test\Package\Domain\Model\ManyToMany\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\ManyToMany\SourceRepository';
		$this->setAnswers(array(
			'test.package',		// Package

			'model/create',		// What to do
			'manyToMany/Target',// modelName
			'',					// proceed to generate

			'model/create',				// What to do
			'manyToMany/Source',		// modelName
			'sourceItems',				// propertyName
			'relation',					// propertyType
			'many to many',				// relationType
			$relationTargetClassName,	// targetclass
			'targetItems',				// inversedBy

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sourceItems', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'setSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'addSourceItem');
		$this->assertClassHasMethod($expectedModelClassName, 'removeSourceItem');
		$this->assertClassHasDocComment($expectedModelClassName, 'sourceItems', '@ORM\ManyToMany(inversedBy="targetItems")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targetItems', '\Doctrine\Common\Collections\Collection<' . $expectedModelClassName . '>');
		$this->assertClassHasMethod($relationTargetClassName, 'getTargetItems');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargetItems');
		$this->assertClassHasMethod($relationTargetClassName, 'addTargetItem');
		$this->assertClassHasMethod($relationTargetClassName, 'removeTargetItem');
		$this->assertClassHasDocComment($relationTargetClassName, 'targetItems', '@ORM\ManyToMany(inversedBy="sourceItems")');
	}

	/**
	* @test
	*/
	public function createRelationToAlreadyExistingEntity() {
		$relationTargetClassName = '\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity';
		$expectedModelClassName = '\Test\Package\Domain\Model\OneToMany\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\OneToMany\SourceRepository';
		$this->setAnswers(array(
			'test.package',		// Package

			'model/create',				// What to do
			'oneToMany/Source',			// modelName
			'sourceItems',				// propertyName
			'relation',					// propertyType
			'one to many',				// relationType
			$relationTargetClassName,	// targetclass
			'targetItem',				// mappedBy

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sourceItems', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'setSourceItems');
		$this->assertClassHasMethod($expectedModelClassName, 'addSourceItem');
		$this->assertClassHasMethod($expectedModelClassName, 'removeSourceItem');
		$this->assertClassHasDocComment($expectedModelClassName, 'sourceItems', '@ORM\OneToMany(mappedBy="targetItem")');

		// Check the mappedBy autogeneration
		// $this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targetItem', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getTargetItem');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargetItem');
		$this->assertClassHasDocComment($relationTargetClassName, 'targetItem', '@ORM\ManyToOne(inversedBy="sourceItems")');
	}

	/**
	* @test
	* @group focus
	*/
	public function updateExistingEntity() {
		$expectedModelClassName = '\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity';
		$this->setAnswers(array(
			'test.package',		// Package

			'model/update',				// What to do
			$expectedModelClassName,	// modelName

			'someString',	// propertyName
			'string',		// propertyType

			'',					// proceed to generate
			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassHasProperty($expectedModelClassName, 'someString', 'string');
		$this->assertClassHasMethod($expectedModelClassName, 'getSomeString');
		$this->assertClassHasMethod($expectedModelClassName, 'setSomeString');
	}
}
