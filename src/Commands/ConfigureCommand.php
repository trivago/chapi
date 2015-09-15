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
use Symfony\Component\Yaml\Parser;

class ConfigureCommand extends AbstractCommand
{
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
     * @param InputInterface $oInput
     * @param OutputInterface $oOutput
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        $this->oInput = $oInput;
        $this->oOutput = $oOutput;

        return $this->process();
    }

    /**
     * @return int
     */
    protected function process()
    {
        $_aParams = $this->getUserValuesFromQuestions();

        if ($this->hasValidateUserInput($_aParams))
        {
            $this->saveParameters($_aParams);
            return 0;
        }

        return 1;
    }

    /**
     * @param array $aUserInput
     */
    private function saveParameters(array $aUserInput)
    {
        $_oDumper = new Dumper();
        $_sYaml = $_oDumper->dump(array('parameters' => $aUserInput));

        $_oFileSystem = new Filesystem();
        $_oFileSystem->dumpFile($this->getHomeDir() . '/parameters.yml', $_sYaml);
    }

    /**
     * @return array
     */
    private function getUserValuesFromQuestions()
    {
        $_aResult = [];

        $_aResult['chronos_url'] = $this->printQuestion(
            'Please enter the chronos url (inclusive port)',
            $this->getParameterValue('chronos_url')
        );
        $_aResult['cache_dir'] = $this->printQuestion(
            'Please enter a cache directory',
            $this->getParameterValue('cache_dir', realpath($this->getCacheDir()))
        );
        $_aResult['repository_dir'] = $this->printQuestion(
            'Please enter your root path to your job files',
            $this->getParameterValue('repository_dir', realpath(__DIR__ . '/../../'))
        );

        return $_aResult;
    }

    /**
     * @param array $aUserInput
     * @return bool
     */
    private function hasValidateUserInput(array $aUserInput)
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
     * @param string $sKey
     * @param mixed $mDefaultValue
     * @return mixed
     */
    private function getParameterValue($sKey, $mDefaultValue = null)
    {
        $_oParser = new Parser();
        $_sParameterFile = $this->getHomeDir() . '/parameters.yml';

        if (file_exists($_sParameterFile))
        {
            $_aParameters = $_oParser->parse(
                file_get_contents($_sParameterFile)
            );

            if (isset($_aParameters['parameters']) && isset($_aParameters['parameters'][$sKey]))
            {
                return $_aParameters['parameters'][$sKey];
            }
        }

        return $mDefaultValue;
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