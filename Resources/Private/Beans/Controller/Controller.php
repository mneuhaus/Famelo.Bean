<?php
namespace {namespace}\Controller{controllerName -> b:format.objectNamespace()};

/*                                                                        *
<f:format.padding padLength="74"> * This script belongs to the TYPO3 Flow package "{packageKey}".</f:format.padding>*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class {controllerName -> b:format.objectName()}Controller extends \TYPO3\Flow\Mvc\Controller\ActionController {
	<f:for each="{actions}" as="action">
	/**
	 * @return void
	 */
	public function {action.actionName}Action() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}
	</f:for>
}