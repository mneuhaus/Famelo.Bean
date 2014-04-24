<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 */
class RouteBuilder extends YamlBuilder {
	public function merge($sourceData, $targetData) {
		if (isset($this->configuration['beforeRoute'])) {
			$newRoutes = array();
			foreach ($sourceData as $route) {
				if ($route['name'] === $this->configuration['beforeRoute']) {
					foreach ($targetData as $targetRoute) {
						$newRoutes[] = $targetRoute;
					}
				}
				$newRoutes[] = $route;
			}
			return $newRoutes;
		}
		return parent::merge($sourceData, $targetData);
	}
}