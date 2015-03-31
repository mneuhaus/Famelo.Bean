<?php
namespace Famelo\Bean\ViewHelpers\Entity;

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
use TYPO3\Flow\Reflection\ClassSchema;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class RepositoryClassNameViewHelper extends AbstractViewHelper {

	
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * Uppercase first character
	 *
	 * @return string The altered string.
	 */
	public function render() {
		$className = $this->renderChildren();
		$classSchema = $this->reflectionService->getClassSchema($className);
		if ($classSchema instanceof ClassSchema) {
			return $classSchema->getRepositoryClassName();
		}
		return preg_replace('/Domain\\\\Model\\\\(.*)/', 'Domain\\\\Repository\\\\$1Repository', $className);
	}
}
