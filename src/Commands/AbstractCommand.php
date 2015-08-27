<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-21
 *
 */


namespace Chapi\Commands;


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
            $_oLoader = new YamlFileLoader($_oContainer, new FileLocator($this->getHomeDir()));
            $_oLoader->load('parameters.yml');

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
        if (!file_exists($this->getHomeDir() . '/parameters.yml'))
        {
            $this->oOutput->writeln(sprintf('<error>%s</error>', 'No parameter file found. Please run "configure" command for initial setup.'));
            return false;
        }

        return true;
    }

    /**
     * @link   https://github.com/composer/composer/blob/69210d5bc130f8cc9f96f99582a041254d7b9833/src/Composer/Factory.php
     * @return string
     * @throws \RuntimeException
     */
    protected function getHomeDir()
    {
        if (!empty(self::$sHomeDir))
        {
            return self::$sHomeDir;
        }

        $home = getenv('CHAPI_HOME');
        if (!$home) {
            if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                if (!getenv('APPDATA')) {
                    throw new \RuntimeException('The APPDATA or CHAPI_HOME environment variable must be set for composer to run correctly');
                }
                $home = strtr(getenv('APPDATA'), '\\', '/') . '/chapi';
            } else {
                if (!getenv('HOME')) {
                    throw new \RuntimeException('The HOME or CHAPI_HOME environment variable must be set for composer to run correctly');
                }
                $home = rtrim(getenv('HOME'), '/') . '/.chapi';
            }
        }

        if (!file_exists($home . '/.htaccess'))
        {
            if (!is_dir($home))
            {
                if (!mkdir($home, 0755, true))
                {
                    throw new \RuntimeException(sprintf('Unable to create home directory "%s"', $home));
                }
            }

            if (false === file_put_contents( $home . '/.htaccess', 'Deny from all'))
            {
                throw new \RuntimeException(sprintf('Unable to create htaccess file in home directory "%s"', $home));
            }
        }

        return self::$sHomeDir = $home;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        $_sCacheDir = $this->getHomeDir() . '/cache';
        if (!is_dir($_sCacheDir))
        {
            if (!mkdir($_sCacheDir, 0755, true))
            {
                throw new \RuntimeException(sprintf('Unable to create cache directory "%s"', $_sCacheDir));
            }
        }

        return $_sCacheDir;
    }
}