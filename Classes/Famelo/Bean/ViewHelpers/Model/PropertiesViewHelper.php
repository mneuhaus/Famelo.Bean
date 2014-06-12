<?php
namespace Famelo\Bean\ViewHelpers\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Kickstart".       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 */
class PropertiesViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $runtimeReflectionService;

	/**
	 *
	 * @param string $className
	 * @return string The humanized string
	 */
	public function render($className = NULL) {
		if ($className === NULL) {
			$className = $this->renderChildren();
		}

		$classSchema = $this->runtimeReflectionService->getClassSchema($className);
		$properties = array();
		foreach ($classSchema->getProperties() as $propertyName => $property) {
			if ($propertyName == 'Persistence_Object_Identifier') {
				continue;
			}
			$properties[$propertyName] = $this->getFormField($propertyName, $property['type']);
		}
		return $properties;
	}

	public function getFormField($name, $type) {
		switch ($type) {
			case 'string':
			case 'integer':
			case 'double':
			case 'float':
				return '<f:form.textfield property="' . $name . '" id="' . $name . '" />';

			case 'boolean':
				return '<f:form.checkbox property="' . $name . '" id="' . $name . '" />';

			case 'DateTime':
				return '<f:form.textfield type="datetime" value="{f:format.date(date: \'now\', format: \'Y-m-d\TH:i:sP\')}" property="' . $name . '" id="' . $name . '" />';

			default:
				return 'sorry, no default form field for the type: ' . $type;
		}
	}
}
