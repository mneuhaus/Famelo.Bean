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

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class LowercaseViewHelper extends AbstractViewHelper {

	/**
	 * Uppercase first character
	 *
	 * @return string The altered string.
	 */
	public function render() {
		return strtolower($this->renderChildren());
	}
}
