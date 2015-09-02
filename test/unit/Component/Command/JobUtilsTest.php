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

class JobUtilsTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigureJobNamesArgument()
    {
        $_sDescription = 'testConfigureJobNamesArgument';

        $_oCommand = $this->prophesize('Symfony\Component\Console\Command\Command');
        $_oCommand->addArgument(
            Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES),
            Argument::exact(InputArgument::IS_ARRAY),
            Argument::exact($_sDescription)
        )->shouldBeCalledTimes(1)
        ;

        $this->assertNull(JobUtils::configureJobNamesArgument($_oCommand->reveal(), $_sDescription));
    }

    public function testGetJobNamesSuccess()
    {
        $_aJobs = ['JobA', 'JobB'];

        $_oCommand = $this->prophesize('Symfony\Component\Console\Command\Command');
        $_oCommand->getName()->shouldNotBeCalled();

        $_oInput = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $_oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))
            ->willReturn($_aJobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->assertEquals($_aJobs, JobUtils::getJobNames($_oInput->reveal(), $_oCommand->reveal()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetJobNamesFailure()
    {
        $_oCommand = $this->prophesize('Symfony\Component\Console\Command\Command');
        $_oCommand->getName()->willReturn('CommandName')->shouldBeCalledTimes(2);

        $_oInput = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $_oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))
            ->willReturn([])
            ->shouldBeCalledTimes(1)
        ;

        $this->assertNull(JobUtils::getJobNames($_oInput->reveal(), $_oCommand->reveal()));
    }

    public function testIsWildcard()
    {
        $this->assertTrue(JobUtils::isWildcard(['*']));
        $this->assertTrue(JobUtils::isWildcard(['.']));

        $this->assertFalse(JobUtils::isWildcard(['JobA', 'JobB']));
    }

}