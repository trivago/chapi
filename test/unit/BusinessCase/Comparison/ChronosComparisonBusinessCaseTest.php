<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-13
 */


namespace ChapiTest\unit\BusinessCase\Comparison;

use Chapi\BusinessCase\Comparison\ChronosJobComparisonBusinessCase;
use Chapi\Component\DatePeriod\DatePeriodFactory;
use ChapiTest\src\TestTraits\JobEntityTrait;

class ChronosComparisonBusinessCaseTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryLocalChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $diffCompare;

    /** @var DatePeriodFactory */
    private $datePeriodFactory;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    protected function setUp(): void
    {
        $this->jobRepositoryLocalChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->diffCompare = $this->prophesize('Chapi\Component\Comparison\DiffCompareInterface');
        $this->datePeriodFactory = new DatePeriodFactory();
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    /**
     * @requires ! PHP
     */
    public function testGetLocalJobUpdatesSuccess()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity();
        $jobEntityA2 = $this->getValidScheduledJobEntity();
        $jobEntityA2->scheduleTimeZone = 'Europe/London';

        $jobEntityB1 = $this->getValidScheduledJobEntity('JobB');
        $jobEntityB2 = $this->getValidScheduledJobEntity('JobB');
        $jobEntityB2->schedule = 'R0/2015-09-01T02:00:00Z/P1D';

        $jobEntityC1 = $this->getValidScheduledJobEntity('JobC');
        $jobEntityC2 = $this->getValidScheduledJobEntity('JobC');

        $jobEntityD1 = $this->getValidScheduledJobEntity('JobD');
        $jobEntityD2 = $this->getValidScheduledJobEntity('JobD');
        $time1 = strtotime('-1 day -2 hours');
        $time2 = strtotime('+45 min');
        $jobEntityD1->schedule = sprintf('R/%d-%s-%sT%s:00:00Z/PT1H', date('Y', $time1), date('m', $time1), date('d', $time1), date('H', $time1)); // 'R/2015-01-01T02:00:00Z/PT1H';
        $jobEntityD2->schedule = sprintf('R/%d-%s-%sT%s:00:00Z/PT1H', date('Y', $time2), date('m', $time2), date('d', $time2), date('H', $time2)); // 'R/2015-01-01T02:00:00Z/PT1H';


        $jobEntityE1 = $this->getValidScheduledJobEntity('JobE');
        $jobEntityE2 = $this->getValidScheduledJobEntity('JobE');
        $date1 = date('Y-m-d', strtotime('-2 day'));
        $date2 = date('Y-m-d', strtotime('-1 day'));
        $jobEntityE1->schedule = 'R/' . $date1 . 'T10:30:00Z/PT1M';
        $jobEntityE2->schedule = 'R/' . $date2 . 'T13:14:00.000+02:00/PT1M';

        $jobEntityF1 = $this->getValidScheduledJobEntity('JobF');
        $jobEntityF2 = $this->getValidScheduledJobEntity('JobF');
        $jobEntityF1->schedule = 'R0/2015-09-01T02:00:00Z/P1M';
        $jobEntityF2->schedule = 'R0/2015-09-01T02:00:00Z/P1D';

        $jobEntityG1 = $this->getValidScheduledJobEntity('JobG');
        $jobEntityG2 = $this->getValidScheduledJobEntity('JobG');
        $jobEntityG1->schedule = 'R/' . $date1 . 'T10:30:00Z/PT10M';
        $jobEntityG2->schedule = 'R/' . $date2 . 'T13:14:00.000+02:00/PT10M';
        $jobEntityG2->scheduleTimeZone = '';

        $jobCollection = [
            $jobEntityA1,
            $jobEntityB1,
            $jobEntityC1,
            $jobEntityD1,
            $jobEntityE1,
            $jobEntityF1,
            $jobEntityG1
        ];

        $this->jobRepositoryLocalChronos
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($jobCollection);

        $this->jobRepositoryChronos
            ->getJob($jobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityA2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityB1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityB2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityC1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityC2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityD1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityD2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityE1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityE2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityF1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityF2);

        $this->jobRepositoryChronos
            ->getJob($jobEntityG1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityG2);

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA', 'JobB', 'JobF', 'JobG'],
            $localJobUpdates
        );
    }

    public function testGetLocalUpdatesForBooleanDifference()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2->disabled = true;

        $_aJobCollection = [
            $jobEntityA1
        ];

        $this->jobRepositoryLocalChronos
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($_aJobCollection);

        $this->jobRepositoryChronos
            ->getJob($jobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityA2);

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );
    }

    public function testHasSameJobTypeTrue()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $jobEntityB1 = $this->getValidDependencyJobEntity('JobB');
        $jobEntityB2 = $this->getValidDependencyJobEntity('JobB');

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $this->assertTrue($jobComparisonBusinessCase->hasSameJobType($jobEntityA1, $jobEntityA2));
        $this->assertTrue($jobComparisonBusinessCase->hasSameJobType($jobEntityB1, $jobEntityB2));
    }

    public function testHasSameJobTypeFalse()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = $this->getValidDependencyJobEntity('JobA');

        $jobEntityB1 = $this->getValidDependencyJobEntity('JobB');
        $jobEntityB2 = $this->getValidScheduledJobEntity('JobB');

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $this->assertFalse($jobComparisonBusinessCase->hasSameJobType($jobEntityA1, $jobEntityA2));
        $this->assertFalse($jobComparisonBusinessCase->hasSameJobType($jobEntityB1, $jobEntityB2));
    }

    public function testGetLocalUpdatesForEnvironmentVariablesDifference()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $environmentVariablesA = new \stdClass();
        $environmentVariablesA->FOO = 'bar';
        $environmentVariablesA->BAR = 'foo';

        $environmentVariablesB = new \stdClass();
        $environmentVariablesB->FOO = 'foo';
        $environmentVariablesB->BAR = 'bar';

        $jobEntityA1->environmentVariables = $environmentVariablesA;
        $jobEntityA2->environmentVariables = $environmentVariablesB;

        $jobCollection = [
            $jobEntityA1
        ];

        $this->jobRepositoryLocalChronos
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($jobCollection);

        $this->jobRepositoryChronos
            ->getJob($jobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityA2);

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );
    }

    public function testGetLocalUpdatesForConstraintsDifference()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = $this->getValidScheduledJobEntity('JobA');

        $constraintsA = ['a', 'like', 'b'];
        $constraintsB = ['b', 'like', 'c'];

        $jobEntityA1->constraints[] = $constraintsA;
        $jobEntityA2->constraints[] = $constraintsB;

        $jobCollection = [
            $jobEntityA1
        ];

        $this->jobRepositoryLocalChronos
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($jobCollection);

        $this->jobRepositoryChronos
            ->getJob($jobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityA2);

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();

        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );
    }

    public function testGetLocalUpdatesForContainerDifference()
    {
        $localJobUpdates = $this->setupContainerDifference('image', 'foo/bar');
        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );

        $localJobUpdates = $this->setupContainerDifference('type', 'foo');
        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );

        $jobEntityDummy = $this->getValidContainerJobEntity('JobA');
        $volume = $jobEntityDummy->container->volumes[0];
        $volume->containerPath = 'foo/bar';
        $localJobUpdates = $this->setupContainerDifference('volumes', [$volume]);
        $this->assertEquals(
            ['JobA'],
            $localJobUpdates
        );
    }

    private function setupContainerDifference($sProperty, $mValue)
    {
        $this->setUp();

        $jobEntityA1 = $this->getValidContainerJobEntity('JobA');
        $jobEntityA2 = $this->getValidContainerJobEntity('JobA');

        $jobEntityA2->container->{$sProperty} = $mValue;

        $jobCollection = [
            $jobEntityA1
        ];

        $this->jobRepositoryLocalChronos
            ->getJobs()
            ->shouldBeCalledTimes(1)
            ->willReturn($jobCollection);

        $this->jobRepositoryChronos
            ->getJob($jobEntityA1->name)
            ->shouldBeCalledTimes(1)
            ->willReturn($jobEntityA2);

        $jobComparisonBusinessCase = new ChronosJobComparisonBusinessCase(
            $this->jobRepositoryLocalChronos->reveal(),
            $this->jobRepositoryChronos->reveal(),
            $this->diffCompare->reveal(),
            $this->datePeriodFactory,
            $this->logger->reveal()
        );

        return $jobComparisonBusinessCase->getLocalJobUpdates();
    }
}
