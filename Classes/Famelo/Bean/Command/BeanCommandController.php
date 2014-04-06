<?php

namespace Famelo\Bean\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Bean".      *
 *                                                                        *
 *                                                                        */

use Famelo\Common\Command\AbstractInteractiveCommandController;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class BeanCommandController extends AbstractInteractiveCommandController {

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\Flow\Package\PackageManager
	 */
	protected $packageManager;

	/**
	 * @var string
	 */
	protected $package;

	/**
	* @param \TYPO3\Flow\Package\PackageManagerInterface $packageManager
	* @return void
	*/
	public function injectPackageManager(\TYPO3\Flow\Package\PackageManagerInterface $packageManager) {
		$this->packageManager =  $packageManager;
	}

	/**
	 * Constructs the controller
	 *
	 */
	public function __construct() {
		parent::__construct();

		$style = new OutputFormatterStyle('cyan', 'black');
		$this->output->getFormatter()->setStyle('q', $style);
	}

	/**
	 * An example command
	 *
	 * @param string $requiredArgument This argument is required
	 * @param string $optionalArgument This argument is optional
	 * @return void
	 */
	public function plantCommand() {
		$this->choosePackage();

		while (($bean = $this->choosePlantAction()) !== 'exit') {
			switch ($bean) {
				case 'chooseAction':
					$this->choosePackage();
					break;

				default:
					$implementation = '\Famelo\Bean\Bean\DefaultBean';
					if (isset($bean['implementation'])) {
						$implementation = $bean['implementation'];
					}
					$bean = new $implementation($bean, $this->package, $this);
					$bean->run();
					break;
			}
			$this->outputLine();
		}
	}

	/**
	 * An example command
	 *
	 * @param string $requiredArgument This argument is required
	 * @param string $optionalArgument This argument is optional
	 * @return void
	 */
	public function growCommand() {
		while (($bean = $this->chooseGrowAction()) !== 'done') {
			$implementation = '\Famelo\Bean\Bean\DefaultBean';
			if (isset($bean['implementation'])) {
				$implementation = $bean['implementation'];
			}
			$bean = new $implementation($bean, $this->package, $this);
			$bean->grow();
		}
	}

	public function choosePlantAction() {
		$actions = array(
			'choose-package' => 'choose-package',
			'exit' => 'exit'
		);

		$beans = $this->configurationManager->getConfiguration('Beans');
		foreach ($beans as $identifier => $bean) {
			$bean['identifier'] = $identifier;
			$actions[$identifier] = $bean;
		}

		do {
			$choice = $this->ask('<q>What do you want to create?</q>' . chr(10), NULL, array_keys($actions), TRUE);
			if (!isset($actions[$choice])) {
				$this->outputLine('<error>unknown action</error>');
			}
		} while (!isset($actions[$choice]));
		return $actions[$choice];
	}

	public function chooseGrowAction() {
		$actions = array(
			'done' => 'done'
		);

		$beans = $this->configurationManager->getConfiguration('Beans');
		foreach ($beans as $identifier => $bean) {
			if (!isset($bean['grow']) || $bean['grow'] !== TRUE) {
				continue;
			}
			$bean['identifier'] = $identifier;
			$actions[$identifier] = $bean;
		}

		do {
			$choice = $this->ask('<q>What do you want to create?</q>' . chr(10), NULL, array_keys($actions));
			if (!isset($actions[$choice])) {
				$this->outputLine('<error>unknown action</error>');
			}
		} while (!isset($actions[$choice]));
		return $actions[$choice];
	}

	public function choosePackage() {
		$choices = array();
		$packages = array();
		foreach ($this->packageManager->getAvailablePackages() as $package) {
			$manifest = $package->getComposerManifest();
			if (isset($manifest->type) && stristr($manifest->type, 'typo3')) {
				$choices[] = strtolower($package->getPackageKey());
				$packages[strtolower($package->getPackageKey())] = $package;
			}
		}
		$choice = $this->ask('<q>Which Package do you want to work on?</q>' . chr(10),
			NULL,
			$choices,
			TRUE
		);
		$this->package = $packages[$choice];
		$this->outputLine();
	}
}