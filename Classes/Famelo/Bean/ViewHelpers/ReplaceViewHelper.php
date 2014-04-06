<?php
namespace Famelo\Bean\ViewHelpers;

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
 * {Foo.bar -> b:replace(search: '.', replace: '_')}
 * </code>
 *
 * Output:
 * Foo_bar
 *
 */
class ReplaceViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 *
	 * @param string $search
	 * @param string $replace
	 * @return string The humanized string
	 */
	public function render($search, $replace) {
		return str_replace($search, $replace, $this->renderChildren());
	}
}
