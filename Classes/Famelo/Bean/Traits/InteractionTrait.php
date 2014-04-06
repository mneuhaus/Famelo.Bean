<?php
namespace Famelo\Bean\Traits;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Kickstart".      *
 *                                                                        *
 *                                                                        */

use Famelo\Common\Command\AbstractInteractiveCommandController;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\Package;
use TYPO3\Flow\Utility\Files;

trait InteractionTrait {
    private static $stty;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output;

    /**
     * Constructs the controller
     *
     */
    public function initialize() {
        if ($this->output === NULL) {
            $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $this->dialog = new \Symfony\Component\Console\Helper\DialogHelper();
            $this->progress = new \Symfony\Component\Console\Helper\ProgressHelper();
            $this->table = new \Symfony\Component\Console\Helper\TableHelper();

            $style = new OutputFormatterStyle('cyan', 'black');
            $this->output->getFormatter()->setStyle('q', $style);
        }
    }

    /**
     * Outputs specified text to the console window
     * You can specify arguments that will be passed to the text via sprintf
     * @see http://www.php.net/sprintf
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @return void
     */
    public function output($text, array $arguments = array()) {
        $this->initialize();
        if ($arguments !== array()) {
            $text = vsprintf($text, $arguments);
        }
        $this->output->write($text);
    }

    /**
     * Outputs specified text to the console window and appends a line break
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @return void
     * @see output()
     * @see outputLines()
     */
    public function outputLine($text = '', array $arguments = array()) {
        $this->output($text . PHP_EOL, $arguments);
    }

    /**
     * Asks the user to select a value.
     *
     * @param string|array    $question     The question to ask
     * @param array           $choices      List of choices to pick from
     * @param Boolean         $default      The default answer if the user enters nothing
     * @param Boolean|integer $attempts Max number of times to ask before giving up (false by default, which means infinite)
     * @param string          $errorMessage Message which will be shown if invalid value from choice list would be picked
     * @param Boolean         $multiselect  Select more than one value separated by comma
     *
     * @return integer|string|array The selected value or values (the key of the choices array)
     *
     * @throws \InvalidArgumentException
     */
    public function select($question, $choices, $default = null, $attempts = false, $errorMessage = 'Value "%s" is invalid', $multiselect = false) {
        $this->initialize();
        return $this->dialog->select($this->output, $question, $choices, $default, $attempts, $errorMessage, $multiselect);
    }

    /**
     * Asks a question to the user.
     *
     * @param OutputInterface $output       An Output instance
     * @param string|array    $question     The question to ask
     * @param string          $default      The default answer if none is given by the user
     * @param array           $autocomplete List of values to autocomplete
     * @param boolean         $fuzzy        Set this argument to TRUE to match the autocomplete based on similar_text
     *
     * @return string The user answer
     *
     * @throws \RuntimeException If there is no data to read in the input stream
     */
    public function ask($question, $default = null, array $autocomplete = null, $fuzzy = FALSE) {
        $this->initialize();
        if ($fuzzy === FALSE) {
            return $this->dialog->ask($this->output, $question, $default, $autocomplete);
        }

        $this->output->write($question);

        $inputStream = STDIN;

        if (null === $autocomplete || !$this->hasSttyAvailable()) {
            $ret = fgets($inputStream, 4096);
            if (false === $ret) {
                throw new \RuntimeException('Aborted');
            }
            $ret = trim($ret);
        } else {
            $ret = '';

            $i = 0;
            $ofs = -1;
            $matches = $autocomplete;
            $numMatches = count($matches);

            $sttyMode = shell_exec('stty -g');

            // Disable icanon (so we can fread each keypress) and echo (we'll do echoing here instead)
            shell_exec('stty -icanon -echo');

            // Add highlighted text style
            $this->output->getFormatter()->setStyle('hl', new OutputFormatterStyle('magenta'));

            // Read a keypress
            while (!feof($inputStream)) {
                $c = fread($inputStream, 1);

                // Backspace Character
                if ("\177" === $c) {
                    $i--;
                    $ret = substr($ret, 0, $i);
                } elseif ("\033" === $c) { // Did we read an escape sequence?
                    $c .= fread($inputStream, 2);

                    // A = Up Arrow. B = Down Arrow
                    if ('A' === $c[2] || 'B' === $c[2]) {
                        if ('A' === $c[2] && -1 === $ofs) {
                            $ofs = 0;
                        }

                        if (0 === $numMatches) {
                            continue;
                        }

                        $ofs += ('A' === $c[2]) ? -1 : 1;
                        $ofs = ($numMatches + $ofs) % $numMatches;
                    }
                } elseif (ord($c) < 32) {
                    if ("\t" === $c || "\n" === $c) {
                        if ($numMatches > 0 && -1 !== $ofs) {
                            $ret = $matches[$ofs];
                            // Echo out remaining chars for current match
                            // $this->output->write(substr($ret, $i));
                            // $this->output->write(' => <hl>' . $matches[$ofs] . '</hl>');
                            $i = strlen($ret);
                        }

                        if ("\n" === $c) {
                            $this->output->write($c);
                            break;
                        }

                        $numMatches = 0;
                    }

                    continue;
                } else {
                    // $this->output->write($c);
                    $ret .= $c;
                    $i++;
                }

                $numMatches = 0;

                $unorderedMatches = array();
                foreach ($autocomplete as $value) {
                    // If typed characters match the beginning chunk of value (e.g. [AcmeDe]moBundle)
                    $similarity = similar_text($value, $ret);
                    if ($similarity > 0) {
                        $unorderedMatches[($similarity * 100) + $numMatches++] = $value;
                    }
                }

                krsort($unorderedMatches);
                $numMatches = 0;
                $ofs = 0;
                $matches = array();
                foreach ($unorderedMatches as $value) {
                    $matches[$numMatches++] = $value;
                }

                // Erase characters from cursor to end of line
                $this->output->write("\033[K");

                if ($numMatches > 0 && -1 !== $ofs) {
                    // Save cursor position
                    $this->output->write("\0337");
                    // Write highlighted text
                    $offset = 0;
                    $match = $matches[$ofs];
                    foreach(str_split($ret) as $value) {
                        $pos = strpos($match, $value, $offset);
                        if ($pos !== FALSE) {
                            $match = substr_replace($match, '<<' . $value . '>>', $pos, 1);
                            $offset = $pos + 4;
                        }
                    }
                    $match = str_replace('<<', '<hl>', $match);
                    $match = str_replace('>>', '</hl>', $match);
                    $this->output->write($match);
                    // Restore cursor position
                    $this->output->write("\0338");
                }
            }

            // Reset stty so it behaves normally again
            shell_exec(sprintf('stty %s', $sttyMode));
        }

        if (array_values($autocomplete) !== $autocomplete) {
            var_dump($autocomplete);
        }

        return strlen($ret) > 0 ? $ret : $default;
    }

    /**
     * Asks a confirmation to the user.
     *
     * The question will be asked until the user answers by nothing, yes, or no.
     *
     * @param string|array    $question The question to ask
     * @param Boolean         $default  The default answer if the user enters nothing
     *
     * @return Boolean true if the user has confirmed, false otherwise
     */
    public function askConfirmation($question, $default = true) {
        $this->initialize();
        return $this->dialog->askConfirmation($this->output, $question, $default);
    }

    /**
     * Asks a question to the user, the response is hidden
     *
     * @param string|array    $question The question
     * @param Boolean         $fallback In case the response can not be hidden, whether to fallback on non-hidden question or not
     *
     * @return string         The answer
     *
     * @throws \RuntimeException In case the fallback is deactivated and the response can not be hidden
     */
    public function askHiddenResponse($question, $fallback = true) {
        $this->initialize();
        return $this->dialog->askHiddenResponse($this->output, $question, $fallback);
    }

    /**
     * Asks for a value and validates the response.
     *
     * The validator receives the data to validate. It must return the
     * validated data when the data is valid and throw an exception
     * otherwise.
     *
     * @param string|array    $question     The question to ask
     * @param callable        $validator    A PHP callback
     * @param integer         $attempts     Max number of times to ask before giving up (false by default, which means infinite)
     * @param string          $default      The default answer if none is given by the user
     * @param array           $autocomplete List of values to autocomplete
     *
     * @return mixed
     *
     * @throws \Exception When any of the validators return an error
     */
    public function askAndValidate($question, $validator, $attempts = false, $default = null, array $autocomplete = null) {
        $this->initialize();
        return $this->dialog->askAndValidate($this->output, $question, $validator, $attempts, $default, $autocomplete);
    }

    /**
     * Asks for a value, hide and validates the response.
     *
     * The validator receives the data to validate. It must return the
     * validated data when the data is valid and throw an exception
     * otherwise.
     *
     * @param OutputInterface $output    An Output instance
     * @param string|array    $question  The question to ask
     * @param callable        $validator A PHP callback
     * @param integer         $attempts  Max number of times to ask before giving up (false by default, which means infinite)
     * @param Boolean         $fallback  In case the response can not be hidden, whether to fallback on non-hidden question or not
     *
     * @return string         The response
     *
     * @throws \Exception        When any of the validators return an error
     * @throws \RuntimeException In case the fallback is deactivated and the response can not be hidden
     *
     */
    public function askHiddenResponseAndValidate($question, $validator, $attempts = false, $fallback = true) {
        $this->initialize();
        return $this->dialog->askHiddenResponseAndValidate($this->output, $question, $validator, $attempts, $fallback);
    }

    /**
     * Starts the progress output.
     *
     * @param integer         $max    Maximum steps
     */
    public function progressStart($max = null) {
        $this->initialize();
        $this->progress->start($this->output, $max);
    }

    /**
     * Advances the progress output X steps.
     *
     * @param integer $step   Number of steps to advance
     * @param Boolean $redraw Whether to redraw or not
     *
     * @throws \LogicException
     */
    public function progressAdvance($step = 1, $redraw = false) {
        $this->initialize();
        $this->progress->advance($step, $redraw);
    }

    /**
     * Sets the current progress.
     *
     * @param integer $current The current progress
     * @param Boolean $redraw  Whether to redraw or not
     *
     * @throws \LogicException
     */
    public function progressSet($current, $redraw = false) {
        $this->initialize();
        $this->progress->setCurrent($current, $redraw);
    }

    /**
     * Finishes the progress output.
     */
    public function progressFinish() {
        $this->initialize();
        $this->progress->finish();
    }

    public function table($rows, $headers = NULL) {
        $this->initialize();
        if ($headers !== NULL) {
            $this->table->setHeaders($headers);
        }
        $this->table->setRows($rows);
        $this->table->render($this->output);
    }

    protected function hasSttyAvailable() {
        if (null !== self::$stty) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = $exitcode === 0;
    }
}