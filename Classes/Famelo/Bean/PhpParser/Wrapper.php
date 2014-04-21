<?php
namespace Famelo\Bean\PhpParser;

use Famelo\Bean\PhpParser\Reflection\ReflectionProperty;

/**
 *
 */
class Wrapper {
	public function getProperties() {
		$stmt = $this->getClass($this->statements);
		$properties = array();
		foreach ($stmt->stmts as $classStmt) {
			if ($classStmt instanceof \PhpParser\Node\Stmt\Property) {

				$property = new ReflectionProperty($classStmt);
				$properties[$property->getName()] = $property;
			}
		}
		return $properties;
	}

	public function getMethods() {
		$stmt = $this->getClass($this->statements);
		$methods = array();
		foreach ($stmt->stmts as $classStmt) {
			if ($classStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
				$methods[$classStmt->name] = $classStmt;
			}
		}
		return $methods;
	}

	public function getClass($statements) {
		if ($statements[0] instanceof \PhpParser\Node\Stmt\Namespace_) {
			return $this->getClass($statements[0]->stmts);
		}
		foreach ($statements as $stmt) {
			if ($stmt instanceof \PhpParser\Node\Stmt\Class_) {
				return $stmt;
			}
		}
	}
}
