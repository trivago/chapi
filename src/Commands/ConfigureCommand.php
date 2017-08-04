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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return $this->process();
    }

    /**
     * @return int
     */
    protected function process()
    {
        $parameters = $this->getInputValues();

        if ($this->hasValidateUserInput($parameters)) {
            $this->saveParameters($parameters);
            return 0;
        }

        return 1;
    }

    /**
     * @return array<string,array<string,string|boolean>>
     */
    private function getInputValues()
    {
        $result = [];

        $result['cache_dir'] = [
            'value' => $this->getInputValue('cache_dir', '[GLOBAL] Please enter a cache directory'),
            'required' => true
        ];

        $result['chronos_url'] = [
            'value' => $this->getInputValue('chronos_url', '[CHRONOS] Please enter the chronos url (inclusive port)'),
            'required' => false
        ];

        $result['chronos_http_username'] = [
            'value' => $this->getInputValue('chronos_http_username', '[CHRONOS] Please enter the username to access your chronos instance'),
            'required' => false
        ];

        $result['chronos_http_password'] = [
            'value' => $this->getInputValue('chronos_http_password', '[CHRONOS] Please enter the password to access your chronos instance', true),
            'required' => false
        ];

        $result['repository_dir'] = [
            'value' => $this->getInputValue('repository_dir', '[CHRONOS] Please enter absolute path to your local chronos jobs configurations'),
            'required' => false
        ];

        $result['marathon_url'] = [
            'value' => $this->getInputValue('marathon_url', '[MARATHON] Please enter the marathon url (inclusive port)'),
            'required' => false
        ];

        $result['marathon_http_username'] = [
            'value' => $this->getInputValue('marathon_http_username', '[MARATHON] Please enter the username to access marathon instance'),
            'required' => false
        ];

        $result['marathon_http_password'] = [
            'value' => $this->getInputValue('marathon_http_password', '[MARATHON] Please enter the password to access marathon instance', true),
            'required' => false
        ];

        $result['repository_dir_marathon'] = [
            'value' => $this->getInputValue('repository_dir_marathon', '[MARATHON] Please enter absolute path to your local marathon tasks configurations'),
            'required' => false
        ];

        return $result;
    }

    /**
     * @param string $valueKey
     * @param string $question
     * @param boolean $hideAnswer
     * @return string
     */
    private function getInputValue($valueKey, $question, $hideAnswer = false)
    {
        $_sValue = $this->input->getOption($valueKey);
        if (empty($_sValue)) {
            $_sValue = $this->printQuestion(
                $question,
                $this->getParameterValue($valueKey),
                $hideAnswer
            );
        }

        return $_sValue;
    }

    /**
     * @param array $userInput
     */
    private function saveParameters(array $userInput)
    {
        // We implemented an additional level of information
        // into the user input array: Is this field required or not?
        // To be backwards compatible we only store the value of
        // the question in the dump file.
        // With this loop we get rid of the "required" information
        // from getInputValues().
        $toStore = [];
        foreach ($userInput as $key => $value) {
            $toStore[$key] = ('null' === $value['value']) ? null : $value['value'];
        }

        $configToSave = [
            $this->getProfileName() => [
                'parameters' => $toStore
            ]
        ];

        $path = $this->getHomeDir() . DIRECTORY_SEPARATOR . $this->getParameterFileName();

        // load exiting config to merge
        $config = $this->loadConfigFile(['profiles' => []]);

        $finalConfig = [
            'profiles' => array_merge($config['profiles'], $configToSave)
        ];


        // dump final config
        $dumper = new Dumper();
        $yaml = $dumper->dump($finalConfig, 4);

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile(
            $path,
            $yaml
        );
    }

    /**
     * @param array $userInput
     * @return bool
     */
    private function hasValidateUserInput(array $userInput)
    {
        foreach ($userInput as $key => $value) {
            if ($value['required'] == true && empty($value['value'])) {
                $this->output->writeln(sprintf('<error>Please add a valid value for parameter "%s"</error>', $key));
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    private function getParameterValue($key, $defaultValue = null)
    {
        $parameters = $this->getParameters();

        if (isset($parameters['parameters']) && isset($parameters['parameters'][$key])) {
            return $parameters['parameters'][$key];
        }

        return $defaultValue;
    }

    /**
     * @return array
     */
    private function getParameters()
    {
        $profile = $this->getProfileName();
        $parameters = $this->loadConfigFile();

        return (isset($parameters['profiles']) && isset($parameters['profiles'][$profile]))
            ? $parameters['profiles'][$profile]
            : ['profiles' => []];
    }

    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    private function loadConfigFile($defaultValue = [])
    {
        $parameterFile = $this->getHomeDir() . DIRECTORY_SEPARATOR . $this->getParameterFileName();

        if (!file_exists($parameterFile)) {
            return $defaultValue;
        }

        $parser = new Parser();

        $parameters = $parser->parse(
            file_get_contents($parameterFile)
        );

        return $parameters;
    }


    /**
     * @param string $question
     * @param null|mixed $defaultValue
     * @param boolean $hideAnswer
     * @return mixed
     */
    private function printQuestion($question, $defaultValue = null, $hideAnswer = false)
    {
        $helper = $this->getHelper('question');

        // If we have a hidden answer and the default value is not empty
        // the we will set it as empty, because we don`t want to show
        // the default value on the terminal.
        // We know that the user has to enter the password again
        // if he / she want to reconfigure something. But this
        // is an acceptable tradeoff.
        if ($hideAnswer === true && !empty($defaultValue)) {
            $defaultValue = null;
        }

        $format = (!empty($defaultValue)) ? '<comment>%s (default: %s):</comment>' : '<comment>%s:</comment>';
        $question = new Question(sprintf($format, $question, $defaultValue), $defaultValue);

        // Sensitive information (like passwords) should not be
        // visible during the configuration wizard
        if ($hideAnswer === true) {
            $question->setHidden(true);
            $question->setHiddenFallback(false);
        }

        return $helper->ask($this->input, $this->output, $question);
    }
}
