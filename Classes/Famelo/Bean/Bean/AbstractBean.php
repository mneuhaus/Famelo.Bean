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
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $package;

	/**
	 * @var array
	 */
	protected $variables;

	/**
	 * @var \Famelo\Kickstart\Command\KickstartCommandController
	 */
	protected $controller;

	public function __construct($configuration, $package, $controller) {
		$this->configuration = $configuration;
		$this->package = $package;
		$this->controller = $controller;

		if ($this->package instanceof Package) {
			$this->variables = array(
				'packageKey' => $package->getPackageKey(),
				'namespace' => $package->getNamespace(),
				'packagePath' => $package->getPackagePath(),
				'classesPath' => $package->getClassesNamespaceEntryPath(),
				'resourcesPath' => $package->getResourcesPath(),
				'configurationPath' => $package->getConfigurationPath(),
				'documentationPath' => $package->getDocumentationPath()
			);
		}
	}

    public function run() {
        $this->fetchVariables();

        foreach ($this->configuration['files'] as $file) {
			$builderClassName = '\Famelo\Bean\Builder\FluidBuilder';
			if (isset($file['builder'])) {
				$builderClassName = $file['builder'];
			}
			$builder = new $builderClassName($file);
			if (isset($file['mode'])) {
				call_user_method($file['mode'], $builder, $file['template'], $file['target'], $this->variables);
			} else {
            	$builder->buildNew($file['template'], $file['target'], $this->variables);
			}
        }
    }

	public function fetchVariables() {
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			switch (isset($variable['type']) ? $variable['type'] : 'ask') {
				case 'ask':
				default:
						$this->variables[$variableName] = $this->controller->ask('<q>' . $variable['question'] . '</q>' . chr(10));
					break;
			}
			$this->outputLine();
		}
	}
}