<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-21
 *
 */


namespace Chapi\Commands;


use Chapi\Component\Command\CommandUtils;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class AbstractCommand extends Command
{
    const FOLDER_RESOURCES = '/../../app/Resources/config/';

    /**
     * @var InputInterface
     */
    protected $oInput;

    /**
     * @var OutputInterface
     */
    protected $oOutput;

    /**
     * @var ContainerBuilder
     */
    private static $oContainer;

    /**
     * @var string
     */
    private static $sHomeDir = '';

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $oInput An InputInterface instance
     * @param OutputInterface $oOutput An OutputInterface instance
     *
     * @return integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        $this->oInput = $oInput;
        $this->oOutput = $oOutput;

        if (!$this->isAppRunable())
        {
            return 1;
        }

        // set output for verbosity handling
        /** @var \Symfony\Bridge\Monolog\Handler\ConsoleHandler $_oConsoleHandler */
        $_oConsoleHandler = $this->getContainer()->get('ConsoleHandler');
        $_oConsoleHandler->setOutput($this->oOutput);

        return $this->process();
    }

    /**
     * @return int
     */
    abstract protected function process();

    /**
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        if (is_null(self::$oContainer))
        {
            $_oContainer = new ContainerBuilder();

            // load local parameters
            $this->loadParameterConfig($this->getHomeDir(), 'parameters.yml', $_oContainer);

            // load optional parameter in the current working directory
            $this->loadParameterConfig($this->getWorkingDir(), '.chapiconfig', $_oContainer);

            // load services
            $_oLoader = new YamlFileLoader($_oContainer, new FileLocator(__DIR__ . self::FOLDER_RESOURCES));
            $_oLoader->load('services.yml');

            self::$oContainer = $_oContainer;
        }

        return self::$oContainer;
    }

    /**
     * @return bool
     */
    protected function isAppRunable()
    {
        if (
            !file_exists($this->getHomeDir() . DIRECTORY_SEPARATOR . 'parameters.yml')
            && !file_exists($this->getWorkingDir() . DIRECTORY_SEPARATOR . '.chapiconfig')
        ) // one file have to exist
        {
            $this->oOutput->writeln(sprintf(
                '<error>%s</error>',
                'No parameter file found. Please run "configure" command for initial setup or add a local `.chapiconfig` to your working directory.'
            ));
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getHomeDir()
    {
        if (!empty(self::$sHomeDir))
        {
            return self::$sHomeDir;
        }

        $_sHomeDir = getenv('CHAPI_HOME');
        if (!$_sHomeDir)
        {
            $_sHomeDir = CommandUtils::getOsHomeDir() . DIRECTORY_SEPARATOR . '.chapi';
        }

        CommandUtils::hasCreateDirectoryIfNotExists($_sHomeDir);

        return self::$sHomeDir = $_sHomeDir;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        $_sCacheDir = $this->getHomeDir() . DIRECTORY_SEPARATOR . 'cache';
        CommandUtils::hasCreateDirectoryIfNotExists($_sCacheDir);

        return $_sCacheDir;
    }

    /**
     * @return string
     */
    protected function getWorkingDir()
    {
        return getcwd();
    }

    /**
     * @param string $sPath
     * @param string $sFile
     * @param ContainerBuilder $oContainer
     */
    private function loadParameterConfig($sPath, $sFile, $oContainer)
    {
        // load local parameters
        if (file_exists($sPath . DIRECTORY_SEPARATOR . $sFile))
        {
            $_oLoader = new YamlFileLoader($oContainer, new FileLocator($sPath));
            $_oLoader->load($sFile);
        }
    }
}