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
		var_dump($this->previousVariables);
		$type = $this->chooseFieldType();

		$property = array(
			'type' => $type,
			'docComment' => NULL
		);

		switch ($type) {
			case 'relation':
				$property = $this->createRelationProperty($property);
				break;
		}

		$this->value = $property;
	}

	public function createRelationProperty($property) {
		$relations = array('one to many', 'many to one', 'one to one', 'many to many');
		$type = $this->ask(
			'<q>What type of relation (' . implode(', ', $relations) . '):</q> ' . chr(10),
			NULL,
			$relations,
			TRUE
		);

		$className = $this->chooseClassNameAnnotatedWith(
			'<q>What is the target entity for this relation?</q>',
			'\TYPO3\Flow\Annotations\Entity'
		);

		$property['relationType'] = $type;
		$property['docComments'] = array();

		$modelName = $this->previousVariables['modelName'];

		switch ($type) {
			case 'one to one':
				$mappedBy = $this->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\\' . $className;
				$property['docComment'] = '@ORM\OneToOne(mappedBy="' . $mappedBy . '")';
				break;

			case 'many to one':
				$mappedBy = $this->ask(
					'<q>inversed by:</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\\' . $className;
				$property['docComment'] = '@ORM\ManyToOne(inversedBy="' . $mappedBy . '")';

				break;

			case 'one to many':
				$mappedBy = $this->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$property['docComment'] = '@ORM\OneToMany(mappedBy="' . $mappedBy . '")';
				$property['subtype'] = '\\' . $className;
				break;

			case 'many to many':
				$mappedBy = $this->ask(
					'<q>mapped by (Default: ' . $modelName . '):</q> ' . chr(10),
					$modelName
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$property['docComment'] = '@ORM\ManyToMany(inversedBy="' . $mappedBy . '")';
				$property['subtype'] = '\\' . $className;
				break;
		}
		return $property;
	}

	public function chooseFieldType() {
		$choice = $this->ask(
			'<q>Property Type (' . implode(',', array_keys($this->propertyTypes)) . '):</q> ' . chr(10),
			NULL,
			array_keys($this->propertyTypes),
			TRUE
		);
		return $this->propertyTypes[$choice];
	}
}