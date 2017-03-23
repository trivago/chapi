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
use Chapi\Component\Config\ChapiConfig;
use Chapi\Component\Config\ChapiConfigInterface;
use Chapi\Component\DependencyInjection\Loader\ChapiConfigLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser;

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
    private $oContainer;

    /**
     * @var string
     */
    private static $sHomeDir = '';

    /**
     * @inheritdoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        // setup default --profile option for all commands
        $this->addOption(
            'profile',
            null,
            InputOption::VALUE_OPTIONAL,
            'Look for global confguration in .../parameters_<profile>.yml instead of .../parameters.yml',
            'default'
        );
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $oInput, OutputInterface $oOutput)
    {
        $this->oInput = $oInput;
        $this->oOutput = $oOutput;

        parent::initialize($oInput, $oOutput);
    }

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
        if (is_null($this->oContainer))
        {
            $_oContainer = $this->loadContainer();

            // load services
            $_oLoader = new YamlFileLoader($_oContainer, new FileLocator(__DIR__ . self::FOLDER_RESOURCES));
            $_oLoader->load('services.yml');

            $this->oContainer = $_oContainer;
        }

        return $this->oContainer;
    }

    /**
     * @return string
     */
    protected function getParameterFileName()
    {
        return ChapiConfigInterface::CONFIG_FILE_NAME;
    }

    /**
     * @return bool
     */
    protected function isAppRunable()
    {
        // one file has to exist
        if (
            !file_exists($this->getHomeDir() . DIRECTORY_SEPARATOR . ChapiConfigInterface::CONFIG_FILE_NAME)
            && !file_exists($this->getWorkingDir() . DIRECTORY_SEPARATOR . ChapiConfigInterface::CONFIG_FILE_NAME)
        )
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
     * @return string
     */
    protected function getProfileName()
    {
        return $this->oInput->getOption('profile');
    }

    /**
     * @return ContainerBuilder
     */
    private function loadContainer()
    {
        $_oContainer = new ContainerBuilder();
        $_oChapiConfig = new ChapiConfig(
            [$this->getHomeDir(), $this->getWorkingDir()],
            new Parser(),
            $this->getProfileName()
        );

        $_oChapiConfigLoader  = new ChapiConfigLoader($_oContainer, $_oChapiConfig);
        $_oChapiConfigLoader->loadProfileParameters();

        // load basic parameters
        $_oContainer->setParameter('chapi_home', $this->getHomeDir());
        $_oContainer->setParameter('chapi_work_dir', $this->getWorkingDir());
        $_oContainer->setParameter('chapi_profile', $this->getProfileName());

        return $_oContainer;
    }
}
