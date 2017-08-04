<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 */


namespace unit\Command;

use Chapi\Commands\AbstractCommand;
use ChapiTest\src\TestTraits\CommandTestTrait;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oConsoleHandler;

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->oConsoleHandler = $this->prophesize('\Symfony\Bridge\Monolog\Handler\ConsoleHandler');
        $this->oContainer->get(Argument::exact('ConsoleHandler'))->willReturn($this->oConsoleHandler->reveal());

        vfsStream::setup('unitTestRoot', null, array('homeDir'=>[], 'workingDir'=>[]));
    }

    public function testGetHomeDirUnix()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('OS should be windows');
        }

        $_sHomeDir = rtrim(getenv('HOME'), '/') . '/.chapi'; // unix home dir

        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertContains('.chapi', $_oCommand->getHomeDirPub());
        $this->assertContains($_sHomeDir, $_oCommand->getHomeDirPub());
        $this->assertTrue(is_dir($_oCommand->getHomeDirPub()));
    }

    public function testGetCacheDir()
    {
        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertContains('cache', $_oCommand->getCacheDir());
        $this->assertTrue(is_dir($_oCommand->getCacheDir()));
    }

    public function testIsAppRuanableWithLocalConfig()
    {
        $_aStructure = [
            'homeDir' => [],
            'workingDir'=> [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ]
        ];

        vfsStream::create($_aStructure);

        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();
        $_oCommand->initializePub(
            $this->oInput->reveal(),
            $this->oOutput->reveal()
        );

        $this->assertTrue($_oCommand->isAppRunablePub());
    }

    public function testIsAppRuanableWithGlobalConfig()
    {
        $_aStructure = [
            'homeDir'=>[
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ],
            'workingDir'=>[]
        ];

        vfsStream::create($_aStructure);

        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();
        $_oCommand->initializePub(
            $this->oInput->reveal(),
            $this->oOutput->reveal()
        );

        $this->assertTrue($_oCommand->isAppRunablePub());
    }

    public function testIsAppRuanableWithGlobalAndLocalConfig()
    {
        $_aStructure = [
            'homeDir' => [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ],
            'workingDir '=> [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ]
        ];

        vfsStream::create($_aStructure);

        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();
        $_oCommand->initializePub(
            $this->oInput->reveal(),
            $this->oOutput->reveal()
        );

        $this->assertTrue($_oCommand->isAppRunablePub());
    }

    public function testGetParameterFileNameDefault()
    {
        $this->oInput->getOption('profile')->willReturn('default');
        $_oCommand = new AbstractCommandDummy();
        $_oCommand->initializePub(
            $this->oInput->reveal(),
            $this->oOutput->reveal()
        );

        $this->assertEquals('.chapiconfig', $_oCommand->getParameterFileNamePub());
    }
}

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
