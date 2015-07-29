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
    const FOLDER_APP_CONFIG = '/../../app/config/';
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

        if (!$this->isAppRunable()) {
            exit(1);
        }

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
            $_oLoader = new YamlFileLoader($_oContainer, new FileLocator(__DIR__ . self::FOLDER_APP_CONFIG));
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
        if (!file_exists(__DIR__ . self::FOLDER_APP_CONFIG . 'parameters.yml'))
        {
            $this->oOutput->writeln(sprintf('<error>%s</error>', 'No parameter file found. Please run "configure" command for initial setup.'));
            return false;
        }

        return true;
    }
}