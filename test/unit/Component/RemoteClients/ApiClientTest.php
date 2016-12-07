<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-04
 *
 */

namespace unit\Component\RemoteClients;

use Chapi\Component\RemoteClients\ChronosApiClient;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Prophecy\Argument;

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oHttpClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oHttpResponse;

    /**
     *
     */
    public function setUp()
    {
        $this->oHttpClient = $this->prophesize('Chapi\Component\Http\HttpClientInterface');
        $this->oHttpResponse = $this->prophesize('Chapi\Component\Http\HttpClientResponseInterface');
    }

    // --------------------
    // listingJobs tests
    // --------------------
    public function testListingJobsSuccess()
    {
        $_aTestResult = ['entity1', 'entity2'];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(200);
        $this->oHttpResponse->json()->shouldBeCalledTimes(1)->willReturn($_aTestResult);

        $this->oHttpClient->get(Argument::exact('/scheduler/jobs'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertEquals($_aTestResult, $_oApiClient->listingJobs());
    }

    public function testListingJobsFailure()
    {
        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);
        $this->oHttpResponse->json()->shouldNotBeCalled();

        $this->oHttpClient->get(Argument::exact('/scheduler/jobs'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertEquals([], $_oApiClient->listingJobs());
    }

    // --------------------
    // addingJob tests
    // --------------------
    public function testAddingScheduleJobSuccess()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertTrue($_oApiClient->addingJob($_oTestJobEntity));
    }

    public function testAddingDependencyJobSuccess()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->parents = ['jobA', 'jobB'];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertTrue($_oApiClient->addingJob($_oTestJobEntity));
    }

    public function testAddingScheduleJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertFalse($_oApiClient->addingJob($_oTestJobEntity));
    }

    public function testAddingDependencyJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->parents = ['jobA', 'jobB'];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertFalse($_oApiClient->addingJob($_oTestJobEntity));
    }

    /**
     * @expectedException \Chapi\Exception\ApiClientException
     */
    public function testAddingJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();

        $this->oHttpClient->postJsonData()
            ->shouldNotBeCalled()
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertNull($_oApiClient->addingJob($_oTestJobEntity));
    }

    // --------------------
    // updatingJob tests
    // --------------------
    public function testUpdatingScheduleJobSuccess()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertTrue($_oApiClient->updatingJob($_oTestJobEntity));
    }

    public function testUpdatingDependencyJobSuccess()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->parents = ['jobA', 'jobB'];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertTrue($_oApiClient->updatingJob($_oTestJobEntity));
    }

    public function testUpdatingScheduleJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertFalse($_oApiClient->updatingJob($_oTestJobEntity));
    }

    public function testUpdatingDependencyJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();
        $_oTestJobEntity->parents = ['jobA', 'jobB'];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->oHttpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($_oTestJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertFalse($_oApiClient->updatingJob($_oTestJobEntity));
    }

    /**
     * @expectedException \Chapi\Exception\ApiClientException
     */
    public function testUpdatingJobFailure()
    {
        $_oTestJobEntity = new ChronosJobEntity();

        $this->oHttpClient->postJsonData()
            ->shouldNotBeCalled()
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertNull($_oApiClient->updatingJob($_oTestJobEntity));
    }

    // --------------------
    // removeJob tests
    // --------------------
    public function testRemoveJobSuccess()
    {
        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->oHttpClient->delete(Argument::exact('/scheduler/job/jobName'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertTrue($_oApiClient->removeJob('jobName'));
    }

    public function testRemoveJobFailure()
    {
        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->oHttpClient->delete(Argument::exact('/scheduler/job/jobName'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertFalse($_oApiClient->removeJob('jobName'));
    }

    public function testGetJobStatsSuccess()
    {
        $_aTestResult = [
            'histogram' => [
                '75thPercentile' => 2.34,
                '95thPercentile' => 1.23,
                '98thPercentile' => 1.23,
                '99thPercentile' => 1.23,
                'median' => 11.11,
                'mean' => 2.22,
                'count' => 10
            ],
            'taskStatHistory' => []
        ];

        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(200);
        $this->oHttpResponse->json()->shouldBeCalledTimes(1)->willReturn($_aTestResult);

        $this->oHttpClient->get(Argument::exact('/scheduler/job/stat/JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertEquals($_aTestResult, $_oApiClient->getJobStats('JobA'));
    }

    public function testGetJobStatsFailure()
    {
        $this->oHttpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);
        $this->oHttpResponse->json()->shouldNotBeCalled();

        $this->oHttpClient->get(Argument::exact('/scheduler/job/stat/JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oHttpResponse->reveal())
        ;

        $_oApiClient = new ChronosApiClient($this->oHttpClient->reveal());

        $this->assertEquals([], $_oApiClient->getJobStats('JobA'));
    }
}