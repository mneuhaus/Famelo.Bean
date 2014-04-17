<?php
namespace Famelo\Bean\Variables;

/**
 */
class PropertiesVariable extends AbstractVariable {
	public function interact() {
		$properties = array();
		while (($field = $this->createProperty()) !== FALSE) {
			$properties[] = $field;
			$this->previewFields($properties);
		}
		$this->outputLine();
	}

	public function createProperty() {
		$name = $this->ask('<q>Property name (leave empty to skip):</q> ');
		if (empty($name)) {
			return FALSE;
		}
		$type = $this->chooseFieldType();

		$property = array(
			'name' => $name,
			'type' => $type,
			'docComment' => NULL
		);

		switch ($type) {
			case 'relation':
				$property = $this->createRelationProperty($property);
				break;
		}

		$this->outputLine();
		return $property;
	}

	public function createRelationProperty($property) {
		$relations = array('one to many', 'many to one', 'one to one', 'many to many');
		$type = $this->ask(
			'<q>What type of relation (' . implode(', ', $relations) . ':</q> ' . chr(10),
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

		switch ($type) {
			case 'one to one':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$property['type'] = '\\' . $className;
				$property['docComment'] = '@ORM\OneToOne(mappedBy="' . $mappedBy . '")';
				break;

			case 'many to one':
				$mappedBy = $this->ask(
					'<q>inversed by:</q> ' . chr(10)
				);
				$property['type'] = '\\' . $className;
				$property['docComment'] = '@ORM\ManyToOne(inversedBy="' . $mappedBy . '")';

				break;

			case 'one to many':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$property['docComment'] = '@ORM\OneToMany(mappedBy="' . $mappedBy . '")';
				$property['subtype'] = '\\' . $className;
				break;

			case 'many to many':
				$mappedBy = $this->ask(
					'<q>mapped by:</q> ' . chr(10)
				);
				$property['type'] = '\Doctrine\Common\Collections\Collection<\\' . $className . '>';
				$property['docComment'] = '@ORM\ManyToMany(inversedBy="' . $mappedBy . '")';
				$property['subtype'] = '\\' . $className;
				break;
		}
		return $property;
	}

	public function chooseFieldType() {
		$types = array(
			'string' => 'string',
			'integer' => 'integer',
			'float' => 'float',
			'boolean' => 'boolean',
			'datetime' => '\DateTime',
			'done' => 'done',
			'relation' => 'relation'
		);
		$choice = $this->ask(
			'<q>Property Type (' . implode(',', array_keys($types)) . '):</q> ' . chr(10),
			NULL,
			array_keys($types)
		);
		return $types[$choice];
	}

	public function previewFields($properties) {
		$rows = array();
		foreach ($properties as $property) {
			$rows[] = array(
				$property['name'],
				$property['type']
			);
		}
		$this->table($rows, array('Name', 'Type'));
	}
}