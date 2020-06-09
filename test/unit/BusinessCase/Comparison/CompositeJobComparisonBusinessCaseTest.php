<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-17
 *
 */

namespace ChapiTest\unit\BusinessCase\Comparison;

use Chapi\BusinessCase\Comparison\CompositeJobComparisonBusinessCase;
use Prophecy\Argument;

class CompositeJobComparisonBusinessCaseTest extends \PHPUnit\Framework\TestCase
{

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $marathonCase;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $chronosCase;

    protected function setUp(): void
    {
        $this->marathonCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->chronosCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
    }

    public function testGetLocalMissingJobsSuccess()
    {
        $this->marathonCase
            ->getLocalMissingJobs()
            ->willReturn(["/local/missing1"])
            ->shouldBeCalled();

        $this->chronosCase
            ->getLocalMissingJobs()
            ->willReturn(["LocalMissing1"])
            ->shouldBeCalled();

        $compositeJobComparison = new CompositeJobComparisonBusinessCase();
        $compositeJobComparison->addComparisonCases(
            $this->marathonCase->reveal()
        );

        $compositeJobComparison->addComparisonCases(
            $this->chronosCase->reveal()
        );


        $missingJobs =  $compositeJobComparison->getLocalMissingJobs();

        $this->assertEquals(2, count($missingJobs), "Expected 2 elements, got only " . count($missingJobs));
    }

    public function testGetRemoteMissingJobs()
    {
        $this->marathonCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"])
            ->shouldBeCalled();

        $this->chronosCase
            ->getRemoteMissingJobs()
            ->willReturn(["RemoteMissing1"])
            ->shouldBeCalled();

        $compositeJobComparison = new CompositeJobComparisonBusinessCase();
        $compositeJobComparison->addComparisonCases(
            $this->marathonCase->reveal()
        );

        $compositeJobComparison->addComparisonCases(
            $this->chronosCase->reveal()
        );

        $missingJobs =  $compositeJobComparison->getRemoteMissingJobs();

        $this->assertEquals(2, count($missingJobs), "Expected 2 elements, got only " . count($missingJobs));
    }

    public function testGetLocalJobUpdates()
    {
        $this->marathonCase
            ->getLocalJobUpdates()
            ->willReturn(["/local/update"])
            ->shouldBeCalled();

        $this->chronosCase
            ->getLocalJobUpdates()
            ->willReturn(["LocalUpdate1"])
            ->shouldBeCalled();

        $compositeJobComparison = new CompositeJobComparisonBusinessCase();
        $compositeJobComparison->addComparisonCases(
            $this->marathonCase->reveal()
        );

        $compositeJobComparison->addComparisonCases(
            $this->chronosCase->reveal()
        );

        $updatedJobs = $compositeJobComparison->getLocalJobUpdates();

        $this->assertEquals(2, count($updatedJobs), "Expected 2 elements, got only " . count($updatedJobs));
    }

    public function testGetJobDiffForMarathonCaseSuccess()
    {
        $this->marathonCase
            ->isJobAvailable(Argument::exact('/marathon/app'))
            ->willReturn(true);

        $this->chronosCase
            ->isJobAvailable(Argument::exact('/marathon/app'))
            ->willReturn(false);

        $this->marathonCase
            ->getJobDiff(Argument::exact('/marathon/app'))
            ->willReturn(["key" => "diff"]);

        $this->chronosCase
            ->getJobDiff(Argument::any())
            ->shouldNotBeCalled();


        $compositeJobComparison = new CompositeJobComparisonBusinessCase();
        $compositeJobComparison->addComparisonCases(
            $this->marathonCase->reveal()
        );

        $compositeJobComparison->addComparisonCases(
            $this->chronosCase->reveal()
        );

        $diff = $compositeJobComparison->getJobDiff('/marathon/app');
        $this->assertEquals(["key" => "diff"], $diff, "Expected diff doesn't match got diff");
    }
}
