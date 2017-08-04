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

class CompositeJobComparisonBusinessCaseTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oMarathonCase;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oChronosCase;

    public function setUp()
    {
        $this->oMarathonCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->oChronosCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
    }

    public function testGetLocalMissingJobsSuccess()
    {
        $this->oMarathonCase
            ->getLocalMissingJobs()
            ->willReturn(["/local/missing1"])
            ->shouldBeCalled();

        $this->oChronosCase
            ->getLocalMissingJobs()
            ->willReturn(["LocalMissing1"])
            ->shouldBeCalled();

        $oCompositeJobComparision = new CompositeJobComparisonBusinessCase();
        $oCompositeJobComparision->addComparisonCases(
            $this->oMarathonCase->reveal()
        );

        $oCompositeJobComparision->addComparisonCases(
            $this->oChronosCase->reveal()
        );


        $_aMissingJobs =  $oCompositeJobComparision->getLocalMissingJobs();

        $this->assertEquals(2, count($_aMissingJobs), "Expected 2 elements, got only " . count($_aMissingJobs));
    }

    public function testGetRemoteMissingJobs()
    {
        $this->oMarathonCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"])
            ->shouldBeCalled();

        $this->oChronosCase
            ->getRemoteMissingJobs()
            ->willReturn(["RemoteMissing1"])
            ->shouldBeCalled();

        $oCompositeJobComparision = new CompositeJobComparisonBusinessCase();
        $oCompositeJobComparision->addComparisonCases(
            $this->oMarathonCase->reveal()
        );

        $oCompositeJobComparision->addComparisonCases(
            $this->oChronosCase->reveal()
        );

        $_aMissingJobs =  $oCompositeJobComparision->getRemoteMissingJobs();

        $this->assertEquals(2, count($_aMissingJobs), "Expected 2 elements, got only " . count($_aMissingJobs));
    }

    public function testGetLocalJobUpdates()
    {
        $this->oMarathonCase
            ->getLocalJobUpdates()
            ->willReturn(["/local/update"])
            ->shouldBeCalled();

        $this->oChronosCase
            ->getLocalJobUpdates()
            ->willReturn(["LocalUpdate1"])
            ->shouldBeCalled();

        $oCompositeJobComparision = new CompositeJobComparisonBusinessCase();
        $oCompositeJobComparision->addComparisonCases(
            $this->oMarathonCase->reveal()
        );

        $oCompositeJobComparision->addComparisonCases(
            $this->oChronosCase->reveal()
        );

        $_aUpdatedJobs = $oCompositeJobComparision->getLocalJobUpdates();

        $this->assertEquals(2, count($_aUpdatedJobs), "Expected 2 elements, got only " . count($_aUpdatedJobs));
    }

    public function testGetJobDiffForMarathonCaseSuccess()
    {
        $this->oMarathonCase
            ->isJobAvailable(Argument::exact('/marathon/app'))
            ->willReturn(true);

        $this->oChronosCase
            ->isJobAvailable(Argument::exact('/marathon/app'))
            ->willReturn(false);

        $this->oMarathonCase
            ->getJobDiff(Argument::exact('/marathon/app'))
            ->willReturn(["key" => "diff"]);

        $this->oChronosCase
            ->getJobDiff(Argument::any())
            ->shouldNotBeCalled();


        $oCompositeJobComparision = new CompositeJobComparisonBusinessCase();
        $oCompositeJobComparision->addComparisonCases(
            $this->oMarathonCase->reveal()
        );

        $oCompositeJobComparision->addComparisonCases(
            $this->oChronosCase->reveal()
        );

        $_aDiff = $oCompositeJobComparision->getJobDiff('/marathon/app');
        $this->assertEquals(["key" => "diff"], $_aDiff, "Expected diff doesn't match got diff");
    }
}
