<?php

namespace Famelo\Bean\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Bean".      *
 *                                                                        *
 *                                                                        */

use Famelo\Common\Command\AbstractInteractiveCommandController;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

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
	 * @var \Famelo\Bean\Service\InteractionService
	 * @Flow\Inject
	 */
	protected $interaction;

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

	public function injectInteraction($interaction) {
		$this->interaction = $interaction;
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
					$bean->injectInteraction($this->interaction);
					$bean->plant();
					break;
			}
			$this->interaction->outputLine();
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
			$choice = $this->interaction->ask('<q>What do you want to create?</q>' . chr(10), NULL, array_keys($actions), TRUE);
			if (!isset($actions[$choice])) {
				$this->interaction->outputLine('<error>unknown action</error>');
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
			$choice = $this->interaction->ask('<q>What do you want to create?</q>' . chr(10), NULL, array_keys($actions));
			if (!isset($actions[$choice])) {
				$this->interaction->outputLine('<error>unknown action</error>');
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
		$choice = $this->interaction->ask('<q>Which Package do you want to work on?</q>' . chr(10),
			NULL,
			$choices,
			TRUE
		);
		$this->package = $packages[$choice];
		$this->interaction->outputLine();
	}

	public function renamePackageCommand() {

		$choices = array();
		$packages = array();
		foreach ($this->packageManager->getAvailablePackages() as $package) {
			$manifest = $package->getComposerManifest();
			if (isset($manifest->type) && stristr($manifest->type, 'typo3')) {
				$choices[] = strtolower($package->getPackageKey());
				$packages[strtolower($package->getPackageKey())] = $package;
			}
		}
		$choice = $this->interaction->ask('<q>Which Package do you want to rename?</q>' . chr(10),
			NULL,
			$choices,
			TRUE
		);
		$choice = 'typo3.expose';
		$package = $packages[$choice];

		$replacePackageKey = $this->interaction->ask('<q>What should the package be renamed to?</q>' . chr(10));

		$searchReplace = array(
			// packageKey
			$package->getPackageKey() => $replacePackageKey,

			// composer package name
			strtolower(str_replace('\\', '/', $package->getNamespace())) => strtolower(str_replace('.', '/', $replacePackageKey)),

			// Regular Namespace
			$package->getNamespace() => str_replace('.', '\\', $replacePackageKey),

			// Escaped Namespace
			str_replace('\\', '\\\\', $package->getNamespace()) => str_replace('.', '\\\\', $replacePackageKey),

			// extensionName
			strtolower($package->getPackageKey()) => strtolower($replacePackageKey)
		);

		$packageFiles = array_merge(
			Files::readDirectoryRecursively($package->getClassesPath(), 'php'),
			Files::readDirectoryRecursively($package->getFunctionalTestsPath(), 'php'),
			Files::readDirectoryRecursively($package->getConfigurationPath(), 'yaml'),
			Files::readDirectoryRecursively($package->getResourcesPath())
		);
		foreach ($packageFiles as $packageFileName) {
			$this->searchAndReplace($packageFileName, $searchReplace);
		}

		$this->searchAndReplace($package->getPackagePath() . 'composer.json', $searchReplace);

		$searchPath = explode('.', $package->getPackageKey());
		$replacePath = explode('.', $replacePackageKey);

		rename($package->getClassesPath() . $searchPath[0], $package->getClassesPath() . $replacePath[0]);
		rename($package->getClassesPath() . $replacePath[0] . '/' . $searchPath[1], $package->getClassesPath() . $replacePath[0] . '/' . $replacePath[1]);
		rename($package->getPackagePath(), $package->getPackagePath() . '../' . $replacePackageKey);
	}

	public function searchAndReplace($fileName, $searchReplace) {
		$content = file_get_contents($fileName);
		$content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
		file_put_contents($fileName, $content);
	}
}