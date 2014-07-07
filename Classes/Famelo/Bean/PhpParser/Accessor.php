<?php
namespace Famelo\Bean\PhpParser;

use Famelo\Bean\PhpParser\Reflection\ReflectionProperty;

/**
 *
 */
class Accessor {
	public function getStatement($statements, $className) {
		if ($stmts[0] instanceof \PhpParser\Node\Stmt\Namespace_) {
			return $this->getClass($stmts[0]->stmts);
		}
		foreach ($stmts as $stmt) {
			if ($stmt instanceof \PhpParser\Node\Stmt\Class_) {
				return $stmt;
			}
		}
	}
}
