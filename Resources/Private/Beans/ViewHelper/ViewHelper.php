<?php
namespace {namespace}\ViewHelpers{viewHelperName -> b:format.objectNamespace()};
/*                                                                        *
<f:format.padding padLength="74"> * This script belongs to the TYPO3 Flow package "{packageKey}".</f:format.padding>*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class {viewHelperName -> b:format.objectName()}ViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 *
	 * @param string $foo
	 * @return string
	 */
	public function render($foo) {
		return $foo;
	}
}