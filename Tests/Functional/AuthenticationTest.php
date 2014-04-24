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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Party\Domain;

/**
 */
class AuthenticationTest extends BaseTest {
	/**
	 * @var array
	 * @Flow\Inject(setting="DefaultVariables")
	 */
	protected $variables;

	/**
	* @test
	* @group focus
	*/
	public function createAuthenticationFiles() {
		$this->setAnswers(array(
			'test.package',		// Package

			'authentication',	// What to do
			'test',				// modelName

			'exit'				// exit command
		));
		$this->controller->plantCommand();

		$this->assertClassExists('Test\Package\Controller\LoginController');
		$this->assertFileExists($this->packagePath . '/Resources/Private/Templates/Login/Index.html');
		$this->assertClassExists('Test\Package\Command\TestCommandController');
		$this->assertClassExists('Test\Package\Domain\Model\User');
		$this->assertClassExists('Test\Package\Domain\Repository\UserRepository');

		$variables = $this->configurationManager->getConfiguration('Settings', 'Famelo.Bean.DefaultVariables');

		$this->assertRouteEquals(
			$this->packagePath . '/Configuration/Routes.yaml',
			'Login',
			array(
				'name' => 'Login',
				'uriPattern' => 'login',
				'defaults' => array(
					'@format' => 'html',
					'@controller' => 'Login',
					'@action' => 'index',
					'@package' => 'Test.Package'
				)
			)
		);

		$this->assertRouteEquals(
			$variables['FLOW_PATH_CONFIGURATION'] . 'Routes.yaml',
			'TestProvider',
			array(
				'name' => 'TestProvider',
				'uriPattern' => 'test/<TestSubroutes>',
				'defaults' => array(
					'@format' => 'html'
				),
				'subRoutes' => array(
					'TestProvider' => array(
						'package' => 'Test.Package'
					)
				)
			)
		);

		$this->assertRouteOrder(
			$variables['FLOW_PATH_CONFIGURATION'] . 'Routes.yaml',
			'TestProvider',
			'Flow'
		);

		$this->assertSettings(
			$this->packagePath . '/Configuration/Settings.yaml',
			'TYPO3.Flow.security',
			array(
				'enable' => true,
				'authentication' => array(
					'providers' => array(
						'TestProvider' => array(
							'provider' => 'PersistedUsernamePasswordProvider',
							'entryPoint' => 'WebRedirect',
							'entryPointOptions' => array(
								'uri' => 'test/login'
							)
						)
					)
				)
			)
		);
	}
}
