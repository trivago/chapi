<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/17
 */

namespace unit\Command;

use Chapi\Commands\ConfigureCommand;
use ChapiTest\src\TestTraits\CommandTestTrait;
use Prophecy\Argument;

class ConfigureCommandTest extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oQuestionHelper;

    /**
     * @var string
     */
    private $sTempTestDir = '';

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->oQuestionHelper = $this->prophesize('\Symfony\Component\Console\Helper\QuestionHelper');

        // init and set up temp directory
        $_sTempTestDir = sys_get_temp_dir();
        $this->sTempTestDir = $_sTempTestDir . DIRECTORY_SEPARATOR . 'ChapiUnitTest';
        if (!is_dir($this->sTempTestDir))
        {
            mkdir($this->sTempTestDir, 0755);
        }

        ConfigureCommandDummy::$sHomeDirDummy = $this->sTempTestDir;
    }

    public function tearDown()
    {
        if (file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml'))
        {
            unlink($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml');
        }
    }

    public function testConfigureWithoutArgumentsSuccess()
    {
        $this->oInput->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oInput->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oInput->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->oQuestionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(3)
            ->willReturn('stringInput')
        ;

        $_oCommand = new ConfigureCommandDummy();
        $_oCommand::$oQustionHelperDummy = $this->oQuestionHelper->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        $this->assertTrue(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml'));
    }

    public function testConfigureWithArgumentsSuccess()
    {
        $this->oInput->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->oInput->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn('/cacheDir');
        $this->oInput->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn('/path');

        $this->oQuestionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldNotBeCalled()
        ;

        $_oCommand = new ConfigureCommandDummy();
        $_oCommand::$oQustionHelperDummy = $this->oQuestionHelper->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        $this->assertTrue(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml'));
    }

    public function testConfigureWithAndWithoutArgumentsSuccess()
    {
        $this->oInput->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->oInput->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oInput->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->oQuestionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(2)
            ->willReturn('inputValue')
        ;

        $_oCommand = new ConfigureCommandDummy();
        $_oCommand::$oQustionHelperDummy = $this->oQuestionHelper->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        $this->assertTrue(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml'));
    }

    public function testConfigureWithoutArgumentsFailure()
    {
        $this->oInput->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->oInput->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oInput->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->oQuestionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(2)
            ->willReturn('')
        ;

        $_oCommand = new ConfigureCommandDummy();
        $_oCommand::$oQustionHelperDummy = $this->oQuestionHelper->reveal();

        $this->assertEquals(
            1,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        $this->assertFalse(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'parameters.yml'));
    }
}

class ConfigureCommandDummy extends ConfigureCommand
{
    public static $oContainerDummy;

    public static $sHomeDirDummy;

    public static $oQustionHelperDummy;

    protected function getContainer()
    {
        return self::$oContainerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }

    protected function getHomeDir()
    {
        return self::$sHomeDirDummy;
    }

    public function getHelper($sHelper)
    {
        return ($sHelper == 'question') ? self::$oQustionHelperDummy : parent::getHelper($sHelper);
    }
}