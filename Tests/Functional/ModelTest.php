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
	public function createModelInSubdirectory() {
		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'foo/bar/baz',
			'properties' => array(
				array(
					'propertyName' => 'someString',
					'propertyType' => 'string'
				)
			)
		));
		$expectedModelClassName = '\Test\Package\Domain\Model\Foo\Bar\Baz';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\Foo\Bar\BazRepository';

		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);
		$bean->build($variables);

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
					'propertyName' => 'locals',
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
		$this->assertClassHasProperty($expectedModelClassName, 'locals', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getLocals');
		$this->assertClassHasMethod($expectedModelClassName, 'setLocals');
		$this->assertClassHasMethod($expectedModelClassName, 'addLocal');
		$this->assertClassHasMethod($expectedModelClassName, 'removeLocal');
		$this->assertClassHasDocComment($expectedModelClassName, 'locals', '@ORM\OneToMany(mappedBy="foreign")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'foreign', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getForeign');
		$this->assertClassHasMethod($relationTargetClassName, 'setForeign');
		$this->assertClassHasDocComment($relationTargetClassName, 'foreign', '@ORM\ManyToOne(inversedBy="locals")');
	}

	/**
	* @test
	*/
	public function createManyToOneRelation() {
		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'ManyToOne/Target',
			'properties' => array()
		));
		$relationTargetClassName = '\Test\Package\Domain\Model\ManyToOne\Target';
		$bean->build($variables);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'ManyToOne/Source',
			'properties' => array(
				array(
					'propertyName' => 'source',
					'propertyType' => $relationTargetClassName,
					'relation' => array(
						'type' => 'ManyToOne',
						'inversedBy' => 'targets'
					)
				)
			)
		));
		$bean->build($variables);

		$expectedModelClassName = '\Test\Package\Domain\Model\ManyToOne\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\ManyToOne\SourceRepository';
		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'source', $relationTargetClassName);
		$this->assertClassHasMethod($expectedModelClassName, 'getSource');
		$this->assertClassHasMethod($expectedModelClassName, 'setSource');
		$this->assertClassHasDocComment($expectedModelClassName, 'source', '@ORM\ManyToOne(inversedBy="targets")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targets', '\Doctrine\Common\Collections\Collection<' . $expectedModelClassName . '>');
		$this->assertClassHasMethod($relationTargetClassName, 'getTargets');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargets');
		$this->assertClassHasMethod($relationTargetClassName, 'addTarget');
		$this->assertClassHasMethod($relationTargetClassName, 'removeTarget');
		$this->assertClassHasDocComment($relationTargetClassName, 'targets', '@ORM\OneToMany(mappedBy="source")');
	}

	/**
	* @test
	*/
	public function createOneToOneRelation() {
		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'OneToOne/Target',
			'properties' => array()
		));
		$relationTargetClassName = '\Test\Package\Domain\Model\OneToOne\Target';
		$bean->build($variables);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'OneToOne/Source',
			'properties' => array(
				array(
					'propertyName' => 'source',
					'propertyType' => $relationTargetClassName,
					'relation' => array(
						'type' => 'OneToOne',
						'mappedBy' => 'target'
					)
				)
			)
		));
		$bean->build($variables);

		$expectedModelClassName = '\Test\Package\Domain\Model\OneToOne\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\OneToOne\SourceRepository';
		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'source', $relationTargetClassName);
		$this->assertClassHasMethod($expectedModelClassName, 'getSource');
		$this->assertClassHasMethod($expectedModelClassName, 'setSource');
		$this->assertClassHasDocComment($expectedModelClassName, 'source', '@ORM\OneToOne(mappedBy="target")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'target', $expectedModelClassName);
		$this->assertClassHasMethod($relationTargetClassName, 'getTarget');
		$this->assertClassHasMethod($relationTargetClassName, 'setTarget');
		$this->assertClassHasDocComment($relationTargetClassName, 'target', '@ORM\OneToOne(mappedBy="source")');
	}

	/**
	* @test
	*/
	public function createManyToManyRelation() {
		$beans = $this->configurationManager->getConfiguration('Beans');
		$bean = new DefaultBean($beans['model/create']);
		$bean->setSilent(TRUE);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'ManyToMany/Target',
			'properties' => array()
		));
		$relationTargetClassName = '\Test\Package\Domain\Model\ManyToMany\Target';
		$bean->build($variables);

		$variables = array_merge($this->getBaseVariables('Test.Package'), array(
			'modelName' => 'ManyToMany/Source',
			'properties' => array(
				array(
					'propertyName' => 'sources',
					'propertyType' => '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>',
					'subtype' => $relationTargetClassName,
					'relation' => array(
						'type' => 'ManyToMany',
						'inversedBy' => 'targets'
					)
				)
			)
		));
		$bean->build($variables);

		$expectedModelClassName = '\Test\Package\Domain\Model\ManyToMany\Source';
		$expectedRepositoryClassName = '\Test\Package\Domain\Repository\ManyToMany\SourceRepository';
		$this->assertClassExists($expectedRepositoryClassName);
		$this->assertClassExists($expectedModelClassName);
		$this->assertClassHasProperty($expectedModelClassName, 'sources', '\Doctrine\Common\Collections\Collection<' . $relationTargetClassName . '>');
		$this->assertClassHasMethod($expectedModelClassName, 'getSources');
		$this->assertClassHasMethod($expectedModelClassName, 'setSources');
		$this->assertClassHasMethod($expectedModelClassName, 'addSource');
		$this->assertClassHasMethod($expectedModelClassName, 'removeSource');
		$this->assertClassHasDocComment($expectedModelClassName, 'sources', '@ORM\ManyToMany(inversedBy="targets")');

		// Check the mappedBy autogeneration
		$this->assertClassExists($relationTargetClassName);
		$this->assertClassHasProperty($relationTargetClassName, 'targets', '\Doctrine\Common\Collections\Collection<' . $expectedModelClassName . '>');
		$this->assertClassHasMethod($relationTargetClassName, 'getTargets');
		$this->assertClassHasMethod($relationTargetClassName, 'setTargets');
		$this->assertClassHasMethod($relationTargetClassName, 'addTarget');
		$this->assertClassHasMethod($relationTargetClassName, 'removeTarget');
		$this->assertClassHasDocComment($relationTargetClassName, 'targets', '@ORM\ManyToMany(inversedBy="sources")');
	}
}
