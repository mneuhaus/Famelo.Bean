<?php

namespace Famelo\Bean\Bean;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Util\Inflector;
use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class ModelBean extends AbstractBean {
	/**
	 * @var \TYPO3\Flow\Package\PackageManager
	 */
	protected $packageManager;

	/**
	* @param \TYPO3\Flow\Package\PackageManagerInterface $packageManager
	* @return void
	*/
	public function injectPackageManager(\TYPO3\Flow\Package\PackageManagerInterface $packageManager) {
		$this->packageManager =  $packageManager;
	}

	// public function run() {
	// 	$this->builder->setPartialPath('resource://Famelo.Bean/Private/Beans/Model/');
	// 	parent::runt();
	// 	// $this->fetchVariables();

	// 	// $fields = $variables['fields'];
	// 	// unset($variables['fields']);

	// 	// $template = file_get_contents($source);
	// 	// $template = new Template($this->parser, $template);
	// 	// $node = $template->getStmts($variables);
	// 	// $this->controller->outputLine('<info>Source: ' . $source . '</info>');

	// 	// foreach ($fields as $field) {
	// 	// 	$this->addField($node, $field);
	// 	// }

	// 	// foreach ($this->configuration['files'] as $file) {
 //  //           $this->builder->buildNew($file['template'], $file['target'], $this->variables);
 //  //       }
	// }

	public function grow() {
		$this->classFiles = array();
		foreach ($this->packageManager->getAvailablePackages() as $package) {
			foreach ($package->getClassFiles() as $className => $classFile) {
				$this->classFiles[$className] = $package->getPackagePath() . $classFile;
			}
		}

		$className = $this->chooseClassNameAnnotatedWith(
			'<q>Which Entity do you want to grow?</q>',
			'\TYPO3\Flow\Annotations\Entity'
		);

		$fields = array();
		while (($field = $this->createField()) !== FALSE) {
			$fields[] = $field;
		    $this->previewFields($fields);
		}

		$fileName = $this->classFiles[$className];
		$code = file_get_contents($fileName);

		try {
			$code = str_replace("\\n", "<<<newline>>>", $code);
		    // parse
		    $stmts = $this->parser->parse($code);

		    foreach ($fields as $field) {
		    	$this->addField($stmts, $field);
		    }

		    // pretty print
		    $code = $this->printCode($stmts);
			file_put_contents($fileName, $code);
		} catch (Error $e) {
		    echo 'Parse Error: ', $e->getMessage();
		}
	}
}