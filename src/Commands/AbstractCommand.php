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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class AbstractCommand extends Command
{
    const FOLDER_APP_CONFIG = '/../../app/config/';
    const FOLDER_RESOURCES = '/../../app/Resources/config/';

    /**
     * @var ContainerBuilder
     */
    private static $oContainer;

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
     * @param OutputInterface $oOutput
     * @return bool
     */
    protected function isAppRunable(OutputInterface $oOutput)
    {
        if (!file_exists(__DIR__ . self::FOLDER_APP_CONFIG . 'parameters.yml'))
        {
            $oOutput->writeln(sprintf('<error>%s</error>', 'No parameter file found. Please run "configure" command for initial setup.'));
            return false;
        }

        return true;
    }
}