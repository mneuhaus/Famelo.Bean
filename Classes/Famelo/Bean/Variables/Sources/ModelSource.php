<?php
namespace Famelo\Bean\Variables\Sources;

use TYPO3\Flow\Annotations as Flow;

/**
 */
class ModelSource {
	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $packageKey;

	/**
	 * @var array
	 */
	protected $values = array();

	public function __construct($configuration, $packageKey) {
		$this->configuration = $configuration;
		$this->packageKey = $packageKey;
	}

	public function getSources() {
		$classNames = $this->reflectionService->getClassNamesByAnnotation('\TYPO3\Flow\Annotations\Entity');
		$models = array();
		foreach ($classNames as $key => $className) {
			$packageKey = $this->objectManager->getPackageKeyByObjectName($className);
			if (strtolower($packageKey) !== strtolower($this->packageKey)) {
				continue;
			}
			$className = '\\' . $className;

			preg_match('/Domain\\\\Model\\\\(.+)/', $className, $match);
			if (isset($match[1])) {
				$models[$className] = str_replace('\\', '/', $match[1]);
			} else {
				$models[$className] = $className;
			}
		}

		ksort($models);

		return $models;
	}

	public function setSource($source) {
		$this->source = $source;

		preg_match('/(Domain\\\\Model|Controller)\\\\(.+)/', $this->source, $match);
		$this->values['modelName'] = str_replace('\\', '/', $match[2]);

		$properties = array();
		$classSchema = $this->reflectionService->getClassSchema($this->source);
		foreach ($classSchema->getProperties() as $propertyName => $property) {
			if ($propertyName === 'Persistence_Object_Identifier') {
				continue;
			}

            $declaringClass = $this->getDeclaringTraitForProperty($this->source, $propertyName);
			if (!stristr($declaringClass->name, ltrim($this->source, '\\'))) {
				continue;
			}

			$properties[] = array(
				'propertyName' => $propertyName,
				'propertyType' => $this->getPropertyType($propertyName, $property)
			);
		}
		$this->values['properties'] = $properties;
	}

	public function getPropertyType($propertyName, $property) {
		$type = $property['type'];
		$annotations = $this->reflectionService->getPropertyAnnotations($this->source, $propertyName);
		switch (TRUE) {
			case isset($annotations['Doctrine\ORM\Mapping\OneToOne']):
				$annotation = current($annotations['Doctrine\ORM\Mapping\OneToOne']);
				return array(
					'type' => 'relation',
					'relation' => 'oneToOne',
					'elementType' => $property['type'],
					'targetProperty' => $annotation->mappedBy
				);

			case isset($annotations['Doctrine\ORM\Mapping\OneToMany']):
				$annotation = current($annotations['Doctrine\ORM\Mapping\OneToMany']);
				return array(
					'type' => 'relation',
					'relation' => 'oneToMany',
					'elementType' => $property['elementType'],
					'targetProperty' => $annotation->mappedBy
				);

			case isset($annotations['Doctrine\ORM\Mapping\ManyToOne']):
				$annotation = current($annotations['Doctrine\ORM\Mapping\ManyToOne']);
				return array(
					'type' => 'relation',
					'relation' => 'manyToOne',
					'elementType' => $property['type'],
					'targetProperty' => $annotation->inversedBy
				);

			case isset($annotations['Doctrine\ORM\Mapping\ManyToMany']):
				$annotation = current($annotations['Doctrine\ORM\Mapping\ManyToMany']);
				return array(
					'type' => 'relation',
					'relation' => 'manyToMany',
					'elementType' => $property['elementType'],
					'targetProperty' => $annotation->inversedBy
				);
		}
		if ($property['type'] == 'DateTime') {
			return array('type' => '\DateTime');
		}
		return array('type' => $property['type']);
	}

	public function getValues() {
		return $this->values;
	}

	public function getValue($name) {
		if (isset($this->values[$name])) {
			return $this->values[$name];
		}
	}

    /**
     * Finds the trait that declares $className::$propertyName
     * source: http://mouf-php.com/blog/php_reflection_api_traits
     */
    public function getDeclaringTraitForProperty($className, $propertyName) {
        $reflectionClass = new \ReflectionClass($className);

        // Let's scan all traits
        $trait = $this->deepScanTraitsForProperty($reflectionClass->getTraits(), $propertyName);
        if ($trait != null) {
            return $trait;
        }
        // The property is not part of the traits, let's find in which parent it is part of.
        if ($reflectionClass->getParentClass()) {
            $declaringClass = $this->getDeclaringTraitForProperty($reflectionClass->getParentClass()->getName(), $propertyName);
            if ($declaringClass != null) {
                return $declaringClass;
            }
        }
        if ($reflectionClass->hasProperty($propertyName)) {
            return $reflectionClass;
        }

        return null;
    }

    /**
     * Recursive method called to detect a method into a nested array of traits.
     *
     * @param $traits ReflectionClass[]
     * @param $propertyName string
     * @return ReflectionClass|null
     */
    public function deepScanTraitsForProperty(array $traits, $propertyName) {
        foreach ($traits as $trait) {
            // If the trait has a property, it's a win!
            $result = $this->deepScanTraitsForProperty($trait->getTraits(), $propertyName);
            if ($result != null) {
                return $result;
            } else {
                if ($trait->hasProperty($propertyName)) {
                    return $trait;
                }
            }
        }
        return null;
    }
}