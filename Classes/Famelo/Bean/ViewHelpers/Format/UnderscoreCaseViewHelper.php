<?php
namespace Famelo\Bean\ViewHelpers\Format;

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
 * Humanize a camel cased value
 *
 * = Examples =
 *
 * <code title="Example">
 * {CamelCasedModelName -> k:inflect.humanizeCamelCase()}
 * </code>
 *
 * Output:
 * Camel cased model name
 *
 */
class UnderscoreCaseViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 *
	 * @return string The humanized string
	 */
	public function render() {
		$content = $this->renderChildren();
	}
}
