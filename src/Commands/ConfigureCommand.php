<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Chapi\Component\DependencyInjection\Loader\YamChapiConfigLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('cache_dir', 'd', InputOption::VALUE_OPTIONAL, 'Path to cache directory')

            ->addOption('chronos_url', 'u', InputOption::VALUE_OPTIONAL, 'The chronos url (inclusive port)', '')
            ->addOption('chronos_http_username', 'un', InputOption::VALUE_OPTIONAL, 'The chronos username (HTTP credentials)', '')
            ->addOption('chronos_http_password', 'p', InputOption::VALUE_OPTIONAL, 'The chronos password (HTTP credentials)', '')
            ->addOption('repository_dir', 'r', InputOption::VALUE_OPTIONAL, 'Root path to your job files', '')

            ->addOption('marathon_url', 'mu', InputOption::VALUE_OPTIONAL, 'The marathon url (inclusive port)', '')
            ->addOption('marathon_http_username', 'mun', InputOption::VALUE_OPTIONAL, 'The marathon username (HTTP credentials)', '')
            ->addOption('marathon_http_password', 'mp', InputOption::VALUE_OPTIONAL, 'The marathon password (HTTP credentials)', '')
            ->addOption('repository_dir_marathon', 'mr', InputOption::VALUE_OPTIONAL, 'Root path to the app files', '')
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
        $_aParams = $this->getInputValues();

        if ($this->hasValidateUserInput($_aParams))
        {
            $this->saveParameters($_aParams);
            return 0;
        }

        return 1;
    }

    /**
     * @return array<string,array<string,string|boolean>>
     */
    private function getInputValues()
    {
        $_aResult = [];

        $_aResult['cache_dir'] = [
            'value' => $this->getInputValue('cache_dir', '[GLOBAL] Please enter a cache directory'),
            'required' => true
        ];

        $_aResult['chronos_url'] = [
            'value' => $this->getInputValue('chronos_url', '[CHRONOS] Please enter the chronos url (inclusive port)'),
            'required' => false
        ];

        $_aResult['chronos_http_username'] = [
            'value' => $this->getInputValue('chronos_http_username', '[CHRONOS] Please enter the username to access your chronos instance'),
            'required' => false
        ];

        $_aResult['chronos_http_password'] = [
            'value' => $this->getInputValue('chronos_http_password', '[CHRONOS] Please enter the password to access your chronos instance', true),
            'required' => false
        ];

        $_aResult['repository_dir'] = [
            'value' => $this->getInputValue('repository_dir', '[CHRONOS] Please enter absolute path to your local chronos jobs configurations'),
            'required' => false
        ];

        $_aResult['marathon_url'] = [
            'value' => $this->getInputValue('marathon_url', '[MARATHON] Please enter the marathon url (inclusive port)'),
            'required' => false
        ];

        $_aResult['marathon_http_username'] = [
            'value' => $this->getInputValue('marathon_http_username', '[MARATHON] Please enter the username to access marathon instance'),
            'required' => false
        ];

        $_aResult['marathon_http_password'] = [
            'value' => $this->getInputValue('marathon_http_password', '[MARATHON] Please enter the password to access marathon instance', true),
            'required' => false
        ];

        $_aResult['repository_dir_marathon'] = [
            'value' => $this->getInputValue('repository_dir_marathon', '[MARATHON] Please enter absolute path to your local marathon tasks configurations'),
            'required' => false
        ];

        return $_aResult;
    }

    /**
     * @param string $sValueKey
     * @param string $sQuestion
     * @param boolean $bHiddenAnswer
     * @return string
     */
    private function getInputValue($sValueKey, $sQuestion, $bHiddenAnswer = false)
    {
        $_sValue = $this->oInput->getOption($sValueKey);
        if (empty($_sValue))
        {
            $_sValue = $this->printQuestion(
                $sQuestion,
                $this->getParameterValue($sValueKey),
                $bHiddenAnswer
            );
        }

        return $_sValue;
    }

    /**
     * @param array $aUserInput
     */
    private function saveParameters(array $aUserInput)
    {
        // We implemented an additional level of information
        // into the user input array: Is this field required or not?
        // To be backwards compatible we only store the value of
        // the question in the dump file.
        // With this loop we get rid of the "required" information
        // from getInputValues().
        $aToStore = [];
        foreach ($aUserInput as $key => $value)
        {
            $aToStore[$key] = ('null' === $value['value']) ? null : $value['value'];
        }

        $_aConfigToSave = [
            $this->getProfileName() => [
                'parameters' => $aToStore
            ]
        ];

        $_sPath = $this->getHomeDir() . DIRECTORY_SEPARATOR . $this->getParameterFileName();

        // load exiting config to merge
        $_aConfig = $this->loadConfigFile();
        $_aFinalConfig['profiles'] = array_merge($_aConfig['profiles'], $_aConfigToSave);


        // dump final config
        $_oDumper = new Dumper();
        $_sYaml = $_oDumper->dump($_aFinalConfig, 4);

        $_oFileSystem = new Filesystem();
        $_oFileSystem->dumpFile(
            $_sPath,
            $_sYaml
        );
    }

    /**
     * @param array $aUserInput
     * @return bool
     */
    private function hasValidateUserInput(array $aUserInput)
    {
        foreach ($aUserInput as $_sKey => $_sValue)
        {
            if ($_sValue['required'] == true && empty($_sValue['value']))
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
        $_aParameters = $this->loadConfigFile($this->getProfileName());

        if (isset($_aParameters['parameters']) && isset($_aParameters['parameters'][$sKey]))
        {
            return $_aParameters['parameters'][$sKey];
        }


        return $mDefaultValue;
    }

    /**
     * @param null $sProfile
     * @return array|mixed
     */
    private function loadConfigFile($sProfile = null)
    {
        $_aEmptyResult = [
            'profiles' => []
        ];

        $_oParser = new Parser();
        $_sParameterFile = $this->getHomeDir() . DIRECTORY_SEPARATOR . $this->getParameterFileName();

        if (file_exists($_sParameterFile)) {
            $_aParameters = $_oParser->parse(
                file_get_contents($_sParameterFile)
            );

            if (null === $sProfile)
            {
                return $_aParameters;
            }

            return (isset($_aParameters['profiles']) && isset($_aParameters['profiles'][$sProfile]))
                ? $_aParameters['profiles'][$sProfile]
                : $_aEmptyResult;
        }

        return $_aEmptyResult;
    }


    /**
     * @param string $sQuestion
     * @param null|mixed $mDefaultValue
     * @param boolean $bHiddenAnswer
     * @return mixed
     */
    private function printQuestion($sQuestion, $mDefaultValue = null, $bHiddenAnswer = false)
    {
        $_oHelper = $this->getHelper('question');

        // If we have a hidden answer and the default value is not empty
        // the we will set it as empty, because we don`t want to show
        // the default value on the terminal.
        // We know that the user has to enter the password again
        // if he / she want to reconfigure something. But this
        // is an acceptable tradeoff.
        if ($bHiddenAnswer === true && !empty($mDefaultValue))
        {
            $mDefaultValue = null;
        }

        $_sFormat = (!empty($mDefaultValue)) ? '<comment>%s (default: %s):</comment>' : '<comment>%s:</comment>';
        $_oQuestion = new Question(sprintf($_sFormat, $sQuestion, $mDefaultValue), $mDefaultValue);

        // Sensitive information (like passwords) should not be
        // visible during the configuration wizard
        if ($bHiddenAnswer === true)
        {
            $_oQuestion->setHidden(true);
            $_oQuestion->setHiddenFallback(false);
        }

        return $_oHelper->ask($this->oInput, $this->oOutput, $_oQuestion);
    }
}