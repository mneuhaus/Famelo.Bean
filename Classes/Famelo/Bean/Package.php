<?php
namespace Famelo\Bean;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The TYPO3 Flow Package
 *
 */
class Package extends BasePackage {

	/**
	 * @var boolean
	 */
	protected $protected = TRUE;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		require_once(FLOW_PATH_PACKAGES . '/Libraries/nikic/php-parser/lib/bootstrap.php');

		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect('TYPO3\Flow\Configuration\ConfigurationManager', 'configurationManagerReady', function(ConfigurationManager $configurationManager) {
			$configurationManager->registerConfigurationType('Beans', ConfigurationManager::CONFIGURATION_PROCESSING_TYPE_APPEND);
		});
	}
}
