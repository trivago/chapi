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

use ChapiTest\src\TestTraits\CommandTestTrait;
use Prophecy\Argument;

class ConfigureCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $questionHelper;

    /**
     * @var string
     */
    private $tempTestDir = '';

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->questionHelper = $this->prophesize('\Symfony\Component\Console\Helper\QuestionHelper');

        // init and set up temp directory
        $tempTestDir = sys_get_temp_dir();
        $this->tempTestDir = $tempTestDir . DIRECTORY_SEPARATOR . 'ChapiUnitTest';
        if (!is_dir($this->tempTestDir)) {
            mkdir($this->tempTestDir, 0755);
        }

        ConfigureCommandDummy::$homeDirDummy = $this->tempTestDir;
    }

    public function tearDown()
    {
        if (file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig')) {
            unlink($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig');
        }
    }

    public function testConfigureWithoutArgumentsSuccess()
    {
        $this->input->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('chronos_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('chronos_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->input->getOption(Argument::exact('marathon_url'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('marathon_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('marathon_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir_marathon'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->questionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(9)
            ->willReturn('stringInput')
        ;

        $command = new ConfigureCommandDummy();
        $command::$questionHelperDummy = $this->questionHelper->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        $this->assertTrue(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig'));
    }

    public function testConfigureWithArgumentsSuccess()
    {
        $this->input->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->input->getOption(Argument::exact('chronos_http_username'))->shouldBeCalledTimes(1)->willReturn('username');
        $this->input->getOption(Argument::exact('chronos_http_password'))->shouldBeCalledTimes(1)->willReturn('password');
        $this->input->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn('/cacheDir');
        $this->input->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn('/path');

        $this->input->getOption(Argument::exact('marathon_url'))->shouldBeCalledTimes(1)->willReturn('http://marathon.com');
        $this->input->getOption(Argument::exact('marathon_http_username'))->shouldBeCalledTimes(1)->willReturn('musername');
        $this->input->getOption(Argument::exact('marathon_http_password'))->shouldBeCalledTimes(1)->willReturn('mpassword');
        $this->input->getOption(Argument::exact('repository_dir_marathon'))->shouldBeCalledTimes(1)->willReturn('/path/marathon');

        $this->questionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldNotBeCalled()
        ;

        $command = new ConfigureCommandDummy();
        $command::$questionHelperDummy = $this->questionHelper->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        $this->assertTrue(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig'));
    }

    public function testConfigureWithAndWithoutArgumentsSuccess()
    {
        $this->input->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->input->getOption(Argument::exact('chronos_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('chronos_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->input->getOption(Argument::exact('marathon_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->input->getOption(Argument::exact('marathon_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('marathon_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir_marathon'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->questionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(7)
            ->willReturn('inputValue')
        ;

        $command = new ConfigureCommandDummy();
        $command::$questionHelperDummy = $this->questionHelper->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        $this->assertTrue(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig'));
    }

    public function testConfigureWithoutArgumentsFailure()
    {
        $this->input->getOption(Argument::exact('chronos_url'))->shouldBeCalledTimes(1)->willReturn('http://url.com');
        $this->input->getOption(Argument::exact('chronos_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('chronos_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('cache_dir'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->input->getOption(Argument::exact('marathon_url'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('marathon_http_username'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('marathon_http_password'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('repository_dir_marathon'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->questionHelper->ask(
            Argument::type('Symfony\Component\Console\Input\InputInterface'),
            Argument::type('Symfony\Component\Console\Output\OutputInterface'),
            Argument::type('Symfony\Component\Console\Question\Question')
        )
            ->shouldBeCalledTimes(8)
            ->willReturn('')
        ;

        $command = new ConfigureCommandDummy();
        $command::$questionHelperDummy = $this->questionHelper->reveal();

        $this->assertEquals(
            1,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        $this->assertFalse(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . '.chapiconfig'));
    }
}
