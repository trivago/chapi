<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 */


namespace unit\Command;

use ChapiTest\src\TestTraits\CommandTestTrait;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;

class AbstractCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $consoleHandler;

    protected function setUp(): void
    {
        $this->setUpCommandDependencies();

        $this->consoleHandler = $this->prophesize('\Symfony\Bridge\Monolog\Handler\ConsoleHandler');
        $this->container->get(Argument::exact('ConsoleHandler'))->willReturn($this->consoleHandler->reveal());

        vfsStream::setup('unitTestRoot', null, array('homeDir'=>[], 'workingDir'=>[]));
    }

    public function testGetHomeDirUnix()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('OS should be windows');
        }

        $homeDir = rtrim(getenv('HOME'), '/') . '/.chapi'; // unix home dir

        $command = new AbstractCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertStringContainsString('.chapi', $command->getHomeDirPub());
        $this->assertStringContainsString($homeDir, $command->getHomeDirPub());
        $this->assertTrue(is_dir($command->getHomeDirPub()));
    }

    public function testGetCacheDir()
    {
        $command = new AbstractCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertStringContainsString('cache', $command->getCacheDir());
        $this->assertTrue(is_dir($command->getCacheDir()));
    }

    public function testIsAppRuanableWithLocalConfig()
    {
        $structure = [
            'homeDir' => [],
            'workingDir'=> [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ]
        ];

        vfsStream::create($structure);

        $command = new AbstractCommandDummy();
        $command::$containerDummy = $this->container->reveal();
        $command->initializePub(
            $this->input->reveal(),
            $this->output->reveal()
        );

        $this->assertTrue($command->isAppRunablePub());
    }

    public function testIsAppRuanableWithGlobalConfig()
    {
        $structure = [
            'homeDir'=>[
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ],
            'workingDir'=>[]
        ];

        vfsStream::create($structure);

        $command = new AbstractCommandDummy();
        $command::$containerDummy = $this->container->reveal();
        $command->initializePub(
            $this->input->reveal(),
            $this->output->reveal()
        );

        $this->assertTrue($command->isAppRunablePub());
    }

    public function testIsAppRuanableWithGlobalAndLocalConfig()
    {
        $structure = [
            'homeDir' => [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ],
            'workingDir '=> [
                '.chapiconfig' => "{ profiles: { default: { parameters: { chronos_url: 'http://chronos.url:4400/', cache_dir: /tmp, repository_dir: /path/job/repository } } } }"
            ]
        ];

        vfsStream::create($structure);

        $command = new AbstractCommandDummy();
        $command::$containerDummy = $this->container->reveal();
        $command->initializePub(
            $this->input->reveal(),
            $this->output->reveal()
        );

        $this->assertTrue($command->isAppRunablePub());
    }

    public function testGetParameterFileNameDefault()
    {
        $this->input->getOption('profile')->willReturn('default');
        $command = new AbstractCommandDummy();
        $command->initializePub(
            $this->input->reveal(),
            $this->output->reveal()
        );

        $this->assertEquals('.chapiconfig', $command->getParameterFileNamePub());
    }
}
