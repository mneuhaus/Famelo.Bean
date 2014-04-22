<?php
namespace {namespace}\Command{controllerName -> b:format.objectNamespace()};
/*                                                                        *
<f:format.padding padLength="74"> * This script belongs to the TYPO3 Flow package "{packageKey}".</f:format.padding>*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class {controllerName -> b:format.objectName()}CommandController extends \TYPO3\Flow\Cli\CommandController {
	<f:for each="{actions}" as="action">
	/**
	 * @return void
	 */
	public function {action.actionName}Command() {
		// ...
	}
	</f:for>
}