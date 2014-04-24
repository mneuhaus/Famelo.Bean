<?php

namespace Famelo\Bean\Bean;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Bean\Traits\InteractionTrait;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\Package;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class AbstractBean {
    use InteractionTrait;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Kickstart\Utility\Inflector
	 * @Flow\Inject
	 */
	protected $inflector;

	/**
	 * @var \Famelo\Bean\Service\InteractionService
	 * @Flow\Inject
	 */
	protected $interaction;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $package;

	/**
	 * @var array
	 * @Flow\Inject(setting="DefaultVariables")
	 */
	protected $variables;

	/**
	 * @var \Famelo\Kickstart\Command\KickstartCommandController
	 */
	protected $controller;

	public function injectInteraction($interaction) {
		$this->interaction = $interaction;
	}

	public function __construct($configuration, $package = NULL) {
		$this->configuration = $configuration;
		$this->package = $package;
	}

	public function initialize() {
		if ($this->package instanceof Package) {
			$this->variables = array_merge($this->variables, array(
				'packageKey' => $this->package->getPackageKey(),
				'namespace' => $this->package->getNamespace(),
				'packagePath' => $this->package->getPackagePath(),
				'classesPath' => $this->package->getClassesNamespaceEntryPath(),
				'resourcesPath' => $this->package->getResourcesPath(),
				'configurationPath' => $this->package->getConfigurationPath(),
				'documentationPath' => $this->package->getDocumentationPath()
			));
		}
	}

	public function getVariables() {
		return $this->variables;
	}
}