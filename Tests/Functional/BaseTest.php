<?php
namespace Famelo\Bean\Tests\Functional;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Famelo\Bean\Bean\DefaultBean;
use Famelo\Bean\PhpParser\Reflection\ReflectionClass;
use PhpParser\Lexer;
use PhpParser\Parser;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
abstract class BaseTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	*/
	public function setUp() {
		parent::setUp();
		$this->configurationManager	= $this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->controller  			= $this->objectManager->get('Famelo\Bean\Command\BeanCommandController');

		$this->reflectionService	= $this->objectManager->get('Famelo\Bean\Reflection\RuntimeReflectionService');
		$existingEntityFilename = FLOW_PATH_PACKAGES . 'Application/Famelo.Bean/Tests/Functional/Fixtures/ExistingEntity.php';
		$this->reflectionService->addFilenameForClassName('\Famelo\Bean\Tests\Functional\Fixtures\ExistingEntity', $existingEntityFilename);

		$this->buildPath = FLOW_PATH_DATA . 'Temporary/Testing/Build';
		$this->packagePath = FLOW_PATH_DATA . 'Temporary/Testing/Build/Package/';
		$this->reset();

		$this->interaction 			= $this->getMock('Famelo\Bean\Service\InteractionService');
		$this->controller->injectInteraction($this->interaction);


        $this->consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('cyan', 'black');
        $this->consoleOutput->getFormatter()->setStyle('q', $style);

		$packageManager 			= $this->getMock('TYPO3\Flow\Package\PackageManager');
		$this->controller->injectPackageManager($packageManager);
		$package 					= $this->getMock('TYPO3\Flow\Package\Package', array(), array($packageManager, 'Test.Package', $this->packagePath));
        $packageManager ->expects($this->any())
             			->method('getAvailablePackages')
             			->will($this->returnValue(array($package)));
        $manifest = new \stdClass();
        $manifest->type = 'typo3';
        $package->expects($this->any())
             	->method('getComposerManifest')
             	->will($this->returnValue($manifest));
        $package->expects($this->any())
             	->method('getPackageKey')
             	->will($this->returnValue('Test.Package'));
        $package->expects($this->any())
             	->method('getNamespace')
             	->will($this->returnValue('Test\Package'));
        $package->expects($this->any())
             	->method('getPackagePath')
             	->will($this->returnValue($this->packagePath));
        $package->expects($this->any())
             	->method('getClassesNamespaceEntryPath')
             	->will($this->returnValue($this->packagePath . 'Classes/Test/Package/'));
        $package->expects($this->any())
             	->method('getResourcesPath')
             	->will($this->returnValue($this->packagePath . 'Resources/'));
        $package->expects($this->any())
             	->method('getConfigurationPath')
             	->will($this->returnValue($this->packagePath . 'Configuration/'));
        $package->expects($this->any())
             	->method('getDocumentationPath')
             	->will($this->returnValue($this->packagePath . 'Documentation/'));
	}

	/**
	 */
	public function reset() {
		$this->modelName = NULL;
		$this->packageName = NULL;
		$this->properties = NULL;

		if (is_dir($this->packagePath)) {
			$tmpDir = $this->buildPath . '.' . time();
			rename($this->buildPath, $this->buildPath . '.' . time());
			Files::removeDirectoryRecursively($tmpDir);
		}
		Files::createDirectoryRecursively($this->packagePath);

		$composerFileName = $this->packagePath . 'composer.json';
		file_put_contents($composerFileName, '{
    "name": "test/package",
    "type": "typo3-flow-package"
    "require": {
        "typo3/flow": "2.1.*"
    },
    "autoload": {
        "psr-0": {
            "Test\\Package": "Classes"
        }
    }
}');

		$existingEntityFilename = FLOW_PATH_PACKAGES . 'Application/Famelo.Bean/Tests/Functional/Fixtures/ExistingEntity.php';
		file_put_contents($existingEntityFilename, '<?php
namespace Famelo\Bean\Tests\Functional\Fixtures;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ExistingEntity {

    /**
     * @var string
     *
     */
    protected $existingProperty;

    /**
     * Gets existingProperty.
     *
     * @return string $existingProperty
     */
    public function getExistingProperty() {
        return $this->existingProperty;
    }

    /**
     * Sets the existingProperty.
     *
     * @param string $existingProperty
     */
    public function setExistingProperty($existingProperty) {
        $this->existingProperty = $someString;
    }

}');

		$globalRoutes = $this->buildPath . '/Configuration/Routes.yaml';
		Files::createDirectoryRecursively(dirname($globalRoutes));
		file_put_contents($globalRoutes, '
-
  name: Flow
  uriPattern: \'<FlowSubroutes>\'
  defaults:
    \'@format\': html
  subRoutes:
    FlowSubroutes:
      package: TYPO3.Flow
');

	}

	public function setAnswers($answers, $debug = FALSE) {
		$this->interaction
				->expects($this->exactly(count($answers)))
				->method('ask')
				->will($this->returnCallback(function($question) use(&$answers, $debug) {
           			$answer = array_shift($answers);
					if ($debug === TRUE) {
            			$this->consoleOutput->write($question);
            			$this->consoleOutput->writeln($answer);
            		}
           			return $answer;
        }));
        $this->interaction->expects($this->any())
             	->method('output')
				->will($this->returnCallback(function($output) use($debug) {
					if ($debug === TRUE) {
            			$this->consoleOutput->write($output);
            		}
        }));
        $this->interaction->expects($this->any())
             	->method('outputLine')
				->will($this->returnCallback(function($output) use($debug) {
					if ($debug === TRUE) {
            			$this->consoleOutput->writeln($output);
            		}
        }));
	}

	public function assertClassExists($className, $classFileName = NULL) {
		if ($classFileName === NULL) {
			$classFileName = '/Classes/' . str_replace('\\', '/', $className) . '.php';
		}
		$this->assertFileExists($this->packagePath . $classFileName);

		require_once($this->packagePath . $classFileName);
		$this->assertTrue(class_exists($className), '"' . $className . '" does not exists');
	}

	public function assertClassHasProperty($className, $propertyName, $propertyType, $fileName = NULL) {
		if ($fileName !== NULL) {
			$fileName = $this->packagePath . $fileName;
		}
		$reflection = new ReflectionClass($className, $fileName);
		$this->assertTrue($reflection->hasProperty($propertyName),
			'"' . $className . '::$' . $propertyName . '" not found'
		);

		$property = $reflection->getProperty($propertyName);
		$this->assertTrue(stristr($property->getDocComment(), '@var ' . $propertyType) !== FALSE,
			'"' . $className . '::$' . $propertyName . '" not of type "' . $propertyType . '"'
		);
	}

	public function assertClassHasNotProperty($className, $propertyName, $fileName = NULL) {
		if ($fileName !== NULL) {
			$fileName = $this->packagePath . $fileName;
		}
		$reflection = new ReflectionClass($className, $fileName);
		$this->assertFalse($reflection->hasProperty($propertyName),
			'"' . $className . '::$' . $propertyName . '" found'
		);
	}

	public function assertClassHasDocComment($className, $propertyName, $docComment, $fileName = NULL) {
		if ($fileName !== NULL) {
			$fileName = $this->packagePath . $fileName;
		}
		$reflection = new ReflectionClass($className, $fileName);
		$property = $reflection->getProperty($propertyName);
		$this->assertTrue(stristr($property->getDocComment(), $docComment) !== FALSE,
			'"' . $className . '::$' . $propertyName . '" does not have "' . $docComment . '"'
		);
	}

	public function assertClassHasMethod($className, $methodName, $fileName = NULL) {
		if ($fileName !== NULL) {
			$fileName = $this->packagePath . $fileName;
		}
		$reflection = new ReflectionClass($className, $fileName);
		$this->assertTrue($reflection->hasMethod($methodName),
			'"' . $className . '::$' . $methodName . '()" not found'
		);
	}

	public function assertClassHasNotMethod($className, $methodName, $fileName = NULL) {
		if ($fileName !== NULL) {
			$fileName = $this->packagePath . $fileName;
		}
		$reflection = new ReflectionClass($className, $fileName);
		$this->assertFalse($reflection->hasMethod($methodName),
			'"' . $className . '::$' . $methodName . '()" not found'
		);
	}

	public function assertPolicyExists($policyFile, $policyName, $policyDefinition) {
		$this->assertFileExists($policyFile);
		$policy = Yaml::parse(file_get_contents($policyFile));

		$this->assertTrue(isset($policy['resources']['methods'][$policyName]), 'Policy "' . $policyName . '" does not exist');

		$this->assertEquals($policy['resources']['methods'][$policyName], $policyDefinition);
	}

	public function assertRouteEquals($routeFile, $routeName, $routeDefinition) {
		$this->assertFileExists($routeFile);
		$routes = Yaml::parse(file_get_contents($routeFile));

		foreach ($routes as $route) {
			if ($route['name'] === $routeName) {
				$this->assertEquals($route, $routeDefinition);
				return;
			}
		}
		$this->fail('Route "' . $routeName . '" does not exist');
	}

	public function assertRouteOrder($routeFile, $firstRoute, $secondRoute) {
		$this->assertFileExists($routeFile);
		$routes = Yaml::parse(file_get_contents($routeFile));

		$firstRouteFound = FALSE;
		$secondRouteFound = FALSE;
		foreach ($routes as $route) {
			if ($route['name'] === $firstRoute) {
				$firstRouteFound = TRUE;
			}
			if ($route['name'] === $secondRoute) {
				if ($firstRouteFound === FALSE) {
					$this->fail('Route "' . $firstRoute . '" comes before "' . $secondRoute . '"');
				}
				$secondRouteFound = TRUE;
			}
		}
		if ($firstRouteFound === FALSE) {
			$this->fail('Route "' . $firstRoute . '" not found');
		}
		if ($secondRouteFound === FALSE) {
			$this->fail('Route "' . $secondRoute . '" not found');
		}
	}

	public function assertSettings($settingsFile, $settingsPath, $settingsDefinition) {
		$this->assertFileExists($settingsFile);
		$settings = Yaml::parse(file_get_contents($settingsFile));

		$parts = explode('.', $settingsPath);
		foreach ($parts as $part) {
			$this->assertTrue(isset($settings[$part]),
				'Settings Path "' . $settingsPath . '" does not exist');
			$settings = $settings[$part];
		}

		$this->assertEquals($settings, $settingsDefinition);
	}

	public function assertNodeTypeExists($nodeTypesFile, $nodeTypeName) {
		$this->assertFileExists($nodeTypesFile);
		$nodeTypes = Yaml::parse(file_get_contents($nodeTypesFile));

		$this->assertTrue(isset($nodeTypes[$nodeTypeName]),
			'NodeType "' . $nodeTypeName . '" does not exist');
	}

	public function assertPrototypeExists($typoScriptFile, $prototypeName) {
		$this->assertFileExists($typoScriptFile);
		$typoScript = file_get_contents($typoScriptFile);

		$this->assertTrue(stristr($typoScript, 'prototype('. $prototypeName . ')') !== FALSE,
			'Prototype "' . $prototypeName . '" does not exist');
	}
}
