<?php
/**
 * @package: chapi
 *
 * @author:  ppokatilo
 * @since:   2017-08-04
 */

namespace unit\Command;

use Chapi\Commands\AbstractCommand;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCommandDummy extends AbstractCommand
{

    public static $oContainerDummy;

    protected function configure()
    {
        $this->setName('unitTestAbstractCommand');
    }

    protected function getContainer()
    {
        return self::$oContainerDummy;
    }

    public function getCacheDir()
    {
        return parent::getCacheDir();
    }

    protected function process()
    {
        return 0;
    }

    protected function getHomeDir()
    {
        return vfsStream::url('unitTestRoot/homeDir');
    }

    protected function getWorkingDir()
    {
        return vfsStream::url('unitTestRoot/workingDir');
    }

    public function isAppRunablePub()
    {
        return $this->isAppRunable();
    }

    public function getHomeDirPub()
    {
        return parent::getHomeDir();
    }

    public function initializePub(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::initialize($oInput, $oOutput);
    }

    public function getParameterFileNamePub()
    {
        return parent::getParameterFileName();
    }
}
