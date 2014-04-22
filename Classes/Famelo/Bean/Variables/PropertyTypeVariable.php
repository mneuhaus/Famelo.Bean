<?php
namespace Famelo\Bean\Variables;

use TYPO3\Flow\Annotations as Flow;

/**
 */
class PropertyTypeVariable extends AbstractVariable {
	/**
	 * @var array
	 * @Flow\Inject(setting="PropertyTypes")
	 */
	protected $propertyTypes;

	public function interact() {
		$type = $this->chooseFieldType();

		$propertyType = array(
			'type' => $type,
			'docComment' => NULL
		);

		switch ($type) {
			case 'relation':
				$propertyType = $this->createRelationProperty($propertyType);
				break;
		}

		$this->value = $propertyType;
	}

	public function createRelationProperty($property) {
		$relations = array('one to many', 'many to one', 'one to one', 'many to many');
		$type = $this->interaction->ask(
			'<q>What type of relation (' . implode(', ', $relations) . '):</q> ' . chr(10),
			NULL,
			$relations,
			TRUE
		);

		$className = '\\' . ltrim($this->chooseClassNameAnnotatedWith(
			'<q>What is the target entity for this relation?</q>',
			'\TYPO3\Flow\Annotations\Entity'
		), '\\');

		$property['relationType'] = $type;
		$property['docComments'] = array();

		$modelName = lcfirst($this->previousVariables['modelName']);

		switch ($type) {
			case 'one to one':
				$mappedBy = $this->interaction->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = $className;
				$property['relation'] = array(
					'type' => 'OneToOne',
					'mappedBy' => $mappedBy
				);
				break;

			case 'many to one':
				$inversedBy = $this->interaction->ask(
					'<q>inversed by:</q> ' . chr(10),
					$modelName
				);
				$property['type'] = $className;
				$property['relation'] = array(
					'type' => 'ManyToOne',
					'inversedBy' => $inversedBy
				);

				break;

			case 'one to many':
				$mappedBy = $this->interaction->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<' . $className . '>';
				$property['relation'] = array(
					'type' => 'OneToMany',
					'mappedBy' => $mappedBy
				);
				$property['subtype'] = $className;
				break;

			case 'many to many':
				$inversedBy = $this->interaction->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<' . $className . '>';
				$property['relation'] = array(
					'type' => 'ManyToMany',
					'inversedBy' => $inversedBy
				);
				$property['subtype'] = $className;
				break;
		}
		return $property;
	}

	public function chooseFieldType() {
		$choice = $this->interaction->ask(
			'<q>Property Type (' . implode(',', array_keys($this->propertyTypes)) . '):</q> ' . chr(10),
			NULL,
			array_keys($this->propertyTypes),
			TRUE
		);
		return $this->propertyTypes[$choice];
	}
}