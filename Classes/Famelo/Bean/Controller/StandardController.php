<?php
namespace Famelo\Bean\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Bean".           *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends BaseController {
	/**
	 * @return void
	 */
	public function indexAction() {
		$parts = $this->configurationManager->getConfiguration('Parts');
		$actions = array();
		foreach ($parts as $identifier => $part) {
			$part['identifier'] = $identifier;
			$actions[$identifier] = $part;
		}

		$this->view->assign('parts', $parts);
	}

	/**
	 * @param string $action
	 */
	public function showAction($action) {
		$this->view->assign('part', $this->actions[$action]);
	}

}