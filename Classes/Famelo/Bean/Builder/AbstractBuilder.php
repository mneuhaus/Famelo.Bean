<?php

namespace Famelo\Bean\Builder;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Bean\PhpParser\Printer\TYPO3;
use Famelo\Bean\Traits\InteractionTrait;
use Famelo\Common\Command\AbstractInteractiveCommandController;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Template;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Flow\Utility\Files;

/**
 */
class AbstractBuilder {
	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var \Famelo\Bean\Fluid\KickstartView
	 * @Flow\Inject
	 */
	protected $view;

	/**
	 * @var \TYPO3\Kickstart\Utility\Inflector
	 * @Flow\Inject
	 */
	protected $inflector;

	/**
	 * @var \Famelo\Bean\Reflection\RuntimeReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * The flash messages. Use $this->flashMessageContainer->addMessage(...) to add a new Flash
	 * Message.
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	public function injectInteraction($interaction) {
		$this->interaction = $interaction;
	}

	public function __construct($configuration) {
		$this->configuration = $configuration;
	}

	public function generateFileName($target, $variables) {
		$this->view->assignMultiple($variables);
		return str_replace('//', '/', $this->view->renderString($target));
	}

	/**
	 * Creates a Message object and adds it to the FlashMessageContainer.
	 *
	 * This method should be used to add FlashMessages rather than interacting with the container directly.
	 *
	 * @param string $messageBody text of the FlashMessage
	 * @param string $messageTitle optional header of the FlashMessage
	 * @param string $severity severity of the FlashMessage (one of the \TYPO3\Flow\Error\Message::SEVERITY_* constants)
	 * @param array $messageArguments arguments to be passed to the FlashMessage
	 * @param integer $messageCode
	 * @return void
	 * @throws \InvalidArgumentException if the message body is no string
	 * @see \TYPO3\Flow\Error\Message
	 * @api
	 */
	public function addFlashMessage($messageBody, $messageTitle = '', $severity = Message::SEVERITY_OK, array $messageArguments = array(), $messageCode = NULL) {
		if (!is_string($messageBody)) {
			throw new \InvalidArgumentException('The message body must be of type string, "' . gettype($messageBody) . '" given.', 1243258395);
		}
		switch ($severity) {
			case Message::SEVERITY_NOTICE:
				$message = new \TYPO3\Flow\Error\Notice($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			case Message::SEVERITY_WARNING:
				$message = new \TYPO3\Flow\Error\Warning($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			case Message::SEVERITY_ERROR:
				$message = new \TYPO3\Flow\Error\Error($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			default:
				$message = new Message($messageBody, $messageCode, $messageArguments, $messageTitle);
			break;
		}
		$this->flashMessageContainer->addMessage($message);
	}
}