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
use Prophecy\Argument;

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
    }

    public function testGetHomeDirUnix()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR'))
        {
            $this->markTestSkipped('OS should be windows');
        }

        $_sHomeDir = rtrim(getenv('HOME'), '/') . '/.chapi'; // unix home dir

        $_oCommand = new AbstractCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertContains('.chapi',  $_oCommand->getHomeDir());
        $this->assertContains($_sHomeDir,  $_oCommand->getHomeDir());

        $this->assertContains('cache',  $_oCommand->getCacheDir());
        $this->assertContains($_sHomeDir,  $_oCommand->getCacheDir());
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

    /**
     * @inheritDoc
     */
    protected function process()
    {
        return 0;
    }

    public function getHomeDir()
    {
        return parent::getHomeDir();
    }
}