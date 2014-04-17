<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\printer\Standard;
use PhpParser\Template;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class FluidBuilder extends AbstractBuilder {
	/**
	 * @var \TYPO3\Fluid\View\StandaloneView
	 * @Flow\Inject
	 */
	protected $view;

	public function plant($variables = array()) {
		$source = $this->configuration['template'];
		$target = $this->configuration['target'];

		$this->view->setTemplatePathAndFilename($source);
		$this->view->assignMultiple($variables);

		$content = $this->view->render();

		$target = $this->generateFileName($target, $variables);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}
		if (!file_exists($target)) {
			$this->outputLine('<info>Created: ' . $target . '</info>');
			// echo $content;
			file_put_contents($target, $content);
		}
	}

	// public function append($source, $target, $variables = array()) {
	// 	$this->view->setTemplatePathAndFilename($source);
	// 	$this->view->assignMultiple($variables);

	// 	$content = $this->view->render();

	// 	$target = $this->generateFileName($target, $variables);

	// 	if (!is_dir(dirname($target))) {
	// 		Files::createDirectoryRecursively(dirname($target));
	// 	}

	// 	if (file_exists($target)) {
	// 		$content = file_get_contents($target) . chr(10) . $content;
	// 	}

	// 	$this->outputLine('<info>Created: ' . $target . '</info>');
	// 	file_put_contents($target, $content);
	// }
}