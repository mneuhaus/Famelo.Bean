<?php
namespace Famelo\Bean\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Bean".           *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class BaseController extends \TYPO3\Flow\Mvc\Controller\ActionController {
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * @var array
	 */
	protected $beans = array();

	/**
	 * @var array
	 */
	protected $packages = array();

	/**
	 * @var string
	 */
	protected $packageKey = NULL;

	/**
	 * @var string
	 */
	protected $package = NULL;

	/**
	 * @var array
	 * @Flow\Inject(setting="Variables")
	 */
	protected $variableImplementations;

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param \TYPO3\Flow\Mvc\View\ViewInterface $view The view to be initialized
	 * @return void
	 * @api
	 */
	protected function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {
		$parts = $this->configurationManager->getConfiguration('Parts');
		foreach ($parts as $identifier => $part) {
			$part['identifier'] = $identifier;
			$this->beans[$identifier] = $part;
		}
		$view->assign('beans', $this->beans);

		foreach ($this->packageManager->getAvailablePackages() as $package) {
			$manifest = $package->getComposerManifest();
			if (isset($manifest->type) && stristr($manifest->type, 'typo3')) {
				$choices[] = strtolower($package->getPackageKey());
				$this->packages[$package->getPackageKey()] = $package;
			}
		}
		$view->assign('packages', $this->packages);

		if ($this->request->hasArgument('package')) {
			$this->packageKey = $this->request->getArgument('package');
			$this->package = $this->packages[$this->packageKey];
			$this->view->assign('currentPackage', $this->packages[$this->packageKey]);
		}

		if ($this->request->hasArgument('bean')) {
			$this->bean = $this->request->getArgument('bean');
			$this->configuration = $this->beans[$this->bean];
			$this->view->assign('bean', $this->beans[$this->bean]);
		}

		if (isset($this->configuration['source'])) {
			$source = new $this->configuration['source']($this->configuration, $this->packageKey);
			$view->assign('sources', $source->getSources());
		}
	}

	public function getVariableImplementation($variableType) {
		if (isset($this->variableImplementations[$variableType])) {
			return $this->variableImplementations[$variableType];
		}
		return $variableType;
	}

	public function getVariables() {
		return array_merge($this->request->getArguments(), array(
			'packageKey' => $this->package->getPackageKey(),
			'namespace' => $this->package->getNamespace(),
			'packagePath' => $this->package->getPackagePath(),
			'classesPath' => $this->package->getClassesNamespaceEntryPath(),
			'resourcesPath' => $this->package->getResourcesPath(),
			'configurationPath' => $this->package->getConfigurationPath(),
			'documentationPath' => $this->package->getDocumentationPath()
		));
	}

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * @param string $source
	 */
	public function editAction($source = NULL) {
		if ($source !== NULL) {
			$this->view->assign('source', $source);
			$sourceImplemention = new $this->configuration['source']($this->configuration);
			$sourceImplemention->setSource($source);
		}
		$variales = array();
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			$variableType = isset($variable['type']) ? $variable['type'] : 'ask';
			$variableImplementation = $this->getVariableImplementation($variableType);
			$variable = new $variableImplementation($variable, array());
			$variable->setName($variableName);
			if ($source !== NULL) {
				$variable->setSource($sourceImplemention);
				$variable->setValue($sourceImplemention->getValue($variableName));
			}
			$variables[$variableName] = $variable;
		}
		$this->view->assign('variables', $variables);
	}

	/**
	 */
	public function newAction() {
		$variales = array();
		foreach ($this->configuration['variables'] as $variableName => $variable) {
			$variableType = isset($variable['type']) ? $variable['type'] : 'ask';
			$variableImplementation = $this->getVariableImplementation($variableType);
			$variable = new $variableImplementation($variable, array());
			$variable->setName($variableName);
			$variables[$variableName] = $variable;
		}
		$this->view->assign('variables', $variables);
	}

	/**
	 * @param string $source
	 */
	public function saveAction($source = NULL) {
		$variables = $this->getVariables();
        foreach ($this->configuration['files'] as $file) {
			$builderClassName = '\Famelo\Bean\Builder\FluidBuilder';
			if (isset($file['builder'])) {
				$builderClassName = $file['builder'];
			}
			$builder = new $builderClassName($file);
			$builder->save($source, $variables);
        }
        $this->redirect('index', NULL, NULL, array(
        	'bean' => $this->request->getArgument('bean'),
        	'package' => $this->request->getArgument('package')
        ));
	}
}