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
use PhpParser\Template;
use PhpParser\printer\Standard;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class PolicyBuilder extends FluidBuilder {
	public function append($variables = array()) {
		$source = $this->configuration['template'];
		$target = $this->configuration['target'];

		$this->view->setTemplatePathAndFilename($source);
		$this->view->assignMultiple($variables);

		$content = $this->view->render();

		$targetData = Yaml::parse($content);

		$target = $this->generateFileName($target, $variables);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}

		$changes = array();
		if (file_exists($target)) {
			$content = file_get_contents($target);
			$sourceData = Yaml::parse($content);
			$targetData = array_merge_recursive($sourceData, $targetData);
			$changes[] = '<info>Updated: ' . $target . '</info>';
		} else {
			$changes[] = '<info>Created: ' . $target . '</info>';
		}
		file_put_contents($target, Yaml::dump($targetData, 10, 2));
		return $changes;
	}
}