<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

class ConfigureCommand extends AbstractCommand
{
    /**
     * @var InputInterface
     */
    private $oInput;

    /**
     * @var OutputInterface
     */
    private $oOutput;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('configure')
            ->setDescription('Configure application and add necessary configs')
        ;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        $this->oInput = $oInput;
        $this->oOutput = $oOutput;

        $_aParams = $this->getUserValuesFromQuestions();

        if ($this->validateUserInput($_aParams))
        {
            $this->saveParameters($_aParams);
        }
    }

    /**
     * @param array $aUserInput
     */
    private function saveParameters(array $aUserInput)
    {
        $_oDumper = new Dumper();
        $_sYaml = $_oDumper->dump(array('parameters' => $aUserInput));

        $_oFileSystem = new Filesystem();
        $_oFileSystem->dumpFile(__DIR__ . self::FOLDER_APP_CONFIG . 'parameters.yml', $_sYaml);
    }

    /**
     * @return array
     */
    private function getUserValuesFromQuestions()
    {
        $_aResult = [];

        $_aResult['chronos_url'] = $this->printQuestion('Please enter the chronos url (inclusive port)');

        return $_aResult;
    }

    /**
     * @param array $aUserInput
     * @return bool
     */
    private function validateUserInput(array $aUserInput)
    {
        foreach ($aUserInput as $_sKey => $_sValue)
        {
            if (empty($_sValue))
            {
                $this->oOutput->writeln(sprintf('<error>Please add a valid value for parameter "%s"</error>', $_sKey));
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $sQuestion
     * @param null|mixed $mDefaultValue
     * @return mixed
     */
    private function printQuestion($sQuestion, $mDefaultValue = null)
    {
        $_oHelper = $this->getHelper('question');
        $_sFormat = (!empty($mDefaultValue)) ? '<comment>%s (default: %s):</comment>' : '<comment>%s:</comment>';

        $_oQuestion = new Question(sprintf($_sFormat, $sQuestion, $mDefaultValue), $mDefaultValue);

        return $_oHelper->ask($this->oInput, $this->oOutput, $_oQuestion);
    }
}