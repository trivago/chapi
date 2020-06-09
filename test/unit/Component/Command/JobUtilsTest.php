<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 */


namespace unit\Component\Command;

use Chapi\Component\Command\JobUtils;
use Chapi\Component\Command\JobUtilsInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputArgument;

class JobUtilsTest extends \PHPUnit\Framework\TestCase
{

    public function testConfigureJobNamesArgument()
    {
        $description = 'testConfigureJobNamesArgument';

        $command = $this->prophesize('Symfony\Component\Console\Command\Command');
        $command->addArgument(
            Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES),
            Argument::exact(InputArgument::IS_ARRAY),
            Argument::exact($description)
        )->shouldBeCalledTimes(1)
        ;

        $this->assertNull(JobUtils::configureJobNamesArgument($command->reveal(), $description));
    }

    public function testGetJobNamesSuccess()
    {
        $jobs = ['JobA', 'JobB'];

        $command = $this->prophesize('Symfony\Component\Console\Command\Command');
        $command->getName()->shouldNotBeCalled();

        $input = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))
            ->willReturn($jobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertEquals($jobs, JobUtils::getJobNames($input->reveal(), $command->reveal()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetJobNamesFailure()
    {
        $command = $this->prophesize('Symfony\Component\Console\Command\Command');
        $command->getName()->willReturn('CommandName')->shouldBeCalledTimes(2);

        $input = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))
            ->willReturn([])
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(JobUtils::getJobNames($input->reveal(), $command->reveal()));
    }

    public function testIsWildcard()
    {
        $this->assertTrue(JobUtils::isWildcard(['*']));
        $this->assertTrue(JobUtils::isWildcard(['.']));

        $this->assertFalse(JobUtils::isWildcard(['JobA', 'JobB']));
    }
}
