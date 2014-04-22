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
class PhpBuilder extends AbstractBuilder {
	/**
	 * @var \PhpParser\Parser
	 */
	protected $parser;

	/**
	 * @var \PhpParser\BuilderFactory
	 */
	protected $factory;

	/**
	 * @var \PhpParser\PrettyPrinterAbstract
	 */
	protected $printer;

	public function __construct($configuration) {
		parent::__construct($configuration);
		$this->parser  = new Parser(new Lexer);
		$this->factory = new BuilderFactory;
		$this->printer = new TYPO3;
	}

	public function plant($variables = array()) {
		$source = $this->configuration['template'];
		$target = $this->configuration['target'];

		foreach ($variables as $key => $value) {
			if (is_object($value) || is_array($value)) {
				unset($variables[$key]);
			}
		}

		$target = $this->generateFileName($target, $variables);
		$template = file_get_contents($source);
		$template = new Template($this->parser, $template);
		$node = $template->getStmts($variables);

		$code = $this->printCode($node);

		if (!is_dir(dirname($target))) {
			Files::createDirectoryRecursively(dirname($target));
		}
		if (!file_exists($target)) {
			file_put_contents($target, $code);
			return array('<info>Created: ' . $target . '</info>');
		}
	}

	public function getPartial($partial, $replacements) {
		$template = file_get_contents($this->configuration['partialPath'] . $partial . '.php');
		$template = new Template($this->parser, $template);
		// var_dump($partial, $replacements);
		$node = $template->getStmts($replacements);
		return $node[0]->stmts;
	}

	public function printCode($stmts) {
		$code = "<?php\n" . $this->printer->prettyPrint($stmts);

	    $code = preg_replace("/;\n\n\nuse /", ";\nuse ", $code);
	    $code = preg_replace("/\*\/\n\/\*\*/", "*/\n\n/**", $code);
	    $code = preg_replace("/\{\n\n\n\}/s", "{}", $code);
		$code = preg_replace("/\s*$/", "", $code);
		$code = preg_replace("/array\\([\s\n]+/s", "array(", $code);

		$lines = explode("\n", $code);
	    foreach ($lines as $key => $line) {
	    	$lines[$key] = rtrim($line);
	    }
	    $code = implode("\n", $lines);

		return str_replace("<<<newline>>>", "\\n", $code);
	}

	public function getClassProperties($stmt) {
		$properties = array();
		foreach ($stmt->stmts as $classStmt) {
			if ($classStmt instanceof \PhpParser\Node\Stmt\Property) {
				$properties[] = $classStmt;
			}
		}
		return $properties;
	}

	public function getClassMethods($stmt) {
		$classMethods = array();
		foreach ($stmt->stmts as $classStmt) {
					if ($classStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
						$classMethods[$classStmt->name] = $classStmt;
					}
		}
		return $classMethods;
	}

	public function getClass($stmts) {
		if ($stmts[0] instanceof \PhpParser\Node\Stmt\Namespace_) {
			return $this->getClass($stmts[0]->stmts);
		}
		foreach ($stmts as $stmt) {
			if ($stmt instanceof \PhpParser\Node\Stmt\Class_) {
				return $stmt;
			}
		}
	}

	public function getClassName($stmts) {
		return '\\' . $stmts[0]->name . '\\' . $this->getClass($stmts)->name;
	}
}