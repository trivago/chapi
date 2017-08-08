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
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private static $homeDir = '';

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
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        parent::initialize($input, $output);
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
     * @return integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isAppRunable()) {
            return 1;
        }

        // set output for verbosity handling
        /** @var \Symfony\Bridge\Monolog\Handler\ConsoleHandler $consoleHandler */
        $consoleHandler = $this->getContainer()->get('ConsoleHandler');
        $consoleHandler->setOutput($this->output);

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
        if (is_null($this->container)) {
            $container = $this->loadContainer();

            // load services
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . self::FOLDER_RESOURCES));
            $loader->load('services.yml');

            $this->container = $container;
        }

        return $this->container;
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
        if (!file_exists($this->getHomeDir() . DIRECTORY_SEPARATOR . ChapiConfigInterface::CONFIG_FILE_NAME)
            && !file_exists($this->getWorkingDir() . DIRECTORY_SEPARATOR . ChapiConfigInterface::CONFIG_FILE_NAME)
        ) {
            $this->output->writeln(sprintf(
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
        if (!empty(self::$homeDir)) {
            return self::$homeDir;
        }

        $homeDir = getenv('CHAPI_HOME');
        if (!$homeDir) {
            $homeDir = CommandUtils::getOsHomeDir() . DIRECTORY_SEPARATOR . '.chapi';
        }

        CommandUtils::hasCreateDirectoryIfNotExists($homeDir);

        return self::$homeDir = $homeDir;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        $cacheDir = $this->getHomeDir() . DIRECTORY_SEPARATOR . 'cache';
        CommandUtils::hasCreateDirectoryIfNotExists($cacheDir);

        return $cacheDir;
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
        return $this->input->getOption('profile');
    }

    /**
     * @return ContainerBuilder
     */
    private function loadContainer()
    {
        $container = new ContainerBuilder();
        $chapiConfig = new ChapiConfig(
            [$this->getHomeDir(), $this->getWorkingDir()],
            new Parser(),
            $this->getProfileName()
        );

        $chapiConfigLoader = new ChapiConfigLoader($container, $chapiConfig);
        $chapiConfigLoader->loadProfileParameters();

        // load basic parameters
        $container->setParameter('chapi_home', $this->getHomeDir());
        $container->setParameter('chapi_work_dir', $this->getWorkingDir());
        $container->setParameter('chapi_profile', $this->getProfileName());

        return $container;
    }
}
