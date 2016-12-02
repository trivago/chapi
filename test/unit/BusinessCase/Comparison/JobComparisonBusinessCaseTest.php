<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-13
 */


namespace ChapiTest\unit\BusinessCase\Comparison;


use Chapi\BusinessCase\Comparison\JobComparisonBusinessCase;
use Chapi\Component\DatePeriod\DatePeriodFactory;
use ChapiTest\src\TestTraits\JobEntityTrait;

class JobComparisonBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryMarathon;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oDiffCompare;

    /** @var DatePeriodFactory */
    private $oDatePeriodFactory;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    public function setUp()
    {
        $this->oJobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobRepositoryMarathon = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oDiffCompare = $this->prophesize('Chapi\Component\Comparison\DiffCompareInterface');
        $this->oDatePeriodFactory = new DatePeriodFactory();
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    /**
     * @requires ! PHP
     */
    public function testGetLocalJobUpdatesSuccess()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity();
        $_JobEntityA2 = $this->getValidScheduledJobEntity();
        $_JobEntityA2->scheduleTimeZone = 'Europe/London';

        $_JobEntityB1 = $this->getValidScheduledJobEntity('JobB');
        $_JobEntityB2 = $this->getValidScheduledJobEntity('JobB');
        $_JobEntityB2->schedule = 'R0/2015-09-01T02:00:00Z/P1D';

        $_JobEntityC1 = $this->getValidScheduledJobEntity('JobC');
        $_JobEntityC2 = $this->getValidScheduledJobEntity('JobC');

        $_JobEntityD1 = $this->getValidScheduledJobEntity('JobD');
        $_JobEntityD2 = $this->getValidScheduledJobEntity('JobD');
        $_iTime1 = strtotime('-1 day -2 hours');
        $_iTime2 = strtotime('+45 min');
        $_JobEntityD1->schedule = sprintf('R/%d-%s-%sT%s:00:00Z/PT1H', date('Y', $_iTime1), date('m', $_iTime1), date('d', $_iTime1), date('H', $_iTime1)); // 'R/2015-01-01T02:00:00Z/PT1H';
        $_JobEntityD2->schedule = sprintf('R/%d-%s-%sT%s:00:00Z/PT1H', date('Y', $_iTime2), date('m', $_iTime2), date('d', $_iTime2), date('H', $_iTime2)); // 'R/2015-01-01T02:00:00Z/PT1H';


        $_JobEntityE1 = $this->getValidScheduledJobEntity('JobE');
        $_JobEntityE2 = $this->getValidScheduledJobEntity('JobE');
        $_sDate1 = date('Y-m-d', strtotime('-2 day'));
        $_sDate2 = date('Y-m-d', strtotime('-1 day'));
        $_JobEntityE1->schedule = 'R/' . $_sDate1 . 'T10:30:00Z/PT1M';
        $_JobEntityE2->schedule = 'R/' . $_sDate2 . 'T13:14:00.000+02:00/PT1M';

        $_JobEntityF1 = $this->getValidScheduledJobEntity('JobF');
        $_JobEntityF2 = $this->getValidScheduledJobEntity('JobF');
        $_JobEntityF1->schedule = 'R0/2015-09-01T02:00:00Z/P1M';
        $_JobEntityF2->schedule = 'R0/2015-09-01T02:00:00Z/P1D';

        $_JobEntityG1 = $this->getValidScheduledJobEntity('JobG');
        $_JobEntityG2 = $this->getValidScheduledJobEntity('JobG');
        $_JobEntityG1->schedule = 'R/' . $_sDate1 . 'T10:30:00Z/PT10M';
        $_JobEntityG2->schedule = 'R/' . $_sDate2 . 'T13:14:00.000+02:00/PT10M';
        $_JobEntityG2->scheduleTimeZone = '';

        $_aJobCollection = [
            $_JobEntityA1,
            $_JobEntityB1,
            $_JobEntityC1,
            $_JobEntityD1,
            $_JobEntityE1,
            $_JobEntityF1,
            $_JobEntityG1
        ];

        $this->oJobRepositoryLocal
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityA2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityB1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityB2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityC1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityC2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityD1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityD2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityE1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityE2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityF1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityF2);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityG1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityG2);

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryMarathon->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA', 'JobB', 'JobF', 'JobG'],
            $_aLocalJobUpdates
        );
    }

    public function testGetLocalUpdatesForBooleanDifference()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2->disabled = true;

        $_aJobCollection = [
            $_JobEntityA1
        ];

        $this->oJobRepositoryLocal
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityA2);

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryMarathon->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );
    }

    public function testHasSameJobTypeTrue()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $_JobEntityB1 = $this->getValidDependencyJobEntity('JobB');
        $_JobEntityB2 = $this->getValidDependencyJobEntity('JobB');

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryMarathon->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $this->assertTrue($_oJobComparisonBusinessCase->hasSameJobType($_JobEntityA1, $_JobEntityA2));
        $this->assertTrue($_oJobComparisonBusinessCase->hasSameJobType($_JobEntityB1, $_JobEntityB2));
    }

    public function testHasSameJobTypeFalse()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2 = $this->getValidDependencyJobEntity('JobA');

        $_JobEntityB1 = $this->getValidDependencyJobEntity('JobB');
        $_JobEntityB2 = $this->getValidScheduledJobEntity('JobB');

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryMarathon->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $this->assertFalse($_oJobComparisonBusinessCase->hasSameJobType($_JobEntityA1, $_JobEntityA2));
        $this->assertFalse($_oJobComparisonBusinessCase->hasSameJobType($_JobEntityB1, $_JobEntityB2));
    }

    public function testGetLocalUpdatesForEnvironmentVariablesDifference()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $_oEnvironmentVariablesA = new \stdClass();
        $_oEnvironmentVariablesA->FOO = 'bar';
        $_oEnvironmentVariablesA->BAR = 'foo';

        $_oEnvironmentVariablesB = new \stdClass();
        $_oEnvironmentVariablesB->FOO = 'foo';
        $_oEnvironmentVariablesB->BAR = 'bar';

        $_JobEntityA1->environmentVariables = $_oEnvironmentVariablesA;
        $_JobEntityA2->environmentVariables = $_oEnvironmentVariablesB;

        $_aJobCollection = [
            $_JobEntityA1
        ];

        $this->oJobRepositoryLocal
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityA2);

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryMarathon->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );
    }
    
    public function testGetLocalUpdatesForConstraintsDifference()
    {
        $_JobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_JobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $_aConstraintsA = ['a', 'like', 'b'];
        $_aConstraintsB = ['b', 'like', 'c'];

        $_JobEntityA1->constraints[] = $_aConstraintsA;
        $_JobEntityA2->constraints[] = $_aConstraintsB;

        $_aJobCollection = [
            $_JobEntityA1
        ];

        $this->oJobRepositoryLocal
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityA2);

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();
        
        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );
    }

    public function testGetLocalUpdatesForContainerDifference()
    {
        $_aLocalJobUpdates = $this->setupContainerDifference('image', 'foo/bar');
        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );

        $_aLocalJobUpdates = $this->setupContainerDifference('type', 'foo');
        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );

        $_JobEntityDummy = $this->getValidContainerJobEntity('JobA');
        $_oVolume = $_JobEntityDummy->container->volumes[0];
        $_oVolume->containerPath = 'foo/bar';
        $_aLocalJobUpdates = $this->setupContainerDifference('volumes', [$_oVolume]);
        $this->assertEquals(
            ['JobA'],
            $_aLocalJobUpdates
        );
    }
    
    private function setupContainerDifference($sProperty, $mValue)
    {
        $this->setUp();
        
        $_JobEntityA1 = $this->getValidContainerJobEntity('JobA');
        $_JobEntityA2 = $this->getValidContainerJobEntity('JobA');

        $_JobEntityA2->container->{$sProperty} = $mValue;

        $_aJobCollection = [
            $_JobEntityA1
        ];

        $this->oJobRepositoryLocal
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->oJobRepositoryChronos
            ->getJob($_JobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($_JobEntityA2);

        $_oJobComparisonBusinessCase = new JobComparisonBusinessCase(
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oDiffCompare->reveal(),
            $this->oDatePeriodFactory,
            $this->oLogger->reveal()
        );

        return $_oJobComparisonBusinessCase->getLocalJobUpdates();
    }
}